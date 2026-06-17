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

        return view('branchpurchase.create', compact('branches', 'purchase_number', 'product_services', 'customFields', 'branchId', 'warehouse'));
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        if ($user->type == 'company' && !$user->can('create purchase')) {
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
                return redirect()->back()->with('error', $messages->first());
            }

            $branchPurchase = new BranchPurchase();
            $branchPurchase->branch_purchase_no = $this->branchPurchaseNumber();
            $branchPurchase->branch_id = $request->branch_id;
            $branchPurchase->warehouse_id = $request->warehouse_id;
            $branchPurchase->purchase_date = $request->purchase_date;
            $branchPurchase->category_id = $request->category_id;
            $branchPurchase->status = 0;
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
            return redirect()->route('branchpurchase.show', Crypt::encrypt($branchPurchase->id))->with('success', __('Branch Purchase successfully created.'));
        } catch (\Exception $e) {
            DB::rollback();
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

        return view('branchpurchase.edit', compact('branches', 'product_services', 'branchPurchase', 'warehouse', 'purchase_number'));
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        $branchPurchase = BranchPurchase::find($id);

        if (!$branchPurchase || $branchPurchase->created_by != $user->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'company' && !$user->can('edit purchase')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type == 'branch' && $branchPurchase->branch_id != $user->id) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($branchPurchase->status > 0) {
            return redirect()->route('branchpurchase.index')->with('error', __('Branch Purchase items cannot be changed after submission.'));
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
                return redirect()->route('branchpurchase.index')->with('error', $messages->first());
            }

            $branchPurchase->branch_id = $request->branch_id;
            $branchPurchase->purchase_date = $request->purchase_date;
            $branchPurchase->category_id = $request->category_id;
            $branchPurchase->save();

            $oldItems = BranchPurchaseItem::where('branch_purchase_id', $branchPurchase->id)->get();
            $newItemIds = [];

            foreach ($request->items as $product) {
                if (!empty($product['id']) && $product['id'] != '0') {
                    $item = BranchPurchaseItem::find($product['id']);
                    if ($item) {
                        if (isset($product['item'])) $item->product_id = $product['item'];
                        $item->quantity = $product['quantity'];
                        $item->tax = $product['tax'] ?? 0;
                        $item->discount = $product['discount'] ?? 0;
                        $item->price = $product['price'];
                        $item->description = $product['description'] ?? '';
                        $item->save();
                        $newItemIds[] = $item->id;
                    }
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
            return redirect()->route('branchpurchase.index')->with('success', __('Branch Purchase successfully updated.'));
        } catch (\Exception $e) {
            DB::rollback();
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
            return redirect()->back()->with('error', __('Only company users can convert to Invoice.'));
        }

        $branchPurchase = BranchPurchase::find($id);
        if (!$branchPurchase || $branchPurchase->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($branchPurchase->status != 6) {
            return redirect()->back()->with('error', __('Branch Purchase must be finalized before conversion.'));
        }

        if ($branchPurchase->invoice_converted) {
            return redirect()->back()->with('error', __('Already converted to Invoice.'));
        }

        DB::beginTransaction();
        try {
            $branch = $branchPurchase->branchUser;
            $branchName = $branch ? $branch->name : 'Branch';

            $latestInvoice = Invoice::where('created_by', \Auth::user()->creatorId())->latest()->first();
            $invoiceNo = $latestInvoice ? $latestInvoice->invoice_id + 1 : 1;

            $mainStore = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
            $branchStore = $branchPurchase->warehouse_id;

            $invoice = new Invoice();
            $invoice->invoice_id = $invoiceNo;
            $invoice->customer_id = 0;
            $invoice->issue_date = $branchPurchase->purchase_date;
            $invoice->due_date = date('Y-m-d', strtotime($branchPurchase->purchase_date . ' +30 days'));
            $invoice->status = 0;
            $invoice->category_id = $branchPurchase->category_id;
            $invoice->from_store = $mainStore ? $mainStore->id : 0;
            $invoice->to_store = $branchStore;
            $invoice->owned_by = \Auth::user()->ownedId();
            $invoice->created_by = \Auth::user()->creatorId();
            $invoice->save();

            foreach ($branchPurchase->items as $item) {
                $qty = $item->shipped_quantity;
                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->invoice_id = $invoice->id;
                $invoiceProduct->product_id = $item->product_id;
                $invoiceProduct->quantity = $qty;
                $invoiceProduct->tax = $item->tax;
                $invoiceProduct->discount = $item->discount;
                $invoiceProduct->price = $item->price;
                $invoiceProduct->description = $item->description;
                $invoiceProduct->save();
            }

            $branchPurchase->invoice_converted = true;
            $branchPurchase->save();

            DB::commit();
            return redirect()->route('invoice.show', Crypt::encrypt($invoice->id))->with('success', __('Branch Purchase successfully converted to Invoice.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
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
