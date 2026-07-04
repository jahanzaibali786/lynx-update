<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deduction Sheet</title>
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

        .report-school {
            margin: 0;
            font-family: "Edwardian Script ITC", Georgia, serif;
            font-size: 42px;
            line-height: 1;
            font-weight: 700;
        }

        .report-branch {
            margin: 4px 0;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .report-title-image {
            width: 330px;
            max-width: 330px;
            height: auto;
            display: inline-block;
        }

        .report-month {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .deduction-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        .deduction-table th,
        .deduction-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .deduction-table thead th {
            background: #bfbfbf;
            font-weight: 700;
        }

        .branch-row th {
            background: #d9d9d9;
            text-align: left;
            font-size: 9px;
            font-weight: 800;
        }

        .total-row td {
            background: #bfbfbf;
            font-weight: 800;
        }

        .text-left {
            text-align: left !important;
        }

        .num {
            text-align: right !important;
        }
    </style>
</head>

<body>
    @php
        $fmt = fn ($value) => number_format((float) ($value ?? 0), 0);
        $logoSrc = !empty($isPdf) ?  asset('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
        $schoolTitleSrc = !empty($isPdf) ? asset('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
        $selectedBranch = !empty($requestdata['branches'] ?? null) && ($requestdata['branches'] ?? '') !== 'all'
            ? optional(\App\Models\User::find($requestdata['branches']))->name
            : 'All Branches';
        $reportDate = !empty($requestdata['date'] ?? null)
            ? \Carbon\Carbon::parse($requestdata['date'])->format('F Y')
            : now()->format('F Y');
        $columnCount = 18;
        $totals = [
            'gross' => 0,
            'emp_sec' => 0,
            'it' => 0,
            'sal_advance' => 0,
            'eobi' => 0,
            'pessi' => 0,
            'emp_sec_loan' => 0,
            'loan' => 0,
            'dedu' => 0,
            'total_deduction' => 0,
            'net_pay' => 0,
        ];
        $grouped = $datas
            ->sortBy(function ($data) {
                return strtolower(
                    (optional(optional($data->employee)->user)->name ?? '') . '|' .
                    (optional($data->employee)->name ?? '')
                );
            })
            ->groupBy(function ($data) {
                return optional(optional($data->employee)->user)->name ?: 'No Branch';
            });
        $globalSr = 1;
    @endphp

    <div class="report-header">
        <div class="report-header-cell report-logo">
            <img src="{{ $logoSrc }}" style="max-width: 90px; max-height: 90px;" alt="logo">
        </div>
        <div class="report-header-cell report-title">
            <img src="{{ $schoolTitleSrc }}" class="report-title-image" alt="The Lynx School" title="The Lynx School">
            <div class="report-branch">{{ $selectedBranch }}</div>
            <div class="report-month">Deduction Sheet Report for {{ $reportDate }}</div>
        </div>
        <div class="report-header-cell report-logo"></div>
    </div>

    <table class="deduction-table">
        <thead>
            <tr>
                <th colspan="7">EMPLOYEES DETAIL</th>
                <th colspan="9">DEDUCTION</th>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th>Sr#</th>
                <th>Branch Sr</th>
                <th>Emp No</th>
                <th>Scale</th>
                <th>Name</th>
                <th>Designation</th>
                <th>DOJ</th>
                <th>Gross</th>
                <th>ES</th>
                <th>I.Tax</th>
                <th>Salary Adv.</th>
                <th>EOBI</th>
                <th>PESSI</th>
                <th>Loan Sec</th>
                <th>Loan</th>
                <th>Other Deduction</th>
                <th>Total Ded</th>
                <th>NET</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped as $branchName => $branchRows)
                @php
                    $branchSr = 1;
                    $branchCount = $branchRows->count();
                    $branchTotals = array_fill_keys(array_keys($totals), 0);
                @endphp
                <tr class="branch-row">
                    <th colspan="{{ $columnCount }}">{{ $branchName }}</th>
                </tr>
                @foreach ($branchRows->sortBy(fn ($data) => strtolower(optional($data->employee)->name ?? '')) as $data)
                    @php
                        $employee = $data->employee;
                        $totalDeduction = ($data->emp_sec ?? 0)
                            + ($data->it ?? 0)
                            + ($data->sal_advance ?? 0)
                            + ($data->eobi ?? 0)
                            + ($data->pessi ?? 0)
                            + ($data->emp_sec_loan ?? 0)
                            + ($data->loan ?? 0)
                            + ($data->dedu ?? 0);
                        $displayGross = ($data->gross ?? 0) + ($data->stop_sal ?? 0);
                        $rowTotals = [
                            'gross' => $displayGross,
                            'emp_sec' => $data->emp_sec ?? 0,
                            'it' => $data->it ?? 0,
                            'sal_advance' => $data->sal_advance ?? 0,
                            'eobi' => $data->eobi ?? 0,
                            'pessi' => $data->pessi ?? 0,
                            'emp_sec_loan' => $data->emp_sec_loan ?? 0,
                            'loan' => $data->loan ?? 0,
                            'dedu' => $data->dedu ?? 0,
                            'total_deduction' => $totalDeduction,
                            'net_pay' => $data->net_pay ?? 0,
                        ];
                        foreach ($rowTotals as $key => $value) {
                            $branchTotals[$key] += $value;
                            $totals[$key] += $value;
                        }
                    @endphp
                    <tr>
                        <td>{{ $globalSr++ }}</td>
                        <td>{{ $branchSr++ }}</td>
                        <td>{{ optional($employee)->employee_id }}</td>
                        <td>{{ $data->scale_no }}</td>
                        <td class="text-left">{{ optional($employee)->name }}</td>
                        <td class="text-left">{{ optional(optional($employee)->designation)->name }}</td>
                        <td>{{ optional($employee)->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') : '' }}</td>
                        <td class="num">{{ $fmt($displayGross) }}</td>
                        <td class="num">{{ $fmt($data->emp_sec ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->it ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->sal_advance ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->eobi ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->pessi ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->emp_sec_loan ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->loan ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->dedu ?? 0) }}</td>
                        <td class="num">{{ $fmt($totalDeduction) }}</td>
                        <td class="num">{{ $fmt($data->net_pay ?? 0) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="7">{{ $branchName }} TOTAL (Count: {{ $branchCount }})</td>
                    <td class="num">{{ $fmt($branchTotals['gross']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['emp_sec']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['it']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['sal_advance']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['eobi']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['pessi']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['emp_sec_loan']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['loan']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['dedu']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['total_deduction']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['net_pay']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7">GRAND TOTAL (Count: {{ $datas->count() }})</td>
                <td class="num">{{ $fmt($totals['gross']) }}</td>
                <td class="num">{{ $fmt($totals['emp_sec']) }}</td>
                <td class="num">{{ $fmt($totals['it']) }}</td>
                <td class="num">{{ $fmt($totals['sal_advance']) }}</td>
                <td class="num">{{ $fmt($totals['eobi']) }}</td>
                <td class="num">{{ $fmt($totals['pessi']) }}</td>
                <td class="num">{{ $fmt($totals['emp_sec_loan']) }}</td>
                <td class="num">{{ $fmt($totals['loan']) }}</td>
                <td class="num">{{ $fmt($totals['dedu']) }}</td>
                <td class="num">{{ $fmt($totals['total_deduction']) }}</td>
                <td class="num">{{ $fmt($totals['net_pay']) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
