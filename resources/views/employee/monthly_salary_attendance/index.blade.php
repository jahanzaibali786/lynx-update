@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Monthly Salary Attendance') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Salary Attendance') }}</li>
@endsection
@push('css-page')
    <style>
        .attendance-summary-badges .badge {
            font-size: 12px;
            line-height: 1.4;
            padding: 7px 10px;
            font-weight: 600;
        }
    </style>
@endpush
@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let attendanceActionProcessing = false;

        function startAttendanceAction(button, title) {
            if (attendanceActionProcessing) {
                return false;
            }

            attendanceActionProcessing = true;
            $('.attendance-action-btn').addClass('disabled').attr('aria-disabled', 'true');
            $(button).data('original-html', $(button).html()).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

            Swal.fire({
                title: title || 'Processing...',
                text: 'Please wait.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });

            return true;
        }

        function stopAttendanceAction() {
            attendanceActionProcessing = false;
            $('.attendance-action-btn').each(function() {
                $(this).removeClass('disabled').removeAttr('aria-disabled');
                if ($(this).data('original-html')) {
                    $(this).html($(this).data('original-html'));
                    $(this).removeData('original-html');
                }
            });
        }

        document.getElementById('generate-btn').addEventListener('click', function(event) {
            event.preventDefault();
            if (!startAttendanceAction(this, 'Generating attendance...')) {
                return;
            }
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('emp-month-sal-attendance.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(result) {
                    if (result.success) {
                        let msg = result.message;

                        if (result.skipped && result.skipped.length > 0) {
                            msg += '<br><br><strong>Skipped Employees:</strong><ul>';
                            result.skipped.forEach(function(item) {
                                msg +=
                                    `<li><b>Emp #:</b> ${item.employee_id} - ${item.reason}</li>`;
                            });
                            msg += '</ul>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Attendance Generated',
                            html: msg,
                            confirmButtonText: 'OK',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Something went wrong!'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: error || 'Check console for details.'
                    });
                },
                complete: function() {
                    stopAttendanceAction();
                }
            });
        });


        // Check/uncheck all checkboxes
        if (document.getElementById('check-all')) {
            document.getElementById('check-all').addEventListener('change', function(event) {
                var checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = event.target.checked;
                });
            });
        }
        document.getElementById('finalize-btn').addEventListener('click', function(event) {
            event.preventDefault();
            if (attendanceActionProcessing) {
                return;
            }
            var checkedRows = [];
            var checkboxes = document.querySelectorAll('.row-checkbox:checked');
            checkboxes.forEach(function(checkbox) {
                checkedRows.push(checkbox.value);
            });

            if (checkedRows.length > 0) {
                if (!startAttendanceAction(this, 'Finalizing attendance...')) {
                    return;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('finalize_attendance') }}",
                    type: "POST",
                    data: {
                        rows: checkedRows
                    },
                    success: function(result) {
                        if (result.success) {
                            // alert(result.message);
                            Swal.fire({
                                icon: 'success',
                                title: 'Attendance Finalized',
                                text: result.message,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // alert(result.message);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message,
                                confirmButtonText: 'OK',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: error || 'Check console for details.',
                        });
                    },
                    complete: function() {
                        stopAttendanceAction();
                    }
                });
            } else {
                // alert('No rows selected');
                Swal.fire({
                    icon: 'warning',
                    title: 'No Rows Selected',
                    text: 'Please select at least one row to finalize.',
                    confirmButtonText: 'OK',
                });
            }
        });
        //ddelete

        document.getElementById('delete-btn').addEventListener('click', function(event) {
            event.preventDefault();
            if (attendanceActionProcessing) {
                return;
            }
            var checkedRows = [];
            var checkboxes = document.querySelectorAll('.row-checkbox:checked');
            checkboxes.forEach(function(checkbox) {
                checkedRows.push(checkbox.value);
            });

            if (checkedRows.length > 0) {
                if (confirm('Are you sure you want to delete the selected entries?')) {
                    if (!startAttendanceAction(this, 'Deleting attendance...')) {
                        return;
                    }
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('emp-month-sal-attendance.bulkDelete') }}",
                        type: "DELETE",
                        data: {
                            rows: checkedRows
                        },
                        success: function(result) {
                            if (result.success) {
                                // alert(result.message);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Entries Deleted',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                // alert(result.message);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Request Failed',
                                text: error || 'Check console for details.',
                            });
                        },
                        complete: function() {
                            stopAttendanceAction();
                        }
                    });
                }
            } else {
                // alert('No rows selected');
                Swal.fire({
                    icon: 'warning',
                    title: 'No Rows Selected',
                    text: 'Please select at least one row to delete.',
                    confirmButtonText: 'OK',
                });
            }
        });

        function salaryAttendanceExport() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var reportType = document.getElementById('attendance_export_report_type').value || 'summary';
            var exportType = document.getElementById('attendance_export_type').value || 'xlsx';

            formData.set('report_type', reportType);
            formData.set('export_type', exportType);

            var exportUrl = "{{ route('salary_attendance_export') }}?" + new URLSearchParams(formData).toString();
            if (exportType === 'pdf') {
                window.open(exportUrl, '_blank');
                return;
            }

            window.location.href = exportUrl;
        }
    </script>

    <script>
        $(document).ready(function() {
            $(document).on('change', '#department_ids', function() {
                var department_id = $(this).val();
                getDesignation(department_id);
            });

            function getDesignation(did) {

                $.ajax({
                    url: '{{ route('employee.json') }}',
                    type: 'POST',
                    data: {
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        // console.log(data);
                        // $('#des').empty().append(`{{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                    //         <select class="select2 form-control " id="designation_id" name="designation_id" data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                    //             <option value="">{{ __('Select any Designation') }}</option>
                    //         </select>`);
                        $('#designation_ids').empty();

                        $('#designation_ids').append(
                            '<option value="">{{ __('Select any Designation') }}</option>');
                        $.each(data, function(key, value) {
                            $('#designation_ids').append('<option value="' + key + '">' +
                                value + '</option>');
                        });
                    }
                });
            }
        });
    </script>
