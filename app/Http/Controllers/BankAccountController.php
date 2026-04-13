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


    public function statement(Request $request, $id)
    {
        if (\Auth::user()->can('manage bank account') || \Auth::user()->can('create bank account')) {
            $bankAccount = BankAccount::find($id);

            $start = $request->start_date ?? date('Y-m-01');
            $end = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            $oldBalance = JournalItem::where('bank_id', $id)
                ->where('head', '0')
                ->whereHas('journalEntery', function ($q) use ($start, $request) {
                    $q->where('date', '<', $start);

                    if ($request->branch) {
                        $q->where('branch_id', $request->branch);
                    }
                })
                ->selectRaw('SUM(debit) as totalDebit, SUM(credit) as totalCredit')
                ->first();

            $openingBalance = ($oldBalance->totalDebit ?? 0) - ($oldBalance->totalCredit ?? 0);

           $journalItems = JournalItem::with(['journalEntery', 'accounts'])->where('bank_id', $id)->where('head','0')
            ->whereHas('journalEntery', function($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            });

         if ($request->branch) {
                $journalItems->whereHas('journalEntery', function($q) use ($request) {
                    $q->where('branch_id', $request->branch);
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
                'balance' => number_format($openingBalance, 2),
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
                    'debit' => number_format($debit, 2),
                    'credit' => number_format($credit, 2),
                    'balance' => number_format($balance, 2),
                ]);
            }

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('All Branches', '');
            } else {
                $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
                $branches->prepend('All Branches', '');
            }

            return view('bankAccount.statement', compact('bankAccount', 'rows', 'filter', 'branches', 'totalDebit', 'totalCredit', 'balance'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function statementExport(Request $request, $id)
    {
        $bankAccount = BankAccount::find($id);

        $start = $request->start_date ?? date('Y-m-01');
        $end = $request->end_date ?? date('Y-m-d', strtotime('+1 day'));

             $journalItems = JournalItem::with(['journalEntery', 'accounts'])->where('bank_id', $id)
            ->whereHas('journalEntery', function($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end]);
            });


        if ($request->branch) {
            $journalItems->whereHas('journalEntery', function($q) use ($request) {
                $q->where('branch_id', $request->branch);
            });
        }

        $journalItems = $journalItems->orderBy('created_at', 'asc')->get();

        $headings = ['#', 'Date', 'Voucher', 'Account Name', 'Memo', 'Reference', 'Debit', 'Credit', 'Balance'];

        $data = [];
        $i = 1;
        $balance = 0;

        foreach ($journalItems as $item) {
            $journalEntry = $item->journalEntery;
            if (!$journalEntry) continue;

            $debit = $item->debit ?? 0;
            $credit = $item->credit ?? 0;
            $balance += ($debit - $credit);

            $data[] = [
                $i++,
                date('d-M-Y', strtotime($journalEntry->date)),
                Utility::formatVoucherNumber($journalEntry->journal_id, $journalEntry->voucher_type),
                $item->accounts ? $item->accounts->name : '-',
                $item->description ?? '-',
                $journalEntry->reference ?? '-',
                $debit,
                $credit,
                $balance,
            ];
        }

        $name = 'Bank_Statement_' . ($bankAccount->holder_name ?? '') . '_' . now()->format('Y_m_d_H_i_s');
        $export = Excel::download(new LedgerExport($data, $headings), $name . '.xlsx');

        ob_end_clean();

        return $export;
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
