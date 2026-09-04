@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee Salary Proporal') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Salary Proporal') }}</li>
@endsection

@section('action-btn')
    <div class="col text-end">
        <a href="#"
           data-url="{{ route('employee-salary-proporal.bulk.create') }}"
           data-size="modal-fullscreen"
           data-ajax-popup="true"
           data-bs-title="{{ __('Bulk Proposals') }}"
           class="apply-btn btn mx-1 btn-sm btn-outline-success">
            <span class="btn-inner--icon">Bulk Create</span>
        </a>

        <a href="#"
           data-url="{{ route('employee-salary-proporal.create') }}"
           data-size="xl"
           data-ajax-popup="true"
           data-bs-title="{{ __('Create Salary Proposal') }}"
           class="apply-btn btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Single Create</span>
        </a>

        @if (\Auth::user()->type == 'company')
            <form id="spf_bulk_action_form"
                  action="{{ route('employee-salary-proporal.bulk.approve') }}"
                  method="POST"
                  class="d-inline">
                @csrf
                <button type="submit" class="btn mx-1 btn-sm btn-success">
                    <i class="ti ti-check me-1"></i>{{ __('Approve Selected') }}
                </button>
            </form>
        @else
            <form id="spf_bulk_action_form"
                  action="{{ route('employee-salary-proporal.bulk.sendForApproval') }}"
                  method="POST"
                  class="d-inline">
                @csrf
                <button type="submit" class="btn mx-1 btn-sm btn-success">
                    <i class="fa fa-user me-1"></i>{{ __('Send Selected For Approval') }}
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')

