<script>
    $(document).ready(function() {
        var latestGeneratedSalaryMonth = '{{ $latestGeneratedSalaryMonth }}';
        var latestGeneratedSalaryText = '{{ $latestGeneratedSalaryText }}';
        var paidFromWasEdited = true;

        function formatMonthValue(date) {
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return date.getFullYear() + '-' + month;
        }

        function formatDisplayDate(date) {
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function monthValueToDate(value) {
            return value ? new Date(value + '-01T00:00:00') : null;
        }

        function dateValueToDate(value) {
            return value ? new Date(value + 'T00:00:00') : null;
        }

        function validatePaidFromMonth(showMessage) {
            var paidFrom = $('#approval_paid_from').val();
            var isInvalid = latestGeneratedSalaryMonth && paidFrom && paidFrom <= latestGeneratedSalaryMonth;
            var message = 'From paid month salary already generated. Please select next month.';

            if (isInvalid) {
                $('#paid_from_error').text(latestGeneratedSalaryText ? message + ' Last salary: ' + latestGeneratedSalaryText + '.' : message);
                if (showMessage) {
                    show_toastr('error', message, 'error');
                }
                return false;
            }

            $('#paid_from_error').text('');
            return true;
        }

        function updateInstallmentPlan(autoSetPaidFrom) {
            var approvalDate = dateValueToDate($('#date').val());
            var loanAmount = parseFloat($('#approval_amount').val());
            var installments = parseInt($('#approval_installment').val());
            var paidFromDate = monthValueToDate($('#approval_paid_from').val());
            var $body = $('#installment_plan_body');
            var total = 0;

            $body.empty();
            $('#installment_plan_total').text('0.00');

            if (!approvalDate || isNaN(approvalDate.getTime()) || isNaN(loanAmount) || loanAmount <= 0 || isNaN(installments) || installments <= 0) {
                return;
            }

            if (autoSetPaidFrom && !paidFromWasEdited) {
                var nextMonth = new Date(approvalDate);
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                $('#approval_paid_from').val(formatMonthValue(nextMonth));
                paidFromDate = monthValueToDate($('#approval_paid_from').val());
            }

            if (!paidFromDate || isNaN(paidFromDate.getTime())) {
                return;
            }

            validatePaidFromMonth(false);

            var perMonthAmount = loanAmount / installments;

            for (var i = 1; i <= installments; i++) {
                var installmentDate = new Date(paidFromDate);
                installmentDate.setMonth(installmentDate.getMonth() + (i - 1));
                var installmentAmount = i === installments ? loanAmount - total : perMonthAmount;
                total += installmentAmount;

                $body.append(
                    '<tr>' +
                    '<td>' + i + '</td>' +
                    '<td>' + formatDisplayDate(installmentDate) + '</td>' +
                    '<td class="text-end">' + installmentAmount.toFixed(2) + '</td>' +
                    '</tr>'
                );
            }

            $('#installment_plan_total').text(total.toFixed(2));
        }

        $('#date').on('input change', function() {
            updateInstallmentPlan(true);
        });
        $('#approval_paid_from').on('input change', function() {
            paidFromWasEdited = true;
            updateInstallmentPlan(false);
            validatePaidFromMonth(true);
        });
        $('#approval_amount, #approval_installment').on('input change keyup', function() {
            updateInstallmentPlan(false);
        });
        updateInstallmentPlan(true);

     $('#approve').on('click', function(event) {
          event.preventDefault();
          var date = $('#date').val();
          var chart = $('#chart').val();
          var bank = $('#bank').val();
          var paymentMethod = $('#payment_method').val();
          var amount = parseFloat($('#approval_amount').val());
          var installment = parseInt($('#approval_installment').val());
          var paidFrom = $('#approval_paid_from').val();

          if (!date || !paidFrom || !chart || !bank || !paymentMethod || isNaN(amount) || amount <= 0 || isNaN(installment) || installment <= 0) {
              show_toastr('error', 'Please fill the required fields.', 'error');
              return;
          }
          if (!validatePaidFromMonth(true)) {
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
            {{ Form::select('bank_id',$bank_accounts,null, array('class' => 'form-control  custom-select', "id" => "bank")) }}
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 form-group">
            {{Form::label('account_id',__('Account'),array('class'=>'form-label')) }}<span class="text-danger pl-1"> *</span>
            <select name="account_id" class="form-control  custom-select" id="chart">
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
            {{ Form::label('date', __('Approval Date'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::date('date', \Carbon\Carbon::now()->format('Y-m-d'), array('class' => 'form-control ', 'id'=>'date')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('payment_method', __('Payment By'),['class'=>'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('payment_method', ['' => __('Select Payment'), 'online' => __('OL'), 'cheque' => __('CHQ'), 'cash' => __('CSH')], null, ['class' => 'form-control', 'required' => 'required', 'id' => 'payment_method']) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            {{ Form::number('amount',$loan->amount, array('class' => 'form-control','step'=>'0.01','min'=>'0.01','required'=>'required','id'=>'approval_amount')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            {{ Form::text('reference', '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('installment', __('Installment'),['class'=>'form-label']) }}
            {{ Form::number('installment',$loan->pay_period, array('class' => 'form-control','min'=>'1','required'=>'required','id'=>'approval_installment')) }}
        </div>
        <div class="form-group  col-md-6">
            {{ Form::label('paid_from', __('Paid From'),['class'=>'form-label']) }}
            {{ Form::month('paid_from', !empty($loan->from_pay_month) ? \Carbon\Carbon::parse($loan->from_pay_month)->format('Y-m') : null, array('class' => 'form-control','required'=>'required','id'=>'approval_paid_from')) }}
            <span class="text-danger" id="paid_from_error"></span>
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
    @php
        $payPeriod = (int) $loan->pay_period;
        $loanAmount = (float) $loan->amount;
        $perMonthAmount = $payPeriod > 0 ? $loanAmount / $payPeriod : 0;
        $runningTotal = 0;
    @endphp
    @if($payPeriod > 0)
        <div class="mt-4">
            <h6 class="mb-3">{{ __('Installment Plan') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Sr No.') }}</th>
                            <th>{{ __('Month') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody id="installment_plan_body">
                        <tr>
                            <td colspan="3" class="text-center">{{ __('Select approval details to view plan.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end"><strong>{{ __('Total') }}</strong></td>
                            <td class="text-end"><strong id="installment_plan_total">0.00</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
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
