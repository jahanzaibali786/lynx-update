@extends('layouts.admin')
@section('page-title')
    {{ __('Track Registration') }}
@endsection
@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'track_registration_report', 'method' => 'GET', 'id' => 'track_registration_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Registration Branch'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch', 'all'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('current_branch', __('Current Branch'), ['class' => 'form-label']) }}
                        {{ Form::select('current_branch', $branches, request('current_branch', 'all'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $classes, request('class', 'all'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_from', __('From'), ['class' => 'form-label']) }}
                        {{ Form::date('date_from', request('date_from'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('date_to', __('To'), ['class' => 'form-label']) }}
                        {{ Form::date('date_to', request('date_to'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('track_registration_report').submit(); return false;">Search</a>
                    <a href="{{ route('track_registration_report') }}" class="btn btn-sm btn-danger">Clear</a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">Export</button>
                        <ul class="dropdown-menu">
                            <li><button class="dropdown-item" type="submit" name="export" value="excel">Excel</button></li>
                            <li><button class="dropdown-item" type="submit" name="export" value="pdf">Pdf</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <div class="card mt-2 p-3">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem;"><b>The Lynx School</b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; font-weight: 800;">
            <p style="text-align: center; font-weight: 900; font-size: 1rem;">Student Registration Track Report</p>
            </p>
        </div>
        <div class="d-flex justify-content-between">
            <p><b>Period From :</b> {{ @$_GET['date_from'] ?? '' }}</p>
            <p><b>Period To :</b> {{ @$_GET['date_to'] ?? '' }}</p>
        </div>
        <div style="width:100%:">
            <table class="datatable">
                <thead class="table_heads">
                    <tr>
                        <th>Sr#</th>
                        <th>Reg No</th>
                        <th>Roll No</th>
                        <th>Adm Date</th>
                        <th>Student Name</th>
                        <th>Father Name</th>
                        <th>Registration Branch</th>
                        <th>Current Branch</th>
                        <th>Current Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $i => $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->StudentRegistration->reg_no ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->roll_no ?? ($student->enrollId ?? '-') }}</td>
                            <td>{{ !empty($student->StudentRegistration->regdate) ? date('d M Y', strtotime($student->StudentRegistration->regdate)) : '-' }}</td>
                            <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->fathername ?? '-' }}</td>
                            <td>{{ $student->admbranch->name ??  '-' }}</td>
                            <td>{{ $student->branch->name ??  '-' }}</td>
                            <td>{{ $student->class->name ?? ($student->StudentRegistration->class->name ?? '-') }}</td>
                            <td>{{ $student->StudentRegistration->student_status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
