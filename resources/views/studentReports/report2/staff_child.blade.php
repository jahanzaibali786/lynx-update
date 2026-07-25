@extends('layouts.admin')
@section('page-title')
    {{ __('Staff Child List') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Staff Child List') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['staff_child'], 'method' => 'GET', 'id' => 'staff_child']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('base_on', __('Search By'), ['class' => 'form-label']) }}
                                    {{ Form::select('base_on', ['cnic' => 'CNIC', 'staff_child' => 'Staff Child (Regtype)'], request()->get('base_on', 'cnic'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('employee_status', __('Employee Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_status', ['active' => 'Active Employee', 'resigned' => 'Resigned Employee'], request()->get('employee_status', 'active'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('staff_child').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('staff_child') }}" class="btn btn-sm btn-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown">
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
    @if(!empty($groupedStudents) && $groupedStudents->isNotEmpty())
    <div class="card mt-2 p-4">
        <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
        <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> Staff Child Report </b></p>
        <p style="text-align:center; font-weight:900; font-size:1rem;">
            @isset($_GET['branches'])
                {{ !empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches' }}
            @endisset
        </p>
        <div class="table-responsive maximumHeightNew mt-2">
            <table class="datatable maximumHeightNew">
                <thead class="table_heads">
                    <tr>
                        <th>Sr#</th>
                        <th>Bsr#</th>
                        <th>Branch</th>
                        <th>Emp No</th>
                        <th>Emp Branch</th>
                        <th>Emp Name</th>
                        <th>Roll No</th>
                        <th>Child Name</th>
                        <th>Child Branch</th>
                        <th>Child Class</th>
                        <th>D.O.A</th>
                        <th>Tuition Fee</th>
                        <th>Concession</th>
                        <th>Payable</th>
                        <th>Discount Policy</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sr = 1;
                        $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                    @endphp
                    @foreach ($groupedStudents as $branchId => $students)
                        <tr class="branch-header" style="background-color:#bcbcbc;">
                            <td colspan="15" style="font-weight: bold; text-align: left;">
                                {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                            </td>
                        </tr>
                        @php $bsr = 1; @endphp
                        @foreach ($students as $student)
                             @php
                                $headId = $head->id ?? 0;

                                $feeStructure = $student->fee_structure
                                    ->where('head_id', $headId)
                                    ->first();

                                $amount = (float) ($feeStructure->amount ?? 0);

                                $discountPct = 0;
                                $policyName = '';

                                $concession = \App\Models\Concession::with('concession')->where('student_id', $student->id)
                                    ->where('end_date', '>=', date('Y-m-d'))
                                    ->orderBy('id', 'desc')
                                    ->where('active_status', '!=', 0)
                                    ->where('status', 'Approved')
                                    ->first();
                                if (!$concession) {
                                    $concession = \App\Models\Concession::with('concession')->where('student_id', $student->id)
                                        ->orderBy('id', 'desc')
                                        ->whereNull('end_date')
                                        ->where('active_status', '!=', 0)
                                        ->where('status', 'Approved')
                                        ->first();
                                }
                                if ($concession) {
                                    $policyHead = $concession
                                                    ->concession
                                                    ->policy_head()
                                                    ->where('head_id', $headId)
                                                    ->latest('id')
                                                    ->first();

                                    $discountPct = (float) ($policyHead->percentage ?? 0);
                                    $discAmnt = ($amount * $discountPct) / 100;
                                    $policyName = $concession->concession->title ?? '';
                                }

                                $payable = $amount - ($amount * $discountPct / 100);

                                $employee = $student->employee ?? null;
                            @endphp
                            <tr>
                                <td>{{ $sr++ }}</td>
                                <td>{{ $bsr++ }}</td>
                                <td>{{ $branches[$branchId] ?? '' }}</td>
                                <td>{{ $employee->employee_id ?? '' }}</td>
                                <td>{{ !empty($employee->owned_by) ? ($branchLookup[$employee->owned_by] ?? '') : '' }}</td>
                                <td>{{ $employee->name ?? '' }}</td>
                                <td>{{ $student->roll_no ?? '' }}</td>
                                <td>{{ $student->stdname ?? '' }}</td>
                                <td>{{ $student->branches->name ?? '' }}</td>
                                <td>{{ $student->class->name ?? '' }}</td>
                                <td>{{ $student->enrollment ? date('d-M-Y', strtotime($student->enrollment->adm_date)) : '' }}</td>
                                <td>{{ number_format($amount, 0) }}</td>
                                <td>{{ number_format($discAmnt, 0) }}</td>
                                <td>{{ number_format($payable, 0) }}</td>
                                <td>{{ $policyName }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif(request()->has('branches') || request()->has('base_on'))
    <div class="card mt-2 p-4">
        <p style="text-align:center;">No records found.</p>
    </div>
    @endif
@endsection