{{ Form::model(array('route' => array('concession.remove', $id))) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('cancel_date', __('Cancel Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
        {{ Form::date('cancel_date', null, ['class' => 'form-control', 'placeholder' => __('Cancel Date'), 'required' => 'required']) }}
    </div>
    <div class="form-group">
        {{ Form::text('id', $id, ['class' => 'form-control','hidden'=>'hidden']) }}
    </div>
    <div class="form-group">
        {{ Form::label('cancel_remarks', __('Cancel/Removal Remarks'), ['class' => 'form-label']) }}<span
            style="color: red"> *</span>
        {{ Form::textarea('cancel_remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Remarks'), 'rows' => 3]) }}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-secondary border border-secondary text-secondary" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Submit')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