<style>
    .salary-proposal-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 104px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: .01em;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .salary-proposal-status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        opacity: .9;
        flex: 0 0 6px;
    }

    .salary-proposal-status-badge.status-draft {
        color: #475569;
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .salary-proposal-status-badge.status-pending {
        color: #92400e;
        background: #fffbeb;
        border-color: #fcd34d;
    }

    .salary-proposal-status-badge.status-approved {
        color: #166534;
        background: #f0fdf4;
        border-color: #86efac;
    }

    .salary-proposal-status-badge.status-rejected {
        color: #991b1b;
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .salary-proposal-status-badge.status-unknown {
        color: #52525b;
        background: #fafafa;
        border-color: #d4d4d8;
    }
</style>
    <div class="card mb-3">
        <div class="card-body">
            {{ Form::open(['route' => ['employee-salary-proporal.index'], 'method' => 'GET', 'id' => 'spf_filter_form']) }}
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    {{ Form::label('spf_branch', __('Branch'), ['class' => 'form-label']) }}
                    {{ Form::select('branch_id', $branches, request('branch_id'), ['class' => 'form-control select custom-select', 'id' => 'spf_branch']) }}
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    {{ Form::label('spf_department', __('Department'), ['class' => 'form-label']) }}
                    {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control select custom-select', 'id' => 'spf_department']) }}
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    {{ Form::label('spf_designation', __('Designation'), ['class' => 'form-label']) }}
                    {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control select custom-select', 'id' => 'spf_designation']) }}
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    {{ Form::label('spf_employee', __('Employee'), ['class' => 'form-label']) }}
                    {{ Form::select('employee_id', $employees, request('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'spf_employee']) }}
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    {{ Form::label('spf_status', __('Status'), ['class' => 'form-label']) }}
                    {{ Form::select('status', [
                        'open' => __('Draft + Pending Approval'),
                        '0' => __('Draft'),
                        '1' => __('Pending Approval'),
                        '2' => __('Approved'),
                        '3' => __('Rejected'),
                        'all' => __('All Statuses'),
                    ], $statusFilter ?? request('status', 'open'), ['class' => 'form-control select custom-select', 'id' => 'spf_status']) }}
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary flex-fill">{{ __('Search') }}</button>
                    <a href="{{ route('employee-salary-proporal.index') }}" class="btn btn-sm btn-outline-danger flex-fill">{{ __('Clear') }}</a>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr class="table_heads">
                            <th style="width:45px;" class="text-center">
                                <input type="checkbox" id="spf_check_all">
                            </th>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Prev Scale</th>
                            <th>New Scale</th>
                            <th class="text-end">Prev Net</th>
                            <th class="text-end">New Net</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salaryproposal as $proposal)
                            @php
                                $status = (int) $proposal->status;
                                $statusLabel = [
                                    0 => 'Draft',
                                    1 => 'Pending Approval',
                                    2 => 'Approved',
                                    3 => 'Rejected',
                                ][$status] ?? 'Unknown';

                                $statusClass = [
                                    0 => 'status-draft',
                                    1 => 'status-pending',
                                    2 => 'status-approved',
                                    3 => 'status-rejected',
                                ][$status] ?? 'status-unknown';

                                // Bulk actions remain available on the index. Individual approve/reject stays inside Show.
                                $canSelect = \Auth::user()->type == 'company'
                                    ? $status === 1
                                    : in_array($status, [0, 3], true);

                                $newSnapshot = is_array($proposal->new_salarysnapshot)
                                    ? $proposal->new_salarysnapshot
                                    : (json_decode($proposal->new_salarysnapshot ?? '', true) ?: []);
                                $previousSnapshot = is_array($proposal->prev_salary_snapshot)
                                    ? $proposal->prev_salary_snapshot
                                    : (json_decode($proposal->prev_salary_snapshot ?? '', true) ?: []);

                                $employeeId = $newSnapshot['employee_id'] ?? $previousSnapshot['employee_id'] ?? null;
                                $employeeModel = $employeeId ? ($employeeMap->get((int) $employeeId) ?? null) : null;

                                $employeeName = $newSnapshot['employee_name']
                                    ?? $previousSnapshot['employee_name']
                                    ?? optional($employeeModel)->name
                                    ?? optional($proposal->employees)->name
                                    ?? '-';

                                $employeeBranchId = $employeeModel->owned_by ?? $proposal->owned_by ?? null;
                                $branchName = ($employeeBranchId && isset($branchNameMap))
                                    ? ($branchNameMap->get((int) $employeeBranchId) ?? optional(optional($employeeModel)->userbranch)->name ?? '-')
                                    : (optional(optional($employeeModel)->userbranch)->name ?? '-');
                                $departmentName = $newSnapshot['department_name']
                                    ?? $previousSnapshot['department_name']
                                    ?? optional(optional($employeeModel)->department)->name
                                    ?? '-';
                                $designationName = $newSnapshot['designation_name']
                                    ?? $previousSnapshot['designation_name']
                                    ?? optional(optional($employeeModel)->designation)->name
                                    ?? '-';

                                $previousScale = $previousSnapshot['scale_label'] ?? '-';
                                $newScale = $newSnapshot['scale_label']
                                    ?? optional($proposal->employee_payscale)->scale_no
                                    ?? '-';
                                $previousNet = (float) ($previousSnapshot['net_salary'] ?? 0);
                                $newNet = (float) ($newSnapshot['net_salary'] ?? $proposal->net_salary ?? 0);

                                // Keep source only in backend/snapshot; do not show Generation column.
                                $proposalSource = $newSnapshot['proposal_source']
                                    ?? $previousSnapshot['proposal_source']
                                    ?? null;
                                if (!$proposalSource) {
                                    $proposalSource = isset($newSnapshot['salary_detail']) && isset($newSnapshot['employee_percentages'])
                                        ? 'single'
                                        : 'bulk';
                                }
                                $isSingleProposal = $proposalSource === 'single';
                            @endphp

                            <tr>
                                <td class="text-center">
                                    @if ($canSelect)
                                        <input type="checkbox"
                                               class="spf_row_check"
                                               name="proposal_ids[]"
                                               value="{{ $proposal->id }}"
                                               form="spf_bulk_action_form">
                                    @endif
                                </td>
                                <td>{{ $proposal->id }}</td>
                                <td>{{ $employeeName }}</td>
                                <td>{{ $branchName }}</td>
                                <td>{{ $departmentName }}</td>
                                <td>{{ $designationName }}</td>
                                <td>{{ $previousScale }}</td>
                                <td>{{ $newScale }}</td>
                                <td class="text-end">{{ number_format($previousNet, 2) }}</td>
                                <td class="text-end fw-semibold">{{ number_format($newNet, 2) }}</td>
                                <td><span class="salary-proposal-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td>
                                    <div class="action-btn d-flex align-items-center gap-1">
                                        <a href="#"
                                           data-url="{{ route('employee-salary-proporal.show', $proposal->id) }}"
                                           data-size="xl"
                                           data-ajax-popup="true"
                                           data-bs-title="{{ __('Salary Proposal') }}"
                                           class="btn btn-sm btn-outline-info">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @if (\Auth::user()->type != 'company')

                                            @if (in_array($status, [0, 3], true))
                                                <form action="{{ route('employee-salary-proporal.sendForApproval', $proposal->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" data-bs-title="{{ __('Send For Approval') }}">
                                                        <i class="fa fa-user"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('employee-salary-proporal.destroy', $proposal->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-title="{{ __('Delete') }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                                                                @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    {{ __('No salary proposals found for the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
$(function () {
    'use strict';

    var $filter = $('#spf_filter_form');

    function initFilterSelect($select) {
        if (!$select || !$select.length) return;

        if ($select[0].customSelectInstance) {
            try { $select[0].customSelectInstance.destroy(); } catch (e) {}
            delete $select[0].customSelectInstance;
        }

        $select.next('.custom-select-wrapper').remove();
        $select.parent().children('.custom-select-wrapper').remove();
        $select.show();

        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
            $select[0].customSelectInstance = window.CustomSelect.create($select[0]);
        }
    }

    // Scope initialization only to index filters. Create-modal selects are never touched here.
    $filter.find('select.custom-select').each(function () {
        initFilterSelect($(this));
    });

    function syncCheckAll() {
        var $rows = $('.spf_row_check');
        var total = $rows.length;
        var checked = $rows.filter(':checked').length;

        $('#spf_check_all')
            .prop('checked', total > 0 && checked === total)
            .prop('indeterminate', checked > 0 && checked < total);
    }

    $(document)
        .off('change.salaryProposalIndexCheckAll', '#spf_check_all')
        .on('change.salaryProposalIndexCheckAll', '#spf_check_all', function () {
            $('.spf_row_check').prop('checked', $(this).is(':checked'));
            syncCheckAll();
        });

    $(document)
        .off('change.salaryProposalIndexRow', '.spf_row_check')
        .on('change.salaryProposalIndexRow', '.spf_row_check', syncCheckAll);

    $('#spf_bulk_action_form').off('submit.salaryProposalIndex').on('submit.salaryProposalIndex', function (e) {
        if ($('.spf_row_check:checked').length === 0) {
            e.preventDefault();
            if (typeof show_toastr === 'function') {
                show_toastr('error', 'Please select at least one salary proposal.', 'error');
            } else {
                alert('Please select at least one salary proposal.');
            }
        }
    });
});
</script>
@endpush
