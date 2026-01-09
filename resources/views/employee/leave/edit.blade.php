{{ Form::model($emp_leave, array('route' => array('emp-leaves.update', $emp_leave->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('emp_id', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('emp_id',$emp_leave->employee->name, ['class' => 'form-control', 'required' => 'required','readonly'=>'readonly']) }}
        </div>
        
        <div class="form-group col-md-6">
            {{ Form::label('casual_consumed', __('Casual Consumed'), ['class' => 'form-label']) }}
            {{ Form::text('casual_consumed', null, ['class' => 'form-control', 'required' => 'required','readonly'=>'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('annual_consumed', __('Annual Consumed'), ['class' => 'form-label']) }}
            {{ Form::text('annual_consumed', null, ['class' => 'form-control', 'required' => 'required','readonly'=>'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('casual_total', __('Casual Total'), ['class' => 'form-label']) }}
            {{ Form::text('casual_total', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('annual_total', __('Annual Total'), ['class' => 'form-label']) }}
            {{ Form::text('annual_total', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
