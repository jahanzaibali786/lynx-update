{{Form::open(array('url'=>'employee-scale-heads','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('head',__('Salary Head'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{Form::text('head',null,array('class'=>'form-control','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('status',__('Status'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{ Form::select('status', ['1' => 'Active', '0' => 'IN-Active'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{Form::close()}}
