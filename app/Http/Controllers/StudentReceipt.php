<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\ChartOfAccount;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\Challans;
use App\Models\JournalItem;
use App\Models\StudentReceipt as Receipt;
use App\Exports\StudentReceiptExport;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Utility;
use Auth;
use DB;
use Illuminate\Http\Request;

class StudentReceipt extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd('');
        $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        $query = Receipt::with('challan');

        if (\Auth::user()->type == 'company') {
            $query->where(function ($q) {
                $q->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('received_by', \Auth::user()->id);
            });
        } else {
            $query->where(function ($q) {
                $q->where('owned_by', \Auth::user()->ownedId())
                    ->orWhere('received_by', \Auth::user()->id);
            });
        }

        if (!empty($request->date)) {
            $query->whereDate('recipt_date', $request->date);
        } else {
            $query->whereDate('recipt_date', date("Y-m-d"));
        }

        $recipts = $query->get();

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('students.studentreceipt.index', compact('accounts', 'recipts'));
    }


    public function list(Request $request)
    {
        // Get accounts and branches
        if (\Auth::user()->type == 'company') {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');

            $branches = User::where('type', '=', 'branch')->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $query = Receipt::with('challan')->where('student_receipts.created_by', \Auth::user()->creatorId());
        } else {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('owned_by', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id');

            $branches = User::where('id', '=', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $query = Receipt::with('challan')->where('student_receipts.owned_by', \Auth::user()->ownedId());
        }

        // Convert accounts to array
        $accountsArray = $accounts->toArray();
        $accounts = ['allbank' => 'Select all banks'] + $accountsArray;

        // If challan_no is given, only filter by that
        if ($request->filled('challan_no')) {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challanNo', $request->challan_no);
            });
        } else {
            // Apply from_date and to_date filter (default today)
            $fromDate = $request->from_date ?? date('Y-m-d');
            $toDate = $request->to_date ?? $fromDate;

            $query->whereBetween('recipt_date', [$fromDate, $toDate]);

            // Filter by bank
            if ($request->filled('default_bank') && $request->default_bank != 'allbank') {
                $query->where('bank_id', $request->default_bank);
            }

            // Filter by branches
            if ($request->filled('branches')) {
                $query->where('student_receipts.owned_by', $request->branches);
            }

            // Filter by student
            if ($request->filled('student')) {
                $query->where('student_id', $request->student);
            }
        }

        // Get results
        $recipts = $query->orderBy('recipt_date', 'Asc')->get();
        $session = [];
        $class = [];
        $students = [];

        return view('students.studentreceipt.list', compact('accounts', 'recipts', 'session', 'class', 'students', 'branches'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recipt = Receipt::with('challan')->find($id);
        if (\Auth::user()->type == 'company') {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
        } else {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('owned_by', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id');
        }
        $voucher = JournalItem::where('journal', $recipt->voucher_id)->where('credit', '!=', '0')->get();
        return view('students.studentreceipt.edit', compact('recipt', 'voucher', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $receipt = Receipt::findOrFail($id);
            $oldAmount = (float) $receipt->recipt_amount;
            $oldBankId = (int) $receipt->bank_id;
            Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');
            $oldReceiveType = $receipt->receive_type;
            $oldDate = $receipt->recipt_date;

            $challan = Challans::find($receipt->challan_id);
            if (!$challan) {
                throw new \Exception('Challan not found.');
            }

            // ── Normalise items: unify 'credit' and 'amount' keys → 'amount' ────
            $normaliseItems = function (array $items): array {
                return array_map(function ($item) {
                    if (!isset($item['amount']) && isset($item['credit'])) {
                        $item['amount'] = $item['credit'];
                    }
                    $item['amount'] = (float) ($item['amount'] ?? 0);

                    // ── Resolve head_id from challan_head if missing ─────────────
                    if (empty($item['head_id']) && !empty($item['challan_head_id'])) {
                        $challanHead = ChallanHead::find($item['challan_head_id']);
                        if ($challanHead) {
                            $item['head_id'] = (int) $challanHead->head_id;
                        }
                    }

                    return $item;
                }, $items);
            };

            $newItems = $normaliseItems($request->items ?? []);
            $oldItems = $normaliseItems($request->old_items ?? []);

            // ── If old_items not provided by frontend, derive from journal ───────
            // Fetch per-head credit amounts from the journal tied to this receipt.
            // Safe because:
            //   - bank/type only changed  → amounts same → $amountsChanged = false
            //   - amounts changed         → frontend must send old_items correctly
            if (empty($oldItems)) {
                $journalEntry = JournalEntry::find($receipt->voucher_id);
                if ($journalEntry) {
                    $creditLines = JournalItem::where('journal', $journalEntry->id)
                        ->where('types', 'Challan Payment')
                        ->where('credit', '>', 0)
                        ->where('debit', 0)
                        ->get();

                    foreach ($creditLines as $line) {
                        $challanHead = ChallanHead::where('challan_id', $receipt->challan_id)
                            ->where('head_id', $line->head)
                            ->first();

                        if ($challanHead) {
                            $oldItems[] = [
                                'head_id' => (int) $line->head,
                                'challan_head_id' => (int) $challanHead->id,
                                'amount' => (float) $line->credit,
                            ];
                        }
                    }
                }

                // Final fallback: mirror newItems so $amountsChanged = false
                // and ChallanHead paid amounts are not touched
                if (empty($oldItems)) {
                    $oldItems = $newItems;
                }
            }

            /*
            |---------------------------------------------
            | If Amount = 0 → Delete Receipt & Reverse
            |---------------------------------------------
            */
            if ((float) $request->recipt_amount == 0.0) {

                $journalId = $receipt->voucher_id;

                if ($journalId) {
                    $otherReceipts = Receipt::where('voucher_id', $journalId)
                        ->where('id', '!=', $receipt->id)
                        ->count();

                    if ($otherReceipts === 0) {
                        // Only receipt on this journal — hard delete everything
                        JournalItem::where('journal', $journalId)->delete();
                        JournalEntry::where('id', $journalId)->delete();
                    } else {
                        // Shared journal — remove only this receipt's lines
                        $this->reverseReceiptJournalItems($receipt, $oldItems);
                    }
                }

                // Reverse challan_head paid amounts
                foreach ($oldItems as $item) {
                    $challanHead = ChallanHead::find($item['challan_head_id']);
                    if ($challanHead) {
                        $challanHead->paid = max(0, $challanHead->paid - $item['amount']);
                        $challanHead->save();
                    }
                }

                // Reverse challan paid_amount
                $challan->paid_amount = max(0, $challan->paid_amount - $oldAmount);
                $this->recalculateChallanStatus($challan);
                Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');
                $receipt->delete();
                DB::commit();
                return back()->with('success', 'Receipt deleted successfully.');
            }

            $newTotal = array_sum(array_column($newItems, 'amount'));
            Utility::bankAccountBalance($request->bank_id, $newTotal, 'credit');
            /*
            |---------------------------------------------
            | Update Receipt Record
            |---------------------------------------------
            */
            $timestamp = strtotime(str_replace('/', '-', $request->recipt_date));
            $newDate = date('Y-m-d', $timestamp);

            $receipt->recipt_date = $newDate;
            $receipt->bank_id = $request->bank_id;
            $receipt->receive_type = $request->payment_method;
            $receipt->recipt_amount = $newTotal;
            $receipt->challan_amount = $request->challan_amt ?? $receipt->challan_amount;
            $receipt->late_amount = $request->late_amt ?? $receipt->late_amount;
            $receipt->arrears = $request->arrears ?? $receipt->arrears;
            $receipt->referance = $request->ref ?? $receipt->referance;

            /*
            |---------------------------------------------
            | Determine new voucher type
            |---------------------------------------------
            */
            $newVoucherType = (strtoupper($request->payment_method) === 'CD') ? 'CRV' : 'BRV';

            $newBank = BankAccount::find($request->bank_id);
            if (!$newBank || !$newBank->chart_account_id) {
                throw new \Exception('Bank account does not have a Chart of Account attached.');
            }

            $oldJournal = JournalEntry::find($receipt->voucher_id);

            $dateChanged = ($oldDate !== $newDate);
            $bankChanged = ($oldBankId !== (int) $request->bank_id);
            $typeChanged = ($oldReceiveType !== $request->payment_method);
            $needsJournalMove = $dateChanged;

            if ($needsJournalMove && $oldJournal) {
                /*
                |---------------------------------------------
                | DATE CHANGED — move lines to correct journal
                |---------------------------------------------
                */

                // Remove this receipt's credit lines from old journal
                $this->removeReceiptCreditLines($oldJournal, $receipt, $oldItems);

                // Reduce debit on old journal
                $this->adjustBankDebitLine($oldJournal, $oldAmount, 'subtract');

                // Delete old journal if now empty
                $this->cleanupJournalIfEmpty($oldJournal);

                // Find/create journal for new date + bank + type
                $newJournal = $this->findOrCreateJournal(
                    $challan,
                    $newDate,
                    (int) $request->bank_id,
                    $newVoucherType,
                    $receipt,
                    $newBank
                );

                // Insert fresh credit lines (no old contribution — brand new journal)
                $this->upsertCreditLines($newJournal, $newItems, $receipt, $newBank, []);

                // Add debit line to new journal
                $this->adjustBankDebitLine($newJournal, $newTotal, 'add', $newBank, $challan, $receipt);

                $receipt->voucher_id = $newJournal->id;

            } else {
                /*
                |---------------------------------------------
                | SAME JOURNAL — update everything in place
                |---------------------------------------------
                */
                if ($oldJournal) {

                    /*
                    |---------------------------------------------
                    | Bank changed → update journal + both line
                    | sides in place, no migration needed
                    |---------------------------------------------
                    */
                    if ($bankChanged) {
                        $oldJournal->bank_id = $newBank->id;
                        $oldJournal->save();
                        $newDescription = 'Receive of Challan no: ' . $challan->challanNo . ' - Bank: ' . $newBank->bank_name;

                        // Update DEBIT line — chart account + bank_id
                        JournalItem::where('journal', $oldJournal->id)
                            ->where('debit', '>', 0)
                            ->where('credit', 0)
                            ->where('types', 'Challan Payment')
                            ->update([
                                'account' => $newBank->chart_account_id,
                                'bank_id' => $newBank->id,
                                'description' => $newDescription,
                            ]);

                        // Update CREDIT lines — bank_id only
                        JournalItem::where('journal', $oldJournal->id)
                            ->where('credit', '>', 0)
                            ->where('debit', 0)
                            ->where('types', 'Challan Payment')
                            ->update(['bank_id' => $newBank->id, 'description' => $newDescription]);
                    }

                    /*
                    |---------------------------------------------
                    | Voucher type changed → update type AND
                    | regenerate journal_id for new sequence
                    |---------------------------------------------
                    */
                    if ($typeChanged) {
                        $newJournalId = (JournalEntry::where('owned_by', $challan->owned_by)
                            ->where('voucher_type', $newVoucherType)
                            ->max('journal_id') ?? 0) + 1;

                        $oldJournal->voucher_type = $newVoucherType;
                        $oldJournal->journal_id = $newJournalId;
                        $oldJournal->save();
                    }

                    /*
                    |---------------------------------------------
                    | Update credit line amounts
                    |---------------------------------------------
                    */
                    $this->upsertCreditLines($oldJournal, $newItems, $receipt, $newBank, $oldItems);

                    /*
                    |---------------------------------------------
                    | Adjust debit line amount only if total changed
                    |---------------------------------------------
                    */
                    $diff = $newTotal - $oldAmount;
                    if ($diff != 0.0) {
                        $this->adjustBankDebitLine(
                            $oldJournal,
                            abs($diff),
                            $diff > 0 ? 'add' : 'subtract',
                            $newBank,
                            $challan,
                            $receipt
                        );
                    }
                }
            }

            /*
            |---------------------------------------------
            | Update ChallanHead paid amounts
            | ONLY if amounts actually changed
            |---------------------------------------------
            */
            $amountsChanged = false;

            foreach ($newItems as $newItem) {
                $oldContribution = 0.0;
                foreach ($oldItems as $oldItem) {
                    if (($oldItem['challan_head_id'] ?? null) == ($newItem['challan_head_id'] ?? null)) {
                        $oldContribution = (float) ($oldItem['amount'] ?? 0);
                        break;
                    }
                }
                if ((float) $newItem['amount'] !== $oldContribution) {
                    $amountsChanged = true;
                    break;
                }
            }

            if ($amountsChanged) {
                // Reverse old head amounts
                foreach ($oldItems as $oldItem) {
                    $challanHead = ChallanHead::find($oldItem['challan_head_id']);
                    if ($challanHead) {
                        $challanHead->paid = max(0, $challanHead->paid - $oldItem['amount']);
                        $challanHead->save();
                    }
                }

                // Apply new head amounts
                foreach ($newItems as $newItem) {
                    $challanHead = ChallanHead::find($newItem['challan_head_id']);
                    if ($challanHead) {
                        $challanHead->paid = $challanHead->paid + $newItem['amount'];
                        $challanHead->save();
                    }
                }
            }
            // $oldItems;
            /*
            |---------------------------------------------
            | Update Challan paid_amount and status
            |---------------------------------------------
            */
            if ($newTotal !== $oldAmount) {
                $challan->paid_amount = max(0, $challan->paid_amount - $oldAmount + $newTotal);
            }
            $this->recalculateChallanStatus($challan);

            $receipt->save();

            DB::commit();
            return back()->with('success', 'Receipt updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |=============================================================
    | HELPER METHODS
    |=============================================================
    */

    /**
     * Remove ONLY this receipt's credit contributions from a journal.
     * Uses $oldItems (already normalised) — no request() access inside helpers.
     */
    private function removeReceiptCreditLines(
        JournalEntry $journal,
        Receipt $receipt,
        array $oldItems
    ): void {
        foreach ($oldItems as $oldItem) {
            $headId = $oldItem['head_id'] ?? null;
            $contribution = (float) ($oldItem['amount'] ?? 0);

            if (!$headId || $contribution <= 0)
                continue;

            $feeHead = FeeHead::find($headId);
            if (!$feeHead)
                continue;

            $account = ChartOfAccount::find($feeHead->receivable_account_id);
            if (!$account)
                continue;

            $line = JournalItem::where('journal', $journal->id)
                ->where('account', $account->id)
                ->where('head', $headId)
                ->where('types', 'Challan Payment')
                ->first();

            if (!$line)
                continue;

            $line->credit = max(0, $line->credit - $contribution);
            if ($line->credit <= 0) {
                $line->delete();
            } else {
                $line->save();
            }
        }
    }

    /**
     * Upsert credit lines for this receipt's items.
     * $oldItems = [] when moving to a brand-new journal.
     */
    private function upsertCreditLines(
        JournalEntry $journal,
        array $items,
        Receipt $receipt,
        BankAccount $bank,
        array $oldItems
    ): void {
        $challan = Challans::find($receipt->challan_id);

        foreach ($items as $item) {
            $newAmount = (float) ($item['amount'] ?? 0);
            $headId = isset($item['head_id']) ? (int) $item['head_id'] : null;

            if ($newAmount <= 0 || !$headId)
                continue;

            $feeHead = FeeHead::find($headId);
            if (!$feeHead)
                throw new \Exception('FeeHead not found: ' . $headId);

            $account = ChartOfAccount::find($feeHead->receivable_account_id);
            if (!$account)
                throw new \Exception('Chart of Account not found for FeeHead: ' . $headId);

            // ── Find old contribution by head_id (cast both sides to int) ────
            $oldContribution = 0.0;
            foreach ($oldItems as $oldItem) {
                $oldHeadId = isset($oldItem['head_id']) ? (int) $oldItem['head_id'] : null;
                if ($oldHeadId === $headId) {
                    $oldContribution = (float) ($oldItem['amount'] ?? 0);
                    break;
                }
            }

            $existingLine = JournalItem::where('journal', $journal->id)
                ->where('account', $account->id)
                ->where('head', $headId)
                ->where('types', 'Challan Payment')
                ->first();

            if ($existingLine) {
                // Subtract old contribution, add new amount
                $existingLine->credit = max(0, $existingLine->credit - $oldContribution) + $newAmount;
                $existingLine->save();
            } else {
                JournalItem::create([
                    'journal' => $journal->id,
                    'account' => $account->id,
                    'head' => $headId,
                    'bank_id' => $bank->id,
                    'user_id' => $receipt->student_id,
                    'user_type' => 'Student',
                    'types' => 'Challan Payment',
                    'branch_id' => $challan->owned_by ?? null,
                    'description' => 'Receive of Challan no: ' . ($challan->challanNo ?? '') . ' - Bank: ' . $bank->bank_name,
                    'credit' => $newAmount,
                    'debit' => 0,
                ]);
            }
        }
    }
    /**
     * Add or subtract from the bank DEBIT line of a journal.
     * Never touches credit lines.
     */
    private function adjustBankDebitLine(
        JournalEntry $journal,
        float $amount,
        string $action,   // 'add' | 'subtract'
        ?BankAccount $bank = null,
        ?Challans $challan = null,
        ?Receipt $receipt = null
    ): void {
        $debitLine = JournalItem::where('journal', $journal->id)
            ->where('types', 'Challan Payment')
            ->where('debit', '>', 0)
            ->where('credit', 0)
            ->first();

        if ($debitLine) {
            $debitLine->debit = $action === 'add'
                ? $debitLine->debit + $amount
                : max(0, $debitLine->debit - $amount);

            if ($debitLine->debit <= 0) {
                $debitLine->delete();
                return;
            }

            if ($bank) {
                $debitLine->account = $bank->chart_account_id;
                $debitLine->bank_id = $bank->id;
            }
            $debitLine->save();

        } elseif ($action === 'add' && $amount > 0 && $bank && $challan) {
            JournalItem::create([
                'journal' => $journal->id,
                'account' => $bank->chart_account_id,
                'bank_id' => $bank->id,
                'branch_id' => $challan->owned_by ?? null,
                'types' => 'Challan Payment',
                'user_type' => 'Student',
                'description' => 'Receive of Challan no: ' . $challan->challanNo . ' - Bank: ' . $bank->bank_name,
                'credit' => 0,
                'debit' => $amount,
            ]);
        }
    }

    /**
     * Find existing journal or create a new one for new date/bank/type.
     */
    private function findOrCreateJournal(
        Challans $challan,
        string $date,
        int $bankId,
        string $voucherType,
        Receipt $receipt,
        BankAccount $bank
    ): JournalEntry {
        $journal = JournalEntry::firstOrCreate(
            [
                'owned_by' => $challan->owned_by,
                'voucher_type' => $voucherType,
                'date' => $date,
                'challan_id' => $challan->id,
                'bank_id' => $bankId,
                'user_id' => $challan->student_id,
            ],
            [
                'journal_id' => (JournalEntry::where('owned_by', $challan->owned_by)
                    ->where('voucher_type', $voucherType)
                    ->max('journal_id') ?? 0) + 1,
                'description' => 'Challan No: ' . $challan->challanNo,
                'reference_id' => $receipt->id,
                'reference' => $receipt->referance,
                'category' => $challan->challan_type,
                'user_type' => 'Student',
                'created_by' => $receipt->created_by,
            ]
        );

        Receipt::where('id', $receipt->id)->update(['voucher_id' => $journal->id]);

        return $journal;
    }

    /**
     * Delete journal entry if it has no items left.
     */
    private function cleanupJournalIfEmpty(JournalEntry $journal): void
    {
        if (JournalItem::where('journal', $journal->id)->count() === 0) {
            $journal->delete();
        }
    }

    /**
     * Recalculate and save challan status.
     */
    private function recalculateChallanStatus(Challans $challan): void
    {
        $due = $challan->total_amount - ($challan->paid_amount + $challan->concession_amount);

        $challan->status = match (true) {
            $due <= 0 => 'Paid',
            $challan->paid_amount > 0 => 'Partial Paid',
            default => 'Issued',
        };

        // If status becomes Issued, reset paid_date
        if ($challan->status == 'Issued') {
            $challan->paid_date = null;
        }

        $challan->save();
    }

    /**
     * Reverse all journal items for a deleted receipt.
     */
    private function reverseReceiptJournalItems(Receipt $receipt, array $oldItems): void
    {
        $journal = JournalEntry::find($receipt->voucher_id);
        if (!$journal)
            return;

        $hasValidItems = !empty($oldItems)
            && isset($oldItems[0]['head_id'])
            && isset($oldItems[0]['amount'])
            && $oldItems[0]['amount'] > 0;

        if ($hasValidItems) {
            // Normal path — subtract per-head contributions
            $this->removeReceiptCreditLines($journal, $receipt, $oldItems);
        } else {
            // Fallback — check if other receipts share this journal
            $otherReceiptsOnJournal = Receipt::where('voucher_id', $journal->id)
                ->where('id', '!=', $receipt->id)
                ->count();

            if ($otherReceiptsOnJournal === 0) {
                JournalItem::where('journal', $journal->id)->delete();
                $journal->delete();
                return;
            }

            // Shared journal — subtract per challan head paid amounts
            $challanHeads = ChallanHead::where('challan_id', $receipt->challan_id)->get();

            foreach ($challanHeads as $challanHead) {
                $feeHead = FeeHead::find($challanHead->head_id);
                if (!$feeHead)
                    continue;

                $account = ChartOfAccount::find($feeHead->receivable_account_id);
                if (!$account)
                    continue;

                $line = JournalItem::where('journal', $journal->id)
                    ->where('account', $account->id)
                    ->where('head', $challanHead->head_id)
                    ->where('types', 'Challan Payment')
                    ->first();

                if (!$line)
                    continue;

                $line->credit = max(0, $line->credit - $challanHead->paid);
                if ($line->credit <= 0) {
                    $line->delete();
                } else {
                    $line->save();
                }
            }
        }

        // Always adjust debit line
        $this->adjustBankDebitLine($journal, (float) $receipt->recipt_amount, 'subtract');

        // Cleanup journal if now empty
        $this->cleanupJournalIfEmpty($journal);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
