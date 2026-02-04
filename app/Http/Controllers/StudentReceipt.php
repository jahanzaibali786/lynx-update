<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\JournalEntry;
use App\Models\Challans;
use App\Models\JournalItem;
use App\Models\StudentReceipt as Receipt;
use App\Exports\StudentReceiptExport;
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
            $totalCredits = 0;
            $hasNonZeroItems = false;
            
            // Store old values for bank balance adjustment
            $oldBankId = $receipt->bank_id;
            $oldAmount = $receipt->recipt_amount;
            
            // Get bank account details if bank_id is provided
            $bankAccount = null;
            $bankName = '';
            if (!empty($request->bank_id)) {
                $bankAccount = BankAccount::find($request->bank_id);
                if ($bankAccount) {
                    $bankName = $bankAccount->bank_name;
                }
            }
            
            // Get challan number from receipt relationship
            $challanNo = $receipt->challan ? $receipt->challan->challanNo : '';
            
            // Use receipt date for timestamps
            $receiptDateTime = !empty($request->recipt_date) 
                ? \Carbon\Carbon::parse($request->recipt_date) 
                : now();
            
            // Track challan IDs that need updating
            $challansToUpdate = [];
            
            // Process each item and check for zero amounts
            foreach ($request->items as $itemData) {
                $creditAmount = floatval($itemData['credit']);
                
                // Skip items with 0 credit (will be removed)
                if ($creditAmount == 0) {
                    continue;
                }
                
                $hasNonZeroItems = true;
                
                $journalItem = JournalItem::findOrFail($itemData['journal_item_id']);
                $challanHead = ChallanHead::findOrFail($itemData['challan_head_id']);
                
                // Calculate maximum allowed credit
                $maxCredit = $challanHead->price - $challanHead->paid + $journalItem->credit;
                
                if ($creditAmount > $maxCredit) {
                    throw new \Exception("Credit for head {$challanHead->feeHead->fee_head} exceeds remaining balance.");
                }
                
                // Update journal item
                if (!empty($request->bank_id) && $bankAccount) {
                    $journalItem->bank_id = $request->bank_id;
                    $journalItem->account = $bankAccount->chart_account_id;
                }
                $journalItem->credit = $creditAmount;
                $journalItem->created_at = $receiptDateTime;
                $journalItem->updated_at = $receiptDateTime;
                $journalItem->save();
                
                // Update challan head paid amount
                $challanHead->paid = ($challanHead->paid + $creditAmount) - $itemData['voucher_old'];
                $challanHead->created_at = $receiptDateTime;
                $challanHead->updated_at = $receiptDateTime;
                $challanHead->save();
                
                // Track challan for update
                if (!isset($challansToUpdate[$challanHead->challan_id])) {
                    $challansToUpdate[$challanHead->challan_id] = 0;
                }
                
                $totalCredits += $creditAmount;
            }
            
            // If all items are zero, delete receipt and rollback everything
            if (!$hasNonZeroItems) {
                $journalId = null;
                
                // Find journal ID from any item
                foreach ($request->items as $itemData) {
                    $journalItem = JournalItem::find($itemData['journal_item_id']);
                    if ($journalItem) {
                        $journalId = $journalItem->journal;
                        break;
                    }
                }
                
                // Rollback all challan head amounts
                foreach ($request->items as $itemData) {
                    $journalItem = JournalItem::find($itemData['journal_item_id']);
                    if ($journalItem) {
                        $challanHead = ChallanHead::find($itemData['challan_head_id']);
                        if ($challanHead) {
                            // Rollback the paid amount
                            $challanHead->paid = $challanHead->paid - $itemData['voucher_old'];
                            $challanHead->created_at = $receiptDateTime;
                            $challanHead->updated_at = $receiptDateTime;
                            $challanHead->save();
                            
                            // Track challan for update
                            if (!isset($challansToUpdate[$challanHead->challan_id])) {
                                $challansToUpdate[$challanHead->challan_id] = 0;
                            }
                        }
                    }
                }
                
                // Update all affected challans
                foreach ($challansToUpdate as $challanId => $dummy) {
                    $this->updateChallanTotals($challanId, $receiptDateTime);
                }
                
                // Delete all journal items (receipt vouchers) for this journal
                if ($journalId) {
                    JournalItem::where('journal', $journalId)->delete();
                    JournalEntry::where('id', $journalId)->delete();
                }
                
                // Reverse bank balance (deduct the old amount)
                if (!empty($oldBankId) && $oldAmount > 0) {
                    Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');
                }
                
                // Delete the receipt
                $receipt->delete();
                
                DB::commit();
                return redirect()
                    ->route('student_receipt.index')
                    ->with('success', __('Receipt deleted as all amounts were set to zero.'));
            }
            
            // Remove journal items with 0 credit
            foreach ($request->items as $itemData) {
                $creditAmount = floatval($itemData['credit']);
                
                if ($creditAmount == 0) {
                    $journalItem = JournalItem::find($itemData['journal_item_id']);
                    if ($journalItem) {
                        $challanHead = ChallanHead::find($itemData['challan_head_id']);
                        if ($challanHead) {
                            // Rollback the paid amount for this head
                            $challanHead->paid = $challanHead->paid - $itemData['voucher_old'];
                            $challanHead->created_at = $receiptDateTime;
                            $challanHead->updated_at = $receiptDateTime;
                            $challanHead->save();
                            
                            // Track challan for update
                            if (!isset($challansToUpdate[$challanHead->challan_id])) {
                                $challansToUpdate[$challanHead->challan_id] = 0;
                            }
                        }
                        
                        // Delete the journal item
                        $journalItem->delete();
                    }
                }
            }
            
            // Update receipt details (only if not empty)
            if (!empty($request->recipt_date)) {
                $receipt->recipt_date = $request->recipt_date;
            }
            $receipt->recipt_amount = $totalCredits;
            if (!empty($request->bank_id)) {
                $receipt->bank_id = $request->bank_id;
            }
            if (!empty($request->payment_method)) {
                $receipt->receive_type = $request->payment_method;
            }
            if (!empty($request->reference)) {
                $receipt->referance = $request->reference;
            }
            $receipt->created_at = $receiptDateTime;
            $receipt->updated_at = $receiptDateTime;
            $receipt->save();
            
            // Get journal ID from remaining items
            $journalId = null;
            foreach ($request->items as $itemData) {
                if (floatval($itemData['credit']) > 0) {
                    $journalItem = JournalItem::find($itemData['journal_item_id']);
                    if ($journalItem) {
                        $journalId = $journalItem->journal;
                        break;
                    }
                }
            }
            
            // Update debit line and all related records
            if ($journalId) {
                // Update debit line (bank account entry)
                $debitLine = JournalItem::where('journal', $journalId)
                    ->where('debit', '!=', 0)
                    ->first();
                
                if (!$debitLine) {
                    throw new \Exception("Debit line not found for journal #{$journalId}.");
                }
                
                // Update bank account and description for debit line
                if (!empty($request->bank_id) && $bankAccount) {
                    $debitLine->bank_id = $request->bank_id;
                    $debitLine->account = $bankAccount->chart_account_id;
                    $debitLine->description = 'Receive of Challan no: ' . $challanNo . ' - Bank: ' . $bankName;
                }
                $debitLine->debit = $totalCredits;
                $debitLine->created_at = $receiptDateTime;
                $debitLine->updated_at = $receiptDateTime;
                $debitLine->save();
                
                // Update bank_id, account, and timestamps for all credit lines in this journal
                if (!empty($request->bank_id) && $bankAccount) {
                    $creditLines = JournalItem::where('journal', $journalId)
                        ->where('credit', '!=', 0)
                        ->get();
                    
                    foreach ($creditLines as $creditLine) {
                        $creditLine->bank_id = $request->bank_id;
                        $creditLine->account = $bankAccount->chart_account_id;
                        
                        // Update description to include bank name
                        $creditLine->description = 'Receive of Challan no: ' . $challanNo . ' - Bank: ' . $bankName;
                        
                        $creditLine->created_at = $receiptDateTime;
                        $creditLine->updated_at = $receiptDateTime;
                        $creditLine->save();
                    }
                } else {
                    // Update only timestamps if bank_id is empty
                    JournalItem::where('journal', $journalId)
                        ->where('credit', '!=', 0)
                        ->update([
                            'created_at' => $receiptDateTime,
                            'updated_at' => $receiptDateTime
                        ]);
                }
                
                // Update journal entry
                $journal = JournalEntry::find($journalId);
                if ($journal) {
                    if (!empty($request->reference)) {
                        $journal->reference = $request->reference;
                    }
                    if (!empty($request->bank_id)) {
                        $journal->bank_id = $request->bank_id;
                    }
                    $journal->created_at = $receiptDateTime;
                    $journal->updated_at = $receiptDateTime;
                    $journal->save();
                }
            }
            
            // Update all affected challans
            foreach ($challansToUpdate as $challanId => $dummy) {
                $this->updateChallanTotals($challanId, $receiptDateTime);
            }
            
            // Handle Bank Balance Updates
            $newBankId = !empty($request->bank_id) ? $request->bank_id : $oldBankId;
            
            // Case 1: Same bank, different amount
            if ($oldBankId == $newBankId && !empty($oldBankId)) {
                if ($oldAmount != $totalCredits) {
                    // Reverse old amount
                    Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');
                    // Add new amount
                    Utility::bankAccountBalance($newBankId, $totalCredits, 'credit');
                }
            }
            // Case 2: Different bank
            elseif ($oldBankId != $newBankId) {
                // Reverse from old bank
                if (!empty($oldBankId) && $oldAmount > 0) {
                    Utility::bankAccountBalance($oldBankId, $oldAmount, 'debit');
                }
                // Add to new bank
                if (!empty($newBankId) && $totalCredits > 0) {
                    Utility::bankAccountBalance($newBankId, $totalCredits, 'credit');
                }
            }
            
            DB::commit();
            return redirect()
                ->route('student_receipt.index')
                ->with('success', __('Receipt updated successfully.'));
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors($e->getMessage());
        }
    }
    /**
     * Update challan total_amount and paid_amount based on its heads
     */
    private function updateChallanTotals($challanId, $receiptDateTime)
    {
        $challan = Challans::find($challanId);
        if (!$challan) {
            return;
        }
        
        // Calculate totals from challan heads
        $heads = ChallanHead::where('challan_id', $challanId)->get();
        
        $totalAmount = $heads->sum('price') + $heads->sum('concession');
        $concessionAmount = $heads->sum('concession');
        $paidAmount = $heads->sum('paid');
        
        // Update challan
        $challan->total_amount = $totalAmount;
        $challan->concession_amount = $concessionAmount;
        $challan->paid_amount = $paidAmount;
        $challan->updated_at = $receiptDateTime;
        $challan->save();
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
