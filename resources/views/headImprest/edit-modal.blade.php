{{ Form::model($journalEntry, ['route' => ['expense-voucher.update', $journalEntry->id], 'method' => 'POST', 'enctype' => 'multipart/form-data', 'class' => 'ajax-modal-form expense-voucher-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                {{ Form::select('branches', $branches, $journalEntry->owned_by, ['class' => 'form-control select', 'id' => 'branches']) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('date', __('Transaction Date'), ['class' => 'form-label']) }}
                {{ Form::date('date', $journalEntry->date, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('bank_id', __('Bank Account (Head Imprest Only)'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::select('bank_id', $bankAccounts, $journalEntry->bank_id, ['class' => 'form-control select', 'id' => 'bank_id', 'required' => 'required', 'placeholder' => __('Select Bank Account')]) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('expense_account_id', __('Expense Account (COA Expense Head Only)'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::select('expense_account_id', $treeOptions, $selectedExpenseAccountId, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-4 col-md-4">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::number('amount', $journalEntry->amount, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'placeholder' => __('Enter Amount')]) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="form-group">
                {{ Form::label('payment_mode', __('Payment Mode'), ['class' => 'form-label']) }}
                <span class="text-danger">*</span>
                {{ Form::select('payment_mode', ['' => 'Select Payment Mode', 'dd' => 'DD', 'cd' => 'CD', 'bank-transfer' => 'Bank Transfer', 'chq' => 'Cheque', 'others' => 'Others'], $journalEntry->payment_mode, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="form-group">
                {{ Form::label('reference', __('Invoice No'), ['class' => 'form-label']) }}
                {{ Form::text('reference', $journalEntry->reference, ['class' => 'form-control', 'placeholder' => __('Enter Invoice No')]) }}
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12 col-md-12">
            <div class="form-group">
                {{ Form::label('attachment', __('Attachment'), ['class' => 'form-label']) }}
                {{ Form::file('attachment', ['class' => 'form-control']) }}
                <small class="text-muted">{{ __('Supported formats: PDF, Images, Document (Max: 5MB). Leave blank to keep current attachment.') }}</small>
                @if($journalEntry->attachment)
                    <div class="mt-2">
                        <a href="{{ \Storage::url($journalEntry->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="ti ti-download"></i> {{ __('View Current Attachment') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12 col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description / Narration'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', $journalEntry->description, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Narration')]) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-outline-primary ms-2">{{ __('Update Expense Voucher') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        if (typeof ajaxModalForm === 'function') {
            ajaxModalForm({
                formSelector: '.ajax-modal-form',
                onSuccess: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#commonModal').modal('hide');
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        toastr.error(response.message || "An error occurred.");
                    }
                },
                onError: function(xhr) {
                    var errorMsg = "An error occurred.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                }
            });
        }
    });
</script>
@include('headImprest.partials.bank-refresh', ['selectedBankId' => $journalEntry->bank_id])
