@extends('layouts.admin')
@section('page-title')
{{__('Manage Student Withdrawal')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{asset('js/jquery.repeater.min.js')}}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script>
// $(document).on('change', '#branch_from', function() {
//     var branch = $(this).val();

//     $.ajax({
//         url: '{{route('branch.class')}}',
//         type: 'POST',
//         data: {
//             "branch_id": branch,
//             "_token": "{{ csrf_token() }}",
//         },
//         success: function(data) {

//             $('#class_from').empty();
//             $('#class_from').append('<option value="">{{__('Select Class ')}}</option>');

//             for (let index = 0; index < data.length; index++) {
//                 $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[index][
//                     'name'
//                 ] + '</option>');
//             }
//         }
//     });
// });
// // $(document).on('change', '#branch_to', function () {
// //     var branch = $(this).val();

// //     $.ajax({
// //         url: '{{route('branch.class')}}',
// //         type: 'POST',
// //         data: {
// //             "branch_id": branch, "_token": "{{ csrf_token() }}",
// //         },
// //         success: function (data) {

// //             $('#class_to').empty();
// //             $('#class_to').append('<option value="">{{__('Select Class')}}</option>');
// //             $('#section_to').empty();
// //             $('#section_to').append('<option value="">{{__('Select Section')}}</option>');

// //                 for (let index = 0; index < data.length; index++) {
// //                     $('#class_to').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +'</option>');
// //                 }
// //         }
// //     });
// // });

// $(document).on('change', '#class_from', function() {
//     var class_id = $(this).val();

//     $.ajax({
//         url: '{{route('class.student_head')}}',
//         type: 'POST',
//         data: {
//             "class_id": class_id,
//             "_token": "{{ csrf_token() }}",
//         },
//         success: function(data) {

//             var s = `{{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
//                             <select id="class_students" name="student_id" class="form-control select" required="required">
//                                 <option value="" selected disabled>{{ __('Select Student') }}</option>`;

//                     for (let index = 0; index < data.student.length; index++) {
//                         s +=`<option value="${ data.student[index]['roll_no']}">${ data.student[index]['roll_no']} - ${data.student[index]['stdname']} s/d/o ${data.student[index]['fathername']} </option>`;
//                     }
//                     s += `</select>`;
//                     $('.std_data').empty().html(s);
//                     if(data.length != 0){
//                         $('#class_students').addClass('js-searchBox');
//                         JsSearchBox();
//                         updateWidths();
//                     }

//         }
//     });
// });
// $(document).on('change', '#class_to', function () {
//     var class_id = $(this).val();

//     $.ajax({
//         url: '{{route('class.section')}}',
//         type: 'POST',
//         data: {
//             "class_id": class_id, "_token": "{{ csrf_token() }}",
//         },
//         success: function (data) {

//             $('#section_to').empty();
//             // $('#section_to').append('<option value="">{{__('Select Section')}}</option>');
//                 for (let index = 0; index < data.length; index++) {
//                     $('#section_to').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +'</option>');
//                 }
//         }
//     });
// });
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('All Withdrawal Students')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    {{-- @can('create session') --}}
    <a href="#" data-size="lg" data-url="{{ route('withdrawlstudent.create') }}" data-ajax-popup="true"
         data-bs-title="{{__('Create Withdrawl Application')}}" class="mx-1 btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Create</span>
    </a>
    {{-- @endcan --}}
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(['route' => ['withdrawlstudent.index'], 'method' => 'GET', 'id' => 'withdrawlstudent']) }}
                    <div class="row d-flex justify-content-end ">
                        @if(\Auth::user()->type == 'company')
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' ]) }}
                            </div>
                        </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'),['class'=>'form-label'])}}
                                {{ Form::select('status', $status, isset($_GET['status']) ? $_GET['status'] : 'draft', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Period From'),['class'=>'form-label'])}}
                                {{ Form::date('start_date', isset($_GET['start_date'])? $_GET['start_date']:$dateFrom, array('class' => 'form-control')) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2" >
                            <div class="btn-box">
                                {{ Form::label('end_date', __('Period To'),['class'=>'form-label'])}}
                                {{ Form::date('end_date', isset($_GET['end_date'])? $_GET['end_date']:$dateTo, array('class' => 'form-control')) }}
                            </div>
                        </div>

                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('withdrawlstudent').submit(); return false;"
                                  title="Search" data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('withdrawlstudent.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                  title="Clear Filter" data-bs-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon">Clear</span>
                            </a>
                            {{-- <a href="#" onclick="printReport(); return false;" class="btn mx-1 btn-sm btn-outline-success"
                                 title="" title="Print">
                                <span class="btn-inner--icon">Print
                                </span>
                            </a> --}}
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table class="datatable">
        <thead>
        <tr class="table_heads">
            <th>{{__('#')}}</th>
            <th>{{__('Branch')}}</th>
            <th>{{__('Withdraw date')}}</th>
            <th>{{__('Roll#')}}</th>
            <th>{{__('Student')}}</th>
            <th>{{__('Father Name')}}</th>
            <th>{{__('Class')}}</th>
            <th>{{__('Reason')}}</th>
            <th>{{__('Status')}}</th>
            <th width="200px">{{__('Action')}}</th>
        </tr>
        </thead>
        <tbody>
            @foreach ($studentwithdrawal as $transfer)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ (!empty($transfer->branch))? @$transfer->branch->name : '-' }}</td>
                <td>{{ (!empty($transfer->withdraw_date))? $transfer->withdraw_date : '-' }}</td>
                <td>{{ (!empty($transfer->student))? @$transfer->student->roll_no : '-' }}</td>
                <td>{{ (!empty($transfer->student))? @$transfer->student->stdname : '-' }}</td>
                <td>{{ (!empty($transfer->student))? @$transfer->student->fathername : '-' }}</td>
                <td>{{ (!empty($transfer->class))? @$transfer->class->name : '-' }}</td>
                <td>{{ (!empty($transfer->remark))? @$transfer->remark : '-' }}</td>
                <td>{{ (!empty($transfer->status))? $transfer->status : '-' }}</td>
                <td>
                    <div class="action-btnnn ms-2" style=" /* width: 100px; */
                            height: 28px;
                            border-radius: 9.3552px;
                            color: #fff;
                            display: inline-flex
                        ;
                            align-items: end;
                            /* justify-content: center; */
                            font-size: 20px;">
                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin' || $transfer->status == 'draft')
                        <a href="#!" data-size="lg" data-url="{{route('withdrawlstudent.edit', $transfer->id)}}"
                            data-ajax-popup="true" class="mx-1 btn btn-sm btn-outline-primary"
                             data-bs-title="{{__('Edit')}}">
                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                        @endif
                        @if ($transfer->status == 'draft')
                            {!! Form::open(['method' => 'POST', 'route' => ['withdrawlstudent.reactive', $transfer->id], 'id' => 'reactive-form-'.$transfer->id, 'class' => 'd-inline']) !!}
                            <a href="#" role="button" class="mx-1 btn btn-sm btn-outline-warning bs-pass-para"
                                title="{{ __('Reactive') }}" data-bs-toggle="tooltip" data-bs-title="{{__('Reactive')}}"
                                data-confirm="{{__('Are You Sure?').'|'.__('This will reactivate the withdrawal and restore the student. Do you want to continue?')}}"
                                data-confirm-yes="document.getElementById('reactive-form-{{$transfer->id}}').submit();">
                                <span class="btn-inner--icon"><i class="ti ti-refresh"></i></span>
                            </a>
                            {!! Form::close() !!}
                        @endif
                            
                            <a href="{{ route('withdrawlapplication', ['id' => @$transfer->id]) }}" class="mx-1 btn mx-1 btn-sm btn-outline-success"  data-bs-title="{{__('Withdrawal Application')}}">
                                <span class="btn-inner--icon"><i class="ti ti-eye"></i></</span></a>
                                <a href="{{ route('student_withdrawal.settlement_certificate', ['id' => @$transfer->id]) }}" target="_blank" class="mx-1 btn mx-1 btn-sm btn-outline-success"  data-bs-title="{{__('Clearance Certificate')}}">
                                    <span class="btn-inner--icon"><i class="ti ti-list"></i></span></a>
                                    @if ($transfer->status == 'approved')
                                    <a href="{{ route('student_withdrawal.certificate_print', $transfer->id) }}" target="_blank" class="mx-1 btn mx-1 btn-sm btn-outline-success"  data-bs-title="{{__('Print')}}">
                                        <span class="btn-inner--icon"><i class="ti ti-printer"></i></span></a>
                                    @endif
                                    {{-- @endcan --}}
                                    {{--
                                                    <a href="{{ route('transferstudent.change_status', [$transfer->id, 'For Approval']) }}"
                        class="mx-1 btn mx-1 btn-sm btn-outline-success" id="change-status" 
                        title="Send For Approval"
                        data-bs-title="{{__('Send For Approval')}}"><span class="btn-inner--icon"><i class="ti ti-eye"></i></span></a>

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['transferstudent.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id])!!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}"
                            data-bs-title="{{__('Delete')}}"
                            data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                            data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();"><i
                                class="ti ti-trash text-white"></i></a>
                            {!! Form::close()!!}
                            --}}
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

{{-- <script>
    $('.change-status').click(function () {
    var id = $(this).data('id');
    var status = $(this).data('status');

        $.ajax({
            url: '{{ route('concession.change_status') }}',
type: 'POST',
data: {
id: id,
status: status,
_token: '{{ csrf_token() }}'
},
success: function (response) {
if (response.success) {
// Update the button text and data-status attribute
if (status == 1) {
$(this).text('Deactivate');
$(this).data('status', 0);
} else {
$(this).text('Activate');
$(this).data('status', 1);
}

// Show a success message
alert(response.success);
}
}
});
});
</script> --}}
