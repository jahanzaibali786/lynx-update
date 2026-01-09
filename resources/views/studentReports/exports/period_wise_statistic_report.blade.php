@extends('layouts.admin')
@section('page-title')
{{__('Period Wise Statistic Report')}}
@endsection

@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
function generatePDF() {
    const element = document.getElementById('studentfeereceipt');
    const opt = {
        filename: 'period_wise_statistic_report.pdf',
        html2canvas: { scale: 1 },
        jsPDF: { unit: 'pt', format: [700, 900], orientation: 'portrait' }
    };
    html2pdf().from(element).set(opt).save();
}

function printPDF() {
    const element = document.getElementById('studentfeereceipt');
    const opt = {
        filename: 'period_wise_statistic_report.pdf',
        html2canvas: { scale: 1 },
        jsPDF: { unit: 'pt', format: [700, 900], orientation: 'portrait' }
    };
    html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        window.open(pdf);
    });
}
</script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Period Wise Statistic Report')}}</li>
@endsection

@section('action-btn')
<div class="float-end"></div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2">
            <div class="card">
                <div class="card-body" style="padding: 12px;">
                    {{ Form::open(['route' => ['period_wise_statistic_report'], 'method' => 'GET', 'id' => 'period_wise_statistic_report']) }}
                    <div class="row d-flex justify-content-start">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                {{ Form::date('from_date', $fromDate, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                {{ Form::date('to_date', $toDate, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('period_wise_statistic_report').submit(); return false;"
                                 data-bs-title="Search">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"  data-bs-title="Print"><span
                                    class="btn-inner--icon">Print</span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card mt-2 p-4" id="studentfeereceipt">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School 
                    </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">PWD BRANCH ISLAMABAD</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Period Wise Statistic Report</p>
        </div>
    </div>
    <table class="datatable">
        <thead>
            <tr class="table_heads report_table">
                <th>Month Year</th>
                <th>OB</th>
                <th>Admissions</th>
                <th>Withdrawals</th>
                <th>Transfers IN</th>
                <th>Transfers OUT</th>
                <th>Passing Out</th>
                <th>Closing</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAdmissions = 0;
                $totalWithdrawals = 0;
                $totalTransfersIn = 0;
                $totalTransfersOut = 0;
                $totalPassingOut = 0;
                $totalClosing = 0;
            @endphp
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['month_year'] }}</td>
                    <td>{{ 0 }}</td>
                    <td>{{ $row['admissions'] }}</td>
                    <td>{{ $row['withdrawals'] }}</td>
                    <td>{{ $row['transfers_in'] }}</td>
                    <td>{{ $row['transfers_out'] }}</td>
                    <td>{{ $row['passing_out'] }}</td>
                    {{--<td>{{ $row['closing'] }}</td>--}}
                </tr>
                @php
                    $totalAdmissions += $row['admissions'];
                    $totalWithdrawals += $row['withdrawals'];
                    $totalTransfersIn += $row['transfers_in'];
                    $totalTransfersOut += $row['transfers_out'];
                    $totalPassingOut += $row['passing_out'];
                   // $totalClosing += $row['closing'];
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>{{ 0 }}</th> 
                <th>{{ $totalAdmissions }}</th>
                <th>{{ $totalWithdrawals }}</th>
                <th>{{ $totalTransfersIn }}</th>
                <th>{{ $totalTransfersOut }}</th>
                <th>{{ $totalPassingOut }}</th>
                {{--<th>{{ $totalClosing }}</th>--}}
            </tr>
        </tfoot>
    </table>
</div>
@endsection
