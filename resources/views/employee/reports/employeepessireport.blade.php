@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Pessi Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Emp. pessi Report') }}</li>
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        // function generatePDF() {
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'employee_pessi_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700,1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }
        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'employee_pessi_report.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700,1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
        //         window.open(pdf);
        //     });
        // }
        function generatePDF() {
            var form = document.getElementById('employeepessireport');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('employeepessipdf.report') }}?" + queryString,
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
        function printPdf() {
            const form = document.getElementById('employeepessireport');
            let url = new URL(form.action, window.location.origin);
            let params = new URLSearchParams(new FormData(form));
            params.set('print', 'pdf');
            url.search = params.toString();
            window.open(url.toString(), '_blank');
        }
        function submitWithPrintFlag(type = "pdf") {
            const form = $('#employeepessireport');
            if (type === "pdf")
                $('#is_print').val(1);
            if (type === "excel")
                $('#is_excel').val(1);
            form.attr('target', '_blank').submit().removeAttr('target');
            $('#is_print').val(0);
            $('#is_excel').val(0);
        }
    </script>
@endpush
@section('content')
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['employeepessireport'], 'method' => 'GET', 'id' => 'employeepessireport']) }}
                            <div class="row d-flex justify-content-end">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Date'), ['class' => 'form-label']) }}
                                        <input type="month" class="form-control" name="month"
                                            value="{{ $year }}-{{ $month }}">
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('employeepessireport').submit(); return false;"
                                         data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('employeepessireport') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
        </div>
    @endif
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Employee Pessi Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                    @endif
                </p>
                <p style="font-weight:600; font-size:0.8rem;">pessi Contribution for the Month
                    of<span><b>&nbsp;<u>{{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }}-{{ \Carbon\Carbon::createFromFormat('Y', $year)->format('Y') }}</u></b></span>
                </p>

                <table class="table">
                    <thead class="table_heads">
                    <tr>
                        <th>Sr. No.</th>
                        <th>Emp Code</th>
                        <th>Employee Name</th>
                        <th>Father Name</th>
                        <th>CNIC</th>
                        <th>Designation</th>
                        <th>Monthly Wages</th>
                        <th>Working Days</th>
                        <th>Wages Status</th>
                        <th>Contribution Amnt.</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reportData as $branchName => $employees)
                        <tr>
                            <th colspan="5">{{ $branchName }}</th>
                        </tr>
                        @foreach ($employees as $index => $employee)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->employee->name }}</td>
                                <td>{{ $employee->employee->f_name }}</td>
                                <td>{{ $employee->employee->cnic }}</td>
                                <td>{{ $employee->employee->designation->name }}</td>
                                <td style="text-align:right;">{{ number_format($employee->basics, 2) }}</td>
                                <td style="text-align:right;">{{ $employee->sal_days }}</td>
                                <td style="text-align:right;">Monthly</td>
                                <td style="text-align:right;">{{ $employee->pessi }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endsection
