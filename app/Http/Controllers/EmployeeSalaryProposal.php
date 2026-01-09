<?php

namespace App\Http\Controllers;

use App\Models\EmployeeScale;
use App\Models\SalaryHeads;
use App\Models\SalaryProposal;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class EmployeeSalaryProposal extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $salaryproposal = SalaryProposal::with('employees')->where('created_by', '=', \Auth::user()->creatorId())->where('status',1)->get();
        } else {
            // dd(\Auth::user()->ownedId());
            $salaryproposal = SalaryProposal::with('employees')->where('owned_by', '=', \Auth::user()->ownedId())->get();
        }
        // dd($salaryproposal);
        return view('employee.salary_proposal.index', compact('salaryproposal'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $payscale = EmployeeScale::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('scale_no', 'id');
            // $query = Competencies::where('created_by', '=', \Auth::user()->creatorId());
        }else{
            $payscale = EmployeeScale::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('scale_no', 'id');
            // $query = Competencies::where('owned_by', '=', \Auth::user()->ownedId());
        }
        $payscale->prepend('Select Scale', '');
        return view('employee.salary_proposal.create',compact('payscale'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'emp_no' => 'required',
            'payscale' => 'required',
            'income_tax' => 'required',
            'other_deduction' => 'required',
            'EOBI' => 'required',
            'gross' => 'required',
            'net_salary' => 'required',
            'pay_method' => 'required',
            'bank_name' => 'required',
            'bank_account' => 'required',
        ]);
        try {
            $salaryProposal = new SalaryProposal();
            $salaryProposal->emp_no = $request->emp_no;
            $salaryProposal->payscale = $request->payscale;
            $salaryProposal->income_tax = $request->income_tax;
            $salaryProposal->other_deduction = $request->other_deduction;
            $salaryProposal->EOBI = $request->EOBI;
            $salaryProposal->gross = $request->gross;
            $salaryProposal->net_salary = $request->net_salary;
            $salaryProposal->pay_method = $request->pay_method;
            $salaryProposal->bank_name = $request->bank_name;
            $salaryProposal->bank_account = $request->bank_account;
            $salaryProposal->created_by = \Auth::user()->creatorId();
            $salaryProposal->owned_by = \Auth::user()->ownedId();
            $salaryProposal->save();
            return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the Salary Proposal.');
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
            $payscale = EmployeeScale::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('scale_no', 'id');
            // $query = Competencies::where('created_by', '=', \Auth::user()->creatorId());
        }else{
            $payscale = EmployeeScale::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('scale_no', 'id');
            // $query = Competencies::where('owned_by', '=', \Auth::user()->ownedId());
        }
        $payscale->prepend('Select Scale', '');
        $salaryProposal = SalaryProposal::with('employees','employees.designation','employees.department')->where('id',$id)->first();
        // dd($salaryProposal);
        return view('employee.salary_proposal.edit',compact('payscale','salaryProposal'));
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
        // add check 
        // dd($request->all());
        $validation = Validator::make($request->all(), [
            'emp_no' => 'required',
            'payscale' => 'required',
            'income_tax' => 'required',
            'other_deduction' => 'required',
            'EOBI' => 'required',
            'gross' => 'required',
            'net_salary' => 'required',
            'pay_method' => 'required',
            'bank_name' => 'required',
            'bank_account' => 'required',
        ]);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }    
        try {
            $salaryProposal = SalaryProposal::find($id);
            $salaryProposal->emp_no = $request->emp_no;
            $salaryProposal->payscale = $request->payscale;
            $salaryProposal->income_tax = $request->income_tax;
            $salaryProposal->other_deduction = $request->other_deduction;
            $salaryProposal->EOBI = $request->EOBI;
            $salaryProposal->gross = $request->gross;
            $salaryProposal->net_salary = $request->net_salary;
            $salaryProposal->pay_method = $request->pay_method;
            $salaryProposal->bank_name = $request->bank_name;
            $salaryProposal->bank_account = $request->bank_account;
            $salaryProposal->created_by = \Auth::user()->creatorId();
            $salaryProposal->owned_by = \Auth::user()->ownedId();
            $salaryProposal->save();
            return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the Salary Proposal.');
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
        SalaryProposal::where('id',$id)->delete();
        return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal deleted successfully.');
    }

    public function emp_scale_detail(Request $request, $id = null){
        $net_gross = 0;
        $scale = EmployeeScale::with('employeeScaleHeads')->where('id',$id)->where('created_by', \Auth::user()->creatorId())->first();
        $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
        // dd($scale);
        foreach ($heads as $account) {
            $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
            $net_gross += @$headValue->head_value;
        }
        // dd($net_gross);
        return response([
            // 'scale' => $scale,
            'e' => $net_gross
        ]);
    }

    public function sendForApproval($id)
    {
        $salaryProposal = SalaryProposal::find($id);
        $salaryProposal->status = 1; // Status for "Send for Approval"
        $salaryProposal->save();
        return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal sent for approval successfully.');
    }

    public function approve($id)
    {
        $salaryProposal = SalaryProposal::find($id);
        $salaryProposal->status = 2; // Status for "Approve"
        $salaryProposal->save();
        return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal approved successfully.');
    }

    public function rollback($id)
    {
        $salaryProposal = SalaryProposal::find($id);
        $salaryProposal->status = 3; // Status for "Rollback"
        $salaryProposal->save();
        return redirect()->route('employee-salary-proporal.index')->with('success', 'Salary Proposal rolled back successfully.');
    }
}
