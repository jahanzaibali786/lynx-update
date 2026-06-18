@extends('layouts.admin')

@section('page-title')
    {{ __('Student Fee Revision Report') }}
@endsection

@push('script-page')
    <script>
        function exportFeeRevisionExcel() {
            var form = document.getElementById('fee_revision_report_form');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            window.location.href = "{{ route('fee_revision_report') }}?" + new URLSearchParams(formData).toString();
        }

        function refreshRevisionFilters(updateClasses) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ route('student_report.filter_students') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    branch_id: $('#revision_branch').val(),
                    class_id: $('#revision_class').val()
                },
                success: function(response) {
                    if (response.status !== 'success') {
                        return;
                    }

                    if (updateClasses) {
                        $('#revision_class').empty().append($('<option>', { value: 'all', text: 'All Classes' }));
                        response.classes.forEach(function(cls) {
                            $('#revision_class').append($('<option>', { value: cls.id, text: cls.name }));
                        });
                    }

                    $('#revision_student').empty().append($('<option>', { value: '', text: 'Select Student' }));
                    response.students.forEach(function(student) {
                        $('#revision_student').append($('<option>', { value: student.id, text: student.name }));
                    });
                }
            });
        }

        $(document).on('change', '#revision_branch', function() {
            refreshRevisionFilters(true);
        });

        $(document).on('change', '#revision_class', function() {
            refreshRevisionFilters(false);
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Fee Revision Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['fee_revision_report'], 'method' => 'GET', 'id' => 'fee_revision_report_form']) }}
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('session_from_id', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from_id', $sessions, request('session_from_id', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('session_to_id', __('Session To'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_to_id', $sessions, request('session_to_id', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request('branches', 'all'), ['class' => 'form-control select', 'id' => 'revision_branch']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $classes, request('class', 'all'), ['class' => 'form-control select', 'id' => 'revision_class']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Student'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                                    {{ Form::select('student', $students, request('student'), ['class' => 'form-control select', 'required' => 'required', 'id' => 'revision_student']) }}
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2 align-items-end">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('fee_revision_report_form').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('fee_revision_report') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="button" onclick="exportFeeRevisionExcel(); return false;">
                                                <i class="ti ti-file me-2"></i>
                                                <span class="btn-inner--icon">Excel</span>
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

    <div class="content" id="report-content">
        <div class="card p-4">
            <div style="width: 100%; text-align: center;">
                <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
                <p style="text-align:center; font-weight:600; font-size:1rem;">
                    {{ request('branches') && request('branches') !== 'all' ? ($branches[request('branches')] ?? 'Selected Branch') : 'All Branches' }}
                </p>
            </div>
            <div style="width: 100%; display: flex; justify-content: space-between;">
                <p><b>Session From: </b>{{ request('session_from_id') && request('session_from_id') !== 'all' ? ($sessions[request('session_from_id')] ?? '-') : 'All' }}</p>
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{ $reportName }}</p>
                <p><b>Session To: </b>{{ request('session_to_id') && request('session_to_id') !== 'all' ? ($sessions[request('session_to_id')] ?? '-') : 'All' }}</p>
            </div>

            @if ($studentDetail)
                <table style="width:100%; margin-bottom:12px;">
                    <tbody>
                        <tr>
                            <td><b>Roll No:</b> {{ optional($studentDetail->enrollment)->enrollId ?? $studentDetail->roll_no ?? '-' }}</td>
                            <td><b>Student:</b> {{ $studentDetail->stdname ?? '-' }}</td>
                            <td><b>Father:</b> {{ $studentDetail->fathername ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><b>Branch:</b> {{ optional(optional($studentDetail->enrollment)->branch)->name ?? optional($studentDetail->branches)->name ?? '-' }}</td>
                            <td><b>Class:</b> {{ optional(optional($studentDetail->enrollment)->class)->name ?? optional($studentDetail->class)->name ?? '-' }}</td>
                            <td><b>Section:</b> {{ optional(optional($studentDetail->enrollment)->section)->name ?? optional($studentDetail->section)->name ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @include('studentReports.partials.fee_revision_report_table', [
                'tableClass' => 'datatable maximumHeightNew',
                'theadClass' => 'sticky-headerNew',
                'headerClass' => 'table_heads report_table',
            ])
        </div>
    </div>
@endsection
