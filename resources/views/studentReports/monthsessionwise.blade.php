@extends('layouts.admin')

@section('page-title')
    {{ __('Month Wise Report') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Month Wise Report') }}</li>
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
                    <div class="card-body p-3">
                        {{ Form::open(['route' => ['sessionMonthWisereport'], 'method' => 'GET', 'id' => 'sessionWisereport']) }}
                        <div class="row justify-content-end">
                            <!-- Branch Selector -->
                            <div class="col-xl-3 col-md-6 mb-2">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'branches',
                                    $branches,
                                    $selectedBranchId,
                                    ['class' => 'form-control select']
                                ) }}
                            </div>

                            <!-- Month From -->
                            <div class="col-xl-2 col-md-6 mb-2">
                                {{ Form::label('month_from', __('Month From'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'month_from',
                                    $monthNames,
                                    $selectedMonthFrom,
                                    ['class' => 'form-control select']
                                ) }}
                            </div>

                            <!-- Month To -->
                            <div class="col-xl-2 col-md-6 mb-2">
                                {{ Form::label('month_to', __('Month To'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'month_to',
                                    $monthNames,
                                    $selectedMonthTo,
                                    ['class' => 'form-control select']
                                ) }}
                            </div>

                            <!-- Year -->
                            <div class="col-xl-2 col-md-6 mb-2">
                                {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'year',
                                    $years,
                                    $selectedYear,
                                    ['class' => 'form-control select']
                                ) }}
                            </div>

                            <!-- Actions -->
                            <div class="col-xl-2 col-md-6 d-flex align-items-end mb-2">
                                <button type="submit" class="btn btn-primary btn-sm me-1">
                                    {{ __('Search') }}
                                </button>
                                {{ Form::open(['route' => ['monthlystatistics'], 'method' => 'GET', 'id' => 'monthlystatistics']) }}
                                <button type="submit" name="export" value="excel" class="btn btn-outline-success btn-sm me-1">
                                    {{ __('Excel') }}
                                </button>
                                <button type="submit" name="print" value="pdf" formtarget="_blank" class="btn btn-outline-success btn-sm">
                                    {{ __('PDF') }}
                                </button>
                                {{ Form::close() }}
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Header -->
    <div class="card mt-3 table-responsive">
        <div class="text-center py-3">
            <h2 style="font-family:Edwardian Script ITC;">{{ __('The Lynx School') }}</h2>
            <h4 class="fw-bold">{{ __('Month Wise Report') }}</h4>
            <h5 class="fw-bold">
                {{ $branches[$selectedBranchId] ?? Auth::user()->name }}
            </h5>
            <p>
                <strong>{{ __('Period:') }}</strong>
                {{ $labelFrom }} — {{ $labelTo }}, {{ $selectedYear }}
            </p>
        </div>

        <!-- Registration Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Registration') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $registrationCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalRegistrations[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Strength Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Student Strength') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $strengthCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalStrength[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Enrollment Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Enrollment') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $enrollmentCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalEnrollments[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Withdrawal Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Withdrawal') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $withdrawalCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalWithdrawals[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
