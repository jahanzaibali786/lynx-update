<script>
    $(document).ready(function() {

     $('#approve').on('click', function(event) {
          event.preventDefault();
          var date = $('#date').val();
          var chart = $('#chart').val();
          var bank = $('#bank').val();

          if (!date || !chart || !bank) {
              show_toastr('error', 'Please fill the required fields.', 'error');
              return;
          }
          $('#stat').val('1');
         $(this).closest('form').submit();
     });
     $('#reject').on('click', function(event) {
         event.preventDefault();
         $('#stat').val('2');
         $(this).closest('form').submit();
     });

 });

</script>
<script>
      $(document).ready(function() {
        $('.selectbox').select2({
            placeholder: "Select accounts",
            allowClear: true
        });
   });

</script>
{{ Form::model($loan, array('route' => array('loan.loanstatuschange', $loan->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group  col-lg-6 col-md-6 col-sm-6">
            {{ Form::label('bank_id', __('Bank Account'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('bank_id',$bank_accounts,null, array('class' => 'form-control select', "id" => "bank")) }}
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
            {{Form::label('account_id',__('Account'),array('class'=>'form-label')) }}<span class="text-danger pl-1"> *</span>
            <select name="account_id" class="form-control selectbox" id="chart">
                <option value="" selected disabled>Select Account</option>
                @foreach ($accounts as $chartAccount)
                    <option value="{{ $chartAccount['id'] }}" class="subAccount" >{{$chartAccount['code'] .' - '. $chartAccount['name'] }}</option>
                    @foreach ($subAccounts as $subAccount)
                        @if ($chartAccount['id'] == $subAccount['account'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5" > &nbsp; &nbsp;&nbsp; {{ $subAccount['code'] .' - '. $subAccount['name'] }}</option>
                        @endif
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::date('date', '', array('class' => 'form-control ', 'id'=>'date')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Form::number('amount',$loan->amount, array('class' => 'form-control','readonly'=>'readonly','step'=>'0.01')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Form::text('reference', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('installment', __('Installment'),['class'=>'form-label']) }}
            {{ Form::number('installment',$loan->pay_period, array('class' => 'form-control','readonly'=>'readonly',)) }}
        </div>

        <input type="hidden" name="status" value="" id="stat">
        {{-- <div>
            <label>
                {{ Form::radio('status', 1, $loan->status == 1) }} {{ __('Approve') }}
            </label>
        </div>
        <div>
            <label>
                {{ Form::radio('status', 2, $loan->status == 2) }} {{ __('Reject') }}
            </label>
        </div> --}}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    {{-- <input type="submit" value="{{ __('Save') }}" class="btn btn-primary"> --}}
    @if($loan->status == 0)
        <input type="submit" value="{{__('Approve')}}" id="approve" style="margin-right: 10px;" class="btn  btn-outline-primary">
        <input type="submit" value="{{__('Reject')}}" id="reject" class="btn btn-outline-danger">
    @endif
</div>
{{ Form::close() }}