@extends('layouts.admin')
@section('page-title')
    {{ __('Employee Profile Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Profile Report') }}</li>
@endsection
@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'employee_profile_report', 'method' => 'GET', 'id' => 'employee_profile_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select', 'id' => 'branch']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                        {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                        {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                        {{ Form::select('status', $statuses, request('status'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('employee_profile_report').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                id="reportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportActionsDropdown">
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
            {{ Form::close() }}
        </div>
    </div>

    <div id="printableArea">
        <div class="card mt-2 p-2">
            <div class="mt-1" style="margin: 0 auto; padding: 10px; width:100%; display:flex; justify-content: center; align-items: center; flex-direction: column;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
                </div>
                <div style="width: 100%; text-align: center;">
                    <p style="font-size:1rem; text-align: center; font-weight: 800;">Employee Profile Report</p>
                </div>
                <div style="width: 100%; display: flex; justify-content: space-between;">
                    <p><b>Branch: </b>{{ @$branches[request('branch')] ?? 'All Branches' }}</p>
                </div>

                <div class="table-responsive maximumHeightNew mt-2" style="width: 100%;">
                    <table class="datatable">
                        <thead class="table_heads sticky-headerNew">
                            <tr class="table_heads">
                                <th>{{ __('Sr No.') }}</th>
                                <th>{{ __('Emp ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Father Name') }}</th>
                                <th>{{ __('CNIC') }}</th>
                                <th>{{ __('DOB') }}</th>
                                <th>{{ __('Gender') }}</th>
                                <th>{{ __('Religion') }}</th>
                                <th>{{ __('Blood Group') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('DOJ') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $index => $emp)
                                <tr class="trNew">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $emp->employee_id }}</td>
                                    <td>{{ $emp->name }}</td>
                                    <td>{{ $emp->f_name }}</td>
                                    <td>{{ $emp->cnic }}</td>
                                    <td>{{ $emp->dob == '0000-00-00' || !$emp->dob ? '' : \Carbon\Carbon::parse($emp->dob)->format('d M Y') }}</td>
                                    <td>{{ strtoupper($emp->gender) }}</td>
                                    <td>{{ $emp->religion }}</td>
                                    <td>{{ $emp->blood_group }}</td>
                                    <td>{{ $emp->phone }}</td>
                                    <td>{{ $emp->email }}</td>
                                    <td>{{ optional($emp->userbranch)->name }}</td>
                                    <td>{{ optional($emp->department)->name }}</td>
                                    <td>{{ optional($emp->designation)->name }}</td>
                                    <td>{{ $emp->company_doj == '0000-00-00' || !$emp->company_doj ? '' : \Carbon\Carbon::parse($emp->company_doj)->format('d M Y') }}</td>
                                    <td>{{ $emp->address }}</td>
                                    <td>
                                        @if($emp->is_res_ter == 1) Resigned
                                        @elseif($emp->is_res_ter == 2) Terminated
                                        @else Active
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center">{{ __('No employees found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
