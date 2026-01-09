{{ Form::model($leavetype, array('route' => array('leavetype.update', $leavetype->id), 'method' => 'PUT')) }}
<div class="modal-body">
  <div class="row">
  <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Leave Type'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, array('class' => 'form-control', 'placeholder' => __('Enter Leave Type Name'), 
                )) }}
                @error('title')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
    <div class="col-md-6">
      <div class="form-group">
        {{ Form::label('days', __('Days Per Year'), ['class' => 'form-label']) }}
        {{ Form::number('days', null, array('class' => 'form-control', 'placeholder' => __('Enter Days / Year'))) }}
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        {{ Form::label('Status', __('Status'), ['class' => 'form-label']) }}
        <div class="d-flex justify-content-between radio-check">
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="paid" value="paid" name="Status" class="custom-control-input"
                   @if ($leavetype->status === 'paid')
                     checked
                   @endif
            >
            <label class="custom-control-label" for="paid">{{ __('Paid') }}</label>
          </div>
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="unpaid" value="unpaid" name="Status" class="custom-control-input"
                   @if ($leavetype->status !== 'paid')
                     checked
                   @endif
            >
            <label class="custom-control-label" for="unpaid">{{ __('Unpaid') }}</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
  <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>

{{ Form::close() }}
