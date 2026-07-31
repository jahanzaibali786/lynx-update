<?php

namespace App\Http\Controllers;

use App\Models\StockTransferOrder;
use App\Models\StockTransferOrderItem;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Vender;
use App\Models\Utility;
use App\Models\warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockTransferOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();

        if (!$user->can('manage stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company') {
            $branchList = User::where('created_by', $creatorId)
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        } else {
            $branchList = User::where('id', $user->ownedId())
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id');
        }

        $query = StockTransferOrder::where('created_by', $creatorId);

        if ($user->type != 'company') {
            $query->where('branch_id', $user->id);
        } elseif ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        }

        $StockTransferOrders = $query->paginate(25);
        $status = StockTransferOrder::$statues;

        return view('stocktransferorder.index', compact('StockTransferOrders', 'status', 'branchList'));
    }

    public function create($branchId = 0)
    {
        $user = \Auth::user();

        if (!$user->can('create stock transfer order')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($user->type == 'branch') {
            $branchId = $user->id;
        }

        $customFields = CustomField::where('created_by', '=', $user->creatorId())->where('module', '=', 'purchase')->get();

        $purchase_number = $user->stockTransferOrderNumberFormat($this->stockTransferOrderNumber());

        if ($user->type == 'company') {
            $branches = User::where('created_by', $user->creatorId())
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $user->ownedId())
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id');
        }

        $warehouse = warehouse::where('owned_by', $user->creatorId())->get()->pluck('name', 'id');

        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $user->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
        $product_services->prepend('Select Item', '');

        if (request()->ajax()) {
            return view('stocktransferorder.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse'))->renderSections()['content'] ?? '';
        }

        return view('stocktransferorder.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        if (!$user->can('create stock transfer order')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), $this->stockTransferOrderRules(true));
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $messages->first()]);
            }

            return redirect()->back()->with('error', $messages->first());
        }

        DB::beginTransaction();
        try {
            $StockTransferOrder = new StockTransferOrder();
            $StockTransferOrder->branch_purchase_no = $this->stockTransferOrderNumber();
            $StockTransferOrder->branch_id = $user->type == 'branch' ? $user->id : $request->branch_id;
            $StockTransferOrder->warehouse_id = $request->warehouse_id;
            $StockTransferOrder->purchase_date = $request->purchase_date;
            $StockTransferOrder->category_id = $request->category_id;
            $StockTransferOrder->status = 5;
            $StockTransferOrder->created_by = $user->creatorId();
            $StockTransferOrder->owned_by = $user->ownedId();
            $StockTransferOrder->save();

            $products = $request->items;
            for ($i = 0; $i < count($products); $i++) {
                $item = new StockTransferOrderItem();
                $item->branch_purchase_id = $StockTransferOrder->id;
                $item->product_id = $products[$i]['item'];
                $item->quantity = $products[$i]['quantity'] ?? 0;
                $item->tax = $products[$i]['tax'] ?? 0;
                $item->discount = $products[$i]['discount'] ?? 0;
                $item->price = $products[$i]['price'] ?? 0;
                $item->description = $products[$i]['description'] ?? '';
                $item->save();
            }

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('Stock Transfer Order successfully created and sent to Head Office.')]);
            }
            return redirect()->route('stock-transfer-order.show', Crypt::encrypt($StockTransferOrder->id))->with('success', __('Stock Transfer Order successfully created and sent to Head Office.'));
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($ids)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();

        if (!$user->can('show stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Stock Transfer Order Not Found.'));
        }

        $StockTransferOrder = StockTransferOrder::find($id);

        if (!$StockTransferOrder || $StockTransferOrder->created_by != $creatorId) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company') {
            $branch = $StockTransferOrder->branchUser;
            $iteams = $StockTransferOrder->items;
            return view('stocktransferorder.view', compact('StockTransferOrder', 'branch', 'iteams'));
        }

        if ($user->type == 'branch' && $StockTransferOrder->branch_id == $user->id) {
            $branch = $StockTransferOrder->branchUser;
            $iteams = $StockTransferOrder->items;
            return view('stocktransferorder.view', compact('StockTransferOrder', 'branch', 'iteams'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function edit($idsd)
    {
        $user = \Auth::user();

        if (!$user->can('edit stock transfer order')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $idwww = Crypt::decrypt($idsd);
        $StockTransferOrder = StockTransferOrder::find($idwww);

        if (!$StockTransferOrder || $StockTransferOrder->created_by != $user->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($user->type == 'branch' && $StockTransferOrder->branch_id != $user->id) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $canEditCurrentStatus = (
            $user->type == 'company' && in_array($StockTransferOrder->status, [0, 5])
        ) || (
            $user->type == 'company' && $StockTransferOrder->status == 6 && $this->stockTransferOrderHasRemainingItems($StockTransferOrder)
        ) || (
            $user->type == 'branch' && $StockTransferOrder->branch_id == $user->id && $StockTransferOrder->status == 0
        );

        if (!$canEditCurrentStatus) {
            return response()->json(['error' => __('Stock Transfer Order cannot be edited in current status.')], 401);
        }

        $warehouse = warehouse::where('owned_by', $user->creatorId())->get()->pluck('name', 'id');
        $purchase_number = $user->purchaseNumberFormat($StockTransferOrder->branch_purchase_no);

        if ($user->type == 'company') {
            $branches = User::where('created_by', $user->creatorId())
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $user->ownedId())
                ->where('type', 'branch')
                ->where('is_active', 1)
                ->pluck('name', 'id');
        }
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $user->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');

        if (request()->ajax()) {
            return view('stocktransferorder.edit', compact('branches', 'product_services', 'StockTransferOrder', 'warehouse', 'purchase_number'))->renderSections()['content'] ?? '';
        }

        return view('stocktransferorder.edit', compact('branches', 'product_services', 'StockTransferOrder', 'warehouse', 'purchase_number'));
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();

        if (!$user->can('edit stock transfer order')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }

            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $StockTransferOrder = StockTransferOrder::find($id);

        if (!$StockTransferOrder || $StockTransferOrder->created_by != $user->creatorId()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'branch' && $StockTransferOrder->branch_id != $user->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $canEditCurrentStatus = (
            $user->type == 'company' && in_array($StockTransferOrder->status, [0, 5])
        ) || (
            $user->type == 'company' && $StockTransferOrder->status == 6 && $this->stockTransferOrderHasRemainingItems($StockTransferOrder)
        ) || (
            $user->type == 'branch' && $StockTransferOrder->branch_id == $user->id && $StockTransferOrder->status == 0
        );

        if (!$canEditCurrentStatus) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Stock Transfer Order cannot be edited in current status.')]);
            }
            return redirect()->route('stock-transfer-order.index')->with('error', __('Stock Transfer Order cannot be edited in current status.'));
        }

        $validator = \Validator::make($request->all(), $this->stockTransferOrderRules(false));
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $messages->first()]);
            }

            return redirect()->route('stock-transfer-order.index')->with('error', $messages->first());
        }

        DB::beginTransaction();
        try {
            $StockTransferOrder->branch_id = $user->type == 'branch' ? $user->id : $request->branch_id;
            $StockTransferOrder->purchase_date = $request->purchase_date;
            $StockTransferOrder->category_id = $request->category_id;
            $StockTransferOrder->save();

            $oldItems = StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)->lockForUpdate()->get();
            $newItemIds = [];

            foreach ($request->items as $product) {
                $itemId = $product['id'] ?? 0;
                // Treat as new item if ID is 0, '0', or doesn't exist in DB
                $item = ($itemId > 0)
                    ? StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)->find($itemId)
                    : null;
                if ($item) {
                    if (isset($product['item']) && (int) $product['item'] !== (int) $item->product_id && (float) ($item->shipped_quantity ?? 0) > 0) {
                        throw new \RuntimeException(__('Shipped Stock Transfer Order items cannot change product.'));
                    }

                    if (isset($product['item'])) {
                        $item->product_id = $product['item'];
                    }

                    $shippedQuantity = (float) ($item->shipped_quantity ?? 0);
                    $requestedQuantity = (float) $product['quantity'];
                    if ($requestedQuantity < $shippedQuantity) {
                        throw new \RuntimeException(__('Quantity cannot be less than already shipped quantity.'));
                    }

                    $item->quantity = $requestedQuantity;
                    $item->tax = $product['tax'] ?? 0;
                    $item->discount = $product['discount'] ?? 0;
                    $item->price = $product['price'];
                    $item->description = $product['description'] ?? '';
                    $item->save();
                    $newItemIds[] = $item->id;
                } else {
                    if (($product['id'] ?? 0) > 0) {
                        throw new \RuntimeException(__('The selected Stock Transfer Order item could not be found.'));
                    }

                    $item = new StockTransferOrderItem();
                    $item->branch_purchase_id = $StockTransferOrder->id;
                    $item->product_id = $product['item'];
                    $item->quantity = $product['quantity'];
                    $item->tax = $product['tax'] ?? 0;
                    $item->discount = $product['discount'] ?? 0;
                    $item->price = $product['price'];
                    $item->description = $product['description'] ?? '';
                    $item->save();
                    $newItemIds[] = $item->id;
                }
            }

            foreach ($oldItems as $old) {
                if (!in_array($old->id, $newItemIds)) {
                    $old->delete();
                }
            }

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('Stock Transfer Order successfully updated.')]);
            }
            return redirect()->route('stock-transfer-order.index')->with('success', __('Stock Transfer Order successfully updated.'));
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(StockTransferOrder $StockTransferOrder)
    {
        $user = \Auth::user();

        if (!$user->can('delete stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->created_by != $user->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'branch' && $StockTransferOrder->branch_id != $user->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->status == 6 || $StockTransferOrder->invoice_converted) {
            return redirect()->back()->with('error', __('Approved or converted Stock Transfer Orders cannot be deleted.'));
        }

        StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)->delete();
        $StockTransferOrder->delete();
        return redirect()->route('stock-transfer-order.index')->with('success', __('Stock Transfer Order successfully deleted.'));
    }

    function stockTransferOrderNumber()
    {
        $latest = StockTransferOrder::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->branch_purchase_no + 1;
    }

    public function fwToHo($id)
    {
        if (!\Auth::user()->can('forward stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'admin') {
            return redirect()->back()->with('error', __('Only branch users can forward to Head Office.'));
        }

        $StockTransferOrder = StockTransferOrder::find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->branch_id != \Auth::id()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->status != 0) {
            return redirect()->back()->with('error', __('Stock Transfer Order already forwarded.'));
        }

        $StockTransferOrder->status = 5;
        $StockTransferOrder->save();

        return redirect()->back()->with('success', __('Stock Transfer Order successfully forwarded to Head Office.'));
    }

    public function finalize(Request $request, $id)
    {
        if (!\Auth::user()->can('approve stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Only company users can finalize.'));
        }

        $StockTransferOrder = StockTransferOrder::find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->status != 5) {
            return redirect()->back()->with('error', __('Stock Transfer Order must be in Fw to Ho status.'));
        }

        DB::beginTransaction();
        try {
            if ($request->has('shipped_quantities')) {
                foreach ($request->shipped_quantities as $itemId => $shippedQty) {
                    $item = StockTransferOrderItem::find($itemId);
                    if ($item && $item->branch_purchase_id == $StockTransferOrder->id) {
                        $item->shipped_quantity = $shippedQty ?? $item->quantity;
                        $item->save();
                    }
                }
            }

            $StockTransferOrder->status = 6;
            $StockTransferOrder->save();

            DB::commit();
            return redirect()->back()->with('success', __('Stock Transfer Order approved successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject($id)
    {
        if (!\Auth::user()->can('reject stock transfer order')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Only company users can reject.'));
        }

        $StockTransferOrder = StockTransferOrder::find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($StockTransferOrder->status != 5) {
            return redirect()->back()->with('error', __('Stock Transfer Order must be in Fw to Ho status.'));
        }

        $StockTransferOrder->status = 0;
        $StockTransferOrder->save();

        return redirect()->back()->with('success', __('Stock Transfer Order rejected and returned to Draft.'));
    }

    public function convertToInvoice($id)
    {
        if (!\Auth::user()->can('convert stock transfer order to invoice')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (\Auth::user()->type != 'company') {
            return response()->json(['error' => __('Only company users can convert to Invoice.')], 401);
        }

        $StockTransferOrder = StockTransferOrder::with(['items.product', 'branchUser'])->find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($StockTransferOrder->status != 6) {
            return response()->json(['error' => __('Stock Transfer Order must be approved before conversion.')], 422);
        }

            if ($StockTransferOrder->invoice_converted) {
                return response()->json(['error' => __('Already converted to Invoice.')], 422);
            }

        if (!\Auth::user()->can('create invoice')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $mainStore = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
        $storeTo = warehouse::where('id', '!=', $mainStore ? $mainStore->id : 0)
            ->where('created_by', \Auth::user()->creatorId())
            ->pluck('name', 'id');
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', \Auth::user()->creatorId())
            ->where('type', '!=', 'service')
            ->get()
            ->pluck('name', 'id');
        $product_services->prepend('Select Item', '');

        $invoice_number = \Auth::user()->invoiceNumberFormat($this->stockTransferOrderInvoiceNumber());
        $issueDate = date('Y-m-d');
        $dueDate = date('Y-m-d');
        $selectedStoreTo = $StockTransferOrder->warehouse_id;
        $conversionItems = $StockTransferOrder->items->map(function ($item) {
            $shippedQuantity = (float) ($item->shipped_quantity ?? 0);
            $product = $item->product;
            $quantity = max(0, (float) $item->quantity - $shippedQuantity);

            return [
                'id' => $item->id,
                'source_item_id' => $item->id,
                'item_id' => $item->product_id,
                'item_name' => $product ? trim(($product->sku ? $product->sku . ' - ' : '') . $product->name) : '',
                'quantity' => $quantity,
                'ordered_quantity' => (float) $item->quantity,
                'shipped_quantity' => $shippedQuantity,
                'remaining_quantity' => $quantity,
                'price' => (float) $item->price,
                'discount' => (float) ($item->discount ?? 0),
                'tax' => $item->tax ?? '',
                'type' => 'new',
                'unit' => $product && $product->unit() ? $product->unit()->name : '',
                'amount' => ($quantity * (float) $item->price) - (float) ($item->discount ?? 0),
                'description' => $item->description ?? '',
                'source' => __('Stock Transfer Order'),
            ];
        })->values();

        $view = view('stocktransferorder.convert_to_invoice', compact(
            'StockTransferOrder',
            'mainStore',
            'storeTo',
            'product_services',
            'invoice_number',
            'issueDate',
            'dueDate',
            'selectedStoreTo',
            'conversionItems'
        ));

        if (request()->ajax()) {
            return $view->renderSections()['content'] ?? '';
        }

        return $view;
    }

    public function storeConvertedInvoice(Request $request, $id)
    {
        if (!\Auth::user()->can('convert stock transfer order to invoice')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        if (\Auth::user()->type != 'company') {
            return response()->json(['success' => false, 'message' => __('Only company users can convert to Invoice.')]);
        }

        if (!\Auth::user()->can('create invoice')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        $StockTransferOrder = StockTransferOrder::with('items')->find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        if ($StockTransferOrder->status != 6) {
            return response()->json(['success' => false, 'message' => __('Stock Transfer Order must be approved before conversion.')]);
        }

        $validator = \Validator::make($request->all(), [
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'store_from' => 'required',
            'store_to' => 'required',
            'items' => 'required|array|min:1',
            'items.*.source_item_id' => 'required|integer|exists:branch_purchase_items,id',
            'items.*.item' => 'required|integer|exists:product_services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()]);
        }

        DB::beginTransaction();
        try {
            $StockTransferOrder = StockTransferOrder::whereKey($id)->lockForUpdate()->first();
            if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
                throw new \RuntimeException(__('Permission denied.'));
            }

            if ($StockTransferOrder->status != 6) {
                throw new \RuntimeException(__('Stock Transfer Order must be approved before conversion.'));
            }

            $stockTransferItems = StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $invoice = new Invoice();
            $invoice->invoice_id = $this->stockTransferOrderInvoiceNumber();
            $invoice->customer_id = 0;
            $invoice->issue_date = $request->issue_date;
            $invoice->due_date = $request->due_date;
            $invoice->ref_number = $request->ref_number;
            $invoice->status = 0;
            $invoice->category_id = $StockTransferOrder->category_id;
            $invoice->from_store = $request->store_from;
            $invoice->to_store = $request->store_to;
            $invoice->owned_by = \Auth::user()->ownedId();
            $invoice->created_by = \Auth::user()->creatorId();
            $invoice->save();

            $newitems = $request->items;
            foreach ($request->items as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $sourceItemId = (int) ($item['source_item_id'] ?? 0);
                if (empty($item['item']) || $quantity <= 0 || $price <= 0 || !$sourceItemId || !isset($stockTransferItems[$sourceItemId])) {
                    throw new \Exception(__('Please enter valid item, quantity and price for all rows.'));
                }

                $sourceItem = $stockTransferItems[$sourceItemId];
                if ((int) $sourceItem->product_id !== (int) $item['item']) {
                    throw new \Exception(__('Invoice item must match the selected Stock Transfer Order item.'));
                }

                $shippedQuantity = (float) ($sourceItem->shipped_quantity ?? 0);
                $remainingQuantity = max(0, (float) $sourceItem->quantity - $shippedQuantity);
                if ($quantity > $remainingQuantity) {
                    throw new \Exception(__('Invoice quantity cannot exceed the remaining Stock Transfer Order quantity.'));
                }

                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->invoice_id = $invoice->id;
                $invoiceProduct->product_id = $item['item'];
                $invoiceProduct->quantity = $quantity;
                $invoiceProduct->tax = $item['tax'] ?? 0;
                $invoiceProduct->discount = $item['discount'] ?? 0;
                $invoiceProduct->price = $price;
                $invoiceProduct->type = $item['type'] ?? 'new';
                $invoiceProduct->description = $item['description'] ?? '';
                $invoiceProduct->save();

                $product = ProductService::find($item['item']);
                if ($product) {
                    $type = $item['type'] ?? 'new';
                    if ($type === 'use') {
                        $product->used_quantity = max(0, ($product->used_quantity ?? 0) - $quantity);
                    } elseif ($type === 'damage') {
                        $product->damaged_quantity = max(0, ($product->damaged_quantity ?? 0) - $quantity);
                    } else {
                        $product->quantity = max(0, ($product->quantity ?? 0) - $quantity);
                    }
                    $product->save();
                }

                $sourceItem->shipped_quantity = $shippedQuantity + $quantity;
                $sourceItem->save();

                Utility::warehouse_transfer_qty($request->store_from, $request->store_to, $item['item'], $quantity);
                $newitems[$index]['prod_id'] = $invoiceProduct->id;
                $newitems[$index]['source_item_id'] = $sourceItemId;
            }

            $StockTransferOrder->load('items');
            $StockTransferOrder->invoice_converted = $this->stockTransferOrderHasRemainingItems($StockTransferOrder) ? false : true;
            $StockTransferOrder->save();

            $data['id'] = $invoice->id;
            $data['no'] = $invoice->invoice_id;
            $data['date'] = $invoice->issue_date;
            $data['reference'] = $invoice->ref_number;
            $data['category'] = 'Invoice';
            $data['owned_by'] = $invoice->owned_by;
            $data['created_by'] = $invoice->created_by;
            $data['from_store'] = $invoice->from_store;
            $data['to_store'] = $invoice->to_store;
            $data['items'] = $newitems;
            Utility::invoicejv($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('Stock Transfer Order successfully converted to Invoice.'),
                'redirect_url' => route('invoice.show', Crypt::encrypt($invoice->id)),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function stockTransferOrderHasRemainingItems(StockTransferOrder $StockTransferOrder): bool
    {
        $StockTransferOrder->loadMissing('items');

        return $StockTransferOrder->items->contains(function ($item) {
            return max(0, (float) $item->quantity - (float) ($item->shipped_quantity ?? 0)) > 0;
        });
    }

    private function stockTransferOrderInvoiceNumber()
    {
        $latest = Invoice::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();

        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function items(Request $request)
    {
        if (!\Auth::user()->can('show stock transfer order')) {
            abort(403, __('Permission denied.'));
        }

        $StockTransferOrder = StockTransferOrder::where('created_by', \Auth::user()->creatorId())
            ->when(\Auth::user()->type != 'company', function ($query) {
                $query->where('branch_id', \Auth::id());
            })
            ->findOrFail($request->branch_purchase_id);
        $items = $StockTransferOrder->items;

        return response()->json($items);
    }

    public function product(Request $request)
    {
        if (!\Auth::user()->can('create stock transfer order') && !\Auth::user()->can('edit stock transfer order')) {
            abort(403, __('Permission denied.'));
        }

        $product = ProductService::where('created_by', \Auth::user()->creatorId())
            ->find($request->product_id);
        if ($product) {
            $taxes = [];
            if ($product->tax_id) {
                $taxData = \App\Models\Tax::find($product->tax_id);
                if ($taxData) {
                    $taxes[] = [
                        'name' => $taxData->name,
                        'rate' => $taxData->rate,
                    ];
                }
            }

            return response()->json(json_encode([
                'product' => $product,
                'taxes' => $taxes,
                'unit' => $product->unit_id ? \App\Models\Unit::find($product->unit_id)->name ?? '' : '',
            ]));
        }
        return response()->json('{}');
    }

    public function vender(Request $request)
    {
        if (!\Auth::user()->can('create stock transfer order') && !\Auth::user()->can('edit stock transfer order')) {
            abort(403, __('Permission denied.'));
        }

        $branch = User::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'branch')
            ->findOrFail($request->id);

        return view('stocktransferorder.branch_detail', compact('branch'));
    }

    private function stockTransferOrderRules(bool $requireWarehouse): array
    {
        $creatorId = \Auth::user()->creatorId();
        $rules = [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($creatorId) {
                    $query->where('created_by', $creatorId)->where('type', 'branch');
                }),
            ],
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item' => [
                'required',
                'integer',
                Rule::exists('product_services', 'id')->where(function ($query) use ($creatorId) {
                    $query->where('created_by', $creatorId)->where('type', '!=', 'service');
                }),
            ],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];

        if ($requireWarehouse) {
            $rules['warehouse_id'] = [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) use ($creatorId) {
                    $query->where('created_by', $creatorId)->orWhere('owned_by', $creatorId);
                }),
            ];
        }

        return $rules;
    }
}
