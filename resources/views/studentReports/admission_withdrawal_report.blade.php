@extends('layouts.admin')
@section('page-title')
{{__('Manage Admission & Withdrawal Structure')}}
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        function generatePDF() {
        var form = document.getElementById('admissionwithdrawal_submit');
        var formData = new FormData(form);
        var queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: "{{ route('admissionwithdrawalPdf.report') }}?" + queryString,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
        } 
    </script>
        <script>
        function exportToExcel() {
            var form = document.getElementById('admissionwithdrawal_submit');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            var queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('admissionwithdrawal.index') }}?" + queryString + "&export=excel";
        }
    </script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Admission & Withdrawal Report')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(['route' => ['admissionwithdrawal.index'], 'method' => 'GET', 'id' => 'admissionwithdrawal_submit']) }}
                    <div class="row align-items-center justify-content-end ">
                        <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                            <div class="row d-flex justify-content-end ">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('date_from', __('From Date'), ['class' => 'form-label'])}}
                                        {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : '', array('class' => 'form-control ')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('date_to', __('To Date'), ['class' => 'form-label'])}}
                                        {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : '', array('class' => 'form-control ')) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label'])}}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select custom-select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('type', __('Type'), ['class' => 'form-label'])}}
                                        {{ Form::select('type', ['' => 'Select Type','Admissions' => 'Admissions', 'Withdrawal' => 'Withdrawal'], isset($_GET['type']) ? $_GET['type'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                                class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <!-- Search Button -->
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('admissionwithdrawal_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('admissionwithdrawal.index') }}"
                                    class="btn mx-1 btn-sm btn-outline-danger" data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <form method="post" style="display: inline;">
                                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                                    <i class="ti ti-file me-2"></i>Excel
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="get" style="display: inline;">
                                                @csrf
                                                @method('GET')
                                                <input type="hidden" name="export" value="pdf">
                                                <button class="dropdown-item" type="submit" name="print" value="pdf">
                                                    <i class="ti ti-download me-2"></i>Pdf
                                                </button>
                                            </form>
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

{{--<div class="my-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-3">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div>--}}
<div class="content" id="report-content">
    
        <div class="card p-4">
            <div style="width: 100%; text-align: center;">
                <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b>
                </p>
                <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
                <p style="text-align:center; font-weight:600; font-size:1rem;">
                    {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
            </div>
            {{-- <p style="text-align:center; font-weight:900; font-size:1rem;">Transfer Out Report</p> --}}
            <div class="" style="width: 100%; display: flex; justify-content: space-between;">
                <p><b>Period From: </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
                <p style ="text-align:center; font-weight:900; font-size:1rem;"> Admission and Withdrawal Report</p>
                <p><b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
        <div class="table-responsive maximumHeightNew">
        <table class="datatable">
            <thead class="table_heads sticky-headerNew">
            <tr class="table_heads">
                <th rowspan="2">Sr#</th>
                <th rowspan="2">Roll#</th>
                <th rowspan="2">Name of Student</th>
                <th rowspan="2">Date of Birth</th>
                <th rowspan="2">Father Name</th>
                <th rowspan="2">Occupation</th>
                <th rowspan="2">Address</th>
                <th rowspan="2">Phone#</th>
                <th colspan="4" style="background:#d3d3d3; text-align:center;">ADMISSION BRANCH</th>
                <th colspan="4" style="background:#d3d3d3; text-align:center;">WITHDRAWAL BRANCH</th>
                <th rowspan="2">Receivable</th>
                <th rowspan="2">Payable</th>
                <th rowspan="2">Reason Of Leaving</th>
            </tr>
            <tr class="table_heads">
                <th>Session</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Class</th>
                <th>Session</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Class</th>
            </tr>
            </thead>
            <tbody>
                @php 
                    $totalReceivable = 0;
                    $totalPayable = 0;
                @endphp
                
                @foreach ($all_data as $data)
                    @php
                        $student = $data->StudentRegistration;
                        $withdrawal = $data->withdrawal;
                        $receivable = $withdrawal->receivable ?? 0;
                        $payable = $withdrawal->payable ?? 0;
                        
                        $totalReceivable += $receivable;
                        $totalPayable += $payable;
                    @endphp
                    
                    <tr class="trNew">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $student->roll_no ?? $student->enrollId ?? '-' }}</td>
                        <td>{{ $student->stdname ?? '-' }}</td>
                        <td>{{ $student->dob ?? '-' }}</td>
                        <td>{{ $student->fathername ?? '-' }}</td>
                        <td>{{ $student->fatherprofession ?? '-' }}</td>
                        <td>{{ $student->address ?? '-' }}</td>
                        <td>{{ $student->fatherphone ?? '-' }}</td>
                        <!-- Admission Branch -->
                        <td>{{ $student->session->year ?? $data->adm_session ?? '' }}</td>
                        <td>{{ $student->branches->name ?? $student->branch_name->name ?? $data->adm_branch ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d-M-Y') }}</td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <!-- Withdrawal Branch -->
                        <td>{{ $withdrawal->session->year ?? '' }}</td>
                        <td>{{ $withdrawal->branch->name ?? '' }}</td>
                        <td>{{ $withdrawal->withdraw_date ?? '-' }}</td>
                        <td>{{ $withdrawal->class->name ?? '-' }}</td>
                        <!-- Receivable and Payable -->
                        <td style="text-align:right;">{{ number_format($receivable, 0) }}</td>
                        <td style="text-align:right;">{{ number_format($payable, 0) }}</td>
                        <td>{{ $withdrawal->reason ?? $withdrawal->remark ?? '-' }}</td>
                    </tr>
                @endforeach
                
                <!-- Grand Total -->
                <tr class="trNew" style="background:#e9e7e7; font-weight:bold;">
                    <td colspan="16" style="text-align:right; padding-right:10px;">TOTAL</td>
                    <td style="text-align:right;">{{ number_format($totalReceivable, 0) }}</td>
                    <td style="text-align:right;">{{ number_format($totalPayable, 0) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
