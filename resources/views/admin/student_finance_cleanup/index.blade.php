@extends('layouts.admin')

@section('page-title')
    {{ __('Student Finance Cleanup Monitor') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Finance Cleanup Monitor') }}</li>
@endsection

@push('css-page')
    <style>
        .cleanup-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            gap: 12px;
        }
        .cleanup-stat {
            border: 1px solid #e7e9ed;
            border-radius: 6px;
            padding: 12px 14px;
            background: #fff;
        }
        .cleanup-stat-label {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .cleanup-stat-value {
            color: #202124;
            font-size: 20px;
            font-weight: 700;
        }
        .cleanup-status-badge {
            font-size: 13px;
            padding: 7px 11px;
        }
        .cleanup-progress {
            height: 18px;
            background: #edf0f3;
        }
        .cleanup-progress .progress-bar {
            min-width: 0;
            transition: width .35s ease;
        }
        @media (max-width: 900px) {
            .cleanup-summary {
                grid-template-columns: repeat(2, minmax(140px, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">{{ __('Student Finance Cleanup') }} #{{ $runId }}</h5>
                <small class="text-muted" id="dateRange"></small>
            </div>
            <span id="runStatus" class="badge cleanup-status-badge bg-secondary"></span>
        </div>

        <div class="card-body">
            <div class="cleanup-summary mb-4">
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Progress') }}</div>
                    <div class="cleanup-stat-value" id="progressValue">0%</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Deleted Receipts') }}</div>
                    <div class="cleanup-stat-value" id="deletedReceipts">0</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Remaining Receipts') }}</div>
                    <div class="cleanup-stat-value" id="remainingReceipts">0</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Current Year') }}</div>
                    <div class="cleanup-stat-value" id="currentYear">-</div>
                </div>
            </div>

            <div class="progress cleanup-progress mb-2">
                <div id="progressBar" class="progress-bar bg-success" role="progressbar"
                    style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <small class="text-muted" id="progressText"></small>
                <small class="text-muted">Auto-refresh: 5 seconds</small>
            </div>

            <div id="errorAlert" class="alert alert-danger d-none"></div>
            <div id="schedulerAlert" class="alert alert-warning d-none">
                The cleanup is waiting, but the Laravel scheduler has not processed the next chunk.
                Configure the server cron to run <code>php artisan schedule:run</code> every minute.
            </div>
            <div class="alert alert-info">
                Admission challans and their receipts are protected and excluded from this cleanup.
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <h6>{{ __('Deleted Records') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr><th>Receipts</th><td id="totalReceipts">0</td></tr>
                                <tr><th>Receipt Journals</th><td id="receiptJournals">0</td></tr>
                                <tr><th>Receipt Journal Items</th><td id="receiptJournalItems">0</td></tr>
                                <tr><th>Challans</th><td id="challans">0</td></tr>
                                <tr><th>Challan Heads</th><td id="challanHeads">0</td></tr>
                                <tr><th>Challan Journals</th><td id="challanJournals">0</td></tr>
                                <tr><th>Challan Journal Items</th><td id="challanJournalItems">0</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    <h6>{{ __('Runtime') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr><th>Chunk Size</th><td id="chunkSize">0</td></tr>
                                <tr><th>Last Receipt ID</th><td id="lastReceiptId">0</td></tr>
                                <tr><th>Next Run</th><td id="nextRun">-</td></tr>
                                <tr><th>Locked At</th><td id="lockedAt">-</td></tr>
                                <tr><th>Last Activity</th><td id="lastActivity">-</td></tr>
                                <tr><th>Server Time</th><td id="serverTime">-</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <h6 class="mt-3">{{ __('Year Progress') }}</h6>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>State</th>
                            <th class="text-end">Receipts Remaining</th>
                        </tr>
                    </thead>
                    <tbody id="yearRows"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        (function () {
            const statusUrl = @json(route('student-finance-cleanup.status', $runId));
            let statusData = @json($initialStatus);

            const number = value => Number(value || 0).toLocaleString();
            const text = (id, value) => document.getElementById(id).textContent = value ?? '-';

            function badgeClass(status) {
                return {
                    scheduled: 'bg-warning text-dark',
                    running: 'bg-primary',
                    completed: 'bg-success',
                    failed: 'bg-danger'
                }[status] || 'bg-secondary';
            }

            function render(data) {
                const status = String(data.status || 'unknown');
                const statusBadge = document.getElementById('runStatus');
                statusBadge.className = 'badge cleanup-status-badge ' + badgeClass(status);
                statusBadge.textContent = status.toUpperCase();

                text('dateRange', data.from_date + ' to ' + data.to_date);
                text('progressValue', data.percentage + '%');
                text('deletedReceipts', number(data.deleted_receipts));
                text('remainingReceipts', number(data.remaining_receipts));
                text('currentYear', data.current_year);
                text('progressText', number(data.deleted_receipts) + ' of ' + number(data.target_receipts) + ' receipts deleted');

                const progressBar = document.getElementById('progressBar');
                progressBar.style.width = data.percentage + '%';
                progressBar.setAttribute('aria-valuenow', data.percentage);

                text('totalReceipts', number(data.totals.receipts));
                text('receiptJournals', number(data.totals.receipt_journals));
                text('receiptJournalItems', number(data.totals.receipt_journal_items));
                text('challans', number(data.totals.challans));
                text('challanHeads', number(data.totals.challan_heads));
                text('challanJournals', number(data.totals.challan_journals));
                text('challanJournalItems', number(data.totals.challan_journal_items));
                text('chunkSize', number(data.chunk_size));
                text('lastReceiptId', number(data.last_receipt_id));
                text('nextRun', data.execute_after || '-');
                text('lockedAt', data.locked_at || '-');
                text('lastActivity', data.updated_at || '-');
                text('serverTime', data.server_time || '-');

                const errorAlert = document.getElementById('errorAlert');
                errorAlert.textContent = data.last_error || '';
                errorAlert.classList.toggle('d-none', !data.last_error);
                document.getElementById('schedulerAlert')
                    .classList.toggle('d-none', !data.scheduler_delayed);

                document.getElementById('yearRows').innerHTML = data.years.map(row => {
                    const stateClass = row.state === 'completed'
                        ? 'bg-success'
                        : (row.state === 'processing' ? 'bg-primary' : 'bg-secondary');
                    return `<tr>
                        <td>${row.year}</td>
                        <td><span class="badge ${stateClass}">${row.state}</span></td>
                        <td class="text-end">${number(row.remaining)}</td>
                    </tr>`;
                }).join('');
            }

            async function refreshStatus() {
                try {
                    const response = await fetch(statusUrl, {
                        headers: {'Accept': 'application/json'},
                        cache: 'no-store'
                    });
                    if (!response.ok) {
                        throw new Error('Unable to load cleanup status.');
                    }
                    statusData = await response.json();
                    render(statusData);
                } catch (error) {
                    const alert = document.getElementById('errorAlert');
                    alert.textContent = error.message;
                    alert.classList.remove('d-none');
                }
            }

            render(statusData);
            setInterval(refreshStatus, 5000);
        })();
    </script>
@endpush
