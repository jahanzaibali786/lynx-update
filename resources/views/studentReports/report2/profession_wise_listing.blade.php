@extends('layouts.admin')

@section('page-title')
    {{ __('Profession Wise Listing') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['profession_wise_listing'], 'method' => 'GET', 'id' => 'profession_wise_listing']) }}

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
                                    onclick="document.getElementById('profession_wise_listing').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('profession_wise_listing') }}" class="btn btn-sm btn-danger"
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
    <div class="card mt-2 p-4">

        <div class="content" id="report-content">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b>
            </p>
            <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>

            <p style="text-align:center; font-weight:600; font-size:1rem;">
                {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}</p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">Profession Wise Listing</p>
            <div class="table-responsive maximumHeightNew">
                <table class="datatable maximumHeightNew">

                    <thead class="table_heads sticky-headerNew">
                        <tr>
                            <th>{{ __('Sr No.') }}</th>
                            <th>{{ __('B Sr No.') }}</th>
                            <th>{{ __('Reg. #') }}</th>
                            <th>{{ __('Student Name') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Father Name') }}</th>
                            <th>{{ __('Father Profession') }}</th>
                            <th>{{ __('Contact#') }}</th>
                            <th>{{ __('Mother Name') }}</th>
                            <th>{{ __('Mother Profession') }}</th>
                            <th>{{ __('Contact#') }}</th>
                            <th>{{ __('Guardian Name') }}</th>
                            <th>{{ __('Guardian Profession') }}</th>
                            <th>{{ __('Contact#') }}</th>
                            <th>{{ __('Home Address') }}</th>
                            <th>{{ __('Adm. Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $globalIndex = 1; @endphp
                        @foreach ($groupedStudents as $branchId => $professions)
                            <tr class="branch-header" style="background-color:#bcbcbc;">
                                <td colspan="16" style="font-weight: bold;">
                                    {{ @$branches[$branchId] ?? 'Branch Not Specified' }}
                                </td>
                            </tr>

                            @foreach ($professions as $students)
                                @foreach ($students as $index => $student)
                                    <tr>
                                        <td>{{ $globalIndex }}</td>
                                        <td>{{ $loop->parent->index * $students->count() + $index + 1 }}</td>
                                        <td>{{ $student->enrollId }}</td>
                                        <td>{{ @$student->StudentRegistration->stdname }}</td>
                                        <td>{{ @$student->class->name }}</td>
                                        <td>{{ @$student->StudentRegistration->fathername }}</td>
                                        <td>{{ @$student->StudentRegistration->fatherprofession }}</td>
                                        <td>{{ @$student->StudentRegistration->fatherphone }}</td>
                                        <td>{{ @$student->StudentRegistration->mothername }}</td>
                                        <td>{{ @$student->StudentRegistration->motherprofession }}</td>
                                        <td>{{ @$student->StudentRegistration->motherphone }}</td>
                                        <td>{{ @$student->StudentRegistration->guardianname }}</td>
                                        <td>{{ @$student->StudentRegistration->guardianprofession }}</td>
                                        <td>{{ @$student->StudentRegistration->guardianphone }}</td>
                                        <td>{{ @$student->StudentRegistration->address }}</td>
                                        <td>{{ @$student->active_status == 1 ? 'Active' : 'WDR' }}</td>
                                    </tr>
                                    @php $globalIndex++; @endphp
                                @endforeach
                            @endforeach
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
