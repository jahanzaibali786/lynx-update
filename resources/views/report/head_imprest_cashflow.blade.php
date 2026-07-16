@extends('layouts.admin')
@section('page-title')
    {{__('Head Imprest Cash Flow Statement')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Head Imprest Report')}}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: 'Head_Imprest_Cashflow_Report_' + '{{ $branchName }}' + '_' + '{{ $fromDate }}' + '_to_' + '{{ $toDate }}',
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A3', orientation: 'portrait'}
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@push('css-page')
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printableArea, #printableArea * {
        visibility: visible !important;
    }
    #printableArea {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
@endpush

@section('action-btn')
    <div class="float-end d-flex gap-2">
        <a href="#" class="btn btn-sm btn-primary m-0" onclick="window.print()">
            <span class="btn-inner--icon"><i class="ti ti-printer me-1"></i>{{__('Print / Save PDF')}}</span>
        </a>
        <a href="#" class="btn btn-sm btn-outline-secondary m-0" onclick="saveAsPDF()">
            <span class="btn-inner--icon"><i class="ti ti-download me-1"></i>{{__('Export PDF')}}</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12 mb-4">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.head-imprest.cashflow'], 'method' => 'GET', 'id' => 'head_imprest_report_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('from_date', __('Start of Period'), ['class' => 'form-label']) }}
                                        {{ Form::date('from_date', $fromDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('to_date', __('End of Period'), ['class' => 'form-label']) }}
                                        {{ Form::date('to_date', $toDate, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::select('branch_id', $branches, $selectedBranchId, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto d-flex gap-1">
                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('head_imprest_report_filter').submit(); return false;">
                                        <span class="btn-inner--icon">{{ __('Search') }}</span>
                                    </a>
                                    <a href="{{ route('report.head-imprest.cashflow') }}" class="btn btn-sm btn-outline-danger">
                                        <span class="btn-inner--icon">{{ __('Clear') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card" id="printableArea" style="background-color: #fff; color: #000; padding: 25px; border-radius: 8px;">
                <div class="card-header border-0 pb-0 text-center">
                    <h2 class="mb-1 text-dark fw-bold">{{ \Auth::user()->name }}</h2>
                    <h4 class="text-uppercase text-muted mb-2">{{ __('Monthly Cash Flow Statement') }}</h4>
                    <p class="mb-1 fw-bold text-secondary">
                        {{ __('Branch') }}: <span class="text-dark">{{ $branchName }}</span>
                    </p>
                    <p class="mb-4 text-muted">
                        {{ __('Start of Period') }}: <span class="text-dark fw-bold">{{ date('d M Y', strtotime($fromDate)) }}</span> &nbsp;|&nbsp;
                        {{ __('End of Period') }}: <span class="text-dark fw-bold">{{ date('d M Y', strtotime($toDate)) }}</span>
                    </p>

                    <div class="row justify-content-center mb-4">
                        <div class="col-md-5">
                            <div class="p-3 border rounded bg-light-primary text-center">
                                <h6 class="text-uppercase text-secondary mb-1">{{ __('Beginning Cash Balance') }}</h6>
                                <h4 class="text-dark fw-bold mb-0">{{ \Auth::user()->priceFormat($beginningBalance) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 border rounded bg-light-success text-center">
                                <h6 class="text-uppercase text-secondary mb-1">{{ __('Ending Cash Balance') }}</h6>
                                <h4 class="text-success fw-bold mb-0">{{ \Auth::user()->priceFormat($endingBalance) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- INFLOWS TABLE -->
                    <div class="mb-5">
                        <h5 class="text-uppercase fw-bold text-primary mb-3" style="border-bottom: 2px solid #5c636a; padding-bottom: 5px;">
                            {{ __('Inflows') }}
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th width="15%">{{ __('Date') }}</th>
                                        <th width="20%">{{ __('Cheque / Payment Mode') }}</th>
                                        <th width="35%">{{ __('Narration') }}</th>
                                        <th width="15%" class="text-end">{{ __('Rs.') }}</th>
                                        <th width="15%" class="text-end">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-light fw-bold text-dark">
                                        <td class="text-center">{{ date('d M Y', strtotime($fromDate)) }}</td>
                                        <td class="text-center">-</td>
                                        <td>{{ __('Beginning Cash Balance') }}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">{{ number_format($beginningBalance, 2) }}</td>
                                    </tr>
                                    @foreach($inflowItems as $inflow)
                                        <tr>
                                            <td class="text-center">{{ date('d M Y', strtotime($inflow['date'])) }}</td>
                                            <td class="text-center">{{ $inflow['mode'] }}</td>
                                            <td>{{ $inflow['narration'] }}</td>
                                            <td class="text-end">{{ number_format($inflow['amount'], 2) }}</td>
                                            <td class="text-end">-</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-info fw-bold">
                                        <td colspan="3" class="text-uppercase">{{ __('Total Inflows') }}</td>
                                        <td class="text-end">{{ number_format($totalInflows, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalInflows, 2) }}</td>
                                    </tr>
                                    <tr class="table-success fw-bold">
                                        <td colspan="4" class="text-uppercase">{{ __('Total Cash Available') }}</td>
                                        <td class="text-end">{{ number_format($beginningBalance + $totalInflows, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- OUTFLOWS TABLE -->
                    <div class="mb-5">
                        <h5 class="text-uppercase fw-bold text-danger mb-3" style="border-bottom: 2px solid #5c636a; padding-bottom: 5px;">
                            {{ __('Outflows') }}
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th width="30%">{{ __('Account Head') }}</th>
                                        <th width="20%">{{ __('Period') }}</th>
                                        <th width="15%" class="text-end">{{ __('Rs.') }}</th>
                                        <th width="15%" class="text-end">{{ __('Total') }}</th>
                                        <th width="10%" class="text-end">{{ __('Average') }}</th>
                                        <th width="10%" class="text-end">{{ __('Percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outflows as $outflow)
                                        <tr>
                                            <td rowspan="2" class="align-middle fw-bold text-dark">{{ $outflow['account_name'] }}</td>
                                            <td class="text-muted"><i class="ti ti-paperclip"></i> {{ __('1-15 summary attached') }}</td>
                                            <td class="text-end">{{ number_format($outflow['first_half'], 2) }}</td>
                                            <td rowspan="2" class="align-middle text-end fw-bold text-dark">{{ number_format($outflow['total'], 2) }}</td>
                                            <td rowspan="2" class="align-middle text-end">{{ number_format($outflow['average'], 2) }}</td>
                                            <td rowspan="2" class="align-middle text-end">{{ number_format($outflow['percentage'], 1) }}%</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted"><i class="ti ti-paperclip"></i> {{ __('16-31 summary attached') }}</td>
                                            <td class="text-end">{{ number_format($outflow['second_half'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">{{ __('No outflows recorded in this period') }}</td>
                                        </tr>
                                    @endforelse
                                    <tr class="table-warning fw-bold text-dark">
                                        <td colspan="3" class="text-uppercase">{{ __('Total Outflows') }}</td>
                                        <td class="text-end">{{ number_format($totalOutflows, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalOutflows > 0 ? ($totalOutflows / 25) : 0, 2) }}</td>
                                        <td class="text-end">100.0%</td>
                                    </tr>
                                    <tr class="table-success fw-bold text-dark" style="font-size: 1.1rem;">
                                        <td colspan="3" class="text-uppercase">{{ __('Net Cash Flow') }}</td>
                                        <td class="text-end" colspan="3">{{ number_format($totalInflows - $totalOutflows, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SIGNATURES SECTION -->
                    <div class="row mt-5 pt-4 text-center">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <p class="mb-5 text-muted">{{ __('Prepared By') }}</p>
                            <div class="border-top mx-auto" style="width: 250px; border-color: #000 !important;">
                                <p class="mt-2 fw-bold text-dark">{{ __('Accountant / Branch Head') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-5 text-muted">{{ __('Approved By') }}</p>
                            <div class="border-top mx-auto" style="width: 250px; border-color: #000 !important;">
                                <p class="mt-2 fw-bold text-dark">{{ __('Managing Director / Head Office') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
