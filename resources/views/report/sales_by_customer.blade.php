@extends('layouts.admin')
@section('page-title')
    {{ __('Sales By Customer') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Sales By Customer') }}</li>
@endsection
{{-- @section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-title="{{__('Download')}}"
           data-bs-title="{{__('Download')}}">
            <span class="btn-inner--icon">Pdf / Print</span>
        </a>
    </div>
@endsection --}}
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['sales_by_customer'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control month-btn']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control month-btn']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('store', __('Customer'), ['class' => 'form-label']) }}
                                    {{ Form::select('store', $store, isset($_GET['store']) ? $_GET['store'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('customer_submit').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('sales_by_customer') }}" class="btn btn-sm btn-danger"
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
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Sales By Customer Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['start_date'])
                    Start Date: {{ date('d M Y', strtotime($_GET['start_date'])) }}
                @endisset
                @isset($_GET['end_date'])
                    End Date: {{ date('d M Y', strtotime($_GET['end_date'])) }}
                @endisset
            </p>
            <div class="table-responsive">
                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>Customer</th>
                            <th>Invoice No</th>
                            <th>Invoice Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $customer_id => $customer_invoices)
                            
                            {{-- <tr class="branch-header" style="background-color:#bcbcbc;">
                                <td colspan="4" style="font-weight: bold;">
                                    {{ $branches[$customer_id] ?? 'Unknown Customer' }}
                                </td>
                            </tr> --}}
                            @foreach ($customer_invoices as $invoice)
                                <tr>
                                    <td>{{ $branches[$customer_id] ?? 'Unknown Customer' }}</td>
                                    <td>{{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}</td>
                                    <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                    <td>{{ \Auth::user()->priceFormat($invoice->getTotal()) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
{{-- @push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'Sales_By_Customer_Report.pdf',
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
    </script>
@endpush --}}
