@extends('layouts.admin')
@section('page-title')
    {{ __('Session Wise Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        function exportToExcel() {
            var repName = 'monthsessionbranchwise';
            var form = document.getElementById('sessionWisereport');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            formData.append('repName', repName);
            var queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('monthlybranchwisereport') }}?" + queryString;
        }
        function exportToPdf() {
            var repName = 'monthsessionbranchwise';
            var form = document.getElementById('sessionWisereport');
            var formData = new FormData(form);
            formData.append('export', 'pdf');
            formData.append('repName', repName);
            var queryString = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('monthlybranchwisereport') }}?" + queryString;
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Session Wise Report') }}</li>
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
                        {{ Form::open(['route' => ['sessionMonthBranchWise'], 'method' => 'GET', 'id' => 'sessionWisereport']) }}
                        <div class="row d-flex">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('month_from', __('Month From'), ['class' => 'form-label']) }}
                                    {{ Form::select('month_from', $month_data, request()->get('month_from'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('month_to', __('Month To'), ['class' => 'form-label']) }}
                                    {{ Form::select('month_to', $month_data, request()->get('month_to') ?? '12', ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                    {{ Form::select('year', $years, request()->get('year') ?? date('Y'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            {{ Form::close() }}
                            <div
                                class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                                <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('sessionWisereport').submit(); return false;"
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
                                            <button class="dropdown-item" type="submit" onclick="exportToExcel(); return false;" name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" onclick="exportToPdf(); return false;" name="export" value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Month Wise Report</p>
        </div>
        <div class="" style="width:100%">
            <p style="width: 34%; float:left;"><b>Month From:
                </b>{{ request()->get('month_from') ? $month_data[request()->get('month_from')] : 'January' }}</p>
            <p style="width: 34%; float:left;"></p>
            <p style="width: 30%; float:left; padding-left:100px;"><b>Month
                    To:</b>{{ request()->get('month_to') ? $month_data[request()->get('month_to')] : 'December' }}</p>
        </div>
        <!-- Data Display Section -->
        <table class=" mt-4">
            <thead>
                <tr class="table_heads">
                    <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Registration</th>
                </tr>
                <tr>
                    <th>Month</th>
                    @foreach ($branches as $branchId => $branch)
                        <th>{{ $branch }}</th>
                    @endforeach
                    <th style="background:gray; text-align:center; color:red;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $session)
                    {{-- @dd($months); --}}
                    <tr>
                        <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                        @foreach ($branches as $branchId => $branch)
                            {{-- @dd($session,$class->id) --}}
                            <td>{{ $registrationCounts[$session][$branchId] }}</td>
                        @endforeach
                        <td style="background:gray; text-align:center; color:red;">{{ $totalRegistrations[$session] }}</td>
                    </tr>
                @endforeach
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Student
                            Strength</th>
                    </tr>
                    <tr>
                        <th>Month</th>
                        @foreach ($branches as $branchId => $branch)
                            <th>{{ $branch }}</th>
                        @endforeach
                        <th style="background:gray; text-align:center; color:red;">Total</th>
                    </tr>
                </thead>
                @foreach ($months as $session)
                    <tr>
                        <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                        @foreach ($branches as $branchId => $branch)
                            <td>{{ $strengthCounts[$session][$branchId] }}</td>
                        @endforeach
                        <td style="background:gray; text-align:center; color:red;">{{ $totalStrength[$session] }}</td>
                    </tr>
                @endforeach
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Enrollment
                        </th>
                    </tr>
                    <tr>
                        <th>Month</th>
                        @foreach ($branches as $branchId => $branch)
                            <th>{{ $branch }}</th>
                        @endforeach
                        <th style="background:gray; text-align:center; color:red;">Total</th>
                    </tr>
                </thead>
                @foreach ($months as $session)
                    <tr>
                        <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                        @foreach ($branches as $branchId => $branch)
                            <td>{{ $enrollmentCounts[$session][$branchId] }}</td>
                        @endforeach
                        <td style="background:gray; text-align:center; color:red;">{{ $totalEnrollments[$session] }}</td>
                    </tr>
                @endforeach
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Withdrawal
                        </th>
                    </tr>
                    <tr>
                        <th>Month</th>
                        @foreach ($branches as $branchId => $branch)
                            <th>{{ $branch }}</th>
                        @endforeach
                        <th style="background:gray; text-align:center; color:red;">Total</th>
                    </tr>
                </thead>
                @foreach ($months as $session)
                    <tr>
                        <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                        @foreach ($branches as $branchId => $branch)
                            <td>{{ $withdrawalCounts[$session][$branchId] }}</td>
                        @endforeach
                        <td style="background:gray; text-align:center; color:red;">{{ $totalWithdrawals[$session] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
