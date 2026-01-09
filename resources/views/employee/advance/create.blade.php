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
        data: { id: id },
        dataType: 'json',
        success: function(result) {
            if (result.status === 'success') {
                var $empSelect = $('#employee_id');

                // destroy previous custom-select instance if present
                if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                    try { $empSelect[0].customSelectInstance.destroy(); } catch (e) {}
                    delete $empSelect[0].customSelectInstance;
                }
                if ($empSelect.next('.custom-select-wrapper').length) {
                    $empSelect.next('.custom-select-wrapper').remove();
                }
                $empSelect.removeClass('custom-select');

                // populate options with only non-resigned employees
                $empSelect.empty();
                $empSelect.append($('<option>', { value: '', text: 'Select Employee' }));

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
                    $empSelect.append($('<option>', { value: '', text: 'No active employees', disabled: true }));
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
                    if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0].customSelectInstance.refresh === 'function') {
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
}function branchemployees(id) {
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
        data: { id: id },
        dataType: 'json',
        success: function(result) {
            if (result.status === 'success') {
                var $empSelect = $('#employee_id');

                // destroy previous custom-select instance if present
                if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                    try { $empSelect[0].customSelectInstance.destroy(); } catch (e) {}
                    delete $empSelect[0].customSelectInstance;
                }
                if ($empSelect.next('.custom-select-wrapper').length) {
                    $empSelect.next('.custom-select-wrapper').remove();
                }
                $empSelect.removeClass('custom-select');

                // populate options with only non-resigned employees
                $empSelect.empty();
                $empSelect.append($('<option>', { value: '', text: 'Select Employee' }));

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
                    $empSelect.append($('<option>', { value: '', text: 'No active employees', disabled: true }));
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
                    if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0].customSelectInstance.refresh === 'function') {
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
{{Form::open(array('url' => 'employee-advance', 'method' => 'post'))}}
<div class="modal-body">
    <div class="row">
    <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
            {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
        </div>
        @if(\Auth::user()->type != 'Employee')
        <div class="form-group col-md-6">
            {{Form::label('employee_id', __('Employee'), ['class' => 'form-label'])}}<span style="color: red">
                *</span>
            {{Form::select('employee_id', $employee,null, array('class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')))}}
        </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Advnace Amount'), ['class' => 'form-label amount_label']) }}
            {{ Form::number('amount', null, array('class' => 'form-control ', 'required' => 'required', 'step' => '0.01','id'=>'loan_amount')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Advnace date'), ['class' => 'form-label amount_label']) }}
            {{ Form::date('date', null, array('class' => 'form-control ', 'required' => 'required')) }}
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('reason', __('Reason')) }}
                {{ Form::textarea('reason', null, array('class' => 'form-control ', 'required' => 'required', 'rows' => 3)) }}
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" id="submit_btn" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}

