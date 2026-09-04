{{ Form::model($advance, ['route' => ['vendor-advance.update', $advance->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('vender_id', __('Vendor'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::select('vender_id', $vendors, $advance->vender_id, ['class' => 'form-control custom-select', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('advance_amount', __('Advance Amount'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::number('advance_amount', $advance->advance_amount, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('advance_date', __('Advance Month'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::month('advance_date', \Carbon\Carbon::parse($advance->advance_date)->format('Y-m'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('advance_reason', __('Reason'), ['class' => 'form-label']) }}
            {{ Form::textarea('advance_reason', $advance->advance_reason, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
