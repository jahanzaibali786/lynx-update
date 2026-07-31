{{ Form::open(['route' => 'chart-of-account-sub-category.store']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('type', __('Account Type'), ['class' => 'form-label']) }}
            {{ Form::select('type', $types, null, ['class' => 'form-control select', 'required' => 'required', 'placeholder' => __('Select Account Type')]) }}
            @error('type')
                <small class="invalid-type" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Sub Category Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required']) }}
            @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
        @if ($types->isEmpty())
            <div class="form-group col-md-12">
                <div class="alert alert-warning mb-0">
                    {{ __('Please create an account type first.') }}
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary" {{ $types->isEmpty() ? 'disabled' : '' }}>
</div>
{{ Form::close() }}
