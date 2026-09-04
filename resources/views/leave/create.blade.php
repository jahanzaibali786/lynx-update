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
{{ Form::open(['url' => 'leave', 'method' => 'post', 'id' => 'leave-create-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
            </div>
        </div>
        @if(\Auth::user()->type == 'company' || \Auth::user()->type == 'branch' || \Auth::user()->type == 'HR')
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="form-group">
                {{Form::label('employee_id', __('Employee'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                {{Form::select('employee_id', $employees, null, array('class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')))}}
            </div>
        </div>
        @endif
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="form-group">
                {{Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                <select name="leave_type_id" id="leave_type_id" required class="form-control select">
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @foreach($leavetypes as $leave)
                    <option value="{{ $leave->id }}">{{ $leave->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6 mr-2">
            <label for="annual_total" class="form-label">LB Annual</label>
            <input type="text" name="annual_total" id="" value="" class="form-control" disabled>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6 mr-2">
            <label for="casual_total" class="form-label">LB Casual</label>
            <input type="text" name="casual_total" id="" value="" class="form-control" disabled>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {{ Form::label('applied_on', __('Applied On'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::date('applied_on', null, ['class' => 'form-control', 'id' => 'applied_on', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::date('start_date', null, ['class' => 'form-control', 'id' => 'start_date', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::date('end_date', null, ['class' => 'form-control', 'id' => 'end_date', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {{ Form::label('total_days', __('Total Days'), ['class' => 'form-label']) }}
                {{ Form::text('total_days', null, ['class' => 'form-control', 'id' => 'total_days', 'disabled' => 'disabled']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::select('leave_reason', [
                        'sick_leave' => __('Sick Leave'),
                        'domestic_problem' => __('Domestic Problem'),
                        'maternity' => __('Maternity'),
                        'other' => __('Other')
                    ], null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Leave Reason')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('remark', __('Remark'), ['class' => 'form-label'])}}
                {{Form::textarea('remark', null, array('class' => 'form-control grammer_textarea', 'rows' => 1, 'placeholder' => __('Leave Remark')))}}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary" id="leave-create-submit">
</div>
{{Form::close()}}
<script>
    if (window.jQuery && typeof ajaxModalForm === 'function') {
        $(document).on('change', '#start_date', function() {
            var startDate = $(this).val();
            $('#end_date').attr('min', startDate || '');

            if (startDate && $('#end_date').val() && $('#end_date').val() < startDate) {
                $('#end_date').val(startDate).trigger('change');
            }
        });

        $(document).on('submit', '#leave-create-form', function(e) {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            if (startDate && endDate && endDate < startDate) {
                e.preventDefault();
                show_toastr('error', '{{ __('End Date must be greater than or equal to Start Date.') }}', 'error');
                $('#end_date').focus();
                return false;
            }
        });

        ajaxModalForm({
            formSelector: '#leave-create-form',
            submitText: '{{ __('Processing...') }}',
            closeOnSuccess: false,
            showToast: false,
            onSuccess: function(response, $form) {
                if (response && response.row_html) {
                    var $tbody = $('.datatable tbody');
                    $tbody.prepend(response.row_html);
                    $tbody.find('tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });
                }

                show_toastr('success', (response && response.message) || '{{ __('Leave successfully created.') }}',
                    'success');

                var $employee = $('#employee_id');
                if ($employee.length) {
                    $employee.val('');
                    $employee.find('option:selected').prop('selected', false);
                    $employee.find('option[value=""]').prop('selected', true);

                    if ($employee[0] && $employee[0].customSelectInstance) {
                        try {
                            $employee[0].customSelectInstance.destroy();
                        } catch (e) {}
                        delete $employee[0].customSelectInstance;
                    }

                    $employee.next('.custom-select-wrapper').remove();
                    $employee.addClass('custom-select').show();

                    if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                        window.CustomSelect.create($employee[0]);
                    }

                    $employee.trigger('change');
                }
            }
        });
    }
</script>
