<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size:0.8rem;
    }
    th{text-align: left;}
    #periodtext{
        display:none;}
</style>
<table style="width:100%; margin-top:100px;">
    <thead>
        <tr style="background:gray; text-align:center; ">
            <th>S.R</th>
            <th>T.FEE STRUCTURE</th>
            <th>No of Student</th>
            <th style=" color:red;">Total</th>
        </tr>
    </thead>
    @php
        $total = 0;
        $totaldiscount = 0;
        $totalamount = 0;
        $currentRangeStart = 0;
    @endphp
    <tbody>
        <tbody>
            @foreach ($challanHeads as $session)
                @php
                    $total += $session->price_count;
                    $totaldiscount += $session->total_concession;
                    $totalamount += $session->total_price;
                @endphp
                {{-- @dd($session) --}}
                <tr style=" text-align:center;">
                    <td style=" text-align:center;">{{ $loop->iteration }}</td>
                    <td style=" text-align:center;">{{ $session->final_price }}</td>
                    <td style=" text-align:center;">{{ $session->price_count }}</td>
                    <td style=" text-align:center;">{{ $session->total_price }}</td>
                </tr>
            @endforeach
            <tr style="background:gray; ">
                <td colspan="2" style=" text-align:center; color:#fff">Total</td>
                <td style=" text-align:center; color:#fff">{{ $total }}</td>
                <td style=" text-align:center; color:#fff">{{ $totalamount }}</td>
                {{-- @dd($session) --}}
            </tr>
        </tbody>
    </table>
    <table class="table mt-3" style="margin-top:20px; width:100%">
        <thead>
            <tr style="background:gray; ">
                <th colspan="4" style=" text-align:center;"> REGULAR CHALLAN FEE INSIGHT REPROT {{ strtoupper(\Carbon\Carbon::parse(request()->get('month') ?? date('Y-m'))->format('F Y')) }}</th>
            </tr>
            <tr>
                <td colspan="3" style=" text-align:left;">TOTAL FEE OF BRANCH</td>
                <td style=" text-align:right;"> {{ $totalamount + $totaldiscount }}</td>
            </tr>
            <tr>
                <td colspan="3" style=" text-align:left;">DISCOUNTED AMOUNT</td>
                <td style=" text-align:right;"> {{ $totaldiscount }}</td>
            </tr>
            <tr>
                <td colspan="3" style=" text-align:left;">BRANCH FEE DISCOUNT %</td>
                <td style=" text-align:right;">@if(@$totalamount && @$totaldiscount){{ round($totalamount / $totaldiscount, 2) }}@else 0 @endif</td>
            </tr>
            <tr>
                <td colspan="3" style=" text-align:left;">AVERAGE FEE OF BRANCH</td>
                <td style=" text-align:right;">@if(@$totalamount && @$total){{ round($totalamount / $total, 2) }}@else 0 @endif</td>
            </tr>
        </tbody>
    </table>
<table class="table mt-3" style="margin-top:10px; width:50%">
    <thead>
        <tr >
            <th style="background:gray; text-align:center;">Fee Range</th>
            <th style="background:gray; text-align:center;">Total Students</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ranges as $range)
            <tr >
                <td style="text-align:center; ">{{ $range['start'] }} - {{ $range['end'] }}</td>
                <td style="text-align:center; ">{{ $range['total_students'] }}</td>
            </tr>
        @endforeach
            <tr style="background:gray; ">
                <td style=" text-align:center; color:#fff">Total</td>
                <td style=" text-align:center; color:#fff">{{ $total }}</td>
            </tr>
    </tbody>
</table>
