@include('student.exports.header')
<table class="datatable">
    <thead>
        @php
            $i = 1;
            $grandTotal = [
                'previousUnpaid' => 0,
                'totalAmount' => 0,
                'concessionAmount' => 0,
                'monthly_fee' => 0,
            ];
            $grandHeadTotals = [];
        @endphp
        <tr>
            <th colspan="9">Student Information</th>
            <th>Monthly Fee</th>
            @foreach (@$heads as $head)
                <th colspan="3">{{ @$head->fee_head }}</th>
            @endforeach
            <th colspan="2">Current Month Bill</th>
            <th colspan="2">Discount</th>
        </tr>
        <tr>
            <th>Sr.</th>
            <th>B Sr.</th>
            <th>Bill No</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Reg type</th>
            <th>Class</th>
            <th>D / Adm</th>
            <th>Billing Month</th>
            <th>Rs.</th>
            @foreach (@$heads as $head)
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
        @foreach ($report as $a => $row)
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                    {{ $branches[$a] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="{{ 10 + ($heads->count() * 3) }}" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                </td>
            </tr>
            @php
                $branchTotal = [
                    'previousUnpaid' => 0,
                    'totalAmount' => 0,
                    'concessionAmount' => 0,
                    'monthly_fee' => 0,
                ];
                $branchHeadTotals = [];
            @endphp

            @foreach ($row as $index => $data)
                @php
                    // Convert all numeric values to float to prevent string operations
                    $monthly_fee = floatval(@$data->monthly_fee ?? 0);
                    $total_amount = floatval($data->total_amount ?? 0);
                    $concession_amount = floatval($data->concession_amount ?? 0);
                    $net_receivable = $total_amount - $concession_amount;
                    $previousUnpaidChallans = floatval($previousUnpaidChallans ?? 0);
                @endphp
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ @$data->challanNo }}</td>
                    <td>{{ @$data->student->roll_no }}</td>
                    <td>
                        {{ @$data->student->stdname }}
                    </td>
                    <td>{{ @$data->student->registeroption->name }}</td>
                    <td>{{ @$data->class->name }}</td>
                    <td>{{ date('d-m-Y', strtotime(@$data->student->admission_date)) }}</td>
                    <td>{{ date('M-Y', strtotime(@$data->fee_month)) }}</td>
                    <td>{{($monthly_fee) }}</td>
                    
                    @foreach ($heads as $head)
                        @php
                            $headData = $data->heads->firstWhere('head_id', $head->id);
                            $price = floatval($headData->price ?? 0);
                            $concession = floatval($headData->concession ?? 0);
                            $netAmount = $price - $concession;

                            // Initialize branch totals for this head if not set
                            if (!isset($branchHeadTotals[$head->id])) {
                                $branchHeadTotals[$head->id] = [
                                    'price' => 0,
                                    'concession' => 0,
                                    'netAmount' => 0
                                ];
                            }
                            $branchHeadTotals[$head->id]['price'] += $price;
                            $branchHeadTotals[$head->id]['concession'] += $concession;
                            $branchHeadTotals[$head->id]['netAmount'] += $netAmount;

                            // Initialize grand totals for this head if not set
                            if (!isset($grandHeadTotals[$head->id])) {
                                $grandHeadTotals[$head->id] = [
                                    'price' => 0,
                                    'concession' => 0,
                                    'netAmount' => 0
                                ];
                            }
                            $grandHeadTotals[$head->id]['price'] += $price;
                            $grandHeadTotals[$head->id]['concession'] += $concession;
                            $grandHeadTotals[$head->id]['netAmount'] += $netAmount;
                        @endphp
                        <td>{{($price) }}</td>
                        <td>{{($concession) }}</td>
                        <td>{{($netAmount) }}</td>
                    @endforeach
                    
                    <td>{{($previousUnpaidChallans) }}</td>
                    <td>{{($net_receivable) }}</td>
                    <td>{{($concession_amount) }}</td>
                    <td>{{ @$data->concession->name ?? '' }}</td>
                    
                    @php
                        // Update branch totals
                        $branchTotal['previousUnpaid'] += $previousUnpaidChallans;
                        $branchTotal['totalAmount'] += $net_receivable;
                        $branchTotal['concessionAmount'] += $concession_amount;
                        $branchTotal['monthly_fee'] += $monthly_fee;

                        // Update grand totals
                        $grandTotal['previousUnpaid'] += $previousUnpaidChallans;
                        $grandTotal['totalAmount'] += $net_receivable;
                        $grandTotal['concessionAmount'] += $concession_amount;
                        $grandTotal['monthly_fee'] += $monthly_fee;

                        $i++;
                    @endphp
                </tr>
            @endforeach
            <tr>
                <td colspan="9" style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000;">Branch Total</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchTotal['monthly_fee']) }}</td>
                @foreach ($heads as $head)
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchHeadTotals[$head->id]['price'] ?? 0) }}</td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchHeadTotals[$head->id]['concession'] ?? 0) }}</td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchHeadTotals[$head->id]['netAmount'] ?? 0) }}</td>
                @endforeach
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchTotal['previousUnpaid']) }}</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchTotal['totalAmount']) }}</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{($branchTotal['concessionAmount']) }}</td>
                <td colspan="1" style="font-size: 8px; border: 2px solid #000; background-color: #B8B8B8;"></td>
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 13 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 13 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 13 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="9" style="font-size: 8px; font-weight: bold; background-color: #8B8B8B; text-align: center; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;">Grand Total</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandTotal['monthly_fee']) }}</td>
            @foreach ($heads as $head)
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandHeadTotals[$head->id]['price'] ?? 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandHeadTotals[$head->id]['concession'] ?? 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandHeadTotals[$head->id]['netAmount'] ?? 0) }}</td>
            @endforeach
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandTotal['previousUnpaid']) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandTotal['totalAmount']) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{($grandTotal['concessionAmount']) }}</td>
            <td colspan="1" style="font-size: 8px; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;"></td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')