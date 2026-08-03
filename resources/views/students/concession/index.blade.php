@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Concession') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        // function classStudents(id) {
        //     var type = $('#type').val();
        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: "{{ route('class.students') }}",
        //         type: "POST",
        //         data: {
        //             class_id: id,
        //             type: type
        //         },
        //         dataType: 'json',
        //         success: function(result) {
        //             console.log(result);
        //             if (result.status == 'success') {
        //                 var s = ` {{ Form::label('student_id', __('Students'), ['class' => 'form-label']) }}<span style="color: red">
    //                             *</span><select name="student_id"  class="form-control select " id="student_select">
    //                             <option value="all" selected >All Students</option> `;


        //                 for (var id in result.students) {
        //                     if (result.students.hasOwnProperty(id)) {
        //                         s += `<option value="` + id + `">` + result.students[id] + `</option>`;
        //                         // $('#student_select').append($('<option>', { value: id, text: result.students[id] }));
        //                     }
        //                 }
        //                 s += `</select>`;
        //                 $('#std_names').empty();
        //                 $('#std_names').html(s);
        //                 if (result.length != 0) {
        //                     $('#student_select').addClass('js-searchBox');
        //                     JsSearchBox();
        //                     updateWidths();
        //                 }
        //                 $('#student_select').val('all');
        //             }

        //         }
        //     });
        // }

        // $(document).on('change', '#class_id', function() {
        //     var classId = $(this).val();
        //     $('.av').addClass('d-none');
        //     $('#student-details').empty('');
        //     if (classId) {
        //         classStudents(classId);
        //     } else {
        //         $('#student_select').empty();
        //     }
        // });
        // $(document).on('change', '#type', function() {
        //     var classId = $('#class_id').val();
        //     console.log(classId);
        //     if (classId) {
        //         classStudents(classId);
        //     } else {
        //         $('#student_select').empty();
        //     }
        // });

        // $(document).on('change', '#branch', function() {
        //     var branch = $(this).val();
        //     $.ajax({
        //         url: '{{ route('branch.class') }}',
        //         type: 'POST',
        //         data: {
        //             "branch_id": branch,
        //             "_token": "{{ csrf_token() }}",
        //         },
        //         success: function(data) {
        //             $('#class_id').empty();
        //             $('#class_id').append('<option value="" selected{{ __('Select Class') }}</option>');
        //             for (let index = 0; index < data.length; index++) {
        //                 $('#class_id').append('<option value="' + data[index]['id'] + '">' + data[index]
        //                     ['name'] + '</option>');
        //             }
        //         }
        //     });
        // });

        // $(document).on('change', '#student_select', function() {
        //     var studentId = this.value;
        //     $('.av').addClass('d-none');
        //     $('#student-details').empty('');
        //     if (studentId) {
        //         fetchStudentDetails(studentId);
        //     } else {
        //         document.getElementById('student-details').innerHTML = '';
        //     }
        // });

        // function fetchStudentDetails(studentId) {
        //     fetch('{{ url('concession/student-detail') }}/' + studentId)
        //         .then(response => response.json())
        //         .then(data => {
        //             displayStudentDetails(data);
        //         })
        //         .catch(error => console.error('Error:', error));
        // }

        // function displayStudentDetails(data) {
        //     var detailsDiv = document.getElementById('student-details');
        //     if (data) {
        //         detailsDiv.innerHTML =
        //             `<div style="display:grid; grid-template-columns:auto auto auto;"><p><strong>Student Name:</strong>${data.data.stdname}</p><p><strong>Father Name:</strong>${data.data.fathername}</p><p><strong>Father CNIC:</strong>${data.data.fathercnic}</p><p><strong>Email:</strong>${data.data.email}</p><p><strong>Roll No:</strong>${data.enroll.enrollId}</p><p><strong>Class:</strong>${data.class}</p><p><strong>Section:</strong>${data.section}</p><p><strong>Concession:</strong>${data.concession}</p></div>`;
        //         if (data.concession != 'No Previous Concession') {
        //             $('.av').removeClass('d-none');
        //         }
        //     } else {
        //         detailsDiv.innerHTML = '<p>No details available for this student.</p>';
        //     }
        // }

        // $(document).ready(function () {
        //     $('table').on('click', 'tr[data-id]', function () {
        //         var concession_id = $(this).data('id');
        //         $.ajax({
        //             {{-- url: '{{ route('concession.student_details') }}', --}}
        //             type: 'POST',
        //             data: {
        //                 "concession_id": concession_id,
        //                 "_token": "{{ csrf_token() }}",
        //             },
        //             success: function (data) {
        //                 // Assuming your data structure is as follows
        //                 console.log(data.data.email);
        //                 console.log(data.data.stdname);
        //                 console.log(data.data.fathername);
        //                 console.log(data.data.fathercnic);
        //                 console.log(data.enroll.enrollId);
        //                 console.log(data.class);
        //                 console.log(data.section);

        //                 // Construct HTML content for modal body
        //                 var modalBodyHtml = '<div style="display:grid; grid-template-columns:auto auto;">';
        //                 modalBodyHtml += '<p><strong>Student Name:</strong> ' + data.data.stdname + '</p>';
        //                 modalBodyHtml += '<p><strong>Father Name:</strong> ' + data.data.fathername + '</p>';
        //                 modalBodyHtml += '<p><strong>Father CNIC:</strong> ' + data.data.fathercnic + '</p>';
        //                 modalBodyHtml += '<p><strong>Email:</strong> ' + data.data.email + '</p>';
        //                 modalBodyHtml += '<p><strong>Enroll ID:</strong> ' + data.enroll.enrollId + '</p>';
        //                 modalBodyHtml += '<p><strong>Class:</strong> ' + data.class + '</p>';
        //                 modalBodyHtml += '<p><strong>Section:</strong> ' + data.section + '</p>';
        //                 modalBodyHtml += '</div>';

        //                 // Set modal body HTML and display modal
        //                 $('#studentDetailsModal .modal-body').html(modalBodyHtml);
        //                 $('#studentDetailsModal').modal('show');
        //             },
        //             error: function (xhr, status, error) {
        //                 console.error(xhr.responseText);
        //             }
        //         });
        //     });
        // });


        //         function printReport() {
        //     let form = document.getElementById('concession_submit');
        //     let formData = new FormData(form);
        //     let queryString = new URLSearchParams(formData).toString();
        //     window.location.href = "{{ route('concession.report') }}?" + queryString;
        // }
        function printReport() {
            var form = document.getElementById('concession_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('concession.report') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    </script>
    <script>
        // Function to fetch students based on class ID and type
        function classStudents(id) {
            var type = $('#type').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('class.students') }}",
                type: "POST",
                data: {
                    class_id: id,
                    type: type
                },
                dataType: 'json',
                success: function(data) {
                    var $studentSelect = $('#student_select');
                    // Remove previous custom select wrapper and instance
                    if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                        $studentSelect[0].customSelectInstance.destroy();
                        delete $studentSelect[0].customSelectInstance;
                    }
                    if ($studentSelect.next('.custom-select-wrapper').length) {
                        $studentSelect.next('.custom-select-wrapper').remove();
                    }
                    $studentSelect.removeClass('custom-select');

                    // Clear and append new options
                    $studentSelect.empty();
                    $studentSelect.append($('<option>', {
                        value: '',
                        text: 'Select Student'
                    }));

                    for (var j = 0; j < data.student.length; j++) {
                        var std = data.student[j];
                        $studentSelect.append($('<option>', {
                            value: std.id,
                            text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std.fathername
                        }));
                    }

                    // Re-add class and re-init
                    $studentSelect.addClass('custom-select');
                    $studentSelect.show();
                    // Directly create new CustomSelect instance for this select only
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        window.CustomSelect.create($studentSelect[0]);
                    }

                }
            });
        }

        // Variables to track the changes
        let lastClassId = null;
        let lastType = null;

        // Modified event handler for class_id change
        $(document).on('change', '#class_id', function() {
            let classId = $(this).val();
            $.ajax({
                url: "{{ route('class.student_head') }}",
                type: "POST",
                data: {
                    class_id: classId,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $studentSelect = $('#student_select');
                    // Remove previous custom select wrapper and instance
                    if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                        $studentSelect[0].customSelectInstance.destroy();
                        delete $studentSelect[0].customSelectInstance;
                    }
                    if ($studentSelect.next('.custom-select-wrapper').length) {
                        $studentSelect.next('.custom-select-wrapper').remove();
                    }
                    $studentSelect.removeClass('custom-select');

                    // Clear and append new options
                    $studentSelect.empty();
                    $studentSelect.append($('<option>', {
                        value: '',
                        text: 'Select Student'
                    }));

                    for (var j = 0; j < data.student.length; j++) {
                        var std = data.student[j];
                        $studentSelect.append($('<option>', {
                            value: std.id,
                            text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std
                                .fathername
                        }));
                    }

                    // Re-add class and re-init
                    $studentSelect.addClass('custom-select');
                    $studentSelect.show();
                    // Directly create new CustomSelect instance for this select only
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        window.CustomSelect.create($studentSelect[0]);
                    }
                }
            });
        });

        // Modified event handler for type change
        $(document).on('change', '#type', function() {
            var classId = $('#class_id').val();
            var currentType = $(this).val();

            if (classId && (classId !== lastClassId || currentType !== lastType)) {
                lastClassId = classId;
                lastType = currentType;
                classStudents(classId);
            } else if (!classId) {
                $('#student_select').empty();
            }
        });

        // Branch change event handler - no changes needed
        $(document).on('change', '#branch', function() {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#class_id').empty();
                    $('#class_id').append(
                        '<option value="" selected>{{ __('Select Class') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_id').append('<option value="' + data[index]['id'] + '">' + data[index]
                            ['name'] + '</option>');
                    }
                }
            });
        });

        // Student select change handler - no changes needed
        $(document).on('change', '#student_select', function() {
            var studentId = this.value;
            $('.av').addClass('d-none');
            $('#student-details').empty('');
            if (studentId) {
                fetchStudentDetails(studentId);
            } else {
                document.getElementById('student-details').innerHTML = '';
            }
        });

        function fetchStudentDetails(studentId) {
            fetch('{{ url('concession/student-detail') }}/' + studentId)
                .then(response => response.json())
                .then(data => {
                    displayStudentDetails(data);
                })
                .catch(error => console.error('Error:', error));
        }

        function displayStudentDetails(data) {
            var detailsDiv = document.getElementById('student-details');
            if (data) {
                let concessionTitle = data.concession?.concession?.title && data.concession.concession.title !== 'No Concession' 
                    ? data.concession.concession.title 
                    : 'N/A';
                detailsDiv.innerHTML = `
                    <div style="display:grid; grid-template-columns:auto auto auto;">
                        <p><strong>Student Name:</strong> ${data.data?.stdname || 'N/A'}</p>
                        <p><strong>Father Name:</strong> ${data.data?.fathername || 'N/A'}</p>
                        <p><strong>Father CNIC:</strong> ${data.data?.fathercnic || 'N/A'}</p>
                        <p><strong>Email:</strong> ${data.data?.email || 'N/A'}</p>
                        <p><strong>Roll No:</strong> ${data.enroll?.enrollId || 'N/A'}</p>
                        <p><strong>Class:</strong> ${data.class || 'N/A'}</p>
                        <p><strong>Section:</strong> ${data.section || 'N/A'}</p>
                        <p><strong>Concession:</strong> ${concessionTitle}</p>
                    </div>
                 `;
                if (data.concession && data.concession.concession && data.concession.concession !== 'No Concession') {
                    $('.av').removeClass('d-none');                    
                    let cancelDate = data.concession.cancel_date;
                    if (cancelDate) {
                        let formatted = new Date(cancelDate).toISOString().split('T')[0];
                        $('#cancle_date').val(formatted);
                    }

                    let cancelRemarks = data.concession.cancel_remarks;
                    if (cancelRemarks) {
                        $('#cancle_remarks').val(cancelRemarks);
                    }
                }
                if (data.tc) {
                    detailsDiv.innerHTML += `
                    <b><h5>Teacher Child</h5></b>
                    <div style="display:grid; grid-template-columns:auto auto auto;">
                        <p><strong>Employee ID:</strong> ${data.tc.employee?.employee_id || 'N/A'}</p>
                        <p><strong>Employee Name:</strong> ${data.tc.employee?.name || 'N/A'}</p>
                        <p><strong>Employee Designation:</strong> ${data.tc.employee?.designation?.name || 'N/A'}</p>
                        <p><strong>Employee Branch:</strong> ${data.tc.employee?.userbranch?.name || 'N/A'}</p>
                        <p><strong>Employee Service Period:</strong> ${data.tc.employee?.tenure || 'N/A'}</p>
                        <p><strong>Employee Joining Date:</strong> ${data.tc.employee?.company_doj || 'N/A'}</p>
                        <p><strong>Employee Scale:</strong> ${data.tc.employee?.ScaleNo?.scale_no || 'N/A'}</p>
                        </div>
                    `;
                }
            } else {
                detailsDiv.innerHTML = '<p>No details available for this student.</p>';
            }
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Concession') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="xl" data-url="{{ route('concession.create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['concession.index'], 'method' => 'GET', 'id' => 'concession_submit']) }}
                        <div class="row d-flex justify-content-end" style="width: 100%">
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

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Period From'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : $request->start_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                           {{-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('Period To'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : $request->end_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            --}}
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('concession_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('concession.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a href="#" onclick="printReport(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success" data-bs-title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr class="table_heads">
                    <th>{{ __('#') }}</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Concession') }}</th>
                    <th>{{ __('Applied') }}</th>
                    <th>{{ __('Period_from') }}</th>
                    <th>{{ __('Period_to') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th width="200px">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($concessions as $concession)
                    <tr data-id="{{ $concession->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ !empty($concession->student) ? $concession->student->stdname : '-' }}</td>
                        <td>{{ !empty($concession->concession) ? $concession->concession->title : '-' }}</td>
                        <td>{{ !empty($concession->apply_date) ? $concession->apply_date : '-' }}</td>
                        <td>{{ !empty($concession->start_date) ? $concession->start_date : '-' }}</td>
                        <td>{{ !empty($concession->end_date) ? $concession->end_date : '-' }}</td>
                        <td>
                            <div class="action-btn ms-2">
                                @if (\Auth::user()->type == 'company')
                                    @if ($concession->status != 'Approved' || $concession->status != 'Canceled' || $concession->status != 'Rejected')
                                        <a style="width: 100%;" href="#"
                                            data-url="{{ route('concession.status', $concession->id) }}" data-size="xl"
                                            data-ajax-popup="true" data-bs-toggle="{{ __('Concession Status') }}"
                                            data-bs-title="{{ __('Update Status') }}"
                                            class="btn btn-sm {{ $concession->status == 'Draft' ? 'btn-outline-info' : ($concession->status == 'For Approval' ? 'btn-outline-warning' : 'btn-outline-danger') }}">{{ $concession->status }}
                                        </a>
                                    @elseif($concession->status == 'Approved')
                                        <button style="width: 100%; cursor:auto"
                                            class="btn mx-1 btn-sm btn-outline-success">Approved</button>
                                    @elseif($concession->status == 'Canceled')
                                        <button style="width: 100%; cursor:auto"
                                            class="btn mx-1 btn-sm btn-outline-danger">Canceled</button>
                                    @else
                                        <button style="width: 100%; cursor:auto"
                                            class="btn mx-1 btn-sm btn-outline-danger">Rejected</button>
                                    @endif
                                @else
                                    @if ($concession->status != 'Approved' || $concession->status != 'Canceled' || $concession->status != 'Rejected')
                                        <button style="width: 100%;"
                                            class="btn btn-sm {{ @$concession->status == 'Draft' ? 'btn-outline-info' : ($concession->status == 'For Approval' ? 'btn-outline-warning' : 'btn-outline-danger') }}">{{ $concession->status }}
                                        </button>
                                    @elseif($concession->status != 'Approved')
                                        <button style="width: 100%; cursor:auto "
                                            class="btn mx-1 btn-sm btn-outline-success')}}">Approved</button>
                                    @elseif($concession->status != 'Canceled')
                                        <button style="width: 100%; cursor:auto "
                                            class="btn mx-1 btn-sm btn-outline-danger')}}">Canceled</button>
                                    @else
                                        <button style="width: 100%; cursor:auto "
                                            class="btn mx-1 btn-sm btn-outline-danger')}}">Rejected</button>
                                    @endif
                                @endif
                            </div>
                        </td>
                        @php
    $isAdmin   = Auth::user()->type === 'company';
    $isApproved = $concession->status == 'Approved';
    $hasOrder  = !empty($concession->concession_id);
@endphp

<td>
    <div class="action-btn ms-2">

        {{-- USER --}}
        @if(!$isAdmin)
            @if(!$isApproved)
                <a href="{{ route('concession.change_status', [$concession->id, 'For Approval']) }}"
                   class="btn btn-sm btn-outline-warning"
                   title="Send For Approval">
                    <i class="ti ti-send"></i>
                </a>

                <a href="#"
                   data-url="{{ route('concession.edit', $concession->id) }}"
                   data-ajax-popup="true"
                   data-size="xl"
                   class="btn btn-sm btn-outline-primary"
                   title="Edit">
                    <i class="ti ti-pencil"></i>
                </a>
            @endif
        @endif

        {{-- ADMIN --}}
        @if($isAdmin)

            {{-- Edit allowed only BEFORE approval --}}
            @if(!$isApproved)
                <a href="#"
                   data-url="{{ route('concession.edit', $concession->id) }}"
                   data-ajax-popup="true"
                   data-size="xl"
                   class="btn btn-sm btn-outline-primary"
                   title="Edit">
                    <i class="ti ti-pencil"></i>
                </a>
            @endif

            {{-- Approved but order NOT generated --}}
            @if($isApproved && !$hasOrder)
                <form action="{{ route('concession-order', $concession->id) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm btn-outline-success"
                            title="Generate Order">
                        Generate
                    </button>
                </form>

                <a href="#"
                   data-url="{{ route('concession.cancel', $concession->id) }}"
                   data-ajax-popup="true"
                   class="btn btn-sm btn-outline-danger"
                   title="Cancel">
                    <i class="ti ti-ban"></i>
                </a>
            @endif

            {{-- Approved AND order exists → FULL LOCK --}}
            {{-- Approved AND order exists -- FULL LOCK --}}
            @if($isApproved && $hasOrder)
                <form action="{{ route('concession-order', $concession->id) }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm btn-success text-white"
                            title="Generate Order"
                            style="box-shadow:none;">
                        Generate
                    </button>
                </form>

                <a href="#"
                   data-url="{{ route('concession.endconcession', $concession->id) }}"
                   data-ajax-popup="true"
                   class="btn btn-sm btn-warning text-white"
                   title="End Concession">
                    <i class="ti ti-clock-pause"></i> End
                </a>
            @endif

        @endif

    </div>
</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($concessions->hasPages())
            <div class="pagination">
                <ul>
                    @if ($concessions->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $concessions->appends(request()->query())->previousPageUrl() }}"
                                rel="prev">&laquo; Previous</a></li>
                    @endif
                    @if ($concessions->currentPage() > 1)
                        <li><a href="{{ $concessions->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $concessions->currentPage();
                        $lastPage = $concessions->lastPage();
                        $startPage = max(1, $currentPage - 4);
                        $endPage = min($lastPage, $currentPage + 5);
                        if ($endPage - $startPage < 9) {
                            if ($currentPage < $lastPage - 9) {
                                $endPage = $startPage + 9;
                            } else {
                                $startPage = max(1, $lastPage - 9);
                            }
                        }
                    @endphp
                    @for ($page = $startPage; $page <= $endPage; $page++)
                        <li class="{{ $page == $concessions->currentPage() ? 'active' : '' }}">
                            <a href="{{ $concessions->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($concessions->hasMorePages())
                        <li><a href="{{ $concessions->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($concessions->currentPage() < $concessions->lastPage())
                        <li><a
                                href="{{ $concessions->appends(request()->query())->url($concessions->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>


    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailsModalLabel">{{ __('Student Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Student details will be dynamically loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
