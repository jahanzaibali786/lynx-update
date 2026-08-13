<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ProductServiceCategory;
use App\Models\ProductService;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\StockReport;
use App\Models\Utility;
use App\Models\User;
use App\Models\Vender;
use App\Models\warehouse;
use App\Services\PurchaseReceivingService;
use App\Services\GrnVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GrnController extends Controller
{
    private PurchaseReceivingService $purchaseReceiving;
    private GrnVoucherService $grnVoucher;

    public function __construct(PurchaseReceivingService $purchaseReceiving, GrnVoucherService $grnVoucher)
    {
        $this->purchaseReceiving = $purchaseReceiving;
        $this->grnVoucher = $grnVoucher;
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage grn')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user = \Auth::user();
        $query = Grn::with(['vendor', 'warehouse', 'branch', 'items.purchase'])
            ->where('created_by', $user->creatorId());

        if ($user->type != 'company') {
            $query->where('owned_by', $user->ownedId());
        }

        $warehouses = $this->warehouseOptions();
        $vendors = Vender::optionsForCreator($user->creatorId());

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
        $statuses =GRN::$statues;

        return view('grn.index', compact('grns', 'warehouses', 'vendors','statuses'));
    }

    public function accountsIndex(Request $request)
    {
        $user = \Auth::user();
        
        if (!$user->can('show account grn')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = Grn::with(['vendor', 'warehouse', 'branch', 'items.purchase'])
            ->where('created_by', $user->creatorId());
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', [7, 8]);
        }

        $warehouses = $this->warehouseOptions();
        $vendors = Vender::optionsForCreator($user->creatorId());

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if (!$request->has('start_date') && !$request->has('end_date')) {
            $request->merge([
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-d')
            ]);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('grn_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('grn_date', '<=', $request->end_date);
        }

        $grns = $query->latest()->paginate(15);
        
        $statuses = [
            7 => 'Pending',
            8 => 'Approved'
        ];

        return view('grn.accounts_index', compact('grns', 'warehouses', 'vendors', 'statuses'));
    }

    public function create(Request $request)
    {
        if (!\Auth::user()->can('create grn')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $viewData = $this->formData();
        if ($request->ajax()) {
            $html = view('grn.create', $viewData)->renderSections()['content'] ?? '';
            return response('<div class="modal-body">' . $html . '</div>');
        }
        return view('grn.create', $viewData);
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create grn')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

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
                'purchase_order' => $data['purchase_order'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 0,
                'owned_by' => $ownedBy,
                'created_by' => $user->creatorId(),
                'added_by' => $user->id,
            ]);

            $this->storeItems($grn, $data['items']);
            $grn->load('items');
            $this->applyPurchaseReceived($grn);

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
        if (!\Auth::user()->can('edit grn')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $this->authorizeGrn($grn);
        if ($grn->status >= 5 && !in_array($grn->status, [9, 10])) {
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
        if (!\Auth::user()->can('show grn')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $this->authorizeGrn($grn);
        $grn->load(['vendor', 'warehouse', 'addedBy', 'approvedBy', 'voucher', 'items.product', 'items.purchase', 'items.purchaseProduct']);

        return view('grn.show', compact('grn'));
    }

    public function update(Request $request, Grn $grn)
    {
        if (!\Auth::user()->can('edit grn')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $this->authorizeGrn($grn);
        if ($grn->status >= 5 && !in_array($grn->status, [9, 10])) {
            return redirect()->route('grn.show', $grn->id)->with('error', __('Finalized GRN cannot be edited.'));
        }
        $data = $this->validatedData($request);

        DB::beginTransaction();
        try {
            $grn->load('items');
            $this->reversePurchaseReceived($grn);

            $grn->update([
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => $data['warehouse_id'],
                'grn_date' => $data['grn_date'],
                'reference_no' => $data['reference_no'] ?? null,
                'purchase_order' => $data['purchase_order'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'owned_by' => $this->resolveOwnedBy($request),
            ]);

            $grn->items()->delete();
            StockReport::where('type', 'grn')->where('type_id', $grn->id)->delete();

            $this->storeItems($grn, $data['items']);
            $grn->load('items');
            $this->applyPurchaseReceived($grn);

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
        if (!\Auth::user()->can('delete grn')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $this->authorizeGrn($grn);

        if ($grn->status == 8) {
            return redirect()->back()->with('error', __('Accounts-approved GRN cannot be deleted. Reverse its voucher instead.'));
        }

        DB::beginTransaction();
        try {
            $this->reversePurchaseReceived($grn);
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

        if ($grn->status == 5 && \Auth::user()->can('finalize grn')) {
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

                    // if ($item->purchase_product_id && $item->purchaseProduct) {
                    //     $remaining = $this->remainingPurchaseItemQuantity($item->purchaseProduct, $grn->id);
                    //     if ($receivedQty > $remaining) {
                    //         throw new \Exception(__('Received quantity cannot exceed remaining purchase quantity.'));
                    //     }
                    // }

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

        if (!\Auth::user()->can('forward grn to accounts')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $grn->status = 7;
        $grn->save();

        return redirect()->back()->with('success', __('GRN forwarded to Accounts.'));
    }

    public function accountsApprove(Request $request, Grn $grn)
    {
        $this->authorizeGrn($grn);

        if (
            $grn->status != 7 || !\Auth::user()->can('account approve grn')
        ) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($request->isMethod('get')) {
            try {
                $grn->load(['vendor', 'warehouse', 'branch', 'items.product']);

                if ($grn->items->isEmpty()) {
                    return redirect()->back()->with('error', __('Please add at least one item before approving GRN.'));
                }

                $voucherPreview = $this->grnVoucher->previewLines($grn);
                $chartAccountOptions = $this->chartAccountOptions();
                $branchOptions = $this->branchOptions();
                $bankAccounts = $this->bankAccountOptions();
                $voucherCategoryTypes = $this->voucherCategoryTypeOptions();
                $displayVoucherNumber = __('System generated on save');

                return view('grn.accounts_approve', compact('grn', 'voucherPreview', 'chartAccountOptions', 'branchOptions', 'bankAccounts', 'voucherCategoryTypes', 'displayVoucherNumber'));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        $request->validate([
            'voucher_type' => ['required', Rule::in(['JV', 'CPV', 'BPV', 'CRV', 'BRV'])],
            'branches' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'payment_date' => ['nullable', 'date'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'bank_id' => ['nullable', 'integer'],
            'category_type_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'transaction_no' => ['nullable', 'string', 'max:255'],
            'narration' => ['nullable', 'string'],
            'accounts' => ['required', 'array'],
            'accounts.*.account_id' => ['required', 'integer'],
            'accounts.*.description' => ['nullable', 'string'],
            'accounts.*.debit' => ['nullable', 'numeric', 'min:0'],
            'accounts.*.credit' => ['nullable', 'numeric', 'min:0'],
            'accounts.*.tra_date' => ['nullable', 'date'],
            'accounts.*.ref_no' => ['nullable', 'string', 'max:255'],
            'accounts.*.branch_id' => ['required', 'integer'],
        ]);

        DB::beginTransaction();
        try {
            $grn = Grn::whereKey($grn->id)->lockForUpdate()->firstOrFail();
            if ($grn->status != 7) {
                throw new \RuntimeException(__('GRN has already been processed.'));
            }

            $grn->load('items.purchaseProduct');

            if ($grn->items->isEmpty()) {
                throw new \Exception(__('Please add at least one item before approving GRN.'));
            }

            $this->applyStock($grn);
            $voucher = $this->grnVoucher->createForApproval($grn, [
                'voucher_type' => $request->input('voucher_type', 'JV'),
                'branches' => $request->input('branches'),
                'date' => $request->input('date'),
                'payment_date' => $request->input('payment_date'),
                'payment_mode' => $request->input('payment_mode'),
                'bank_id' => $request->input('bank_id'),
                'category_type_id' => $request->input('category_type_id'),
                'amount' => $request->input('amount'),
                'reference' => $request->input('reference'),
                'transaction_no' => $request->input('transaction_no'),
                'narration' => $request->input('narration'),
                'lines' => $request->input('accounts', []),
            ]);

            $grn->status = 8;
            $grn->approved_by = \Auth::id();
            $grn->voucher_id = $voucher->id;
            $grn->save();

            DB::commit();
            return redirect()->route('grn.show', $grn->id)->with('success', __('GRN approved by Accounts. Stock history and voucher have been created.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('accountsApprove failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function fwToHo(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status != 0 && $grn->status != 9 && $grn->status != 10) {
            return redirect()->back()->with('error', __('GRN cannot be forwarded from current status.'));
        }

        $grn->status = 5;
        $grn->save();

        return redirect()->back()->with('success', __('GRN forwarded to Head Office.'));
    }

    public function reject(Grn $grn)
    {
        $this->authorizeGrn($grn);

        if ($grn->status == 5 ) {
            $grn->status = 9;
            $grn->save();
            return redirect()->back()->with('success', __('GRN rejected by HO and sent back.'));
        }

        if ($grn->status == 7) {
            $grn->status = 10;
            $grn->save();
            return redirect()->back()->with('success', __('GRN rejected by Accounts and sent back.'));
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
            'purchase_order' => 'required|string|max:1000',
            'purchase_order_id' => [
                'nullable',
                'integer',
                Rule::exists('purchases', 'id')->where(function ($query) {
                    $query->where('created_by', \Auth::user()->creatorId());
                }),
            ],
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

        $vendors = Vender::optionsForCreator($user->creatorId());

        $warehouseRecords = $this->warehouseRecords();
        $warehouses = $warehouseRecords->pluck('name', 'id');
        $nextGrnNumber = $this->nextGrnNumber($user->creatorId());

    $products = ProductService::select('id', 'sku', 'name', 'purchase_price', 'purchase_description', 'description')
        ->where('created_by', $user->creatorId())
        ->where('type', '!=', 'service')
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

    private function chartAccountOptions(): array
    {
        return ChartOfAccount::select('id', DB::raw('CONCAT(code, " - ", name) AS code_name'))
            ->where('created_by', \Auth::user()->creatorId())
            ->where('is_enabled', 1)
            ->orderBy('code')
            ->pluck('code_name', 'id')
            ->toArray();
    }

    private function branchOptions()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('is_active', '1')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);

            return $branches;
        }

        return User::where('id', '=', \Auth::user()->ownedId())->where('is_active', '1')->get()->pluck('name', 'id');
    }

    private function bankAccountOptions()
    {
        $bankAccountQuery = BankAccount::query()->where('created_by', \Auth::user()->creatorId());
        if (\Schema::hasColumn('bank_accounts', 'owned_by')) {
            $bankAccountQuery->orWhere('owned_by', \Auth::user()->ownedId());
        }

        $bankAccounts = $bankAccountQuery->orderBy('bank_name')
            ->get()
            ->mapWithKeys(function ($bank) {
                $labelParts = array_filter([
                    $bank->bank_name ?? null,
                    $bank->holder_name ?? null,
                    $bank->account_number ?? null,
                ]);

                return [$bank->id => implode(' - ', $labelParts)];
            });
        $bankAccounts->prepend('Select Bank Name', '');

        return $bankAccounts;
    }

    private function voucherCategoryTypeOptions()
    {
        $voucherCategoryTypes = ProductServiceCategory::where('type', 'voucher')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id');
        $voucherCategoryTypes->prepend('Select Voucher Category Type', '');

        return $voucherCategoryTypes;
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
                'price' => !isset($item['price']) || $item['price'] === '' ? 0 : $item['price'],
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
            Utility::addProductStock(
                $grnItem->product_id,
                $quantity,
                'grn',
                $description,
                $grn->id,
                [
                    'warehouse_id' => $grn->warehouse_id,
                    'unit_price' => $grnItem->price ?? 0,
                    'sale_price' => 0,
                    'remaining_qty' => $quantity,
                    'condition' => $grnItem->condition,
                ]
            );
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
        if (!\Auth::user()->can('create grn')) {
            abort(403, __('Permission denied.'));
        }

        $user = \Auth::user();
        $purchases = Purchase::with(['vender', 'items'])
            ->where('created_by', $user->creatorId())
            ->where('status', Purchase::STATUS_FINALIZED)
            ->where('grn_converted', false)
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
        if (!\Auth::user()->can('create grn') && !\Auth::user()->can('edit grn')) {
            abort(403, __('Permission denied.'));
        }

        $purchase = Purchase::with('items.products')->findOrFail($id);

        if ($purchase->created_by != \Auth::user()->creatorId()) {
            abort(403, __('Permission denied.'));
        }

        if ($purchase->status != Purchase::STATUS_FINALIZED || $purchase->grn_converted) {
            return response()->json([], 422);
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
        return $this->purchaseReceiving->remaining($item, $exceptGrnId ? (int) $exceptGrnId : null);
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
            $grn->purchase_order = implode(', ', $orders);
            $grn->save();
        }
    }

    private function applyPurchaseReceived(Grn $grn): void
    {
        $this->purchaseReceiving->book($grn);
    }

    private function reversePurchaseReceived(Grn $grn): void
    {
        $this->purchaseReceiving->release($grn);
    }

    public function addVendorForm()
    {
        if (!\Auth::user()->can('create grn')) {
            abort(403, __('Permission denied.'));
        }

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
