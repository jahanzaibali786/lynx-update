@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Sec. Deduction') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Emp. Monthly Security Deduction Report') }}</li>
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        // function generatePDF() {
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'employee_monthly_sec_deduction_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: 'a4',
        //             orientation: 'portrait'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }
        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'employee_monthly_sec_deduction_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: 'a4',
        //             orientation: 'portrait'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
        //         window.open(pdf);
        //     });
        // }

        function printDompdfPDF() {
            var form = document.getElementById('monthlysecurtiyded');
            var formData = new FormData(form);
            var params = new URLSearchParams(formData);
            params.set('print', 'pdf');
            var url = form.action + '?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
@endpush
@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['monthlysecurtiyded'], 'method' => 'GET', 'id' => 'monthlysecurtiyded']) }}
                            <div class="row d-flex justify-content-end">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        {{ Form::select(
                                            'month',
                                            [
                                                '01' => 'January',
                                                '02' => 'February',
                                                '03' => 'March',
                                                '04' => 'April',
                                                '05' => 'May',
                                                '06' => 'June',
                                                '07' => 'July',
                                                '08' => 'August',
                                                '09' => 'September',
                                                '10' => 'October',
                                                '11' => 'November',
                                                '12' => 'December',
                                            ],
                                            request('month', date('m')),
                                            ['class' => 'form-control select'],
                                        ) }}

                                    </div>
                                </div>
                                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                        {{ Form::select('year', array_combine(range(date('Y') - 25, date('Y') + 1), range(date('Y') - 25, date('Y') + 1)), date('Y'), ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('monthlysecurtiyded').submit(); return false;"
                                         data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('monthlysecurtiyded') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    {{-- @endif --}}
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Employee Security Deduction Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>
            <p style="font-weight:600; font-size:0.8rem;">Employee Security Deduction Report for the month of<span> <b>
                            &nbsp;
                            <u>{{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }}-{{ \Carbon\Carbon::createFromFormat('Y', $year)->format('Y') }}</u></b></span>
                </p>

                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Emp Code</th>
                            <th>Employee Name</th>
                            <th>Father Name</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $grandTotal = 0;
                    @endphp

                    @foreach ($reportData as $branchName => $employees)
                        <tr>
                            <th colspan="5">{{ $branchName }}</th>
                        </tr>
                        @foreach ($employees as $index => $employee)
                        {{-- @dd($employee) --}}
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->employee->name }}</td>
                                <td>{{ $employee->employee->f_name }}</td>
                                <td style="text-align:right;">{{ number_format($employee->emp_sec, 2) }}</td>
                            </tr>
                            @php
                                $grandTotal += $employee->emp_sec;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="4" style="text-align:right;"><b>Branch Total</b></td>
                            <td style="text-align:right;">
                                <b>{{ number_format($employees->sum('emp_sec'), 2) }}</b>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" style="text-align:right;"><b>Grand Total</b></td>
                        <td style="text-align:right;">
                            <b>{{ number_format($grandTotal, 2) }}</b>
                        </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
