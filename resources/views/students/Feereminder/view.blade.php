@extends('layouts.admin')
@section('page-title')
    {{ __('Fee Reminder') }}
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/brands.min.css"
        integrity="sha512-EJp8vMVhYl7tBFE2rgNGb//drnr1+6XKMvTyamMS34YwOEFohhWkGq13tPWnK0FbjSS6D8YoA3n3bZmb3KiUYA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script>
            $(document).on('change', '#branch', function() {
                let branch = $(this).val();
                $.ajax({
                    url: "{{ route('branch.class') }}",
                    type: "POST",
                    data: {
                        branch_id: branch,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(result) {
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
                        for (var j = 0; j < result.length; j++) {
                            var cls = result[j];
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
    
                        $('#student_select').html('<option value="">Select Student</option>');
                    }
                });
            });
    
            $(document).on('change', '#class_select', function() {
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
                                value: std.roll_no,
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
            });
        </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fee Reminder') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        @php
                            $type = [
                                'first' => '1st Reminder Fee Collection',
                                'second' => '2nd Reminder Fee Collection',
                                'final' => 'Final Reminder',
                            ];
                        @endphp
                        {{ Form::open(['route' => 'feereminderslip.index', 'method' => 'GET', 'id' => 'feereminderslip']) }}
                        <div class="row d-flex">
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : $request->start_date, ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : $request->end_date, ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('reminder', __('Reminder'), ['class' => 'form-label']) }}
                                    {{ Form::select('reminder', $type, isset($_GET['reminder']) ? $_GET['reminder'] : '', ['class' => 'form-control select', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex mt-2">
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)', 'id' => 'branch']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', @$class, isset($_GET['class']) ? $_GET['class'] : '', ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', @$student, isset($_GET['student']) ? $_GET['student'] : '', ['class' => 'form-control select custom-select', 'id' => 'student_select']) }}
                                </div>
                            </div>
                            {{ Form::close() }}
                        <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                            <button type="submit" id="" class="btn mx-1 btn-sm btn-outline-primary ml-2" title="search" ><span class="btn-inner--icon">Search</span></button>
                            <button id="printButton" type="button" class="btn mx-1 btn-sm btn-outline-warning"  disabled><span class="btn-inner--icon">Print</span></button>
                            <button id="whatsappButton" type="button" class="btn mx-1 btn-sm btn-outline-success"  disabled><span class="btn-inner--icon"><i class="fa-brands fa-whatsapp"></i></span></button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="" style="width: 100%;">
        <table>
            <thead>
                <tr class="table_heads" style="font-size:0.8rem;">
                    <th>{{ __('Student Name') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Section') }}</th>
                    <th>{{ __('Fee Months') }}</th>
                    <th>{{ __('Total Amount') }}</th>
                    <th><input id="checkAll" type="checkbox"></th>
                </tr>
            </thead>
            {{-- <tbody>
            @foreach ($challans as $challan)
            <tr>
                <td>{{@$challan->student->stdname}}</td>
                <td>{{@$challan->class->name}}</td>
                <td>{{@$challan->enrollstudent->section->name}}</td>
                <td>{{ \Carbon\Carbon::parse($challan->fee_month)->format('F,Y') }}</td>
                <td>{{@$challan->total}}</td>
                <input type="hidden" value="{{@$challan->enrollstudent->enrollId}}" hidden>
                <td><input type="checkbox" name="checked[]"></td>
            </tr>
            @endforeach
        </tbody> --}}
            @php
                $studentsData = [];
            @endphp

            @foreach ($challans as $challan)
                @php
                    $studentId = $challan->student_id;
                    // Initialize data if the student doesn't exist in the array
if (!isset($studentsData[$studentId])) {
    $studentsData[$studentId] = [
        'name' => $challan->student->stdname ?? 'N/A',
        'branch' => $challan->student->branches->name ?? '',
        'headmaster_name' => @$challan->enrollstudent->master->headmaster_name->name ?? '',
        'class' => $challan->class->name ?? 'N/A',
        'section' => $challan->enrollstudent->section->name ?? 'N/A',
        'roll_no' => $challan->enrollstudent->enrollId ?? '',
        'fee_months' => [],
        'total_amount' => 0,
    ];
}
// Add fee month and update total amount for the student
$studentsData[$studentId]['fee_months'][] = \Carbon\Carbon::parse($challan->fee_month)->format(
    'F, Y',
);
$studentsData[$studentId]['amount_months'][] =
    \Carbon\Carbon::parse($challan->fee_month)->format('M') . '-' . $challan->total;
$studentsData[$studentId]['total_amount'] += $challan->total;
                @endphp
            @endforeach
            {{-- @dd($studentsData); --}}

            @foreach ($studentsData as $data)
                <tr>
                    <td>{{ $data['name'] }}</td>
                    <td>{{ $data['class'] }}</td>
                    <td>{{ $data['section'] }}</td>
                    <td style="max-width:350px;">{{ implode(', ', $data['fee_months']) }}</td>
                    <td>{{ $data['total_amount'] }}</td>
                    <input type="hidden" value="{{ $data['roll_no'] }}" hidden>
                    <td><input type="checkbox" name="checked[]"></td>
                    <td hidden>{{ implode(', ', $data['amount_months']) }}</td>
                    <td hidden>{{ $data['branch'] }}</td>
                    <td hidden>{{ $data['headmaster_name'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @if (isset($challans) && $challans->isNotEmpty())
    @endif
    <script>
        var Printbtn = document.getElementById('printButton');
        var whatsappbtn = document.getElementById('whatsappButton');
        var checkAllCheckbox = document.getElementById('checkAll');
        var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');

        checkAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(function(checkbox) {
                checkbox.checked = checkAllCheckbox.checked;
            });
            updatePrintButtonState();
        });

        rowCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    checkAllCheckbox.checked = false;
                } else if (Array.from(rowCheckboxes).every(cb => cb.checked)) {
                    checkAllCheckbox.checked = true;
                }
                updatePrintButtonState();
            });
        });

        function updatePrintButtonState() {
            var anyChecked = Array.from(rowCheckboxes).some(function(checkbox) {
                return checkbox.checked;
            });
            Printbtn.disabled = !anyChecked;
            whatsappbtn.disabled = !anyChecked;
        }

        updatePrintButtonState();

        document.getElementById('printButton').addEventListener('click', function() {
            var selectedRows = [];
            var reminder = document.querySelector('select[name="reminder"]').value;
            var checkedCheckboxes = document.querySelectorAll('input[name="checked[]"]:checked');

            checkedCheckboxes.forEach(function(checkbox) {

                var row = checkbox.closest('tr');
                var studentName = row.cells[0].textContent;
                var studentClass = row.cells[1].textContent;
                var section = row.cells[2].textContent;
                var feeMonths = row.cells[3].textContent;
                var totalAmount = row.cells[4].textContent;
                var rollno = row.querySelector('input[type="hidden"]').value;
                var totalmonth = row.cells[6].textContent;
                var branch = row.cells[7].textContent;
                var headmaster_name = row.cells[8].textContent;

                selectedRows.push({
                    studentName: studentName,
                    studentClass: studentClass,
                    section: section,
                    feeMonths: feeMonths,
                    totalAmount: totalAmount,
                    rollno: rollno,
                    totalmonth: totalmonth,
                    branch: branch,
                    headmaster_name: headmaster_name,
                });
            });

            selectedRows.forEach(function(row) {
                var requestData = {
                    reminder: reminder,
                    data: row
                };

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('feereminderslip.store') }}',
                    type: 'POST',
                    data: requestData,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response, status, xhr) {
                        var filename = '';
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            var matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        var blob = new Blob([response], {
                            type: 'application/pdf'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename || 'Fee_Reminder_Slip.pdf';
                        link.click();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
        document.getElementById('whatsappButton').addEventListener('click', function() {
            var selectedRows = [];
            var reminder = document.querySelector('select[name="reminder"]').value;
            var checkedCheckboxes = document.querySelectorAll('input[name="checked[]"]:checked');

            checkedCheckboxes.forEach(function(checkbox) {
                var row = checkbox.closest('tr');
                var studentName = row.cells[0].textContent;
                var studentClass = row.cells[1].textContent;
                var section = row.cells[2].textContent;
                var feeMonths = row.cells[3].textContent;
                var totalAmount = row.cells[4].textContent;
                var rollno = row.querySelector('input[type="hidden"]').value;
                var totalmonth = row.cells[6].textContent;
                var branch = row.cells[7].textContent;
                var headmaster_name = row.cells[8].textContent;

                selectedRows.push({
                    studentName: studentName,
                    studentClass: studentClass,
                    section: section,
                    feeMonths: feeMonths,
                    totalAmount: totalAmount,
                    rollno: rollno,
                    totalmonth: totalmonth,
                    branch: branch,
                    headmaster_name: headmaster_name,
                });
            });

            selectedRows.forEach(function(row) {
                var requestData = {
                    reminder: reminder,
                    data: row,
                    type: 'whatsapp' // Indicate WhatsApp request
                };

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('feereminderslip.store') }}',
                    // url: 'https://7d16-119-152-7-241.ngrok-free.app/lynx/feereminderslip',
                    type: 'POST',
                    data: requestData,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        alert('WhatsApp message sent successfully!');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
@endsection
