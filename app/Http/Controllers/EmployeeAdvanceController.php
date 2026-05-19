<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeMonthlySalary;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class EmployeeAdvanceController extends Controller
{
    private function normalizeMonthDate($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        return $value;
    }

    private function getLatestGeneratedSalaryMonth($employeeId)
    {
        $latestSalary = EmployeeMonthlySalary::where('employee_id', $employeeId)
            ->orderBy('salary_date', 'desc')
            ->first();

        return $latestSalary ? \Carbon\Carbon::parse($latestSalary->salary_date)->startOfMonth() : null;
    }

    private function getAllowedAdvanceMonth($employeeId)
    {
        $latestSalaryMonth = $this->getLatestGeneratedSalaryMonth($employeeId);

        return $latestSalaryMonth
            ? $latestSalaryMonth->copy()->addMonth()->startOfMonth()
            : now()->startOfMonth();
    }

    private function validateAdvanceMonth($employeeId, $advanceDate)
    {
        $advanceMonth = \Carbon\Carbon::parse($advanceDate)->startOfMonth();
        $allowedMonth = $this->getAllowedAdvanceMonth($employeeId);

        if (!$advanceMonth->eq($allowedMonth)) {
            return __('Advance month must be :month.', ['month' => $allowedMonth->format('M Y')]);
        }

        return null;
    }

    private function hasSalaryGeneratedForMonth($employeeId, $monthDate)
    {
        $month = \Carbon\Carbon::parse($monthDate)->startOfMonth();

        return EmployeeMonthlySalary::where('employee_id', $employeeId)
            ->whereYear('salary_date', $month->year)
            ->whereMonth('salary_date', $month->month)
            ->exists();
    }

    private function hasActiveAdvanceForMonth($employeeId, $monthDate, $ignoreId = null)
    {
        $month = \Carbon\Carbon::parse($monthDate)->startOfMonth();

        return EmployeeAdvance::where('employee_id', $employeeId)
            ->where('status', '!=', 2)
            ->whereYear('advance_date', $month->year)
            ->whereMonth('advance_date', $month->month)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists();
    }

    private function createAdvanceVoucher(EmployeeAdvance $advance, BankAccount $bankAccount, $paymentMethod)
    {
        $voucherType = $paymentMethod === 'cash' ? 'CPV' : 'BPV';
        $latest = JournalEntry::where('owned_by', $advance->owned_by)
            ->where('voucher_type', $voucherType)
            ->latest()
            ->first();

        $journal = new JournalEntry();
        $journal->journal_id = $latest ? $latest->journal_id + 1 : 1;
        $journal->date = $advance->approval_date;
        $journal->reference = $advance->reference;
        $journal->description = 'Advance id : ' . $advance->id;
        $journal->reference_id = $advance->id;
        $journal->category = 'Advance';
        $journal->voucher_type = $voucherType;
        $journal->user_id = $advance->employee_id;
        $journal->user_type = 'Employee';
        $journal->owned_by = $advance->owned_by;
        $journal->created_by = $advance->created_by;
        $journal->save();

        JournalItem::create([
            'journal' => $journal->id,
            'account' => $bankAccount->chart_account_id,
            'description' => $advance->advance_reason,
            'user_id' => $advance->employee_id,
            'user_type' => 'Employee',
            'bank_id' => $bankAccount->id,
            'credit' => $advance->advance_amount,
            'debit' => 0,
            'entry_id' => $advance->id,
            'types' => 'Advance Payment',
            'branch_id' => $advance->owned_by,
        ]);

        JournalItem::create([
            'journal' => $journal->id,
            'account' => $advance->chartaccount_id,
            'description' => $advance->advance_reason,
            'user_id' => $advance->employee_id,
            'user_type' => 'Employee',
            'credit' => 0,
            'debit' => $advance->advance_amount,
            'entry_id' => $advance->id,
            'types' => 'Advance Payment',
            'branch_id' => $advance->owned_by,
        ]);

        return $journal->id;
    }

    private function syncAdvanceVoucher(EmployeeAdvance $advance)
    {
        if (empty($advance->voucher_id)) {
            return;
        }

        $journal = JournalEntry::where('id', $advance->voucher_id)
            ->where('category', 'Advance')
            ->first();

        if (!$journal) {
            return;
        }

        $bankAccount = BankAccount::find($advance->bank_id);

        $journal->date = $advance->approval_date ?: $journal->date;
        $journal->reference = $advance->reference;
        $journal->description = 'Advance id : ' . $advance->id;
        $journal->reference_id = $advance->id;
        $journal->user_id = $advance->employee_id;
        $journal->user_type = 'Employee';
        $journal->owned_by = $advance->owned_by;
        $journal->created_by = $advance->created_by;
        $journal->save();

        $creditLine = JournalItem::where('journal', $journal->id)
            ->where('credit', '>', 0)
            ->first();

        if (!$creditLine) {
            $creditLine = new JournalItem();
            $creditLine->journal = $journal->id;
        }

        $creditLine->account = $bankAccount ? $bankAccount->chart_account_id : $creditLine->account;
        $creditLine->description = $advance->advance_reason;
        $creditLine->user_id = $advance->employee_id;
        $creditLine->user_type = 'Employee';
        $creditLine->bank_id = $bankAccount ? $bankAccount->id : $creditLine->bank_id;
        $creditLine->credit = $advance->advance_amount;
        $creditLine->debit = 0;
        $creditLine->entry_id = $advance->id;
        $creditLine->types = 'Advance Payment';
        $creditLine->branch_id = $advance->owned_by;
        $creditLine->save();

        $debitLine = JournalItem::where('journal', $journal->id)
            ->where('debit', '>', 0)
            ->first();

        if (!$debitLine) {
            $debitLine = new JournalItem();
            $debitLine->journal = $journal->id;
        }

        $debitLine->account = $advance->chartaccount_id;
        $debitLine->description = $advance->advance_reason;
        $debitLine->user_id = $advance->employee_id;
        $debitLine->user_type = 'Employee';
        $debitLine->credit = 0;
        $debitLine->debit = $advance->advance_amount;
        $debitLine->entry_id = $advance->id;
        $debitLine->types = 'Advance Payment';
        $debitLine->branch_id = $advance->owned_by;
        $debitLine->save();
    }

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = EmployeeAdvance::with(['employee', 'approvedBy'])->where('created_by', \Auth::user()->creatorId());
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        } else {
            $query = EmployeeAdvance::with(['employee', 'approvedBy'])->where('owned_by', \Auth::user()->ownedId());
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', '');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', '');
        }

        if (!empty($request->branches)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('branch_id', $request->branches);
            });
        }
        if (!empty($request->department_id)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            });
        }
        if (!empty($request->designation_id)) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('designation_id', $request->designation_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $advance = $query->paginate(25);
        return view('employee.advance.index', compact('advance', 'branches', 'departments', 'designations'));
    }

    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
        } else {
            $employee = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $employee->prepend('Select Employee', '');
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        return view('employee.advance.create', compact('employee', 'branches'));
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            $request->merge([
                'date' => $this->normalizeMonthDate($request->input('date')),
            ]);

            $request->validate([
                'branches' => 'required|integer',
                'employee_id' => 'required|integer',
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date',
                'reason' => 'required|string',
            ]);

            $monthError = $this->validateAdvanceMonth($request->employee_id, $request->date);
            if ($monthError) {
                \DB::rollback();
                return redirect()->back()->with('error', $monthError);
            }

            if ($this->hasActiveAdvanceForMonth($request->employee_id, $request->date)) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Advance already exists for selected month.'));
            }

            $advance = new EmployeeAdvance();
            $advance->employee_id = $request->input('employee_id');
            $advance->advance_amount = $request->input('amount');
            $advance->advance_date = $request->input('date');
            $advance->advance_reason = $request->input('reason');
            $advance->status = 0;
            $advance->owned_by = \Auth::user()->ownedId();
            $advance->created_by = \Auth::user()->creatorId();
            $advance->save();
            \DB::commit();
            return redirect()->route('employee-advance.index')->with('success', __('Advance successfully created.'));
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        //
    }

    public function printAdvance(Request $request, $id)
    {
        $advance = EmployeeAdvance::with(['employee', 'approvedBy'])->findOrFail($id);

        if ($request->boolean('download') || $request->boolean('preview') || $request->boolean('print')) {
            return $this->advancePdf($advance, $request->boolean('download'));
        }

        return view('employee.advance.pdf', compact('advance'));
    }

    private function advancePdf(EmployeeAdvance $advance, $download = false)
    {
        $html = view('employee.advance.pdf', [
            'advance' => $advance,
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

        return $dompdf->stream('advance_salary_' . $advance->id . '.pdf', ['Attachment' => $download]);
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('edit loan')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $advance = EmployeeAdvance::findOrFail($id);

        if ($advance->status == 1 && \Auth::user()->type != 'company') {
            return response()->json(['error' => __('Only admin can edit approved advance.')], 401);
        }

        $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('Select Branch', '');
        $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        return view('employee.advance.edit', compact('advance', 'branches', 'employee'));
    }

    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $advance = EmployeeAdvance::findOrFail($id);

            if ($advance->status == 1 && \Auth::user()->type != 'company') {
                \DB::rollback();
                return redirect()->back()->with('error', __('Only admin can edit approved advance.'));
            }

            $request->merge([
                'date' => $this->normalizeMonthDate($request->input('date')),
            ]);

            $request->validate([
                'employee_id' => 'required|integer',
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date',
                'reason' => 'required|string',
            ]);

            if ($advance->status == 1 && $this->hasSalaryGeneratedForMonth($advance->employee_id, $advance->advance_date)) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Approved advance cannot be edited because salary is already generated.'));
            }

            $monthError = $this->validateAdvanceMonth($request->employee_id, $request->date);
            if ($monthError) {
                \DB::rollback();
                return redirect()->back()->with('error', $monthError);
            }

            if ($this->hasActiveAdvanceForMonth($request->employee_id, $request->date, $advance->id)) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Advance already exists for selected month.'));
            }

            $advance->employee_id = $request->input('employee_id');
            $advance->advance_amount = $request->input('amount');
            $advance->advance_date = $request->input('date');
            $advance->advance_reason = $request->input('reason');
            $advance->owned_by = \Auth::user()->ownedId();
            $advance->created_by = \Auth::user()->creatorId();
            $advance->save();

            if ($advance->status == 1) {
                $this->syncAdvanceVoucher($advance);
            }

            \DB::commit();
            return redirect()->route('employee-advance.index')->with('success', __('Advance successfully updated.'));
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        $advance = EmployeeAdvance::findOrFail($id);

        if ($advance->status == 1) {
            return redirect()->back()->with('error', __('Approved advance cannot be deleted.'));
        }

        $advance->delete();
        return redirect()->back()->with('success', __('Advance successfully deleted.'));
    }

    public function employeeLatestSalaryMonth($id)
    {
        $latestSalaryMonth = $this->getLatestGeneratedSalaryMonth($id);
        $allowedAdvanceMonth = $this->getAllowedAdvanceMonth($id);
        $hasAdvanceForAllowedMonth = $this->hasActiveAdvanceForMonth($id, $allowedAdvanceMonth);

        return response()->json([
            'latest_salary_month' => $latestSalaryMonth ? $latestSalaryMonth->format('Y-m') : '',
            'latest_salary_text' => $latestSalaryMonth ? $latestSalaryMonth->format('M Y') : '',
            'allowed_advance_month' => $allowedAdvanceMonth->format('Y-m'),
            'allowed_advance_text' => $allowedAdvanceMonth->format('M Y'),
            'has_advance_for_month' => $hasAdvanceForAllowedMonth,
            'advance_exists_message' => $hasAdvanceForAllowedMonth ? __('Advance already exists for :month.', ['month' => $allowedAdvanceMonth->format('M Y')]) : '',
        ]);
    }

    public function status($id)
    {
        $advance = EmployeeAdvance::with('employee')->findOrFail($id);

        if (!\Auth::user()->can('edit loan') || $advance->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $bankAccounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' - ',holder_name) AS name"))
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');
        $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->toArray();
        $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', '!=', 0)
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->toArray();

        return view('employee.advance.status', compact('advance', 'bankAccounts', 'accounts', 'subAccounts'));
    }

    public function statusChange(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
            $advance = EmployeeAdvance::findOrFail($id);

            if ($advance->status != 0) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Advance status is already updated.'));
            }

            if ((int) $request->status == 1) {
                $request->merge([
                    'advance_date' => $this->normalizeMonthDate($request->input('advance_date')),
                ]);

                $request->validate([
                    'bank_id' => 'required|integer|exists:bank_accounts,id',
                    'account_id' => 'required|integer|exists:chart_of_accounts,id',
                    'approval_date' => 'required|date',
                    'advance_date' => 'required|date',
                    'amount' => 'required|numeric|min:0.01',
                    'payment_method' => 'required|in:cash,online,approved_by',
                    'reference' => 'nullable|string',
                ]);

                if ($this->hasSalaryGeneratedForMonth($advance->employee_id, $request->advance_date)) {
                    \DB::rollback();
                    return redirect()->back()->with('error', __('Advance month salary already generated. Please select next month.'));
                }

                $monthError = $this->validateAdvanceMonth($advance->employee_id, $request->advance_date);
                if ($monthError) {
                    \DB::rollback();
                    return redirect()->back()->with('error', $monthError);
                }

                if ($this->hasActiveAdvanceForMonth($advance->employee_id, $request->advance_date, $advance->id)) {
                    \DB::rollback();
                    return redirect()->back()->with('error', __('Advance already exists for selected month.'));
                }

                $advance->status = 1;
                $advance->advance_amount = $request->amount;
                $advance->advance_date = $request->advance_date;
                $advance->approval_date = $request->approval_date;
                $advance->bank_id = $request->bank_id;
                $advance->chartaccount_id = $request->account_id;
                $advance->reference = $request->reference;
                $advance->payment_method = $request->payment_method;
                $advance->approved_by = \Auth::id();
                $advance->save();

                $bankAccount = BankAccount::findOrFail($request->bank_id);
                $advance->voucher_id = $this->createAdvanceVoucher($advance, $bankAccount, $request->payment_method);
                $advance->save();
            } elseif ((int) $request->status == 2) {
                $advance->status = 2;
                $advance->approved_by = \Auth::id();
                $advance->save();
            } else {
                \DB::rollback();
                return redirect()->back()->with('error', __('Invalid status.'));
            }

            \DB::commit();
            return redirect()->back()->with('success', __('Advance status successfully updated.'));
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
