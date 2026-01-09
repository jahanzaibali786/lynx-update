@include('student.exports.header')
<table class="datatable">
    <thead>
        <tr>
            <th
                style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; background:gray;">
                S.R</th>
            <th
                style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 150px; text-align:center; background:gray;">
                T.FEE STRUCTURE</th>
            <th
                style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 150px; text-align:center; background:gray;">
                No of Student</th>
            <th
                style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 120px; color:rgb(0, 0, 0); background:gray; text-align:center;">
                T.FEE TOTAL</th>
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
                <td
                    style="font-size: 8rem; border: 2px solid #D3D3D3; border-collapse: collapse; text-align:center; width: 100px; font-size: 8px;">
                    {{ $loop->iteration }}</td>
                <td
                    style="font-size: 8rem; border: 2px solid #D3D3D3; border-collapse: collapse; text-align:center; width: 180px; font-size: 8px;">
                    {{ $session->final_price }}</td>
                <td
                    style="font-size: 8rem; border: 2px solid #D3D3D3; border-collapse: collapse; text-align:center; width: 180px; font-size: 8px;">
                    {{ $session->price_count }}</td>
                <td
                    style="font-size: 8rem; text-align:center; border: 2px solid #D3D3D3; border-collapse: collapse;  color:rgb(0, 0, 0); text-align:center; width: 140px; font-size: 8px;">
                    {{ $session->total_price }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2"
                style="background:gray; font-size: 10rem; font-weight: 500; border: 6px solid black; border-collapse: collapse; text-align:center; font-size: 8px; font-weight: bold;">
                Total</td>
            <td
                style="background:gray; font-size: 10rem; font-weight: 500; border: 6px solid black; border-collapse: collapse; text-align:center; font-size: 8px; font-weight: bold;">
                {{ $total }}</td>
            <td
                style="background:gray; text-align:center; border: 6px solid black; border-collapse: collapse;  color:rgb(0, 0, 0); text-align:center; font-size: 8px; font-weight: bold;">
                {{ $totalamount }}</td>
        </tr>
    </tbody>
</table>
<table class="datatable">
    <thead>
        <tr
            style="font-size: 15px; vertical-align: middle; font-weight: bold; height: 45px; border: 10px solid black; border-collapse: collapse; background:gray; text-align:center;">
            <td colspan="4"
                style="font-size: 15px; vertical-align: middle; font-weight: bold; height: 45px; border: 10px solid black; border-collapse: collapse; background:gray; text-align:center;">
                BRANCH SUMMARY
            </td>
        </tr>
    </thead>
    <tbody style="">

        <tr>
            <td colspan="3" style="font-size: 8rem;  text-align:left;">ACTUAL FEE AS PER FEE STRUCTURE (REGULAR
                CHALLAN)</td>
            <td style="font-size: 12rem; font-weight: bold; text-align:right;">{{ $totalamount + $totaldiscount }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 8rem;  text-align:left;">ACTUAL FEE RECEIVABLE (REGULAR CHALLAN)</td>
            <td style="font-size: 12rem; font-weight: bold; text-align:right;">{{ $totalamount }}</td>
        </tr>

        <tr>
            <td colspan="3" style="font-size: 8rem;  text-align:left;">DISCOUNTED AMOUNT (REGULAR CHALLAN)</td>
            <td style="font-size: 12rem; font-weight: bold;  text-align:right;">{{ $totaldiscount }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 8rem;  text-align:left;">TOTAL FEE DISCOUNT % OF BRANCH</td>
            <td style="font-size: 12rem; font-weight: bold;  text-align:right;">
                @if (@$totalamount && @$totaldiscount)
                    {{ round($totalamount / $totaldiscount, 2) }}
                @else
                    0
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 8rem;  text-align:left;">AVERAGE FEE OF BRANCH</td>
            <td style="font-size: 12rem; font-weight: bold;  text-align:right;">
                @if (@$totalamount && @$total)
                    {{ round($totalamount / $total, 2) }}
                @else
                    0
                @endif
            </td>
        </tr>
    </tbody>
</table>
<table style="margin-top:10px; width:50%">
    <thead>
        <tr>
            <td colspan="2"
                style="font-size: 10rem; font-weight: 500; background:gray; text-align:center;">
                Fee Range
            </td>
            <td
                style="font-size: 10rem; font-weight: 500; background:gray; text-align:center;">
                No of Students
            </td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        @foreach ($ranges as $range)
            <tr>
                <td colspan="2"
                    style="font-size: 10rem; font-weight: 500; border:1px solid #D3D3D3; text-align:left;">
                    {{ $range['start'] }} - {{ $range['end'] }}</td>
                <td
                    style="font-size: 10rem; font-weight: 500; border:1px solid #D3D3D3; text-align:right;">
                    {{ $range['total_students'] }}</td>
                <td></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2"
                style="font-size: 10rem; font-weight: 500; background:gray; text-align:left; border-bottom: 4px double black;">
                Total</td>
            <td
                style="font-size: 10rem; font-weight: 500; background:gray; text-align:right; border-bottom: 4px double black;">
                {{ $total }}</td>
            <td style="border-bottom: 4px double black;"></td>
        </tr>
    </tbody>
</table>
{{-- signatures --}}
<table style="padding-top:70px; width:100%">
    <tbody>
        <tr><td colspan="4" style="height:70px"></td></tr>
        <tr>
            <td colspan="3"
                style="font-size: 12rem; font-weight: text-align:left; border-top: 4px solid black;">
                Executive Director</td>
            <td
                style="font-size: 12rem; font-weight: text-align:right; border-top: 4px solid black;">
                Head of Institution</td>
        </tr>
        <tr><td colspan="4" style="height:40px"></td></tr>
    </tbody>
</table>
<table style="width:100%">
    <tbody>
        <tr>
            <td colspan="3"
                style="font-size: 12rem; font-weight: bold; text-align:center; vertical-align: middle; border-top: 4px solid black;">
                Director Finance</td>
            <td style="font-size: 12rem; font-weight:"></td>
        </tr>
        <tr><td colspan="4" style="height:40px"></td></tr>
    </tbody>
</table>
<table style="width:100%">
    <tbody style="">
        <tr>
            <td colspan="3"
                style="font-size: 12rem; font-weight: bold; text-align:center; vertical-align: middle; border-top: 4px solid black;">
                Managing Director</td>
            <td style="font-size: 12rem; font-weight:"></td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')
