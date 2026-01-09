<!-- resources/views/employee/reports/empLeaveRpt.blade.php -->
@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Leave Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Leave Report') }}</li>
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
        data: { id: id },
        dataType: 'json',
        success: function(result) {
            if (result.status === 'success') {
                var $empSelect = $('#employee_id');

                // destroy previous custom-select instance if present
                if ($empSelect[0] && $empSelect[0].customSelectInstance) {
                    try { $empSelect[0].customSelectInstance.destroy(); } catch (e) {}
                    delete $empSelect[0].customSelectInstance;
                }
                if ($empSelect.next('.custom-select-wrapper').length) {
                    $empSelect.next('.custom-select-wrapper').remove();
                }
                $empSelect.removeClass('custom-select');

                // populate options with only non-resigned employees
                $empSelect.empty();
                $empSelect.append($('<option>', { value: '', text: 'Select Employee' }));

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
                    $empSelect.append($('<option>', { value: '', text: 'No active employees', disabled: true }));
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
                    if ($empSelect[0] && $empSelect[0].customSelectInstance && typeof $empSelect[0].customSelectInstance.refresh === 'function') {
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

        function submitWithPrintFlag(type = "pdf") {
            const form = $('#empleaveReport');
            if (type === "pdf")
                $('#is_print').val(1);
            if (type === "excel")
                $('#is_excel').val(1);
            form.attr('target', '_blank').submit().removeAttr('target');
            $('#is_print').val(0);
            $('#is_excel').val(0);
        }
    </script>
@endpush

@section('content')
    {{-- @if (auth()->user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['empleaveReport'], 'method' => 'GET', 'id' => 'empleaveReport']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                {{ Form::select('employee_id', $employees, request('employee_id'), ['class' => 'form-control select custom-select', 'placeholder' => __('Select Employee')]) }}
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('from_date', __('From'), ['class' => 'form-label']) }}
                                {{ Form::date('from_date', request('from_date'), ['class' => 'form-control']) }}
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('to_date', __('To'), ['class' => 'form-label']) }}
                                {{ Form::date('to_date', request('to_date'), ['class' => 'form-control']) }}
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                {{-- <button type="button" class="btn mx-1 btn-sm btn-outline-primary me-2"
                                    data-bs-title="print" onclick="submitWithPrintFlag()">
                                    <span class="btn-inner--icon">Print</span>
                                </button> --}}
                                <button type="submit" class="btn mx-1 btn-sm btn-outline-primary me-2"
                                    data-bs-title="search">
                                    <span class="btn-inner--icon">Search</span>
                                </button>
                                <a href="{{ route('empleaveReport') }}" data-bs-title="reset"
                                    class="btn mx-1 btn-sm btn-outline-danger">
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
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}

    <div class="mb-4 card p-3" id="report-content"
        style="width:100% !important; display:flex; justify-content: center; align-items: center;">
        <div class="text-center mb-3">
            <h2 class="m-0" style="font-family: Edwardian Script ITC; font-size: 3rem;">The Lynx School</h2>
            <br>
            <p class="mb-0">
                @if (request('branches'))
                    {{ Auth::user()->getBranch(request('branches'))->name ?? 'All Branches' }}
                @else
                    <p class="mb-0" style="font-size: 1rem;">All Branches</p>
                @endif
            </p>
            <br>
            <p class="mb-0" style="font-size: 1rem;">Period: {{ $fromDate->toDateString() }} to
                {{ $toDate->toDateString() }}</p>
        </div>
        <br>
        <div class="table-responsive" style="width:100% !important;">
            <table class="table">
                <thead class="table_heads">
                    <tr>
                        <th>{{ __('Sr.') }}</th>
                        <th>{{ __('Br.Sr') }}</th>
                        <th>{{ __('D/O/J') }}</th>
                        <th>{{ __('Emp No.') }}</th>
                        <th>{{ __('Employee Name') }}</th>
                        <th>{{ __('CL OB') }}</th>
                        <th>{{ __('CL Avail') }}</th>
                        <th>{{ __('CL CB') }}</th>
                        <th>{{ __('AL OB') }}</th>
                        <th>{{ __('AL Avail') }}</th>
                        <th>{{ __('AL CB') }}</th>
                        <th>{{ __('ML OB') }}</th>
                        <th>{{ __('ML Avail') }}</th>
                        <th>{{ __('ML CB') }}</th>
                        <th>{{ __('Unpaid') }}</th>
                        <th>{{ __('Unpaid Total') }}</th>
                        <th>{{ __('Total Availed') }}</th>
                        <th>{{ __('Total Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $branchName => $employeesData)
                        <tr class="table-secondary">
                            <td colspan="18">{{ $branchName }}</td>
                        </tr>
                        @foreach ($employeesData as $idx => $item)
                            @php $emp = $item['employee']; @endphp
                            <tr>
                                <td>{{ $loop->parent->index + 1 }}.{{ $idx + 1 }}</td>
                                <td>{{ $emp->userbranch->id ?? '' }}</td>
                                <td>{{ \Carbon\Carbon::parse($emp->company_doj)->format('Y-m-d') }}</td>
                                <td>{{ $emp->employee_id }}</td>
                                <td>{{ $emp->name }}</td>
                                <td>{{ $item['ob']['CL'] }}</td>
                                <td>{{ $item['availed']['CL'] }}</ntd>
                                <td>{{ $item['cb']['CL'] }}</td>
                                <td>{{ $item['ob']['AL'] }}</td>
                                <td>{{ $item['availed']['AL'] }}</td>
                                <td>{{ $item['cb']['AL'] }}</td>
                                <td>{{ $item['ob']['ML'] }}</td>
                                <td>{{ $item['availed']['ML'] }}</td>
                                <td>{{ $item['cb']['ML'] }}</td>
                                <td>{{ $item['availed']['Unpaid'] }}</td>
                                <td>{{ $item['availed']['Unpaid'] }}</td>
                                <td>{{ $item['totalAvailed'] }}</td>
                                <td>{{ $item['totalBalance'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
