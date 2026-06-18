<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre Challan Report</title>
    <style>
        .num {
            mso-number-format: '\#\,\#\#0';
            text-align: right;
        }

        .num-bold {
            mso-number-format: '\#\,\#\#0';
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $grandTotal = [
            'total_amount' => 0,
            'total_net' => 0,
            'total_discount' => 0,
            'arrears' => 0,
            'late_fee' => 0,
            'gross' => 0,
            'prev_month_amount' => 0,
            'difference' => 0,
        ];
        $grandHeadTotals = [];
        $i = 1;
    @endphp

    <tr>
        <td colspan="7"
            style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
            The Lynx School
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center;">
        </td>
    </tr>
    @if (@$is_branch)
        <tr>
            <td colspan="7" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                <span style="text-transform: uppercase;">
                    {{ @$branchName }}
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
            </td>
        </tr>
    @else
        <tr>
            <td colspan="7" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                <span style="text-transform: uppercase;">
                    @php
                        $branchKey = request()->get('branches');
                        $branchName = 'All Branches';

                        // Ensure branchKey is a valid array offset type (string or integer)
                        if ($branchKey && (is_string($branchKey) || is_int($branchKey))) {
                            if (isset($branches) && is_array($branches) && array_key_exists($branchKey, $branches)) {
                                $branchName = $branches[$branchKey];
                            } elseif (isset($branches) && is_object($branches) && method_exists($branches, 'get')) {
                                $branchName = $branches->get($branchKey, 'All Branches');
                            }
                        }

                        // Fallback to branch variable if available
                        if (isset($branch) && $branch && $branchName === 'All Branches') {
                            $branchName = $branch;
                        }
                    @endphp
                    {{ $branchName }}
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="{{ $colspan ?? 7 }}"
            style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
            <span style="text-transform: uppercase;">
                @php $reportVal = $report_name ?? ''; @endphp
                {{ is_array($reportVal) ? $reportVal['name'] ?? '' : $reportVal }}
            </span>
        </td>
    </tr>
    <tr>
        
        <td colspan="{{ $colspan ?? 7 }}"
            style="text-align: left; font-family: calibri; font-size: 12px;">
            <span style="text-transform: uppercase; text-align: left;">
                Average Fees: {{$averageTuitionFee}}
                
            </span>
        </td>
    </tr>
    @if (@$is_period)
        <tr>
            <td colspan="7" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
                @php
                    $reportName = is_array($report_name ?? '') ? $report_name['name'] ?? '' : $report_name ?? '';

                    if (str_contains(strtolower($reportName), 'fee structure listing')) {
                        $sessions = [];

                        // Handle both array and single session cases
                        if (isset($params['session'])) {
                            if (is_array($params['session'])) {
                                // Multiple sessions selected - get all session years
                                $sessions = \App\Models\Session::whereIn('id', $params['session'])
                                    ->pluck('year')
                                    ->toArray();
                            } else {
                                // Single session selected
                                $session = \App\Models\Session::find($params['session']);
                                if ($session) {
                                    $sessions = [$session->year];
                                }
                            }
                        }

                        if (!empty($sessions)) {
                            // For first session in list, show "From prev_year To current_year"
                            $currentSession = $sessions[0];
                            $years = explode('-', $currentSession);
                            if (count($years) === 2) {
                                $fromSession = $years[0] - 1 . '-' . ($years[1] - 1);
                                echo "From $fromSession To $currentSession";
                            }

                            // If multiple sessions, append the rest
                            if (count($sessions) > 1) {
                                echo ' (Also: ' . implode(', ', array_slice($sessions, 1)) . ')';
                            }
                        } else {
                            echo 'All Sessions';
                        }
                    } else {
                        // Handle date period display
                        $fromDate = $params['date_from'] ?? '';
                        $toDate = $params['date_to'] ?? '';

                        if ($fromDate || $toDate) {
                            echo 'From: ' . ($fromDate ? date('d M Y', strtotime($fromDate)) : '') . ' ';
                            echo '    To ' . ($toDate ? date('d M Y', strtotime($toDate)) : '');
                        }
                    }
                @endphp
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;"></td>
        </tr>
    @endif
    <table>
        <thead>
            <tr>
                <th colspan="7">Student Information</th>
                <th colspan="3">Monthly Fee</th>
                @foreach ($heads as $head)
                    <th colspan="3">{{ $head->fee_head }}</th>
                @endforeach
                <th colspan="3">Current Month Bill</th>
                <th colspan="2">Discount</th>
                <th>This Month</th>
                <th>Prev Month</th>
                <th>Difference</th>
            </tr>

            <tr>
                <th>Sr.</th>
                <th>B Sr.</th>
                <th>Roll No</th>
                <th>Student Name</th>
				<th>Admission Date</th>
                <th>Reg Type</th>
                <th>Class</th>
                <th>Billing Month</th>
                <th>Challan Type</th>
                <th>Rs.</th>
                @foreach ($heads as $head)
                    <th>Rs.</th>
                    <th>Disc.</th>
                    <th>Net Rs.</th>
                @endforeach
                <th>Arrears</th>
                <th>Late Fee</th>
                <th>Net Receivable</th>
                <th>Discount</th>
                <th>Policy</th>
                <th>Gross</th>
                <th>Prev Amt</th>
                <th>+/- Diff</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($report as $branchId => $students)
                @php
                   $totalCols = 18 + ($heads->count() * 3);
                @endphp
                <tr>
                    <td colspan="4"
                        style="font-weight:bold; text-align:left; background-color:#bcbcbc;
                               border:1px solid #000; white-space:nowrap; overflow:visible; padding:2px 4px;">
                        {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                    </td>
                    <td colspan="{{ $totalCols - 4 }}" style="background-color:#bcbcbc; border:1px solid #000;"></td>
                </tr>

                @php
                    $branchTotal = [
                        'total_amount' => 0,
                        'total_net' => 0,
                        'total_discount' => 0,
                        'arrears' => 0,
                        'late_fee' => 0,
                        'gross' => 0,
                        'prev_month_amount' => 0,
                        'difference' => 0,
                    ];
                    $branchHeadTotals = collect($heads)
                        ->mapWithKeys(
                            fn($h) => [
                                $h->id => ['amount' => 0, 'discount_amount' => 0, 'net_amount' => 0],
                            ],
                        )
                        ->toArray();
                @endphp

                @foreach ($students as $index => $stu)
                    @php
                        $stu = array_merge(
                            [
                                'total_amount' => 0,
                                'total_net' => 0,
                                'total_discount' => 0,
                                'arrears' => 0,
                                'late_fee' => 0,
                                'gross' => 0,
                                'head_details' => [],
                                'prev_month_amount' => 0,
                                'difference' => 0,
                            ],
                            $stu,
                        );

                        $diffValue = (float) $stu['difference'];
                        $diffColor = $diffValue > 0 ? 'green' : ($diffValue < 0 ? '#cc0000' : 'inherit');
                    @endphp

                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $stu['roll_no'] }}</td>
                        <td>{{ $stu['student_name'] }}</td>
						<td>{{ $stu['adm_date'] ? \Carbon\Carbon::parse($stu['adm_date'])->format('d-M-Y') : '-' }}</td>
                        <td>{{ $stu['registration_type'] }}</td>
                        <td>{{ $stu['class_name'] }}</td>

                        {{-- ↓ Raw Y-m-d so export class converts to Excel date serial --}}
                        <td>{{ \Carbon\Carbon::parse($dateInput)->format('Y-M') }}</td>

                        <td>{{ $stu['challan_type_short'] }}</td>
                        <td class="num">{{ (int) $stu['total_amount'] }}</td>

                        @foreach ($heads as $head)
                            @php
                                $h = array_merge(
                                    [
                                        'amount' => 0,
                                        'discount_amount' => 0,
                                        'net_amount' => 0,
                                    ],
                                    $stu['head_details'][$head->id] ?? [],
                                );

                                $branchHeadTotals[$head->id]['amount'] += $h['amount'];
                                $branchHeadTotals[$head->id]['discount_amount'] += $h['discount_amount'];
                                $branchHeadTotals[$head->id]['net_amount'] += $h['net_amount'];

                                if (!isset($grandHeadTotals[$head->id])) {
                                    $grandHeadTotals[$head->id] = [
                                        'amount' => 0,
                                        'discount_amount' => 0,
                                        'net_amount' => 0,
                                    ];
                                }
                                $grandHeadTotals[$head->id]['amount'] += $h['amount'];
                                $grandHeadTotals[$head->id]['discount_amount'] += $h['discount_amount'];
                                $grandHeadTotals[$head->id]['net_amount'] += $h['net_amount'];
                            @endphp
                            <td class="num">{{ (int) $h['amount'] }}</td>
                            <td class="num">{{ (int) $h['discount_amount'] }}</td>
                            <td class="num">{{ (int) $h['net_amount'] }}</td>
                        @endforeach

                        <td class="num">{{ (int) $stu['arrears'] }}</td>
                        <td class="num">{{ (int) $stu['late_fee'] }}</td>
                        <td class="num-bold">{{ (int) ($stu['total_net'] + $stu['arrears']) }}</td>
                        <td class="num">{{ (int) $stu['total_discount'] }}</td>
                        <td>{{ $stu['concession_category'] ?? '' }}</td>
                        <td class="num">{{ (int) $stu['gross'] }}</td>
                        <td class="num">{{ (int) $stu['prev_month_amount'] }}</td>
                        <td class="num" style="color: {{ $diffColor }};">{{ (int) $diffValue }}</td>

                        @php
                            $branchTotal['total_amount'] += $stu['total_amount'];
                            $branchTotal['total_discount'] += $stu['total_discount'];
                            $branchTotal['total_net'] += $stu['total_net'];
                            $branchTotal['arrears'] += $stu['arrears'];
                            $branchTotal['late_fee'] += $stu['late_fee'];
                            $branchTotal['gross'] += $stu['gross'];
                            $branchTotal['prev_month_amount'] += $stu['prev_month_amount'];
                            $branchTotal['difference'] += $stu['difference'];

                            $grandTotal['total_amount'] += $stu['total_amount'];
                            $grandTotal['total_discount'] += $stu['total_discount'];
                            $grandTotal['total_net'] += $stu['total_net'];
                            $grandTotal['arrears'] += $stu['arrears'];
                            $grandTotal['late_fee'] += $stu['late_fee'];
                            $grandTotal['gross'] += $stu['gross'];
                            $grandTotal['prev_month_amount'] += $stu['prev_month_amount'];
                            $grandTotal['difference'] += $stu['difference'];
                        @endphp
                    </tr>
                @endforeach

                @php
                    $bDiffColor =
                        $branchTotal['difference'] > 0
                            ? 'green'
                            : ($branchTotal['difference'] < 0
                                ? '#cc0000'
                                : 'inherit');
                @endphp
                <tr>
                    <td colspan="9"
                        style="background:gray; font-size:8px; text-align:center; border:1px solid black; font-weight:bold;">
                        Branch Total
                    </td>
                    <td class="num"
                        style="background:gray; font-size:8px; border:1px solid black; font-weight:bold;">
                        {{ (int) $branchTotal['total_amount'] }}
                    </td>
                    @foreach ($heads as $head)
                        <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                            {{ (int) ($branchHeadTotals[$head->id]['amount'] ?? 0) }}
                        </td>
                        <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                            {{ (int) ($branchHeadTotals[$head->id]['discount_amount'] ?? 0) }}
                        </td>
                        <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                            {{ (int) ($branchHeadTotals[$head->id]['net_amount'] ?? 0) }}
                        </td>
                    @endforeach
                    <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) $branchTotal['arrears'] }}
                    </td>
                    <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) $branchTotal['late_fee'] }}
                    </td>
                    <td class="num-bold" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) ($branchTotal['total_net'] + $branchTotal['arrears']) }}
                    </td>
                    <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) $branchTotal['total_discount'] }}
                    </td>
                    <td style="background:gray; font-size:8px; border:1px solid black;"></td>
                    <td class="num" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) $branchTotal['gross'] }}
                    </td>
                    <td class="num-bold" style="background:gray; font-size:8px; border:1px solid black;">
                        {{ (int) $branchTotal['prev_month_amount'] }}
                    </td>
                    <td class="num-bold"
                        style="background:gray; font-size:8px; border:1px solid black; color:{{ $bDiffColor }};">
                        {{ (int) $branchTotal['difference'] }}
                    </td>
                </tr>

                <tr>
                    <td colspan="{{ $totalCols }}"></td>
                </tr>
                <tr>
                    <td colspan="{{ $totalCols }}"></td>
                </tr>
            @endforeach

            @php
                $gDiffColor =
                    $grandTotal['difference'] > 0 ? 'green' : ($grandTotal['difference'] < 0 ? '#cc0000' : 'inherit');
            @endphp
            <tr>
                <td colspan="9"
                    style="background:gray; font-size:8px; border:1px solid black; text-align:center;
                           border-top:2px double black; border-bottom:2px double black; font-weight:bold;">
                    Grand Total
                </td>
                <td class="num-bold"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['total_amount'] }}
                </td>
                @foreach ($heads as $head)
                    <td class="num"
                        style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                        {{ (int) ($grandHeadTotals[$head->id]['amount'] ?? 0) }}
                    </td>
                    <td class="num"
                        style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                        {{ (int) ($grandHeadTotals[$head->id]['discount_amount'] ?? 0) }}
                    </td>
                    <td class="num"
                        style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                        {{ (int) ($grandHeadTotals[$head->id]['net_amount'] ?? 0) }}
                    </td>
                @endforeach
                <td class="num"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['arrears'] }}
                </td>
                <td class="num"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['late_fee'] }}
                </td>
                <td class="num-bold"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) ($grandTotal['total_net'] + $grandTotal['arrears']) }}
                </td>
                <td class="num"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['total_discount'] }}
                </td>
                <td
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                </td>
                <td class="num"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['gross'] }}
                </td>
                <td class="num-bold"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black;">
                    {{ (int) $grandTotal['prev_month_amount'] }}
                </td>
                <td class="num-bold"
                    style="background:gray; font-size:8px; border:1px solid black; border-top:2px double black; border-bottom:2px double black; color:{{ $gDiffColor }};">
                    {{ (int) $grandTotal['difference'] }}
                </td>
            </tr>

        </tbody>
    </table>

    @include('student.exports.footer')
</body>

</html>