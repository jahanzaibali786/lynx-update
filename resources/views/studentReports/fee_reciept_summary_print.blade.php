<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    th, td {
        padding: 4px 6px;
        font-size: 0.85rem;
    }
    th {
        background-color: #f0f0f0;
        font-weight: bold;
    }
    .branch-title {
        font-size: 1rem;
        font-weight: 600;
        padding: 10px 0 4px 0;
        width: 100%;
        display: block;
    }
</style>

@php
    $startDate = \Carbon\Carbon::parse($fromDate);
    $endDate = \Carbon\Carbon::parse($toDate);
    $grandTotalReceipts = 0;
    $grandTotalAmount = 0;
@endphp

@foreach ($branches as $branchKey => $branchName)
    @if ($branchKey !== '' && ($selectedBranch === null || $selectedBranch == $branchKey))
        <span class="branch-title">{{ $branchName }}</span>
        <table style="width: 100%; margin-bottom: 18px;">
            <thead>
                <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                    <th style="width:20%; border: 1px solid black;">{{__('Date')}}</th>
                    <th style="width:40%; border: 1px solid black;">{{__('Receipts')}}</th>
                    <th style="width:40%; border: 1px solid black;">{{__('Amount')}}</th>
                </tr>
            </thead>
            <tbody style="font-size:0.7rem;">
                @php
                    $branchTotalReceipts = 0;
                    $branchTotalAmount = 0;
                @endphp
                @for ($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                    @php
                        $branchReceipts = $recipts->filter(function ($receipt) use ($date, $branchKey) {
                            return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by == $branchKey;
                        });
                        $receiptCount = $branchReceipts->count();
                        $totalAmount = $branchReceipts->sum(function ($receipt) {
                            return $receipt->voucher->sum('credit');
                        });
                        $branchTotalReceipts += $receiptCount;
                        $branchTotalAmount += $totalAmount;
                        $grandTotalReceipts += $receiptCount;
                        $grandTotalAmount += $totalAmount;
                    @endphp
                    @if ($receiptCount > 0)
                        <tr>
                            <td style="border: 1px solid black;">{{ $date->format('d-M-Y') }}</td>
                            <td style="border: 1px solid black;">{{ $receiptCount }}</td>
                            <td style="border: 1px solid black;">{{ $totalAmount }}</td>
                        </tr>
                    @endif
                @endfor
                <tr style="font-weight:bold;">
                    <td style="text-align:right; border: 1px solid black;">Branch Total:</td>
                    <td style="border: 1px solid black;">{{ $branchTotalReceipts }}</td>
                    <td style="border: 1px solid black;">{{ $branchTotalAmount }}</td>
                </tr>
            </tbody>
        </table>
    @endif
@endforeach

<table style="width:100%; margin-top: 24px;">
    <thead style="font-weight:bold; background-color:grey; color:#fff;">
        <tr>
            <td style="width:60%; text-align:right; border: 1px solid black;">Grand Total :</td>
            <td style="width:20%; border: 1px solid black;">{{ $grandTotalReceipts }}</td>
            <td style="width:20%; border: 1px solid black;">{{ $grandTotalAmount }}</td>
        </tr>
    </thead>
</table>










