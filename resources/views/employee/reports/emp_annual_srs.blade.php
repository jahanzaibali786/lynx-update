@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Annual SRS Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Emp. Annual SRS Report') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['empAnnualSrs'], 'method' => 'GET', 'id' => 'empAnnualSrs']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, request()->input('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, request()->input('employee_id'), ['class' => 'form-control select', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                            {{ Form::month('from_date', request()->input('from_date') ?? date('Y-m', strtotime('-1 year')), ['class' => 'form-control']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                            {{ Form::month('to_date', request()->input('to_date') ?? date('Y-m'), ['class' => 'form-control']) }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <div class="btn-box d-flex justify-content-end gap-2">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('empAnnualSrs').submit(); return false;"
                                        data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('empAnnualSrs') }}" class="btn btn-sm btn-danger"
                                        data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                                                        <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block mx-1">
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
                    <div class="card-body table-responsive">
                        <table class="datatable ">
                            <thead>
                                <tr class="table_heads">
                                    <th>{{ __('Sr.No') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Emp. No') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('D.O.J') }}</th>
                                    <th>{{ __('Service Period') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Gross Salary') }}</th>
                                    <th>{{ __('ACR Score') }}</th>
                                    <th>{{ __('ACR Grade') }}</th>
                                    <th>{{ __('Payscale Department') }}</th>
                                    <th>{{ __('New Payscale No') }}</th>
                                    <th>{{ __('Proposed New Gross Salary') }}</th>
                                    <th>{{ __('No of Child') }}</th>
                                    <th>{{ __('Child Concession Rs.') }}</th>
                                    <th>{{ __('Eobi Employer Cont') }}</th>
                                    <th>{{ __('Pessi  Employer Cont') }}</th>
                                    <th>{{ __('Any Other Benefit') }}</th>
                                    <th>{{ __('Cost to Company') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandgrossprev = 0;
                                    $grandgrossProp = 0;
                                    $grandcostToCompany = 0;
                                @endphp
                                @foreach ($annualSrsData as $employeeId => $records)
                                    @php

                                        $latest = $records['latest'];
                                        $previous = $records['previous'];

                                        // If no previous exists, fall back to latest for gross
                                        $gross = $previous
                                            ? $previous->net -
                                                ($previous->other_deduction +
                                                    $previous->advance +
                                                    $previous->pessi +
                                                    $previous->eobi +
                                                    $previous->itax +
                                                    $previous->emp_sec +
                                                    $previous->child_concession +
                                                    $previous->drns +
                                                    $previous->misc +
                                                    $previous->conv)
                                            : $latest->net -
                                                ($latest->other_deduction +
                                                    $latest->advance +
                                                    $latest->pessi +
                                                    $latest->eobi +
                                                    $latest->itax +
                                                    $latest->emp_sec +
                                                    $latest->child_concession +
                                                    $latest->drns +
                                                    $latest->misc +
                                                    $latest->conv);

                                        $grandgrossprev += $gross;
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $latest->employee->userbranch->name ?? '' }}</td>
                                        <td>{{ $latest->employee->employee_id ?? '' }}</td>
                                        <td>{{ $latest->employee->name ?? '' }}</td>
                                        <td>{{ $latest->employee->company_doj ?? '' }}</td>
                                        <td>{{ @$latest->employee ? $latest->employee->getEmployeeTenure($latest->employee->id) : '' }}
                                        </td>
                                        <td>{{ $latest->employee->department->name ?? '' }}</td>
                                        <td>{{ $latest->employee->designation->name ?? '' }}</td>

                                        {{-- From previous scale --}}
                                        <td>{{ $gross ?? '0' }}</td>

                                        {{-- From latest scale --}}
                                        <td>{{ $latest->acr_score ?? '' }}</td>
                                        <td>{{ $latest->acr_grade ?? '' }}</td>
                                        <td>{{ $latest->department->name ?? '' }}</td>
                                        <td>{{ $latest->scale->scale_no ?? '' }}</td>
                                        @php
                                            $proposedGross =
                                                $latest->net -
                                                ($latest->other_deduction +
                                                    $latest->advance +
                                                    $latest->pessi +
                                                    $latest->eobi +
                                                    $latest->itax +
                                                    $latest->emp_sec +
                                                    $latest->child_concession +
                                                    $latest->drns +
                                                    $latest->misc +
                                                    $latest->conv);
                                            $grandgrossProp += $proposedGross;
                                        @endphp
                                        <td>{{ $proposedGross ?? '' }}</td>
                                        <td>{{ @$latest->employee->empChilds ? $latest->employee->empChilds->count() : '0' }}</td>
                                        <td>{{ $latest->child_concession ?? '0' }}</td>
                                        <td>{{ $latest->eobi_employer ?? '0' }}</td>
                                        <td>{{ $latest->pessi_employer ?? '0' }}</td>
                                        <td>{{ $latest->other_add ?? '0' }}</td>
                                        @php
                                            $CostTC =
                                                $proposedGross +
                                                $latest->eobi_employer +
                                                $latest->pessi_employer +
                                                $latest->other_add;
                                            $grandcostToCompany += $CostTC;
                                        @endphp
                                        <td>{{ $CostTC ?? '0' }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="8"><b>Total</b></td>
                                    <td><b>{{ $grandgrossprev }}</b></td>
                                    <td colspan="4"></td>
                                    <td><b>{{ $grandgrossProp }}</b></td>
                                    <td colspan="5"></td>
                                    <td><b>{{ $grandcostToCompany }}</b></td>
                                </tr>
                            </tbody>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
