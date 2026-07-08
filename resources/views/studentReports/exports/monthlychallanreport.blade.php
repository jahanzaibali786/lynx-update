@include('student.exports.header')
<table class="datatable">
    <thead>
        @php
            $i = 1;
            $grandTotal = [
                'previousUnpaid' => 0,
                'gross'          => 0,
                'totalAmount'    => 0,
                'concessionAmount' => 0,
                'monthly_fee'    => 0,
            ];
            $grandHeadTotals = [];
        @endphp
        <tr>
            <th colspan="9">Student Information</th>
            <th>Monthly Fee</th>
            @foreach (@$heads as $head)
                <th colspan="3">{{ @$head->fee_head }}</th>
            @endforeach
            <th colspan="3">Current Month Bill</th>
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
            <th>Gross</th>
            <th>Net Receivable</th>
            <th>Discount %</th>
            <th>Category</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $a => $row)
            <tr>
                <td colspan="4"
                    style="font-weight:bold; text-align:left; background-color:#bcbcbc; border:1px solid #000;">
                    {{ $branches[$a] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="{{ 6 + $heads->count() * 3 + 5 }}"
                    style="background-color:#bcbcbc; border:1px solid #000;">
                </td>
            </tr>

            @php
                $branchTotal = [
                    'previousUnpaid'   => 0,
                    'gross'            => 0,
                    'totalAmount'      => 0,
                    'concessionAmount' => 0,
                    'monthly_fee'      => 0,
                ];
                $branchHeadTotals = [];
            @endphp

            @foreach ($row as $index => $data)
                @php
                    // ── Monthly fee: sum of ALL head prices on this challan ──
                    $monthly_fee = 0;
                    foreach ($heads as $head) {
                        $specificHead = $data->heads->firstWhere('head_id', $head->id);
                        $monthly_fee += $specificHead ? floatval($specificHead->price) : 0;
                    }

                    // ── Gross = total_amount (full, before concession) ───────
                    $total_amount      = floatval($data->total_amount ?? 0);
                    $concession_amount = floatval($data->concession_amount ?? 0);
                    $gross             = $total_amount - $concession_amount;                      // gross = full billed amount

                    // ── Arrears ──────────────────────────────────────────────
                    $startDate = '2026-01-01';
                    $previousUnpaidChallans = App\Models\Challans::where('student_id', $data->student_id)
                        ->where('status', '!=', 'Paid')
						->where('challan_type','!=','registration')
                        ->whereDate('fee_month', '>=', $startDate)
                        ->whereDate('fee_month', '<', date('Y-m-d', strtotime($data->fee_month)))
                        ->where('id', '!=', $data->id)
                        ->sum(\DB::raw('total_amount - concession_amount - paid_amount'));
                    $net_receivable    = ($total_amount - $concession_amount) + $previousUnpaidChallans; // net after discount

                    // ── Adm date parsing ─────────────────────────────────────
                    $admDateRaw  = $data->enrollstudent->adm_date ?? null;
                    $admCarbon   = null;
                    if ($admDateRaw) {
                        foreach (['d-M-Y', 'd-M-y', 'Y-m-d', 'd/m/Y', 'm/d/Y'] as $fmt) {
                            try {
                                $admCarbon = \Carbon\Carbon::createFromFormat($fmt, $admDateRaw);
                                if ($admCarbon) break;
                            } catch (\Exception $e) {
                                $admCarbon = null;
                            }
                        }
                        if (!$admCarbon) {
                            try { $admCarbon = \Carbon\Carbon::parse($admDateRaw); } catch (\Exception $e) {}
                        }
                    }
                @endphp

                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ @$data->challanNo }}</td>
                    <td>{{ @$data->student->roll_no }}</td>
                    <td>{{ @$data->student->stdname }}</td>
                    <td>{{ @$data->student->registeroption->name }}</td>
                    <td>{{ @$data->class->name }}</td>

                    {{-- D/Adm date — Excel serial so the column is formatted as date in xlsx --}}
                    <td>
                        {{ $admCarbon
                            ? \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($admCarbon)
                            : '' }}
                    </td>

                    {{-- Billing Month — Excel serial --}}
                    <td>
                        {{ \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel(
                               \Carbon\Carbon::parse($data->fee_month)
                           ) }}
                    </td>

                    <td>{{ $monthly_fee }}</td>

                    @foreach ($heads as $head)
                        @php
                            $headData   = $data->heads->firstWhere('head_id', $head->id);
                            $price      = floatval($headData->price      ?? 0);
                            $concession = floatval($headData->concession ?? 0);
                            $netAmount  = $price - $concession;

                            if (!isset($branchHeadTotals[$head->id])) {
                                $branchHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                            }
                            $branchHeadTotals[$head->id]['price']     += $price;
                            $branchHeadTotals[$head->id]['concession'] += $concession;
                            $branchHeadTotals[$head->id]['netAmount']  += $netAmount;

                            if (!isset($grandHeadTotals[$head->id])) {
                                $grandHeadTotals[$head->id] = ['price' => 0, 'concession' => 0, 'netAmount' => 0];
                            }
                            $grandHeadTotals[$head->id]['price']     += $price;
                            $grandHeadTotals[$head->id]['concession'] += $concession;
                            $grandHeadTotals[$head->id]['netAmount']  += $netAmount;
                        @endphp
                        <td>{{ $price }}</td>
                        <td>{{ $concession }}</td>
                        <td>{{ $netAmount }}</td>
                    @endforeach

                    <td>{{ $previousUnpaidChallans }}</td>
                    <td>{{ $gross }}</td>
                    <td>{{ $net_receivable }}</td>
                    <td>{{ $concession_amount }}</td>
                    <td>{{ @$data->concession?->policy?->title ?? '' }}</td>

                    @php
                        // Accumulate ONCE here (removed from top of loop)
                        $branchTotal['monthly_fee']      += $monthly_fee;
                        $branchTotal['gross']            += $gross;
                        $branchTotal['previousUnpaid']   += $previousUnpaidChallans;
                        $branchTotal['totalAmount']      += $net_receivable;
                        $branchTotal['concessionAmount'] += $concession_amount;

                        $grandTotal['monthly_fee']       += $monthly_fee;
                        $grandTotal['gross']             += $gross;
                        $grandTotal['previousUnpaid']    += $previousUnpaidChallans;
                        $grandTotal['totalAmount']       += $net_receivable;
                        $grandTotal['concessionAmount']  += $concession_amount;

                        $i++;
                    @endphp
                </tr>
            @endforeach

            {{-- ── Branch Total ────────────────────────────────────────── --}}
            @php
                // fixed cols: Sr(1)+BSr(1)+BillNo(1)+RollNo(1)+Name(1)+Reg(1)+Class(1)+Adm(1)+Month(1) = 9
                // then monthly_fee(1) + heads(n*3) + arrears(1) + gross(1) + net(1) + disc(1) + cat(1) = 5+n*3
                $totalDataCols = 9 + 1 + ($heads->count() * 3) + 5;
            @endphp
            <tr>
                <td colspan="9"
                    style="font-size:8px; font-weight:bold; background-color:#B8B8B8; text-align:center; border:2px solid #000;">
                    Branch Total
                </td>
                <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                    {{ $branchTotal['monthly_fee'] }}
                </td>
                @foreach ($heads as $head)
                    <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['price'] ?? 0 }}
                    </td>
                    <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['concession'] ?? 0 }}
                    </td>
                    <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                        {{ $branchHeadTotals[$head->id]['netAmount'] ?? 0 }}
                    </td>
                @endforeach
                <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                    {{ $branchTotal['previousUnpaid'] }}
                </td>
                <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                    {{ $branchTotal['gross'] }}
                </td>
                <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                    {{ $branchTotal['totalAmount'] }}
                </td>
                <td style="font-size:8px; font-weight:bold; border:2px solid #000; background-color:#B8B8B8; text-align:center;">
                    {{ $branchTotal['concessionAmount'] }}
                </td>
                <td style="font-size:8px; border:2px solid #000; background-color:#B8B8B8;"></td>
            </tr>

            @php $spacerCols = 9 + 1 + ($heads->count() * 3) + 5; @endphp
            <tr><td colspan="{{ $spacerCols }}" style="background:#fff; height:10px; border:none;"></td></tr>
            <tr><td colspan="{{ $spacerCols }}" style="background:#fff; height:10px; border:none;"></td></tr>
            <tr><td colspan="{{ $spacerCols }}" style="background:#fff; height:10px; border:none;"></td></tr>
        @endforeach

        {{-- ── Grand Total ─────────────────────────────────────────────── --}}
        @php $spacerCols = 9 + 1 + ($heads->count() * 3) + 5; @endphp
        <tr>
            <td colspan="9"
                style="font-size:8px; font-weight:bold; background-color:#8B8B8B; text-align:center; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000;">
                Grand Total
            </td>
            <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                {{ $grandTotal['monthly_fee'] }}
            </td>
            @foreach ($heads as $head)
                <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['price'] ?? 0 }}
                </td>
                <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['concession'] ?? 0 }}
                </td>
                <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                    {{ $grandHeadTotals[$head->id]['netAmount'] ?? 0 }}
                </td>
            @endforeach
            <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                {{ $grandTotal['previousUnpaid'] }}
            </td>
            <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                {{ $grandTotal['gross'] }}
            </td>
            <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                {{ $grandTotal['totalAmount'] }}
            </td>
            <td style="font-size:8px; font-weight:bold; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000; text-align:center;">
                {{ $grandTotal['concessionAmount'] }}
            </td>
            <td style="font-size:8px; background-color:#A9A9A9; border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000;"></td>
        </tr>

        <tr><td colspan="{{ $spacerCols }}" style="background:#fff; height:20px; border:none;"></td></tr>

        {{-- Summary section variables --}}
        @php
            $sc_type   = 3; $sc_branch = 3; $sc_count = 3; $sc_amount = 3;
            $sty_section = 'font-size:9px; font-weight:bold; background-color:#B0B0B0; color:#fff; border:2px solid #444; text-align:left; padding:4px 8px;';
            $sty_hdr     = 'font-size:8px; font-weight:bold; background-color:#C8C8C8; border:1px solid #555; padding:3px; text-align:center;';
            $sty_row     = 'font-size:8px; background-color:#EFEFEF; border:1px solid #999; padding:3px;';
            $sty_tot     = 'font-size:8px; font-weight:bold; background-color:#B0B0B0; border:1px solid #444; border-top:2px solid #333; padding:3px;';
        @endphp

        {{-- Admission Summary --}}
        @php $hasAdmission = !empty($admissionSummary) && collect($admissionSummary)->sum('count') > 0; @endphp
        @if ($hasAdmission)
            <tr><td colspan="12" style="{{ $sty_section }}">Admission Challans &mdash; This Month</td></tr>
            <tr>
                <td colspan="{{ $sc_type }}"   style="{{ $sty_hdr }}">Type</td>
                <td colspan="{{ $sc_branch }}" style="{{ $sty_hdr }}">Branch</td>
                <td colspan="{{ $sc_count }}"  style="{{ $sty_hdr }}">No. of Challans</td>
                <td colspan="{{ $sc_amount }}" style="{{ $sty_hdr }} text-align:right;">Total Receivable (Net)</td>
            </tr>
            @php $admTotalAmt = 0; @endphp
            @foreach ($admissionSummary as $branchId => $summary)
                @php $admTotalAmt += floatval($summary['total']); @endphp
                <tr>
                    <td colspan="{{ $sc_type }}"   style="{{ $sty_row }} text-align:center;">Admission</td>
                    <td colspan="{{ $sc_branch }}" style="{{ $sty_row }} padding-left:6px;">{{ $branches[$branchId] ?? 'Branch #'.$branchId }}</td>
                    <td colspan="{{ $sc_count }}"  style="{{ $sty_row }} text-align:center;">{{ $summary['count'] }}</td>
                    <td colspan="{{ $sc_amount }}" style="{{ $sty_row }} text-align:right;">{{ number_format($summary['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="{{ $sc_type + $sc_branch + $sc_count }}" style="{{ $sty_tot }} text-align:right; padding-right:6px;">Total</td>
                <td colspan="{{ $sc_amount }}" style="{{ $sty_tot }} text-align:right;">{{ number_format($admTotalAmt, 2) }}</td>
            </tr>
            <tr><td colspan="12" style="background:#fff; height:14px; border:none;"></td></tr>
        @endif

        {{-- Advance Summary --}}
        @php $hasAdvance = !empty($advanceSummary) && collect($advanceSummary)->sum('count') > 0; @endphp
        @if ($hasAdvance)
            <tr><td colspan="12" style="{{ $sty_section }}">Advance Challans &mdash; This Month</td></tr>
            <tr>
                <td colspan="{{ $sc_type }}"   style="{{ $sty_hdr }}">Type</td>
                <td colspan="{{ $sc_branch }}" style="{{ $sty_hdr }}">Branch</td>
                <td colspan="{{ $sc_count }}"  style="{{ $sty_hdr }}">No. of Challans</td>
                <td colspan="{{ $sc_amount }}" style="{{ $sty_hdr }} text-align:right;">Total Receivable (Net)</td>
            </tr>
            @php $advTotalAmt = 0; @endphp
            @foreach ($advanceSummary as $branchId => $summary)
                @php $advTotalAmt += floatval($summary['total']); @endphp
                <tr>
                    <td colspan="{{ $sc_type }}"   style="{{ $sty_row }} text-align:center;">Advance</td>
                    <td colspan="{{ $sc_branch }}" style="{{ $sty_row }} padding-left:6px;">{{ $branches[$branchId] ?? 'Branch #'.$branchId }}</td>
                    <td colspan="{{ $sc_count }}"  style="{{ $sty_row }} text-align:center;">{{ $summary['count'] }}</td>
                    <td colspan="{{ $sc_amount }}" style="{{ $sty_row }} text-align:right;">{{ number_format($summary['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="{{ $sc_type + $sc_branch + $sc_count }}" style="{{ $sty_tot }} text-align:right; padding-right:6px;">Total</td>
                <td colspan="{{ $sc_amount }}" style="{{ $sty_tot }} text-align:right;">{{ number_format($advTotalAmt, 2) }}</td>
            </tr>
            <tr><td colspan="12" style="background:#fff; height:14px; border:none;"></td></tr>
        @endif
    </tbody>
</table>
@include('student.exports.footer')