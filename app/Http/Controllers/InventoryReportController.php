<?php

namespace App\Http\Controllers;

use App\Exports\SalesByCustomerExport;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\StockReport;
use App\Models\User;
use App\Models\warehouse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReportController extends Controller
{
    public function sales_by_customer(Request $request)
    {
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

            $invoices = $query->get()->groupBy('to_store');

            if ($request->has('export') && $request->export == 'excel') {
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);
                $data = [
                    'invoices' => $invoices,
                    'branches' => $branches,
                    'store' => $store,
                    'status' => $status,
                    'request' => $request,
                ];

                return Excel::download(new SalesByCustomerExport($data, $request->all()), 'sales_by_customer_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $request->merge(['date_from' => $request->start_date]);
                $request->merge(['date_to' => $request->end_date]);
                $data = [
                    'invoices' => $invoices,
                    'branches' => $branches,
                    'store' => $store,
                    'status' => $status,
                    'request' => $request,
                ];

                return Excel::download(new SalesByCustomerExport($data, $request->all()), 'sales_by_customer_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }

            return view('report.sales_by_customer', compact( 'invoices', 'branches', 'store', 'status'));
        }else{
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
