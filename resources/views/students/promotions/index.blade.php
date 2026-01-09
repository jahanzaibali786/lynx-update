@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Student Promotions') }}
@endsection
@push('script-page')
    <script>
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
                        $('#class_id').empty();
                        $('#class_id').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));
                        $('#class_to').empty();
                        $('#class_to').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));

                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $('#class_id').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                            $('#class_to').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
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
                        for (let index = 0; index < data.length; index++) {
                            $('#section_to').append('<option value="' + data[index]['id'] + '">' + data[
                                index]['name'] + '</option>');
                        }
                    }
                });
            });

        }

        // updateDropdown();
        // function updateDropdown() {
        //     const branch1 = document.getElementById("class_id").value;
        //     const branch2 = document.getElementById("class_to");
        //     const options = branch2.options;
        //     // Enable all options first
        //     for (let i = 0; i < options.length; i++) {
        //         options[i].disabled = false;
        //     }

        //     // Disable the option that matches the value of the first dropdown
        //     if (branch1) {
        //         for (let i = 0; i < options.length; i++) {
        //             if (options[i].value === branch1) {
        //                 options[i].disabled = true;
        //                 break;
        //             }
        //         }
        //     }
        // }

        function updateFeeHeads() {
            let feeData = [];
            $('tbody tr').each(function() {
                let headId = $(this).find('td input').data('head-id');
                let amount = $(this).find('td input').val();
                feeData.push({
                    head_id: headId,
                    amount: amount
                });
            });
            var branches = $('#branches').val();
            var session_id = $('#session_id').val();
            var class_to = $('#class_to').val();
            if (!session_id || !class_to) {
                show_toastr('error', 'Session and Class To cannot be empty.', 'error');
                return;
            }
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('student-promotion.headsupdate') }}",
                type: 'POST',
                data: {
                    feeData: feeData,
                    branches: branches,
                    session_id: session_id,
                    class_id: class_to,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        show_toastr('success', response.message, 'success');
                        window.location.reload();
                    } else {
                        show_toastr('error', 'An Error occurred on updating fees.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    show_toastr('error', 'An error occurred.', 'error');
                }
            });
        }
        // Submit checked student data with filters
        function submitChecked() {
            var form = document.getElementById('concession_submit');
            // Trigger form validation
            if (form.checkValidity()) {} else {
                form.reportValidity();
            }
            let studentData = [];
            let checkedBoxes = $('table input[type="checkbox"]:checked');
            if (checkedBoxes.length === 0) {
                alert('Please check entries to promote.');
                return;
            }
            checkedBoxes.each(function() {
                let row = $(this).closest('tr');
                let enrollId = row.find('td').eq(0).text();
                let sectionTo = row.find('select').val();
                studentData.push({
                    enrollId: enrollId,
                    section_to: sectionTo
                });
            });
            let filters = {
                branches: $('#branches').val(),
                class_id: $('#class_id').val(),
                class_to: $('#class_to').val(),
                session_id: $('#session_id').val(),
            };
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('student-promotion.store') }}",
                type: 'POST',
                data: {
                    studentData: studentData,
                    filters: filters
                },
                success: function(response) {
                    if (response.status === 'success') {
                        show_toastr('success', response.message, 'success');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        show_toastr('error', response.message, 'error');
                        // window.location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = xhr.responseJSON ? xhr.responseJSON.message :
                        'An unexpected error occurred';
                    show_toastr('error', errorMessage, 'error');
                    console.log(xhr.responseText);
                }
            });
        }

        function Checked(e) {
            e.perventDefult;
            var form = document.getElementById('concession_submit');
            // Trigger form validation
            if (form.checkValidity()) {
                // If form is valid, submit it
                form.submit();
            } else {
                // If form is invalid, show the native validation error messages
                form.reportValidity();
            }
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Promotions') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student-promotion.index'], 'method' => 'GET', 'id' => 'concession_submit']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span
                                        style="color: red"> *</span>
                                    {{ Form::select('class_id', $classes, isset($_GET['class_id']) ? $_GET['class_id'] : '', ['class' => 'form-control select', 'id' => 'class_id', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class_to', __('Promote to Class'), ['class' => 'form-label']) }}<span
                                        style="color: red"> *</span>
                                    {{ Form::select('class_to', $classes, isset($_GET['class_to']) ? $_GET['class_to'] : '', ['class' => 'form-control select', 'id' => 'class_to', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span
                                        style="color: red"> *</span>
                                    {{ Form::select('session_id', $session, isset($_GET['session_id']) ? $_GET['session_id'] : '', ['class' => 'form-control select', 'id' => 'session_id', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">

                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"

                                     onclick="Checked(event)" title="Search data" data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student-promotion.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     title="Clear Filter" data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>

                                <a id="submitChecked"   title="Selected Students Promote" class="btn mx-1 btn-sm btn-outline-warning"
                                    onclick="submitChecked()"><span class="btn-inner--icon">Promote Class</span></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="row">
        <div class="col-xl-4">
            <div class="">
                <span style="font-size: large; text-align: center;">
                    @if ($classwisefee && count($classwisefee) > 0)
                        {{ @$classwisefee ? @$classwisefee['0']->class->name : '' }} (
                        {{ @$classwisefee ? @$classwisefee['0']->session->year : '' }} )
                    @endif
                </span>
                <table class="">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr No</th>
                            <th>Account Head</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classwisefee as $classfee)
                            @php
                                $fee = \App\Models\FeeHead::where('id', @$classfee->head_id)->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ @$fee->fee_head }}</td>
                                <td><input type="text" style="width:100px;" value="{{ @$classfee->amount }}"
                                        data-head-id="{{ @$classfee->head_id }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (count($classwisefee) > 0)
                    <div class="row d-flex justify-content-end ">
                        <div class="col-xl-3">
                            <button class="btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                title="Update Class To Fee Structure of Selected Session"
                                onclick="updateFeeHeads()">Update</button>
                        </div>

                    </div>
                @endif
            </div>
        </div>
        <div class="col-xl-8">
            <div class="">
                <table class="">
                    <thead class="table_heads">

                        <tr>
                            <th>Sr. No</th>
                            <th>Roll No</th>
                            <th style="width: 70px">Name</th>
                            <th>Father Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Section To</th>
                            <th><input type="checkbox" name="" id=""></th>
                        </tr>
                    </thead>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ @$student->enrollId }}</td>
                            <td>{{ @$student->StudentRegistration->stdname }}</td>
                            <td>{{ @$student->StudentRegistration->fathername }}</td>
                            <td>{{ @$student->class->name }}</td>
                            <td>{{ @$student->section->name }}</td>
                            <td> {!! Form::select('section_to', @$section ?? ['' => 'select Section'], null, [
                                'class' => 'form-control',
                                'required' => 'required',
                                'id' => 'section_to',
                            ]) !!}
                            </td>
                            <td><input type="checkbox" name="" id=""></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection
