{!! Form::open(['route' => 'account-wise-fee.store', 'method' => 'POST']) !!}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('session_id', __('Session'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('session_id', $session, null, ['class' => 'form-control select','required' => 'required']) }}
            </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branches', $branches, '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'),['class'=>'form-label']) }}
                {{ Form::select('class_id', ['' => 'Select Class'], null, ['class' => 'form-control select','id' => 'class_create']) }}

            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('head_id', __('Head'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('head_id',$heads,null, ['class' => 'form-control select','id' => 'head_form', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('amount',null, ['class' => 'form-control', 'required' => 'required',]) }}
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Submit') }}" class="btn  btn-outline-primary">
    </div>


{{ Form::close() }}
