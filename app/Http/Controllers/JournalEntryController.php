<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage journal entry'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->where('voucher_type','JV');
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId())->where('voucher_type','JV');
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $journalEntries = $query->orderBy('id', 'desc')->paginate(25);
            // dd($journalEntries);
            return view('journalEntry.index', compact('journalEntries','branches'));
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

            $journalId = $this->journalNumber();

            return view('journalEntry.create', compact('chartAccounts', 'subAccounts', 'journalId'));
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

            $journal              = new JournalEntry();
            $journal->journal_id  = $this->journalNumber();
            $journal->date        = $request->date;
            $journal->reference   = $request->reference;
            $journal->description = $request->description;
            $journal->voucher_type = 'JV';
            $journal->owned_by    = \Auth::user()->ownedId();
            $journal->created_by  = \Auth::user()->creatorId();
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
                            $new_balance = $old_balance - $journalItem->debit;
                        }
                        if ($journalItem->credit > 0) {
                            $new_balance = $old_balance + $journalItem->credit;
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
                    'message' => __('Journal entry successfully created.'),
                    'redirect' => route('journal-entry.show', $journal->id)
                ]);
            // return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully created.'));
            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => __('Something went wrong: ') . $e->getMessage()
                ], 500);
            }
        }
        else
        {
            return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
        }
    }


    public function show(JournalEntry $journalEntry)
    {
        if(\Auth::user()->can('show journal entry'))
        {
            if($journalEntry->created_by == \Auth::user()->creatorId())
            {
                $accounts = $journalEntry->accounts;
                $settings = Utility::settings();

                return view('journalEntry.view', compact('journalEntry', 'accounts', 'settings'));
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


    public function edit(JournalEntry $journalEntry)
    {
        if(\Auth::user()->can('edit journal entry'))
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
            return view('journalEntry.edit', compact('chartAccounts', 'subAccounts', 'journalEntry'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, JournalEntry $journalEntry)
    {
        if(\Auth::user()->can('edit journal entry'))
        {
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
                $journalEntry->created_by  = \Auth::user()->creatorId();
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
                                $new_balance = $old_balance - $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance + $journalItem->credit;
                            }
                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }
                }

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully updated.'));
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


    public function destroy(JournalEntry $journalEntry)
    {


        if(\Auth::user()->can('delete journal entry'))
        {
            if($journalEntry->created_by == \Auth::user()->creatorId())
            {
                $journalEntry->delete();


                JournalItem::where('journal', '=', $journalEntry->id)->delete();

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully deleted.'));
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

    function journalNumber()
    {
        $latest = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId())->where('voucher_type','JV')->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->journal_id + 1;
    }

    public function accountDestroy(Request $request)
    {

        if(\Auth::user()->can('delete journal entry'))
        {
            JournalItem::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Journal entry account successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function journalDestroy($item_id)
    {
        if(\Auth::user()->can('delete journal entry'))
        {
            $journal = JournalItem::find($item_id);
            $journal->delete();

            return redirect()->back()->with('success', __('Journal account successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function test(Request $request)
    {
         if (\Auth::user()->type == 'company') {
                $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('All Branches', 'All Branches');
            } else {
                $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
                $branches->prepend('All Branches', 'All Branches');
            }

            $user = \Auth::user();
            $creatorId = $user->creatorId();
            $start = $request->start_date ?? date('Y-m-01');
            $end = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));
            $branch = $request->branch;
           

            $isAccountFiltered = !empty($request->account);
            $type = $isAccountFiltered ? 'other' : 'group';

            // Fetch accounts once based on condition
            $chartAccountsQuery = ChartOfAccount::where('created_by', $creatorId);
            if ($isAccountFiltered) {
                $chartAccountsQuery->where('id', $request->account);
            }else{
                $a = ChartOfAccount::where('created_by', $creatorId)->where('parent', 0)->first();
                $chartAccountsQuery->where('id', $a->id);
            }
            $chart_accounts = $chartAccountsQuery->get();

            // Get parent accounts for dropdown
            $accounts = ChartOfAccount::select('id', 'code', 'name', 'parent')
                ->where('parent', 0)
                ->where('created_by', $creatorId)
                ->get();

            // Fetch sub-accounts
            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                ->where('chart_of_accounts.parent', '!=', 0)
                ->where('chart_of_accounts.created_by', $creatorId)
                ->get();
            $filter = [
                'balance' => 0,
                'credit' => 0,
                'debit' => 0,
                'startDateRange' => $start,
                'endDateRange' => $end,
            ];
        if (request()->ajax()) {

            $start = $request->start_date ?? date('Y-m-01');
            $end = $request->end_date ?? date('Y-m-d');

            $start = $request->start_date ?? date('Y-m-01');
            $end = $request->end_date ?? date('Y-m-d');
            $account = $request->account ?? 0;
            

        // 1. Opening balance (SQL)
        $openingBalances = DB::table('journal_items as ji')
            ->join('journal_entries as je', 'je.id', '=', 'ji.journal')
            ->selectRaw('ji.account, SUM(ji.debit - ji.credit) as opening_balance')
            ->where('je.date', '<', $start)
            ->when($branch != 'All Branches', function ($query) use ($branch) {
                return $query->where('je.owned_by', $branch);
            })
            ->when($account != 0, function ($query) use ($account) {
                return $query->where('ji.account', $account);
            })
            ->groupBy('ji.account')
            ->pluck('opening_balance', 'account');

            $openingBalance = $account != 0 ? ($openingBalances[$account] ?? 0) : array_sum($openingBalances);
            
        // 2. Main ledger query
        $query = DB::table('journal_items as ji')
            ->join('journal_entries as je', 'je.id', '=', 'ji.journal')
            ->join('chart_of_accounts as ca', 'ca.id', '=', 'ji.account')
            ->leftJoin('users as u', 'u.id', '=', 'je.owned_by')
            ->selectRaw("
                ji.id,
                je.date,
                ji.account,
                ca.name as accountname,
                ji.description as memo,
                ji.debit,
                ji.credit,
                ji.journal as journal_id,
                je.voucher_type,
                je.created_at
            ")
            ->when($branch != 'All Branches', function ($query) use ($branch) {
                return $query->where('je.owned_by', $branch);
            })
            ->when($account != 0, function ($query) use ($account) {
                return $query->where('ji.account', $account);
            })
            ->whereBetween('ji.created_at', [$start.' 00:00:00', $end.' 23:59:59'])->orderBy('ji.created_at', 'asc');

            return DataTables::of($query)

    ->addColumn('balance', function ($row) use ($openingBalances) {

        static $map = [];

        $acc = $row->account;

        if (!isset($map[$acc])) {
            $map[$acc] = $openingBalances[$acc] ?? 0;
        }

        
        $map[$acc] += $row->debit - $row->credit;
        return $map[$acc];
    })

    ->with([
        'openingRow' => [
            'id' => '',
            'date' => '',
            'account' => '',
            'accountname' => 'Opening Balance',
            'memo' => 'Opening Balance',
            'debit' => '',
            'credit' => '',
            'balance' => $account != 0 
                ? number_format($openingBalances[$account] ?? 0, 1)
                : number_format(array_sum($openingBalances), 1),
            'voucher_type' => '',
            'journal_id' => ''
        ]
    ])

    ->make(true);


        }

        return view('journalEntry.index',compact('branches','accounts','subAccounts','filter'));
    }
}
