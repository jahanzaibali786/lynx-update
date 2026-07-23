<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Exports\ProductServiceExport;
use App\Exports\ProductServiceReportExport;
use App\Imports\ProductServiceImport;
use App\Models\Product;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceSubCategory;
use App\Models\ProductServiceUnit;
use App\Models\Tax;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use App\Models\WarehouseProduct;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;




class ProductServiceController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage product & service')) {
            // Branch and employee filters
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
                ->where('type', 'product & service')
                ->get()
                ->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $subcategoryQuery = ProductServiceSubCategory::where('created_by', \Auth::user()->creatorId());
            if (!empty($request->category)) {
                $subcategoryQuery->where('category_id', $request->category);
            }
            $subcategory = $subcategoryQuery->get()->pluck('name', 'id');
            $subcategory->prepend('Select Sub-Category', '');
            $query = ProductService::with(['category', 'subcategory'])->where('created_by', \Auth::user()->creatorId());
            // dd($query->get());
            if (!empty($request->category)) {
                $query->where('category_id', $request->category);
            }
            if (!empty($request->item_type)) {
                $query->where('item_type', $request->item_type);
            }
            if (!empty($request->subcategory)) {
                $query->where('sub_category_id', $request->subcategory);
            }
            if ($request->has('export') && $request->export == 'excel') {
                $productServices = $query->get();
                return Excel::download(new ProductServiceReportExport($productServices), 'product_service_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $productServices = $query->get();
                return Excel::download(new ProductServiceReportExport($productServices), 'product_service_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            $productServices = $query->orderBy('name')->get();

            $itemTypes = [
                '' => 'Select Type',
                'inventory_part' => 'Inventory Part',
                'non_inventory_part' => 'Non-Inventory Part',
                'service' => 'Service',
            ];

            return view('productservice.index', compact('productServices', 'category', 'subcategory', 'itemTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $unit = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $unit->prepend('Select Unit', '');
            $tax = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $parentItems = ProductService::where('created_by', \Auth::user()->creatorId())
                ->where(function ($query) {
                    $query->whereNull('is_subitem')->orWhere('is_subitem', 0);
                })
                ->orderBy('name')
                ->pluck('name', 'id');
            $parentItems->prepend('Select Parent Item', '');
            $incomeChartAccounts = $this->chartAccountsByType(['Income']);
            $expenseChartAccounts = $this->chartAccountsByType(['Cost of Goods Sold', 'Costs of Goods Sold']);
            $inventoryAssetAccounts = $this->chartAccountsByType(['Assets']);
            $itemTypes = [
                'inventory_part' => 'Inventory Part',
                'non_inventory_part' => 'Non-Inventory Part',
                'service' => 'Service',
            ];
            $viewData = compact('category', 'unit', 'tax', 'customFields', 'incomeChartAccounts', 'expenseChartAccounts', 'inventoryAssetAccounts', 'parentItems', 'itemTypes');

            if ($request->ajax()) {
                $html = view('productservice.create', $viewData)->renderSections()['content'] ?? '';
                return response('<div class="modal-body product-service-create-modal">' . $html . '</div>');
            }

            return view('productservice.create', $viewData);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('create product & service')) {

            $rules = [
                'name' => 'required',
                'sku' => [
                    'required',
                    Rule::unique('product_services')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->creatorId());
                    })
                ],
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required_if:item_type,inventory_part|nullable|numeric',
                'category_id' => 'required',
                'sub_category_id' => 'required',
                'unit_id' => 'required',
                'item_type' => 'required',
                'sale_chartaccount_id' => 'required',
                'expense_chartaccount_id' => 'required_if:item_type,inventory_part',
                'inventory_asset_account_id' => 'required_if:item_type,inventory_part',
                'parent_id' => 'required_if:is_subitem,1',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $messages->first(),
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            $productService = DB::transaction(function () use ($request) {
                $productService = new ProductService();
                $productService->name = $request->name;
                $productService->description = $request->sales_description ?: $request->description;
                $productService->sku = $request->sku;
                $productService->item_type = $request->item_type;
                if ($request->item_type === 'service') {
                    $productService->manufacturer_part_number = null;
                    $productService->purchase_description = null;
                    $productService->purchase_price = 0;
                    $productService->expense_chartaccount_id = 0;
                    $productService->inventory_asset_account_id = 0;
                } elseif ($request->item_type === 'non_inventory_part') {
                    $productService->manufacturer_part_number = $request->manufacturer_part_number;
                    $productService->purchase_description = null;
                    $productService->purchase_price = 0;
                    $productService->expense_chartaccount_id = 0;
                    $productService->inventory_asset_account_id = 0;
                } else {
                    $productService->manufacturer_part_number = $request->manufacturer_part_number;
                    $productService->purchase_description = $request->purchase_description;
                    $productService->purchase_price = $request->purchase_price;
                    $productService->expense_chartaccount_id = $request->expense_chartaccount_id;
                    $productService->inventory_asset_account_id = $request->inventory_asset_account_id;
                }
                $productService->is_subitem = $request->has('is_subitem') ? 1 : 0;
                $productService->parent_id = $productService->is_subitem ? $request->parent_id : null;
                $productService->sales_description = $request->sales_description;
                $productService->sale_price = $request->sale_price;
                $productService->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
                $productService->unit_id = $request->unit_id;
                $productService->quantity = $request->quantity ?? 0;
                $productService->used_quantity = $request->used_quantity ?? 0;
                $productService->damaged_quantity = $request->damaged_quantity ?? 0;
                $productService->type = $request->item_type === 'service' ? 'service' : 'product';
                $productService->sale_chartaccount_id = $request->sale_chartaccount_id;
                $productService->category_id = $request->category_id;
                $productService->sub_category_id = $request->sub_category_id;

                if (!empty($request->pro_image)) {
                    $image_size = $request->file('pro_image')->getSize();
                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                    if ($result == 1) {
                        if ($productService->pro_image) {
                            $path = storage_path('uploads/pro_image' . $productService->pro_image);
                        }
                        $fileName = $request->pro_image->getClientOriginalName();
                        $productService->pro_image = $fileName;
                        $dir = 'uploads/pro_image';
                        $path = Utility::upload_file($request, 'pro_image', $fileName, $dir, []);
                    }
                }

                $productService->owned_by = \Auth::user()->ownedId();
                $productService->created_by = \Auth::user()->creatorId();
                $productService->save();
                CustomField::saveData($productService, $request->customField);

                if ($productService->quantity > 0) {
                    $desc = $productService->quantity . ' New opening balance added against product code ' . $productService->sku;
                    Utility::addProductStock($productService->id, $productService->quantity, 'opening_balance', $desc, 0);
                }
                if ($productService->used_quantity > 0) {
                    $desc = $productService->used_quantity . ' Used opening balance added against product code ' . $productService->sku;
                    Utility::addProductStock($productService->id, $productService->used_quantity, 'opening_balance', $desc, 0);
                }
                if ($productService->damaged_quantity > 0) {
                    $desc = $productService->damaged_quantity . ' Damaged opening balance added against product code ' . $productService->sku;
                    Utility::addProductStock($productService->id, $productService->damaged_quantity, 'opening_balance', $desc, 0);
                }

                return $productService;
            });

            if ($request->expectsJson() || $request->ajax()) {
                $productService->load(['category', 'subcategory']);

                return response()->json([
                    'success' => true,
                    'message' => __('Product successfully created.'),
                    'id' => $productService->id,
                    'row_html' => view('productservice.partials.row', [
                        'productService' => $productService,
                        'index' => 1,
                    ])->render(),
                ]);
            }

            return redirect()->route('productservice.index')->with('success', __('Product successfully created.'));
        } else {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }

            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        $productService = ProductService::with([
            'category',
            'subcategory',
            'parentItem',
            'saleAccount',
            'expenseAccount',
            'inventoryAssetAccount',
        ])->findOrFail($id);

        if (!\Auth::user()->can('show product & service') || $productService->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $warehouseProducts = WarehouseProduct::with('warehousedetail')
            ->where('product_id', $productService->id)
            ->where('created_by', \Auth::user()->creatorId())
            ->get();
        $productService->customField = CustomField::getData($productService, 'product');
        $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();

        $itemTypes = [
            'inventory_part' => 'Inventory Part',
            'non_inventory_part' => 'Non-Inventory Part',
            'service' => 'Service',
        ];

        return view('productservice.show', compact('productService', 'warehouseProducts', 'itemTypes', 'customFields'));
    }

    public function edit(Request $request, $id)
    {
        $productService = ProductService::find($id);

        if (\Auth::user()->can('edit product & service')) {
            if ($productService->created_by == \Auth::user()->creatorId()) {
                $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');
                $category->prepend('Select Category', '');
                $unit = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $unit->prepend('Select Unit', '');
                $tax = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

                $productService->customField = CustomField::getData($productService, 'product');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
                $productService->tax_id = !empty($productService->tax_id) ? explode(',', $productService->tax_id) : [];
                $parentItems = ProductService::where('created_by', \Auth::user()->creatorId())
                    ->where('id', '!=', $productService->id)
                    ->where(function ($query) {
                        $query->whereNull('is_subitem')->orWhere('is_subitem', 0);
                    })
                    ->orderBy('name')
                    ->pluck('name', 'id');
                $parentItems->prepend('Select Parent Item', '');
                $incomeChartAccounts = $this->chartAccountsByType(['Income']);
                $expenseChartAccounts = $this->chartAccountsByType(['Cost of Goods Sold', 'Costs of Goods Sold']);
                $inventoryAssetAccounts = $this->chartAccountsByType(['Assets']);
                $itemTypes = [
                    'inventory_part' => 'Inventory Part',
                    'non_inventory_part' => 'Non-Inventory Part',
                    'service' => 'Service',
                ];
                $viewData = compact('category', 'unit', 'tax', 'productService', 'customFields', 'incomeChartAccounts', 'expenseChartAccounts', 'inventoryAssetAccounts', 'parentItems', 'itemTypes');

                if ($request->ajax()) {
                    $html = view('productservice.edit', $viewData)->renderSections()['content'] ?? '';
                    return response('<div class="modal-body product-service-create-modal">' . $html . '</div>');
                }

                return view('productservice.edit', $viewData);
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {

        if (\Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);
            if ($productService->created_by == \Auth::user()->creatorId()) {
                $rules = [
                    'name' => 'required',
                    'sku' => [
                        'required',
                        Rule::unique('product_services')->ignore($productService->id)->where(function ($query) {
                            return $query->where('created_by', \Auth::user()->creatorId());
                        })
                    ],
                    'sale_price' => 'required|numeric',
                    'purchase_price' => 'required_if:item_type,inventory_part|nullable|numeric',
                    'category_id' => 'required',
                    'sub_category_id' => 'required',
                    'unit_id' => 'required',
                    'item_type' => 'required',
                    'sale_chartaccount_id' => 'required',
                    'expense_chartaccount_id' => 'required_if:item_type,inventory_part',
                    'inventory_asset_account_id' => 'required_if:item_type,inventory_part',
                    'parent_id' => 'required_if:is_subitem,1',

                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $messages->first(),
                            'errors' => $validator->errors(),
                        ], 422);
                    }

                    return redirect()->back()->with('error', $messages->first())->withInput();
                }

                $productService->name = $request->name;
                $productService->description = $request->sales_description ?: $request->description;
                $productService->sku = $request->sku;
                $productService->item_type = $request->item_type;
                if ($request->item_type === 'service') {
                    $productService->manufacturer_part_number = null;
                    $productService->purchase_description = null;
                    $productService->purchase_price = 0;
                    $productService->expense_chartaccount_id = 0;
                    $productService->inventory_asset_account_id = 0;
                } elseif ($request->item_type === 'non_inventory_part') {
                    $productService->manufacturer_part_number = $request->manufacturer_part_number;
                    $productService->purchase_description = null;
                    $productService->purchase_price = 0;
                    $productService->expense_chartaccount_id = 0;
                    $productService->inventory_asset_account_id = 0;
                } else {
                    $productService->manufacturer_part_number = $request->manufacturer_part_number;
                    $productService->purchase_description = $request->purchase_description;
                    $productService->purchase_price = $request->purchase_price;
                    $productService->expense_chartaccount_id = $request->expense_chartaccount_id;
                    $productService->inventory_asset_account_id = $request->inventory_asset_account_id;
                }
                
                $productService->sales_description = $request->sales_description;
                $productService->sale_price = $request->sale_price;
                $productService->is_subitem = $request->has('is_subitem') ? 1 : 0;
                $productService->parent_id = $productService->is_subitem ? $request->parent_id : null;
                $productService->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
                $productService->unit_id = $request->unit_id;

                // if(!empty($request->quantity))
                // {
                //     $productService->quantity   = $request->quantity;
                // }
                // else{
                //     $productService->quantity   = 0;
                // }
                $productService->type = $request->item_type === 'service' ? 'service' : 'product';
                $productService->sale_chartaccount_id = $request->sale_chartaccount_id;
                $productService->category_id = $request->category_id;
                $productService->sub_category_id = $request->sub_category_id;

                if (!empty($request->pro_image)) {
                    //storage limit
                    $file_path = '/uploads/pro_image/' . $productService->pro_image;
                    $image_size = $request->file('pro_image')->getSize();
                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                    if ($result == 1) {
                        if ($productService->pro_image) {
                            Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                            $path = storage_path('uploads/pro_image' . $productService->pro_image);
                            //                            if(file_exists($path))
//                            {
//                                \File::delete($path);
//                            }
                        }
                        $fileName = $request->pro_image->getClientOriginalName();
                        $productService->pro_image = $fileName;
                        $dir = 'uploads/pro_image';
                        $path = Utility::upload_file($request, 'pro_image', $fileName, $dir, []);
                    }

                }

                $productService->created_by = \Auth::user()->creatorId();
                $productService->save();
                CustomField::saveData($productService, $request->customField);

                if ($request->expectsJson() || $request->ajax()) {
                    $productService->load(['category', 'subcategory']);

                    return response()->json([
                        'success' => true,
                        'message' => __('Product successfully updated.'),
                        'id' => $productService->id,
                        'row_html' => view('productservice.partials.row', [
                            'productService' => $productService,
                            'index' => '',
                        ])->render(),
                    ]);
                }

                return redirect()->route('productservice.index')->with('success', __('Product successfully updated.'));
            } else {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Permission denied.'),
                    ], 403);
                }

                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }

            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    private function productDeleteBlockers($productId)
    {
        $checks = [
            'Purchase' => [
                ['purchase_products', 'product_id'],
                ['bill_products', 'product_id'],
            ],
            'GRN' => [
                ['grn_items', 'product_id'],
            ],
            'Invoice' => [
                ['invoice_products', 'product_id'],
                ['pos_products', 'product_id'],
            ],
            'Purchase Order' => [
                ['branch_purchase_items', 'product_id'],
            ],
            'Stock History' => [
                ['stock_reports', 'product_id'],
                ['warehouse_products', 'product_id'],
                ['warehouse_transfers', 'product_id'],
            ],
            'Return Order' => [
                ['return_order_products', 'product_id'],
            ],
        ];

        $blockers = [];

        foreach ($checks as $label => $tables) {
            foreach ($tables as [$table, $column]) {
                if (
                    Schema::hasTable($table) &&
                    Schema::hasColumn($table, $column) &&
                    DB::table($table)->where($column, $productId)->exists()
                ) {
                    $blockers[] = $label;
                    break;
                }
            }
        }

        return $blockers;
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $productService = ProductService::find($id);
            if (empty($productService)) {
                return redirect()->back()->with('error', __('Product not found.'));
            }

            if ($productService->created_by == \Auth::user()->creatorId()) {
                $blockers = $this->productDeleteBlockers($productService->id);
                if (!empty($blockers)) {
                    return redirect()->back()->with('error', __('Product cannot be deleted because it is used in: ') . implode(', ', $blockers) . '.');
                }

                if (!empty($productService->pro_image)) {
                    //storage limit
                    $file_path = '/uploads/pro_image/' . $productService->pro_image;
                    $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                }

                $productService->delete();

                return redirect()->route('productservice.index')->with('success', __('Product successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'product_service_' . date('Y-m-d i:h:s');
        $data = Excel::download(new ProductServiceExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile()
    {
        return view('productservice.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $products = (new ProductServiceImport)->toArray(request()->file('file'))[0];
        $totalProduct = count($products) - 1;
        $errorArray = [];
        for ($i = 1; $i <= count($products) - 1; $i++) {
            $items = $products[$i];

            $taxes = explode(';', $items[5]);

            $taxesData = [];
            foreach ($taxes as $tax) {
                $taxes = Tax::where('id', $tax)->first();
                //                $taxesData[] = $taxes->id;
                $taxesData[] = !empty($taxes->id) ? $taxes->id : 0;


            }

            $taxData = implode(',', $taxesData);
            //            dd($taxData);

            if (!empty($productBySku)) {
                $productService = $productBySku;
            } else {
                $productService = new ProductService();
            }

            $productService->name = $items[0];
            $productService->sku = $items[1];
            $productService->sale_price = $items[2];
            $productService->purchase_price = $items[3];
            $productService->quantity = $items[4];
            $productService->tax_id = $items[5];
            $productService->category_id = $items[6];
            $productService->unit_id = $items[7];
            $productService->type = $items[8];
            $productService->description = $items[9];
            $productService->created_by = \Auth::user()->creatorId();

            if (empty($productService)) {
                $errorArray[] = $productService;
            } else {
                $productService->save();
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {

            $data['status'] = 'success';
            $data['msg'] = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalProduct . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function warehouseDetail($id)
    {
        $products = WarehouseProduct::with('warehousedetail')->where('product_id', '=', $id)->where('created_by', '=', \Auth::user()->creatorId())->get();
        return view('productservice.detail', compact('products'));
    }

    public function searchProducts(Request $request)
    {

        $lastsegment = $request->session_key;

        if (Auth::user()->can('manage pos') && $request->ajax() && isset($lastsegment) && !empty($lastsegment)) {

            $output = "";
            if ($request->war_id == '0') {
                $ids = WarehouseProduct::where('warehouse_id', 1)->get()->pluck('product_id')->toArray();

                if ($request->cat_id !== '' && $request->search == '') {
                    if ($request->cat_id == '0') {
                        $products = ProductService::getallproducts()->whereIn('product_services.id', $ids)->get();

                    } else {
                        $products = ProductService::getallproducts()->where('category_id', $request->cat_id)->whereIn('product_services.id', $ids)->get();
                    }

                } else {
                    if ($request->cat_id == '0') {
                        $products = ProductService::getallproducts()->where('product_services.name', 'LIKE', "%{$request->search}%")->get();
                    } else {
                        $products = ProductService::getallproducts()->where('product_services.name', 'LIKE', "%{$request->search}%")->orWhere('category_id', $request->cat_id)->get();
                    }
                }
            } else {
                $ids = WarehouseProduct::where('warehouse_id', $request->war_id)->get()->pluck('product_id')->toArray();

                if ($request->cat_id == '0') {
                    $products = ProductService::getallproducts()->whereIn('product_services.id', $ids)->get();

                } else {
                    $products = ProductService::getallproducts()->whereIn('product_services.id', $ids)->where('category_id', $request->cat_id)->get();

                }

            }


            if (count($products) > 0) {
                foreach ($products as $key => $product) {
                    $quantity = $product->warehouseProduct($product->id, $request->war_id != 0 ? $request->war_id : 7);

                    $unit = (!empty($product) && !empty($product->unit())) ? $product->unit()->name : '';

                    if (!empty($product->pro_image)) {
                        $image_url = ('uploads/pro_image') . '/' . $product->pro_image;
                    } else {
                        $image_url = ('uploads/pro_image') . '/default.png';
                    }
                    if ($request->session_key == 'purchases') {
                        $productprice = $product->purchase_price != 0 ? $product->purchase_price : 0;
                    } else if ($request->session_key == 'pos') {
                        $productprice = $product->sale_price != 0 ? $product->sale_price : 0;

                    } else {
                        $productprice = $product->sale_price != 0 ? $product->sale_price : $product->purchase_price;
                    }

                    $output .= '

                            <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4 col-12">
                                <div class="tab-pane fade show active toacart w-100" data-url="' . url('add-to-cart/' . $product->id . '/' . $lastsegment) . '">
                                    <div class="position-relative card">
                                        <img alt="Image placeholder" src="' . asset(Storage::url($image_url)) . '" class="card-image avatar shadow hover-shadow-lg" style=" height: 6rem; width: 100%;">
                                        <div class="p-0 custom-card-body card-body d-flex ">
                                            <div class="card-body my-2 p-2 text-left card-bottom-content">
                                                <h6 class="mb-2 text-dark product-title-name">' . $product->name . '</h6>
                                                <small class="badge badge-primary mb-0">' . Auth::user()->priceFormat($productprice) . '</small>

                                                <small class="top-badge badge badge-danger mb-0">' . $quantity . ' ' . $unit . '</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    ';

                }

                return Response($output);

            } else {
                $output = '<div class="card card-body col-12 text-center">
                    <h5>' . __("No Product Available") . '</h5>
                    </div>';
                return Response($output);
            }
        }
    }

    public function addToCart(Request $request, $id, $session_key)
    {


        if (Auth::user()->can('manage product & service') && $request->ajax()) {
            $product = ProductService::find($id);
            $productquantity = 0;

            if ($product) {
                $productquantity = $product->getTotalProductQuantity();
            }

            if (!$product || ($session_key == 'pos' && $productquantity == 0)) {
                return response()->json(
                    [
                        'code' => 404,
                        'status' => 'Error',
                        'error' => __('This product is out of stock!'),
                    ],
                    404
                );
            }

            $productname = $product->name;

            if ($session_key == 'purchases') {

                $productprice = $product->purchase_price != 0 ? $product->purchase_price : 0;
            } else if ($session_key == 'pos') {

                $productprice = $product->sale_price != 0 ? $product->sale_price : 0;
            } else {

                $productprice = $product->sale_price != 0 ? $product->sale_price : $product->purchase_price;
            }

            $originalquantity = (int) $productquantity;

            $taxes = Utility::tax($product->tax_id);

            $totalTaxRate = Utility::totalTaxRate($product->tax_id);

            $product_tax = '';
            $product_tax_id = [];
            foreach ($taxes as $tax) {
                $product_tax .= !empty($tax) ? "<span class='badge badge-primary'>" . $tax->name . ' (' . $tax->rate . '%)' . "</span><br>" : '';
                $product_tax_id[] = !empty($tax) ? $tax->id : 0;
            }

            if (empty($product_tax)) {
                $product_tax = "-";
            }
            $producttax = $totalTaxRate;


            $tax = ($productprice * $producttax) / 100;

            $subtotal = $productprice + $tax;
            //            dd($subtotal);
            $cart = session()->get($session_key);
            //            $image_url       = (!empty($product->image) && Storage::exists($product->image)) ? $product->image : 'logo/placeholder.png';
            $image_url = (!empty($product->pro_image) && Storage::exists($product->pro_image)) ? $product->pro_image : 'uploads/pro_image/' . $product->pro_image;


            $model_delete_id = 'delete-form-' . $id;

            $carthtml = '';

            $carthtml .= '<tr data-product-id="' . $id . '" id="product-id-' . $id . '">
                            <td class="cart-images">
                                <img alt="Image placeholder" src="' . asset(Storage::url($image_url)) . '" class="card-image avatar shadow hover-shadow-lg">
                            </td>

                            <td class="name">' . $productname . '</td>

                            <td class="">
                                   <span class="quantity buttons_added">
                                         <input type="button" value="-" class="minus">
                                         <input type="number" step="1" min="1" max="" name="quantity" title="' . __('Quantity') . '" class="input-number" size="4" data-url="' . url('update-cart/') . '" data-id="' . $id . '">
                                         <input type="button" value="+" class="plus">
                                   </span>
                            </td>


                            <td class="tax">' . $product_tax . '</td>

                            <td class="price">' . Auth::user()->priceFormat($productprice) . '</td>

                            <td class="subtotal">' . Auth::user()->priceFormat($subtotal) . '</td>

                            <td class="">
                                 <a href="#" class="action-btn bg-danger bs-pass-para-pos" data-confirm="' . __("Are You Sure?") . '" data-text="' . __("This action can not be undone. Do you want to continue?") . '" data-confirm-yes=' . $model_delete_id . ' title="' . __('Delete') . '}" data-id="' . $id . '" title="' . __('Delete') . '"   >
                                   <span class=""><i class="ti ti-trash btn btn-sm text-white"></i></span>
                                 </a>
                                 <form method="post" action="' . url('remove-from-cart') . '"  accept-charset="UTF-8" id="' . $model_delete_id . '">
                                      <input name="_method" type="hidden" value="DELETE">
                                      <input name="_token" type="hidden" value="' . csrf_token() . '">
                                      <input type="hidden" name="session_key" value="' . $session_key . '">
                                      <input type="hidden" name="id" value="' . $id . '">
                                 </form>

                            </td>
                        </td>';
            // if cart is empty then this the first product
            if (!$cart) {
                $cart = [
                    $id => [
                        "name" => $productname,
                        "quantity" => 1,
                        "price" => $productprice,
                        "id" => $id,
                        "tax" => $producttax,
                        "subtotal" => $subtotal,
                        "originalquantity" => $originalquantity,
                        "product_tax" => $product_tax,
                        "product_tax_id" => !empty($product_tax_id) ? implode(',', $product_tax_id) : 0,
                    ],
                ];


                if ($originalquantity < $cart[$id]['quantity'] && $session_key == 'pos') {
                    return response()->json(
                        [
                            'code' => 404,
                            'status' => 'Error',
                            'error' => __('This product is out of stock!'),
                        ],
                        404
                    );
                }

                session()->put($session_key, $cart);

                return response()->json(
                    [
                        'code' => 200,
                        'status' => 'Success',
                        'success' => $productname . __(' added to cart successfully!'),
                        'product' => $cart[$id],
                        'carthtml' => $carthtml,
                    ]
                );
            }

            // if cart not empty then check if this product exist then increment quantity
            if (isset($cart[$id])) {

                $cart[$id]['quantity']++;
                $cart[$id]['id'] = $id;

                $subtotal = $cart[$id]["price"] * $cart[$id]["quantity"];
                $tax = ($subtotal * $cart[$id]["tax"]) / 100;

                $cart[$id]["subtotal"] = $subtotal + $tax;
                $cart[$id]["originalquantity"] = $originalquantity;

                if ($originalquantity < $cart[$id]['quantity'] && $session_key == 'pos') {
                    return response()->json(
                        [
                            'code' => 404,
                            'status' => 'Error',
                            'error' => __('This product is out of stock!'),
                        ],
                        404
                    );
                }

                session()->put($session_key, $cart);

                return response()->json(
                    [
                        'code' => 200,
                        'status' => 'Success',
                        'success' => $productname . __(' added to cart successfully!'),
                        'product' => $cart[$id],
                        'carttotal' => $cart,
                    ]
                );
            }

            // if item not exist in cart then add to cart with quantity = 1
            $cart[$id] = [
                "name" => $productname,
                "quantity" => 1,
                "price" => $productprice,
                "tax" => $producttax,
                "subtotal" => $subtotal,
                "id" => $id,
                "originalquantity" => $originalquantity,
                "product_tax" => $product_tax,
            ];

            if ($originalquantity < $cart[$id]['quantity'] && $session_key == 'pos') {
                return response()->json(
                    [
                        'code' => 404,
                        'status' => 'Error',
                        'error' => __('This product is out of stock!'),
                    ],
                    404
                );
            }

            session()->put($session_key, $cart);

            return response()->json(
                [
                    'code' => 200,
                    'status' => 'Success',
                    'success' => $productname . __(' added to cart successfully!'),
                    'product' => $cart[$id],
                    'carthtml' => $carthtml,
                    'carttotal' => $cart,
                ]
            );
        } else {
            return response()->json(
                [
                    'code' => 404,
                    'status' => 'Error',
                    'error' => __('This Product is not found!'),
                ],
                404
            );
        }
    }

    public function updateCart(Request $request)
    {

        $id = $request->id;
        $quantity = $request->quantity;
        $discount = $request->discount;
        $session_key = $request->session_key;

        if (Auth::user()->can('manage product & service') && $request->ajax() && isset($id) && !empty($id) && isset($session_key) && !empty($session_key)) {
            $cart = session()->get($session_key);


            if (isset($cart[$id]) && $quantity == 0) {
                unset($cart[$id]);
            }

            if ($quantity) {

                $cart[$id]["quantity"] = $quantity;

                $producttax = isset($cart[$id]) ? $cart[$id]["tax"] : 0;
                $productprice = $cart[$id]["price"];

                $subtotal = $productprice * $quantity;
                $tax = ($subtotal * $producttax) / 100;

                $cart[$id]["subtotal"] = $subtotal + $tax;

            }

            if (isset($cart[$id]) && isset($cart[$id]["originalquantity"]) < $cart[$id]['quantity'] && $session_key == 'pos') {
                return response()->json(
                    [
                        'code' => 404,
                        'status' => 'Error',
                        'error' => __('This product is out of stock!'),
                    ],
                    404
                );
            }

            $subtotal = array_sum(array_column($cart, 'subtotal'));
            $discount = $request->discount;
            $total = $subtotal - $discount;
            $totalDiscount = User::priceFormats($total);
            $discount = $totalDiscount;


            session()->put($session_key, $cart);

            return response()->json(
                [
                    'code' => 200,
                    'success' => __('Cart updated successfully!'),
                    'product' => $cart,
                    'discount' => $discount,
                ]
            );
        } else {
            return response()->json(
                [
                    'code' => 404,
                    'status' => 'Error',
                    'error' => __('This Product is not found!'),
                ],
                404
            );
        }
    }

    public function emptyCart(Request $request)
    {
        $session_key = $request->session_key;

        if (Auth::user()->can('manage product & service') && isset($session_key) && !empty($session_key)) {
            $cart = session()->get($session_key);
            if (isset($cart) && count($cart) > 0) {
                session()->forget($session_key);
            }

            return redirect()->back()->with('error', __('Cart is empty!'));
        } else {
            return redirect()->back()->with('error', __('Cart cannot be empty!.'));

        }
    }

    public function warehouseemptyCart(Request $request)
    {
        $session_key = $request->session_key;

        $cart = session()->get($session_key);
        if (isset($cart) && count($cart) > 0) {
            session()->forget($session_key);
        }

        return response()->json();

    }

    public function removeFromCart(Request $request)
    {
        $id = $request->id;
        $session_key = $request->session_key;
        if (Auth::user()->can('manage product & service') && isset($id) && !empty($id) && isset($session_key) && !empty($session_key)) {
            $cart = session()->get($session_key);
            if (isset($cart[$id])) {
                unset($cart[$id]);
                session()->put($session_key, $cart);
            }

            return redirect()->back()->with('error', __('Product removed from cart!'));
        } else {
            return redirect()->back()->with('error', __('This Product is not found!'));
        }
    }
    public function getSubcategories($categoryId)
    {
        $subcategories = ProductServiceSubCategory::where('category_id', $categoryId)->get();
        return response()->json(['subcategories' => $subcategories]);
    }

    public function printproductslist(Request $request)
{
    if (\Auth::user()->can('manage product & service')) {
        // Fetch categories and subcategories
        $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
            ->where('type', 'product & service')
            ->get()
            ->pluck('name', 'id');
        $category->prepend('Select Category', '');

        $subcategoryQuery = ProductServiceSubCategory::where('created_by', \Auth::user()->creatorId());
        if (!empty($request->category)) {
            $subcategoryQuery->where('category_id', $request->category);
        }
        $subcategory = $subcategoryQuery->get()->pluck('name', 'id');
        $subcategory->prepend('Select Sub-Category', '');

        // Query for products based on selected category and subcategory
        $query = ProductService::with(['category', 'subcategory'])->where('created_by', \Auth::user()->creatorId());
        if (!empty($request->category)) {
            $query->where('category_id', $request->category);
        }
        if (!empty($request->item_type)) {
            $query->where('item_type', $request->item_type);
        }
        if (!empty($request->subcategory)) {
            $query->where('sub_category_id', $request->subcategory);
        }

        // Get product services
        $productServices = $query->get();

        $viewData = [
            'productServices' => $productServices,
            'requestdata' => $request->all(),
        ];

        // Render HTML content for the PDF
        $html = view('productservice.printlist', $viewData)->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        // Create the final HTML
        $html = '<html><head>
             <style>
                 @page {
                     margin-top: 100px;
                     margin-bottom: 100px;
                 }
                 .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                 .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
             </style>
             </head><body>
             <div class="header">' . $headerHtml . '</div>
             <div class="footer">' . $footerHtml . '</div>
             ' . $html . '
             </body></html>';

        // Setup options for DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('enable_php', true); // Allow PHP scripting within the HTML

        // Initialize DOMPDF and generate the PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Fetch PDF content as base64
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);

        return response()->json(['base64Pdf' => $base64Pdf]);
    }
}

    private function chartAccountsByType(array $typeNames)
    {
        $normalizedTypes = array_map('strtolower', $typeNames);

        $accounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name, " (", chart_of_account_sub_types.name, ")") AS code_name, chart_of_accounts.id'))
            ->join('chart_of_account_sub_types', 'chart_of_accounts.sub_type', '=', 'chart_of_account_sub_types.id')
            ->join('chart_of_account_types', 'chart_of_accounts.type', '=', 'chart_of_account_types.id')
            // ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
            ->whereIn(\DB::raw('LOWER(chart_of_account_types.name)'), $normalizedTypes)
            ->orderBy('chart_of_account_sub_types.id')
            ->orderBy('chart_of_accounts.code')
            ->get()
            ->pluck('code_name', 'id');

        $accounts->prepend('Select Account', '');

        return $accounts;
    }

}
