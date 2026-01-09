<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{

    public function index(Request $request)
    {

        if(\Auth::user()->can('manage transaction'))
        {
            $filter['account']  = __('All');
            $filter['category'] = __('All');
            if(\Auth::user()->type == 'company' )
            {
                $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            }
            else
            {
                $account = BankAccount::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('holder_name', 'id');
            }
            $account->prepend(__('Stripe / Paypal'), 'strip-paypal');
            $account->prepend('Select Account', '');

            $accounts = Transaction::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')
                                    ->leftjoin('bank_accounts', 'transactions.account', '=', 'bank_accounts.id')
                                    ->groupBy('transactions.account')->selectRaw('sum(amount) as total');

                                    if(\Auth::user()->type == 'company' )
                                    {
                                        $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->whereIn(
                                            'type', [
                                                        1,
                                                        2,
                                                    ]
                                            )->get()->pluck('name', 'name');
                                    }
                                    else
                                    {
                                        $category = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->whereIn(
                                            'type', [
                                                        1,
                                                        2,
                                                    ]
                                            )->get()->pluck('name', 'name');
                                    }

            $category->prepend('Invoice', 'Invoice');
            $category->prepend('Bill', 'Bill');
            $category->prepend('Select Category', '');

            $transactions = Transaction::orderBy('id', 'desc');

            if(!empty($request->start_month) && !empty($request->end_month))
            {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            }
            else
            {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            }
            
            $currentdate = $start;
            

            while($currentdate <= $end)
            {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);
                if (\Auth::user()->type == 'company') {
                    $transactions->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('transactions.created_by', '=', \Auth::user()->creatorId());
                        }
                    );

                    $accounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('transactions.created_by', '=', \Auth::user()->creatorId());

                        }
                    );
                }else{
                    $transactions->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('transactions.owned_by', '=', \Auth::user()->ownedId());
                        }
                    );

                    $accounts->Orwhere(
                        function ($query) use ($data){
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('transactions.owned_by', '=', \Auth::user()->ownedId());

                        }
                    );
                }

                $currentdate = strtotime('+1 month', $currentdate);
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if(!empty($request->account))
            {
                $transactions->where('account', $request->account);

                if($request->account == 'strip-paypal')
                {
                    $accounts->where('account', 0);
                    $filter['account'] = __('Stripe / Paypal');
                }
                else
                {
                    $accounts->where('account', $request->account);
                    $bankAccount       = BankAccount::find($request->account);
                    $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                    if($bankAccount->holder_name == 'Cash')
                    {
                        $filter['account'] = 'Cash';
                    }
                }

            }
            if(!empty($request->category))
            {
                $transactions->where('category', $request->category);
                $accounts->where('category', $request->category);

                $filter['category'] = $request->category;
            }

            if(\Auth::user()->type == 'company' )
            {
                $transactions->where('created_by', '=', \Auth::user()->creatorId());
                $accounts->where('transactions.created_by', '=', \Auth::user()->creatorId());
            }
            else
                $transactions->where('owned_by', '=', \Auth::user()->ownedId());
                $accounts->where('transactions.owned_by', '=', \Auth::user()->ownedId());
            {
            }
            if (!empty($request->branch)) {
                $transactions->where('owned_by', '=', $request->branch);
            }
            $transactions = $transactions->get();
            $accounts     = $accounts->get();

            return view('transaction.index', compact('transactions', 'account', 'category', 'filter', 'accounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //for export in transaction report
    public function export()
    {
        $name = 'transaction_' . date('Y-m-d i:h:s');
        $data = Excel::download(new TransactionExport(), $name . '.xlsx');

        return $data;
    }


}
