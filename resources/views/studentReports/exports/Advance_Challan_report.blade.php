@include('student.exports.header')
<table>
    <thead>
        <tr>
        </tr>
        @php
            $i=1;
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
            <th rowspan="2">Monthly Fee</th>
            @foreach ((@$heads ?? []) as $head)
                <th colspan="3">{{ @$head->fee_head }}</th>
            @endforeach
            <th colspan="2">Current Month Bill</th>
            <th rowspan="2">Discount</th>
        </tr>
        <tr>
            <th>Sr.</th>
            <th>B Sr.</th>
            <th>Bill No</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Reg type</th>
            <th>Class</th>
            <th>Billing Month</th>
            <th>Billing Period</th>
            {{-- <th>Rs.</th> --}}
            @foreach ((@$heads ?? []) as $head)
                <th>Rs.</th>
                <th>Disc</th>
                <th>Rs.</th>
            @endforeach
            <th>Arrears</th>
            <th>Net Receivable</th>
            {{-- <th>Discount</th> --}}
            {{-- <th>Category</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ((@$report ?? []) as $a => $row)
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                    {{ $branches[$a] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="{{ 7 + ($heads->count() * 3) }}" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
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
            @foreach (($row ?? []) as $index => $data)
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $index + 1 }}</td>
                <td>{{ $data->challanNo }}</td>
                <td>{{ $data->rollno }}</td>
                <td style="font-size: 8px; text-align:left;">
                    {!! nl2br(wordwrap(e(@$data->student->stdname), 15, "\n")) !!}
                </td>                
                <td style="font-size: 8px; text-align:left;">{{ @$data->student->registeroption->name }}</td>
                <td style="font-size: 8px; text-align:left;">{{ @$data->class->name }}</td>
                <td>{{  date('M-Y', strtotime($data->fee_month))  }}</td>
                <td>
                    @php
                        $startDate = \Carbon\Carbon::parse($data->fee_month)->startOfMonth();
                        $endDate = \Carbon\Carbon::parse($data->fee_month)->endOfMonth();
                        $billingPeriod = '(' . $startDate->format('M-y') . ')';
                    @endphp
                    {{ $billingPeriod }}
                </td>
                <td style="font-size: 8px; font-weight: bold; text-align:center;">{{ $data->monthly_fee ?? '' }}</td>
                @php
                    $price = $data->heads->firstWhere('head_id', $head->id)->price ?? 0;
                    $concession = $data->heads->firstWhere('head_id', $head->id)->concession ?? 0;
                    $netAmount = $price - $concession;

                    // Update branch totals for each head
                    if (!isset($branchHeadTotals[$head->id])) {
                        $branchHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                    }
                    $branchHeadTotals[$head->id]['price'] += $price;
                    $branchHeadTotals[$head->id]['concession'] += $concession;
                    $branchHeadTotals[$head->id]['netAmount'] += $netAmount;

                    // Update grand totals for each head
                    if (!isset($grandHeadTotals[$head->id])) {
                        $grandHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                    }
                    $grandHeadTotals[$head->id]['price'] += $price;
                    $grandHeadTotals[$head->id]['concession'] += $concession;
                    $grandHeadTotals[$head->id]['netAmount'] += $netAmount;

                    // Update branch totals
                    $branchTotal['previousUnpaid'] += $data->total_amount - $data->concession_amount;
                    $branchTotal['totalAmount'] += $data->total_amount - $data->concession_amount;
                    $branchTotal['concessionAmount'] += $data->concession_amount;
                    $branchTotal['monthly_fee'] += $data->monthly_fee ?? 0;

                    // Update grand totals
                    $grandTotal['previousUnpaid'] += $data->total_amount - $data->concession_amount;
                    $grandTotal['totalAmount'] += $data->total_amount - $data->concession_amount;
                    $grandTotal['concessionAmount'] += $data->concession_amount;
                    $grandTotal['monthly_fee'] += $data->monthly_fee ?? 0;

                    $i++;
                @endphp
                @foreach ((@$heads ?? []) as $head)
                    <td>{{ $price }}</td>
                    <td>{{ $concession }}</td>
                    <td>{{ $netAmount}}</td>
                @endforeach
                <td>{{$previousUnpaidChallans ?? 0}}</td>
                <td>{{ $data->total_amount - $data->concession_amount}}</td>
                <td>{{ $data->concession_amount}}</td>
                {{-- <td>{{ @$data->concession->name ?? ''}}</td> --}}
            </tr>
            @endforeach
            <tr class="branch-total-row" style="border-top: 3px solid #000;">
                <td colspan="9" style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000;">Branch Total</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchTotal['monthly_fee'] ?? '' }}</td>
                @foreach ((@$heads ?? []) as $head)
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchHeadTotals[$head->id]['price'] ?? 0 }}</td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchHeadTotals[$head->id]['concession'] ?? 0 }}</td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchHeadTotals[$head->id]['netAmount'] ?? 0 }}</td>
                @endforeach
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchTotal['previousUnpaid'] }}</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchTotal['totalAmount'] }}</td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">{{ $branchTotal['concessionAmount'] }}</td>
                {{-- <td colspan="1" style="font-size: 8px; border: 2px solid #000; background-color: #B8B8B8;"></td> --}}
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 14 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 14 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="{{ ($heads->count() * 3) + 14 }}" style="background: #fff; height: 10px; border: none;"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="9" style="font-size: 8px; font-weight: bold; background-color: #8B8B8B; text-align: center; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;">Grand Total</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandTotal['monthly_fee'] ?? '' }}</td>
            @foreach ((@$heads ?? []) as $head)
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandHeadTotals[$head->id]['price'] ?? 0 }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandHeadTotals[$head->id]['concession'] ?? 0 }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandHeadTotals[$head->id]['netAmount'] ?? 0 }}</td>
            @endforeach
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandTotal['previousUnpaid'] }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandTotal['totalAmount'] }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">{{ $grandTotal['concessionAmount'] }}</td>
            {{-- <td colspan="1" style="font-size: 8px; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;"></td> --}}
        </tr>
    </tbody>
</table>

@include('student.exports.footer')
