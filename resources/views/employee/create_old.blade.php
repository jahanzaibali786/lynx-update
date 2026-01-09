@extends('layouts.admin')
@section('page-title')
    {{__('Create Employee')}}
@endsection
@section('content')
    <div class="row">
        {{Form::open(array('route'=>array('employee.store'),'method'=>'post','enctype'=>'multipart/form-data'))}}
        {{--        <form method="post" action="{{route('employee.store')}}" enctype="multipart/form-data">--}}
        {{--        @csrf--}}
    </div>
    <div class="row">
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h5 class="mb-0">{{__('Personal Detail')}}</h5></div>
                <div class="card-body ">
                    <div class="row">
                        {!! Form::hidden('password','123456', ['class' => 'form-control','required' => 'required']) !!}
                        <div class="form-group col-md-6">
                            {!! Form::label('salute', __('Salute'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {{ Form::select('salute', ['Mr ' => 'Mr','Mrs ' => 'Mrs','Miss ' => 'Miss','Ms ' => 'Ms'], null, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('name', __('Name'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::text('name', old('name'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('f_name', __('Father/Husband Name'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::text('f_name', old('f_name'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::label('cnic', __('CNIC'), ['class' => 'form-label']) !!}
                            {!! Form::text('cnic', old('cnic'), ['class' => 'form-control','id' => 'cnic']) !!}
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('dob', __('Date of Birth'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                {!! Form::date('dob', old('dob'), ['class' => 'form-control']) !!}
                                {{-- {!! Form::text('dob', old('dob'), ['class' => 'form-control datepicker']) !!} --}}
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="form-group ">
                                {!! Form::label('gender', __('Gender'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                <div class="d-flex radio-check gap-3">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="g_male" value="Male" checked="checked" name="gender" class="custom-control-input">
                                        <label class="custom-control-label" for="g_male">{{__('Male')}}</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="g_female" value="Female" name="gender" class="custom-control-input">
                                        <label class="custom-control-label" for="g_female">{{__('Female')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('religion', __('Religion'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {{ Form::select('religion', ['Muslim' => 'Muslim', 'Non Muslim' => 'Non Muslim'], null, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('blood_group', __('Blood Group'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {{ Form::select('blood_group', ['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'], null, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('phone', __('Phone'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::number('phone',old('phone'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('email', __('Email'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::email('email',old('email'), ['class' => 'form-control','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('present_address', __('Present Address'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::textarea('present_address',old('present_address'), ['class' => 'form-control','rows'=>2]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('address', __('Permanent Address'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::textarea('address',old('address'), ['class' => 'form-control','rows'=>2]) !!}
                        </div>


                        {{-- <div class="form-group col-md-6">
                            {!! Form::label('password', __('Password'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::password('password', ['class' => 'form-control','required' => 'required']) !!}
                        </div> --}}
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="card card-fluid">
                <div class="card-header"><h5 class="mb-0">{{__('Company Detail')}}</h5></div>
                <div class="card-body employee-detail-create-body">
                    <div class="row">
                        @csrf
                        {{-- <div class="form-group col-md-12">
                            {!! Form::label('employee_id', __('Employee ID'),['class'=>'form-label']) !!}
                            {!! Form::text('employee_id', $employeesId, ['class' => 'form-control','disabled'=>'disabled']) !!}
                        </div> --}}

                        <div class="form-group col-md-6">
                            {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
                            {{ Form::select('branch_id', $branches,null, array('class' => 'form-control ','required'=>'required')) }}
                        </div>

                        <div class="form-group col-md-6">
                            {{ Form::label('department_id', __('Department'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
                            {{ Form::select('department_id', $departments,null, array('class' => 'form-control  ','id'=>'department_id','required'=>'required')) }}
                        </div>

                        <div class="form-group col-md-6 " id="des">
                            {{ Form::label('designation_id', __('Designation'),['class'=>'form-label']) }}<span class="text-danger pl-1">*</span>
                            <select class="select form-control " id="designation_id" name="designation_id"  data-placeholder="{{ __('Select Designation ...') }}">
                                <option value="">{{__('Select any Designation')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('area', __('Area'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {{ Form::select('area', ['Islamabad' => 'Islamabad', 'Rawalpindi' => 'Rawalpindi', 'Lahore' => 'Lahore'], null, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="col-md-6 ">
                            <div class="form-group ">
                                {!! Form::label('category', __('Category'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                                <div class="d-flex radio-check gap-3">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="g_regular" value="Regular" name="category" checked="checked" class="custom-control-input">
                                        <label class="custom-control-label" for="g_regular">{{__('Regular')}}</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="g_adhoc" value="Adhoc" name="category" class="custom-control-input">
                                        <label class="custom-control-label" for="g_adhoc">{{__('Adhoc')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 ">
                            {!! Form::label('company_doj', __('Date Of Joining'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::date('company_doj', null, ['class' => 'form-control ','id'=>'joining_date','required' => 'required']) !!}
                        </div>
                        <div class="form-group col-md-6 ">
                            {!! Form::label('probation_period', __('Probation Period Month'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::number('probation_period', null, ['class' => 'form-control ','id'=>'pro_date','required' => 'required', 'min' => '1']) !!}
                        </div>
                        <div class="form-group col-md-6 ">
                            {!! Form::label('probation_end', __('Probation End Date'),['class'=>'form-label']) !!}<span class="text-danger pl-1">*</span>
                            {!! Form::date('probation_end', null, ['class' => 'form-control ','id'=>'pro_end_date','required' => 'required','readonly' => 'readonly']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
                <div class="row mt-2" style="float: right; margin-right: 15px;">
                    <input type="submit" value="{{__('Submit')}}" class="btn btn-outline-primary">
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('script-page')

    <script>
        document.getElementById('cnic').addEventListener('keyup', function() {
            formatCNIC(this);
        });

        function formatCNIC(input) {
            var value = input.value.replace(/\D/g, '');
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            if (value.length > 13) {
                value = value.substring(0, 13) + '-' + value.substring(13);
            }
            input.value = value;
        }
        $(document).ready(function () {
            var d_id = $('#department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function () {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {

            $.ajax({
                url: '{{route('employee.json')}}',
                type: 'POST',
                data: {
                    "department_id": did, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    // console.log(data);
                    // $('#des').empty().append(`{{ Form::label('designation_id', __('Designation'),['class'=>'form-label']) }}
                    //         <select class="select2 form-control " id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                    //             <option value="">{{__('Select any Designation')}}</option>
                    //         </select>`);
                    $('#designation_id').empty();

                    $('#designation_id').append('<option value="">{{__('Select any Designation')}}</option>');
                    $.each(data, function (key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }
        document.getElementById('pro_date').addEventListener('keyup', function() {
            updateProbationEndDate();
        });
        document.getElementById('joining_date').addEventListener('change', function() {
            updateProbationEndDate();
        });

        function updateProbationEndDate() {
        // document.getElementById('pro_date').addEventListener('keyup', function() {
            var p_id =  parseInt($('#pro_date').val());
            var currentDate = new Date($('#joining_date').val());  // Get current date
            if (!isNaN(currentDate.getTime())) {

                if(isNaN(p_id)){
                    var currentDate = new Date($('#joining_date').val());
                    var formattedDate = currentDate.toISOString().slice(0, 10);
                    document.getElementById('pro_end_date').value = formattedDate;
                }else{

                    var futureDate = new Date(currentDate.setMonth(currentDate.getMonth() + p_id));  // Add months

                    // Format the future date as YYYY-MM-DD
                    var formattedDate = futureDate.toISOString().slice(0, 10);

                    // Set the value of the 'joining_date' input field
                    document.getElementById('pro_end_date').value = formattedDate;
                }
            }

            // });
        }


    </script>
@endpush
