@php
    $firstBatch = $report->first();
    $sessionFromLabel = optional($firstBatch?->sessionFrom)->year ?? 'Previous Session';
    $sessionToLabel = optional($firstBatch?->sessionTo)->year ?? 'New Session';
    $groupedByBranch = $report
        ->sortBy(fn($batch) => strtolower(optional($batch->registration)->stdname ?? ''))
        ->groupBy(fn($batch) => $batch->branch_to_id ?: $batch->owned_by ?: 'unassigned');
@endphp

@if ($report->count())
    <div style="margin-top:18px;">
        <table class="{{ $tableClass ?? '' }}" style="width:100%; table-layout:fixed; border-collapse:collapse;">
            <colgroup>
                <col style="width:22%;">
                <col style="width:12%;">
                <col style="width:12%;">
                <col style="width:12%;">
                <col style="width:12%;">
                <col style="width:10%;">
                <col style="width:10%;">
                <col style="width:10%;">
            </colgroup>
            <thead class="{{ $theadClass ?? '' }}">
                <tr class="{{ $headerClass ?? '' }}">
                    <th rowspan="2" style="text-align:center; vertical-align:middle;">Heads</th>
                    <th colspan="2" style="text-align:center; vertical-align:middle;">Session {{ $sessionFromLabel }}</th>
                    <th colspan="2" style="text-align:center; vertical-align:middle;">Session {{ $sessionToLabel }}</th>
                    <th rowspan="2" style="text-align:center; vertical-align:middle;">Percentage (%)</th>
                    <th rowspan="2" style="text-align:center; vertical-align:middle;">Diff (+/-)</th>
                    <th rowspan="2" style="text-align:center; vertical-align:middle;">Inc/Dec</th>
                </tr>
                <tr class="{{ $headerClass ?? '' }}">
                    <th style="text-align:center; vertical-align:middle;">Base Amount</th>
                    <th style="text-align:center; vertical-align:middle;">Prev Payable</th>
                    <th style="text-align:center; vertical-align:middle;">New Base Amount</th>
                    <th style="text-align:center; vertical-align:middle;">New Payable</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedByBranch as $branchId => $branchBatches)
                    @php
                        $branchName = optional($branchBatches->first()->branchTo)->name
                            ?? optional($branchBatches->first()->branchFrom)->name
                            ?? ($branches[$branchId] ?? 'Branch Not Specified');
                        $studentGroups = $branchBatches
                            ->sortBy(fn($batch) => strtolower(optional($batch->registration)->stdname ?? ''))
                            ->groupBy('reg_id');
                    @endphp
                    <tr>
                        <td colspan="8" style="font-weight:bold; background:#bcbcbc; text-align:left;">
                            {{ $branchName }}
                        </td>
                    </tr>

                    @foreach ($studentGroups as $studentId => $studentBatches)
                        @php
                            $student = $studentBatches->first()->registration;
                            $studentLabel = trim(
                                (optional($studentBatches->first()->student)->enrollId ? optional($studentBatches->first()->student)->enrollId . ' - ' : '')
                                . ($student->stdname ?? '-')
                                . (($student->fathername ?? null) ? ' s/d/o ' . $student->fathername : '')
                            );
                            $totals = [
                                'prev_base_amount' => 0,
                                'new_base_amount' => 0,
                                'prev_payable_amount' => 0,
                                'new_payable_amount' => 0,
                            ];
                        @endphp
                        <tr>
                            <td colspan="8" style="font-weight:bold; background:#e9e9e9; text-align:left;">
                                {{ $studentLabel }}
                            </td>
                        </tr>

                        @foreach ($studentBatches as $batch)
                            @foreach ($batch->items->sortBy(fn($item) => optional($item->feehead)->fee_head ?? '') as $item)
                                @php
                                    $discountPercentage = (float) ($concessionHeadPercentages[$batch->reg_id][$item->head_id] ?? 0);
                                    $prevPayable = round((float) ($item->prev_base_amount ?? 0) - (((float) ($item->prev_base_amount ?? 0) * $discountPercentage) / 100));
                                    $newPayable = round((float) ($item->new_base_amount ?? 0) - (((float) ($item->new_base_amount ?? 0) * $discountPercentage) / 100));
                                    $diff = $newPayable - $prevPayable;
                                    $movement = $diff > 0 ? 'Incremented' : ($diff < 0 ? 'Decremented' : 'No Change');
                                    $totals['prev_base_amount'] += (float) ($item->prev_base_amount ?? 0);
                                    $totals['new_base_amount'] += (float) ($item->new_base_amount ?? 0);
                                    $totals['prev_payable_amount'] += $prevPayable;
                                    $totals['new_payable_amount'] += $newPayable;
                                @endphp
                                <tr>
                                    <td style="text-align:left; vertical-align:middle; word-wrap:break-word;">{{ optional($item->feehead)->fee_head ?? '-' }}</td>
                                    <td style="text-align:right;">{{ number_format((float) ($item->prev_base_amount ?? 0), 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($prevPayable, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format((float) ($item->new_base_amount ?? 0), 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($newPayable, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format((float) ($item->percentage ?? 0), 2) }}%</td>
                                    <td style="text-align:right;">{{ number_format($diff, 2) }}</td>
                                    <td style="text-align:center;">{{ $movement }}</td>
                                </tr>
                            @endforeach
                        @endforeach

                        @php $studentDiffTotal = $totals['new_payable_amount'] - $totals['prev_payable_amount']; @endphp
                        <tr>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:center;">Student Total</td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:right;">{{ number_format($totals['prev_base_amount'], 2) }}</td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:right;">{{ number_format($totals['prev_payable_amount'], 2) }}</td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:right;">{{ number_format($totals['new_base_amount'], 2) }}</td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:right;">{{ number_format($totals['new_payable_amount'], 2) }}</td>
                            <td style="font-weight:bold; background:#f2f2f2;"></td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:right;">{{ number_format($studentDiffTotal, 2) }}</td>
                            <td style="font-weight:bold; background:#f2f2f2; text-align:center;"></td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <table class="{{ $tableClass ?? '' }}" style="width:100%;">
        <tbody>
            <tr>
                <td style="text-align:center;">No fee revision record found.</td>
            </tr>
        </tbody>
    </table>
@endif
