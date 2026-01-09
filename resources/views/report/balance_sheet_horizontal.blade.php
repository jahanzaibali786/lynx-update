@extends('layouts.admin')
@section('page-title')
    {{ __('Balance Sheet') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Balance Sheet') }}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#filter").click(function() {
                $("#show_filter").toggle();
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var start_date = $(".startDate").val();
                var end_date = $(".endDate").val();
                var branch = $(".branch").val();

                $('.start_date').val(start_date);
                $('.end_date').val(end_date);
                $('.branch').val(branch);

            }
        });
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        {{ Form::open(['route' => ['balance.sheet.print', 'horizontal']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="branch" class="branch">
        <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"  title="{{ __('Print') }}"
            data-original-title="{{ __('Print') }}"><span class="btn-inner--icon">Print</span></button>
        {{ Form::close() }}
    </div>

    <div class="float-end me-2">
        {{ Form::open(['route' => ['balance.sheet.export']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="branch" class="branch">
        <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"  title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><span class="btn-inner--icon">Export</span></button>
        {{ Form::close() }}
    </div>

    <div class="float-end me-2" id="filter">
        <button id="filter"  title="filter" class="btn mx-1 btn-sm btn-outline-primary"><span class="btn-inner--icon">Filters</span></button>
    </div>

    <div class="float-end me-2">
        <a href="{{ route('report.balance.sheet', 'vertical') }}" class="btn mx-1 btn-sm btn-outline-primary" 
            title="{{ __('Vertical View') }}" data-original-title="{{ __('Vertical View') }}"><span class="btn-inner--icon"><i
                class="ti ti-separator-horizontal"></i></span></a>
    </div>
@endsection

@section('content')
    <div class="mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card" id="show_filter" style="display:none;">
                        <div class="card-body">
                            {{ Form::open(['route' => ['report.balance.sheet'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                             <div class="btn-box">
                                                {{ Form::label('branch', __('branch'), ['class' => 'form-label']) }}
                                                {{ Form::select('branch', $branches, isset($_GET['branches']) ? $_GET['branches'] : '',  ['class' => 'form-control branch']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate form-control']) }}
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate form-control']) }}
                                            </div>
                                        </div>
                                        <input type="hidden" name="view" value="horizontal">
                                    </div>
                                </div>
                                <div class="col-auto mt-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                                onclick="document.getElementById('report_bill_summary').submit(); return false;"
                                                 data-bs-title="{{ __('Apply') }}"
                                                data-bs-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon">Search</span>
                                            </a>

                                            <a href="{{ route('report.balance.sheet') }}" class="btn mx-1 btn-sm btn-outline-danger "
                                                 title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i
                                                        class="ti ti-trash-off text-white-off "></i></span>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        @php
            $authUser = \Auth::user()->creatorId();
            $user = App\Models\User::find($authUser);
        @endphp

        @php
            $authUser = \Auth::user()->creatorId();
            $user = App\Models\User::find($authUser);
        @endphp
        <div class="row justify-content-center" id="printableArea">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="account-main-title mb-5">
                            {{-- <h5>{{ 'Balance Sheet of ' . $user->name . ' as of ' . $filter['branch_name'] .'  '. $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}
                                </h4> --}}
                                <h5>{{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</</h5>
                        </div>

                        @php
                            $totalAmount = 0;
                        @endphp

                        <div class="row">
                            <div class="col-md-6">
                                <div class="aacount-title d-flex align-items-center justify-content-between border py-2">
                                    <h5 class="mb-0 ms-3">{{ __('Liabilities & Equity') }}</h5>
                                </div>
                                <div class="border-start border-end">
                                    @foreach ($chartAccounts as $type => $accounts)
                                        @if ($accounts != [] && $type != 'Assets')
                                            <div class="account-main-inner py-2">
                                                <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                                                @php
                                                    $total = 0;
                                                @endphp
                                                @foreach ($accounts as $account)
                                                    <div class="border-bottom py-2">
                                                        <p class="fw-bold ps-4 mb-2">
                                                            {{ $account['subType'] == true ? $account['subType'] : '' }}
                                                        </p>
                                                        @foreach ($account['account'] as $key => $record)
                                                            @if ($key < count($account['account']) - 1)
                                                                @if (!preg_match('/\btotal\b/i', $record['account_name']))
                                                                    <div
                                                                        class="account-inner d-flex align-items-center justify-content-between ps-5">
                                                                        <p class="mb-2"><a
                                                                                href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                                                class="text-primary">{{ $record['account_name'] }}</a>
                                                                        </p>
                                                                        <p class="mb-2 text-center">
                                                                            {{ $record['account_code'] }}</p>
                                                                            @php
                                                                                $liabBalance = $record['netAmount'];
                                                                                $liabBalance = ltrim($liabBalance, '-');
                                                                            @endphp
                                                                        <p class="text-primary mb-2 float-end text-end me-3">
                                                                            {{ \Auth::user()->priceFormat($liabBalance) }}</p>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        <div
                                                            class="account-inner d-flex align-items-center justify-content-between ps-4">
                                                            <p class="fw-bold mb-2">
                                                                {{ end($account['account']) == true ? end($account['account'])['account_name'] : 0 }}
                                                            </p>
                                                             @php
                                                                $alltotBalance = end($account['account']) == true ? end($account['account'])['netAmount'] :0;
                                                                $alltotBalance = ltrim($alltotBalance, '-');
                                                            @endphp
                                                            <p class="fw-bold mb-2 text-end me-3">
                                                                {{ end($account['account']) == true ? \Auth::user()->priceFormat($alltotBalance) : \Auth::user()->priceFormat(0) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    @php
                                                        $total += end($account['account']) == true ? end($account['account'])['netAmount'] : 0;
                                                    @endphp
                                                @endforeach
                                                <div
                                                    class="aacount-title d-flex align-items-center justify-content-between border-top border-bottom py-2 px-2 pe-0">
                                                    <h6 class="fw-bold mb-0">{{ 'Total for ' . $type }}</h6>
                                                    @php
                                                        $subtotBalance = $total;
                                                        $subtotBalance = ltrim($subtotBalance, '-');
                                                    @endphp
                                                    <h6 class="fw-bold mb-0 text-end me-3">{{ \Auth::user()->priceFormat($subtotBalance) }}</h6>
                                                </div>
                                                @php
                                                    if ($type != 'Assets') {
                                                        $totalAmount += $total;
                                                    }
                                                @endphp
                                            </div>
                                        @endif
                                    @endforeach
                                    @if ($totalAmount != 0)
                                        <div
                                            class="d-flex align-items-center justify-content-between border-bottom py-2 px-0">
                                            <h6 class="fw-bold mb-0 ms-2">{{ 'Total for Liabilities & Equity' }}</h6>
                                            <h6 class="fw-bold mb-0 text-end me-3">{{ \Auth::user()->priceFormat($totalAmount) }}</h6>
                                        </div>
                                    @endif
                                </div>

                            </div>

                            @php
                                $total = 0;
                            @endphp

                            <div class="col-md-6">
                                <div class="aacount-title d-flex align-items-center justify-content-between border py-2">
                                    <h5 class="mb-0 ms-3">{{ __('Assets') }}</h5>
                                </div>
                                <div class="border-start border-end">
                                    @foreach ($chartAccounts as $type => $accounts)
                                        @if ($accounts != [] && $type == 'Assets')
                                            <div class="account-main-inner py-2">
                                                @if ($type == 'Liabilities')
                                                    <p class="fw-bold mb-3"> {{ __('Liabilities & Equity') }}</p>
                                                @endif
                                                <p class="fw-bold ps-2 mb-2">{{ $type }}</p>


                                                @foreach ($accounts as $account)
                                                    <div class="border-bottom py-2">
                                                        <p class="fw-bold ps-4 mb-2">
                                                            {{ $account['subType'] == true ? $account['subType'] : '' }}
                                                        </p>
                                                        @foreach ($account['account'] as $key => $record)
                                                            @if ($key < count($account['account']) - 1)
                                                                @if (!preg_match('/\btotal\b/i', $record['account_name']))
                                                                    <div
                                                                        class="account-inner d-flex align-items-center justify-content-between ps-5">
                                                                        <p class="mb-2"><a
                                                                                href="{{ route('report.ledger', $record['account_id']) }}?account={{ $record['account_id'] }}"
                                                                                class="text-primary">{{ $record['account_name'] }}</a>
                                                                        </p>
                                                                        <p class="mb-2 text-center">
                                                                            {{ $record['account_code'] }}</p>
                                                                             @php
                                                                                $liabdBalance = $record['netAmount'];
                                                                                $liabdBalance = ltrim($liabdBalance, '-');
                                                                            @endphp
                                                                        <p class="text-primary mb-2 float-end text-end me-3">
                                                                            {{ \Auth::user()->priceFormat($liabdBalance) }}</p>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        <div
                                                            class="account-inner d-flex align-items-center justify-content-between ps-4">
                                                            <p class="fw-bold mb-2">
                                                                {{ end($account['account']) == true ? end($account['account'])['account_name'] : 0 }}
                                                            </p>
                                                              @php
                                                                $allastotBalance = end($account['account']) == true ? end($account['account'])['netAmount'] :0;
                                                                $allastotBalance = ltrim($allastotBalance, '-');
                                                            @endphp
                                                            <p class="fw-bold mb-2 text-end me-3">
                                                                {{ end($account['account']) == true ? \Auth::user()->priceFormat($allastotBalance) : \Auth::user()->priceFormat(0) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    @php
                                                        $total += end($account['account']) == true ? end($account['account'])['netAmount'] : 0;
                                                    @endphp
                                                @endforeach

                                            </div>
                                        @endif
                                    @endforeach
                                    @if ($totalAmount != 0)
                                        <div
                                            class="d-flex align-items-center justify-content-between border-bottom py-2 px-0">
                                            <h6 class="fw-bold mb-0 ms-2">{{ 'Total for Assets' }}</h6>
                                              @php
                                                $subastotBalance = $total;
                                                $subastotBalance = ltrim($subastotBalance, '-');
                                            @endphp
                                            <h6 class="fw-bold mb-0 text-end me-3">{{ \Auth::user()->priceFormat($subastotBalance) }}</h6>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
