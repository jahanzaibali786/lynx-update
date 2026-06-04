@extends('layouts.admin')

@section('page-title')
    {{ __('Challan Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Challan Detail') }}</li>
@endsection

@section('content')
    @php
        $feeMonthLabel = $challan->fee_month ? \Carbon\Carbon::parse($challan->fee_month)->format('M-Y') : '-';
        $challanDate = $challan->challan_date ?: $challan->fee_month;
        $totalGross = (float) $challan->heads->sum('price');
        $totalDiscount = (float) $challan->heads->sum('concession');
        $totalNet = $totalGross - $totalDiscount;
        $enrollStudent = $challan->enrollstudent;
        $studentEnrollment = optional($student)->enrollment;
        $studentClass =
            optional($challan->class)->name ?:
            optional(optional($enrollStudent)->class)->name ?:
            optional(optional($student)->class)->name ?:
            '-';
        $studentSection =
            optional(optional($enrollStudent)->section)->name ?:
            optional(optional($studentEnrollment)->section)->name ?:
            optional(optional($student)->section)->name ?:
            '-';
        $rollNumber = $challan->rollno ?: (optional($student)->roll_no ?: '-');
        $readonly = $editMode ? [] : ['readonly' => 'readonly'];
        $disabled = $editMode ? [] : ['disabled' => 'disabled'];
        $subscription = [
            'monthly' => 'Monthly',
            'bi-monthly' => 'Bi-Monthly',
            'quarterly' => 'Quarterly',
            '4-monthly' => '4 Month Subscription',
            '5-monthly' => '5 Month Subscription',
            '6-monthly' => '6 Month Subscription',
            '7-monthly' => '7 Month Subscription',
            '8-monthly' => '8 Month Subscription',
            '9-monthly' => '9 Month Subscription',
            '10-monthly' => '10 Month Subscription',
            '11-monthly' => '11 Month Subscription',
            'yearly' => 'Annual Subscription',
        ];
        $challanMonths = $challan->other_months
            ? array_filter(array_map('trim', explode(',', $challan->other_months)))
            : [];
        $subscriptionByCount = [
            1 => 'monthly',
            2 => 'bi-monthly',
            3 => 'quarterly',
            4 => '4-monthly',
            5 => '5-monthly',
            6 => '6-monthly',
            7 => '7-monthly',
            8 => '8-monthly',
            9 => '9-monthly',
            10 => '10-monthly',
            11 => '11-monthly',
            12 => 'yearly',
        ];
        $currentDuration = max(1, count($challanMonths));
        $selectedSubscription = $subscriptionByCount[$currentDuration] ?? 'monthly';
    @endphp

    <style>
        .legacy-split {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 3fr) minmax(320px, 2fr);
        }

        .revision-option {
            align-items: center;
            display: grid;
            gap: 8px;
            grid-template-columns: 24px minmax(180px, 1fr) 100px 100px 70px;
            margin-bottom: 6px;
        }

        .revision-option:last-child {
            margin-bottom: 0;
        }

        .revision-option-header {
            color: #6c757d;
            display: grid;
            font-size: 12px;
            font-weight: 600;
            gap: 8px;
            grid-template-columns: 24px minmax(180px, 1fr) 100px 100px 70px;
            margin-bottom: 6px;
        }

        .legacy-money-cell {
            min-width: 110px;
        }

        @media (max-width: 1100px) {
            .legacy-split {
                grid-template-columns: 1fr;
            }

            .revision-option {
                grid-template-columns: 24px 1fr;
            }

            .revision-option-header {
                display: none;
            }
        }
    </style>

    <form id="legacy-challan-form" action="{{ route('challan.legacy_update', $challan->id) }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">{{ $challan->challan_type ?: 'Regular' }} Challan</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <a href="{{ route('challan.show', ['id' => $challan->id, 'type' => 'print']) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary">
                                {{ __('Print') }}
                            </a>

                            @if ($editMode)
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Update') }}</button>
                            @elseif ($canEdit)
                                <a href="{{ route('challan.legacy_show', ['id' => $challan->id, 'mode' => 'edit', 'session_id' => $selectedSessionId]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    {{ __('Edit') }}
                                </a>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    disabled>{{ __('Edit') }}</button>
                            @endif

                            @if ($canRollback)
                                <button type="submit" form="legacy-rollback-form" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Rollback this challan?');">
                                    {{ __('Rollback') }}
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    disabled>{{ __('Rollback') }}</button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('challan_no', __('Challan No'), ['class' => 'form-label']) }}
                                    {{ Form::text('challan_no', $challan->challanNo, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('fee_month', __('Fee Month'), ['class' => 'form-label']) }}
                                    {{ Form::text('fee_month', $feeMonthLabel, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('issue_date', $challan->issue_date, array_merge(['class' => 'form-control', 'required' => 'required'], $readonly)) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('due_date', $challan->due_date, array_merge(['class' => 'form-control', 'required' => 'required'], $readonly)) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('challan_type', __('Challan Type'), ['class' => 'form-label']) }}
                                    {{ Form::text('challan_type', $challan->challan_type ?: '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('student_name', __('Student Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('student_name', optional($student)->stdname ?: '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('father_name', __('Father Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('father_name', optional($student)->fathername ?: '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('roll_no', __('Roll Number'), ['class' => 'form-label']) }}
                                    {{ Form::text('roll_no', $rollNumber, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_id', $sessions, $selectedSessionId, array_merge(['id' => 'legacy_session_id', 'class' => 'form-control'], $disabled)) }}
                                    @if (!$editMode)
                                        <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('class_name', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::text('class_name', $studentClass, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('section_name', __('Section'), ['class' => 'form-label']) }}
                                    {{ Form::text('section_name', $studentSection, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('branch_name', __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::text('branch_name', optional($challan->branch)->name ?: '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('net_amount', __('Net Amount'), ['class' => 'form-label']) }}
                                    {{ Form::text('net_amount', number_format($totalNet, 2), ['id' => 'legacy_net_amount', 'class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('paid_amount', __('Paid Amount'), ['class' => 'form-label']) }}
                                    {{ Form::text('paid_amount', number_format((float) ($challan->paid_amount ?? 0), 2), ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('fee_subscription', __('Fee Subscription'), ['class' => 'form-label']) }}
                                    {{ Form::select('fee_subscription', $subscription, $selectedSubscription, array_merge(['id' => 'legacy_fee_subscription', 'class' => 'form-control'], $disabled)) }}
                                    @if (!$editMode)
                                        <input type="hidden" name="fee_subscription" value="{{ $selectedSubscription }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('concession_policy_id', __('Concession Policy'), ['class' => 'form-label']) }}
                                    {{ Form::select('concession_policy_id', $concessionPolicyOptions, $selectedConcessionPolicyId, array_merge(['id' => 'legacy_concession_policy_id', 'class' => 'form-control'], $disabled)) }}
                                    @if (!$editMode)
                                        <input type="hidden" name="concession_policy_id"
                                            value="{{ $selectedConcessionPolicyId }}">
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('remarks', $challan->remarks ?: 'FEE CHALLAN FOR THE MONTH OF ' . strtoupper($feeMonthLabel), array_merge(['class' => 'form-control', 'rows' => 2], $readonly)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Challan Heads') }}</h5>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table_heads">
                                    <tr>
                                        <th style="width: 55px;">{{ __('Action') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th class="text-end">{{ __('Discount') }}</th>
                                        <th class="text-end">{{ __('Net Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($challanHeadRows as $row)
                                        @php
                                            $selectedOption =
                                                $row['options']->firstWhere('value', $row['selected']) ?:
                                                $row['options']->first();
                                            $headPrice = $row['is_challan_head']
                                                ? (float) $row['challan_base']
                                                : (float) ($selectedOption['base_amount'] ?? 0);
                                            $headNet = $row['is_challan_head']
                                                ? (float) $row['challan_payable']
                                                : (float) ($selectedOption['payable_amount'] ?? 0);
                                            $headDiscount = max(0, $headPrice - $headNet);
                                        @endphp
                                        @php
                                            $headNameLower = strtolower($row['head_name'] ?? '');
                                            $isSingleChargeHead =
                                                str_contains($headNameLower, 'admission fee') ||
                                                str_contains($headNameLower, 'annual fee') ||
                                                str_contains($headNameLower, 'late fee');
                                            $displayBase = $row['checked'] ? $headPrice : 0;
                                            $displayNet = $row['checked'] ? $headNet : 0;
                                            $displayDiscount = max(0, $displayBase - $displayNet);
                                            $existingBaseUnit =
                                                !$isSingleChargeHead && $currentDuration > 1
                                                    ? $row['challan_base'] / $currentDuration
                                                    : $row['challan_base'];
                                            $existingNetUnit =
                                                !$isSingleChargeHead && $currentDuration > 1
                                                    ? $row['challan_payable'] / $currentDuration
                                                    : $row['challan_payable'];
                                        @endphp
                                        <tr class="legacy-head-row" data-head-id="{{ $row['head_id'] }}"
                                            data-head-name="{{ $headNameLower }}">
                                            <td>
                                                @if ($row['paid'] > 0 && $row['checked'])
                                                    <input type="hidden" name="checked_heads[]"
                                                        value="{{ $row['head_id'] }}">
                                                @endif
                                                <input type="checkbox" class="legacy-head-check" name="checked_heads[]"
                                                    value="{{ $row['head_id'] }}" {{ $row['checked'] ? 'checked' : '' }}
                                                    {{ !$editMode || $row['paid'] > 0 ? 'disabled' : '' }}>
                                                <input type="hidden" name="existing_base_amount[{{ $row['head_id'] }}]"
                                                    value="{{ $existingBaseUnit }}">
                                                <input type="hidden" name="existing_payable_amount[{{ $row['head_id'] }}]"
                                                    value="{{ $existingNetUnit }}">
                                            </td>
                                            <td>
                                                {{ $row['head_name'] }}
                                                @if (!$row['is_challan_head'])
                                                    <span
                                                        class="badge bg-light text-dark">{{ __('Structure Head') }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ __('Challan Head') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end legacy-money-cell legacy-head-base"
                                                data-existing="{{ $existingBaseUnit }}">
                                                {{ number_format($displayBase, 2) }}
                                            </td>
                                            <td class="text-end legacy-money-cell legacy-head-discount">
                                                {{ number_format($displayDiscount, 2) }}
                                            </td>
                                            <td class="text-end legacy-money-cell legacy-head-net"
                                                data-existing="{{ $existingNetUnit }}">
                                                {{ number_format($displayNet, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">{{ __('No challan heads found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td></td>
                                        <td class="text-end fw-bold">{{ __('Total') }}</td>
                                        <td class="text-end fw-bold" id="legacy_total_base">
                                            {{ number_format($totalGross, 2) }}</td>
                                        <td class="text-end fw-bold" id="legacy_total_discount">
                                            {{ number_format($totalDiscount, 2) }}</td>
                                        <td class="text-end fw-bold" id="legacy_total_net">
                                            {{ number_format($totalNet, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="legacy-split mt-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                {{ __('Student Fee Structure History') }}
                                <span class="text-muted">
                                    ({{ $session->year ?? $session->title ?? __('No Session') }})
                                </span>
                            </h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table_heads">
                                        <tr>
                                            <th>{{ __('Head') }}</th>
                                            <th>{{ __('Revised Amounts') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($structureHistory as $row)
                                            <tr>
                                                <td>
                                                    <strong>{{ $row['head_name'] }}</strong>
                                                    <div class="text-muted">
                                                        {{ $row['is_challan_head'] ? __('Challan Head') : __('Structure Head') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($row['options']->isNotEmpty() || $row['is_challan_head'])
                                                        <div class="revision-option-header">
                                                            <span></span>
                                                            <span>{{ __('Option') }}</span>
                                                            <span class="text-end">{{ __('Base Amount') }}</span>
                                                            <span class="text-end">{{ __('Payable') }}</span>
                                                            <span class="text-end">{{ __('Percent') }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($row['is_challan_head'])
                                                        @php
                                                            $historyHeadNameLower = strtolower($row['head_name'] ?? '');
                                                            $historySingleChargeHead =
                                                                str_contains($historyHeadNameLower, 'admission fee') ||
                                                                str_contains($historyHeadNameLower, 'annual fee') ||
                                                                str_contains($historyHeadNameLower, 'late fee');
                                                            $historyBaseUnit =
                                                                !$historySingleChargeHead && $currentDuration > 1
                                                                    ? $row['challan_base'] / $currentDuration
                                                                    : $row['challan_base'];
                                                            $historyPayableUnit =
                                                                !$historySingleChargeHead && $currentDuration > 1
                                                                    ? $row['challan_payable'] / $currentDuration
                                                                    : $row['challan_payable'];
                                                        @endphp
                                                        <label class="revision-option">
                                                            <input type="radio" class="legacy-revision-radio"
                                                                name="selected_revision[{{ $row['head_id'] }}]"
                                                                value="challan:{{ $row['head_id'] }}"
                                                                data-head-id="{{ $row['head_id'] }}"
                                                                data-base="{{ $historyBaseUnit }}"
                                                                data-payable="{{ $historyPayableUnit }}"
                                                                {{ empty($row['selected']) ? 'checked' : '' }}
                                                                {{ $editMode ? '' : 'disabled' }}>
                                                            <span>
                                                                {{ __('Current Challan Amount') }}
                                                                <small class="d-block text-muted">
                                                                    {{ __('Already attached with this challan') }}
                                                                </small>
                                                            </span>
                                                            <span class="text-end legacy-option-base"
                                                                data-head-id="{{ $row['head_id'] }}"
                                                                data-base="{{ $historyBaseUnit }}"
                                                                data-multiply="subscription">
                                                                {{ number_format($row['challan_base'], 2) }}
                                                            </span>
                                                            <span class="text-end legacy-option-payable"
                                                                data-head-id="{{ $row['head_id'] }}"
                                                                data-base="{{ $historyBaseUnit }}"
                                                                data-multiply="subscription">
                                                                {{ number_format($row['challan_payable'], 2) }}
                                                            </span>
                                                            <span class="text-end">-</span>
                                                        </label>
                                                    @endif

                                                    @if ($row['options']->isNotEmpty())
                                                        @foreach ($row['options'] as $option)
                                                            <label class="revision-option">
                                                                <input type="radio" class="legacy-revision-radio"
                                                                    name="selected_revision[{{ $row['head_id'] }}]"
                                                                    value="{{ $option['value'] }}"
                                                                    data-head-id="{{ $row['head_id'] }}"
                                                                    data-base="{{ $option['base_amount'] }}"
                                                                    data-payable="{{ $option['payable_amount'] }}"
                                                                    {{ $row['selected'] === $option['value'] ? 'checked' : '' }}
                                                                    {{ $editMode ? '' : 'disabled' }}>
                                                                <span>
                                                                    {{ $option['label'] }}
                                                                    <small class="d-block text-muted">
                                                                        {{ $option['context'] }}
                                                                        @if ($option['effective_from'])
                                                                            |
                                                                            {{ \Carbon\Carbon::parse($option['effective_from'])->format('d-M-Y') }}
                                                                        @endif
                                                                    </small>
                                                                </span>
                                                                <span class="text-end legacy-option-base"
                                                                    data-head-id="{{ $row['head_id'] }}"
                                                                    data-base="{{ $option['base_amount'] }}">
                                                                    {{ number_format($option['base_amount'], 2) }}
                                                                </span>
                                                                <span class="text-end legacy-option-payable"
                                                                    data-head-id="{{ $row['head_id'] }}"
                                                                    data-base="{{ $option['base_amount'] }}">
                                                                    {{ number_format($option['payable_amount'], 2) }}
                                                                </span>
                                                                <span class="text-end">
                                                                    {{ $option['percentage'] === null ? '-' : number_format($option['percentage'], 2) . '%' }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">
                                                            {{ __('No revised structure found. Existing challan amount will remain if checked.') }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center">
                                                    {{ __('No fee structure history found for this session.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Student Challans by Month') }}</h5>
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table_heads">
                                        <tr>
                                            <th>{{ __('Month') }}</th>
                                            <th>{{ __('Challan No') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-end">{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($studentChallans as $studentChallan)
                                            @php
                                                $monthText = $studentChallan->other_months
                                                    ? collect(explode(',', $studentChallan->other_months))
                                                        ->filter()
                                                        ->map(
                                                            fn($date) => \Carbon\Carbon::parse(trim($date))->format(
                                                                'M-Y',
                                                            ),
                                                        )
                                                        ->implode(', ')
                                                    : ($studentChallan->fee_month
                                                        ? \Carbon\Carbon::parse($studentChallan->fee_month)->format(
                                                            'M-Y',
                                                        )
                                                        : '-');
                                                $payable =
                                                    (float) $studentChallan->total_amount -
                                                    (float) $studentChallan->concession_amount;
                                            @endphp
                                            <tr>
                                                <td>{{ $monthText }}</td>
                                                <td>
                                                    <a href="{{ route('challan.legacy_show', $studentChallan->id) }}" target="_blank">
                                                        {{ $studentChallan->challanNo }}
                                                    </a>
                                                </td>
                                                <td>{{ $studentChallan->challan_type }}</td>
                                                <td class="text-end">{{ number_format($payable, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    {{ __('No challans found for this student.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($canRollback)
        <form id="legacy-rollback-form" action="{{ route('challan.legacy_rollback', $challan->id) }}" method="POST"
            class="d-none">
            @csrf
        </form>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const money = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const subscriptionMap = {
                'monthly': 1,
                'bi-monthly': 2,
                'quarterly': 3,
                '4-monthly': 4,
                '5-monthly': 5,
                '6-monthly': 6,
                '7-monthly': 7,
                '8-monthly': 8,
                '9-monthly': 9,
                '10-monthly': 10,
                '11-monthly': 11,
                'yearly': 12
            };
            const concessionPolicyPercentages = @json($concessionPolicyPercentages);

            function parseAmount(value) {
                const amount = parseFloat(String(value || '0').replace(/,/g, ''));
                return Number.isFinite(amount) ? amount : 0;
            }

            function selectedRevisionForHead(headId) {
                return document.querySelector(`.legacy-revision-radio[data-head-id="${headId}"]:checked`);
            }

            function firstRevisionForHead(headId) {
                return document.querySelector(`.legacy-revision-radio[data-head-id="${headId}"]`);
            }

            function selectedPolicyId() {
                return document.getElementById('legacy_concession_policy_id')?.value || '';
            }

            function policyPercentage(headId) {
                const policyId = selectedPolicyId();
                if (!policyId || !concessionPolicyPercentages[policyId]) {
                    return 0;
                }

                return parseAmount(concessionPolicyPercentages[policyId][headId] || 0);
            }

            function payableFromPolicy(base, headId) {
                const discount = Math.round(base * (policyPercentage(headId) / 100));
                return Math.max(0, base - discount);
            }

            function getDuration() {
                const subscription = document.getElementById('legacy_fee_subscription')?.value || 'monthly';
                return subscriptionMap[subscription] || 1;
            }

            function rowMultiplier(row) {
                const headName = (row.dataset.headName || '').toLowerCase();
                if (headName.includes('admission fee') || headName.includes('annual fee') || headName.includes(
                        'late fee')) {
                    return 1;
                }

                return getDuration();
            }

            function amountsForRow(row) {
                const headId = row.dataset.headId;
                const checkbox = row.querySelector('.legacy-head-check');
                const baseCell = row.querySelector('.legacy-head-base');
                const netCell = row.querySelector('.legacy-head-net');
                const multiplier = rowMultiplier(row);

                if (!checkbox || !checkbox.checked) {
                    return {
                        base: 0,
                        net: 0
                    };
                }

                let selectedRadio = selectedRevisionForHead(headId);
                if (!selectedRadio) {
                    selectedRadio = firstRevisionForHead(headId);
                    const isExistingHead = parseAmount(baseCell?.dataset.existing) > 0 || parseAmount(netCell
                        ?.dataset.existing) > 0;
                    if (selectedRadio && !isExistingHead) {
                        selectedRadio.checked = true;
                    }
                }

                if (selectedRadio && selectedRadio.checked) {
                    const base = parseAmount(selectedRadio.dataset.base);
                    return {
                        base: base * multiplier,
                        net: payableFromPolicy(base, headId) * multiplier
                    };
                }

                const base = parseAmount(baseCell?.dataset.existing);
                return {
                    base: base * multiplier,
                    net: payableFromPolicy(base, headId) * multiplier
                };
            }

            function updateRow(row) {
                const amounts = amountsForRow(row);
                const discount = Math.max(0, amounts.base - amounts.net);

                row.querySelector('.legacy-head-base').textContent = money.format(amounts.base);
                row.querySelector('.legacy-head-discount').textContent = money.format(discount);
                row.querySelector('.legacy-head-net').textContent = money.format(amounts.net);
            }

            function updateTotals() {
                let totalBase = 0;
                let totalNet = 0;

                document.querySelectorAll('.legacy-option-base').forEach(function(cell) {
                    const base = parseAmount(cell.dataset.base);
                    const headId = cell.dataset.headId;
                    let multiplier = 1;
                    if (cell.dataset.multiply === 'subscription') {
                        const row = document.querySelector(`.legacy-head-row[data-head-id="${headId}"]`);
                        multiplier = row ? rowMultiplier(row) : getDuration();
                    } else {
                        multiplier = parseAmount(cell.dataset.multiply || 0) || 1;
                    }
                    cell.textContent = money.format(base * multiplier);
                });

                document.querySelectorAll('.legacy-option-payable').forEach(function(cell) {
                    const base = parseAmount(cell.dataset.base);
                    const headId = cell.dataset.headId;
                    let multiplier = 1;
                    if (cell.dataset.multiply === 'subscription') {
                        const row = document.querySelector(`.legacy-head-row[data-head-id="${headId}"]`);
                        multiplier = row ? rowMultiplier(row) : getDuration();
                    } else {
                        multiplier = parseAmount(cell.dataset.multiply || 0) || 1;
                    }
                    cell.textContent = money.format(payableFromPolicy(base, headId) * multiplier);
                });

                document.querySelectorAll('.legacy-head-row').forEach(function(row) {
                    updateRow(row);
                    const amounts = amountsForRow(row);
                    totalBase += amounts.base;
                    totalNet += amounts.net;
                });

                const totalDiscount = Math.max(0, totalBase - totalNet);

                const totalBaseCell = document.getElementById('legacy_total_base');
                const totalDiscountCell = document.getElementById('legacy_total_discount');
                const totalNetCell = document.getElementById('legacy_total_net');
                const netInput = document.getElementById('legacy_net_amount');

                if (totalBaseCell) totalBaseCell.textContent = money.format(totalBase);
                if (totalDiscountCell) totalDiscountCell.textContent = money.format(totalDiscount);
                if (totalNetCell) totalNetCell.textContent = money.format(totalNet);
                if (netInput) netInput.value = money.format(totalNet);
            }

            document.querySelectorAll('.legacy-head-check').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('.legacy-head-row');
                    const headId = row?.dataset.headId;
                    if (this.checked && headId && !selectedRevisionForHead(headId)) {
                        const firstRadio = firstRevisionForHead(headId);
                        const hasExistingAmount = parseAmount(row.querySelector('.legacy-head-base')
                                ?.dataset.existing) > 0 ||
                            parseAmount(row.querySelector('.legacy-head-net')?.dataset.existing) >
                            0;
                        if (firstRadio && !hasExistingAmount) {
                            firstRadio.checked = true;
                        }
                    }
                    updateTotals();
                });
            });

            document.querySelectorAll('.legacy-revision-radio').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    const checkbox = document.querySelector(
                        `.legacy-head-check[value="${this.dataset.headId}"]`);
                    if (checkbox && !checkbox.disabled) {
                        checkbox.checked = true;
                    }
                    updateTotals();
                });
            });

            const subscriptionSelect = document.getElementById('legacy_fee_subscription');
            if (subscriptionSelect) {
                subscriptionSelect.addEventListener('change', updateTotals);
            }

            const concessionPolicySelect = document.getElementById('legacy_concession_policy_id');
            if (concessionPolicySelect) {
                concessionPolicySelect.addEventListener('change', updateTotals);
            }

            const issueDateInput = document.querySelector('input[name="issue_date"]');
            if (issueDateInput) {
                issueDateInput.addEventListener('change', updateTotals);
            }

            updateTotals();

            const sessionSelect = document.getElementById('legacy_session_id');
            if (sessionSelect) {
                sessionSelect.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('mode', 'edit');
                    url.searchParams.set('session_id', this.value);
                    window.location.href = url.toString();
                });
            }
        });
    </script>
@endsection
