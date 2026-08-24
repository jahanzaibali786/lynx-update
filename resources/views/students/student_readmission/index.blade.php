@extends('layouts.admin')

@section('page-title')
    {{ __('Re-Admission Applications') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Re-Admission') }}</li>
@endsection

@section('action-btn')
    <div class="float-end readmission-action-wrap">
        <a href="{{ route('readmissionstudent.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i>{{ __('New Application') }}
        </a>
    </div>
@endsection

@push('css-page')
<style>
    .readmission-index-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 10px 26px rgba(16, 42, 67, .08);
        overflow: hidden;
    }

    .readmission-action-wrap {
        margin-bottom: 1rem;
    }

    .summary-card {
        border: 0;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(16, 42, 67, .07);
        height: auto;
        min-height: 78px;
    }

    .summary-card .card-body {
        padding: .7rem 1rem;
        min-height: 78px;
    }

    .summary-card .h3 {
        font-size: 1.35rem;
        line-height: 1.15;
        margin-top: .1rem;
    }

    .summary-card .small {
        font-size: .76rem;
        line-height: 1.2;
    }

    .summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .summary-total .summary-icon {
        background: #eff6ff;
        color: #2563eb;
    }

    .summary-pending .summary-icon {
        background: #fff7ed;
        color: #ea580c;
    }

    .summary-approved .summary-icon {
        background: #ecfdf5;
        color: #059669;
    }

    .summary-rejected .summary-icon {
        background: #fef2f2;
        color: #dc2626;
    }

    .filter-card .form-label {
        font-size: .78rem;
        color: #64748b;
        margin-bottom: .25rem;
        font-weight: 600;
    }

    .status-badge,
    .scenario-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: .36rem .68rem;
        font-size: .72rem;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.2;
    }

    .status-for-approval {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .status-approved {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .status-rejected,
    .status-rollbacked,
    .status-canceled,
    .status-cancelled {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .status-default {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .scenario-reactivation {
        background: #ecfeff;
        color: #0e7490;
        border: 1px solid #a5f3fc;
    }

    .scenario-readmission {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .scenario-re-enrollment {
        background: #f5f3ff;
        color: #6d28d9;
        border: 1px solid #ddd6fe;
    }

    .readmission-table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .readmission-table {
        width: 100%;
        min-width: 1080px;
        margin-bottom: 0;
        table-layout: auto;
    }

    .readmission-table thead th {
        vertical-align: middle;
        white-space: nowrap;
        font-size: .78rem;
        padding: .85rem .75rem;
    }

    .readmission-table tbody td {
        vertical-align: middle;
        padding: .85rem .75rem;
        border-color: #eef2f7;
    }

    .readmission-table tbody tr.pending-row > td {
        background: #fffdf9;
    }

    .readmission-table tbody tr:hover > td {
        background: #f8fafc;
    }

    .readmission-table tbody tr.pending-row:hover > td {
        background: #fff9f1;
    }

    .col-application {
        width: 115px;
        min-width: 115px;
    }

    .col-student {
        width: 220px;
        min-width: 210px;
    }

    .col-scenario {
        width: 135px;
        min-width: 135px;
    }

    .col-placement {
        min-width: 250px;
    }

    .col-date {
        width: 145px;
        min-width: 145px;
    }

    .col-status {
        width: 145px;
        min-width: 145px;
    }

    .col-actions {
        width: 250px;
        min-width: 250px;
    }

    .application-no {
        font-weight: 700;
        color: #334155;
        line-height: 1.25;
    }

    .student-name {
        font-weight: 700;
        color: #1f2937;
        line-height: 1.35;
        white-space: normal;
        word-break: break-word;
    }

    .student-roll,
    .cell-subtext {
        font-size: .76rem;
        color: #6b7280;
        line-height: 1.35;
    }

    .placement-stack {
        display: flex;
        flex-direction: column;
        gap: .4rem;
        min-width: 0;
    }

    .placement-line {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: .35rem;
        align-items: start;
        line-height: 1.3;
    }

    .placement-label {
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .placement-value {
        color: #334155;
        font-size: .82rem;
        white-space: normal;
        word-break: break-word;
    }

    .placement-value.target {
        font-weight: 600;
        color: #0f172a;
    }

    .approval-note {
        font-size: .72rem;
        color: #9a3412;
        margin-top: .35rem;
        line-height: 1.25;
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
        flex-wrap: wrap;
    }

    .action-group .btn {
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-width: 1px !important;
        font-weight: 600;
    }

    /* Fixed action colors.
       These do not depend on Bootstrap/theme outline colors. */
    .btn-action-preview,
    .btn-action-preview:hover,
    .btn-action-preview:focus,
    .btn-action-preview:active {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .btn-action-challan,
    .btn-action-challan:hover,
    .btn-action-challan:focus,
    .btn-action-challan:active {
        background: #0f766e !important;
        border-color: #0f766e !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .btn-action-approve,
    .btn-action-approve:hover,
    .btn-action-approve:focus,
    .btn-action-approve:active {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .btn-action-reject,
    .btn-action-reject:hover,
    .btn-action-reject:focus,
    .btn-action-reject:active {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .btn-clear-filter,
    .btn-clear-filter:hover,
    .btn-clear-filter:focus,
    .btn-clear-filter:active {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .btn-action-preview i,
    .btn-action-challan i,
    .btn-action-approve i,
    .btn-action-reject i,
    .btn-clear-filter i {
        color: #ffffff !important;
    }

    .action-group form {
        margin: 0;
    }

    .challan-link {
        white-space: nowrap;
    }

    .reject-reason-help {
        font-size: .8rem;
        color: #6b7280;
    }

    @media (min-width: 1200px) {
        .readmission-table th:last-child,
        .readmission-table td:last-child {
            position: sticky;
            right: 0;
            z-index: 2;
            background: #fff;
            box-shadow: -10px 0 18px -18px rgba(15, 23, 42, .6);
        }

        .readmission-table thead th:last-child {
            z-index: 3;
        }

        .readmission-table tbody tr.pending-row td:last-child {
            background: #fffdf9;
        }

        .readmission-table tbody tr:hover td:last-child {
            background: #f8fafc;
        }

        .readmission-table tbody tr.pending-row:hover td:last-child {
            background: #fff9f1;
        }
    }

    @media (max-width: 767.98px) {
        .summary-card .card-body {
            padding: .7rem .9rem;
        }

        .readmission-index-card .card-header {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .readmission-table {
            min-width: 980px;
        }

        .col-actions {
            min-width: 220px;
            width: 220px;
        }
    }
</style>
@endpush

@section('content')
@php
    $isCompany = Auth::user()->type === 'company';

    $displayDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d-F-Y');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $statusClass = function ($status) {
        $normalized = strtolower(trim((string) $status));

        if ($normalized === 'for approval') return 'status-for-approval';
        if ($normalized === 'approved') return 'status-approved';
        if ($normalized === 'rejected') return 'status-rejected';
        if ($normalized === 'rollbacked') return 'status-rollbacked';
        if ($normalized === 'canceled') return 'status-canceled';
        if ($normalized === 'cancelled') return 'status-cancelled';

        return 'status-default';
    };

    $scenarioClass = function ($flowType) {
        if ($flowType === 'reactivation') return 'scenario-reactivation';
        if ($flowType === 're_enrollment') return 'scenario-re-enrollment';

        return 'scenario-readmission';
    };

    $scenarioLabel = function ($flowType) {
        if ($flowType === 'reactivation') return 'Reactivation';
        if ($flowType === 're_enrollment') return 'Re-enrollment';

        return 'Readmission';
    };
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card summary-card summary-total">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">{{ __('Total Applications') }}</div>
                    <div class="h3 mb-0">{{ $summary['total'] ?? 0 }}</div>
                </div>
                <span class="summary-icon"><i class="ti ti-files"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card summary-card summary-pending">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">{{ __('For Approval') }}</div>
                    <div class="h3 mb-0">{{ $summary['for_approval'] ?? 0 }}</div>
                </div>
                <span class="summary-icon"><i class="ti ti-clock-hour-4"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card summary-card summary-approved">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">{{ __('Approved') }}</div>
                    <div class="h3 mb-0">{{ $summary['approved'] ?? 0 }}</div>
                </div>
                <span class="summary-icon"><i class="ti ti-circle-check"></i></span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card summary-card summary-rejected">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">{{ __('Rejected') }}</div>
                    <div class="h3 mb-0">{{ $summary['rejected'] ?? 0 }}</div>
                </div>
                <span class="summary-icon"><i class="ti ti-circle-x"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="card readmission-index-card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('readmissionstudent.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-control">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('Scenario') }}</label>
                    <select name="flow_type" class="form-control">
                        @foreach($flowOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('flow_type') === $value ? 'selected' : '' }}>
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('From Date') }}</label>
                    <input type="date"
                           name="from_date"
                           value="{{ request('from_date') }}"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ __('To Date') }}</label>
                    <input type="date"
                           name="to_date"
                           value="{{ request('to_date') }}"
                           class="form-control">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ti ti-filter me-1"></i>{{ __('Filter') }}
                    </button>
                    <a href="{{ route('readmissionstudent.index') }}"
                       class="btn btn-clear-filter"
                       title="{{ __('Clear Filters') }}">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card readmission-index-card">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">{{ __('Applications') }}</h5>
                <small class="text-muted">
                    @if($isCompany)
                        {{ __('Review branch applications and approve or reject snapshotted requests.') }}
                    @else
                        {{ __('Track your submitted reactivation, readmission and re-enrollment applications.') }}
                    @endif
                </small>
            </div>
            <span class="badge bg-light text-dark border">
                {{ $studenttransfer->count() }} {{ __('record(s)') }}
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="readmission-table-wrap">
            <table class="table table-hover readmission-table">
                <thead class="table_heads">
                    <tr>
                        <th class="col-application">{{ __('Application') }}</th>
                        <th class="col-student">{{ __('Student') }}</th>
                        <th class="col-scenario">{{ __('Scenario') }}</th>
                        <th class="col-placement">{{ __('Placement') }}</th>
                        <th class="col-date">{{ __('Date') }}</th>
                        <th class="col-status">{{ __('Status') }}</th>
                        <th class="col-actions text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($studenttransfer as $item)
                        @php
                            $snapshot = [];

                            if (!empty($item->snapshot)) {
                                if (is_array($item->snapshot)) {
                                    $snapshot = $item->snapshot;
                                } else {
                                    $decoded = json_decode($item->snapshot, true);
                                    $snapshot = is_array($decoded) ? $decoded : [];
                                }
                            }

                            $studentSnapshot = $snapshot['student'] ?? [];
                            $flowSnapshot = $snapshot['flow'] ?? [];
                            $source = $snapshot['source_placement'] ?? [];
                            $target = $snapshot['target_placement'] ?? [];
                            $requestSnapshot = $snapshot['request'] ?? [];

                            $flowType = $flowSnapshot['type'] ?? $item->flow_type ?? 'readmission';
                            $status = strtolower(trim((string) $item->status));

                            $rollNo = $studentSnapshot['roll_no']
                                ?? optional($item->student)->roll_no
                                ?? $item->student_id;

                            $studentName = $studentSnapshot['student_name']
                                ?? optional($item->student)->stdname
                                ?? '-';

                            $fatherName = $studentSnapshot['father_name']
                                ?? optional($item->student)->fathername
                                ?? null;

                            $sourceBranch = $source['branch'] ?? '-';
                            $sourceClass = $source['class'] ?? '-';
                            $sourceSession = $source['session'] ?? '-';

                            $targetBranch = $target['branch'] ?? optional($item->branch)->name ?? '-';
                            $targetClass = $target['class'] ?? optional($item->class)->name ?? '-';
                            $targetSession = $target['session'] ?? optional($item->session)->year ?? '-';

                            $applicationDate = $requestSnapshot['readmission_date']
                                ?? $item->readmission_date;

                            $hasChallan = !empty($item->challan_id);

                            $sourceText = trim(
                                implode(' / ', array_filter([
                                    $sourceBranch,
                                    $sourceClass,
                                    $sourceSession,
                                ], fn ($value) => !empty($value) && $value !== '-'))
                            );

                            $targetText = trim(
                                implode(' / ', array_filter([
                                    $targetBranch,
                                    $targetClass,
                                    $targetSession,
                                ], fn ($value) => !empty($value) && $value !== '-'))
                            );
                        @endphp

                        <tr class="{{ $status === 'for approval' ? 'pending-row' : '' }}">
                            <td class="col-application">
                                <div class="application-no">
                                    #RA-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="cell-subtext mt-1">
                                    {{ __('ID') }}: {{ $item->id }}
                                </div>
                            </td>

                            <td class="col-student">
                                <div class="student-name">
                                    {{ $rollNo }} - {{ $studentName }}
                                </div>

                                @if($fatherName)
                                    <div class="student-roll mt-1">
                                        {{ __('S/D/O') }} {{ $fatherName }}
                                    </div>
                                @endif
                            </td>

                            <td class="col-scenario">
                                <span class="scenario-badge {{ $scenarioClass($flowType) }}">
                                    {{ __($scenarioLabel($flowType)) }}
                                </span>

                                @if($flowType === 'reactivation')
                                    <div class="cell-subtext mt-1">
                                        {{ __('No approval required') }}
                                    </div>
                                @endif
                            </td>

                            <td class="col-placement">
                                <div class="placement-stack">
                                    @if(!empty($snapshot))
                                        <div class="placement-line">
                                            <span class="placement-label">{{ __('Current') }}</span>
                                            <span class="placement-value">
                                                {{ $sourceText !== '' ? $sourceText : '-' }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="placement-line">
                                        <span class="placement-label">{{ __('Target') }}</span>
                                        <span class="placement-value target">
                                            {{ $targetText !== '' ? $targetText : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="col-date">
                                <strong>{{ $displayDate($applicationDate) }}</strong>

                                @if(!empty($snapshot['captured_at']))
                                    <div class="cell-subtext mt-1">
                                        {{ __('Submitted') }}:
                                        {{ $displayDate($snapshot['captured_at']) }}
                                    </div>
                                @endif
                            </td>

                            <td class="col-status">
                                <span class="status-badge {{ $statusClass($status) }}">
                                    {{ ucwords($status ?: '-') }}
                                </span>

                                @if($status === 'for approval' && $isCompany)
                                    <div class="approval-note">
                                        {{ __('Company decision required') }}
                                    </div>
                                @endif
                            </td>

                            <td class="col-actions">
                                <div class="action-group">
                                    <a href="{{ route('readmissionstudent.show', $item->id) }}"
                                       class="btn btn-sm btn-action-preview"
                                       title="{{ __('Preview') }}">
                                        <i class="ti ti-eye me-1"></i>{{ __('Preview') }}
                                    </a>

                                    {{-- Readmission:
                                         show ONLY the normal challan-view action.
                                         Do not render any separate Installment/Installment Challan action. --}}
                                    @if($flowType === 'readmission')
                                        @if($hasChallan)
                                            <a href="{{ route('installmentview', $item->challan_id) }}"
                                               class="btn btn-sm btn-action-challan challan-link"
                                               title="{{ __('View Challan') }}">
                                                <i class="ti ti-receipt me-1"></i>{{ __('View Challan') }}
                                            </a>
                                        @endif
                                    @else
                                        @if($hasChallan)
                                            <a href="{{ route('installmentview', $item->challan_id) }}"
                                               class="btn btn-sm btn-action-challan challan-link"
                                               title="{{ __('View Challan') }}">
                                                <i class="ti ti-receipt me-1"></i>{{ __('Challan') }}
                                            </a>
                                        @endif
                                    @endif

                                    @if($isCompany && $status === 'for approval')
                                        <form method="POST"
                                              action="{{ route('readmissionstudent.approve', $item->id) }}"
                                              class="approve-application-form">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-action-approve"
                                                    title="{{ __('Approve') }}">
                                                <i class="ti ti-check me-1"></i>{{ __('Approve') }}
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="btn btn-sm btn-action-reject reject-application-btn"
                                                data-id="{{ $item->id }}"
                                                data-application="#RA-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}"
                                                data-student="{{ $rollNo }} - {{ $studentName }}"
                                                title="{{ __('Reject') }}">
                                            <i class="ti ti-x me-1"></i>{{ __('Reject') }}
                                        </button>
                                    @elseif(!$hasChallan && $status === 'for approval')
                                        <span class="cell-subtext">
                                            {{ __('Challan after approval') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="ti ti-file-off fs-3"></i>
                                </div>
                                <strong>{{ __('No readmission applications found.') }}</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($isCompany)
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="rejectApplicationForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Reject Application') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong id="reject_application_no"></strong>
                        <div id="reject_student_name" class="small mt-1"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label">
                            {{ __('Rejection Reason') }} <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason"
                                  class="form-control"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="{{ __('Enter the reason this application is being rejected...') }}"></textarea>
                        <div class="reject-reason-help mt-1">
                            {{ __('This reason is saved with the company decision audit snapshot.') }}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>{{ __('Reject Application') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('script-page')
<script>
(function () {
    const approveForms = document.querySelectorAll('.approve-application-form');

    approveForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = window.confirm(
                'Approve this application? Enrollment, fee structure and challans will be applied from the saved snapshot.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    const modalElement = document.getElementById('rejectApplicationModal');
    const rejectForm = document.getElementById('rejectApplicationForm');

    if (!modalElement || !rejectForm) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const baseUrl = @json(url('readmissionstudent'));

    document.querySelectorAll('.reject-application-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            rejectForm.action = baseUrl + '/' + id + '/reject';

            document.getElementById('reject_application_no').textContent =
                this.dataset.application || '';

            document.getElementById('reject_student_name').textContent =
                this.dataset.student || '';

            rejectForm.querySelector('textarea[name="rejection_reason"]').value = '';

            modal.show();
        });
    });
})();
</script>
@endpush