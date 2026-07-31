{{ Form::model($chartOfAccountSubType, ['route' => ['chart-of-account-sub-category.update', $chartOfAccountSubType->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('type', __('Account Type'), ['class' => 'form-label']) }}
            {{ Form::text('type', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Sub Category Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
            @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
            @enderror
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
