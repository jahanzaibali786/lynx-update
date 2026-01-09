@extends('layouts.admin')

@section('page-title')
    {{ __('Employee History Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee History Report') }}</li>
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function branchemployees(id) {
            // remember previous selection so we can restore if still available
            var prevVal = $('#employee_id').val();

            function isResigned(emp) {
                if (!emp) return false;
                if ('resigned' in emp) {
                    var v = emp.resigned;
                    return v === 1 || v === '1' || v === true || v === 'true';
                }
                if ('is_resigned' in emp) {
                    var v2 = emp.is_resigned;
                    return v2 === 1 || v2 === '1' || v2 === true || v2 === 'true';
                }
                if ('is_active' in emp) {
                    var a = emp.is_active;
                    return a === 0 || a === '0' || a === false || a === 'false';
                }
                if ('status' in emp) {
                    var s = String(emp.status).toLowerCase();
                    return s === 'resigned' || s === 'left' || s === 'inactive' || s === 'terminated';
                }
                // fallback: assume active if we can't detect a resigned flag
                return false;
            }

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
                    if (result.status === 'success') {
                        var $empSelect = $('#employee_id');

                        // destroy previous custom-select instance if present
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

                        // populate options with only non-resigned employees
                        $empSelect.empty();
                        $empSelect.append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        var added = 0;
                        for (var j = 0; j < result.employee.length; j++) {
                            var emp = result.employee[j];
                            if (!isResigned(emp)) {
                                $empSelect.append($('<option>', {
                                    value: emp.id,
                                    text: emp.name
                                }));
                                added++;
                            }
                        }

                        if (added === 0) {
                            // show a disabled placeholder if no active employees
                            $empSelect.append($('<option>', {
                                value: '',
                                text: 'No active employees',
                                disabled: true
                            }));
                        }

                        // re-init custom select for this element only
                        $empSelect.addClass('custom-select');
                        $empSelect.show();
                        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                            window.CustomSelect.create($empSelect[0]);
                        }

                        // Restore previous selection if still present and not resigned
                        if (prevVal && $empSelect.find('option[value="' + prevVal + '"]').length) {
                            $empSelect.val(prevVal);
                            if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0]
                                .customSelectInstance.refresh === 'function') {
                                $empSelect[0].customSelectInstance.refresh();
                            }
                        }
                    } else {
                        console.warn('branchemployees returned status:', result.status);
                    }
                },
                error: function(xhr, status, err) {
                    console.error('AJAX error in branchemployees:', err);
                }
            });
        }
    </script>
    <script>
        function submitWithPrintFlag() {
            const form = document.getElementById('employmentHistory');
            const input = document.getElementById('is_print');
            input.value = 1;

            form.target = '_blank';
            form.submit();
            form.target = '';
            resetPrintFlagAndSubmit();
        }

        function resetPrintFlagAndSubmit() {
            const form = document.getElementById('employmentHistory');
            const input = document.getElementById('is_print');
            input.value = 0;
            form.submit();
        }
    </script>
@endpush

@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['employmentHistory'], 'method' => 'GET', 'id' => 'employmentHistory']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->input('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_id', $employees, request()->input('employee_id'), ['class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                                </div>
                            </div>
                            {{-- //rejoin select  --}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', ['rejoin' => __('Rejoin'), 'regular' => __('Regular')], request()->input('status'), ['class' => 'form-control select', 'placeholder' => __('Select Status')]) }}
                                </div>
                            </div>
                            <input type="hidden" name="is_print" id="is_print" value="0">
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('employmentHistory').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('employmentHistory') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.5rem; text-align: center; margin-top:-20px"><b>Employee History Report</b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>
            <div class="table-responsive">
                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>{{ __('Sr. No.') }}</th>
                            <th>{{ __('Employee Name') }}</th>
                            <th>{{ __('Designation') }}</th>
                            <th>{{ __('Department') }}</th>
                            <th>{{ __('D.O.J') }}</th>
                            <th>{{ __('Tenure') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($employeesquery) > 0)
                            @php $srNo = 1; @endphp
                            @foreach ($employeesquery as $employee)
                                @if ($employee->employee_rejoin && count($employee->employee_rejoin) > 0)
                                    @foreach ($employee->employee_rejoin as $rejoin)
                                        {{-- Previous Rejoin Period Row --}}
                                        <tr>
                                            <td>{{ $srNo++ }}</td>
                                            <td>{{ $employee->name }} (Previous)</td>
                                            <td>{{ $employee->designation->name ?? '' }}</td>
                                            <td>{{ $employee->department->name ?? '' }}</td>
                                            <td>{{ \Auth::user()->dateFormat($rejoin->prev_doj) }}</td>
                                            <td>
                                                @php
                                                    $prevDoj = \Carbon\Carbon::parse($rejoin->prev_doj);
                                                    $prevLeaving = \Carbon\Carbon::parse($rejoin->prev_leaving_date);
                                                    $prevTenure = $prevDoj->diff($prevLeaving);
                                                @endphp
                                                {{ $prevTenure->y }} years {{ $prevTenure->m }} months
                                            </td>
                                        </tr>
                                    @endforeach
                                    {{-- Current Rejoin Period Row --}}
                                    <tr>
                                        <td>{{ $srNo++ }}</td>
                                        <td>{{ $employee->name }} (Rejoined)</td>
                                        <td>{{ $employee->designation->name ?? '' }}</td>
                                        <td>{{ $employee->department->name ?? '' }}</td>
                                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                        <td>
                                            @php
                                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                                $currentTenure = $currentDoj->diff(now());
                                            @endphp
                                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                                        </td>
                                    </tr>
                                @else
                                    {{-- Single row if no rejoin --}}
                                    <tr>
                                        <td>{{ $srNo++ }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->designation->name ?? '' }}</td>
                                        <td>{{ $employee->department->name ?? '' }}</td>
                                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                        <td>
                                            @php
                                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                                $currentTenure = $currentDoj->diff(now());
                                            @endphp
                                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No Record Found') }}</td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
