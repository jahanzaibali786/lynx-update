{{ Form::model($subcategories, array('route' => array('product-sub-category.update', $subcategories->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('name', __('SubCategory Name'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-12 d-block">
            {{ Form::label('category_id', __('Main Category'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('category_id',$categories,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('color', __('Sub Category Color'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::text('color', null, array('class' => 'form-control jscolor','required'=>'required')) }}
            <small>{{__('For chart representation')}}</small>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}

