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
        return response([
            'e' => $this->scaleGrossAmount($id)
        ]);
    }


    public function bulkCreate()
    {
        [$sessions, $branches, $departments, $designations, $activeSessionId] = $this->bulkFilterOptions();
        $branchLocked = \Auth::user()->type === 'branch';
        $defaultBranchId = $branchLocked ? $this->currentUserBranchId() : null;
        return view('employee.salary_proposal.bulk_create', compact('sessions', 'branches', 'departments', 'designations', 'branchLocked', 'defaultBranchId', 'activeSessionId'));
    }

    public function bulkSearch(Request $request)
    {
        $user = \Auth::user();
        $branchId = $this->currentUserBranchId();

        if ($user->type === 'branch') {
            $request->merge(['branch_id' => $branchId]);
        }

        $employeesQuery = \App\Models\Employee::with([
                'department',
                'designation',
                'employee_payscale_details' => function ($query) {
                    $query->orderByDesc('id');
                },
            ])
            ->when($user->type === 'company', function ($query) use ($user) {
                $query->where('created_by', $user->creatorId());
            }, function ($query) use ($user) {
                $query->where('owned_by', $user->ownedId());
            })
            ->where('is_active', 1);

        if ($request->filled('branch_id')) {
            $employeesQuery->where('branch_id', $request->branch_id);
        }
        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('designation_id')) {
            $employeesQuery->where('designation_id', $request->designation_id);
        }

        $employees = $employeesQuery->orderBy('name')->get();
        $payScales = $this->bulkPayScaleOptions($request->department_id);
        $tuitionHead = $this->tuitionHead();

        $payload = $employees->map(function ($employee) use ($request, $payScales, $tuitionHead) {
            return $this->buildBulkEmployeePayload($employee, $request->session_id, $payScales, $tuitionHead);
        })->values();

        return response()->json([
            'success' => true,
            'employees' => $payload,
            'pay_scales' => $this->formatPayScaleOptions($payScales),
        ]);
    }

    public function bulkPreview(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'payscale_id' => 'nullable',
        ]);

        $employee = \App\Models\Employee::with([
            'department',
            'designation',
            'employee_payscale_details' => function ($query) {
                $query->orderByDesc('id');
            },
        ])->findOrFail($request->employee_id);

        $payScales = $this->bulkPayScaleOptions($request->department_id);
        $tuitionHead = $this->tuitionHead();

        return response()->json([
            'success' => true,
            'employee' => $this->buildBulkEmployeePayload($employee, $request->session_id, $payScales, $tuitionHead, $request->payscale_id),
        ]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'proposals' => 'required|array|min:1',
        ]);

        $created = 0;

        try {
            \DB::transaction(function () use ($request, &$created) {
                foreach ($request->input('proposals', []) as $proposal) {
                    if (empty($proposal['employee_id']) || empty($proposal['emp_no']) || empty($proposal['payscale'])) {
                        continue;
                    }

                    $salaryProposal = new SalaryProposal();
                    $salaryProposal->emp_no = $proposal['emp_no'];
                    $salaryProposal->payscale = $proposal['payscale'];
                    $salaryProposal->income_tax = $proposal['income_tax'] ?? 0;
                    $salaryProposal->other_deduction = $proposal['other_deduction'] ?? 0;
                    $salaryProposal->EOBI = $proposal['EOBI'] ?? 0;
                    $salaryProposal->gross = $proposal['gross'] ?? 0;
                    $salaryProposal->net_salary = $proposal['net_salary'] ?? 0;
                    $salaryProposal->pay_method = $proposal['pay_method'] ?? '';
                    $salaryProposal->bank_name = $proposal['bank_name'] ?? '';
                    $salaryProposal->bank_account = $proposal['bank_account'] ?? '';
                    $salaryProposal->created_by = \Auth::user()->creatorId();
                    $salaryProposal->owned_by = \Auth::user()->ownedId();
                    $salaryProposal->save();
                    $created++;
                }
            });

            if ($created === 0) {
                return redirect()->back()->with('error', 'No valid employees were selected for bulk salary proposal creation.');
            }

            return redirect()->route('employee-salary-proporal.index')->with('success', $created . ' salary proposal(s) created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the bulk salary proposals.');
        }
    }

    private function bulkFilterOptions(): array
    {
        $user = \Auth::user();

        if ($user->type === 'company') {
            $sessions = \App\Models\Session::where('created_by', $user->creatorId())->pluck('year', 'id');
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('Select Branch', '');
            $departments = \App\Models\Department::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $designations = collect();
        } else {
            $sessions = \App\Models\Session::where('owned_by', $user->ownedId())->pluck('year', 'id');
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
            $departments = \App\Models\Department::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $designations = collect();
        }

        $activeSessionId = $this->activeSessionId();

        $sessions->prepend('Select Session', '');
        $departments->prepend('Select Department', '');

        return [$sessions, $branches, $departments, $designations, $activeSessionId];
    }

    private function bulkPayScaleOptions($departmentId)
    {
        $user = \Auth::user();
        $query = EmployeeScale::query();

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        return $query->with('employeeScaleHeads.SalaryHeads')->orderBy('scale_no')->get();
    }

    private function formatPayScaleOptions($payScales)
    {
        return $payScales->map(function ($scale) {
            return [
                'id' => $scale->id,
                'label' => $scale->scale_no,
                'department_id' => $scale->department_id,
            ];
        })->values();
    }

    private function currentUserBranchId()
    {
        $user = \Auth::user();

        return $user->branch_id ?? $user->ownedId() ?? $user->id;
    }

    private function activeSessionId()
    {
        $user = \Auth::user();
        $query = \App\Models\Session::where('active_status', 1);

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        return optional($query->orderByDesc('id')->first())->id;
    }


    private function employeeDisplayName($employee)
    {
        return trim((string) ($employee->name ?? $employee->employee_name ?? $employee->full_name ?? $employee->f_name ?? ''));
    }

    private function employeeNumber($employee)
    {
        return $employee->employee_id ?? $employee->emp_no ?? $employee->id;
    }

    private function employeeDoj($employee)
    {
        return $employee->company_doj ?? $employee->joining_date ?? null;
    }

    private function employeeProbationEnd($employee)
    {
        return $employee->probation_end ?? null;
    }

    private function formatDateValue($value)
    {
        if (empty($value) || $value === '0000-00-00') {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d-M-Y');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    private function serviceDuration($employee)
    {
        if (method_exists($employee, 'getEmployeeTenure')) {
            return $employee->getEmployeeTenure($employee->id);
        }

        $doj = $this->employeeDoj($employee);
        if (empty($doj)) {
            return '-';
        }

        try {
            $diff = \Carbon\Carbon::parse($doj)->diff(now());
            return $diff->y . 'years ' . $diff->m . 'Months';
        } catch (\Exception $e) {
            return '-';
        }
    }

    private function employeeCurrentScaleId($employee)
    {
        $detail = $employee->employee_payscale_details->first();
        if (!$detail) {
            return null;
        }

        return $detail->pay_scale_id ?? $detail->payscale_id ?? $detail->id;
    }

    private function employeeLatestProposal($employee)
    {
        $employeeNo = $this->employeeNumber($employee);

        return SalaryProposal::where('emp_no', $employeeNo)->orderByDesc('id')->first();
    }

    private function previousDeductionTotal($employee)
    {
        $proposal = $this->employeeLatestProposal($employee);
        if (!$proposal) {
            return 0;
        }

        return round((float) ($proposal->income_tax ?? 0) + (float) ($proposal->other_deduction ?? 0) + (float) ($proposal->EOBI ?? 0), 2);
    }

    private function scaleGrossAmount($scaleId)
    {
        if (empty($scaleId)) {
            return 0;
        }

        $user = \Auth::user();
        $query = EmployeeScale::with('employeeScaleHeads')->where('id', $scaleId);

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        $scale = $query->first();
        if (!$scale) {
            return 0;
        }

        $heads = SalaryHeads::where($user->type === 'company' ? 'created_by' : 'owned_by', $user->type === 'company' ? $user->creatorId() : $user->ownedId())->get();
        $netGross = 0;
        foreach ($heads as $account) {
            $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
            $netGross += (float) (@$headValue->head_value ?? 0);
        }

        return round($netGross, 2);
    }

    private function scaleLabel($scaleId)
    {
        if (empty($scaleId)) {
            return '-';
        }

        $user = \Auth::user();
        $query = EmployeeScale::where('id', $scaleId);

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        return optional($query->first())->scale_no ?? '-';
    }

    private function taxAmountForEmployee($employeeId, $scaleId)
    {
        if (empty($employeeId) || empty($scaleId)) {
            return 0;
        }

        try {
            $taxRequest = new Request([
                'employee_id' => $employeeId,
                'empScaleId' => $scaleId,
            ]);

            $response = app(\App\Http\Controllers\TaxSlabsController::class)->calculateTax($taxRequest);
            $payload = method_exists($response, 'getData') ? $response->getData(true) : json_decode($response->getContent(), true);

            if (!is_array($payload) || isset($payload['error'])) {
                return 0;
            }

            return (float) ($payload['permonthtax'] ?? $payload['totaltax'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function tuitionHead()
    {
        return \App\Models\FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%TUITION%')])->first();
    }

    private function employeeChildrenRegistrations($employee, $sessionId = null)
    {
        if (empty($employee->cnic)) {
            return collect();
        }

        return \App\Models\StudentRegistration::with(['session', 'class', 'branches', 'fee_structure', 'concession.policy_head'])
            ->where(function ($query) use ($employee) {
                $query->where('fathercnic', $employee->cnic)
                    ->orWhere('mothercnic', $employee->cnic);
            })
            ->when(!empty($sessionId), function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->get();
    }

    private function childFeeForStudent($student, $tuitionHead)
    {
        if (!$student || !$tuitionHead) {
            return 0;
        }

        $feeStructure = $student->fee_structure->firstWhere('head_id', $tuitionHead->id);
        $baseAmount = (float) ($feeStructure->amount ?? 0);

        if ($baseAmount <= 0) {
            return 0;
        }

        $concession = \App\Models\Concession::with('policy_head')
            ->where('student_id', $student->id)
            ->where('status', 'Approved')
            ->where('active_status', 1)
            ->where(function ($query) {
                $query->where('end_date', '>=', date('Y-m-d'))
                    ->orWhereNull('end_date');
            })
            ->orderByDesc('id')
            ->first();

        $percentage = 0;
        if ($concession) {
            $policyHead = $concession->policy_head->firstWhere('head_id', $tuitionHead->id);
            $percentage = (float) ($policyHead->percentage ?? 0);
        }

        return round($baseAmount - (($baseAmount * $percentage) / 100), 2);
    }

    private function buildBulkEmployeePayload($employee, $sessionId, $payScales, $tuitionHead, $selectedScaleId = null)
    {
        $currentScaleId = $this->employeeCurrentScaleId($employee);
        $availableScaleIds = $payScales->pluck('id')->all();
        $selectedScaleId = $selectedScaleId ?: ($currentScaleId && in_array($currentScaleId, $availableScaleIds, true) ? $currentScaleId : ($availableScaleIds[0] ?? null));

        $children = $this->employeeChildrenRegistrations($employee, $sessionId);
        $childCount = $children->count();
        $childAmount = $children->sum(function ($student) use ($tuitionHead) {
            return $this->childFeeForStudent($student, $tuitionHead);
        });

        $currentGross = $this->scaleGrossAmount($currentScaleId);
        $currentTax = $this->taxAmountForEmployee($employee->id, $currentScaleId);
        $currentDeduction = $this->previousDeductionTotal($employee);
        $currentCtc = round($currentGross + $currentTax + $currentDeduction + $childAmount, 2);

        $newGross = $this->scaleGrossAmount($selectedScaleId);
        $newTax = $this->taxAmountForEmployee($employee->id, $selectedScaleId);
        $newCtc = round($newGross + $newTax + $childAmount, 2);

        return [
            'employee_id' => $employee->id,
            'employee_no' => $this->employeeNumber($employee),
            'employee_name' => $this->employeeDisplayName($employee),
            'doj' => $this->formatDateValue($this->employeeDoj($employee)),
            'service_duration' => $this->serviceDuration($employee),
            'probation_end_date' => $this->formatDateValue($this->employeeProbationEnd($employee)),
            'department_name' => optional($employee->department)->name ?? '-',
            'designation_name' => optional($employee->designation)->name ?? '-',
            'current_scale_id' => $currentScaleId,
            'current_scale_label' => $this->scaleLabel($currentScaleId),
            'current_gross' => round($currentGross, 2),
            'current_tax' => round($currentTax, 2),
            'current_deduction' => round($currentDeduction, 2),
            'child_count' => $childCount,
            'child_amount' => round($childAmount, 2),
            'current_ctc' => round($currentCtc, 2),
            'selected_scale_id' => $selectedScaleId,
            'selected_scale_label' => $this->scaleLabel($selectedScaleId),
            'new_gross' => round($newGross, 2),
            'new_tax' => round($newTax, 2),
            'new_child_amount' => round($childAmount, 2),
            'new_ctc' => round($newCtc, 2),
        ];
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
