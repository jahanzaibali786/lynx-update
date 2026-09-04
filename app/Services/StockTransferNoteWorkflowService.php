<?php

namespace App\Services;

use App\Models\ProductService;
use App\Models\StockReport;
use App\Models\StockTransferNote;
use App\Models\StockTransferNoteItem;
use App\Models\User;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferNoteWorkflowService
{
    public function forward(StockTransferNote $note, User $actor): StockTransferNote
    {
        return DB::transaction(function () use ($note, $actor) {
            $note = $this->lockedNote($note);

            if (!in_array($note->status, [StockTransferNote::STATUS_DRAFT, StockTransferNote::STATUS_REJECTED], true)) {
                throw new RuntimeException(__('Only draft or rejected notes can be sent for approval.'));
            }

            if ($note->items()->count() === 0) {
                throw new RuntimeException(__('Please add at least one item before forwarding.'));
            }

            $note->status = StockTransferNote::STATUS_SENT_FOR_APPROVAL;
            $note->forwarded_by = $actor->id;
            $note->forwarded_at = now();
            $note->save();

            return $note->fresh();
        });
    }

    public function reject(StockTransferNote $note, User $actor): StockTransferNote
    {
        return DB::transaction(function () use ($note, $actor) {
            $note = $this->lockedNote($note);

            if ($note->status !== StockTransferNote::STATUS_SENT_FOR_APPROVAL) {
                throw new RuntimeException(__('Only notes sent for approval can be rejected.'));
            }

            $note->status = StockTransferNote::STATUS_REJECTED;
            $note->rejected_by = $actor->id;
            $note->rejected_at = now();
            $note->save();

            return $note->fresh();
        });
    }

    public function approve(StockTransferNote $note, User $actor): StockTransferNote
    {
        return DB::transaction(function () use ($note, $actor) {
            $note = $this->lockedNote($note);

            if ($note->status !== StockTransferNote::STATUS_SENT_FOR_APPROVAL) {
                throw new RuntimeException(__('Only notes sent for approval can be approved.'));
            }

            if ((int) $note->store_from === (int) $note->store_to) {
                throw new RuntimeException(__('Source and receiving stores must be different.'));
            }

            $sourceStore = $this->storeForNote($note, (int) $note->store_from);
            $receivingStore = $this->storeForNote($note, (int) $note->store_to);
            $items = StockTransferNoteItem::where('stn_id', $note->id)->orderBy('id')->get();

            if ($items->isEmpty()) {
                throw new RuntimeException(__('Please add at least one item before approval.'));
            }

            foreach ($items as $item) {
                $this->transferItem($note, $item, $sourceStore, $receivingStore);
            }

            $note->status = StockTransferNote::STATUS_APPROVED;
            $note->approved_by = $actor->id;
            $note->approve_date = now()->toDateString();
            $note->save();

            return $note->fresh();
        }, 3);
    }

    public function issue(StockTransferNote $note, User $actor): StockTransferNote
    {
        return DB::transaction(function () use ($note, $actor) {
            $note = $this->lockedNote($note);

            if ($note->status !== StockTransferNote::STATUS_APPROVED) {
                throw new RuntimeException(__('Only approved notes can be issued.'));
            }

            $note->status = StockTransferNote::STATUS_ISSUED;
            $note->issued_by = $actor->id;
            $note->issued_at = now();
            $note->save();

            return $note->fresh();
        });
    }

    private function transferItem(
        StockTransferNote $note,
        StockTransferNoteItem $item,
        warehouse $sourceStore,
        warehouse $receivingStore
    ): void {
        $quantity = (float) $item->quantity;

        if ($quantity <= 0) {
            throw new RuntimeException(__('Transfer quantity must be greater than zero.'));
        }

        $stockField = $this->warehouseStockField($item->type ?? 'new');
        $condition = $this->stockCondition($item->type ?? 'new');
        $sourceStock = $this->lockedWarehouseStock($note, $sourceStore->id, $item->product_id);
        $available = (float) ($sourceStock->{$stockField} ?? 0);

        if ($available < $quantity) {
            throw new RuntimeException(__('Insufficient stock in the source store for :product.', [
                'product' => ProductService::find($item->product_id)?->name ?? $item->product_id,
            ]));
        }

        $allocations = $this->consumeFifoLayers($note, $item, $sourceStore->id, $condition, $quantity);

        $sourceStock->{$stockField} = $available - $quantity;
        $sourceStock->save();

        $receivingStock = WarehouseProduct::where('warehouse_id', $receivingStore->id)
            ->where('product_id', $item->product_id)
            ->lockForUpdate()
            ->first();

        if (!$receivingStock) {
            $receivingStock = new WarehouseProduct();
            $receivingStock->warehouse_id = $receivingStore->id;
            $receivingStock->product_id = $item->product_id;
            $receivingStock->quantity = 0;
            $receivingStock->used_quantity = 0;
            $receivingStock->damaged_quantity = 0;
            $receivingStock->created_by = $note->created_by;
        }

        $receivingStock->{$stockField} = (float) ($receivingStock->{$stockField} ?? 0) + $quantity;
        $receivingStock->save();

        foreach ($allocations as $allocation) {
            $this->recordMovement(
                $note,
                $item,
                $sourceStore,
                -$allocation['quantity'],
                $allocation['unit_price'],
                0,
                'stock_transfer_note_out'
            );

            $this->recordMovement(
                $note,
                $item,
                $receivingStore,
                $allocation['quantity'],
                $allocation['unit_price'],
                $allocation['quantity'],
                'stock_transfer_note_in'
            );
        }
    }

    private function consumeFifoLayers(
        StockTransferNote $note,
        StockTransferNoteItem $item,
        int $warehouseId,
        string $condition,
        float $requiredQuantity
    ): array {
        $layers = StockReport::where('product_id', $item->product_id)
            ->where('warehouse_id', $warehouseId)
            ->where('created_by', $note->created_by)
            ->where('remaining_qty', '>', 0)
            ->where(function ($query) use ($condition) {
                $query->where('condition', $condition);
                if ($condition === 'new') {
                    $query->orWhereNull('condition');
                }
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $remaining = $requiredQuantity;
        $allocations = [];

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $available = (float) $layer->remaining_qty;
            $consumed = min($available, $remaining);

            if ($consumed <= 0) {
                continue;
            }

            $layer->remaining_qty = $available - $consumed;
            $layer->save();

            $allocations[] = [
                'quantity' => $consumed,
                'unit_price' => (float) $layer->unit_price,
            ];
            $remaining -= $consumed;
        }

        if ($remaining > 0.00001) {
            throw new RuntimeException(__('Insufficient FIFO stock history in the source store for :product.', [
                'product' => ProductService::find($item->product_id)?->name ?? $item->product_id,
            ]));
        }

        return $allocations;
    }

    private function recordMovement(
        StockTransferNote $note,
        StockTransferNoteItem $item,
        warehouse $store,
        float $quantity,
        float $unitPrice,
        float $remainingQuantity,
        string $type
    ): void {
        StockReport::create([
            'product_id' => $item->product_id,
            'quantity' => $quantity,
            'warehouse_id' => $store->id,
            'unit_price' => $unitPrice,
            'sale_price' => (float) $item->price,
            'remaining_qty' => $remainingQuantity,
            'condition' => $this->stockCondition($item->type ?? 'new'),
            'type' => $type,
            'type_id' => $note->id,
            'description' => __('Stock Transfer Note :number', [
                'number' => sprintf('STN-%05d', $note->stn_id),
            ]),
            'owned_by' => $store->owned_by ?? $note->owned_by,
            'created_by' => $note->created_by,
        ]);
    }

    private function lockedWarehouseStock(StockTransferNote $note, int $warehouseId, int $productId): WarehouseProduct
    {
        $stock = WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw new RuntimeException(__('Product stock does not exist in the source store.'));
        }

        return $stock;
    }

    private function lockedNote(StockTransferNote $note): StockTransferNote
    {
        return StockTransferNote::whereKey($note->id)->lockForUpdate()->firstOrFail();
    }

    private function storeForNote(StockTransferNote $note, int $storeId): warehouse
    {
        $store = warehouse::whereKey($storeId)
            ->where(function ($query) use ($note) {
                $query->where('created_by', $note->created_by)
                    ->orWhere('owned_by', $note->created_by);
            })
            ->first();

        if (!$store) {
            throw new RuntimeException(__('Selected store is outside the Stock Transfer Note company scope.'));
        }

        return $store;
    }

    private function warehouseStockField(string $type): string
    {
        return match ($type) {
            'use', 'used' => 'used_quantity',
            'damage', 'damaged' => 'damaged_quantity',
            default => 'quantity',
        };
    }

    private function stockCondition(string $type): string
    {
        return match ($type) {
            'use', 'used' => 'used',
            'damage', 'damaged' => 'damaged',
            default => 'new',
        };
    }
}
