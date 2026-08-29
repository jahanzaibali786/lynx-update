@extends('layouts.admin')

@section('page-title')
    {{ __('Admission Listing') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        function branchcustomer(id) {
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
                    if (result.status === 'success') {
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
                            text: 'All Classes'
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

                        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }
                    }
                }
            });
        }

        function classStudents(id) {
            if (!id || id === 'all') {
                $('#student_select').empty().append($('<option>', {
                    value: 'all',
                    text: 'All Students'
                }));

                $('#section_select').empty().append($('<option>', {
                    value: 'all',
                    text: 'All Sections'
                }));

                return;
            }

            $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                url: "{{ route('class.students') }}",
                type: "POST",
                data: {
                    class_id: id
                },
                dataType: "json",
                success: function(result) {
                    if (result.status === "success") {
                        const $student = $("#student_select");

                        $student.empty();
                        $student.append($("<option>", {
                            value: "all",
                            text: "All Students"
                        }));

                        for (var sid in result.students) {
                            if (result.students.hasOwnProperty(sid)) {
                                $student.append($("<option>", {
                                    value: sid,
                                    text: result.students[sid]
                                }));
                            }
                        }

                        $student.val("all");
                    }

                    $.ajax({
                        url: "{{ route('class.section') }}",
                        type: "POST",
                        dataType: "json",
                        data: {
                            class_id: id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            const $section = $("#section_select");

                            $section.empty();
                            $section.append($("<option>", {
                                value: "all",
                                text: "All Sections"
                            }));

                            for (let i = 0; i < data.length; i++) {
                                $section.append($("<option>", {
                                    value: data[i].id,
                                    text: data[i].name
                                }));
                            }

                            $section.val("all");
                        }
                    });
                }
            });
        }

        $(document).on('change', '#class_select', function() {
            classStudents($(this).val());
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Admission Listing') }}</li>
@endsection

@section('action-btn')
    <style>
        .branch-totla-row td {
            background: #48494b4f !important;
            font-weight: 700;
        }

        .maximumHeightNew {
            max-height: 650px;
        }

        .sticky-headerNew th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 2;
        }

        .datatable th,
        .datatable td {
            white-space: nowrap;
            padding: 8px;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'admissionlisting', 'method' => 'GET', 'id' => 'admissionlisting']) }}

            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch', 'all'), [
                            'class' => 'form-control select custom-select',
                            'onchange' => 'branchcustomer(this.value)'
                        ]) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $classes, request('class', 'all'), [
                            'class' => 'form-control select custom-select',
                            'id' => 'class_select'
                        ]) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                        {{ Form::select('student', $student, request('student', 'all'), [
                            'class' => 'form-control select',
                            'id' => 'student_select'
                        ]) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('sections', __('Section'), ['class' => 'form-label']) }}
                        {{ Form::select('sections', $sections, request('sections', 'all'), [
                            'class' => 'form-control select',
                            'id' => 'section_select'
                        ]) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label']) }}
                        {{ Form::date('date_from', request('date_from'), ['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label']) }}
                        {{ Form::date('date_to', request('date_to'), ['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#"
                       class="btn mx-1 btn-sm btn-outline-primary"
                       onclick="document.getElementById('admissionlisting').submit(); return false;">
                        Search
                    </a>
                    <div class="d-flex align-items-center gap-2 report-export-actions">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle"
                                type="button"
                                id="exportDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="ti ti-file-spreadsheet me-2"></i> Export
                        </button>

                        <ul class="dropdown-menu report-dropdown-menu"
                            aria-labelledby="exportDropdown">
                            <li>
                                <button class="dropdown-item report-dropdown-item"
                                        type="submit"
                                        name="export"
                                        value="excel"
                                        form="admissionlisting">
                                    <i class="ti ti-file me-2"></i> Summary
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item report-dropdown-item"
                                        type="submit"
                                        name="export"
                                        value="detail_excel"
                                        form="admissionlisting">
                                    <i class="ti ti-list-details me-2"></i> Detailed
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-danger dropdown-toggle"
                                type="button"
                                id="pdfDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="ti ti-file-type-pdf me-2"></i> PDF
                        </button>

                        <ul class="dropdown-menu report-dropdown-menu"
                            aria-labelledby="pdfDropdown">
                            <li>
                                <button class="dropdown-item report-dropdown-item"
                                        type="submit"
                                        name="print"
                                        value="pdf"
                                        form="admissionlisting">
                                    <i class="ti ti-file-text me-2"></i> Summary
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item report-dropdown-item"
                                        type="submit"
                                        name="export"
                                        value="detail_pdf"
                                        form="admissionlisting">
                                    <i class="ti ti-list-details me-2"></i> Detailed
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>

    <div id="registrationcont" class="card">
        <div class="p-4" style="width: 100%; max-width: 100%; overflow-x: auto;">
            <div style="margin: 0 auto; padding: 30px;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;">
                        <b>The Lynx School</b>
                    </p>
                </div>

                <div style="width: 100%; display: flex; justify-content: space-between;">
                    <p>
                        <b>Period From:</b>
                        {{ request('date_from') ? date('d M Y', strtotime(request('date_from'))) : '-' }}
                    </p>

                    <p>
                        <b>Branch:</b>
                        {{ $branches[request('branch')] ?? 'All Branches' }}
                    </p>

                    <p>
                        <b>Period To:</b>
                        {{ request('date_to') ? date('d M Y', strtotime(request('date_to'))) : '-' }}
                    </p>
                </div>

                <div style="width: 100%;">
                    <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                        {{ $branches[request('branch')] ?? 'All Branches' }}
                    </span>

                    <div class="table-responsive maximumHeightNew" style="overflow-x: auto;">
                        <table class="datatable maximumHeightNew" style="width: 100%;">
                            <thead class="sticky-headerNew">
                                <tr class="table_heads" style="font-weight:400; font-size:0.8rem;">
                                    <th rowspan="2">{{ __('Sr No.') }}</th>
                                    <th rowspan="2">{{ __('B Sr No.') }}</th>
                                    <th rowspan="2">{{ __('Reg No #') }}</th>
                                    <th rowspan="2">{{ __('Roll No #') }}</th>
                                    <th rowspan="2">{{ __('Challan No #') }}</th>
                                    <th rowspan="2">{{ __('Billing Month') }}</th>
                                    <th rowspan="2">{{ __('Admission Date') }}</th>
                                    <th rowspan="2">{{ __('Class') }}</th>
                                    <th rowspan="2">{{ __('Student Name') }}</th>

                                    @foreach ($heads as $head)
                                        <th colspan="2" class="text-center">{{ $head->fee_head ?? '-' }}</th>
                                    @endforeach

                                    <th rowspan="2">{{ __('Amount') }}</th>
                                    <th rowspan="2">{{ __('Challan Status') }}</th>
                                    <th rowspan="2">{{ __('Student Status') }}</th>
                                    <th rowspan="2">{{ __('Discount Policy') }}</th>
                                </tr>
                                <tr class="table_heads" style="font-weight:400; font-size:0.75rem;">
                                    @foreach ($heads as $head)
                                        <th>{{ __('Paid') }}</th>
                                        <th>{{ __('Remaining') }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $mainloop = 1;
                                @endphp

                                @forelse ($studentData as $branchId => $students)
								<tr class="branch-name-row">
			    <td colspan="{{ 13 + (count($heads) * 2) }}">
			        <strong>{{ $branches[$branchId] ?? ($students->first()->branch->name ?? 'Unknown Branch') }}</strong>
			    </td>
			</tr>
                                    @foreach ($students as $index => $student)
                                        @php
                                            $studentKey = $student->regId;

                                            $challanData = $studentChallanData[$studentKey] ?? [
                                                'challan_no' => '',
                                                'challan_ids' => [],
                                                'challan_count' => 0,
                                                'fee_month' => '',
                                                'challan_status' => '',
                                                'heads' => [],
                                                'total' => 0,
                                                'discount_policy' => '',
                                            ];
                                        @endphp

                                        <tr>
                                            <td>{{ $mainloop++ }}</td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->StudentRegistration->reg_no ?? $student->regId ?? '-' }}</td>
                                            <td>{{ $student->enrollId ?? '-' }}</td>

                                            <td>
                                                {{ !empty($challanData['challan_no']) ? $challanData['challan_no'] : '-' }}
                                                @if (($challanData['challan_count'] ?? 0) > 1)
                                                    <small class="text-muted">({{ $challanData['challan_count'] }} challans)</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ !empty($challanData['fee_month']) ? $challanData['fee_month'] : '-' }}
                                            </td>
                                            <td>
                                                {{ !empty($student->adm_date) ? date('d M Y', strtotime($student->adm_date)) : '-' }}
                                            </td>

                                            <td>{{ $student->class->name ?? '-' }}</td>
                                            <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>

                                            @foreach ($heads as $head)
                                                @php
                                                    $headData = $challanData['heads'][$head->id] ?? null;
                                                @endphp
                                                <td>{{ $headData ? number_format($headData['paid_amount'] ?? 0, 2) : '-' }}</td>
                                                <td>{{ $headData ? number_format($headData['remaining_amount'] ?? 0, 2) : '-' }}</td>
                                            @endforeach

                                            <td>{{ number_format($challanData['total'] ?? 0, 2) }}</td>
                                            <td>{{ !empty($challanData['challan_status']) ? $challanData['challan_status'] : '-' }}</td>
                                            <td>{{ $student->StudentRegistration->student_status ?? '-' }}</td>
                                            <td>{{ !empty($challanData['discount_policy']) ? $challanData['discount_policy'] : '-' }}</td>
                                        </tr>
                                    @endforeach

                                    <tr class="branch-totla-row">
                                        <td colspan="9">
                                            <b>Branch Total</b>
                                        </td>

                                        @foreach ($heads as $head)
                                            @php
                                                $headTotal = $branchHeadTotals[$branchId][$head->id] ?? [];
                                            @endphp
                                            <td><strong>{{ number_format($headTotal['paid_amount'] ?? 0, 2) }}</strong></td>
                                            <td><strong>{{ number_format($headTotal['remaining_amount'] ?? 0, 2) }}</strong></td>
                                        @endforeach

                                        <td>
                                            <strong>{{ number_format($branchTotals[$branchId] ?? 0, 2) }}</strong>
                                        </td>

                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 13 + (count($heads) * 2) }}" class="text-center">
                                            No admission record found.
                                        </td>
                                    </tr>
                                @endforelse

                                <tr class="branch-totla-row" style="border-top: 5px solid #fff;">
                                    <td colspan="9">
                                        <b>Grand Total</b>
                                    </td>

                                    @foreach ($heads as $head)
                                        @php
                                            $headTotal = $grandHeadTotals[$head->id] ?? [];
                                        @endphp
                                        <td><strong>{{ number_format($headTotal['paid_amount'] ?? 0, 2) }}</strong></td>
                                        <td><strong>{{ number_format($headTotal['remaining_amount'] ?? 0, 2) }}</strong></td>
                                    @endforeach

                                    <td>
                                        <strong>{{ number_format($grandTotal ?? 0, 2) }}</strong>
                                    </td>

                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    .report-export-actions .report-dropdown-menu {
        min-width: 180px;
        background-color: #ffffff !important;
        border: 1px solid #d9dee3 !important;
        box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.12) !important;
        padding: 0.35rem !important;
        z-index: 1080;
    }

    .report-export-actions .report-dropdown-item {
        display: flex;
        align-items: center;
        width: 100%;
        background-color: #ffffff !important;
        color: #212529 !important;
        font-weight: 500;
        border-radius: 0.35rem;
        padding: 0.55rem 0.75rem;
    }

    .report-export-actions .report-dropdown-item i {
        color: #495057 !important;
    }

    .report-export-actions .report-dropdown-item:hover,
    .report-export-actions .report-dropdown-item:focus,
    .report-export-actions .report-dropdown-item:active {
        background-color: #f1f3f5 !important;
        color: #111111 !important;
    }

    .report-export-actions .report-dropdown-item:hover i,
    .report-export-actions .report-dropdown-item:focus i {
        color: #111111 !important;
    }
</style>

@endsection