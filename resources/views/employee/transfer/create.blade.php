<script>
    $(document).ready(function() {
        // User wants to allow inter-branch transfer, so we do not prevent selecting same branch
    });
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
    }

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
            url: '{{route('employee.json')}}',
            type: 'POST',
            data: {
                "department_id": id,
                "_token": "{{ csrf_token() }}",
            },
            success: function (data) {
                $('#desig_id').empty();
                $('#desig_id').append('<option value="">{{__('Select Designation')}}</option>');
                $.each(data, function (key, value) {
                    $('#desig_id').append('<option value="' + key + '">' + value + '</option>');
                });

                if (selectedDesignationId) {
                    $('#desig_id').val(String(selectedDesignationId)).trigger('change');
                }
            }
        });
    }
</script>
{{ Form::open(['url' => 'employee-transfer', 'method' => 'post', 'class' => 'employee-transfer-ajax-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_from_id', __('Branch From'),['class'=>'form-label'])}}
            {{ Form::select('branch_from_id', $from_branches, isset($_GET['branch_id']) ? $_GET['branch_id'] : '', ['class' => 'form-control select' ,  'onchange' => 'branchemployees(this.value)','id'=>'branch_from']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_to_id', __('Branch To'),['class'=>'form-label'])}}
            {{ Form::select('branch_to_id', $to_branches, isset($_GET['branch_id']) ? $_GET['branch_id'] : '', ['class' => 'form-control select' , 'onchange' => 'getDepartments(this.value)','id'=>'branch_to']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'),['class'=>'form-label'])}}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control select custom-select','id' => 'employee_id','required'=>'required', 'onchange' => 'employeedep(this.value)')) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('transfer_date',__('Transfer Date'),['class'=>'form-label'])}}
            {{Form::date('transfer_date',null,array('class'=>'form-control '))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_from_id',__('Department From'),['class'=>'form-label'])}}
            {{Form::text('department_from_id',null,  array('class'=>'form-control select','id'=>'exist_dept','readonly'=>'readonly'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_to_id',__('Department To'),['class'=>'form-label'])}}
            {{Form::select('department_to_id',$departments,null,array('class'=>'form-control select','id'=>'dec_id', 'onchange' => 'getDesignation(this.value)'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('designation_from_id',__('Designation From'),['class'=>'form-label'])}}
            {{Form::text('designation_from_id',null,  array('class'=>'form-control select','id'=>'exist_desig','readonly'=>'readonly'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('designation_to_id',__('Designation To'),['class'=>'form-label'])}}
            {{Form::select('designation_to_id',$designations,null,array('class'=>'form-control select','id'=>'desig_id'))}}
        </div>
        <div class="form-group col-lg-6">
            {{Form::label('Transfer Type',__('Description'),['class'=>'form-label'])}}
            <select name="transfer_type" class="form-select" >
                <option value="">Transfer Type</option>
                <option value="inter-branch">Inter Branch</option>
                <option value="inter-city">Inter City</option>
            </select>
        </div>
        <div class="form-group col-lg-6">
            {{Form::label('scale',__('Last Pay Scale'),['class'=>'form-label'])}}
            {{Form::text('scale',null,array('class'=>'form-control','id'=>'scale','Readonly'=>'Readonly'))}}
        </div>
        <div class="form-group col-lg-6">
            {{Form::label('gross',__('Net Pay'),['class'=>'form-label'])}}
            {{Form::text('gross',null,array('class'=>'form-control','id'=>'gross','Readonly'=>'Readonly'))}}
        </div>
        <div class="form-group col-lg-12">
            {{Form::label('description',__('Description'),['class'=>'form-label'])}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>'3','placeholder'=>__('Enter Description')))}}
        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{ Form::close() }}
<script>
    ajaxModalForm({
        formSelector: '.employee-transfer-ajax-form',
        submitText: '{{ __('Creating...') }}',
        onSuccess: function() {
            if (typeof window.refreshEmployeeTransferContent === 'function') {
                window.refreshEmployeeTransferContent();
            }
        }
    });
</script>
