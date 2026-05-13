<script>
    $(document).ready(function() {
        function validateLoanAmount() {
            var loanAmount = parseFloat($('#loan_amount').val());
            var maxAmount = parseFloat($('#max_amount').val());
            var selectedType = $('#total_sec').val();

            if (selectedType === 'security') {
                if (isNaN(maxAmount) || maxAmount <= 0) {
                    $('#loan_error').text('(Security amount must be greater than 0 to take a loan.)');
                    $('#submit_btn').prop('disabled', true);
                } else if (loanAmount > maxAmount) {
                    $('#loan_error').text('(Loan amount cannot exceed ' + maxAmount + ')');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#loan_error').text('');
                    $('#submit_btn').prop('disabled', false);
                }
            } else {
                $('#loan_error').text('');
                $('#submit_btn').prop('disabled', false);
            }
        }

        function calculatePerMonth() {
            var loanAmount = parseFloat($('#loan_amount').val());
            var payPeriod = parseInt($('#pay_date').val());

            if (isNaN(loanAmount) || isNaN(payPeriod) || payPeriod <= 0) {
                $('#permonth').val('');
                return;
            }

            $('#permonth').val((loanAmount / payPeriod).toFixed(2));
        }

        function formatMonthValue(date) {
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return date.getFullYear() + '-' + month;
        }

        function monthValueToDate(value) {
            return value ? new Date(value + '-01T00:00:00') : null;
        }

        function updateProbationEndDate() {
            var payPeriod = parseInt($('#pay_date').val());
            var currentDate = monthValueToDate($('#from_pay_month').val());

            if (currentDate && !isNaN(currentDate.getTime())) {
                if (isNaN(payPeriod) || payPeriod <= 0) {
                    $('#loan_ended').val(formatMonthValue(currentDate));
                } else {
                    var futureDate = new Date(currentDate);
                    futureDate.setMonth(futureDate.getMonth() + (payPeriod - 1));
                    $('#loan_ended').val(formatMonthValue(futureDate));
                }
            }
        }

        $('#employee_id').change(function() {
            var employeeId = $(this).val();

            if (employeeId) {
                $.ajax({
                    url: '{{ url('get-employee-serv-sec') }}/' + employeeId,
                    type: 'GET',
                    success: function(response) {
                        if (response) {
                            $('#service_tenure').val(response.service_tenure);
                            $('#department').val(response.emp_department);
                            $('#max_amount').val((response.total_sec) / 2);
                        } else {
                            $('#service_tenure').val('0');
                            $('#department').val('');
                            $('#max_amount').val('');
                        }
                        validateLoanAmount();
                    },
                    error: function() {
                        $('#service_tenure').val('');
                        $('#department').val('');
                        $('#max_amount').val('');
                        validateLoanAmount();
                    }
                });
            } else {
                $('#service_tenure').val('');
                $('#department').val('');
                $('#max_amount').val('');
                validateLoanAmount();
            }
        });

        $('#loan_amount, #pay_date').on('input change keyup', function() {
            validateLoanAmount();
            updateProbationEndDate();
            calculatePerMonth();
        });

        $('#total_sec').on('change', function() {
            validateLoanAmount();
        });

        $('#from_pay_month').on('change', function() {
            updateProbationEndDate();
            calculatePerMonth();
        });

        updateProbationEndDate();
        calculatePerMonth();
        validateLoanAmount();
    });
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
            {!! Form::label('emp_sec', __('Type'), ['class' => 'form-label']) !!}
            {!! Form::select(
                'emp_sec',
                [
                    '' => 'Select Type',
                    'security' => 'Security',
                    'course' => 'Course / Training',
                    'others' => 'Others',
                ],
                null,
                [
                    'class' => 'form-control',
                    'id' => 'total_sec',
                ],
            ) !!}
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
        <div class="form-group col-md-3">
            {{ Form::label('from_pay_month', __('From Pay Month'), ['class' => 'form-label']) }}
            {{ Form::month('from_pay_month', !empty($loan->from_pay_month) ? \Carbon\Carbon::parse($loan->from_pay_month)->format('Y-m') : null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('pay_period', __('Pay Months'), ['class' => 'form-label']) !!}
            {!! Form::number('pay_period', null, ['class' => 'form-control', 'id' => 'pay_date', 'required' => 'required', 'min' => '1']) !!}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('loan_ended', __('To Month'), ['class' => 'form-label']) }}
            {{ Form::month('loan_ended', !empty($loan->loan_ended) ? \Carbon\Carbon::parse($loan->loan_ended)->format('Y-m') : null, ['class' => 'form-control', 'id' => 'loan_ended', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('permonth', __('Installment Per Month'), ['class' => 'form-label amount_label']) }}
            {{ Form::number('permonth', $loan->per_month_amount, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'id' => 'permonth', 'readonly' => 'readonly']) }}
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
    <input type="submit" id="submit_btn" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
