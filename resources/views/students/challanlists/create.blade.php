{{ Form::open(['url' => 'store-challan', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        {{-- //session  --}}
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('session_id', $session, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'id' => 'branch_input', 'required' => 'required', 'onchange' => 'loadBranchData(this.value)']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', ['' => 'Select Class'], null, ['class' => 'form-control select custom-select', 'id' => 'class_input', 'required' => 'required', 'onchange' => 'loadClassStudents(this.value)']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('student_id', ['' => 'Select Student'], null, ['class' => 'form-control select custom-select', 'id' => 'student_input', 'required' => 'required']) }}
            </div>
        </div>
       
        {{ Form::text('challan_type', strtoupper($challanType) . ' CHALLAN', ['class' => 'form-control', 'hidden' => 'hidden']) }}
        @if (strtolower($challanType) == 'advance')
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('fee_month', __('Fee Month'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::month('fee_month', date('Y-m'), ['class' => 'form-control', 'placeholder' => __('Enter Fee Month'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('advance_months', __('Advance Months'), ['class' => 'form-label']) }}<span
                        style="color: red"> *</span>
                    {{ Form::select('advance_months', ['' => 'Select Advance Months', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine', '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve'], null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span
                        style="color: red"> *</span>
                    {{ Form::date('issue_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Challan Date'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::date('due_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Due Date'), 'required' => 'required']) }}
                </div>
            </div>
        @else
            <div class="col-4">
                <div class="form-group">
                    {{ Form::label('fee_month', __('Fee Month'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::month('fee_month', date('Y-m'), ['class' => 'form-control', 'placeholder' => __('Enter Fee Month'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span
                        style="color: red"> *</span>
                    {{ Form::date('issue_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Challan Date'), 'required' => 'required']) }}
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span style="color: red">
                        *</span>
                    {{ Form::date('due_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Due Date'), 'required' => 'required']) }}
                </div>
            </div>
        @endif
        {{-- //remarks --}}
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Remarks'), 'rows' => 2]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
<script>
$(document).on('change', '#branch_input', function() {
    let branch = $(this).val();
    $.ajax({
        url: "{{ route('branch.class') }}",
        type: "POST",
        data: {
            branch_id: branch,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(result) {
            var $classSelect = $('#class_input');
            if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                $classSelect[0].customSelectInstance.destroy();
                delete $classSelect[0].customSelectInstance;
            }
            if ($classSelect.next('.custom-select-wrapper').length) {
                $classSelect.next('.custom-select-wrapper').remove();
            }
            $classSelect.removeClass('custom-select');

            $classSelect.empty().append($('<option>', { value: 'all', text: 'All Class' }));
            result.forEach(cls => {
                $classSelect.append($('<option>', { value: cls.id, text: cls.name }));
            });

            $classSelect.addClass('custom-select').show();
            if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                window.CustomSelect.create($classSelect[0]);
            }

            $('#student_input').html('<option value="">Select Student</option>');
        }
    });
});

$(document).on('change', '#class_input', function() {
    let classId = $(this).val();
    $.ajax({
        url: "{{ route('class.student_head') }}",
        type: "POST",
        data: {
            class_id: classId,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(data) {
            var $studentSelect = $('#student_input');
            if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                $studentSelect[0].customSelectInstance.destroy();
                delete $studentSelect[0].customSelectInstance;
            }
            if ($studentSelect.next('.custom-select-wrapper').length) {
                $studentSelect.next('.custom-select-wrapper').remove();
            }
            $studentSelect.removeClass('custom-select');

            $studentSelect.empty().append($('<option>', { value: '', text: 'Select Student' }));
            data.student.forEach(std => {
                $studentSelect.append($('<option>', { value: std.roll_no, text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std.fathername }));
            });

            $studentSelect.addClass('custom-select').show();
            if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                window.CustomSelect.create($studentSelect[0]);
            }
        }
    });
});

</script>
