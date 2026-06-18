{{ Form::open(['url' => 'withdrawlstudent']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('document_no', __('Document No'), ['class' => 'form-label']) }}<span
                    style="color:red">*</span>
                {{ Form::text('document_no', $document_no, ['class' => 'form-control', 'placeholder' => __('Enter Document No'), 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control', 'id' => 'branch_from', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::select('class_id', ['' => 'Select Class'], null, ['class' => 'form-control custom-select', 'id' => 'class_from', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group std_data">
                {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color:red">
                    *</span>
                {{ Form::select('student_id', ['' => 'Select Student'], null, ['class' => 'form-control custom-select', 'id' => 'class_students', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('withdraw_date', __('Withdraw Date'), ['class' => 'form-label']) }}<span
                    style="color:red"> *</span>
                {{ Form::date('withdraw_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Withdraw Date'), 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('application_date', __('Application Date'), ['class' => 'form-label']) }}<span
                    style="color:red"> *</span>
                {{ Form::date('application_date', null, ['class' => 'form-control', 'placeholder' => __('Enter Application Date'), 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::select(
                    'reason',
                    [
                        'School Change' => 'School Change',
                        'Dissatisfaction Academics, Teachers, Management' => 'Dissatisfaction Academics, Teachers, Management',
                        'Transport Issue' => 'Transport Issue',
                        'Fee defaulter' => 'Fee defaulter',
                        'Fee Affordability Issue' => 'Fee Affordability Issue',
                        'Passing Out' => 'Passing Out',
                        'Other' => 'Other',
                    ],
                    null,
                    ['class' => 'form-control', 'required' => 'required', 'id' => 'reason_select'],
                ) }}
            </div>
        </div>

        {{-- NEW: Other Reason field - only shows when Other is selected --}}
        <div class="col-6" id="other_reason_box" style="display: none;">
            <div class="form-group">
                {{ Form::label('other_reason', __('Other Reason'), ['class' => 'form-label']) }}<span
                    style="color:red"> *</span>
                {{ Form::text('other_reason', null, ['class' => 'form-control', 'placeholder' => __('Enter Other Reason'), 'maxlength' => '50', 'id' => 'other_reason_field']) }}
            </div>
        </div>

        {{-- Keep existing remarks exactly as before --}}
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'placeholder' => __('Enter Remarks'), 'required' => 'required', 'rows' => 2]) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}

<script>
    /* --------------------------------------------------------------------------
   Helpers for custom-select plugin
--------------------------------------------------------------------------- */
    function destroyCustomSelect($el) {
        if (!$el || !$el.length) return;

        if ($el[0].customSelectInstance) {
            $el[0].customSelectInstance.destroy();
            delete $el[0].customSelectInstance;
        }

        $el.removeClass('custom-select');
        $el.next('.custom-select-wrapper').remove();
    }

    function reInitCustomSelect($el) {
        if (!$el || !$el.length) return;

        $el.addClass('custom-select').show();

        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
            window.CustomSelect.create($el[0]);
        }
    }

    /* --------------------------------------------------------------------------
       On document ready
    --------------------------------------------------------------------------- */
    $(document).ready(function() {
        // Ensure selects start clean
        destroyCustomSelect($('#class_from'));
        destroyCustomSelect($('#class_students'));

        reInitCustomSelect($('#class_from'));
        reInitCustomSelect($('#class_students'));
    });

    /* --------------------------------------------------------------------------
       Branch → Classes
    --------------------------------------------------------------------------- */
    $(document).on('change', '#branch_from', function() {
        let branchId = $(this).val();
        let $classSelect = $('#class_from');
        let $studentSelect = $('#class_students');

        $.ajax({
            url: '{{ route('branch.class') }}',
            type: 'POST',
            data: {
                branch_id: branchId,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {

                /* Reset Class select */
                destroyCustomSelect($classSelect);
                $classSelect.empty().append(
                    $('<option>', {
                        value: '',
                        text: 'Select Class'
                    })
                );

                if (Array.isArray(data)) {
                    data.forEach(function(cls) {
                        $classSelect.append(
                            $('<option>', {
                                value: cls.id,
                                text: cls.name
                            })
                        );
                    });
                }

                reInitCustomSelect($classSelect);

                /* Reset Student select */
                destroyCustomSelect($studentSelect);
                $studentSelect.empty().append(
                    $('<option>', {
                        value: '',
                        text: 'Select Student'
                    })
                );
                reInitCustomSelect($studentSelect);
            }
        });
    });

    /* --------------------------------------------------------------------------
       Class → Students
    --------------------------------------------------------------------------- */
    $(document).on('change', '#class_from', function() {
        let classId = $(this).val();
        let $studentSelect = $('#class_students');

        if (!classId) {
            destroyCustomSelect($studentSelect);
            $studentSelect.empty().append(
                $('<option>', {
                    value: '',
                    text: 'Select Student'
                })
            );
            reInitCustomSelect($studentSelect);
            return;
        }

        $.ajax({
            url: '{{ route('class.student_head') }}',
            type: 'POST',
            data: {
                class_id: classId,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(response) {

                destroyCustomSelect($studentSelect);
                $studentSelect.empty().append(
                    $('<option>', {
                        value: '',
                        text: 'Select Student'
                    })
                );

                if (response.student && response.student.length) {
                    response.student.forEach(function(s) {
                        $studentSelect.append(
                            $('<option>', {
                                value: s.id, // ✅ correct value
                                text: s.roll_no + ' - ' + s.stdname + ' s/d/o ' + s
                                    .fathername
                            })
                        );
                    });
                }

                reInitCustomSelect($studentSelect);
            }
        });
    });
    $(document).on('change', '#reason_select', function () {
    if ($(this).val() === 'Other') {
        $('#other_reason_box').show();
        $('#other_reason_field').attr('required', 'required');
    } else {
        $('#other_reason_box').hide();
        $('#other_reason_field').removeAttr('required');
    }
});
</script>
