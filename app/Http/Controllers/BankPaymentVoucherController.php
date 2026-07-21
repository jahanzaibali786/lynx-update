<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;

class BankPaymentVoucherController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage journal entry'))
        {
            $startDate = $request->start_date ?: now()->subDays(30)->toDateString();
            $endDate = $request->end_date ?: now()->toDateString();
            $voucherSeriesFilter = $request->filled('voucher_series') ? strtoupper($request->voucher_series) : 'MANUAL';

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->where('voucher_type','BPV');
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId())->where('voucher_type','BPV');
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $query->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate);

            if (!empty($voucherSeriesFilter)) {
                if ($voucherSeriesFilter === 'SYSTEM') {
                    $query->where(function ($seriesQuery) {
                        $seriesQuery->where('voucher_series', 'SYSTEM')
                            ->orWhereNull('voucher_series')
                            ->orWhere('voucher_series', '');
                    });
                } elseif ($voucherSeriesFilter === 'MANUAL') {
                    $query->where('voucher_series', 'MANUAL');
                }
            }

            $journalEntries = $query->orderBy('id', 'desc')->paginate(25);
            
            return view('bank-payment-voucher.index', compact('journalEntries','branches', 'startDate', 'endDate', 'voucherSeriesFilter'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create journal entry'))
        {
            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code,  chart_of_accounts.parent'))
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()
            ->toarray();

            $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();
            $journalId = $this->BPVNumber();

            return view('bank-payment-voucher.create', compact('chartAccounts', 'subAccounts', 'journalId'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {

        if(\Auth::user()->can('create invoice'))
        {
            \DB::beginTransaction();
            try {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'accounts' => 'required',
                               ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->getMessageBag()->first()
                ], 422);
            }

            $accounts = $request->accounts;

            $totalDebit  = 0;
            $totalCredit = 0;
            for($i = 0; $i < count($accounts); $i++)
            {
                $debit       = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $credit      = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $totalDebit  += $debit;
                $totalCredit += $credit;
            }

            if($totalCredit != $totalDebit)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Debit and Credit must be Equal.')
                ], 400);
            }

            $journal               = new JournalEntry();
            $journal->journal_id   = $this->BPVNumber();
            $journal->date         = $request->date;
            $journal->reference    = $request->reference;
            $journal->description  = $request->description;
            $journal->voucher_type = 'BPV';
            $journal->owned_by     = \Auth::user()->ownedId();
            $journal->created_by   = \Auth::user()->creatorId();
            $journal->save();


            for($i = 0; $i < count($accounts); $i++)

            {
                $journalItem              = new JournalItem();
                $journalItem->journal     = $journal->id;
                $journalItem->account     = $accounts[$i]['account'];
                $journalItem->description = $accounts[$i]['description'];
                $journalItem->debit       = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $journalItem->credit      = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $journalItem->save();

                $bankAccounts = BankAccount::where('chart_account_id','=',$accounts[$i]['account'])->get();
                if(!empty($bankAccounts))
                {
                    foreach ($bankAccounts as $bankAccount)
                    {
                        $old_balance = $bankAccount->opening_balance;
                        if ($journalItem->debit > 0) {
                            $new_balance = $old_balance + $journalItem->debit;
                        }
                        if ($journalItem->credit > 0) {
                            $new_balance = $old_balance - $journalItem->credit;
                        }
                        if (isset($new_balance)) {
                            $bankAccount->opening_balance = $new_balance;
                            $bankAccount->save();
                        }
                    }
                }

            }

            \DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => __('Bank Payment Voucher successfully created.'),
                'redirect' => route('bank-payment-voucher.show', $journal->id)
            ]);
            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => __('Something went wrong: ') . $e->getMessage()
                ], 500);
            }
        }
    }


    public function show($journalEntry)
    {
        if(\Auth::user()->can('show journal entry'))
        {
            $journalEntry = JournalEntry::where('id', $journalEntry)->first();
            if($journalEntry->created_by == \Auth::user()->creatorId())
            {
                $accounts = $journalEntry->accounts;
                $settings = Utility::settings();

                return view('bank-payment-voucher.view', compact('journalEntry', 'accounts', 'settings'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($journalEntry)
    {
        if(\Auth::user()->can('edit journal entry'))
        {
            $journalEntry = JournalEntry::where('id', $journalEntry)->first();
             $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code,  chart_of_accounts.parent'))
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()
            ->toarray();

            $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();
            return view('bank-payment-voucher.edit', compact('chartAccounts', 'subAccounts', 'journalEntry'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, $journalEntry)
    {
        if(\Auth::user()->can('edit journal entry'))
        {
            $journalEntry = JournalEntry::where('id', $journalEntry)->first();
            if($journalEntry->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'date' => 'required',
                                       'accounts' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $accounts = $request->accounts;

                $totalDebit  = 0;
                $totalCredit = 0;
                for($i = 0; $i < count($accounts); $i++)
                {
                    $debit       = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $credit      = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $totalDebit  += $debit;
                    $totalCredit += $credit;
                }

                if($totalCredit != $totalDebit)
                {
                    return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
                }

                $journalEntry->date        = $request->date;
                $journalEntry->reference   = $request->reference;
                $journalEntry->description = $request->description;
                // $journalEntry->created_by  = \Auth::user()->creatorId();
                $journalEntry->save();

                for($i = 0; $i < count($accounts); $i++)
                {
                    $journalItem = JournalItem::find($accounts[$i]['id']);

                    if($journalItem == null)
                    {
                        $journalItem          = new JournalItem();
                        $journalItem->journal = $journalEntry->id;
                    }

                    if(isset($accounts[$i]['account']))
                    {
                        $journalItem->account = $accounts[$i]['account'];
                    }

                    $journalItem->description = $accounts[$i]['description'];
                    $journalItem->debit  = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $journalItem->credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $journalItem->save();


                    $bankAccounts = BankAccount::where('chart_account_id','=',$accounts[$i]['account'])->get();
                    if(!empty($bankAccounts))
                    {
                        foreach ($bankAccounts as $bankAccount)
                        {
                            $old_balance = $bankAccount->opening_balance;
                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance + $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance - $journalItem->credit;
                            }
                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }
                }

                return redirect()->route('bank-payment-voucher.index')->with('success', __('Bank Payment Voucher successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($journalEntry)
    {

        if(\Auth::user()->can('delete journal entry'))
        {
            $journalEntry = JournalEntry::where('id', $journalEntry)->first();
            if($journalEntry->created_by == \Auth::user()->creatorId())
            {
                $journalEntry->delete();


                JournalItem::where('journal', '=', $journalEntry->id)->delete();

                return redirect()->route('bank-payment-voucher.index')->with('success', __('Bank Payment Voucher successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function BPVNumber()
    {
        $latest = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId())->where('voucher_type','BPV')->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->journal_id + 1;
    }

    public function bpvaccountDestroy(Request $request)
    {

        if(\Auth::user()->can('delete journal entry'))
        {
            JournalItem::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Bank Payment Voucher account successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bpvjournalDestroy($item_id)
    {
        if(\Auth::user()->can('delete journal entry'))
        {
            $journal = JournalItem::find($item_id);
            $journal->delete();

            return redirect()->back()->with('success', __('Bank Payment Voucher account successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
