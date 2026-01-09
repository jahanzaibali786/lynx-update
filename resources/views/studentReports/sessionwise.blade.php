@extends('layouts.admin')
@section('page-title')
    {{ __('Session Wise Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
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
                        {{ Form::open(['route' => ['sessionWisereport'], 'method' => 'GET', 'id' => 'sessionWisereport']) }}
                        <div class="row d-flex justify-content-start ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session_from', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from', $sessions, request()->get('session_from'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session_to', __('Session To'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_to', $sessions, request()->get('session_to'), ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
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
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container card table-responsive">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School
                </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Session Wise Report</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : Auth::user()->name }}</p>
        </div>
        <div class="" style="width:100%">
            <p style="width: 34%; float:left;"><b>Session From:
                </b>{{ request()->get('session_from') ? $sessions[request()->get('session_from')] : '' }}</p>
            <p style="width: 34%; float:left;"></p>
            <p style="width: 30%; float:left; padding-left:100px;"><b>Session To:</b>
                {{ request()->get('session_to') ? $sessions[request()->get('session_to')] : '' }}</p>
        </div>

        <!-- Data Display Section -->
        <table class=" mt-4 ">
            <thead>
                <tr class="table_heads">
                    <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Registration
                    </th>
                </tr>
                <tr>
                    <th>Session</th>
                    @foreach ($school_classes as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($select_session as $session)
                    <tr>
                        <td>{{ $session->year }}</td>
                        @foreach ($school_classes as $class)
                            <td>{{ $registrationCounts[$session->id][$class] }}</td>
                        @endforeach
                        <td>{{ $totalRegistrations[$session->id] }}</td>
                    </tr>
                @endforeach
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Student
                            Strength</th>
                    </tr>
                    <tr>
                        <th>Session</th>
                        @foreach ($school_classes as $class)
                            <th>{{ $class }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{ $session->year }}</td>
                    @foreach ($school_classes as $class)
                        <td>{{ $strengthCounts[$session->id][$class] }}</td>
                    @endforeach
                    <td>{{ $totalStrength[$session->id] }}</td>
                </tr>
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">
                            Enrollment
                        </th>
                    </tr>
                    <tr>
                        <th>Session</th>
                        @foreach ($school_classes as $class)
                            <th>{{ $class }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{ $session->year }}</td>
                    @foreach ($school_classes as $class)
                        <td>{{ $enrollmentCounts[$session->id][$class] }}</td>
                    @endforeach
                    <td>{{ $totalEnrollments[$session->id] }}</td>
                </tr>
                <thead>
                    <tr class="table_heads">
                        <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">
                            Withdrawal
                        </th>
                    </tr>
                    <tr>
                        <th>Session</th>
                        @foreach ($school_classes as $class)
                            <th>{{ $class }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{ $session->year }}</td>
                    @foreach ($school_classes as $class)
                        <td>{{ $withdrawalCounts[$session->id][$class] }}</td>
                    @endforeach
                    <td>{{ $totalWithdrawals[$session->id] }}</td>
                </tr>

            </tbody>
        </table>
    </div>
@endsection
