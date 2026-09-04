@extends('layouts.admin')
@section('page-title')
    {{ __('Monthly Pre Challan Reports') }}
@endsection
<style>
    .action-btn .btn i {
        opacity: 1 !important;
        visibility: visible !important;
        color: #fff !important;
    }
    .btn-outline-success i { color: #198754 !important; }
    .btn-outline-primary i { color: #0d6efd !important; }
    .btn-outline-danger i  { color: #dc3545 !important; }
    .b-brand{
        height: 0px !important;
    }
</style>

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Monthly Pre Challan Reports') }}</li>
@endsection

@section('action-btn')
    <div class="float-end mb-2">
        <a href="#" data-size="lg" data-url="{{ route('prechallan.create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Pre Challan') }}" data-bs-toggle="{{ __('Upload Pre-Challan') }}"
            class="btn btn-sm btn-primary">
            Upload Pre Challan
        </a>
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['prechallan.index'], 'method' => 'GET', 'id' => 'transfer_form']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-12">
                                <div class="row align-items-center justify-content-end">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : null, ['class' => 'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                            {{ Form::month('date', isset($_GET['date']) ? $_GET['date'] : now()->format('Y-m'), ['class' => 'form-control month-btn']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto mt-4">
                                        <div class="row">
                                            <div class="col-auto">
                                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                                    onclick="document.getElementById('transfer_form').submit(); return false;">
                                                    <span class="btn-inner--icon">Search</span>
                                                </a>
                                                <a href="{{ route('prechallan.index') }}"
                                                    class="btn mx-1 btn-sm btn-outline-danger" title="{{ __('Reset') }}">
                                                    <span class="btn-inner--icon">Clear</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Report') }}</th>
                <th>{{ __('Ho Report') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Remarks') }}</th>
                <th>{{ __('Ho Remarks') }}</th>
                <th width="10%">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prechallanreports as $report)
                <tr class="font-style">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->month)->format('F Y') }}</td>
                    <td>{{ $report->branch->name }}</td>

                    @if (!empty($report->file_path))
                        <td><a href="{{ asset('/public/storage/' . $report->file_path) }}" target="_blank">Download</a></td>
                    @else
                        <td>No File</td>
                    @endif

                    @if (!empty($report->head_office_file) && Storage::disk('public')->exists($report->head_office_file))
                        <td><a href="{{ asset('/public/storage/' . $report->head_office_file) }}" target="_blank">Download</a></td>
                    @else
                        <td>No File</td>
                    @endif

                    {{-- ====== STATUS COLUMN ====== --}}
                    <td>
                        @if (Auth::user()->type == 'company' && $report->status == 'sent_for_approval')
                            {{-- Clickable badge that opens the review modal --}}
                            <span class="badge bg-info"
                                  role="button"
                                  style="cursor:pointer;"
                                  data-id="{{ $report->id }}"
                                  data-branch="{{ $report->branch->name }}"
                                  data-month="{{ \Carbon\Carbon::parse($report->month)->format('F Y') }}"
                                  data-file="{{ !empty($report->file_path) ? asset('/public/storage/' . $report->file_path) : '' }}"
                                  data-remarks="{{ $report->remarks }}"
                                  data-update-url="{{ route('prechallan.updateStatus', $report->id) }}"
                                  onclick="openApprovalModal(this)">
                                {{ __('Sent For Approval') }} &nbsp;<i class="ti ti-click" style="font-size:11px;"></i>
                            </span>
                        @elseif ($report->status == 'approved')
                            <span class="badge bg-success">{{ __('Approved') }}</span>
                        @elseif ($report->status == 'rejected')
                            <span class="badge bg-danger">{{ __('Rejected') }}</span>
                        @elseif ($report->status == 'sent_for_approval')
                            <span class="badge bg-info">{{ __('Sent For Approval') }}</span>
                        @else
                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    {{-- ====== END STATUS COLUMN ====== --}}

                    <td>{{ $report->remarks }}</td>
                    <td>{{ $report->ho_remarks }}</td>

                    <td class="Action">
                        <div class="action-btn d-flex align-items-center">

                            {{-- View/Edit --}}
                            <a href="#" data-size="lg"
                               data-url="{{ route('prechallan.show', $report->id) }}"
                               data-ajax-popup="true"
                               class="btn btn-sm btn-outline-success mx-1" title="View">
                                <i class="ti ti-edit"></i>
                            </a>

                            {{-- Send for approval --}}
                            @if ($report->status == 'pending')
                                <form action="{{ route('prechallan.sendForApproval', $report->id) }}" method="POST"
                                    style="display:inline; margin-bottom: 0 !important;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary mx-1"
                                        title="Send For Approval"
                                        onclick="return confirm('Are you sure you want to send this report for approval?')">
                                        <i class="ti ti-send"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- Delete --}}
                            <a href="#" class="btn btn-sm btn-outline-danger mx-1" title="Delete"
                                onclick="event.preventDefault();
                                    if(confirm('Are you sure you want to delete this report?')) {
                                        document.getElementById('delete-form-{{ $report->id }}').submit();
                                    }">
                                <i class="ti ti-trash"></i>
                            </a>
                            <form id="delete-form-{{ $report->id }}"
                                action="{{ route('prechallan.destroy', $report->id) }}" method="POST"
                                style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>

                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    {{-- ===================== Approval Review Modal ===================== --}}
    <div class="modal fade" id="approvalReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Review Pre-Challan Report') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <th style="width:35%">{{ __('Branch') }}</th>
                            <td id="modal-branch">—</td>
                        </tr>
                        <tr>
                            <th>{{ __('Month') }}</th>
                            <td id="modal-month">—</td>
                        </tr>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <td><span class="badge bg-info">{{ __('Sent For Approval') }}</span></td>
                        </tr>
                        <tr>
                            <th>{{ __('Report') }}</th>
                            <td id="modal-file">—</td>
                        </tr>
                        <tr>
                            <th>{{ __('Remarks') }}</th>
                            <td id="modal-remarks">—</td>
                        </tr>
                    </table>

                    <div class="mb-2">
                        <label for="modal-review-remarks" class="form-label">
                            {{ __('Your Remarks (optional)') }}
                        </label>
                        <textarea id="modal-review-remarks" class="form-control" rows="2"
                            placeholder="{{ __('Add a note...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-reject-report">
                        <i class="ti ti-x"></i> {{ __('Reject') }}
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-approve-report">
                        <i class="ti ti-check"></i> {{ __('Approve') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ================================================================= --}}


    <script>
        var currentUpdateUrl = null;

        function openApprovalModal(el) {
            currentUpdateUrl = el.dataset.updateUrl;

            document.getElementById('modal-branch').textContent  = el.dataset.branch  || '—';
            document.getElementById('modal-month').textContent   = el.dataset.month   || '—';
            document.getElementById('modal-remarks').textContent = el.dataset.remarks || '—';
            document.getElementById('modal-review-remarks').value = '';

            var fileEl = document.getElementById('modal-file');
            if (el.dataset.file) {
                fileEl.innerHTML = '<a href="' + el.dataset.file + '" target="_blank">Download</a>';
            } else {
                fileEl.textContent = 'No File';
            }

            var modal = new bootstrap.Modal(document.getElementById('approvalReviewModal'));
            modal.show();
        }

        function submitDecision(status) {
            if (!currentUpdateUrl) return;

            var remarks  = document.getElementById('modal-review-remarks').value;
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(currentUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: status, remarks: remarks }),
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    bootstrap.Modal.getInstance(
                        document.getElementById('approvalReviewModal')
                    ).hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Something went wrong.');
                }
            })
            .catch(function() {
                alert('Request failed. Please try again.');
            });
        }

        document.getElementById('btn-approve-report').addEventListener('click', function() {
            if (confirm('Are you sure you want to approve this report?')) {
                submitDecision('approved');
            }
        });

        document.getElementById('btn-reject-report').addEventListener('click', function() {
            if (confirm('Are you sure you want to reject this report?')) {
                submitDecision('rejected');
            }
        });
    </script>
@endsection