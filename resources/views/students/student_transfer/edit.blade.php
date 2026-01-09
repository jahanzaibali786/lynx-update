
{{ Form::model($studentTransfer, array('route' => array('transferstudent.update', $studentTransfer->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('branch_from', __('Branch From'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_from', [ @$studentTransfer->branch_from => @$studentTransfer->branchfrom->name], null, ['class' => 'form-control select', 'id' => 'branch_from1', 'required' => 'required','readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_from', __('Class From'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_from', $class_from, null, ['class' => 'form-control select','id' => 'class_from1', 'required' => 'required','readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('student_id', $std, null, ['class' => 'form-control select','id' => 'class_students1', 'required' => 'required','readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('transfer_date', __('Transfer Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('transfer_date', null, array('class' => 'form-control ','placeholder'=>__('Enter Transfer Date'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('transfer_type', __('Transfer Type'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('transfer_type', ['inter branch' => 'Inter Branch', 'inter city' => 'Inter City'], null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'type',]) }}
            </div>
        </div>
{{--
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('section_from', __('Section From'),['class'=>'form-label']) }}
                {{ Form::select('section_from', $sections, null, ['class' => 'form-control select']) }}
            </div>
        </div> --}}
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('branch_to', __('Branch To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_to', $branches, null, ['class' => 'form-control select','id' => 'branch_to', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_to', __('Class To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_to', $class_to, null, ['class' => 'form-control select','id' => 'class_to', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('section_to', __('Section To'),['class'=>'form-label']) }}
                {{ Form::select('section_to', $section_to, null, ['class' => 'form-control select','id' => 'section_to','required' => 'required']) }}
            </div>
        </div>
        <div class="col-4 type">
            <div class="form-group">
                {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('issue_date', @$studentTransfer->challan->issue_date, array('class' => 'form-control ','placeholder'=>__('Enter Issue Date'),)) }}
            </div>
        </div>
        <div class="col-4 type">
            <div class="form-group">
                {{ Form::label('due_date', __('Due Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('due_date', @$studentTransfer->challan->due_date, array('class' => 'form-control ','placeholder'=>__('Enter Due Date'),)) }}
            </div>
        </div>
        <div class="col-8">
            <div class="form-group">
                {{ Form::label('reason', __('Transfer Reason'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::textarea('reason', null, array('class' => 'form-control','placeholder'=>__('Enter Reason'),'required'=>'required','rows' => 2)) }}
            </div>
        </div>
        <input type="hidden" name="challan_id" value="{{@$studentTransfer->challan->id}}">
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    $(document).ready(function() {
        $('#type').trigger('change');
        });
    JsSearchBox();
    $(document).on('change', '#type', function () {
        var type = $(this).val();
        if(type == 'inter branch'){
            $('.type').removeClass('d-none');
        }else{
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
