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
{{ Form::open(['url' => 'appraisal', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                {{ Form::select('branch', $brances, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)', 'id' => 'branch']) }}
            </div>
        </div>
        <div class="col-md-6 mt-2">
            <div class="form-group">
                {{ Form::label('employee', __('Employee*'), ['class' => 'form-label']) }}
                <div class="employee_div">
                    <select name="employee" id="employee_id" class="form-control custom-select" required>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('appraisal_date', __('Select Month*'), ['class' => 'col-form-label']) }}
                {{ Form::month('appraisal_date', '', ['class' => 'form-control ', 'autocomplete' => 'off', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => 'Enter remark']) }}
            </div>
        </div>
    </div>
    <div class="row" id="stares">
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}



<script>
    $('#employee_id').change(function() {

        var emp_id = $('#employee_id').val();
        $.ajax({
            url: "{{ route('empByStar') }}",
            type: "post",
            data: {
                "employee": emp_id,
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {
                $('#stares').html(data.html);
            }
        })
    });
</script>

<script>
    $('#branch').on('change', function() {
        var branch_id = this.value;
        branchemployees(branch_id);

        $.ajax({
            url: "{{ route('getemployee') }}",
            type: "post",
            data: {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {

                $('#employee_id').html('<option value="">Select Employee</option>');
                $.each(data.employee, function(key, value) {
                    $("#employee_id").append('<option value="' + value.id + '">' + value.name +
                        '</option>');
                });

            }
        })


    });
</script>
