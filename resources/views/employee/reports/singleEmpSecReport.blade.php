@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Security Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Emp. Security Report') }}</li>
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        function branchemployees(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.employees') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status === 'success') {
                        var $empSelect = $('#employee_id');
                        if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                            try {
                                $empSelect[0].customSelectInstance.destroy();
                            } catch (e) {}
                            delete $empSelect[0].customSelectInstance;
                        }
                        if ($empSelect.next('.custom-select-wrapper').length) {
                            $empSelect.next('.custom-select-wrapper').remove();
                        }
                        $empSelect.removeClass('custom-select');
                        $empSelect.empty();
                        $empSelect.append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        var added = 0;
                        for (var j = 0; j < result.employee.length; j++) {
                            var emp = result.employee[j];
                            $empSelect.append($('<option>', {
                                value: emp.id,
                                text: emp.name
                            }));
                            added++;
                        }

                        if (added === 0) {
                            // show a disabled placeholder if no active employees
                            $empSelect.append($('<option>', {
                                value: '',
                                text: 'No active employees',
                                disabled: true
                            }));
                        }

                        // re-init custom select for this element only
                        $empSelect.addClass('custom-select');
                        $empSelect.show();
                        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                            window.CustomSelect.create($empSelect[0]);
                        }
                    } else {
                        console.warn('branchemployees returned status:', result.status);
                    }
                },
            });
        }

        function validateAndSubmit() {
            const branch = document.querySelector('[name="branches"]');
            const employee = document.querySelector('[name="employee_id"]');

            if (!branch.value) {
                alert("Please select a branch.");
                branch.focus();
                return false;
            }
            if (!employee.value) {
                alert("Please select an employee.");
                employee.focus();
                return false;
            }

            document.getElementById('singleEmpSecReport').submit();
        }
    </script>
@endpush
@section('content')
    {{-- //filter employee and branch --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['singleEmpSecReport'], 'method' => 'GET', 'id' => 'singleEmpSecReport']) }}
                        <div class="row d-flex justify-content-end" id="filter_change">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->input('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_id', $employees, request()->input('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'employee_id', 'placeholder' => __('Select Employee'), 'required' => 'required']) }}
                                </div>
                            </div>
                            @php
                                $currentYear = date('Y');
                                $years = [];
                                for ($year = 2016; $year <= $currentYear; $year++) {
                                    $years[$year] = $year;
                                }

                                // Get selected values or default to current year
                                $fromYear = request()->input('from_year') ?? 2016;
                                $toYear = request()->input('to_year') ?? $currentYear;
                            @endphp
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_year', __('From'), ['class' => 'form-label']) }}
                                    {{ Form::select('from_year', $years, $fromYear, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_year', __('To'), ['class' => 'form-label']) }}
                                    {{ Form::select('to_year', $years, $toYear, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                {{-- // actions  --}}
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="validateAndSubmit(); return false;" data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>

                                <a href="{{ route('singleEmpSecReport') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                                                    <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block mx-1">
                                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
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
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Emp. Security Report') }}</h5>
                </div>
                @if ($empFilter)
                    <div class="card-body">
                        <div class="d-flex justify-content-start">
                            <div class="w-100" style="max-width: 520px;">
                                <div class="row g-1">
                                    <div class="col-5 text-start fw-semibold">Branch Name:</div>
                                    <div class="col-7 text-start">{{ $employee->branch->name ?? '-' }}</div>

                                    <div class="col-5 text-start fw-semibold">Employee Name:</div>
                                    <div class="col-7 text-start">{{ $employee->name ?? '-' }}</div>

                                    <div class="col-5 text-start fw-semibold">Designation:</div>
                                    <div class="col-7 text-start">{{ $employee->designation->name ?? '-' }}</div>

                                    <div class="col-5 text-start fw-semibold">Date of Joining:</div>
                                    <div class="col-7 text-start">{{ $employee->company_doj ?? '-' }}</div>

                                    <div class="col-5 text-start fw-semibold">Period From:</div>
                                    <div class="col-7 text-start">{{ $fromYear }}</div>

                                    <div class="col-5 text-start fw-semibold">Period To:</div>
                                    <div class="col-7 text-start">{{ $toYear }}</div>

                                    <div class="col-5 text-start fw-semibold">Opening Balance:</div>
                                    <div class="col-7 text-start">{{ number_format($openingBalance, 2) }}</div>

                                    <div class="col-5 text-start fw-semibold">Closing Balance:</div>
                                    <div class="col-7 text-start">{{ number_format($closingBalance, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="datatable table-bordered">
                                        <thead class="table_heads">
                                            <tr>
                                                <th style="position: sticky; left: 0; background: #fff; z-index: 2;" width="150px">Months
                                                </th>
                                                @for ($year = $fromYear; $year <= $toYear; $year++)
                                                    <th width="150px">{{ $year }}</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Monthly data rows --}}
                                            @for ($month = 1; $month <= 12; $month++)
                                                <tr>
                                                    <td style="position: sticky; left: 0; background: #f8f9fa; z-index: 1; width: 100px;" width="150px">
                                                        {{ \Carbon\Carbon::create(null, $month)->format('F') }}
                                                    </td>
                                                    @for ($year = $fromYear; $year <= $toYear; $year++)
                                                        <td width="150px">{{ $salaryData[$year][$month] ?? 0 }}</td>
                                                    @endfor
                                                </tr>
                                            @endfor

                                            {{-- Yearly totals --}}
                                            <tr>
                                                <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 1;">
                                                    Total</th>
                                                @for ($year = $fromYear; $year <= $toYear; $year++)
                                                    <th>{{ number_format($yearlyTotals[$year] ?? 0, 2) }}</th>
                                                @endfor
                                            </tr>

                                            {{-- Grand total --}}
                                            <tr>
                                                <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 1;">
                                                    Grand Total</th>
                                                <th colspan="{{ $toYear - 2016 + 1 }}" class="text-end">{{ number_format(array_sum($yearlyTotals), 2) }}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                    </div>
                @endif
            </div>
        </div>
    @endsection
