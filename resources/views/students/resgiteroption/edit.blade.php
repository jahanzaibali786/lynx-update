{{ Form::model($registerOption, array('route' => array('registerOption.update', $registerOption->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span style="color: red"> *</span>
        {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Name'),'required'=>'required')) }}
    </div>
    <div class="form-group">
        {{ Form::label('discount', __('Value'),['class'=>'form-label']) }}<span style="color: red"> *</span>
        {{ Form::text('discount', null, array('class' => 'form-control','placeholder'=>__('Value'),'required'=>'required')) }}
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
