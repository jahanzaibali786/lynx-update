<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Sheet</title>
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

        .advance-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        .advance-table th,
        .advance-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .advance-table thead th {
            background: #bfbfbf;
            font-weight: 700;
        }

        .group-row th {
            background: #e6e6e6;
            text-align: left;
            font-size: 9px;
            font-weight: 800;
        }

        .total-row td,
        .branch-total-row td {
            background: #bfbfbf;
            font-weight: 800;
        }

        .branch-total-row td {
            background: #d9d9d9;
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
        $logoSrc = !empty($isPdf) ? asset('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
        $schoolTitleSrc = !empty($isPdf) ? asset('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
        $selectedBranch = !empty($requestdata['branches'] ?? null) && ($requestdata['branches'] ?? '') !== 'all'
            ? optional(\App\Models\User::find($requestdata['branches']))->name
            : 'All Branches';
        $reportDate = !empty($requestdata['date'] ?? null)
            ? \Carbon\Carbon::parse($requestdata['date'])->format('F Y')
            : now()->format('F Y');
        $columnCount = 10;
        $totals = ['advance' => 0, 'eobi' => 0, 'it' => 0, 'loan' => 0];
        $groupedByBranch = $datas
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
            <img src="{{ $schoolTitleSrc }}" class="report-title-image" alt="The Lynx School">
            <div class="report-branch">{{ $selectedBranch }}</div>
            <div class="report-month">Advance Sheet Report for {{ $reportDate }}</div>
        </div>
        <div class="report-header-cell report-logo"></div>
    </div>

    <table class="advance-table">
        <thead>
            <tr>
                <th colspan="6">EMPLOYEES DETAIL</th>
                <th colspan="4">ADVANCE / DEDUCTIONS</th>
            </tr>
            <tr>
                <th>Sr#</th>
                <th>Branch Sr</th>
                <th>Emp No</th>
                <th>Name</th>
                <th>Designation</th>
                <th>DOJ</th>
                <th>Adv</th>
                <th>EOBI</th>
                <th>I.Tax</th>
                <th>Loan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedByBranch as $branchName => $branchRows)
                @php
                    $branchTotals = ['advance' => 0, 'eobi' => 0, 'it' => 0, 'loan' => 0];
                    $branchCount = $branchRows->count();
                    $branchSr = 1;
                @endphp
                <tr class="group-row">
                    <th colspan="{{ $columnCount }}">{{ $branchName }}</th>
                </tr>
                @foreach ($branchRows->sortBy(fn ($data) => strtolower(optional($data->employee)->name ?? '')) as $data)
                    @php
                        $employee = $data->employee;
                        $rowTotals = [
                            'advance' => $data->sal_advance ?? 0,
                            'eobi' => $data->eobi ?? 0,
                            'it' => $data->it ?? 0,
                            'loan' => $data->loan ?? 0,
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
                        <td class="text-left">{{ optional($employee)->name }}</td>
                        <td class="text-left">{{ optional(optional($employee)->designation)->name }}</td>
                        <td>{{ optional($employee)->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') : '' }}</td>
                        <td class="num">{{ $fmt($data->sal_advance ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->eobi ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->it ?? 0) }}</td>
                        <td class="num">{{ $fmt($data->loan ?? 0) }}</td>
                    </tr>
                @endforeach
                <tr class="branch-total-row">
                    <td colspan="6">{{ $branchName }} TOTAL (Count: {{ $branchCount }})</td>
                    <td class="num">{{ $fmt($branchTotals['advance']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['eobi']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['it']) }}</td>
                    <td class="num">{{ $fmt($branchTotals['loan']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6">GRAND TOTAL (Count: {{ $datas->count() }})</td>
                <td class="num">{{ $fmt($totals['advance']) }}</td>
                <td class="num">{{ $fmt($totals['eobi']) }}</td>
                <td class="num">{{ $fmt($totals['it']) }}</td>
                <td class="num">{{ $fmt($totals['loan']) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
