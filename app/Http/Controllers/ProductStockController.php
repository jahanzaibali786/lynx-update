<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceSubCategory;
use App\Exports\ProductStockReportExport;
use App\Models\ProductStock;
use App\Models\Utility;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage product & service')) {
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
                ->where('type', 'product & service')
                ->get()
                ->pluck('name', 'id');
            $category->prepend('Select Category', '');
            $subcategory = ProductServiceSubCategory::where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $subcategory->prepend('Select Sub-Category', '');
            $query = ProductService::with('subcategory')->where('type', '=', 'product')->where('created_by', \Auth::user()->creatorId());
            if (!empty($request->category)) {
                $query->where('category_id', $request->category);
            }
            if (!empty($request->subcategory)) {
                $query->whereHas('subcategory', function ($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }
            $productServices = $query->get();
            $viewData = [
                'productServices' => $productServices,
            ];
            if (!empty($request->print) && $request->print == 'true') {
                $html = view('productstock.printlist', $viewData)->render();
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
                $options->set('enable_php', true); 
                // Initialize DOMPDF and generate the PDF
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();

                // // Fetch PDF content as base64
                // $pdfContent = $dompdf->output();
                // $base64Pdf = base64_encode($pdfContent);

                return $dompdf->stream('product_stock.pdf', ['Attachment' => false]);
            }
            if ($request->has('export') && $request->export == 'excel') {
                $productServices = $query->get();
                return Excel::download(new ProductStockReportExport($productServices), 'product_stock_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $productServices = $query->get();
                return Excel::download(new ProductStockReportExport($productServices), 'product_stock_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            $productServices = $query->paginate(25);
            return view('productstock.index', compact('productServices', 'category', 'subcategory'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\ProductStock $productStock
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ProductStock $productStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ProductStock $productStock
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $productService = ProductService::find($id);
        if (\Auth::user()->can('edit product & service')) {
            if ($productService->created_by == \Auth::user()->creatorId()) {
                return view('productstock.edit', compact('productService'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ProductStock $productStock
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);
            $total = $productService->quantity + $request->quantity;

            if ($productService->created_by == \Auth::user()->creatorId()) {
                $productService->quantity = $total;
                $productService->created_by = \Auth::user()->creatorId();
                $productService->save();

                //Product Stock Report
                $type = 'manually';
                $type_id = 0;
                $description = $request->quantity . '  ' . __('quantity added by manually');
                Utility::addProductStock($productService->id, $request->quantity, $type, $description, $type_id);


                return redirect()->route('productstock.index')->with('success', __('Product quantity updated manually.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ProductStock $productStock
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductStock $productStock)
    {
        //
    }
}
