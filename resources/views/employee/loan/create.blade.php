<script>
    //
    $(document).ready(function() {
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
                            $('#max_amount').val((response.total_sec) / 2);
                            if (isNaN(response.total_sec) || response.total_sec <= 0) {
                                $('#submit_btn').prop('disabled', true);
                            } else {
                                $('#submit_btn').prop('disabled', false);
                            }
                        } else {
                            $('#service_tenure').val('0');
                            // $('#total_sec').val('0');
                            $('#submit_btn').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $('#service_tenure').val('');
                        // $('#total_sec').val('');
                        $('#submit_btn').prop('disabled', true);
                    }
                });
            } else {
                $('#service_tenure').val('');
                // $('#total_sec').val('');
                $('#submit_btn').prop('disabled', true);
            }
        });

        $('#loan_amount').on('input', function() {
            var loanAmount = parseFloat($(this).val());
            var totalSecurity = parseFloat($('#max_amount').val());
            var selectedType = $('#total_sec').val();
            if (selectedType == 'security') {
                if (isNaN(totalSecurity) || totalSecurity <= 0) {
                    $('#loan_error').text('(Security amount must be greater than 0 to take a loan.)');
                    $('#submit_btn').prop('disabled', true);
                } else if (max_amount) {
                    $('#loan_error').text('(Loan amount cannot exceed ' + totalSecurity + ')');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#loan_error').text('');
                    $('#submit_btn').prop('disabled', false);
                }
            } else {
                $('#loan_error').text('');
                $('#submit_btn').prop('disabled', false);
            }
        });

        $('#total_sec').on('change', function() {
            var selectedType = $(this).val();
            if (selectedType !== 'security') {
                $('#loan_amount').val('');
                $('#loan_error').text('');
                $('#submit_btn').prop('disabled', false);
            }
        });

    });
    ///
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('from_pay_month').setAttribute('min', today);
    });

    document.getElementById('pay_date').addEventListener('keyup', function() {
        updateProbationEndDate();
    });
    document.getElementById('pay_date').addEventListener('change', function() {
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
{{ Form::open(['url' => 'loan', 'method' => 'post']) }}
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
            {{ Form::number('amount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01', 'id' => 'loan_amount']) }}
        </div>
        <div class="form-group col-md-3">
            {{ Form::label('maxamount', __('Max Amount'), ['class' => 'form-label amount_label']) }}<span
                class="text-danger" id="loan_error"></span>
            {{ Form::number('maxamount', null, ['class' => 'form-control ', 'required' => 'required', 'step' => '0.01', 'id' => 'max_amount', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('from_pay_month', __('From Pay Month'), ['class' => 'form-label']) }}
            {{ Form::date('from_pay_month', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {!! Form::label('pay_period', __('Pay Months'), ['class' => 'form-label']) !!}
            {!! Form::number('pay_period', 1, [
                'class' => 'form-control',
                'id' => 'pay_date',
                'required' => 'required',
                'min' => '1',
            ]) !!}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('loan_ended', __('Till Month'), ['class' => 'form-label']) }}
            {{ Form::date('loan_ended', null, ['class' => 'form-control', 'id' => 'loan_ended', 'readonly' => 'readonly']) }}
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