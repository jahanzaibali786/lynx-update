{{ Form::open(array('route' => array('purchase.payment', $purchase->id),'method'=>'post','enctype' => 'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
            {{ Form::date('date', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Form::number('amount',$purchase->getDue(), array('class' => 'form-control', 'id'=>'amount','required'=>'required','step'=>'0.01')) }}
        </div>
        <input type="hidden" name="" value="{{$purchase->getDue()}}" id="full_val">
        <div class="form-group  col-md-6">
            {{ Form::label('account_id', __('Account'),['class'=>'form-label']) }}
            {{ Form::select('account_id',$accounts,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>

        <div class="form-group  col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Form::text('reference', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group  col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
        </div>


        <div class="col-md-6 form-group">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            <div class="choose-file ">
                <label for="file" class="form-label">
                    <input type="file" name="add_receipt" id="image" class="form-control"  >
                </label>
                <p class="upload_file"></p>

            </div>
        </div>


    </div>
    <div class="modal-footer">

        <input type="button" value="{{__('Cancel')}}" class="btn btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Add')}}" class="btn  btn-outline-primary">
    </div>

</div>

{{ Form::close() }}

<script>
 $(document).on('keyup', '#amount', function () {
    var amount = parseInt($('#amount').val(), 10);
    var val = parseInt($('#full_val').val(), 10);
    if (amount > val) {
        $('#amount').val(val);
    }
});
</script>