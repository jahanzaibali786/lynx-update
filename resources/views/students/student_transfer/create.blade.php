{{ Form::open(array('url' => 'transferstudent')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('branch_from', __('Branch From'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_from', $branches, null, ['class' => 'form-control select', 'id' => 'branch_from', 'required' => 'required', 'onchange'=>'updateSecondDropdown()']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_from', __('Class From'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_from', ['' => 'Select Class'], null, ['class' => 'form-control select','id' => 'class_from', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group std_data" >
                {{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('student_id', ['' => 'Select Student'], null, ['class' => 'form-control select','id' => 'class_students', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('transfer_date', __('Transfer Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('transfer_date', date('Y-m-d'), array('class' => 'form-control ','placeholder'=>__('Enter Transfer Date'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('transfer_type', __('Transfer Type'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('transfer_type', ['intra-city' => 'Intra-City (Within City)', 'inter-city' => 'Inter-City (Outside City)'], null, ['class' => 'form-control select', 'id' => 'transfer_type', 'required' => 'required', 'onchange' => 'handleTransferTypeChange()']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('branch_to', __('Branch To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_to', $allbranches, null, ['class' => 'form-control select','id' => 'branch_to', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_to', __('Class To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_to', ['' => 'Select Class'], null, ['class' => 'form-control  select','id' => 'class_to', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('section_to', __('Section To'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('section_to', ['' => 'Select Section'], null, ['class' => 'form-control select','id' => 'section_to','required' => 'required']) }}
            </div>
        </div>

        <!-- Challan fields - Only visible for inter-city transfers -->
        <div class="col-4 challan-field" style="display: none;">
            <div class="form-group">
                {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('issue_date', date('Y-m-d'), array('class' => 'form-control ','placeholder'=>__('Enter Issue Date'),'id'=>'issue_date')) }}
            </div>
        </div>
        <div class="col-4 challan-field" style="display: none;">
            <div class="form-group">
                {{ Form::label('due_date', __('Due Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('due_date', date('Y-m-d', strtotime(' +3 days')), array('class' => 'form-control ','placeholder'=>__('Enter Due Date'),'id'=>'due_date')) }}
            </div>
        </div>
        <div class="col-8">
            <div class="form-group">
                {{ Form::label('reason', __('Transfer Reason'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::textarea('reason', null, array('class' => 'form-control','placeholder'=>__('Enter Reason'),'required'=>'required','rows' => 2)) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    $(document).ready(function() {
        // Trigger change events to load dependent dropdowns
        $('#branch_from').trigger('change');
        $('#branch_to').trigger('change');
        
        // Initialize transfer type change handler
        handleTransferTypeChange();
        
        // Handle form submission
        $('form').on('submit', function (e) {
            var transferType = $('#transfer_type').val();
            
            // For inter-city transfers, validate challan fields
            if (transferType === 'inter-city') {
                var issueDate = $('#issue_date').val();
                var dueDate = $('#due_date').val();
                
                if (!issueDate || !dueDate) {
                    e.preventDefault();
                    alert('Issue Date and Due Date are required for Inter-City transfers.');
                    return false;
                }
                
                // Enable challan fields for submission
                $('#issue_date').prop('disabled', false);
                $('#due_date').prop('disabled', false);
            } else {
                // For intra-city transfers, disable challan fields so they're not sent
                $('#issue_date').prop('disabled', true);
                $('#due_date').prop('disabled', true);
            }
            
            // Ensure branch_to is enabled
            $('#branch_to').prop('disabled', false);
            
            return true;
        });
    });
    
    // Handle transfer type change
    function handleTransferTypeChange() {
        var transferType = $('#transfer_type').val();
        
        if (transferType === 'inter-city') {
            // Show challan fields for inter-city transfer
            $('.challan-field').show();
            $('#issue_date').prop('required', true);
            $('#due_date').prop('required', true);
        } else {
            // Hide challan fields for intra-city transfer
            $('.challan-field').hide();
            $('#issue_date').prop('required', false);
            $('#due_date').prop('required', false);
        }
    }
    
    JsSearchBox();

    function updateWidths() {
        // Get all elements with the class 'refineText'
        var refineTextElements = document.querySelectorAll('.refineText');
        
        if (refineTextElements.length === 0) return;
        
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
        if (parentElement && parentElement.offsetWidth) {
            var parentWidth = parentElement.offsetWidth;
            // Apply the width to all elements with the class 'searchBoxElement'
            var searchBoxElements = document.querySelectorAll('.searchBoxElement');
            searchBoxElements.forEach(function(element) {
                element.style.width = parentWidth + 'px';
            });

            // Set the border color for the first refineText element
            refineTextElements[0].style.borderColor = '#100773';
        }
    }

    // Call the function initially to set the initial widths based on the parent's width
    setTimeout(function() {
        updateWidths();
    }, 1000); // Delay of 1 second (1000 milliseconds)
</script>