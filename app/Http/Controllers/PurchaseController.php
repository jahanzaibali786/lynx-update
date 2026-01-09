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
            // $warehouse->prepend('Select Warehouse', '');

            $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
            // $product_services = ProductService::where('created_by', \Auth::user()->creatorId())->where('type','!=', 'service')->get()->pluck('name', 'id');
            $product_services->prepend('Select Item', '');

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
                        // 'category_id' => 'required',
                        'items' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $purchase = new Purchase();
                $purchase->purchase_id = $this->purchaseNumber();
                $purchase->vender_id = $request->vender_id;
                $purchase->warehouse_id = $request->warehouse_id;
                $purchase->purchase_date = $request->purchase_date;
                $purchase->purchase_number = !empty($request->purchase_number) ? $request->purchase_number : 0;
                $purchase->status = 0;
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
                    $purchaseProduct->tax = $products[$i]['tax'] ?? 0;
                    //                $purchaseProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                    $purchaseProduct->discount = $products[$i]['discount'] ?? 0;
                    $purchaseProduct->price = $products[$i]['price'];
                    $purchaseProduct->description = $products[$i]['description'];
                    $purchaseProduct->save();

                    $newitems[$i]['prod_id'] = $purchaseProduct->id;
                    //inventory management (Quantity)
                    Utility::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);


                    //Product Stock Report
                    $type = 'purchase';
                    $type_id = $purchase->id;
                    $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . \Auth::user()->purchaseNumberFormat($purchase->purchase_id);
                    Utility::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);

                    //Warehouse Stock Report
                    if (isset($products[$i]['item'])) {
                        Utility::addWarehouseStock($products[$i]['item'], $products[$i]['quantity'], $request->warehouse_id);
                    }

                }
                $ven = Vender::where('id', $request->vender_id)->first();
                $data['id'] = $purchase->id;
                $data['no'] = $purchase->purchase_id;
                $data['date'] = $purchase->purchase_date;
                $data['reference'] = $purchaseProduct->purchase_date;
                $data['category'] = 'Purchase';
                $data['owned_by'] = $purchase->created_by;
                $data['created_by'] = $purchase->created_by;
                $data['user_id'] = $ven->account_id;
                $data['user_type'] = 'Vendor';
                $data['vender_account'] = $ven->account_id;
                $data['items'] = $newitems;

                foreach ($newitems as $item) {
                    if ($item instanceof \Closure) {
                        dd('Closure found in item');
                    }
                }
                $dataret = Utility::purchasejv($data);

                // dd($purchase,'egwd');
                $purchase->voucher_id = $dataret;
                $purchase->save();

                DB::commit();
                return redirect()->route('purchase.index', $purchase->id)->with('success', __('Purchase successfully created.'));
            } catch (\Exception $e) {
                DB::rollback();
                // dd($e);
                return redirect()->back()->with('error', $e);
            }
        } else {
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
            // $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 'expense')->get()->pluck('name', 'id');
            // $category->prepend('Select Category', '');
            $warehouse = warehouse::where('owned_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $purchase_number = \Auth::user()->purchaseNumberFormat($purchase->purchase_id);
            $venders = Vender::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');

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
                        return redirect()->route('purchase.index')->with('error', $messages->first());
                    }
                    $purchase->vender_id = $request->vender_id;
                    $purchase->purchase_date = $request->purchase_date;
                    $purchase->category_id = $request->category_id;
                    $purchase->save();
                    $products = $request->items;
                    // voucher get of this purchase order
                    $voucher = JournalEntry::where('category', 'Purchase')->where('reference_id', $purchase->id)->where('voucher_type', 'JV')->first();

                    if (!$voucher) {
                        DB::rollback();
                        return redirect()->back()->with('error', __('Purchase voucher not found. Please check the purchase entry.'));
                    }

                    $total_tax = 0;
                    $total_pay = 0;
                    // update only if amount not pay on purchase order
                    if ($purchase->status == 0 || $purchase->status == 1) {
                        for ($i = 0; $i < count($products); $i++) {
                            $purchaseProduct = PurchaseProduct::find($products[$i]['id']);

                            if ($purchaseProduct == null) {
                                $purchaseProduct = new PurchaseProduct();
                                $purchaseProduct->purchase_id = $purchase->id;
                                $purchaseProduct->quantity = $products[$i]['quantity'];
                                // new item quantity added in product
                                Utility::total_quantity('plus', $products[$i]['quantity'], $products[$i]['item']);
                                $old_qty = 0;
                                if (isset($products[$i]['item'])) {
                                    $purchaseProduct->product_id = $products[$i]['item'];
                                }
                                $purchaseProduct->tax = $products[$i]['tax'];
                                $purchaseProduct->discount = $products[$i]['discount'];
                                $purchaseProduct->price = $products[$i]['price'];
                                $purchaseProduct->description = $products[$i]['description'];
                                $purchaseProduct->save();
                                $product = ProductService::where('id', $purchaseProduct->product_id)->first();
                                // new item added in Voucher
                                $journalItem = new JournalItem();
                                $journalItem->journal = $voucher->id;
                                $journalItem->account = @$product->sale_chartaccount_id;
                                $journalItem->entry_id = @$purchaseProduct->id;
                                $journalItem->types = 'Purchase';
                                $journalItem->description = $product->name;
                                $journalItem->head_ids = $product->id;
                                $journalItem->branch_id = $purchase->created_by;
                                $journalItem->credit = 0;
                                $journalItem->debit = ($products[$i]['quantity'] * $products[$i]['price']) - $products[$i]['discount'];
                                $journalItem->save();
                                $total_pay += ($products[$i]['price'] * $products[$i]['quantity']) - ($products[$i]['discount']);
                                // new item if has tax then added in Voucher
                                $taxes = Tax::where('id',$product->tax_id)->first();
                                $total_tax += $products[$i]['itemTaxPrice'];
                                if($taxes && !empty($taxes->account_expance)){
                                    $journalItem = new JournalItem();
                                    $journalItem->journal = $voucher->id;
                                    $journalItem->account = $taxes->account_expance;
                                    $journalItem->types =  'Purchase';
                                    $journalItem->description = 'Tax on '.$product->id;
                                    $journalItem->head_ids = $product->id;
                                    $journalItem->branch_id = $purchase->created_by;
                                    $journalItem->debit = $products[$i]['itemTaxPrice'];
                                    $journalItem->credit = 0;
                                    $journalItem->save();
                                } else {
                                    DB::rollback();
                                    return redirect()->back()->with('error', __('Tax account not configured properly for the product tax.'));
                                }

                            } else {
                                $old_qty = $purchaseProduct->quantity;
                                Utility::total_quantity('minus', $purchaseProduct->quantity, $purchaseProduct->product_id);
                                if (isset($products[$i]['item'])) {
                                    $purchaseProduct->product_id = $products[$i]['item'];
                                }

                                $purchaseProduct->quantity = $products[$i]['quantity'];
                                $purchaseProduct->tax = $products[$i]['tax'];
                                $purchaseProduct->discount = $products[$i]['discount'];
                                $purchaseProduct->price = $products[$i]['price'];
                                $purchaseProduct->description = $products[$i]['description'];
                                $purchaseProduct->save();

                                $product = ProductService::where('id', $purchaseProduct->product_id)->first();
                                // already Voucher item update its values
                                $jouitem = JournalItem::where('journal', $voucher->id)->where('entry_id', $purchaseProduct->id)->where('types', 'Purchase')->first();
                                $jouitem->account = @$product->sale_chartaccount_id;
                                $jouitem->debit = ($products[$i]['price'] * $products[$i]['quantity']) - ($products[$i]['discount']);
                                $jouitem->save();
                                $total_pay += ($products[$i]['price'] * $products[$i]['quantity']) - ($products[$i]['discount']);
                                // already Voucher item tax update its values
                                $taxes = Tax::where('id',$product->tax_id)->first();
                                $total_tax += $products[$i]['itemTaxPrice'];
                                if($taxes && !empty($taxes->account_expance)){
                                    $jotax = JournalItem::where('journal', $voucher->id)
                                        ->where('account',$taxes->account_expance)
                                        ->where('description','Tax on '.$product->id)
                                        ->where('head_ids',$product->id)
                                        ->where('types','Purchase')
                                        ->first();
                                    if($jotax){
                                        $jotax->debit = $products[$i]['itemTaxPrice'];
                                        $jotax->save();
                                    } else {
                                        $journalItem = new JournalItem();
                                        $journalItem->journal = $voucher->id;
                                        $journalItem->account = $taxes->account_expance;
                                        $journalItem->types =  'Purchase';
                                        $journalItem->description = 'Tax on '.$product->id;
                                        $journalItem->head_ids = $product->id;
                                        $journalItem->branch_id = $purchase->created_by;
                                        $journalItem->debit = $products[$i]['itemTaxPrice'];
                                        $journalItem->credit = 0;
                                        $journalItem->save();
                                    }
                                } else {
                                    DB::rollback();
                                    return redirect()->back()->with('error', __('Tax account not configured properly for the product tax.'));
                                }
                            }



                            if ($products[$i]['id'] > 0) {
                                Utility::total_quantity('plus', $products[$i]['quantity'], $purchaseProduct->product_id);
                            }

                            //Product Stock Report
                            $type = 'purchase';
                            $type_id = $purchase->id;
                            StockReport::where('type', '=', 'purchase')->where('type_id', '=', $purchase->id)->delete();
                            $description = $products[$i]['quantity'] . '  ' . __(' quantity add in purchase') . ' ' . \Auth::user()->purchaseNumberFormat($purchase->purchase_id);

                            if (isset($products[$i]['item'])) {
                                Utility::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);
                            }

                            //Warehouse Stock Report
                            $new_qty = $purchaseProduct->quantity;
                            $total_qty = $new_qty - $old_qty;
                            if (isset($products[$i]['item'])) {
                                Utility::addWarehouseStock($products[$i]['item'], $total_qty, $request->warehouse_id);
                            }

                        }
                        $ven = Vender::find($request->vender_id);
                        if ($ven === null || empty($ven->account_id)) {
                            // already Voucher Payable update its values
                            $types = ChartOfAccountType::where('created_by', '=', $purchase->created_by)->where('name', 'Liabilities')->first();
                            if ($types) {
                                $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name', 'Payables')->first();
                                $account = ChartOfAccount::where('type', $types->id)->where('sub_type', $sub_type->id)->where('name', 'payable study pack')->first();
                                // if head not exist then create it
                                if ($account) {
                                } else {
                                    $account = new ChartOfAccount();
                                    $account->name = 'payable study pack';
                                    $account->code = '0';
                                    $account->type = $types->id;
                                    $account->sub_type = $sub_type->id;
                                    $account->description = 'payable study pack';
                                    $account->is_enabled = 1;
                                    $account->created_by = \Auth::user()->creatorId();
                                    $account->save();
                                }
                            }
                            if ($account) {
                                $item_last = JournalItem::where('journal', $voucher->id)->where('account', $account->id)->first();
                                $item_last->credit = ($total_pay + $total_tax);
                                $item_last->save();
                            }
                        } else {
                            $item_last = JournalItem::where('journal', $voucher->id)->where('account', $ven->account_id)->first();
                            $item_last->credit = ($total_pay + $total_tax);
                            $item_last->save();
                        }

                    } else {
                        return redirect()->route('purchase.index')->with('error', __('Purchase items cant change'));
                    }

                    // $data['id'] =$purchaseProduct->id;
                    // $data['no'] =$purchaseProduct->purchase_id;
                    // $data['date'] =$purchase->purchase_date;
                    // $data['reference'] =$purchaseProduct->purchase_date;
                    // $data['category'] = 'Purchase';
                    // $data['owned_by'] =$purchase->created_by;
                    // $data['created_by'] =$purchase->created_by;
                    // $data['items'] =$newitems;
                    // $dataret  = Utility::purchasejv($data);

                    DB::commit();
                    return redirect()->route('purchase.index')->with('success', __('Purchase successfully updated.'));
                } catch (\Exception $e) {
                    DB::rollback();
                    dd($e);
                    return redirect()->back()->with('error', $e);
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        } }
        catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
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
                    $purchasepayment = PurchasePayment::find($value->id)->first();
                    $purchasepayment->delete();
                }

                foreach ($purchase_products as $purchase_product) {
                    $warehouse_qty = WarehouseProduct::where('warehouse_id', $purchase->warehouse_id)->where('product_id', $purchase_product->product_id)->first();

                    $warehouse_transfers = WarehouseTransfer::where('product_id', $purchase_product->product_id)->where('from_warehouse', $purchase->warehouse_id)->get();
                    foreach ($warehouse_transfers as $warehouse_transfer) {
                        $temp = WarehouseProduct::where('warehouse_id', $warehouse_transfer->to_warehouse)->first();
                        if ($temp) {
                            $temp->quantity = $temp->quantity - $warehouse_transfer->quantity;
                            if ($temp->quantity > 0) {
                                $temp->save();
                            } else {
                                $temp->delete();
                            }

                        }
                    }
                    if (!empty($warehouse_qty)) {
                        $warehouse_qty->quantity = $warehouse_qty->quantity - $purchase_product->quantity;
                        if ($warehouse_qty->quantity > 0) {
                            $warehouse_qty->save();
                        } else {
                            $warehouse_qty->delete();
                        }
                    }
                    $product_qty = ProductService::where('id', $purchase_product->product_id)->first();
                    if (!empty($product_qty)) {
                        $product_qty->quantity = $product_qty->quantity - $purchase_product->quantity;
                        $product_qty->save();
                    }
                    $purchase_product->delete();

                }

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

                $bankAccount = BankAccount::find($request->account_id);
                $data['id'] = $purchasePayment->id;
                $data['date'] = $purchasePayment->date;
                $data['reference'] = $request->reference;
                $data['description'] = $purchasePayment->purchase_id;
                $data['prod_id'] = $purchasePayment->id;
                $data['amount'] = $purchasePayment->amount;
                $data['category'] = 'Purchase';
                $data['owned_by'] = $purchasePayment->created_by;
                $data['created_by'] = $purchasePayment->created_by;
                $data['account_id'] = $bankAccount->chart_account_id;
                $data['user_id'] = $vender->id;
                $data['user_type'] = 'Vendor';
                $data['vender_account'] = $vender->account_id;
                if (strtolower($bankAccount->bank_name) == 'cash' || strtolower($bankAccount->holder_name) == 'cash') {
                    $dataret = Utility::cpv_entry($data);
                } else {
                    $dataret = Utility::bpv_entry($data);
                }
                PurchasePayment::where('id', $purchasePayment->id)->update([
                    'voucher_id' => $dataret,
                ]);

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
                    $warehouse_id = $purchase->warehouse_id;
                    $ware_pro = WarehouseProduct::where('warehouse_id', $warehouse_id)->where('product_id', $res->product_id)->first();

                    $qty = $ware_pro->quantity;
                    // if($res->quantity == $qty || $res->quantity > $qty)
                    // {
                    //     $ware_pro->delete();
                    // }
                    // elseif($res->quantity < $qty)
                    // {
                    $ware_pro->quantity = $qty - $res->quantity;
                    $ware_pro->save();
                    // }
                    // Deleting voucher entry
                    $voucher = JournalEntry::where('category', 'Purchase')->where('reference_id', $purchase->id)->where('voucher_type', 'JV')->first();
                    $item = JournalItem::where('journal', $voucher->id)->where('entry_id', $res->id)->delete();

                    $value = ($res->price * $res->quantity) - ($res->discount);
                    $taxPrice = 0;
                    $tax = Tax::find($res->tax);
                    if ($tax) {
                        $taxPrice = ($tax->rate / 100) * ($res->price * $res->quantity);
                        $jotax = JournalItem::where('journal', $voucher->id)->where('account', $tax->account_expance)->where('description', 'Tax on ' . $res->product_id)->where('head_ids', $res->product_id)->where('types', 'Purchase')->delete();
                    }
                    //    less amount from payable
                    $types = ChartOfAccountType::where('created_by', '=', $purchase->created_by)->where('name', 'Liabilities')->first();
                    if ($types) {
                        $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name', 'Current Liabilities')->first();
                        $account = ChartOfAccount::where('type', $types->id)->where('sub_type', $sub_type->id)->where('name', 'Account Payable')->first();
                    }
                    $item_last = JournalItem::where('journal', $voucher->id)->where('account', $account->id)->first();
                    $item_last->credit = $item_last->credit - ($value + $taxPrice);
                    $item_last->save();

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


}
