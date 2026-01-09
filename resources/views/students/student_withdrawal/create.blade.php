{{ Form::open(['url' => 'withdrawlstudent']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('document_no', __('Document No'), ['class' => 'form-label']) }}<span style="color:red">*</span>
                {{ Form::text('document_no', $document_no, ['class' => 'form-control','placeholder'=>__('Enter Document No'),'required'=>'required','readonly'=>'readonly']) }}
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
                {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::select('student_id', ['' => 'Select Student'], null, ['class' => 'form-control custom-select', 'id' => 'class_students', 'required' => 'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('withdraw_date', __('Withdraw Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::date('withdraw_date', null, ['class' => 'form-control','placeholder'=>__('Enter Withdraw Date'),'required'=>'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('application_date', __('Application Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::date('application_date', null, ['class' => 'form-control','placeholder'=>__('Enter Application Date'),'required'=>'required']) }}
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::select('reason', [
                    'School Change' => 'School Change',
                    'Dissatisfaction Academics, Teachers, Management' => 'Dissatisfaction Academics, Teachers, Management',
                    'Transport Issue' => 'Transport Issue',
                    'Fee defaulter' => 'Fee defaulter',
                    'Fee Affordability Issue' => 'Fee Affordability Issue',
                    'Passing Out' => 'Passing Out',
                    'Other' => 'Other'
                ], null, ['class' => 'form-control','required'=>'required']) }}
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                {{ Form::textarea('remark', null, ['class' => 'form-control','placeholder'=>__('Enter Remarks'),'required'=>'required','rows'=>2]) }}
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
    // --- Helpers -------------------------------------------------------------
    function destroyCustomSelect($el) {
        if ($el && $el[0] && $el[0].customSelectInstance) {
            $el[0].customSelectInstance.destroy();
            delete $el[0].customSelectInstance;
        }
        if ($el && $el.next('.custom-select-wrapper').length) {
            $el.next('.custom-select-wrapper').remove();
        }
        $el.removeClass('custom-select');
    }

    // Aggressive removal of any legacy JsSearch wrappers/classes if present
    function purgeJsSearch($el) {
        if (!$el || !$el.length) return;
        $el.removeClass('js-searchBox'); // prevent re-init
        // common wrappers / artifacts used by JsSearch-like plugins
        $el.siblings('.searchBoxElement, .searchbox-container, .js-search-wrapper').remove();
        if ($el.parent().hasClass('searchBoxElement') || $el.parent().hasClass('js-search-wrapper')) {
            $el.unwrap();
        }
        // make sure the original select is visible
        $el.show();
    }

    function reinitCustomSelect($el) {
        $el.addClass('custom-select').show();
        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
            window.CustomSelect.create($el[0]);
        }
    }

    // --- On load: sanitize and kick off branch -> class cascade --------------
    $(document).ready(function () {
        // Rebuild student block to ensure no leftover JS-search markup from server-side render
        $('.std_data').html(`
            {!! str_replace("\n", "", Form::label('student_id', __('Student'), ['class' => 'form-label'])->toHtml()) !!}<span style="color:red"> *</span>
            <select id="class_students" name="student_id" class="form-control custom-select" required>
                <option value="">{{ __('Select Student') }}</option>
            </select>
        `);

        purgeJsSearch($('#class_students'));
        destroyCustomSelect($('#class_students'));
        reinitCustomSelect($('#class_students'));

        // Trigger branch change to populate classes
        $('#branch_from').trigger('change');
    });

    // --- Branch -> Class -----------------------------------------------------
    $(document).on('change', '#branch_from', function () {
        var branch = $(this).val();

        $.ajax({
            url: '{{ route('branch.class') }}',
            type: 'POST',
            data: { branch_id: branch, _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function (data) {
                var $classSelect = $('#class_from');

                // clean any plugins
                purgeJsSearch($classSelect);
                destroyCustomSelect($classSelect);

                // rebuild options
                $classSelect.empty().append($('<option>', { value: '', text: "{{ __('Select Class') }}" }));
                for (var i = 0; i < data.length; i++) {
                    $classSelect.append($('<option>', { value: data[i].id, text: data[i].name }));
                }

                reinitCustomSelect($classSelect);

                // reset students select
                var $studentSelect = $('#class_students');
                purgeJsSearch($studentSelect);
                destroyCustomSelect($studentSelect);
                $studentSelect.empty().append($('<option>', { value: '', text: "{{ __('Select Student') }}" }));
                reinitCustomSelect($studentSelect);
            }
        });
    });

    // --- Class -> Students ---------------------------------------------------
    $(document).on('change', '#class_from', function () {
        var class_id = $(this).val();

        $.ajax({
            url: '{{ route('class.student_head') }}',
            type: 'POST',
            data: { class_id: class_id, _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function (data) {
                var $studentSelect = $('#class_students');

                purgeJsSearch($studentSelect);
                destroyCustomSelect($studentSelect);

                $studentSelect.empty()
                    .append($('<option>', { value: '', text: "{{ __('Select Student') }}" }));

                for (var j = 0; j < data.student.length; j++) {
                    var s = data.student[j];
                    $studentSelect.append($('<option>', {
                        value: s.roll_no,
                        text: s.roll_no + ' - ' + s.stdname + ' s/d/o ' + s.fathername
                    }));
                }

                reinitCustomSelect($studentSelect);
            }
        });
    });
</script>
