<script>
    //
    $(document).ready(function() {
        var latestGeneratedSalaryMonth = '';
        var latestGeneratedSalaryText = '';
        var employeeSecurityAmount = 0;

        function addMonthsToMonthValue(value, months) {
            var date = monthValueToDate(value);
            if (!date) {
                return '';
            }
            date.setMonth(date.getMonth() + months);
            return formatMonthValue(date);
        }

        function getMinimumFromPayMonth() {
            var todayMonth = formatMonthValue(new Date());
            var salaryNextMonth = latestGeneratedSalaryMonth ? addMonthsToMonthValue(latestGeneratedSalaryMonth, 1) : '';

            return salaryNextMonth || todayMonth;
        }

        function updateFromPayMonthMin() {
            var minMonth = getMinimumFromPayMonth();
            $('#from_pay_month').attr('min', minMonth);

            if ($('#from_pay_month').val() && $('#from_pay_month').val() < minMonth) {
                $('#from_pay_month').val(minMonth);
            }
        }

        function validateFromPayMonth(showMessage) {
            var fromPayMonth = $('#from_pay_month').val();
            var minMonth = getMinimumFromPayMonth();
            var message = '';

            if (fromPayMonth && fromPayMonth < minMonth) {
                message = latestGeneratedSalaryMonth
                    ? 'From paid month salary already generated. Please select next month.'
                    : 'Please select current month or a future month.';

                if (latestGeneratedSalaryText) {
                    message += ' Last salary: ' + latestGeneratedSalaryText + '.';
                }
            }

            $('#from_pay_month_error').text(message);

            if (message) {
                $('#submit_btn').prop('disabled', true);
                if (showMessage) {
                    show_toastr('error', message, 'error');
                }
                return false;
            }

            if (!$('#loan_error').text()) {
                $('#submit_btn').prop('disabled', false);
            }
            return true;
        }

        function updateMaxAmountByLoanType() {
            if ($('#total_sec').val() == 'security' && employeeSecurityAmount > 0) {
                $('#max_amount').val(employeeSecurityAmount / 2);
                $('#actual_security_amount_text').text('(' + employeeSecurityAmount + ')');
            } else {
                $('#max_amount').val('');
                $('#actual_security_amount_text').text('');
            }
        }
        window.validateLoanCreateFromPayMonth = validateFromPayMonth;
        window.getLoanCreateMinimumFromPayMonth = getMinimumFromPayMonth;

        $('#employee_id').change(function() {
            var employeeId = $(this).val();
            if (employeeId) {
                $.ajax({
                    url: '{{ url('get-employee-serv-sec') }}/' + employeeId,
                    type: 'GET',
                    success: function(response) {
                        $('#loan_error').text('');
                        $('#loan_amount').val('');
                        if (response) {
                            $('#service_tenure').val(response.service_tenure);
                            $('#department').val(response.emp_department);
                            // $('#total_sec').val(response.total_sec);
                            employeeSecurityAmount = parseFloat(response.total_sec) || 0;
                            updateMaxAmountByLoanType();
                            latestGeneratedSalaryMonth = response.latest_salary_month || '';
                            latestGeneratedSalaryText = response.latest_salary_text || '';
                            updateFromPayMonthMin();
                            var isFromPayMonthValid = validateFromPayMonth(false);
                            if (isNaN(response.total_sec) || response.total_sec <= 0) {
                                $('#submit_btn').prop('disabled', true);
                            } else if (!isFromPayMonthValid) {
                                $('#submit_btn').prop('disabled', true);
                            } else {
                                $('#submit_btn').prop('disabled', false);
                            }
                        } else {
                            $('#service_tenure').val('0');
                            // $('#total_sec').val('0');
                            employeeSecurityAmount = 0;
                            $('#max_amount').val('');
                            $('#actual_security_amount_text').text('');
                            latestGeneratedSalaryMonth = '';
                            latestGeneratedSalaryText = '';
                            updateFromPayMonthMin();
                            $('#submit_btn').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $('#service_tenure').val('');
                        // $('#total_sec').val('');
                        employeeSecurityAmount = 0;
                        $('#max_amount').val('');
                        $('#actual_security_amount_text').text('');
                        latestGeneratedSalaryMonth = '';
                        latestGeneratedSalaryText = '';
                        updateFromPayMonthMin();
                        $('#submit_btn').prop('disabled', true);
                    }
                });
            } else {
                $('#service_tenure').val('');
                // $('#total_sec').val('');
                employeeSecurityAmount = 0;
                $('#max_amount').val('');
                $('#actual_security_amount_text').text('');
                latestGeneratedSalaryMonth = '';
                latestGeneratedSalaryText = '';
                updateFromPayMonthMin();
                $('#submit_btn').prop('disabled', true);
            }
        });

       $('#loan_amount').on('input', function () {

            var loanAmount = parseFloat($(this).val());
            var totalSecurity = parseFloat($('#max_amount').val());
            var selectedType = $('#total_sec').val();

            if (selectedType == 'security') {

                if (isNaN(totalSecurity) || totalSecurity <= 0) {

                    $('#loan_error').text('(Security amount must be greater than 0 to take a loan.)');
                    $('#submit_btn').prop('disabled', true);

                } else if (loanAmount > totalSecurity) {

                    $('#loan_error').text('(Loan amount cannot exceed ' + totalSecurity + ')');
                    $('#submit_btn').prop('disabled', true);

                } else {

                    $('#loan_error').text('');
                    $('#submit_btn').prop('disabled', !validateFromPayMonth(false));
                }

            } else {

                $('#loan_error').text('');
                $('#submit_btn').prop('disabled', !validateFromPayMonth(false));
            }
            calculatePerMonth();
        });

        $('#total_sec').on('change', function() {
            var selectedType = $(this).val();
            updateMaxAmountByLoanType();
            if (selectedType != 'security') {
                $('#loan_amount').val('');
                $('#loan_error').text('');
                $('#submit_btn').prop('disabled', !validateFromPayMonth(false));
            }
            $('#loan_amount').trigger('input');
        });

        $('#from_pay_month').on('change', function() {
            validateFromPayMonth(true);
        });

        $('#loan_create_form').on('submit', function(event) {
            if (!validateFromPayMonth(true)) {
                event.preventDefault();
            }
        });

    });
    function calculatePerMonth() {

        var loanAmount = parseFloat($('#loan_amount').val());
        var payPeriod = parseInt($('#pay_date').val());

        // Validate
        if (
            isNaN(loanAmount) ||
            isNaN(payPeriod) ||
            payPeriod <= 0
        ) {

            $('#permonth').val('');

            return;
        }

        // Calculate installment per month
        var perMonth = Math.round(loanAmount / payPeriod);

        // Set value with 2 decimal
        $('#permonth').val(perMonth.toFixed(2));
    }
    ///
    function formatMonthValue(date) {
        var month = String(date.getMonth() + 1).padStart(2, '0');
        return date.getFullYear() + '-' + month;
    }

    function monthValueToDate(value) {
        return value ? new Date(value + '-01T00:00:00') : null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('from_pay_month').setAttribute('min', formatMonthValue(new Date()));
    });

    document.getElementById('pay_date').addEventListener('keyup', function() {
        updateProbationEndDate();
        calculatePerMonth();
    });
    document.getElementById('pay_date').addEventListener('change', function() {
        updateProbationEndDate();
        calculatePerMonth();
    });

    document.getElementById('from_pay_month').addEventListener('change', function() {
        var selectedDate = monthValueToDate(this.value);
        var minimumMonth = typeof window.getLoanCreateMinimumFromPayMonth === 'function'
            ? window.getLoanCreateMinimumFromPayMonth()
            : formatMonthValue(new Date());
        var minimumDate = monthValueToDate(minimumMonth);

        if (selectedDate < minimumDate) {
            alert('From paid month salary already generated. Please select next month.');
            this.value = minimumMonth;
        }

        if (typeof window.validateLoanCreateFromPayMonth === 'function') {
            window.validateLoanCreateFromPayMonth(true);
        }

        updateProbationEndDate();
        calculatePerMonth();
    });
    //  create function to calculate probation end date based on pay period and from pay month

    function updateProbationEndDate() {

        var p_id = parseInt($('#pay_date').val());
        var currentDate = monthValueToDate($('#from_pay_month').val());

        if (currentDate && !isNaN(currentDate.getTime())) {

            if (isNaN(p_id) || p_id <= 0) {

                var formattedDate = formatMonthValue(currentDate);
                $('#loan_ended').val(formattedDate);

            } else {

                var futureDate = new Date(currentDate);

                // subtract 1 because current month is first installment
                futureDate.setMonth(futureDate.getMonth() + (p_id - 1));

                var formattedDate = formatMonthValue(futureDate);

                $('#loan_ended').val(formattedDate);
            }
        }
    }
