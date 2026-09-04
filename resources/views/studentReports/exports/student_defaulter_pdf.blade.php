<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student Defaulter Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .report-info {
            margin-bottom: 15px;
        }

        .report-info div {
            display: inline-block;
            margin-right: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .branch-header {
            background-color: #a9a9a9;
            font-weight: bold;
        }

        .branch-total {
            background-color: #dcdcdc;
            font-weight: bold;
        }

        .grand-total {
            background-color: #cccccc;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    @php
        // First pass: compute grand totals per month to skip zero-month columns
        $computedGrandMonthlyTotals = array_fill(0, count($monthsArray), 0);
        foreach ($reportData as $data) {
            foreach ($data['challans'] as $challanGroup) {
                foreach ($challanGroup as $chall) {
                    // Match by fee_month's YEAR-MONTH (Admission challans store
                    // fee_month on the admission day, not the 1st of the month).
                    $feeTs = @$chall->fee_month ? strtotime($chall->fee_month) : false;
                    $chFeeYm = $feeTs ? date('Y-m', $feeTs) : null;
                    $price = $chall->total_amount - ($chall->paid_amount + $chall->concession_amount);
                    foreach ($monthsArray as $l => $monthYear) {
                        [$month, $year] = explode('-', $monthYear);
                        if ($chFeeYm !== null && $chFeeYm === "$year-$month") {
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
        $i = 1;
        $grandMonthlyTotals = array_fill(0, count($monthsArray), 0);
        $grandTotal = 0;
    @endphp
    <table class="table" style="margin-top: -50px;">
        <thead>
            <tr class="table_heads">
                <th rowspan="2">{{ __('Sr No.') }}</th>
                <th rowspan="2">{{ __('B Sr No.') }}</th>
                <th rowspan="2">{{ __('Roll No') }}</th>
                <th rowspan="2">{{ __('Student Name') }}</th>
                <th rowspan="2">{{ __('Admission Date') }}</th>
                <th rowspan="2">{{ __('Reg Type') }}</th>
                <th rowspan="2">{{ __('Class') }}</th>
                <th rowspan="2">{{ __('Phone No') }}</th>
                <th rowspan="2">{{ __('Arrears') }}</th>
                @foreach (@$yearMonthCounts as $ak => $year)
                    <th colspan="{{ $year }}" style="text-align: center;">{{ $ak }}</th>
                @endforeach
                <th rowspan="2">{{ __('Total Amount') }}</th>
            </tr>
            <tr style="">
                @foreach (@$yearMonths as $month)
                    <th colspan=""
                        style=" border-radius:0px !important;">
                        {{ date('M', mktime(0, 0, 0, (int) $month, 1)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach (@$reportData as $data)
                @if ($data['challans']->count() < 1)
                    @continue
                @endif
                <tr style="background:  #a9a9a9;">
                    <td colspan="{{ count($monthsArray) + 10 }}">{{ $data['branch'] }}</td>
                </tr>
                @php
                    $branchMonthlyTotals = array_fill(0, count($monthsArray), 0);
                    $branchTotal = 0;
                    $brsr = 1;
                @endphp
                @foreach ($data['challans'] as $index => $challanGroup)
                    @php
                        $studentMonthlyTotals = array_fill(0, count($monthsArray), 0);
                        $studentTotal = 0;
                        $firstChall = $challanGroup->first();
                    @endphp
                    @foreach ($challanGroup as $chall)
                        @php
                            $feeTs = @$chall->fee_month ? strtotime($chall->fee_month) : false;
                            $chFeeYm = $feeTs ? date('Y-m', $feeTs) : null;
                            $price = $chall->total_amount - ($chall->paid_amount + $chall->concession_amount);
                        @endphp
                        @foreach ($monthsArray as $l => $monthYear)
                            @php
                                [$month, $year] = explode('-', $monthYear);
                                if ($chFeeYm !== null && $chFeeYm === "$year-$month") {
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
                        <td>0</td>
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
                        $branchTotal += $studentTotal;
                    @endphp
                @endforeach
                <tr style="background: #dcdcdc; font-weight: bold;">
                    <td colspan="9">Branch Total</td>
                    @foreach ($branchMonthlyTotals as $monthlyTotal)
                        <td>{{ $monthlyTotal }}</td>
                    @endforeach
                    <td>{{ $branchTotal }}</td>
                </tr>
                @php
                    $grandTotal += $branchTotal;
                @endphp
            @endforeach
            <tr style="background: #cccccc; font-weight: bold;">
                <td colspan="9">Grand Total</td>
                @foreach ($grandMonthlyTotals as $grandMonthlyTotal)
                    <td>{{ $grandMonthlyTotal }}</td>
                @endforeach
                <td>{{ $grandTotal }}</td>
            </tr>
        </tbody>
    </table>
    <p style="text-align: right; font-size: 9px; font-weight: bold;">
        Generated on: {{ date('d-M-Y h:i A') }}
    </p>
</body>

</html>
