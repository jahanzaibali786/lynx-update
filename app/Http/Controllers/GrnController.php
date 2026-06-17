<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\ProductService;
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
        $query = Grn::with(['vendor', 'warehouse', 'branch', 'items'])
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

    public function create()
    {
        return view('grn.create', $this->formData());
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
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Grn $grn)
    {
        $this->authorizeGrn($grn);
        if ($grn->status >= 5) {
            return redirect()->route('grn.show', $grn->id)->with('error', __('Finalized GRN cannot be edited.'));
        }

        return view('grn.edit', array_merge($this->formData(), compact('grn')));
    }

    public function show(Grn $grn)
    {
        $this->authorizeGrn($grn);
        $grn->load(['vendor', 'warehouse', 'items.product']);

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
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Grn $grn)
    {
        $this->authorizeGrn($grn);

        DB::beginTransaction();
        try {
            if ($grn->status == 6) {
                $this->reverseStock($grn);
                Utility::updateUserBalance('vendor', $grn->vendor_id, $grn->getSubTotal(), 'credit');
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

    public function finalize(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status == 5 && \Auth::user()->type == 'company') {
            DB::beginTransaction();
            try {
                $grn->load('items');

                if ($grn->items->isEmpty()) {
                    DB::rollBack();
                    return redirect()->back()->with('error', __('Please add at least one item before finalizing GRN.'));
                }

                $this->applyStock($grn);
                Utility::userBalance('vendor', $grn->vendor_id, $grn->getSubTotal(), 'credit');

                $grn->status = 6;
                $grn->save();

                DB::commit();
                return redirect()->route('grn.show', $grn->id)->with('success', __('GRN finalized successfully. Stock and vendor balance updated.'));
            } catch (\Throwable $e) {
                DB::rollBack();
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        return redirect()->back()->with('error', __('Permission denied.'));
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
            'reference_no' => 'nullable|string|max:191',
            'purchase_order_id' => 'nullable|string|max:191',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:product_services,id',
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
            ->orderBy('name')
            ->get();

        $productOptions = $products->mapWithKeys(function ($product) {
            return [$product->id => trim(($product->sku ? $product->sku . ' - ' : '') . $product->name)];
        })->prepend('Select Item', '');

        $productMeta = $products->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'price' => (float) $product->purchase_price,
                    'description' => $product->purchase_description ?: $product->description,
                ],
            ];
        });

        return compact('vendors', 'warehouses', 'warehouseRecords', 'productOptions', 'productMeta', 'nextGrnNumber');
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
                'product_id' => $item['product_id'],
                'condition' => $item['condition'],
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
