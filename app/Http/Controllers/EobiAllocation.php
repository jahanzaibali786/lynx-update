<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EobiAllocation extends Controller
{
    public function index()
    {
        $emps = Employee::where('is_res_ter', 0)->orderByDesc('id')->get();
        return view('employee.eobi.index', compact('emps'));
    } 
    public function edit($id)
    {
        $emp = Employee::find($id);

        return view('employee.eobi.edit', compact('emp'));
    } 
    public function update(Request $request, $id)
    {
        $emp = Employee::find($id);
        $emp->eobi = $request->eobi;
        $emp->eobi_employer = $request->eobi_employer;
        $emp->pessi = $request->pessi;
        $emp->pessi_employer = $request->pessi_employer;
        $emp->save();
        return redirect()->route('emp-eobi-allocation.index')->with('success', 'Allocated successfully.');
    }
   //destroy
   
}
