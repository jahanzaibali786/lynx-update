{{ Form::model($employeesalary, ['route' => ['emp-month-sal-attendance.update', $employeesalary->id], 'method' => 'PUT']) }}
@php
    $salaryEditable = $salaryEditable ?? false;
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
@endphp
<div class="modal-body">
    @if (!$salaryEditable)
        <div class="alert alert-warning mb-3">
            {{ __('This salary can be edited only while it is unpaid and not GM finalized.') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-3"><b>Emp Code</b> : {{@$employeesalary->employee->id}} </div>
        <div class="col-md-3"><b>Name</b> : {{@$employeesalary->employee->name}}</div>
        <div class="col-md-3"><b>Father Name</b> : {{@$employeesalary->employee->f_name}}</div>
        <div class="col-md-3"><b>Dept.</b>: {{@$employeesalary->employee->department->name}}</div>
        <div class="col-md-3"><b>Branch</b>:
            {{!empty(\Auth::user()->getBranch(@$employeesalary->employee->branch_id ))?\Auth::user()->getBranch(@$employeesalary->employee->branch_id )->name:''}}
        </div>
        <div class="col-md-3"><b>Area</b>: {{@$employeesalary->employee->area}}</div>
        <div class="col-md-3"><b>Date</b>: {{@$employeesalary->salary_date}}</div>
        <div class="col-md-3"><b>Paid Through</b>: {{!empty($lastPayscaleDetail) ? $lastPayscaleDetail->paymode : 'HBL'}}
        </div>
        <div class="col-md-3"><b>Bank</b>:
            {{!empty($employeesalary->bank) ? $employeesalary->bank : 'HBL HEAD OFFICE MAIN'}}</div>
        <div class="col-md-3"><b>Cheque</b>: {{!empty($employeesalary->cheque) ? $employeesalary->cheque : ''}}</div>
    </div>
    <hr>
    @php
    $lastPayscaleDetail = @$employeesalary->employee->employee_payscale_details->last();
    @endphp
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
            {{ Form::text('other_dedu_payable_account',  !empty($lastPayscaleDetail->other_dedu_payable_account) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->other_dedu_payable_account)->first()->name : '', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance', __('Advance'), ['class' => 'form-label']) !!}
            {{ Form::number('advance',  !empty($employeesalary) ? $employeesalary->sal_advance : '',  ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('advance_payable_account', __('Advance Payable Account'), ['class' => 'form-label']) !!}
            {{ Form::text('advance_payable_account',  !empty($lastPayscaleDetail->advance_payable_account) ? \App\Models\ChartOfAccount::where('id', $lastPayscaleDetail->advance_payable_account )->first()->name: '',  ['class' => 'form-control']) }}
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
        }

        earningFields.concat(deductionFields).forEach(fieldName => {
            form.querySelector(`input[name="${fieldName}"]`)?.addEventListener('input', recalculateSalary);
        });

        recalculateSalary();
    })();
</script>
{{ Form::close() }}
