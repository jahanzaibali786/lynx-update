<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HealthInsurance;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class HealthInsuracnePlanSetup extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $healthInsurances = HealthInsurance::where('created_by', \Auth::user()->creatorId())->get();
        } else {
            $healthInsurances = HealthInsurance::where('owned_by', \Auth::user()->ownedId())->get();
        }
        return view('employee.health-insurance.index', compact('healthInsurances'));
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
            $employee = Employee::where('created_by', \Auth::user()->creatorId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $query = HealthInsurance::where('created_by', \Auth::user()->creatorId())->get();
        } else {
            $employee = Employee::where('owned_by', \Auth::user()->ownedId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = HealthInsurance::where('owned_by', \Auth::user()->ownedId())->get();
        }
        return view('employee.health-insurance.create', compact('branches', 'employee'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'employee_id' => 'required',
                'branches' => 'required',
                'plan_name' => 'required',
                'plan_type' => 'required',
                'plan_amount' => 'required',
                'plan_start' => 'required|date',
                'plan_end' => 'required|date|after:plan_start',
            ]
        );

        if ($validator->fails()) {
            dd($validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $healthInsurance = new HealthInsurance();
            $healthInsurance->employee_id = $request->employee_id;
            $healthInsurance->branch_id = $request->branches;
            $healthInsurance->plan_name = $request->plan_name;
            $healthInsurance->plan_type = $request->plan_type;
            $healthInsurance->plan_amount = $request->plan_amount;
            $healthInsurance->plan_start = $request->plan_start;
            $healthInsurance->plan_end = $request->plan_end;
            $healthInsurance->description = $request->description;
            $healthInsurance->status = 1;
            $healthInsurance->created_by = \Auth::user()->creatorId();
            $healthInsurance->owned_by = \Auth::user()->ownedId();
            $healthInsurance->save();
            DB::commit();   
            return redirect()->route('health-insurance-plan.index')->with('success', __('Health Insurance Plan successfully created.'));
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            return redirect()->back()->with('error', __('Something went wrong. Please try again.'));
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
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employee = Employee::where('created_by', \Auth::user()->creatorId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $healthInsurance = HealthInsurance::find($id);
        } else {
            $employee = Employee::where('owned_by', \Auth::user()->ownedId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $healthInsurance = HealthInsurance::find($id);
        }
        return view('employee.health-insurance.edit', compact('branches', 'employee', 'healthInsurance'));
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
        $validator = \Validator::make(
            $request->all(),
            [
                'employee_id' => 'required',
                'branches' => 'required',
                'plan_name' => 'required',
                'plan_type' => 'required',
                'plan_amount' => 'required',
                'plan_start' => 'required|date',
                'plan_end' => 'required|date|after:plan_start',
            ]
        );

        if ($validator->fails()) {
            dd($validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();
            $healthInsurance = HealthInsurance::find($id);
            $healthInsurance->employee_id = $request->employee_id;
            $healthInsurance->branch_id = $request->branches;
            $healthInsurance->plan_name = $request->plan_name;
            $healthInsurance->plan_type = $request->plan_type;
            $healthInsurance->plan_amount = $request->plan_amount;
            $healthInsurance->plan_start = $request->plan_start;
            $healthInsurance->plan_end = $request->plan_end;
            $healthInsurance->description = $request->description;
            $healthInsurance->status = 1;
            $healthInsurance->created_by = \Auth::user()->creatorId();
            $healthInsurance->owned_by = \Auth::user()->ownedId();
            $healthInsurance->save();
            DB::commit();   
            return redirect()->route('health-insurance-plan.index')->with('success', __('Health Insurance Plan successfully updated.'));
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            return redirect()->back()->with('error', __('Something went wrong. Please try again.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $healthInsurance = HealthInsurance::find($id);
            $healthInsurance->delete();
            DB::commit();   
            return redirect()->route('health-insurance-plan.index')->with('success', __('Health Insurance Plan successfully deleted.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('Something went wrong. Please try again.'));
        }
    }
}
