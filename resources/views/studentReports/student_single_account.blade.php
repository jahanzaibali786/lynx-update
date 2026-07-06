@extends('layouts.admin')

@section('page-title')
    {{ __('Student Single Account') }}
@endsection

@push('script-page')
    <style>
        .dropdown-toggle.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .export-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        .opening-balance {
            background-color: #e3f2fd;
            font-weight: bold;
        }

        .closing-balance {
            background-color: #fff9c4;
            font-weight: bold;
        }

        /* Color for negative values (payments) */
        .negative-value {
            color: red;
            font-weight: 500;
        }

        /* Color for positive values (receivables) */
        .positive-value {
            color: green;
            font-weight: 500;
        }

        .finalize-glow {
            color: #dc3545 !important;
            font-weight: 700;
            animation: finalizeGlow 1.2s ease-in-out infinite;
        }

        @keyframes finalizeGlow {
            0%, 100% {
                text-shadow: 0 0 0 rgba(220, 53, 69, 0);
                transform: scale(1);
            }
            50% {
                text-shadow: 0 0 8px rgba(220, 53, 69, 0.85);
                transform: scale(1.04);
            }
        }
    </style>
    <script>
        $(document).on('change', '#student_select, #class_select, #status_select, #branches', function() {
            clearExportInput();
            toggleExportButtons();
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
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');

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

                        $classSelect.addClass('custom-select');
                        $classSelect.show();
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }

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
                        toggleExportButtons();
                    }
                }
            });
        }

        function classStudents(classId, status, selectedStudent) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('class.withdrawstudents') }}",
                type: "POST",
                data: {
                    class_id: classId,
                    status: status
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        var $stdSelect = $('#student_select');
                        if ($stdSelect[0] && $stdSelect[0].customSelectInstance) {
                            $stdSelect[0].customSelectInstance.destroy();
                            delete $stdSelect[0].customSelectInstance;
                        }
                        if ($stdSelect.next('.custom-select-wrapper').length) {
                            $stdSelect.next('.custom-select-wrapper').remove();
                        }
                        $stdSelect.removeClass('custom-select');

                        $stdSelect.empty();
                        $('#student_select').append($('<option>', {
                            value: 'all',
                            text: 'All Students'
                        }));
                        for (var id in result.students) {
                            if (result.students.hasOwnProperty(id)) {
                                $('#student_select').append($('<option>', {
                                    value: id,
                                    text: result.students[id]
                                }));
                            }
                        }
                        if (selectedStudent && result.students.hasOwnProperty(selectedStudent)) {
                            $('#student_select').val(selectedStudent);
                        } else {
                            $('#student_select').val('all');
                        }
                        CustomSelect.create(document.getElementById('student_select'));
                        toggleExportButtons();
                    }
                }
            });
        }

        $(document).on('change', '#class_select, #status_select', function() {
            var classId = $('#class_select').val() ?? null;
            var status = $('#status_select').val();
            // if (classId && status) {
                classStudents(classId, status);
            // }
        });

        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            var status = $('#status_select').val();
            if (classId) {
                classStudents(classId, status);
            }
        });

        function validateForm() {
            var studentSelect = document.getElementById('student_select');

            if (studentSelect.value == '' || studentSelect.value == null || studentSelect.value == 'all') {
                alert('Please select a student before applying the filter.');
                return false;
            }
            clearExportInput();
            document.getElementById('student_single_account').submit();
        }

        function validateExport(exportType) {
            var studentSelect = document.getElementById('student_select');

            if (studentSelect.value == '' || studentSelect.value == null || studentSelect.value == 'all') {
                alert('Please select a specific student before exporting.');
                return false;
            }

            var form = document.getElementById('student_single_account');

            // IMPORTANT: remove old export first
            clearExportInput();

            // add fresh export flag
            var exportInput = document.createElement('input');
            exportInput.type = 'hidden';
            exportInput.name = 'export';
            exportInput.value = exportType;

            form.appendChild(exportInput);

            form.submit();
            return true;
        }

        function clearExportInput() {
            let form = document.getElementById('student_single_account');
            let existing = form.querySelector('input[name="export"]');
            if (existing) {
                existing.remove();
            }
        }

        function toggleExportButtons() {
            var studentSelect = document.getElementById('student_select');
            var exportButtons = document.querySelectorAll('.export-btn');
            var exportDropdown = document.getElementById('actionDropdown');
            if (studentSelect.value == '' || studentSelect.value == null || studentSelect.value == 'all') {
                exportButtons.forEach(function(btn) {
                    btn.disabled = true;
                });
                exportDropdown.disabled = true;
                exportDropdown.classList.add('disabled');
            } else {
                exportButtons.forEach(function(btn) {
                    btn.disabled = false;
                });
                exportDropdown.disabled = false;
                exportDropdown.classList.remove('disabled');
            }
        }

        $(document).ready(function() {
            toggleExportButtons();
            classStudents($('#class_select').val(), $('#status_select').val() || 'active', '{{ $selected_student ?? '' }}');
        });

        $(document).on('change', '#student_select', function() {
            toggleExportButtons();
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Single Account') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_single_account'], 'method' => 'GET', 'id' => 'student_single_account', 'novalidate' => 'novalidate']) }}
                        <div class="row d-flex justify-content-start ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', $from_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('to_date', $to_date, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, $selected_branch, ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
							<div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, $selected_class, ['class' => 'form-control select custom-select', 'id' => 'class_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Student Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', ['active' => 'Active', 'withdraw' => 'Withdraw'], request()->get('status', 'active'), ['class' => 'form-control select', 'id' => 'status_select']) }}
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}<span
                                        style="color: red"> *</span>
                                    {{ Form::select('student', $students, $selected_student, ['class' => 'form-control select custom-select', 'id' => 'student_select', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div
                                class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="validateForm(); return false;" data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_single_account') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item export-btn" type="button"
                                                onclick="validateExport('excel'); return false;">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item export-btn" type="button"
                                                onclick="validateExport('pdf'); return false;">
                                                <i class="ti ti-download me-2"></i>Pdf
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

    @if (isset($std) && $std)
    @php
        $previousStatementFinalized = !empty($previousStatementFile) && !empty($previousStatementFile->finalized_at);
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Previous Data Sheet') }}</h5>
                    @if (!empty($previousStatementFile))
                        <small class="text-muted">
                            @if ($previousStatementFinalized)
                                {{ __('Finalized') }}:
                                {{ optional($previousStatementFile->finalized_at)->format('d-M-Y h:i A') }}
                            @else
                                {{ __('Unfinalized') }}
                            @endif
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-5 d-flex">
                            @if (!empty($previousStatementFile))
                                <p class="mb-1" style="width: fit-content;">
                                    <strong>{{ __('Current Sheet') }}:</strong>
                                    {{ $previousStatementFile->original_name }}
                                    <a href="{{ route('student_single_account.previous_data.download', $previousStatementFile->id) }}"
                                        class="ms-2 text-primary" title="{{ __('Download') }}">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    @if (!$previousStatementFinalized)
                                        {{ Form::open(['route' => ['student_single_account.previous_data.finalize', $previousStatementFile->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                                            <button type="submit" class="border-0 bg-transparent p-0 ms-2 finalize-glow"
                                                title="{{ __('Finalize') }}">
                                                <i class="ti ti-check"></i> Finalize
                                            </button>
                                        {{ Form::close() }}
                                    @elseif (\Auth::user()->type == 'company')
                                        {{ Form::open(['route' => ['student_single_account.previous_data.rollback', $previousStatementFile->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                                            <button type="submit" class="border-0 bg-transparent p-0 ms-2 text-warning"
                                                title="{{ __('Rollback') }}">
                                                <i class="ti ti-rotate-2"></i> Rollback
                                            </button>
                                        {{ Form::close() }}
                                    @endif
                                </p>
                            @else
                                <p class="text-muted mb-0">
                                    {{ __('No previous data sheet finalized yet.') }}
                                </p>
                            @endif
                        </div>

                        <div class="col-md-7">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                @if (\Auth::user()->type == 'company')
                                    {{ Form::open(['route' => 'student_single_account.previous_data.upload', 'method' => 'POST', 'files' => true, 'class' => 'd-flex flex-wrap justify-content-end gap-2', 'style' => 'width: 100%;']) }}
                                        {{ Form::hidden('student_id', $std->id) }}
                                        {{ Form::file('previous_data_file', ['class' => 'form-control form-control-sm', 'style' => 'max-width: 260px;', 'required' => 'required', 'accept' => '.xlsx,.xls,.csv']) }}
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="ti ti-upload me-1 text-light"></i>{{ !empty($previousStatementFile) ? __('Replace') : __('Upload') }}
                                        </button>
                                    {{ Form::close() }}
                                @elseif (empty($previousStatementFile) || !$previousStatementFinalized)
                                    {{ Form::open(['route' => 'student_single_account.previous_data.upload', 'method' => 'POST', 'files' => true, 'class' => 'd-flex flex-wrap justify-content-end gap-2', 'style' => 'width: 100%;']) }}
                                        {{ Form::hidden('student_id', $std->id) }}
                                        {{ Form::file('previous_data_file', ['class' => 'form-control form-control-sm', 'style' => 'max-width: 300px;', 'required' => 'required', 'accept' => '.xlsx,.xls,.csv']) }}
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="ti ti-upload me-1 text-light"></i>{{ __('Upload') }}
                                        </button>
                                    {{ Form::close() }}
                                @endif
                            </div>
                            @if (\Auth::user()->type != 'company' && $previousStatementFinalized)
                                <small class="text-muted d-block text-end mt-2">
                                    {{ __('This sheet is finalized. Branch users can download only.') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="content" id="report-content">
        <div class="card p-4 table-responsive maximumHeightNew">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem;"><b>The Lynx School</b></p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; font-weight: 800;">
                <p style="text-align: center; font-weight: 900; font-size: 1rem;">Student Single Account Statement</p>
                </p>
            </div>
            <div class="d-flex justify-content-between">
                <p><b>Period From :</b> {{ @$from_date }}</p>
                @if (!empty($std))
                    <p><b>{{ @$std->branches ? $std->branches->name : '' }}</b></p>
                @else
                    <p><b>{{ @$branches[$selected_branch] ?? '' }}</b></p>
                @endif
                <p><b>Period To :</b> {{ @$to_date }}</p>
            </div>
            @if (isset($std))
                <div class="d-flex justify-content-between" style="flex-direction:column;">
                    <div class="d-flex justify-content-between">
                        <p><b>Student Name: {{ @$std->stdname }}</b></p>
                        <p><b>Class: {{ @$std->class->name }}</b></p>
                        <p><b>Section: {{ @$std->enrollment->section->name }}</b></p>
                        <p><b>Roll No: {{ @$std->enrollment->enrollId }}</b></p>
                    </div>
                </div>
            @endif

            <table style="font-size:0.8rem;">
                <thead>
                    <tr class="table_heads report_table">
                        <th>Sr#</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Challan No</th>
                        <th>Billing Month</th>
                        <th>Challan Type</th>
                        <th>Fee Head</th>
                        <th>Receipt Mode</th>
                        <th>Receipt Ref.</th>
                        <th>Bank</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalCredit = 0; // Total receivables
                        $totalDebit = 0; // Total payments
                    @endphp

                    @foreach (@$accountStatement as $index => $item)
                        @php
                            // Calculate totals (excluding opening and closing)
                            if ($item['type'] != 'opening' && $item['type'] != 'closing') {
                                $totalCredit += $item['credit'];
                                $totalDebit += $item['debit'];
                            }

                            // Set row styling for opening/closing only
                            $rowClass = '';
                            if ($item['type'] == 'opening') {
                                $rowClass = 'opening-balance';
                            } elseif ($item['type'] == 'closing') {
                                $rowClass = 'closing-balance';
                            }
                        @endphp

                        <tr class="{{ $rowClass }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['description'] }}</td>
                            <td>{{ $item['challan_no'] ?? '-' }}</td>
                            <td>
                                {{ !empty($item['billing_month']) && $item['billing_month'] !== '-' ? $item['billing_month'] : '-' }}
                            </td>
                            <td>{{ $item['challan_type'] ?? '-' }}</td>
                            <td>{{ $item['head_name'] ?? '-' }}</td>
                            <td>{{ $item['receipt_mode'] ?? '-' }}</td>
                            <td>{{ $item['receipt_ref'] ?? '-' }}</td>
                            <td>{{ $item['bank_name'] ?? '-' }}</td>

                            @if ($rowClass == 'closing-balance')
                                {{-- Closing Balance Row - Show totals --}}
                                <td class="negative-value" style="color: red !important;">
                                    <b>{{ number_format($totalDebit, 2) }}</b>
                                </td>
                                <td class="positive-value" style="color: green !important;">
                                    <b>{{ number_format($totalCredit, 2) }}</b>
                                </td>
                            @else
                                {{-- Regular Rows --}}
                                <td class="{{ $item['debit'] > 0 ? 'negative-value' : '' }}">
                                    {{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}
                                </td>
                                <td class="{{ $item['credit'] > 0 ? 'positive-value' : '' }}">
                                    {{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}
                                </td>
                            @endif

                            <td><b>{{ number_format($item['balance'], 2) }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
