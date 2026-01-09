@extends('layouts.admin')
@section('page-title')
    {{ __('Emp. Retirement Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Emp. Retirement Report') }}</li>
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function branchemployees(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.employees') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        var $empSelect = $('#employee_id');
                        if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                            try {
                                $empSelect[0].customSelectInstance.destroy();
                            } catch (e) {}
                            delete $empSelect[0].customSelectInstance;
                        }
                        if ($empSelect.next('.custom-select-wrapper').length) {
                            $empSelect.next('.custom-select-wrapper').remove();
                        }
                        $empSelect.removeClass('custom-select');
                        $empSelect.empty();
                        $empSelect.append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        for (var j = 0; j < result.employee.length; j++) {
                            var emp = result.employee[j];
                            $('#employee_id').append($('<option>', {
                                value: emp.id,
                                text: emp.name
                            }));
                        }
                        $empSelect.addClass('custom-select');
                        $empSelect.show();
                        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                            window.CustomSelect.create($empSelect[0]);
                        }
                    }
                }
            });
        }
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['empRetirementReport'], 'method' => 'GET', 'id' => 'empRetirementReport']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branches, request()->input('branch'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)', 'id' => 'branch']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}

                                            {{ Form::select('employee_id', $employees, request()->input('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('empRetirementReport').submit(); return false;"
                                            data-bs-title="{{ __('Apply') }}">

                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('empRetirementReport') }}"
                                            class="btn mx-1 btn-sm btn-outline-danger" data-bs-title="{{ __('Reset') }}">
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
                                                    <button class="dropdown-item" type="submit" name="export"
                                                        value="excel">
                                                        <i class="ti ti-file me-2"></i>Excel
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" type="submit" name="export"
                                                        value="pdf">
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
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Emp. Retirement Report') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead>
                                <tr class="table_heads">
                                    {{-- Sr.No	Branch 	Emp No 	Emp Name	Retirement Age 60	Emp D.O.B	DOJ	Service Period Y/M	Left Service Tenure																										 --}}
                                    <th>{{ __('Sr.No') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Employee ID') }}</th>
                                    <th>{{ __('Employee Name') }}</th>
                                    <th>{{ __('Date of Birth') }}</th>
                                    <th>{{ __('Retirement Age 60') }}</th>
                                    <th>{{ __('Date of Joining') }}</th>
                                    <th>{{ __('Service Period Y/M') }}</th>
                                    <th>{{ __('Left Service Tenure') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employeesdata as $emp)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $emp->userbranch->name }}</td>
                                        <td>{{ $emp->employee_id }}</td>
                                        <td>{{ $emp->name }}</td>

                                        {{-- Date of Birth --}}
                                        <td>{{ date('d-M-Y', strtotime($emp->dob)) }}</td>

                                        {{-- Retirement Date (DOB + 60 years) --}}
                                        <td>{{ date('d-M-Y', strtotime($emp->dob . ' +60 years')) }}</td>

                                        {{-- Date of Joining --}}
                                        <td>{{ date('d-M-Y', strtotime($emp->company_doj)) }}</td>

                                        {{-- Service Completed (from DOJ to Today) --}}
                                        @php
                                            $doj = strtotime($emp->company_doj);
                                            $now = time();
                                            $diff = $now - $doj;

                                            $years = floor($diff / (365 * 24 * 60 * 60));
                                            $months = floor(
                                                ($diff - $years * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60),
                                            );
                                        @endphp
                                        <td>{{ $years }} years {{ $months }} months</td>

                                        {{-- Remaining Service (until Age 60) --}}
                                        @php
                                            $retirement = strtotime($emp->dob . ' +60 years');
                                            $remaining = $retirement - $now;

                                            if ($remaining > 0) {
                                                $ryears = floor($remaining / (365 * 24 * 60 * 60));
                                                $rmonths = floor(
                                                    ($remaining - $ryears * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60),
                                                );
                                            } else {
                                                $ryears = 0;
                                                $rmonths = 0;
                                            }
                                        @endphp
                                        <td>{{ $ryears }} years {{ $rmonths }} months</td>

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
