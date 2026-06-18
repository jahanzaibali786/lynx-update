<div class="card" id="branch-box">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                    <select name="branch_id" class="form-control select" id="branch_id">
                        <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                    </select>
                </div>
                <div class="form-group text-end">
                    <a href="#" id="remove" class="text-danger">{{ __('Remove') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
