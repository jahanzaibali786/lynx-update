@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Salary Proporal') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary Proporal') }}</li>
@endsection

@section('action-btn')
    <div class="col text-end">
        <a href="#" data-url="{{ route('employee-salary-proporal.bulk.create') }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Bulk Proposals') }}" class="apply-btn btn mx-1 btn-sm btn-outline-success">
            <span class="btn-inner--icon">
                Bulk Proposals
            </span>
        </a>
        <a href="#" data-url="{{ route('employee-salary-proporal.create') }}" data-size="lg" data-ajax-popup="true" data-bs-title="{{ __('Create Salary Proposal') }}" class="apply-btn btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">
                Create
            </span>
        </a>
    </div>
@endsection
@section('content')
    {{-- @if (\Auth::user()->type == 'company')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body filter_change" >
                    {{ Form::open(['route' => ['employee-salary-detail.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                    <div class="row d-flex justify-content-end ">

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department_id', $departments, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                {{ Form::select('designation_id', $designations, isset($_GET['designation_id']) ? $_GET['designation_id'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('employee_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('employee-salary-detail.index') }}" class="btn mx-1btn-sm btn-outline-danger" 
                                data-bs-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon">Clear</span>
                            </a>
                            <a href="#" onclick="printsalarydetail(); return false;" class="btn mx-1btn-sm btn-outline-primary"
                                 title="" title="Print">
                                <span class="btn-inner--icon">Print
                                </span>
                            </a>
                            <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export" value="excel"><span class="btn-inner--icon">Pdf / Print</span></button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif --}}

    <table class="">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>Employee</th>
                <th>Payscale</th>
                <th>Net Salary</th>
                <th>Bank Account</th>
                <th>Action</th>
            </tr>
        </thead>
        <div class="table-responsive">
            <tbody>
                @foreach ($salaryproposal as $proposal)
                    <tr>
                        <td>{{ $proposal->id }}</td>
                        <td>{{ @$proposal->employees->name }}</td>
                        <td>{{ @$proposal->employee_payscale->scale_no }}</td>
                        <td>{{ $proposal->net_salary }}</td>
                        <td>{{ $proposal->bank_account }}</td>
                        <td>
                            <div class="action-btn">
                                @if (\Auth::user()->type == 'company')
                                    <form action="{{ route('employee-salary-proporal.approve', $proposal->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="mx-1 btn mx-1 btn-sm btn-success"
                                             data-bs-title="{{ __('Approve') }}">
                                            <span class="btn-inner--icon">
                                                <i class="ti ti-check"></i>
                                            </span>
                                        </button>
                                    </form>
                                    <form action="{{ route('employee-salary-proporal.rollback', $proposal->id) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="mx-1 btn mx-1 btn-sm btn-warning"
                                             data-bs-title="{{ __('Rollback') }}">
                                            <span class="btn-inner--icon">
                                                <i class="fa fa-close"></i>Rollback
                                            </span>
                                        </button>
                                    </form>
                                    @endif
                                    @if (\Auth::user()->type != 'company')
                                        <form
                                            action="{{ route('employee-salary-proporal.sendForApproval', $proposal->id) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm mx-1 btn-success"
                                                 data-bs-title="{{ __('Send For Approval') }}"
                                                @if ($proposal->status == '1' || $proposal->status == '3') disabled @endif>
                                                <span class="btn-inner--icon">
                                                    <i class="fa fa-user"></i>
                                                </span>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- @if ($proposal->status != '1') --}}
                                        <a href="#"
                                            data-url="{{ route('employee-salary-proporal.edit', $proposal->id) }}"
                                            data-size="lg" data-ajax-popup="true" data-bs-title="{{ __('Edit Salary Proposal') }}"
                                            class="btn btn-sm mx-1 btn-outline-primary " >
                                            <span class="btn-inner--icon">
                                                <i class="ti ti-pencil"></i>
                                            </span>
                                        </a>
                                        <form action="{{ route('employee-salary-proporal.destroy', $proposal->id) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm mx-1 btn-danger"
                                                 data-bs-title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon">
                                                    <i class="fa fa-trash"></i>
                                                </span>
                                            </button>
                                        </form>
                                    {{-- @endif --}}
                                {{-- @endif --}}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
    </table>
    </div>
@endsection
