{{ Form::open(['url' => 'employee_scale', 'method' => 'post', 'id' => 'employeeScaleForm']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::select('department_id', $department, null, ['class' => 'form-control select', 'required' => 'required' ,'id' =>'dep_id']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('scale_no', __('Scale No'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('scale_no', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        @foreach ($heads as $account)
            <div class="col-md-6">
                <div class="form-group">
                    <input type="hidden" name="account_id[]" value="{{ $account->id }}">
                    <label class="form-label">{{ !empty($account->head) ? $account->head : '-' }}<span style="color: red">
                            *</span></label>
                    <input type="number" name="account_value[]" placeholder="0" value="0" class="form-control" min="0">
                </div>
            </div>
        @endforeach
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('effect_from', __('Effect From'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::date('effect_from', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required' ,'id' => 'effect_id']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('adhoc', __('Is Adhoc'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('adhoc', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control', 'required' => 'required','id' => 'is_adhoc']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('status', ['1' => 'Active', '0' => 'IN-Active'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <a class="btn mx-1 btn-sm btn-outline-light" href="{{route('employee_scale.index')}}">
        <span class="btn-inner--icon">Cancel</span>
    </a>
    <a class="btn mx-1 btn-sm btn-outline-primary" onclick="submitscale()">
        <span class="btn-inner--icon">Create</span>
    </a>
</div>
{{ Form::close() }}