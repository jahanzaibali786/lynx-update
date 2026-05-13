<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeMonthlySalary;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\User;
use App\Models\Utility;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = Loan::with('employee')->where('created_by', \Auth::user()->creatorId());
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        } else {
            $query = Loan::with('employee')->where('owned_by', \Auth::user()->ownedId());
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        }

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
        if(!empty($request->status)){
            $query->where('status',$request->status);
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
        $loans= $query->paginate(25);
        return view('employee.loan.index', compact('loans','branches','departments','designations'));
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

                $amount = $request->input('amount');
                $pay_period = $request->input('pay_period');
                $per_month_amount = $amount / $pay_period;
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
                $loan->per_month_amount = round($per_month_amount);
                $loan->received_amount = $received_amount;
                $loan->status = $status;
                $loan->owned_by = $owned_by;
                $loan->created_by = $created_by;
                $loan->save();
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
                return view('employee.loan.edit', compact('loan', 'loan_options', 'loans', 'branches', 'employee'));
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
                $per_month_amount = $amount / $pay_period;
                $received_amount = 0;
                $status = 0;

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
                $loan->per_month_amount = round($per_month_amount);
                $loan->received_amount = $received_amount;
                $loan->status = $status;
                $loan->save();
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
        return response([
            'service_tenure'=>$service_tenure,
            'total_sec'=>$total_sec,
            'emp_department'=>$employee->department->name,
        ]);
    }

    public function printloan($id){
        $loan = Loan::with('employee')->where('id', $id)->first();
        return view('employee.loan.pdf',compact('loan'));
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
    public function loanstatuschange(Request $request,$id){
        \DB::beginTransaction();
        try {
            $this->normalizeLoanMonthDates($request);

            if($request->status == 2){
                $loan = Loan::where('id', $id)->first();
                $loan->status = $request->status;
                $loan->save();
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
                    'payment_method' => 'required|in:cash,online,check',

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
                    $loan->per_month_amount = round($amount / $payPeriod);
                    $loan->approval_date = $approvalDate->format('Y-m-d');
                    $loan->bank_id = $request->bank_id;
                    $loan->chartaccount_id = $request->account_id;
                    $loan->referance_id = $request->reference;
                    $loan->save();

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
}
