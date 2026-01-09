@extends('layouts.admin')
@section('page-title')
    {{ __('Staff Turnover Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Staff Turnover Report') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['staff.turnover'], 'method' => 'GET', 'id' => 'staffTurnOver']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('from_date', isset($_GET['from_date']) ? $_GET['from_date'] : '', ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('to_date', isset($_GET['to_date']) ? $_GET['to_date'] : '', ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('staffTurnOver').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('staff.turnover') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <table class="datatable">
                            <thead>
                                <tr class="table_heads">
                                    {{-- Sr.No	Branch 	Emp No 	Emp Name	Staff O/B	New Appointment	Resigned	Staff Turn Over																											 --}}
                                    <th>{{ __('Sr.No') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Emp No') }}</th>
                                    <th>{{ __('Emp Name') }}</th>
                                    <th>{{ __('Staff O/B') }}</th>
                                    <th>{{ __('New Appointment') }}</th>
                                    <th>{{ __('Resigned') }}</th>
                                    <th>{{ __('Staff Turn Over') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $ob = 0;
                                $new = 0;
                                $resigned = 0;
                                @endphp
                                @foreach ($employees as $employee)
                                    @php
                                       if($employee->company_doj >= $from_date && $employee->company_doj <= $to_date)
                                       {
                                           $ob++;
                                       }
                                       if($employee->joining_date >= $from_date && $employee->joining_date <= $to_date)
                                       {
                                           $new++;
                                       }
                                       if(@$employee->resignation->last_attendance_date >= $from_date && @$employee->resignation->last_attendance_date <= $to_date)
                                       {
                                           $resigned++;
                                       }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $employee->branch->name ?? '' }}</td>
                                        <td>{{ $employee->employee_id }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $ob }}</td>
                                        <td>{{ $new }}</td>
                                        <td>{{ $resigned }}</td>
                                        <td>{{ ($ob + $new) - $resigned }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
