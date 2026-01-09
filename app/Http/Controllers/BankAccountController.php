<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $accountsQuery = BankAccount::query();

        if ($search !== "") {
            $accountsQuery->where(function ($query) use ($search) {
                $columns = (new BankAccount())->getFillable();
                        foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }
        if (\Auth::user()->can('create bank account')) {
            if (\Auth::user()->type == 'company') {
                $accountsQuery->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $accountsQuery->where('owned_by', '=', \Auth::user()->ownedId());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        $accounts = $accountsQuery->get();
        return view('bankAccount.index', compact('accounts','search'));
    }



    public function create()
    {
        if (\Auth::user()->can('create bank account')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'account')->get();
            $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chart_accounts->prepend('Select Account', '');
            if(\Auth::user()->type == 'company' )
            {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                // $branches->prepend('Select Branch', '');
            }
            else
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                // $branches->prepend('Select Branch', '');
            }

            return view('bankAccount.create', compact('customFields', 'chart_accounts','branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create bank account')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'holder_name' => 'required',
                    'bank_name' => 'required',
                    'account_number' => 'required',
                    'opening_balance' => 'required',
                    'contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('bank-account.index')->with('error', $messages->first());
            }

            $account = new BankAccount();
            $account->chart_account_id = $request->chart_account_id;
            $account->holder_name = $request->holder_name;
            $account->bank_name = $request->bank_name;
            $account->account_number = $request->account_number;
            $account->opening_balance = $request->opening_balance;
            $account->contact_number = $request->contact_number;
            $account->bank_address = $request->bank_address;
            $account->owned_by = $request->branches;
            $account->created_by = \Auth::user()->creatorId();
            $account->save();
            CustomField::saveData($account, $request->customField);

            return redirect()->route('bank-account.index')->with('success', __('Account successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show()
    {
        return redirect()->route('bank-account.index');
    }


    public function edit(BankAccount $bankAccount)
    {
        if (\Auth::user()->can('edit bank account')) {
            if ($bankAccount->created_by == \Auth::user()->creatorId()) {
                $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->get()
                    ->pluck('code_name', 'id');
                $chart_accounts->prepend('Select Account', '');

                $bankAccount->customField = CustomField::getData($bankAccount, 'account');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'account')->get();

                if(\Auth::user()->type == 'company' )
            {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                // $branches->prepend('Select Branch', '');
            }
            else
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                // $branches->prepend('Select Branch', '');
            }

                return view('bankAccount.edit', compact('bankAccount', 'customFields', 'chart_accounts','branches'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, BankAccount $bankAccount)
    {
        if (\Auth::user()->can('create bank account')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'holder_name' => 'required',
                    'bank_name' => 'required',
                    'account_number' => 'required',
                    'opening_balance' => 'required',
                    'contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('bank-account.index')->with('error', $messages->first());
            }
            $bankAccount->chart_account_id = $request->chart_account_id;
            $bankAccount->holder_name = $request->holder_name;
            $bankAccount->bank_name = $request->bank_name;
            $bankAccount->account_number = $request->account_number;
            $bankAccount->opening_balance = $request->opening_balance;
            $bankAccount->contact_number = $request->contact_number;
            $bankAccount->bank_address = $request->bank_address;
            $bankAccount->owned_by = $request->branches;
            $bankAccount->created_by = \Auth::user()->creatorId();
            $bankAccount->save();
            CustomField::saveData($bankAccount, $request->customField);

            return redirect()->route('bank-account.index')->with('success', __('Account successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(BankAccount $bankAccount)
    {
        if (\Auth::user()->can('delete bank account')) {
            if ($bankAccount->created_by == \Auth::user()->creatorId()) {
                $revenue = Revenue::where('account_id', $bankAccount->id)->first();
                $invoicePayment = InvoicePayment::where('account_id', $bankAccount->id)->first();
                $transaction = Transaction::where('account', $bankAccount->id)->first();
                $payment = Payment::where('account_id', $bankAccount->id)->first();
                $billPayment = BillPayment::first();

                if (!empty($revenue) && !empty($invoicePayment) && !empty($transaction) && !empty($payment) && !empty($billPayment)) {
                    return redirect()->route('bank-account.index')->with('error', __('Please delete related record of this account.'));
                } else {
                    $bankAccount->delete();

                    return redirect()->route('bank-account.index')->with('success', __('Account successfully deleted.'));
                }

            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
