<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class JournalVoucherService
{
    public function create(Request $request): JournalEntry
    {
        return DB::transaction(function () use ($request) {
            $accounts = $request->accounts;
            $this->validateVoucherItems($accounts);

            $voucherType = $this->normalizeVoucherType($request->voucher_type);
            $ownedBy = $request->branches ?: Auth::user()->ownedId();
            $transactionDateTime = Carbon::parse($request->date)->setTimeFrom(now());

            $journal = new JournalEntry();
            $series = strtoupper($request->voucher_series ?? 'SYSTEM');
            $journal->voucher_series = $series;

            if ($series === 'MANUAL') {
                $last = JournalEntry::where('voucher_type', $voucherType)
                    ->where('voucher_series', 'MANUAL')
                    ->where('created_by', Auth::user()->creatorId())
                    ->max('manual_series_no');
                $next = $last + 1;
                $journal->manual_series_no = $next;
                $journal->manual_reference = 'M-' . $voucherType . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
                $journal->journal_id = $next;
            } else {
                $journal->journal_id = $this->voucherNumber($voucherType, $ownedBy);
            }

            $journal->date = $request->date;
            $journal->reference = $request->reference;
            $journal->description = $request->narration ?? $request->description;
            $journal->voucher_type = $voucherType;
            $journal->owned_by = $ownedBy;
            $journal->created_by = Auth::user()->creatorId();
            $this->setNullableModelValueIfColumn($journal, 'added_by', Auth::id());
            $this->setNullableModelValueIfColumn($journal, 'added_at', now());
            $this->setModelValueIfColumn($journal, 'amount', $request->amount);
            $this->setModelValueIfColumn($journal, 'category', 'manual');
            $this->setModelValueIfColumn($journal, 'status', 'Draft');
            $journal->category_type_id = $request->category_type_id;
            $this->fillJournalEntryExtraFields($journal, $request);
            $journal->created_at = $transactionDateTime;
            $journal->updated_at = $transactionDateTime;
            $journal->save();

            $this->saveVoucherItems($journal, $accounts, $ownedBy, $voucherType);

            return $journal;
        });
    }

    private function validateVoucherItems(array $accounts): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit = isset($account['debit']) ? (float) $account['debit'] : 0;
            $credit = isset($account['credit']) ? (float) $account['credit'] : 0;
            $accountId = $account['account_id'] ?? $account['account'] ?? null;

            if (empty($accountId)) {
                throw new InvalidArgumentException(__('Please select an account for every line.'), 422);
            }

            if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
                throw new InvalidArgumentException(__('Each line must have either debit or credit amount.'), 422);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (round($totalCredit, 2) != round($totalDebit, 2)) {
            throw new InvalidArgumentException(__('Debit and Credit must be Equal.'), 400);
        }
    }

    private function saveVoucherItems(JournalEntry $journal, array $accounts, $ownedBy, string $voucherType): void
    {
        $transactionDateTime = Carbon::parse($journal->date)->setTimeFrom(now());

        foreach ($accounts as $account) {
            $accountId = $account['account_id'] ?? $account['account'];
            $debit = isset($account['debit']) ? (float) $account['debit'] : 0;
            $credit = isset($account['credit']) ? (float) $account['credit'] : 0;
            $bankAccounts = BankAccount::where('chart_account_id', '=', $accountId)->first();
            $journalItem = new JournalItem();
            $journalItem->journal = $journal->id;
            $journalItem->account = $accountId;
            $journalItem->description = trim($account['description'] ?? '');
            $this->setModelValueIfColumn($journalItem, 'memo', trim($account['memo'] ?? ''));
            $journalItem->debit = $debit;
            $journalItem->credit = $credit;
            $journalItem->types = $account['types'] ?? $voucherType;
            $journalItem->branch_id = $account['branch_id'] ?? $ownedBy;
            $journalItem->bank_id = $bankAccounts ? $bankAccounts->id : null;
            $journalItem->ref_no = $account['ref_no'] ?? null;
            $journalItem->tra_date = $account['tra_date'] ?? null;
            $journalItem->user_type = $account['user_type'] ?? null;
            $journalItem->user_id = $account['user_id'] ?? null;
            $this->setNullableModelValueIfColumn($journalItem, 'added_by', Auth::id());
            $this->setNullableModelValueIfColumn($journalItem, 'added_at', now());
            $this->setNullableModelValueIfColumn($journalItem, 'updated_by', Auth::id());
            $this->setModelValueIfColumn($journalItem, 'category', 'manual');
            $this->setModelValueIfColumn($journalItem, 'department_id', $account['dept_id'] ?? null);
            $this->setModelValueIfColumn($journalItem, 'designation_id', $account['designation_id'] ?? null);
            $journalItem->created_at = $transactionDateTime;
            $journalItem->updated_at = $transactionDateTime;
            $journalItem->save();

            if ($journal->status == 'Approved') {
                $this->updateBankAccountBalance($accountId, $journalItem->debit, $journalItem->credit);
            }
        }
        // also update the bank_id in journal entry 
        if ($bankAccounts) {
            $journal->bank_id = $bankAccounts->id;
            $journal->save();
        }
    }

    private function updateBankAccountBalance($accountId, $debit, $credit): void
    {
        $bankAccounts = BankAccount::where('chart_account_id', '=', $accountId)->get();
        foreach ($bankAccounts as $bankAccount) {
            $oldBalance = $bankAccount->opening_balance;
            $newBalance = null;

            if ($debit > 0) {
                $newBalance = $oldBalance + $debit;
            }

            if ($credit > 0) {
                $newBalance = $oldBalance - $credit;
            }

            if (isset($newBalance)) {
                $bankAccount->opening_balance = $newBalance;
                $bankAccount->save();
            }
        }
    }

    private function voucherNumber($voucherType = 'JV', $ownedBy = null): int
    {
        $latest = JournalEntry::where('owned_by', '=', $ownedBy ?: Auth::user()->ownedId())
            ->where('voucher_type', $this->normalizeVoucherType($voucherType))
            ->latest()
            ->first();

        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function normalizeVoucherType($voucherType): string
    {
        $type = strtoupper($voucherType ?: 'JV');
        $allowedTypes = ['JV', 'CPV', 'BPV', 'CRV', 'BRV'];

        return in_array($type, $allowedTypes, true) ? $type : 'JV';
    }

    private function fillJournalEntryExtraFields(JournalEntry $journal, Request $request): void
    {
        $paymentMode = $request->payment_mode ?? $request->mode;
        $status = $request->status ?: ($journal->status ?: 'Draft');

        if ($request->has('bank_id')) {
            $this->setNullableModelValueIfColumn($journal, 'bank_id', $request->bank_id);
        }

        $this->setNullableModelValueIfColumn($journal, 'payment_mode', $paymentMode);
        $this->setNullableModelValueIfColumn($journal, 'mode', $paymentMode);
        $this->setNullableModelValueIfColumn($journal, 'cheque_no', $request->cheque_no);
        $this->setNullableModelValueIfColumn($journal, 'cheque_date', $request->cheque_date);
        $partyType = $request->user_type === 'Vendor' ? 'Vender' : $request->user_type;
        $this->setNullableModelValueIfColumn($journal, 'user_type', $partyType);
        $this->setNullableModelValueIfColumn($journal, 'user_id', $request->user_id);
        $this->setNullableModelValueIfColumn($journal, 'transaction_no', $request->transaction_no);
        $this->setNullableModelValueIfColumn($journal, 'status', $status);
        $this->setNullableModelValueIfColumn($journal, 'reversed_entry_id', $request->reversed_entry_id);
        $this->setNullableModelValueIfColumn($journal, 'payee_account_title', $request->payee_account_title);
        $this->setNullableModelValueIfColumn($journal, 'payee_account_no', $request->payee_account_no);
        $this->setNullableModelValueIfColumn($journal, 'payee_contact', $request->payee_contact);
        $this->setNullableModelValueIfColumn($journal, 'payee_email', $request->payee_email);
        $this->setNullableModelValueIfColumn($journal, 'payee_cnic', $request->payee_cnic);
        $this->setNullableModelValueIfColumn($journal, 'receiver_name', $request->receiver_name);
        $this->setNullableModelValueIfColumn($journal, 'receiver_cnic', $request->receiver_cnic);
        $this->setNullableModelValueIfColumn($journal, 'receiver_contact', $request->receiver_contact);
        $this->setNullableModelValueIfColumn($journal, 'receiver_email', $request->receiver_email);
        $this->setNullableModelValueIfColumn($journal, 'payment_date', $request->payment_date);

        $reversedTimestamp = $request->reversed_timestamp;
        if ($status === 'Reversed' && !$reversedTimestamp && empty($journal->reversed_timestamp)) {
            $reversedTimestamp = now();
        }
        $this->setNullableModelValueIfColumn($journal, 'reversed_timestamp', $reversedTimestamp);

        if ($request->has('is_system_generated')) {
            $this->setNullableModelValueIfColumn($journal, 'is_system_generated', $request->boolean('is_system_generated'));
        }

        if (in_array($status, ['Approved', 'Posted'], true) && empty($journal->approved_by)) {
            $this->setNullableModelValueIfColumn($journal, 'approved_by', Auth::id());
            $this->setNullableModelValueIfColumn($journal, 'approved_at', now());
        }

        $this->setNullableModelValueIfColumn($journal, 'attachment', $this->storeJournalAttachment($request, $journal->attachment ?? null));
    }

    private function storeJournalAttachment(Request $request, $oldPath = null): ?string
    {
        if (!$request->hasFile('attachment')) {
            return $oldPath;
        }

        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        $file = $request->file('attachment');
        $dir = 'uploads/journal_attachments';
        if (!is_dir(public_path($dir))) {
            mkdir(public_path($dir), 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $fileName = time() . '_' . uniqid() . '_' . $safeName;
        $file->move(public_path($dir), $fileName);

        return $dir . '/' . $fileName;
    }

    private function setModelValueIfColumn($model, string $column, $value): void
    {
        if ($value !== null && Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }

    private function setNullableModelValueIfColumn($model, string $column, $value): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value === '' ? null : $value;
        }
    }
}
