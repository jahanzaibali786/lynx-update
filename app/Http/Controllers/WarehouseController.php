<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
            $branches->prepend('Select Branch', '');
            $query = warehouse::with(['branch', 'assignedEmployee'])
                ->where('created_by', '=', $user->creatorId());
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
        if (!\Auth::user()->can('create warehouse')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $employeesByBranch = $this->employeesByBranch();
        // $branches->prepend('Select Branch', '');
        return view('warehouse.create', compact('branches', 'employeesByBranch'));

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
                    'assigned_employee_id' => 'required|integer',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!$this->employeeBelongsToBranch($request->assigned_employee_id, $request->branch_id)) {
                return redirect()->back()->withInput()->with('error', __('Selected employee does not belong to the selected branch.'));
            }

            $warehouse = new warehouse();
            $warehouse->name = $request->name;
            $warehouse->address = $request->address;
            $warehouse->city = $request->city;
            $warehouse->city_zip = $request->city_zip;
            $warehouse->owned_by = $request->branch_id;
            $warehouse->assigned_employee_id = $request->assigned_employee_id;
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
                $branches = User::where('type', 'branch')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $employeesByBranch = $this->employeesByBranch();
                return view('warehouse.edit', compact('warehouse', 'branches', 'employeesByBranch'));
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
                        'assigned_employee_id' => 'required|integer',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if (!$this->employeeBelongsToBranch($request->assigned_employee_id, $request->branch_id)) {
                    return redirect()->back()->withInput()->with('error', __('Selected employee does not belong to the selected branch.'));
                }

                $warehouse->name = $request->name;
                $warehouse->address = $request->address;
                $warehouse->city = $request->city;
                $warehouse->city_zip = $request->city_zip;
                $warehouse->owned_by = $request->branch_id;
                $warehouse->assigned_employee_id = $request->assigned_employee_id;
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

    private function employeesByBranch(): array
    {
        return Employee::select('id', 'name', 'branch_id', 'owned_by')
            ->where('created_by', \Auth::user()->creatorId())
            ->where('user_id', '>', 0)
            ->where('is_active', 1)
            ->where('is_res_ter', 0)
            ->orderBy('name')
            ->get()
            ->groupBy(function (Employee $employee) {
                return (int) ($employee->owned_by ?: $employee->branch_id);
            })
            ->map(function ($employees) {
                return $employees->map(function (Employee $employee) {
                    return ['id' => $employee->id, 'name' => $employee->name];
                })->values()->all();
            })
            ->all();
    }

    private function employeeBelongsToBranch($employeeId, $branchId): bool
    {
        return Employee::whereKey($employeeId)
            ->where('created_by', \Auth::user()->creatorId())
            ->where('user_id', '>', 0)
            ->where('is_active', 1)
            ->where('is_res_ter', 0)
            ->where(function ($query) use ($branchId) {
                $query->where('owned_by', $branchId)
                    ->orWhere('branch_id', $branchId);
            })
            ->exists();
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
