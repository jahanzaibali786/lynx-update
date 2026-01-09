{{ Form::model($tax_slab, ['route' => ['tax-slab.update', $tax_slab->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        @php
            $currentYear = date('Y');
            $endYear = $currentYear + 1;
            $years = [];
            for ($year = 2016; $year <= $endYear; $year++) {
                $years[$year] = $year;
            }
        @endphp
        <div class="form-group col-md-6">
            {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
            {{ Form::select('year', $years, $years[$tax_slab->year], ['class' => 'form-control', 'required' => 'required']) }}
            @error('year')
                <small class="invalid-year" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('no', __('Slab No.'), ['class' => 'form-label']) }}
            {{ Form::number('no', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('no')
                <small class="invalid-no" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('lower_limit', __('Salary Slabs Lower Limit'), ['class' => 'form-label']) }}
            {{ Form::number('lower_limit', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('lower_limit')
                <small class="invalid-lower_limit" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('upper_limit', __('Salary Slabs Upper Limit'), ['class' => 'form-label']) }}
            {{ Form::number('upper_limit', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('upper_limit')
                <small class="invalid-upper_limit" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('fixed_tax_amount', __('Fixed Tax Amount'), ['class' => 'form-label']) }}
            {{ Form::number('fixed_tax_amount', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('fixed_tax_amount')
                <small class="invalid-fixed_tax_amount" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('prev_limit_percentage', __('Prev Limit %'), ['class' => 'form-label']) }}
            {{ Form::number('prev_limit_percentage', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('prev_limit_percentage')
                <small class="invalid-prev_limit_percentage" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
