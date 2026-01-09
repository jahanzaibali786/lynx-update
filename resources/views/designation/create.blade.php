{{ Form::open(['url' => 'designation', 'method' => 'post']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                {{ Form::select('department_id', $departments, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Designation Name')]) }}
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                {{ Form::label('code', __('Code'), ['class' => 'form-label']) }}
                {{ Form::text('code', null, ['class' => 'form-control', 'placeholder' => __('Enter Code')]) }}
                @error('code')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="form-group">
                <div class="btn-box">
                    {!! Form::label('rank', __('Ranks'), ['class' => 'form-label']) !!}
                    {{ Form::select(
                        'rank',
                        [
                            'Non-Managers' => 'Non-Managers',
                            'Managers' => 'Managers',
                        ],
                        '',
                        ['class' => 'form-control'],
                    ) }}
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('job_description', __('Job Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('job_description', null, ['class' => 'form-control', 'rows' => '4', 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
