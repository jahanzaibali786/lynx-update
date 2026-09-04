{{ Form::model($transfer, ['route' => ['employee-transfer.update', $transfer->id], 'method' => 'PUT', 'class' => 'employee-transfer-ajax-form']) }}
<div class="modal-body">
   
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'),['class'=>'form-label'])}}
            {{ Form::text('employee_name', $employees->get($transfer->employee_id), ['class' => 'form-control', 'readonly' => 'readonly']) }}
            {{ Form::hidden('employee_id', $transfer->employee_id) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('transfer_date',__('Transfer Date'),['class'=>'form-label'])}}
            {{Form::date('transfer_date',null,array('class'=>'form-control '))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_from_id', __('Branch From'),['class'=>'form-label'])}}
            {{ Form::text('branch_from_name', $from_branches->get($transfer->branch_from_id), ['class' => 'form-control', 'readonly' => 'readonly']) }}
            {{ Form::hidden('branch_from_id', $transfer->branch_from_id) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_to_id', __('Branch To'),['class'=>'form-label'])}}
            {{ Form::select('branch_to_id', $to_branches, isset($_GET['branch_to_id']) ? $_GET['branch_to_id'] : '', ['class' => 'form-control select' , 'onchange' => 'getDepartments(this.value)', 'id' => 'branch_to_id']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_from_id',__('Department From'),['class'=>'form-label'])}}
            {{ Form::text('department_from_name', $departments->get($transfer->department_from_id), ['class' => 'form-control', 'readonly' => 'readonly']) }}
            {{ Form::hidden('department_from_id', $transfer->department_from_id) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_to_id',__('Department To'),['class'=>'form-label'])}}
            {{Form::select('department_to_id',$departments,null,array('class'=>'form-control select', 'id' => 'dec_id', 'required' => 'required', 'onchange' => 'getDesignation(this.value)'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('designation_from_id', __('Designation From'), ['class' => 'form-label']) }}
            {{ Form::text('designation_from_name', $designations->get($transfer->designation_from_id), ['class' => 'form-control', 'readonly' => 'readonly']) }}
            {{ Form::hidden('designation_from_id', $transfer->designation_from_id) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('designation_to_id', __('Designation To'), ['class' => 'form-label']) }}
            {{ Form::select('designation_to_id', $designations, null, ['class' => 'form-control select', 'id' => 'desig_id', 'required' => 'required']) }}
        </div>
        <div class="form-group col-lg-12">
            {{Form::label('description',__('Description'),['class'=>'form-label'])}}
            {{Form::textarea('description',$transfer->transfer_reason,array('class'=>'form-control','placeholder'=>__('Enter Description')))}}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>


{{Form::close()}}
<script>
    $(document).ready(function() {
        // User wants to allow inter-branch transfer, so we do not prevent selecting same branch
    });

    function getDepartments(id) {
        $.ajax({
            url: '{{route('employee.getdepartment')}}',
            type: 'POST',
            data: {
                "branch_id": id,
                "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                $('#dec_id').empty();
                $('#dec_id').append('<option value="">{{__('Select Department')}}</option>');
                $.each(data, function (key, value) {
                    $('#dec_id').append('<option value="' + key + '">' + value + '</option>');
                });
                $('#desig_id').empty();
                $('#desig_id').append('<option value="">{{__('Select Designation')}}</option>');
            }
        });
    }

    function getDesignation(id, selectedDesignationId) {
        $.ajax({
            url: '{{ route('employee.json') }}',
            type: 'POST',
            data: {
                department_id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function (data) {
                $('#desig_id').empty();
                $('#desig_id').append('<option value="">{{ __('Select Designation') }}</option>');
                $.each(data, function (key, value) {
                    $('#desig_id').append('<option value="' + key + '">' + value + '</option>');
                });

                if (selectedDesignationId) {
                    $('#desig_id').val(String(selectedDesignationId)).trigger('change');
                }
            }
        });
    }

    ajaxModalForm({
        formSelector: '.employee-transfer-ajax-form',
        submitText: '{{ __('Updating...') }}',
        onSuccess: function() {
            if (typeof window.refreshEmployeeTransferContent === 'function') {
                window.refreshEmployeeTransferContent();
            }
        }
    });
</script>
