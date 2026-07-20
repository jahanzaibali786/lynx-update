<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\ChartOfAccount;
use App\Models\ProductService;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\StockReport;
use App\Models\Utility;
use App\Models\Vender;
use App\Models\warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        $query = Grn::with(['vendor', 'warehouse', 'branch', 'items.purchase'])
            ->where('created_by', $user->creatorId());

        if ($user->type != 'company') {
            $query->where('owned_by', $user->ownedId());
        }

        $warehouses = $this->warehouseOptions();
        $vendors = Vender::where('created_by', $user->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('grn_date')) {
            $query->whereDate('grn_date', $request->grn_date);
        }

        $grns = $query->latest()->get();

        return view('grn.index', compact('grns', 'warehouses', 'vendors'));
    }

    public function create(Request $request)
    {
        $viewData = $this->formData();
        if ($request->ajax()) {
            $html = view('grn.create', $viewData)->renderSections()['content'] ?? '';
            return response('<div class="modal-body">' . $html . '</div>');
        }
        return view('grn.create', $viewData);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $user = \Auth::user();

        DB::beginTransaction();
        try {
            $ownedBy = $this->resolveOwnedBy($request);

            $grn = Grn::create([
                'grn_no' => $this->nextGrnNumber($user->creatorId()),
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => $data['warehouse_id'],
                'grn_date' => $data['grn_date'],
                'reference_no' => $data['reference_no'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 0,
                'owned_by' => $ownedBy,
                'created_by' => $user->creatorId(),
            ]);

            $this->storeItems($grn, $data['items']);

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('GRN successfully created.'), 'id' => $grn->id]);
            }
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Request $request, Grn $grn)
    {
        $this->authorizeGrn($grn);
        if ($grn->status >= 5) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Finalized GRN cannot be edited.')], 422);
            }
            return redirect()->route('grn.show', $grn->id)->with('error', __('Finalized GRN cannot be edited.'));
        }

        $viewData = array_merge($this->formData(), compact('grn'));
        if ($request->ajax()) {
            $html = view('grn.edit', $viewData)->renderSections()['content'] ?? '';
            return response('<div class="modal-body">' . $html . '</div>');
        }
        return view('grn.edit', $viewData);
    }

    public function show(Grn $grn)
    {
        $this->authorizeGrn($grn);
        $grn->load(['vendor', 'warehouse', 'items.product', 'items.purchase', 'items.purchaseProduct']);

        return view('grn.show', compact('grn'));
    }

    public function update(Request $request, Grn $grn)
    {
        $this->authorizeGrn($grn);
        if ($grn->status >= 5) {
            return redirect()->route('grn.show', $grn->id)->with('error', __('Finalized GRN cannot be edited.'));
        }
        $data = $this->validatedData($request);

        DB::beginTransaction();
        try {
            $grn->update([
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => $data['warehouse_id'],
                'grn_date' => $data['grn_date'],
                'reference_no' => $data['reference_no'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'owned_by' => $this->resolveOwnedBy($request),
            ]);

            $grn->items()->delete();
            StockReport::where('type', 'grn')->where('type_id', $grn->id)->delete();

            $this->storeItems($grn, $data['items']);

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('GRN successfully updated.'), 'id' => $grn->id]);
            }
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Grn $grn)
    {
        $this->authorizeGrn($grn);

        DB::beginTransaction();
        try {
            if ($grn->status == 8) {
                $this->reverseStock($grn);
                $this->reversePurchaseReceived($grn);
            }
            StockReport::where('type', 'grn')->where('type_id', $grn->id)->delete();
            $grn->items()->delete();
            $grn->delete();

            DB::commit();
            return redirect()->route('grn.index')->with('success', __('GRN successfully deleted.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function finalize(Request $request, Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status == 5 && \Auth::user()->type == 'company') {
            DB::beginTransaction();
            try {
                $grn->load('items.purchaseProduct');

                if ($grn->items->isEmpty()) {
                    DB::rollBack();
                    return redirect()->back()->with('error', __('Please add at least one item before finalizing GRN.'));
                }

                foreach ($grn->items as $item) {
                    $receivedQty = (float) ($request->input('received_quantities.' . $item->id, $item->quantity));
                    if ($receivedQty <= 0) {
                        throw new \Exception(__('Received quantity must be greater than zero.'));
                    }

                    if ($item->purchase_product_id && $item->purchaseProduct) {
                        $remaining = $this->remainingPurchaseItemQuantity($item->purchaseProduct, $grn->id);
                        if ($receivedQty > $remaining) {
                            throw new \Exception(__('Received quantity cannot exceed remaining purchase quantity.'));
                        }
                    }

                    $item->quantity = $receivedQty;
                    $item->save();
                }

                $this->syncPurchaseOrderText($grn);

                $grn->status = 6;
                $grn->save();

                DB::commit();
                return redirect()->route('grn.show', $grn->id)->with('success', __('GRN finalized successfully. Forward it to Accounts for stock approval.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function fwToAccounts(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status != 6) {
            return redirect()->back()->with('error', __('Only finalized GRN can be forwarded to Accounts.'));
        }

        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $grn->status = 7;
        $grn->save();

        return redirect()->back()->with('success', __('GRN forwarded to Accounts.'));
    }

    public function accountsApprove(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status != 7 || !in_array(\Auth::user()->type, ['company', 'accountant'])) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        DB::beginTransaction();
        try {
            $grn->load('items.purchaseProduct');

            if ($grn->items->isEmpty()) {
                throw new \Exception(__('Please add at least one item before approving GRN.'));
            }

            $this->applyPurchaseReceived($grn);
            $this->applyStock($grn);

            $grn->status = 8;
            $grn->save();

            DB::commit();
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN approved by Accounts. Stock has been updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('accountsApprove failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function fwToHo(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status != 0) {
            return redirect()->back()->with('error', __('GRN cannot be forwarded from current status.'));
        }

        $grn->status = 5;
        $grn->save();

        return redirect()->back()->with('success', __('GRN forwarded to Head Office.'));
    }

    public function reject(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status == 5 && \Auth::user()->type == 'company') {
            $grn->status = 0;
            $grn->save();
            return redirect()->back()->with('success', __('GRN rejected and sent back to draft.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    private function validatedData(Request $request)
    {
        return $request->validate([
            'vendor_id' => 'required|integer|exists:venders,id',
            'warehouse_id' => 'required|integer',
            'grn_date' => 'required|date',
            'reference_no' => 'required|string|max:191',
            'purchase_order_id' => 'required|string|max:1000',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:product_services,id',
            'items.*.purchase_id' => 'nullable|integer|exists:purchases,id',
            'items.*.purchase_product_id' => 'nullable|integer|exists:purchase_products,id',
            'items.*.purchase_order_no' => 'nullable|string|max:191',
            'items.*.ordered_quantity' => 'nullable|numeric|min:0',
            'items.*.condition' => 'required|in:new,used,damaged',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);
    }

    private function formData()
    {
        $user = \Auth::user();

        $vendors = Vender::where('created_by', $user->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');

        $warehouseRecords = $this->warehouseRecords();
        $warehouses = $warehouseRecords->pluck('name', 'id')->prepend('Select Store', '');
        $nextGrnNumber = $this->nextGrnNumber($user->creatorId());

    $products = ProductService::select('id', 'sku', 'name', 'purchase_price', 'purchase_description', 'description')
        ->where('created_by', $user->creatorId())
        ->where('type', '!=', 'service')
        ->where('type', '!=', 'grn')
        ->orderBy('name')
        ->get();

    $productOptions = $products->mapWithKeys(function ($product) {
        return [$product->id => trim(($product->sku ? $product->sku . ' - ' : '') . $product->name)];
    })->prepend('Select Product', '');

        $productMeta = $products->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'price' => (float) $product->purchase_price,
                    'description' => $product->purchase_description ?: $product->description,
                ],
            ];
        });

        $vendorAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', 0)
            ->where('created_by', $user->creatorId())
            ->orderBy('code')
            ->get();

        $vendorSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
            ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id')
            ->where('chart_of_accounts.parent', '!=', 0)
            ->where('chart_of_accounts.created_by', $user->creatorId())
            ->orderBy('chart_of_accounts.code')
            ->get();

        return compact('vendors', 'warehouses', 'warehouseRecords', 'productOptions', 'productMeta', 'nextGrnNumber', 'vendorAccounts', 'vendorSubAccounts');
    }

    private function warehouseOptions($branchId = null)
    {
        $user = \Auth::user();
        $query = warehouse::query();

        if ($user->type == 'company') {
            $query->where('owned_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        return $query->orderBy('name')->pluck('name', 'id')->prepend('Select Store', '');
    }

    private function warehouseRecords()
    {
        $user = \Auth::user();
        $query = warehouse::query();

        if ($user->type == 'company') {
            $query->where('owned_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        return $query->orderBy('name')->get(['id', 'name', 'owned_by']);
    }

    private function resolveOwnedBy(Request $request)
    {
        $user = \Auth::user();

        if ($user->type != 'company') {
            return $user->ownedId();
        }

        return $user->creatorId();
    }

    private function nextGrnNumber($creatorId)
    {
        return ((int) Grn::where('created_by', $creatorId)->max('grn_no')) + 1;
    }

    private function storeItems(Grn $grn, array $items)
    {
        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];

            GrnItem::create([
                'grn_id' => $grn->id,
                'purchase_id' => $item['purchase_id'] ?? null,
                'purchase_product_id' => $item['purchase_product_id'] ?? null,
                'purchase_order_no' => $item['purchase_order_no'] ?? null,
                'product_id' => $item['product_id'],
                'condition' => $item['condition'],
                'ordered_quantity' => (float) ($item['ordered_quantity'] ?? 0),
                'quantity' => $quantity,
                'price' => $item['price'] ?? 0,
                'description' => $item['description'] ?? null,
            ]);

            $product = ProductService::find($item['product_id']);
            if ($product && $product->type != 'service' && (float) ($item['price'] ?? 0) > 0 && $product->purchase_price != (float) $item['price']) {
                $product->purchase_price = (float) $item['price'];
                $product->save();
            }
        }

        $this->syncPurchaseOrderText($grn);
    }

    private function applyStock(Grn $grn)
    {
        StockReport::where('type', 'grn')->where('type_id', $grn->id)->delete();

        foreach ($grn->items as $grnItem) {
            $quantity = (float) $grnItem->quantity;

            $this->adjustProductConditionStock($grnItem->product_id, $grnItem->condition, $quantity);
            Utility::addWarehouseStock($grnItem->product_id, $quantity, $grn->warehouse_id);

            $description = $quantity . ' ' . __('quantity received in GRN') . ' GRN-' . sprintf('%05d', $grn->grn_no);
            Utility::addProductStock($grnItem->product_id, $quantity, 'grn', $description, $grn->id);
        }
    }

    private function reverseStock(Grn $grn)
    {
        $grn->loadMissing('items');

        foreach ($grn->items as $item) {
            $quantity = (float) $item->quantity;
            $this->adjustProductConditionStock($item->product_id, $item->condition, -$quantity);
            Utility::addWarehouseStock($item->product_id, -$quantity, $grn->warehouse_id);
        }
    }

    private function adjustProductConditionStock($productId, $condition, $quantity)
    {
        $product = ProductService::find($productId);

        if (!$product || $product->type == 'service') {
            return;
        }

        if ($condition === 'used') {
            $product->used_quantity = max(0, (float) ($product->used_quantity ?? 0) + $quantity);
        } elseif ($condition === 'damaged') {
            $product->damaged_quantity = max(0, (float) ($product->damaged_quantity ?? 0) + $quantity);
        } else {
            $product->quantity = max(0, (float) ($product->quantity ?? 0) + $quantity);
        }

        $product->save();
    }

    public function draftPurchases()
    {
        $user = \Auth::user();
        $purchases = Purchase::with(['vender', 'items'])
            ->where('created_by', $user->creatorId())
            ->where('status', 6)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($purchase) {
                return $purchase->items->sum(function ($item) {
                    return $this->remainingPurchaseItemQuantity($item);
                }) > 0;
            })
            ->values();

        return view('grn.draft_purchases', compact('purchases'));
    }

    public function purchaseItems($id, Request $request)
    {
        $purchase = Purchase::with('items.products')->findOrFail($id);

        if ($purchase->created_by != \Auth::user()->creatorId()) {
            abort(403, __('Permission denied.'));
        }

        $items = $purchase->items->map(function ($item) use ($purchase, $request) {
            $product = $item->products;
            $remaining = $this->remainingPurchaseItemQuantity($item, $request->input('grn_id'));

            if ($remaining <= 0) {
                return null;
            }

            return [
                'purchase_id' => $purchase->id,
                'purchase_product_id' => $item->id,
                'purchase_order_no' => \Auth::user()->purchaseNumberFormat($purchase->purchase_id),
                'product_id' => $item->product_id,
                'product_name' => $product ? trim(($product->sku ? $product->sku . ' - ' : '') . $product->name) : '',
                'ordered_quantity' => (float) $item->quantity,
                'received_quantity' => (float) ($item->received_quantity ?? 0),
                'quantity' => $remaining,
                'available_quantity' => $remaining,
                'price' => (float) ($item->price ?? 0),
                'description' => $item->description ?? '',
            ];
        })->filter()->values();

        return response()->json($items);
    }

    private function remainingPurchaseItemQuantity(PurchaseProduct $item, $exceptGrnId = null): float
    {
        $ordered = (float) $item->quantity;
        $received = (float) ($item->received_quantity ?? 0);
        $pending = GrnItem::query()
            ->join('grns', 'grns.id', '=', 'grn_items.grn_id')
            ->where('grn_items.purchase_product_id', $item->id)
            ->whereIn('grns.status', [0, 5, 6, 7])
            ->when($exceptGrnId, fn($q) => $q->where('grn_items.grn_id', '!=', $exceptGrnId))
            ->sum('grn_items.quantity');

        return max(0, $ordered - $received - (float) $pending);
    }

    private function syncPurchaseOrderText(Grn $grn): void
    {
        $orders = $grn->items()
            ->whereNotNull('purchase_order_no')
            ->pluck('purchase_order_no')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($orders)) {
            $grn->purchase_order_id = implode(', ', $orders);
            $grn->save();
        }
    }

    private function applyPurchaseReceived(Grn $grn): void
    {
        foreach ($grn->items as $item) {
            if (!$item->purchase_product_id) {
                continue;
            }

            $purchaseItem = PurchaseProduct::lockForUpdate()->find($item->purchase_product_id);
            if (!$purchaseItem) {
                continue;
            }

            $remaining = max(0, (float) $purchaseItem->quantity - (float) ($purchaseItem->received_quantity ?? 0));
            $quantity = (float) $item->quantity;

            if ($quantity > $remaining) {
                throw new \Exception(__('GRN received quantity exceeds remaining purchase quantity.'));
            }

            $purchaseItem->received_quantity = (float) ($purchaseItem->received_quantity ?? 0) + $quantity;
            $purchaseItem->save();
        }

        $this->refreshLinkedPurchaseConversionFlags($grn);
    }

    private function reversePurchaseReceived(Grn $grn): void
    {
        foreach ($grn->items as $item) {
            if (!$item->purchase_product_id) {
                continue;
            }

            $purchaseItem = PurchaseProduct::lockForUpdate()->find($item->purchase_product_id);
            if (!$purchaseItem) {
                continue;
            }

            $purchaseItem->received_quantity = max(0, (float) ($purchaseItem->received_quantity ?? 0) - (float) $item->quantity);
            $purchaseItem->save();
        }

        $this->refreshLinkedPurchaseConversionFlags($grn);
    }

    private function refreshLinkedPurchaseConversionFlags(Grn $grn): void
    {
        $purchaseIds = $grn->items->pluck('purchase_id')->filter()->unique();

        foreach ($purchaseIds as $purchaseId) {
            $purchase = Purchase::with('items')->find($purchaseId);
            if (!$purchase) {
                continue;
            }

            $purchase->grn_converted = $purchase->items->every(function ($item) {
                return (float) ($item->received_quantity ?? 0) >= (float) $item->quantity;
            });
            $purchase->save();
        }
    }

    public function addVendorForm()
    {
        $data = $this->formData();
        return view('grn.add_vendor', [
            'vendorAccounts' => $data['vendorAccounts'],
            'vendorSubAccounts' => $data['vendorSubAccounts'],
        ]);
    }

    private function authorizeGrn(Grn $grn)
    {
        $user = \Auth::user();

        if ($grn->created_by != $user->creatorId()) {
            abort(403, __('Permission denied.'));
        }

        if ($user->type != 'company' && $grn->owned_by != $user->ownedId()) {
            abort(403, __('Permission denied.'));
        }
    }
}
