@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Without Pay Leaves') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Without Pay Leaves') }}</li>
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
        // New printPdf function
        function printPdf() {
            const form = document.getElementById('empwithoutPayleave');
            let url = new URL(form.action, window.location.origin);
            let params = new URLSearchParams(new FormData(form));
            params.set('print', 'pdf');
            url.search = params.toString();
            window.open(url.toString(), '_blank');
        }
    </script>
@endpush

@section('content')
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['empwithoutPayleave'], 'method' => 'GET', 'id' => 'empwithoutPayleave']) }}
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
                                {{-- //from  --}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('from_date', request()->input('from_date'), ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                {{-- //to  --}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('to_date', request()->input('to_date'), ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    {{-- //print  --}}
                                    <!-- <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="printPdf(); return false;" data-bs-title="{{ __('Print') }}">
                                        <span class="btn-inner--icon">Print</span>
                                    </a> -->
                                    {{-- // actions  --}}
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('empwithoutPayleave').submit(); return false;"
                                        data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('empwithoutPayleave') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    @endif
    <div class="content" id="report-content">
        <div class="card p-4">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>
            <div class="table-responsive">
                <table class="datatable">
                    <tbody>
                        <thead class="table_heads">
                            <tr class="">
                                <th class="text-sm font-weight-bolder ">{{ __('sr.') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Br.sr') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Branch Name') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('D/O/J') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Service Pr.') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Emp No.') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Employee Name') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('From') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('To') }}</th>
                                <th class=" text-sm font-weight-bolder ">{{ __('Total W/O/P Days') }}</th>
                            </tr>
                        </thead>
                    <tbody>
                        @php
                            $groupedByBranch = collect($data)->groupBy(function ($emp) {
                                return $emp->employees->userbranch->name ?? '';
                            });
                        @endphp

                        @foreach ($groupedByBranch as $branchName => $empleaves)
                            <tr class="text-center bg-gray-200 font-bold">
                                <td colspan="10">{{ $branchName }}</td>
                            </tr>
                            @foreach ($empleaves as $index => $empleave)
                                <tr class="text-center">
                                    <td>{{ $loop->parent->index * $empleaves->count() + $index + 1 }}</td>
                                    <td>{{ @$empleave->employees->userbranch->id ?? '' }}</td>
                                    <td>{{ $branchName }}</td>
                                    <td>{{ @$empleave->employees->company_doj ?? '' }}</td>
                                    <td>{{ $empleave->employees ? @$empleave->employees->getEmployeeTenure($empleave->employees->id) : '' }}
                                    </td>
                                    <td>{{ @$empleave->employees->employee_id ?? '' }}</td>
                                    <td>{{ @$empleave->employees->name ?? '' }}</td>
                                    <td>{{ @$empleave->start_date ?? '' }}</td>
                                    <td>{{ @$empleave->end_date ?? '' }}</td>
                                    <td>{{ @$empleave->total_leave_days ?? '' }}</td>
                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
