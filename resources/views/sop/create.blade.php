{{ Form::open(['url' => 'sops', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-4 col-lg-4">
            <div class="form-group">
                {{ Form::label('sop_title', __('Title')) }}
                {{ Form::text('sop_title', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="form-group">
                {{ Form::label('sop_type', __('Type')) }}
                {{ Form::select('sop_type', ['' => 'Select Type', 'regular' => 'Regular', 'adhoc' => 'AdHoc', 'visiting' => 'Visiting'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="form-group">
                {{ Form::label('date', __('Date')) }}
                {{ Form::date('date', now(), ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12 col-lg-12">
        <div class="form-group">
            {{ Form::label('description', __('Description')) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3']) }}
        </div>
        </div>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}
