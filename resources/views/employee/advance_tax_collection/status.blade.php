{{ Form::model($collection, ['route' => ['advance-tax-collection.statusChange', $collection->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('employee_name', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('employee_name', !empty($collection->employee->name) ? $collection->employee->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
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
            {{ Form::text('payment_method', $collection->payment_method ? ucfirst($collection->payment_method) : '-', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('approval_date', __('Approval Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::date('approval_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
            {{ Form::textarea('remarks', $collection->remarks, ['class' => 'form-control', 'readonly' => 'readonly', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    @if($collection->status == 0)
        <button type="submit" name="status" value="1" class="btn btn-outline-primary">{{ __('Approve') }}</button>
        <button type="submit" name="status" value="2" class="btn btn-outline-danger" formnovalidate>{{ __('Reject') }}</button>
    @endif
</div>
{{ Form::close() }}
