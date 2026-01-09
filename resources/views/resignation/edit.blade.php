{{Form::model($resignation, array('route' => array('resignation.update', $resignation->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, $resignation->branch_id, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)','readonly'=>'readonly']) }}
        </div>
        @if(\Auth::user()->type != 'Employee')
            <div class="form-group col-lg-6 col-md-6">
                {{Form::label('employee_id', __('Employee'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                {{Form::select('employee_id', $employees, null, array('class' => 'form-control select', 'required' => 'required','readonly'=>'readonly', 'id' => 'employee_id', 'placeholder' => __('Select Employee')))}}
            </div>
        @endif
        <div class="form-group col-lg-4 col-md-4">
            {{Form::label('last_attendance_date', __('Last Attendace Date'), ['class' => 'form-label'])}}<span style="color: red">
            *</span>
            {{Form::date('last_attendance_date', null, array('class' => 'form-control'))}}
        </div>
        <div class="form-group col-lg-4 col-md-4">
            {{Form::label('notice_date', __('Notice Date'), ['class' => 'form-label'])}}
            {{Form::date('notice_date', null, array('class' => 'form-control '))}}
        </div>
        <div class="form-group col-lg-4 col-md-4">
            {{Form::label('resignation_date', __('Resignation Date'), ['class' => 'form-label'])}}<span style="color: red">
            *</span>
            {{Form::date('resignation_date', null, array('class' => 'form-control '))}}
        </div>

        <div class="form-group col-lg-12">
            {{Form::label('description', __('Description'), ['class' => 'form-label'])}}
            {{Form::textarea('description', null, array('class' => 'form-control', 'placeholder' => __('Enter Description')))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
