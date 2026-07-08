<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('employee', @$collection->employee->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
            {{ Form::text('branch', \Auth::user()->getbranch(@$collection->employee->branch_id) ? \Auth::user()->getbranch(@$collection->employee->branch_id)->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tax_month', __('Tax Month'), ['class' => 'form-label']) }}
            {{ Form::text('tax_month', \Carbon\Carbon::parse($collection->tax_month)->format('M Y'), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('collection_date', __('Collection Date'), ['class' => 'form-label']) }}
            {{ Form::text('collection_date', \Carbon\Carbon::parse($collection->collection_date)->format('d-M-Y'), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::text('amount', number_format($collection->amount, 2), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
            {{ Form::text('payment_method', ucfirst($collection->payment_method), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', $collection->reference, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('proof_picture', __('Proof Attachment'), ['class' => 'form-label']) }}
            <div>
                @if(!empty($collection->proof_picture))
                    <a href="{{ route('advance-tax-collection.proof', $collection->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        {{ __('View Proof') }}
                    </a>
                    <small class="d-block text-muted mt-1">{{ basename($collection->proof_picture) }}</small>
                @else
                    {{ Form::text('proof_picture', '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                @endif
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::text('status', \App\Models\AdvanceTaxCollection::$statuses[$collection->status] ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('approval_date', __('Approval Date'), ['class' => 'form-label']) }}
            {{ Form::text('approval_date', !empty($collection->approval_date) ? \Carbon\Carbon::parse($collection->approval_date)->format('d-M-Y') : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('approved_by', __('Approved By'), ['class' => 'form-label']) }}
            {{ Form::text('approved_by', !empty($collection->approvedBy->name) ? $collection->approvedBy->name : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
            {{ Form::textarea('remarks', $collection->remarks, ['class' => 'form-control', 'readonly' => 'readonly', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
</div>
