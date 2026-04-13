<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Utility;
use DB;
use Illuminate\Http\Request;

class BankTransferController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage bank transfer')) {

            $user = \Auth::user();

            if ($user->type == 'company') {

                $account = BankAccount::select(
                    '*',
                    \DB::raw("
                    CASE 
                        WHEN CHAR_LENGTH(COALESCE(account_number,'')) <= 7 
                             AND COALESCE(account_number,'') != ''
                        THEN CONCAT(COALESCE(account_number,''),' - ',COALESCE(holder_name,''),' ',COALESCE(bank_name,''))
                        ELSE CONCAT(COALESCE(bank_name,''),' ',COALESCE(holder_name,''))
                    END AS name
                ")
                )
                    ->where('created_by', $user->creatorId())
                    ->pluck('name', 'id')
                    ->toArray();

                $query = BankTransfer::where('created_by', $user->creatorId());

            } else {

                $account = BankAccount::select(
                    '*',
                    \DB::raw("
                    CASE 
                        WHEN CHAR_LENGTH(COALESCE(account_number,'')) <= 7 
                             AND COALESCE(account_number,'') != ''
                        THEN CONCAT(COALESCE(account_number,''),' - ',COALESCE(holder_name,''),' ',COALESCE(bank_name,''))
                        ELSE CONCAT(COALESCE(bank_name,''),' ',COALESCE(holder_name,''))
                    END AS name
                ")
                )
                    ->where('owned_by', $user->ownedId())
                    ->pluck('name', 'id')
                    ->toArray();

                $query = BankTransfer::where('owned_by', $user->ownedId());
            }

            // ─────────────────────────────
            // FILTERS
            // ─────────────────────────────
            if (count(explode('to', $request->date)) > 1) {
                $date_range = explode(' to ', $request->date);
                $query->whereBetween('date', $date_range);
            } elseif (!empty($request->date)) {
                $query->whereBetween('date', [$request->date, $request->date]);
            }

            if (!empty($request->f_account)) {
                $query->where('from_account', $request->f_account);
            }

            if (!empty($request->t_account)) {
                $query->where('to_account', $request->t_account);
            }

            $account = ['' => 'Select Account'] + $account;

            // ─────────────────────────────
            // GET TRANSFERS
            // ─────────────────────────────
            $transfers = $query
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // ─────────────────────────────
            // GET LATEST IDS PER FROM ACCOUNT
            // ─────────────────────────────
            // $latestIdsArray = BankTransfer::select(\DB::raw('MAX(id) as id'))
            //     ->groupBy('from_account')
            //     ->pluck('id')
            //     ->toArray();
            $latestIdsArray = BankTransfer::select('id')
                ->whereIn('id', function ($query) {
                    $query->select(\DB::raw('MAX(id)'))
                        ->from('bank_transfers as t2')
                        ->groupBy('from_account');
                })
                ->pluck('id')
                ->toArray();

            return view('bank-transfer.index', compact('transfers', 'account', 'latestIdsArray'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create bank transfer')) {

            if (\Auth::user()->type == 'company') {

                $bankAccounts = BankAccount::select(
                    'id',
                    'opening_balance',
                    \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name")
                )
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();

            } else {

                $bankAccounts = BankAccount::select(
                    'id',
                    'opening_balance',
                    \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name")
                )
                    ->where('owned_by', \Auth::user()->ownedId())
                    ->get();

            }

            return view('bank-transfer.create', compact('bankAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if (\Auth::user()->can('create bank transfer')) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'from_account' => 'required|numeric',
                        'to_account' => 'required|numeric',
                        'amount' => 'required|numeric',
                        'date' => 'required',
                        'description' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $bankAccounts = BankAccount::select('chart_account_id', 'bank_name', 'owned_by')->where('id', $request->from_account)->first();
                $to_account = BankAccount::select('chart_account_id', 'bank_name', 'owned_by')->where('id', $request->to_account)->first();
                // dd($bankAccounts, $to_account);
                $transfer = new BankTransfer();
                $transfer->from_account = $request->from_account;
                $transfer->to_account = $request->to_account;
                $transfer->amount = $request->amount;
                $transfer->previous_balance = $request->prev_balance;
                $transfer->date = $request->date;
                $transfer->payment_method = 0;
                $transfer->reference = $request->reference;
                $transfer->description = $request->description;
                $transfer->owned_by = $bankAccounts->owned_by;
                $transfer->created_by = \Auth::user()->creatorId();
                $transfer->save();


                Utility::bankAccountBalance($request->from_account, $request->amount, 'debit');

                Utility::bankAccountBalance($request->to_account, $request->amount, 'credit');

                $data = [
                    'id' => $transfer->id,
                    'date' => $request->date,
                    'description' => $request->description,
                    'reference' => $request->reference,
                    'amount' => $request->amount,
                    'from_bank_name' => $bankAccounts->bank_name,
                    'to_bank_name' => $to_account->bank_name,
                    'from_bank_id' => $bankAccounts->id,
                    'to_bank_id' => $to_account->id,
                    'from_account' => $bankAccounts->chart_account_id,
                    'to_account' => $to_account->chart_account_id,
                    'owned_by' => $bankAccounts->owned_by,
                    'created_by' => \Auth::user()->creatorId(),
                    'user_id' => \Auth::user()->id, //Branch,User,Supplier,Customer,Employee,Vendor
                    'user_type' => 'Branch', //Branch,User,Supplier,Customer,Employee,Vendor
                ];
                // dd($data,$bankAccounts,$to_account);
                //voucher_id
                $dataret = Utility::bankTransferJvEntry($data);
                $transfer->voucher_id = $dataret;
                $transfer->save();
                DB::commit();
                return redirect()->route('bank-transfer.index')->with('success', __('Amount successfully transfer.'));

            } else {

                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function show($id)
    {
        $transfer = BankTransfer::find($id);
        $fromBankAccount = BankAccount::with('chartAccount')
            ->find($transfer->from_account);

        $toBankAccount = BankAccount::with('chartAccount')
            ->find($transfer->to_account);

        // FROM ACCOUNT NAME (sender / issued by)
        $fromAccountName = $fromBankAccount
            ? $fromBankAccount->account_number . ' - ' . $fromBankAccount->bank_name
            : 'N/A';

        // TO ACCOUNT NAME (receiver / received by)
        $toAccountName = $toBankAccount
            ? $toBankAccount->account_number . ' - ' . $toBankAccount->bank_name
            : 'N/A';

        $openingBalance = (float) $transfer->previous_balance;
        $amount = (float) $transfer->amount;

        // Sender's balance DECREASES by amount (can go negative)
        $closingBalance = $openingBalance - $amount;

        $formattedDate = \Carbon\Carbon::parse($transfer->date)
            ->format('l, F j, Y');

        return view('bank-transfer.view', [

            'ref' => $transfer->reference,
            'date' => $formattedDate,

            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,   // opening - amount (signed)

            'debit_amount' => $amount,
            'credit_amount' => $amount,

            'debit_account' => $toAccountName,    // money goes TO this account
            'credit_account' => $fromAccountName,  // money comes FROM this account

            'fromBankAccount' => $fromBankAccount,
            'toBankAccount' => $toBankAccount,
            'transfer' => $transfer,
        ]);
    }
    public function edit(BankTransfer $transfer, $id)
    {
        if (\Auth::user()->can('edit bank transfer')) {
            $transfer = BankTransfer::where('id', $id)->first();
            if (\Auth::user()->type == 'company') {

                $bankAccounts = BankAccount::select(
                    'id',
                    'opening_balance',
                    \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name")
                )
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();

            } else {

                $bankAccounts = BankAccount::select(
                    'id',
                    'opening_balance',
                    \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name")
                )
                    ->where('owned_by', \Auth::user()->ownedId())
                    ->get();

            }
            return view('bank-transfer.edit', compact('bankAccounts', 'transfer'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, BankTransfer $transfer, $id)
    {
        DB::beginTransaction();
        try {
            if (\Auth::user()->can('edit bank transfer')) {

                $transfer = BankTransfer::find($id);

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'from_account' => 'required|numeric|different:to_account',
                        'to_account' => 'required|numeric',
                        'amount' => 'required|numeric',
                        'date' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                Utility::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit');
                Utility::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');

                $from_account = BankAccount::where('id', $request->from_account)->first();
                $to_account = BankAccount::where('id', $request->to_account)->first();
                if ($transfer->from_account == $request->from_account) {
                    $opening_balance = $transfer->previous_balance;
                } else {
                    $opening_balance = $from_account->opening_balance;
                }


                $transfer->from_account = $request->from_account;
                $transfer->to_account = $request->to_account;
                $transfer->previous_balance = $opening_balance;
                $transfer->amount = $request->amount;
                $transfer->date = $request->date;
                $transfer->payment_method = 0;
                $transfer->reference = $request->reference;
                $transfer->description = $request->description;
                $transfer->save();

                Utility::bankAccountBalance($request->from_account, $request->amount, 'debit');
                Utility::bankAccountBalance($request->to_account, $request->amount, 'credit');

                if ($transfer->voucher_id) {

                    $journal = JournalEntry::find($transfer->voucher_id);

                    if ($journal) {

                        JournalItem::where('journal', $journal->id)->delete();

                        $journal->delete();
                    }
                }

                $data = [
                    'id' => $transfer->id,
                    'date' => $request->date,
                    'reference' => $request->reference,
                    'description' => $request->description,
                    'amount' => $request->amount,
                    'from_bank_id' => $from_account->id,
                    'to_bank_id' => $to_account->id,
                    'from_account' => $from_account->chart_account_id,
                    'from_bank_name' => @$from_account->chartAccount->name,
                    'to_bank_name' => @$to_account->chartAccount->name,
                    'to_account' => $to_account->chart_account_id,
                    'owned_by' => $from_account->owned_by,
                    'created_by' => \Auth::user()->creatorId(),
                    'user_id' => \Auth::user()->id,
                    'user_type' => 'Branch',
                ];

                $voucherId = Utility::bankTransferJvEntry($data);

                $transfer->voucher_id = $voucherId;
                $transfer->save();

                DB::commit();
                return redirect()->route('bank-transfer.index')
                    ->with('success', __('Amount successfully transfer updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
{
    if (\Auth::user()->type != 'company') {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    DB::beginTransaction();
    try {
        $transfer = BankTransfer::find($id);
        if (!$transfer) {
            return redirect()->back()->with('error', __('Transfer not found.'));
        }

        // Reverse the balances
        Utility::bankAccountBalance($transfer->from_account, $transfer->amount, 'credit'); // return money to sender
        Utility::bankAccountBalance($transfer->to_account, $transfer->amount, 'debit');    // remove money from receiver

        // Delete associated voucher if exists
        if ($transfer->voucher_id) {
            $journal = JournalEntry::find($transfer->voucher_id);
            if ($journal) {
                JournalItem::where('journal', $journal->id)->delete();
                $journal->delete();
            }
        }

        // Delete the transfer
        $transfer->delete();

        DB::commit();
        return redirect()->route('bank-transfer.index')
            ->with('success', __('Transfer successfully deleted.'));

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}
    public function generateReference($bankAccountId)
    {
        $bank = BankAccount::with('chartAccount')->find($bankAccountId);

        $prefix = 'BNK-';

        if ($bank && $bank->chartAccount) {

            $name = strtolower($bank->chartAccount->name);

            if (str_contains($name, 'cash') || str_contains($name, 'csh')) {
                $prefix = 'CSH-';
            }
        }

        // Get last reference
        $last = BankTransfer::orderBy('id', 'desc')->first();

        if ($last) {
            $number = intval(preg_replace('/[^0-9]/', '', $last->reference)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
    public function getReference($id)
    {
        $ref = $this->generateReference($id);

        return response()->json([
            'reference' => $ref
        ]);
    }
}
