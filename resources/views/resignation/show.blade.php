{{Form::model($resignation, array('route' => array('resignation.approval', $resignation->id), 'method' => 'PUT')) }}
<div class="modal-body">
   {{-- //approval action or reject action  --}}
   <div class="row">
        <div class="form-group col-lg-12 col-md-12">
            {{Form::label('status', __('Status'), ['class' => 'form-label'])}}<span style="color: red">
                *</span>
            {{Form::select('status', [''=>'Select Action','1'=>'Approve','2'=>'Reject'], null, array('class' => 'form-control select', 'required' => 'required'))}}
        </div>
        <div class="form-group col-lg-12 col-md-12">
            {{Form::label('appr_rej_desc', __('Description'), ['class' => 'form-label'])}}
            {{Form::textarea('appr_rej_desc', null, array('class' => 'form-control', 'placeholder' => __('Enter Description')))}}
        </div>
   </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Save')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
