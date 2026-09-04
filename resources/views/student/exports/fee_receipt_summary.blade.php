@php
    // Deduplicate: group by branch → voucher_id, one entry per unique payment
    $deduplicatedReceipts = $recipts
        ->groupBy('owned_by')
        ->flatMap(function ($branchReceipts) {
            return $branchReceipts
                ->groupBy('voucher_id')
                ->map(function ($voucherGroup) {
                    $first = $voucherGroup->first();
                    return (object) [
                        'owned_by'    => $first->owned_by,
                        'recipt_date' => $first->recipt_date instanceof \Carbon\Carbon
                            ? $first->recipt_date
                            : \Carbon\Carbon::parse($first->recipt_date),
                        '_amount'     => $first->voucher->sum('credit'),
                    ];
                });
        });

    $receiptsByDateBranch = [];
    $branchTotals         = [];
    $grandTotalReceipts   = 0;
    $grandTotalAmount     = 0;

    foreach ($deduplicatedReceipts as $receipt) {
        $dateKey   = $receipt->recipt_date->format('Y-m-d');
        $branchKey = $receipt->owned_by;
        $amount    = $receipt->_amount;

        if (!isset($receiptsByDateBranch[$dateKey][$branchKey])) {
            $receiptsByDateBranch[$dateKey][$branchKey] = ['count' => 0, 'amount' => 0];
        }
        $receiptsByDateBranch[$dateKey][$branchKey]['count']++;
        $receiptsByDateBranch[$dateKey][$branchKey]['amount'] += $amount;

        if (!isset($branchTotals[$branchKey])) {
            $branchTotals[$branchKey] = ['count' => 0, 'amount' => 0];
        }
        $branchTotals[$branchKey]['count']++;
        $branchTotals[$branchKey]['amount'] += $amount;

        $grandTotalReceipts++;
        $grandTotalAmount += $amount;
    }
@endphp

@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2">Date</th>
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th colspan="2">{{ strtoupper($branch) }}</th>
                @endif
            @endforeach
            <th colspan="2">TOTAL</th>
        </tr>
        <tr>
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th>Receipt #</th>
                    <th>Amount</th>
                @endif
            @endforeach
            <th>Receipt #</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @for ($date = $params['date_from']; $date <= $params['date_to']; $date->addDay())
            @php
                $dateKey           = $date->format('Y-m-d');
                $hasData           = isset($receiptsByDateBranch[$dateKey]);
                $dateTotalReceipts = 0;
                $dateTotalAmount   = 0;
            @endphp

            @if ($hasData)
                <tr>
                    <td>{{ $date->format('d-M-y') }}</td>
                    @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                            @php
                                $branchData         = $receiptsByDateBranch[$dateKey][$key] ?? ['count' => 0, 'amount' => 0];
                                $dateTotalReceipts += $branchData['count'];
                                $dateTotalAmount   += $branchData['amount'];
                            @endphp
                            <td>{{ $branchData['count'] }}</td>
                            <td>{{ number_format($branchData['amount'], 0) }}</td>
                        @endif
                    @endforeach
                    <td>{{ $dateTotalReceipts }}</td>
                    <td>{{ number_format($dateTotalAmount, 0) }}</td>
                </tr>
            @endif
        @endfor

        <tr>
            <td style="border: 2px solid black; background-color: gray; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">TOTAL</td>
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <td style="border: 2px solid black; background-color: gray; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">{{ $branchTotals[$key]['count'] ?? 0 }}</td>
                    <td style="border: 2px solid black; background-color: gray; font-size: 8px; font-family: calibri; text-align: right; font-weight: bold;">{{ number_format($branchTotals[$key]['amount'] ?? 0, 0) }}</td>
                @endif
            @endforeach
            <td style="border: 2px solid black; background-color: gray; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">{{ $grandTotalReceipts }}</td>
            <td style="border: 2px solid black; background-color: gray; font-size: 8px; font-family: calibri; text-align: right; font-weight: bold;">{{ number_format($grandTotalAmount, 0) }}</td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')