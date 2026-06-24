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
    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">{{ __('Start Student Finance Cleanup') }}</h5>
                <small class="text-muted">
                    {{ __('Use this when server terminal/artisan access is not available.') }}
                </small>
            </div>
            <span class="badge bg-info">{{ __('Admission Protected') }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student-finance-cleanup.index') }}" class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('From Date') }}</label>
                    <input type="date" name="from" class="form-control" value="{{ $preview['from'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('To Date') }}</label>
                    <input type="date" name="to" class="form-control" value="{{ $preview['to'] }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">{{ __('Preview') }}</button>
                </div>
            </form>

            <div class="cleanup-summary mb-3">
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Preview Receipts') }}</div>
                    <div class="cleanup-stat-value">{{ number_format($preview['total_receipts']) }}</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Receipt Vouchers') }}</div>
                    <div class="cleanup-stat-value">{{ number_format($preview['total_vouchers']) }}</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Linked Challans') }}</div>
                    <div class="cleanup-stat-value">{{ number_format($preview['total_challans']) }}</div>
                </div>
                <div class="cleanup-stat">
                    <div class="cleanup-stat-label">{{ __('Default Chunk') }}</div>
                    <div class="cleanup-stat-value">500</div>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Year') }}</th>
                            <th class="text-end">{{ __('Receipts') }}</th>
                            <th class="text-end">{{ __('Receipt Vouchers') }}</th>
                            <th class="text-end">{{ __('Linked Challans') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preview['rows'] as $row)
                            <tr>
                                <td>{{ $row->year }}</td>
                                <td class="text-end">{{ number_format($row->receipts) }}</td>
                                <td class="text-end">{{ number_format($row->receipt_vouchers) }}</td>
                                <td class="text-end">{{ number_format($row->challans) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">{{ __('No records found for this range.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('student-finance-cleanup.start') }}" class="border rounded p-3">
                @csrf
                <input type="hidden" name="from" value="{{ $preview['from'] }}">
                <input type="hidden" name="to" value="{{ $preview['to'] }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Chunk Size') }}</label>
                        <input type="number" name="chunk" class="form-control" value="500" min="100" max="2000">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="confirm_backup" value="1" id="confirmBackup">
                            <label class="form-check-label" for="confirmBackup">
                                {{ __('I have taken a full database backup and understand this will delete old finance data.') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-danger w-100"
                            onclick="return confirm('Start destructive finance cleanup for selected range?')">
                            {{ __('Schedule Cleanup') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($runId)
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">{{ __('Student Finance Cleanup') }} #{{ $runId }}</h5>
                <small class="text-muted" id="dateRange"></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="processChunkBtn" class="btn btn-sm btn-outline-primary">
                    {{ __('Process Next Chunk') }}
                </button>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="autoProcess">
                    <label class="form-check-label" for="autoProcess">{{ __('Auto process in browser') }}</label>
                </div>
                <span id="runStatus" class="badge cleanup-status-badge bg-secondary"></span>
            </div>
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
    @endif
@endsection

@push('script-page')
    @if($runId)
    <script>
        (function () {
            const statusUrl = @json(route('student-finance-cleanup.status', $runId));
            const processUrl = @json(route('student-finance-cleanup.process', $runId));
            const csrfToken = @json(csrf_token());
            let statusData = @json($initialStatus);
            let processRunning = false;

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

            async function processNextChunk() {
                if (processRunning || ['completed', 'failed'].includes(String(statusData.status || ''))) {
                    return;
                }

                processRunning = true;
                const btn = document.getElementById('processChunkBtn');
                btn.disabled = true;
                btn.textContent = 'Processing...';

                try {
                    const response = await fetch(processUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        cache: 'no-store'
                    });
                    if (!response.ok) {
                        throw new Error('Unable to process cleanup chunk.');
                    }
                    statusData = await response.json();
                    render(statusData);
                } catch (error) {
                    const alert = document.getElementById('errorAlert');
                    alert.textContent = error.message;
                    alert.classList.remove('d-none');
                } finally {
                    processRunning = false;
                    btn.disabled = false;
                    btn.textContent = 'Process Next Chunk';
                }
            }

            render(statusData);
            setInterval(refreshStatus, 5000);
            document.getElementById('processChunkBtn').addEventListener('click', processNextChunk);
            setInterval(function () {
                if (document.getElementById('autoProcess').checked) {
                    processNextChunk();
                }
            }, 8000);
        })();
    </script>
    @endif
@endpush
