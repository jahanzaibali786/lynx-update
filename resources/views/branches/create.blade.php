{{ Form::open(['url' => 'branches']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('b_code', __('Branch Code'), ['class' => 'form-label']) }}
                {{ Form::text('b_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Code'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('email', __('E-Mail Address'), ['class' => 'form-label']) }}
                {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Email'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter User Password'), 'required' => 'required', 'minlength' => '6']) }}
                @error('password')
                    <small class="invalid-password" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('phone', __('Official Contact no'), ['class' => 'form-label']) }}
                {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('i.e. PTCL')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('address', __('Official Address'), ['class' => 'form-label']) }}
                {{ Form::text('address', null, ['class' => 'form-control', 'placeholder' => __('Enter Branch Official Address')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('hod_name', __('HOD Name'), ['class' => 'form-label']) }}
                {{ Form::select('hod', $users, null, ['class' => 'form-control', 'placeholder' => __('Select a User')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('hod_email', __('HOD Email'), ['class' => 'form-label']) }}
                {{ Form::text('hod_email', null, ['class' => 'form-control', 'placeholder' => __('Enter Hod Email')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('eobi_reg_no', __('EOBI Registration no'), ['class' => 'form-label']) }}
                {{ Form::text('eobi_reg_no', null, ['class' => 'form-control', 'placeholder' => __('Enter EOBI Registration no')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('pessi_reg_no', __('PESSI Registration no'), ['class' => 'form-label']) }}
                {{ Form::text('pessi_reg_no', null, ['class' => 'form-control', 'placeholder' => __('Enter PESSI Registration no')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('eobi_values', __('EOBI Value'), ['class' => 'form-label']) }}
                {{ Form::text('eobi_values', null, ['class' => 'form-control', 'placeholder' => __('Enter EOBI Value')]) }}
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="form-group">
                {{ Form::label('pessi_values', __('PESSI Value'), ['class' => 'form-label']) }}
                {{ Form::text('pessi_values', null, ['class' => 'form-control', 'placeholder' => __('Enter PESSI Value')]) }}
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
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-outline-primary">
</div>

{{ Form::close() }}
