@extends('layouts.admin')
@section('page-title')
    {{ __('Employee Salary History Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>

    {{-- <script>
        function generatePDF() {
            console.log('generating');
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'emp_sal_history.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: [700, 950],
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            console.log('printing');
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'emp_sal_history.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: [700, 950],
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }
    </script> --}}
    <script>
        function validateAndSubmitForm(isPrint = false) {
            const form = document.getElementById('student_receipt_list');
            const empIdValue = document.querySelector('#employee_id').value;

            if (empIdValue === '') {
                alert('Select Employee !');
                return false;
            }

            document.getElementById('is_print').value = isPrint ? '1' : '0';

            form.submit();
        }

        function submitWithPrintFlag() {
            validateAndSubmitForm(true);
        }
    </script>

    <style>
        @media print {
            .table-responsive {
                overflow: visible !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
            }
        }
    </style>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary History Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['salary_history_report'], 'method' => 'GET', 'id' => 'student_receipt_list', 'onsubmit' => 'return validateForm()']) }}
                        <div class="row d-flex justify-content-start ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('from_date', request()->get('from_date') ?? date('Y-m-d', strtotime('-1 year')), ['class' => 'form-control']) }}
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
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('department_id', $departments, request()->get('department_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <input type="hidden" name="is_print" id="is_print" value="0">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                    {{ Form::select('designation_id', $designations, request()->get('designation_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('employee_id', __('Employees'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_id', $employees, request()->get('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'employee_id', 'required' => 'required']) }}
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="submitForm(); return false;" data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"  data-bs-title="Print"><span
                                    class="btn-inner--icon">Print</span>
                            </a> --}}
                                {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="validateAndSubmitForm(true); return false;"
                                    data-bs-title="{{ __('Print') }}">
                                    <span class="btn-inner--icon">Print</span>
                                </a>
                                <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export"
                                    value="excel" data-bs-title="Download Report"><span
                                        class="btn-inner--icon">Export</span></button> --}}
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
                                {{-- <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary" onclick="submitForm(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()" 
                                    data-bs-title="Print"><span class="btn-inner--icon">Print</span>
                                </a>
                                <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" value="excel"
                                     data-bs-title="Download Report"><span
                                        class="btn-inner--icon">Pdf / Print</span></button>
                            </div> --}}
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-2 p-4" id="studentfeereceipt">
            <div style="margin: 0 auto;">

                <div class="mt-4" style="margin: 0 auto; padding: 30px;">
                    <div style="width: 100%; text-align: center;">
                        <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School
                            </b></p>
                    </div>
                    <div style="width: 100%; text-align: center;">
                        <p style="font-size:1rem; text-align: center; font-weight: 800;">Employee Salary History
                        </p>
                    </div>
                    <div class="" style="width:100%">
                        <p style="width: 34%; float:left;"><b>From Date:
                            </b>{{ request()->get('from_date') ?? date('Y-M-d') }}
                        </p>
                        <p style="width: 34%; float:left;"></p>
                        <p style="width: 30%; float:left; padding-left:100px;"><b>To Date:
                            </b>{{ request()->get('to_date') ?? date('Y-M-d') }}</p>
                    </div>
                    <div class="table-responsive" style="width: 80%; ">
                        @php
                            // Collect all unique salary heads
                            $uniqueSalaryHeads = collect();
                            foreach ($employee as $emp) {
                                foreach ($emp->employee_monthly_salaries ?? [] as $salary) {
                                    foreach ($salary->salaryheads ?? [] as $salhead) {
                                        if ($salhead->SalaryHead->head ?? '') {
                                            $uniqueSalaryHeads->push([
                                                'id' => $salhead->SalaryHead->id ?? $salhead->salary_head_id,
                                                'head' => $salhead->SalaryHead->head ?? '',
                                            ]);
                                        }
                                    }
                                }
                            }
                            // Remove duplicates based on salary head ID
                            $uniqueSalaryHeads = $uniqueSalaryHeads->unique('id');
                        @endphp

                        <table class="">
                            <thead>
                                <tr class="table_heads" style="font-size:0.8rem;">
                                    <th>{{ __('Sr No.') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Pay Scale') }}</th>
                                    <th>{{ __('Desg.') }}</th>

                                    {{-- Display unique salary heads --}}
                                    @foreach ($uniqueSalaryHeads as $salaryHead)
                                        <th>{{ $salaryHead['head'] }}</th>
                                    @endforeach

                                    <th>{{ __('Earned Basic') }}</th>
                                    <th>{{ __('Other') }}</th>
                                    <th>{{ __('Sal') }}</th>
                                    <th>{{ __('Other Allowance') }}</th>
                                    <th>{{ __('Drns & Misc') }}</th>
                                    <th>{{ __('stop_sal') }}</th>
                                    <th>{{ __('Gross') }}</th>
                                    <th>{{ __('Emp.sec') }}</th>
                                    <th>{{ __('Adv.Tax') }}</th>
                                    <th>{{ __('EOBI') }}</th>
                                    <th>{{ __('Loan E.s') }}</th>
                                    <th>{{ __('Other Deduction') }}</th>
                                    <th>{{ __('Stop_sal') }}</th>
                                    <th>{{ __('PESSI') }}</th>
                                    <th>{{ __('Loan Adj.') }}</th>
                                    <th>{{ __('Net') }}</th>
                                    <th>{{ __('PESSI Comp') }}</th>
                                    <th>{{ __('EOBI Comp') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Cost to Comp.') }}</th>
                                    <th>{{ __('OP') }}</th>
                                    <th>{{ __('LVS') }}</th>
                                    <th>{{ __('Bal') }}</th>
                                    <th>{{ __('OP') }}</th>
                                    <th>{{ __('LVS') }}</th>
                                    <th>{{ __('Bal') }}</th>
                                    <th>{{ __('Working Days') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employee as $emp)
                                    @foreach (@$emp->employee_monthly_salaries ?? [] as $salary)
                                        @php
                                            $payscale = $emp->employee_payscale_details->last();
                                        @endphp
                                        <tr style="font-size:0.7rem;">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Auth::user()->getBranch($emp->branch_id)->name }}</td>
                                            <td>{!! \Carbon\Carbon::parse(@$salary->salary_date)->format('d-F-Y') !!}</td>
                                            <td>{{ @$salary->scale_no }}</td>
                                            <td>{!! @$emp->designation->name !!}</td>

                                            {{-- Display salary head values in order --}}
                                            @foreach ($uniqueSalaryHeads as $uniqueHead)
                                                @php
                                                    $headValue = '';
                                                    foreach (@$salary->salaryheads ?? [] as $salhead) {
                                                        if (
                                                            ($salhead->SalaryHead->id ?? $salhead->salary_head_id) ==
                                                            $uniqueHead['id']
                                                        ) {
                                                            $headValue = $salhead->head_value;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <td>{{ $headValue ?: '0' }}</td>
                                            @endforeach

                                            <td>{{ !empty(@$salary->basics) ? $salary->basics : '0' }}</td>
                                            <td>{{ !empty(@$salary->conv) ? @$salary->conv : '0' }}</td>
                                            <td>{{ !empty(@$salary->sal_all) ? @$salary->sal_all : '0' }}</td>
                                            <td>{{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                                            <td>{{ (@$salary->drns ?? 0) + (@$salary->misc ?? 0) }}</td>
                                            <td>{{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                                            <td>{{ !empty(@$salary->gross) ? @$salary->gross : '0' }}</td>
                                            <td>{{ !empty(@$salary->emp_sec) ? @$salary->emp_sec : '0' }}</td>
                                            <td>{{ !empty(@$salary->it) ? @$salary->it : '0' }}</td>
                                            <td>{{ !empty(@$payscale->eobi) ? @$payscale->eobi : '0' }}</td>
                                            <td>{{ !empty(@$salary->loan) ? @$salary->loan : '0' }}</td>
                                            <td>{{ !empty(@$salary->other) ? @$salary->other : '0' }}</td>
                                            <td>{{ !empty(@$salary->stop_sal) ? @$salary->stop_sal : '0' }}</td>
                                            <td>{{ !empty(@$payscale->pessi) ? @$payscale->pessi : '0' }}</td>
                                            <td>{{ !empty(@$salary->loan_adj) ? @$salary->loan_adj : '0' }}</td>
                                            @php
                                                $total_deduction =
                                                    @$salary->gross -
                                                    (@$salary->emp_sec +
                                                        @$salary->it +
                                                        @$payscale->eobi +
                                                        @$salary->loan +
                                                        @$salary->other +
                                                        @$salary->stop_sal +
                                                        @$salary->loan_adj +
                                                        @$payscale->pessi);
                                            @endphp
                                            <td>{{ $total_deduction }}</td>
                                            <td>{{ !empty(@$payscale->eobi_employer) ? @$payscale->eobi_employer : '0' }}
                                            </td>
                                            <td>{{ !empty(@$payscale->pessi_employer) ? @$payscale->pessi_employer : '0' }}
                                            </td>
                                            @php
                                                $total_sum =
                                                    @$salary->loan_adj +
                                                    @$payscale->eobi_employer +
                                                    @$payscale->pessi_employer;
                                                $total_addition = @$total_deduction + @$total_sum;
                                                
                                                $targetMonth = \Carbon\Carbon::parse($salary->salary_date)->format('Y-m');

                                                $attendance = $emp->employee_monthly_salaries_attend
                                                    ->filter(function ($att) use ($targetMonth) {
                                                        return \Illuminate\Support\Str::startsWith($att->for_month_of, $targetMonth);
                                                    })
                                                    ->first();
                                            @endphp
                                            <td>{{ !empty(@$total_sum) ? @$total_sum : '0' }}</td>
                                            <td>{{ !empty(@$total_addition) ? @$total_addition : '0' }}</td>
                                            <td>{{ !empty(@$attendance->total_annual) ? @$attendance->total_annual : '0' }}</td>
                                            <td>{{ (!empty(@$attendance->total_annual) ? @$attendance->total_annual : '0') - (!empty(@$attendance->bal_annual) ? @$attendance->bal_annual : '0') }}</td>
                                            <td>{{ !empty(@$attendance->bal_annual) ? @$attendance->bal_annual : '0' }}</td>
                                            <td>{{ !empty(@$attendance->total_casual) ? @$attendance->total_casual : '0' }}</td>
                                            <td>{{ (!empty(@$attendance->total_casual) ? @$attendance->total_casual : '0') - (!empty(@$attendance->bal_casual) ? @$attendance->bal_casual : '0') }}</td>
                                            <td>{{ !empty(@$attendance->bal_casual) ? @$attendance->bal_casual : '0' }}</td>
                                            <td>{{ (!empty(@$attendance->working_days) ? @$attendance->working_days : '0')  }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        @if (is_object($employees) && method_exists($employees, 'hasPages') && $employees->hasPages())
                            <div class="pagination">
                                <ul>
                                    @if ($employees->onFirstPage())
                                        <li class="disabled">&laquo; Previous</li>
                                    @else
                                        <li><a href="{{ $employees->appends(request()->query())->previousPageUrl() }}"
                                                rel="prev">&laquo; Previous</a></li>
                                    @endif
                                    @if ($employees->currentPage() > 1)
                                        <li><a href="{{ $employees->appends(request()->query())->url(1) }}">First</a></li>
                                    @endif
                                    @php
                                        $currentPage = $employees->currentPage();
                                        $lastPage = $employees->lastPage();
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
                                        <li class="{{ $page == $employees->currentPage() ? 'active' : '' }}">
                                            <a
                                                href="{{ $employees->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endfor
                                    @if ($employees->hasMorePages())
                                        <li><a href="{{ $employees->appends(request()->query())->nextPageUrl() }}"
                                                rel="next">Next
                                                &raquo;</a></li>
                                    @else
                                        <li class="disabled">Next &raquo;</li>
                                    @endif
                                    @if ($employees->currentPage() < $employees->lastPage())
                                        <li><a
                                                href="{{ $employees->appends(request()->query())->url($employees->lastPage()) }}">Last</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <script>
            function submitForm() {
                var employeeSelect = document.getElementById('employee_id');
                if (employeeSelect.value) {
                    document.getElementById('student_receipt_list').submit();
                } else {
                    alert('Please select an employee.');
                }
            }
            function branchemployees(id) {
                // remember previous selection so we can restore if still available
                var prevVal = $('#employee_id').val();

                function isResigned(emp) {
                    if (!emp) return false;
                    if ('resigned' in emp) {
                        var v = emp.resigned;
                        return v === 1 || v === '1' || v === true || v === 'true';
                    }
                    if ('is_resigned' in emp) {
                        var v2 = emp.is_resigned;
                        return v2 === 1 || v2 === '1' || v2 === true || v2 === 'true';
                    }
                    if ('is_active' in emp) {
                        var a = emp.is_active;
                        return a === 0 || a === '0' || a === false || a === 'false';
                    }
                    if ('status' in emp) {
                        var s = String(emp.status).toLowerCase();
                        return s === 'resigned' || s === 'left' || s === 'inactive' || s === 'terminated';
                    }
                    // fallback: assume active if we can't detect a resigned flag
                    return false;
                }

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

                            // destroy previous custom-select instance if present
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

                            // populate options with only non-resigned employees
                            $empSelect.empty();
                            $empSelect.append($('<option>', {
                                value: '',
                                text: 'Select Employee'
                            }));

                            var added = 0;
                            for (var j = 0; j < result.employee.length; j++) {
                                var emp = result.employee[j];
                                if (!isResigned(emp)) {
                                    $empSelect.append($('<option>', {
                                        value: emp.id,
                                        text: emp.name
                                    }));
                                    added++;
                                }
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

                            // Restore previous selection if still present and not resigned
                            if (prevVal && $empSelect.find('option[value="' + prevVal + '"]').length) {
                                $empSelect.val(prevVal);
                                if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0]
                                    .customSelectInstance.refresh === 'function') {
                                    $empSelect[0].customSelectInstance.refresh();
                                }
                            }
                        } else {
                            console.warn('branchemployees returned status:', result.status);
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('AJAX error in branchemployees:', err);
                    }
                });
            }
        </script>

    @endsection
