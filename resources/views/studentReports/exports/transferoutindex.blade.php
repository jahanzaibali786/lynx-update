@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Transfer Out Reports') }}
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script>
        // function generatePDF() {
        //     console.log('generating');
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'transferoutreport.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).save();
        // }

        // function printPDF() {
        //     console.log('printing');
        //     const element = document.getElementById('report-content');
        //     const opt = {
        //         filename: 'transferoutreport.pdf',
        //         html2canvas: {
        //             scale: 1
        //         },
        //         jsPDF: {
        //             unit: 'pt',
        //             format: [700, 1000],
        //             orientation: 'landscape'
        //         }
        //     };
        //     html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        //         window.open(pdf);
        //     });
        // }

        function generatePDF() {
            var form = document.getElementById('transferout_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('transferout.report') }}?" + queryString,
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
        function exportExcel() {
            var form = document.getElementById('transferout_submit');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            var queryString = new URLSearchParams(formData).toString();
            
            window.location.href = "{{ route('transferout.report') }}?" + queryString;
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Transfer Out Report') }}</li>
@endsection

@section('content')
    {{-- <div class="my-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-6">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div> --}}

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['transferout.index'], 'method' => 'GET', 'id' => 'transferout_submit']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_from', __('From Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_from', isset($_GET['date_from']) ? $_GET['date_from'] : '', ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('date_to', __('To Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('date_to', isset($_GET['date_to']) ? $_GET['date_to'] : '', ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                            {{ Form::select(
                                                'type',
                                                ['' => 'Select Type', 'inter city' => 'Inter City', 'inter branch' => 'Inter banch'],
                                                isset($_GET['type']) ? $_GET['type'] : '',
                                                ['class' => 'form-control select'],
                                            ) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('transferout_submit').submit(); return false;"
                                     data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('admissionwithdrawal.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                class="btn-inner--icon">Print</span>
                        </a> --}}
                                <a href="#" onclick="generatePDF(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-success"  title=""
                                    title="Print">
                                    <span class="btn-inner--icon">Print
                                    </span>
                                </a>
                                <a href="#" onclick="exportExcel(); return false;" class="btn mx-1 btn-sm btn-outline-success"
                                    data-bs-title="Export">Export</a>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content" id="report-content">
        <div class="card mt-2 p-4">
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
                <p style ="text-align:center; font-weight:900; font-size:1rem;"> Transfer Out Report</p>
                <p><b>Period To: </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
            <table class="datatable">
                <thead>
                <tr class="table_heads">
                    <th colspan="6"></th>
                    <th colspan="2">{{ __('Transfer From') }}</th>
                    <th colspan="2">{{ __('Transfer To') }}</th>
                </tr>

                <tr class="table_heads">
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Transfer Date') }}</th>
                    <th>{{ __('Reg No') }}</th>
                    <th>{{ __('Roll No') }}</th>
                    <th>{{ __('Student Name') }}</th>
                    <th>{{ __('Father Name') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Reason') }}</th>
                </tr>
                </thead>
                @foreach ($studenttransfer as $transfer)
                    <tr>
                        <td>{{ !empty($transfer->transfer_type) ? $transfer->transfer_type : '-' }}</td>
                        <td>{{ !empty($transfer->transfer_date) ? @$transfer->transfer_date : '-' }}</td>
                        <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->regId : '-' }}</td>
                        <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->enrollId : '-' }}</td>

                        <td>{{ !empty($transfer->student) ? @$transfer->student->stdname : '-' }}</td>
                        <td>{{ !empty($transfer->student) ? @$transfer->student->fathername : '-' }}</td>
                        <td>{{ !empty($transfer->branchfrom) ? @$transfer->branchfrom->name : '-' }}</td>
                        <td>{{ !empty($transfer->classfrom) ? @$transfer->classfrom->name : '-' }}</td>
                        <td>{{ !empty($transfer->branchto) ? @$transfer->branchto->name : '-' }}</td>
                        <td>{{ !empty($transfer->classto) ? @$transfer->classto->name : '-' }}</td>
                        <td>0</td>
                        <td>{{ !empty($transfer->status) ? $transfer->status : '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
