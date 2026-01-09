<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeAdvance;
use App\Models\User;
use App\Models\Employee;
class EmployeeAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = EmployeeAdvance::with('employee')->where('created_by', \Auth::user()->creatorId());
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        } else {
            $query = EmployeeAdvance::with('employee')->where('owned_by', \Auth::user()->ownedId());
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        }

        if (!empty($request->branches)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('branch_id', $request->branches);
            });
        }
        if (!empty($request->department_id)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            });
        }
        if (!empty($request->designation_id)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('designation_id', $request->designation_id);
            });
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        $advance = $query->paginate(25);
        return view('employee.advance.index', compact('advance', 'branches', 'departments', 'designations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');

        } else {

            $employee = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        return view('employee.advance.create', compact('employee', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            $request->validate([
                'branches' => 'required|integer',
                'employee_id' => 'required|integer',
                'amount' => 'required|numeric',
                'date' => 'required',
                'reason' => 'string',
            ]);
            $amount = $request->input('amount');
            $owned_by = \Auth::user()->ownedId();
            $created_by = \Auth::user()->creatorId();
            $advance = new EmployeeAdvance();
            $advance->employee_id = $request->input('employee_id');
            $advance->advance_amount = $amount;
            $advance->advance_date = $request->input('date');
            $advance->advance_reason = $request->input('reason');
            $advance->status = 0;
            $advance->owned_by = $owned_by;
            $advance->created_by = $created_by;
            $advance->save();
            \DB::commit();
            return redirect()->route('employee-advance.index')->with('success', __('Advance  successfully created.'));
        } catch (\Exception $e) {
            dd($e);
            \DB::rollback();
            return redirect()->back()->with('error', $e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('edit loan')) {
                $advance = EmployeeAdvance::find($id);
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                return view('employee.advance.edit', compact('advance', 'branches', 'employee'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $request->validate([
                'employee_id' => 'required|integer',
                'amount' => 'required|numeric',
                'date' => 'required',
                'reason' => 'string',
            ]);
            $amount = $request->input('amount');
            $owned_by = \Auth::user()->ownedId();
            $created_by = \Auth::user()->creatorId();
            $advance = EmployeeAdvance::find($id);
            $advance->employee_id = $request->input('employee_id');
            $advance->advance_amount = $amount;
            $advance->advance_date = $request->input('date');
            $advance->advance_reason = $request->input('reason');
            $advance->status = 0;
            $advance->owned_by = $owned_by;
            $advance->created_by = $created_by;
            $advance->save();
            \DB::commit();
            return redirect()->route('employee-advance.index')->with('success', __('Advance  successfully Updated.'));
        } catch (\Exception $e) {
            dd($e);
            \DB::rollback();
            return redirect()->back()->with('error', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $advance = EmployeeAdvance::find($id);
        $advance->delete();
        return redirect()->back()->with('success', __('Advance successfully deleted.'));
    }
}
