@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Vendor Summary Report') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'Purchase_Vendor_Summary_Report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'Purchase_Vendor_Summary_Report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }
    </script>
    <script>
        function branchstore(id) {
            var branch = id;
            $.ajax({
                url: '{{ route('branch.store') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#store').empty();
                    // $('#store').append('<option value="">{{ __('Select Store') }}</option>');

                    for (let index = 0; index < data.length; index++) {
                        $('#store').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +
                            '</option>');
                    }
                }
            });
        }

        document.getElementById('branchstore').addEventListener('change', function() {
            var id = this.value;
            branchstore(id);
        });

        //     function printReport() {
        //         // alert('student')
        //     let form = document.getElementById('customer_submit');
        //     let formData = new FormData(form);
        //     let queryString = new URLSearchParams(formData).toString();
        //     window.location.href = "{{ route('purchase_pdf.report') }}?" + queryString;
        // }
        function printReport() {
            var form = document.getElementById('customer_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('purchase_pdf.report') }}?" + queryString,
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
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Vendor Summary Report') }}</li>
@endsection
{{-- @section('action-btn')
<div class="float-end">
    <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
        class="btn-inner--icon">Print</span>
</a>     
</div>
@endsection --}}

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['purchase_vendor_summary'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            {{-- <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label'])}}
                                {{ Form::date('issue_date', isset($_GET['issue_date'])?$_GET['issue_date']:'', array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}
                            </div>
                        </div> --}}
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}

                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : @$dateFrom, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] :  @$dateTo, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('vender', __('Vendors'), ['class' => 'form-label']) }}
                                    {{ Form::select('vender', $vender, isset($_GET['vender']) ? $_GET['vender'] : '', ['class' => 'form-control select', 'onchange' => 'branchstore(this.value)']) }}
                                </div>
                            </div>
                            {{-- <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('store', __('Store'),['class'=>'form-label'])}}
                                {{ Form::select('store', $class ?? [], isset($_GET['store']) ? $_GET['store'] : '', ['class' => 'form-control select', 'id' => 'store']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('store_to', __('Store To'),['class'=>'form-label'])}}
                                {{ Form::select('store_to', $store_to ?? [], isset($_GET['store_to']) ? $_GET['store_to'] : '', ['class' => 'form-control select', 'id' => 'store_to']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'),['class'=>'form-label'])}}
                                {{ Form::select('status', [''=>'Select Status'] + $status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}
                            </div>
                        </div> --}}
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('customer_submit').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('invoice.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    <div class="content" id="report-content">
        <div class="card p-4 mt-3">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center; line-height:2.2rem;"><b>The
                    Lynx School <br><span style="font-family: arial; font-weight:600; font-size:1rem;"> </span> </b></p>
            {{-- <p style="text-align: center; font-weight: 600; font-size: 1rem;">PWD BRANCH ISLAMABAD</p> --}}
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1rem;">
                    Start Date: {{ date('d M Y', strtotime($request->input('start_date'))) }}
                </span>
                <p style="text-align: center; font-weight: 900; font-size: 1rem; margin: 0;">Purchaes By Vendor Summary</p>
                <span style="font-size: 1rem;">
                    End Date: {{ date('d M Y', strtotime($request->input('end_date'))) }}
                </span>
            </div>
            <table class="datatable">
                <thead class="table_heads">
                    <tr>
                        <th>Vendor</th>
                        <th>Total Purchase</th>
                        <th>Total Due</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vendorSummary as $summary)
                        <tr>
                            <td>{{ $summary['vendor_name'] }}</td>
                            <td>{{ number_format($summary['total_purchase'], 2) }}</td>
                            <td>{{ number_format($summary['total_due'], 2) }}</td>
                            <td>{{ number_format($summary['total_balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
