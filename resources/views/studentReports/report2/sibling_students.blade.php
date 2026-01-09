@extends('layouts.admin')
@section('page-title')
    {{ __('Sibling Students Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

   <!-- <script>
        $(document).ready(function() {
            $('.searchbox').searchbox();
        });
    </script> -->
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['sibling_students'], 'method' => 'GET', 'id' => 'sibling_students']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex justify-content-end gap-2 align-items-center">
                    <!-- Search Button -->
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('sibling_students').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('sibling_students') }}" class="btn btn-sm btn-danger"
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
                                <form method="post" style="display: inline;">
                                    <button class="dropdown-item" type="submit" name="export" value="excel">
                                        <i class="ti ti-file me-2"></i>Excel
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form method="get" style="display: inline;">
                                    @csrf
                                    @method('GET')
                                    <input type="hidden" name="export" value="pdf">
                                    <button class="dropdown-item" type="submit" name="print" value="pdf">
                                        <i class="ti ti-download me-2"></i>Pdf
                                    </button>
                                </form>
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="text-center">
                        <p style="font-family: Edwardian Script ITC; font-size: 3.5rem; text-align: center;"><b>The Lynx
                                School</b></p>
                        <h2 class="font-bolder">Sibling Students Report</h2>
                    </div>
                    <div class="table-responsive maximumHeightNew mt-2">
                        <table class="table datatable maximumHeightNew">
                            <thead class="table_heads">
                                <tr>
                                    {{-- SR	B.SR	Roll No.	Student Name	Class	Father Name	CNIC	Mother Name	CNIC	CLASS FEE	Discount %	Monthly Fee	STATUS --}}
                                    <th>{{ __('SR') }}</th>
                                    <th>{{ __('B.SR') }}</th>
                                    <th>{{ __('Roll No.') }}</th>
                                    <th>{{ __('Student Name') }}</th>
                                    <th>{{ __('Class') }}</th>
                                    <th>{{ __('Father Name') }}</th>
                                    <th>{{ __('CNIC') }}</th>
                                    <th>{{ __('Mother Name') }}</th>
                                    <th>{{ __('CNIC') }}</th>
                                    <th>{{ __('CLASS FEE') }}</th>
                                    <th>{{ __('Discount %') }}</th>
                                    <th>{{ __('Monthly Fee') }}</th>
                                    <th>{{ __('STATUS') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedStudents as $branchId => $students)
                                    <tr class="branch-header" style="background-color:#bcbcbc;">
                                        <td colspan="13" style="font-weight: bold;">
                                            {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                                        </td>
                                    </tr>
                                    @foreach ($students as $index => $student)
                                    {{-- @if($loop->iteration == 2)
                                    @dd($student->concession,@$student->concession->policy_head)
                                    @endif --}}
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $loop->parent->index * $students->count() + $index + 1 }}</td>
                                            <td>{{ $student->roll_no ?? '' }}</td>
                                            <td>{{ $student->stdname ?? '' }}</td>
                                            <td>{{ $student->class->name ?? '-' }}</td>
                                            <td>{{ $student->fathername ?? '-' }}</td>
                                            <td>{{ $student->fathercnic ?? '-' }}</td>
                                            <td>{{ $student->mothername ?? '-' }}</td>
                                            <td>{{ $student->mothercnic ?? '-' }}</td>
                                            @php
                                                $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                                                $class_fee = \App\Models\ClassWiseFee::where('class_id', $student->class_id,)
                                                    ->where('head_id', $head->id ?? null)
                                                    ->first();
                                                $monthly_fee = \App\Models\StudentFeeStructure::where(function ($query) use ($student, $head) {
                                                    $query
                                                        ->where('reg_id', $student->id)
                                                        ->orWhere('student_id', $student->roll_no);
                                                })->where('head_id', $head->id ?? null)->first();
                                                $discount = 0;
                                                if ($student->concession) {
                                                    $discount = $student->concession->policy_head->where('head_id', $head->id)->first()->percentage ?? 0;
                                                }
                                            @endphp
                                            <td>{{ $class_fee->amount ?? '' }}</td>
                                            <td>{{ $discount ?? '' }}</td>
                                            <td>{{ $monthly_fee->amount ?? '' }}</td>
                                            <td>{{ $student->active_status == 1 ? 'Active' : 'Inactive' }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
