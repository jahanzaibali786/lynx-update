<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\JournalItem;
use App\Models\StudentReceipt as Receipt;
use App\Exports\StudentReceiptExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
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
        $recipts = $query->get();
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
        $voucher = JournalItem::where('journal', $recipt->voucher_id)->where('credit', '!=', '0')->get();
        return view('students.studentreceipt.edit', compact('recipt', 'voucher'));
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
            $receipt->recipt_date = $request->recipt_date;
            $receipt->recipt_amount = $request->recipt_amount;
            $receipt->save();
            $totalCredits = 0;
            foreach ($request->items as $itemData) {
                $journalItem = JournalItem::findOrFail($itemData['journal_item_id']);
                $challanHead = ChallanHead::findOrFail($itemData['challan_head_id']);
                $maxCredit = $challanHead->price - $challanHead->paid + $journalItem->credit;
                if ($itemData['credit'] > $maxCredit) {
                    throw new \Exception("Credit for head {$challanHead->feeHead->fee_head} exceeds remaining balance.");
                }
                $journalItem->credit = $itemData['credit'];
                $journalItem->save();
                $challanHead->paid = ($challanHead->paid + $itemData['credit']) - $itemData['voucher_old'] ;
                $challanHead->save();
                $totalCredits += $itemData['credit'];
            }
            $journalId = $journalItem->journal;
            $debitLine = JournalItem::where('journal', $journalId)
                ->where('debit', '!=', 0)
                ->first();
            if (! $debitLine) {
                throw new \Exception("Debit line not found for journal #{$journalId}.");
            }
            $debitLine->debit = $totalCredits;
            $debitLine->save();
            DB::commit();
            return redirect()
                ->route('student_receipt.index')
                ->with('success', __('Receipt updated successfully.'));
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            return back()->withErrors($e->getMessage());
        }
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
