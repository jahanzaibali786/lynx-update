<?php

namespace App\Http\Controllers;

use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySalaryHeads;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeMonthlySalaryAttendance;
use App\Models\EmployeePayscaleDetail;
use App\Models\EmployeeTransfer;
use App\Models\Loan;
use App\Models\SalaryDeductionDetail;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class EmployeeTransferController extends Controller
{
    public function index(Request $request)
    {
        $datefrom = $request->input('datefrom');
        $dateto = $request->input('dateto');
        $type = $request->input('type');
        $status = $request->input('status');
        if (\Auth::user()->can('manage transfer')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = EmployeeTransfer::with('department_from', 'department_to', 'branch_from', 'branch_to')->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                if (Auth::user()->type == 'Employee') {
                    $emp = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    $query = EmployeeTransfer::with('department', 'branch')->where('owned_by', '=', \Auth::user()->ownedId())->where('employee_id', '=', $emp->id);
                } else {
                    $query = EmployeeTransfer::with('department', 'branch')->where('owned_by', '=', \Auth::user()->ownedId());
                }
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            if ($type != '') {
                if ($type == 'transfer_in') {
                    $query = EmployeeTransfer::where('branch_to_id', $request->branches);
                } elseif ($type == 'transfer_out') {
                    $query = EmployeeTransfer::where('branch_from_id', $request->branches);
                }
            }
            if ($datefrom != '' && $dateto != '') {
                $startDate = \Carbon\Carbon::parse($datefrom)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($dateto)->endOfMonth()->format('Y-m-d');
                $query->whereBetween('transfer_date', [$startDate, $endDate]);
            }
            if ($status != '') {
                $query = $query->where('status', $status);
            }
            $transfers = $query->get();

            return view('employee.transfer.index', compact('transfers', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create transfer')) {
            if (Auth::user()->type == 'company') {
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                
                $from_branches = $branches;
                $to_branches = $branches;
                
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $employees->prepend('Select Employee', '');
            } else {
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                
                $from_branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                
                $to_branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $to_branches->prepend('Select Branch', '');
                
                $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $employees->prepend('Select Employee', '');
            }
            return view('employee.transfer.create', compact('designations', 'employees', 'departments', 'from_branches', 'to_branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('create transfer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'branch_to_id' => 'required',
                    'branch_from_id' => 'required',
                    'department_to_id' => 'required',
                    'designation_to_id' => 'required',
                    // 'department_from_id' => 'required',
                    // 'transfer_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $employee = Employee::where('id', $request->employee_id)->first();
            if ($employee) {
                $transfer = new EmployeeTransfer();
                $transfer->employee_id = $request->employee_id;
                $transfer->branch_to_id = $request->branch_to_id;
                $transfer->branch_from_id = $request->branch_from_id;
                $transfer->department_to_id = $request->department_to_id;
                $transfer->department_from_id = $employee->department_id;
                $transfer->designation_from_id = $employee->designation_id;
                $transfer->designation_to_id = $request->designation_to_id;
                $transfer->transfer_date = $request->transfer_date;
                $transfer->transfer_reason = $request->description;
                $transfer->status = 0;
                $transfer->owned_by = \Auth::user()->ownedId();
                $transfer->created_by = \Auth::user()->creatorId();
                $transfer->save();
                return redirect()->route('employee-transfer.index')->with('success', __('Employee Transfer  successfully created.'));
            } else {
                return redirect()->route('employee-transfer.index')->with('error', __('Employee Not Found !'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Transfer $transfer)
    {
        return redirect()->route('employee-transfer.index');
    }

public function print($id)
{
    $transfer = EmployeeTransfer::where('id', $id)->first();
    if(isset($transfer)){
        $html = view('employee.transfer.print', compact('transfer'))->render();
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        // $options->set('defaultFont', 'Arial'); // Optional, but do NOT use 'Arial, sans-serif'
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=\"employee_transfer.pdf\"');
    }else{
        return redirect()->back()->with('error', __('Employee Transfer Not Found !'));
    }
}

    public function edit($id, Request $request)
    {
        if (\Auth::user()->can('edit transfer')) {
            if (Auth::user()->type == 'company') {
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                
                $from_branches = $branches;
                $to_branches = $branches;
                
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $employees->prepend('Select Employee', '');
            } else {
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                
                $from_branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                
                $to_branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $to_branches->prepend('Select Branch', '');
                
                $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $employees->prepend('Select Employee', '');
            }
            $transfer = EmployeeTransfer::where('id', $id)->first();
            return view('employee.transfer.edit', compact('transfer', 'employees', 'departments', 'from_branches', 'to_branches', 'designations'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit transfer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_to_id' => 'required',
                    'branch_from_id' => 'required',
                    'department_to_id' => 'required',
                    'department_from_id' => 'required',
                    'transfer_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $transfer = EmployeeTransfer::find($id);
            $transfer->branch_to_id = $request->branch_to_id;
            $transfer->branch_from_id = $request->branch_from_id;
            $transfer->department_to_id = $request->department_to_id;
            $transfer->department_from_id = $request->department_from_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->transfer_reason = $request->description;
            $transfer->save();
            return redirect()->route('employee-transfer.index')->with('success', __('Transfer successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete transfer')) {
            $transfer = EmployeeTransfer::find($id);
            if ($transfer->created_by == \Auth::user()->creatorId()) {
                $transfer->delete();

                return redirect()->route('employee-transfer.index')->with('success', __('Transfer successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function employeeDetail(Request $request)
    {
        // dd($request->all());
        $employee = Employee::where('owned_by', '=', $request->id)->get();
        $department = Department::where('owned_by', '=', $request->id)->get();
        $designation = Designation::where('owned_by', '=', $request->id)->get();

        if ($request->emp_no) {
            $employee = Employee::with([
                'employee_payscale_details' => function ($query) {
                    $query->latest()->first();
                }
            ])
                ->where('employee_id', $request->emp_no)
                ->first();
            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee Not Found'
                ], 404);
            }
            if ($employee->owned_by != Auth::user()->ownedId()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee is not from this branch'
                ], 404);
            }
            $department = Department::where('id', $employee[0]->department_id)->first();
            $designation = Designation::where('id', $employee[0]->designation_id)->first();
        }

        return response()->json([
            'status' => 'success',
            'employee' => $employee,
            'department' => $department,
            'designation' => $designation,
        ]);
    }


   public function approve($id)
    {
        \DB::beginTransaction();

        try {
            $employee_transfers = EmployeeTransfer::find($id);
            if (!$employee_transfers) {
                return redirect()->back()->with('error', __('Employee transfer record not found.'));
            }
            // Error: Attempt to read property "employee_payscale_details" on null

            $employee_transfers->status = 1;
            $employee_transfers->save();

            $month_days = 30;
            $employee_id = $employee_transfers->employee_id;
            $transfer_date = $employee_transfers->transfer_date;
            $transferDate = Carbon::parse($transfer_date);
            $startOfMonth = $transferDate->copy()->startOfMonth();
            $endOfMonth = $transferDate->copy()->endOfMonth();

            $workingDays = $transferDate->diffInDays($startOfMonth) + 1;
            $employee = Employee::with('employee_payscale_details', 'employee_loan')->where('id', $employee_id)->first();
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }
            $lastPayscaleDetail = $employee->employee_payscale_details->last();
            if (empty($lastPayscaleDetail)) {
                return redirect()->back()->with('error', __('Employee does not have payscale attached.'));
            }

            $payscalesauto = \App\Models\EmployeeScale::with(
                'employeeScaleHeads',
                'employeeScaleHeads.SalaryHeads',
                'employeepayScaledetailHeads'
            )->where('id', $lastPayscaleDetail->pay_scale_id)->first();

            if (!$payscalesauto) {
                return redirect()->back()->with('error', __('Payscale details not found.'));
            }
            //existing salary
            $existingSalary = EmployeeMonthlySalary::where('employee_id', $employee_id)
                ->whereMonth('salary_date', $transferDate->month)
                ->whereYear('salary_date', $transferDate->year)
                ->first();
            if (!$existingSalary) {
                $grossSalary = 0;
                $basicSalary = 0;

                foreach ($payscalesauto->employeeScaleHeads as $scale_head) {
                    if ($scale_head->SalaryHeads->head == 'Initial Basics') {
                        $initialBasicHeadValue = $scale_head->head_value;
                        $basicSalary = ($initialBasicHeadValue / $month_days) * $workingDays;
                    } else {
                        $grossSalary += $scale_head->head_value;
                    }
                }

                $grossSalary += $basicSalary;
                $loanAmount = 0;
                $securityLoanAmount = 0;
                $salaryLoanDeductions = [];
                $transferMonthStart = $transferDate->copy()->startOfMonth();
                $transferMonthEnd = $transferDate->copy()->endOfMonth();
                $salaryLoans = Loan::where('employee_id', $employee_id)
                    ->with('installments')
                    ->where('status', 1)
                    ->whereDate('from_pay_month', '<=', $transferMonthEnd->format('Y-m-d'))
                    ->whereDate('loan_ended', '>=', $transferMonthStart->format('Y-m-d'))
                    ->get();

                foreach ($salaryLoans as $loan) {
                    $loanStartDate = Carbon::parse($loan->from_pay_month)->startOfMonth();
                    $loanEndDate = Carbon::parse($loan->loan_ended)->endOfMonth();
                    if ($loan->status == 1 && $transferMonthStart->between($loanStartDate, $loanEndDate) && !$loan->isStoppedForMonth($transferMonthStart)) {
                        $remainingLoanAmount = max(0, (float) $loan->amount - (float) $loan->received_amount);
                        $installment = $loan->nextPayableInstallment($transferMonthStart);
                        $baseInstallmentAmount = $installment ? (float) $installment->due_amount : (float) $loan->per_month_amount;
                        $installmentAmount = min($baseInstallmentAmount * ($workingDays / $month_days), $remainingLoanAmount);

                        if ($installmentAmount > 0 && $loan->emp_sec == 'security') {
                            $securityLoanAmount += $installmentAmount;
                        } elseif ($installmentAmount > 0) {
                            $loanAmount += $installmentAmount;
                        }

                        if ($installmentAmount > 0) {
                            $salaryLoanDeductions[] = [
                                'loan' => $loan,
                                'installment' => $installment,
                                'amount' => $installmentAmount,
                            ];
                        }
                    }
                }

                $advanceAmount = (float) ($lastPayscaleDetail->advance ?? 0);
                $deduction = $loanAmount + $securityLoanAmount + $advanceAmount + (float) ($lastPayscaleDetail->emp_sec ?? 0) + (float) ($lastPayscaleDetail->pessi ?? 0) + (float) ($lastPayscaleDetail->eobi ?? 0) + (float) ($lastPayscaleDetail->other_deduction ?? 0) + (float) ($lastPayscaleDetail->item ?? 0);
                $netSalary = ($grossSalary - $deduction) > 0 ? $grossSalary - $deduction : 0;

                $employeemonthlysal = EmployeeMonthlySalary::create([
                    'employee_id' => $employee_id,
                    'salary_date' => $transferDate->format('Y-m-d'),
                    'scale_no' => $payscalesauto->scale_no,
                    'sal_days' => $workingDays,
                    'basics' => $basicSalary,
                    'conv' => $lastPayscaleDetail->conv ?? '0',
                    'stop_sal' => 0,
                    'other' => $lastPayscaleDetail->other_deduction ?? '0',
                    'gross' => $grossSalary,
                    'loan' => $loanAmount ?: '0',
                    'emp_sec' => $lastPayscaleDetail->emp_sec ?? '0',
                    'pessi_employer' => $lastPayscaleDetail->pessi_employer ?? '0',
                    'pessi' => $lastPayscaleDetail->pessi ?? '0',
                    'it' => $lastPayscaleDetail->item ?? '0',
                    'eobi' => $lastPayscaleDetail->eobi ?? '0',
                    'eobi_employer' => $lastPayscaleDetail->eobi_employer ?? '0',
                    'dedu' => $lastPayscaleDetail->other_deduction ?? '0',
                    'emp_sec_loan' => $securityLoanAmount ? round($securityLoanAmount) : '0',
                    'tra_course' => 0,
                    'sal_advance' => $advanceAmount ? round($advanceAmount) : '0',
                    'net_pay' => round($netSalary),
                    'prc_final' => 0,
                    'sal_final' => 0,
                    'on_hold' => 0,
                    'owned_by' => $employee->owned_by,
                    'created_by' => Auth::user()->creatorId(),
                ]);

                foreach ($salaryLoanDeductions as $loanDeduction) {
                    $salaryLoan = $loanDeduction['loan'];
                    $loanInstallment = $loanDeduction['installment'];
                    $loanDeductionAmount = round($loanDeduction['amount']);
                    if ($loanDeductionAmount <= 0) {
                        continue;
                    }

                    $deductionDetail = SalaryDeductionDetail::create([
                        'salary_id' => $employeemonthlysal->id,
                        'employee_id' => $employee_id,
                        'type' => 'loan',
                        'sub_type' => $salaryLoan->emp_sec,
                        'reference_id' => $salaryLoan->id,
                        'amount' => $loanDeductionAmount,
                        'note' => 'Loan installment deduction - ' . $salaryLoan->title,
                        'coa_id' => $salaryLoan->chartaccount_id,
                    ]);

                    if ($loanInstallment) {
                        $loanInstallment->paid_amount = min((float) $loanInstallment->amount, (float) $loanInstallment->paid_amount + $loanDeductionAmount);
                        if ((float) $loanInstallment->paid_amount >= (float) $loanInstallment->amount) {
                            $loanInstallment->status = 1;
                            $loanInstallment->paid_at = $employeemonthlysal->salary_date;
                        }
                        $loanInstallment->salary_id = $employeemonthlysal->id;
                        $loanInstallment->salary_deduction_detail_id = $deductionDetail->id;
                        $loanInstallment->save();
                    }

                    $salaryLoan->received_amount = ((float) $salaryLoan->received_amount) + $loanDeductionAmount;
                    $salaryLoan->save();
                }

                foreach ($payscalesauto->employeeScaleHeads as $scale_head) {
                    EmployeeMonthlySalaryHeads::create([
                        'employee_id' => $employee_id,
                        'scale_id' => $payscalesauto->id,
                        'scale_no' => $payscalesauto->scale_no,
                        'sal_id' => $employeemonthlysal->id,
                        'salary_date' => $transferDate->format('Y-m-d'),
                        'head_id' => $scale_head->head,
                        'head_value' => $scale_head->head_value,
                        'owned_by' => Auth::user()->ownedId(),
                        'created_by' => Auth::user()->creatorId(),
                    ]);
                }
                //voucher
                $allAccounts = [];
                $deductionAccounts = $this->salaryDeductionVoucherAccounts($employeemonthlysal, $lastPayscaleDetail);
                // dd($lastPayscaleDetail);
                $allAccounts = [
                    [
                        'account_id' => 216,
                        'name' => 'Salary Expense (Basic + Med + Rent + Sec)',
                        'debit' =>
                            (float) ($grossSalary ?? 0),
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $lastPayscaleDetail->security_receive_account,
                        'name' => 'Employee Security Payable',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->emp_sec,
                    ],
                    // Cr: Income Tax Payable
                    [
                        'account_id' => $lastPayscaleDetail->tax_payable_account,
                        'name' => 'Tax Payable (Income Tax)',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->it,
                    ],
                    // Cr: EOBI Payable (Employee)
                    [
                        'account_id' => $lastPayscaleDetail->eobi_payable_account,
                        'name' => 'EOBI Payable (Employee)',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->eobi,
                    ],
                    // Cr: PASSI Payable (Employee)
                    [
                        'account_id' => $lastPayscaleDetail->pessi_payable_account,
                        'name' => 'PASSI Payable (Employee)',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->pessi,
                    ],
                    [
                        'account_id' => $lastPayscaleDetail->other_dedu_payable_account,
                        'name' => 'Other Deduction Payable',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->dedu ?? 0, // or $all_data[?]
                    ],
                    // Cr: Net Salary Payable
                    [
                        'account_id' => $lastPayscaleDetail->net_payable_account,
                        'name' => 'Net Salary Payable',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->net_pay,
                    ],
                    [
                        'account_id' => 216,
                        'name' => 'Advance Salary',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->sal_advance,
                    ],
                    // Cr: Employer PASSI Payable
                    [
                        // 'type' => 'Liabilities2',
                        // 'sub_type' => 'Payables29',
                        'account_id' => 225,
                        'name' => 'Employer PASSI Payable',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->pessi_employer,
                    ],
                    // Cr: Employer EOBI Payable
                    [
                        // 'type' => 'Liabilities2',
                        // 'sub_type' => 'Payables29',
                        'account_id' => 221,
                        'name' => 'Employer EOBI Payable',
                        'debit' => 0,
                        'credit' => $employeemonthlysal->eobi_employer,
                    ],
                ];
                $allAccounts = array_merge($allAccounts, $deductionAccounts);
                $filteredAccounts = array_filter($allAccounts, function ($item) {
                    return ($item['debit'] ?? 0) > 0 || ($item['credit'] ?? 0) > 0;
                });
                $journal_data = [
                    'date' => $employeemonthlysal->salary_date,
                    'reference' => 'SAL-' . $employeemonthlysal->id,
                    'employee_name' => @$employee->name,
                    'no' => $employeemonthlysal->id,
                    'salary_month' => date('F Y', strtotime($employeemonthlysal->salary_date)),
                    'id' => $employeemonthlysal->id,
                    'category' => 'salary',
                    'user_id' => @$employee->user_id,
                    'user_type' => 'employee',
                    'owned_by' => @$employee->owned_by,
                    'created_by' => @$employee->created_by,
                    'accounts' => array_values($filteredAccounts),
                ];
                $journal = Utility::Salaryjrentryvoucher($journal_data);
                $employeemonthlysal->voucher_id = $journal;
                $employeemonthlysal->save();
            }
            $employee->owned_by = $employee_transfers->branch_to_id;
            $employee->branch_id = $employee_transfers->branch_to_id;
            $employee->department_id = $employee_transfers->department_to_id;
            $employee->save();
            \DB::commit();

            return redirect()->route('employee-transfer.index')->with('success', __('Transfer successfully approved And Salary Generated.'));
        } catch (\Exception $e) {
            dd($e);
            \DB::rollBack();
            return redirect()->route('employee-transfer.index')->with('error', __('Error: ') . $e->getMessage());
        }
    }

    private function salaryDeductionVoucherAccounts($salary, $lastPayscaleDetail): array
    {
        $details = SalaryDeductionDetail::where('salary_id', $salary->id)
            ->whereIn('type', ['loan', 'advance'])
            ->get();

        $accounts = [];
        foreach ($details as $detail) {
            $accountId = $detail->coa_id;
            if (!$accountId && $detail->type == 'loan' && $detail->sub_type == 'security') {
                $accountId = $lastPayscaleDetail->security_receive_account ?? null;
            } elseif (!$accountId && $detail->type == 'loan') {
                $accountId = $lastPayscaleDetail->other_dedu_payable_account ?? null;
            } elseif (!$accountId && $detail->type == 'advance') {
                $accountId = 216;
            }

            if (!$accountId) {
                continue;
            }

            if ($detail->type == 'advance') {
                $name = 'Advance Salary';
            } elseif ($detail->sub_type == 'security') {
                $name = 'Employee Security Payable';
            } else {
                $name = 'Loan Deduction Payable';
            }

            $key = $detail->type . '-' . $detail->sub_type . '-' . $accountId;
            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'account_id' => $accountId,
                    'name' => $name,
                    'debit' => 0,
                    'credit' => 0,
                ];
            }

            $accounts[$key]['credit'] += round($detail->amount);
        }

        return array_values($accounts);
    }

    public function employeedep(Request $request)
    {
        $employee = Employee::with('department', 'designation')->where('id', '=', $request->id)->first();
        $scale = EmployeePayscaleDetail::with('scale')->where('employee_id', '=', $employee->id)->orderBy('id', 'desc')->first();

        $result = [
            'status' => 'success',
            'employee' => $employee,
            'scale' => $scale,
        ];
        return response()->json($result);
    }
}
