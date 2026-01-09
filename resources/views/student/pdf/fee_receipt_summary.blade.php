@php
    // Convert dates to Carbon instances if they aren't already
    $fromDate = $params['from_date'] instanceof \Carbon\Carbon 
        ? $params['from_date'] 
        : \Carbon\Carbon::parse($params['from_date']);
    $toDate = $params['to_date'] instanceof \Carbon\Carbon 
        ? $params['to_date'] 
        : \Carbon\Carbon::parse($params['to_date']);

    // Pre-process receipt data for faster access
    $receiptsByDateBranch = [];
    $branchTotals = [];
    $grandTotalReceipts = 0;
    $grandTotalAmount = 0;

    foreach ($recipts as $receipt) {
        $receiptDate = $receipt->recipt_date instanceof \Carbon\Carbon 
            ? $receipt->recipt_date 
            : \Carbon\Carbon::parse($receipt->recipt_date);
        $dateKey = $receiptDate->format('Y-m-d');
        $branchKey = $receipt->owned_by;
        
        if (!isset($receiptsByDateBranch[$dateKey])) {
            $receiptsByDateBranch[$dateKey] = [];
        }
        
        if (!isset($receiptsByDateBranch[$dateKey][$branchKey])) {
            $receiptsByDateBranch[$dateKey][$branchKey] = [
                'count' => 0,
                'amount' => 0
            ];
        }
        
        $amount = $receipt->voucher->sum('credit');
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
<table style="width: 100%; margin-bottom: 18px; margin-top: 100px; font-size: 8px; font-family: calibri; border-collapse: collapse;">
    <thead>
        <tr style="background-color: gray; font-weight: bold; font-size: 8px; font-family: calibri;">
            <th rowspan="2" style="border: 1px solid black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Date</th>
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th colspan="2" style="border: 1px solid black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">{{ strtoupper($branch) }}</th>
                @endif
            @endforeach
            <th colspan="2" style="border: 1px solid black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">TOTAL</th>
        </tr>
        <tr style="background-color: gray; font-weight: bold; font-size: 8px; font-family: calibri;">
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th style="border: 1px dotted black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">Receipt #</th>
                    <th style="border: 1px dotted black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">Amount</th>
                @endif
            @endforeach
            <th style="border: 1px dotted black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">Receipt #</th>
            <th style="border: 1px dotted black; font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri; text-align: center;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @for ($date = clone $fromDate; $date <= $toDate; $date->addDay())
            @php
                $dateKey = $date->format('Y-m-d');
                $hasData = isset($receiptsByDateBranch[$dateKey]);
                $dateTotalReceipts = 0;
                $dateTotalAmount = 0;
            @endphp

            @if ($hasData)
                <tr style="font-size: 8px; font-family: calibri;">
                    <td style="border: 1px solid black; font-weight: bold; font-size: 8px; font-family: calibri; text-align: center;">{{ $date->format('d-M-y') }}</td>
                    @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                            @php
                                $branchData = $receiptsByDateBranch[$dateKey][$key] ?? ['count' => 0, 'amount' => 0];
                                $dateTotalReceipts += $branchData['count'];
                                $dateTotalAmount += $branchData['amount'];
                            @endphp
                            <td style="font-size: 8px; font-family: calibri; text-align: center;">{{ $branchData['count'] }}</td>
                            <td style="font-size: 8px; font-family: calibri; text-align: right;">{{ number_format($branchData['amount'], 0) }}</td>
                        @endif
                    @endforeach
                    <td style="font-size: 8px; font-family: calibri; text-align: center;">{{ $dateTotalReceipts }}</td>
                    <td style="font-size: 8px; font-family: calibri; text-align: right;">{{ number_format($dateTotalAmount, 0) }}</td>
                </tr>
            @endif
        @endfor
        
        <!-- Grand Total Row -->
        <tr style="font-weight: bold; background-color: gray; font-size: 8px; font-family: calibri;">
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