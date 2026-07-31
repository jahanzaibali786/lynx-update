{{ Form::model($warehouse, array('route' => array('store.update', $warehouse->id), 'method' => 'PUT')) }}

<div class="modal-body">

    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-6">

            {{ Form::label('name', __('Store Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
            @error('name')
            <small class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </small>
            @enderror
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::select('branch_id', $branches,$warehouse->owned_by, array('class' => 'form-control','id'=>'store-branch-id','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('assigned_employee_id', __('Assigned Employee'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
            {{ Form::select('assigned_employee_id', [], null, array('class' => 'form-control','id'=>'store-assigned-employee-id','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('city',__('City'),array('class'=>'form-label')) }}
            {{Form::text('city',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('city_zip',__('Zip Code'),array('class'=>'form-label')) }}
            {{Form::text('city_zip',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('address',__('Address'),array('class'=>'form-label')) }}
            {{Form::textarea('address',null,array('class'=>'form-control','rows'=>1,'required'=>'required'))}}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    (function () {
        var employeesByBranch = @json($employeesByBranch);
        var selectedEmployee = @json(old('assigned_employee_id', $warehouse->assigned_employee_id));
        var $branch = $('#store-branch-id');
        var $employee = $('#store-assigned-employee-id');

        function refreshStoreEmployees() {
            var employees = employeesByBranch[$branch.val()] || [];
            $employee.empty().append($('<option>', { value: '', text: @json(__('Select Employee')) }));

            $.each(employees, function (index, employee) {
                $employee.append($('<option>', {
                    value: employee.id,
                    text: employee.name,
                    selected: String(employee.id) === String(selectedEmployee)
                }));
            });
        }

        $branch.off('change.storeEmployee').on('change.storeEmployee', function () {
            selectedEmployee = '';
            refreshStoreEmployees();
        });
        refreshStoreEmployees();
    })();
</script>
