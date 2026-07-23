{{ Form::model($transfer, ['route' => ['employee-transfer.update', $transfer->id], 'method' => 'PUT']) }}
<div class="modal-body">
   
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'),['class'=>'form-label'])}}
            {{ Form::select('employee_id', $employees,null, array('class' => 'form-control select','required'=>'required')) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('transfer_date',__('Transfer Date'),['class'=>'form-label'])}}
            {{Form::date('transfer_date',null,array('class'=>'form-control '))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_from_id', __('Branch From'),['class'=>'form-label'])}}
            {{ Form::select('branch_from_id', $from_branches, isset($_GET['branch_from_id']) ? $_GET['branch_from_id'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('branch_to_id', __('Branch To'),['class'=>'form-label'])}}
            {{ Form::select('branch_to_id', $to_branches, isset($_GET['branch_to_id']) ? $_GET['branch_to_id'] : '', ['class' => 'form-control select' , 'onchange' => 'getDepartments(this.value)', 'id' => 'branch_to_id']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_from_id',__('Department From'),['class'=>'form-label'])}}
            {{Form::select('department_from_id',$departments,null,array('class'=>'form-control select'))}}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{Form::label('department_to_id',__('Department To'),['class'=>'form-label'])}}
            {{Form::select('department_to_id',$departments,null,array('class'=>'form-control select', 'id' => 'dec_id'))}}
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
            }
        });
    }
</script>
