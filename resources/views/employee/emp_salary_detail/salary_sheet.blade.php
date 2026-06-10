<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Sheet</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        .report-header {
            width: 100%;
            display: table;
            margin-bottom: 12px;
        }

        .report-header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .report-logo {
            width: 20%;
            text-align: center;
        }

        .report-title {
            width: 60%;
            text-align: center;
        }

        .report-title-image {
            width: 330px;
            max-width: 330px;
            height: auto;
            display: inline-block;
        }
        .report-branch {
            margin: 4px 0;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .report-month {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .salary-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        .salary-table th,
        .salary-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .salary-table thead th {
            background: #bfbfbf;
            font-weight: 700;
        }

        .group-row th {
            background: #e6e6e6;
            text-align: left;
            font-size: 9px;
            font-weight: 800;
        }

        .department-row th {
            background: #f2f2f2;
            text-align: left;
            font-size: 8px;
            font-weight: 800;
        }

        .text-left {
            text-align: left !important;
        }

        .total-row td {
            background: #bfbfbf;
            font-weight: 800;
        }

        .branch-total-row td {
            background: #d9d9d9;
            font-weight: 800;
        }
    </style>
</head>

<body>
    @php
        $logoSrc = !empty($isPdf) ?  asset('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
        $schoolTitleSrc = !empty($isPdf) ? public_path('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
        $selectedBranch = !empty($requestdata['branches'] ?? null) && ($requestdata['branches'] ?? '') !== 'all'
            ? optional(\App\Models\User::find($requestdata['branches']))->name
            : 'All Branches';
        $columnCount = 8 + count($salaryHeads) + 5 + 9 + 1 + 4 + 3 + 3 + 1;
        $headTotals = [];
        foreach ($salaryHeads as $head) {
            $headTotals[$head->id] = 0;
        }

        $totals = [
            'basics' => 0,
            'other_add' => 0,
            'other' => 0,
            'other_misc' => 0,
            'stop_sal' => 0,
            'gross' => 0,
            'emp_sec' => 0,
            'it' => 0,
            'sal_advance' => 0,
            'eobi' => 0,
            'emp_sec_loan' => 0,
            'stop_deduction' => 0,
            'pessi' => 0,
            'dedu' => 0,
            'loan' => 0,
            'net_pay' => 0,
            'pessi_employer' => 0,
            'eobi_employer' => 0,
            'total_cost' => 0,
            'cost_to_comp' => 0,
        ];
        $totalKeys = array_keys($totals);

        $sortedDatas = $datas
            ->sortBy(function ($data) {
                return strtolower(
                    (optional(optional($data->employee)->user)->name ?? '') . '|' .
                    (optional($data->salarydepartment)->name ?? optional(optional($data->employee)->department)->name ?? '') . '|' .
                    (optional($data->employee)->name ?? '')
                );
            })
            ->values();

        $groupedByBranch = $sortedDatas->groupBy(function ($data) {
            return optional(optional($data->employee)->user)->name ?: 'No Branch';
        });

        $globalSr = 1;
    @endphp

    <div class="report-header">
        <div class="report-header-cell report-logo">
            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="The Lynx School Logo" title="The Lynx School Logo">
        </div>
        <div class="report-header-cell report-title">
            <img src="{{ asset('assets/images/lynxheadertext.jpg') }}" class="report-title-image" alt="The Lynx School" title="The Lynx School">
            <div class="report-branch">{{ $selectedBranch }}</div>
            <div class="report-month">Salary Sheet Report for {{ \Carbon\Carbon::parse($requestdata['date'])->format('F Y') }}</div>
        </div>
        <div class="report-header-cell report-logo"></div>
    </div>

    <table class="salary-table">
        <thead>
            <tr>
                <th colspan="7">EMPLOYEES DETAIL</th>
                <th colspan="{{ count($salaryHeads) + 6 }}">ALLOWANCES</th>
                <th colspan="9">DEDUCTION</th>
                <th></th>
                <th colspan="4">Cost to School</th>
                <th colspan="3">CL</th>
                <th colspan="3">AL</th>
                <th></th>
            </tr>
            <tr>
                <th>Sr#</th>
                <th>Dept Sr#</th>
                <th>Emp No</th>
                <th>Scale</th>
                <th>Name</th>
                <th>Designation</th>
                <th>DOJ</th>
                <th>Basic</th>
                @foreach ($salaryHeads as $head)
                    <th>{{ $head->head }}</th>
                @endforeach
                <th>Other Allowance</th>
                <th>Other</th>
                <th>Drns & Misc</th>
                <th>Stop Salary</th>
                <th>Gross</th>
                <th>ES</th>
                <th>IT</th>
                <th>Salary Adv.</th>
                <th>EOBI</th>
                <th>Loan Sec</th>
                <th>Stop</th>
                <th>PESSI</th>
                <th>Other Deduction</th>
                <th>Loan</th>
                <th>Net</th>
                <th>PESSI Comp</th>
                <th>EOBI Comp</th>
                <th>Total Cost</th>
                <th>Cost To Comp</th>
                <th>OP</th>
                <th>Lvs</th>
                <th>Bal</th>
                <th>OP</th>
                <th>Lvs</th>
                <th>Bal</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedByBranch as $branchName => $branchRows)
                @php
                    $branchTotals = array_fill_keys($totalKeys, 0);
                    $branchHeadTotals = [];
                    foreach ($salaryHeads as $head) {
                        $branchHeadTotals[$head->id] = 0;
                    }
                @endphp
                <tr class="group-row">
                    <th colspan="{{ $columnCount }}">{{ $branchName }}</th>
                </tr>
                @foreach ($branchRows->groupBy('department_id') as $departmentRows)
                    @php
                        $departmentName = optional($departmentRows->first()->salarydepartment)->name
                            ?: optional(optional($departmentRows->first()->employee)->department)->name
                            ?: 'No Department Name';
                        $deptSr = 1;
                    @endphp
                    <tr class="department-row">
                        <th colspan="{{ $columnCount }}">{{ $departmentName }}</th>
                    </tr>
                    @foreach ($departmentRows->sortBy(fn ($data) => strtolower(optional($data->employee)->name ?? '')) as $data)
                        @php
                            $employee = $data->employee;
                            $leaves = optional(optional($employee)->employee_monthly_salaries_attend)->first();
                            $casualLeaves = ($leaves->total_casual ?? 0) - ($leaves->bal_casual ?? 0);
                            $annualLeaves = ($leaves->total_annual ?? 0) - ($leaves->bal_annual ?? 0);
                            $otherMisc = ($data->drns ?? 0) + ($data->misc ?? 0);
                            $totalCost = ($data->pessi_employer ?? 0) + ($data->eobi_employer ?? 0);
                            $costToComp = $totalCost + ($data->gross ?? 0);

                            $totals['basics'] += $data->basics ?? 0;
                            $totals['other_add'] += $data->other_add ?? 0;
                            $totals['other'] += $data->conv ?? 0;
                            $totals['other_misc'] += $otherMisc;
                            $totals['stop_sal'] += $data->stop_sal ?? 0;
                            $totals['gross'] += $data->gross ?? 0;
                            $totals['emp_sec'] += $data->emp_sec ?? 0;
                            $totals['it'] += $data->it ?? 0;
                            $totals['sal_advance'] += $data->sal_advance ?? 0;
                            $totals['eobi'] += $data->eobi ?? 0;
                            $totals['emp_sec_loan'] += $data->emp_sec_loan ?? 0;
                            $totals['stop_deduction'] += $data->stop_sal ?? 0;
                            $totals['pessi'] += $data->pessi ?? 0;
                            $totals['dedu'] += $data->dedu ?? 0;
                            $totals['loan'] += $data->loan ?? 0;
                            $totals['net_pay'] += $data->net_pay ?? 0;
                            $totals['pessi_employer'] += $data->pessi_employer ?? 0;
                            $totals['eobi_employer'] += $data->eobi_employer ?? 0;
                            $totals['total_cost'] += $totalCost;
                            $totals['cost_to_comp'] += $costToComp;

                            $branchTotals['basics'] += $data->basics ?? 0;
                            $branchTotals['other_add'] += $data->other_add ?? 0;
                            $branchTotals['other'] += $data->conv ?? 0;
                            $branchTotals['other_misc'] += $otherMisc;
                            $branchTotals['stop_sal'] += $data->stop_sal ?? 0;
                            $branchTotals['gross'] += $data->gross ?? 0;
                            $branchTotals['emp_sec'] += $data->emp_sec ?? 0;
                            $branchTotals['it'] += $data->it ?? 0;
                            $branchTotals['sal_advance'] += $data->sal_advance ?? 0;
                            $branchTotals['eobi'] += $data->eobi ?? 0;
                            $branchTotals['emp_sec_loan'] += $data->emp_sec_loan ?? 0;
                            $branchTotals['stop_deduction'] += $data->stop_sal ?? 0;
                            $branchTotals['pessi'] += $data->pessi ?? 0;
                            $branchTotals['dedu'] += $data->dedu ?? 0;
                            $branchTotals['loan'] += $data->loan ?? 0;
                            $branchTotals['net_pay'] += $data->net_pay ?? 0;
                            $branchTotals['pessi_employer'] += $data->pessi_employer ?? 0;
                            $branchTotals['eobi_employer'] += $data->eobi_employer ?? 0;
                            $branchTotals['total_cost'] += $totalCost;
                            $branchTotals['cost_to_comp'] += $costToComp;
                        @endphp
                        <tr>
                            <td>{{ $globalSr++ }}</td>
                            <td>{{ $deptSr++ }}</td>
                            <td>{{ optional($employee)->employee_id }}</td>
                            <td>{{ $data->scale_no }}</td>
                            <td class="text-left">{{ optional($employee)->name }}</td>
                            <td class="text-left">{{ optional(optional($employee)->designation)->name }}</td>
                            <td>{{ optional($employee)->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') : '' }}</td>
                            <td>{{ $data->basics ?? 0 }}</td>
                            @foreach ($salaryHeads as $head)
                                @php
                                    $headValue = optional($data->salary_heads->firstWhere('head_id', $head->id))->head_value ?? 0;
                                    $headTotals[$head->id] += $headValue;
                                    $branchHeadTotals[$head->id] += $headValue;
                                @endphp
                                <td>{{ $headValue }}</td>
                            @endforeach
                            <td>{{ $data->other_add ?? 0 }}</td>
                            <td>{{ $data->conv ?? 0 }}</td>
                            <td>{{ $otherMisc }}</td>
                            <td>{{ $data->stop_sal ?? 0 }}</td>
                            <td>{{ $data->gross ?? 0 }}</td>
                            <td>{{ $data->emp_sec ?? 0 }}</td>
                            <td>{{ $data->it ?? 0 }}</td>
                            <td>{{ $data->sal_advance ?? 0 }}</td>
                            <td>{{ $data->eobi ?? 0 }}</td>
                            <td>{{ $data->emp_sec_loan ?? 0 }}</td>
                            <td>{{ $data->stop_sal ?? 0 }}</td>
                            <td>{{ $data->pessi ?? 0 }}</td>
                            <td>{{ $data->dedu ?? 0 }}</td>
                            <td>{{ $data->loan ?? 0 }}</td>
                            <td>{{ $data->net_pay ?? 0 }}</td>
                            <td>{{ $data->pessi_employer ?? 0 }}</td>
                            <td>{{ $data->eobi_employer ?? 0 }}</td>
                            <td>{{ $totalCost }}</td>
                            <td>{{ $costToComp }}</td>
                            <td>{{ $leaves->total_casual ?? 0 }}</td>
                            <td>{{ $casualLeaves }}</td>
                            <td>{{ $leaves->bal_casual ?? 0 }}</td>
                            <td>{{ $leaves->total_annual ?? 0 }}</td>
                            <td>{{ $annualLeaves }}</td>
                            <td>{{ $leaves->bal_annual ?? 0 }}</td>
                            <td>{{ $data->sal_days ?? 0 }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="branch-total-row">
                    <td colspan="7">{{ $branchName }} TOTAL</td>
                    <td>{{ $branchTotals['basics'] }}</td>
                    @foreach ($salaryHeads as $head)
                        <td>{{ $branchHeadTotals[$head->id] }}</td>
                    @endforeach
                    <td>{{ $branchTotals['other_add'] }}</td>
                    <td>{{ $branchTotals['other'] }}</td>
                    <td>{{ $branchTotals['other_misc'] }}</td>
                    <td>{{ $branchTotals['stop_sal'] }}</td>
                    <td>{{ $branchTotals['gross'] }}</td>
                    <td>{{ $branchTotals['emp_sec'] }}</td>
                    <td>{{ $branchTotals['it'] }}</td>
                    <td>{{ $branchTotals['sal_advance'] }}</td>
                    <td>{{ $branchTotals['eobi'] }}</td>
                    <td>{{ $branchTotals['emp_sec_loan'] }}</td>
                    <td>{{ $branchTotals['stop_deduction'] }}</td>
                    <td>{{ $branchTotals['pessi'] }}</td>
                    <td>{{ $branchTotals['dedu'] }}</td>
                    <td>{{ $branchTotals['loan'] }}</td>
                    <td>{{ $branchTotals['net_pay'] }}</td>
                    <td>{{ $branchTotals['pessi_employer'] }}</td>
                    <td>{{ $branchTotals['eobi_employer'] }}</td>
                    <td>{{ $branchTotals['total_cost'] }}</td>
                    <td>{{ $branchTotals['cost_to_comp'] }}</td>
                    <td colspan="7"></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7">GRAND TOTAL</td>
                <td>{{ $totals['basics'] }}</td>
                @foreach ($salaryHeads as $head)
                    <td>{{ $headTotals[$head->id] }}</td>
                @endforeach
                <td>{{ $totals['other_add'] }}</td>
                <td>{{ $totals['other'] }}</td>
                <td>{{ $totals['other_misc'] }}</td>
                <td>{{ $totals['stop_sal'] }}</td>
                <td>{{ $totals['gross'] }}</td>
                <td>{{ $totals['emp_sec'] }}</td>
                <td>{{ $totals['it'] }}</td>
                <td>{{ $totals['sal_advance'] }}</td>
                <td>{{ $totals['eobi'] }}</td>
                <td>{{ $totals['emp_sec_loan'] }}</td>
                <td>{{ $totals['stop_deduction'] }}</td>
                <td>{{ $totals['pessi'] }}</td>
                <td>{{ $totals['dedu'] }}</td>
                <td>{{ $totals['loan'] }}</td>
                <td>{{ $totals['net_pay'] }}</td>
                <td>{{ $totals['pessi_employer'] }}</td>
                <td>{{ $totals['eobi_employer'] }}</td>
                <td>{{ $totals['total_cost'] }}</td>
                <td>{{ $totals['cost_to_comp'] }}</td>
                <td colspan="7"></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
