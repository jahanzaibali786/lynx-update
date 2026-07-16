<?php

namespace App\Http\Controllers;

use App\Exports\chartofaccountexport;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\User;
use App\Models\Utility;
use App\Models\JournalItem;
use App\Models\ChartOfAccountParent;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ChartOfAccountController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage chart of account'))
        {
            $user = \Auth::user();
            $companyName = $user->creatorId() ? User::find($user->creatorId())->name : 'Lynx School';
            // dd($companyName);
    
            if (!empty($request->start_date) && !empty($request->end_date)){
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d', strtotime('+1 day'));
            }
            if (!empty($request->branch)){
                $branch = $request->branch;
            } else {
                $branch = '';
            }
            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            $filter['branch'] =$branch;
            $types = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $accounts = ChartOfAccount::whereIn('type', $types->pluck('id'))
            ->where('created_by', '=', \Auth::user()->creatorId())
            // ->where('parent',0)
            ->with(['subType', 'parentAccount','subAccounts','subAccounts.childaccounts'])
            ->get()
            ->groupBy('type');
            

            $chartAccounts = [];
            foreach ($types as $type) {
                $typeName = $type->name;
                $chartAccounts[$typeName] = $accounts[$type->id] ?? [];
            }
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            }
        $filter['showBalance'] = $request->input('show_balance', true);
            // Determine the export format
            $exportFormat = $request->input('export_format', 'view');
           switch ($exportFormat) {
            case 'pdf':
                $data = [
                    'chartAccounts' => $chartAccounts,
                    'filter' => $filter,
                    'companyName' => $companyName,
                    'reportName' => 'Chart of Accounts',
                    'request' => $request,
                ];
                $html = view('chartOfAccount.print', $data)->render();

                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                return $dompdf->stream('chart_of_accounts.pdf', ['Attachment' => false]);

            case 'excel':
                    return Excel::download(new chartofaccountexport($chartAccounts, $filter, $companyName), 'Chart_of_accounts.xlsx');
                

                default:
                    return view('chartOfAccount.index', compact('chartAccounts', 'types', 'filter', 'branches'));
        }

            return view('chartOfAccount.index', compact('chartAccounts', 'types' , 'filter','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $types = ChartOfAccountType::where('created_by',\Auth::user()->creatorId())->get();
        // $types->prepend('Select Account Type', 0);
        $account_type = [];

        foreach ($types as $type) {
            $accountTypes = ChartOfAccountSubType::where('type', $type->id)->where('created_by',\Auth::user()->creatorId())->get();

            $temp = [];
            foreach($accountTypes as $accountType)
            {
                $temp[$accountType->id] = $accountType->name;
            }
            $account_type[$type->name] = $temp;
        }
            $selectAcc =     [
                null => "Select",
                ];
               $account_type =  array_merge($selectAcc, $account_type);
        return view('chartOfAccount.create', compact('account_type'));
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create chart of account'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                //    'type' => 'required',
                                    'sub_type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // $account              = new ChartOfAccount();
            // $account->name        = $request->name;
            // $account->code        = $request->code;
            // $account->type        = $request->type;
            // $account->sub_type    = $request->sub_type;
            // $account->description = $request->description;
            // $account->is_enabled  = isset($request->is_enabled) ? 1 : 0;
            // $account->created_by  = \Auth::user()->ownedId();
            // $account->created_by  = \Auth::user()->creatorId();
            // $account->save();
            $type = ChartOfAccountSubType::where('id',$request->sub_type)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if($request->parent == 0){
                $account              = new ChartOfAccount();
                $account->name        = $request->name;
                $account->code        = $request->code;
                $account->type        = $type->type;
                $account->sub_type    = $request->sub_type;
                $account->parent      = 0;
                $account->description = $request->description;
                $account->is_enabled  = isset($request->is_enabled) ? 1 : 0;
                $account->created_by  = \Auth::user()->creatorId();
                $account->save();
            }else{
            $account = ChartOfAccount::where('id',$request->parent)->where('created_by', '=', \Auth::user()->creatorId())->first();
            if(!empty($account->name)){
            $existingparentAccount = ChartOfAccountParent::where('name',$account->name)->where('created_by',\Auth::user()->creatorId())->first();

            if ($existingparentAccount) {
                $parentAccount = $existingparentAccount;
                $parentAccount->name        = $account->name;
                $parentAccount->sub_type    = $request->sub_type;
                $parentAccount->type        = $type->type;
                $parentAccount->account      = $request->parent;
                $parentAccount->created_by  = \Auth::user()->creatorId();
                $parentAccount->save();
            } else {
                $parentAccount              = new ChartOfAccountParent();
                $parentAccount->name        = $account->name;
                $parentAccount->sub_type    = $request->sub_type;
                $parentAccount->type        = $type->type;
                $parentAccount->account      = $request->parent;
                $parentAccount->created_by  = \Auth::user()->creatorId();
                $parentAccount->save();
            }


            $account              = new ChartOfAccount();
            $account->name        = $request->name;
            $account->code        = $request->code;
            $account->type        = $type->type;
            $account->sub_type    = $request->sub_type;
            $account->parent      = $parentAccount->id;
            $account->description = $request->description;
            $account->is_enabled  = isset($request->is_enabled) ? 1 : 0;
            $account->created_by  = \Auth::user()->creatorId();
            $account->save();
        }
        }
            return redirect()->route('chart-of-account.index')->with('success', __('Account successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(ChartOfAccount $chartOfAccount,Request $request)
    {
        if(\Auth::user()->can('ledger report'))
        {
            if(!empty($request->start_date) && !empty($request->end_date))
            {
                $start = $request->start_date;
                $end   = $request->end_date;
            }
            else
            {
                $start = date('Y-m-01');
                $end   = date('Y-m-t');
            }
            if(!empty($request->start_date) && !empty($request->end_date))
            {
                $accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->get()->pluck('code_name', 'id');
                $accounts->prepend('Select Account', '');

            }
            else
            {
                $accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->get()
                    ->pluck('code_name', 'id');
                $accounts->prepend('Select Account', '');
            }
            if(!empty($request->account))
            {
                $account = ChartOfAccount::find($request->account);
            }
            else
            {
                $account = ChartOfAccount::find($chartOfAccount->id);
            }
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            }

            // $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')
            //     ->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')
            //     ->where('journal_entries.created_by', '=', \Auth::user()->creatorId())
            //     ->where('account', !empty($account) ? $account->id : 0);
            // $journalItems->where('date', '>=', $start);
            // $journalItems->where('date', '<=', $end);
            // $journalItems = $journalItems->get();

            $balance = 0;
            $debit   = 0;
            $credit  = 0;

            // foreach($journalItems as $item)
            // {
            //     if($item->debit > 0)
            //     {
            //         $debit += $item->debit;
            //     }

            //     else
            //     {
            //         $credit += $item->credit;
            //     }

            //     $balance = $credit - $debit;
            // }

            $filter['startDateRange'] = $start;
            $filter['endDateRange']   = $end;

            return view('chartOfAccount.show', compact('filter', 'account', 'accounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        if(\Auth::user()->can('edit chart of account'))
        {
            $types = ChartOfAccountType::where('created_by',\Auth::user()->creatorId())->get();
            $account_type = [];

            foreach ($types as $type) {
                $accountTypes = ChartOfAccountSubType::where('type', $type->id)->where('created_by',\Auth::user()->creatorId())->get();

                $temp = [];
                foreach($accountTypes as $accountType)
                {
                    $temp[$accountType->id] = $accountType->name;
                }
                $account_type[$type->name] = $temp;
            }
            $selectAcc = [
                null => "Select",
            ];
            $account_type = array_merge($selectAcc, $account_type);

            $parentAccounts = [];
            if ($chartOfAccount->parent > 0 && $chartOfAccount->parentAccount) {
                $parentAccounts = ChartOfAccount::where('sub_type', $chartOfAccount->sub_type)
                    ->where('id', '!=', $chartOfAccount->id)
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            }

            return view('chartOfAccount.edit', compact('chartOfAccount', 'account_type', 'parentAccounts'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

public function updateCategory(Request $request)
    {
        $account = ChartOfAccount::find($request->account_id);
        if ($account) {
            $account->category = $request->category;
            $account->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        if(\Auth::user()->can('edit chart of account'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'sub_type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $type = ChartOfAccountSubType::where('id',$request->sub_type)->where('created_by', '=', \Auth::user()->creatorId())->first();

            $chartOfAccount->name        = $request->name;
            $chartOfAccount->code        = $request->code;
            $chartOfAccount->description = $request->description;
            $chartOfAccount->is_enabled  = isset($request->is_enabled) ? 1 : 0;
            if ($type) {
                $chartOfAccount->type     = $type->type;
                $chartOfAccount->sub_type = $request->sub_type;
            }

            // Handle parent account change
            // If parent = 0 (no sub-account) or not checked
            if ($request->parent == 0 || !$request->has('parent') || $request->parent == null) {
                $chartOfAccount->parent = 0;
            } else {
                // It is a sub-account
                $parentAcc = ChartOfAccount::where('id',$request->parent)->where('created_by', '=', \Auth::user()->creatorId())->first();
                if(!empty($parentAcc->name)){
                    $existingparentAccount = ChartOfAccountParent::where('name',$parentAcc->name)->where('created_by',\Auth::user()->creatorId())->first();

                    if ($existingparentAccount) {
                        $parentAccount = $existingparentAccount;
                        $parentAccount->name        = $parentAcc->name;
                        $parentAccount->sub_type    = $request->sub_type;
                        if ($type) {
                            $parentAccount->type    = $type->type;
                        }
                        $parentAccount->account      = $request->parent;
                        $parentAccount->created_by  = \Auth::user()->creatorId();
                        $parentAccount->save();
                    } else {
                        $parentAccount              = new ChartOfAccountParent();
                        $parentAccount->name        = $parentAcc->name;
                        $parentAccount->sub_type    = $request->sub_type;
                        if ($type) {
                            $parentAccount->type    = $type->type;
                        }
                        $parentAccount->account      = $request->parent;
                        $parentAccount->created_by  = \Auth::user()->creatorId();
                        $parentAccount->save();
                    }

                    $chartOfAccount->parent = $parentAccount->id;
                }
            }

            $chartOfAccount->save();

            return redirect()->route('chart-of-account.index')->with('success', __('Account successfully updated.'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if(\Auth::user()->can('delete chart of account'))
        {
            $chartOfAccount->delete();

            return redirect()->route('chart-of-account.index')->with('success', __('Account successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getSubType(Request $request)
    {
        $types = ChartOfAccount::where('sub_type', $request->type)->get()->pluck('name', 'id');
        $types->prepend('Select an account', 0);

        return response()->json($types);
    }
}
