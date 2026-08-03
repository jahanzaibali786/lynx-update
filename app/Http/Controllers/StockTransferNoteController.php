<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\ProductService;
use App\Models\Session as AcademicSession;
use App\Models\StockTransferNote;
use App\Models\StockTransferNoteItem;
use App\Models\StudyPack;
use App\Models\User;
use App\Models\Utility;
use App\Models\warehouse;
use App\Services\StockTransferNoteWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class StockTransferNoteController extends Controller
{
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    protected function stockTransferNoteNumber()
    {
        $user = Auth::user();

        $lastNumber = StockTransferNote::where('created_by', $user->creatorId())->max('stn_id');

        return $lastNumber ? ((int) $lastNumber + 1) : 1;
    }

    protected function storeUserMap()
    {
        return warehouse::with('assignedEmployee')
            ->where('created_by', Auth::user()->creatorId())
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

    protected function resolveStoreUserId($storeId)
    {
        if (empty($storeId)) {
            return null;
        }

        $store = warehouse::with('assignedEmployee')
            ->where('created_by', Auth::user()->creatorId())
            ->find($storeId);

        return $store?->assignedEmployee?->user_id;
    }

    protected function studyPackPayload(int $creatorId)
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
                        'type' => 'new',
                    ];
                })->values()->all(),
            ];
        })->values();
    }

    protected function stockTransferNoteItemsPayload($items)
    {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_id' => $item->product_id,
                'item_name' => $item->product ? (trim(($item->product->sku ?? '') . ' - ' . ($item->product->name ?? ''), ' -')) : '',
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'description' => $item->description ?? '',
                'type' => $item->type ?? 'new',
                'study_pack_id' => $item->study_pack_id,
                'study_pack_title' => $item->study_pack_title ?? '',
                'study_pack_class' => $item->study_pack_class ?? '',
            ];
        })->values();
    }

    protected function buildPublicView(StockTransferNote $invoice)
    {
        $user = User::find($invoice->created_by);
        $settings = Utility::settingsById($invoice->created_by);
        $items = $invoice->items;

        return view('stock_transfer_note.public_show', compact('user', 'settings', 'invoice', 'items'));
    }

    protected function buildAdminView(StockTransferNote $invoice)
    {
        $iteams = $invoice->items;
        $branch = $invoice->fromStore;
        $store = $invoice->toStore;
        $status = StockTransferNote::$statues;
        $settings = Utility::settingsById($invoice->created_by);

        return view('stock_transfer_note.show', compact('invoice', 'iteams', 'branch', 'store', 'status', 'settings'));
    }

    protected function renderIndexRow(StockTransferNote $invoice, $rowNumber = 1): string
    {
        $invoice->loadMissing(['fromStore', 'toStore']);

        return view('stock_transfer_note.partials.index_row', [
            'invoice' => $invoice,
            'rowNumber' => $rowNumber,
        ])->render();
    }

    protected function validateRequestedStock(array $items, int $creatorId): void
    {
        $requested = [];

        foreach ($items as $row) {
            $productId = (int) ($row['item'] ?? 0);
            $type = $row['type'] ?? 'new';
            $quantity = (int) ($row['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $key = $productId . '|' . $type;
            $requested[$key] = ($requested[$key] ?? 0) + $quantity;
        }

        foreach ($requested as $key => $quantity) {
            [$productId, $type] = explode('|', $key);

            $product = ProductService::where('created_by', $creatorId)->find((int) $productId);

            if (!$product) {
                throw new \RuntimeException(__('Selected product not found.'));
            }

            $available = match ($type) {
                'use' => (int) ($product->used_quantity ?? 0),
                'damage' => (int) ($product->damaged_quantity ?? 0),
                default => (int) ($product->quantity ?? 0),
            };

            if ($quantity > $available) {
                $productName = trim(($product->sku ? $product->sku . ' - ' : '') . $product->name);

                throw new \RuntimeException(__('Requested quantity for :product exceeds available :type stock (:available).', [
                    'product' => $productName,
                    'type' => ucfirst($type),
                    'available' => $available,
                ]));
            }
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->can('manage stock transfer note')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = StockTransferNote::with(['fromStore', 'toStore'])
            ->where('created_by', $user->creatorId());

        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        } else {
            $branches = collect();
            $query->where('owned_by', $user->ownedId());
        }

        $store = warehouse::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
        $store->prepend('Select Store', '');

        if ($request->filled('branches')) {
            $query->where('owned_by', $request->branches);
        }

        if ($request->filled('store')) {
            $query->where('store_from', $request->store);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('issue_date')) {
            $query->whereDate('issue_date', $request->issue_date);
        }

        $invoices = $query->orderByDesc('id')->get();
        $status = StockTransferNote::$statues;

        return view('stock_transfer_note.index', compact('invoices', 'store', 'status', 'branches'));
    }

    public function create($customerId = 0)
    {
        $user = Auth::user();

        if (!$user->can('create stock transfer note')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $store_from = warehouse::where(function ($query) use ($user) {
            $query->where('created_by', $user->creatorId())
                ->orWhere('owned_by', $user->creatorId());
        })
            ->orderBy('id')
            ->first();

        if (!$store_from) {
            $message = __('Please create or assign a source store before creating a Stock Transfer Note.');

            return request()->ajax()
                ? response()->json(['error' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        $store_to = warehouse::where(function ($query) use ($user) {
            $query->where('created_by', $user->creatorId())
                ->orWhere('owned_by', $user->creatorId());
        })
            ->where('id', '!=', $store_from->id)
            ->get()
            ->pluck('name', 'id');
        $storeUsers = $this->storeUserMap();
        $sessions = AcademicSession::where('created_by', $user->creatorId())
            ->orderByDesc('starting_date')
            ->pluck('year', 'id');
        $defaultSessionId = AcademicSession::where('created_by', $user->creatorId())
            ->where('active_status', 1)
            ->orderByDesc('starting_date')
            ->value('id') ?? $sessions->keys()->first();
        $product_services = ProductService::select(DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $user->creatorId())
            ->where('type', '!=', 'service')
            ->get()
            ->pluck('name', 'id');
        $product_services->prepend('Select item', '');
        $studyPackPayload = $this->studyPackPayload($user->creatorId());
        $invoice_number = $user->invoiceNumberFormat($this->stockTransferNoteNumber());
        $customFields = CustomField::where('created_by', $user->creatorId())
            ->where('module', 'invoice')
            ->get();

        if (request()->ajax()) {
            return view('stock_transfer_note.create', compact('invoice_number', 'product_services', 'store_from', 'store_to', 'storeUsers', 'sessions', 'defaultSessionId', 'customFields', 'studyPackPayload'))
                ->renderSections()['content'] ?? '';
        }

        return view('stock_transfer_note.create', compact('invoice_number', 'product_services', 'store_from', 'store_to', 'storeUsers', 'sessions', 'defaultSessionId', 'customFields', 'studyPackPayload'));
    }

    public function product(Request $request)
    {
        if (!Auth::user()->can('create stock transfer note') && !Auth::user()->can('edit stock transfer note')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $product = ProductService::where('created_by', Auth::user()->creatorId())->find($request->product_id);

        if (!$product) {
            return json_encode([]);
        }

        $data['product'] = $product;
        $data['unit'] = !empty($product->unit()) ? $product->unit()->name : '';
        $data['taxRate'] = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $data['totalAmount'] = $product->sale_price;
        $data['stock_new'] = $product->quantity ?? 0;
        $data['stock_used'] = $product->used_quantity ?? 0;
        $data['stock_damaged'] = $product->damaged_quantity ?? 0;

        return json_encode($data);
    }

    public function items(Request $request)
    {
        if (!Auth::user()->can('edit stock transfer note')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $note = StockTransferNote::where('created_by', Auth::user()->creatorId())->find($request->invoice_id);

        if (!$note) {
            return response()->json(['success' => false, 'message' => __('Stock Transfer Note Not Found.')], 404);
        }

        $item = StockTransferNoteItem::where('stn_id', $note->id)
            ->where('product_id', $request->product_id)
            ->first();

        return json_encode($item);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->can('create stock transfer note')) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Permission denied.')])
                : redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'issue_date' => 'required',
            'due_date' => 'required',
            'store_from' => 'required',
            'store_to' => 'required|different:store_from',
            'session_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|integer|exists:product_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.type' => 'required|in:new,use,damage',
            'items.*.description' => 'nullable|string',
            'items.*.study_pack_id' => 'nullable|integer',
            'items.*.study_pack_title' => 'nullable|string|max:255',
            'items.*.study_pack_class' => 'nullable|string|max:255',
            'shipping_via' => 'nullable|string|max:50',
            'stn_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            $message = $validator->getMessageBag()->first();

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $message])
                : redirect()->back()->with('error', $message);
        }

        if (!$this->storesBelongToCompany([$request->store_from, $request->store_to], $user->creatorId())) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected store is outside your company scope.')], 422)
                : redirect()->back()->with('error', __('Selected store is outside your company scope.'));
        }

        if (!$this->sessionBelongsToCompany($request->session_id, $user->creatorId())) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected session is outside your company scope.')], 422)
                : redirect()->back()->withInput()->with('error', __('Selected session is outside your company scope.'));
        }

        $studyPackIds = collect($request->items)
            ->pluck('study_pack_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validStudyPackIds = StudyPack::where('created_by', $user->creatorId())
            ->whereIn('id', $studyPackIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validStudyPackIds) !== $studyPackIds->count()) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected Study Pack is outside your company scope.')], 422)
                : redirect()->back()->withInput()->with('error', __('Selected Study Pack is outside your company scope.'));
        }

        $this->validateRequestedStock($request->items, $user->creatorId());

        $issueByUserId = $this->resolveStoreUserId($request->store_from);
        $receivedByUserId = $this->resolveStoreUserId($request->store_to);

        if (!$issueByUserId || !$receivedByUserId) {
            $message = __('Please assign an employee to both the source store and receiving store.');

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->withInput()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $noteNumber = $this->stockTransferNoteNumber();
            $note = new StockTransferNote();
            $note->stn_id = $noteNumber;
            $note->sto_id = $noteNumber;
            $note->issue_date = $request->issue_date;
            $note->due_date = $request->due_date;
            $note->approve_date = null;
            $note->ref_number = $request->ref_number;
            $note->status = 0;
            $note->category_id = $request->category_id ?? null;
            $note->shipping_via = $request->shipping_via;
            $note->stn_type = $request->stn_type;
            $note->store_from = $request->store_from;
            $note->store_to = $request->store_to;
            $note->session_id = $request->session_id;
            $note->approved_by = null;
            $note->issue_to = $request->store_to;
            $note->recived_by = $receivedByUserId;
            $note->issue_by = $issueByUserId;
            $note->owned_by = $user->ownedId();
            $note->created_by = $user->creatorId();
            $note->save();

            foreach ($request->items as $row) {
                $product = ProductService::where('created_by', $user->creatorId())->find($row['item']);
                $type = $row['type'] ?? 'new';
                $quantity = (int) ($row['quantity'] ?? 0);

                if (!$product) {
                    throw new \RuntimeException(__('Selected product not found.'));
                }

                if ($quantity <= 0) {
                    throw new \RuntimeException(__('Transfer quantity must be greater than zero.'));
                }

                $item = new StockTransferNoteItem();
                $item->stn_id = $note->id;
                $item->product_id = $row['item'];
                $item->quantity = $quantity;
                $item->price = $row['price'] ?? 0;
                $item->description = $row['description'] ?? '';
                $item->type = $type;
                $item->study_pack_id = !empty($row['study_pack_id']) ? (int) $row['study_pack_id'] : null;
                $item->study_pack_title = $row['study_pack_title'] ?? null;
                $item->study_pack_class = $row['study_pack_class'] ?? null;
                $item->save();

            }

            DB::commit();

            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => true,
                    'message' => __('Stock Transfer Note successfully created.'),
                    'row_html' => $this->renderIndexRow($note, 1),
                ]);
            }

            return redirect()->route('stock-transfer-note.show', Crypt::encrypt($note->id))
                ->with('success', __('Stock Transfer Note successfully created.'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $e->getMessage()])
                : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($ids)
    {
        $user = Auth::user();

        if (!$user->can('show stock transfer note')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        $invoice = StockTransferNote::with(['fromStore', 'toStore', 'items.product'])->find($id);

        if (!$invoice || $invoice->created_by != $user->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return $this->buildAdminView($invoice);
    }

    public function edit($ids)
    {
        $user = Auth::user();

        if (!$user->can('edit stock transfer note')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Stock Transfer Note Not Found.')], 404);
        }

        $invoice = StockTransferNote::with(['items'])->find($id);

        if (!$invoice || $invoice->created_by != $user->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (!in_array($invoice->status, [StockTransferNote::STATUS_DRAFT, StockTransferNote::STATUS_REJECTED], true)) {
            return response()->json(['error' => __('Only draft or rejected notes can be edited.')], 422);
        }

        $store_from = warehouse::where('id', $invoice->store_from)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->creatorId())
                    ->orWhere('owned_by', $user->creatorId());
            })
            ->first();

        if (!$store_from) {
            return response()->json(['error' => __('Source store not found.')], 404);
        }
        $store_to = warehouse::where('created_by', $user->creatorId())
            ->where('id', '!=', $store_from->id ?? 0)
            ->get()
            ->pluck('name', 'id');
        $storeUsers = $this->storeUserMap();
        $sessions = AcademicSession::where('created_by', $user->creatorId())
            ->orderByDesc('starting_date')
            ->pluck('year', 'id');
        $product_services = ProductService::select(DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', $user->creatorId())
            ->where('type', '!=', 'service')
            ->get()
            ->pluck('name', 'id');
        $studyPackPayload = $this->studyPackPayload($user->creatorId());
        $existingItemsPayload = $this->stockTransferNoteItemsPayload($invoice->items->load('product'));
        $customFields = CustomField::where('created_by', $user->creatorId())
            ->where('module', 'invoice')
            ->get();
        $invoice_number = $user->invoiceNumberFormat($invoice->invoice_id);

        if (request()->ajax()) {
            return view('stock_transfer_note.edit', compact('invoice', 'store_from', 'store_to', 'storeUsers', 'sessions', 'product_services', 'customFields', 'invoice_number', 'studyPackPayload', 'existingItemsPayload'))
                ->renderSections()['content'] ?? '';
        }

        return view('stock_transfer_note.edit', compact('invoice', 'store_from', 'store_to', 'storeUsers', 'sessions', 'product_services', 'customFields', 'invoice_number', 'studyPackPayload', 'existingItemsPayload'));
    }

    public function update(Request $request, $ids)
    {
        $user = Auth::user();

        if (!$user->can('edit stock transfer note')) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Permission denied.')])
                : redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Stock Transfer Note Not Found.')])
                : redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        $invoice = StockTransferNote::with('items')->find($id);

        if (!$invoice || $invoice->created_by != $user->creatorId()) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Permission denied.')])
                : redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!in_array($invoice->status, [StockTransferNote::STATUS_DRAFT, StockTransferNote::STATUS_REJECTED], true)) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Only draft or rejected notes can be edited.')], 422)
                : redirect()->back()->with('error', __('Only draft or rejected notes can be edited.'));
        }

        $validator = \Validator::make($request->all(), [
            'issue_date' => 'required',
            'due_date' => 'required',
            'store_from' => 'required',
            'store_to' => 'required|different:store_from',
            'session_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|integer|exists:product_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.type' => 'required|in:new,use,damage',
            'items.*.description' => 'nullable|string',
            'items.*.study_pack_id' => 'nullable|integer',
            'items.*.study_pack_title' => 'nullable|string|max:255',
            'items.*.study_pack_class' => 'nullable|string|max:255',
            'shipping_via' => 'nullable|string|max:50',
            'stn_type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            $message = $validator->getMessageBag()->first();

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $message])
                : redirect()->back()->with('error', $message);
        }

        if (!$this->storesBelongToCompany([$request->store_from, $request->store_to], $user->creatorId())) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected store is outside your company scope.')], 422)
                : redirect()->back()->with('error', __('Selected store is outside your company scope.'));
        }

        if (!$this->sessionBelongsToCompany($request->session_id, $user->creatorId())) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected session is outside your company scope.')], 422)
                : redirect()->back()->withInput()->with('error', __('Selected session is outside your company scope.'));
        }

        $studyPackIds = collect($request->items)
            ->pluck('study_pack_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $validStudyPackIds = StudyPack::where('created_by', $user->creatorId())
            ->whereIn('id', $studyPackIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($validStudyPackIds) !== $studyPackIds->count()) {
            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => __('Selected Study Pack is outside your company scope.')], 422)
                : redirect()->back()->withInput()->with('error', __('Selected Study Pack is outside your company scope.'));
        }

        $this->validateRequestedStock($request->items, $user->creatorId());

        $issueByUserId = $this->resolveStoreUserId($request->store_from);
        $receivedByUserId = $this->resolveStoreUserId($request->store_to);

        if (!$issueByUserId || !$receivedByUserId) {
            $message = __('Please assign an employee to both the source store and receiving store.');

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $message], 422)
                : redirect()->back()->withInput()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $invoice->items()->delete();

            $invoice->issue_date = $request->issue_date;
            $invoice->due_date = $request->due_date;
            $invoice->ref_number = $request->ref_number;
            $invoice->shipping_via = $request->shipping_via;
            $invoice->stn_type = $request->stn_type;
            $invoice->store_from = $request->store_from;
            $invoice->store_to = $request->store_to;
            $invoice->session_id = $request->session_id;
            $invoice->issue_by = $issueByUserId;
            $invoice->recived_by = $receivedByUserId;
            $invoice->save();

            CustomField::saveData($invoice, $request->customField);

            foreach ($request->items as $row) {
                $product = ProductService::where('created_by', $user->creatorId())->find($row['item']);
                $type = $row['type'] ?? 'new';
                $quantity = (int) ($row['quantity'] ?? 0);

                if (!$product) {
                    throw new \RuntimeException(__('Selected product not found.'));
                }

                if ($quantity <= 0) {
                    throw new \RuntimeException(__('Transfer quantity must be greater than zero.'));
                }

                $item = new StockTransferNoteItem();
                $item->stn_id = $invoice->id;
                $item->product_id = $row['item'];
                $item->quantity = $quantity;
                $item->price = $row['price'] ?? 0;
                $item->description = $row['description'] ?? '';
                $item->type = $type;
                $item->study_pack_id = !empty($row['study_pack_id']) ? (int) $row['study_pack_id'] : null;
                $item->study_pack_title = $row['study_pack_title'] ?? null;
                $item->study_pack_class = $row['study_pack_class'] ?? null;
                $item->save();

            }

            DB::commit();

            if ($this->wantsJson($request)) {
                return response()->json(['success' => true, 'message' => __('Stock Transfer Note successfully updated.')]);
            }

            return redirect()->route('stock-transfer-note.show', Crypt::encrypt($invoice->id))
                ->with('success', __('Stock Transfer Note successfully updated.'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->wantsJson($request)
                ? response()->json(['success' => false, 'message' => $e->getMessage()])
                : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('delete stock transfer note')) {
            return $this->wantsJson(request())
                ? response()->json(['success' => false, 'message' => __('Permission denied.')], 403)
                : redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $invoiceId = Crypt::decrypt($id);
        } catch (\Throwable $e) {
            $invoiceId = is_numeric($id) ? (int) $id : 0;
        }

        $invoice = StockTransferNote::with('items')
            ->where('created_by', Auth::user()->creatorId())
            ->find($invoiceId);

        if (!$invoice) {
            return $this->wantsJson(request())
                ? response()->json(['success' => false, 'message' => __('Stock Transfer Note Not Found.')], 404)
                : redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        if (!in_array($invoice->status, [StockTransferNote::STATUS_DRAFT, StockTransferNote::STATUS_REJECTED], true)) {
            return $this->wantsJson(request())
                ? response()->json(['success' => false, 'message' => __('Only draft or rejected stock transfer notes can be deleted.')], 422)
                : redirect()->back()->with('error', __('Only draft or rejected stock transfer notes can be deleted.'));
        }

        DB::beginTransaction();
        try {
            $invoice->items()->delete();

            $invoice->delete();
            DB::commit();

            if ($this->wantsJson(request())) {
                return response()->json(['success' => true, 'message' => __('Stock Transfer Note deleted successfully.')]);
            }

            return redirect()->route('stock-transfer-note.index')->with('success', __('Stock Transfer Note deleted successfully.'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->wantsJson(request())
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 500)
                : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroyProduct(Request $request)
    {
        if (!Auth::user()->can('edit stock transfer note')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $itemId = $request->id;

        if (empty($itemId)) {
            return response()->json(['success' => false, 'message' => __('Item not found.')], 404);
        }

        $item = StockTransferNoteItem::find($itemId);

        if (!$item) {
            return response()->json(['success' => false, 'message' => __('Item not found.')], 404);
        }

        $note = StockTransferNote::where('created_by', Auth::user()->creatorId())->find($item->stn_id);

        if (!$note || !in_array($note->status, [StockTransferNote::STATUS_DRAFT, StockTransferNote::STATUS_REJECTED], true)) {
            return response()->json(['success' => false, 'message' => __('Only draft or rejected notes can be edited.')], 422);
        }

        DB::beginTransaction();
        try {
            $item->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => __('Item removed successfully.')]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function draftDemandOrders(Request $request)
    {
        return response('<div class="p-3 text-muted">' . __('No demand order picker is configured for Stock Transfer Note yet.') . '</div>');
    }

    public function demandOrderItems($id)
    {
        return response()->json([]);
    }

    public function invoice($invoice_id)
    {
        try {
            $id = Crypt::decrypt($invoice_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        $invoice = StockTransferNote::with(['fromStore', 'toStore', 'items.product'])->find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return $this->buildPublicView($invoice);
    }

    public function invoiceLink($invoiceId)
    {
        return $this->invoice($invoiceId);
    }

    public function forwardToHo(Request $request, $ids, StockTransferNoteWorkflowService $workflow)
    {
        return $this->runWorkflowAction($request, $ids, 'forward stock transfer note', function ($note, $user) use ($workflow) {
            return $workflow->forward($note, $user);
        }, __('Stock Transfer Note sent for approval successfully.'));
    }

    public function rejectByHo(Request $request, $ids, StockTransferNoteWorkflowService $workflow)
    {
        return $this->runWorkflowAction($request, $ids, 'reject stock transfer note', function ($note, $user) use ($workflow) {
            return $workflow->reject($note, $user);
        }, __('Stock Transfer Note rejected.'));
    }

    public function approveByHo(Request $request, $ids, StockTransferNoteWorkflowService $workflow)
    {
        return $this->runWorkflowAction($request, $ids, 'approve stock transfer note', function ($note, $user) use ($workflow) {
            return $workflow->approve($note, $user);
        }, __('Stock Transfer Note approved and stock transferred successfully.'));
    }

    public function issue(Request $request, $ids, StockTransferNoteWorkflowService $workflow)
    {
        return $this->runWorkflowAction($request, $ids, 'issue stock transfer note', function ($note, $user) use ($workflow) {
            return $workflow->issue($note, $user);
        }, __('Stock Transfer Note issued successfully.'));
    }

    private function runWorkflowAction(Request $request, $ids, string $permission, callable $action, string $message)
    {
        $user = Auth::user();

        if (!$user->can($permission)) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        try {
            $id = Crypt::decrypt($ids);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => __('Stock Transfer Note Not Found.')], 404);
        }

        $noteQuery = StockTransferNote::where('created_by', $user->creatorId());

        if ($user->type !== 'company') {
            $noteQuery->where('owned_by', $user->ownedId());
        }

        $note = $noteQuery->find($id);

        if (!$note) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        try {
            $action($note, $user);

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function storesBelongToCompany(array $storeIds, int $creatorId): bool
    {
        $storeIds = array_values(array_unique(array_map('intval', $storeIds)));

        if (count($storeIds) !== 2) {
            return false;
        }

        return warehouse::whereIn('id', $storeIds)
            ->where(function ($query) use ($creatorId) {
                $query->where('created_by', $creatorId)
                    ->orWhere('owned_by', $creatorId);
            })
            ->count() === 2;
    }

    private function sessionBelongsToCompany($sessionId, int $creatorId): bool
    {
        return AcademicSession::whereKey($sessionId)
            ->where('created_by', $creatorId)
            ->exists();
    }

    public function print($invoiceId)
    {
        $user = Auth::user();

        if (!$user->can('show stock transfer note')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id = Crypt::decrypt($invoiceId);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        $query = StockTransferNote::with(['fromStore.branch', 'toStore.branch', 'items.product'])
            ->where('created_by', $user->creatorId());

        if ($user->type !== 'company') {
            $query->where('owned_by', $user->ownedId());
        }

        $invoice = $query->find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', __('Stock Transfer Note Not Found.'));
        }

        $settings = Utility::settingsById($invoice->created_by);
        $unitNames = DB::table('product_service_units')
            ->whereIn('id', $invoice->items->pluck('product.unit_id')->filter()->unique())
            ->pluck('name', 'id');
        $session = $invoice->academicSession?->year;

        if (!$session) {
            $session = AcademicSession::where('created_by', $invoice->created_by)
                ->whereDate('starting_date', '<=', $invoice->issue_date)
                ->whereDate('ending_date', '>=', $invoice->issue_date)
                ->value('year');
        }
        $issuedBy = User::find($invoice->issue_by);
        $receivedBy = User::find($invoice->recived_by);

        return view('stock_transfer_note.print', compact(
            'invoice',
            'settings',
            'unitNames',
            'session',
            'issuedBy',
            'receivedBy'
        ));
    }
}
