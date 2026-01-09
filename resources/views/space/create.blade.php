{{ Form::open(array('url' => 'space')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group row">
            <div class="col">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter Spacetype Name'),'required'=>'required')) }}
            </div>
            <div class="col">
                {{ Form::label('type_id', __('Space_type'), ['class' => 'form-label']) }}
                {{ Form::select('type_id', $spacetype, null, ['class' => 'form-control', 'placeholder' => __('Select Space Type'), 'required' => 'required']) }}    
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                {{ Form::label('capacity', __('Capacity'),['class'=>'form-label']) }}
                {{ Form::number('capacity', null, array('class' => 'form-control','placeholder'=>__('Enter Capacity'),'required'=>'required')) }}
            </div>
            <div class="col">
                {{ Form::label('price', __('Price'),['class'=>'form-label']) }}
                {{ Form::number('price', null, array('class' => 'form-control','placeholder'=>__('Enter Price'),'required'=>'required')) }}
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                {{ Form::label('meeting', __('Meeting'), ['class' => 'form-label']) }}
                {{ Form::select('meeting', ['no' =>'No','yes' =>'Yes'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="col">
                {{ Form::label('window', __('Window'), ['class' => 'form-label']) }}
                {{ Form::select('window', ['no' =>'No','yes' =>'Yes'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description'), 'rows' => 3, 'required' => 'required']) }}
        </div>

        @if(!$customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}


