{{ Form::model($employeesalary, ['route' => ['emp-month-sal-attendance.update', $employeesalary->id], 'method' => 'PUT']) }}
@php
    $salaryEditable = $salaryEditable ?? false;
    $lastPayscaleDetail = @$employeesalary->employee->employee_payscale_details->last();
    $salaryTotalDeductions =
        (float) ($employeesalary->loan ?? 0) +
        (float) ($employeesalary->emp_sec_loan ?? 0) +
        (float) ($employeesalary->emp_sec ?? 0) +
        (float) ($employeesalary->pessi ?? 0) +
        (float) ($employeesalary->eobi ?? 0) +
        (float) ($employeesalary->it ?? 0) +
        (float) ($employeesalary->dedu ?? 0) +
        (float) ($employeesalary->tra_course ?? 0) +
        (float) ($employeesalary->sal_advance ?? 0);
    $calculatedNetPay = max(0, (float) ($employeesalary->gross ?? 0) + (float) ($employeesalary->stop_sal ?? 0) - $salaryTotalDeductions);
    $employeeIsResigned = !empty(optional($employeesalary->employee)->is_res_ter) || !empty(optional($employeesalary->employee)->is_resigned);
@endphp
<div class="modal-body">
    @if (!$salaryEditable)
        <div class="alert alert-warning mb-3">
            {{ __('This salary can be edited only while it is unpaid and not GM finalized.') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-3"><b>Emp No</b> : {{@$employeesalary->employee->employee_id}} </div>
        <div class="col-md-3"><b>Name</b> : {{@$employeesalary->employee->name}}</div>
        <div class="col-md-3"><b>Father Name</b> : {{@$employeesalary->employee->f_name}}</div>
        <div class="col-md-3"><b>Dept.</b>: {{@$employeesalary->employee->department->name}}</div>
        <div class="col-md-3"><b>Branch</b>:
            {{ optional($employeesalary->employee->userbranch)->name }}
        </div>
        <div class="col-md-3"><b>Area</b>: {{@$employeesalary->employee->area}}</div>
        <div class="col-md-3"><b>Date of Joining</b>: {{ !empty($employeesalary->employee->company_doj) ? \Carbon\Carbon::parse($employeesalary->employee->company_doj)->format('d-M-Y') : '-' }}</div>
        <div class="col-md-3"><b>Salary Month</b>: {{ !empty($employeesalary->salary_date) ? date('M-Y', strtotime($employeesalary->salary_date)) : '-' }}</div>
        <div class="col-md-3"><b>Paid Date</b>: {{ !empty($employeesalary->paid_date) ? date('d-M-Y', strtotime($employeesalary->paid_date)) : '-' }}</div>
        <div class="col-md-3"><b>Status</b>: {{ ucwords(str_replace('_', ' ', $employeesalary->status ?? 'unpaid')) }}</div>
        <div class="col-md-3"><b>Employee Status</b>:
            @if ($employeeIsResigned)
                <span class="badge bg-danger">{{ __('Resign') }}</span>
            @else
                <span class="badge bg-success">{{ __('Active') }}</span>
            @endif
        </div>
        <div class="col-md-3"><b>Paid Through</b>: {{ $employeesalary->paymode ?: (!empty($lastPayscaleDetail) ? $lastPayscaleDetail->paymode : '-') }}</div>
        <div class="col-md-3"><b>Account No</b>: {{ $employeesalary->account_number ?: (!empty($lastPayscaleDetail) ? $lastPayscaleDetail->account_number : '-') }}</div>
    </div>
    @if (!empty($salaryAttendance))
        @php
            $attendanceWorkingDaysRaw = (float) ($salaryAttendance->working_days ?? 0);
            $attendanceAbsentDays = (float) ($salaryAttendance->absents ?? 0);
            $attendanceEmployeeMonthDays = $attendanceWorkingDaysRaw + $attendanceAbsentDays;
            $attendanceMonthDays = $attendanceEmployeeMonthDays > 0 && $attendanceEmployeeMonthDays < 24
                ? $attendanceEmployeeMonthDays
                : (float) ($salaryAttendance->month_days ?? $attendanceEmployeeMonthDays);
            $attendanceHolidays = 0;

            if (!($attendanceEmployeeMonthDays > 0 && $attendanceEmployeeMonthDays < 24) && !empty($salaryAttendance->for_month_of)) {
                $attendanceMonth = \Carbon\Carbon::parse($salaryAttendance->for_month_of);

                for ($day = $attendanceMonth->copy()->startOfMonth(); $day->lte($attendanceMonth->copy()->endOfMonth()); $day->addDay()) {
                    if ($day->isSunday()) {
                        $attendanceHolidays++;
                    }
                }

                $attendanceHolidays = min($attendanceHolidays, (int) $attendanceMonthDays);
            }

            $attendanceWorkingDays = max(0, $attendanceMonthDays - $attendanceHolidays);
            $attendanceEmployeeWorkingDays = $attendanceWorkingDays + $attendanceHolidays - $attendanceAbsentDays;
        @endphp
        <hr>
        <h5 class="mb-2">{{ __('Attendance') }}</h5>
        <div class="row">
            <div class="col-md-3"><b>{{ __('Attendance Month') }}</b>: {{ !empty($salaryAttendance->for_month_of) ? date('M-Y', strtotime($salaryAttendance->for_month_of)) : '-' }}</div>
            <div class="col-md-3"><b>{{ __('Employee Working Days') }}</b>: {{ number_format($attendanceEmployeeWorkingDays, 0) }}</div>
            <div class="col-md-3"><b>{{ __('Holidays') }}</b>: {{ number_format($attendanceHolidays, 0) }}</div>
            <div class="col-md-3"><b>{{ __('Working Days') }}</b>: {{ number_format($attendanceWorkingDays, 0) }}</div>
            <div class="col-md-3"><b>{{ __('Absents') }}</b>: {{ number_format((float) ($salaryAttendance->absents ?? 0), 0) }}</div>
            <div class="col-md-3"><b>{{ __('Leave') }}</b>: {{ number_format((float) ($salaryAttendance->leave ?? 0), 0) }}</div>
            <div class="col-md-3"><b>{{ __('Month Days') }}</b>: {{ number_format($attendanceMonthDays, 0) }}</div>
            <div class="col-md-3"><b>{{ __('Total Annual') }}</b>: {{ $salaryAttendance->total_annual ?? '-' }}</div>
            <div class="col-md-3"><b>{{ __('Bal. Annual') }}</b>: {{ $salaryAttendance->bal_annual ?? '-' }}</div>
            <div class="col-md-3"><b>{{ __('Total Casual') }}</b>: {{ $salaryAttendance->total_casual ?? '-' }}</div>
            <div class="col-md-3"><b>{{ __('Bal. Casual') }}</b>: {{ $salaryAttendance->bal_casual ?? '-' }}</div>
            <div class="col-md-3"><b>{{ __('Fwd to HR') }}</b>: {{ (int) ($salaryAttendance->accountant_finalize ?? 0) === 1 ? __('Yes') : __('No') }}</div>
            <div class="col-md-3"><b>{{ __('Finalized') }}</b>: {{ (int) ($salaryAttendance->adm_final ?? 0) === 1 ? __('Yes') : __('No') }}</div>
            <div class="col-md-3"><b>{{ __('Salary Final') }}</b>: {{ (int) ($salaryAttendance->sal_final ?? 0) === 1 ? __('Yes') : __('No') }}</div>
            <div class="col-md-3"><b>{{ __('HR Final') }}</b>: {{ (int) ($salaryAttendance->gm_final ?? 0) === 1 ? __('Yes') : __('No') }}</div>
        </div>
    @else
        <hr>
        <div class="alert alert-info mb-0">
            {{ __('No attendance row found for this salary month.') }}
        </div>
    @endif
    <hr>
    <div class="scale_heads_row row">
        @if (@$lastPayscaleDetail && isset($lastPayscaleDetail->pay_scale_id))
        @php
        $gross = 0;
        $payscalesauto =
        \App\Models\EmployeeScale::with('employeeScaleHeads','employeeScaleHeads.SalaryHeads','employeepayScaledetailHeads')->where('id',$lastPayscaleDetail->pay_scale_id)->first();
        $salheads = \App\Models\EmployeeMonthlySalaryHeads::with('SalaryHead')->where('sal_id',$employeesalary->id)->get();
        @endphp
        @foreach (@$salheads as $scale_head)
        <div class="form-group col-md-2">
            <label class="form-label">{{ @$scale_head->SalaryHead->head }}</label>
            <input type="number" name="scale_heads[{{ @$scale_head->id }}]" value="{{ @$scale_head->head_value }}"
                class="form-control" readonly>
        </div>
        @php
        if ($scale_head->SalaryHead->head == 'Initial Basic') {
            $basic_new = @$scale_head->head_value;
        }
        $gross += @$scale_head->head_value;
        @endphp
        @endforeach
        <div class="form-group col-md-2">
            <label class="form-label">Basics</label>
            <input type="basic" name="basic" value="{{ @$employeesalary->basics }}" class="form-control" readonly>
        </div>
        <div class="form-group col-md-4">
            <label class="form-label">Gross</label>
            <input type="gross" name="gross" value="{{ @$gross - $basic_new + @$employeesalary->basics }}" class="form-control" readonly>
        </div>
        @endif
    </div>
    <div class="row">

        <div class="form-group col-md-3">
            {!! Form::label('drns', __('Drns'), ['class' => 'form-label']) !!}
            {{ Form::number('drns',  !empty($employeesalary->drns) ? $employeesalary->drns : '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('conv', __('Other'), ['class' => 'form-label']) !!}
            {{ Form::number('conv',  !empty($employeesalary->conv) ? $employeesalary->conv : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('other_add', __('Other Allowance'), ['class' => 'form-label']) !!}
            {{ Form::number('other_add', !empty($employeesalary->other_add) ? $employeesalary->other_add : '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('misc', __('Misc'), ['class' => 'form-label']) !!}
            {{ Form::number('misc', !empty($employeesalary->misc) ? $employeesalary->misc : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('chaild_concession', __('Child Cons.'), ['class' => 'form-label']) !!}
            {{ Form::number('chaild_concession',  !empty($employeesalary->chaild_con) ? $employeesalary->chaild_con : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('emp_sec', __('Emp Sec.'), ['class' => 'form-label'])
            !!}<span>{{@$employeesalary->employee->security}}
                %</span>
            {{ Form::number('emp_sec', !empty($employeesalary->emp_sec) ? $employeesalary->emp_sec : '0',  ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly', 'id' => 'security']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('security_receive_account', __('Security Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('security_receive_account',  !empty($lastPayscaleDetail->security_receive_account) ? \App\Models\ChartOfAccount::where('id',$lastPayscaleDetail->security_receive_account)->first()->name : '',['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('itax', __('I.Tax'), ['class' => 'form-label']) !!}
            {{ Form::number('itax',  !empty($employeesalary->it) ? $employeesalary->it : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('tax_payable_account', __('I.Tax Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('tax_payable_account', !empty($lastPayscaleDetail->tax_payable_account) ? \App\Models\ChartOfAccount::where('id',@$lastPayscaleDetail->tax_payable_account)->first()->name : '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('eobi', __('EOBI'), ['class' => 'form-label']) !!}
            <span>&nbsp;&nbsp;{{ @$employeesalary->employee->eobi }}%</span>
            {{ Form::number('eobi', !empty($employeesalary->eobi) ? $employeesalary->eobi : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('eobi_employer', __('EOBI Employer'), ['class' => 'form-label']) !!}
            <span>&nbsp;&nbsp;{{ @$employeesalary->employee->eobi_employer }}%</span>
            {{ Form::number('eobi_employer', !empty($employeesalary->eobi_employer) ? $employeesalary->eobi_employer : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('eobi_payable_account', __('EOBI Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('eobi_payable_account',  !empty($lastPayscaleDetail->eobi_payable_account) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->eobi_payable_account)->first()->name : '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pessi', __('PESSI'), ['class' => 'form-label']) !!}<span>
                &nbsp;&nbsp;{{@$employeesalary->employee->pessi}}%</span>
            {{ Form::number('pessi', !empty($employeesalary->pessi) ? $employeesalary->pessi : '' ,  ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pessi_employer', __('PESSI Employer'), ['class' => 'form-label']) !!}<span> &nbsp;&nbsp;
                {{@$employeesalary->employee->pessi_employer}}%</span>
            {{ Form::number('pessi_employer',!empty($employeesalary->pessi_employer) ? $employeesalary->pessi_employer : '' ,['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pessi_payable_account', __('PESSI Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('pessi_payable_account',  !empty($lastPayscaleDetail->pessi_payable_account) ? \App\Models\ChartOfAccount::where('id',$lastPayscaleDetail->pessi_payable_account)->first()->name : '',  ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('other_deduction', __('Other Deduction'), ['class' => 'form-label']) !!}
            {{ Form::number('other_deduction',  !empty($employeesalary) ? $employeesalary->dedu : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('other_dedu_payable_account', __('Deduction Payable Account'), ['class' => 'form-label'])
            !!}
            {{ Form::text('other_dedu_payable_account',  !empty($lastPayscaleDetail->other_dedu_payable_account) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->other_dedu_payable_account)->first()->name : '', ['class' => 'form-control conditional-account-field', 'data-amount-field' => 'other_deduction']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance', __('Advance'), ['class' => 'form-label']) !!}
            {{ Form::number('advance',  !empty($employeesalary) ? $employeesalary->sal_advance : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance_payable_account', __('Advance Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('advance_payable_account',  !empty($lastPayscaleDetail->advance_payable_account) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->advance_payable_account )->first()->name: '',  ['class' => 'form-control conditional-account-field', 'data-amount-field' => 'advance']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('stop_sal', __('Stop Salary'), ['class' => 'form-label']) !!}
            {{ Form::number('stop_sal', !empty($employeesalary) ? $employeesalary->stop_sal : '0', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('remarks', __('Description / Remarks'), ['class' => 'form-label']) !!}
            {{ Form::textarea('remarks', $employeesalary->remarks ?? '', ['class' => 'form-control', 'rows' => 2, 'maxlength' => 1000]) }}
        </div>
    </div>

    <div class="row net_row">
        <div class="form-group col-md-6">
            {!! Form::label('net', __('Net'), ['class' => 'form-label']) !!}
            {{ Form::number('net', $calculatedNetPay, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('net_payable_account', __('Net Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('net_payable_account',  !empty($lastPayscaleDetail) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->net_payable_account)->first()->name : '',  ['class' => 'form-control','readonly'=>'readonly']) }}
        </div>
    </div>
    <hr>

    @if(!empty($arrears) && count($arrears) > 0)
    <div>
        <h4><b>Arrears</b></h4>
        <table class="datatable">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Arear Month</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($arrears as $arear)
                <tr >
                    <td>{{$loop->iteration}}</td>
                    <td>{{date('F, Y', strtotime($arear->salary_date))}}</td>
                    <td>{{$arear->gross}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
<div class="modal-footer {{ $salaryEditable ? '' : 'd-none' }}">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
<script>
    if (!@json($salaryEditable)) {
        const salaryDetailForm = document.currentScript ? document.currentScript.closest('form') : null;
        if (salaryDetailForm) {
            salaryDetailForm.querySelectorAll('input, select, textarea, button').forEach(field => {
                if (field.type === 'hidden' || field.dataset.bsDismiss === 'modal') {
                    return;
                }
                field.setAttribute(field.tagName === 'INPUT' || field.tagName === 'TEXTAREA' ? 'readonly' : 'disabled', 'readonly');
            });
        }
    }

    (() => {
        const form = document.currentScript ? document.currentScript.closest('form') : null;
        if (!form) {
            return;
        }

        const earningFields = ['chaild_concession', 'drns', 'misc', 'conv', 'other_add'];
        const deductionFields = ['itax', 'other_deduction', 'advance'];
        const grossElement = form.querySelector('input[name="gross"]');
        const netElement = form.querySelector('input[name="net"]');
        const originalConditionalAccountValues = {};
        const initialGross = @json((float) ($employeesalary->gross ?? 0));
        const fixedDeductions = @json(
            (float) ($employeesalary->loan ?? 0) +
            (float) ($employeesalary->emp_sec_loan ?? 0) +
            (float) ($employeesalary->emp_sec ?? 0) +
            (float) ($employeesalary->pessi ?? 0) +
            (float) ($employeesalary->eobi ?? 0) +
            (float) ($employeesalary->tra_course ?? 0)
        );
        const stopSalary = @json((float) ($employeesalary->stop_sal ?? 0));
        const initialEditableEarnings = earningFields.reduce((total, fieldName) => {
            return total + (parseFloat(form.querySelector(`input[name="${fieldName}"]`)?.value) || 0);
        }, 0);
        const baseGross = initialGross - initialEditableEarnings;

        form.querySelectorAll('.conditional-account-field').forEach(field => {
            originalConditionalAccountValues[field.name] = field.value || '';
        });

        function toggleConditionalAccounts() {
            form.querySelectorAll('.conditional-account-field').forEach(field => {
                const amountFieldName = field.dataset.amountField;
                const amount = parseFloat(form.querySelector(`input[name="${amountFieldName}"]`)?.value) || 0;
                const shouldRequire = amount > 0;

                field.required = shouldRequire;
                field.readOnly = !shouldRequire;
                field.classList.toggle('bg-light', !shouldRequire);

                if (!shouldRequire) {
                    field.value = '';
                } else if (!field.value && originalConditionalAccountValues[field.name]) {
                    field.value = originalConditionalAccountValues[field.name];
                }
            });
        }

        function recalculateSalary() {
            const editableEarnings = earningFields.reduce((total, fieldName) => {
                return total + (parseFloat(form.querySelector(`input[name="${fieldName}"]`)?.value) || 0);
            }, 0);
            const editableDeductions = deductionFields.reduce((total, fieldName) => {
                return total + (parseFloat(form.querySelector(`input[name="${fieldName}"]`)?.value) || 0);
            }, 0);
            const gross = baseGross + editableEarnings;
            const net = Math.max(0, gross + stopSalary - fixedDeductions - editableDeductions);

            if (grossElement) {
                grossElement.value = gross.toFixed(2);
            }
            if (netElement) {
                netElement.value = net.toFixed(2);
            }

            toggleConditionalAccounts();
        }

        earningFields.concat(deductionFields).forEach(fieldName => {
            form.querySelector(`input[name="${fieldName}"]`)?.addEventListener('input', recalculateSalary);
        });

        toggleConditionalAccounts();
        recalculateSalary();
    })();
</script>
{{ Form::close() }}
