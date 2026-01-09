{{ Form::model($branch, ['route' => ['branches.update', $branch->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Client Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('email', __('E-Mail Address'), ['class' => 'form-label']) }}
                {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Client Email'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('phone', __('Official Contact no'), ['class' => 'form-label']) }}
                {{ Form::tel('phone', @$school->phone_no, ['class' => 'form-control', 'placeholder' => __('i.e. PTCL')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('address', __('Official Address'), ['class' => 'form-label']) }}
                {{ Form::text('address', @$school->address, ['class' => 'form-control', 'placeholder' => __('Enter Branch Official Address')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('hod_name', __('HOD Name'), ['class' => 'form-label']) }}
                {{ Form::select('hod', $users, @$school->headmaster, ['class' => 'form-control', 'placeholder' => __('Select a User')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('hod_email', __('HOD Email'), ['class' => 'form-label']) }}
                {{ Form::email('hod_email', null, ['class' => 'form-control', 'placeholder' => __('Enter Hod Email')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('eobi_reg_no', __('EOBI Registration no'), ['class' => 'form-label']) }}
                {{ Form::text('eobi_reg_no', @$school->eobi_reg_no, ['class' => 'form-control', 'placeholder' => __('Enter EOBI Registration no')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('pessi_reg_no', __('PESSI Registration no'), ['class' => 'form-label']) }}
                {{ Form::text('pessi_reg_no', @$school->pessi_reg_no, ['class' => 'form-control', 'placeholder' => __('Enter PESSI Registration no')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('eobi_values', __('EOBI Values'), ['class' => 'form-label']) }}
                {{ Form::text('eobi_values', @$school->eobi_values, ['class' => 'form-control', 'placeholder' => __('Enter EOBI Values')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('pessi_values', __('PESSI Values'), ['class' => 'form-label']) }}
                {{ Form::text('pessi_values', @$school->pessi_values, ['class' => 'form-control', 'placeholder' => __('Enter PESSI Values')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('bank', __('Offical Bank'), ['class' => 'form-label']) }}
                {{ Form::select('bank', $bankAccount, @$school->bank, ['class' => 'form-control', 'placeholder' => __('Select a Offical Bank')]) }}
            </div>
        </div>
        @if (!$customFields->isEmpty())
            @include('custom_fields.formBuilder')
        @endif

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-outline-primary">
</div>

{{ Form::close() }}
