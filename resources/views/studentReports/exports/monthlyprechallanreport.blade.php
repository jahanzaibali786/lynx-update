<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Fee Challan Report</title>
</head>
<body>
    @php
        $i = 1;
        $grandTotal = [
            'total' => 0,
            'net' => 0,
            'discount' => 0,
            'monthly_fee' => 0,
            'arrears' => 0
        ];
        $grandHeadTotals = [];
    @endphp

    @include('student.exports.header')
    
    <table>
        <thead>
            <tr>
                <th colspan="7">Student Information</th>
                <th>Monthly Fee</th>
                @foreach ($heads as $head)
                    <th colspan="3">{{ $head->fee_head }}</th>
                @endforeach
                <th colspan="2">Current Month Bill</th>
                <th colspan="2">Discount</th>
            </tr>
            <tr>
                <th>Sr.</th>
                <th>B Sr.</th>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>Reg type</th>
                <th>Class</th>
                <th>Billing Month</th>
                <th>Rs.</th>
                @foreach ($heads as $head)
                    <th>Rs.</th>
                    <th>Disc.</th>
                    <th>Rs.</th>
                @endforeach
                <th>Arrears</th>
                <th>Net Receivable</th>
                <th>Discount %</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
                $grandTotal = [
                    'total_amount' => 0,
                    'total_net' => 0,
                    'total_discount' => 0,
                    'arrears' => 0
                ];
                $grandHeadTotals = [];
            @endphp

            @foreach ($report as $branchId => $students)
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="{{ 7 + ($heads->count() * 3) }}" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                </td>
            </tr>
                @php
                    $branchTotal = [
                        'total_amount' => 0,
                        'total_net' => 0,
                        'total_discount' => 0,
                        'arrears' => 0
                    ];
                    $branchHeadTotals = collect($heads)
                        ->mapWithKeys(fn($h) => [$h->id => [
                            'amount' => 0, 
                            'discount_amount' => 0, 
                            'net_amount' => 0
                        ]])->toArray();
                @endphp

                @foreach ($students as $index => $stu)
                    @php
                        $stu = array_merge([
                            'total_amount' => 0,
                            'total_net' => 0,
                            'total_discount' => 0,
                            'arrears' => 0,
                            'head_details' => []
                        ], $stu);
                    @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $stu['roll_no'] }}</td>
                        <td>{{ $stu['student_name'] }}</td>
                        <td>{{ $stu['registration_type'] }}</td>
                        <td>{{ $stu['class_name'] }}</td>
                        <td>{{ $dateInput }}</td>
                        <td>{{ $stu['total_amount'] }}</td>

                        @foreach ($heads as $head)
                            @php
                                $h = array_merge([
                                    'amount' => 0,
                                    'discount_amount' => 0,
                                    'net_amount' => 0
                                ], $stu['head_details'][$head->id] ?? []);
                                
                                $branchHeadTotals[$head->id]['amount'] += $h['amount'];
                                $branchHeadTotals[$head->id]['discount_amount'] += $h['discount_amount'];
                                $branchHeadTotals[$head->id]['net_amount'] += $h['net_amount'];
                                
                                if (!isset($grandHeadTotals[$head->id])) {
                                    $grandHeadTotals[$head->id] = [
                                        'amount' => 0,
                                        'discount_amount' => 0,
                                        'net_amount' => 0
                                    ];
                                }
                                $grandHeadTotals[$head->id]['amount'] += $h['amount'];
                                $grandHeadTotals[$head->id]['discount_amount'] += $h['discount_amount'];
                                $grandHeadTotals[$head->id]['net_amount'] += $h['net_amount'];
                            @endphp
                            <td>{{ $h['amount'] }}</td>
                            <td>{{ $h['discount_amount'] }}</td>
                            <td>{{ $h['net_amount'] }}</td>
                        @endforeach
                        
                        <td>{{ $stu['arrears'] }}</td>
                        <td>{{ $stu['total_net'] }}</td>
                        <td>{{ $stu['total_discount'] }}</td>
                        <td>{{ $stu['concession_category'] ?? '' }}</td>
                        
                        @php
                            $branchTotal['total_amount'] += $stu['total_amount'];
                            $branchTotal['total_discount'] += $stu['total_discount'];
                            $branchTotal['total_net'] += $stu['total_net'];
                            $branchTotal['arrears'] += $stu['arrears'];

                            $grandTotal['total_amount'] += $stu['total_amount'];
                            $grandTotal['total_discount'] += $stu['total_discount'];
                            $grandTotal['total_net'] += $stu['total_net'];
                            $grandTotal['arrears'] += $stu['arrears'];
                        @endphp
                    </tr>
                @endforeach

                <!-- Branch Total Row -->
                <tr>
                    <td colspan="6" style="background:gray; font-size: 8px; text-align: center; border: 1px solid black;">Branch Total</td>
                    <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchTotal['total_amount'] }}</td>
                    @foreach ($heads as $head)
                        <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchHeadTotals[$head->id]['amount'] ?? 0 }}</td>
                        <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchHeadTotals[$head->id]['discount_amount'] ?? 0 }}</td>
                        <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchHeadTotals[$head->id]['net_amount'] ?? 0 }}</td>
                    @endforeach
                    <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchTotal['arrears'] }}</td>
                    <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchTotal['total_net'] }}</td>
                    <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;">{{ $branchTotal['total_discount'] }}</td>
                    <td style="background:gray; font-size: 8px; text-align: right; border: 1px solid black;"></td>
                </tr>
                
                <tr>
                    <td colspan="{{ ($heads->count() * 3) + 11 }}"></td>
                </tr>
                <tr>
                    <td colspan="{{ ($heads->count() * 3) + 11 }}"></td>
                </tr>
            @endforeach

            <!-- Grand Total Row -->
            <tr>
                <td colspan="6" style="background:gray; font-size: 8px; border: 1px solid black; text-align: center; border-top: 1px double black; border-bottom: 1px double black;">Grand Total</td>
                <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandTotal['total_amount'] }}</td>
                @foreach ($heads as $head)
                    <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandHeadTotals[$head->id]['amount'] ?? 0 }}</td>
                    <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandHeadTotals[$head->id]['discount_amount'] ?? 0 }}</td>
                    <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandHeadTotals[$head->id]['net_amount'] ?? 0 }}</td>
                @endforeach
                <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandTotal['arrears'] }}</td>
                <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandTotal['total_net'] }}</td>
                <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;">{{ $grandTotal['total_discount'] }}</td>
                <td style="background:gray; font-size: 8px; border: 1px solid black; text-align: right; border-top: 1px double black; border-bottom: 1px double black;"></td>
            </tr>
        </tbody>
    </table>
    
    @include('student.exports.footer')
</body>
</html>