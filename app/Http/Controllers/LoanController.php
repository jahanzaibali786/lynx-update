<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeMonthlySalary;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\LoanStopHistory;
use App\Models\SalaryDeductionDetail;
use App\Models\User;
use App\Models\Utility;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LoanController extends Controller
{
    private function normalizeMonthDate($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        return $value;
    }

    private function normalizeLoanMonthDates(Request $request)
    {
        $request->merge([
            'from_pay_month' => $this->normalizeMonthDate($request->input('from_pay_month')),
            'loan_ended' => $this->normalizeMonthDate($request->input('loan_ended')),
            'date' => $this->normalizeMonthDate($request->input('date')),
            'paid_from' => $this->normalizeMonthDate($request->input('paid_from')),
        ]);
    }

    private function hasGeneratedSalaryFromMonth($employeeId, $monthDate)
    {
        $month = \Carbon\Carbon::parse($monthDate)->startOfMonth();

        return EmployeeMonthlySalary::where('employee_id', $employeeId)
            ->whereDate('salary_date', '>=', $month->format('Y-m-d'))
            ->exists();
    }

    private function createLoanVoucher(Loan $loan, BankAccount $bankAccount, $paymentMethod)
    {
        $data['id'] = $loan->id;
        $data['date'] = $loan->approval_date;
        $data['no'] = $loan->id;
        $data['reference'] = $loan->referance_id;
        $data['description'] = $loan->reason;
        $data['prod_id'] = $loan->id;
        $data['amount'] = $loan->amount;
        $data['category'] = 'Loan';
        $data['owned_by'] = $loan->owned_by;
        $data['created_by'] = $loan->created_by;
        $data['account_id'] = $bankAccount->chart_account_id;
        $data['bank_id'] = $bankAccount->id;
        $data['user_id'] = $loan->employee_id;
        $data['user_type'] = 'employee';
        $data['types'] = 'Loan Payment';
        $data['loan_account'] = $loan->chartaccount_id;
        $data['created_at'] = now();

        return $paymentMethod === 'cash'
            ? Utility::cpv_entry($data)
            : Utility::bpv_entry($data);
    }

    private function syncLoanVoucher(Loan $loan)
    {
        if (empty($loan->voucher_id)) {
            return;
        }

        $journal = JournalEntry::where('id', $loan->voucher_id)
            ->where('category', 'Loan')
            ->first();

        if (!$journal) {
            return;
        }

        $bankAccount = BankAccount::find($loan->bank_id);

        $journal->date = $loan->approval_date ?: $journal->date;
        $journal->reference = $loan->referance_id;
        $journal->description = 'Loan id : ' . $loan->id;
        $journal->reference_id = $loan->id;
        $journal->user_id = $loan->employee_id;
        $journal->user_type = 'Employee';
        $journal->owned_by = $loan->owned_by;
        $journal->created_by = $loan->created_by;
        $journal->save();

        $creditLine = JournalItem::where('journal', $journal->id)
            ->where('entry_id', $loan->id)
            ->where('credit', '>', 0)
            ->first();

        if (!$creditLine && $bankAccount) {
            $creditLine = JournalItem::where('journal', $journal->id)
                ->where('account', $bankAccount->chart_account_id)
                ->where('credit', '>', 0)
                ->first();
        }

        if (!$creditLine) {
            $creditLine = new JournalItem();
            $creditLine->journal = $journal->id;
        }

        $creditLine->account = $bankAccount ? $bankAccount->chart_account_id : $creditLine->account;
        $creditLine->description = $loan->reason;
        $creditLine->credit = $loan->amount;
        $creditLine->debit = 0;
        $creditLine->entry_id = $loan->id;
        $creditLine->branch_id = $loan->owned_by;
        $creditLine->save();

        $debitLine = JournalItem::where('journal', $journal->id)
            ->where('account', $loan->chartaccount_id)
            ->where('debit', '>', 0)
            ->first();

        if (!$debitLine) {
            $debitLine = JournalItem::where('journal', $journal->id)
                ->where('debit', '>', 0)
                ->first();
        }

        if (!$debitLine) {
            $debitLine = new JournalItem();
            $debitLine->journal = $journal->id;
        }

        $debitLine->account = $loan->chartaccount_id;
        $debitLine->description = $loan->reason;
        $debitLine->credit = 0;
        $debitLine->debit = $loan->amount;
        $debitLine->branch_id = $loan->owned_by;
        $debitLine->save();
    }

    private function getLoanReceivedAmount(Loan $loan)
    {
        $deductedAmount = SalaryDeductionDetail::loans()
            ->where('reference_id', $loan->id)
            ->sum('amount');

        return max((float) $loan->received_amount, (float) $deductedAmount);
    }

    private function getLoanReceivedInstallments(Loan $loan)
    {
        return SalaryDeductionDetail::loans()
            ->where('reference_id', $loan->id)
            ->where('amount', '>', 0)
            ->count();
    }

    private function getLatestGeneratedSalaryMonth($employeeId)
    {
        $latestSalary = EmployeeMonthlySalary::where('employee_id', $employeeId)
            ->orderBy('salary_date', 'desc')
            ->first();

        return $latestSalary ? \Carbon\Carbon::parse($latestSalary->salary_date)->startOfMonth() : null;
    }

    private function getFiscalYearOptions()
    {
        $currentYear = \Carbon\Carbon::now()->month >= 7
            ? \Carbon\Carbon::now()->year
            : \Carbon\Carbon::now()->year - 1;

        $years = [];
        for ($year = $currentYear + 1; $year >= $currentYear - 7; $year--) {
            $years[$year . '-' . ($year + 1)] = $year . '-' . ($year + 1);
        }

        return collect(['' => __('Select Fiscal Year')])->merge($years);
    }

    private function getFiscalYearDateRange($fiscalYear)
    {
        if (!preg_match('/^(\d{4})-(\d{4})$/', (string) $fiscalYear, $matches)) {
            return null;
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            return null;
        }

        return [
            \Carbon\Carbon::create($startYear, 7, 1)->startOfDay(),
            \Carbon\Carbon::create($endYear, 6, 30)->endOfDay(),
        ];
    }

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = Loan::with(['employee', 'stopHistories', 'approvedBy'])->where('created_by', \Auth::user()->creatorId());
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        } else {
            $query = Loan::with(['employee', 'stopHistories', 'approvedBy'])->where('owned_by', \Auth::user()->ownedId());
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        }

        $employeeQuery = Employee::query()->orderBy('name');
        if (\Auth::user()->type == 'company') {
            $employeeQuery->where('created_by', \Auth::user()->creatorId());
        } else {
            $employeeQuery->where('owned_by', \Auth::user()->ownedId());
        }

        if (!empty($request->branches)) {
            $employeeQuery->where('branch_id', $request->branches);
        }

        $employees = $employeeQuery->get()->pluck('name', 'id');
        $employees->prepend(__('Select Employee'), '');
        $fiscalYears = $this->getFiscalYearOptions();

        if(!empty($request->branches)){
            $query->whereHas('employee', function($query) use ($request) {
                $query->where('branch_id', $request->branches);
            });
        }
        if(!empty($request->department_id)){
            $query->whereHas('employee', function($query) use ($request) {
                $query->where('department_id', $request->department_id);
            });
        }
        if(!empty($request->designation_id)){
            $query->whereHas('employee', function($query) use ($request) {
                $query->where('designation_id', $request->designation_id);
            });
        }
        if($request->filled('status')){
            $query->where('status',$request->status);
        }
        if(!empty($request->employee_id)){
            $query->where('employee_id', $request->employee_id);
        }
        if(!empty($request->fiscal_year)){
            $fiscalRange = $this->getFiscalYearDateRange($request->fiscal_year);
            if ($fiscalRange) {
                $query->whereBetween('from_pay_month', [
                    $fiscalRange[0]->format('Y-m-d'),
                    $fiscalRange[1]->format('Y-m-d'),
                ]);
            }
        }
        if ($request->filled('is_print') && $request->is_print == 1) {
            $loans = $query->get();
            $bodyHtml = view('employee.loan.report', compact('loans', 'branches'))->render();
            $headerHtml = view('employee.report.pdf.header')->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $finalHtml = '
            <html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -60px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
            </style>
            </head>
            <body>
                <div class="header">' . $headerHtml . '</div>
                <div class="footer">' . $footerHtml . '</div>
                ' . $bodyHtml . '
            </body></html>';
            // dd($finalHtml);

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($finalHtml);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('loan_report.pdf', ['Attachment' => false]);
        }
        $loans= $query->orderBy('id', 'desc')->get();
        return view('employee.loan.index', compact('loans','branches','departments','designations', 'employees', 'fiscalYears'));
    }
    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employee = Employee::where('created_by', \Auth::user()->creatorId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');

        } else {

            $employee = Employee::where('owned_by', \Auth::user()->ownedId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        return view('employee.loan.create', compact('employee', 'branches'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('create loan')) {
            \DB::beginTransaction();
            try {
                $this->normalizeLoanMonthDates($request);

                $validator = \Validator::make(
                    $request->all(),
                    [
                    'branches' => 'required|integer',
                    'employee_id' => 'required|integer',
                    'title' => 'required|string',
                    'amount' => 'required|numeric',
                    'from_pay_month' => 'required|date',
                    'pay_period' => 'required|integer',
                    'loan_ended' => 'required|date',
                    'reason' => 'required',
                ]);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    \DB::rollback();
                    return redirect()->back()->with('error', $messages->first());
                }

                $latestGeneratedSalaryMonth = $this->getLatestGeneratedSalaryMonth($request->input('employee_id'));
                $fromPayMonth = \Carbon\Carbon::parse($request->input('from_pay_month'))->startOfMonth();

                if ($latestGeneratedSalaryMonth && $fromPayMonth->lte($latestGeneratedSalaryMonth)) {
                    \DB::rollback();
                    return redirect()->back()->with('error', __('From paid month salary already generated. Please select next month.'));
                }

                $amount = $request->input('amount');
                $pay_period = $request->input('pay_period');
                $installmentAmounts = Loan::roundedInstallmentAmounts($amount, $pay_period);
                $received_amount = 0;
                $status = 0;
                $emp =Employee::find($request->input('employee_id'));
                $owned_by = $emp->owned_by;
                $created_by = \Auth::user()->creatorId();

                $loan = new Loan();
                $loan->branches = $request->input('branches');
                $loan->employee_id = $request->input('employee_id');
                $loan->title = $request->input('title');
                $loan->department = $request->input('department');
                $loan->amount = $amount;
                $loan->maxamount = $request->input('maxamount');
                $loan->emp_sec = $request->input('emp_sec');
                $loan->service_tenure = $request->input('service_tenure');
                $loan->apply_date = now();
                $loan->from_pay_month = $request->input('from_pay_month');
                $loan->pay_period = $pay_period;
                $loan->loan_ended = $request->input('loan_ended');
                $loan->reason = $request->input('reason');
                $loan->per_month_amount = $installmentAmounts[0] ?? 0;
                $loan->received_amount = $received_amount;
                $loan->status = $status;
                $loan->owned_by = $owned_by;
                $loan->created_by = $created_by;
                $loan->save();
                $loan->syncInstallmentPlan($request->input('from_pay_month'));
                \DB::commit();
                return redirect()->route('loan.index')->with('success', __('Loan  successfully created.'));
            } catch (\Exception $e) {
                dd($e);
                \DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Loan $loan)
    {
        $loan->load('stopHistories');
        $employee = Employee::with('department')->where('id',$loan->employee_id)->first();
        return view('employee.loan.show',compact('loan','employee'));
    }

    public function edit($loan)
    {
        $loan = Loan::find($loan);
        if (\Auth::user()->can('edit loan')) {
            if ($loan->created_by == \Auth::user()->creatorId()) {
                $loan_options = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $loans = loan::$Loantypes;
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $employee = Employee::where('created_by', \Auth::user()->creatorId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
                $employee->prepend('Select Employee', '');
                $receivedAmount = $this->getLoanReceivedAmount($loan);
                $receivedInstallments = $this->getLoanReceivedInstallments($loan);
                $latestGeneratedSalaryMonth = $this->getLatestGeneratedSalaryMonth($loan->employee_id);
                $latestGeneratedSalaryMonthValue = $latestGeneratedSalaryMonth ? $latestGeneratedSalaryMonth->format('Y-m') : '';
                $latestGeneratedSalaryText = $latestGeneratedSalaryMonth ? $latestGeneratedSalaryMonth->format('M Y') : '';
                return view('employee.loan.edit', compact('loan', 'loan_options', 'loans', 'branches', 'employee', 'receivedAmount', 'receivedInstallments', 'latestGeneratedSalaryMonthValue', 'latestGeneratedSalaryText'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Loan $loan)
    {
        if (\Auth::user()->can('edit loan')) {
            if ($loan->created_by == \Auth::user()->creatorId()) {
                $this->normalizeLoanMonthDates($request);

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'branches' => 'required|integer',
                        'employee_id' => 'required|integer',
                        'title' => 'required|string',
                        'amount' => 'required|numeric',
                        'emp_sec' => 'required|string',
                        'maxamount' => 'required|numeric',
                        'from_pay_month' => 'required|date',
                        'pay_period' => 'required|integer',
                        'loan_ended' => 'required|date',
                        'reason' => 'string',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $amount = $request->input('amount');
                $pay_period = $request->input('pay_period');
                $received_amount = $loan->status == 1 ? $this->getLoanReceivedAmount($loan) : 0;
                $received_installments = $loan->status == 1 ? $this->getLoanReceivedInstallments($loan) : 0;
                $remaining_amount = $amount - $received_amount;
                $remaining_installments = $pay_period - $received_installments;

                if ($loan->status == 1) {
                    if ($amount < $received_amount) {
                        return redirect()->back()->with('error', __('Loan amount cannot be less than received loan amount.'));
                    }

                    if ($pay_period < $received_installments) {
                        return redirect()->back()->with('error', __('Installment count cannot be less than received installments.'));
                    }

                    if ($remaining_amount > 0 && $remaining_installments <= 0) {
                        return redirect()->back()->with('error', __('Please add remaining installments for remaining loan amount.'));
                    }

                    $fromPayMonth = \Carbon\Carbon::parse($request->input('from_pay_month'))->startOfMonth();
                    $latestGeneratedSalaryMonth = $this->getLatestGeneratedSalaryMonth($loan->employee_id);

                    if ($latestGeneratedSalaryMonth && $fromPayMonth->lte($latestGeneratedSalaryMonth)) {
                        return redirect()->back()->with('error', __('From paid month salary already generated. Please select next month.'));
                    }

                    $request->merge([
                        'loan_ended' => $remaining_installments > 0 ? $fromPayMonth->copy()->addMonths($remaining_installments - 1)->format('Y-m-d') : $fromPayMonth->format('Y-m-d'),
                    ]);
                }

                $installmentAmounts = Loan::roundedInstallmentAmounts(
                    $loan->status == 1 ? $remaining_amount : $amount,
                    $loan->status == 1 ? max(1, $remaining_installments) : $pay_period
                );

                $loan->branches = $request->input('branches');
                $loan->employee_id = $request->input('employee_id');
                $loan->title = $request->input('title');
                $loan->department = $request->input('department');
                $loan->amount = $amount;
                $loan->maxamount = $request->input('maxamount');
                $loan->emp_sec = $request->input('emp_sec');
                $loan->service_tenure = $request->input('service_tenure');
                $loan->from_pay_month = $request->input('from_pay_month');
                $loan->pay_period = $pay_period;
                $loan->loan_ended = $request->input('loan_ended');
                $loan->reason = $request->input('reason');
                $loan->per_month_amount = $installmentAmounts[0] ?? 0;
                $loan->received_amount = $received_amount;
                $loan->save();
                $loan->syncInstallmentPlan($request->input('from_pay_month'));

                if ($loan->status == 1) {
                    $this->syncLoanVoucher($loan);
                }

                return redirect()->back()->with('success', __('Loan successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Loan $loan)
    {
        if (\Auth::user()->can('delete loan')) {
            if ($loan->created_by == \Auth::user()->creatorId()) {
                $loan->delete();

                return redirect()->back()->with('success', __('Loan successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function emp_sec_tenure($id){

        $total_sec = 0;
        $service_tenure=0;
        $employee_pay = EmployeeMonthlySalary::with(['employee','employee.designation','salary_heads','employee.employee_payscale_details','employee.employee_payscale_details.scale',
        ])->where('employee_id',$id)->where('status','paid')->where('created_by', '=', \Auth::user()->creatorId())->get();
        $employee = Employee::with('department')->where('id',$id)->first();
        if(!empty($employee_pay)){
            foreach($employee_pay as $pay){
               $total_sec += $pay->emp_sec;
            }
        }
        if ($employee->company_doj) {
            $company_doj = \Carbon\Carbon::parse($employee->company_doj);
            $current_date = \Carbon\Carbon::now();
            $years = $current_date->diffInYears($company_doj);
            $months = $current_date->diffInMonths($company_doj) % 12;
            if ($years > 0) {
                $service_tenure = $years . 'years -' . $months . 'months';
            } else {
                $service_tenure = $months . ' Months';
            }
        }
        $latestGeneratedSalaryMonth = $this->getLatestGeneratedSalaryMonth($id);

        return response([
            'service_tenure'=>$service_tenure,
            'total_sec'=>$total_sec,
            'emp_department'=>$employee->department->name,
            'latest_salary_month' => $latestGeneratedSalaryMonth ? $latestGeneratedSalaryMonth->format('Y-m') : '',
            'latest_salary_text' => $latestGeneratedSalaryMonth ? $latestGeneratedSalaryMonth->format('M Y') : '',
        ]);
    }

    public function printloan(Request $request, $id){
        $loan = Loan::with(['employee', 'installments'])->where('id', $id)->first();
        if (!$loan) {
            return redirect()->back()->with('error', __('Loan not found.'));
        }

        if ($request->boolean('download') || $request->boolean('preview') || $request->boolean('print')) {
            return $this->loanInstallmentPdf($loan, $request->boolean('download'));
        }

        return view('employee.loan.pdf',compact('loan'));
    }

    private function loanInstallmentPdf(Loan $loan, $download = false)
    {
        $html = view('employee.loan.pdf', [
            'loan' => $loan,
            'isPdf' => true,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $dompdf->getCanvas()->page_text(500, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0, 0, 0]);

        return $dompdf->stream('loan_installment_plan_' . $loan->id . '.pdf', ['Attachment' => $download]);
    }
    public function loanstatus($id){
            $bank_accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' - ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()
            ->toarray();
            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();
            $loan = Loan::with('employee')->where('id', $id)->first();
            $latestGeneratedSalary = EmployeeMonthlySalary::where('employee_id', $loan->employee_id)
                ->orderBy('salary_date', 'desc')
                ->first();
            $latestGeneratedSalaryMonth = $latestGeneratedSalary ? \Carbon\Carbon::parse($latestGeneratedSalary->salary_date)->format('Y-m') : '';
            $latestGeneratedSalaryText = $latestGeneratedSalary ? \Carbon\Carbon::parse($latestGeneratedSalary->salary_date)->format('M Y') : '';
        return view('employee.loan.status',compact('loan','accounts','subAccounts','bank_accounts','latestGeneratedSalaryMonth','latestGeneratedSalaryText'));
    }

    public function stop($id)
    {
        $loan = Loan::with(['employee', 'stopHistories'])->where('id', $id)->firstOrFail();

        if (!\Auth::user()->can('edit loan') || $loan->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $latestGeneratedSalary = EmployeeMonthlySalary::where('employee_id', $loan->employee_id)
            ->orderBy('salary_date', 'desc')
            ->first();
        $latestGeneratedSalaryMonth = $latestGeneratedSalary ? \Carbon\Carbon::parse($latestGeneratedSalary->salary_date)->format('Y-m') : '';
        $latestGeneratedSalaryText = $latestGeneratedSalary ? \Carbon\Carbon::parse($latestGeneratedSalary->salary_date)->format('M Y') : '';

        return view('employee.loan.stop', compact('loan', 'latestGeneratedSalaryMonth', 'latestGeneratedSalaryText'));
    }

    public function stopStore(Request $request, $id)
    {
        $loan = Loan::where('id', $id)->firstOrFail();

        if (!\Auth::user()->can('edit loan') || $loan->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->merge([
            'stop_from_month' => $this->normalizeMonthDate($request->input('stop_from_month')),
        ]);

        $validator = \Validator::make($request->all(), [
            'stop_from_month' => 'required|date',
            'months' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $stopFromMonth = \Carbon\Carbon::parse($request->stop_from_month)->startOfMonth();
        $months = (int) $request->months;
        $stopToMonth = $stopFromMonth->copy()->addMonths($months - 1)->startOfMonth();

        if ($this->hasGeneratedSalaryFromMonth($loan->employee_id, $stopFromMonth)) {
            return redirect()->back()->with('error', __('From paid month salary already generated. Please select next month.'));
        }

        $loanStartMonth = \Carbon\Carbon::parse($loan->from_pay_month)->startOfMonth();
        $loanEndMonth = \Carbon\Carbon::parse($loan->loan_ended)->startOfMonth();

        if ($stopFromMonth->lt($loanStartMonth) || $stopFromMonth->gt($loanEndMonth)) {
            return redirect()->back()->with('error', __('Stop month must be within the loan period.'));
        }

        $overlappingStop = LoanStopHistory::where('loan_id', $loan->id)
            ->whereDate('stop_from_month', '<=', $stopToMonth->format('Y-m-d'))
            ->whereDate('stop_to_month', '>=', $stopFromMonth->format('Y-m-d'))
            ->exists();

        if ($overlappingStop) {
            return redirect()->back()->with('error', __('Loan already has a stop record for selected month.'));
        }

        LoanStopHistory::create([
            'loan_id' => $loan->id,
            'stop_from_month' => $stopFromMonth->format('Y-m-d'),
            'stop_to_month' => $stopToMonth->format('Y-m-d'),
            'months' => $months,
            'reason' => $request->reason,
            'owned_by' => $loan->owned_by,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        $loan->syncInstallmentPlan();

        return redirect()->back()->with('success', __('Loan stopped successfully.'));
    }

    public function stopDestroy($id)
    {
        $history = LoanStopHistory::with('loan')->where('id', $id)->firstOrFail();
        $loan = $history->loan;

        if (\Auth::user()->type != 'company' || $loan->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $stopFrom = \Carbon\Carbon::parse($history->stop_from_month)->startOfMonth();
        $stopTo = \Carbon\Carbon::parse($history->stop_to_month)->endOfMonth();

        $salaryExists = EmployeeMonthlySalary::where('employee_id', $loan->employee_id)
            ->whereBetween('salary_date', [$stopFrom->format('Y-m-d'), $stopTo->format('Y-m-d')])
            ->exists();

        if ($salaryExists) {
            return redirect()->back()->with('error', __('Stop loan entry cannot be deleted because salary is already generated for stopped month.'));
        }

        $history->delete();

        $loan->syncInstallmentPlan();

        return redirect()->back()->with('success', __('Loan stop entry deleted successfully.'));
    }

    public function loanstatuschange(Request $request,$id){
        \DB::beginTransaction();
        try {
            $this->normalizeLoanMonthDates($request);

            if($request->status == 2){
                $loan = Loan::where('id', $id)->first();
                $loan->status = $request->status;
                $loan->approved_by = \Auth::id();
                $loan->save();
                $loan->installments()->where('status', 0)->delete();
                \DB::commit();
                return redirect()->back()->with('success', __('Loan Rejected successfully.'));
            }else{

                $validator = \Validator::make(
                    $request->all(),
                    [
                    'bank_id' => 'required|integer|exists:bank_accounts,id',
                    'account_id' => 'required|integer|exists:chart_of_accounts,id',
                    'date' => 'required|date',
                    'paid_from' => 'required|date',
                    'amount' => 'required|numeric|min:0.01',
                    'installment' => 'required|integer|min:1',
                    'payment_method' => 'required|in:cash,online,cheque',

                ]);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    \DB::rollback();
                    return redirect()->back()->with('error', $messages->first());
                }
                    $loan = Loan::where('id', $id)->first();
                    $amount = $request->input('amount');
                    $payPeriod = (int) $request->input('installment');
                    $approvalDate = \Carbon\Carbon::parse($request->date);
                    $fromPayMonth = \Carbon\Carbon::parse($request->paid_from)->startOfMonth();
                    $loanEnded = $fromPayMonth->copy()->addMonths($payPeriod - 1);

                    if ($this->hasGeneratedSalaryFromMonth($loan->employee_id, $fromPayMonth)) {
                        \DB::rollback();
                        return redirect()->back()->with('error', __('From paid month salary already generated. Please select next month.'));
                    }

                    $loan->status = $request->status;
                    $loan->amount = $amount;
                    $loan->pay_period = $payPeriod;
                    $loan->from_pay_month = $fromPayMonth->format('Y-m-d');
                    $loan->loan_ended = $loanEnded->format('Y-m-d');
                    $installmentAmounts = Loan::roundedInstallmentAmounts($amount, $payPeriod);
                    $loan->per_month_amount = $installmentAmounts[0] ?? 0;
                    $loan->approval_date = $approvalDate->format('Y-m-d');
                    $loan->bank_id = $request->bank_id;
                    $loan->chartaccount_id = $request->account_id;
                    $loan->referance_id = $request->reference;
                    $loan->approved_by = \Auth::id();
                    $loan->save();
                    $loan->syncInstallmentPlan($fromPayMonth);

                    $bankAccount = BankAccount::find($request->bank_id);
                    $dataret = $this->createLoanVoucher($loan, $bankAccount, $request->payment_method);

                    $loan->voucher_id = $dataret;
                    $loan->save();
                \DB::commit();

                return redirect()->back()->with('success', __('Loan Approved successfully.'));
            }
        } catch (\Exception $e) {
            dd($e);
            \DB::rollback();
            return redirect()->back()->with('error', $e);
        }
    }

    public function runInstallmentSetup()
    {
        if (!\Auth::check() || \Auth::user()->type != 'company') {
            abort(403, __('Permission denied.'));
        }

        @set_time_limit(300);

        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_05_21_000001_create_loan_installments_table.php',
        ]);
        $migrationOutput = Artisan::output();
        return response(
            '<h3>Loan installment setup completed.</h3>' .
            '<h4>Migration</h4><pre>' . e($migrationOutput) . '</pre>' 
        );
    }
}
