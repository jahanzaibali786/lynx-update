<?php

namespace App\Http\Controllers;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\EmpFinalSettlementAdjDed;
use App\Models\EmpFinalSettlementHeads;
use App\Models\Employee;
use App\Models\EmployeeChildAdjustment;
use App\Models\EmployeeFinalSettlement;
use App\Models\EmployeeLeaves;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeePayscaleDetail;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Loan;
use App\Models\SalaryHeads;
use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $emplsetlement = EmployeeFinalSettlement::with('employee')->where('created_by', \Auth::user()->creatorId())->get();
        } else {
            $emplsetlement = EmployeeFinalSettlement::with('employee')->where('owned_by', \Auth::user()->ownedId())->get();
        }
        return view('employee.finalsettlement.index', compact('emplsetlement'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id = null)
    {
        if (\Auth::user()->type == 'company') {
            $employee = Employee::with('designation', 'department', 'resignation', 'employee_payscale_details', 'employee_payscale_details.scale', 'employee_payscale_details.scale.employeeScaleHeads')->where('created_by', \Auth::user()->creatorId())->where('id', $request->id)->first();
            $allchartOfAccounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $salaryHeads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
        } else {

            $employee = Employee::with('designation', 'department', 'resignation', 'employee_payscale_details', 'employee_payscale_details.scale', 'employee_payscale_details.scale.employeeScaleHeads')->where('owned_by', \Auth::user()->ownedId())->where('id', $request->id)->first();
            $allchartOfAccounts = ChartOfAccount::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
            $salaryHeads = SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
        }
        $payscale = $employee->employee_payscale_details->last();
        $leavescalc = EmployeeLeaves::where('employee_id', $request->id)->first();
        if (!$leavescalc) {
            return redirect()->back()->with('error', 'Employee Leaves not found!');
        }
        if (!$payscale) {
            return redirect()->back()->with('error', 'Pay Scale Not attached .');
        }
        $initialBasicsHead = null;
        $inititaSalary = 0;
        foreach ($salaryHeads as $head) {
            if (strtolower($head->head) == 'initial basic') {
                $initialBasicsHead = $head;
                break;
            }
        }
        if ($initialBasicsHead) {
            $initialBasicsHeadId = $initialBasicsHead->id;
            $matchingHead = null;
            foreach ($payscale->scale->employeeScaleHeads as $employeeHead) {
                if ($employeeHead->head == $initialBasicsHeadId) {

                    $matchingHead = $employeeHead;
                    break;
                }
            }
            if ($matchingHead) {
                $inititaSalary = $matchingHead->head_value;
            }
        }
        $total_sec = 0;
        $total_extralvs = 0;
        $employee_pay = EmployeeMonthlySalary::with([
            'employee',
            'salary_heads',
        ])->where('employee_id', $id)->where('status', 'paid')->where('created_by', '=', \Auth::user()->creatorId())->get();
        $employee = Employee::with('department')->where('id', $id)->first();
        $eligibleSec = true;
        $eligibleSec = $this->isEligibleForSecurity($employee);
        $total_sec = $employee_pay->sum('emp_sec') ?? 0; //dd($total_sec);

        $loanAmount = 0;
        $loans = Loan::where('employee_id', $employee->id)->get();
        if (!empty($loans)) {
            foreach ($loans as $loan) {
                if (($loan->amount) > ($loan->received_amount)) {
                    $loanAmount += (($loan->amount) - ($loan->received_amount));
                }
            }
        }
        $notpaidsalaries = 0;
        $unpaid = EmployeeMonthlySalary::where('employee_id', $employee->id)
            ->whereMonth('salary_date', '<=', date('m', strtotime($employee->resignation->last_attendance_date)))->where('status', 'unpaid')->get();
        $notpaidsalaries = $unpaid->sum('net_pay');
        if (strtolower($employee->department->name) == 'academic') {
            $total_annual = 0;
        } else {
            $total_annual = 40;
        }
        //employee alows to take leaves before last attendance date
        $lastAttendanceDate = optional($employee->resignation)->last_attendance_date;
        if ($lastAttendanceDate) {
            $empResignMonth = (int) Carbon::parse($lastAttendanceDate)->format('m');
            $diffMonths = $empResignMonth - 1;

            $empTakeAnnual = $diffMonths * 2.5;
            $empTakeCasual = $diffMonths * 0.833;

            // Casual
            $casualUsed = $leavescalc->casual_consumed;
            $extraCasual = max(0, $casualUsed - $empTakeCasual);
            $lessCasual = max(0, $empTakeCasual - $casualUsed);

            // Annual
            $annualUsed = $leavescalc->annual_consumed;
            $extraAnnual = max(0, $annualUsed - $empTakeAnnual);
            $lessAnnual = max(0, $empTakeAnnual - $annualUsed);

        } else {
            $empTakeAnnual = $empTakeCasual = 0;
            $extraCasual = $lessCasual = $extraAnnual = $lessAnnual = 0;
        }
        $employee_payscale = EmployeePayscaleDetail::where('employee_id', $employee->id)
            ->orderByDesc('id')
            ->first();

        $accounts = [];
        if ($employee_payscale) {
            $accounts = [
                $employee_payscale->security_receive_account ?? null,
                $employee_payscale->net_payable_account ?? null,
            ];
        }
        // dd($accounts,$employee_payscale,$request->all());
        $accounts = array_filter($accounts, fn($value) => !is_null($value));
        $employee_id = $employee->id;
        $emp_child_adjustment = EmployeeChildAdjustment::where('employee_id', $employee_id);
        if ($emp_child_adjustment) {
            $emp_child_adjustment = $emp_child_adjustment->whereIn('account_id', $accounts);
        }
        $emp_child_adjustment = $emp_child_adjustment->get();
        $already_adjusted = $emp_child_adjustment->sum('adjust_amount');
        // dd($emp_child_adjustment);
        $chartOfAccounts = ChartOfAccount::whereIn('id', $accounts)->pluck('name', 'id');
        if ($already_adjusted > 0) {
            $totalAvailable = $total_sec + $notpaidsalaries - $already_adjusted;
        } else {
            $totalAvailable = $total_sec + $notpaidsalaries;
        }

        $remaining_security = $total_sec - ($emp_child_adjustment->where('account_id', $employee_payscale->security_receive_account)->sum('adjust_amount') ?? 0);
        $remaining_unpaid_salaries = $notpaidsalaries - ($emp_child_adjustment->where('account_id', $employee_payscale->net_payable_account)->sum('adjust_amount') ?? 0);
        // dd($remaining_security, $remaining_unpaid_salaries);
        // dd($extraCasual, $lessCasual, $extraAnnual, $lessAnnual);
        return view('employee.finalsettlement.create', compact('employee', 'notpaidsalaries', 'inititaSalary', 'total_sec', 'eligibleSec', 'loanAmount', 'extraCasual', 'lessCasual', 'extraAnnual', 'lessAnnual', 'chartOfAccounts', 'emp_child_adjustment', 'already_adjusted', 'totalAvailable', 'remaining_security', 'remaining_unpaid_salaries', 'allchartOfAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $employee = Employee::with('designation', 'department', 'resignation', 'employee_payscale_details', 'employee_payscale_details.scale', 'employee_payscale_details.scale.employeeScaleHeads')->where('created_by', \Auth::user()->creatorId())->where('id', $request->employee_id)->first();
            $salaryHeads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
        } else {

            $employee = Employee::with('designation', 'department', 'resignation', 'employee_payscale_details', 'employee_payscale_details.scale', 'employee_payscale_details.scale.employeeScaleHeads')->where('owned_by', \Auth::user()->ownedId())->where('id', $request->employee_id)->first();
            $salaryHeads = SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
        }
        $payscale = $employee->employee_payscale_details->last();
        $initialBasicsHead = null;
        $inititaSalary = 0;
        foreach ($salaryHeads as $head) {
            if (strtolower($head->head) === 'initial basics') {
                $initialBasicsHead = $head;
                break;
            }
        }
        if ($initialBasicsHead) {
            $initialBasicsHeadId = $initialBasicsHead->id;
            $matchingHead = null;
            foreach ($payscale->scale->employeeScaleHeads as $employeeHead) {
                if ($employeeHead->head == $initialBasicsHeadId) {

                    $matchingHead = $employeeHead;
                    break;
                }
            }
            if ($matchingHead) {
                $inititaSalary = $matchingHead->head_value;
            }
        }
        //service tenure
        $initialBasicHeadValue = 0;
        $gross = 0;
        $earnedgross = 0;
        $company_doj = \Carbon\Carbon::parse($employee->company_doj);
        $resign_date = \Carbon\Carbon::parse($employee->resignation->last_attendance_date);
        $years = $resign_date->diffInYears($company_doj);
        $months = $resign_date->diffInMonths($company_doj) % 12;
        if ($years > 0) {
            $service_tenure = $years . 'years -' . $months . 'months';
        } else {
            $service_tenure = $months . ' Months';
        }
        $payscale = @$employee->employee_payscale_details->last();
        $today = $resign_date->copy();
        $previous_month_25th = $resign_date->copy()->subMonth()->day(25);
        if ($today->day > 25) {
            $start_date = $today->copy()->day(25);
            $nextMonth = $resign_date->copy()->addMonth();
            $existingslaryforthismonth = \App\Models\EmployeeMonthlySalary::where(
                'employee_id',
                $employee->id,
            )
                ->whereMonth('salary_date', $nextMonth->month)
                ->whereYear('salary_date', $nextMonth->year)
                ->first();
        } else {
            $start_date = $today->copy()->subMonth()->day(25);
            $existingslaryforthismonth = \App\Models\EmployeeMonthlySalary::where('employee_id', $employee->id)
                ->whereMonth('salary_date', $resign_date->month)
                ->whereYear('salary_date', $resign_date->year)
                ->first();
        }
        if ($existingslaryforthismonth) {
            $total_days = 0;
        } else {
            $total_days = $today->diffInDays($start_date);
        }
        $total_days_in_month = $resign_date->daysInMonth;
        $security_amnt = 0;
        if($request->adjustment_labels){
            //get sec lebel
            foreach($request->adjustment_labels as $index => $label){
                if($label == 'Emp. Sec. Refunded to emp as per MD Instruction'){
                    $security_amnt = $request->adjustment[$index];
                }
            }
        }
        DB::beginTransaction();
        try {
            $employee_final_settlement = new EmployeeFinalSettlement();
            $employee_final_settlement->branch_id = $employee->branch_id;
            $employee_final_settlement->emp_id = $request->employee_id;
            $employee_final_settlement->designation_id = $employee->designation->id;
            $employee_final_settlement->doj = $company_doj->format('Y-m-d');
            $employee_final_settlement->tenure = $service_tenure;
            $employee_final_settlement->payscale_no = $payscale->pay_scale_id;
            $employee_final_settlement->basic_sal = $request->gros;
            $employee_final_settlement->security_amnt = $security_amnt;
            $employee_final_settlement->sec_eligibilty = $request->is_eligible_security;
            $employee_final_settlement->payable = $request->total_payable;
            $employee_final_settlement->working_days = $total_days;
            $employee_final_settlement->owned_by = $employee->owned_by;
            $employee_final_settlement->created_by = \Auth::user()->creatorId();
            $employee_final_settlement->save();

            if ($employee_final_settlement) {
                //heads total and earned heads for total working day's of last month
                foreach ($payscale->scale->employeeScaleHeads as $salhead) {
                    $gross += $salhead->head_value;
                    $percentagevalue = ($salhead->head_value * $total_days) / $total_days_in_month;
                    $earnedgross += $percentagevalue;
                    //
                    $empsettlementheads = new EmpFinalSettlementHeads();
                    $empsettlementheads->employee_id = $request->employee_id;
                    $empsettlementheads->final_settlement_id = $employee_final_settlement->id;
                    $empsettlementheads->scale_no = $payscale->scale->scale_no;
                    $empsettlementheads->head_id = $salhead->head;
                    $empsettlementheads->head_value = $salhead->head_value;
                    $empsettlementheads->earned_value = $percentagevalue;
                    $empsettlementheads->save();
                    // dd($empsettlementheads);
                }
            }
            if ($employee_final_settlement && $empsettlementheads) {
                foreach ($request->adjustment_labels as $index => $label) {
                    EmpFinalSettlementAdjDed::create([
                        'final_settlement_id' => $employee_final_settlement->id,
                        'key' => $label,
                        'value' => $request->adjustment[$index],
                        'type' => 'adjustment'
                    ]);
                }
                foreach ($request->deduction_labels as $index => $label) {
                    EmpFinalSettlementAdjDed::create([
                        'final_settlement_id' => $employee_final_settlement->id,
                        'key' => $label,
                        'value' => $request->deduction[$index],
                        'type' => 'deduction'
                    ]);
                }
            }
            if ($request->credit && $request->debit) {
                $latest = JournalEntry::where('owned_by', '=', $employee->owned_by)->where('voucher_type', 'BPV')->orderby('id', 'desc')->first();
                if (!$latest) {
                    $latest = 1;
                } else {
                    $latest = $latest->journal_id + 1;
                }
                $journal = new JournalEntry();
                $journal->journal_id = $latest;
                $journal->date = date('Y-m-d');
                $journal->reference = 'Final Settlement';
                $journal->description = 'Final Settlement for ' . $employee->name;
                $journal->voucher_type = 'BPV';
                $journal->reference_id = $employee_final_settlement->id;
                $journal->category = 'Final Settlement';
                $journal->user_id = $employee->id;
                $journal->user_type = 'employee';
                $journal->owned_by = $employee->owned_by;
                $journal->created_by = \Auth::user()->creatorId();
                $journal->save();
                foreach ($request->chart_of_accounts as $index => $chaccount) {
                    if ($request->credit[$index] != 0 || $request->debit[$index] != 0) {
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journal->id;
                        $journalItem->account = $chaccount;
                        $journalItem->head = null;
                        $journalItem->description = $request->memo[$index];
                        $journalItem->entry_id = $employee_final_settlement->id;
                        $journalItem->types = 'Final Settlement';
                        $journalItem->credit = $request->credit[$index] ?? 0;
                        $journalItem->debit = $request->debit[$index] ?? 0;
                        $journalItem->branch_id = $employee->owned_by;
                        $journalItem->save();
                    } else {
                        continue;
                    }
                }
            }
            DB::commit();
            return redirect()->route('resignation.index')->with('success', __('Employee Final Settlement  successfully created.'));
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
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
    public function edit($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $emplsetlement = EmployeeFinalSettlement::with('employee', 'finalsettlementHeads', 'final_set_adj_ded')->where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
            $salaryHeads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            $allchartOfAccounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        } else {
            $emplsetlement = EmployeeFinalSettlement::with('employee', 'finalsettlementHeads', 'final_set_adj_ded')->where('id', $id)->where('owned_by', \Auth::user()->ownedId())->first();
            $salaryHeads = SalaryHeads::where('owned_by', \Auth::user()->ownedId())->get();
            $allchartOfAccounts = ChartOfAccount::where('owned_by', \Auth::user()->ownedId())->pluck('name', 'id');
        }
        $payscale = $emplsetlement->employee->employee_payscale_details->last();
        $leavescalc = EmployeeLeaves::where('employee_id', $emplsetlement->emp_id)->first();
        if (!$leavescalc) {
            return redirect()->back()->with('error', 'Employee Leaves not found!');
        }
        $total_sec = 0;
        $employee_pay = EmployeeMonthlySalary::with([
            'employee',
            'salary_heads',
        ])->where('employee_id', $emplsetlement->emp_id)->where('status', 'paid')->where('created_by', '=', \Auth::user()->creatorId())->get();
        $inititaSalary = 0;
        $initialBasicsHead = null;
        foreach ($salaryHeads as $head) {
            if (strtolower($head->head) == 'initial basic') {
                $initialBasicsHead = $head;
                break;
            }
        }

        if ($initialBasicsHead) {
            $initialBasicsHeadId = $initialBasicsHead->id;
            $matchingHead = null;
            foreach ($payscale->scale->employeeScaleHeads as $employeeHead) {
                if ($employeeHead->head == $initialBasicsHeadId) {

                    $matchingHead = $employeeHead;
                    break;
                }
            }
            if ($matchingHead) {
                $inititaSalary = $matchingHead->head_value;
            }
        }
        $employee = Employee::with('department')->where('id', $emplsetlement->emp_id)->first();
        $eligibleSec = true;
        $eligibleSec = $this->isEligibleForSecurity($employee);
        $total_sec = $employee_pay->sum('emp_sec');
        if (strtolower($employee->department->name) == 'academic') {
            $total_annual = 0;
        } else {
            $total_annual = 40;
        }
        //employee alows to take leaves before last attendance date
        $lastAttendanceDate = optional($employee->resignation)->last_attendance_date;
        if ($lastAttendanceDate) {
            $empResignMonth = (int) Carbon::parse($lastAttendanceDate)->format('m');
            $diffMonths = $empResignMonth - 1;

            $empTakeAnnual = $diffMonths * 2.5;
            $empTakeCasual = $diffMonths * 0.833;

            // Casual
            $casualUsed = $leavescalc->casual_consumed;
            $extraCasual = max(0, $casualUsed - $empTakeCasual);
            $lessCasual = max(0, $empTakeCasual - $casualUsed);

            // Annual
            $annualUsed = $leavescalc->annual_consumed;
            $extraAnnual = max(0, $annualUsed - $empTakeAnnual);
            $lessAnnual = max(0, $empTakeAnnual - $annualUsed);

        } else {
            $empTakeAnnual = $empTakeCasual = 0;
            $extraCasual = $lessCasual = $extraAnnual = $lessAnnual = 0;
        }
        $notpaidsalaries = 0;
        $unpaid = EmployeeMonthlySalary::where('employee_id', $employee->id)
            ->whereMonth('salary_date', '<=', date('m', strtotime($employee->resignation->last_attendance_date)))->where('status', 'unpaid')->get();
        $notpaidsalaries = $unpaid->sum('net_pay');
        $employee_payscale = EmployeePayscaleDetail::where('employee_id', $employee->id)
            ->orderByDesc('id')
            ->first();

        $accounts = [];
        if ($employee_payscale) {
            $accounts = [
                $employee_payscale->security_receive_account ?? null,
                $employee_payscale->net_payable_account ?? null,
            ];
        }
        // dd($accounts,$employee_payscale,$request->all());
        $accounts = array_filter($accounts, fn($value) => !is_null($value));
        $employee_id = $employee->id;
        $emp_child_adjustment = EmployeeChildAdjustment::where('employee_id', $employee_id);
        if ($emp_child_adjustment) {
            $emp_child_adjustment = $emp_child_adjustment->whereIn('account_id', $accounts);
        }
        $emp_child_adjustment = $emp_child_adjustment->get();
        $already_adjusted = $emp_child_adjustment->sum('adjust_amount');
        // dd($emp_child_adjustment);
        $chartOfAccounts = ChartOfAccount::whereIn('id', $accounts)->pluck('name', 'id');
        if ($already_adjusted > 0) {
            $totalAvailable = $total_sec + $notpaidsalaries - $already_adjusted;
        } else {
            $totalAvailable = $total_sec + $notpaidsalaries;
        }

        $remaining_security = $total_sec - ($emp_child_adjustment->where('account_id', $employee_payscale->security_receive_account)->sum('adjust_amount') ?? 0);
        $remaining_unpaid_salaries = $notpaidsalaries - ($emp_child_adjustment->where('account_id', $employee_payscale->net_payable_account)->sum('adjust_amount') ?? 0);

        //payment vouchers 
        $paymentVouchers = JournalEntry::with('accounts')->where('category', 'Final Settlement')->where('voucher_type','BPV')->orwhere('voucher_type','CPV')->where('reference_id', $emplsetlement->id)->get();

        return view('employee.finalsettlement.edit', compact('emplsetlement', 'total_sec', 'inititaSalary', 'total_annual', 'empTakeAnnual', 'empTakeCasual', 'extraCasual', 'lessCasual', 'extraAnnual', 'lessAnnual', 'chartOfAccounts', 'emp_child_adjustment', 'already_adjusted', 'totalAvailable', 'remaining_security', 'remaining_unpaid_salaries', 'eligibleSec', 'notpaidsalaries','allchartOfAccounts','paymentVouchers'));
    }
    public function isEligibleForSecurity(Employee $employee): bool
    {
        $resignDate = Carbon::parse(optional($employee->resignation)->last_attendance_date);
        $joiningDate = Carbon::parse($employee->company_doj);
        $probationEnd = Carbon::parse($employee->probation_end);

        // 1) Still employed
        if (!$employee->resignation || !$resignDate) {
            return true;
        }

        // 2) Resigned during probation
        if ($resignDate->lte($probationEnd)) {
            return false;
        }

        // 3) August join, resigned May/June
        if (
            $joiningDate->format('m') == '08' &&
            in_array($resignDate->format('m'), ['05', '06'], true)
        ) {
            return true;
        }

        // 4) Resigned in May, not full notice
        if ($resignDate->format('m') == '05' && $resignDate->format('d') < 31) {
            return false;
        }

        // 5) Resigned before May
        if ((int) $resignDate->format('m') < 5) {
            return false;
        }

        // 6) Resigned on May 31
        if (
            $resignDate->format('m') == '05' &&
            $resignDate->format('d') == '31'
        ) {
            return true;
        }

        // 7) Everything else
        return false;
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
        // dd($request->all());
        $request->validate([
            'adjustment_labels' => 'array',
            'adjustment' => 'array',
            'deduction_labels' => 'array',
            'deduction' => 'array',
        ]);
        DB::beginTransaction();
        try {
            $employee_final_settlement = EmployeeFinalSettlement::findOrFail($id);
            EmpFinalSettlementAdjDed::where('final_settlement_id', $id)->delete();
            foreach ($request->adjustment_labels as $index => $label) {
                EmpFinalSettlementAdjDed::create([
                    'final_settlement_id' => $employee_final_settlement->id,
                    'key' => $label,
                    'value' => $request->adjustment[$index],
                    'type' => 'adjustment'
                ]);
            }
            foreach ($request->deduction_labels as $index => $label) {
                EmpFinalSettlementAdjDed::create([
                    'final_settlement_id' => $employee_final_settlement->id,
                    'key' => $label,
                    'value' => $request->deduction[$index],
                    'type' => 'deduction'
                ]);
            }
            DB::commit();
            return redirect()->route('emp-final-settlement.index')->with('success', __('Employee Final Settlement successfully updated.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
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
        //
    }

    public function adjustchallan(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $received_amount = 0;
            $challan = Challans::findOrFail($request->challan_id);
            $journal = new JournalEntry();
            $journal->date = date('Y-m-d');
            $journal->reference = $request->ref;
            $journal->description = $request->ref;
            $journal->reference_id = $challan->id;
            $journal->category = 'Challan Adjustment';
            $journal->voucher_type = 'JV';
            $journal->owned_by = \Auth::user()->ownedId();
            $journal->created_by = \Auth::user()->creatorId();
            $journal->save();
            foreach ($request->heads as $index => $head) {
                $challanhead = ChallanHead::where('id', $head['head_id'])->first();
                $challanhead->paid = $challanhead->paid + $head['adjust_amount'];
                $challanhead->save();
                if ($index == 1) {
                    // dd($challanhead,$index);
                }
                $received_amount += $head['adjust_amount'];
                $head_id = FeeHead::where('id', $challanhead->head_id)->first();
                $account_name = ChartOfAccount::where('id', $head_id->account_id)->first();
                $journalItem = new JournalItem();
                $journalItem->journal = $journal->id;
                $journalItem->account = $account_name->id;
                $journalItem->head = $head['head_id'];
                $journalItem->description = $head['referance'];
                $journalItem->entry_id = $challanhead->id;
                $journalItem->types = 'Challan Adjustment';
                $journalItem->credit = $head['adjust_amount'];
                $journalItem->debit = 0;
                $journalItem->save();
            }
            $account_name = ChartOfAccount::where('id', $request->adjustment_account)->first();
            $journalItem = new JournalItem();
            $journalItem->journal = $journal->id;
            $journalItem->account = $account_name->id;
            $journalItem->head = null;
            $journalItem->description = $request->ref;
            $journalItem->entry_id = null;
            $journalItem->types = 'Challan Adjustment';
            $journalItem->credit = 0;
            $journalItem->debit = $received_amount;
            $journalItem->save();
            $challan->paid_amount = $challan->paid_amount + $received_amount;
            $challan->paid_date = date('Y-m-d');
            if ($challan->paid_amount >= $challan->total_amount) {
                $challan->status = 'paid';
            } else {
                $challan->status = 'partial paid';
            }
            $challan->save();
            $adjustment = new EmployeeChildAdjustment();
            $adjustment->student_id = $challan->student_id;
            $adjustment->employee_id = $request->employee_id;
            $adjustment->challan_id = $challan->id;
            $adjustment->account_id = $request->adjustment_account;
            $adjustment->voucher_id = $journal->id;
            $adjustment->adjust_amount = $received_amount;
            $adjustment->ref = $request->ref;
            $adjustment->type = $account_name->id;
            $adjustment->adj_type = 'challan';
            $adjustment->owned_by = \Auth::user()->ownedId();
            $adjustment->created_by = \Auth::user()->creatorId();
            $adjustment->save();
            DB::commit();
            return redirect()->back()->with('success', __('Challan successfully adjusted.'));
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function rollbackChallanAdjustment(Request $request)
    {
        DB::beginTransaction();
        try {
            $adjustment = EmployeeChildAdjustment::findOrFail($request->id);
            $challan = Challans::findOrFail($adjustment->challan_id);
            $challan->paid_amount = $challan->paid_amount - $adjustment->adjust_amount;
            if ($challan->paid_amount == 0) {
                $challan->status = 'issued';
            } else {
                $challan->status = 'partial paid';
            }
            $challan->save();
            $journal = JournalEntry::findOrFail($adjustment->voucher_id);
            $journalItem = JournalItem::where('journal', $journal->id)->delete();
            $journal->delete();
            $adjustment->delete();
            DB::commit();
            return response()->json(['success' => 'Challan Adjustment rollback successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    public function rollbackPaymentVoucher(Request $request)
    {
        DB::beginTransaction();
        try {
            $journal = JournalEntry::findOrFail($request->id);
            $journalItem = JournalItem::where('journal', $journal->id)->delete();
            $journal->delete();
            DB::commit();
            return response()->json(['success' => 'Payment Voucher rollback successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    public function finalize(Request $request)
    {
        DB::beginTransaction();
        try {
            $employee_final_settlement = EmployeeFinalSettlement::findOrFail($request->id);
            $employee_final_settlement->status = 1;
            $employee_final_settlement->save();
            $employee = Employee::findOrFail($employee_final_settlement->emp_id);
            $employee->is_res_ter =  1;
            $employee->save();
            DB::commit();
            return redirect()->back()->with('success', __('Employee Final Settlement successfully approved.'));
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
