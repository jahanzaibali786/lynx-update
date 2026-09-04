{{ Form::open(['url' => 'loan', 'loan' => $loan]) }}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-4">
            {!! Form::label('branch', __('Branch'), ['class' => 'form-label']) !!}
            {{ Form::text('branch', \Auth::user()->getBranch($employee->branch_id) ? \Auth::user()->getBranch($employee->branch_id)->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('apply_date', __('Apply Date'), ['class' => 'form-label']) !!}
            {{ Form::date('apply_date', $loan->apply_date, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('end_date', __('End Month'), ['class' => 'form-label']) !!}
            {{ Form::text('end_date', !empty($loan->loan_ended) ? \Carbon\Carbon::parse($loan->loan_ended)->format('M Y') : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('employee_name', __('Employee Name'), ['class' => 'form-label']) !!}
            {{ Form::text('employee_name', !empty($employee->name) ? $employee->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('department', __('Department'), ['class' => 'form-label']) !!}
            {{ Form::text('department', !empty($employee->department->name) ? $employee->department->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('service_tenure', __('Service Tenure'), ['class' => 'form-label']) !!}
            {!! Form::text('service_tenure', $loan->service_tenure, [
            'class' => 'form-control',
            'readonly' => 'readonly'
            ]) !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('emp_security', __('Security'), ['class' => 'form-label']) !!}
            {!! Form::number('emp_security', $loan->emp_sec, [
            'class' =>
            'form-control',
            'readonly' => 'readonly'
            ])
            !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('maxamount', __('Maximum Amount'), ['class' => 'form-label']) !!}
            {!! Form::number('maxamount', $loan->maxamount, [
            'class' =>
            'form-control',
            'readonly' => 'readonly'
            ])
            !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('amount', __('Loan Amount') . ' ', ['class' => 'form-label']) !!}
            <span style="color: {{ @$loan->status == 0 ? 'orange' : ($loan->status == 2 ? 'red' : 'green')}}">
                ({{ $loan->status == 0 ? 'Pending' : ($loan->status == 2 ? 'Rejected' : 'Approved') }})
            </span>
            {!! Form::number('amount', $loan->amount, [
            'class' =>
            'form-control',
            'readonly' => 'readonly'
            ])
            !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
</div>
{{Form::close()}}
