{{ Form::model($studentWithdrawal, ['route' => ['withdrawlstudent.update', $studentWithdrawal->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('document_no', __('Document No'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('document_no', null, ['class' => 'form-control', 'placeholder' => __('Enter Document No'), 'required' => 'required','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'id' => 'branch_from', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', $class_from, null, ['class' => 'form-control select', 'id' => 'class_from', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('student_id', $std, null, ['class' => 'form-control select', 'id' => 'class_students', 'required' => 'required', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('withdraw_date', __('Withdraw Date'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::date('withdraw_date', null, ['class' => 'form-control ', 'placeholder' => __('Enter Withdraw Date'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('application_date', __('Application Date'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::date('application_date', $studentWithdrawal->apply_date, ['class' => 'form-control ', 'placeholder' => __('Enter Application Date'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('reason', ['School Change' => 'School Change', 'Dissatisfaction Academics, Teachers, Management' => 'Dissatisfaction Academics, Teachers, Management', 'Transport Issue' => 'Transport Issue', 'Fee defaulter' => 'Fee defaulter', 'Fee Affordability Issue' => 'Fee Affordability Issue', 'Passing Out' => 'Passing Out', 'Other' => 'Other'], null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
        <input type="hidden" name="is_po" id="is_po" value="{{ $studentWithdrawal->is_po ?? 0 }}">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'placeholder' => __('Enter Remarks'), 'required' => 'required', 'rows' => 2]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer"> 
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    $(document).on('change', '#reason', function () {
        $('#is_po').val($(this).val() === 'Passing Out' ? '1' : '0');
    });

    JsSearchBox();

    function updateWidths() {
        // Get all elements with the class 'refineText'
        var refineTextElements = document.querySelectorAll('.refineText');
        // Iterate over each 'refineText' element
        refineTextElements.forEach(function(refineText) {
            // Get the parent element of the current refineText element
            var parentElement = refineText.parentNode;

            // Check if the parent element exists and has a valid width
            if (parentElement && parentElement.offsetWidth) {
                // Get the width of the parent element
                var parentWidth = parentElement.offsetWidth;
                // Set the width of the refineText element to match its parent's width
                refineText.style.width = parentWidth + 'px';
            }
        });

        // Get the width of the first refineText element's parent
        var parentElement = refineTextElements[0].parentNode;
        var parentWidth = parentElement.offsetWidth;
        // Apply the width to all elements with the class 'searchBoxElement'
        var searchBoxElements = document.querySelectorAll('.searchBoxElement');
        searchBoxElements.forEach(function(element) {
            element.style.width = parentWidth + 'px';
        });

        // Set the border color for the first refineText element
        refineTextElements[0].style.borderColor = '#100773';
    }

    // Call the function initially to set the initial widths based on the parent's width
    setTimeout(function() {
        updateWidths();
    }, 1000); // Delay of 1 second (1000 milliseconds)

    $(document).on('change', '#branch_from', function() {
        var branch = $(this).val();

        $.ajax({
            url: '{{ route('branch.class') }}',
            type: 'POST',
            data: {
                "branch_id": branch,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {

                $('#class_from').empty();
                $('#class_from').append('<option value="">{{ __('Select Class ') }}</option>');

                for (let index = 0; index < data.length; index++) {
                    $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[
                        index][
                        'name'
                    ] + '</option>');
                }
            }
        });
    });

    $(document).on('change', '#class_from', function() {
        var class_id = $(this).val();

        $.ajax({
            url: '{{ route('class.student_head') }}',
            type: 'POST',
            data: {
                "class_id": class_id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {

                var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option>`;

                for (let index = 0; index < data.student.length; index++) {
                    s +=
                    `<option value="${ data.student[index]['roll_no']}">${ data.student[index]['roll_no']} - ${data.student[index]['stdname']} s/d/o ${data.student[index]['fathername']} </option>`;
                }
                s += `</select>`;
                $('.std_data').empty().html(s);
                if (data.length != 0) {
                    $('#class_students').addClass('js-searchBox');
                    JsSearchBox();
                    updateWidths();
                }

            }
        });
    });
</script>
