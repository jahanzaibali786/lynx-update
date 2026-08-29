@extends('layouts.admin')
@section('page-title')
    {{ __('Bulk Tuition Increment') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student-promotion.index') }}">{{ __('Student Promotions') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Tuition Increment') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0">{{ __('Bulk Tuition Increment') }}</h5>
                            <small class="text-muted">{{ __('Applies to all active Shifa panel students in your scope.') }}</small>
                        </div>
                        <a href="{{ route('student-promotion.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Active Shifa Students') }}</div>
                                <div class="fs-2 fw-bold">{{ $studentCount }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Tuition Fee Head') }}</div>
                                <div class="fw-semibold">{{ $tuitionHead->fee_head ?? __('Not Found') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small">{{ __('Bulk Action') }}</div>
                                <div class="fw-semibold">{{ __('Raises the current Tuition Fee structure by a percentage.') }}</div>
                            </div>
                        </div>
                    </div>

                    {{ Form::open(['route' => ['student-promotion.bulk-tuition.store'], 'method' => 'POST']) }}
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            {{ Form::label('tuition_increment_percentage', __('Tuition Fee Increment %'), ['class' => 'form-label']) }}
                            <input type="number" name="tuition_increment_percentage" id="tuition_increment_percentage" class="form-control" min="1" step="1" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Apply to All Shifa Students') }}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
