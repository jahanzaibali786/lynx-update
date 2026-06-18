@include('student.exports.header')
<table>
    <thead>
        <tr></tr>
        @php
            $i = 1;
            $grandTotal = [
                'previousUnpaid'    => 0,
                'totalAmount'       => 0,
                'concessionAmount'  => 0,
                'monthly_fee'       => 0,
            ];
            $grandHeadTotals = [];
        @endphp
        <tr>
            <th colspan="7">Student Information</th>
            <th colspan="3">Monthly Fee</th>
            @foreach (@$heads ?? [] as $head)
                <th colspan="3">{{ $head->fee_head }}</th>
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
            <th>Billing Month</th>
            <th>Billing Period</th>
            <th>Rs.</th>
            @foreach (@$heads ?? [] as $head)
                <th>Rs.</th>
                <th>Disc</th>
                <th>Rs.</th>
            @endforeach
            <th>Arrears</th>
            <th>Net Receivable</th>
            <th>Discount</th>
            <th>Category</th>
        </tr>
    </thead>
    <tbody>
        @foreach (@$report ?? [] as $a => $row)
            <tr>
                <td colspan="4"
                    style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                    {{ $branches[$a] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="{{ 10 + $heads->count() * 3 }}"
                    style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                </td>
            </tr>

            @php
                $branchTotal = [
                    'previousUnpaid'   => 0,
                    'totalAmount'      => 0,
                    'concessionAmount' => 0,
                    'monthly_fee'      => 0,
                ];
                $branchHeadTotals = [];
            @endphp

            @foreach ($row ?? [] as $index => $data)
                @php
                    // ✅ Calculate per-head amounts FIRST, store in array keyed by head id
                    $rowHeadData  = [];
                    $monthly_fee  = 0;

                    foreach ($heads as $head) {
                        $specificHead = collect($data->heads)->firstWhere('head_id', $head->id);

                        $price      = $specificHead && isset($specificHead->price)      ? floatval($specificHead->price)      : 0;
                        $concession = $specificHead && isset($specificHead->concession) ? floatval($specificHead->concession) : 0;
                        $netAmount  = $price - $concession;

                        $rowHeadData[$head->id] = compact('price', 'concession', 'netAmount');

                        $monthly_fee += $price;

                        // Branch head totals
                        if (!isset($branchHeadTotals[$head->id])) {
                            $branchHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                        }
                        $branchHeadTotals[$head->id]['price']     += $price;
                        $branchHeadTotals[$head->id]['concession'] += $concession;
                        $branchHeadTotals[$head->id]['netAmount']  += $netAmount;

                        // Grand head totals
                        if (!isset($grandHeadTotals[$head->id])) {
                            $grandHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                        }
                        $grandHeadTotals[$head->id]['price']     += $price;
                        $grandHeadTotals[$head->id]['concession'] += $concession;
                        $grandHeadTotals[$head->id]['netAmount']  += $netAmount;
                    }

                    // ✅ Arrears: unpaid challans before this fee_month
                    $previousUnpaidChallans = App\Models\Challans::where('student_id', $data->student_id)
                        ->where('status', '!=', 'Paid')
                        ->whereDate('fee_month', '<', date('Y-m-d', strtotime($data->fee_month)))
                        ->where('id', '!=', $data->id)
                        ->sum(\DB::raw('total_amount - concession_amount'));

                    // ✅ Branch totals — accumulated ONCE per row
                    $branchTotal['monthly_fee']      += $monthly_fee;
                    $branchTotal['previousUnpaid']   += $previousUnpaidChallans ?? 0;
                    $branchTotal['totalAmount']      += $data->total_amount - $data->concession_amount;
                    $branchTotal['concessionAmount'] += $data->concession_amount;

                    // ✅ Grand totals — accumulated ONCE per row
                    $grandTotal['monthly_fee']      += $monthly_fee;
                    $grandTotal['previousUnpaid']   += $previousUnpaidChallans ?? 0;
                    $grandTotal['totalAmount']      += $data->total_amount - $data->concession_amount;
                    $grandTotal['concessionAmount'] += $data->concession_amount;

                    $i++;
                @endphp

                <tr>
                    <td>{{ $i - 1 }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $data->challanNo }}</td>
                    <td>{{ $data->student->roll_no ?? $data->rollno ?? '' }}</td>
                    <td style="font-size: 8px; text-align:left;">
                        {!! nl2br(wordwrap(e($data->student->stdname ?? ''), 15, "\n")) !!}
                    </td>
                    <td style="font-size: 8px; text-align:left;">{{ $data->student->registeroption->name ?? '' }}</td>
                    <td style="font-size: 8px; text-align:left;">{{ $data->class->name ?? '' }}</td>
                    <td>{{ date('M-Y', strtotime($data->fee_month)) }}</td>
                    <td>
                        @if (!empty($data->other_months))
                            @foreach (explode(',', $data->other_months) as $month)
                                {{ \Carbon\Carbon::parse(trim($month))->format('M-Y') }}
                            @endforeach
                        @else
                            {{ date('M-Y', strtotime($data->fee_month)) }}
                        @endif
                    </td>

                    {{-- ✅ Monthly fee total (sum of all head prices) --}}
                    <td style="font-size: 8px; font-weight: bold; text-align:center;">{{ $monthly_fee }}</td>

                    {{-- ✅ Per-head columns — read from pre-built $rowHeadData array --}}
                    @foreach (@$heads ?? [] as $head)
                        <td style="text-align:center;">{{ $rowHeadData[$head->id]['price']      ?? 0 }}</td>
                        <td style="text-align:center;">{{ $rowHeadData[$head->id]['concession'] ?? 0 }}</td>
                        <td style="text-align:center;">{{ $rowHeadData[$head->id]['netAmount']  ?? 0 }}</td>
                    @endforeach

                    <td>{{ $previousUnpaidChallans ?? 0 }}</td>
                    <td>{{ $data->total_amount - $data->concession_amount }}</td>
                    <td>{{ $data->concession_amount }}</td>
                    <td>{{ $data->concession?->policy?->title ?? '' }}</td>
                </tr>
            @endforeach

            {{-- Branch Total Row --}}
            <tr class="branch-total-row" style="border-top: 3px solid #000;">
                <td colspan="9"
                    style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000;">
                    Branch Total
                </td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                    {{ $branchTotal['monthly_fee'] }}
                </td>
                @foreach (@$heads ?? [] as $head)
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['price']      ?? 0 }}
                    </td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['concession'] ?? 0 }}
                    </td>
                    <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['netAmount']  ?? 0 }}
                    </td>
                @endforeach
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                    {{ $branchTotal['previousUnpaid'] }}
                </td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                    {{ $branchTotal['totalAmount'] }}
                </td>
                <td style="font-size: 8px; font-weight: bold; border: 2px solid #000; background-color: #B8B8B8; text-align:center;">
                    {{ $branchTotal['concessionAmount'] }}
                </td>
                <td colspan="1" style="font-size: 8px; border: 2px solid #000; background-color: #B8B8B8;"></td>
            </tr>

            {{-- Spacer rows --}}
            @for ($s = 0; $s < 3; $s++)
                <tr>
                    <td colspan="{{ $heads->count() * 3 + 14 }}" style="background: #fff; height: 10px; border: none;"></td>
                </tr>
            @endfor

        @endforeach

        {{-- Grand Total Row --}}
        <tr>
            <td colspan="9"
                style="font-size: 8px; font-weight: bold; background-color: #8B8B8B; text-align: center; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;">
                Grand Total
            </td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                {{ $grandTotal['monthly_fee'] }}
            </td>
            @foreach (@$heads ?? [] as $head)
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['price']      ?? 0 }}
                </td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['concession'] ?? 0 }}
                </td>
                <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['netAmount']  ?? 0 }}
                </td>
            @endforeach
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                {{ $grandTotal['previousUnpaid'] }}
            </td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                {{ $grandTotal['totalAmount'] }}
            </td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:center;">
                {{ $grandTotal['concessionAmount'] }}
            </td>
            <td colspan="1" style="font-size: 8px; background-color: #A9A9A9; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;"></td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')