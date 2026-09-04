@extends('layouts.admin')

@section('page-title')
    {{ __('Student Promotion Report') }}
@endsection

@push('script-page')
    <script>
        function exportPromotionExcel() {
            var form = document.getElementById('student_promotion_report_form');
            var formData = new FormData(form);
            formData.append('export', 'excel');
            window.location.href = "{{ route('student_promotion_report') }}?" + new URLSearchParams(formData).toString();
        }

        function toggleBranchTo() {
            $('.branch-to-filter').toggle($('#promotion_type').val() === 'branch_promotion');
        }

        $(document).on('change', '#promotion_type', toggleBranchTo);
        $(document).ready(toggleBranchTo);
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Promotion Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['student_promotion_report'], 'method' => 'GET', 'id' => 'student_promotion_report_form']) }}
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('promotion_type', __('Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('promotion_type', ['promotion' => 'Promotion', 'branch_promotion' => 'Branch Promotion'], $promotionType, ['class' => 'form-control select', 'id' => 'promotion_type']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('session_from_id', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from_id', $sessions, request('session_from_id', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('session_to_id', __('Session To'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_to_id', $sessions, request('session_to_id', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('branch_from', __('Branch From'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_from', $branches, request('branch_from', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 align-items-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 branch-to-filter">
                                <div class="btn-box">
                                    {{ Form::label('branch_to', __('Branch To'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_to', $branches, request('branch_to', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('class_from', __('Class From'), ['class' => 'form-label']) }}
                                    {{ Form::select('class_from', $classesFrom, request('class_from', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('class_to', __('Class To'), ['class' => 'form-label']) }}
                                    {{ Form::select('class_to', $classesTo, request('class_to', 'all'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 mt-4 d-flex justify-content-end gap-2 align-items-center">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary" onclick="document.getElementById('student_promotion_report_form').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_promotion_report') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" type="button" onclick="exportPromotionExcel(); return false;">
                                                <i class="ti ti-file me-2"></i>Excel
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

    <div class="content" id="report-content">
        <div class="card p-4">
            <div style="width: 100%; text-align: center;">
                <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
                <p style="text-align:center; font-weight:600; font-size:1rem;">
                    {{ request('branch_from') && request('branch_from') !== 'all' ? ($branches[request('branch_from')] ?? 'Selected Branch') : 'All Branches' }}
                </p>
            </div>
            <p style="text-align:center; font-weight:900; font-size:1rem;">{{ $reportName }}</p>

            @include('studentReports.partials.student_promotion_report_table', [
                'tableClass' => 'datatable maximumHeightNew',
                'theadClass' => 'sticky-headerNew',
                'headerClass' => 'table_heads report_table',
                'params' => request()->all(),
            ])
        </div>
    </div>
@endsection
