@extends('layouts.admin')
@section('page-title')
    {{ __('Fee Insight Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fee Insight Report') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['tuition_feereport'], 'method' => 'GET', 'id' => 'sessionWisereport']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('month', request()->get('month') ?? date('Y-m'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('sessionWisereport').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <button  data-bs-title="Export" class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export"
                                    value="excel"><span class="btn-inner--icon">Export</span></button>
                                <button  data-bs-title="Print" class="btn mx-1 btn-sm btn-outline-success" type="submit" name="print"
                                    value="pdf"><span class="btn-inner--icon"><i
                                            class="ti ti-print">Pdf / Print</i></span></button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container card">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School 
                    </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'LYNX NETWORK' }}</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">FEE INSIGHT REPORT FOR THE MONTH OF {{ strtoupper(\Carbon\Carbon::parse(request()->get('month') ?? date('Y-m'))->format('F Y')) }}</p>
        </div>
        {{-- <div class="" style="width:100%">
            <p style="width: 34%; float:left;"><b>Session From: </b>{{ request()->get('session_from') ? $sessions[request()->get('session_from')] : $sessions[$current_session] }}</p>
            <p style="width: 34%; float:left;"></p>
            <p style="width: 30%; float:left; padding-left:100px;"><b>Session To:</b>
                {{ request()->get('session_to') ? $sessions[request()->get('session_to')] : $sessions[$current_session] }}</p>
        </div> --}}

        <!-- Data Display Section -->
        <table class=" mt-4">
            <thead>
                <tr class="table_heads" style="background:gray; text-align:center; ">
                    <th>S.R</th>
                    <th>T.FEE STRUCTURE</th>
                    <th>No of Student</th>
                    <th style=" color:red;">Total</th>
                </tr>
            </thead>
            @php
                $total = 0;
                $totaldiscount = 0;
                $totalamount = 0;
                $currentRangeStart = 0;
            @endphp
            <tbody>
                @foreach ($challanHeads as $session)
                    @php
                        $total += $session->price_count;
                        $totaldiscount += $session->total_concession;
                        $totalamount += $session->total_price;
                    @endphp
                    {{-- @dd($session) --}}
                    <tr style=" text-align:center;">
                        <td style=" text-align:center;">{{ $loop->iteration }}</td>
                        <td style=" text-align:center;">{{ $session->final_price }}</td>
                        <td style=" text-align:center;">{{ $session->price_count }}</td>
                        <td style=" text-align:center;">{{ $session->total_price }}</td>
                    </tr>
                @endforeach
                <tr style="background:gray; ">
                    <td colspan="2" style=" text-align:center; color:#fff">Total</td>
                    <td style=" text-align:center; color:#fff">{{ $total }}</td>
                    <td style=" text-align:center; color:#fff">{{ $totalamount }}</td>
                    {{-- @dd($session) --}}
                </tr>
            </tbody>
        </table>
        <table class="table mt-3" style="margin-top:10px; width:100%">
            <thead>
                <tr class="table_heads" style="background:gray; " >
                    <th colspan="4" style=" text-align:center; background:gray"> REGULAR CHALLAN FEE INSIGHT REPROT {{ strtoupper(\Carbon\Carbon::parse(request()->get('month') ?? date('Y-m'))->format('F Y')) }}</th>
                </tr>
                <tr>
                    <td colspan="3" style=" text-align:left;">TOTAL FEE OF BRANCH</td>
                    <td style=" text-align:right;"> {{ $totalamount + $totaldiscount }}</td>
                </tr>
                <tr>
                    <td colspan="3" style=" text-align:left;">DISCOUNTED AMOUNT</td>
                    <td style=" text-align:right;"> {{ $totaldiscount }}</td>
                </tr>
                <tr>
                    <td colspan="3" style=" text-align:left;">BRANCH FEE DISCOUNT %</td>
                    <td style=" text-align:right;">@if(@$totalamount && @$totaldiscount){{ round($totalamount / $totaldiscount, 2) }}@else 0 @endif</td>
                </tr>
                <tr>
                    <td colspan="3" style=" text-align:left;">AVERAGE FEE OF BRANCH</td>
                    <td style=" text-align:right;">@if(@$totalamount && @$total){{ round($totalamount / $total, 2) }}@else 0 @endif</td>
                </tr>


            </tbody>
        </table>
        <table class="table mt-3" style="margin-top:10px; width:50%">
            <thead>
                <tr class="table_heads">
                    <th style="background:gray; text-align:center;">Fee Range</th>
                    <th style="background:gray; text-align:center;">Total Students</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ranges as $range)
                    <tr >
                        <td style="text-align:center; ">{{ $range['start'] }} - {{ $range['end'] }}</td>
                        <td style="text-align:center; ">{{ $range['total_students'] }}</td>
                    </tr>
                @endforeach
                    <tr class="table_heads">
                        <td style=" text-align:center; color:#fff">Total</td>
                        <td style=" text-align:center; color:#fff">{{ $total }}</td>
                    </tr>
            </tbody>
        </table>

    </div>
@endsection
