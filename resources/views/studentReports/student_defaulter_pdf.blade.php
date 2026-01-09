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
    <table class="table" style="margin-top: -50px;">
        <thead>
            <tr class="table_heads">
                <th rowspan="2">{{ __('Sr No.') }}</th>
                <th rowspan="2">{{ __('B Sr No.') }}</th>
                <th rowspan="2">{{ __('Roll No') }}</th>
                <th rowspan="2">{{ __('Student Name') }}</th>
                <th rowspan="2">{{ __('Reg Type') }}</th>
                <th rowspan="2">{{ __('Class') }}</th>
                <th rowspan="2">{{ __('Phone No') }}</th>
                <th rowspan="2">{{ __('Arrears') }}</th>
                @php
                    // Step 1: Group months by year and count the months for each year
                    $yearMonthCounts = [];
                    foreach ($monthsArray as $monthYear) {
                        [$month, $year] = explode('-', $monthYear);
                        if (!isset($yearMonthCounts[$year])) {
                            $yearMonthCounts[$year] = 0;
                        }
                        $yearMonthCounts[$year]++;
                        $yearMonths[] = $month;
                    }
                    $i = 1;
                    $grandMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize grand total for each month
                    $grandTotal = 0; // Initialize overall grand total
                @endphp
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
                    <td colspan="{{ count($monthsArray) + 9 }}">{{ $data['branch'] }}</td>
                </tr>
                @php
                    $branchMonthlyTotals = array_fill(0, count($monthsArray), 0); // Initialize branch total for each month
                    $branchTotal = 0; // Initialize branch overall total
                    $brsr = 1;
                @endphp
                @foreach (@$data['challans'] as $index => $challan)
                    @foreach (@$challan as $chall)
                        @php
                            $studentTotal = 0; // Initialize student total
                        @endphp
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $brsr++ }}</td>
                            <td>{{ @$chall->student->roll_no }}</td>
                            <td>{{ @$chall->student->stdname }}</td>
                            <td>{{ @$chall->student->registeroption->name }}</td>
                            <td>{{ @$chall->class->name }}</td>
                            <td>{{ @$chall->student->fatherphone }}</td>
                            <td>0</td>
                            @foreach ($monthsArray as $l => $monthYear)
                                @php
                                    [$month, $year] = explode('-', $monthYear);
                                    $formattedDate = date('Y-m-01', strtotime("$year-$month-01"));
                                    $specificdata = collect($challan)->firstWhere('fee_month', $formattedDate);
                                    $price = $specificdata
                                        ? $specificdata->total_amount -
                                            ($specificdata->paid_amount + $specificdata->concession_amount)
                                        : 0;
                                    $studentTotal += $price;
                                    // dd($branchMonthlyTotals[$loop->parent->iteration]);
                                    $branchMonthlyTotals[$l] += $price;
                                    $grandMonthlyTotals[$l] += $price;
                                @endphp
                                <td>{{ $price }}</td>
                            @endforeach
                            <td>{{ $studentTotal }}</td>
                        </tr>
                        @php
                            $branchTotal += $studentTotal; // Add student total to branch total
                        @endphp
                        {{-- @dd($branchMonthlyTotals,$grandMonthlyTotals) --}}
                    @break
                @endforeach
                <!-- Branch Total Row -->
            @endforeach
            <tr style="background: #dcdcdc; font-weight: bold;">
                <td colspan="8">Branch Total</td>
                @foreach ($branchMonthlyTotals as $monthlyTotal)
                    <td>{{ $monthlyTotal }}</td>
                @endforeach
                <td>{{ $branchTotal }}</td>
            </tr>

            @php
                $grandTotal += $branchTotal; // Add branch total to grand total
            @endphp
        @endforeach
        <tr style="background: #cccccc; font-weight: bold;">
            <td colspan="8">Grand Total</td>
            @foreach ($grandMonthlyTotals as $grandMonthlyTotal)
                <td>{{ $grandMonthlyTotal }}</td>
            @endforeach
            <td>{{ $grandTotal }}</td>
        </tr>
    </tbody>
</table>
</body>

</html>
