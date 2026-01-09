@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Transfer Student') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        $(document).on('change', '#branch_from', function() {
            var branch = $(this).val();

            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#class_from').empty();
                    $('#class_from').append('<option value="">{{ __('Select Class') }}</option>');
                    var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option> </select>`;
                    $('.std_data').empty().html(s);

                    for (let index = 0; index < data.length; index++) {
                        $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                }
            });
        });
        $(document).on('change', '#branch_to', function() {
            var branch = $(this).val();

            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#class_to').empty();
                    $('#class_to').append('<option value="">{{ __('Select Class') }}</option>');
                    $('#section_to').empty();
                    $('#section_to').append('<option value="">{{ __('Select Section') }}</option>');

                    for (let index = 0; index < data.length; index++) {
                        $('#class_to').append('<option value="' + data[index]['id'] + '">' + data[index]
                            ['name'] + '</option>');
                    }
                }
            });
        });


        $(document).on('change', '#class_from', function() {
            var class_id = $(this).val();

            $.ajax({
                url: '{{ route('class.student_head') }}',
                type: 'POST',
                data: {
                    "class_id": class_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var s = `{{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                            <select id="class_students" name="student_id" class="form-control select" required="required">
                                <option value="" selected disabled>{{ __('Select Student') }}</option>`;

                    for (let index = 0; index < data.student.length; index++) {
                        s +=
                        `<option value="${ data.student[index]['roll_no']}">${ data.student[index]['roll_no']} - ${data.student[index]['stdname']} s/d/o ${data.student[index]['fathername']} </option>`;
                    }
                    s += `</select>`;
                    $('.std_data').empty().html(s);
                    if (data.length != 0) {
                        $('#class_students').addClass('js-searchBox');
                        JsSearchBox();
                        updateWidths();
                    }
                }
            });
        });
        $(document).on('change', '#class_to', function() {
            var class_id = $(this).val();

            $.ajax({
                url: '{{ route('class.section') }}',
                type: 'POST',
                data: {
                    "class_id": class_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#section_to').empty();
                    // $('#section_to').append('<option value="">{{ __('Select Section') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#section_to').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                }
            });
        });

        // $(document).on('change', '#type', function () {
        //     var type = $(this).val();
        //     if(type == 'inter branch'){
        //         $('.dis').attr('readonly', true);
        //     } else {
        //         $('.dis').attr('readonly', false);
        //     }
        // })
        function updateSecondDropdown() {
            const branch1 = document.getElementById("branch_from").value;
            const branch2 = document.getElementById("branch_to");
            const options = branch2.options;

            // Enable all options first
            for (let i = 0; i < options.length; i++) {
                options[i].disabled = false;
            }

            // Disable the option that matches the value of the first dropdown
            if (branch1) {
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === branch1) {
                        options[i].disabled = true;
                        break;
                    }
                }
            }
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Transfer Student') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}

            <a href="#" data-size="xl" data-url="{{ route('transferstudent.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create Transfer Application')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create</span>
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
                        {{ Form::open(['route' => ['transferstudent.index'], 'method' => 'GET', 'id' => 'transferstudent']) }}
                        <div class="row d-flex justify-content-end ">
                            @if (\Auth::user()->type == 'company')
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            {{-- @dd($dateFrom) --}}
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Period From'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : $dateFrom, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('Period To'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : $dateTo, ['class' => 'form-control']) }}
                                </div>
                            </div>


                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('transferstudent').submit(); return false;"
                                  title="Search" data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('transferstudent.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
            <thead class="table_heads">

                <tr >
                    <th>{{ __('#') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Roll#') }}</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Father Name') }}</th>
                    <th>{{ __('Transfer From') }}</th>
                    <th>{{ __('Transfer To') }}</th>
                    <th>{{ __('Transfer Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($studenttransfer as $transfer)
                    <tr>

                        <td>{{  $loop->iteration }}
                        </td>
                        <td>{{ !empty($transfer->transfer_date) ? @$transfer->transfer_date : '-' }}</td>
                        <td>{{ !empty($transfer->student_id) ? @$transfer->student_id : '-' }}</td>
                        <td>{{ !empty($transfer->student) ? @$transfer->student->stdname : '-' }}</td>
                        <td>{{ !empty($transfer->student) ? @$transfer->student->fathername : '-' }}</td>
                        <td>{{ !empty($transfer->branchfrom) ? @$transfer->branchfrom->name : '-' }}</td>
                        <td>{{ !empty($transfer->branchto) ? @$transfer->branchto->name : '-' }}</td>
                        <td>{{ !empty($transfer->transfer_type) ? $transfer->transfer_type : '-' }}</td>
                        <td>{{ !empty($transfer->status) ? $transfer->status : '-' }}</td>
                        <td>
                            <div class="action-btn ms-2">
                                {{-- @can('edit section') --}}
                                @if (@$transfer->status != 'approved' && @$transfer->status != 'rejected')
                                    <a href="#!" data-url="{{ route('transferstudent.edit', $transfer->id) }}"
                                        data-size="xl" data-ajax-popup="true"
                                        class="mx-1 btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                        data-bs-title="{{ __('Edit Application') }}"
                                        data-bs-title="{{ __('Edit') }}"><span class="btn-inner--icon"><i
                                                class="ti ti-pencil "></i></span></a>
                                @endif
                                <a href="{{ route('transferapplication', $transfer->id) }}"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-success"
                                    data-bs-title="{{ __('Transfer Application Details') }}" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('Transfer Application') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-eye "></i></span></a>
                                @if (@$transfer->status != 'rejected')
                                    <a href="{{ route('transferstudent.show', $transfer->id) }}"
                                        class="mx-1 btn mx-1 btn-sm btn-outline-success" data-bs-toggle="tooltip"
                                        data-bs-title="{{ __('Transfer Order') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-file-text"></i></span>
                                    </a>
                                @endif
                                {{-- @endcan --}}
                                {{--
                                <a href="{{ route('transferstudent.change_status', [$transfer->id, 'For Approval']) }}"  class="mx-3 btn btn-sm align-items-center" id="change-status"  data-bs-toggle="tooltip" title="Send For Approval"
                                data-bs-title="{{__('Send For Approval')}}"><i class="ti ti-eye text-white"></i></a>
                             --}}


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
