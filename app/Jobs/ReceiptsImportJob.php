<?php

namespace App\Jobs;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\FeeHead;
use App\Models\JournalItem;
use App\Models\StudentReceipt;
use App\Models\Utility;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ReceiptsImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;

    public $timeout = 0; // unlimited

    public function __construct($filePath, $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $success_counter = 0;
            $error_counter = 0;
            $duplication_counter = 0;
            $skip_data = [];
            $deadlock_data = [];
            $processed_records = [];
            $raw_header = [];
            $challanLateFeeCalculated = [];

            if (($handle = fopen($this->filePath, 'r')) === false) {
                \Log::error('ReceiptsImportJob: Unable to open file', ['path' => $this->filePath]);
                return;
            }

            $count = 0;

            /* ── HEADER ROW ──────────────────────────────────────────────── */
            $headerRow = fgetcsv($handle, 30000, ',');
            if ($headerRow === false) {
                \Log::error('ReceiptsImportJob: File is empty or unreadable.');
                fclose($handle);
                return;
            }

            $raw_header = $headerRow;
            $header = $headerRow;
            $header[] = 'Status';
            $header[] = 'Reason';
            $processed_records[] = $header;

            /* ── DATA ROWS ───────────────────────────────────────────────── */
            while (($all_data = fgetcsv($handle, 30000, ',')) !== false) {

                $count++;

                if ($count % 100 === 0) {
                    gc_collect_cycles();
                }

                $rowNumber = $count;
                $maxRetries = 3;
                $attempt = 0;
                $rowError = null;
                $isDeadlockRow = false;

                /* ── DEADLOCK-RETRYING TRANSACTION ───────────────────────── */
                while ($attempt <= $maxRetries) {
                    try {
                        DB::transaction(function () use ($all_data, $rowNumber, &$challanLateFeeCalculated, &$success_counter) {
                            \Log::info('Processing receipt import row', [
                                'row' => $rowNumber,
                                'challan_no' => $all_data[6] ?? 'N/A',
                                'amount' => $all_data[13] ?? 'N/A',
                            ]);

                            /* ═══════════════════════════════════════════════
                             * BASIC VALIDATION
                             * ═══════════════════════════════════════════════ */
                            if (empty(trim($all_data[6] ?? ''))) {
                                throw new \Exception('Empty Challan No');
                            }

                            $amount = (float) ($all_data[13] ?? 0);
                            if ($amount <= 0) {
                                throw new \Exception('Amount is 0 or negative');
                            }

                            \Log::debug('DEBUG all_data', ['row' => $rowNumber, 'data' => $all_data]);

                            /* ═══════════════════════════════════════════════
                             * CHALLAN LOOKUP
                             * ═══════════════════════════════════════════════ */
                            $chaln = Challans::where('challanNo', $all_data[6])
                                ->whereIn('challan_type', ['Regular', 'Advance'])
                                ->first();

                            if (!$chaln) {
                                throw new \Exception('Challan Not Exist: ' . $all_data[6]);
                            }

                            \Log::debug('DEBUG challan loaded', [
                                'row' => $rowNumber,
                                'challan_no' => $chaln->challanNo,
                                'total_amount' => $chaln->total_amount,
                                'paid_amount' => $chaln->paid_amount,
                                'status' => $chaln->status,
                            ]);

                            $student = $chaln->student;
                            $isShifaStudent = ($student && $student->register_option == 2);

                            // Always derive payment date from CSV row
                            $paymentDate = date('Y-m-d', strtotime($all_data[1]));

                            \Log::info('Challan loaded', [
                                'challan_no' => $chaln->challanNo,
                                'total_amount' => $chaln->total_amount,
                                'paid_amount' => $chaln->paid_amount,
                                'status' => $chaln->status,
                                'is_shifa' => $isShifaStudent,
                                'payment_date' => $paymentDate,
                            ]);

                            /* ═══════════════════════════════════════════════
                             * FEE HEAD IDENTIFICATION
                             * ═══════════════════════════════════════════════ */
                            $feeHeadName = trim($all_data[11] ?? '');
                            $isLateFee = (trim(strtolower($feeHeadName)) === 'late fee');
                            $isOverReceipt = (
                                stripos($feeHeadName, 'over receipt') !== false ||
                                stripos($feeHeadName, 'overreceipt') !== false
                            );

                            $feeHead = FeeHead::where('fee_head', 'like', '%' . $feeHeadName . '%')->first();

                            if (!$feeHead && !$isLateFee && !$isOverReceipt) {
                                throw new \Exception('Fee Head Not Found: ' . $feeHeadName);
                            }

                            /* ═══════════════════════════════════════════════
                             * REGULAR HEAD MUST ALREADY EXIST ON CHALLAN
                             * ═══════════════════════════════════════════════ */
                            if (!$isLateFee && !$isOverReceipt && $feeHead) {
                                $existingHead = ChallanHead::where('challan_id', $chaln->id)
                                    ->where('head_id', $feeHead->id)
                                    ->first();
                                if (!$existingHead) {
                                    throw new \Exception(
                                        'Fee head "' . $feeHeadName . '" does not exist on this challan. '
                                        . 'Cannot add new regular fee heads during receipt import.'
                                    );
                                }
                            }

                            /* ═══════════════════════════════════════════════
                             * LATE FEE CALCULATION
                             * ═══════════════════════════════════════════════ */
                            $challanKey = $chaln->id;

                            if (!$isLateFee && !$isShifaStudent) {
                                if (!isset($challanLateFeeCalculated[$challanKey])) {
                                    $this->calculateAndUpdateLateFee($chaln, $paymentDate);
                                    $challanLateFeeCalculated[$challanKey] = true;
                                    $chaln->refresh();
                                    \Log::info('Late fee calculated (first encounter)', [
                                        'challan_id' => $challanKey,
                                        'date' => $paymentDate,
                                    ]);
                                }
                            }

                            if ($isLateFee && !$isShifaStudent) {
                                $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                                if ($calculatedLateFee <= 0) {
                                    throw new \Exception(
                                        'Late fee rejected: no calculated late fee for non-Shifa challan '
                                        . $chaln->challanNo
                                    );
                                }
                            }

                            $chaln->refresh();

                            /* ═══════════════════════════════════════════════
                             * FIND OR CREATE CHALLAN HEAD
                             * ═══════════════════════════════════════════════ */
                            $challanHead = null;
                            $isNewHead = false;

                            if ($isLateFee) {

                                $latefeeHeadDef = FeeHead::where('fee_head', 'like', '%Late Fee%')->first();
                                if (!$latefeeHeadDef) {
                                    throw new \Exception('Late Fee FeeHead definition not found in system');
                                }

                                $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                    ->where('head_id', $latefeeHeadDef->id)
                                    ->first();

                                if (!$challanHead) {
                                    $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                                    if ($amount > $calculatedLateFee + 0.01) {
                                        throw new \Exception(
                                            'Late fee payment (' . number_format($amount, 2) . ') exceeds '
                                            . 'calculated (' . number_format($calculatedLateFee, 2) . ') '
                                            . 'for challan ' . $chaln->challanNo
                                        );
                                    }

                                    $isNewHead = true;
                                    $latehead = new ChallanHead;
                                    $latehead->challan_id = $chaln->id;
                                    $latehead->head_id = $latefeeHeadDef->id;
                                    $latehead->price = $calculatedLateFee;
                                    $latehead->concession = 0;
                                    $latehead->paid = 0;
                                    $latehead->save();

                                    $chaln->total_amount = ($chaln->total_amount ?? 0) + $calculatedLateFee;
                                    $chaln->save();

                                    $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                        . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                        . ' - ' . ($all_data[0] ?? '');

                                    // Income journal
                                    $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $latefeeHeadDef->account_id)
                                        ->where('head', $latefeeHeadDef->id)
                                        ->where('entry_id', $latehead->id)
                                        ->where('types', 'Challan')
                                        ->where('debit', 0)->first();

                                    if ($incomeJ) {
                                        $incomeJ->credit = ($incomeJ->credit ?? 0) + $calculatedLateFee;
                                        $incomeJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $latefeeHeadDef->account_id;
                                        $ji->head = $latefeeHeadDef->id;
                                        $ji->entry_id = $latehead->id;
                                        $ji->description = 'Income Account: ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = $calculatedLateFee;
                                        $ji->debit = 0;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    // Receivable journal
                                    $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $latefeeHeadDef->receivable_account_id)
                                        ->where('head', $latefeeHeadDef->id)
                                        ->where('entry_id', $latehead->id)
                                        ->where('types', 'Challan')
                                        ->where('credit', 0)->first();

                                    if ($receivableJ) {
                                        $receivableJ->debit = ($receivableJ->debit ?? 0) + $calculatedLateFee;
                                        $receivableJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $latefeeHeadDef->receivable_account_id;
                                        $ji->head = $latefeeHeadDef->id;
                                        $ji->entry_id = $latehead->id;
                                        $ji->description = 'Account Receivable: ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = 0;
                                        $ji->debit = $calculatedLateFee;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    $challanHead = $latehead;
                                    $chaln->refresh();
                                    $challanHead->refresh();
                                }

                            } elseif ($isOverReceipt) {

                                if (!$feeHead) {
                                    throw new \Exception('Over-receipt Fee Head Not Found: ' . $feeHeadName);
                                }

                                $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                    ->where('head_id', $feeHead->id)
                                    ->first();

                                if (!$challanHead) {
                                    $isNewHead = true;
                                    $newHead = new ChallanHead;
                                    $newHead->challan_id = $chaln->id;
                                    $newHead->head_id = $feeHead->id;
                                    $newHead->price = $amount;
                                    $newHead->concession = 0;
                                    $newHead->paid = 0;
                                    $newHead->save();

                                    $chaln->total_amount = ($chaln->total_amount ?? 0) + $amount;
                                    $chaln->save();

                                    $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                        . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                        . ' - ' . ($all_data[0] ?? '');

                                    // Income journal
                                    $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $feeHead->account_id)
                                        ->where('head', $feeHead->id)
                                        ->where('entry_id', $newHead->id)
                                        ->where('types', 'Challan')
                                        ->where('debit', 0)->first();

                                    if ($incomeJ) {
                                        $incomeJ->credit = ($incomeJ->credit ?? 0) + $amount;
                                        $incomeJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $feeHead->account_id;
                                        $ji->head = $feeHead->id;
                                        $ji->entry_id = $newHead->id;
                                        $ji->description = 'Income Account: ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = $amount;
                                        $ji->debit = 0;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    // Receivable journal
                                    $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $feeHead->receivable_account_id)
                                        ->where('head', $feeHead->id)
                                        ->where('entry_id', $newHead->id)
                                        ->where('types', 'Challan')
                                        ->where('credit', 0)->first();

                                    if ($receivableJ) {
                                        $receivableJ->debit = ($receivableJ->debit ?? 0) + $amount;
                                        $receivableJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $feeHead->receivable_account_id;
                                        $ji->head = $feeHead->id;
                                        $ji->entry_id = $newHead->id;
                                        $ji->description = 'Account Receivable: ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = 0;
                                        $ji->debit = $amount;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    $challanHead = $newHead;
                                    $chaln->refresh();
                                    $challanHead->refresh();
                                }

                            } else {
                                $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                    ->where('head_id', $feeHead->id)
                                    ->first();
                            }

                            if (!$challanHead) {
                                throw new \Exception('Could not locate or create challan head for: ' . $feeHeadName);
                            }

                            /* ═══════════════════════════════════════════════
                             * VALIDATE LATE FEE PAYMENT (existing head)
                             * ═══════════════════════════════════════════════ */
                            if ($isLateFee && !$isNewHead) {
                                $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                                $totalLateFeePayment = ($challanHead->paid ?? 0) + $amount;

                                if ($totalLateFeePayment > $calculatedLateFee + 0.01) {
                                    throw new \Exception(
                                        'Late fee payment exceeds calculated amount. '
                                        . 'Calculated: ' . number_format($calculatedLateFee, 2)
                                        . ', Already paid: ' . number_format($challanHead->paid ?? 0, 2)
                                        . ', Paying now: ' . number_format($amount, 2)
                                        . ', Total would be: ' . number_format($totalLateFeePayment, 2)
                                    );
                                }
                            }

                            /* ═══════════════════════════════════════════════
                             * OVER-RECEIPT EXPANSION
                             * ═══════════════════════════════════════════════ */
                            if ($isOverReceipt && !$isNewHead) {
                                $remainingDue = ($challanHead->price ?? 0)
                                    - (($challanHead->paid ?? 0) + ($challanHead->concession ?? 0));

                                if ($remainingDue < 0.01) {
                                    \Log::info('Over-receipt: expanding existing head', [
                                        'challan_no' => $chaln->challanNo,
                                        'expand_by' => $amount,
                                    ]);

                                    $challanHead->price = ($challanHead->price ?? 0) + $amount;
                                    $challanHead->save();

                                    $chaln->total_amount = ($chaln->total_amount ?? 0) + $amount;
                                    $chaln->save();

                                    $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                        . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                        . ' - ' . ($all_data[0] ?? '');

                                    // Income journal
                                    $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $feeHead->account_id)
                                        ->where('head', $feeHead->id)
                                        ->where('entry_id', $challanHead->id)
                                        ->where('types', 'Challan')
                                        ->where('debit', 0)->first();

                                    if ($incomeJ) {
                                        $incomeJ->credit = ($incomeJ->credit ?? 0) + $amount;
                                        $incomeJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $feeHead->account_id;
                                        $ji->head = $feeHead->id;
                                        $ji->entry_id = $challanHead->id;
                                        $ji->description = 'Income Account (Over-receipt Expansion): ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = $amount;
                                        $ji->debit = 0;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    // Receivable journal
                                    $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                        ->where('account', $feeHead->receivable_account_id)
                                        ->where('head', $feeHead->id)
                                        ->where('entry_id', $challanHead->id)
                                        ->where('types', 'Challan')
                                        ->where('credit', 0)->first();

                                    if ($receivableJ) {
                                        $receivableJ->debit = ($receivableJ->debit ?? 0) + $amount;
                                        $receivableJ->save();
                                    } else {
                                        $ji = new JournalItem;
                                        $ji->journal = $chaln->voucher_id;
                                        $ji->account = $feeHead->receivable_account_id;
                                        $ji->head = $feeHead->id;
                                        $ji->entry_id = $challanHead->id;
                                        $ji->description = 'Account Receivable (Over-receipt Expansion): ' . $description;
                                        $ji->types = 'Challan';
                                        $ji->user_id = $chaln->student_id;
                                        $ji->user_type = 'Student';
                                        $ji->credit = 0;
                                        $ji->debit = $amount;
                                        $ji->save();
                                        $ji->created_at = $chaln->created_at;
                                        $ji->updated_at = $chaln->updated_at;
                                        $ji->save();
                                    }

                                    $chaln->refresh();
                                    $challanHead->refresh();
                                }
                            }

                            /* ═══════════════════════════════════════════════
                             * VALIDATE PAYMENT VS REMAINING DUE (non-over-receipt)
                             * ═══════════════════════════════════════════════ */
                            if (!$isOverReceipt && !$isNewHead) {
                                // dd($challanHead);
                                $remainingDue = ($challanHead->price ?? 0)
                                    - (($challanHead->paid ?? 0) + ($challanHead->concession ?? 0));

                                \Log::debug('DEBUG remaining due check', [
                                    'row' => $rowNumber,
                                    'remaining_due' => $remainingDue,
                                    'amount' => $amount,
                                    'challan_no' => $chaln->challanNo,
                                    'fee_head' => $feeHeadName,
                                ]);

                                if ($amount > $remainingDue + 0.01) {
                                    throw new \Exception(
                                        'Payment (' . number_format($amount, 2) . ') exceeds remaining due '
                                        . '(' . number_format($remainingDue, 2) . ') for "' . $feeHeadName . '"'
                                    );
                                }
                            }

                            /* ═══════════════════════════════════════════════
                             * APPLY PAYMENT TO HEAD + CHALLAN
                             * ═══════════════════════════════════════════════ */
                            $challanHead->paid = ($challanHead->paid ?? 0) + $amount;
                            $challanHead->save();

                            $chaln->paid_amount = ($chaln->paid_amount ?? 0) + $amount;
                            $chaln->paid_date = $paymentDate;

                            $totalPaidWithConcession = ($chaln->paid_amount ?? 0) + ($chaln->concession_amount ?? 0);
                            if ($totalPaidWithConcession >= ($chaln->total_amount ?? 0) - 0.01) {
                                $chaln->status = 'paid';
                            } elseif (($chaln->paid_amount ?? 0) > 0) {
                                $chaln->status = 'partial';
                            } else {
                                $chaln->status = 'unpaid';
                            }
                            $chaln->save();

                            \Log::info('Payment applied', [
                                'challan_no' => $chaln->challanNo,
                                'fee_head' => $feeHeadName,
                                'amount' => $amount,
                                'challan_status' => $chaln->status,
                                'payment_date' => $paymentDate,
                            ]);

                            /* ═══════════════════════════════════════════════
                             * BANK ACCOUNT
                             * ═══════════════════════════════════════════════ */
                            $clean_title = trim(preg_replace('/\s*\(.*?\)/', '', $all_data[9]));
                            $normalizedInput = ltrim($clean_title, '0');

                            $bankAccount = BankAccount::whereRaw(
                                "TRIM(LEADING '0' FROM account_number) = ?",
                                [$normalizedInput]
                            )->first();

                            if (!$bankAccount) {
                                throw new \Exception('Bank Account Not Found: ' . $all_data[9]);
                            }

                            /* ═══════════════════════════════════════════════
                             * STUDENT RECEIPT
                             * ═══════════════════════════════════════════════ */
                            $receipt = StudentReceipt::create([
                                'recipt_date' => $paymentDate,
                                'challan_id' => $chaln->id,
                                'student_id' => $chaln->student_id,
                                'recipt_amount' => $amount,
                                'challan_amount' => $chaln->total_amount,
                                'bank_id' => $bankAccount->id,
                                'account_id' => $bankAccount->chart_account_id,
                                'referance' => $all_data[12],
                                'receive_type' => $all_data[10],
                                'received_by' => $chaln->owned_by,
                                'owned_by' => $chaln->owned_by,
                                'created_by' => auth()->user()->creatorId(),
                            ]);

                            /* ═══════════════════════════════════════════════
                             * VOUCHER ENTRY (BRV / CRV)
                             * ═══════════════════════════════════════════════ */
                            $voucherHeadId = $isLateFee
                                ? (FeeHead::where('fee_head', 'like', '%Late Fee%')->value('id'))
                                : ($feeHead->id ?? null);

                            if (!$voucherHeadId) {
                                throw new \Exception('Cannot resolve fee head ID for voucher entry');
                            }

                            $data = [
                                'id' => $chaln->id,
                                'challan_id' => $chaln->id,
                                'no' => $chaln->challanNo,
                                'prod_id' => $receipt->id,
                                'bank_id' => $bankAccount->id,
                                'branch_id' => $chaln->owned_by,
                                'date' => $paymentDate,
                                'reference' => $all_data[12],
                                'description' => $chaln->description,
                                'user_id' => $chaln->student_id,
                                'branch_name' => optional(optional($chaln->student)->branch_name)->name ?? '',
                                'std_name' => $all_data[4],
                                'recipt' => $receipt->id,
                                'bank_name' => $bankAccount->bank_name,
                                'user_type' => 'Student',
                                'amount' => $amount,
                                'category' => $chaln->challan_type,
                                'owned_by' => $chaln->owned_by,
                                'created_by' => auth()->user()->creatorId(),
                                'account_id' => $bankAccount->chart_account_id,
                                'items' => [
                                    [
                                        'head' => $voucherHeadId,
                                        'price' => $amount,
                                        'quantity' => 1,
                                        'concession' => 0,
                                        'total' => $amount,
                                    ]
                                ],
                                'total' => $amount,
                            ];

                            \Log::debug('DEBUG voucher data', ['row' => $rowNumber, 'data' => $data]);

                            ucwords($all_data[2]) === 'CD'
                                ? Utility::crv_entry($data)
                                : Utility::brv_entry($data);

                            $success_counter++;

                            \Log::info('Receipt import row successful', [
                                'row' => $rowNumber,
                                'challan_no' => $chaln->challanNo,
                                'receipt_id' => $receipt->id,
                                'payment_date' => $paymentDate,
                            ]);

                        }, 1);

                        $rowError = null;
                        break;

                    } catch (\Illuminate\Database\QueryException $e) {

                        $isDeadlock = (
                            $e->getCode() === '40001' ||
                            ($e->errorInfo[1] ?? null) === 1213
                        );

                        if ($isDeadlock && $attempt < $maxRetries) {
                            $attempt++;
                            \Log::warning('Deadlock on row, retrying', [
                                'row' => $rowNumber,
                                'attempt' => $attempt,
                            ]);
                            usleep(100000 * $attempt);
                            continue;
                        }

                        $rowError = $e;
                        $isDeadlockRow = $isDeadlock;
                        break;

                    } catch (\Throwable $e) {
                        $rowError = $e;
                        break;
                    }
                } // end while attempt

                /* ── HANDLE ROW OUTCOME ──────────────────────────────────── */
                if ($rowError !== null) {
                    $error_counter++;

                    \Log::error('Receipt import row failed', [
                        'row' => $rowNumber,
                        'challan_no' => $all_data[6] ?? 'N/A',
                        'type' => $isDeadlockRow ? 'DEADLOCK' : 'ERROR',
                        'error' => $rowError->getMessage(),
                        'line' => $rowError->getLine(),
                    ]);

                    if ($isDeadlockRow) {
                        $deadlock_data[] = $all_data;
                    } else {
                        $skip_data[] = array_merge($all_data, [
                            'Status' => 'Error',
                            'Reason' => $rowError->getMessage(),
                        ]);
                    }
                }

            } // end while fgetcsv

            fclose($handle);

            /* ════════════════════════════════════════════════════════════
             * BUILD OUTPUT FILES
             * ════════════════════════════════════════════════════════════ */
            $hasErrors = !empty($skip_data);
            $hasDeadlocks = !empty($deadlock_data);

            $deadlockFilename = null;
            $deadlockSavedPath = null;

            if ($hasDeadlocks) {
                $deadlockDir = public_path('assets/import/deadlock');
                if (!is_dir($deadlockDir)) {
                    mkdir($deadlockDir, 0755, true);
                }
                $deadlockFilename = 'receipts_import_' . time() . '_deadlock.csv';
                $deadlockSavedPath = $deadlockDir . '/' . $deadlockFilename;

                $fp = fopen($deadlockSavedPath, 'w+');
                if (!empty($raw_header)) {
                    fputcsv($fp, $raw_header);
                }
                foreach ($deadlock_data as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);

                \Log::info('Deadlock retry file saved', [
                    'path' => $deadlockSavedPath,
                    'row_count' => count($deadlock_data),
                ]);
            }

            $errorPath = null;
            if ($hasErrors) {
                $errorFile = 'receipts_import_errors_' . time() . '.csv';
                $errorPath = public_path('assets/import/errors/' . $errorFile);

                $fp = fopen($errorPath, 'w+');
                if (!empty($processed_records)) {
                    fputcsv($fp, $processed_records[0]);
                }
                foreach ($skip_data as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
            }

            \Log::info('Import completed', [
                'success' => $success_counter,
                'errors' => $error_counter,
                'deadlocks' => count($deadlock_data),
                'duplicates' => $duplication_counter,
                'deadlock_file' => $deadlockSavedPath ?? 'none',
            ]);

        } catch (\Exception $e) {
            \Log::error('ReceiptsImportJob fatal error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /* ════════════════════════════════════════════════════════════════════════
     * HELPERS
     * ════════════════════════════════════════════════════════════════════════ */

    private function calculateLateFeeAmount($challan, $paymentDate)
    {
        $dueDate = \Carbon\Carbon::parse($challan->due_date);
        $today = \Carbon\Carbon::parse($paymentDate);

        // Weekend adjustment (Saturday/Sunday → Monday)
        if ($dueDate->isSaturday()) {
            $dueDate->addDays(2);
        } elseif ($dueDate->isSunday()) {
            $dueDate->addDays(1);
        }

        if ($today->lte($dueDate)) {
            return 0;
        }

        $daysOverdue = min($today->diffInDays($dueDate), 10);
        $lateFeePerDay = 120;
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;

        return $lateFeeAmount;
    }

    private function calculateAndUpdateLateFee($challan, $paymentDate)
    {
        \Log::info('=== START calculateAndUpdateLateFee ===', [
            'challan_id' => $challan->id,
            'status' => $challan->status,
            'due_date' => $challan->due_date,
            'paid_date' => $challan->paid_date ?? null,
        ]);

        $status = strtolower($challan->status ?? '');

        if ($status === 'paid') {
            \Log::info('Challan already fully paid. Skipping.');
            return;
        }

        $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();
        if (!$lateFeeHead) {
            \Log::error('Late Fee Head not found.');
            return;
        }

        $existingLateFee = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        // Special case: partial paid with existing paid_date but no late fee yet
        if (
            in_array($status, ['partial', 'partial paid']) &&
            !empty($challan->paid_date) &&
            !$existingLateFee
        ) {
            \Log::info('Partial paid with paid_date found. Using paid_date for late fee calculation.');
            $calculationDate = \Carbon\Carbon::parse($challan->paid_date);
        } else {
            $calculationDate = \Carbon\Carbon::parse($paymentDate);
        }

        $dueDate = \Carbon\Carbon::parse($challan->due_date);

        // Weekend adjustment
        if ($dueDate->isSaturday()) {
            $dueDate->addDays(2);
        } elseif ($dueDate->isSunday()) {
            $dueDate->addDays(1);
        }

        if ($calculationDate->lte($dueDate)) {
            \Log::info('Not overdue.');
            return;
        }

        $daysOverdue = min($calculationDate->diffInDays($dueDate), 10);
        $lateFeePerDay = 120;
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;

        if ($lateFeeAmount <= 0) {
            return;
        }

        \Log::info('Late fee calculated', [
            'days_overdue' => $daysOverdue,
            'late_fee' => $lateFeeAmount,
        ]);

        /* ── UPDATE EXISTING LATE FEE ──────────────────────────────────── */
        if ($existingLateFee) {

            $oldPrice = $existingLateFee->price ?? 0;
            $challan->total_amount -= $oldPrice;

            $existingLateFee->update([
                'price' => $lateFeeAmount,
                'updated_at' => now(),
            ]);

            JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('debit', 0)
                ->update(['credit' => $lateFeeAmount, 'updated_at' => now()]);

            JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('credit', 0)
                ->update(['debit' => $lateFeeAmount, 'updated_at' => now()]);

            $challan->total_amount += $lateFeeAmount;
            $challan->save();

            \Log::info('Existing late fee updated.');
            return;
        }

        /* ── CREATE NEW LATE FEE ───────────────────────────────────────── */
        \Log::info('Creating new late fee.');

        $latehead = ChallanHead::create([
            'challan_id' => $challan->id,
            'head_id' => $lateFeeHead->id,
            'price' => $lateFeeAmount,
            'concession' => 0,
            'paid' => 0,
        ]);

        JournalItem::create([
            'journal' => $challan->voucher_id,
            'account' => $lateFeeHead->account_id,
            'head' => $lateFeeHead->id,
            'entry_id' => $latehead->id,
            'user_id' => $challan->student_id,
            'user_type' => 'Student',
            'types' => 'Challan',
            'credit' => $lateFeeAmount,
            'debit' => 0,
            'description' => 'Late Fee Income - Challan No ' . $challan->challanNo,
        ]);

        JournalItem::create([
            'journal' => $challan->voucher_id,
            'account' => $lateFeeHead->receivable_account_id,
            'head' => $lateFeeHead->id,
            'entry_id' => $latehead->id,
            'user_id' => $challan->student_id,
            'user_type' => 'Student',
            'types' => 'Challan',
            'credit' => 0,
            'debit' => $lateFeeAmount,
            'description' => 'Late Fee Receivable - Challan No ' . $challan->challanNo,
        ]);

        $challan->total_amount += $lateFeeAmount;
        $challan->save();

        \Log::info('Late fee created successfully.');
        \Log::info('=== END calculateAndUpdateLateFee ===');
    }
}