@php
    $snapshotData = is_array($concessionSnapshot ?? null)
        ? $concessionSnapshot
        : [];

    $snapshotStudent = $snapshotData['student'] ?? [];
    $snapshotConcession = $snapshotData['concession'] ?? [];
    $snapshotHeads = collect($snapshotData['heads'] ?? []);
    $snapshotTotals = $snapshotData['totals'] ?? [];
    $hasApprovalSnapshot = $snapshotHeads->isNotEmpty();

    $typeLabels = [
        0 => 'Registration',
        1 => 'Regular',
        2 => 'Withdrawal',
    ];

    $displayType = $snapshotConcession['type_label']
        ?? ($typeLabels[(int)($concession->type ?? 1)] ?? 'Regular');

    $displayDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d-M-y');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $studentBranch = $hasApprovalSnapshot
        ? ($snapshotStudent['branch_name'] ?? '-')
        : (
            @$concession->student->student_status == 'Registered'
                ? (@$concession->student->reg_branch->name ?? '-')
                : (
                    @$concession->student->enrollment
                        ? (@$concession->student->enrollment->branch->name ?? '-')
                        : (@$concession->branches->name ?? '-')
                )
        );

    $studentRollNo = $hasApprovalSnapshot
        ? ($snapshotStudent['roll_no'] ?? '-')
        : (@$concession->student->enrollment->enrollId ?? @$concession->student->roll_no ?? '-');

    $studentName = $hasApprovalSnapshot
        ? ($snapshotStudent['name'] ?? '-')
        : (@$concession->student->stdname ?? '-');

    $registrationNo = $hasApprovalSnapshot
        ? ($snapshotStudent['id'] ?? '-')
        : (@$concession->student->id ?? '-');

    $fatherName = $hasApprovalSnapshot
        ? ($snapshotStudent['father_name'] ?? '-')
        : (@$concession->student->fathername ?? '-');

    $studentClass = $hasApprovalSnapshot
        ? ($snapshotStudent['class_name'] ?? '-')
        : (@$concession->student->class->name ?? '-');

    $policyTitle = $snapshotConcession['policy_title']
        ?? @$concession->concession->title
        ?? '-';

    $billingMonth = $snapshotConcession['effective_from']
        ?? $concession->effective_from
        ?? null;

    $applyDate = $snapshotConcession['apply_date']
        ?? $concession->apply_date
        ?? null;

    $startDate = $snapshotConcession['start_date']
        ?? $concession->start_date
        ?? null;

    $endDate = $snapshotConcession['end_date']
        ?? $concession->end_date
        ?? null;

    $approvalDate = $snapshotData['approval_date']
        ?? $concession->approval_date
        ?? null;

    /*
     * Use the stored concession_id directly instead of depending on the
     * loaded policy relation. This keeps Current Structure heads available
     * even if the relationship is not loaded/resolved in the modal.
     */
    $currentConcessionHeads = \App\Models\ConcessionPolicyHead::where(
        'concession_id',
        $concession->concession_id
    )
        ->where('percentage', '!=', 0)
        ->get();

    $currentRows = collect();
    $currentBaseTotal = 0;
    $currentConcessionTotal = 0;
    $currentPayableTotal = 0;

    foreach ($currentConcessionHeads as $dta) {
        $head = \App\Models\FeeHead::find($dta->head_id);

        $classhead = \App\Models\ClassWiseFee::where(
            'class_id',
            @$concession->student->class_id
        )
            ->where('head_id', $dta->head_id)
            ->first();

        $actualAmount = (float)($classhead->amount ?? 0);
        $percentage = (float)($dta->percentage ?? 0);
        $concessionAmount = round(($percentage / 100) * $actualAmount, 2);
        $payableAmount = round($actualAmount - $concessionAmount, 2);

        $currentBaseTotal += $actualAmount;
        $currentConcessionTotal += $concessionAmount;
        $currentPayableTotal += $payableAmount;

        $currentRows->push([
            'head_name' => $head->fee_head ?? '-',
            'percentage' => $percentage,
            'base_amount' => $actualAmount,
            'concession_amount' => $concessionAmount,
            'payable_amount' => $payableAmount,
        ]);
    }
