{{ Form::model($healthInsurance, array('route' => array('health-insurance-plan.update', $healthInsurance->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, $healthInsurance->branch_id, ['class' => 'form-control select', 'readonly' => 'readonly', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
        @if (\Auth::user()->type != 'Employee')
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Select Employee'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
            {{ Form::select('employee_id', $employee, null, ['class' => 'form-control select', 'readonly' => 'readonly', 'required' => 'required', 'id' => 'employee_id']) }}
        </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('plan_name', __('Plan Name'), ['class' => 'form-label']) }}<span style="color: red">
                *</span>
            {{ Form::text('plan_name', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('plan_type', __('Plan Type'), ['class' => 'form-label']) }}<span style="color: red">
                *</span>
                {{ Form::select('plan_type', [''=>'Select Plan Type','insurance'=>'Insurance','health'=>'Health'], null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('plan_amount', __('Plan Amount'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::number('plan_amount', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        <div class="form-group col-md-4">
            {{ Form::label('plan_start', __('Plan Start'), ['class' => 'form-label']) }}<span style="color: red">
                *</span>
            {{ Form::date('plan_start', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('plan_end', __('Plan End'), ['class' => 'form-label']) }}<span style="color: red">
                *</span>
            {{ Form::date('plan_end', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
