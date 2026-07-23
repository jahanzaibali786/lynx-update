@php
    // First pass: compute grand totals per month to skip zero-month columns
    $computedGrandMonthlyTotals = array_fill(0, count($monthsArray), 0);
    foreach ($reportData as $data) {
        foreach ($data['challans'] as $challanGroup) {
            foreach ($challanGroup as $chall) {
                foreach ($monthsArray as $l => $monthYear) {
                    [$month, $year] = explode('-', $monthYear);
                    $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                    if (@$chall->fee_month == $formattedDate) {
                        $price = $chall->total_amount - ($chall->paid_amount + $chall->concession_amount);
                        $computedGrandMonthlyTotals[$l] += $price;
                    }
                }
            }
        }
    }
    $filteredMonthsArray = [];
    foreach ($monthsArray as $l => $monthYear) {
        if ($computedGrandMonthlyTotals[$l] != 0) {
            $filteredMonthsArray[] = $monthYear;
        }
    }
    $monthsArray = $filteredMonthsArray;
    $yearMonthCounts = [];
    $yearMonths = [];
    foreach ($monthsArray as $monthYear) {
        [$month, $year] = explode('-', $monthYear);
        if (!isset($yearMonthCounts[$year])) { $yearMonthCounts[$year] = 0; }
        $yearMonthCounts[$year]++;
        $yearMonths[] = $month;
    }
    $totalCols = count($monthsArray) + 11; // Sr, B Sr, Roll, Student, Adm Date, Reg, Class, Phone, Monthly Fee, Arrears, months, Total
    $colspan = $totalCols;
    $i = 1;
    $grandMonthlyTotals = array_fill(0, count($monthsArray), 0);
    $grandTotal = 0;
    $grandMonthlyFeeTotal = 0;
    $grandArrearsTotal = 0;
@endphp
@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2">Sr.</th>
            <th rowspan="2">B Sr No.</th>
            <th rowspan="2">Roll No</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2">Admission Date</th>
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
                <td colspan="{{ count($monthsArray) + 7 }}" style="font-size: 8px; font-weight: 700; border: 2px solid black; border-left:none; border-collapse: collapse; background: gray;"></td>
            </tr>
            @php
                $branchMonthlyTotals = array_fill(0, count($monthsArray), 0);
                $branchTotal = 0;
                $branchMonthlyFeeTotal = 0;
                $branchArrearsTotal = 0;
                $brsr = 1;
            @endphp
            @foreach($data['challans'] as $index => $challanGroup)
                @php
                    $studentMonthlyTotals = array_fill(0, count($monthsArray), 0);
                    $studentTotal = 0;
                    $firstChall = $challanGroup->first();
                    $studentMonthlyFee = 0;
                    $studentArrears = 0;
                @endphp
                @foreach($challanGroup as $chall)
                    @php
                        $studentMonthlyFee += $chall->monthly_fee ?? 0;
                        $studentArrears += $chall->arrears ?? 0;
                    @endphp
                    @foreach ($monthsArray as $l => $monthYear)
                        @php
                            [$month, $year] = explode('-', $monthYear);
                            $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                            if (@$chall->fee_month == $formattedDate) {
                                $price = $chall->total_amount - ($chall->paid_amount + $chall->concession_amount);
                                $studentMonthlyTotals[$l] += $price;
                            }
                        @endphp
                    @endforeach
                @endforeach
                @php
                    $studentTotal = array_sum($studentMonthlyTotals);
                @endphp
                @if ($studentTotal == 0)
                    @continue
                @endif
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $brsr++ }}</td>
                    <td>{{ @$firstChall->student->roll_no }}</td>
                    <td>{{ @$firstChall->student->stdname }}</td>
                    <td>{{ @$firstChall->enrollstudent->adm_date ? \Carbon\Carbon::parse($firstChall->enrollstudent->adm_date)->format('d-M-Y') : '-' }}</td>
                    <td>{{ @$firstChall->student->registeroption->name }}</td>
                    <td>{{ @$firstChall->class->name }}</td>
                    <td>{!! str_replace(',', '<br>', @$firstChall->student->fatherphone) !!}</td>
                    <td>{{ $studentMonthlyFee }}</td>
                    <td>{{ $studentArrears }}</td>
                    @foreach ($studentMonthlyTotals as $price)
                        <td>{{ $price }}</td>
                    @endforeach
                    <td>{{ $studentTotal }}</td>
                </tr>
                @php
                    foreach ($studentMonthlyTotals as $l => $price) {
                        $branchMonthlyTotals[$l] += $price;
                        $grandMonthlyTotals[$l] += $price;
                    }
                    $branchMonthlyFeeTotal += $studentMonthlyFee;
                    $branchArrearsTotal += $studentArrears;
                    $branchTotal += $studentTotal;
                    $grandMonthlyFeeTotal += $studentMonthlyFee;
                    $grandArrearsTotal += $studentArrears;
                @endphp
            @endforeach
            <tr>
                <td colspan="8" style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-collapse: collapse; font-weight: bold; text-align:center;">Branch Total</td>
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
            <td colspan="{{ $totalCols }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ $totalCols }}" style="background: #fff; height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 8px; background: #dcdcdc; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; border-collapse: collapse; font-weight: bold; text-align:center;">Grand Total</td>
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
<tr>
    <td colspan="{{ $totalCols }}" style="text-align: right; font-size: 8px; font-weight: bold; border: none;">
        Generated on: {{ date('d-M-Y h:i A') }}
    </td>
</tr>
