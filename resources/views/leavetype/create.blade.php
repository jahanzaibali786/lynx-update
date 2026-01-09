<script>
    const paidRadio = document.getElementById('paid');
    const unpaidRadio = document.getElementById('unpaid');
    const daysField = document.querySelector('input[name="days"]');

    function toggleDaysField() {
        if (unpaidRadio.checked) {
            daysField.setAttribute('readonly', true);
            daysField.value = '';
        } else {
            daysField.removeAttribute('readonly');
        }
    }
    paidRadio.addEventListener('change', toggleDaysField);
    unpaidRadio.addEventListener('change', toggleDaysField);
    toggleDaysField();
</script>

{{ Form::open(array('url' => 'leavetype', 'method' => 'post')) }}
<div class="modal-body">

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('title', __('Leave Type'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, array('class' => 'form-control', 'placeholder' => __('Enter Leave Type Name'))) }}
                @error('title')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('Status', __('Status'), ['class' => 'form-label']) }}
                    <div class="d-flex justify-content-between radio-check">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="paid" value="paid" name="Status" class="custom-control-input">
                            <label class="custom-control-label" for="paid">{{ __('Paid') }}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="unpaid" value="unpaid" checked name="Status" class="custom-control-input">
                            <label class="custom-control-label" for="unpaid">{{ __('UnPaid') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('days', __('Days Per Year'), ['class' => 'form-label']) }}
                    {{ Form::number('days', null, array('class' => 'form-control', 'placeholder' => __('Enter Days / Year'))) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}