@endphp

<div class="modal-body concession-status-compact">
    <style>
        .concession-status-compact {
            --cs-primary: #4f46e5;
            --cs-primary-soft: #eef2ff;
            --cs-info: #0284c7;
            --cs-info-soft: #ecfeff;
            --cs-success: #059669;
            --cs-success-soft: #ecfdf5;
            --cs-warning: #d97706;
            --cs-warning-soft: #fff7ed;
            --cs-danger: #dc2626;
            --cs-danger-soft: #fef2f2;
            --cs-border: #e5e7eb;
            --cs-muted: #64748b;
            --cs-text: #1e293b;
            background: #f8fafc;
            padding: .85rem;
overflow: hidden;
        }

        .concession-status-compact .cs-shell {
            display: flex;
            flex-direction: column;
            gap: .75rem;
}

        .concession-status-compact .cs-top {
            display: grid;
            grid-template-columns: 1fr;
            gap: .65rem;
            flex: 0 0 auto;
        }

        .concession-status-compact .cs-panel {
            background: #fff;
            border: 1px solid var(--cs-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
        }

        .concession-status-compact .cs-panel-head {
            padding: .65rem .8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            border-bottom: 1px solid var(--cs-border);
        }

        .concession-status-compact .cs-panel-head.primary {
            background: var(--cs-primary-soft);
            border-bottom-color: #dfe3ff;
        }

        .concession-status-compact .cs-panel-head.info {
            background: #f0f9ff;
            border-bottom-color: #dbeafe;
        }

        .concession-status-compact .cs-panel-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: var(--cs-text);
        }

        .concession-status-compact .cs-panel-sub {
            margin-top: .08rem;
            font-size: .76rem;
            color: var(--cs-muted);
        }

        .concession-status-compact .cs-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .28rem .55rem;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .concession-status-compact .cs-badge-primary {
            background: #e0e7ff;
            color: #3730a3;
        }

        .concession-status-compact .cs-badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .concession-status-compact .cs-badge-info {
            background: #dbeafe;
            color: #075985;
        }

        .concession-status-compact .cs-badge-warning {
            background: #ffedd5;
            color: #9a3412;
        }

        .concession-status-compact .cs-mini-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
        }

        .concession-status-compact .cs-mini {
            padding: .7rem .8rem;
            min-height: 66px;
            border-right: 1px solid var(--cs-border);
            border-bottom: 1px solid var(--cs-border);
        }

        .concession-status-compact .cs-mini:nth-child(4n) {
            border-right: 0;
        }

        .concession-status-compact .cs-mini-label {
            display: block;
            font-size: .8rem;
            font-weight: 800;
            color: var(--cs-muted);
            text-transform: uppercase;
            letter-spacing: .035em;
            margin-bottom: .1rem;
        }

        .concession-status-compact .cs-mini-value {
            color: var(--cs-text);
            font-size: .9rem;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }

        .concession-status-compact .cs-mini-span-3 {
            grid-column: span 3;
        }

        .concession-status-compact .cs-policy-title {
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            word-break: normal;
            scrollbar-width: thin;
            padding-bottom: 2px;
        }

        .concession-status-compact .cs-tabs-panel {
            min-height: 260px;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        .concession-status-compact .cs-tabs {
            display: flex;
            gap: .35rem;
            padding: .55rem .6rem 0;
            background: #fff;
            border-bottom: 1px solid var(--cs-border);
            flex-wrap: wrap;
        }

        .concession-status-compact .cs-tab-btn {
            border: 0;
            background: transparent;
            color: var(--cs-muted);
            font-size: .9rem;
            font-weight: 800;
            padding: .48rem .65rem;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
        }

        .concession-status-compact .cs-tab-btn.active[data-target="snapshot"] {
            background: var(--cs-success-soft);
            color: #047857;
        }

        .concession-status-compact .cs-tab-btn.active[data-target="current"] {
            background: var(--cs-info-soft);
            color: #0369a1;
        }

        .concession-status-compact .cs-tab-btn.active[data-target="history"] {
            background: var(--cs-warning-soft);
            color: #b45309;
        }

        .concession-status-compact .cs-tab-content {
            display: none;
            padding: .85rem;
            overflow: auto;
            min-height: 120px;
            flex: 1 1 auto;
            background: #fff;
        }

        .concession-status-compact .cs-tab-content.active {
            display: block;
        }

        .concession-status-compact .cs-note {
            padding: .5rem .65rem;
            border-radius: 8px;
            font-size: .8rem;
            margin-bottom: .55rem;
            border: 1px solid transparent;
        }

        .concession-status-compact .cs-note-success {
            background: var(--cs-success-soft);
            color: #065f46;
            border-color: #bbf7d0;
        }

        .concession-status-compact .cs-note-info {
            background: #f0f9ff;
            color: #075985;
            border-color: #bae6fd;
        }

        .concession-status-compact .cs-note-warning {
            background: var(--cs-warning-soft);
            color: #9a3412;
            border-color: #fed7aa;
        }

        .concession-status-compact .table {
            margin-bottom: 0;
            font-size: .9rem;
        }

        .concession-status-compact .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: .5rem .55rem;
            background: #f8fafc;
            color: #475569;
            border-bottom-width: 1px;
            white-space: nowrap;
            font-size: .76rem;
            font-weight: 800;
        }

        .concession-status-compact .table tbody td,
        .concession-status-compact .table tfoot td {
            padding: .48rem .55rem;
            vertical-align: middle;
        }

        .concession-status-compact .table tbody tr:hover {
            background: #fafafa;
        }

        .concession-status-compact .table tbody tr {
            min-height: 42px;
        }

        .concession-status-compact .table tbody td {
            line-height: 1.45;
        }

        .concession-status-compact .cs-money {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .concession-status-compact .cs-total-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .45rem;
            margin-top: .55rem;
        }

        .concession-status-compact .cs-total {
            padding: .5rem .6rem;
            border-radius: 8px;
            border: 1px solid var(--cs-border);
            background: #fff;
        }

        .concession-status-compact .cs-total.success {
            background: var(--cs-success-soft);
            border-color: #bbf7d0;
        }

        .concession-status-compact .cs-total.info {
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .concession-status-compact .cs-total-label {
            font-size: .68rem;
            font-weight: 800;
            color: var(--cs-muted);
            text-transform: uppercase;
        }

        .concession-status-compact .cs-total-value {
            margin-top: .08rem;
            font-size: .9rem;
            font-weight: 800;
            color: var(--cs-text);
            font-variant-numeric: tabular-nums;
        }

        .concession-status-compact .cs-actions-panel {
            flex: 0 0 auto;
            background: #fff;
            border: 1px solid var(--cs-border);
            border-radius: 10px;
            padding: .8rem .9rem;
        }

        .concession-status-compact .cs-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .9rem;
            flex-wrap: wrap;
            line-height: 1.5;
        }

        .concession-status-compact .cs-action-title {
            font-size: .9rem;
            font-weight: 800;
            color: var(--cs-text);
            line-height: 1.45;
            margin-bottom: .2rem;
        }

        .concession-status-compact .cs-action-subtitle {
            display: block;
            margin-top: .15rem;
            line-height: 1.55;
            font-size: .78rem;
            color: var(--cs-muted);
        }

        .concession-status-compact .cs-action-buttons {
            display: flex;
            gap: .4rem;
            flex-wrap: wrap;
        }

        .concession-status-compact .cs-action-form {
            margin-top: .55rem;
            padding-top: .55rem;
            border-top: 1px solid var(--cs-border);
        }

        .concession-status-compact textarea.form-control {
            min-height: 72px;
        }

        @media (max-width: 900px) {
            .concession-status-compact .cs-mini-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .concession-status-compact .cs-mini:nth-child(4n) {
                border-right: 1px solid var(--cs-border);
            }

            .concession-status-compact .cs-mini:nth-child(2n) {
                border-right: 0;
            }

            .concession-status-compact .cs-mini-span-3 {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575px) {
.concession-status-compact .cs-mini-grid,
            .concession-status-compact .cs-total-strip {
                grid-template-columns: 1fr;
            }

            .concession-status-compact .cs-mini {
                border-right: 0 !important;
            }

            .concession-status-compact .cs-action-buttons {
                width: 100%;
            }

            .concession-status-compact .cs-action-buttons .btn {
                flex: 1 1 auto;
            }
        }
    </style>

    <div class="cs-shell">

        {{-- Compact summary area --}}
        <div class="cs-top">
            <div class="cs-panel">
                <div class="cs-panel-head primary">
                    <div>
                        <h6 class="cs-panel-title">Student</h6>
                        <div class="cs-panel-sub">Student identity, branch and class information</div>
                    </div>
                    <span class="cs-badge cs-badge-primary">{{ $displayType }}</span>
                </div>

                <div class="cs-mini-grid">
                    <div class="cs-mini">
                        <span class="cs-mini-label">Student</span>
                        <div class="cs-mini-value">{{ $studentName }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Father</span>
                        <div class="cs-mini-value">{{ $fatherName }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Registration</span>
                        <div class="cs-mini-value">{{ $registrationNo }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Roll No.</span>
                        <div class="cs-mini-value">{{ $studentRollNo }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Branch</span>
                        <div class="cs-mini-value">{{ $studentBranch }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Class</span>
                        <div class="cs-mini-value">{{ $studentClass }}</div>
                    </div>
                </div>
            </div>

            <div class="cs-panel">
                <div class="cs-panel-head info">
                    <div>
                        <h6 class="cs-panel-title">Concession Application</h6>
                        <div class="cs-panel-sub">Application policy, billing month and concession period</div>
                    </div>
                    <span class="cs-badge
                        @if(strtolower((string)@$concession->status) === 'approved')
                            cs-badge-success
                        @elseif(strtolower((string)@$concession->status) === 'rejected')
                            cs-badge-warning
                        @else
                            cs-badge-info
                        @endif
                    ">
                        {{ @$concession->status ?? '-' }}
                    </span>
                </div>

                <div class="cs-mini-grid">
                    <div class="cs-mini">
                        <span class="cs-mini-label">Order</span>
                        <div class="cs-mini-value">#{{ @$concession->id }}</div>
                    </div>
                    <div class="cs-mini cs-mini-span-3">
                        <span class="cs-mini-label">Policy</span>
                        <div class="cs-mini-value cs-policy-title"
                             title="{{ $policyTitle }}">
                            {{ $policyTitle }}
                        </div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Billing Month</span>
                        <div class="cs-mini-value">{{ $displayDate($billingMonth) }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Apply</span>
                        <div class="cs-mini-value">{{ $displayDate($applyDate) }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">Start</span>
                        <div class="cs-mini-value">{{ $displayDate($startDate) }}</div>
                    </div>
                    <div class="cs-mini">
                        <span class="cs-mini-label">End</span>
                        <div class="cs-mini-value">{{ $displayDate($endDate) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact tabbed content --}}
        <div class="cs-panel cs-tabs-panel">
            <div class="cs-tabs">
                <button type="button"
                        class="cs-tab-btn active"
                        data-target="snapshot">
                    Applied Snapshot
                </button>

                <button type="button"
                        class="cs-tab-btn"
                        data-target="current">
                    Current Structure
                </button>

                <button type="button"
                        class="cs-tab-btn"
                        data-target="history">
                    Previous History
                    @if(isset($prev_concession) && count($prev_concession) > 0)
                        ({{ count($prev_concession) }})
                    @endif
                </button>
            </div>

            {{-- Snapshot tab --}}
            <div class="cs-tab-content active" data-pane="snapshot">
                @if($hasApprovalSnapshot)
                    <div class="cs-note cs-note-success">
                        Exact values saved when this concession was approved.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Head</th>
                                    <th class="text-end">%</th>
                                    <th class="text-end">Base</th>
                                    <th class="text-end">Concession</th>
                                    <th class="text-end">Payable</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snapshotHeads as $headRow)
                                    <tr>
                                        <td>{{ $headRow['head_name'] ?? '-' }}</td>
                                        <td class="text-end">
                                            {{ number_format((float)($headRow['percentage'] ?? 0), 2) }}%
                                        </td>
                                        <td class="text-end cs-money">
                                            {{ number_format((float)($headRow['base_amount'] ?? $headRow['actual_amount'] ?? 0), 2) }}
                                        </td>
                                        <td class="text-end cs-money">
                                            {{ number_format((float)($headRow['concession_amount'] ?? 0), 2) }}
                                        </td>
                                        <td class="text-end fw-bold cs-money">
                                            {{ number_format((float)($headRow['payable_amount'] ?? 0), 2) }}
                                        </td>
                                        <td>
                                            @if(($headRow['amount_source'] ?? '') === 'student_fee_structure')
                                                Student Structure
                                            @elseif(($headRow['amount_source'] ?? '') === 'class_wise_fee')
                                                Class Fee
                                            @else
                                                {{ $headRow['amount_source'] ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="cs-total-strip">
                        <div class="cs-total success">
                            <div class="cs-total-label">Base</div>
                            <div class="cs-total-value">
                                {{ number_format((float)($snapshotTotals['base_amount'] ?? 0), 2) }}
                            </div>
                        </div>
                        <div class="cs-total success">
                            <div class="cs-total-label">Concession</div>
                            <div class="cs-total-value">
                                {{ number_format((float)($snapshotTotals['concession_amount'] ?? 0), 2) }}
                            </div>
                        </div>
                        <div class="cs-total success">
                            <div class="cs-total-label">Payable</div>
                            <div class="cs-total-value">
                                {{ number_format((float)($snapshotTotals['payable_amount'] ?? 0), 2) }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="cs-note cs-note-warning">
                        Historical approval snapshot is not available for this legacy concession.
                    </div>

                    @if($currentRows->isNotEmpty())
                        <div class="small text-muted">
                            Current policy has {{ $currentRows->count() }} concession head(s).
                            Open the <strong>Current Structure</strong> tab to view them.
                        </div>
                    @endif
                @endif
            </div>

            {{-- Current tab --}}
            <div class="cs-tab-content" data-pane="current">
                <div class="cs-note cs-note-info">
                    Recalculated using the student's current class fee structure.
                </div>

                @if($currentRows->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Head</th>
                                    <th class="text-end">%</th>
                                    <th class="text-end">Base</th>
                                    <th class="text-end">Concession</th>
                                    <th class="text-end">Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentRows as $row)
                                    <tr>
                                        <td>{{ $row['head_name'] }}</td>
                                        <td class="text-end">
                                            {{ number_format((float)$row['percentage'], 2) }}%
                                        </td>
                                        <td class="text-end cs-money">
                                            {{ number_format((float)$row['base_amount'], 2) }}
                                        </td>
                                        <td class="text-end cs-money">
                                            {{ number_format((float)$row['concession_amount'], 2) }}
                                        </td>
                                        <td class="text-end fw-bold cs-money">
                                            {{ number_format((float)$row['payable_amount'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="cs-total-strip">
                        <div class="cs-total info">
                            <div class="cs-total-label">Current Base</div>
                            <div class="cs-total-value">{{ number_format($currentBaseTotal, 2) }}</div>
                        </div>
                        <div class="cs-total info">
                            <div class="cs-total-label">Current Concession</div>
                            <div class="cs-total-value">{{ number_format($currentConcessionTotal, 2) }}</div>
                        </div>
                        <div class="cs-total info">
                            <div class="cs-total-label">Current Payable</div>
                            <div class="cs-total-value">{{ number_format($currentPayableTotal, 2) }}</div>
                        </div>
                    </div>
                @else
                    <div class="text-muted small">
                        No current concession heads are available.
                    </div>
                @endif
            </div>

            {{-- History tab --}}
            <div class="cs-tab-content" data-pane="history">
                @if(isset($prev_concession) && count($prev_concession) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Concession</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Cancel</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prev_concession as $prevcon)
                                    <tr>
                                        <td>#{{ @$prevcon->id }}</td>
                                        <td>{{ @$prevcon->concession->title ?? '-' }}</td>
                                        <td>{{ $displayDate(@$prevcon->start_date) }}</td>
                                        <td>{{ $displayDate(@$prevcon->end_date) }}</td>
                                        <td>{{ $displayDate(@$prevcon->cancel_date) }}</td>
                                        <td>{{ @$prevcon->cancel_remarks ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="cs-note cs-note-warning mb-0">
                        No previous concession history is available.
                    </div>
                @endif
            </div>
        </div>

        {{-- Compact action footer --}}
        @if(Auth::user()->type == 'company')
            <div class="cs-actions-panel">
                <div id="concession-action-buttons" class="cs-actions">
                    <div class="pe-3">
                        <div class="cs-action-title">Application Action</div>
                        <span class="cs-action-subtitle">
                            Approve, reject or return this concession application for correction.
                        </span>
                    </div>

                    <div class="cs-action-buttons">
                        <a href="javascript:void(0);"
                           id="reject-btn"
                           class="btn btn-sm btn-outline-danger">
                            Reject
                        </a>

                        <a href="javascript:void(0);"
                           id="rollback-btn"
                           class="btn btn-sm btn-outline-warning">
                            Roll Back
                        </a>

                        <a href="{{ route('concession.change_status', [$concession->id, 'Approved']) }}"
                           id="approve-btn"
                           class="btn btn-sm btn-primary">
                            Approve
                        </a>
                    </div>
                </div>

                <div id="rejection-form" class="cs-action-form d-none">
                    <form action="{{ route('concession.reject_reason', [$concession->id]) }}" method="POST">
                        @csrf
                        <textarea name="reject_reason"
                                  id="reject_reason"
                                  class="form-control form-control-sm"
                                  rows="2"
                                  placeholder="Enter rejection reason"></textarea>
                        <input type="hidden" name="type" value="Rejected">

                        <div class="cs-action-buttons justify-content-end mt-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    id="cancel-reject-btn">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn btn-sm btn-danger">
                                Submit Rejection
                            </button>
                        </div>
                    </form>
                </div>

                <div id="rollback-form" class="cs-action-form d-none">
                    <form action="{{ route('concession.reject_reason', [$concession->id]) }}" method="POST">
                        @csrf
                        <textarea name="rollback_reason"
                                  id="rollback_reason"
                                  class="form-control form-control-sm"
                                  rows="2"
                                  placeholder="Enter rollback reason"></textarea>
                        <input type="hidden" name="type" value="Rollback">

                        <div class="cs-action-buttons justify-content-end mt-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    id="cancel-rollback-btn">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn btn-sm btn-warning">
                                Submit Rollback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function($) {
    'use strict';

    const ns = '.concessionStatusCompact';

    $(document)
        .off('click' + ns, '.cs-tab-btn')
        .on('click' + ns, '.cs-tab-btn', function() {
            const target = $(this).data('target');

            $('.cs-tab-btn').removeClass('active');
            $(this).addClass('active');

            $('.cs-tab-content').removeClass('active');
            $('.cs-tab-content[data-pane="' + target + '"]').addClass('active');
        });

    function resetActions() {
        $('#rejection-form, #rollback-form').addClass('d-none');
        $('#concession-action-buttons').removeClass('d-none');
    }

    $(document)
        .off('click' + ns, '#reject-btn')
        .on('click' + ns, '#reject-btn', function() {
            $('#concession-action-buttons').addClass('d-none');
            $('#rollback-form').addClass('d-none');
            $('#rejection-form').removeClass('d-none');
        });

    $(document)
        .off('click' + ns, '#rollback-btn')
        .on('click' + ns, '#rollback-btn', function() {
            $('#concession-action-buttons').addClass('d-none');
            $('#rejection-form').addClass('d-none');
            $('#rollback-form').removeClass('d-none');
        });

    $(document)
        .off('click' + ns, '#cancel-reject-btn, #cancel-rollback-btn')
        .on('click' + ns, '#cancel-reject-btn, #cancel-rollback-btn', function() {
            resetActions();
        });

})(jQuery);
</script>
