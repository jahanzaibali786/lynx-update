<script>
    $(document).ready(function() {
        function toggleVendorAdvanceReference() {
            var paymentMethod = $('#vendor_advance_payment_method').val();
            $('#vendor_advance_account_number_group, #vendor_advance_cheque_no_group').addClass('d-none');
            $('#vendor_advance_account_number, #vendor_advance_cheque_no').prop('required', false).val('');

            if (paymentMethod === 'online') {
                $('#vendor_advance_account_number_group').removeClass('d-none');
                $('#vendor_advance_account_number').prop('required', true);
            } else if (paymentMethod === 'cheque') {
                $('#vendor_advance_cheque_no_group').removeClass('d-none');
                $('#vendor_advance_cheque_no').prop('required', true);
            }
        }

        $('#vendor_advance_payment_method').on('change', toggleVendorAdvanceReference);
        toggleVendorAdvanceReference();
    });
</script>
{{ Form::model($advance, ['route' => ['vendor-advance.statusChange', $advance->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('vendor_name', __('Vendor'), ['class' => 'form-label']) }}
            {{ Form::text('vendor_name', $advance->vendor->name ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('advance_date', __('Advance Month'), ['class' => 'form-label']) }}
            {{ Form::text('advance_date_display', \Carbon\Carbon::parse($advance->advance_date)->format('M Y'), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('advance_amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::text('advance_amount_display', \Auth::user()->priceFormat($advance->advance_amount), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('approval_date', __('Approval Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::date('approval_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('bank_id', __('Bank Account'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control custom-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_method', __('Payment By'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::select('payment_method', ['' => __('Select Payment'), 'online' => __('OL'), 'cheque' => __('CHQ'), 'cash' => __('CSH')], null, ['class' => 'form-control', 'required' => 'required', 'id' => 'vendor_advance_payment_method']) }}
        </div>
        <div class="form-group col-md-6 d-none" id="vendor_advance_account_number_group">
            {{ Form::label('account_number', __('Account Number'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::text('account_number', null, ['class' => 'form-control', 'id' => 'vendor_advance_account_number']) }}
        </div>
        <div class="form-group col-md-6 d-none" id="vendor_advance_cheque_no_group">
            {{ Form::label('cheque_no', __('Cheque No'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::text('cheque_no', null, ['class' => 'form-control', 'id' => 'vendor_advance_cheque_no']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', null, ['class' => 'form-control', 'placeholder' => __('Optional for cash')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('advance_reason', __('Reason'), ['class' => 'form-label']) }}
            {{ Form::textarea('advance_reason', $advance->advance_reason, ['class' => 'form-control', 'rows' => 3, 'readonly' => 'readonly']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    @if($advance->status == 0)
        <button type="submit" name="status" value="1" class="btn btn-outline-primary">{{ __('Approve') }}</button>
        <button type="submit" name="status" value="2" class="btn btn-outline-danger" formnovalidate>{{ __('Reject') }}</button>
    @endif
</div>
{{ Form::close() }}
