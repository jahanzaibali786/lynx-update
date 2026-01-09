@extends('layouts.admin')
@section('page-title')
    {{__('Manage Transfer Student')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{asset('js/jquery.repeater.min.js')}}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        $(document).on('change', '#branch_from', function () {
            var branch = $(this).val();

            $.ajax({
                url: '{{route('branch.class')}}',
                type: 'POST',
                data: {
                    "branch_id": branch, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#class_from').empty();
                    $('#class_from').append('<option value="">{{__('Select Class')}}</option>');
                    var s = `{{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option> </select>`;
                    $('.std_data').empty().html(s);

                        for (let index = 0; index < data.length; index++) {
                            $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +'</option>');
                        }
                }
            });
        });

        $(document).on('change', '#class_from', function () {
            var class_id = $(this).val();

            $.ajax({
                url: '{{route('class.readmission')}}',
                type: 'POST',
                data: {
                    "class_id": class_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    var s = `{{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option>`;

                    for (let index = 0; index < data.student.length; index++) {
                        s +=`<option value="${ data.student[index]['roll_no']}">${ data.student[index]['roll_no']} - ${data.student[index]['stdname']} s/d/o ${data.student[index]['fathername']} </option>`;
                    }
                    s += `</select>`;
                    $('.std_data').empty().html(s);
                        if(data.length != 0){
                            $('#class_students').addClass('js-searchBox');
                            JsSearchBox();
                        }
                }
            });
        });

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('All Re-Admission Student')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
            <a href="#" data-size="lg" data-url="{{ route('readmissionstudent.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create</span>
            </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')

    <div class="table-responsive" style="margin-top: 25px !important">
        <table class="datatable">
            <thead>
            <tr class="table_heads">
                <th>{{__('#')}}</th>
                <th>{{__('Date')}}</th>
                <th>{{__('Roll#')}}</th>
                <th>{{__('Student')}}</th>
                <th>{{__('Father Name')}}</th>
                <th>{{__('Branch')}}</th>
                <th>{{__('Class')}}</th>
                <th>{{__('Session')}}</th>
                <th>{{__('Status')}}</th>
                <th>{{__('Action')}}</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($studenttransfer as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ (!empty($transfer->readmission_date))? @$transfer->readmission_date : '-' }}</td>
                        <td>{{ (!empty($transfer->student_id))? @$transfer->student_id : '-' }}</td>
                        <td>{{ (!empty($transfer->student))? @$transfer->student->stdname : '-' }}</td>
                        <td>{{ (!empty($transfer->student))? @$transfer->student->fathername : '-' }}</td>
                        <td>{{ (!empty($transfer->branch))? @$transfer->branch->name : '-' }}</td>
                        <td>{{ (!empty($transfer->class))? @$transfer->class->name : '-' }}</td>
                        <td>{{ (!empty($transfer->session))? $transfer->session->year : '-' }}</td>
                        <td>{{ (!empty($transfer->status))? $transfer->status : '-' }}</td>
                        <td>
                            <div class="action-btn ms-2">
                                {{-- @can('edit section') --}}
                                @if(@$transfer->status != 'approved' && @$transfer->status != 'rejected')
                                    <a href="#!" data-url="{{route('readmissionstudent.edit',$transfer->id)}}"  data-size="xl" data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  title="{{__('Edit')}}"
                                    ><span class="btn-inner--icon"><i class="ti ti-pencil "></i></span></a>
                                @endif
                                {{-- <a href="{{route('readmissionstudent',$transfer->id)}}" class="mx-1 btn mx-1 btn-sm btn-outline-success" data-bs-title="{{__('Transfer Application')}}" data-bs-title="{{__('Transfer Application')}}">
                            <span class="btn-inner--icon"><i class="ti ti-eye "></i></span></a> --}}
                                {{-- @endcan --}}

                            @if(@$transfer->status != 'approved' && @$transfer->status != 'rejected' && @$transfer->status != 'For Approval')
                                <a href="{{ route('readmission.change_status', [$transfer->id, 'For Approval']) }}"  class=" btn btn-sm align-items-center  btn-outline-primary" id="change-status"   title="Send For Approval"
                                data-bs-title="{{__('Send For Approval')}}"><span class="btn-inner--icon"><i class="ti ti-file text-white"></i></span></a>
                            @endif
                            @if(@$transfer->status == 'For Approval')
                                <a href="{{ route('readmission.change_status', [$transfer->id, 'approved']) }}"  class=" btn btn-sm align-items-center btn-sm btn-outline-success" id="change-status"   title="Approved"
                                data-bs-title="{{__('Approved')}}"><span class="btn-inner--icon"><i class="ti ti-file text-white"></i></span></a>
                                <a href="{{ route('readmission.change_status', [$transfer->id, 'rejected']) }}"  class=" btn btn-sm align-items-center btn-sm btn-outline-danger" id="change-status"   title="Rejected"
                                data-bs-title="{{__('Rejected')}}"><span class="btn-inner--icon"><i class="ti ti-file text-white"></i></span></a>
                            @endif


                            {{-- <div class="action-btn bg-danger ms-2">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['transferstudent.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id])!!}
                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                {!! Form::close()!!}
                            </div> --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
