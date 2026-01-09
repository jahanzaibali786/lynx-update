@extends('layouts.admin')

@section('page-title')
    {{ __('Staff Child Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Staff Child Report') }}</li>
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
        // New printPdf function
        function printPdf() {
            const form = document.getElementById('staffChildReport');
            let url = new URL(form.action, window.location.origin);
            let params = new URLSearchParams(new FormData(form));
            params.set('print', 'pdf');
            url.search = params.toString();
            window.open(url.toString(), '_blank');
        }

        function submitWithPrintFlag(type = "pdf") {
            const form = $('#staffChildReport');
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
    {{-- @if (\Auth::user()->type == 'company') --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['staffChildReport'], 'method' => 'GET', 'id' => 'staffChildReport']) }}
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
                                <input type="hidden" name="is_print" id="is_print" value="0">
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('staffChildReport').submit(); return false;"
                                         data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('staffChildReport') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
                                    {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                    class="btn-inner--icon">Print</span></a> --}}
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
            <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">
                @isset($_GET['branches'])
                    {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
                @endisset
            </p>
            <div class="table-responsive mt-4">
                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr#</th>
                            <th>Branch</th>
                            <th>Employee</th>
                            <th>Emp. Service Period</th>
                            <th>Designation</th>
                            <th>Child Roll No</th>
                            <th>Child Branch</th>
                            <th>Child Name</th>
                            <th>Child Class</th>
                            <th>D.O.A</th>
                            <th>Tuition Fee </th>
                            <th>Child Concession % </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $key => $child)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ @$child->employee ? @$child->employee->userbranch->name : '' }}</td>
                                <td>{{ @$child->employee ? @$child->employee->name : '' }}</td>
                                <td>{{ @$child->employee ? @$child->employee->getEmployeeTenure(@$child->employee->id) : '' }}
                                </td>
                                <td>{{ @$child->employee ? @$child->employee->designation->name : '' }}</td>
                                <td>{{ @$child->student ? @$child->student->roll_no : '' }}</td>
                                <td>{{ @$child->student ? @$child->student->branches->name : '' }}</td>
                                <td>{{ @$child->student ? @$child->student->stdname : '' }}</td>
                                <td>{{ @$child->student ? @$child->student->class->name : '' }}</td>
                                <td>{{ @$child->enrollment ? @$child->enrollment->adm_date : '' }}</td>
                                @php
                                   

                                    // 1. Get Tuition Fee Head
                                    $tutionfeehead = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();

                                    // 2. Get Student Fee Structure (safe access to student and fee head)
                                    $studentId = optional(optional($child)->student)->id;
                                    $headId = optional($tutionfeehead)->id;
                                    $stdfeestr = \App\Models\StudentFeeStructure::where('reg_id', $studentId)
                                        ->where('head_id', $headId)
                                        ->first();

                                    // 3. Get Concession for student
                                    $concession = \App\Models\Concession::with('student', 'class')
                                        ->where('student_id', $studentId)
                                        ->first();

                                    // Initialize concession policy and related values
                                    $concession_policy = null;
                                    $concession_heads = collect();
                                    $concession_amt = 0;
                                    $totalpercentage = 0;

                                    if ($concession && $concession->concession_id) {
                                        // 4. Fetch concession policy only if the concession exists
                                        $concession_policy = \App\Models\ConcessionPolicy::with([
                                            'concession',
                                            'concession.student',
                                            'concession.student.enrollment',
                                            'concession.class',
                                            'concession.student.session',
                                        ])->find($concession->concession_id);

                                        // 5. Get Concession Policy Heads
                                        if ($concession_policy) {
                                            $concession_heads = \App\Models\ConcessionPolicyHead::where(
                                                'concession_id',
                                                $concession_policy->id,
                                            )
                                                ->where('percentage', '!=', 0)
                                                ->get();
                                        }

                                        // 6. Calculate Total Percentage
                                        foreach ($concession_heads as $dta) {
                                            $totalpercentage += $dta->percentage;
                                        }
                                    }
                                @endphp

                                <td>{{ @$stdfeestr ? @$stdfeestr->amount : '' }}</td>
                                <td>{{ @$totalpercentage }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No Data Available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
