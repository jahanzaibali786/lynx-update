@extends('layouts.admin')
@section('page-title', __('Student Pre-Challan Report'))

@push('script-page')
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

                    $('#student_select').html('<option value="all">All Students</option>');
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
                        value: 'all',
                        text: 'All Students'
                    }));

                    for (var j = 0; j < data.student.length; j++) {
                        var std = data.student[j];
                        $studentSelect.append($('<option>', {
                            value: std.roll_no,
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
    </script>

    <style>
        /* Container must scroll vertically for sticky thead to work */
        .table-container {
            width: 100%;
            max-height: 110vh;
            /* adjust height as needed */
            overflow-x: auto;
            overflow-y: auto;
        }

        .sticky-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sticky-table thead th {
            position: -webkit-sticky;
            /* standard */
            position: sticky !important;
            top: -10px !important;
            padding: 12px 8px !important;
            font-weight: 600 !important;
            z-index: 999 !important;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4) !important;
        }

        .sticky-table td,
        .sticky-table th {
            padding: 8px 12px;
            text-align: left;
        }

        .report-start {
            background-color: #fff;
        }
    </style>
@endpush
@section('action-btn')
    <div class="float-end mb-2">

        {{-- //pre challan reports list --}}
        <a href="{{ route('prechallan.index') }}" class="btn btn-sm btn-outline-primary">
            {{ __('Pre Challan List') }}
        </a>
    </div>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            {{ Form::open(['route' => 'monthlyprechallanreport', 'method' => 'GET']) }}
            <div class="row">
                <div class="col-xl-2 col-lg-2 col-md-3">
                    {{ Form::label('branches', 'Branches') }}
                    {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control custom-select', 'id' => 'branch']) }}
                </div>
                <div class="col-xl-2 col-lg-2 col-md-3">
                    {{ Form::label('class', 'Class') }}
                    {{ Form::select('class', $class, request('class'), ['class' => 'form-control custom-select', 'id' => 'class_select']) }}
                </div>
                <div class="col-xl-2 col-lg-2 col-md-3">
                    {{ Form::label('student', 'Student') }}
                    {{ Form::select('student', $students, request('student', 'all'), ['class' => 'form-control custom-select', 'id' => 'student_select']) }}
                </div>
                <div class="col-xl-2 col-lg-2 col-md-3">
                    {{ Form::label('date', 'Billing Month') }}
                    {{ Form::month('date', request('date', date('Y-m')), ['class' => 'form-control']) }}
                </div>
                <div class="col-xl-2 col-lg-2 col-md-3 mt-3">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success dropdown-toggle"
                            data-bs-toggle="dropdown">Export</button>
                        <ul class="dropdown-menu">
                            <li>
                                <button name="export" value="excel" class="dropdown-item" type="submit">
                                    Excel
                                </button>
                            </li>
                            <li>
                                <button name="export" value="pdf" class="dropdown-item" type="submit">
                                    PDF
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    @if ($report->isNotEmpty())
        <div class="mt-4 report-start">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem;"><b>The Lynx School</b></p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="font-size:1rem; font-weight: 800;">
                    Student Pre-Challan Report - {{ $month }}
                </p>
            </div>
            <div class="table-container table-responsive" style="width: 100%;">
                <table class="sticky-table datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr.</th>
                            <th>Branch</th>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Admission Date</th>
                            <th>Reg Type</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Monthly Fee</th>
                            @foreach ($heads as $head)
                                <th>{{ $head->fee_head }}</th>
                                <th>Disc</th>
                                <th>Net</th>
                            @endforeach
                            <th>Total</th>
                            <th>Discount</th>
                            <th>Net</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($report as $branchId => $students)
                            @foreach ($students as $stu)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $branches[$branchId] ?? '' }}</td>
                                    <td>{{ $stu['roll_no'] }}</td>
                                    <td>{{ $stu['student_name'] }}</td>
                                    <td>{{ $stu['adm_date'] ? \Carbon\Carbon::parse($stu['adm_date'])->format('d-M-Y') : '-' }}
                                    </td>
                                    <td>{{ $stu['registration_type'] }}</td>
                                    <td>{{ $stu['class_name'] }}</td>
                                    <td>{{ $stu['section_name'] }}</td>
                                    <td>{{ round($stu['total_amount']) }}</td>

                                    @foreach ($heads as $head)
                                        @php
                                            $d = $stu['head_details'][$head->id] ?? [
                                                'amount' => 0,
                                                'discount_amount' => 0,
                                                'net_amount' => 0,
                                            ];
                                        @endphp
                                        <td>{{ round($d['amount']) }}</td>
                                        <td>{{ round($d['discount_amount']) }}</td>
                                        <td>{{ round($d['net_amount']) }}</td>
                                    @endforeach

                                    <td>{{ round($stu['total_amount']) }}</td>
                                    <td>{{ round($stu['total_discount']) }}</td>
                                    <td>{{ round($stu['total_net']) }}</td>
                                    <td>{{ $stu['concession_category'] }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
