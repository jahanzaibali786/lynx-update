{{ Form::model(array('route' => array('concession.updateend', $id))) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
        {{ Form::date('end_date', null, ['class' => 'form-control', 'placeholder' => __('End Date'), 'required' => 'required']) }}
    </div>
    <div class="form-group">
        {{ Form::text('id', $id, ['class' => 'form-control','hidden'=>'hidden']) }}
    </div>
    <div class="form-group">
        {{ Form::label('end_remarks', __('End Concession Remarks'), ['class' => 'form-label']) }}<span
            style="color: red"> *</span>
        {{ Form::textarea('end_remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Remarks'), 'rows' => 3, 'required' => 'required']) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-secondary border border-secondary text-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Submit')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
