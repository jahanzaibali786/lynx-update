<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Grn;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Schema;

class GrnVoucherService
{
    public function previewLines(Grn $grn): array
    {
        return $this->buildLines($grn);
    }

    public function createForApproval(Grn $grn, array $voucherData = []): JournalEntry
    {
        $voucherType = $this->normalizeVoucherType($voucherData['voucher_type'] ?? 'JV');
        $existing = JournalEntry::where('category', 'GRN')
            ->where('reference_id', $grn->id)
            ->where('voucher_series', 'SYSTEM')
            ->where('is_system_generated', 1)
            ->first();

        if ($existing) {
            return $existing;
        }

        $lines = ! empty($voucherData['lines'])
            ? $this->buildCustomLines($grn, $voucherData['lines'])
            : $this->buildLines($grn);
        $totalDebit = $lines['total_debit'];
        $totalCredit = $lines['total_credit'];
        $voucherAmount = $totalDebit->isGreaterThan($totalCredit) ? $totalDebit : $totalCredit;

        if ($voucherAmount->isLessThanOrEqualTo(0)) {
            throw new \RuntimeException(__('GRN voucher total must be greater than zero.'));
        }

        if (! $totalDebit->isEqualTo($totalCredit)) {
            throw new \RuntimeException(__('Debit and Credit must be Equal.'));
        }

        $grnTotal = BigDecimal::of((string) $grn->items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->price;
        }))->toScale(2, RoundingMode::HALF_UP);

        if ($voucherAmount->isGreaterThan($grnTotal)) {
            throw new \RuntimeException(__('Voucher amount cannot be greater than GRN amount.'));
        }

        if (! User::whereKey($grn->owned_by)->lockForUpdate()->first(['id'])) {
            throw new \RuntimeException(__('The GRN branch is invalid.'));
        }

        $ownedBy = $this->validBranchId($voucherData['branches'] ?? $grn->owned_by, $grn);
        $transactionDate = $voucherData['date'] ?? $grn->grn_date;
        $journalReference = trim($voucherData['reference'] ?? '') ?: ($grn->reference_no ?: $this->grnNumber($grn));
        $journalDescription = trim($voucherData['narration'] ?? '') ?: __('Stock received against').' '.$this->grnNumber($grn);

        $latest = JournalEntry::where('owned_by', $ownedBy)
            ->where('voucher_type', $voucherType)
            ->where('voucher_series', 'SYSTEM')
            ->orderByDesc('journal_id')
            ->lockForUpdate()
            ->first();

        $timestamp = date('Y-m-d H:i:s',strtotime($transactionDate . ' ' . date('H:i:s')));

        $journal = JournalEntry::create([
            'journal_id' => $latest ? ((int) $latest->journal_id + 1) : 1,
            'date' => $transactionDate,
            'reference' => $journalReference,
            'description' => $journalDescription,
            'reference_id' => $grn->id,
            'category' => 'GRN',
            'voucher_type' => $voucherType,
            'voucher_series' => 'SYSTEM',
            'user_id' => $grn->vendor_id,
            'user_type' => 'Vender',
            'owned_by' => $ownedBy,
            'created_by' => $grn->created_by,
            'added_by' => \Auth::id(),
            'added_at' => now(),
            'updated_by' => \Auth::id(),
            'approved_by' => \Auth::id(),
            'approved_at' => now(),
            'is_system_generated' => 1,
            'status' => 'Approved',
        ]);
        $this->setIfColumn($journal, 'payment_date', $voucherData['payment_date'] ?? null);
        $this->setIfColumn($journal, 'payment_mode', $voucherData['payment_mode'] ?? null);
        $this->setIfColumn($journal, 'bank_id', $voucherData['bank_id'] ?? null);
        $this->setIfColumn($journal, 'category_type_id', $voucherData['category_type_id'] ?? null);
        $this->setIfColumn($journal, 'amount', $voucherData['amount'] ?? (string) $voucherAmount);
        $this->setIfColumn($journal, 'transaction_no', $voucherData['transaction_no'] ?? null);
            $journal->created_at =$timestamp;
            $journal->updated_at =$timestamp;
            $journal->save();

        foreach ($lines['lines'] as $line) {
            $JournalItem=JournalItem::create([
                'journal' => $journal->id,
                'account' => $line['account']->id,
                'user_id' => $grn->vendor_id,
                'user_type' => 'Vender',
                'model_id' => $grn->id,
                'model_type' => Grn::class,
                'entry_id' => $line['entry_id'],
                'types' => $voucherType,
                'description' => $line['description'],
                'ref_no' => $line['ref_no'],
                'tra_date' => $line['tra_date'],
                'head_ids' => $line['head_ids'],
                'branch_id' => $line['branch_id'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'added_by' => \Auth::id(),
                'added_at' => now(),
            ]);
                $JournalItem->created_at =$timestamp;
                $JournalItem->updated_at =$timestamp;
                $JournalItem->save();
        }

        return $journal;
    }

    private function buildLines(Grn $grn): array
    {
        $grn->loadMissing(['vendor', 'items.product']);

        if (! $grn->vendor || ! $grn->vendor->account_id) {
            throw new \RuntimeException(__('Selected vendor does not have a COA account attached.'));
        }

        $debitLines = [];
        $total = BigDecimal::zero()->toScale(2);

        foreach ($grn->items as $item) {
            if (! $item->product || ! $item->product->inventory_asset_account_id) {
                throw new \RuntimeException(__('A GRN product does not have an Inventory Asset account attached.'));
            }

            $lineKey = 'item_'.$item->id;
            $inventoryAccount = $this->account($item->product->inventory_asset_account_id, $grn->created_by, __('inventory asset'));
            $amount = BigDecimal::of((string) $item->quantity)
                ->multipliedBy((string) $item->price)
                ->toScale(2, RoundingMode::HALF_UP);

            if ($amount->isLessThanOrEqualTo(0)) {
                throw new \RuntimeException(__('Each GRN item must have a positive cost before Accounts approval.'));
            }

            $debitLines[] = [
                'key' => $lineKey,
                'item' => $item,
                'account' => $inventoryAccount,
                'amount' => (string) $amount,
                'description' => $item->product->name.' - '.$this->grnNumber($grn),
                'ref_no' => $grn->reference_no ?: $this->grnNumber($grn),
                'tra_date' => $grn->grn_date,
                'branch_id' => $grn->owned_by,
                'debit' => (string) $amount,
                'credit' => '0.00',
                'entry_id' => $item->id,
                'head_ids' => $item->product_id,
            ];
            $total = $total->plus($amount);
        }

        $vendorKey = 'vendor_payable';
        $vendorAccount = $this->account($grn->vendor->account_id, $grn->created_by, __('vendor payable'));
        $creditLine = [
            'key' => $vendorKey,
            'account' => $vendorAccount,
            'description' => __('Vendor payable for').' '.$this->grnNumber($grn),
            'ref_no' => $grn->reference_no ?: $this->grnNumber($grn),
            'tra_date' => $grn->grn_date,
            'branch_id' => $grn->owned_by,
            'debit' => '0.00',
            'credit' => (string) $total,
            'entry_id' => $grn->id,
            'head_ids' => null,
        ];

        return [
            'debit_lines' => $debitLines,
            'credit_line' => $creditLine,
            'lines' => array_merge($debitLines, [$creditLine]),
            'total' => $total,
            'total_debit' => $total,
            'total_credit' => $total,
            'grn_number' => $this->grnNumber($grn),
        ];
    }

    private function buildCustomLines(Grn $grn, array $submittedLines): array
    {
        $grn->loadMissing(['vendor', 'items']);

        $lines = [];
        $totalDebit = BigDecimal::zero()->toScale(2);
        $totalCredit = BigDecimal::zero()->toScale(2);

        foreach ($submittedLines as $index => $line) {
            $accountId = $line['account_id'] ?? $line['account'] ?? null;
            if (! $accountId) {
                throw new \RuntimeException(__('Please select an account for every voucher line.'));
            }

            $account = $this->account($accountId, $grn->created_by, __('voucher'));
            $debit = BigDecimal::of((string) ($line['debit'] ?? 0))->toScale(2, RoundingMode::HALF_UP);
            $credit = BigDecimal::of((string) ($line['credit'] ?? 0))->toScale(2, RoundingMode::HALF_UP);

            if ($debit->isLessThan(0) || $credit->isLessThan(0)) {
                throw new \RuntimeException(__('Debit and Credit cannot be negative.'));
            }

            if (($debit->isLessThanOrEqualTo(0) && $credit->isLessThanOrEqualTo(0)) || ($debit->isGreaterThan(0) && $credit->isGreaterThan(0))) {
                throw new \RuntimeException(__('Each line must have either debit or credit amount.'));
            }

            $branchId = $this->validBranchId($line['branch_id'] ?? $grn->owned_by, $grn);

            $lines[] = [
                'key' => (string) $index,
                'account' => $account,
                'description' => trim($line['description'] ?? ''),
                'ref_no' => trim($line['ref_no'] ?? '') ?: ($grn->reference_no ?: $this->grnNumber($grn)),
                'tra_date' => $line['tra_date'] ?? $grn->grn_date,
                'branch_id' => $branchId,
                'debit' => (string) $debit,
                'credit' => (string) $credit,
                'entry_id' => $line['entry_id'] ?? null,
                'head_ids' => $line['head_ids'] ?? null,
            ];

            $totalDebit = $totalDebit->plus($debit);
            $totalCredit = $totalCredit->plus($credit);
        }

        if (count($lines) < 2) {
            throw new \RuntimeException(__('Please add at least two voucher lines.'));
        }

        return [
            'lines' => $lines,
            'debit_lines' => array_filter($lines, fn ($line) => (float) $line['debit'] > 0),
            'credit_line' => null,
            'total' => $totalDebit,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'grn_number' => $this->grnNumber($grn),
        ];
    }

    private function account($accountId, int $creatorId, string $label): ChartOfAccount
    {
        $account = ChartOfAccount::whereKey($accountId)
            ->where('created_by', $creatorId)
            ->where('is_enabled', 1)
            ->first();

        if (! $account) {
            throw new \RuntimeException(__('The configured :label account is invalid or disabled.', ['label' => $label]));
        }

        return $account;
    }

    private function setIfColumn($model, string $column, $value): void
    {
        if (Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }

    private function validBranchId($branchId, Grn $grn): int
    {
        $branchId = (int) ($branchId ?: $grn->owned_by);

        if ($branchId === (int) $grn->owned_by || $branchId === (int) $grn->created_by) {
            return $branchId;
        }

        $exists = User::whereKey($branchId)
            ->where('type', 'branch')
            ->where('is_active', '1')
            ->where('created_by', $grn->created_by)
            ->exists();

        if (! $exists) {
            throw new \RuntimeException(__('The selected branch is invalid.'));
        }

        return $branchId;
    }

    private function grnNumber(Grn $grn): string
    {
        return 'GRN-'.sprintf('%05d', $grn->grn_no);
    }

    private function normalizeVoucherType($voucherType): string
    {
        $type = strtoupper($voucherType ?: 'JV');
        $allowedTypes = ['JV', 'CPV', 'BPV', 'CRV', 'BRV'];

        return in_array($type, $allowedTypes, true) ? $type : 'JV';
    }
}
