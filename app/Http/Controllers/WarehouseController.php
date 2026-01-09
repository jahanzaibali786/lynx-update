<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utility;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(\Auth::user()->can('manage warehouse')){
            $user    = \Auth::user();
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
            $branches->prepend('Select Branch', '');
            $query = warehouse::where('created_by', '=', $user->creatorId());
            if (!empty($request->branch)) {
                $query->where('owned_by', '=', $request->branch);
            }

            $warehouses = $query->paginate(25);

            return view('warehouse.index', compact('warehouses', 'branches'));
        }else{
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
        $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        // $branches->prepend('Select Branch', '');
        return view('warehouse.create', compact('branches'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create warehouse')) {
            // $validator = \Validator::make(
            //     $request->all(), [
            //         'name' => 'required',
            //         // 'branch_id' => 'required',
            //         'branch_id' => 'required|unique:warehouses,owned_by',
            //     ], [
            //         'branch_id.required' => 'The branch field is required.',
            //         'branch_id.unique' => 'This branch already has a store.',
            // ]);
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'branch_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $warehouse = new warehouse();
            $warehouse->name = $request->name;
            $warehouse->address = $request->address;
            $warehouse->city = $request->city;
            $warehouse->city_zip = $request->city_zip;
            $warehouse->owned_by = $request->branch_id;
            $warehouse->created_by = \Auth::user()->creatorId();
            $warehouse->save();

            return redirect()->route('store.index')->with('success', __('Store successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $warehouse = Warehouse::where('warehouse_id' , $id)->first();
        $warehouse = Warehouse::where('id', $id)->first();
        $warehouse_id = $warehouse->id;
        if (\Auth::user()->can('show warehouse')) {

            if (WarehouseProduct::where('warehouse_id', $warehouse->id)->exists()) {
                $warehouse = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('created_by', '=', \Auth::user()->creatorId())->get();

                return view('warehouse.show', compact('warehouse', 'warehouse_id'));
            } else {
                $warehouse = [];
                return view('warehouse.show', compact('warehouse', 'warehouse_id'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $warehouse = Warehouse::find($id);
        if (\Auth::user()->can('edit warehouse')) {
            if ($warehouse->created_by == \Auth::user()->creatorId()) {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                return view('warehouse.edit', compact('warehouse', 'branches'));
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, warehouse $warehouse, $id)
    {
        $warehouse = Warehouse::find($id);
        if (\Auth::user()->can('edit warehouse')) {
            if ($warehouse->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'branch_id' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $warehouse->name = $request->name;
                $warehouse->address = $request->address;
                $warehouse->city = $request->city;
                $warehouse->city_zip = $request->city_zip;
                $warehouse->owned_by = $request->branch_id;
                $warehouse->save();

                return redirect()->route('store.index')->with('success', __('Store successfully updated.'));
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
     * @param  \App\Models\warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(warehouse $warehouse)
    {
        if (\Auth::user()->can('delete warehouse')) {
            if ($warehouse->created_by == \Auth::user()->creatorId()) {
                $warehouse->delete();


                return redirect()->route('store.index')->with('success', __('Store successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function branch_store(Request $request)
    {

        $store = Warehouse::where('owned_by', '=', $request->branch_id)->get();
        return response()->json($store);
        // return $store;
    }
    public function print($id)
{
    $warehouse = Warehouse::where('id', $id)->first();
    if (WarehouseProduct::where('warehouse_id', $warehouse->id)->exists()) {
        $warehouseProducts = WarehouseProduct::where('warehouse_id', $warehouse->id)
            ->where('created_by', '=', \Auth::user()->creatorId())
            ->paginate(15);
        $html = view('warehouse.print', ['warehouse' => $warehouseProducts])->render();
    } else {
        $warehouseProducts = [];
        $html = view('warehouse.print', ['warehouse' => $warehouseProducts])->render();
    }
    $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
    $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
    $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
            .footer { position: fixed; bottom: -30px; height: 50px; left:0px; right:0px; }
            .table, tr, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
        </style>
        </head><body>
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'Portrait');
    $dompdf->render();

    return $dompdf->stream('warehouse_report.pdf', array('Attachment' => 1));
}

}
