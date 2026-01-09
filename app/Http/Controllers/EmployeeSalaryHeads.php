<?php

namespace App\Http\Controllers;

use App\Models\SalaryHeads;
use App\Models\EmployeeMonthlySalaryHeads;
use App\Models\EmployeeScaleHeads;
use Illuminate\Http\Request;

class EmployeeSalaryHeads extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage employee')) {

            $query = \App\Models\SalaryHeads::where('created_by', \Auth::user()->creatorId());
            $employee_scales_heads = $query->paginate(25);

            return view('employee.scaleheads.index', compact('employee_scales_heads'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function create()
    {
        if (\Auth::user()->can('create trainer')) {
            return view('employee.scaleheads.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
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
        if (\Auth::user()->can('create trainer')) {
            // dd($request->all());
            $validator = \Validator::make(
                $request->all(),
                [
                    'head' => 'required',
                    'status' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee_scales = new \App\Models\SalaryHeads();
            $employee_scales->head = $request->head;
            $employee_scales->status = $request->status;
            $employee_scales->owned_by = \Auth::user()->ownedId();
            $employee_scales->created_by = \Auth::user()->creatorId();
            $employee_scales->save();

            return redirect()->route('employee-scale-heads.index')->with('success', __('Employee Scale Head successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
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
        if (\Auth::user()->can('edit trainer')) {
            $employeeScale = SalaryHeads::find($id);
            return view('employee.scaleheads.edit', compact('employeeScale'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if (\Auth::user()->can('edit trainer')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'head' => 'required',
                    'status' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $salaryHeads = SalaryHeads::find($id);
            $salaryHeads->head = $request->head;
            $salaryHeads->status = $request->status;
            $salaryHeads->save();

            return redirect()->route('employee-scale-heads.index')->with('success', __('Employee Scale Head successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
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
        //         if(\Auth::user()->can('delete vender'))
        // {
            $head=SalaryHeads::find($id);
            if($head->created_by == \Auth::user()->creatorId() && \Auth::user()->type == 'company')
            {
                $data= EmployeeMonthlySalaryHeads::where('head_id',$head->id)->get();
                if($data->isEmpty()){
                    $data2= EmployeeScaleHeads::where('head',$head->id)->delete();
                    $head->delete();
                }else{
                    return redirect()->back()->with('error', __('Data exist against this Salary Head'));
                }
                $head->delete();

                return redirect()->route('employee-scale-heads.index')->with('success', __('Employee Scale Head successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }
}
