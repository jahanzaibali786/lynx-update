{{ Form::model($StudentReadmission, ['route' => ['readmissionstudent.update', $StudentReadmission->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'id' => 'branch_from', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', $class, null, ['class' => 'form-control select', 'id' => 'class_from', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group std_data">
                {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('student_id', $std ,null, ['class' => 'form-control select', 'id' => 'class_students', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('session_id', $session, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('readmission_date', __('Re-Admission Date'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::date('readmission_date', date('Y-m-d'), ['class' => 'form-control ', 'placeholder' => __('Enter Transfer Date'), 'required' => 'required']) }}
            </div>
        </div>


        <div class="col-6">
            <div class="form-group">
                {{ Form::label('month_date', __('Fee Month'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::month('month_date', date('Y-m'), ['class' => 'form-control ', 'placeholder' => __('Enter Month'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::date('issue_date', date('Y-m-d'), ['class' => 'form-control ', 'placeholder' => __('Enter Issue Date'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::date('due_date', date('Y-m-d', strtotime('+7 days')), ['class' => 'form-control ', 'placeholder' => __('Enter Due Date'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('reason', __('Re-Admission Reason'), ['class' => 'form-label']) }}<span
                    style="color: red">
                    *</span>
                {{ Form::textarea('reason', $StudentReadmission->remarks, ['class' => 'form-control', 'placeholder' => __('Enter Reason'), 'required' => 'required', 'rows' => 2]) }}
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
    $(document).ready(function() {
        $('#type').trigger('change');
    });
    JsSearchBox();
    $(document).on('change', '#type', function() {
        var type = $(this).val();
        if (type == 'inter branch') {
            $('.type').removeClass('d-none');
        } else {
            $('.type').addClass('d-none');
        }
    });

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
</script>
