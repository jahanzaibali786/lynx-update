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
            {!! Form::label('end_date', __('End Date'), ['class' => 'form-label']) !!}
            {{ Form::date('end_date', $loan->loan_ended, ['class' => 'form-control', 'readonly' => 'readonly']) }}
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
    <div class="mt-4">
        <h6 class="mb-3">{{ __('Stop History') }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('To') }}</th>
                        <th>{{ __('Months') }}</th>
                        <th>{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loan->stopHistories as $history)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($history->stop_from_month)->format('M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($history->stop_to_month)->format('M Y') }}</td>
                            <td>{{ $history->months }}</td>
                            <td>{{ !empty($history->reason) ? $history->reason : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('No stop history found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
</div>
{{Form::close()}}
