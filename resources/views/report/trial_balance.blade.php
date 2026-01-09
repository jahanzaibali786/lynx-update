@extends('layouts.admin')
@section('page-title')
    {{ __('Trial Balance') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Trial Balance') }}</li>
@endsection

@push('script-page')
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
@endpush

@section('action-btn')
    <div class="float-end">
        {{ Form::open(['route' => ['trial.balance.print']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="branch" class="branch1">
        <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"  title="{{ __('Print') }}"
            data-original-title="{{ __('Print') }}"><span class="btn-inner--icon">Print</span></button>
        {{ Form::close() }}
    </div>

    <div class="float-end me-2">
        {{ Form::open(['route' => ['trial.balance.export']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="branch" class="branch1">
        <button type="submit" class="btn mx-1 btn-sm btn-outline-primary"  title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><span class="btn-inner--icon">Export</span></button>
        {{ Form::close() }}
    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="btn mx-1 btn-sm btn-outline-primary"  title="filter"><span class="btn-inner--icon">Filters</span></button>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card" id="show_filter" style="display:none;">
                    <div class="card-body">
                        {{ Form::open(['route' => ['trial.balance'], 'method' => 'GET', 'id' => 'report_trial_balance']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    {{-- <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                            {{ Form::label('branch', __('branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branches, $filter['branch'], ['class' => 'branch3 form-control branch']) }}
                                        </div>
                                    </div> --}}
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


                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('report_trial_balance').submit(); return false;"
                                             data-bs-title="{{ __('Apply') }}"
                                            data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>

                                        <a href="{{ route('trial.balance') }}" class="btn btn-sm btn-danger "
                                             data-bs-title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
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

    <div class="row justify-content-center" id="printableArea">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="account-main-title mb-5">
                        {{-- <h5>{{ 'Trial Balance of ' . $user->name . ' as of ' . $filter['branch_name'] .'  '. $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }} --}}
                            </h4>
                    </div>
                    <div
                        class="aacount-title d-flex align-items-center justify-content-between border-top border-bottom py-2">
                        <h6 class="mb-0">{{ __('Account') }}</h6>
                        <h6 class="mb-0 text-center">{{ _('Account Code') }}</h6>
                        <h6 class="mb-0 text-end me-5">{{ __('Debit') }}</h6>
                        <h6 class="mb-0 text-end">{{ __('Credit') }}</h6>

                    </div>
                    @php
                    $totalCredit = 0;
                    $totalDebit = 0;
                    @endphp

                    @foreach ($totalAccounts as $type => $accounts)
                        <div class="account-main-inner border-bottom py-2">
                            <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                            @foreach ($accounts as $key => $record)
                                <div class="account-inner d-flex align-items-center justify-content-between">
                                    <p class="mb-2"><a
                                            href="{{ route('report.ledger', $record['id']) }}?account={{ $record['id'] }}"
                                            class="text-primary">{{ $record['name'] }}</a>
                                    </p>
                                    <p class="mb-2 text-center">{{ $record['code'] }}</p>
                                    @if($type == 'Assets')
                                        <p class="text-primary mb-2 text-end me-5">
                                        @php $total= $record['totalDebit'] - $record['totalCredit'] ;
                                            $totalDebit+= $total;
                                            $totalCredit+= 0;
                                        @endphp
                                        {{ \Auth::user()->priceFormat($total) }}</p>
                                        <p class="text-primary mb-2 float-end text-end">
                                            {{ \Auth::user()->priceFormat(0.00) }}</p>
                                    @else
                                        <p class="text-primary mb-2 text-end me-5">
                                        {{ \Auth::user()->priceFormat($record['totalDebit']) }}</p>
                                        <p class="text-primary mb-2 float-end text-end">
                                            {{ \Auth::user()->priceFormat($record['totalCredit']) }}</p>
                                        @php
                                            $totalDebit+= $record['totalDebit'];
                                            $totalCredit+= $record['totalCredit'];
                                        @endphp
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    @if($totalAccounts != [])
                    <div
                        class="aacount-title d-flex align-items-center justify-content-between border-top border-bottom py-2 px-2 pe-0">
                        <h6 class="fw-bold mb-0">{{ 'Total' }}</h6>
                        <h6 class="fw-bold mb-0">{{ ''}}</h6>
                        <h6 class="fw-bold mb-0 text-end me-5">{{ \Auth::user()->priceFormat($totalDebit) }}</h6>
                        <h6 class="fw-bold mb-0 text-end">{{ \Auth::user()->priceFormat($totalCredit) }}</h6>

                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection


@push('script-page')
    <script>
        $(document).ready(function() {
            callback();

            function callback() {
                var start_date = $(".startDate").val();
                var end_date = $(".endDate").val();
                var branch = $(".branch3").val();

                $('.start_date').val(start_date);
                $('.end_date').val(end_date);
                $('.branch1').val(branch);

            }
        });
    </script>
@endpush
