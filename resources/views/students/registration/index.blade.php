@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Registrations') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Registrations') }}</li>
@endsection
@section('action-btn')



    <div class="float-end">
        {{-- @can('create session') --}}
        <a href="{{ route('registration.create') }}" class="btn btn-sm btn-outline-primary" 
            data-bs-title="{{ __('New Registration') }}">
            <span class="btn-inner--icon"> Create</span>
        </a>
        {{-- @endcan --}}
    </div>

@endsection
@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}

        <style>
        #paddingModal{
        display: none;
        }
    </style>
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['registration.index'], 'method' => 'GET', 'id' => 'registration_submit']) }}
                            <div class="row d-flex justify-content-start ">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 cols-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('classes', __('Class'), ['class' => 'form-label']) }}
                                        {{Form::select('classes',$classes,request()->get('classes', ''), ['class' => 'form-control select', 'id' => 'class_select'])}}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('sessions', __('Session'), ['class' => 'form-label']) }}
                                        {{Form::select('sessions',$sessions,request()->get('sessions', ''), ['class' => 'form-control select', 'id' => 'session_select'])}}
                                        </div>
                                </div>
                                {{-- //gender  --}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                                        {{ Form::select('gender', ['' => 'Select Gender', 'Male' => 'Male', 'Female' => 'Female'], isset($_GET['gender']) ? $_GET['gender'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                {{-- //sort by alphabatically,gender,date of admission --}}
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('sort', __('Sort'), ['class' => 'form-label']) }}
                                        {{ Form::select('sort', ['' => 'Select Sort', 'asc' => 'Ascending', 'desc' => 'Descending', 'gender' => 'Gender'], isset($_GET['sort']) ? $_GET['sort'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                        {{ Form::select('type', ['Registered' => 'Registered', 'Enrolled' => 'Enrolled'], isset($_GET['type']) ? $_GET['type'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('search', __('Search'), ['class' => 'form-label']) }}
                                        {{ Form::text('search', isset($_GET['search']) ? $_GET['search'] : '', ['class' => 'form-control', 'placeholder' => __('Name or Reg. No')]) }}
                                    </div>
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('registration_submit').submit(); return false;"
                                          title="Search Filter"
                                        data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('registration.index') }}" class="btn btn-sm btn-outline-danger"
                                          title="Clear Filter"
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
    <div class="col-12 table-responsive mt-1">
        <table class="datatable">
            <thead class="table_heads">
                <tr class="">
                    <th>{{ __('#') }}</th>
                    <th>{{ __('Reg No.') }}</th>
                    <th>{{ __('Reg Date') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Father Name') }}</th>
                    <th>{{ __('Cell No.') }}</th>
                    {{-- <th>{{__('Email')}}</th> --}}
                    <th>{{ __('Session') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('DOB') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registrations as $registration)
                    <tr style="  border-radius: 10px !important;">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $registration->reg_no }}</td>
                        <td>{{ $registration->regdate == '0000-00-00' || !$registration->regdate ? '' : date('d-M-Y', strtotime($registration->regdate)) }}</td>
                        <td>{{ $registration->stdname }}</td>
                        <td>{{ $registration->fathername }}</td>
                        <td>{{ $registration->fatherphone }}</td>
                        {{-- <td style="white-space: normal !important;">{{ $registration->email }}</td> --}}
                        <td>{{ !empty(@$registration->session) ? @$registration->session->year : '-' }}</td>
                        <td>{{ !empty(@$registration->class) ? @$registration->class->name : '-' }}</td>
                        <td>{{ $registration->dob }}</td>
                        <td>{{ $registration->gender }}</td>
                        <td>{{ $registration->student_status }}</td>
                        <td>
                            <div class="action-btn ms-2">
                                {{-- <a href="{{ route('registration.edit', $registration->id) }}"
                        class="mx-1 btn btn-sm align-items-center btn-outline-primary"  data-bs-title="{{__('Edit')}}"
                        data-bs-title="{{__('Edit')}}"><span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a> --}}
                                <a href="{{ route('reg.receipt', $registration->id) }}"
                                    class="mx-1 btn btn-sm align-items-center btn-outline-primary" 
                                    data-bs-title="{{ __('Registration Receipt') }}"
                                    data-bs-title="{{ __('Registration Receipt') }}"><span class="btn-inner--icon"><i
                                            class="ti ti-receipt"></i></span></a>
                                <a href="{{ route('registration.show', $registration->id) }}"
                                    class="mx-1 btn btn-sm align-items-center btn-outline-primary" 
                                    data-bs-title="{{ __('Show') }}" data-bs-title="{{ __('Show') }}"><span
                                        class="btn-inner--icon"><i class="ti ti-eye"></i></span></a>
                                {{-- <form action="{{ route('enrollment.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="registration_id" value="{{ $registration->id }}">
                            @if ($registration->student_status != 'Enrolled')
                                <button type="submit" class="mx-1 btn btn-sm align-items-center btn-outline-primary"  data-bs-title="{{ __('Enroll') }}">
                                    <span class="btn-inner--icon"><i class="fa-solid fa-user-plus"></i></span>
                                </button>
                            @endif
                        </form> --}}
                                @if ($registration->student_status == 'Enrolled')
                                    <a href="{{ route('admission.order', $registration->id) }}"
                                        class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                         data-bs-title="{{ __('Admission Order') }}"
                                        data-bs-title="{{ __('Admission Order') }}"><span class="btn-inner--icon"><i
                                                class="ti ti-receipt"></i></span></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- <div class="pagination page-item">
    <ul>
        @if ($registrations->onFirstPage())
            <li class="disabled">&laquo;</li>
        @else
            <li>
                <a href="{{ $registrations->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a>
            </li>
        @endif
        @for ($page = 1; $page <= $registrations->lastPage(); $page++)
            <li class="{{ $page == $registrations->currentPage() ? 'active' : '' }}">
                <a href="{{ $registrations->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
        @endfor
        @if ($registrations->hasMorePages())
            <li>
                <a href="{{ $registrations->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a>
            </li>
        @else
            <li class="disabled">&raquo;</li>
        @endif
    </ul>
</div> --}}
    {{-- @if ($registrations->hasPages())
        <div class="pagination">
            <ul>
                @if ($registrations->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $registrations->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($registrations->currentPage() > 1)
                    <li><a href="{{ $registrations->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $registrations->currentPage();
                    $lastPage = $registrations->lastPage();
                    $startPage = max(1, $currentPage - 4);
                    $endPage = min($lastPage, $currentPage + 5);
                    if ($endPage - $startPage < 9) {
                        if ($currentPage < $lastPage - 9) {
                            $endPage = $startPage + 9;
                        } else {
                            $startPage = max(1, $lastPage - 9);
                        }
                    }
                @endphp
                @for ($page = $startPage; $page <= $endPage; $page++)
                    <li class="{{ $page == $registrations->currentPage() ? 'active' : '' }}">
                        <a href="{{ $registrations->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($registrations->hasMorePages())
                    <li><a href="{{ $registrations->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($registrations->currentPage() < $registrations->lastPage())
                    <li><a
                            href="{{ $registrations->appends(request()->query())->url($registrations->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}
@endsection
