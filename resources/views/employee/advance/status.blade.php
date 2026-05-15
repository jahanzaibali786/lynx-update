{{ Form::model($advance, ['route' => ['employee-advance.statusChange', $advance->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('employee_name', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('employee_name', !empty($advance->employee->name) ? $advance->employee->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('advance_date', __('Advance Month'), ['class' => 'form-label']) }}
            {{ Form::month('advance_date', \Carbon\Carbon::parse($advance->advance_date)->format('Y-m'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', $advance->advance_amount, ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('approval_date', __('Approval Date'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::date('approval_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'id' => 'advance_approval_date', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('bank_id', __('Bank Account'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control custom-select', 'id' => 'advance_bank']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_method', __('Payment By'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::select('payment_method', ['' => __('Select Payment'), 'online' => __('OL'), 'cheque' => __('CHQ'), 'cash' => __('CSH')], null, ['class' => 'form-control', 'required' => 'required', 'id' => 'advance_payment_method']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('account_id', __('Account'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            <select name="account_id" class="form-control custom-select" id="advance_chart">
                <option value="" selected disabled>{{ __('Select Account') }}</option>
                @foreach ($accounts as $chartAccount)
                    <option value="{{ $chartAccount['id'] }}">{{ $chartAccount['code'] . ' - ' . $chartAccount['name'] }}</option>
                    @foreach ($subAccounts as $subAccount)
                        @if ($chartAccount['id'] == $subAccount['parent'])
                            <option value="{{ $subAccount['id'] }}"> &nbsp; &nbsp;&nbsp; {{ $subAccount['code'] . ' - ' . $subAccount['name'] }}</option>
                        @endif
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', null, ['class' => 'form-control']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('advance_reason', __('Reason'), ['class' => 'form-label']) }}
                {{ Form::textarea('advance_reason', $advance->advance_reason, ['class' => 'form-control', 'rows' => 3, 'readonly' => 'readonly']) }}
            </div>
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
