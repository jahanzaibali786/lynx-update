@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Salary') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary Detail') }}</li>
@endsection
@push('script-page')
    <script>
        function printsalarydetail() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('printsalarydetail') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    </script>
@endpush

@section('content')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['employee-salary-detail.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                            <div class="row d-flex justify-content-end">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                        {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                        {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary" onclick="document.getElementById('employee_submit').submit(); return false;">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('employee-salary-detail.index') }}" class="btn mx-1 btn-sm btn-outline-danger">
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
                                            {{-- <li>
                                                <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                    <i class="ti ti-download me-2"></i>Pdf
                                                </button>
                                            </li> --}}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="table-responsive">
        <table class="datatable table">
            <thead>
                <tr class="table_heads">
                    <th>#</th>
                    <th>{{ __('Employee ID') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Bank A/C') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('Designation') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Scale No') }}</th>
                    <th>{{ __('Working Days') }}</th>
                    <th>{{ __('EOBI') }}</th>
                    <th>{{ __('EOBI Values') }}</th>
                    <th>{{ __('PESSI') }}</th>
                    <th>{{ __('PESSI Values') }}</th>
                    <th>{{ __('Emp Sec.') }}</th>
                    <th>{{ __('Income Tax') }}</th>
                    <th>{{ __('Gross') }}</th>
                    <th>{{ __('Net') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    
                    @php
                        $lastPayscaleDetail = $employee->employee_payscale_details->last();
                        $gross = 0;
                    @endphp

                    @if ($lastPayscaleDetail)
                        @php
                            $payscalesauto = \App\Models\EmployeeScale::with(
                                'employeeScaleHeads',
                                'employeeScaleHeads.SalaryHeads',
                                'employeepayScaledetailHeads'
                            )
                                ->find($lastPayscaleDetail->pay_scale_id);

                            if ($payscalesauto) {
                                foreach ($payscalesauto->employeeScaleHeads ?? [] as $scale_head) {
                                    $gross += $scale_head->head_value ?? 0;
                                }
                            }

                            $gross += (
                                ($lastPayscaleDetail->drns ?? 0) +
                                ($lastPayscaleDetail->conv ?? 0) +
                                ($lastPayscaleDetail->misc ?? 0) +
                                ($lastPayscaleDetail->other_add ?? 0)
                            );
                            $branches_school = \App\Models\SchoolDetails::where('branch_id', $employee->owned_by)->first();
                            $eobiValue = ($employee->eobi / 100) * ($branches_school->eobi_values ?? 0);
                            $eobiEmployerValue = ($employee->eobi_employer / 100) * ($branches_school->eobi_values ?? 0);
                            $pessiValue = ($employee->pessi / 100) * ($branches_school->pessi_values ?? 0);
                            $pessiEmployerValue = ($employee->pessi_employer / 100) * ($branches_school->pessi_values ?? 0);
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="Id">
                                @can('show employee profile')
                                    <a href="#" data-size="xl"
                                        data-url="{{ route('employee-salary-detail.show', Crypt::encrypt($employee->id)) }}"
                                        data-ajax-popup="true" class="btn btn-sm btn-outline-primary mx-1">
                                        {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                    </a>
                                @else
                                    <span class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</span>
                                @endcan
                            </td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $lastPayscaleDetail->account_number ?? '-' }}</td>
                            <td>{{ optional(\Auth::user()->getDepartment($employee->department_id))->name ?? '-' }}</td>
                            <td>{{ optional(\Auth::user()->getDesignation($employee->designation_id))->name ?? '-' }}</td>
                            <td>{{ optional(\Auth::user()->getBranch($employee->owned_by))->name ?? '-' }}</td>
                            <td>{{ $lastPayscaleDetail->scale->scale_no ?? '-' }}</td>
                            <td>{{ $lastPayscaleDetail->working_days ?? '-' }}</td>
                            <td>{{ $employee->eobi . '|' . $employee->eobi_employer }}</td>
                            <td>{{ $eobiValue . '|' . $eobiEmployerValue }}</td>
                            <td>{{ $employee->pessi . '|' . $employee->pessi_employer }}</td>
                            <td>{{ $pessiValue . '|' . $pessiEmployerValue }}</td>
                            <td>{{ $lastPayscaleDetail->emp_sec }}</td>
                            <td>{{ $lastPayscaleDetail->itax ?? '0' }}</td>
                            <td>{{ $gross }}</td>
                            <td>{{ $lastPayscaleDetail->net ?? '-' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="Id">
                                @can('show employee profile')
                                    <a href="#" data-size="xl"
                                        data-url="{{ route('employee-salary-detail.show', Crypt::encrypt($employee->id)) }}"
                                        data-ajax-popup="true" class="btn btn-sm btn-outline-primary mx-1"  data-bs-toggle="{{ __('Assign Scale') }}">
                                        {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                    </a>
                                @else
                                    <span class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</span>
                                @endcan
                            </td>
                            <td>{{ $employee->name }}</td>
                            <td>-</td>
                            <td>{{ optional(\Auth::user()->getDepartment($employee->department_id))->name ?? '-' }}</td>
                            <td>{{ optional(\Auth::user()->getDesignation($employee->designation_id))->name ?? '-' }}</td>
                            <td>{{ optional(\Auth::user()->getBranch($employee->branch_id))->name ?? '-' }}</td>
                            <td>{{ $employee->eobi . '|' . $employee->eobi_employer }}</td>
                            <td>-</td>
                            <td>{{ $employee->pessi . '|' . $employee->pessi_employer }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
