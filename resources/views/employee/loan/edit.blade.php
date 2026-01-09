<script>
$(document).ready(function() {
    $('#employee_id').change(function() {
        var employeeId = $(this).val();
        if(employeeId) {
            $.ajax({
                url: '{{ url('get-employee-serv-sec') }}/' + employeeId,
                type: 'GET',
                success: function(response) {
                    if(response) {
                        $('#service_tenure').val(response.service_tenure);
                        $('#department').val(response.emp_department);
                        $('#total_sec').val(response.total_sec);
                        $('#max_amount').val((response.total_sec) / 2);
                        if (isNaN(response.total_sec) || response.total_sec <= 0) {
                            $('#submit_btn').prop('disabled', true);
                        } else {
                            $('#submit_btn').prop('disabled', false);
                        }
                    } else {
                        $('#service_tenure').val('0');
                        $('#total_sec').val('0');
                        $('#submit_btn').prop('disabled', true);
                    }
                },
                error: function() {
                    $('#service_tenure').val('');
                    $('#total_sec').val('');
                    $('#submit_btn').prop('disabled', true);
                }
            });
        } else {
            $('#service_tenure').val('');
            $('#total_sec').val('');
            $('#submit_btn').prop('disabled', true);
        }
    });

    $('#loan_amount').on('input', function() {
        var loanAmount = parseFloat($(this).val());
        var totalSecurity = parseFloat($('#total_sec').val());

        if (isNaN(totalSecurity) || totalSecurity <= 0) {
            $('#loan_error').text('(Security amount must be greater than 0 to take a loan.)');
            $('#submit_btn').prop('disabled', true);
        } else if (loanAmount > totalSecurity / 2) {
            $('#loan_error').text('(Loan amount cannot exceed ' + totalSecurity / 2 + ')');
            $('#submit_btn').prop('disabled', true);
        } else {
            $('#loan_error').text('');
            $('#submit_btn').prop('disabled', false);
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('from_pay_month').setAttribute('min', today);
});

document.getElementById('pay_date').addEventListener('keyup', function() {
    updateProbationEndDate();
});

document.getElementById('from_pay_month').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
    var today = new Date();

    if (selectedDate < today) {
        alert('Please select today or a future date.');
        this.value = today.toISOString().split('T')[0];
    }

    updateProbationEndDate();
});

function updateProbationEndDate() {
    var p_id = parseInt($('#pay_date').val());
    var currentDate = new Date($('#from_pay_month').val());
    
    if (!isNaN(currentDate.getTime())) {
        if (isNaN(p_id)) {
            var formattedDate = currentDate.toISOString().slice(0, 10);
            document.getElementById('loan_ended').value = formattedDate;
        } else {
            var futureDate = new Date(currentDate.setMonth(currentDate.getMonth() + p_id));
            var formattedDate = futureDate.toISOString().slice(0, 10);
            document.getElementById('loan_ended').value = formattedDate;
        }
    }
}
</script>
{{ Form::model($loan, array('route' => array('loan.update', $loan->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'readonly' => 'readonly', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Select Employee'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
            {{ Form::select('employee_id', $employee, null, ['class' => 'form-control select', 'readonly' => 'readonly', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
            {{ Form::text('department', null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'department']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('service_tenure', __('Service Tenure'), ['class' => 'form-label']) !!}
            {!! Form::text('service_tenure', null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'service_tenure']) !!}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('emp_sec', __('Security'), ['class' => 'form-label']) !!}
            {!! Form::number('emp_sec', null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'total_sec']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('amount', __('Loan Amount'), ['class' => 'form-label amount_label']) }}<span class="text-danger" id="loan_error"></span>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'id' => 'loan_amount']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('maxamount', __('Max Amount'), ['class' => 'form-label amount_label']) }}<span class="text-danger" id="loan_error"></span>
            {{ Form::number('maxamount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'id' => 'max_amount', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('from_pay_month', __('From Pay Month'), ['class' => 'form-label']) }}
            {{ Form::date('from_pay_month', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pay_period', __('Pay Months'), ['class' => 'form-label']) !!}
            {!! Form::number('pay_period', null, ['class' => 'form-control', 'id' => 'pay_date', 'required' => 'required', 'min' => '1']) !!}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('loan_ended', __('Till Month'), ['class' => 'form-label']) }}
            {{ Form::date('loan_ended', null, ['class' => 'form-control', 'id' => 'loan_ended', 'readonly' => 'readonly']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason')) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}