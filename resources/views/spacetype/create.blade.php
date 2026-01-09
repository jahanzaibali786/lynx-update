{{ Form::open(array('url' => 'spacetype')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter Spacetype Name'),'required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tax_name', __('Tax Rate Name'),['class'=>'form-label']) }}
            {{ Form::text('tax_name', '', array('class' => 'form-control','placeholder'=>__('Tax Rate Name'),'required'=>'required', 'maxlength' => 20)) }}
            @error('tax_name')
            <small class="invalid-tax_name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('rate', __('Tax Rate %'),['class'=>'form-label']) }}
            {{ Form::number('rate', '', array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
            @error('rate')
            <small class="invalid-rate" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
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


