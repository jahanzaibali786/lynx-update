@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Student Profiles') }}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Student Profiles') }}</li>
@endsection

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['enrollment.index'], 'method' => 'GET', 'id' => 'registration_submit']) }}
                        <div class="row d-flex justify-content-start ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('classes', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('classes', $classes, request()->get('classes', ''), ['class' => 'form-control select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('sections', __('Section'), ['class' => 'form-label']) }}
                                    {{ Form::select('sections', $sections, request()->get('sections', ''), ['class' => 'form-control select', 'id' => 'section_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('sessions', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('sessions', $sessions, request()->get('sessions', ''), ['class' => 'form-control select', 'id' => 'session_select']) }}
                                </div>
                            </div>
                            {{-- //gender  --}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                                    {{ Form::select('gender', ['' => 'Select Gender', 'Male' => 'Male', 'Female' => 'Female'], isset($_GET['gender']) ? $_GET['gender'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            {{-- //sort by alphabatically,gender,date of admission --}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('search', __('Search'), ['class' => 'form-label']) }}
                                    {{ Form::text('search', isset($_GET['search']) ? $_GET['search'] : '', ['class' => 'form-control', 'placeholder' => __('Name or Roll. No')]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('sort', __('Sort'), ['class' => 'form-label']) }}
                                    {{ Form::select('sort', ['' => 'Select Sort', 'asc' => 'A - Z', 'desc' => 'Z - A', 'date' => 'Date of Admission', 'gender' => 'Gender'], isset($_GET['sort']) ? $_GET['sort'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-outline-primary"
                                    onclick="document.getElementById('registration_submit').submit(); return false;"
                                    title="Search Filter" data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('enrollment.index') }}" class="btn btn-sm btn-outline-danger"
                                    title="Clear Filter" data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Class List
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="submit" name="class_list_export"
                                                value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" name="Class Listexport"
                                                value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}

    <table class="datatable">
        <thead class="table_heads">
            <tr>
                <th>{{ __('Sr No') }}</th>
                <th max-width="200px">{{ __('Branch') }}</th>
                <th>{{ __('Reg No') }}</th>
                <th>{{ __('Roll No') }}</th>
                <th>{{ __('Student Name') }}</th>
                <th>{{ __('Father Name') }}</th>
                <th>{{ __('Class') }}</th>
                <th>{{ __('Section') }}</th>
                <th>{{ __('Session') }}</th>
                <th>{{ __('Reg Type') }}</th>
                <th>{{ __('Admission Date') }}</th>
                <th width="200px">{{ __('Action') }}</th>
                {{-- <th width="200px">{{__('Action')}}</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($enrollments as $enroll)
               
                <tr>
                    @php
                        $studentData = App\Models\StudentRegistration::with(
                            'enrollment.class',
                            'enrollment.section',
                            'registeroption',
                            'session',
                        )
                            ->where('id', $enroll->regId)
                            ->first();
                    @endphp
                   
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ @$enroll->branch->name }}</td>
                    <td>{{ @$studentData->reg_no }}</td>
                    <td>{{ @$enroll->enrollId }}</td>
                    <td>{{ @$studentData->stdname }}</td>
                    <td>{{ @$studentData->fathername }}</td>
                    <td>{{ @$enroll->class->name }}</td>
                    <td style="width:180px; !important;">
                        <a href="#"
                            class="btn btn-sm btn-outline-primary w-100 d-flex align-items-start justify-content-start text-left"
                            style="text-align:left; min-height:38px; white-space:normal; word-break:break-word; line-height:1.2; width:160px !important;" data-size="lg" data-url="{{ route('section.show', $enroll->id) }}" data-ajax-popup="true" data-title="{{ __('Section History') }}">
                            
                            {{ $enroll->section->name ?? 'Set Section' }}
                        </a>
                    </td>
                    <td>{{ @$studentData->session->year }}</td>
                    <td>{{ @$studentData->registeroption->name }}</td>
                    <td>{{ @$enroll->adm_date ? date('d-M-Y', strtotime($enroll->adm_date)) : '-' }}</td>
                    <td>
                        <div class="action-btn ms-2">
                            <a href="{{ route('registration.show', $enroll->regId) }}"
                                class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                data-bs-title="{{ __('Show') }}" data-bs-title="{{ __('Show') }}"><span
                                    class="btn-inner--icon"><i class="ti ti-eye"></i></span></a>

                            <a href="{{ route('admission.order', $studentData->id) }}"
                                class="mx-1 btn btn-sm align-items-center btn-outline-success"
                                data-bs-title="{{ __('Admission Order') }}"
                                data-bs-title="{{ __('Admission Order') }}"><span class="btn-inner--icon"><i
                                        class="ti ti-receipt"></i></span></a>

                        </div>
                {!! Form::close() !!}
            </td> 
                </tr>
            @endforeach
        </tbody>
    </table>
    <script>
        $(document).on('change', '#class_select', function() {
            var class_id = $(this).val();

            $.ajax({
                url: '{{ route('class.section') }}',
                type: 'POST',
                data: {
                    "class_id": class_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#section_select').empty();
                    $('#section_select').append($('<option>', {
                        value: 'all',
                        text: 'All Sections'
                    }));
                    // $('#section_to').append('<option value="">{{ __('Select Section') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#section_select').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                }
            });
        });

        function branchcustomer(id) {
            var customer = $('#customerselect').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {

                    if (result.status == 'success') {
                        var $classSelect = $('#class_select');
                        // Remove previous custom select wrapper and instance
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');

                        // Clear and append new options
                        $classSelect.empty();
                        $classSelect.append($('<option>', {
                            value: 'all',
                            text: 'All Class'
                        }));
                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }

                        // Re-add class and re-init
                        $classSelect.addClass('custom-select');
                        $classSelect.show();
                        // Directly create new CustomSelect instance for this select only
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }

                        // Session select update (unchanged)
                        $('#sessionselect').empty();
                        $('#sessionselect').append($('<option>', {
                            value: 'all',
                            text: 'All Session'
                        }));
                        for (var i = 0; i < result.session.length; i++) {
                            var session = result.session[i];
                            $('#sessionselect').append($('<option>', {
                                value: session.id,
                                text: session.title
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
        }

        // function classStudents(id) {
        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: "{{ route('class.students') }}",
        //         type: "POST",
        //         data: {
        //             class_id: id
        //         },
        //         dataType: 'json',
        //         success: function(result) {
        //             if (result.status == 'success') {
        //                 $('#student_select').empty();
        //                 $('#student_select').append($('<option>', {
        //                     value: '',
        //                     text: 'Select Student'
        //                 }));
        //                 for (var id in result.students) {
        //                     if (result.students.hasOwnProperty(id)) {
        //                         $('#student_select').append($('<option>', {
        //                             value: id,
        //                             text: result.students[id]
        //                         }));
        //                     }
        //                 }
        //                 $('#student_select').val('');
        //             }
        //         }
        //     });
        // }
        // $(document).on('change', '#class_select', function() {
        //     var classId = $(this).val();
        //     if (classId) {
        //         classStudents(classId);
        //     }
        // });
    </script>
@endsection
