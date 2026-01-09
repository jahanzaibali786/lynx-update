@extends('layouts.admin')
@section('page-title')
{{__('Period Wise Statistic Report')}}
@endsection

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
                                {{ Form::select('branches', $branches, $selectedBranch ?? null, ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('period_wise_statistic_report').submit(); return false;"
                                 data-bs-title="Search">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <!-- Actions Dropdown -->
                    <div class="dropdown">
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
                                    <button class="dropdown-item" type="submit" name="print" value="pdf">
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
<div class="card p-4" id="studentfeereceipt">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School 
                    </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Period Wise Statistic Report</p>
        </div>
    </div>
    <table class="datatable maximumHeightNew">
        <thead class="sticky-headerNew">
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
                <tr class="trNew">
                    <td>{{ $row['month_year'] }}</td>
                    <td>{{ 0 }}</td>
                    <td>{{ $row['admissions'] }}</td>
                    <td>{{ $row['withdrawals'] }}</td>
                    <td>{{ $row['transfers_in'] }}</td>
                    <td>{{ $row['transfers_out'] }}</td>
                    <td>{{ $row['passing_out'] }}</td>
                    <td>{{ $row['closing'] }}</td>
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
            <tr class="trNew">
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
