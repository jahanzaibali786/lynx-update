<?php

namespace App\Http\Controllers;

use App\Models\BranchPurchase;
use App\Models\BranchPurchaseItem;
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

class BranchPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();

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

        $query = BranchPurchase::where('created_by', $creatorId);

        if ($user->type != 'company') {
            $query->where('branch_id', $user->id);
        } elseif ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        }

        $branchPurchases = $query->paginate(25);
        $status = BranchPurchase::$statues;

        return view('branchpurchase.index', compact('branchPurchases', 'status', 'branchList'));
    }

    public function create($branchId = 0)
    {
        $user = \Auth::user();

        if ($user->type == 'company' && !$user->can('create purchase')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($user->type == 'branch') {
            $branchId = $user->id;
        }

        $customFields = CustomField::where('created_by', '=', $user->creatorId())->where('module', '=', 'purchase')->get();

        $purchase_number = $user->purchaseNumberFormat($this->branchPurchaseNumber());

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
            return view('branchpurchase.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse'))->renderSections()['content'] ?? '';
        }

        return view('branchpurchase.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        if ($user->type == 'company' && !$user->can('create purchase')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        DB::beginTransaction();
        try {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'warehouse_id' => 'required',
                    'purchase_date' => 'required',
                    'items' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $messages->first()]);
                }
                return redirect()->back()->with('error', $messages->first());
            }

            $branchPurchase = new BranchPurchase();
            $branchPurchase->branch_purchase_no = $this->branchPurchaseNumber();
            $branchPurchase->branch_id = $request->branch_id;
            $branchPurchase->warehouse_id = $request->warehouse_id;
            $branchPurchase->purchase_date = $request->purchase_date;
            $branchPurchase->category_id = $request->category_id;
            $branchPurchase->status = 5;
            $branchPurchase->created_by = $user->creatorId();
            $branchPurchase->owned_by = $user->ownedId();
            $branchPurchase->save();

            $products = $request->items;
            for ($i = 0; $i < count($products); $i++) {
                $item = new BranchPurchaseItem();
                $item->branch_purchase_id = $branchPurchase->id;
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
                return response()->json(['success' => true, 'message' => __('Branch Purchase successfully created and sent to Head Office.')]);
            }
            return redirect()->route('branchpurchase.show', Crypt::encrypt($branchPurchase->id))->with('success', __('Branch Purchase successfully created and sent to Head Office.'));
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

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Branch Purchase Not Found.'));
        }

        $branchPurchase = BranchPurchase::find($id);

        if (!$branchPurchase || $branchPurchase->created_by != $creatorId) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company' && $user->can('show purchase')) {
            $branch = $branchPurchase->branchUser;
            $iteams = $branchPurchase->items;
            return view('branchpurchase.view', compact('branchPurchase', 'branch', 'iteams'));
        }

        if ($user->type == 'branch' && $branchPurchase->branch_id == $user->id) {
            $branch = $branchPurchase->branchUser;
            $iteams = $branchPurchase->items;
            return view('branchpurchase.view', compact('branchPurchase', 'branch', 'iteams'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function edit($idsd)
    {
        $user = \Auth::user();
        $idwww = Crypt::decrypt($idsd);
        $branchPurchase = BranchPurchase::find($idwww);

        if (!$branchPurchase || $branchPurchase->created_by != $user->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($user->type == 'company' && !$user->can('edit purchase')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($user->type == 'branch' && $branchPurchase->branch_id != $user->id) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $canEditCurrentStatus = (
            $user->type == 'company' && in_array($branchPurchase->status, [0, 5])
        ) || (
            $user->type == 'branch' && $branchPurchase->branch_id == $user->id && $branchPurchase->status == 0
        );

        if (!$canEditCurrentStatus) {
            return response()->json(['error' => __('Branch Purchase cannot be edited in current status.')], 401);
        }

        $warehouse = warehouse::where('owned_by', $user->creatorId())->get()->pluck('name', 'id');
        $purchase_number = $user->purchaseNumberFormat($branchPurchase->branch_purchase_no);

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
            return view('branchpurchase.edit', compact('branches', 'product_services', 'branchPurchase', 'warehouse', 'purchase_number'))->renderSections()['content'] ?? '';
        }

        return view('branchpurchase.edit', compact('branches', 'product_services', 'branchPurchase', 'warehouse', 'purchase_number'));
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        $branchPurchase = BranchPurchase::find($id);

        if (!$branchPurchase || $branchPurchase->created_by != $user->creatorId()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company' && !$user->can('edit purchase')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'branch' && $branchPurchase->branch_id != $user->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $canEditCurrentStatus = (
            $user->type == 'company' && in_array($branchPurchase->status, [0, 5])
        ) || (
            $user->type == 'branch' && $branchPurchase->branch_id == $user->id && $branchPurchase->status == 0
        );

        if (!$canEditCurrentStatus) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Branch Purchase cannot be edited in current status.')]);
            }
            return redirect()->route('branchpurchase.index')->with('error', __('Branch Purchase cannot be edited in current status.'));
        }

        DB::beginTransaction();
        try {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'purchase_date' => 'required',
                    'items' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $messages->first()]);
                }
                return redirect()->route('branchpurchase.index')->with('error', $messages->first());
            }

            $branchPurchase->branch_id = $request->branch_id;
            $branchPurchase->purchase_date = $request->purchase_date;
            $branchPurchase->category_id = $request->category_id;
            $branchPurchase->save();

            $oldItems = BranchPurchaseItem::where('branch_purchase_id', $branchPurchase->id)->get();
            $newItemIds = [];

            foreach ($request->items as $product) {
                $itemId = $product['id'] ?? 0;
                // Treat as new item if ID is 0, '0', or doesn't exist in DB
                $item = ($itemId > 0) ? BranchPurchaseItem::find($itemId) : null;
                if ($item) {
                    if (isset($product['item'])) $item->product_id = $product['item'];
                    $item->quantity = $product['quantity'];
                    $item->tax = $product['tax'] ?? 0;
                    $item->discount = $product['discount'] ?? 0;
                    $item->price = $product['price'];
                    $item->description = $product['description'] ?? '';
                    $item->save();
                    $newItemIds[] = $item->id;
                } else {
                    $item = new BranchPurchaseItem();
                    $item->branch_purchase_id = $branchPurchase->id;
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
                return response()->json(['success' => true, 'message' => __('Branch Purchase successfully updated.')]);
            }
            return redirect()->route('branchpurchase.index')->with('success', __('Branch Purchase successfully updated.'));
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(BranchPurchase $branchPurchase)
    {
        $user = \Auth::user();

        if ($branchPurchase->created_by != $user->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company' && !$user->can('delete purchase')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'branch' && $branchPurchase->branch_id != $user->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        BranchPurchaseItem::where('branch_purchase_id', $branchPurchase->id)->delete();
        $branchPurchase->delete();
        return redirect()->route('branchpurchase.index')->with('success', __('Branch Purchase successfully deleted.'));
    }

    function branchPurchaseNumber()
    {
        $latest = BranchPurchase::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->branch_purchase_no + 1;
    }

    public function fwToHo($id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'admin') {
            return redirect()->back()->with('error', __('Only branch users can forward to Head Office.'));
        }

        $branchPurchase = BranchPurchase::find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($branchPurchase->status != 0) {
            return redirect()->back()->with('error', __('Branch Purchase already forwarded.'));
        }

        $branchPurchase->status = 5;
        $branchPurchase->save();

        return redirect()->back()->with('success', __('Branch Purchase successfully forwarded to Head Office.'));
    }

    public function finalize(Request $request, $id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Only company users can finalize.'));
        }

        $branchPurchase = BranchPurchase::find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($branchPurchase->status != 5) {
            return redirect()->back()->with('error', __('Branch Purchase must be in Fw to Ho status.'));
        }

        DB::beginTransaction();
        try {
            if ($request->has('shipped_quantities')) {
                foreach ($request->shipped_quantities as $itemId => $shippedQty) {
                    $item = BranchPurchaseItem::find($itemId);
                    if ($item && $item->branch_purchase_id == $branchPurchase->id) {
                        $item->shipped_quantity = $shippedQty ?? $item->quantity;
                        $item->save();
                    }
                }
            }

            $branchPurchase->status = 6;
            $branchPurchase->save();

            DB::commit();
            return redirect()->back()->with('success', __('Branch Purchase finalized successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject($id)
    {
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Only company users can reject.'));
        }

        $branchPurchase = BranchPurchase::find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($branchPurchase->status != 5) {
            return redirect()->back()->with('error', __('Branch Purchase must be in Fw to Ho status.'));
        }

        $branchPurchase->status = 0;
        $branchPurchase->save();

        return redirect()->back()->with('success', __('Branch Purchase rejected and returned to Draft.'));
    }

    public function convertToInvoice($id)
    {
        if (\Auth::user()->type != 'company') {
            return response()->json(['error' => __('Only company users can convert to Invoice.')], 401);
        }

        $branchPurchase = BranchPurchase::with(['items.product', 'branchUser'])->find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($branchPurchase->status != 6) {
            return response()->json(['error' => __('Branch Purchase must be finalized before conversion.')], 422);
        }

        if ($branchPurchase->invoice_converted) {
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

        $invoice_number = \Auth::user()->invoiceNumberFormat($this->branchPurchaseInvoiceNumber());
        $issueDate = date('Y-m-d');
        $dueDate = date('Y-m-d');
        $selectedStoreTo = $branchPurchase->warehouse_id;
        $conversionItems = $branchPurchase->items->map(function ($item) {
            $product = $item->product;
            $quantity = (float) ($item->shipped_quantity ?: $item->quantity);

            return [
                'id' => $item->id,
                'item_id' => $item->product_id,
                'item_name' => $product ? trim(($product->sku ? $product->sku . ' - ' : '') . $product->name) : '',
                'quantity' => $quantity,
                'price' => (float) $item->price,
                'discount' => (float) ($item->discount ?? 0),
                'tax' => $item->tax ?? '',
                'type' => 'new',
                'unit' => $product && $product->unit() ? $product->unit()->name : '',
                'amount' => ($quantity * (float) $item->price) - (float) ($item->discount ?? 0),
                'description' => $item->description ?? '',
                'source' => __('Branch Purchase'),
            ];
        })->values();

        $view = view('branchpurchase.convert_to_invoice', compact(
            'branchPurchase',
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
        if (\Auth::user()->type != 'company') {
            return response()->json(['success' => false, 'message' => __('Only company users can convert to Invoice.')]);
        }

        if (!\Auth::user()->can('create invoice')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        $branchPurchase = BranchPurchase::with('items')->find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')]);
        }

        if ($branchPurchase->status != 6) {
            return response()->json(['success' => false, 'message' => __('Branch Purchase must be finalized before conversion.')]);
        }

        if ($branchPurchase->invoice_converted) {
            return response()->json(['success' => false, 'message' => __('Already converted to Invoice.')]);
        }

        $validator = \Validator::make($request->all(), [
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'store_from' => 'required',
            'store_to' => 'required',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()]);
        }

        DB::beginTransaction();
        try {
            $invoice = new Invoice();
            $invoice->invoice_id = $this->branchPurchaseInvoiceNumber();
            $invoice->customer_id = 0;
            $invoice->issue_date = $request->issue_date;
            $invoice->due_date = $request->due_date;
            $invoice->ref_number = $request->ref_number;
            $invoice->status = 0;
            $invoice->category_id = $branchPurchase->category_id;
            $invoice->from_store = $request->store_from;
            $invoice->to_store = $request->store_to;
            $invoice->owned_by = \Auth::user()->ownedId();
            $invoice->created_by = \Auth::user()->creatorId();
            $invoice->save();

            $newitems = $request->items;
            foreach ($request->items as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                if (empty($item['item']) || $quantity <= 0 || $price <= 0) {
                    throw new \Exception(__('Please enter valid item, quantity and price for all rows.'));
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

                Utility::warehouse_transfer_qty($request->store_from, $request->store_to, $item['item'], $quantity);
                $newitems[$index]['prod_id'] = $invoiceProduct->id;
            }

            $branchPurchase->invoice_converted = true;
            $branchPurchase->save();

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
                'message' => __('Branch Purchase successfully converted to Invoice.'),
                'redirect_url' => route('invoice.show', Crypt::encrypt($invoice->id)),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function branchPurchaseInvoiceNumber()
    {
        $latest = Invoice::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();

        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function items(Request $request)
    {
        $items = BranchPurchaseItem::where('branch_purchase_id', $request->branch_purchase_id)->get();
        return response()->json($items);
    }

    public function product(Request $request)
    {
        $product = ProductService::find($request->product_id);
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
        $branch = User::find($request->id);
        return view('branchpurchase.branch_detail', compact('branch'));
    }
}
