@extends('layouts.admin')
@section('page-title')
    {{ __('Student Fee Receipt Detail') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Auto-populate dropdowns on page load if parameters exist
            var branchId = "{{ request()->get('branches') }}";
            var classId = "{{ request()->get('class') }}";
            var studentId = "{{ request()->get('student') }}";

            // If branch is selected, load classes
            if (branchId && branchId !== '') {
                loadClasses(branchId, classId, studentId);
            } else if (classId && classId !== '') {
                // If only class is selected (without branch), load students
                loadStudents(classId, studentId);
            }
        });

        // Function to load classes and then students if needed
        function loadClasses(branchId, selectedClassId, selectedStudentId) {
            $.ajax({
                url: "{{ route('branch.class') }}",
                type: "POST",
                data: {
                    branch_id: branchId,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(result) {
                    var $classSelect = $('#class_select');

                    // Remove previous custom select wrapper and instance
                    if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                        try { $classSelect[0].customSelectInstance.destroy(); } catch(e) { /* ignored */ }
                        delete $classSelect[0].customSelectInstance;
                    }
                    if ($classSelect.next('.custom-select-wrapper').length) {
                        $classSelect.next('.custom-select-wrapper').remove();
                    }
                    $classSelect.removeClass('custom-select');

                    // Clear and append new options
                    $classSelect.empty();
                    $classSelect.append($('<option>', {
                        value: '',
                        text: 'Select Class'
                    }));

                    // Populate classes
                    if (Array.isArray(result)) {
                        for (var j = 0; j < result.length; j++) {
                            var cls = result[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    } else if (result.data && Array.isArray(result.data)) {
                        for (var j = 0; j < result.data.length; j++) {
                            var cls = result.data[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }

                    // Set selected class if exists
                    if (selectedClassId) {
                        $classSelect.val(selectedClassId);
                    }

                    // Re-add class and re-init custom select
                    $classSelect.addClass('custom-select');
                    $classSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        try { window.CustomSelect.create($classSelect[0]); } catch(e) { /* ignored */ }
                    }

                    // If class was selected, load students
                    if (selectedClassId) {
                        loadStudents(selectedClassId, selectedStudentId);
                    }
                },
                error: function(xhr, status, err) {
                    console.error('branch.class ajax error', err, xhr.responseText);
                }
            });
        }

        // Function to load students
        function loadStudents(classId, selectedStudentId) {
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
                        try { $studentSelect[0].customSelectInstance.destroy(); } catch(e) {}
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

                    // Populate students
                    if (data && Array.isArray(data.student)) {
                        for (var j = 0; j < data.student.length; j++) {
                            var std = data.student[j];
                            $studentSelect.append($('<option>', {
                                value: std.roll_no,
                                text: std.roll_no + ' - ' + std.stdname + (std.fathername ? ' s/d/o ' + std.fathername : '')
                            }));
                        }
                    } else if (data && data.students && Array.isArray(data.students)) {
                        for (var j = 0; j < data.students.length; j++) {
                            var std = data.students[j];
                            $studentSelect.append($('<option>', {
                                value: std.roll_no || std.id,
                                text: (std.roll_no ? std.roll_no + ' - ' : '') + std.stdname + (std.fathername ? ' s/d/o ' + std.fathername : '')
                            }));
                        }
                    } else if (data && typeof data === 'object') {
                        for (var id in data) {
                            if (data.hasOwnProperty(id)) {
                                $studentSelect.append($('<option>', {
                                    value: id,
                                    text: data[id]
                                }));
                            }
                        }
                    }

                    // Set selected student if exists
                    if (selectedStudentId) {
                        $studentSelect.val(selectedStudentId);
                    }

                    // Re-init custom select
                    $studentSelect.addClass('custom-select');
                    $studentSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        try { window.CustomSelect.create($studentSelect[0]); } catch(e) {}
                    }
                },
                error: function(xhr, status, err) {
                    console.error('class.student_head ajax error', err, xhr.responseText);
                }
            });
        }

        // Branch -> load classes (on change event)
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
                        try { $classSelect[0].customSelectInstance.destroy(); } catch(e) { /* ignored */ }
                        delete $classSelect[0].customSelectInstance;
                    }
                    if ($classSelect.next('.custom-select-wrapper').length) {
                        $classSelect.next('.custom-select-wrapper').remove();
                    }
                    $classSelect.removeClass('custom-select');

                    // Clear and append new options
                    $classSelect.empty();
                    $classSelect.append($('<option>', {
                        value: '',
                        text: 'Select Class'
                    }));

                    // result expected to be an array of class objects [{id, name}, ...]
                    if (Array.isArray(result)) {
                        for (var j = 0; j < result.length; j++) {
                            var cls = result[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    } else if (result.data && Array.isArray(result.data)) {
                        // in case your controller returns { data: [...] }
                        for (var j = 0; j < result.data.length; j++) {
                            var cls = result.data[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }

                    // Re-add class and re-init if you use custom select plugin
                    $classSelect.addClass('custom-select');
                    $classSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        try { window.CustomSelect.create($classSelect[0]); } catch(e) { /* ignored */ }
                    }

                    // reset student select
                    $('#student_select').html('<option value="">Select Student</option>');
                    if ($('#student_select')[0] && $('#student_select')[0].customSelectInstance) {
                        try { $('#student_select')[0].customSelectInstance.destroy(); } catch(e) {}
                        $('#student_select').next('.custom-select-wrapper').remove();
                        $('#student_select').removeClass('custom-select').addClass('custom-select');
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            try { window.CustomSelect.create($('#student_select')[0]); } catch(e) {}
                        }
                    }
                },
                error: function(xhr, status, err) {
                    console.error('branch.class ajax error', err, xhr.responseText);
                }
            });
        });

        // Class -> load students (on change event)
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
                        try { $studentSelect[0].customSelectInstance.destroy(); } catch(e) {}
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

                    // expected: data.student is array of { roll_no, stdname, fathername }
                    if (data && Array.isArray(data.student)) {
                        for (var j = 0; j < data.student.length; j++) {
                            var std = data.student[j];
                            $studentSelect.append($('<option>', {
                                value: std.roll_no,
                                text: std.roll_no + ' - ' + std.stdname + (std.fathername ? ' s/d/o ' + std.fathername : '')
                            }));
                        }
                    } else if (data && data.students && Array.isArray(data.students)) {
                        // alternate key
                        for (var j = 0; j < data.students.length; j++) {
                            var std = data.students[j];
                            $studentSelect.append($('<option>', {
                                value: std.roll_no || std.id,
                                text: (std.roll_no ? std.roll_no + ' - ' : '') + std.stdname + (std.fathername ? ' s/d/o ' + std.fathername : '')
                            }));
                        }
                    } else if (data && typeof data === 'object') {
                        // If controller returns an object mapping id => name
                        for (var id in data) {
                            if (data.hasOwnProperty(id)) {
                                $studentSelect.append($('<option>', {
                                    value: id,
                                    text: data[id]
                                }));
                            }
                        }
                    }

                    // Re-init custom select if used
                    $studentSelect.addClass('custom-select');
                    $studentSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        try { window.CustomSelect.create($studentSelect[0]); } catch(e) {}
                    }
                },
                error: function(xhr, status, err) {
                    console.error('class.student_head ajax error', err, xhr.responseText);
                }
            });
        });

        function exportToExcel() {
            // Get all values using jQuery to handle custom selects
            var params = {
                from_date: $('[name="from_date"]').val() || '',
                to_date: $('[name="to_date"]').val() || '',
                default_bank: $('#default_bank').val() || '',
                head: $('[name="head"]').val() || '',
                voucher: $('[name="voucher"]').val() || '',
                branches: $('#branch').val() || '',
                class: $('#class_select').val() || '',
                student: $('#student_select').val() || '',
                export: 'excel'
            };
            
            console.log('Export Excel Params:', params);
            
            // Build query string from all parameters
            var queryString = Object.keys(params)
                .filter(key => params[key] !== '')
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
                .join('&');
            
            console.log('Export Excel Query:', queryString);
            window.location.href = "{{ route('student_fee_receipt_detail.report') }}?" + queryString;
        }

        function exceltopdf() {
            // Get all values using jQuery to handle custom selects
            var params = {
                from_date: $('[name="from_date"]').val() || '',
                to_date: $('[name="to_date"]').val() || '',
                default_bank: $('#default_bank').val() || '',
                head: $('[name="head"]').val() || '',
                voucher: $('[name="voucher"]').val() || '',
                branches: $('#branch').val() || '',
                class: $('#class_select').val() || '',
                student: $('#student_select').val() || '',
                export: 'pdf'
            };
            
            console.log('Export PDF Params:', params);
            
            // Build query string from all parameters
            var queryString = Object.keys(params)
                .filter(key => params[key] !== '')
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
                .join('&');
            
            console.log('Export PDF Query:', queryString);
            window.location.href = "{{ route('student_fee_receipt_detail.report') }}?" + queryString;
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Fee Receipt Detail') }}</li>
@endsection

@section('action-btn')
    <div class="float-end"></div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['student_fee_receipt_detail'], 'method' => 'GET', 'id' => 'student_receipt_list']) }}
                        <div class="row d-flex justify-content-end " style="width: 100%">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', request()->get('from_date') ?? date('Y-m-d', strtotime('first day of this month') ), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('to_date', request()->get('to_date') ?? date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, request()->get('default_bank'), ['class' => 'form-control select custom-select', 'id' => 'default_bank']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('head', __('T.Heads'), ['class' => 'form-label']) }}
                                    {{ Form::select('head', $heads, request()->get('head'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-end mt-2">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('vouchers', __('Voucher'), ['class' => 'form-label']) }}
                                    {{ Form::select('voucher', $voucherOptions, request()->get('voucher'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>

                            <!-- NOTE: ID changed to 'branch' to match the script -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, request()->get('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Student'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', $students, request()->get('student'), ['class' => 'form-control select custom-select', 'id' => 'student_select']) }}
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_list').submit(); return false;"
                                    title="search" data-bs-original-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li>
                                            <button class="dropdown-item" type="button" onclick="exportToExcel();">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button" onclick="exceltopdf();">
                                                <i class="ti ti-printer me-2"></i>PDF
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-2 p-4" id="studentfeereceipt">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School </b></p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; text-align: center; font-weight: 800;">Fee Receipt Detail</p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; text-align: center; font-weight: 800;">
                    {{ @$brnches_name->name ?? 'All Branches' }}</p>
            </div>
            <div class="d-flex" style="justify-content: space-between;">
                <p style=""><b>From Date: </b>{{ request()->get('from_date') ?? date('Y-M-d') }}</p>
                <p style=""></p>
                <p style=""><b>To Date:
                    </b>{{ request()->get('to_date') ?? date('Y-M-d') }}</p>
            </div>
            <div class="maximumHeightNew" style="width: 100%;">
                <table class="datatable">
                    <thead class="sticky-headerNew">
                        <tr class="table_heads" style="font-size:0.8rem;">
                            <th>{{ __('Sr#') }}</th>
                            <th>{{ __('Br.Sr#') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Ch. Type') }}</th>
                            <th>{{ __('Roll #.') }}</th>
                            <th>{{ __('Student') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Challan No') }}</th>
                            <th>{{ __('Billing Month') }}</th>
                            <th>{{ __('Billing Cycle') }}</th>
                            <th>{{ __('Bank/Cash') }}</th>
                            <th>{{ __('Mode') }}</th>
                            <th>{{ __('T.Head') }}</th>
                            <th>{{ __('Ref.') }}</th>
                            <th>{{ __('Rs.') }}</th>
                            <th>{{ __('Over Receipt') }}</th>
                        </tr>
                    </thead>
                    <tbody>
@php
                        $globalSr = 1;
                        $grandTotal = 0;

                    @endphp
@foreach ($groupedVouchers as $branchId => $branchVouchers)
    @php $branchSr = 1; 
        $branchTotal = 0;
    @endphp
    <tr style="background-color: #f0f0f0;">
        <td colspan="16" style="font-weight: bold;">
            {{ $branchNames[$branchId] ?? 'Unknown Branch' }}
        </td>
    </tr>

    @foreach ($branchVouchers as $voucher)
        <tr style="font-size:0.7rem;">
            <td>{{ $globalSr++ }}</td>
            <td>{{ $branchSr++ }}</td>
            <td>
                {{ \Carbon\Carbon::parse($voucher['receipts']->first()->recipt_date)->format('d-M-Y') }}
            </td>
            <td>{{ $voucher['challan']?->challan_type }}</td>
            <td>{{ $voucher['challan']?->enrollstudent?->enrollId ?? $voucher['challan']?->student?->roll_no }}</td>
            <td>{{ $voucher['challan']?->student?->stdname }}</td>
            <td>{{ $voucher['challan']?->class?->name }}</td>
            <td>{{ $voucher['challan']?->challanNo }}</td>
            <td>
                {{ $voucher['challan']?->fee_month ? \Carbon\Carbon::parse($voucher['challan']->fee_month)->format('F Y') : '' }}
            </td>
            <td>{{ $voucher['challan']?->billing_cycle }}</td>
            <td>{{ $voucher['bank']?->bank_name }}</td>
            <td>{{ $voucher['receipts']->first()->receive_type }}</td>
            <td>
                @foreach($voucher['voucher_items'] as $item)
                    {{ $item->heads?->fee_head }}<br>
                @endforeach
            </td>
            <td>
                @foreach($voucher['receipts'] as $r)
                    {{ $r->referance }}<br>
                @endforeach
            </td>
            <td>{{ $voucher['voucher_items']->sum('credit') }}</td>
            @php
                $branchTotal += $voucher['voucher_items']->sum('credit');
            @endphp
            <td>0.0</td>
        </tr>
    @endforeach
    <tr style="background-color: #f0f0f0;">
        <td colspan="14" style="font-weight: bold;">
            Total
        </td>
        <td>{{ $branchTotal }}</td>
        <td>0.0</td>
    </tr>
						@php
                            $grandTotal += $branchTotal;
                        @endphp
@endforeach
						<tr style="background-color: #c9c5c5;">
                        <td colspan="14" style="font-weight: bold;">
                            Grand Total
                        </td>
                        <td>{{ $grandTotal }}</td>
                        <td>0.0</td>
                    </tr>
</tbody>

                </table>
            </div>
        
    </div>

@endsection