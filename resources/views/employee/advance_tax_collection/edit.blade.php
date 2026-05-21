{{ Form::model($collection, ['route' => ['advance-tax-collection.update', $collection->id], 'method' => 'PUT']) }}
<div class="modal-body">
    @if($collection->status == 1)
        <div class="alert alert-info">
            {{ __('Approved advance tax collection can be edited by admin only.') }}
        </div>
    @endif
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::text('branch_name', \Auth::user()->getbranch(@$collection->employee->branch_id) ? \Auth::user()->getbranch(@$collection->employee->branch_id)->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::text('employee_name', @$collection->employee->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tax_month', __('Tax Month'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::month('tax_month', \Carbon\Carbon::parse($collection->tax_month)->format('Y-m'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('collection_date', __('Collection Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::date('collection_date', $collection->collection_date, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::number('amount', $collection->amount, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
            {{ Form::select('payment_method', ['' => __('Select Payment Method')] + $paymentMethods, $collection->payment_method, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', $collection->reference, ['class' => 'form-control', 'maxlength' => 191]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
            {{ Form::textarea('remarks', $collection->remarks, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
