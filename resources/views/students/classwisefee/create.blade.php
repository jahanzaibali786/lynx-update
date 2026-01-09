
{{ Form::open(array('url' => 'class_wise_fee')) }}
<div class="modal-body">
    <div class="row">
        {{ Form::hidden('class_id', $id, array('class' => 'form-control','placeholder'=>__('Enter Price'),'required'=>'required')) }}
        <div class="form-group">
            {{ Form::label('fee_head', __('Fee Head'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('fee_head', $fee_heads, null, ['class' => 'form-control sec js-searchBox', 'required' => 'required', 'placeholder' => 'Select Fee Head...']) }}
        </div>
        <div class="form-group">
            {{ Form::label('price', __('Price'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('price', null, array('class' => 'form-control','placeholder'=>__('Enter Price'),'required'=>'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('session', __('Session'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('session', $sections, null, ['class' => 'form-control js-searchBox', 'required' => 'required', 'placeholder' => 'Select session...']) }}

        </div>
    
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}
<script>
    
$(document).ready(function() {
    if ($(".sec").length > 0) {
        $($(".sec")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
});
</script>


