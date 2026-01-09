{{ Form::model($asset, array('route' => array('account-assets.update', $asset->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-12">
            {{ Form::label('company_id', __('Company'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('company_id', $company,null, array('class' => 'form-control select2','placeholder'=>'','id'=>'choices-multiple1')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::date('issue_date',@$asset->purchase_date, array('class' => 'form-control ' ,'required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'),['class'=>'form-label']) }}
            {{ Form::date('end_date',@$asset->supported_date, array('class' => 'form-control ')) }}
        </div>
        @if (@$asset)
        <div class="col-12 row field_list">
            <div class="row">
            <div class="form-group col-md-6">
                {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('quantity', __('Quantity'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            </div>
            <div class="form-group col-md-2">
                <a href="#" class="sm btn btn-primary add_fields"  data-bs-title="{{__('Add Fields')}}"> Create {{__('Add')}}
                </a>
            </div>
            </div>
            @foreach ($asset->assetdetail as $item)
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::hidden('ids[]', $item->id, [ 'class' => 'form-control', 'required', ]) !!}
                        {{ Form::text('names[]', $item->name, array('class' => 'form-control','required'=>'required')) }}
                    </div>
                    <div class="form-group col-md-4">
                        {{ Form::number('quantitys[]', $item->quantity, array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}
                    </div>
                    <div class="col-md-2">
                        <a href="#" class="sm btn btn-danger remove_field mb-1"  data-bs-title="{{__('Remove Fields')}}"> <i class="ti ti-trash"></i> {{__('Add')}} </a>
                    </div>
                </div>
            @endforeach
                
        </div>
        @endif
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', null, array('class' => 'form-control','rows'=>3)) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{ Form::close() }}
<script>
    $(document).on('click', '.add_fields', function() {
            var fieldHTML = '';
            fieldHTML +=
                `<div class="row">
                    <div class=" col-md-6">
                        {{ Form::text('name[]', '', array('class' => 'form-control mb-1','placeholder'=>'Asset Name','required'=>'required')) }}
                    </div>
                    <div class=" col-md-4">
                        {{ Form::number('quantity[]', '', array('class' => 'form-control mb-1','placeholder'=>'Asset Quantity','required'=>'required','step'=>'0.01')) }}
                    </div>
                    <div class="col-md-2">
                        <a href="#" class="sm btn btn-danger remove_field mb-1"  data-bs-title="{{__('Remove Fields')}}"> <i class="ti ti-trash"></i> {{__('Add')}} </a>
                    </div>
                </div>`
            $('.field_list').append(fieldHTML); //Add field html	
    
      });
      $(document).on('click', '.remove_field', function() {
        $(this).parent().parent().remove(); //Remove field html
      });
    </script>