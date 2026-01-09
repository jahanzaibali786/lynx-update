{{Form::model($leave, array('route' => array('leave.update', $leave->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                {{ Form::select('branches', $branches, $leave->owned_by, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
            </div>
        </div>
        @if(\Auth::user()->type == 'company' || \Auth::user()->type == 'branch' || \Auth::user()->type == 'HR')
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="form-group">
                {{Form::label('employee_id', __('Employee'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                {{Form::select('employee_id', $employees, null, array('class' => 'form-control select', 'required' => 'required', 'id' => 'employee_id'))}}
            </div>
        </div>
        @endif
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="form-group">
                {{Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                <select name="leave_type_id" id="leave_type_id" required class="form-control select">
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @foreach($leavetypes as $leave_type)
                    <option @if($leave_type->id == $leave->leave_type_id) selected @endif value="{{ $leave_type->id }}">{{ $leave_type->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @php
            $Lb_annual = 0;
            $Lb_casual = 0;
            @$employeeLeaves = \App\Models\EmployeeLeaves::where('employee_id', @$leave->employee_id)->first();
            if (@$employeeLeaves->annual_total > @$employeeLeaves->annual_consumed || @$employeeLeaves->casual_total > @$employeeLeaves->casual_consumed) {
                $Lb_annual = @$employeeLeaves->annual_total - @$employeeLeaves->annual_consumed;
                $Lb_casual = @$employeeLeaves->casual_total - @$employeeLeaves->casual_consumed;
            } else {
                $Lb_annual = 0;
                $Lb_casual = 0;
            }
        @endphp
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6 mr-2">
            <label for="annual_total" class="form-label">LB Annual</label>
            <input type="text" name="annual_total" id="" value="{{@$Lb_annual}}" class="form-control" disabled>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 col-6 mr-2">
            <label for="casual_total" class="form-label">LB Casual</label>
            <input type="text" name="casual_total" id="" value="{{@$Lb_casual}}" class="form-control" disabled>
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
                {{ Form::text('total_days', $leave->total_leave_days, ['class' => 'form-control', 'id' => 'total_days', 'disabled' => 'disabled']) }}
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
    'maternity' => __('Maternity')
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
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{Form::close()}}
<script>
    $(document).ready(function() {
        // 1. grab the existing values (will be empty on "create")
        var existingEmployee = $('#employee_id').val();
        var existingLeaveType = "{{ $leave->leave_type_id ?? '' }}";

        // 2. wire the change
        $('#employee_id').on('change', function() {
            var emp = $(this).val();
            loadLeaveTypes(emp, null);
        });

        // 3. on page‐load for edit, fire once
        if (existingEmployee) {
            loadLeaveTypes(existingEmployee, existingLeaveType);
        }
    });

    /**
     * Fetches leave‐types for a given employee,
     * appends them to #leave_type_id, and
     * optionally pre‐selects one.
     *
     * @param {string} employeeId
     * @param {string|null} selectTypeId
     */
    function loadLeaveTypes(employeeId, selectTypeId) {
        if (!employeeId) return;

        $.ajax({
            url: '{{ route('leave.jsoncount') }}',
            type: 'POST',
            data: {
                employee_id: employeeId,
                _token: "{{ csrf_token() }}",
            },
            success: function(data) {
                var $sel = $('#leave_type_id');
                $sel.empty().append('<option value="">{{ __("Select Leave Type") }}</option>');

                var annualTotal = 0, casualTotal = 0;
                $.each(data, function(_, value) {
                    // compute disable logic
                    var disabled = value.isOnProbation && (value.title.toLowerCase() === 'annual leave');
                    if (value.title.toLowerCase() === 'annual leave') {
                        annualTotal = disabled ? 0 : (value.days - value.total_leave);
                        disabled = annualTotal === 0;
                    } else if (value.title.toLowerCase() === 'casual') {
                        casualTotal = disabled ? 0 : (value.days - value.total_leave);
                        disabled = casualTotal === 0;
                    }

                    var text = value.title + ' (' + value.total_leave + '/' + value.days + ')';
                    var $opt = $('<option>')
                        .val(value.id)
                        .html(text)
                        .prop('disabled', disabled);

                    $sel.append($opt);
                });

                // 4. apply totals
                $('input[name="annual_total"]').val(annualTotal);
                $('input[name="casual_total"]').val(casualTotal);

                // 5. if editing, set the selected leave type
                if (selectTypeId) {
                    $sel.val(selectTypeId);
                }
            }
        });
    }
</script>
