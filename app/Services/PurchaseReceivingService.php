<?php

namespace App\Services;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Purchase;
use App\Models\PurchaseProduct;

class PurchaseReceivingService
{
    public function isFullyReceived(Purchase $purchase): bool
    {
        return $purchase->items->isNotEmpty() && $purchase->items->every(function ($item) {
            return (float) ($item->received_quantity ?? 0) >= (float) $item->quantity;
        });
    }

    public function remaining(PurchaseProduct $purchaseItem, ?int $exceptGrnId = null): float
    {
        $received = (float) ($purchaseItem->received_quantity ?? 0);

        if ($exceptGrnId) {
            $currentGrnQuantity = (float) GrnItem::where('grn_id', $exceptGrnId)
                ->where('purchase_product_id', $purchaseItem->id)
                ->sum('quantity');
            $received = max(0, $received - $currentGrnQuantity);
        }

        return max(0, (float) $purchaseItem->quantity - $received);
    }

    public function book(Grn $grn): void
    {
        $grn->loadMissing('items');
        $purchaseIds = [];

        foreach ($grn->items as $grnItem) {
            if (!$grnItem->purchase_product_id) {
                continue;
            }

            $purchaseItem = PurchaseProduct::lockForUpdate()->find($grnItem->purchase_product_id);
            if (!$purchaseItem) {
                throw new \RuntimeException(__('The linked purchase item no longer exists.'));
            }

            $purchase = Purchase::where('created_by', $grn->created_by)->find($purchaseItem->purchase_id);
            if (!$purchase || ($grnItem->purchase_id && (int) $grnItem->purchase_id !== (int) $purchase->id)) {
                throw new \RuntimeException(__('The GRN item does not belong to the selected purchase.'));
            }

            if ((int) $grnItem->product_id !== (int) $purchaseItem->product_id) {
                throw new \RuntimeException(__('The GRN product does not match the purchase item.'));
            }

            $quantity = (float) $grnItem->quantity;
            // if ($quantity > $this->remaining($purchaseItem)) {
            //     throw new \RuntimeException(__('GRN received quantity exceeds remaining purchase quantity.'));
            // }

            $purchaseItem->received_quantity = (float) ($purchaseItem->received_quantity ?? 0) + $quantity;
            $purchaseItem->save();

            if ((int) $grnItem->purchase_id !== (int) $purchase->id) {
                $grnItem->purchase_id = $purchase->id;
                $grnItem->save();
            }

            $purchaseIds[] = $purchase->id;
        }

        $this->refreshPurchaseFlags($purchaseIds);
    }

    public function release(Grn $grn): void
    {
        $grn->loadMissing('items');
        $purchaseIds = [];

        foreach ($grn->items as $grnItem) {
            if (!$grnItem->purchase_product_id) {
                continue;
            }

            $purchaseItem = PurchaseProduct::lockForUpdate()->find($grnItem->purchase_product_id);
            if (!$purchaseItem) {
                continue;
            }

            $purchaseItem->received_quantity = max(
                0,
                (float) ($purchaseItem->received_quantity ?? 0) - (float) $grnItem->quantity
            );
            $purchaseItem->save();
            $purchaseIds[] = $purchaseItem->purchase_id;
        }

        $this->refreshPurchaseFlags($purchaseIds);
    }

    private function refreshPurchaseFlags(array $purchaseIds): void
    {
        foreach (array_unique($purchaseIds) as $purchaseId) {
            $purchase = Purchase::with('items')->find($purchaseId);
            if (!$purchase) {
                continue;
            }

            $fullyReceived = $this->isFullyReceived($purchase);

            $purchase->grn_converted = $fullyReceived;

            if ($fullyReceived && (int) $purchase->status === Purchase::STATUS_FINALIZED) {
                $purchase->status = Purchase::STATUS_FULLY_RECEIVED;
            } elseif (!$fullyReceived && (int) $purchase->status === Purchase::STATUS_FULLY_RECEIVED) {
                $purchase->status = Purchase::STATUS_FINALIZED;
            }

            $purchase->save();
        }
    }
}
