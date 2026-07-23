<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseProductReportExport;
use App\Exports\PurchaseProductByVendorExport;
use App\Exports\PurchaseMemoExport;
use App\Exports\PurchaseVendorSummaryExport;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\PurchasePayment;
use App\Models\StockReport;
use App\Models\Tax;
use App\Models\Transaction;
use App\Models\Vender;
use App\Models\User;
use App\Models\Utility;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\Crypt;
use App\Models\warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dropdown list (id => name)
        $vendorList = Vender::where('created_by', \Auth::user()->creatorId())
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');
    
        // base query for purchases
        $query = Purchase::where('created_by', \Auth::user()->creatorId());
    
        // filter by vendor (only when Search button is clicked and param is present)
        if ($request->filled('vender')) {
            $query->where('vender_id', $request->vender);
        }
    
        // paginate results
        $purchases = $query->paginate(25);
    
        // statuses
        $status = Purchase::$statues;
    
        return view('purchase.index', compact('purchases', 'status', 'vendorList'));
    }
    public function pur_rep(Request $request)
    {

        $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');
        $status = Purchase::$statues;
        $query = Purchase::where('created_by', '=', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('purchase_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('purchase_date', '<', $request->end_date);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status)) {
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();

                if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'status' => $status,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseMemoExport($data, $request->all()), 'purchase_memo_report.xlsx');
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'status' => $status,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseMemoExport($data, $request->all()), 'purchase_memo_report.pdf');
        }

        return view('purchase.pur_rep', compact('purchases', 'status', 'vender', 'request'));


    }

    public function purchaseReport(Request $request)
    {
        $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');
        $status = Purchase::$statues;
        $query = Purchase::where('created_by', '=', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('purchase_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('purchase_date', '<', $request->end_date);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status))
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();
        $pdf = new Dompdf();
        $html = view('purchase.pdf.report', compact('purchases'))->render();
        $headerHtml = view('invoice.report.pdf.header', compact('request'))->render();
        $footerHtml = view('invoice.report.pdf.footer')->render();

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
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('purchase.pdf');

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);

    }
    public function pur_pro_rep(Request $request)
    {

        $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');
        $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');
        $status = Purchase::$statues;
        $query = Purchase::with('items', 'items.products', 'category')->where('created_by', '=', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('purchase_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('purchase_date', '<', $request->end_date);
        }
        if (!empty($request->categories)) {
            $query->whereDate('category_id', $request->categories);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status)) {
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();

                if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'status' => $status,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseProductReportExport($data, $request->all()), 'purchase_product_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'status' => $status,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseProductReportExport($data, $request->all()), 'purchase_product_report.pdf');
        }

        return view('purchase.pur_rep_pro', compact('purchases', 'status', 'vender', 'categories', 'request'));


    }
    public function purchaseProductReport(Request $request)
    {

        $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');
        $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');
        $status = Purchase::$statues;
        $query = Purchase::with('items', 'items.products', 'category')->where('created_by', '=', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('purchase_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('purchase_date', '<', $request->end_date);
        }
        if (!empty($request->categories)) {
            $query->whereDate('category_id', $request->categories);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status)) {
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();

        $pdf = new Dompdf();
        $html = view('purchase.product_pdf.report', compact('purchases'))->render();
        $headerHtml = view('invoice.report.pdf.header', compact('request'))->render();
        $footerHtml = view('invoice.report.pdf.footer')->render();

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
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('purchase_product.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);

    }
    public function purchaseProductByVendorReport(Request $request)
    {

        $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');
        $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');
        $status = Purchase::$statues;
        $query = Purchase::with('items', 'items.products', 'category')->where('created_by', '=', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('purchase_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('purchase_date', '<', $request->end_date);
        }
        if (!empty($request->categories)) {
            $query->whereDate('category_id', $request->categories);
        }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status)) {
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();

                if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseProductByVendorExport($data, $request->all()), 'product_by_vendor_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'vender' => $vender,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new PurchaseProductByVendorExport($data, $request->all()), 'product_by_vendor_report.pdf');
        }

        if ($request->filled('is_print') && $request->is_print == 1) {
            $bodyHtml = view('purchase.pdf.report_byvendor_print', compact('purchases', 'request'))->render();
            $headerHtml = view('invoice.report.pdf.header', compact('request'))->render();
            $footerHtml = view('invoice.report.pdf.footer')->render();

            $finalHtml = '
                <html><head>
                <style>
                    @page {
                        margin-top: 100px;
                        margin-bottom: 100px;
                    }
                    body { font-family: sans-serif; font-size: 12px; }
                    .header {
                        position: fixed;
                        top: -60px;
                        left: 0;
                        right: 0;
                        height: 100px;
                        text-align: center;
                    }
                    .footer {
                        position: fixed;
                        bottom: -60px;
                        left: 0;
                        right: 0;
                        height: 50px;
                        text-align: center;
                        font-size: 10px;
                        color: #888;
                    }
                </style>
                </head>
                <body>
                    <div class="header">' . $headerHtml . '</div>
                    <div class="footer">' . $footerHtml . '</div>
                    ' . $bodyHtml . '
                </body></html>';

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($finalHtml);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return $dompdf->stream('product_Byvendor_report.pdf', ['Attachment' => false]);
        }
        return view('purchase.pdf.report_byvendor', compact(
            'purchases',
            'vender',
            'categories',
            'request'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($vendorId)
    {
        if (\Auth::user()->can('create purchase')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'purchase')->get();

            $purchase_number = \Auth::user()->purchaseNumberFormat($this->purchaseNumber());
            $venders = Vender::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('Select Vender', '');

            $warehouse = warehouse::where('owned_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
            $product_services->prepend('Select Item', '');

            if (request()->ajax()) {
                return view('purchase.create', compact('venders', 'purchase_number', 'product_services', 'customFields', 'vendorId', 'warehouse'))->renderSections()['content'] ?? '';
            }

            return view('purchase.create', compact('venders', 'purchase_number', 'product_services', 'customFields', 'vendorId', 'warehouse'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (\Auth::user()->can('create purchase')) {
            DB::beginTransaction();
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'vender_id' => 'required',
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
                $purchase = new Purchase();
                $purchase->purchase_id = $this->purchaseNumber();
                $purchase->vender_id = $request->vender_id;
                $purchase->warehouse_id = $request->warehouse_id;
                $purchase->purchase_date = $request->purchase_date;
                $purchase->purchase_number = !empty($request->purchase_number) ? $request->purchase_number : 0;
                $purchase->status = \Auth::user()->type == 'company' ? 5 : 0;
                //            $purchase->discount_apply = isset($request->discount_apply) ? 1 : 0;
                $purchase->category_id = $request->category_id;
                $purchase->created_by = \Auth::user()->creatorId();
                $purchase->save();
                $products = $request->items;
                $newitems = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $purchaseProduct = new PurchaseProduct();
                    $purchaseProduct->purchase_id = $purchase->id;
                    $purchaseProduct->product_id = $products[$i]['item'];
                    $purchaseProduct->quantity = $products[$i]['quantity'] ?? 0;
                    $purchaseProduct->discount = $products[$i]['discount'] ?? 0;
                    $purchaseProduct->price = $products[$i]['price'] ?? 0;
                    $purchaseProduct->description = $products[$i]['description'] ?? '';
                    $purchaseProduct->save();

                    $newitems[$i]['prod_id'] = $purchaseProduct->id;
                    // Removed stock and warehouse updates as per request to keep it as a record only

                }
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => \Auth::user()->type == 'company' ? __('Purchase successfully created and forwarded to Head Office.') : __('Purchase successfully created.')]);
                }
                return redirect()->route('purchase.show', Crypt::encrypt($purchase->id))->with('success', \Auth::user()->type == 'company' ? __('Purchase successfully created and forwarded to Head Office.') : __('Purchase successfully created.'));
            } catch (\Exception $e) {
                DB::rollback();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()]);
                }
                return redirect()->back()->with('error', $e->getMessage());
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {

        if (\Auth::user()->can('show purchase')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Not Found.'));
            }

            $id = Crypt::decrypt($ids);
            $purchase = Purchase::find($id);

            if ($purchase->created_by == \Auth::user()->creatorId()) {

                $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
                $vendor = $purchase->vender;
                $iteams = $purchase->items;

                return view('purchase.view', compact('purchase', 'vendor', 'iteams', 'purchasePayment'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($idsd)
    {
        if (\Auth::user()->can('edit purchase')) {

            $idwww = Crypt::decrypt($idsd);
            $purchase = Purchase::find($idwww);
            $warehouse = warehouse::where('owned_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $purchase_number = \Auth::user()->purchaseNumberFormat($purchase->purchase_id);
            $venders = Vender::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');

            if (request()->ajax()) {
                return view('purchase.edit', compact('venders', 'product_services', 'purchase', 'warehouse', 'purchase_number'))->renderSections()['content'] ?? '';
            }

            return view('purchase.edit', compact('venders', 'product_services', 'purchase', 'warehouse', 'purchase_number', ));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Purchase $purchase)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit purchase'))
        {
            if($purchase->created_by == \Auth::user()->creatorId())
            {
                DB::beginTransaction();
                try {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'vender_id' => 'required',
                            'purchase_date' => 'required',
                            'items' => 'required',
                        ]
                    );
                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();
                        if ($request->ajax()) {
                            return response()->json(['success' => false, 'message' => $messages->first()]);
                        }
                        return redirect()->route('purchase.index')->with('error', $messages->first());
                    }
                    $purchase->vender_id = $request->vender_id;
                    $purchase->purchase_date = $request->purchase_date;
                    $purchase->category_id = $request->category_id;
                    $purchase->save();
                    $products = $request->items;
                    $this->purgePurchaseAccountingVouchers($purchase);
                    // collect existing product IDs to detect removals
                    $existingIds = PurchaseProduct::where('purchase_id', $purchase->id)->pluck('id')->toArray();
                    $submittedIds = [];
                    // update only if amount not pay on purchase order
                    if ($purchase->status == 0 || $purchase->status == 1 || ($purchase->status == 5 && \Auth::user()->type == 'company')) {
                        for ($i = 0; $i < count($products); $i++) {
                            $purchaseProduct = PurchaseProduct::find($products[$i]['id']);

                            if ($purchaseProduct == null) {
                                $purchaseProduct = new PurchaseProduct();
                                $purchaseProduct->purchase_id = $purchase->id;
                                $purchaseProduct->quantity = $products[$i]['quantity'] ?? 1;
                                // new item quantity added in product (Removed for record-only)
                                $old_qty = 0;
                                if (isset($products[$i]['item'])) {
                                    $purchaseProduct->product_id = $products[$i]['item'];
                                }
                                $purchaseProduct->discount = $products[$i]['discount'] ?? 0;
                                $purchaseProduct->price = $products[$i]['price'] ?? 0;
                                $purchaseProduct->description = $products[$i]['description'] ?? '';
                                $purchaseProduct->save();
                                $submittedIds[] = $purchaseProduct->id;

                            } else {
                                $old_qty = $purchaseProduct->quantity;
                                // Removed total_quantity minus
                                if (isset($products[$i]['item'])) {
                                    $purchaseProduct->product_id = $products[$i]['item'];
                                }

                                $purchaseProduct->quantity = $products[$i]['quantity'] ?? 1;
                                $purchaseProduct->discount = $products[$i]['discount'] ?? 0;
                                $purchaseProduct->price = $products[$i]['price'] ?? 0;
                                $purchaseProduct->description = $products[$i]['description'] ?? '';
                                $purchaseProduct->save();

                                $submittedIds[] = $purchaseProduct->id;

                            }

                            // Stock updates removed

                        }
                        // delete items that were removed from the form
                        $removedIds = array_diff($existingIds, $submittedIds);
                        if (!empty($removedIds)) {
                            PurchaseProduct::whereIn('id', $removedIds)->delete();
                        }

                    } else {
                        if ($request->ajax()) {
                            return response()->json(['success' => false, 'message' => __('Purchase items cant change')]);
                        }
                        return redirect()->route('purchase.index')->with('error', __('Purchase items cant change'));
                    }

                    DB::commit();
                    \DB::commit();
                    if ($request->ajax()) {
                        return response()->json(['success' => true, 'message' => __('Purchase successfully updated.')]);
                    }
                    return redirect()->route('purchase.index')->with('success', __('Purchase successfully updated.'));
                } catch (\Exception $e) {
                    DB::rollback();
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $e->getMessage()]);
                    }
                    return redirect()->back()->with('error', $e->getMessage());
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('Permission denied.')]);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')]);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        } }
        catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        if (\Auth::user()->can('delete purchase')) {
            if ($purchase->created_by == \Auth::user()->creatorId()) {
                $purchase_products = PurchaseProduct::where('purchase_id', $purchase->id)->get();

                $purchasepayments = $purchase->payments;
                foreach ($purchasepayments as $key => $value) {
                    $purchasepayment = PurchasePayment::find($value->id);
                    $this->purgePurchasePaymentVoucher($purchasepayment);
                    if ($purchasepayment) {
                        $purchasepayment->delete();
                    }
                }
                $this->purgePurchaseAccountingVouchers($purchase);

                    // Stock decrement logic removed for record-only

                $purchase->delete();
                PurchaseProduct::where('purchase_id', '=', $purchase->id)->delete();


                return redirect()->route('purchase.index')->with('success', __('Purchase successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }



    function purchaseNumber()
    {
        $latest = Purchase::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->purchase_id + 1;
    }
    public function sent($id)
    {
        if (\Auth::user()->can('send purchase')) {
            $purchase = Purchase::where('id', $id)->first();
            $purchase->send_date = date('Y-m-d');
            $purchase->status = 1;
            $purchase->save();

            $vender = Vender::where('id', $purchase->vender_id)->first();

            $purchase->name = !empty($vender) ? $vender->name : '';
            $purchase->purchase = \Auth::user()->purchaseNumberFormat($purchase->purchase_id);

            $purchaseId = Crypt::encrypt($purchase->id);
            $purchase->url = route('purchase.pdf', $purchaseId);

            Utility::userBalance('vendor', $vender->id, $purchase->getTotal(), 'credit');

            $vendorArr = [
                'vender_bill_name' => $purchase->name,
                'vender_bill_number' => $purchase->purchase,
                'vender_bill_url' => $purchase->url,

            ];
            $resp = \App\Models\Utility::sendEmailTemplate('vender_bill_sent', [$vender->id => $vender->email], $vendorArr);

            return redirect()->back()->with('success', __('Purchase successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function resent($id)
    {

        if (\Auth::user()->can('send purchase')) {
            $purchase = Purchase::where('id', $id)->first();

            $vender = Vender::where('id', $purchase->vender_id)->first();

            $purchase->name = !empty($vender) ? $vender->name : '';
            $purchase->purchase = \Auth::user()->purchaseNumberFormat($purchase->purchase_id);

            $purchaseId = Crypt::encrypt($purchase->id);
            $purchase->url = route('purchase.pdf', $purchaseId);
            //

            // Send Email
//        $setings = Utility::settings();
//
//        if($setings['bill_resend'] == 1)
//        {
//            $bill = Bill::where('id', $id)->first();
//            $vender = Vender::where('id', $bill->vender_id)->first();
//            $bill->name = !empty($vender) ? $vender->name : '';
//            $bill->bill = \Auth::user()->billNumberFormat($bill->bill_id);
//            $billId    = Crypt::encrypt($bill->id);
//            $bill->url = route('bill.pdf', $billId);
//            $billResendArr = [
//                'vender_name'   => $vender->name,
//                'vender_email'  => $vender->email,
//                'bill_name'  => $bill->name,
//                'bill_number'   => $bill->bill,
//                'bill_url' =>$bill->url,
//            ];
//
//            $resp = Utility::sendEmailTemplate('bill_resend', [$vender->id => $vender->email], $billResendArr);
//
//
//        }
//
//        return redirect()->back()->with('success', __('Bill successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
//
            return redirect()->back()->with('success', __('Bill successfully sent.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function purchase($purchase_id)
    {

        $settings = Utility::settings();
        $purchaseId = Crypt::decrypt($purchase_id);
        $purchase = Purchase::where('id', $purchaseId)->first();
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', $purchase->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $vendor = $purchase->vender;

        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($purchase->items as $product) {

            $item = new \stdClass();
            $item->name = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;

            $taxes = Utility::tax($product->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = Utility::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
                    $itemTax['tax_price'] = $taxPrice;
                    $itemTaxes[] = $itemTax;


                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }

                }

                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $purchase->itemData = $items;
        $purchase->totalTaxPrice = $totalTaxPrice;
        $purchase->totalQuantity = $totalQuantity;
        $purchase->totalRate = $totalRate;
        $purchase->totalDiscount = $totalDiscount;
        $purchase->taxesData = $taxesData;


        //        $logo         = asset(Storage::url('uploads/logo/'));
//        $company_logo = Utility::getValByName('company_logo_dark');
//        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $purchase_logo = Utility::getValByName('purchase_logo');
        if (isset($purchase_logo) && !empty($purchase_logo)) {
            $img = Utility::get_file('purchase_logo/') . $purchase_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        if ($purchase) {
            $color = '#' . $settings['purchase_color'];
            $font_color = Utility::getFontColor($color);
            $pdf = view('purchase.templates.' . $settings['purchase_template'], compact('purchase', 'color', 'settings', 'vendor', 'img', 'font_color'));
            // dd('');
            return $pdf;
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewPurchase($template, $color)
    {
        $objUser = \Auth::user();
        $settings = Utility::settings();
        $purchase = new Purchase();

        $vendor = new \stdClass();
        $vendor->email = '<Email>';
        $vendor->shipping_name = '<Vendor Name>';
        $vendor->shipping_country = '<Country>';
        $vendor->shipping_state = '<State>';
        $vendor->shipping_city = '<City>';
        $vendor->shipping_phone = '<Vendor Phone Number>';
        $vendor->shipping_zip = '<Zip>';
        $vendor->shipping_address = '<Address>';
        $vendor->billing_name = '<Vendor Name>';
        $vendor->billing_country = '<Country>';
        $vendor->billing_state = '<State>';
        $vendor->billing_city = '<City>';
        $vendor->billing_phone = '<Vendor Phone Number>';
        $vendor->billing_zip = '<Zip>';
        $vendor->billing_address = '<Address>';

        $totalTaxPrice = 0;
        $taxesData = [];
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item = new \stdClass();
            $item->name = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax = 5;
            $item->discount = 50;
            $item->price = 100;

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice = 10;
                $totalTaxPrice += $taxPrice;
                $itemTax['name'] = 'Tax ' . $k;
                $itemTax['rate'] = '10 %';
                $itemTax['price'] = '$10';
                $itemTax['tax_price'] = 10;
                $itemTaxes[] = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[] = $item;
        }

        $purchase->purchase_id = 1;
        $purchase->issue_date = date('Y-m-d H:i:s');
        //        $purchase->due_date   = date('Y-m-d H:i:s');
        $purchase->itemData = $items;

        $purchase->totalTaxPrice = 60;
        $purchase->totalQuantity = 3;
        $purchase->totalRate = 300;
        $purchase->totalDiscount = 10;
        $purchase->taxesData = $taxesData;
        $purchase->created_by = $objUser->creatorId();

        $preview = 1;
        $color = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($purchase->created_by);
        $purchase_logo = $settings_data['purchase_logo'];

        if (isset($purchase_logo) && !empty($purchase_logo)) {
            $img = Utility::get_file('purchase_logo/') . $purchase_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }


        return view('purchase.templates.' . $template, compact('purchase', 'preview', 'color', 'img', 'settings', 'vendor', 'font_color'));
    }

    public function savePurchaseTemplateSettings(Request $request)
    {

        $post = $request->all();
        unset($post['_token']);

        if (isset($post['purchase_template']) && (!isset($post['purchase_color']) || empty($post['purchase_color']))) {
            $post['purchase_color'] = "ffffff";
        }


        if ($request->purchase_logo) {
            $dir = 'purchase_logo/';
            $purchase_logo = \Auth::user()->id . '_purchase_logo.png';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];
            $path = Utility::upload_file($request, 'purchase_logo', $purchase_logo, $dir, $validation);
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['purchase_logo'] = $purchase_logo;
        }


        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Purchase Setting updated successfully'));
    }

    public function items(Request $request)
    {

        $items = PurchaseProduct::where('purchase_id', $request->purchase_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }

    public function purchaseLink($purchaseId)
    {
        try {
            $id = Crypt::decrypt($purchaseId);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Purchase Not Found.'));
        }

        $id = Crypt::decrypt($purchaseId);
        $purchase = Purchase::find($id);

        if (!empty($purchase)) {
            $user_id = $purchase->created_by;
            $user = User::find($user_id);
            $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
            $vendor = $purchase->vender;
            $iteams = $purchase->items;

            return view('purchase.customer_bill', compact('purchase', 'vendor', 'iteams', 'purchasePayment', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function payment($purchase_id)
    {
        if (\Auth::user()->can('create payment purchase')) {
            $purchase = Purchase::where('id', $purchase_id)->first();
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('purchase.payment', compact('venders', 'categories', 'accounts', 'purchase'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));

        }
    }

    public function createPayment(Request $request, $purchase_id)
    {

        if (\Auth::user()->can('create payment purchase')) {
            DB::beginTransaction();
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'date' => 'required',
                        'amount' => 'required',
                        'account_id' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $purchasePayment = new PurchasePayment();
                $purchasePayment->purchase_id = $purchase_id;
                $purchasePayment->date = $request->date;
                $purchasePayment->amount = $request->amount;
                $purchasePayment->account_id = $request->account_id;
                $purchasePayment->payment_method = 0;
                $purchasePayment->reference = $request->reference;
                $purchasePayment->description = $request->description;
                if (!empty($request->add_receipt)) {
                    $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                    $request->add_receipt->storeAs('uploads/payment', $fileName);
                    $purchasePayment->add_receipt = $fileName;
                }
                $purchasePayment->save();

                $purchase = Purchase::where('id', $purchase_id)->first();
                $due = $purchase->getDue();
                $total = $purchase->getTotal();

                if ($purchase->status == 0) {
                    $purchase->send_date = date('Y-m-d');
                    $purchase->save();
                }

                if ($due <= 0) {
                    $purchase->status = 4;
                    $purchase->save();
                } else {
                    $purchase->status = 3;
                    $purchase->save();
                }
                $purchasePayment->user_id = $purchase->vender_id;
                $purchasePayment->user_type = 'Vender';
                $purchasePayment->type = 'Partial';
                $purchasePayment->owned_by = \Auth::user()->id;
                $purchasePayment->created_by = \Auth::user()->id;
                $purchasePayment->payment_id = $purchasePayment->id;
                $purchasePayment->category = 'Bill';
                $purchasePayment->account = $request->account_id;
                Transaction::addTransaction($purchasePayment);

                $vender = Vender::where('id', $purchase->vender_id)->first();

                $payment = new PurchasePayment();
                $payment->name = $vender['name'];
                $payment->method = '-';
                $payment->date = \Auth::user()->dateFormat($request->date);
                $payment->amount = \Auth::user()->priceFormat($request->amount);
                $payment->bill = 'bill ' . \Auth::user()->purchaseNumberFormat($purchasePayment->purchase_id);

                Utility::userBalance('vendor', $purchase->vender_id, $request->amount, 'debit');

                Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

                // Send Email
                // $setings = Utility::settings();
                // if($setings['new_bill_payment'] == 1)
                // {

                //     $vender = Vender::where('id', $purchase->vender_id)->first();
                //     $billPaymentArr = [
                //         'vender_name'   => $vender->name,
                //         'vender_email'  => $vender->email,
                //         'payment_name'  =>$payment->name,
                //         'payment_amount'=>$payment->amount,
                //         'payment_bill'  =>$payment->bill,
                //         'payment_date'  =>$payment->date,
                //         'payment_method'=>$payment->method,
                //         'company_name'=>$payment->method,

                //     ];

                //     $resp = Utility::sendEmailTemplate('new_bill_payment', [$vender->id => $vender->email], $billPaymentArr);

                //     return redirect()->back()->with('success', __('Payment successfully added.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

                // }

                DB::commit();
                return redirect()->back()->with('success', __('Payment successfully added.'));

            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', 'something went wrong');
            }
        }

    }

    public function paymentDestroy(Request $request, $purchase_id, $payment_id)
    {

        if (\Auth::user()->can('delete payment purchase')) {
            $payment = PurchasePayment::find($payment_id);
            $this->purgePurchasePaymentVoucher($payment);
            PurchasePayment::where('id', '=', $payment_id)->delete();

            $purchase = Purchase::where('id', $purchase_id)->first();

            $due = $purchase->getDue();
            $total = $purchase->getTotal();

            if ($due > 0 && $total != $due) {
                $purchase->status = 3;

            } else {
                $purchase->status = 2;
            }

            Utility::userBalance('vendor', $purchase->vender_id, $payment->amount, 'credit');
            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

            $purchase->save();
            $type = 'Partial';
            $user = 'Vender';
            Transaction::destroyTransaction($payment_id, $type, $user);

            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function vender(Request $request)
    {
        $vender = Vender::where('id', '=', $request->id)->first();

        return view('purchase.vender_detail', compact('vender'));
    }
    public function product(Request $request)
    {


        $data['product'] = $product = ProductService::find($request->product_id);
        $data['unit'] = !empty($product->unit()) ? $product->unit()->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->purchase_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function productDestroy(Request $request)
    {

        if (\Auth::user()->can('delete purchase')) {
            DB::beginTransaction();
            try {
                $res = PurchaseProduct::where('id', '=', $request->id)->first();
                $purchase = Purchase::find($res->purchase_id);
                if ($purchase->status == 0 || $purchase->status == 1) {
                    // Removed stock decrement for record-only
                    $this->purgePurchaseAccountingVouchers($purchase);

                    PurchaseProduct::where('id', '=', $request->id)->delete();


                } else {
                    return redirect()->back()->with('error', __("You Can't perform any action"));
                }
                DB::commit();
                return redirect()->back()->with('success', __('Purchase product successfully deleted.'));
            } catch (\Exception $e) {
                DB::rollback();
                // dd($e);
                return redirect()->back()->with('error', 'something went wrong');
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function purchaseVendorSummary(Request $request)
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
        $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

        $vender = Vender::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $vender->prepend('Select Vendor', '');

        $categories = ProductServiceCategory::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
        $categories->prepend('Select Category', '');

        $status = Purchase::$statues;
        $query = Purchase::with('items', 'items.products', 'category', 'vender')
            ->where('created_by', \Auth::user()->creatorId());
        if (!empty($request->vender)) {
            $query->where('vender_id', $request->vender);
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        } else {
            $request->merge(['start_date' => $dateFrom, 'end_date' => $dateTo]);
            $query->whereBetween('purchase_date', [$dateFrom, $dateTo]);
        }

        if (!empty($request->categories)) {
            $query->where('category_id', $request->categories);
        }

        // Fetch all purchases
        $purchases = $query->get();

        // Build vendor summary
        $vendorSummary = [];

        foreach ($purchases as $purchase) {
            $vendorId = $purchase->vender_id;
            $vendorName = $purchase->vender->name ?? 'Unknown Vendor';
            $total = 0;
            // foreach ($purchase->items as $item) {
            //     dd($item,$purchase->items);
            //     $total += $item->price * $item->quantity;
            // }
            $due = $purchase->getDue();
            $total = $purchase->getTotal();
            $balance = $total - $due;

            if (!isset($vendorSummary[$vendorId])) {
                $vendorSummary[$vendorId] = [
                    'vendor_name' => $vendorName,
                    'total_purchase' => 0,
                    'total_due' => 0,
                    'total_balance' => 0,
                ];
            }

            $vendorSummary[$vendorId]['total_purchase'] += $total;
            $vendorSummary[$vendorId]['total_due'] += $due;
            $vendorSummary[$vendorId]['total_balance'] += $balance;
        }

        if ($request->has('export') && $request->export == 'excel') {
            $data = [
                'vender' => $vender,
                'purchases' => $purchases,
                'vendorSummary' => $vendorSummary,
                'request' => $request,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ];

            return Excel::download(new PurchaseVendorSummaryExport($data), 'purchase_vendor_summary.xlsx');
        };
        if ($request->has('export') && $request->export == 'pdf') {
            $data = [
                'vender' => $vender,
                'purchases' => $purchases,
                'vendorSummary' => $vendorSummary,
                'request' => $request,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ];

            return Excel::download(new PurchaseVendorSummaryExport($data), 'purchase_vendor_summary.pdf', \Maatwebsite\Excel\Excel::MPDF);
        };
        // Return to view
        return view('purchase.vendor_summary', compact('vender', 'purchases', 'vendorSummary', 'request', 'dateFrom', 'dateTo'));
    }

    public function fwToHo($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->status = 5; // Fw to Ho
        $purchase->save();
        return redirect()->back()->with('success', __('Purchase forwarded to Head Office.'));
    }

    public function finalize($id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->status == 5 && \Auth::user()->type == 'company') {
            $purchase->status = 6; // Finalized
            $purchase->save();

            return redirect()->back()->with('success', __('Purchase finalized. Ready to convert to GRN.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function reject($id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->status == 5 && \Auth::user()->type == 'company') {
            $purchase->status = 0; // Draft
            $purchase->save();
            return redirect()->back()->with('success', __('Purchase rejected and sent back to draft.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function convertToGrn($id)
    {
        $purchase = Purchase::with('items.products')->findOrFail($id);

        if ($purchase->status != 6 || \Auth::user()->type != 'company') {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($purchase->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $viewData = $this->purchaseGrnFormData($purchase);
        if (count($viewData['initialItems']) === 0) {
            return response()->json(['error' => __('All items fully received.')], 422);
        }

        $view = view('purchase.convert_to_grn', $viewData);

        if (request()->ajax()) {
            return $view->renderSections()['content'] ?? '';
        }

        return $view;
    }

    public function storeConvertedGrn(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        if ($purchase->status != 6 || \Auth::user()->type != 'company' || $purchase->created_by != \Auth::user()->creatorId()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 401);
        }

        $user = \Auth::user();
        $validator = \Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $grn = \App\Models\Grn::create([
                'grn_no' => $this->nextPurchaseGrnNumber($user->creatorId()),
                'vendor_id' => $request->vendor_id,
                'warehouse_id' => $request->warehouse_id,
                'grn_date' => $request->grn_date,
                'reference_no' => $request->reference_no,
                'purchase_order_id' => $request->purchase_order_id,
                'remarks' => $request->remarks,
                'status' => $user->type == 'company' ? 5 : 0,
                'owned_by' => $user->creatorId(),
                'created_by' => $user->creatorId(),
            ]);

            foreach ($request->items as $item) {
                \App\Models\GrnItem::create([
                    'grn_id' => $grn->id,
                    'purchase_id' => $item['purchase_id'] ?? $purchase->id,
                    'purchase_product_id' => $item['purchase_product_id'] ?? null,
                    'purchase_order_no' => $item['purchase_order_no'] ?? $request->purchase_order_id,
                    'product_id' => $item['product_id'],
                    'condition' => $item['condition'],
                    'ordered_quantity' => (float) ($item['ordered_quantity'] ?? 0),
                    'quantity' => (float) $item['quantity'],
                    'price' => (float) ($item['price'] ?? 0),
                    'description' => $item['description'] ?? null,
                ]);

                $product = ProductService::find($item['product_id']);
                if ($product && $product->type != 'service' && (float) ($item['price'] ?? 0) > 0 && (float) $product->purchase_price != (float) $item['price']) {
                    $product->purchase_price = (float) $item['price'];
                    $product->save();
                }
            }


            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('GRN created successfully from Purchase.'),
                'redirect_url' => route('grn.show', $grn->id),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function purchaseGrnFormData(Purchase $purchase)
    {
        $user = \Auth::user();

        $vendors = Vender::where('created_by', $user->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('Select Vendor', '');

        $warehouseRecords = warehouse::where('owned_by', $user->creatorId())
            ->orderBy('name')
            ->get(['id', 'name', 'owned_by']);
        $warehouses = $warehouseRecords->pluck('name', 'id')->prepend('Select Store', '');
        $nextGrnNumber = $this->nextPurchaseGrnNumber($user->creatorId());

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

        $purchaseOrderNo = $user->purchaseNumberFormat($purchase->purchase_id);
        $initialItems = $purchase->items->map(function ($item) use ($purchase, $purchaseOrderNo) {
            $pending = \App\Models\GrnItem::query()
                ->join('grns', 'grns.id', '=', 'grn_items.grn_id')
                ->where('grn_items.purchase_product_id', $item->id)
                ->whereIn('grns.status', [0, 5, 6, 7])
                ->sum('grn_items.quantity');
            $remaining = max(0, (float) $item->quantity - (float) ($item->received_quantity ?? 0) - (float) $pending);

            if ($remaining <= 0) {
                return null;
            }

            return [
                'product_id' => $item->product_id,
                'purchase_id' => $purchase->id,
                'purchase_product_id' => $item->id,
                'purchase_order_no' => $purchaseOrderNo,
                'ordered_quantity' => (float) $item->quantity,
                'available_quantity' => $remaining,
                'condition' => 'new',
                'quantity' => $remaining,
                'price' => (float) ($item->price ?? 0),
                'description' => $item->description ?? '',
                'source' => $purchaseOrderNo,
            ];
        })->filter()->values();

        $formDefaults = [
            'vendor_id' => $purchase->vender_id,
            'warehouse_id' => $purchase->warehouse_id,
            'grn_date' => date('Y-m-d'),
            'reference_no' => $user->purchaseNumberFormat($purchase->purchase_id),
            'purchase_order_id' => $user->purchaseNumberFormat($purchase->purchase_id),
            'remarks' => __('Converted from Purchase') . ' ' . $user->purchaseNumberFormat($purchase->purchase_id),
        ];

        $existingGrns = \App\Models\Grn::whereHas('items', function($q) use($purchase) {
            $q->where('purchase_id', $purchase->id);
        })->get();

        $submitLabel = __('Convert');
        $cancelUrl = route('purchase.show', Crypt::encrypt($purchase->id));
        $showPurchaseLink = false;
        $showAddVendorLink = false;

        return compact(
            'purchase',
            'vendors',
            'warehouses',
            'warehouseRecords',
            'productOptions',
            'productMeta',
            'nextGrnNumber',
            'vendorAccounts',
            'vendorSubAccounts',
            'initialItems',
            'formDefaults',
            'submitLabel',
            'cancelUrl',
            'showPurchaseLink',
            'showAddVendorLink',
            'existingGrns'
        );
    }

    private function nextPurchaseGrnNumber($creatorId)
    {
        return ((int) \App\Models\Grn::where('created_by', $creatorId)->max('grn_no')) + 1;
    }

    private function purgePurchaseAccountingVouchers(?Purchase $purchase): void
    {
        if (!$purchase) {
            return;
        }

        $journalIds = collect([$purchase->voucher_id])
            ->merge(
                JournalEntry::where('category', 'Purchase')
                    ->where('reference_id', $purchase->id)
                    ->where('voucher_type', 'JV')
                    ->pluck('id')
            )
            ->filter()
            ->unique()
            ->values();

        $this->deleteJournalEntriesWithItems($journalIds);

        if ($purchase->voucher_id) {
            $purchase->voucher_id = null;
            $purchase->save();
        }
    }

    private function purgePurchasePaymentVoucher(?PurchasePayment $payment): void
    {
        if (!$payment) {
            return;
        }

        $journalIds = collect([$payment->voucher_id])
            ->merge(
                JournalEntry::where('category', 'Purchase')
                    ->where('reference_id', $payment->id)
                    ->whereIn('voucher_type', ['BPV', 'CPV'])
                    ->pluck('id')
            )
            ->filter()
            ->unique()
            ->values();

        $this->deleteJournalEntriesWithItems($journalIds);

        if ($payment->voucher_id) {
            $payment->voucher_id = null;
            $payment->save();
        }
    }

    private function deleteJournalEntriesWithItems($journalIds): void
    {
        if ($journalIds->isEmpty()) {
            return;
        }

        JournalItem::whereIn('journal', $journalIds)->delete();
        JournalEntry::whereIn('id', $journalIds)->delete();
    }
}
