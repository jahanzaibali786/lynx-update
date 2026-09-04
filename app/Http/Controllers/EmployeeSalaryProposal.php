<?php

namespace App\Http\Controllers;

use App\Models\EmployeeScale;
use App\Models\SalaryHeads;
use App\Models\SalaryProposal;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryProposalColumnSetting;
use Illuminate\Http\Request;
use Validator;

class EmployeeSalaryProposal extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        // Resolve employees first so filtering never depends on the legacy proposal relationship.
        $employeeQuery = Employee::with(['department', 'designation', 'userbranch']);
        if ($user->type === 'company') {
            $employeeQuery->where('created_by', $user->creatorId());
        } else {
            $employeeQuery->where('owned_by', $user->ownedId());
        }

        $accessibleEmployees = $employeeQuery->orderBy('name')->get();
        $employeeMap = $accessibleEmployees->keyBy('id');

        if ($user->type === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->orderBy('name')
                ->pluck('name', 'id');

            // Include HO/company itself as a valid branch option.
            $branches->put($user->id, $user->name . ' (HO)');
        } else {
            $branchId = $this->currentUserBranchId();
            $branches = User::where('id', $branchId)->pluck('name', 'id');
        }
        $branchNameMap = collect($branches->all());
        $branches->prepend('All Branches', '');

        $departments = $accessibleEmployees
            ->filter(fn ($employee) => !empty($employee->department_id))
            ->mapWithKeys(fn ($employee) => [
                $employee->department_id => optional($employee->department)->name ?: ('Department #' . $employee->department_id),
            ])
            ->sort();
        $departments->prepend('All Departments', '');

        $designations = $accessibleEmployees
            ->filter(fn ($employee) => !empty($employee->designation_id))
            ->mapWithKeys(fn ($employee) => [
                $employee->designation_id => optional($employee->designation)->name ?: ('Designation #' . $employee->designation_id),
            ])
            ->sort();
        $designations->prepend('All Designations', '');

        $employees = $accessibleEmployees->pluck('name', 'id');
        $employees->prepend('All Employees', '');

        $query = SalaryProposal::with(['employees', 'employee_payscale']);
        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        // Default listing: Draft + Pending Approval.
        $statusFilter = $request->input('status', 'open');
        if ($statusFilter === 'open' || $statusFilter === null || $statusFilter === '') {
            $query->whereIn('status', [0, 1]);
        } elseif ($statusFilter !== 'all' && in_array((string) $statusFilter, ['0', '1', '2', '3'], true)) {
            $query->where('status', (int) $statusFilter);
        }

        $salaryproposal = $query->orderByDesc('id')->get();

        // Apply employee-based filters using snapshot employee_id as the source of truth.
        $branchFilter = $request->input('branch_id');
        $departmentFilter = $request->input('department_id');
        $designationFilter = $request->input('designation_id');
        $employeeFilter = $request->input('employee_id');

        $salaryproposal = $salaryproposal->filter(function ($proposal) use (
            $employeeMap,
            $branchFilter,
            $departmentFilter,
            $designationFilter,
            $employeeFilter
        ) {
            $newSnapshot = $this->decodeProposalSnapshot($proposal->new_salarysnapshot);
            $previousSnapshot = $this->decodeProposalSnapshot($proposal->prev_salary_snapshot);
            $employeeId = $newSnapshot['employee_id'] ?? $previousSnapshot['employee_id'] ?? null;
            $employee = $employeeId ? $employeeMap->get((int) $employeeId) : null;

            if (!empty($employeeFilter) && (int) $employeeId !== (int) $employeeFilter) {
                return false;
            }

            $proposalBranchId = $employee->owned_by ?? $proposal->owned_by ?? null;
            if (!empty($branchFilter) && (int) $proposalBranchId !== (int) $branchFilter) {
                return false;
            }

            if (!empty($departmentFilter) && (int) ($employee->department_id ?? 0) !== (int) $departmentFilter) {
                return false;
            }

            if (!empty($designationFilter) && (int) ($employee->designation_id ?? 0) !== (int) $designationFilter) {
                return false;
            }

            return true;
        })->values();

        return view('employee.salary_proposal.index', compact(
            'salaryproposal',
            'branches',
            'departments',
            'designations',
            'employees',
            'statusFilter',
            'employeeMap',
            'branchNameMap'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();

        if ($user->type === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->orderBy('name')
                ->pluck('name', 'id');

            // Include HO/company itself as a selectable branch.
            $branches->put($user->id, $user->name . ' (HO)');
            $branches->prepend('Select Branch', '');
            $defaultBranchId = null;
        } else {
            $defaultBranchId = $this->currentUserBranchId();
            $branches = User::where('id', $defaultBranchId)->pluck('name', 'id');
        }

        $employees = collect();
        $payscale = collect(['' => 'Select Scale']);
        $activeSessionId = $this->activeSessionId();

        return view('employee.salary_proposal.create', compact(
            'branches',
            'employees',
            'payscale',
            'defaultBranchId',
            'activeSessionId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();

        $validator = Validator::make($request->all(), [
            'branches' => 'required',
            'employee_id' => 'required|integer',
            'payscale' => 'required|integer',
            'effect_from' => 'required|date',
            'session_id' => 'nullable',
            'pay_method' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'working_days' => 'nullable|numeric|min:1|max:31',
            'drns' => 'nullable|numeric',
            'conv' => 'nullable|numeric',
            'misc' => 'nullable|numeric',
            'other_add' => 'nullable|numeric',
            'chaild_concession' => 'nullable|numeric',
            'emp_sec_percentage' => 'nullable|numeric',
            'emp_sec' => 'nullable|numeric',
            'eobi_percentage' => 'nullable|numeric',
            'eobi' => 'nullable|numeric',
            'eobi_employer_percentage' => 'nullable|numeric',
            'eobi_employer' => 'nullable|numeric',
            'pessi_percentage' => 'nullable|numeric',
            'pessi' => 'nullable|numeric',
            'pessi_employer_percentage' => 'nullable|numeric',
            'pessi_employer' => 'nullable|numeric',
            'itax' => 'nullable|numeric',
            'other_deduction' => 'nullable|numeric',
            'advance' => 'nullable|numeric',
            'net' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError ?: 'Please correct the highlighted fields.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \DB::transaction(function () use ($request, $user) {
                $employee = \App\Models\Employee::with([
                    'department',
                    'designation',
                    'employee_payscale_details' => function ($query) {
                        $query->orderByDesc('id');
                    },
                ])->findOrFail($request->employee_id);

                if ($user->type === 'company') {
                    if ((int) $employee->created_by !== (int) $user->creatorId()) {
                        throw new \RuntimeException('Selected employee does not belong to your company.');
                    }

                    if ((int) $request->branches !== (int) $employee->owned_by &&
                        (int) $request->branches !== (int) $employee->branch_id &&
                        (int) $request->branches !== (int) $user->id) {
                        throw new \RuntimeException('Selected employee does not belong to the selected branch.');
                    }
                } else {
                    if ((int) $employee->owned_by !== (int) $user->ownedId()) {
                        throw new \RuntimeException('Selected employee does not belong to your branch.');
                    }
                }

                $payScales = $this->bulkPayScaleOptions($employee->department_id);
                if (!$payScales->contains('id', (int) $request->payscale)) {
                    throw new \RuntimeException('Selected pay scale is not valid for the employee department.');
                }

                // Single Create follows the same UPSERT rule as Bulk Create.
                // Same employee + same target payscale in Draft/Pending => update it.
                // A different target payscale has no match and therefore creates a new proposal.
                $existingActiveProposal = $this->sameScaleActiveProposal(
                    $employee,
                    (int) $request->payscale,
                    true
                );

                $tuitionHead = $this->tuitionHead();
                $payload = $this->buildBulkEmployeePayload(
                    $employee,
                    $request->session_id ?: $this->activeSessionId(),
                    $payScales,
                    $tuitionHead,
                    $request->payscale
                );

                [$previousSnapshot, $newSnapshot] = $this->buildProposalSnapshots(
                    $employee,
                    $payload,
                    $request->session_id ?: $this->activeSessionId(),
                    $request->effect_from
                );

                $latestDetail = $employee->employee_payscale_details->first();
                $autoAccounts = $this->resolveSingleProposalAccountMappings($employee, $latestDetail);

                // Single-create form contains salary values only. Accounting mappings are assigned automatically.
                $detailOverrides = [
                    'paymode' => $request->pay_method ?? ($newSnapshot['salary_detail']['paymode'] ?? ''),
                    'account_number' => $request->bank_account ?? '',
                    'account_id' => $autoAccounts['account_id'],
                    'working_days' => $request->working_days ?: 30,
                    'drns' => (float) ($request->drns ?? 0),
                    'conv' => (float) ($request->conv ?? 0),
                    'misc' => (float) ($request->misc ?? 0),
                    'other_add' => (float) ($request->other_add ?? 0),
                    'chaild_concession' => (float) ($request->chaild_concession ?? 0),
                    'emp_sec' => (float) ($request->emp_sec ?? 0),                    'security_receive_account' => $autoAccounts['security_receive_account'],
                    'itax' => (float) ($payload['new_tax'] ?? 0),                    'tax_payable_account' => $autoAccounts['tax_payable_account'],
                    'eobi' => (float) ($request->eobi ?? 0),
                    'eobi_employer' => (float) ($request->eobi_employer ?? 0),                    'eobi_payable_account' => $autoAccounts['eobi_payable_account'],
                    'pessi' => (float) ($request->pessi ?? 0),
                    'pessi_employer' => (float) ($request->pessi_employer ?? 0),                    'pessi_payable_account' => $autoAccounts['pessi_payable_account'],
                    'other_deduction' => (float) ($request->other_deduction ?? 0),                    'other_dedu_payable_account' => $autoAccounts['other_dedu_payable_account'],
                    'advance' => (float) ($request->advance ?? 0),                    'advance_payable_account' => $autoAccounts['advance_payable_account'],
                    'net' => (float) ($request->net ?? 0),                    'net_payable_account' => $autoAccounts['net_payable_account'],
                    'effect_from' => $request->effect_from,
                ];
                $newSnapshot['salary_detail'] = array_merge($newSnapshot['salary_detail'], $detailOverrides);
                $newSnapshot['proposal_source'] = 'single';
                $previousSnapshot['proposal_source'] = 'single';

                // Preserve the employee percentages so approval can update Employee exactly like salary detail does.
                $newSnapshot['employee_percentages'] = [
                    'security' => (float) ($request->emp_sec_percentage ?? $employee->security ?? 0),
                    'eobi' => (float) ($request->eobi_percentage ?? $employee->eobi ?? 0),
                    'eobi_employer' => (float) ($request->eobi_employer_percentage ?? $employee->eobi_employer ?? 0),
                    'pessi' => (float) ($request->pessi_percentage ?? $employee->pessi ?? 0),
                    'pessi_employer' => (float) ($request->pessi_employer_percentage ?? $employee->pessi_employer ?? 0),
                ];

                $baseScaleGross = (float) ($payload['new_gross'] ?? 0);
                $grossWithAdditions = $baseScaleGross
                    + (float) ($request->drns ?? 0)
                    + (float) ($request->conv ?? 0)
                    + (float) ($request->misc ?? 0)
                    + (float) ($request->other_add ?? 0);
                $netSalary = (float) ($request->net ?? (
                    $grossWithAdditions
                    - (float) ($request->emp_sec ?? 0)
                    - (float) ($request->eobi ?? 0)
                    - (float) ($request->pessi ?? 0)
                    - (float) ($payload['new_tax'] ?? 0)
                    - (float) ($request->other_deduction ?? 0)
                    - (float) ($request->advance ?? 0)
                ));
                $ctc = $grossWithAdditions
                    + (float) ($request->eobi_employer ?? 0)
                    + (float) ($request->pessi_employer ?? 0)
                    + (float) ($request->chaild_concession ?? 0);

                $newSnapshot['base_scale_gross'] = round($baseScaleGross, 2);
                $newSnapshot['gross'] = round($grossWithAdditions, 2);
                $newSnapshot['emp_security'] = round((float) ($request->emp_sec ?? 0), 2);
                $newSnapshot['tax'] = round((float) ($payload['new_tax'] ?? 0), 2);
                $newSnapshot['eobi_employee'] = round((float) ($request->eobi ?? 0), 2);
                $newSnapshot['pessi_employee'] = round((float) ($request->pessi ?? 0), 2);
                $newSnapshot['eobi_employer'] = round((float) ($request->eobi_employer ?? 0), 2);
                $newSnapshot['pessi_employer'] = round((float) ($request->pessi_employer ?? 0), 2);
                $newSnapshot['eobi_pessi_employee'] = round((float) ($request->eobi ?? 0) + (float) ($request->pessi ?? 0), 2);
                $newSnapshot['net_salary'] = round($netSalary, 2);
                $newSnapshot['child_concession'] = round((float) ($request->chaild_concession ?? 0), 2);
                $newSnapshot['employer_contribution'] = round((float) ($request->eobi_employer ?? 0) + (float) ($request->pessi_employer ?? 0), 2);
                $newSnapshot['cost_to_company'] = round($ctc, 2);

                $proposal = $existingActiveProposal ?: new SalaryProposal();
                $isUpdate = (bool) $existingActiveProposal;

                $proposal->emp_no = $payload['employee_no'];
                $proposal->payscale = $payload['selected_scale_id'];
                $proposal->income_tax = $newSnapshot['tax'];
                $proposal->other_deduction = $newSnapshot['emp_security'];
                $proposal->EOBI = $newSnapshot['eobi_pessi_employee'];
                $proposal->gross = $newSnapshot['gross'];
                $proposal->net_salary = $newSnapshot['net_salary'];
                $proposal->pay_method = $newSnapshot['salary_detail']['paymode'] ?? '';
                $proposal->bank_name = ''; // bank name field removed from proposal create form
                $proposal->bank_account = $newSnapshot['salary_detail']['account_number'] ?? '';
                $proposal->prev_salary_snapshot = json_encode($previousSnapshot);
                $proposal->new_salarysnapshot = json_encode($newSnapshot);
                // Keep an existing active workflow state when updating.
                // A brand-new Single proposal starts as Pending Approval.
                if (!$isUpdate) {
                    $proposal->status = 1;
                }
                $proposal->created_by = $user->creatorId();
                $proposal->owned_by = $employee->owned_by;
                $proposal->save();
            });

            $successMessage = 'Salary Proposal saved successfully. Existing same-payscale proposal was updated when applicable.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect_url' => route('employee-salary-proporal.index'),
                ]);
            }

            return redirect()->route('employee-salary-proporal.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Single salary proposal creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Unable to create salary proposal: ' . $e->getMessage();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function singleEmployeePreview(Request $request)
    {
        $user = \Auth::user();

        /*
         * This endpoint serves two small AJAX needs for the single-create modal:
         * 1) branch_id only  -> return employees for that branch
         * 2) employee_id     -> return the selected employee salary preview
         *
         * Keeping both here avoids depending on the generic branch.employees endpoint,
         * whose response shape/custom-select lifecycle was causing the Employee dropdown
         * to remain visually empty even though employees were returned by AJAX.
         */
        if ($request->filled('branch_id') && !$request->filled('employee_id')) {
            $request->validate([
                'branch_id' => 'required|integer',
            ]);

            $selectedBranchId = (int) $request->branch_id;

            $employeesQuery = \App\Models\Employee::query()
                ->select(['id', 'name', 'employee_id', 'owned_by', 'branch_id', 'is_active', 'is_res_ter']);

            if ($user->type === 'company') {
                $employeesQuery->where('created_by', $user->creatorId())
                    ->where(function ($query) use ($selectedBranchId) {
                        $query->where('owned_by', $selectedBranchId)
                            ->orWhere('branch_id', $selectedBranchId);
                    });
            } else {
                $branchId = (int) $this->currentUserBranchId();
                abort_unless($selectedBranchId === $branchId, 403);

                $employeesQuery->where('owned_by', $user->ownedId());
            }

            // Match the active employee conditions already used by the bulk proposal flow.
            $employeesQuery->where(function ($query) {
                $query->whereNull('is_active')->orWhere('is_active', 1);
            })->where(function ($query) {
                $query->whereNull('is_res_ter')->orWhere('is_res_ter', 0);
            });

            $employees = $employeesQuery
                ->orderBy('name')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'employee_no' => $employee->employee_id ?? null,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'employees' => $employees,
            ]);
        }

        $request->validate([
            'employee_id' => 'required|integer',
            'payscale_id' => 'nullable|integer',
            'session_id' => 'nullable',
        ]);

        $employee = \App\Models\Employee::with([
            'department',
            'designation',
            'employee_payscale_details' => function ($query) {
                $query->orderByDesc('id');
            },
        ])->findOrFail($request->employee_id);

        if ($user->type === 'company') {
            abort_unless((int) $employee->created_by === (int) $user->creatorId(), 403);
        } else {
            abort_unless((int) $employee->owned_by === (int) $user->ownedId(), 403);
        }

        $payScales = $this->bulkPayScaleOptions($employee->department_id);
        $tuitionHead = $this->tuitionHead();
        $payload = $this->buildBulkEmployeePayload(
            $employee,
            $request->session_id ?: $this->activeSessionId(),
            $payScales,
            $tuitionHead,
            $request->payscale_id
        );

        $latestDetail = $employee->employee_payscale_details->first();
        $schoolDetails = \App\Models\SchoolDetails::where('branch_id', $employee->owned_by)->first();
        $selectedScaleId = $payload['selected_scale_id'] ?? null;
        $initialBasic = $this->scaleComponentAmount($selectedScaleId, ['initial basic']);

        return response()->json([
            'success' => true,
            'employee' => $payload,
            'pay_scales' => $this->formatPayScaleOptions($payScales),
            'payment' => [
                'pay_method' => $latestDetail->paymode ?? '',
                // Bank A/C should come from Employee first; old salary detail is only a fallback.
                'bank_account' => $employee->bank_account
                    ?? $employee->account_number
                    ?? $employee->bank_account_number
                    ?? $employee->account_no
                    ?? ($latestDetail->account_number ?? ''),
                'account_id' => $latestDetail->account_id ?? null,
            ],
            'percentages' => [
                'security' => (float) ($employee->security ?? 0),
                'eobi' => (float) ($employee->eobi ?? 0),
                'eobi_employer' => (float) ($employee->eobi_employer ?? 0),
                'pessi' => (float) ($employee->pessi ?? 0),
                'pessi_employer' => (float) ($employee->pessi_employer ?? 0),
            ],
            'company_values' => [
                'eobi' => (float) ($schoolDetails->eobi_values ?? 0),
                'pessi' => (float) ($schoolDetails->pessi_values ?? 0),
            ],
            'scale_initial_basic' => round((float) $initialBasic, 2),
            'latest_detail' => [
                // Explicitly expose whether salary/payscale detail already exists.
                // The create modal uses this to decide whether Bank A/C is display-only or editable.
                'has_existing_payscale' => !empty($latestDetail),
                'id' => $latestDetail->id ?? null,
                'pay_scale_id' => $latestDetail->pay_scale_id ?? null,
                'account_number' => $latestDetail->account_number ?? null,
                'working_days' => $latestDetail->working_days ?? 30,
                'drns' => (float) ($latestDetail->drns ?? 0),
                'conv' => (float) ($latestDetail->conv ?? 0),
                'misc' => (float) ($latestDetail->misc ?? 0),
                'other_add' => (float) ($latestDetail->other_add ?? 0),
                'chaild_concession' => (float) ($latestDetail->chaild_concession ?? 0),
                'other_deduction' => (float) ($latestDetail->other_deduction ?? 0),
                'advance' => (float) ($latestDetail->advance ?? 0),
                'security_receive_account' => $latestDetail->security_receive_account ?? null,
                'tax_payable_account' => $latestDetail->tax_payable_account ?? null,
                'eobi_payable_account' => $latestDetail->eobi_payable_account ?? null,
                'pessi_payable_account' => $latestDetail->pessi_payable_account ?? null,
                'other_dedu_payable_account' => $latestDetail->other_dedu_payable_account ?? null,
                'advance_payable_account' => $latestDetail->advance_payable_account ?? null,
                'net_payable_account' => $latestDetail->net_payable_account ?? null,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();

        $query = SalaryProposal::with(['employees', 'employee_payscale'])->where('id', $id);

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        $salaryProposal = $query->firstOrFail();
        $previousSnapshot = $this->decodeProposalSnapshot($salaryProposal->prev_salary_snapshot);
        $newSnapshot = $this->decodeProposalSnapshot($salaryProposal->new_salarysnapshot);

        $employeeId = $newSnapshot['employee_id'] ?? $previousSnapshot['employee_id'] ?? null;
        $employee = $employeeId ? \App\Models\Employee::with(['department', 'designation', 'userbranch'])->find($employeeId) : null;

        // Both Single and Bulk proposals use one shared detailed show/approval view.
        $view = 'employee.salary_proposal.show_single';

        $accounts = collect();
        $payableaccounts = collect();
        $selectedAccounting = [];

        if ($user->type === 'company') {
            $creatorId = $user->creatorId();
            $latestDetail = $employee
                ? \App\Models\EmployeePayscaleDetail::where('employee_id', $employee->id)->orderByDesc('id')->first()
                : null;
            $proposalDetail = is_array($newSnapshot['salary_detail'] ?? null) ? $newSnapshot['salary_detail'] : [];

            $bankAccounts = BankAccount::where('created_by', $creatorId)->get();
            $accounts = $bankAccounts->mapWithKeys(function ($account) {
                $parts = array_filter([
                    trim((string) ($account->bank_name ?? '')),
                    trim((string) ($account->holder_name ?? '')),
                    trim((string) ($account->account_number ?? '')),
                ]);
                $label = trim(implode(' - ', $parts));
                return [$account->id => ($label !== '' ? $label : ('Account #' . $account->id))];
            });
            $accounts->prepend('Select Bank Account', '');

            $chartAccounts = ChartOfAccount::where('created_by', $creatorId)->orderBy('name')->get();
            $payableaccounts = $chartAccounts->pluck('name', 'id');
            $payableaccounts->prepend('Select Account', '');

            $defaultLabels = [
                'eobi_payable_account' => ['EOBI Payable (Employee)'],
                'pessi_payable_account' => ['Employer PESSI Payable'],
                'security_receive_account' => ['Employee Security Payable'],
                'tax_payable_account' => ['I.Tax Payable Account', 'Income Tax Payable', 'Income Tax Payable Account'],
                'other_dedu_payable_account' => ['Other Deduction Payable'],
                'advance_payable_account' => ['Employee Salary Advance'],
                'net_payable_account' => ['Net Salary Payable'],
            ];

            $defaultIds = [];
            foreach ($defaultLabels as $field => $labels) {
                $match = $chartAccounts->first(function ($account) use ($labels) {
                    return in_array(trim((string) $account->name), $labels, true);
                });
                $defaultIds[$field] = optional($match)->id;
            }

            $defaultBank = $bankAccounts->first(function ($account) {
                return (string) ($account->id ?? '') === '17503'
                    || (string) ($account->account_number ?? '') === '17503';
            });

            $selectedAccounting['account_id'] = $proposalDetail['account_id']
                ?? optional($latestDetail)->account_id
                ?? optional($defaultBank)->id
                ?? optional($bankAccounts->first())->id;

            foreach (array_keys($defaultLabels) as $field) {
                $selectedAccounting[$field] = $proposalDetail[$field]
                    ?? optional($latestDetail)->{$field}
                    ?? ($defaultIds[$field] ?? null)
                    ?? optional($chartAccounts->first())->id;
            }
        }

        return view($view, compact(
            'salaryProposal',
            'previousSnapshot',
            'newSnapshot',
            'employee',
            'accounts',
            'payableaccounts',
            'selectedAccounting'
        ));
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
        $user = \Auth::user();
        $branchLocked = $user->type === 'branch';

        // Company user's own record represents HO.
        $defaultBranchId = $user->type === 'company'
            ? $user->id
            : $this->currentUserBranchId();

        // Column visibility belongs to the company/HO and is inherited by branches.
        $companyId = (int) $user->creatorId();
        $savedColumnSetting = SalaryProposalColumnSetting::where('company_id', $companyId)->first();

        $columnVisibility = [];
        foreach ($this->bulkColumnKeys() as $columnKey) {
            $columnVisibility[$columnKey] = true;
        }

        // HO can also disable selected revision inputs for all branch users.
        $branchInputRestrictions = [
            'new_payscale' => false,
            'increment_pct' => false,
            'salary_value' => false,
        ];

        if ($savedColumnSetting && is_array($savedColumnSetting->columns)) {
            foreach ($savedColumnSetting->columns as $columnKey => $isVisible) {
                if (array_key_exists($columnKey, $columnVisibility)) {
                    $columnVisibility[$columnKey] = (bool) $isVisible;
                }
            }

            $savedRestrictions = $savedColumnSetting->columns['_branch_disabled'] ?? [];
            if (is_array($savedRestrictions)) {
                foreach ($branchInputRestrictions as $key => $default) {
                    if (array_key_exists($key, $savedRestrictions)) {
                        $branchInputRestrictions[$key] = (bool) $savedRestrictions[$key];
                    }
                }
            }
        }

        return view('employee.salary_proposal.bulk_create', compact(
            'sessions',
            'branches',
            'departments',
            'designations',
            'branchLocked',
            'defaultBranchId',
            'activeSessionId',
            'columnVisibility',
            'branchInputRestrictions'
        ));
    }

    /**
     * Persist HO/company Bulk Salary Proposal column visibility.
     * Branch users only consume these settings and cannot change them.
     */
    public function updateBulkColumnVisibility(Request $request)
    {
        $user = \Auth::user();

        if ($user->type !== 'company') {
            return response()->json([
                'success' => false,
                'message' => __('Only company/HO can change bulk column visibility.'),
            ], 403);
        }

        $allowedKeys = $this->bulkColumnKeys();

        // Preferred mode: save the complete checkbox state in one AJAX request.
        if ($request->has('columns')) {
            $validator = \Validator::make($request->all(), [
                'columns' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $incoming = (array) $request->input('columns', []);
            $columns = [];

            // Always persist a complete, normalized map. Unknown keys are ignored.
            foreach ($allowedKeys as $key) {
                $raw = array_key_exists($key, $incoming) ? $incoming[$key] : true;
                $visible = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($visible === null) {
                    $visible = (bool) ((int) $raw);
                }
                $columns[$key] = (bool) $visible;
            }

            $allowedRestrictionKeys = ['new_payscale', 'increment_pct', 'salary_value'];
            $incomingRestrictions = (array) $request->input('branch_disabled', []);
            $branchDisabled = [];
            foreach ($allowedRestrictionKeys as $key) {
                $raw = array_key_exists($key, $incomingRestrictions) ? $incomingRestrictions[$key] : false;
                $disabled = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($disabled === null) {
                    $disabled = (bool) ((int) $raw);
                }
                $branchDisabled[$key] = (bool) $disabled;
            }

            // Keep visibility and branch-input restrictions in the same existing JSON column.
            $columns['_branch_disabled'] = $branchDisabled;

            $setting = SalaryProposalColumnSetting::firstOrNew([
                'company_id' => (int) $user->creatorId(),
            ]);
            $setting->columns = $columns;
            $setting->updated_by = (int) $user->id;
            $setting->save();

            return response()->json([
                'success' => true,
                'columns' => array_intersect_key($columns, array_flip($allowedKeys)),
                'branch_disabled' => $branchDisabled,
                'message' => __('Column and branch input settings saved.'),
            ]);
        }

        // Backward-compatible single-column mode for any older Blade still using it.
        $validator = \Validator::make($request->all(), [
            'column' => 'required|string',
            'visible' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $columnKey = (string) $request->column;
        if (!in_array($columnKey, $allowedKeys, true)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid salary proposal column.'),
            ], 422);
        }

        $visible = filter_var($request->visible, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($visible === null) {
            $visible = (bool) ((int) $request->visible);
        }

        $setting = SalaryProposalColumnSetting::firstOrNew([
            'company_id' => (int) $user->creatorId(),
        ]);

        $columns = is_array($setting->columns) ? $setting->columns : [];
        $existingRestrictions = is_array($columns['_branch_disabled'] ?? null)
            ? $columns['_branch_disabled']
            : [];

        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $columns)) {
                $columns[$key] = true;
            }
        }

        $columns[$columnKey] = (bool) $visible;
        $columns['_branch_disabled'] = $existingRestrictions;
        $setting->columns = $columns;
        $setting->updated_by = (int) $user->id;
        $setting->save();

        return response()->json([
            'success' => true,
            'column' => $columnKey,
            'visible' => (bool) $visible,
            'columns' => $columns,
            'message' => __('Column preference saved.'),
        ]);
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
            ->when($user->type == 'company', function ($query) use ($user) {
                $query->where('created_by', $user->creatorId());
            }, function ($query) use ($user) {
                $query->where('owned_by', $user->ownedId());
            })
            ->where('is_active', 1)->where('is_res_ter', 0);
            // dd($employeesQuery->first());
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
        $user = \Auth::user();

        $request->validate([
            'proposals' => 'required|array|min:1',
            'session_id' => 'nullable',
            'proposals.*.effect_from' => 'required|date',
        ]);

        $created = 0;
        $updated = 0;

        try {
            \DB::transaction(function () use ($request, &$created, &$updated, $user) {
                foreach ($request->input('proposals', []) as $proposal) {
                    if (empty($proposal['employee_id']) || empty($proposal['emp_no']) || empty($proposal['payscale'])) {
                        continue;
                    }

                    $employee = \App\Models\Employee::with([
                        'department',
                        'designation',
                        'employee_payscale_details' => function ($query) {
                            $query->orderByDesc('id');
                        },
                    ])->find($proposal['employee_id']);

                    if (!$employee) {
                        continue;
                    }

                    // Security / ownership check. Branch users can only create for their own branch.
                    // Company users can create for any branch that belongs to their company.
                    if ($user->type === 'company') {
                        if ((int) $employee->created_by !== (int) $user->creatorId()) {
                            continue;
                        }

                        if ($request->filled('branch_id') &&
                            (int) $employee->branch_id !== (int) $request->branch_id &&
                            (int) $employee->owned_by !== (int) $request->branch_id) {
                            continue;
                        }
                    } else {
                        if ((int) $employee->owned_by !== (int) $user->ownedId()) {
                            continue;
                        }
                    }

                    $payScales = $this->bulkPayScaleOptions($employee->department_id);
                    $tuitionHead = $this->tuitionHead();
                    $payload = $this->buildBulkEmployeePayload(
                        $employee,
                        $request->session_id,
                        $payScales,
                        $tuitionHead,
                        $proposal['payscale']
                    );

                    // Bulk creation behaves as an UPSERT for active proposals.
                    // If the same employee + target payscale already exists in Draft/Pending,
                    // update that proposal instead of creating another row.
                    $existingActiveProposal = $this->sameScaleActiveProposal(
                        $employee,
                        (int) $payload['selected_scale_id'],
                        true
                    );

                    [$previousSnapshot, $newSnapshot] = $this->buildProposalSnapshots(
                        $employee,
                        $payload,
                        $request->session_id,
                        $proposal['effect_from'] ?? null
                    );

                    $salaryProposal = $existingActiveProposal ?: new SalaryProposal();
                    $isUpdate = (bool) $existingActiveProposal;

                    $salaryProposal->emp_no = $proposal['emp_no'];
                    $salaryProposal->payscale = $payload['selected_scale_id'];
                    $salaryProposal->income_tax = $payload['new_tax'];
                    $salaryProposal->other_deduction = $payload['new_emp_security'];
                    $salaryProposal->EOBI = $payload['new_eobi_emp'];
                    $salaryProposal->gross = $payload['new_gross'];
                    $salaryProposal->net_salary = $payload['new_net_salary'];
                    $salaryProposal->pay_method = $newSnapshot['salary_detail']['paymode'] ?? '';
                    $salaryProposal->bank_name = '';
                    $salaryProposal->bank_account = $newSnapshot['salary_detail']['account_number'] ?? '';
                    $salaryProposal->prev_salary_snapshot = json_encode($previousSnapshot);
                    $salaryProposal->new_salarysnapshot = json_encode($newSnapshot);
                    // Preserve the workflow state when updating an existing active proposal.
                    // Only newly-created bulk proposals get their initial status from the creator type.
                    if (!$isUpdate) {
                        $salaryProposal->status = $user->type === 'company' ? 1 : 0;
                    }

                    $salaryProposal->created_by = $user->creatorId();
                    $salaryProposal->owned_by = $employee->owned_by;
                    $salaryProposal->save();

                    if ($isUpdate) {
                        $updated++;
                    } else {
                        $created++;
                    }
                }
            });

            if (($created + $updated) === 0) {
                $message = 'No valid employees were selected for bulk salary proposal creation/update.';

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $parts = [];
            if ($created > 0) {
                $parts[] = $created . ($user->type === 'company'
                    ? ' salary proposal(s) created and moved to Pending Approval'
                    : ' salary proposal draft(s) created');
            }
            if ($updated > 0) {
                $parts[] = $updated . ' existing Draft/Pending proposal(s) updated';
            }

            $successMessage = implode(' and ', $parts) . ' successfully.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect_url' => route('employee-salary-proporal.index'),
                ]);
            }

            return redirect()->route('employee-salary-proporal.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Bulk salary proposal creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the bulk salary proposal drafts: ' . $e->getMessage());
        }
    }

    /**
     * Columns that HO can expose/hide for the Bulk Salary Proposal table.
     */
    private function bulkColumnKeys(): array
    {
        return [
            'sr',
            'emp_no',
            'emp_name',
            'doj',
            'end_date',
            'service_period',
            'designation',
            'current_payscale',
            'current_gross',
            'current_security',
            'current_tax',
            'current_eobi',
            'current_net',
            'child_concession',
            'current_employer',
            'current_ctc',
            'department',
            'new_payscale',
            'increment_pct',
            'salary_value',
            'scale_search',
            'effect_from',
            'new_gross',
            'new_security',
            'new_eobi',
            'new_tax',
            'new_net',
            'new_employer',
            'child_count',
            'new_child',
            'new_ctc',
            'gross_diff',
        ];
    }

    private function bulkFilterOptions(): array
    {
        $user = \Auth::user();

        if ($user->type === 'company') {
            $sessions = \App\Models\Session::pluck('year', 'id');
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->orderBy('name')
                ->pluck('name', 'id');
            // Include HO/company itself in bulk branch selection.
            $branches->put($user->id, $user->name . ' (HO)');
            $branches->prepend('Select Branch', '');
            $departments = \App\Models\Department::where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $designations = collect();
        } else {
            $sessions = \App\Models\Session::pluck('year', 'id');
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

        // Employee scales are company-level structures shared with branches.
        // A branch user's owned_by is the branch id, while the scale records are
        // created under the company creator id. Always scope scales by creatorId
        // and then restrict them to the employee/filter department.
        $query = EmployeeScale::query()
            ->where('created_by', $user->creatorId())
            ->where('status', 1);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        return $query
            ->with('employeeScaleHeads.SalaryHeads')
            ->orderBy('scale_no')
            ->get();
    }

    private function formatPayScaleOptions($payScales)
    {
        return $payScales->map(function ($scale) {
            return [
                'id' => $scale->id,
                'label' => $scale->scale_no,
                'department_id' => $scale->department_id,
                'initial_basic' => $this->scaleComponentAmount($scale->id, ['initial basic']),
                'house_rent' => $this->scaleComponentAmount($scale->id, ['house rent']),
                'medical' => $this->scaleComponentAmount($scale->id, ['medical']),
                'salary_value' => round(
                    $this->scaleComponentAmount($scale->id, ['initial basic'])
                    + $this->scaleComponentAmount($scale->id, ['house rent'])
                    + $this->scaleComponentAmount($scale->id, ['medical']),
                    2
                ),
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
        return optional(
            \App\Models\Session::where('active_status', 1)
                ->orderByDesc('id')
                ->first()
        )->id;
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
        $query = EmployeeScale::with('employeeScaleHeads')
            ->where('id', $scaleId)
            ->where('created_by', $user->creatorId());

        $scale = $query->first();
        if (!$scale) {
            return 0;
        }

        $heads = SalaryHeads::where('created_by', $user->creatorId())->get();
        $netGross = 0;
        foreach ($heads as $account) {
            $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
            $netGross += (float) (@$headValue->head_value ?? 0);
        }

        return round($netGross, 2);
    }

    /**
     * Return the value of salary-scale heads whose labels match the requested
     * keywords. This lets the bulk grid expose the Excel salary columns without
     * adding extra queries on the frontend.
     */
    private function scaleComponentAmount($scaleId, array $includeKeywords, array $excludeKeywords = [])
    {
        if (empty($scaleId)) {
            return 0;
        }

        $user = \Auth::user();
        $query = EmployeeScale::with('employeeScaleHeads.SalaryHeads')
            ->where('id', $scaleId)
            ->where('created_by', $user->creatorId());

        $scale = $query->first();
        if (!$scale) {
            return 0;
        }

        $total = 0;
        foreach ($scale->employeeScaleHeads as $scaleHead) {
            $salaryHead = $scaleHead->SalaryHeads ?? null;
            $label = strtolower(trim((string) (
                $salaryHead->head
                ?? $salaryHead->name
                ?? $salaryHead->salary_head
                ?? $salaryHead->head_name
                ?? $salaryHead->title
                ?? ''
            )));

            if ($label === '') {
                continue;
            }

            $matchesInclude = false;
            foreach ($includeKeywords as $keyword) {
                if ($keyword !== '' && str_contains($label, strtolower($keyword))) {
                    $matchesInclude = true;
                    break;
                }
            }

            if (!$matchesInclude) {
                continue;
            }

            $matchesExclude = false;
            foreach ($excludeKeywords as $keyword) {
                if ($keyword !== '' && str_contains($label, strtolower($keyword))) {
                    $matchesExclude = true;
                    break;
                }
            }

            if (!$matchesExclude) {
                $total += (float) ($scaleHead->head_value ?? 0);
            }
        }

        return round($total, 2);
    }

    private function scaleLabel($scaleId)
    {
        if (empty($scaleId)) {
            return '-';
        }

        $user = \Auth::user();
        $query = EmployeeScale::where('id', $scaleId)
            ->where('created_by', $user->creatorId());

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

    private function employeeStatutoryContributions($employee): array
    {
        $branchSchool = \App\Models\SchoolDetails::where('branch_id', $employee->owned_by)->first();

        $eobiBase = (float) ($branchSchool->eobi_values ?? 0);
        $pessiBase = (float) ($branchSchool->pessi_values ?? 0);

        $eobiEmployee = ((float) ($employee->eobi ?? 0) / 100) * $eobiBase;
        $eobiEmployer = ((float) ($employee->eobi_employer ?? 0) / 100) * $eobiBase;
        $pessiEmployee = ((float) ($employee->pessi ?? 0) / 100) * $pessiBase;
        $pessiEmployer = ((float) ($employee->pessi_employer ?? 0) / 100) * $pessiBase;

        return [
            'eobi_employee' => round($eobiEmployee, 2),
            'eobi_employer' => round($eobiEmployer, 2),
            'pessi_employee' => round($pessiEmployee, 2),
            'pessi_employer' => round($pessiEmployer, 2),
            'employee_total' => round($eobiEmployee + $pessiEmployee, 2),
            'employer_total' => round($eobiEmployer + $pessiEmployer, 2),
        ];
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

        // EOBI/PESSI are employee/branch based, not pay-scale-head based.
        $statutory = $this->employeeStatutoryContributions($employee);

        // Previous/current salary detail.
        $latestProposal = $this->employeeLatestProposal($employee);
        $currentGross = $this->scaleGrossAmount($currentScaleId);
        $currentEmpSecurity = $this->scaleComponentAmount($currentScaleId, ['security']);
        $currentTax = $latestProposal
            ? (float) ($latestProposal->income_tax ?? 0)
            : $this->taxAmountForEmployee($employee->id, $currentScaleId);
        $currentEobiEmp = $statutory['employee_total'];
        $currentEmployerContribution = $statutory['employer_total'];
        $calculatedCurrentNet = round($currentGross - $currentEmpSecurity - $currentTax - $currentEobiEmp, 2);
        $currentNetSalary = $latestProposal && $latestProposal->net_salary !== null
            ? (float) $latestProposal->net_salary
            : $calculatedCurrentNet;
        $currentCtc = round($currentGross + $currentEmployerContribution + $childAmount, 2);

        // Revised/new salary detail.
        $newGross = $this->scaleGrossAmount($selectedScaleId);
        $newEmpSecurity = $this->scaleComponentAmount($selectedScaleId, ['security']);
        $newEobiEmp = $statutory['employee_total'];
        $newTax = $this->taxAmountForEmployee($employee->id, $selectedScaleId);
        $newEmployerContribution = $statutory['employer_total'];
        $newNetSalary = round($newGross - $newEmpSecurity - $newEobiEmp - $newTax, 2);
        $newCtc = round($newGross + $newEmployerContribution + $childAmount, 2);
        $grossSalaryDiff = round($newGross - $currentGross, 2);

        $sameScaleDraft = $selectedScaleId
            ? $this->sameScaleActiveProposal($employee, (int) $selectedScaleId)
            : null;

        return [
            'employee_id' => $employee->id,
            'employee_no' => $this->employeeNumber($employee),
            'employee_name' => $this->employeeDisplayName($employee),
            'doj' => $this->formatDateValue($this->employeeDoj($employee)),
            'end_date' => $this->formatDateValue($this->employeeProbationEnd($employee)),
            'service_duration' => $this->serviceDuration($employee),
            'department_name' => optional($employee->department)->name ?? '-',
            'department_id' => $employee->department_id,
            'designation_name' => optional($employee->designation)->name ?? '-',

            'current_scale_id' => $currentScaleId,
            'current_scale_label' => $this->scaleLabel($currentScaleId),
            'current_scale_initial_basic' => $this->scaleComponentAmount($currentScaleId, ['initial basic']),
            'current_gross' => round($currentGross, 2),
            'current_emp_security' => round($currentEmpSecurity, 2),
            'current_tax' => round($currentTax, 2),
            'current_eobi_emp' => round($currentEobiEmp, 2),
            'eobi_employee' => $statutory['eobi_employee'],
            'pessi_employee' => $statutory['pessi_employee'],
            'eobi_employer' => $statutory['eobi_employer'],
            'pessi_employer' => $statutory['pessi_employer'],
            'current_net_salary' => round($currentNetSalary, 2),
            'child_amount' => round($childAmount, 2),
            'current_employer_contribution' => round($currentEmployerContribution, 2),
            'current_ctc' => round($currentCtc, 2),

            'selected_scale_id' => $selectedScaleId,
            'selected_scale_label' => $this->scaleLabel($selectedScaleId),
            'selected_scale_initial_basic' => $this->scaleComponentAmount($selectedScaleId, ['initial basic']),
            'new_gross' => round($newGross, 2),
            'new_emp_security' => round($newEmpSecurity, 2),
            'new_eobi_emp' => round($newEobiEmp, 2),
            'new_tax' => round($newTax, 2),
            'new_net_salary' => round($newNetSalary, 2),
            'new_employer_contribution' => round($newEmployerContribution, 2),
            'child_count' => $childCount,
            'new_child_amount' => round($childAmount, 2),
            'new_ctc' => round($newCtc, 2),
            'gross_salary_diff' => round($grossSalaryDiff, 2),
            'same_scale_draft_exists' => (bool) $sameScaleDraft,
            'same_scale_draft_id' => $sameScaleDraft ? $sameScaleDraft->id : null,
            'same_scale_draft_message' => $sameScaleDraft
                ? 'Already has an active proposal for the same payscale. ' . ((int) $sameScaleDraft->status === 0 ? 'Draft' : 'Pending Approval') . ' #' . $sameScaleDraft->id
                : null,
        ];
    }

    /**
     * Return an existing active proposal for this employee + target payscale.
     * Active means Draft (0) or Pending Approval (1), regardless of whether
     * the proposal was created from Bulk or Single creation.
     *
     * When $lock is true this is called inside the create transaction and
     * locks the matching row, helping prevent rapid duplicate submissions.
     */
    private function sameScaleActiveProposal($employee, $scaleId, $lock = false)
    {
        if (!$employee || empty($scaleId)) {
            return null;
        }

        $employeeNo = $this->employeeNumber($employee);
        if (empty($employeeNo)) {
            return null;
        }

        $query = SalaryProposal::where('emp_no', $employeeNo)
            ->where('payscale', $scaleId)
            ->whereIn('status', [0, 1])
            ->orderByDesc('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function buildProposalSnapshots($employee, array $payload, $sessionId = null, $effectFrom = null): array
    {
        $latestDetail = $employee->employee_payscale_details->first();

        $baseDetail = [
            'employee_id' => $employee->id,
            'contract_id' => $latestDetail->contract_id ?? null,
            'appletter' => $latestDetail->appletter ?? null,
            'paymode' => $latestDetail->paymode ?? '',
            'account_number' => $latestDetail->account_number ?? '',
            'account_id' => $latestDetail->account_id ?? null,
            'department_id' => $employee->department_id,
            'effect_from' => $latestDetail->effect_from ?? null,
            'working_days' => $latestDetail->working_days ?? 30,
            'drns' => $latestDetail->drns ?? 0,
            'conv' => $latestDetail->conv ?? 0,
            'misc' => $latestDetail->misc ?? 0,
            'other_add' => $latestDetail->other_add ?? 0,
            'chaild_concession' => $latestDetail->chaild_concession ?? 0,
            'security_receive_account' => $latestDetail->security_receive_account ?? null,
            'tax_payable_account' => $latestDetail->tax_payable_account ?? null,
            'eobi_payable_account' => $latestDetail->eobi_payable_account ?? null,
            'pessi_payable_account' => $latestDetail->pessi_payable_account ?? null,
            'other_deduction' => $latestDetail->other_deduction ?? 0,
            'other_dedu_payable_account' => $latestDetail->other_dedu_payable_account ?? null,
            'advance' => $latestDetail->advance ?? 0,
            'advance_payable_account' => $latestDetail->advance_payable_account ?? null,
            'net_payable_account' => $latestDetail->net_payable_account ?? null,
        ];

        $common = [
            'session_id' => $sessionId,
            'employee_id' => $payload['employee_id'],
            'employee_no' => $payload['employee_no'],
            'employee_name' => $payload['employee_name'],
            'doj' => $payload['doj'],
            'end_date' => $payload['end_date'],
            'service_duration' => $payload['service_duration'],
            'department_name' => $payload['department_name'],
            'designation_name' => $payload['designation_name'],
            'child_count' => $payload['child_count'],
            'eobi_employee' => $payload['eobi_employee'],
            'pessi_employee' => $payload['pessi_employee'],
            'eobi_employer' => $payload['eobi_employer'],
            'pessi_employer' => $payload['pessi_employer'],
        ];

        $previous = array_merge($common, [
            'scale_id' => $payload['current_scale_id'],
            'scale_label' => $payload['current_scale_label'],
            'gross' => $payload['current_gross'],
            'emp_security' => $payload['current_emp_security'],
            'tax' => $payload['current_tax'],
            'eobi_pessi_employee' => $payload['current_eobi_emp'],
            'net_salary' => $payload['current_net_salary'],
            'child_concession' => $payload['child_amount'],
            'employer_contribution' => $payload['current_employer_contribution'],
            'cost_to_company' => $payload['current_ctc'],
            'salary_detail' => array_merge($baseDetail, [
                'pay_scale_id' => $payload['current_scale_id'],
                'emp_sec' => $payload['current_emp_security'],
                'itax' => $payload['current_tax'],
                'eobi' => $payload['eobi_employee'],
                'eobi_employer' => $payload['eobi_employer'],
                'pessi' => $payload['pessi_employee'],
                'pessi_employer' => $payload['pessi_employer'],
                'net' => $payload['current_net_salary'],
            ]),
        ]);

        $new = array_merge($common, [
            'scale_id' => $payload['selected_scale_id'],
            'scale_label' => $payload['selected_scale_label'],
            'gross' => $payload['new_gross'],
            'emp_security' => $payload['new_emp_security'],
            'tax' => $payload['new_tax'],
            'eobi_pessi_employee' => $payload['new_eobi_emp'],
            'net_salary' => $payload['new_net_salary'],
            'child_concession' => $payload['new_child_amount'],
            'employer_contribution' => $payload['new_employer_contribution'],
            'cost_to_company' => $payload['new_ctc'],
            'gross_salary_diff' => $payload['gross_salary_diff'],
            'effect_from' => $effectFrom,
            'salary_detail' => array_merge($baseDetail, [
                'pay_scale_id' => $payload['selected_scale_id'],
                'effect_from' => $effectFrom,
                'emp_sec' => $payload['new_emp_security'],
                'itax' => $payload['new_tax'],
                'eobi' => $payload['eobi_employee'],
                'eobi_employer' => $payload['eobi_employer'],
                'pessi' => $payload['pessi_employee'],
                'pessi_employer' => $payload['pessi_employer'],
                'net' => $payload['new_net_salary'],
                'chaild_concession' => $payload['new_child_amount'],
            ]),
        ]);

        return [$previous, $new];
    }

    private function resolveSingleProposalAccountMappings($employee, $latestDetail = null): array
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();

        // Branch-side single creation always prefers the configured Bank account 17503.
        $defaultBank = BankAccount::where('created_by', $creatorId)
            ->where(function ($query) {
                $query->where('id', 17503)
                    ->orWhere('account_number', '17503');
            })
            ->first();

        $accountId = $user->type === 'branch'
            ? ($defaultBank->id ?? ($latestDetail->account_id ?? null))
            : ($latestDetail->account_id ?? ($defaultBank->id ?? null));

        $labels = [
            'eobi_payable_account' => 'EOBI Payable (Employee)',
            'pessi_payable_account' => 'Employer PESSI Payable',
            'security_receive_account' => 'Employee Security Payable',
            'other_dedu_payable_account' => 'Other Deduction Payable',
            'advance_payable_account' => 'Employee Salary Advance',
            'net_payable_account' => 'Net Salary Payable',
        ];

        $resolved = ['account_id' => $accountId];

        foreach ($labels as $field => $label) {
            $existing = $latestDetail->{$field} ?? null;
            if (!empty($existing)) {
                $resolved[$field] = $existing;
                continue;
            }

            $resolved[$field] = optional(
                ChartOfAccount::where('created_by', $creatorId)
                    ->where('name', $label)
                    ->first()
            )->id;
        }

        $resolved['tax_payable_account'] = $latestDetail->tax_payable_account ?? optional(
            ChartOfAccount::where('created_by', $creatorId)
                ->where(function ($query) {
                    $query->where('name', 'I.Tax Payable Account')
                        ->orWhere('name', 'Income Tax Payable')
                        ->orWhere('name', 'Income Tax Payable Account');
                })
                ->first()
        )->id;

        return $resolved;
    }

    private function decodeProposalSnapshot($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function applyApprovedProposal(SalaryProposal $proposal): void
    {
        $snapshot = $this->decodeProposalSnapshot($proposal->new_salarysnapshot);
        $detail = $snapshot['salary_detail'] ?? [];

        if (empty($snapshot['employee_id']) || empty($detail['pay_scale_id'])) {
            throw new \RuntimeException('The proposal salary snapshot is incomplete.');
        }

        $employee = \App\Models\Employee::findOrFail($snapshot['employee_id']);

        $payScale = \App\Models\EmployeeScale::where('id', $detail['pay_scale_id'])
            ->where('department_id', $employee->department_id)
            ->first();

        if (!$payScale) {
            throw new \RuntimeException('Employee department and proposed pay scale department must be same.');
        }

        $currentDetail = \App\Models\EmployeePayscaleDetail::where('employee_id', $employee->id)
            ->orderByDesc('id')
            ->first();

        $activeContract = \App\Models\EmployeeContract::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->orderByDesc('from_date')
            ->first();

        $contractId = $detail['contract_id']
            ?? optional($activeContract)->id
            ?? optional($currentDetail)->contract_id;

        $appLetterId = $detail['appletter'] ?? optional($currentDetail)->appletter;
        if (empty($appLetterId) && !empty($employee->category)) {
            $appLetterId = optional(
                \App\Models\AppointmentLetter::where('type', strtolower($employee->category))->latest()->first()
            )->id;
        }

        \App\Models\EmployeePayscaleDetail::create([
            'employee_id' => $employee->id,
            'contract_id' => $contractId,
            'appletter' => $appLetterId,
            'paymode' => $detail['paymode'] ?? optional($currentDetail)->paymode ?? '',
            'account_number' => $detail['account_number'] ?? optional($currentDetail)->account_number ?? '',
            'account_id' => $detail['account_id'] ?? optional($currentDetail)->account_id,
            'department_id' => $employee->department_id,
            'pay_scale_id' => $detail['pay_scale_id'],
            'effect_from' => $detail['effect_from'] ?? $snapshot['effect_from'] ?? now()->toDateString(),
            'working_days' => $detail['working_days'] ?? optional($currentDetail)->working_days ?? 30,
            'drns' => $detail['drns'] ?? optional($currentDetail)->drns ?? 0,
            'conv' => $detail['conv'] ?? optional($currentDetail)->conv ?? 0,
            'misc' => $detail['misc'] ?? optional($currentDetail)->misc ?? 0,
            'other_add' => $detail['other_add'] ?? optional($currentDetail)->other_add ?? 0,
            'chaild_concession' => $detail['chaild_concession'] ?? 0,
            'emp_sec' => $detail['emp_sec'] ?? 0,
            'security_receive_account' => $detail['security_receive_account'] ?? optional($currentDetail)->security_receive_account,
            'itax' => $detail['itax'] ?? 0,
            'tax_payable_account' => $detail['tax_payable_account'] ?? optional($currentDetail)->tax_payable_account,
            'eobi' => $detail['eobi'] ?? 0,
            'eobi_employer' => $detail['eobi_employer'] ?? 0,
            'eobi_payable_account' => $detail['eobi_payable_account'] ?? optional($currentDetail)->eobi_payable_account,
            'pessi' => $detail['pessi'] ?? 0,
            'pessi_employer' => $detail['pessi_employer'] ?? 0,
            'pessi_payable_account' => $detail['pessi_payable_account'] ?? optional($currentDetail)->pessi_payable_account,
            'other_deduction' => $detail['other_deduction'] ?? optional($currentDetail)->other_deduction ?? 0,
            'other_dedu_payable_account' => $detail['other_dedu_payable_account'] ?? optional($currentDetail)->other_dedu_payable_account,
            'advance' => $detail['advance'] ?? optional($currentDetail)->advance ?? 0,
            'advance_payable_account' => $detail['advance_payable_account'] ?? optional($currentDetail)->advance_payable_account,
            'net' => round((float) ($detail['net'] ?? $snapshot['net_salary'] ?? $proposal->net_salary ?? 0)),
            'net_payable_account' => $detail['net_payable_account'] ?? optional($currentDetail)->net_payable_account,
        ]);

        $percentages = $snapshot['employee_percentages'] ?? [];
        if (!empty($percentages)) {
            $employee->security = $percentages['security'] ?? $employee->security;
            $employee->eobi = $percentages['eobi'] ?? $employee->eobi;
            $employee->eobi_employer = $percentages['eobi_employer'] ?? $employee->eobi_employer;
            $employee->pessi = $percentages['pessi'] ?? $employee->pessi;
            $employee->pessi_employer = $percentages['pessi_employer'] ?? $employee->pessi_employer;
            $employee->save();
        }

    }

    public function bulkSendForApproval(Request $request)
    {
        if (\Auth::user()->type === 'company') {
            return redirect()->back()->with('error', 'Company users cannot send branch drafts for approval.');
        }

        $request->validate([
            'proposal_ids' => 'required|array|min:1',
            'proposal_ids.*' => 'integer',
        ]);

        $updated = SalaryProposal::whereIn('id', $request->proposal_ids)
            ->where('owned_by', \Auth::user()->ownedId())
            ->whereIn('status', [0, 3])
            ->update(['status' => 1]);

        return redirect()->route('employee-salary-proporal.index')
            ->with(
                $updated ? 'success' : 'error',
                $updated
                    ? $updated . ' salary proposal(s) sent for approval.'
                    : 'No draft salary proposals were selected.'
            );
    }

    public function bulkApprove(Request $request)
    {
        if (\Auth::user()->type !== 'company') {
            return redirect()->back()->with('error', 'Only company users can approve salary proposals.');
        }

        $request->validate([
            'proposal_ids' => 'required|array|min:1',
            'proposal_ids.*' => 'integer',
        ]);

        $approved = 0;
        $failed = [];
        $creatorId = \Auth::user()->creatorId();

        foreach (array_unique($request->proposal_ids) as $proposalId) {
            try {
                \DB::transaction(function () use ($proposalId, $creatorId, &$approved) {
                    $salaryProposal = SalaryProposal::where('id', $proposalId)
                        ->where('created_by', $creatorId)
                        ->where('status', 1)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $snapshot = $this->decodeProposalSnapshot($salaryProposal->new_salarysnapshot);
                    $employeeId = $snapshot['employee_id'] ?? null;
                    if (empty($employeeId)) {
                        throw new \RuntimeException('Employee is missing from the proposal snapshot.');
                    }

                    $employee = \App\Models\Employee::findOrFail($employeeId);
                    $latestDetail = \App\Models\EmployeePayscaleDetail::where('employee_id', $employee->id)
                        ->orderByDesc('id')
                        ->first();

                    $detail = is_array($snapshot['salary_detail'] ?? null)
                        ? $snapshot['salary_detail']
                        : [];

                    // Bulk approval uses the accounting setup already saved in the proposal first.
                    // Any missing mapping is filled automatically from the employee's latest salary
                    // detail or from the configured default accounting heads.
                    $resolved = $this->resolveSingleProposalAccountMappings($employee, $latestDetail);
                    $accountFields = [
                        'account_id',
                        'eobi_payable_account',
                        'pessi_payable_account',
                        'security_receive_account',
                        'tax_payable_account',
                        'other_dedu_payable_account',
                        'advance_payable_account',
                        'net_payable_account',
                    ];

                    foreach ($accountFields as $field) {
                        if (empty($detail[$field])) {
                            $detail[$field] = $resolved[$field] ?? null;
                        }

                        if (empty($detail[$field])) {
                            throw new \RuntimeException('Default accounting mapping is missing for ' . str_replace('_', ' ', $field) . '.');
                        }
                    }

                    // Ensure the automatically resolved mappings still belong to this company.
                    if (!BankAccount::where('created_by', $creatorId)
                        ->where('id', $detail['account_id'])
                        ->exists()) {
                        throw new \RuntimeException('The default Bank account is invalid or not configured.');
                    }

                    $payableFields = array_values(array_filter($accountFields, fn ($field) => $field !== 'account_id'));
                    $payableIds = collect($payableFields)
                        ->map(fn ($field) => (int) $detail[$field])
                        ->unique()
                        ->values();

                    $validPayableIds = ChartOfAccount::where('created_by', $creatorId)
                        ->whereIn('id', $payableIds)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id);

                    if ($validPayableIds->count() !== $payableIds->count()) {
                        throw new \RuntimeException('One or more default payable accounts are invalid.');
                    }

                    $snapshot['salary_detail'] = $detail;
                    $salaryProposal->new_salarysnapshot = json_encode($snapshot);
                    $salaryProposal->save();

                    $this->applyApprovedProposal($salaryProposal);
                    $salaryProposal->status = 2;
                    $salaryProposal->save();

                    $approved++;
                });
            } catch (\Throwable $e) {
                $failed[] = '#' . $proposalId . ': ' . $e->getMessage();
            }
        }

        if ($approved > 0 && empty($failed)) {
            return redirect()->route('employee-salary-proporal.index')
                ->with('success', $approved . ' salary proposal(s) approved successfully.');
        }

        if ($approved > 0) {
            return redirect()->route('employee-salary-proporal.index')
                ->with('success', $approved . ' salary proposal(s) approved successfully.')
                ->with('error', 'Some proposals could not be approved: ' . implode(' | ', $failed));
        }

        return redirect()->route('employee-salary-proporal.index')
            ->with('error', 'No proposal was approved. ' . implode(' | ', $failed));
    }

    public function sendForApproval($id)
    {
        if (\Auth::user()->type === 'company') {
            return redirect()->back()->with('error', 'Company users cannot send proposals for approval.');
        }

        $salaryProposal = SalaryProposal::where('id', $id)
            ->where('owned_by', \Auth::user()->ownedId())
            ->whereIn('status', [0, 3])
            ->firstOrFail();

        $salaryProposal->status = 1;
        $salaryProposal->save();

        return redirect()->route('employee-salary-proporal.index')
            ->with('success', 'Salary Proposal sent for approval successfully.');
    }

    public function approve(Request $request, $id)
    {
        if (\Auth::user()->type !== 'company') {
            return redirect()->back()->with('error', 'Only company users can approve salary proposals.');
        }

        try {
            \DB::transaction(function () use ($id, $request) {
                $salaryProposal = SalaryProposal::where('id', $id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $snapshot = $this->decodeProposalSnapshot($salaryProposal->new_salarysnapshot);

                // Both Single and Bulk proposals are approved from the shared Show modal.
                // Accounting mappings are mandatory for every pending proposal before approval.
                $validator = Validator::make($request->all(), [
                    'account_id' => 'required|integer',
                    'eobi_payable_account' => 'required|integer',
                    'pessi_payable_account' => 'required|integer',
                    'security_receive_account' => 'required|integer',
                    'tax_payable_account' => 'required|integer',
                    'other_dedu_payable_account' => 'required|integer',
                    'advance_payable_account' => 'required|integer',
                    'net_payable_account' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    throw new \RuntimeException($validator->errors()->first());
                }

                $creatorId = \Auth::user()->creatorId();
                $bankValid = BankAccount::where('created_by', $creatorId)
                    ->where('id', $request->account_id)
                    ->exists();
                if (!$bankValid) {
                    throw new \RuntimeException('Please select a valid Bank account.');
                }

                $accountFields = [
                    'eobi_payable_account',
                    'pessi_payable_account',
                    'security_receive_account',
                    'tax_payable_account',
                    'other_dedu_payable_account',
                    'advance_payable_account',
                    'net_payable_account',
                ];

                $requestedAccountIds = collect($accountFields)
                    ->map(fn ($field) => (int) $request->input($field))
                    ->unique()
                    ->values();

                $validAccountIds = ChartOfAccount::where('created_by', $creatorId)
                    ->whereIn('id', $requestedAccountIds)
                    ->pluck('id')
                    ->map(fn ($accountId) => (int) $accountId);

                if ($validAccountIds->count() !== $requestedAccountIds->count()) {
                    throw new \RuntimeException('One or more selected payable accounts are invalid.');
                }

                $detail = is_array($snapshot['salary_detail'] ?? null)
                    ? $snapshot['salary_detail']
                    : [];

                // Bulk snapshots already carry pay_scale_id/effect_from and salary values in salary_detail.
                // Only accounting mappings are confirmed here.
                $detail['account_id'] = (int) $request->account_id;
                foreach ($accountFields as $field) {
                    $detail[$field] = (int) $request->input($field);
                }

                $snapshot['salary_detail'] = $detail;
                $salaryProposal->new_salarysnapshot = json_encode($snapshot);
                $salaryProposal->save();


                $this->applyApprovedProposal($salaryProposal);
                $salaryProposal->status = 2;
                $salaryProposal->save();
            });

            return redirect()->route('employee-salary-proporal.index')
                ->with('success', 'Salary Proposal approved and applied successfully.');
        } catch (\Exception $e) {
            \Log::error('Salary proposal approval failed', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    public function rollback($id)
    {
        if (\Auth::user()->type !== 'company') {
            return redirect()->back()->with('error', 'Only company users can reject salary proposals.');
        }

        try {
            \DB::transaction(function () use ($id) {
                $salaryProposal = SalaryProposal::where('id', $id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 3 = Rejected / returned to branch for correction.
                // No EmployeePayscaleDetail is created on rejection.
                $salaryProposal->status = 3;
                $salaryProposal->save();
            });

            return redirect()->route('employee-salary-proporal.index')
                ->with('success', 'Salary Proposal rejected and returned to the branch successfully.');
        } catch (\Exception $e) {
            \Log::error('Salary proposal rejection failed', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Reject failed: ' . $e->getMessage());
        }
    }
}
