{{Form::open(array('url' => 'employee-rejoin_post', 'method' => 'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, $emp->branch_id, ['class' => 'form-control select', 'readonly' => 'readonly', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
            <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
            {{ Form::text('employee_name', $emp->name, ['class' => 'form-control', 'readonly' => 'readonly', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
            <input type="hidden" name="employee_id" value="{{ $emp->id }}" id="employee_id">
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('old_department', __('Previous Department'), ['class' => 'form-label']) }}
            {{ Form::text('old_department_name', $emp->department->name ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
            <input type="hidden" name="old_department" value="{{ $emp->department_id }}" id="old_department">
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('old_designation', __('Previous Designation'), ['class' => 'form-label']) }}
            {{ Form::text('old_designation_name', $emp->designation->name ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
            <input type="hidden" name="old_designation" value="{{ $emp->designation_id }}" id="old_designation">
        </div>
         <div class="form-group col-md-6">
            {{ Form::label('new_department', __('New Department'), ['class' => 'form-label']) }}
            {{ Form::select('new_department', $departments,null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('new_designation', __('New Designation'), ['class' => 'form-label']) }}
            {{ Form::select('new_designation',$designation,null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('rejoin_date', __('Rejoin Date'), ['class' => 'form-label']) }}
            {{ Form::date('rejoin_date',null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
