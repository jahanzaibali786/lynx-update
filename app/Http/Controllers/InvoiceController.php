<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceProductExport;
use App\Exports\InvoiceExport;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceBankTransfer;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Plan;
use App\Models\Products;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Roomassign;
use App\Models\StockReport;
use App\Models\Vender;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Space;
use App\Models\StudentRegistration;
use App\Models\Tax;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage invoice')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                // $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::where('owned_by', '=', \Auth::user()->ownedId());
            }

            if (!empty($request->branches)) {
                // $query->where('owned_by', '=', $request->branches);
                $store = warehouse::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
                // $store->prepend('Select Store', '');
            }
            if (!empty($request->store)) {
                $query->where('to_store', '=', $request->store);
            }
            if (count(explode('to', $request->issue_date)) > 1) {
                $date_range = explode(' to ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            } elseif (!empty($request->issue_date)) {
                $date_range = [$request->issue_date, $request->issue_date];
                $query->whereBetween('issue_date', $date_range);
            }
            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }

            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'store', 'status', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function inv_rep(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage invoice')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                // $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::where('created_by', '=', \Auth::user()->creatorId());
                $store_to = warehouse::get()->pluck('name', 'id');
                $store_to->prepend('Select Store', '');
                $brnches_name = User::where('id', $request->branches)->first();
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::where('owned_by', '=', \Auth::user()->ownedId());
                $store_to = warehouse::get()->pluck('name', 'id');
                $store_to->prepend('Select Store', '');
                $brnches_name = User::where('id', $request->branches)->first();
            }

            if (!empty($request->branches)) {
                // $query->where('owned_by', '=', $request->branches);
                $store = warehouse::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
                // $store->prepend('Select Store', '');
            }
            if (!empty($request->store)) {
                $query->where('from_store', '=', $request->store);
            }
            if (!empty($request->store_to)) {
                $query->where('to_store', '=', $request->store_to);
            }
            if (!empty($request->start_date)) {
                $query->whereDate('issue_date', '>', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->whereDate('issue_date', '<', $request->end_date);
            }
            // if (count(explode('to', $request->issue_date)) > 1) {
            //     $date_range = explode(' to ', $request->issue_date);
            //     $query->whereBetween('issue_date', $date_range);
            // } elseif (!empty($request->issue_date)) {
            //     $date_range = [$request->issue_date, $request->issue_date];
            //     $query->whereBetween('issue_date', $date_range);
            // }
            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }

            if (empty($request->start_date) || empty($request->end_date)) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
                $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

                $request->merge(['start_date' => $dateFrom]);
                $request->merge(['end_date' => $dateTo]);
                $query->whereBetween('issue_date', [$dateFrom, $dateTo]);
            }

            $invoices = $query->get()->groupBy('to_store');
            $store_to = warehouse::get()->pluck('name', 'id');
            $store_to->prepend('Select Store', '');
                        if ($request->has('export') && $request->export == 'excel') {
                $invoices = $query->get();
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);

                $data = [
                    'invoices' => $invoices,
                    'store' => $store,
                    'status' => $status,
                    'branches' => $branches,
                    'brnches_name' => $brnches_name,
                    'request' => $request,
                    'store_to' => $store_to,
                ];
                return Excel::download(new InvoiceExport($data, $request->all()), 'invoice_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $invoices = $query->get();
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);

                $data = [
                    'invoices' => $invoices,
                    'store' => $store,
                    'status' => $status,
                    'branches' => $branches,
                    'brnches_name' => $brnches_name,
                    'request' => $request,
                    'store_to' => $store_to,
                ];
                return Excel::download(new InvoiceExport($data, $request->all()), 'invoice_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            return view('invoice.invoice_report', compact('invoices', 'store', 'status', 'branches', 'brnches_name', 'request', 'store_to'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function invoiceReport(Request $request)
    {
        // if (\Auth::user()->can('manage invoice')) {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            // $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $store = warehouse::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $store->prepend('Select Store', '');
            $status = Invoice::$statues;
            $query = Invoice::where('created_by', '=', \Auth::user()->creatorId());
            $store_to = warehouse::get()->pluck('name', 'id');
            $store_to->prepend('Select Store', '');
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $store = warehouse::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $store->prepend('Select Store', '');
            $status = Invoice::$statues;
            $query = Invoice::where('owned_by', '=', \Auth::user()->ownedId());
            $store_to = warehouse::get()->pluck('name', 'id');
            $store_to->prepend('Select Store', '');
            $brnches_name = User::where('id', $request->branches)->first();
        }

        if (!empty($request->branches)) {
            // $query->where('owned_by', '=', $request->branches);
            $store = warehouse::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            // $store->prepend('Select Store', '');
        }
        if (!empty($request->store)) {
            $query->where('from_store', '=', $request->store);
        }
        if (!empty($request->store_to)) {
            $query->where('to_store', '=', $request->store_to);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('issue_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('issue_date', '<', $request->end_date);
        }
        if (!empty($request->status)) {
            $query->where('status', '=', $request->status);
        }

        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('issue_date', [$dateFrom, $dateTo]);
        }

        $invoices = $query->get()->groupBy('to_store');
        $store_to = warehouse::get()->pluck('name', 'id');
        $store_to->prepend('Select Store', '');
        $report_name = 'Invoice Balance Report';
        $pdf = new Dompdf();
        $html = view('invoice.invoice_pdf.report', compact('invoices', 'store', 'status', 'branches', 'brnches_name', 'request', 'store_to'))->render();
        $headerHtml = view('invoice.report.pdf.header', compact('invoices', 'report_name', 'brnches_name', 'request'))->render();
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

        // return $dompdf->stream('class_wise_fee.pdf');
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
    public function invoiceProductReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            // $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $store = warehouse::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $store->prepend('Select Store', '');
            $status = Invoice::$statues;
            $query = Invoice::with('items', 'items.products')->where('created_by', '=', \Auth::user()->creatorId());
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $store = warehouse::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $store->prepend('Select Store', '');
            $status = Invoice::$statues;
            $query = Invoice::with('items', 'items.products')->where('owned_by', '=', \Auth::user()->ownedId());
            $brnches_name = User::where('id', $request->branches)->first();
        }

        if (!empty($request->branches)) {
            $store = warehouse::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        if (!empty($request->store)) {
            $query->where('to_store', '=', $request->store);
        }
        if (!empty($request->store_to)) {
            $query->where('to_store', '=', $request->store_to);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('issue_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('issue_date', '<', $request->end_date);
        }
        // if (count(explode('to', $request->issue_date)) > 1) {
        //     $date_range = explode(' to ', $request->issue_date);
        //     $query->whereBetween('issue_date', $date_range);
        // } elseif (!empty($request->issue_date)) {
        //     $date_range = [$request->issue_date, $request->issue_date];
        //     $query->whereBetween('issue_date', $date_range);
        // }
        if (empty($request->start_date) || empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('issue_date', [$dateFrom, $dateTo]);
        }
        if (!empty($request->status)) {
            $query->where('status', '=', $request->status);
        }

        $invoices = $query->get()->groupBy('to_store');
        $store_to = warehouse::get()->pluck('name', 'id');
        $store_to->prepend('Select Store', '');
        $report_name = 'Invoice Report';
        $pdf = new Dompdf();
        $html = view('invoice.invoice_product_pdf.report', compact('invoices', 'store', 'status', 'branches', 'brnches_name', 'request', 'store_to'))->render();
        $headerHtml = view('invoice.report.pdf.header', compact('invoices', 'brnches_name', 'request', 'report_name'))->render();
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

        // return $dompdf->stream('class_wise_fee.pdf');
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
    public function inv_pro_rep(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage invoice')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                // $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::with('items', 'items.products')->where('created_by', '=', \Auth::user()->creatorId());
                $brnches_name = User::where('id', $request->branches)->first();
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $store = warehouse::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $store->prepend('Select Store', '');
                $status = Invoice::$statues;
                $query = Invoice::with('items', 'items.products')->where('owned_by', '=', \Auth::user()->ownedId());
                $brnches_name = User::where('id', $request->branches)->first();
            }

            if (!empty($request->branches)) {
                $store = warehouse::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            }
            if (!empty($request->store)) {
                $query->where('to_store', '=', $request->store);
            }
            if (!empty($request->store_to)) {
                $query->where('to_store', '=', $request->store_to);
            }
            if (!empty($request->start_date)) {
                $query->whereDate('issue_date', '>', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->whereDate('issue_date', '<', $request->end_date);
            }
            // if (count(explode('to', $request->issue_date)) > 1) {
            //     $date_range = explode(' to ', $request->issue_date);
            //     $query->whereBetween('issue_date', $date_range);
            // } elseif (!empty($request->issue_date)) {
            //     $date_range = [$request->issue_date, $request->issue_date];
            //     $query->whereBetween('issue_date', $date_range);
            // }
            if (empty($request->start_date) || empty($request->end_date)) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
                $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

                $request->merge(['start_date' => $dateFrom]);
                $request->merge(['end_date' => $dateTo]);
                $query->whereBetween('issue_date', [$dateFrom, $dateTo]);
            }
            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }

            $invoices = $query->get()->groupBy('to_store');
            $store_to = warehouse::get()->pluck('name', 'id');
            $store_to->prepend('Select Store', '');

                        if ($request->has('export') && $request->export == 'excel') {
                $invoices = $query->get();
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);

                $data = [
                    'invoices' => $invoices,
                    'store' => $store,
                    'status' => $status,
                    'branches' => $branches,
                    'brnches_name' => $brnches_name,
                    'request' => $request,
                    'store_to' => $store_to,
                ];
                return Excel::download(new InvoiceProductExport($data, $request->all()), 'invoice_product_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $invoices = $query->get();
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);

                $data = [
                    'invoices' => $invoices,
                    'store' => $store,
                    'status' => $status,
                    'branches' => $branches,
                    'brnches_name' => $brnches_name,
                    'request' => $request,
                    'store_to' => $store_to,
                ];
                return Excel::download(new InvoiceProductExport($data, $request->all()), 'invoice_product_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }

            return view('invoice.invoice_product_report', compact('invoices', 'store', 'status', 'branches', 'brnches_name', 'request', 'store_to'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create($customerId)
    {
        if (\Auth::user()->can('create invoice')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            if (\Auth::user()->type == 'company') {
                $store_from = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
                $store_to = warehouse::where('id', '!=', $store_from->id)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());

                // $product_services  = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                // ->where('warehouse_products.warehouse_id', $store_from->id)
                // ->where('warehouse_products.created_by', \Auth::user()->creatorId())
                // ->get(['product_services.name', 'warehouse_products.product_id',  'product_services.sku','warehouse_products.quantity'])
                // ->mapWithKeys(function ($item) use (&$index) {
                //      $index++;
                //     return [ $index => ['id' => $item->product_id,'name' => $item->sku.'-'.$item->name .' ( '.$item->quantity.' )', 'quantity' => $item->quantity]];
                // })->toArray();
                $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');

            } else {
                $store_from = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
                $store_to = warehouse::where('id', '!=', $store_from->id)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());
                // $product_services  = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                // ->where('warehouse_products.warehouse_id', $store_from->id)
                // ->where('warehouse_products.created_by', \Auth::user()->creatorId())
                // ->get(['product_services.name', 'warehouse_products.product_id', 'product_services.sku','warehouse_products.quantity'])
                // ->mapWithKeys(function ($item) use (&$index) {
                //      $index++;
                //     return [ $index => ['id' => $item->product_id,'name' => $item->sku.'-'. $item->name .' ( '.$item->quantity.' )', 'quantity' => $item->quantity]];
                // })
                // ->toArray();
                $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
            }
            // $product_services = Space::where('created_by', \Auth::user()->creatorId())->where('meeting','yes')->get()->pluck('name', 'id');
            $product_services->prepend("Select item", '');

            return view('invoice.create', compact('invoice_number', 'product_services', 'store_from', 'store_to', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function customer(Request $request)
    {
        $customer = Customer::where('id', '=', $request->id)->first();
        return view('invoice.customer_detail', compact('customer'));
    }

    public function product(Request $request)
    {
        $data['product'] = $product = ProductService::find($request->product_id);

        $data['unit'] = (!empty($product->unit())) ? $product->unit()->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->sale_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);
// Add stock quantities for all three types
        $data['stock_new'] = $product->quantity ?? 0;
        $data['stock_used'] = $product->used_quantity ?? 0;
        $data['stock_damaged'] = $product->damaged_quantity ?? 0;

        return json_encode($data);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('create invoice')) {
            DB::beginTransaction();
            try {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        // 'customer_id' => 'required',
                        'issue_date' => 'required',
                        'due_date' => 'required',
                        'store_from' => 'required',
                        'store_to' => 'required',
                        'items' => 'required',
                    ]
                );
                $data = [];

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }


                $status = Invoice::$statues;
                $invoice = new Invoice();
                $invoice->invoice_id = $this->invoiceNumber();
                $invoice->status = 0;
                $invoice->issue_date = $request->issue_date;
                $invoice->due_date = $request->due_date;
                // $invoice->category_id    = $request->category_id;
                $invoice->ref_number = $request->ref_number;
                $invoice->from_store = $request->store_from;
                $invoice->to_store = $request->store_to;
                //            $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
                $invoice->owned_by = \Auth::user()->ownedId();
                $invoice->created_by = \Auth::user()->creatorId();
                $invoice->save();
                CustomField::saveData($invoice, $request->customField);
                $products = $request->items;
                $newitems = $request->items;

                for ($i = 0; $i < count($products); $i++) {

                    $invoiceProduct = new InvoiceProduct();
                    $invoiceProduct->invoice_id = $invoice->id;
                    $invoiceProduct->product_id = $products[$i]['item'];
                    $invoiceProduct->quantity = $products[$i]['quantity'];
                    $invoiceProduct->tax = $products[$i]['tax'] ?? 0;
                    $invoiceProduct->discount = $products[$i]['discount'] ?? 0;
                    $invoiceProduct->price = $products[$i]['price'];
                    // $invoiceProduct->description = $products[$i]['description'];
                    $invoiceProduct->type = $products[$i]['type'] ?? 'new';
                    $invoiceProduct->save();

                    // Deduct stock based on type
                    $product = ProductService::find($products[$i]['item']);
                    if ($product) {
                        $type = $products[$i]['type'] ?? 'new';
                        $qty = $products[$i]['quantity'];

                        switch($type) {
                            case 'new':
                                $product->quantity = max(0, ($product->quantity ?? 0) - $qty);
                                break;
                            case 'use':
                                $product->used_quantity = max(0, ($product->used_quantity ?? 0) - $qty);
                                break;
                            case 'damage':
                                $product->damaged_quantity = max(0, ($product->damaged_quantity ?? 0) - $qty);
                                break;
                        }
                        $product->save();
                    }

                    $newitems[$i]['prod_id'] = $invoiceProduct->id;
                    Utility::warehouse_transfer_qty($request->store_from, $request->store_to, $products[$i]['item'], $products[$i]['quantity']);
                    $description = $products[$i]['quantity'] . '  ' . __(' quantity sold in invoice') . ' ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                    // Utility::addProductStock($products[$i]['item'], $products[$i]['quantity'], 'invoice', $description, $invoice->id);
                }
                // $data['id'] =$invoice->id;
                // $data['date'] =$invoice->issue_date;
                // $data['reference'] =$invoice->ref_number;
                // $data['category'] = 'Invoice';
                // $data['owned_by'] =$invoice->owned_by;
                // $data['created_by'] =$invoice->created_by;
                // $data['items'] =$request->items;
                // $dataret  = Utility::jrentry($data);

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
                $dataret = Utility::invoicejv($data);
                DB::commit();
                //webhook
                $module = 'New Invoice';
                $webhook = Utility::webhookSetting($module);

                DB::commit();
                return redirect()->route('invoice.index', $invoice->id)->with('success', __('Invoice successfully created.'));
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', 'Something went wrong !');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($ids)
    {
        if (\Auth::user()->can('edit invoice')) {
            $id = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
            if (\Auth::user()->type == 'company') {
                $store_from = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
                $store_to = warehouse::where('id', '!=', $store_from->id)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());

                // $product_services  = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                // ->where('warehouse_products.warehouse_id', $store_from->id)
                // ->where('warehouse_products.created_by', \Auth::user()->creatorId())
                // ->get(['product_services.name', 'warehouse_products.product_id',  'product_services.sku','warehouse_products.quantity'])
                // ->mapWithKeys(function ($item) use (&$index) {
                //      $index++;
                //     return [ $index => ['id' => $item->product_id,'name' => $item->sku.'-'.$item->name .' ( '.$item->quantity.' )', 'quantity' => $item->quantity]];
                // })->toArray();
                $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');

            } else {
                $store_from = warehouse::where('owned_by', \Auth::user()->creatorId())->first();
                $store_to = warehouse::where('id', '!=', $store_from->id)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $invoice_number = \Auth::user()->invoiceNumberFormat($this->invoiceNumber());
                // $product_services  = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
                // ->where('warehouse_products.warehouse_id', $store_from->id)
                // ->where('warehouse_products.created_by', \Auth::user()->creatorId())
                // ->get(['product_services.name', 'warehouse_products.product_id', 'product_services.sku','warehouse_products.quantity'])
                // ->mapWithKeys(function ($item) use (&$index) {
                //      $index++;
                //     return [ $index => ['id' => $item->product_id,'name' => $item->sku.'-'. $item->name .' ( '.$item->quantity.' )', 'quantity' => $item->quantity]];
                // })
                // ->toArray();
                $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                    ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
            }
            // $product_services = Space::where('created_by', \Auth::user()->creatorId())->where('meeting','yes')->get()->pluck('name', 'id');
            $product_services->prepend("Select item", '');
            // dd($invoice);
            // $cust = Customer::find($invoice->customer_id);
            // $cont = Contract::where('company_id', $cust->company_id)->get();

            return view('invoice.edit', compact('product_services', 'invoice', 'invoice_number', 'store_from', 'store_to', 'customFields'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Invoice $invoice)
    {

        if (\Auth::user()->can('edit invoice')) {
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        // 'customer_id' => 'required',
                        'issue_date' => 'required',
                        'due_date' => 'required',
                        'items' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('invoice.index')->with('error', $messages->first());
                }

                // $invoice->customer_id    = $request->customer_id;
                $invoice->issue_date = $request->issue_date;
                $invoice->due_date = $request->due_date;
                $invoice->ref_number = $request->ref_number;
                //                $invoice->discount_apply = isset($request->discount_apply) ? 1 : 0;
                $invoice->category_id = $request->category_id;
                $invoice->save();

                // Utility::starting_number( $invoice->invoice_id + 1, 'invoice');
                CustomField::saveData($invoice, $request->customField);
                $products = $request->items;
                $data = [];
                $newitems = $request->items;
                for ($i = 0; $i < count($products); $i++) {
                    $invoiceProduct = InvoiceProduct::find($products[$i]['id']);
                    $oldQuantity = 0;
                    $oldType = 'new';

                    if ($invoiceProduct == null) {
                        $invoiceProduct = new InvoiceProduct();
                        $invoiceProduct->invoice_id = $invoice->id;

                        Utility::total_quantity('minus', $products[$i]['quantity'], $products[$i]['item']);

                        $updatePrice = ($products[$i]['price'] * $products[$i]['quantity']) + ($products[$i]['itemTaxPrice']);
                    } else {
                        // Store old values to restore stock
                        $oldQuantity = $invoiceProduct->quantity;
                        $oldType = $invoiceProduct->type ?? 'new';

                        // Restore old stock
                        $product = ProductService::find($invoiceProduct->product_id);
                        if ($product) {
                            switch($oldType) {
                                case 'new':
                                    $product->quantity = ($product->quantity ?? 0) + $oldQuantity;
                                    break;
                                case 'use':
                                    $product->used_quantity = ($product->used_quantity ?? 0) + $oldQuantity;
                                    break;
                                case 'damage':
                                    $product->damaged_quantity = ($product->damaged_quantity ?? 0) + $oldQuantity;
                                    break;
                            }
                            $product->save();
                        }

                        Utility::total_quantity('plus', $invoiceProduct->quantity, $invoiceProduct->product_id);
                    }

                    if (isset($products[$i]['item'])) {
                        // dd($products);
                        $invoiceProduct->product_id = $products[$i]['item'];
                    }

                    $invoiceProduct->quantity = $products[$i]['quantity'];
                    $invoiceProduct->tax = $products[$i]['tax'];
                    //                    $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                    $invoiceProduct->price = $products[$i]['price'];
                    $invoiceProduct->type = $products[$i]['type'] ?? 'new';
                    $invoiceProduct->save();

                    // Deduct new stock based on type
                    $product = ProductService::find($products[$i]['item']);
                    if ($product) {
                        $type = $products[$i]['type'] ?? 'new';
                        $qty = $products[$i]['quantity'];

                        switch($type) {
                            case 'new':
                                $product->quantity = max(0, ($product->quantity ?? 0) - $qty);
                                break;
                            case 'use':
                                $product->used_quantity = max(0, ($product->used_quantity ?? 0) - $qty);
                                break;
                            case 'damage':
                                $product->damaged_quantity = max(0, ($product->damaged_quantity ?? 0) - $qty);
                                break;
                        }
                        $product->save();
                    }

                    // Utility::total_quantity('plus',$products[$i]['quantity'],$invoiceProduct->product_id);
                    if ($products[$i]['id'] > 0) {
                        Utility::total_quantity('minus', $products[$i]['quantity'], $invoiceProduct->product_id);
                    }


                    //Product Stock Report
                    $type = 'invoice';
                    $type_id = $invoice->id;
                    StockReport::where('type', '=', 'invoice')->where('product_id', $products[$i]['item'])->where('type_id', '=', $invoice->id)->delete();
                    $description = $products[$i]['quantity'] . '  ' . __(' quantity sold in invoice') . ' ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                    // Utility::addProductStock($products[$i]['item'], $products[$i]['quantity'], $type, $description, $type_id);

                    Utility::warehouse_transfer_qty($request->store_from, $request->store_to, $products[$i]['item'], $products[$i]['quantity']);

                    //data items
                    $newitems[$i]['prod_id'] = $invoiceProduct->id;
                }
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

                // $dataret = Utility::invoicejv($data);
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    function invoiceNumber()
    {
        // if(\Auth::user()->type == ('company')){
        //     $latest = Invoice::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        // }else{
        $latest = Invoice::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();
        // }
        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function show($ids)
    {

        if (\Auth::user()->can('show invoice')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invoice Not Found.'));
            }
            $id = Crypt::decrypt($ids);
            $invoice = Invoice::find($id);

            if (!empty($invoice->created_by) == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();

                $customer = $invoice->customer;
                $iteams = $invoice->items;
                $user = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                $user_plan = Plan::find($invoice_user->plan);
                // end for storage limit note

                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'invoice_user', 'user_plan'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Invoice $invoice, Request $request)
    {
        if (\Auth::user()->can('delete invoice')) {
            if ($invoice->created_by == \Auth::user()->creatorId()) {
                foreach ($invoice->payments as $invoices) {
                    Utility::bankAccountBalance($invoices->account_id, $invoices->amount, 'debit');

                    $invoicepayment = InvoicePayment::find($invoices->id);
                    $invoices->delete();
                    $invoicepayment->delete();
                }

                if ($invoice->customer_id != 0 && $invoice->status != 0) {
                    Utility::updateUserBalance('customer', $invoice->customer_id, $invoice->getDue(), 'debit');
                }

                CreditNote::where('invoice', '=', $invoice->id)->delete();

                InvoiceProduct::where('invoice_id', '=', $invoice->id)->delete();
                $invoice->delete();
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function productDestroy(Request $request)
    {
        if (\Auth::user()->can('delete invoice product')) {
            $invoiceProduct = InvoiceProduct::find($request->id);
            $invoice = Invoice::find($invoiceProduct->invoice_id);

            $voucher = JournalEntry::where('category', 'Invoice')->where('description', "Invoice id : {$invoice->id}")->first();
            $product = ProductService::where('id', $invoiceProduct->product_id)->first();
            $item = JournalItem::where('journal', $voucher->id)->where('account', $product->sale_chartaccount_id)->delete();
            $types = ChartOfAccountType::where('created_by', '=', $invoice->created_by)->where('name', 'Assets')->first();
            if ($types) {
                $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name', 'Current Asset')->first();
                $account = ChartOfAccount::where('type', $types->id)->where('sub_type', $sub_type->id)->where('name', 'Account Receivables')->first();
            }
            $item_last = JournalItem::where('journal', $voucher->id)->where('account', $account->id)->first();
            $item_last->debit = $item_last->debit - $request->amount;
            $item_last->save();

            Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');

            InvoiceProduct::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Invoice product successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function customerInvoice(Request $request)
    {
        if (\Auth::user()->can('manage customer invoice')) {

            $status = Invoice::$statues;
            $query = Invoice::where('customer_id', '=', \Auth::user()->id)->where('status', '!=', '0')->where('created_by', \Auth::user()->creatorId());

            if (!empty($request->issue_date)) {
                $date_range = explode(' - ', $request->issue_date);
                $query->whereBetween('issue_date', $date_range);
            }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $invoices = $query->get();

            return view('invoice.index', compact('invoices', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function customerInvoiceShow($id)
    {

        $invoice = Invoice::where('id', $id)->first();
        $user = User::where('id', $invoice->created_by)->first();
        if ($invoice->created_by == $user->creatorId()) {
            $customer = $invoice->customer;
            $iteams = $invoice->items;

            if ($user->type == 'super admin') {
                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'user'));
            } elseif ($user->type == 'company' || $user->type == 'branch') {
                return view('invoice.customer_invoice', compact('invoice', 'customer', 'iteams', 'user'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sent($id)
    {

        if (\Auth::user()->can('send invoice')) {
            // Send Email
            $setings = Utility::settings();

            if ($setings['customer_invoice_sent'] == 1) {
                $invoice = Invoice::where('id', $id)->first();
                $invoice->send_date = date('Y-m-d');
                $invoice->status = 1;
                $invoice->save();

                $customer = Customer::where('id', $invoice->customer_id)->first();
                $invoice->name = !empty($customer) ? $customer->name : '';
                $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

                $invoiceId = Crypt::encrypt($invoice->id);
                $invoice->url = route('invoice.pdf', $invoiceId);

                // Utility::updateUserBalance('customer', $customer->id, $invoice->getTotal(), 'credit');

                // $customerArr = [

                //     'customer_name' => $customer->name,
                //     'customer_email' => $customer->email,
                //     'invoice_name' => $customer->name,
                //     'invoice_number' => $invoice->invoice,
                //     'invoice_url' => $invoice->url,

                // ];
                // $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $customerArr);


                return redirect()->back()->with('success', __('Invoice successfully sent.'));
                // return redirect()->back()->with('success', __('Invoice successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function resent($id)
    {
        if (\Auth::user()->can('send invoice')) {
            $invoice = Invoice::where('id', $id)->first();

            $customer = Customer::where('id', $invoice->customer_id)->first();
            $invoice->name = !empty($customer) ? $customer->name : '';
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $invoiceId = Crypt::encrypt($invoice->id);
            $invoice->url = route('invoice.pdf', $invoiceId);
            $customerArr = [

                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'invoice_name' => $customer->name,
                'invoice_number' => $invoice->invoice,
                'invoice_url' => $invoice->url,

            ];
            $resp = Utility::sendEmailTemplate('customer_invoice_sent', [$customer->id => $customer->email], $customerArr);

            return redirect()->back()->with('success', __('Invoice successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payment($invoice_id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $invoice = Invoice::where('id', $invoice_id)->first();
            if (\Auth::user()->type == 'company') {
                $customers = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $customers = Customer::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            return view('invoice.payment', compact('customers', 'categories', 'accounts', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function createPayment(Request $request, $invoice_id)
    {

        if (\Auth::user()->can('create payment invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_from' => 'required',
                    'account_to' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            DB::beginTransaction();
            $data = [];
            try {
                $invoicePayment = new InvoicePayment();
                $invoicePayment->invoice_id = $invoice_id;
                $invoicePayment->date = $request->date;
                $invoicePayment->amount = $request->amount;
                $invoicePayment->account_id = $request->account_from;
                $invoicePayment->account_to = $request->account_to;
                $invoicePayment->payment_method = 0;
                $invoicePayment->reference = $request->reference;
                $invoicePayment->description = $request->description;
                if (!empty($request->add_receipt)) {
                    //storage limit
                    $image_size = $request->file('add_receipt')->getSize();
                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                    if ($result == 1) {
                        $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                        $request->add_receipt->storeAs('uploads/payment', $fileName);
                        $invoicePayment->add_receipt = $fileName;
                    }
                }

                $invoicePayment->save();

                $invoice = Invoice::where('id', $invoice_id)->first();
                $due = $invoice->getDue();
                $total = $invoice->getTotal();
                if ($invoice->status == 0) {
                    $invoice->send_date = date('Y-m-d');
                    $invoice->save();
                }

                if ($due <= 0) {
                    $invoice->status = 4;
                    $invoice->save();
                } else {
                    $invoice->status = 3;
                    $invoice->save();
                }
                $invoicePayment->user_id = $invoice->owned_by;
                $invoicePayment->user_type = 'Branch';
                $invoicePayment->type = 'Partial';
                $invoicePayment->owned_by = \Auth::user()->ownedId();
                $invoicePayment->created_by = \Auth::user()->id;
                $invoicePayment->payment_id = $invoicePayment->id;
                $invoicePayment->category = 'Invoice';
                $invoicePayment->account = $request->account_from;

                Transaction::addTransaction($invoicePayment);
                $customer = User::where('id', $invoice->owned_by)->first();


                $payment = new InvoicePayment();
                $payment->name = $customer['name'];
                $payment->date = \Auth::user()->dateFormat($request->date);
                $payment->amount = \Auth::user()->priceFormat($request->amount);
                $payment->invoice = 'invoice ' . \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
                $payment->dueAmount = \Auth::user()->priceFormat($invoice->getDue());

                // Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');

                Utility::bankAccountBalance($request->account_from, $request->amount, 'credit');
                Utility::bankAccountBalance($request->account_to, $request->amount, 'debit');

                // Send Email
                // $setings = Utility::settings();
                // if ($setings['new_invoice_payment'] == 1) {
                //     $customer = Customer::where('id', $invoice->customer_id)->first();
                //     $invoicePaymentArr = [
                //         'invoice_payment_name'   => $customer->name,
                //         'invoice_payment_amount'   => $payment->amount,
                //         'invoice_payment_date'  => $payment->date,
                //         'payment_dueAmount'  => $payment->dueAmount,

                //     ];

                //     $resp = Utility::sendEmailTemplate('new_invoice_payment', [$customer->id => $customer->email], $invoicePaymentArr);
                // }
                $data['id'] = $invoice->id;
                $data['no'] = $invoice->invoice_id;
                $data['date'] = $invoicePayment->date;
                $data['reference'] = $invoice->ref_number;
                $data['amount'] = $invoicePayment->amount;
                $data['description'] = $invoicePayment->description;
                $data['category'] = 'Invoice';
                $data['owned_by'] = $invoice->owned_by;
                $data['created_by'] = $invoice->created_by;
                $data['from_bank'] = $request->account_from;
                $data['to_bank'] = $request->account_to;
                $data['prod_id'] = $invoicePayment->id;
                $dataret = Utility::invoicepay($data);

                $bankAccount = BankAccount::find($request->account_from);
                // $data['id'] = $invoice_id;
                // $data['date'] = $invoicePayment->date;
                // $data['reference'] = $invoicePayment->reference;
                // $data['description'] = $invoicePayment->description;
                // $data['amount'] = $invoicePayment->amount;
                // $data['category'] = 'Invoice';
                // $data['owned_by'] = \Auth::user()->ownedId();
                // $data['created_by'] = \Auth::user()->creatorId();
                // $data['account_id'] = $bankAccount->chart_account_id;

                // if(ucwords($bankAccount->bank_name) == 'Cash' || ucwords($bankAccount->holder_name) == 'Cash'){
                //     $dataret  = Utility::crv_entry($data);
                // }else{
                //     $dataret  = Utility::brv_entry($data);
                // }

                DB::commit();
                //webhook
                // $module = 'New Invoice Payment';
                // $webhook =  Utility::webhookSetting($module);
                // if ($webhook) {
                //     $parameter = json_encode($invoice);
                //     $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                //     if ($status == true) {
                //         return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                //     } else {
                //         return redirect()->back()->with('error', __('Webhook call failed.'));
                //     }
                // }
                DB::commit();
                return redirect()->back()->with('success', __('Payment successfully added.'));
                // return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', $e);
            }
        }
    }

    public function paymentDestroy(Request $request, $invoice_id, $payment_id)
    {
        //        dd($invoice_id,$payment_id);

        if (\Auth::user()->can('delete payment invoice')) {
            $payment = InvoicePayment::find($payment_id);

            InvoicePayment::where('id', '=', $payment_id)->delete();

            InvoiceBankTransfer::where('id', '=', $payment_id)->delete();

            $invoice = Invoice::where('id', $invoice_id)->first();
            $due = $invoice->getDue();
            $total = $invoice->getTotal();

            if ($due > 0 && $total != $due) {
                $invoice->status = 3;
            } else {
                $invoice->status = 2;
            }

            if (!empty($payment->add_receipt)) {
                //storage limit
                $file_path = '/uploads/payment/' . $payment->add_receipt;
                $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            }

            $invoice->save();
            $type = 'Partial';
            $user = 'Customer';
            Transaction::destroyTransaction($payment_id, $type, $user);

            Utility::updateUserBalance('customer', $invoice->customer_id, $payment->amount, 'credit');

            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'debit');

            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paymentReminder($invoice_id)
    {

        //        dd($invoice_id);
        $invoice = Invoice::find($invoice_id);
        $customer = Customer::where('id', $invoice->customer_id)->first();
        $invoice->dueAmount = \Auth::user()->priceFormat($invoice->getDue());
        $invoice->name = $customer['name'];
        $invoice->date = \Auth::user()->dateFormat($invoice->send_date);
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        //For Notification
        $setting = Utility::settings(\Auth::user()->creatorId());
        $customer = Customer::find($invoice->customer_id);
        $reminderNotificationArr = [
            'invoice_number' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
            'customer_name' => $customer->name,
            'user_name' => \Auth::user()->name,
        ];

        //Twilio Notification
        if (isset($setting['twilio_reminder_notification']) && $setting['twilio_reminder_notification'] == 1) {
            Utility::send_twilio_msg($customer->contact, 'invoice_payment_reminder', $reminderNotificationArr);
        }

        // Send Email
        $setings = Utility::settings();
        if ($setings['new_payment_reminder'] == 1) {
            $invoice = Invoice::find($invoice_id);
            $customer = Customer::where('id', $invoice->customer_id)->first();
            $invoice->dueAmount = \Auth::user()->priceFormat($invoice->getDue());
            $invoice->name = $customer['name'];
            $invoice->date = \Auth::user()->dateFormat($invoice->send_date);
            $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

            $reminderArr = [

                'payment_reminder_name' => $invoice->name,
                'invoice_payment_number' => $invoice->invoice,
                'invoice_payment_dueAmount' => $invoice->dueAmount,
                'payment_reminder_date' => $invoice->date,

            ];


            $resp = Utility::sendEmailTemplate('new_payment_reminder', [$customer->id => $customer->email], $reminderArr);
        }



        return redirect()->back()->with('success', __('Payment reminder successfully send.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

    public function customerInvoiceSend($invoice_id)
    {
        return view('customer.invoice_send', compact('invoice_id'));
    }

    public function customerInvoiceSendMail(Request $request, $invoice_id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $email = $request->email;
        $invoice = Invoice::where('id', $invoice_id)->first();

        $customer = Customer::where('id', $invoice->customer_id)->first();
        $invoice->name = !empty($customer) ? $customer->name : '';
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice_id);

        $invoiceId = Crypt::encrypt($invoice->id);
        $invoice->url = route('invoice.pdf', $invoiceId);

        try {
            Mail::to($email)->send(new CustomerInvoiceSend($invoice));
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->back()->with('success', __('Invoice successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function shippingDisplay(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if ($request->is_display == 'true') {
            $invoice->shipping_display = 1;
        } else {
            $invoice->shipping_display = 0;
        }
        $invoice->save();

        return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
    }

    public function duplicate($invoice_id)
    {
        DB::beginTransaction();
        try {
            if (\Auth::user()->can('duplicate invoice')) {
                $invoice = Invoice::where('id', $invoice_id)->first();
                $duplicateInvoice = new Invoice();
                $duplicateInvoice->invoice_id = $this->invoiceNumber();
                $duplicateInvoice->customer_id = $invoice['customer_id'];
                $duplicateInvoice->issue_date = date('Y-m-d');
                $duplicateInvoice->due_date = $invoice['due_date'];
                $duplicateInvoice->send_date = null;
                $duplicateInvoice->category_id = $invoice['category_id'];
                $duplicateInvoice->ref_number = $invoice['ref_number'];
                $duplicateInvoice->status = 0;
                $duplicateInvoice->shipping_display = $invoice['shipping_display'];
                $duplicateInvoice->contract_id = $invoice['contract_id'];
                $duplicateInvoice->owned_by = $invoice['owned_by'];
                $duplicateInvoice->created_by = $invoice['created_by'];
                $duplicateInvoice->save();

                if ($duplicateInvoice) {
                    $invoiceProduct = InvoiceProduct::where('invoice_id', $invoice_id)->get();
                    foreach ($invoiceProduct as $product) {
                        $duplicateProduct = new InvoiceProduct();
                        $duplicateProduct->invoice_id = $duplicateInvoice->id;
                        $duplicateProduct->product_id = $product->product_id;
                        $duplicateProduct->quantity = $product->quantity;
                        $duplicateProduct->tax = $product->tax;
                        $duplicateProduct->discount = $product->discount;
                        $duplicateProduct->price = $product->price;
                        $duplicateProduct->save();
                    }
                    $items = [];
                    for ($i = 0; $i < count($invoiceProduct); $i++) {
                        $taxRate = 0;
                        $items[$i]['item'] = $invoiceProduct[$i]['product_id'];
                        $items[$i]['quantity'] = $invoiceProduct[$i]['quantity'];
                        $items[$i]['price'] = $invoiceProduct[$i]['price'];
                        $tax = Tax::where('id', $invoiceProduct[$i]['tax'])->first();
                        if (isset($tax)) {
                            $taxRate = $tax->rate;
                        }
                        $items[$i]['itemTaxPrice'] = ($taxRate / 100) * ($invoiceProduct[$i]['price'] * $invoiceProduct[$i]['quantity']);
                    }

                    $data['id'] = $duplicateInvoice->id;
                    $data['date'] = $duplicateInvoice->issue_date;
                    $data['reference'] = $duplicateInvoice->ref_number;
                    $data['category'] = 'Invoice';
                    $data['owned_by'] = $duplicateInvoice->owned_by;
                    $data['created_by'] = $duplicateInvoice->created_by;
                    $data['items'] = $items;
                    $dataret = Utility::jrentry($data);
                }
                DB::commit();
                return redirect()->back()->with('success', __('Invoice duplicate successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
        }
    }

    public function previewInvoice($template, $color)
    {

        $objUser = \Auth::user();
        $settings = Utility::settings();
        $invoice = new Invoice();

        $customer = new \stdClass();
        $customer->email = '<Email>';
        $customer->shipping_name = '<Customer Name>';
        $customer->shipping_country = '<Country>';
        $customer->shipping_state = '<State>';
        $customer->shipping_city = '<City>';
        $customer->shipping_phone = '<Customer Phone Number>';
        $customer->shipping_zip = '<Zip>';
        $customer->shipping_address = '<Address>';
        $customer->billing_name = '<Customer Name>';
        $customer->billing_country = '<Country>';
        $customer->billing_state = '<State>';
        $customer->billing_city = '<City>';
        $customer->billing_phone = '<Customer Phone Number>';
        $customer->billing_zip = '<Zip>';
        $customer->billing_address = '<Address>';

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
            $item->description = 'XYZ';


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

        $invoice->invoice_id = 1;
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date = date('Y-m-d H:i:s');
        $invoice->itemData = $items;

        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 3;
        $invoice->totalRate = 300;
        $invoice->totalDiscount = 10;
        $invoice->taxesData = $taxesData;
        $invoice->created_by = $objUser->creatorId();

        $invoice->customField = [];
        $customFields = [];

        $preview = 1;
        $color = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $invoice_logo = Utility::getValByName('invoice_logo');
        if (isset($invoice_logo) && !empty($invoice_logo)) {
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }
        dd($template);
        return view('invoice.templates.' . $template, compact('invoice', 'preview', 'color', 'img', 'settings', 'customer', 'font_color', 'customFields'));
    }

    public function invoice($invoice_id)
    {
        // dd($invoice_id);
        $settings = Utility::settings();

        $invoiceId = Crypt::decrypt($invoice_id);
        $invoice = Invoice::where('id', $invoiceId)->first();

        $data = DB::table('settings');
        if (\Auth::user()->type == ('company')) {
            $data = $data->where('created_by', '=', $invoice->created_by);
        } else {
            $data = $data->where('owned_by', '=', $invoice->created_by);
        }
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $invoice->customer;
        $items = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        foreach ($invoice->items as $product) {
            $item = new \stdClass();
            $item->sku = !empty($product->product()) ? $product->product()->sku : '';
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

        $invoice->itemData = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData = $taxesData;
        $invoice->customField = CustomField::getData($invoice, 'invoice');
        $customFields = [];
        if (!empty(\Auth::user())) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();
        }
        //
        //        $logo         = asset(Storage::url('uploads/logo/'));
        //        $company_logo = Utility::getValByName('company_logo_dark');
        //        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
        $invoice_logo = $settings_data['invoice_logo'];
        if (isset($invoice_logo) && !empty($invoice_logo)) {
            $img = Utility::get_file('invoice_logo/') . $invoice_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        if ($invoice) {
            $color = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);
            // dd($settings['invoice_template']);
            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'color', 'settings', 'customer', 'img', 'font_color', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveTemplateSettings(Request $request)
    {

        $post = $request->all();
        unset($post['_token']);

        if (isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color']))) {
            $post['invoice_color'] = "ffffff";
        }


        if ($request->invoice_logo) {
            $dir = 'invoice_logo/';
            $invoice_logo = \Auth::user()->id . '_invoice_logo.png';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];
            $path = Utility::upload_file($request, 'invoice_logo', $invoice_logo, $dir, $validation);

            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['invoice_logo'] = $invoice_logo;
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

        return redirect()->back()->with('success', __('Invoice Setting updated successfully'));
    }

    public function items(Request $request)
    {
        $items = InvoiceProduct::where('invoice_id', $request->invoice_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }

    public function invoiceLink($invoiceId)
    {
        try {
            $id = Crypt::decrypt($invoiceId);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }


        $id = Crypt::decrypt($invoiceId);
        $invoice = Invoice::find($id);

        $settings = Utility::settingsById($invoice->created_by);

        if (!empty($invoice)) {

            $user_id = $invoice->created_by;
            $user = User::find($user_id);
            $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->get();
            $customer = $invoice->customer;
            $iteams = $invoice->items;
            $invoice->customField = CustomField::getData($invoice, 'invoice');
            $customFields = CustomField::where('module', '=', 'invoice')->get();
            $company_payment_setting = Utility::getCompanyPaymentSetting($user_id);

            // start for storage limit note
            $invoice_user = User::find($invoice->created_by);
            $user_plan = Plan::find($invoice_user->plan);
            // end for storage limit note


            return view('invoice.customer_invoice', compact('settings', 'invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'company_payment_setting', 'invoice_user', 'user_plan'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function export()
    {
        $name = 'invoice_' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function companycontract(Request $request)
    {
        $customers = Customer::find($request->id);

        $item = Contract::where('company_id', $customers->company_id)->where('close_date', Null)->get();
        $items = [
            'data' => $item,
        ];

        return response()->json($items);
    }

    public function branchCustomer(Request $request)
    {
        $customer = Customer::where('owned_by', '=', $request->id)->get();
        // dd($request->id);
        if ($customer == null) {
            $result = [
                'status' => 'error',
                'customer' => 'null',

            ];
            return response()->json($result);
        } else {
            $result = [
                'status' => 'success',
                'customer' => $customer,
            ];
            return response()->json($result);
        }
    }

    public function branchVender(Request $request)
    {
        $vender = Vender::where('owned_by', '=', $request->id)->get();
        // dd($request->id);
        if ($vender == null) {
            $result = [
                'status' => 'error',
                'customer' => 'null',

            ];
            return response()->json($result);
        } else {
            $result = [
                'status' => 'success',
                'vender' => $vender,
            ];
            return response()->json($result);
        }
    }

    public function companycontractdetail(Request $request)
    {
        // dd($request->all());
        $index = '-1';
        $ware_products = WarehouseProduct::join('product_services', 'warehouse_products.product_id', '=', 'product_services.id')
            ->where('warehouse_products.warehouse_id', $request->id)
            ->where('warehouse_products.created_by', \Auth::user()->creatorId())
            ->get(['product_services.name', 'warehouse_products.product_id', 'warehouse_products.quantity'])
            ->mapWithKeys(function ($item) use (&$index) {
                $index++;
                return [$index => ['id' => $item->product_id, 'name' => $item->name . ' ( ' . $item->quantity . ' )', 'quantity' => $item->quantity]];
            })
            ->toArray();

        $report = view('invoice.invoice_details', compact('ware_products'))->render();


        return response(['html' => $report,]);
    }


    public function next($ids)
    {
        if (\Auth::user()->can('show invoice')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invoice Not Found.'));
            }
            $id = Crypt::decrypt($ids);
            if (\Auth::user()->type == 'company') {
                $invoice = Invoice::where('status', '0')->where('id', '>', $id)->where('created_by', '=', \Auth::user()->creatorId())->first();
            } else {
                $invoice = Invoice::where('status', '0')->where('id', '>', $id)->where('owned_by', '=', \Auth::user()->ownedId())->first();
            }
            // $invoice = Invoice::where('status','0')->where('id','>',$id)->where()->find($id);
            // $invoice = Invoice::find($id);

            if (!empty($invoice->created_by) == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();


                $customer = $invoice->customer;
                $iteams = $invoice->items;
                $user = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                $user_plan = Plan::find($invoice_user->plan);
                // end for storage limit note

                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'invoice_user', 'user_plan'));
            } else {
                return redirect()->back()->with('error', __('List End.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function back($ids)
    {
        if (\Auth::user()->can('show invoice')) {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invoice Not Found.'));
            }
            $id = Crypt::decrypt($ids);
            if (\Auth::user()->type == 'company') {
                $invoice = Invoice::where('status', '0')->where('id', '<', $id)->where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->first();
            } else {
                $invoice = Invoice::where('status', '0')->where('id', '<', $id)->where('owned_by', '=', \Auth::user()->ownedId())->orderBy('id', 'desc')->first();
            }
            // $invoice = Invoice::where('status','0')->where('id','>',$id)->where()->find($id);
            // $invoice = Invoice::find($id);

            if (!empty($invoice->created_by) == \Auth::user()->creatorId()) {
                $invoicePayment = InvoicePayment::where('invoice_id', $invoice->id)->first();


                $customer = $invoice->customer;
                $iteams = $invoice->items;
                $user = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                $user_plan = Plan::find($invoice_user->plan);
                // end for storage limit note

                $invoice->customField = CustomField::getData($invoice, 'invoice');
                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'invoice')->get();

                return view('invoice.view', compact('invoice', 'customer', 'iteams', 'invoicePayment', 'customFields', 'user', 'invoice_user', 'user_plan'));
            } else {
                return redirect()->back()->with('error', __('List End.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulk_invoice(Request $request)
    {
        if (\Auth::user()->can('create invoice')) {
            DB::beginTransaction();
            try {
                $contracts = Contract::where('owned_by', \Auth::user()->ownedId())->where('close_date', Null)->get();
                foreach ($contracts as $contract) {
                    $invoice = Invoice::where('contract_id', $contract->id)->whereYear('issue_date', date('Y'))->whereMonth('issue_date', date('m'))->first();
                    if ($invoice) {
                    } else {
                        $assign_room = Roomassign::with('space')->where('contract_id', $contract->id)->get();
                        if (\Auth::user()->type == 'company') {
                            $product = ProductService::where('created_by', \Auth::user()->creatorId())->where('space_id', @$assign_room[0]->space->id)->first();
                            $serv = ProductService::where('created_by', \Auth::user()->creatorId())->where('id', @$contract->service_id)->first();
                            $serv2 = ProductService::where('created_by', \Auth::user()->creatorId())->where('id', @$contract->security_deposit_id)->first();
                        } else {
                            $product = ProductService::where('owned_by', \Auth::user()->ownedId())->where('space_id', @$assign_room[0]->space->id)->first();
                            $serv = ProductService::where('owned_by', \Auth::user()->ownedId())->where('id', @$contract->service_id)->first();
                            $serv2 = ProductService::where('owned_by', \Auth::user()->ownedId())->where('id', @$contract->security_deposit_id)->first();
                        }

                        $data = [];
                        $taxes = '';
                        $tax = [];
                        $totalItemTaxRate = 0;
                        if ($product) {
                            if ($product->tax_id == 0) {
                                $taxes .= '-';
                            } else {
                                $taxData = $product->tax($product->tax_id);
                                foreach ($taxData as $taxItem) {
                                    $taxes .= '<span class="badge bg-primary mt-1 mr-2">' . $taxItem->name . ' (' . $taxItem->rate . '%)</span>';
                                    $tax[] = $taxItem->id;
                                    $totalItemTaxRate += floatval($taxItem->rate);
                                }
                            }
                        }

                        $itemTaxPrice = floatval(($totalItemTaxRate / 100)) * floatval($contract->value);
                        $data['tax'] = $tax;
                        $data['totalItemTaxRate'] = $totalItemTaxRate;
                        $data['itemTaxPrice'] = $itemTaxPrice;
                        $data['total'] = floatval($itemTaxPrice) + floatval($contract->value);


                        $data2 = [];
                        $taxes2 = '';
                        $tax2 = [];
                        $totalItemTaxRate2 = 0;
                        if ($serv) {

                            if ($serv) {
                                if ($serv->tax_id == 0) {
                                    $taxes2 .= '-';
                                } else {
                                    $taxData2 = $serv->tax($serv->tax_id);
                                    foreach ($taxData2 as $taxItem2) {
                                        $taxes2 .= '<span class="badge bg-primary mt-1 mr-2">' . $taxItem2->name . ' (' . $taxItem2->rate . '%)</span>';
                                        $tax2[] = $taxItem2->id;
                                        $totalItemTaxRate2 += floatval($taxItem2->rate);
                                    }
                                }
                            }
                        }
                        $itemTaxPrice2 = floatval(($totalItemTaxRate2 / 100)) * floatval($contract->service_price);
                        $data2['tax'] = $tax2;
                        $data2['totalItemTaxRate'] = $totalItemTaxRate2;
                        $data2['itemTaxPrice'] = $itemTaxPrice2;
                        $data2['total'] = floatval($itemTaxPrice2) + floatval($contract->service_price);


                        // for ssecurity services
                        $invoice3 = Invoice::where('contract_id', $contract->id)->first();
                        if (empty($invoice3)) {

                            $data3 = [];
                            $taxes3 = '';
                            $tax3 = [];
                            $totalItemTaxRate3 = 0;
                            if ($serv2) {

                                if ($serv2) {
                                    if ($serv2->tax_id == 0) {
                                        $taxes3 .= '-';
                                    } else {
                                        $taxData3 = $serv2->tax($serv2->tax_id);
                                        foreach ($taxData3 as $taxItem3) {
                                            $taxes3 .= '<span class="badge bg-primary mt-1 mr-2">' . $taxItem3->name . ' (' . $taxItem3->rate . '%)</span>';
                                            $tax3[] = $taxItem3->id;
                                            $totalItemTaxRate3 += floatval($taxItem3->rate);
                                        }
                                    }
                                }
                            }
                            $itemTaxPrice3 = floatval(($totalItemTaxRate3 / 100)) * floatval($contract->security_deposit_price);
                            $data3['tax'] = $tax3;
                            $data3['totalItemTaxRate'] = $totalItemTaxRate3;
                            $data3['itemTaxPrice'] = $itemTaxPrice3;
                            $data3['total'] = floatval($itemTaxPrice3) + floatval($contract->security_deposit_price);
                        }

                        $customer = Customer::where('company_id', $contract->company_id)->first();

                        $category = ProductServiceCategory::where('owned_by', \Auth::user()->ownedId())->where('type', 'income')->first();
                        $status = Invoice::$statues;
                        // dd($contract->id);
                        $latest = Invoice::where('owned_by', '=', \Auth::user()->ownedId())->orderBY('id', 'Desc')->latest()->first();
                        if (!$latest) {
                            $a = 1;
                        } else {
                            $a = $latest->invoice_id + 1;
                        }

                        $invoice_new = new Invoice();
                        $invoice_new->invoice_id = $a;
                        $invoice_new->customer_id = $customer->id;
                        $invoice_new->status = 0;
                        $invoice_new->issue_date = date('Y-m-01');
                        $invoice_new->due_date = date('Y-m-12');
                        $invoice_new->category_id = $category->id;
                        $invoice_new->contract_id = $contract->id;
                        $invoice_new->owned_by = \Auth::user()->ownedId();
                        $invoice_new->created_by = \Auth::user()->creatorId();
                        $invoice_new->save();


                        // dd($invoice,$data,$data2,);
                        if (!empty($data)) {
                            $invoiceProduct = new InvoiceProduct();
                            $invoiceProduct->invoice_id = $invoice_new->id;
                            $invoiceProduct->product_id = $product->id;
                            $invoiceProduct->quantity = $assign_room->count('chair_id');
                            $invoiceProduct->tax = $tax[0];
                            //                $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                            $invoiceProduct->discount = 0;
                            $invoiceProduct->price = $contract->value / $assign_room->count('chair_id');
                            $invoiceProduct->description = "Bulk invoice generate";
                            $invoiceProduct->save();
                            $items[0]['item'] = $invoiceProduct->product_id;
                            $items[0]['quantity'] = $invoiceProduct->quantity;
                            $items[0]['discount'] = $invoiceProduct->discount;
                            $items[0]['price'] = $invoiceProduct->price;
                            $items[0]['description'] = $invoiceProduct->description;
                            $items[0]['itemTaxPrice'] = floatval($itemTaxPrice);
                        }
                        if (!empty($data2)) {
                            $invoiceProduct2 = new InvoiceProduct();
                            $invoiceProduct2->invoice_id = $invoice_new->id;
                            $invoiceProduct2->product_id = $serv->id;
                            $invoiceProduct2->quantity = $assign_room->count('chair_id');
                            $invoiceProduct2->tax = $tax2[0];
                            //                $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                            $invoiceProduct2->discount = 0;
                            $invoiceProduct2->price = $contract->service_price / $assign_room->count('chair_id');
                            $invoiceProduct2->description = "Bulk invoice generate";
                            $invoiceProduct2->save();
                            $items[1]['item'] = $invoiceProduct2->product_id;
                            $items[1]['quantity'] = $invoiceProduct2->quantity;
                            $items[1]['discount'] = $invoiceProduct2->discount;
                            $items[1]['price'] = $invoiceProduct2->price;
                            $items[1]['description'] = $invoiceProduct2->description;
                            $items[1]['itemTaxPrice'] = floatval($itemTaxPrice2);
                        }
                        if (!empty($data3)) {
                            $invoiceProduct3 = new InvoiceProduct();
                            $invoiceProduct3->invoice_id = $invoice_new->id;
                            $invoiceProduct3->product_id = $serv2->id;
                            $invoiceProduct3->quantity = 1;
                            $invoiceProduct3->tax = $tax3[0];
                            //                $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                            $invoiceProduct3->discount = 0;
                            $invoiceProduct3->price = $contract->security_deposit_price;
                            $invoiceProduct3->description = "Bulk invoice generate";
                            $invoiceProduct3->save();
                            $items[2]['item'] = $invoiceProduct3->product_id;
                            $items[2]['quantity'] = $invoiceProduct3->quantity;
                            $items[2]['discount'] = $invoiceProduct3->discount;
                            $items[2]['price'] = $invoiceProduct3->price;
                            $items[2]['description'] = $invoiceProduct3->description;
                            $items[2]['itemTaxPrice'] = floatval($itemTaxPrice3);
                        }

                        $voucher['id'] = $invoice_new->id;
                        $voucher['date'] = $invoice_new->issue_date;
                        $voucher['reference'] = $invoice_new->ref_number;
                        $voucher['category'] = 'Invoice';
                        $voucher['owned_by'] = $invoice_new->owned_by;
                        $voucher['created_by'] = $invoice_new->created_by;
                        $voucher['items'] = $items;

                        $dataret = Utility::jrentry($voucher);
                    }
                }
                DB::commit();
                return redirect()->route('invoice.index')->with('success', __('Invoice successfully created.'));
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

}
