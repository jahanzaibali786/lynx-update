<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Grn;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class GrnVoucherService
{
    public function createForApproval(Grn $grn): JournalEntry
    {
        $existing = JournalEntry::where('category', 'GRN')
            ->where('reference_id', $grn->id)
            ->where('voucher_type', 'JV')
            ->where('voucher_series', 'SYSTEM')
            ->where('is_system_generated', 1)
            ->first();

        if ($existing) {
            return $existing;
        }

        $grn->loadMissing(['vendor', 'items.product']);

        if (! $grn->vendor || ! $grn->vendor->account_id) {
            throw new \RuntimeException(__('Selected vendor does not have a COA account attached.'));
        }

        $vendorAccount = $this->account($grn->vendor->account_id, $grn->created_by, __('vendor payable'));
        $lines = [];
        $total = BigDecimal::zero()->toScale(2);

        foreach ($grn->items as $item) {
            if (! $item->product || ! $item->product->inventory_asset_account_id) {
                throw new \RuntimeException(__('A GRN product does not have an Inventory Asset account attached.'));
            }

            $inventoryAccount = $this->account(
                $item->product->inventory_asset_account_id,
                $grn->created_by,
                __('inventory asset')
            );
            $amount = BigDecimal::of((string) $item->quantity)
                ->multipliedBy((string) $item->price)
                ->toScale(2, RoundingMode::HALF_UP);

            if ($amount->isLessThanOrEqualTo(0)) {
                throw new \RuntimeException(__('Each GRN item must have a positive cost before Accounts approval.'));
            }

            $lines[] = [
                'item' => $item,
                'account' => $inventoryAccount,
                'amount' => (string) $amount,
            ];
            $total = $total->plus($amount);
        }

        if ($total->isLessThanOrEqualTo(0)) {
            throw new \RuntimeException(__('GRN voucher total must be greater than zero.'));
        }

        if (! User::whereKey($grn->owned_by)->lockForUpdate()->first(['id'])) {
            throw new \RuntimeException(__('The GRN branch is invalid.'));
        }

        $latest = JournalEntry::where('owned_by', $grn->owned_by)
            ->where('voucher_type', 'JV')
            ->where('voucher_series', 'SYSTEM')
            ->orderByDesc('journal_id')
            ->lockForUpdate()
            ->first();

        $timestamp = date('Y-m-d H:i:s',strtotime($grn->grn_date . ' ' . date('H:i:s')));

        $journal = JournalEntry::create([
            'journal_id' => $latest ? ((int) $latest->journal_id + 1) : 1,
            'date' => $grn->grn_date,
            'reference' => $grn->reference_no ?: $this->grnNumber($grn),
            'description' => __('Stock received against').' '.$this->grnNumber($grn),
            'reference_id' => $grn->id,
            'category' => 'GRN',
            'voucher_type' => 'JV',
            'voucher_series' => 'SYSTEM',
            'user_id' => $grn->vendor_id,
            'user_type' => 'Vender',
            'owned_by' => $grn->owned_by,
            'created_by' => $grn->created_by,
            'added_by' => \Auth::id(),
            'added_at' => now(),
            'updated_by' => \Auth::id(),
            'approved_by' => \Auth::id(),
            'approved_at' => now(),
            'is_system_generated' => 1,
            'status' => 'Approved',
        ]);
            $journal->created_at =$timestamp;
            $journal->updated_at =$timestamp;
            $journal->save();

        foreach ($lines as $line) {
            $JournalItem=JournalItem::create([
                'journal' => $journal->id,
                'account' => $line['account']->id,
                'user_id' => $grn->vendor_id,
                'user_type' => 'Vender',
                'model_id' => $grn->id,
                'model_type' => Grn::class,
                'entry_id' => $line['item']->id,
                'types' => 'GRN',
                'description' => $line['item']->product->name.' - '.$this->grnNumber($grn),
                'ref_no' => $grn->reference_no ?: $this->grnNumber($grn),
                'tra_date' => $grn->grn_date,
                'head_ids' => $line['item']->product_id,
                'branch_id' => $grn->owned_by,
                'debit' => $line['amount'],
                'credit' => 0,
                'added_by' => \Auth::id(),
                'added_at' => now(),
            ]);
                $JournalItem->created_at =$timestamp;
                $JournalItem->updated_at =$timestamp;
                $JournalItem->save();
        }

        $jpay=JournalItem::create([
            'journal' => $journal->id,
            'account' => $vendorAccount->id,
            'user_id' => $grn->vendor_id,
            'user_type' => 'Vender',
            'model_id' => $grn->id,
            'model_type' => Grn::class,
            'entry_id' => $grn->id,
            'types' => 'GRN',
            'description' => __('Vendor payable for').' '.$this->grnNumber($grn),
            'ref_no' => $grn->reference_no ?: $this->grnNumber($grn),
            'tra_date' => $grn->grn_date,
            'branch_id' => $grn->owned_by,
            'debit' => 0,
            'credit' => (string) $total,
            'added_by' => \Auth::id(),
            'added_at' => now(),
        ]);
        $jpay->created_at =$timestamp;
        $jpay->updated_at =$timestamp;
        $jpay->save();  

        return $journal;
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

    private function grnNumber(Grn $grn): string
    {
        return 'GRN-'.sprintf('%05d', $grn->grn_no);
    }
}
