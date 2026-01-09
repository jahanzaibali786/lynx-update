{{ Form::model($emp, array('route' => array('emp-eobi-allocation.update', $emp->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('emp_id', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('emp_id',$emp->name, ['class' => 'form-control', 'required' => 'required','readonly'=>'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('eobi', __('EOBI'), ['class' => 'form-label']) }}
            {{ Form::text('eobi', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('eobi_employer', __('Eobi Employer'), ['class' => 'form-label']) }}
            {{ Form::text('eobi_employer', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('pessi', __('Pessi'), ['class' => 'form-label']) }}
            {{ Form::text('pessi', null, ['class' => 'form-control' ]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('pessi_employer', __('Pessi Employer'), ['class' => 'form-label']) }}
            {{ Form::text('pessi_employer', null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
