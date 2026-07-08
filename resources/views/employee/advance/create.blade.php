<script>
    function branchemployees(id) {
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
            return false;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('branch.employees') }}",
            type: "POST",
            data: { id: id },
            dataType: 'json',
            success: function(result) {
                if (result.status === 'success') {
                    var $employee = $('#employee_id');

                    if ($employee[0] && $employee[0].customSelectInstance) {
                        try {
                            $employee[0].customSelectInstance.destroy();
                        } catch (e) {}
                        delete $employee[0].customSelectInstance;
                    }
                    if ($employee.next('.custom-select-wrapper').length) {
                        $employee.next('.custom-select-wrapper').remove();
                    }
                    $employee.removeClass('custom-select');

                    $employee.empty().append($('<option>', { value: '', text: 'Select Employee' }));
                    var added = 0;
                    for (var j = 0; j < result.employee.length; j++) {
                        var emp = result.employee[j];
                        if (!isResigned(emp)) {
                            $employee.append($('<option>', {
                                value: emp.id,
                                text: emp.name
                            }));
                            added++;
                        }
                    }

                    if (added === 0) {
                        $employee.append($('<option>', { value: '', text: 'No active employees', disabled: true }));
                    }

                    $employee.addClass('custom-select').show();
                    if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                        window.CustomSelect.create($employee[0]);
                    }

                    if (prevVal && $employee.find('option[value="' + prevVal + '"]').length) {
                        $employee.val(prevVal);
                        if ($employee[0] && $employee[0].customSelectInstance && typeof $employee[0].customSelectInstance.refresh === 'function') {
                            $employee[0].customSelectInstance.refresh();
                        }
                    }
                    $employee.trigger('change');
                }
            }
        });
    }

    $(document).ready(function() {
        var allowedAdvanceMonth = '';
        var allowedAdvanceText = '';
        var advanceExistsMessage = '';

        function formatMonthValue(date) {
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return date.getFullYear() + '-' + month;
        }

        function setAdvanceMonth(monthValue, monthText) {
            allowedAdvanceMonth = monthValue || formatMonthValue(new Date());
            allowedAdvanceText = monthText || allowedAdvanceMonth;
            $('#advance_date').attr('min', allowedAdvanceMonth).attr('max', allowedAdvanceMonth).val(allowedAdvanceMonth);
            $('#advance_month_help').text('Allowed month: ' + allowedAdvanceText);
            $('#advance_date_error').text('');
        }

        function validateAdvanceMonth(showMessage) {
            var selectedMonth = $('#advance_date').val();
            var message = '';

            if (allowedAdvanceMonth && selectedMonth && selectedMonth !== allowedAdvanceMonth) {
                message = 'Advance month must be ' + allowedAdvanceText + '.';
            } else if (advanceExistsMessage) {
                message = advanceExistsMessage;
            }

            $('#advance_date_error').text(message);
            $('#submit_btn').prop('disabled', !!message);

            if (message && showMessage) {
                show_toastr('error', message, 'error');
            }

            return !message;
        }

        $('#employee_id').on('change', function() {
            var employeeId = $(this).val();
            $('#submit_btn').prop('disabled', false);
            $('#advance_month_help').text('');
            $('#advance_date_error').text('');
            advanceExistsMessage = '';

            if (!employeeId) {
                $('#advance_date').val('').removeAttr('min').removeAttr('max');
                return;
            }

            $.ajax({
                url: '{{ url('employee-advance-employee-month') }}/' + employeeId,
                type: 'GET',
                success: function(response) {
                    setAdvanceMonth(response.allowed_advance_month, response.allowed_advance_text);
                    advanceExistsMessage = response.advance_exists_message || '';
                    validateAdvanceMonth(false);
                },
                error: function() {
                    $('#advance_date').val('').removeAttr('min').removeAttr('max');
                    $('#advance_date_error').text('Unable to fetch last salary month.');
                    $('#submit_btn').prop('disabled', false);
                }
            });
        });

        $('#advance_date').on('change', function() {
            validateAdvanceMonth(true);
        });

        $('#advance_create_form').on('submit', function(event) {
            if (!validateAdvanceMonth(true)) {
                event.preventDefault();
            }
        });
    });
</script>
{{ Form::open(['url' => 'employee-advance', 'method' => 'post', 'id' => 'advance_create_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
        @if(\Auth::user()->type != 'Employee')
            <div class="form-group col-md-6">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('employee_id', $employee, null, ['class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Advance Amount'), ['class' => 'form-label amount_label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'id' => 'advance_amount']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Advance Month'), ['class' => 'form-label amount_label']) }}
            {{ Form::month('date', null, ['class' => 'form-control', 'required' => 'required', 'id' => 'advance_date']) }}
            <small class="text-muted" id="advance_month_help"></small>
            <span class="text-danger d-block" id="advance_date_error"></span>
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
    <input type="submit" id="submit_btn" value="{{ __('Create') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
