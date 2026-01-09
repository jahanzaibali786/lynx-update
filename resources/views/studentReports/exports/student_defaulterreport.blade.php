@include('student.exports.header')
<table>
    <thead>
        @php
            $i = 1;
            $yearMonthCounts = [];
            foreach ($monthsArray as $monthYear) {
                [$month, $year] = explode('-', $monthYear);
                if (!isset($yearMonthCounts[$year])) {
                    $yearMonthCounts[$year] = 0;
                }
                $yearMonthCounts[$year]++;
                $yearMonths[] = $month;
            }
            $grandMonthlyTotals = array_fill(0, count($monthsArray), 0);
            $grandTotal = 0;
            $grandMonthlyFeeTotal = 0;
            $grandArrearsTotal = 0;
        @endphp

        <tr>
            <th rowspan="2">Sr.</th>
            <th rowspan="2">B Sr No.</th>
            <th rowspan="2">Roll No</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2">Reg type</th>
            <th rowspan="2">Class</th>
            <th rowspan="2">Phone No</th>
            <th rowspan="2">Monthly Fee</th>
            <th rowspan="2">Arrears</th>
            @foreach($yearMonthCounts as $ak => $year)
            <th colspan="{{ $year }}">{{ $ak }}</th>
            @endforeach
            <th rowspan="2">Total</th>
        </tr>
        <tr>
            @foreach($yearMonths as $month)
            <th>{{ date('M', mktime(0, 0, 0, (int)$month, 1)) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $data)
            @if($data['challans']->count() < 1)
                @continue
            @endif
            <tr style="background: gray">
                <td colspan="4" style="font-size: 8px; text-align: left; font-weight: 700; border: 2px solid black; border-right:none; border-collapse: collapse; background: gray;">{{ $data['branch'] }}</td>
                <td colspan="{{ count($monthsArray) + 6 }}" style="font-size: 8px; font-weight: 700; border: 2px solid black; border-left:none; border-collapse: collapse; background: gray;"></td>
            </tr>
            @php
                $branchMonthlyTotals = array_fill(0, count($monthsArray), 0);
                $branchTotal = 0;
                $branchMonthlyFeeTotal = 0;
                $branchArrearsTotal = 0;
                $brsr = 1;
            @endphp
            @foreach($data['challans'] as $index => $challan)
                @foreach($challan as $chall)
                    @php
                        $studentTotal = 0;
                        $monthlyFee = @$chall->monthly_fee ?? 0;
                        $arrears = 0; // You can calculate actual arrears if needed
                    @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $brsr++ }}</td>
                        <td>{{ @$chall->student->roll_no }}</td>
                        <td>{{ @$chall->student->stdname }}</td>
                        <td>{{ @$chall->student->registeroption->name }}</td>
                        <td>{{ @$chall->class->name }}</td>
                        <td>{!! str_replace(',', '<br>', @$chall->student->fatherphone) !!}</td>
                        <td>{{ $monthlyFee }}</td>
                        <td>{{ $arrears }}</td>
                        @foreach ($monthsArray as $l => $monthYear)
                            @php
                                [$month, $year] = explode('-', $monthYear);
                                $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                                $specificdata = collect($challan)->firstWhere('fee_month', $formattedDate);
                                $price = $specificdata ? $specificdata->total_amount - ($specificdata->paid_amount + $specificdata->concession_amount) : 0;
                                $studentTotal += $price;
                                $branchMonthlyTotals[$l] += $price;
                                $grandMonthlyTotals[$l] += $price;
                            @endphp
                            <td>{{ $price }}</td>
                        @endforeach
                        <td>{{ $studentTotal }}</td>
                    </tr>
                    @php
                        $branchTotal += $studentTotal;
                        $branchMonthlyFeeTotal += $monthlyFee;
                        $branchArrearsTotal += $arrears;
                        $grandMonthlyFeeTotal += $monthlyFee;
                        $grandArrearsTotal += $arrears;
                    @endphp
                    @break
                @endforeach
            @endforeach
            <tr>
                <td colspan="7" style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; font-weight: bold; text-align:center;">Branch Total</td>
                <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; text-align:center; font-weight: bold;">{{ $branchMonthlyFeeTotal }}</td>
                <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; text-align:center; font-weight: bold;">{{ $branchArrearsTotal }}</td>
                @foreach ($branchMonthlyTotals as $monthlyTotal)
                    <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; text-align:right; font-weight: bold;">{{ $monthlyTotal }}</td>
                @endforeach
                <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; text-align:right; font-weight: bold;">{{ $branchTotal }}</td>
            </tr>
            @php
                $grandTotal += $branchTotal;
            @endphp
        @endforeach
        <tr>
            <td colspan="{{ count($monthsArray) + 10 }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ count($monthsArray) + 10 }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ count($monthsArray) + 10 }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="7" style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; font-weight: bold; text-align:center;">Grand Total</td>
            <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; text-align:center; font-weight: bold;">{{ $grandMonthlyFeeTotal }}</td>
            <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; text-align:center; font-weight: bold;">{{ $grandArrearsTotal }}</td>
            @foreach ($grandMonthlyTotals as $grandMonthlyTotal)
                <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; text-align:right; font-weight: bold;">{{ $grandMonthlyTotal }}</td>
            @endforeach
            <td style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; text-align:right; font-weight: bold;">{{ $grandTotal }}</td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')
