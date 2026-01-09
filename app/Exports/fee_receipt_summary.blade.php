@include('student.exports.header')
<table style="width: 100%; margin-bottom: 18px; margin-top: 100px; font-size: 8px; font-family: calibri; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f0f0f0; font-weight: bold; font-size: 8px; font-family: calibri;">
            <th rowspan="2" style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Date</th>
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th colspan="2" style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ strtoupper($branch) }}</th>
                @endif
            @endforeach
            <th colspan="2" style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">TOTAL</th>
        </tr>
        <tr style="background-color: #f0f0f0; font-weight: bold; font-size: 8px; font-family: calibri;">
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    <th style="border: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">Receipt #</th>
                    <th style="border: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">Amount</th>
                @endif
            @endforeach
            <th style="border: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">Receipt #</th>
            <th style="border: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotalReceipts = 0;
            $grandTotalAmount = 0;
        @endphp

        @for ($date = $params['from_date']; $date <= $params['to_date']; $date->addDay())
            @php
                $hasData = false;
                $dateTotalReceipts = 0;
                $dateTotalAmount = 0;
            @endphp

            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    @php
                        $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                            return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by == $key;
                        });
                    @endphp
                    @if ($branchReceipts->isNotEmpty())
                        @php
                            $hasData = true;
                        @endphp
                    @endif
                @endif
            @endforeach

            @if ($hasData)
                <tr style="font-size: 8px; font-family: calibri;">
                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $date->format('d-M-y') }}</td>
                    @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                            @php
                                $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                                    return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by == $key;
                                });
                                $receiptCount = $branchReceipts->count();
                                $totalAmount = $branchReceipts->sum(function ($receipt) {
                                    return $receipt->voucher->sum('credit');
                                });
                                $dateTotalReceipts += $receiptCount;
                                $dateTotalAmount += $totalAmount;
                            @endphp
                            <td style="border: 1px solid #D3D3D3; border-top: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">{{ $receiptCount }}</td>
                            <td style="border: 1px solid #D3D3D3; border-top: 1px dotted black; font-size: 8px; font-family: calibri; text-align: right;">{{ number_format($totalAmount, 0) }}</td>
                        @endif
                    @endforeach
                    <td style="border: 1px solid #D3D3D3; border-top: 1px dotted black; font-size: 8px; font-family: calibri; text-align: center;">{{ $dateTotalReceipts }}</td>
                    <td style="border: 1px solid #D3D3D3; border-top: 1px dotted black; font-size: 8px; font-family: calibri; text-align: right;">{{ number_format($dateTotalAmount, 0) }}</td>
                </tr>
                @php
                    $grandTotalReceipts += $dateTotalReceipts;
                    $grandTotalAmount += $dateTotalAmount;
                @endphp
            @endif
        @endfor
        
        <!-- Grand Total Row -->
        <tr style="font-weight: bold; background-color: #f0f0f0; font-size: 8px; font-family: calibri;">
            <td style="border: 2px solid black; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">TOTAL</td>
            @php $branchTotals = []; @endphp
            @foreach ($branches as $key => $branch)
                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                    @php
                        $branchTotalReceipts = $recipts->filter(function ($receipt) use ($key) {
                            return $receipt->owned_by == $key;
                        })->count();
                        $branchTotalAmount = $recipts->filter(function ($receipt) use ($key) {
                            return $receipt->owned_by == $key;
                        })->sum(function ($receipt) {
                            return $receipt->voucher->sum('credit');
                        });
                    @endphp
                    <td style="border: 2px solid black; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">{{ $branchTotalReceipts }}</td>
                    <td style="border: 2px solid black; font-size: 8px; font-family: calibri; text-align: right; font-weight: bold;">{{ number_format($branchTotalAmount, 0) }}</td>
                @endif
            @endforeach
            <td style="border: 2px solid black; font-size: 8px; font-family: calibri; text-align: center; font-weight: bold;">{{ $grandTotalReceipts }}</td>
            <td style="border: 2px solid black; font-size: 8px; font-family: calibri; text-align: right; font-weight: bold;">{{ number_format($grandTotalAmount, 0) }}</td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')


