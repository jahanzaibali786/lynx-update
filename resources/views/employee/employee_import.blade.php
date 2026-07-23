@extends('layouts.admin')
@section('page-title')
    {{ __('Employee Bulk Update') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Bulk Update') }}</li>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        {{ __('Download the sample file with current employee data for your branch. Edit the editable fields in Excel, then upload the same file. Employee ID and Name are used for matching and cannot be changed. Fields like Branch, Department, Designation are restricted from editing.') }}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Download Sample') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'employee.bulk.sample', 'method' => 'GET', 'target' => '_blank']) }}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', $statuses, request('status'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            {{ Form::submit(__('Download Sample'), ['class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>

            @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-danger">
                    <strong>{{ __('Skipped / Error Details:') }}</strong>
                    <ul class="mb-0 mt-1">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Upload Updated File') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'employee.bulk.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('file', __('Excel File'), ['class' => 'form-label']) }}
                                {{ Form::file('file', ['class' => 'form-control', 'required' => 'required', 'accept' => '.xlsx,.xls,.csv']) }}
                                <small class="form-text text-muted">{{ __('Accepted formats: xlsx, xls, csv') }}</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            {{ Form::submit(__('Update Employees'), ['class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
