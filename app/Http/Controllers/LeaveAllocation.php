<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeaves;
use Illuminate\Http\Request;

class LeaveAllocation extends Controller
{
    public function index()
    {
        $emp_leave = EmployeeLeaves::orderByDesc('id')->paginate(25);

        return view('employee.leave.index', compact('emp_leave'));
    } 
    //edit
    public function edit($id)
    {
        $emp_leave = EmployeeLeaves::find($id);
        return view('employee.leave.edit', compact('emp_leave'));
    }  
    //update
    public function update(Request $request, $id)
    {
        $emp_leave = EmployeeLeaves::find($id);
        $emp_leave->casual_total = $request->casual_total;
        $emp_leave->casual_consumed = $request->casual_consumed;
        $emp_leave->annual_total = $request->annual_total;
        $emp_leave->annual_consumed = $request->annual_consumed;
        $emp_leave->save();
        return redirect()->route('emp-leaves.index')->with('success', 'Leave allocation updated successfully.');
    }
}
