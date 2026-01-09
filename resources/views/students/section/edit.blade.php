
{{ Form::model($section, array('route' => array('section.update', $section->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter Section name'),'required'=>'required')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}


