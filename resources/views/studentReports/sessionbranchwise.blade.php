@extends('layouts.admin')
@section('page-title')
    {{ __('Session Branch Wise Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Session Branch Wise Report') }}</li>
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
                        {{ Form::open(['route' => ['sessionBranchWise'], 'method' => 'GET', 'id' => 'sessionWisereport']) }}
                        <div class="row d-flex justify-content-end ">

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
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('sessionWisereport').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <button  data-bs-title="Export" class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" value="excel"><span class="btn-inner--icon">Pdf / Print</span></button>
                                <button  data-bs-title="Print" formtarget="_blank" class="btn mx-1 btn-sm btn-outline-success" type="submit" name="print" value="pdf"><span class="btn-inner--icon"><i class="ti ti-print">Print</i></span></button>
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
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Session Wise Report</p>
        </div>
        <div class="" style="width:100%">
            <p style="width: 34%; float:left;"><b>Session From: </b>{{ request()->get('session_from') ? $sessions[request()->get('session_from')] : $sessions[$current_session] }}</p>
            <p style="width: 34%; float:left;"></p>
            <p style="width: 30%; float:left; padding-left:100px;"><b>Session To:</b>
                {{ request()->get('session_to') ? $sessions[request()->get('session_to')] : $sessions[$current_session] }}</p>
        </div>

        <!-- Data Display Section -->
        <table class=" mt-4">
            <thead>
                <tr class="table_heads">
                    <th  colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Registration</th>
                </tr>
                <tr>
                    <th>Session</th>
                    @foreach ($branches as $branchId => $branch)
                        <th>{{ $branch }}</th>
                    @endforeach
                    <th style="background:gray; text-align:center; color:red;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($select_session as $session)
                <tr>
                    <td>{{$session->year}}</td>
                    @foreach ($branches as $branchId => $branch)
                        <td>{{ $registrationCounts[$session->id][$branchId] }}</td>
                    @endforeach
                    <td style="background:gray; text-align:center; color:red;">{{ $totalRegistrations[$session->id] }}</td>
                </tr>
                @endforeach
                <thead>
                    <tr class="table_heads">
                        <th  colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Student Strength</th>
                    </tr>
                    <tr>
                        <th>Session</th>
                        @foreach ($branches as $branchId => $branch)
                            <th>{{ $branch }}</th>
                        @endforeach
                        <th style="background:gray; text-align:center; color:red;">Total</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{$session->year}}</td>
                    @foreach ($branches as $branchId => $branch)
                        <td>{{ $strengthCounts[$session->id][$branchId] }}</td>
                    @endforeach
                    <td style="background:gray; text-align:center; color:red;">{{ $totalStrength[$session->id] }}</td>
                </tr>
                <thead>
                    <tr class="table_heads">
                        <th  colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Enrollment</th>
                    </tr>
                    <tr>
                        <th>Session</th>
                        @foreach ($branches as $branchId => $branch)
                            <th>{{ $branch }}</th>
                        @endforeach
                        <th style="background:gray; text-align:center; color:red;">Total</th>
                    </tr>
                </thead>
                <tr>
                    <td>{{$session->year}}</td>
                    @foreach ($branches as $branchId => $branch)
                        <td>{{ $enrollmentCounts[$session->id][$branchId] }}</td>
                    @endforeach
                    <td style="background:gray; text-align:center;color:red;">{{ $totalEnrollments[$session->id] }}</td>
                </tr>
                    <thead>
                        <tr class="table_heads">
                            <th colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Withdrawal</th>
                        </tr>
                        <tr>
                            <th>Session</th>
                            @foreach ($branches as $branchId => $branch)
                                <th>{{ $branch }}</th>
                            @endforeach
                            <th style="background:gray; text-align:center; color:red;">Total</th>
                        </tr>
                    </thead>
                    <tr>
                        <td>{{$session->year}}</td>
                        @foreach ($branches as $branchId => $branch)
                            <td>{{ $withdrawalCounts[$session->id][$branchId] }}</td>
                        @endforeach
                        <td style="background:gray; text-align:center; color:red;">{{ $totalWithdrawals[$session->id] }}</td>
                    </tr>

            </tbody>
        </table>
    </div>
@endsection
