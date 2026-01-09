{{ Form::open(array('url' => 'store')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Store Name'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::text('name', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::select('branch_id', $branches,null, array('class' => 'form-control ','required'=>'required')) }}
        </div>

        <div class="form-group col-md-6">
            {{Form::label('city',__('City'),array('class'=>'form-label')) }}<span class="text-danger pl-1">*</span>
            {{Form::text('city',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('city_zip',__('Zip Code'),array('class'=>'form-label')) }}
            {{Form::text('city_zip',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('address',__('Address'),array('class'=>'form-label')) }}<span class="text-danger pl-1">*</span>
            {{Form::textarea('address',null,array('class'=>'form-control','rows'=>1,'required'=>'required'))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
