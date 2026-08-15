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
use App\Models\JournalItem;
use App\Models\Utility;
use App\Exports\LedgerExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            $assetsType = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Assets')->first();
            $chart_accounts = collect();
            if ($assetsType) {
                $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                    ->where('type', $assetsType->id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('code_name', 'id');
            }
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
                    'chart_account_id' => 'required|unique:bank_accounts,chart_account_id,NULL,id,created_by,' . \Auth::user()->creatorId(),
                    'type' => 'required|in:cash,bank,head_imprest',
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
            $account->type = $request->type;
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
                $assetsType = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Assets')->first();
                $chart_accounts = collect();
                if ($assetsType) {
                    $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                        ->where('type', $assetsType->id)
                        ->where('created_by', \Auth::user()->creatorId())
                        ->get()
                        ->pluck('code_name', 'id');
                }
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
                    'chart_account_id' => 'required|unique:bank_accounts,chart_account_id,' . $bankAccount->id . ',id,created_by,' . \Auth::user()->creatorId(),
                    'type' => 'required|in:cash,bank,head_imprest',
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
            $bankAccount->type = $request->type;
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


    public function statement(Request $request, $id)
    {
        if (\Auth::user()->can('manage bank account') || \Auth::user()->can('create bank account')) {
            $bankAccount = BankAccount::find($id);

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('All Branches', '');
            } else {
                $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
                $branches->prepend('All Branches', '');
            }

            $start = $request->start_date ?? date('Y-m-01');
            $end = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            $bankChartAccountId = (int) ($bankAccount->chart_account_id ?? 0);

            $oldBalance = JournalItem::where(function ($q) use ($id, $bankChartAccountId) {
                    $q->where('bank_id', $id);

                    if ($bankChartAccountId) {
                        $q->orWhere('account', $bankChartAccountId);
                    }
                })
                ->where(function ($q) {
                    $q->where('head', '0')
                        ->orWhereNull('head');
                })
                ->whereHas('journalEntery', function ($q) use ($start, $request) {
                    $q->where('date', '<', $start);

                    if ($request->branch) {
                        $q->where(function ($branchQuery) use ($request) {
                            $branchQuery->where('branch_id', $request->branch)
                                ->orWhere('owned_by', $request->branch);
                        });
                    }
                })
                ->selectRaw('SUM(debit) as totalDebit, SUM(credit) as totalCredit')
                ->first();

            $openingBalance = ($oldBalance->totalDebit ?? 0) - ($oldBalance->totalCredit ?? 0);

           $journalItems = JournalItem::with(['journalEntery', 'accounts'])
            ->where(function ($q) use ($id, $bankChartAccountId) {
                $q->where('bank_id', $id);

                if ($bankChartAccountId) {
                    $q->orWhere('account', $bankChartAccountId);
                }
            })
            ->where(function ($q) {
                $q->where('head', '0')
                    ->orWhereNull('head');
            })
            ->whereHas('journalEntery', function($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            });

         if ($request->branch) {
                $journalItems->whereHas('journalEntery', function($q) use ($request) {
                    $q->where(function ($branchQuery) use ($request) {
                        $branchQuery->where('branch_id', $request->branch)
                            ->orWhere('owned_by', $request->branch);
                    });
                });
            }

            $journalItems = $journalItems->orderBy('created_at', 'asc')->get();

            $rows = collect();
            $balance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;

            $rows->push([
                'journal' => '-',
                'date' => $start,
                'account' => '-',
                'memo' => 'Opening Balance',
                'detail' => '-',
                'voucher' => '-',
                'route' => Utility::VoucherRoute('JV'),
                'debit' => '-',
                'credit' => '-',
                'balance' => $openingBalance,
            ]);

            foreach ($journalItems as $item) {
                $journalEntry = $item->journalEntery;
                if (!$journalEntry) continue;

                $debit = $item->debit ?? 0;
                $credit = $item->credit ?? 0;
                $balance += ($debit - $credit);
                $totalDebit += $debit;
                $totalCredit += $credit;

                $rows->push([
                    'journal' => $journalEntry->id,
                    'date' => $journalEntry->date,
                    'account' => $item->accounts ? $item->accounts->name : '-',
                    'memo' => $item->description ?? '-',
                    'detail' => $journalEntry->reference,
                    'voucher' => Utility::formatVoucherNumber($journalEntry->journal_id, $journalEntry->voucher_type),
                    'route' => Utility::VoucherRoute($journalEntry->voucher_type),
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ]);
            }
            $rows->push([
                'journal' => '-',
                'date' => '',
                'account' => '-',
                'memo' => 'Closing Balance',
                'detail' => '-',
                'voucher' => '-',
                'route' => Utility::VoucherRoute('JV'),
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balance' => $balance,
            ]);

            if ($request->has('export') && $request->export == '1') {
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BankLedgerExport($rows, $filter['startDateRange'], $filter['endDateRange'], $branches[$request->branch]), 'bank_ledger.xlsx');
            }

            

            return view('bankAccount.statement', compact('bankAccount', 'rows', 'filter', 'branches', 'totalDebit', 'totalCredit', 'balance'));
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
                $journalItem = JournalItem::where('bank_id', $bankAccount->id)->first();
                $studentrecipt = StudentReceipt::where('bank_id', $bankAccount->id)->first();
                $billPayment = BillPayment::first();

                if (!empty($revenue) && !empty($invoicePayment) && !empty($transaction) && !empty($payment) && !empty($journalItem) && !empty($studentrecipt) && !empty($billPayment)) {
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
