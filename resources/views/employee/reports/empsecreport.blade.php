@extends('layouts.admin')

@section('page-title')
    {{ __('Emp. Sec. Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Security Report') }}</li>
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            var form = document.getElementById('empsecreport');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('empsecpdf.report') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
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
@endpush

@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['empsecreport'], 'method' => 'GET', 'id' => 'empsecreport']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->input('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_id', $employees, request()->input('employee_id'), ['class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('datefrom', __('Date From'), ['class' => 'form-label']) }}
                                    <input type="month" class="form-control" name="datefrom"
                                        value="{{ request()->input('datefrom') }}">
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('dateto', __('Date To'), ['class' => 'form-label']) }}
                                    <input type="month" class="form-control" name="dateto"
                                        value="{{ request()->input('dateto') }}">
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('empsecreport').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('empsecreport') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Employee Security Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>
            <div class="table-responsive">

                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Employee Name</th>
                            <th>Designation</th>
                            <th>D.O.J</th>
                            <th>Previous Adj.</th>
                            @foreach ($months as $month)
                                <th>{{ \Carbon\Carbon::createFromFormat('M Y', $month)->format('F') }}</th>
                            @endforeach
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotals = array_fill_keys($months, 0);
                        @endphp
                        @foreach ($salaries->groupBy('employee_id') as $employeeId => $employeeSalaries)
                            @php
                                $employee = $employeeSalaries->first()->employee;
                                $employeeTotal = 0;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->designation->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($employee->company_doj)->format('d-m-Y') }}</td>
                                <td>{{ number_format($employeeSalaries->first()->emp_sec ?? 0, 2) }}</td>
                                @php
                                    $employeeTotal = 0;
                                @endphp
                                @foreach ($months as $month)
                                    @php
                                        $monthYear = \Carbon\Carbon::createFromFormat('M Y', $month)->format('Y-m');
                                        $monthlyTotal = $employeeSalaries
                                            ->filter(function ($salary) use ($monthYear) {
                                                $salaryDate = \Carbon\Carbon::parse($salary->salary_date);
                                                return $salaryDate->format('Y-m') == $monthYear;
                                            })
                                            ->sum('emp_sec');
                                        $employeeTotal += $monthlyTotal;
                                        $grandTotals[$month] = isset($grandTotals[$month])
                                            ? $grandTotals[$month] + $monthlyTotal
                                            : $monthlyTotal;
                                        $displayMonth = \Carbon\Carbon::createFromFormat('M Y', $month)->format('F');
                                    @endphp
                                    <td style="text-align:right;">{{ number_format($monthlyTotal, 2) }}</td>
                                @endforeach
                                <td style="text-align:right;"><b>{{ number_format($employeeTotal, 2) }}</b></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
