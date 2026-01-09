@extends('layouts.admin')

@section('page-title')
    {{ __('Period Wise Payroll') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Period Wise Payroll') }}</li>
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function openPrintPdf() {
            var form = document.getElementById('periodwisepayroll');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();
            var url = "{{ route('periodwisepayroll.report') }}?" + queryString + "&print=pdf";
            window.open(url, '_blank');
        }

        function openExportPdf() {
            var form = document.getElementById('periodwisepayroll');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();
            var url = "{{ route('periodwisepayroll') }}?" + queryString + "&export=excel";
            window.open(url, '_blank');
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
        data: { id: id },
        dataType: 'json',
        success: function(result) {
            if (result.status === 'success') {
                var $empSelect = $('#employee_id');

                // destroy previous custom-select instance if present
                if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                    try { $empSelect[0].customSelectInstance.destroy(); } catch (e) {}
                    delete $empSelect[0].customSelectInstance;
                }
                if ($empSelect.next('.custom-select-wrapper').length) {
                    $empSelect.next('.custom-select-wrapper').remove();
                }
                $empSelect.removeClass('custom-select');

                // populate options with only non-resigned employees
                $empSelect.empty();
                $empSelect.append($('<option>', { value: '', text: 'Select Employee' }));

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
                    $empSelect.append($('<option>', { value: '', text: 'No active employees', disabled: true }));
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
                    if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0].customSelectInstance.refresh === 'function') {
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
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['periodwisepayroll'], 'method' => 'GET', 'id' => 'periodwisepayroll']) }}
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
                                        {{ Form::select('employee_id', [''] + $employees->toArray(), request()->input('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'employee_id', 'placeholder' => __('All Employees')]) }}
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
                                        onclick="document.getElementById('periodwisepayroll').submit(); return false;"
                                        data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('periodwisepayroll') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    @endif

    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px">
                <b>{{ !empty($employee_id) ? 'Period Wise Employee Payroll' : 'Period Wise Payroll' }}</b>
            </p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
                @if(!empty($employee_id))
                    <br><b>Employee:</b> {{ $employees[$employee_id] ?? 'Unknown Employee' }}
                @endif
            </p>
            <div style="display:flex; justify-content:space-between;">
                <p><b>Date From :</b> {{ request()->input('datefrom') }}</p>
                <p><b>Date To : </b>{{ request()->input('dateto') }}</p>
            </div>
                @php
                    $totalb = [];
                @endphp
                <div class="table-responsive">
                    <table class="datatable">
                        <thead>
                            <tr class="table_heads">
                                <th>Sr. No.</th>
                                <th>Period</th>
                                @if(empty($employee_id))
                                    <th>Total Of Emp.</th>
                                @endif
                                @foreach ($salaryHeads as $salaryhead)
                                    <th>{{ $salaryhead->head }}</th>
                                @endforeach
                                <th>Gross</th>
                                <th>Employer Contribution E.O.B.I</th>
                                <th>Employer Contribution P.E.S.S.I</th>
                                <th>Employee Security</th>
                                <th>Employee Contribution E.O.B.I</th>
                                <th>Employee Contribution P.E.S.S.I</th>
                                <th>Income Tax</th>
                                <th>Other Deduction</th>
                                <th>Net Payable</th>
                                <th>Child Concession</th>
                                <th>Cost to Company</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $branchTotalByHead = array_fill_keys($salaryHeads->pluck('id')->toArray(), 0);
                            @endphp
                            @foreach ($branchTotals as $branchId => $monthsData)
                                @if(empty($employee_id))
                                    <tr>
                                        <td colspan="{{ 15 + count($salaryHeads) }}"><b>{{ \Auth::user()->getBranch($branchId)->name }}</b></td>
                                    </tr>
                                @endif
                                @foreach ($monthsData as $month => $data)
                                    @php
                                        $branchTotal = [
                                            'total_employees' => 0,
                                            'gross' => 0,
                                            'employer_contribution_eobi' => 0,
                                            'employer_contribution_pessi' => 0,
                                            'employee_security' => 0,
                                            'employee_contribution_eobi' => 0,
                                            'employee_contribution_pessi' => 0,
                                            'income_tax' => 0,
                                            'other_deduction' => 0,
                                            'net_payable' => 0,
                                            'child_concession' => 0,
                                            'cost_to_company' => 0,
                                        ];
                                        foreach ($monthsData as $data) {
                                            $branchTotal['total_employees'] += $data['total_employees'];
                                            $branchTotal['gross'] += $data['gross'];
                                            $branchTotal['employer_contribution_eobi'] +=
                                                $data['employer_contribution_eobi'];
                                            $branchTotal['employer_contribution_pessi'] +=
                                                $data['employer_contribution_pessi'];
                                            $branchTotal['employee_security'] += $data['employee_security'];
                                            $branchTotal['employee_contribution_eobi'] +=
                                                $data['employee_contribution_eobi'];
                                            $branchTotal['employee_contribution_pessi'] +=
                                                $data['employee_contribution_pessi'];
                                            $branchTotal['income_tax'] += $data['income_tax'];
                                            $branchTotal['other_deduction'] += $data['other_deduction'];
                                            $branchTotal['net_payable'] += $data['net_payable'];
                                            $branchTotal['child_concession'] += $data['child_concession'];
                                            $branchTotal['cost_to_company'] += $data['cost_to_company'];
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $month }}</td>
                                        @if(empty($employee_id))
                                            <td>{{ $data['total_employees'] }}</td>
                                        @endif
                                        @foreach ($salaryHeads as $head)
                                            @php
                                                $amount = $totalByHead[$month][$head->id] ?? 0;
                                                $branchTotalByHead[$head->id] += $amount;
                                            @endphp
                                            <td>{{ $amount }}</td>
                                        @endforeach
                                        <td>{{ $data['gross'] }}</td>
                                        <td>{{ $data['employer_contribution_eobi'] }}</td>
                                        <td>{{ $data['employer_contribution_pessi'] }}</td>
                                        <td>{{ $data['employee_security'] }}</td>
                                        <td>{{ $data['employee_contribution_eobi'] }}</td>
                                        <td>{{ $data['employee_contribution_pessi'] }}</td>
                                        <td>{{ $data['income_tax'] }}</td>
                                        <td>{{ $data['other_deduction'] }}</td>
                                        <td>{{ $data['net_payable'] }}</td>
                                        <td>{{ $data['child_concession'] }}</td>
                                        <td>{{ $data['cost_to_company'] }}</td>
                                    </tr>
                                @endforeach

                                @if(empty($employee_id))
                                    <tr>
                                        <td colspan="2"><strong>Branch Total</strong></td>
                                        <td><b>{{ $branchTotal['total_employees'] }}</b></td>
                                        @foreach ($salaryHeads as $head)
                                            <td><b>{{ $branchTotalByHead[$head->id] ?? 0 }}</b></td>
                                        @endforeach
                                        <td><b>{{ $branchTotal['gross'] }}</b></td>
                                        <td><b>{{ $branchTotal['employer_contribution_eobi'] }}</b></td>
                                        <td><b>{{ $branchTotal['employer_contribution_pessi'] }}</b></td>
                                        <td><b>{{ $branchTotal['employee_security'] }}</b></td>
                                        <td><b>{{ $branchTotal['employee_contribution_eobi'] }}</b></td>
                                        <td><b>{{ $branchTotal['employee_contribution_pessi'] }}</b></td>
                                        <td><b>{{ $branchTotal['income_tax'] }}</b></td>
                                        <td><b>{{ $branchTotal['other_deduction'] }}</b></td>
                                        <td><b>{{ $branchTotal['net_payable'] }}</b></td>
                                        <td><b>{{ $branchTotal['child_concession'] }}</b></td>
                                        <td><b>{{ $branchTotal['cost_to_company'] }}</b></td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr style="">
                                <td colspan="2"><strong>{{ !empty($employee_id) ? 'Employee Total' : 'Grand Total' }}</strong></td>
                                @if(empty($employee_id))
                                    <td><b>{{ $grandTotal['total_employees'] ?? 0 }}</b></td>
                                @endif
                                @foreach ($salaryHeads as $head)
                                    <td><b>{{ $grandTotal[$head->id] ?? 0 }}</b></td>
                                @endforeach
                                <td><b>{{ $grandTotal['gross'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['employer_contribution_eobi'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['employer_contribution_pessi'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['employee_security'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['employee_contribution_eobi'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['employee_contribution_pessi'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['income_tax'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['other_deduction'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['net_payable'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['child_concession'] ?? 0 }}</b></td>
                                <td><b>{{ $grandTotal['cost_to_company'] ?? 0 }}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    @endsection
