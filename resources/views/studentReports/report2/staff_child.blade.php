@extends('layouts.admin')
@section('page-title')
    {{ __('Staff Child List') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
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
                    console.log(result);
                    if (result.status == 'success') {
                        $('#employee_id').empty();
                        $('#employee_id').append($('<option>', {
                            value: '',
                            text: 'Select Staff'
                        }));
                        for (var j = 0; j < result.employee.length; j++) {
                            var cls = result.employee[j];
                            $('#employee_id').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}
                }
            });
        }
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['staff_child'], 'method' => 'GET', 'id' => 'staff_child']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Student Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('empbranches', __('Emp. Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('empbranches', $branches, request()->get('empbranches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchemployees(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}
                                    {{ Form::select('staff', $employees, request()->get('staff'), ['class' => 'form-control select custom-select', 'id' => 'employee_id']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                    <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('staff_child').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('staff_child') }}" class="btn btn-sm btn-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
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
                        <th>Br Sr#</th>
                        <th>Branch</th>
                        <th>Emp No</th>
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
                    @foreach ($groupedStudents as $branchId => $students)
                        <tr class="branch-header" style="background-color:#bcbcbc;">
                            <td colspan="14" style="font-weight: bold;">
                                {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                            </td>
                        </tr>
                        @foreach ($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $loop->parent->index * $students->count() + $index + 1 }}</td>
                                <td>{{ $branches[$student->employee->owned_by] ?? '' }}</td>
                                <td>{{ $student->emp_id ?? '' }}</td>
                                <td>{{ @$student->employee->name ?? '' }}</td>
                                <td>{{ @$student->employee->getEmployeeTenure(@$student->employee->id) ?? '' }}</td>
                                <td>{{ @$student->employee->designation->name ?? '' }}</td>
                                <td>{{ @$student->student->roll_no ?? '' }}</td>
                                <td>{{ @$student->student->branches->name ?? '' }}</td>
                                <td>{{ @$student->student->stdname ?? '' }}</td>
                                <td>{{ @$student->student->class->name ?? '' }}</td>
                                <td>{{ date('d-M-Y', strtotime(@$student->enrollment->adm_date)) ?? '' }}</td>
                                @php
                                    $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                                    $monthly_fee = \App\Models\StudentFeeStructure::where(function ($query) use ($student, $head) {
                                    $query
                                        ->where('reg_id', $student->id)
                                        ->orWhere('student_id', $student->roll_no);
                                    })->where('head_id', $head->id ?? null)->first();
                                @endphp
                                <td>{{ $monthly_fee->amount ?? '' }}</td>
                                <td>{{ @$student->student->concession->policy_head->where('head_id', $head->id)->first()->percentage ?? 0 }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