</script>
<script>
    function branchemployees(id) {
        // remember previous selection so we can restore if still available
        var prevVal = $('#employee_id').val();

        function isResigned(emp) {
            if (!emp) return false;
            if ('resigned' in emp) {
                var v = emp.resigned;
                return v === 1 || v === '1' || v === true || v === 'true';
            }
            if ('is_resigned' in emp) {
                var v2 = emp.is_resigned;
                return v2 === 1 || v2 === '1' || v2 === true || v2 === 'true';
            }
            if ('is_active' in emp) {
                var a = emp.is_active;
                return a === 0 || a === '0' || a === false || a === 'false';
            }
            if ('status' in emp) {
                var s = String(emp.status).toLowerCase();
                return s === 'resigned' || s === 'left' || s === 'inactive' || s === 'terminated';
            }
            // fallback: assume active if we can't detect a resigned flag
            return false;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('branch.employees') }}",
            type: "POST",
            data: {
                id: id
            },
            dataType: 'json',
            success: function(result) {
                if (result.status === 'success') {
                    var $empSelect = $('#employee_id');

                    // destroy previous custom-select instance if present
                    if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                        try {
                            $empSelect[0].customSelectInstance.destroy();
                        } catch (e) {}
                        delete $empSelect[0].customSelectInstance;
                    }
                    if ($empSelect.next('.custom-select-wrapper').length) {
                        $empSelect.next('.custom-select-wrapper').remove();
                    }
                    $empSelect.removeClass('custom-select');

                    // populate options with only non-resigned employees
                    $empSelect.empty();
                    $empSelect.append($('<option>', {
                        value: '',
                        text: 'Select Employee'
                    }));

                    var added = 0;
                    for (var j = 0; j < result.employee.length; j++) {
                        var emp = result.employee[j];
                        if (!isResigned(emp)) {
                            $empSelect.append($('<option>', {
                                value: emp.id,
                                text: emp.name
                            }));
                            added++;
                        }
                    }

                    if (added === 0) {
                        // show a disabled placeholder if no active employees
                        $empSelect.append($('<option>', {
                            value: '',
                            text: 'No active employees',
                            disabled: true
                        }));
                    }

                    // re-init custom select for this element only
                    $empSelect.addClass('custom-select');
                    $empSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                        window.CustomSelect.create($empSelect[0]);
                    }

                    // Restore previous selection if still present and not resigned
                    if (prevVal && $empSelect.find('option[value="' + prevVal + '"]').length) {
                        $empSelect.val(prevVal);
                        if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0]
                            .customSelectInstance.refresh === 'function') {
                            $empSelect[0].customSelectInstance.refresh();
                        }
                    }
                } else {
                    console.warn('branchemployees returned status:', result.status);
                }
            },
            error: function(xhr, status, err) {
                console.error('AJAX error in branchemployees:', err);
            }
        });
    }
