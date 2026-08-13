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
use App\Models\Session as AcademicSession;
use App\Models\StockTransferNote;
use App\Models\StockTransferNoteItem;
use App\Models\User;
use App\Models\Vender;
use App\Models\StudyPack;
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
        $sessions = AcademicSession::where('created_by', $user->creatorId())
            ->orderByDesc('starting_date')
            ->pluck('year', 'id');
        $defaultSessionId = AcademicSession::where('created_by', $user->creatorId())
            ->where('active_status', 1)
            ->orderByDesc('starting_date')
            ->value('id') ?? $sessions->keys()->first();

        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $user->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
        $product_services->prepend('Select Item', '');
        $studyPackPayload = $this->studyPackPayload($user->creatorId());

        if (request()->ajax()) {
            return view('stocktransferorder.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse', 'sessions', 'defaultSessionId', 'studyPackPayload'))->renderSections()['content'] ?? '';
        }

        return view('stocktransferorder.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse', 'sessions', 'defaultSessionId', 'studyPackPayload'));
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
            $StockTransferOrder->session_id = $request->session_id;
            $StockTransferOrder->purchase_date = $request->purchase_date;
            $StockTransferOrder->category_id = $request->category_id;
            $StockTransferOrder->status = StockTransferOrder::STATUS_DRAFT;
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
                $item->study_pack_id = !empty($products[$i]['study_pack_id']) ? (int) $products[$i]['study_pack_id'] : null;
                $item->study_pack_title = $products[$i]['study_pack_title'] ?? null;
                $item->study_pack_class = $products[$i]['study_pack_class'] ?? null;
                $item->save();
            }

            DB::commit();
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('Stock Transfer Requisition successfully created in draft.')]);
            }
            return redirect()->route('stock-transfer-order.show', Crypt::encrypt($StockTransferOrder->id))->with('success', __('Stock Transfer Requisition successfully created in draft.'));
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
            return redirect()->back()->with('error', __('Stock Transfer Requisition Not Found.'));
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
            $user->type == 'company' && in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true)
        ) || (
            $user->type == 'branch' && $StockTransferOrder->branch_id == $user->id && in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true)
        );

        if (!$canEditCurrentStatus) {
            return response()->json(['error' => __('Stock Transfer Requisition cannot be edited in current status.')], 401);
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
        $sessions = AcademicSession::where('created_by', $user->creatorId())
            ->orderByDesc('starting_date')
            ->pluck('year', 'id');
        $studyPackPayload = $this->studyPackPayload($user->creatorId());

        if (request()->ajax()) {
            return view('stocktransferorder.edit', compact('branches', 'product_services', 'StockTransferOrder', 'warehouse', 'purchase_number', 'sessions', 'studyPackPayload'))->renderSections()['content'] ?? '';
        }

        return view('stocktransferorder.edit', compact('branches', 'product_services', 'StockTransferOrder', 'warehouse', 'purchase_number', 'sessions', 'studyPackPayload'));
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
            $user->type == 'company' && in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true)
        ) || (
            $user->type == 'branch' && $StockTransferOrder->branch_id == $user->id && in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true)
        );

        if (!$canEditCurrentStatus) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Stock Transfer Requisition cannot be edited in current status.')]);
            }
            return redirect()->route('stock-transfer-order.index')->with('error', __('Stock Transfer Requisition cannot be edited in current status.'));
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
            $StockTransferOrder->warehouse_id = $request->warehouse_id;
            $StockTransferOrder->session_id = $request->session_id;
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
                        throw new \RuntimeException(__('Shipped Stock Transfer Requisition items cannot change product.'));
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
                    $item->study_pack_id = !empty($product['study_pack_id']) ? (int) $product['study_pack_id'] : null;
                    $item->study_pack_title = $product['study_pack_title'] ?? null;
                    $item->study_pack_class = $product['study_pack_class'] ?? null;
                    $item->save();
                    $newItemIds[] = $item->id;
                } else {
                    if (($product['id'] ?? 0) > 0) {
                        throw new \RuntimeException(__('The selected Stock Transfer Requisition item could not be found.'));
                    }

                    $item = new StockTransferOrderItem();
                    $item->branch_purchase_id = $StockTransferOrder->id;
                    $item->product_id = $product['item'];
                    $item->quantity = $product['quantity'];
                    $item->tax = $product['tax'] ?? 0;
                    $item->discount = $product['discount'] ?? 0;
                    $item->price = $product['price'];
                    $item->description = $product['description'] ?? '';
                    $item->study_pack_id = !empty($product['study_pack_id']) ? (int) $product['study_pack_id'] : null;
                    $item->study_pack_title = $product['study_pack_title'] ?? null;
                    $item->study_pack_class = $product['study_pack_class'] ?? null;
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
                return response()->json(['success' => true, 'message' => __('Stock Transfer Requisition successfully updated.')]);
            }
            return redirect()->route('stock-transfer-order.index')->with('success', __('Stock Transfer Requisition successfully updated.'));
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

        if (!in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true) || $StockTransferOrder->invoice_converted) {
            return redirect()->back()->with('error', __('Approved or converted Stock Transfer Requisitions cannot be deleted.'));
        }

        StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)->delete();
        $StockTransferOrder->delete();
        return redirect()->route('stock-transfer-order.index')->with('success', __('Stock Transfer Requisition successfully deleted.'));
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

        $StockTransferOrder = StockTransferOrder::find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (\Auth::user()->type == 'branch' && $StockTransferOrder->branch_id != \Auth::id()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!in_array($StockTransferOrder->status, [StockTransferOrder::STATUS_DRAFT, StockTransferOrder::STATUS_REJECTED], true)) {
            return redirect()->back()->with('error', __('Only draft or rejected Stock Transfer Requisitions can be sent to Head Office.'));
        }

        $StockTransferOrder->status = StockTransferOrder::STATUS_SENT_TO_HO;
        $StockTransferOrder->save();

        return redirect()->back()->with('success', __('Stock Transfer Requisition successfully sent to Head Office.'));
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

        if ($StockTransferOrder->status != StockTransferOrder::STATUS_SENT_TO_HO) {
            return redirect()->back()->with('error', __('Stock Transfer Requisition must be in Sent to HO status.'));
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

            $StockTransferOrder->status = StockTransferOrder::STATUS_APPROVED;
            $StockTransferOrder->save();

            DB::commit();
            return redirect()->back()->with('success', __('Stock Transfer Requisition approved successfully.'));
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

        if ($StockTransferOrder->status != StockTransferOrder::STATUS_SENT_TO_HO) {
            return redirect()->back()->with('error', __('Stock Transfer Requisition must be in Sent to HO status.'));
        }

        $StockTransferOrder->status = StockTransferOrder::STATUS_REJECTED;
        $StockTransferOrder->save();

        return redirect()->back()->with('success', __('Stock Transfer Requisition rejected successfully.'));
    }

    public function convertToInvoice($id)
    {
        if (!\Auth::user()->can('convert stock transfer order to invoice')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (\Auth::user()->type != 'company') {
            return response()->json(['error' => __('Only company users can convert to Stock Transfer Note.')], 401);
        }

        $StockTransferOrder = StockTransferOrder::with(['items.product', 'branchUser'])->find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($StockTransferOrder->status != StockTransferOrder::STATUS_APPROVED) {
            return response()->json(['error' => __('Stock Transfer Requisition must be approved before conversion.')], 422);
        }

        if ($StockTransferOrder->invoice_converted) {
            return response()->json(['error' => __('Already converted to Stock Transfer Note.')], 422);
        }

        if (!\Auth::user()->can('create stock transfer note')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $mainStore = warehouse::where(function ($query) {
            $query->where('created_by', \Auth::user()->creatorId())
                ->orWhere('owned_by', \Auth::user()->creatorId());
        })
            ->where('owned_by', \Auth::user()->creatorId())
            ->first();

        if (!$mainStore) {
            $mainStore = warehouse::where(function ($query) {
                $query->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('owned_by', \Auth::user()->creatorId());
            })->first();
        }

        $storeTo = warehouse::where(function ($query) {
            $query->where('created_by', \Auth::user()->creatorId())
                ->orWhere('owned_by', \Auth::user()->creatorId());
        })
            ->where('id', '!=', $mainStore ? $mainStore->id : 0)
            ->get()
            ->pluck('name', 'id');
        $sessions = AcademicSession::where('created_by', \Auth::user()->creatorId())
            ->orderByDesc('starting_date')
            ->pluck('year', 'id');
        $defaultSessionId = $StockTransferOrder->session_id ?: (
            AcademicSession::where('created_by', \Auth::user()->creatorId())
                ->where('active_status', 1)
                ->orderByDesc('starting_date')
                ->value('id') ?? $sessions->keys()->first()
        );
        $studyPackPayload = $this->studyPackPayload(\Auth::user()->creatorId());
        $storeUsers = $this->storeUserMap();
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', \Auth::user()->creatorId())
            ->where('type', '!=', 'service')
            ->get()
            ->pluck('name', 'id');
        $product_services->prepend('Select Item', '');

        $invoice_number = \Auth::user()->invoiceNumberFormat($this->stockTransferNoteNumber());
        $issueDate = date('Y-m-d');
        $dueDate = date('Y-m-d');
        $selectedStoreTo = $StockTransferOrder->warehouse_id;
        $conversionItems = $StockTransferOrder->items->map(function ($item) {
            $shippedQuantity = (float) ($item->shipped_quantity ?? 0);
            $product = $item->product;
            $quantity = (float) $item->quantity - $shippedQuantity;

            if ($quantity <= 0) {
                return null;
            }

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
                'study_pack_id' => $item->study_pack_id,
                'study_pack_title' => $item->study_pack_title,
                'study_pack_class' => $item->study_pack_class,
                'source' => __('Stock Transfer Requisition'),
            ];
        })->filter()->values();

        $view = view('stocktransferorder.convert_to_invoice', compact(
            'StockTransferOrder',
            'mainStore',
            'storeTo',
            'product_services',
            'invoice_number',
            'issueDate',
            'dueDate',
            'selectedStoreTo',
            'conversionItems',
            'sessions',
            'defaultSessionId',
            'studyPackPayload',
            'storeUsers'
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
            return response()->json(['success' => false, 'message' => __('Only company users can convert to Stock Transfer Note.')]);
        }

        if (!\Auth::user()->can('create stock transfer note')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        $StockTransferOrder = StockTransferOrder::with('items')->find($id);
        if (!$StockTransferOrder || $StockTransferOrder->created_by != \Auth::user()->creatorId()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        if ($StockTransferOrder->status != StockTransferOrder::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => __('Stock Transfer Requisition must be approved before conversion.')]);
        }

        $validator = \Validator::make($request->all(), [
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'session_id' => [
                'required',
                'integer',
                Rule::exists('sessions', 'id')->where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId());
                }),
            ],
            'store_from' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId());
                }),
            ],
            'store_to' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId());
                }),
            ],
            'shipping_via' => 'nullable|string|max:50',
            'stn_type' => 'nullable|string|max:50',
            'ref_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.source_item_id' => 'nullable|integer',
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

            if ($StockTransferOrder->status != StockTransferOrder::STATUS_APPROVED) {
                throw new \RuntimeException(__('Stock Transfer Requisition must be approved before conversion.'));
            }

            $stockTransferItems = StockTransferOrderItem::where('branch_purchase_id', $StockTransferOrder->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $noteNumber = $this->stockTransferNoteNumber();
            $storeFrom = warehouse::with('assignedEmployee')->find($request->store_from);
            $storeTo = warehouse::with('assignedEmployee')->find($request->store_to);

            $note = new StockTransferNote();
            $note->stn_id = $noteNumber;
            $note->sto_id = $StockTransferOrder->id;
            $note->issue_date = $request->issue_date;
            $note->due_date = $request->due_date;
            $note->approve_date = null;
            $note->ref_number = $request->ref_number;
            $note->status = StockTransferNote::STATUS_DRAFT;
            $note->category_id = $StockTransferOrder->category_id;
            $note->shipping_via = $request->shipping_via;
            $note->stn_type = $request->stn_type ?: 'From Requisition';
            $note->store_from = $request->store_from;
            $note->store_to = $request->store_to;
            $note->session_id = $request->session_id;
            $note->approved_by = null;
            $note->issue_to = $request->store_to;
            $note->recived_by = $storeTo?->assignedEmployee?->user_id;
            $note->issue_by = $storeFrom?->assignedEmployee?->user_id;
            $note->owned_by = \Auth::user()->ownedId();
            $note->created_by = \Auth::user()->creatorId();
            $note->save();

            $newitems = $request->items;
            foreach ($request->items as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $sourceItemId = (int) ($item['source_item_id'] ?? 0);
                if (empty($item['item']) || $quantity <= 0 || $price <= 0) {
                    throw new \Exception(__('Please enter valid item, quantity and price for all rows.'));
                }

                $sourceItem = null;
                $shippedQuantity = 0;
                if ($sourceItemId > 0) {
                    if (!isset($stockTransferItems[$sourceItemId])) {
                        throw new \Exception(__('Selected Stock Transfer Requisition source item was not found.'));
                    }

                    $sourceItem = $stockTransferItems[$sourceItemId];
                    if ((int) $sourceItem->product_id !== (int) $item['item']) {
                        throw new \Exception(__('Stock Transfer Note item must match the selected Stock Transfer Requisition item.'));
                    }

                    $shippedQuantity = (float) ($sourceItem->shipped_quantity ?? 0);
                    $remainingQuantity = max(0, (float) $sourceItem->quantity - $shippedQuantity);
                    if ($quantity > $remainingQuantity) {
                        throw new \Exception(__('Stock Transfer Note quantity cannot exceed the remaining Stock Transfer Requisition quantity.'));
                    }
                }

                $noteItem = new StockTransferNoteItem();
                $noteItem->stn_id = $note->id;
                $noteItem->product_id = $item['item'];
                $noteItem->quantity = $quantity;
                $noteItem->price = $price;
                $noteItem->type = $item['type'] ?? 'new';
                $noteItem->description = $item['description'] ?? '';
                $noteItem->study_pack_id = !empty($item['study_pack_id']) ? (int) $item['study_pack_id'] : null;
                $noteItem->study_pack_title = $item['study_pack_title'] ?? null;
                $noteItem->study_pack_class = $item['study_pack_class'] ?? null;
                $noteItem->save();

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

                if ($sourceItem) {
                    $sourceItem->shipped_quantity = $shippedQuantity + $quantity;
                    $sourceItem->save();
                }

                Utility::warehouse_transfer_qty($request->store_from, $request->store_to, $item['item'], $quantity);
                $newitems[$index]['prod_id'] = $noteItem->id;
                $newitems[$index]['source_item_id'] = $sourceItemId;
            }

            $StockTransferOrder->load('items');
            $StockTransferOrder->invoice_converted = $this->stockTransferOrderHasRemainingItems($StockTransferOrder) ? false : true;
            $StockTransferOrder->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('Stock Transfer Requisition successfully converted to Stock Transfer Note.'),
                'redirect_url' => route('stock-transfer-note.show', Crypt::encrypt($note->id)),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function stockTransferNoteNumber(): int
    {
        $lastNumber = StockTransferNote::where('created_by', \Auth::user()->creatorId())->max('stn_id');

        return $lastNumber ? ((int) $lastNumber + 1) : 1;
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
            'session_id' => [
                'required',
                'integer',
                Rule::exists('sessions', 'id')->where(function ($query) use ($creatorId) {
                    $query->where('created_by', $creatorId);
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
            'items.*.study_pack_id' => ['nullable', 'integer'],
            'items.*.study_pack_title' => ['nullable', 'string'],
            'items.*.study_pack_class' => ['nullable', 'string'],
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

    private function studyPackPayload(int $creatorId)
    {
        $studyPacks = StudyPack::with('items')
            ->where('created_by', $creatorId)
            ->orderBy('class')
            ->orderBy('title')
            ->get();

        $productNames = ProductService::select(DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $creatorId)
            ->whereIn('id', $studyPacks->pluck('items')->flatten()->pluck('product_id')->filter()->unique())
            ->get()
            ->pluck('name', 'id');

        return $studyPacks->map(function ($studyPack) use ($productNames) {
            return [
                'id' => $studyPack->id,
                'title' => $studyPack->title,
                'class' => $studyPack->class,
                'session_id' => $studyPack->session_id,
                'items' => $studyPack->items->map(function ($item) use ($productNames) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $productNames[$item->product_id] ?? __('Unknown Item'),
                        'quantity' => (float) $item->quantity,
                        'price' => (float) $item->price,
                        'description' => '',
                    ];
                })->values()->all(),
            ];
        })->values();
    }

    private function storeUserMap()
    {
        return warehouse::with('assignedEmployee')
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->mapWithKeys(function ($store) {
                return [
                    $store->id => [
                        'id' => $store->assignedEmployee?->user_id,
                        'name' => $store->assignedEmployee?->name ?? '',
                    ],
                ];
            });
    }
}
