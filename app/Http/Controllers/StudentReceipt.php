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
    // =========================================================================
    // INDEX / LIST
    // =========================================================================

    public function index(Request $request)
    {
        $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        $accounts->prepend('Select Bank', 'allbank');

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

        if (!empty($request->default_bank) && $request->default_bank != 'allbank') {
            $query->where('bank_id', $request->default_bank);
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

        $accountsArray = $accounts->toArray();
        $accounts = ['allbank' => 'Select all banks'] + $accountsArray;

        if ($request->filled('challan_no')) {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challanNo', $request->challan_no);
            });
        } else {
            $fromDate = $request->from_date ?? date('Y-m-d');
            $toDate = $request->to_date ?? $fromDate;
            $query->whereBetween('recipt_date', [$fromDate, $toDate]);

            if ($request->filled('default_bank') && $request->default_bank != 'allbank') {
                $query->where('bank_id', $request->default_bank);
            }
            if ($request->filled('branches')) {
                $query->where('student_receipts.owned_by', $request->branches);
            }
            if ($request->filled('student')) {
                $query->where('student_id', $request->student);
            }
        }

        $recipts = $query->orderBy('recipt_date', 'Asc')->get();
        $session = [];
        $class = [];
        $students = [];

        return view('students.studentreceipt.list', compact('accounts', 'recipts', 'session', 'class', 'students', 'branches'));
    }

    // =========================================================================
    // CRUD STUBS
    // =========================================================================

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit($id)
    {
        $recipt = Receipt::with('challan')->findOrFail($id);

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

        $hasReceiptItems = JournalItem::where('journal', $recipt->voucher_id)
            ->where('receipt_id', $recipt->id)
            ->where('types', 'Challan Payment')
            ->exists();
        // dd($hasReceiptItems);
        if ($hasReceiptItems) {
            // New system: only this receipt's heads
            $voucher = JournalItem::where('journal', $recipt->voucher_id)
                ->where('receipt_id', $recipt->id)
                ->where('credit', '>', 0)
                ->where('debit', 0)
                ->where('types', 'Challan Payment')
                ->get();
        } else {
            // Old system: receipt_id null, keep previous behavior
            $voucher = JournalItem::where('journal', $recipt->voucher_id)
                ->whereNull('receipt_id')
                ->where('credit', '>', 0)
                ->where('debit', 0)
                ->where('types', 'Challan Payment')
                ->get();
        }

        return view('students.studentreceipt.edit', compact('recipt', 'voucher', 'accounts'));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // ------------------------------------------------------------------
            // 1. Load receipt and snapshot old values
            // ------------------------------------------------------------------
            $receipt = Receipt::findOrFail($id);
            $oldAmount = (float) $receipt->recipt_amount;
            $oldBankId = (int) $receipt->bank_id;
            $oldReceiveType = $receipt->receive_type;
            $oldDate = $receipt->recipt_date;

            // Reverse the old bank balance immediately so we start from a clean slate
            Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');

            // ------------------------------------------------------------------
            // 2. Load challan
            // ------------------------------------------------------------------
            $challan = Challans::find($receipt->challan_id);
            if (!$challan) {
                throw new \Exception('Challan not found.');
            }

            // ------------------------------------------------------------------
            // 3. Normalise items arrays
            // ------------------------------------------------------------------
            $normaliseItems = function (array $items): array {
                return array_map(function ($item) {
                    // Unify 'credit' → 'amount'
                    if (!isset($item['amount']) && isset($item['credit'])) {
                        $item['amount'] = $item['credit'];
                    }
                    $item['amount'] = (float) ($item['amount'] ?? 0);

                    // Resolve head_id from challan_head if missing
                    if (empty($item['head_id']) && !empty($item['challan_head_id'])) {
                        $ch = ChallanHead::find($item['challan_head_id']);
                        if ($ch) {
                            $item['head_id'] = (int) $ch->head_id;
                        }
                    }

                    return $item;
                }, $items);
            };

            $newItems = $normaliseItems($request->items ?? []);
            $oldItems = $normaliseItems($request->old_items ?? []);

            // Derive old items from journal when not sent by front-end
            if (empty($oldItems)) {
                $journalEntry = JournalEntry::find($receipt->voucher_id);
                if ($journalEntry) {
                    $creditLines = JournalItem::where('journal', $journalEntry->id)
                        ->where('types', 'Challan Payment')
                        ->where('credit', '>', 0)
                        ->where('debit', 0)
                        ->get();

                    foreach ($creditLines as $line) {
                        $ch = ChallanHead::where('challan_id', $receipt->challan_id)
                            ->where('head_id', $line->head)
                            ->first();

                        if ($ch) {
                            $oldItems[] = [
                                'head_id' => (int) $line->head,
                                'challan_head_id' => (int) $ch->id,
                                'amount' => (float) $line->credit,
                            ];
                        }
                    }
                }

                if (empty($oldItems)) {
                    $oldItems = $newItems;
                }
            }

            // ------------------------------------------------------------------
            // 4. Handle zero-amount → delete receipt
            // ------------------------------------------------------------------
            if ((float) $request->recipt_amount == 0.0) {
                $this->deleteReceiptAndReverse($receipt, $challan, $oldItems, $oldBankId, $oldAmount);
                DB::commit();
                return back()->with('success', 'Receipt deleted successfully.');
            }

            // ------------------------------------------------------------------
            // 5. Parse new date and new total
            // ------------------------------------------------------------------
            $timestamp = strtotime(str_replace('/', '-', $request->recipt_date));
            $newDate = date('Y-m-d', $timestamp);
            $newTotal = array_sum(array_column($newItems, 'amount'));
            $newReceiptDate = Carbon::parse($newDate);

            // ------------------------------------------------------------------
            // 6. Late fee recalculation based on new date
            //
            //    KEY RULE for the 50% check:
            //    The challan's paid_amount still includes THIS receipt's old amount.
            //    We must subtract oldAmount before testing the 50% threshold,
            //    because this receipt is being re-evaluated, not "already paid".
            // ------------------------------------------------------------------
            $this->recalculateLateFeeForEdit(
                $challan,
                $receipt,
                $oldAmount,
                $newDate,
                $request->payment_method
            );

            // Refresh challan so total_amount reflects any late fee change
            $challan->refresh();

            // ------------------------------------------------------------------
            // 7. Apply new bank balance credit
            // ------------------------------------------------------------------
            Utility::bankAccountBalance($request->bank_id, $newTotal, 'credit');

            // ------------------------------------------------------------------
            // 8. Update receipt record fields
            // ------------------------------------------------------------------
            $receipt->recipt_date = $newDate;
            $receipt->bank_id = $request->bank_id;
            $receipt->receive_type = $request->payment_method;
            $receipt->recipt_amount = $newTotal;
            $receipt->challan_amount = $request->challan_amt ?? $receipt->challan_amount;
            $receipt->late_amount = $request->late_amt ?? $receipt->late_amount;
            $receipt->arrears = $request->arrears ?? $receipt->arrears;
            $receipt->referance = $request->ref ?? $receipt->referance;

            // ------------------------------------------------------------------
            // 9. Determine voucher type
            // ------------------------------------------------------------------
            $newVoucherType = (strtoupper($request->payment_method) === 'CD') ? 'CRV' : 'BRV';

            $newBank = BankAccount::find($request->bank_id);
            if (!$newBank || !$newBank->chart_account_id) {
                throw new \Exception('Bank account does not have a Chart of Account attached.');
            }

            $oldJournal = JournalEntry::find($receipt->voucher_id);
            $dateChanged = ($oldDate !== $newDate);
            $bankChanged = ($oldBankId !== (int) $request->bank_id);
            $typeChanged = ($oldReceiveType !== $request->payment_method);

            // ------------------------------------------------------------------
            // 10. Journal adjustments
            // ------------------------------------------------------------------
            if ($dateChanged && $oldJournal) {
                // Date changed → move receipt lines to new/existing journal for new date
                $this->removeReceiptCreditLines($oldJournal, $receipt, $oldItems);
                $this->adjustBankDebitLine($oldJournal, $oldAmount, 'subtract');
                $this->cleanupJournalIfEmpty($oldJournal);

                $newJournal = $this->findOrCreateJournal(
                    $challan,
                    $newDate,
                    (int) $request->bank_id,
                    $newVoucherType,
                    $receipt,
                    $newBank
                );

                $this->upsertCreditLines($newJournal, $newItems, $receipt, $newBank, []);
                $this->adjustBankDebitLine($newJournal, $newTotal, 'add', $newBank, $challan, $receipt);

                $receipt->voucher_id = $newJournal->id;

            } elseif ($oldJournal) {
                // Same date – update bank/type/amounts in place
                if ($bankChanged) {
                    $oldJournal->bank_id = $newBank->id;
                    $oldJournal->save();

                    $desc = 'Receive of Challan no: ' . $challan->challanNo . ' - Bank: ' . $newBank->bank_name;

                    JournalItem::where('journal', $oldJournal->id)
                        ->where('debit', '>', 0)
                        ->where('credit', 0)
                        ->where('types', 'Challan Payment')
                        ->update([
                            'account' => $newBank->chart_account_id,
                            'bank_id' => $newBank->id,
                            'description' => $desc,
                        ]);

                    JournalItem::where('journal', $oldJournal->id)
                        ->where('credit', '>', 0)
                        ->where('debit', 0)
                        ->where('types', 'Challan Payment')
                        ->update(['bank_id' => $newBank->id, 'description' => $desc]);
                }

                if ($typeChanged) {
                    $newJournalId = (JournalEntry::where('owned_by', $challan->owned_by)
                        ->where('voucher_type', $newVoucherType)
                        ->max('journal_id') ?? 0) + 1;

                    $oldJournal->voucher_type = $newVoucherType;
                    $oldJournal->journal_id = $newJournalId;
                    $oldJournal->save();
                }

                $this->upsertCreditLines($oldJournal, $newItems, $receipt, $newBank, $oldItems);

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

            // ------------------------------------------------------------------
            // 11. Update ChallanHead paid amounts (only if amounts changed)
            // ------------------------------------------------------------------
            $amountsChanged = $this->haveAmountsChanged($newItems, $oldItems);

            if ($amountsChanged) {
                foreach ($oldItems as $oldItem) {
                    $ch = ChallanHead::find($oldItem['challan_head_id'] ?? null);
                    if ($ch) {
                        $ch->paid = max(0, $ch->paid - $oldItem['amount']);
                        $ch->save();
                    }
                }

                foreach ($newItems as $newItem) {
                    $ch = ChallanHead::find($newItem['challan_head_id'] ?? null);
                    if ($ch) {
                        $ch->paid = $ch->paid + $newItem['amount'];
                        $ch->save();
                    }
                }
            }

            // ------------------------------------------------------------------
            // 12. Update challan paid_amount and status
            // ------------------------------------------------------------------
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

    // =========================================================================
    // LATE FEE: RECALCULATION ON EDIT
    // =========================================================================

    /**
     * Recalculate the late fee when a receipt is being edited.
     *
     * Scenario A – new date is ON or BEFORE due date:
     *   → Remove any existing late fee from the journal / challan head.
     *
     * Scenario B – new date is AFTER due date:
     *   → Recalculate late fee using the new date.
     *   → 50% exemption check: EXCLUDE the current receipt's old amount from
     *     paid_amount before checking, because that amount is being re-evaluated.
     *
     * The actual DB writes (ChallanHead, JournalItem, challan.total_amount)
     * are handled by the existing calculateAndUpdateLateFee() method.
     */
    private function recalculateLateFeeForEdit(
        Challans $challan,
        Receipt $receipt,
        float $oldReceiptAmount,
        string $newDate,
        string $paymentMethod
    ): void {
        // Only applies to regular / advance challans
        $type = strtolower(trim($challan->challan_type ?? ''));
        if ($type !== 'regular' && $type !== 'advance') {
            return;
        }

        // Already fully paid challan – nothing to do
        if (strtolower($challan->status) === 'paid') {
            return;
        }

        $dueDate = Carbon::parse($challan->due_date);
        $newReceiptDate = Carbon::parse($newDate);

        // ------------------------------------------------------------------
        // Scenario A: New date is on/before due date → wipe late charges
        // ------------------------------------------------------------------
        if ($newReceiptDate->lte($dueDate)) {
            $this->removeLateChargesFromJournal($receipt->voucher_id, $challan);
            return;
        }

        // ------------------------------------------------------------------
        // Scenario B: New date is after due date → recalculate
        // ------------------------------------------------------------------

        // 50% exemption check with THIS receipt excluded.
        // challan->paid_amount still includes oldReceiptAmount at this point.
        $netPayable = (float) $challan->total_amount - (float) ($challan->concession_amount ?? 0);
        $paidExcludingThis = max(0.0, (float) $challan->paid_amount - $oldReceiptAmount);

        if ($netPayable > 0 && $paidExcludingThis >= ($netPayable * 0.5)) {
            // Already crossed 50% even without this receipt → no late fee
            return;
        }

        // student register_option == 2 → exempt
        if (optional($challan->student)->register_option == 2) {
            return;
        }

        // Delegate to the shared helper which handles create-or-update of the
        // ChallanHead, JournalItems, and challan.total_amount.
        $this->calculateAndUpdateLateFee($challan, $newDate, $paymentMethod);
    }

    // =========================================================================
    // LATE FEE: SHARED CALCULATION + JOURNAL WRITE
    // =========================================================================

    /**
     * Calculate the late fee amount and persist it:
     *  - Creates or updates the LATE FEE ChallanHead entry.
     *  - Creates or updates the corresponding JournalItems (income + receivable).
     *  - Adjusts challan.total_amount.
     *
     * Mirrors the logic in paidchallan() so both paths stay in sync.
     */
    private function calculateAndUpdateLateFee(
        Challans $challan,
        string $paymentDate,
        ?string $receiveType = null
    ): void {
        if (strtolower($challan->status) === 'paid') {
            return;
        }

        $type = strtolower(trim($challan->challan_type ?? ''));
        if ($type !== 'regular' && $type !== 'advance') {
            return;
        }

        $dueDate = Carbon::parse($challan->due_date);
        $today = Carbon::parse($paymentDate);

        if ($today->lte($dueDate)) {
            return;
        }

        $daysOverdue = $today->diffInDays($dueDate);

        // OL grace: exactly 1 day late → exempt
        if ($receiveType && strtoupper($receiveType) === 'OL' && $daysOverdue == 1) {
            return;
        }

        // 50% rule (standard check – used by paidchallan path)
        $totalPayable = (float) $challan->total_amount - (float) ($challan->concession_amount ?? 0);
        $alreadyPaid = (float) ($challan->paid_amount ?? 0);

        if ($totalPayable > 0 && $alreadyPaid >= ($totalPayable * 0.5)) {
            return;
        }

        // Compute late fee (120/day, max 1200)
        $lateFeeAmount = min($daysOverdue * 120, 1200);

        $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();
        if (!$lateFeeHead) {
            return;
        }

        $existingLateFee = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        if ($existingLateFee) {
            // Remove old amount from total, update, add new amount
            $challan->total_amount -= $existingLateFee->price;
            $existingLateFee->update(['price' => $lateFeeAmount, 'updated_at' => now()]);

            // Update income journal item
            $incomeItem = JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('debit', 0)
                ->first();
            if ($incomeItem) {
                $incomeItem->update(['credit' => $lateFeeAmount, 'updated_at' => now()]);
            }

            // Update receivable journal item
            $receivableItem = JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('credit', 0)
                ->first();
            if ($receivableItem) {
                $receivableItem->update(['debit' => $lateFeeAmount, 'updated_at' => now()]);
            }

            $challan->total_amount += $lateFeeAmount;
            $challan->save();

        } else {
            // Create new ChallanHead for late fee
            $latehead = ChallanHead::create([
                'challan_id' => $challan->id,
                'head_id' => $lateFeeHead->id,
                'price' => $lateFeeAmount,
                'concession' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($latehead) {
                $feeHeads = FeeHead::find($lateFeeHead->id);

                // Income entry
                $ji = new JournalItem;
                $ji->journal = $challan->voucher_id;
                $ji->account = $feeHeads->account_id;
                $ji->head = $lateFeeHead->id;
                $ji->entry_id = $latehead->id;
                $ji->user_id = $challan->student_id;
                $ji->user_type = 'Student';
                $ji->description = 'Income Account: Roll no ' . $challan->student->roll_no
                    . ' Challan no ' . $challan->challanNo;
                $ji->types = 'Challan';
                $ji->credit = $lateFeeAmount;
                $ji->debit = 0;
                $ji->created_at = $challan->created_at;
                $ji->updated_at = $challan->updated_at;
                $ji->save();

                // Receivable entry
                $ji2 = new JournalItem;
                $ji2->journal = $challan->voucher_id;
                $ji2->account = $feeHeads->receivable_account_id;
                $ji2->head = $lateFeeHead->id;
                $ji2->entry_id = $latehead->id;
                $ji2->user_id = $challan->student_id;
                $ji2->user_type = 'Student';
                $ji2->description = 'Account Receivable: Roll no ' . $challan->student->roll_no
                    . ' Challan no ' . $challan->challanNo;
                $ji2->types = 'Challan';
                $ji2->credit = 0;
                $ji2->debit = $lateFeeAmount;
                $ji2->created_at = $challan->created_at;
                $ji2->updated_at = $challan->updated_at;
                $ji2->save();
            }

            $challan->total_amount += $lateFeeAmount;
            $challan->save();
        }
    }

    // =========================================================================
    // LATE FEE: REMOVE LATE CHARGES (company posts within due date)
    // =========================================================================

    /**
     * Remove any late fee journal lines from a receipt's journal entry and
     * zero out the corresponding ChallanHead and challan totals.
     */
    private function removeLateChargesFromJournal(?int $journalId, Challans $challan): void
    {
        if (!$journalId) {
            return;
        }

        $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();
        if (!$lateFeeHead) {
            return;
        }

        $existingCH = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        if (!$existingCH || $existingCH->price <= 0) {
            return;
        }

        $lateFeeAmount = (float) $existingCH->price;

        // Remove late fee journal items (types = 'Challan', tied to challan's voucher)
        JournalItem::where('journal', $challan->voucher_id)
            ->where('head', $lateFeeHead->id)
            ->delete();

        // Zero out ChallanHead
        $existingCH->price = 0;
        $existingCH->save();

        // Reduce challan total
        $challan->total_amount = max(0, $challan->total_amount - $lateFeeAmount);
        $challan->save();

        // Zero out late_amount on any receipts linked to this journal
        Receipt::where('voucher_id', $journalId)->update(['late_amount' => 0]);
    }

    // =========================================================================
    // DELETE RECEIPT (zero-amount path)
    // =========================================================================

    private function deleteReceiptAndReverse(
        Receipt $receipt,
        Challans $challan,
        array $oldItems,
        int $oldBankId,
        float $oldAmount
    ): void {
        $journalId = $receipt->voucher_id;

        if ($journalId) {
            $otherReceipts = Receipt::where('voucher_id', $journalId)
                ->where('id', '!=', $receipt->id)
                ->count();

            if ($otherReceipts == 0) {
                JournalItem::where('journal', $journalId)->delete();
                JournalEntry::where('id', $journalId)->delete();
            } else {
                $this->reverseReceiptJournalItems($receipt, $oldItems);
            }
        }

        foreach ($oldItems as $item) {
            $ch = ChallanHead::find($item['challan_head_id'] ?? null);
            if ($ch) {
                $ch->paid = max(0, $ch->paid - $item['amount']);
                $ch->save();
            }
        }

        $challan->paid_amount = max(0, $challan->paid_amount - $oldAmount);
        $this->recalculateChallanStatus($challan);

        // Note: bank balance was already reversed at the top of update()
        $receipt->delete();
    }

    // =========================================================================
    // JOURNAL HELPERS
    // =========================================================================

    /**
     * Remove ONLY this receipt's credit contributions from a journal.
     * Only touches lines with types = 'Challan Payment'.
     */
    private function removeReceiptCreditLines(
        JournalEntry $journal,
        Receipt $receipt,
        array $oldItems
    ): void {
        foreach ($oldItems as $oldItem) {
            $headId = $oldItem['head_id'] ?? null;
            $contribution = (float) ($oldItem['amount'] ?? 0);

            if (!$headId || $contribution <= 0) {
                continue;
            }

            $feeHead = FeeHead::find($headId);

            if (!$feeHead) {
                continue;
            }

            $account = ChartOfAccount::find($feeHead->receivable_account_id);

            if (!$account) {
                continue;
            }

            $line = JournalItem::where('journal', $journal->id)
                ->where('receipt_id', $receipt->id)
                ->where('account', $account->id)
                ->where('head', $headId)
                ->where('types', 'Challan Payment')
                ->first();

            if (!$line) {
                $line = JournalItem::where('journal', $journal->id)
                    ->whereNull('receipt_id')
                    ->where('account', $account->id)
                    ->where('head', $headId)
                    ->where('types', 'Challan Payment')
                    ->first();
            }

            if (!$line) {
                continue;
            }

            $line->credit = max(0, $line->credit - $contribution);

            if ($line->credit <= 0) {
                $line->delete();
            } else {
                $line->save();
            }
        }
    }

    private function upsertCreditLines(
        JournalEntry $journal,
        array $items,
        Receipt $receipt,
        BankAccount $bank,
        array $oldItems
    ): void {
        $challan = Challans::find($receipt->challan_id);
        $processedHeadIds = [];

        foreach ($items as $item) {
            $newAmount = (float) ($item['amount'] ?? 0);
            $headId = isset($item['head_id']) ? (int) $item['head_id'] : null;

            if (!$headId) {
                continue;
            }
            $processedHeadIds[] = $headId;

            $oldContribution = 0.0;

            foreach ($oldItems as $oldItem) {
                $oldHeadId = isset($oldItem['head_id']) ? (int) $oldItem['head_id'] : null;

                if ($oldHeadId === $headId) {
                    $oldContribution = (float) ($oldItem['amount'] ?? 0);
                    break;
                }
            }

            if ($newAmount <= 0) {
                if ($oldContribution > 0) {
                    $this->removeReceiptCreditLines($journal, $receipt, [[
                        'head_id' => $headId,
                        'amount' => $oldContribution,
                    ]]);
                }
                continue;
            }

            $feeHead = FeeHead::find($headId);

            if (!$feeHead) {
                throw new \Exception('FeeHead not found: ' . $headId);
            }

            $account = ChartOfAccount::find($feeHead->receivable_account_id);

            if (!$account) {
                throw new \Exception('Chart of Account not found for FeeHead: ' . $headId);
            }

            $existingLine = JournalItem::where('journal', $journal->id)
                ->where('receipt_id', $receipt->id)
                ->where('account', $account->id)
                ->where('head', $headId)
                ->where('types', 'Challan Payment')
                ->first();

            if (!$existingLine) {
                $existingLine = JournalItem::where('journal', $journal->id)
                    ->whereNull('receipt_id')
                    ->where('account', $account->id)
                    ->where('head', $headId)
                    ->where('types', 'Challan Payment')
                    ->first();
            }

            $now = $receipt->recipt_date . ' ' . Carbon::now()->format('H:i:s');

            if ($existingLine) {
                $existingLine->credit = max(0, $existingLine->credit - $oldContribution) + $newAmount;
                $existingLine->receipt_id = $existingLine->receipt_id ?? null;
                $existingLine->bank_id = $bank->id;
                $existingLine->created_at = $now;
                $existingLine->updated_at = $now;
                $existingLine->save();
            } else {
                JournalItem::create([
                    'journal' => $journal->id,
                    'receipt_id' => $receipt->id,
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
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ($oldItems as $oldItem) {
            $oldHeadId = isset($oldItem['head_id']) ? (int) $oldItem['head_id'] : null;
            $oldContribution = (float) ($oldItem['amount'] ?? 0);

            if ($oldHeadId && $oldContribution > 0 && !in_array($oldHeadId, $processedHeadIds, true)) {
                $this->removeReceiptCreditLines($journal, $receipt, [[
                    'head_id' => $oldHeadId,
                    'amount' => $oldContribution,
                ]]);
            }
        }
    }

    private function adjustBankDebitLine(
        JournalEntry $journal,
        float $amount,
        string $action,
        ?BankAccount $bank = null,
        ?Challans $challan = null,
        ?Receipt $receipt = null
    ): void {
        $debitLine = null;

        if ($receipt) {
            $debitLine = JournalItem::where('journal', $journal->id)
                ->where('receipt_id', $receipt->id)
                ->where('types', 'Challan Payment')
                ->where('debit', '>', 0)
                ->where('credit', 0)
                ->first();

            if (!$debitLine) {
                $debitLine = JournalItem::where('journal', $journal->id)
                    ->whereNull('receipt_id')
                    ->where('types', 'Challan Payment')
                    ->where('debit', '>', 0)
                    ->where('credit', 0)
                    ->first();
            }
        } else {
            $debitLine = JournalItem::where('journal', $journal->id)
                ->where('types', 'Challan Payment')
                ->where('debit', '>', 0)
                ->where('credit', 0)
                ->first();
        }

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

            if ($receipt) {
                $debitLine->receipt_id = $debitLine->receipt_id ?? null;
                $now = $receipt->recipt_date . ' ' . Carbon::now()->format('H:i:s');
                $debitLine->created_at = $now;
                $debitLine->updated_at = $now;
            }

            $debitLine->save();

        } elseif ($action === 'add' && $amount > 0 && $bank && $challan) {
            JournalItem::create([
                'journal' => $journal->id,
                'receipt_id' => $receipt ? $receipt->id : null,
                'account' => $bank->chart_account_id,
                'bank_id' => $bank->id,
                'branch_id' => $challan->owned_by ?? null,
                'types' => 'Challan Payment',
                'user_type' => 'Student',
                'description' => 'Receive of Challan no: ' . $challan->challanNo . ' - Bank: ' . $bank->bank_name,
                'credit' => 0,
                'debit' => $amount,
                'created_at' => $receipt ? ($receipt->recipt_date . ' ' . Carbon::now()->format('H:i:s')) : now(),
                'updated_at' => $receipt ? ($receipt->recipt_date . ' ' . Carbon::now()->format('H:i:s')) : now(),
            ]);
        }
    }

    /**
     * Find an existing journal for date/bank/type or create a fresh one.
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
                'created_at' => $date,
                'updated_at' => $date,
            ]
        );

        Receipt::where('id', $receipt->id)->update(['voucher_id' => $journal->id]);

        return $journal;
    }

    /**
     * Delete journal entry if no items remain.
     */
    private function cleanupJournalIfEmpty(JournalEntry $journal): void
    {
        if (JournalItem::where('journal', $journal->id)->count() === 0) {
            $journal->delete();
        }
    }

    // =========================================================================
    // CHALLAN STATUS HELPERS
    // =========================================================================

    private function recalculateChallanStatus(Challans $challan): void
    {
        $due = $challan->total_amount - ($challan->paid_amount + $challan->concession_amount);

        $challan->status = match (true) {
            $due <= 0 => 'Paid',
            $challan->paid_amount > 0 => 'Partial Paid',
            default => 'Issued',
        };

        if ($challan->status === 'Issued') {
            $challan->paid_date = null;
        }

        $challan->save();
    }

    // =========================================================================
    // RECEIPT REVERSAL (used by delete path)
    // =========================================================================

    private function reverseReceiptJournalItems(Receipt $receipt, array $oldItems): void
    {
        $journal = JournalEntry::find($receipt->voucher_id);
        if (!$journal) {
            return;
        }

        $hasValidItems = !empty($oldItems)
            && isset($oldItems[0]['head_id'])
            && isset($oldItems[0]['amount'])
            && $oldItems[0]['amount'] > 0;

        if ($hasValidItems) {
            $this->removeReceiptCreditLines($journal, $receipt, $oldItems);
        } else {
            $otherReceiptsOnJournal = Receipt::where('voucher_id', $journal->id)
                ->where('id', '!=', $receipt->id)
                ->count();

            if ($otherReceiptsOnJournal == 0) {
                JournalItem::where('journal', $journal->id)->delete();
                $journal->delete();
                return;
            }

            $challanHeads = ChallanHead::where('challan_id', $receipt->challan_id)->get();

            foreach ($challanHeads as $ch) {
                $feeHead = FeeHead::find($ch->head_id);
                if (!$feeHead) {
                    continue;
                }

                $account = ChartOfAccount::find($feeHead->receivable_account_id);
                if (!$account) {
                    continue;
                }

                $line = JournalItem::where('journal', $journal->id)
                    ->where('account', $account->id)
                    ->where('head', $ch->head_id)
                    ->where('types', 'Challan Payment')
                    ->first();

                if (!$line) {
                    continue;
                }

                $line->credit = max(0, $line->credit - $ch->paid);
                if ($line->credit <= 0) {
                    $line->delete();
                } else {
                    $line->save();
                }
            }
        }

        $this->adjustBankDebitLine($journal, (float) $receipt->recipt_amount, 'subtract');
        $this->cleanupJournalIfEmpty($journal);
    }

    // =========================================================================
    // UTILITY: AMOUNTS CHANGED CHECK
    // =========================================================================

    private function haveAmountsChanged(array $newItems, array $oldItems): bool
    {
        foreach ($newItems as $newItem) {
            $oldContribution = 0.0;
            foreach ($oldItems as $oldItem) {
                if (($oldItem['challan_head_id'] ?? null) == ($newItem['challan_head_id'] ?? null)) {
                    $oldContribution = (float) ($oldItem['amount'] ?? 0);
                    break;
                }
            }
            if ((float) $newItem['amount'] !== $oldContribution) {
                return true;
            }
        }
        return false;
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy($id)
    {
    }
}
