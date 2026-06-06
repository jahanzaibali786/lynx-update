@foreach ($datas as $key => $data)
    @php
        $payscale = $data->employee->employee_payscale_details->last();
    @endphp

    <table>
        <tr>
            <td></td>
            <td colspan="13" style="text-align: center;"></td>
        </tr>
        <tr style="height:70px;">
            <td></td>

            <td colspan="11"
                style="vertical-align: middle; text-align:left; font-family:'Edwardian Script ITC'; font-weight:bold; font-size:28px;">
                The Lynx School
            </td>

            <td colspan="2" style="vertical-align: middle; text-align:right;">
                <img src="{{ public_path('assets/images/lynxlogo(2).png') }}" alt="School Logo" width="75"
                    height="75" style="display:block;">
            </td>
        </tr>
        @if (@$is_branch)
            <tr>
                <td></td>
                <td colspan="13" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                    <span style="text-transform: uppercase;">
                        {{ @$branchName }}
                    </span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="13" style="text-align: center;"></td>
            </tr>
        @else
            <tr>
                <td></td>
                <td colspan="13" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                    <span style="text-transform: uppercase;">
                        @php
                            $branchKey = request()->get('branches');
                            $branchName = 'All Branches';

                            if ($branchKey && (is_string($branchKey) || is_int($branchKey))) {
                                if (
                                    isset($branches) &&
                                    is_array($branches) &&
                                    array_key_exists($branchKey, $branches)
                                ) {
                                    $branchName = $branches[$branchKey];
                                } elseif (isset($branches) && is_object($branches) && method_exists($branches, 'get')) {
                                    $branchName = $branches->get($branchKey, 'All Branches');
                                }
                            }

                            if (isset($branch) && $branch && $branchName === 'All Branches') {
                                $branchName = $branch;
                            }
                        @endphp
                        {{ $branchName }}
                    </span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="13" style="text-align: center;"></td>
            </tr>
        @endif
        <tr>
            <td></td>
            <td colspan="{{ $colspan ?? 13 }}"
                style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
                <span style="text-transform: uppercase;">
                    @php $reportVal = $report_name ?? ''; @endphp
                    {{ is_array($reportVal) ? $reportVal['name'] ?? '' : $reportVal }}
                </span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="13" style="text-align: center;"></td>
        </tr>
        @if (@$is_period)
            <tr>
                <td></td>
                <td colspan="13" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
                    @php
                        $reportName = is_array($report_name ?? '') ? $report_name['name'] ?? '' : $report_name ?? '';

                        if (str_contains(strtolower($reportName), 'fee structure listing')) {
                            $sessions = [];

                            if (isset($params['session'])) {
                                if (is_array($params['session'])) {
                                    $sessions = \App\Models\Session::whereIn('id', $params['session'])
                                        ->pluck('year')
                                        ->toArray();
                                } else {
                                    $session = \App\Models\Session::find($params['session']);
                                    if ($session) {
                                        $sessions = [$session->year];
                                    }
                                }
                            }

                            if (!empty($sessions)) {
                                $currentSession = $sessions[0];
                                $years = explode('-', $currentSession);
                                if (count($years) === 2) {
                                    $fromSession = $years[0] - 1 . '-' . ($years[1] - 1);
                                    echo "From $fromSession To $currentSession";
                                }

                                if (count($sessions) > 1) {
                                    echo ' (Also: ' . implode(', ', array_slice($sessions, 1)) . ')';
                                }
                            } else {
                                echo 'All Sessions';
                            }
                        } else {
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
                <td></td>
                <td colspan="13" style="text-align: center;"></td>
            </tr>
        @endif

        @php
            // Attendance & leaves (for this month)
            $att = optional($data->employee->employee_monthly_salaries_attend->first());

            $cl_open = (int) ($att->total_casual ?? 0);
            $cl_bal = (int) ($att->bal_casual ?? 0);
            $cl_taken = max(0, $cl_open - $cl_bal);

            $al_open = (int) ($att->total_annual ?? 0);
            $al_bal = (int) ($att->bal_annual ?? 0);
            $al_taken = max(0, $al_open - $al_bal);

            $workingDays = (int) ($att->working_days ?? ($data->sal_days ?? 0));
        @endphp

        @php
            // ===== Dynamic data for the large table (EARNINGS / DEDUCTIONS / CONTRIBUTIONS) =====
            $salDate = \Carbon\Carbon::parse($data->salary_date);
            $ytdStart = $salDate->copy()->startOfYear()->toDateString();
            $ytdEnd = $salDate->copy()->endOfMonth()->toDateString();
            $empId = $data->employee_id;

            // Map head_id -> head name from $salaryHeads
            $headNames = collect($salaryHeads ?? [])->pluck('head', 'id');

            // Current month heads (P.M)
            $pmHeads = collect($data->salary_heads ?? [])->mapWithKeys(function ($h) use ($headNames) {
                $name = $headNames[$h->head_id] ?? 'Head ' . $h->head_id;
                return [$name => (float) $h->head_value];
            });

            // Y.T.D per head (sum Jan..current month)
            $ytdHeads = \App\Models\EmployeeMonthlySalaryHeads::where('employee_id', $empId)
                ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
                ->selectRaw('head_id, SUM(head_value) as total')
                ->groupBy('head_id')
                ->get()
                ->mapWithKeys(function ($row) use ($headNames) {
                    $name = $headNames[$row->head_id] ?? 'Head ' . $row->head_id;
                    return [$name => (float) $row->total];
                });

            // Build Gross Salary rows dynamically
            $grossSalaryRows = [];
            foreach ($pmHeads as $name => $pmVal) {
                $grossSalaryRows[] = ['label' => $name, 'pm' => $pmVal, 'ytd' => (float) ($ytdHeads[$name] ?? 0)];
            }

            // Gross total (trust monthly 'gross' column)
            $GrossRs = (float) ($data->gross ?? 0);
            $grossYTD = (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
                ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
                ->sum('gross');

            // Enticements (from monthly slip columns)
            $conv_pm = (float) ($data->conv ?? 0);
            $fuel_pm = 0; // change if you store a fuel field
            $mobile_pm = (float) ($data->misc ?? 0); // treat 'misc' as mobile if that's your convention
$others_pm = (float) ($data->other ?? 0);

$conv_ytd = (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
    ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
    ->sum('conv');
$mobile_ytd = (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
    ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
    ->sum('misc');
$others_ytd = (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
    ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
    ->sum('other');

$earnings = [
    'Gross Salary' => $grossSalaryRows,
    'Enticements' => [
        ['label' => 'Other', 'pm' => $conv_pm, 'ytd' => $conv_ytd],
        ['label' => 'Fuel Allowance', 'pm' => $fuel_pm, 'ytd' => 0],
        ['label' => 'Mobile Allowance', 'pm' => $mobile_pm, 'ytd' => $mobile_ytd],
        ['label' => 'Other Allowance', 'pm' => $others_pm, 'ytd' => $others_ytd],
    ],
    'Adjustments' => [
        [
            'label' => 'Stop Salary (' . $salDate->format('M, y') . ')',
            'pm' => (float) ($data->stop_sal ?? 0),
            'ytd' => null,
        ],
    ],
];

// For "Net Gross Rs." and totals row
$NetGrossRs = $conv_pm + $fuel_pm + $mobile_pm + $others_pm; // enticements total P.M
$StopSalary = (float) ($data->stop_sal ?? 0);
$enticementsYTDTotal = $conv_ytd + 0 + $mobile_ytd + $others_ytd;

// Deductions (P.M + Y.T.D)
$deductions = [
    [
        'label' => 'Employee Security',
        'pm' => (float) ($data->emp_sec ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('emp_sec'),
    ],
    [
        'label' => 'E.O.B.I',
        'pm' => (float) ($data->eobi ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('eobi'),
    ],
    [
        'label' => 'P.E.S.S.I',
        'pm' => (float) ($data->pessi ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('pessi'),
    ],
    [
        'label' => 'Income Tax',
        'pm' => (float) ($data->it ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('it'),
    ],
    [
        'label' => 'Other Deduction',
        'pm' => (float) ($data->dedu ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('dedu'),
    ],
    [
        'label' => 'Advance',
        'pm' => (float) ($data->sal_advance ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('sal_advance'),
    ],
    ['label' => 'Stop Salary', 'pm' => 0, 'ytd' => '-'],
    [
        'label' => 'Training Course',
        'pm' => (float) ($data->tra_course ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('tra_course'),
    ],
    [
        'label' => 'Other Allowance',
        'pm' => (float) ($data->other ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('other'),
    ],
    [
        'label' => 'Loan Emp Security',
        'pm' => (float) ($data->loan ?? 0),
        'ytd' => (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
            ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
            ->sum('loan'),
    ],
];

$deductionsTotalPM = array_sum(array_map(fn($r) => (float) $r['pm'], $deductions));
$deductionsTotalYTD = array_sum(
    array_map(fn($r) => is_numeric($r['ytd']) ? (float) $r['ytd'] : 0, $deductions),
);

// Employer contributions
$empSecYTD = (float) \App\Models\EmployeeMonthlySalary::where('employee_id', $empId)
    ->whereBetween('salary_date', [$ytdStart, $ytdEnd])
    ->sum('emp_sec');

$employerContributions = [
    'Employee Security Balance Y.T.D' => [['label' => 'Employee Security', 'amount' => $empSecYTD]],
    'Employer Contribution P.M' => [
        ['label' => 'Eobi Contribution', 'amount' => (float) ($data->eobi_employer ?? 0)],
        ['label' => 'Pessi Contribution', 'amount' => (float) ($data->pessi_employer ?? 0)],
        ['label' => 'Child Concession', 'amount' => (float) ($data->chaild_con ?? 0)],
    ],
];

$NetBalanceRs = $empSecYTD;
$CostToCompanyContributions =
    (float) ($data->eobi_employer ?? 0) +
    (float) ($data->pessi_employer ?? 0) +
    (float) ($data->chaild_con ?? 0);

// ----------- Flatten to match your renderer (no structure change)
$flatEarnings = [];
foreach ($earnings as $category => $items) {
    $flatEarnings[] = [
        'category' => $category,
        'label' => $category == 'Adjustments' ? 'Net Gross Rs.' : $category,
        'pm' => $category == 'Adjustments' ? $NetGrossRs + $GrossRs : 'P.M',
        'ytd' => $category == 'Adjustments' ? '' : 'Y.T.D',
        'head' => true,
        'total' => false,
    ];

    foreach ($items as $item) {
        $flatEarnings[] = [
            'category' => $category,
            'label' => $item['label'] ?? '',
            'pm' => $item['pm'] ?? '',
            'ytd' => $item['ytd'] ?? '',
            'head' => false,
            'total' => false,
        ];
    }

    if ($category === 'Gross Salary') {
        $flatEarnings[] = [
            'category' => $category,
            'label' => 'Gross Rs.',
            'pm' => $GrossRs,
            'ytd' => $grossYTD,
            'head' => false,
            'total' => true,
        ];
        $flatEarnings[] = [
            'category' => $category,
            'label' => '',
            'pm' => '',
            'ytd' => '',
            'head' => false,
            'total' => false,
        ];
    }
}

$flatDeductions = [];
if (count($deductions)) {
    $flatDeductions[] = [
        'label' => 'Heads',
        'pm' => 'P.M',
        'ytd' => 'Y.T.D',
        'head' => true,
        'total' => false,
    ];
    foreach ($deductions as $item) {
        $flatDeductions[] = [
            'label' => $item['label'] ?? '',
            'pm' => $item['pm'] ?? '',
            'ytd' => $item['ytd'] ?? '',
            'head' => false,
            'total' => false,
        ];
    }
}

$flatEmployerContributions = [];
foreach ($employerContributions as $category => $items) {
    $flatEmployerContributions[] = [
        'category' => $category,
        'label' => $category,
        'amount' => '',
        'head' => true,
        'total' => false,
    ];
    foreach ($items as $item) {
        $flatEmployerContributions[] = [
            'category' => $category,
            'label' => $item['label'],
            'amount' => $item['amount'],
            'head' => false,
            'total' => false,
        ];
    }
    if ($category === 'Employee Security Balance Y.T.D') {
        $flatEmployerContributions[] = [
            'category' => $category,
            'label' => '',
            'amount' => '',
            'head' => false,
            'total' => false,
        ];
        $flatEmployerContributions[] = [
            'category' => $category,
            'label' => 'Net Balance Rs.',
            'amount' => $NetBalanceRs,
            'head' => false,
            'total' => true,
        ];
        $flatEmployerContributions[] = [
            'category' => $category,
            'label' => '',
            'amount' => '',
            'head' => false,
            'total' => false,
        ];
    }
}

// Equalize lengths for 3 columns
$maxLength = max(count($flatEarnings), count($flatDeductions), count($flatEmployerContributions));
while (count($flatEarnings) < $maxLength) {
    $flatEarnings[] = [
        'category' => '',
        'label' => '',
        'pm' => '',
        'ytd' => '',
        'head' => false,
        'total' => false,
    ];
}
while (count($flatDeductions) < $maxLength) {
    $flatDeductions[] = ['label' => '', 'pm' => '', 'ytd' => '', 'head' => false, 'total' => false];
}
while (count($flatEmployerContributions) < $maxLength) {
    $flatEmployerContributions[] = ['label' => '', 'amount' => '', 'head' => false, 'total' => false];
}

$result = [
    'earnings' => $flatEarnings,
    'deductions' => $flatDeductions,
    'employer_contributions' => $flatEmployerContributions,
            ];
        @endphp

        <!-- Employee Information Section -->
        <tr>
            <td></td>
            <td colspan="13" style="border: 1px solid black; border-bottom: 0px solid white;"></td>
        </tr>
        <tr style="border: 1px solid black;">
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="font-weight: bold; font-size: 9px;">Employee#</td>
            <td style="text-align: left; font-size: 9px;">{{ $data->employee->id }}</td>
            <td colspan="2"></td>
            <td style="font-weight: bold; font-size: 9px;">Leaves Balances</td>
            <td style="font-weight: bold; font-size: 9px; text-align: center;">C/L</td>
            <td style="font-weight: bold; font-size: 9px; text-align: center;">A/L</td>
            <td></td>
            <td style="font-weight: bold; font-size: 9px;">Payment Date</td>
            <td></td>
            <td style="font-size: 9px;">
                {{ $data->paid_date ? \Carbon\Carbon::parse($data->paid_date)->format('d-M-y') : '-' }}
            </td>
            <td style="border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="font-weight: bold; font-size: 9px;">Name</td>
            <td colspan="2" style="font-size:9px;">{{ $data->employee->name }}</td>
            <td></td>
            <td style="font-weight: bold; font-size: 9px;">O.Balance</td>
            <td style="text-align:center; font-size:9px;">{{ $cl_open }}</td>
            <td style="text-align:center; font-size:9px;">{{ $al_open }}</td>
            <td></td>
            <td style="font-weight: bold; font-size: 9px;">Mode</td>
            <td></td>
            <td style="font-size:9px;">
                {{-- {{ optional($data->employee->employee_payscale_details->first())->paymode ?? '-' }}</td> --}}
                {{-- place a check on this if it is csh or cash then show cash otherwise show Bank if its bank --}}
                @if (strtolower(optional($data->employee->employee_payscale_details->first())->paymode) == 'cash')
                    Cash
                @else
                    Bank
                @endif
            </td>
            <td style="border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="font-weight: bold; font-size: 9px;">Corporate Title</td>
            <td style="font-size:9px;">{{ $data->employee->designation->name }}</td>
            <td colspan="2"></td>
            <td style="font-weight: bold; font-size: 9px;">Leaves</td>
            <td style="text-align:center; font-size:9px;">{{ $cl_taken }}</td>
            <td style="text-align:center; font-size:9px;">{{ $al_taken }}</td>
            <td></td>
            <td style="font-weight: bold; font-size: 9px;">Bank/Branch</td>
            <td></td>
            <td style="font-size:9px;">{{ optional($data->employee->employee_payscale_details->first())->paymode ?? '-' }}</td>
            <td style="border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="font-weight: bold; font-size: 9px;">Department</td>
            <td style="font-size:9px;">{{ $data->employee->department->name ?? '-' }}</td>
            <td colspan="2"></td>
            <td style="font-weight: bold; font-size: 9px;">C.Balance</td>
            <td style="text-align:center; font-size:9px;">{{ $cl_bal }}</td>
            <td style="text-align:center; font-size:9px;">{{ $al_bal }}</td>
            <td></td>
            <td style="font-weight: bold; font-size: 9px;">N.T.N</td>
            <td></td>
            <td style="font-size:9px;">
                {{ optional($data->employee->employee_payscale_details->first())->account_number ?? '-' }}</td>
            <td style="border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td colspan="4"></td>
            <td style="font-weight: bold; font-size: 9px;">Working Days</td>
            <td style="text-align:center; font-size:9px;">{{ $workingDays }}</td>
            <td colspan="6" style="border-right: 1px solid black;"></td>
        </tr>

        <!-- Table Headers -->
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td colspan="3" style="text-align: center; font-weight: bold; border: 1px solid black; font-size: 9px;">
                EARNINGS
            </td>
            <td></td>
            <td colspan="3" style="text-align: center; font-weight: bold; border: 1px solid black; font-size: 9px;">
                DEDUCTIONS</td>
            <td></td>
            <td colspan="3" style="text-align: center; font-weight: bold; border: 1px solid black; font-size: 9px;">
            </td>
            <td style="border-right: 1px solid black;"></td>
        </tr>

        @php
            // Build rows for renderer already done in $result above.
        @endphp

        @for ($i = 0; $i < count($result['earnings']); $i++)
            <tr>
                {{-- Blank Cells for alignment --}}
                <td></td>
                <td style="border-left: 1px solid black;"></td>

                {{-- EARNINGS --}}
                @if ($result['earnings'][$i]['head'])
                    <td
                        style="background: #d8d8d8; border: 1px solid black; font-weight: bold; font-size: 9px; text-transform: capitalize;">
                        {{ $result['earnings'][$i]['label'] }}
                    </td>
                    <td
                        style="background: #d8d8d8; border: 1px solid black; font-weight: bold; font-size: 9px; text-transform: uppercase; {{ (float) $result['earnings'][$i]['pm'] != '0' ? 'text-align: right;' : 'text-align: center;' }};">
                        {{ (float) $result['earnings'][$i]['pm'] != '0' ? number_format((float) $result['earnings'][$i]['pm']) : $result['earnings'][$i]['pm'] }}
                    </td>
                    <td
                        style="background: #d8d8d8; border: 1px solid black; font-weight: bold; text-align: center; font-size: 9px; text-transform: uppercase;">
                        {{ $result['earnings'][$i]['ytd'] }}
                    </td>
                @else
                    <td
                        style="border-left: 1px solid black; border-right: 1px solid black; font-size: 9px; font-weight: bold;{{ $result['earnings'][$i]['total'] == true ? 'border: 1px solid black; font-weigth: bold;' : '' }};">
                        {{ $result['earnings'][$i]['label'] }}
                    </td>
                    <td
                        style="border-left: 1px solid black; border-right: 1px solid black; font-size: 9px; text-align: right;{{ $result['earnings'][$i]['total'] == true ? 'border: 1px solid black; font-weigth: bold;' : '' }};">
                        {{ $result['earnings'][$i]['pm'] != '' ? number_format((float) $result['earnings'][$i]['pm']) : $result['earnings'][$i]['pm'] }}
                    </td>
                    <td
                        style="border-left: 1px solid black; border-right: 1px solid black; font-size: 9px; text-align: right;{{ $result['earnings'][$i]['total'] == true ? 'border: 1px solid black; font-weigth: bold;' : '' }};">
                        {{ $result['earnings'][$i]['ytd'] != '' ? number_format((float) $result['earnings'][$i]['ytd']) : $result['earnings'][$i]['ytd'] }}
                    </td>
                @endif

                {{-- Spacer between columns --}}
                <td></td>

                {{-- DEDUCTIONS --}}
                @if ($result['deductions'][$i]['head'] == true)
                    <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold; font-size: 9px;">
                        {{ $result['deductions'][$i]['label'] }}
                    </td>
                    <td
                        style="background: #d8d8d8; border: 1px solid black; text-align: center; font-weight: bold; font-size: 9px;">
                        P.M</td>
                    <td
                        style="background: #d8d8d8; border: 1px solid black; text-align: center; font-weight: bold; font-size: 9px;">
                        Y.T.D</td>
                @else
                    <td
                        style="font-size: 9px; font-weight: bold; border-left: 1px solid black; border-right: 1px solid black;">
                        {{ $result['deductions'][$i]['label'] }}
                    </td>
                    <td
                        style="font-size: 9px; text-align: right; border-left: 1px solid black; border-right: 1px solid black;">
                        {{ $result['deductions'][$i]['pm'] != '' ? number_format((float) $result['deductions'][$i]['pm']) : $result['deductions'][$i]['pm'] }}
                    </td>
                    <td
                        style="font-size: 9px; text-align: right; border-left: 1px solid black; border-right: 1px solid black;">
                        {{ $result['deductions'][$i]['ytd'] != '' ? number_format((float) $result['deductions'][$i]['ytd']) : $result['deductions'][$i]['pm'] }}
                    </td>
                @endif

                {{-- Spacer between columns --}}
                <td></td>

                {{-- EMPLOYER CONTRIBUTIONS --}}
                @if ($result['employer_contributions'][$i]['head'])
                    <td
                        style="background: #d8d8d8; border: 1px solid black; font-weight: bold; border-right: 0px solid #d8d8d8; font-size: 9px;">
                        {{ $result['employer_contributions'][$i]['label'] }}
                    </td>
                    <td style="background: #d8d8d8; border-bottom: 1px solid black; border-top: 1px solid black;">
                    </td>
                    <td
                        style="background: #d8d8d8; text-align: right; border: 1px solid black; border-left: 0px solid #d8d8d8; font-weight: bold; font-size: 9px;">
                        {{ $result['employer_contributions'][$i]['amount'] }}
                    </td>
                @else
                    <td
                        style="border-left: 1px solid black; font-weight: bold; font-size: 9px; {{ $result['employer_contributions'][$i]['total'] == true ? 'border: 1px solid black; border-right: 1px solid white;' : '' }} ">
                        {{ $result['employer_contributions'][$i]['label'] }}
                    </td>
                    <td
                        style="{{ $result['employer_contributions'][$i]['total'] == true ? 'border-top: 1px solid black; border-bottom: 1px solid black;' : '' }}">
                    </td>
                    <td
                        style="text-align: right; border-right: 1px solid black; font-size: 9px; {{ $result['employer_contributions'][$i]['total'] == true ? 'border: 1px solid black; border-left: 1px solid white;' : '' }}">
                        {{ $result['employer_contributions'][$i]['amount'] != '' ? number_format((float) $result['employer_contributions'][$i]['amount']) : $result['employer_contributions'][$i]['amount'] }}
                    </td>
                @endif

                {{-- Final border --}}
                <td style="border-right: 1px solid black;"></td>
            </tr>
        @endfor

        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="border: 1px solid black; font-size: 9px; font-weight: bold;">Total Rs.</td>
            <td style="border: 1px solid black; font-size: 9px; text-align: right; font-weight: bold;">
                {{ number_format((float) $NetGrossRs + $StopSalary + $GrossRs) }}
            </td>
            <td style="border: 1px solid black; font-size: 9px; text-align: right; font-weight: bold;">
                {{ number_format((float) $enticementsYTDTotal) }}
            </td>
            <td></td>
            <td style="border: 1px solid black; font-size: 9px; font-weight: bold;">Total Rs.</td>
            <td style="border: 1px solid black; font-size: 9px; text-align: right; font-weight: bold;">
                {{ number_format((float) $deductionsTotalPM) }}
            </td>
            <td style="border: 1px solid black; font-size: 9px; text-align: right; font-weight: bold;">
                {{ number_format((float) $deductionsTotalYTD) }}
            </td>
            <td></td>
            <td style="border: 1px solid black; border-right: 0px solid white; font-size: 9px; font-weight: bold;">
                Cost to Company </td>
            <td style="border-bottom: 1px solid black; border-top: 1px solid black;"></td>
            <td
                style=" border: 1px solid black; border-left: 0px solid white; font-size: 9px; text-align: right; font-weight: bold;">
                {{ number_format((float) $CostToCompanyContributions + $GrossRs) }}
            </td>
            <td style="border-right: 1px solid black;"></td>
        </tr>

        <tr>
            <td></td>
            <td colspan="13" style="border-left: 1px solid black; border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td style="border-left: 1px solid black;"></td>
            <td style="font-weight: bold; text-align: right; font-size: 9px;" colspan="6">Total Amount
                Disbursed Rs.</td>
            <td style="font-weight: bold; text-align: right; border: 4px solid black; font-size: 9px;">
                {{ number_format((float) ($NetGrossRs + $StopSalary + $GrossRs - $deductionsTotalPM)) }}
            </td>
            <td colspan="5" style="border-right: 1px solid black;"></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="13" style="border: 1px solid black; border-top: 0px solid white;"></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="12" style="color: #808080;">This is a system generated document and does not require a
                signature</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="13" style="border-bottom: 1px dotted black;"></td>
        </tr>
    </table>
@endforeach