</script>
{{ Form::open(['url' => 'loan', 'method' => 'post', 'id' => 'loan_create_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
        @if (\Auth::user()->type != 'Employee')
            <div class="form-group col-md-6">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('employee_id', $employee, null, ['class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id']) }}
            </div>
        @endif
        <div class="form-group col-md-4">
            {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
            {{ Form::text('department', null, ['class' => 'form-control ', 'readonly' => 'readonly', 'id' => 'department']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('service_tenure', __('Service Tenure'), ['class' => 'form-label']) !!}
            {!! Form::text('service_tenure', null, [
                'class' => 'form-control',
                'readonly' => 'readonly',
                'id' => 'service_tenure',
            ]) !!}
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
            {{ Form::text('title', null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('amount', __('Loan Amount'), ['class' => 'form-label amount_label']) }}<span
                class="text-danger" id="loan_error"></span>
            {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '1', 'id' => 'loan_amount']) }}
        </div>
        <div class="form-group col-md-3">
            <label for="max_amount" class="form-label amount_label">{{ __('Max Amount') }} <span id="actual_security_amount_text"></span></label>
            {{ Form::number('maxamount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '1', 'id' => 'max_amount', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('from_pay_month', __('From Pay Month'), ['class' => 'form-label']) }}
            {{ Form::month('from_pay_month', null, ['class' => 'form-control', 'required' => 'required']) }}
            <span class="text-danger" id="from_pay_month_error"></span>
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('pay_period', __('Pay Months'), ['class' => 'form-label']) !!}
            {!! Form::number('pay_period', 1, [
                'class' => 'form-control',
                'id' => 'pay_date',
                'required' => 'required',
                'min' => '1',
            ]) !!}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('loan_ended', __('To Month'), ['class' => 'form-label']) }}
            {{ Form::month('loan_ended', null, ['class' => 'form-control', 'id' => 'loan_ended', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('permonth', __('Installment Per Month'), ['class' => 'form-label amount_label']) }}<span
                class="text-danger" id="loan_error"></span>
            {{ Form::number('permonth', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '1', 'id' => 'permonth', 'readonly' => 'readonly']) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason')) }}
                {{ Form::textarea('reason', null, ['class' => 'form-control ', 'required' => 'required', 'rows' => 3]) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" id="submit_btn" value="{{ __('Create') }}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
