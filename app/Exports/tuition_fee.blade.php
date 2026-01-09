<table class="datatable">
    <thead>
        <tr>
            <td colspan="5" style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 25rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center; font-weight: 600; font-size: 14rem;">
                <span>{{ $type}}</span>
            </td>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center; font-weight: 600; font-size: 12rem;">
                <span>FEE INSIGHT REPORT FOR THE MONTH OF {{ $month_name}}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;"></td>
        </tr>

        <tr >
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; background:gray;">S.R</th>
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; background:gray;">T.FEE STRUCTURE</th>
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; background:gray;">No of Student</th>
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; color:red; background:gray; text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @php
            $total = 0;
            $totaldiscount = 0;
            $totalamount = 0;
            $currentRangeStart = 0;
        @endphp
        @foreach ($challanHeads as $session)
            @php
                $total += $session->price_count;
                $totaldiscount += $session->total_concession;
                $totalamount += $session->total_price;
            @endphp
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:center;">{{ $loop->iteration }}</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:center;">{{ $session->final_price }}</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:center;">{{ $session->price_count }}</td>
                <td style="background:gray; text-align:center; border: 2px solid black; border-collapse: collapse;  color:red; text-align:center;">{{ $session->total_price }}</td>
            </tr>
        @endforeach
            <tr>
                <td colspan="2" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:center;">Total</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:center;">{{ $total }}</td>
                <td style="background:gray; text-align:center; border: 2px solid black; border-collapse: collapse;  color:red; text-align:center;">{{ $totalamount }}</td>
            </tr>
    </tbody>
</table>
<table class="datatable">
    <thead>
        <tr>
            <td colspan="4" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; background:gray; text-align:center;">
                REGULAR CHALLAN FEE INSIGHT REPROT {{ $month_name}}
            </td>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">

            <tr>
                <td  colspan="3" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:left;">TOTAL FEE OF BRANCH</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:right;">{{ $totalamount + $totaldiscount }}</td>
            </tr>
            <tr>
                <td  colspan="3" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:left;">DISCOUNTED AMOUNT</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:right;">{{ $totaldiscount }}</td>
            </tr>
            <tr>
                <td  colspan="3" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:left;">BRANCH FEE DISCOUNT %</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:right;">@if(@$totalamount && @$totaldiscount){{ round($totalamount / $totaldiscount, 2) }}@else 0 @endif</td>
            </tr>
            <tr>
                <td  colspan="3" style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:left;">AVERAGE FEE OF BRANCH</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:right;">@if(@$totalamount && @$total){{ round($totalamount / $total, 2) }}@else 0 @endif</td>
            </tr>
    </tbody>
</table>
<table style="margin-top:10px; width:50%">
    <thead>
        <tr>
            <td  style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; background:gray; text-align:center;">
                Fee Range
            </td>
            <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; background:gray; text-align:center;">
                Total Students
            </td>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach ($ranges as $range)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:left;">{{ $range['start'] }} - {{ $range['end'] }}</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse; text-align:right;">{{ $range['total_students'] }}</td>
            </tr>
        @endforeach
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; background:gray; border-collapse: collapse; text-align:left;">Total</td>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; background:gray; border-collapse: collapse; text-align:right;">{{ $total }}</td>
            </tr>
    </tbody>
</table>