@endpush
@section('content')
    @php
        $attendanceTotal = $datas->count();
        $attendanceGenerated = $datas->where('accountant_finalize', 0)->where('adm_final', 0)->where('sal_final', 0)->where('gm_final', 0)->count();
        $attendanceFinalized = $datas->where('accountant_finalize', 1)->count();
        $attendanceAdminForwarded = $datas->where('adm_final', 1)->count();
        $attendanceSalaryFinal = $datas->where('sal_final', 1)->count();
        $attendanceGmFinal = $datas->where('gm_final', 1)->count();
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['emp-month-sal-attendance.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branchesList, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('department_id', $departments, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control select', 'id' => 'department_ids']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                    {{ Form::select('designation_id', $designations, isset($_GET['designation_id']) ? $_GET['designation_id'] : '', ['class' => 'form-control select', 'id' => 'designation_ids']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', $date ?? isset($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : now()->format('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="d-flex flex-wrap align-items-center gap-1 attendance-summary-badges">
                                        <span class="badge bg-secondary">{{ __('Total Rows') }}: {{ $attendanceTotal }}</span>
                                        <span class="badge bg-light text-dark">{{ __('Generated') }}: {{ $attendanceGenerated }}</span>
                                        <span class="badge bg-info">{{ __('Fwd to Admin') }}: {{ $attendanceFinalized }}</span>
                                        <span class="badge bg-warning text-dark">{{ __('Finalized') }}: {{ $attendanceAdminForwarded }}</span>
                                        <span class="badge bg-primary">{{ __('Salary Final') }}: {{ $attendanceSalaryFinal }}</span>
                                        <span class="badge bg-success">{{ __('GM Final') }}: {{ $attendanceGmFinal }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-1">
                                        <select id="attendance_export_report_type" class="form-select form-select-sm" style="width: 130px;">
                                            <option value="summary">{{ __('Summary') }}</option>
                                            <option value="details">{{ __('Details') }}</option>
                                        </select>
                                        <select id="attendance_export_type" class="form-select form-select-sm" style="width: 130px;">
                                            <option value="xlsx">{{ __('Excel') }}</option>
                                            <option value="pdf">{{ __('PDF / Print') }}</option>
                                        </select>
                                        <a href="#" class="btn btn-sm btn-outline-secondary"
                                            onclick="salaryAttendanceExport(); return false;"
                                            data-bs-title="Export">
                                            <span class="btn-inner--icon">Export</span>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-primary"
                                            onclick="document.getElementById('employee_submit').submit(); return false;"
                                            data-bs-toggle="{{ __('Apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a id="finalize-btn" href="#" class="btn btn-sm btn-outline-danger attendance-action-btn"
                                            data-bs-title="Finalize / FWD to Admin">
                                            <span class="btn-inner--icon">Finalize / FWD to Admin</span>
                                        </a>
                                        <a id="delete-btn" href="#" class="btn btn-sm btn-outline-danger attendance-action-btn"
                                            data-bs-title="Delete">
                                            <span class="btn-inner--icon">Delete</span>
                                        </a>
                                        <a id="generate-btn" href="#" class="btn btn-sm btn-outline-success attendance-action-btn"
                                            data-bs-title="Generate">
                                            <span class="btn-inner--icon">Generate</span>
                                        </a>
                                        {{-- <a href="{{ route('emp-month-sal-attendance.index') }}"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">Clear</span>
                                        </a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($datas->isNotEmpty())
        <div class="table-responsive">
            <table class="">
                <thead>
                    <tr class="table_heads">
                        <th>{{ __('Sr. No') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Sal. Month') }}</th>
                        <th>{{ __('WorkingDays') }}</th>
                        <th style="width: 50px;">{{ __('Absents') }}</th>
                        <th>{{ __('Total Annual') }}</th>
                        <th>{{ __('Bal.Annual') }}</th>
                        <th>{{ __('Total Casual') }}</th>
                        <th>{{ __('Bal. Casual') }}</th>
                        <th>{{ __('Leave') }}</th>
                        <th>{{ __('MonthDays') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Finalize') }}</th>
                        <th>{{ __('AdmFinal') }}</th>
                        <th>{{ __('SalFinal') }}</th>
                        <th>{{ __('GmFinal') }}</th>
                        {{-- <th>{{__('lock')}}</th> --}}
                        <th><input type="checkbox" id="check-all"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datas as $data)
                        <tr
                            style="color:
                    @if (isset($data->gm_final) && trim(strtolower($data->gm_final)) == 1) green
                    @elseif (isset($data->sal_final) && trim(strtolower($data->sal_final)) == 1)
                        blue
                    @elseif (isset($data->adm_final) && trim(strtolower($data->adm_final)) == 1)
                        red
                    @elseif (isset($data->accountant_finalize) && trim(strtolower($data->accountant_finalize)) == 1)
                        red
                    @else
                        black @endif">
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-style">{{ !empty($data) ? $data->employee->name : '' }}</td>
                            <td>{{ !empty($data) ? date('M-y', strtotime($data->for_month_of)) : '' }}</td>
                            <td>{{ !empty($data) ? $data->working_days : '' }}</td>
                            <td><input type="text" style="width: 50px;"
                                    value="{{ !empty($data) ? $data->absents : '' }}" readonly>
                            </td>
                            <td>{{ !empty($data) ? $data->total_annual : '' }}</td>
                            <td>{{ !empty($data) ? $data->bal_annual : '' }}</td>
                            <td>{{ !empty($data) ? $data->total_casual : '' }}</td>
                            <td>{{ !empty($data) ? $data->bal_casual : '' }}</td>
                            <td><input type="text" style="width: 50px;" value="{{ !empty($data) ? $data->leave : '' }}"
                                    readonly>
                            </td>
                            <td>{{ !empty($data) ? $data->month_days : '' }}</td>
                            <td>
                                @if (!empty($data) && $data->gm_final == 1)
                                    <span class="badge bg-success">{{ __('GM Final') }}</span>
                                @elseif (!empty($data) && $data->sal_final == 1)
                                    <span class="badge bg-primary">{{ __('Salary Final') }}</span>
                                @elseif (!empty($data) && $data->adm_final == 1)
                                    <span class="badge bg-warning text-dark">{{ __('Finalized') }}</span>
                                @elseif (!empty($data) && $data->accountant_finalize == 1)
                                    <span class="badge bg-info">{{ __('Fwd to Admin') }}</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ __('Generated') }}</span>
                                @endif
                            </td>
                            <td><input type="checkbox" name="finalized"
                                    {{ !empty($data) && $data->accountant_finalize == 1 ? 'checked' : '' }} disabled></td>
                            <td><input type="checkbox" name="admfinal"
                                    {{ !empty($data) && $data->adm_final == 1 ? 'checked' : '' }} disabled></td>
                            <td><input type="checkbox" name="salfinal"
                                    {{ !empty($data) && $data->sal_final == 1 ? 'checked' : '' }} disabled></td>
                            <td><input type="checkbox" name="gmfinal"
                                    {{ !empty($data) && $data->gm_final == 1 ? 'checked' : '' }} disabled></td>

                            {{-- <td><input type="checkbox" name="lock" {{ !empty($data) && $data->lock_status == 1 ? 'checked' : '' }}>
                        </td> --}}
                            <td><input type="checkbox" class="row-checkbox" value="{{ $data->id }}"></td>
                            {{-- <td>
                        <div class="action-btn bg-danger ms-2">

                            {!! Form::open(['method' => 'DELETE', 'route' => ['emp-month-sal-attendance.destroy',
                            $data->id],'id'=>'delete-form-'.$data->id]) !!}
                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" 
                                data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                                data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                                data-confirm-yes="document.getElementById('delete-form-{{$data->id}}').submit();">
                                <i class="ti ti-trash text-white"></i></a>
                            {!! Form::close() !!}
                        </div>
                        </td> --}}

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- @if ($datas->hasPages())
            <div class="pagination">
                <ul>
                    @if ($datas->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $datas->appends(request()->query())->previousPageUrl() }}"
                                rel="prev">&laquo; Previous</a></li>
                    @endif
                    @if ($datas->currentPage() > 1)
                        <li><a href="{{ $datas->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $datas->currentPage();
                        $lastPage = $datas->lastPage();
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
                        <li class="{{ $page == $datas->currentPage() ? 'active' : '' }}">
                            <a href="{{ $datas->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($datas->hasMorePages())
                        <li><a href="{{ $datas->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($datas->currentPage() < $datas->lastPage())
                        <li><a
                                href="{{ $datas->appends(request()->query())->url($datas->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif --}}
    @endif
@endsection
