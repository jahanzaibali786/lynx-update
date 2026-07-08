{{ Form::model($advance, ['route' => ['employee-advance.update', $advance->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @if($advance->status == 1)
        <div class="alert alert-info">
            {{ __('Approved advance can be edited by admin only until salary is generated.') }}
        </div>
    @endif
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
            {{ Form::text('branch_name', $branches[$advance->owned_by] ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
            {{ Form::hidden('branches', $advance->owned_by) }}
        </div>
        @if(\Auth::user()->type != 'Employee')
            <div class="form-group col-md-6">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('employee_name', $employee[$advance->employee_id] ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                {{ Form::hidden('employee_id', $advance->employee_id) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Advance Amount'), ['class' => 'form-label amount_label']) }}
            {{ Form::number('amount', $advance->advance_amount, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Advance Month'), ['class' => 'form-label amount_label']) }}
            {{ Form::month('date', \Carbon\Carbon::parse($advance->advance_date)->format('Y-m'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason')) }}
                {{ Form::textarea('reason', $advance->advance_reason, ['class' => 'form-control', 'required' => 'required', 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
