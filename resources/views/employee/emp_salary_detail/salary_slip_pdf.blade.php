@php
    $fmt = fn ($value) => $value === '' || $value === null ? '' : number_format((float) $value);
    $money = fn ($value) => number_format((float) ($value ?? 0));
    $monthLabel = !empty($requestdata['date'] ?? null)
        ? \Carbon\Carbon::parse($requestdata['date'])->format('F Y')
        : '';
    $branchName = $salarySlipBranchName ?? 'All Branches';
    $logoSrc = asset('assets/images/lynxlogo(2).png');
    if (!file_exists($logoSrc)) {
        $logoSrc = asset('assets/images/lynx2.jpg');
    }
    $salarySlipYtdTotals = $salarySlipYtdTotals ?? [];
    $salarySlipHeadYtdTotals = $salarySlipHeadYtdTotals ?? [];
    $salaryHeadNames = collect($salaryHeads ?? [])->pluck('head', 'id');
@endphp

<style>
    @page {
        size: A4 portrait;
        margin: 8px 8px 14px 8px;
    }

    @media print {
        body {
            width: 100%;
        }
    }

    body {
        margin: 0;
        color: #000;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 8.5px;
    }

    .slip {
        height: 350pt;
        box-sizing: border-box;
        padding: 0 18px 5px;
        overflow: hidden;
    }

    .slip-page {
        page-break-after: always;
    }

    .slip-page:last-child {
        page-break-after: auto;
    }

    .slip-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .slip-table td,
    .slip-table th {
        padding: 1px 3px;
        vertical-align: middle;
        line-height: 1.3;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .slip-table td:empty {
        padding-left: 0;
        padding-right: 0;
    }

    .school-title {
        font-family: "Edwardian Script ITC", "Times New Roman", serif;
        font-size: 30px;
        font-weight: bold;
    }

    .branch-title {
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        padding-top: 5px !important;
        padding-bottom: 2px !important;
        line-height: 1.35;
    }

    .report-title {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        padding-bottom: 5px !important;
        line-height: 1.35;
    }

    .logo {
        width: 58px;
        height: 58px;
    }

    .border {
        border: 1px solid #000;
    }

    .left-border {
        border-left: 1px solid #000;
    }

    .right-border {
        border-right: 1px solid #000;
    }

    .top-border {
        border-top: 1px solid #000;
    }

    .section-head {
        background: #d8d8d8;
        border: 1px solid #000;
        font-weight: bold;
        text-align: center;
    }

    .head-row {
        background: #d8d8d8;
        border: 1px solid #000;
        font-weight: bold;
    }

    .label {
        font-weight: bold;
    }

    .nowrap {
        white-space: nowrap;
    }

    .num {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .muted {
        color: #666;
    }

    .total-cell {
        border: 1px solid #000;
        font-weight: bold;
    }

    .disbursed {
        border: 2px solid #000;
        font-weight: bold;
        text-align: right;
    }

    .dotted-separator {
        border-bottom: 1px dotted #000;
        height: 8px;
        padding: 0 !important;
    }
    .report-title-image {
            width: 280px;
            max-width: 280px;
            height: auto;
            display: inline-block;
        }
</style>

@foreach ($datas->values()->chunk(2) as $slipPage)
    <div class="slip-page">
@foreach ($slipPage as $data)
    @php
        $att = optional(optional($data->employee)->employee_monthly_salaries_attend)->first();
        $clOpen = (int) ($att->total_casual ?? 0);
        $clBal = (int) ($att->bal_casual ?? 0);
        $clTaken = max(0, $clOpen - $clBal);
        $alOpen = (int) ($att->total_annual ?? 0);
        $alBal = (int) ($att->bal_annual ?? 0);
        $alTaken = max(0, $alOpen - $alBal);
        $workingDays = (int) ($att->working_days ?? ($data->sal_days ?? 0));
        $scale = optional($data->employee->employee_payscale_details->first());
        $salDate = \Carbon\Carbon::parse($data->salary_date);
        $empId = $data->employee_id;
        $employeeYtd = $salarySlipYtdTotals[$empId] ?? [];
        $employeeHeadYtd = $salarySlipHeadYtdTotals[$empId] ?? [];

        $grossRows = [];
        foreach (($data->salary_heads ?? []) as $salaryHead) {
            $name = $salaryHeadNames[$salaryHead->head_id] ?? 'Head ' . $salaryHead->head_id;
            $grossRows[] = [
                'label' => $name,
                'pm' => (float) $salaryHead->head_value,
                'ytd' => (float) ($employeeHeadYtd[$salaryHead->head_id] ?? 0),
            ];
        }

        $grossPm = (float) ($data->gross ?? 0);
        $grossYtd = (float) ($employeeYtd['gross'] ?? 0);
        $otherPm = (float) ($data->conv ?? 0);
        $miscPm = (float) ($data->misc ?? 0);
        $otherAllowancePm = (float) ($data->other ?? 0);
        $otherYtd = (float) ($employeeYtd['conv'] ?? 0);
        $miscYtd = (float) ($employeeYtd['misc'] ?? 0);
        $otherAllowanceYtd = (float) ($employeeYtd['other'] ?? 0);
        $enticementPm = $otherPm + $miscPm + $otherAllowancePm;
        $enticementYtd = $otherYtd + $miscYtd + $otherAllowanceYtd;
        $stopSalary = (float) ($data->stop_sal ?? 0);

        $earnings = [
            ['label' => 'Gross Salary', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false],
            ...array_map(fn ($row) => $row + ['head' => false, 'total' => false], $grossRows),
            ['label' => 'Gross Rs.', 'pm' => $grossPm, 'ytd' => $grossYtd, 'head' => false, 'total' => true],
            ['label' => 'Enticements', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false],
            ['label' => 'Other', 'pm' => $otherPm, 'ytd' => $otherYtd, 'head' => false, 'total' => false],
            ['label' => 'Drns, Misc', 'pm' => $miscPm, 'ytd' => $miscYtd, 'head' => false, 'total' => false],
            ['label' => 'Other Allowance', 'pm' => $otherAllowancePm, 'ytd' => $otherAllowanceYtd, 'head' => false, 'total' => false],
            ['label' => 'Net Gross Rs.', 'pm' => $grossPm + $enticementPm, 'ytd' => '', 'head' => true, 'total' => false],
            ['label' => 'Stop Salary (' . $salDate->format('M, y') . ')', 'pm' => $stopSalary, 'ytd' => '', 'head' => false, 'total' => false],
        ];

        $ytd = fn ($field) => (float) ($employeeYtd[$field] ?? 0);
        $deductions = [
            ['label' => 'Heads', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false],
            ['label' => 'Employee Security', 'pm' => (float) ($data->emp_sec ?? 0), 'ytd' => $ytd('emp_sec'), 'head' => false, 'total' => false],
            ['label' => 'E.O.B.I', 'pm' => (float) ($data->eobi ?? 0), 'ytd' => $ytd('eobi'), 'head' => false, 'total' => false],
            ['label' => 'P.E.S.S.I', 'pm' => (float) ($data->pessi ?? 0), 'ytd' => $ytd('pessi'), 'head' => false, 'total' => false],
            ['label' => 'Income Tax', 'pm' => (float) ($data->it ?? 0), 'ytd' => $ytd('it'), 'head' => false, 'total' => false],
            ['label' => 'Other Deduction', 'pm' => (float) ($data->dedu ?? 0), 'ytd' => $ytd('dedu'), 'head' => false, 'total' => false],
            ['label' => 'Advance', 'pm' => (float) ($data->sal_advance ?? 0), 'ytd' => $ytd('sal_advance'), 'head' => false, 'total' => false],
            ['label' => 'Stop Salary', 'pm' => 0, 'ytd' => '-', 'head' => false, 'total' => false],
            ['label' => 'Training Course', 'pm' => (float) ($data->tra_course ?? 0), 'ytd' => $ytd('tra_course'), 'head' => false, 'total' => false],
            ['label' => 'Loan Emp Security', 'pm' => (float) ($data->loan ?? 0), 'ytd' => $ytd('loan'), 'head' => false, 'total' => false],
        ];
        $deductionPm = array_sum(array_map(fn ($row) => is_numeric($row['pm']) ? (float) $row['pm'] : 0, $deductions));
        $deductionYtd = array_sum(array_map(fn ($row) => is_numeric($row['ytd']) ? (float) $row['ytd'] : 0, $deductions));
        $empSecYtd = $ytd('emp_sec');
        $contributions = [
            ['label' => 'Employee Security Balance Y.T.D', 'amount' => '', 'head' => true, 'total' => false],
            ['label' => 'Employee Security', 'amount' => $empSecYtd, 'head' => false, 'total' => false],
            ['label' => 'Net Balance Rs.', 'amount' => $empSecYtd, 'head' => false, 'total' => true],
            ['label' => 'Employer Contribution P.M', 'amount' => '', 'head' => true, 'total' => false],
            ['label' => 'Eobi Contribution', 'amount' => (float) ($data->eobi_employer ?? 0), 'head' => false, 'total' => false],
            ['label' => 'Pessi Contribution', 'amount' => (float) ($data->pessi_employer ?? 0), 'head' => false, 'total' => false],
            ['label' => 'Child Concession', 'amount' => (float) ($data->chaild_con ?? 0), 'head' => false, 'total' => false],
        ];
        $ctc = $grossPm + (float) ($data->eobi_employer ?? 0) + (float) ($data->pessi_employer ?? 0) + (float) ($data->chaild_con ?? 0);
        $rowCount = max(count($earnings), count($deductions), count($contributions));
        while (count($earnings) < $rowCount) $earnings[] = ['label' => '', 'pm' => '', 'ytd' => '', 'head' => false, 'total' => false];
        while (count($deductions) < $rowCount) $deductions[] = ['label' => '', 'pm' => '', 'ytd' => '', 'head' => false, 'total' => false];
        while (count($contributions) < $rowCount) $contributions[] = ['label' => '', 'amount' => '', 'head' => false, 'total' => false];
        $disbursed = $grossPm + $enticementPm + $stopSalary - $deductionPm;
    @endphp

    <div class="slip">
        <table class="slip-table">
            <colgroup>
                <col style="width: 17.5%;">
                <col style="width: 8.5%;">
                <col style="width: 8.5%;">
                <col style="width: 17.5%;">
                <col style="width: 8.5%;">
                <col style="width: 8.5%;">
                <col style="width: 22%;">
                <col style="width: 2%;">
                <col style="width: 7%;">
            </colgroup>
            <tr>
                <td colspan="7" class="school-title"> <img src="{{ asset('assets/images/lynxheadertext.jpg') }}" class="report-title-image" alt="The Lynx School" title="The Lynx School"></td>
                <td colspan="2" class="num">
                    <img src="{{ $logoSrc }}" class="logo" alt="logo">
                </td>
            </tr>
            <tr>
                <td colspan="9" class="branch-title">{{ $branchName }}</td>
            </tr>
            <tr>
                <td colspan="9" class="report-title">Employee Pay Slip Report for the month of {{ $monthLabel }}</td>
            </tr>
            <tr>
                <td class="label nowrap top-border left-border">Employee#</td><td class="top-border">{{ optional($data->employee)->employee_id ?? optional($data->employee)->id }}</td>
                <td class="top-border"></td><td class="label top-border">Leaves Balances</td><td class="label center top-border">C/L</td><td class="label center top-border">A/L</td>
                <td class="label top-border">Payment Date</td><td class="top-border"></td>
                <td class="top-border right-border">{{ $data->paid_date ? \Carbon\Carbon::parse($data->paid_date)->format('d-M-y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label nowrap left-border">Name</td><td colspan="2">{{ optional($data->employee)->name }}</td>
                <td class="label">O.Balance</td><td class="center">{{ $clOpen }}</td><td class="center">{{ $alOpen }}</td>
                <td class="label nowrap">Mode</td><td></td>
                <td class="right-border">{{ strtolower($scale->paymode ?? '') === 'cash' ? 'Cash' : 'Bank' }}</td>
            </tr>
            <tr>
                <td class="label left-border">Corporate Title</td><td colspan="2">{{ optional(optional($data->employee)->designation)->name }}</td>
                <td class="label">Leaves</td><td class="center">{{ $clTaken }}</td><td class="center">{{ $alTaken }}</td>
                <td class="label">Bank/Branch</td><td></td>
                <td class="right-border">{{ $scale->paymode ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label nowrap left-border">Department</td><td colspan="2">{{ optional(optional($data->employee)->department)->name ?? '-' }}</td>
                <td class="label">C.Balance</td><td class="center">{{ $clBal }}</td><td class="center">{{ $alBal }}</td>
                <td class="label">N.T.N</td><td></td>
                <td class="right-border">{{ $scale->account_number ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="3" class="left-border"></td>
                <td class="label">Working Days</td><td class="center">{{ $workingDays }}</td>
                <td colspan="4" class="right-border"></td>
            </tr>
            <tr>
                <td colspan="3" class="section-head">EARNINGS</td>
                <td colspan="3" class="section-head">DEDUCTIONS</td>
                <td colspan="3" class="section-head"></td>
            </tr>
            @for ($i = 0; $i < $rowCount; $i++)
                <tr>
                    @if ($earnings[$i]['head'])
                        <td class="head-row">{{ $earnings[$i]['label'] }}</td>
                        <td class="head-row num">{{ is_numeric($earnings[$i]['pm']) ? $fmt($earnings[$i]['pm']) : $earnings[$i]['pm'] }}</td>
                        <td class="head-row center">{{ is_numeric($earnings[$i]['ytd']) ? $fmt($earnings[$i]['ytd']) : $earnings[$i]['ytd'] }}</td>
                    @else
                        <td class="{{ $earnings[$i]['total'] ? 'total-cell' : 'left-border right-border label' }}">{{ $earnings[$i]['label'] }}</td>
                        <td class="{{ $earnings[$i]['total'] ? 'total-cell' : 'left-border right-border' }} num">{{ is_numeric($earnings[$i]['pm']) ? $fmt($earnings[$i]['pm']) : $earnings[$i]['pm'] }}</td>
                        <td class="{{ $earnings[$i]['total'] ? 'total-cell' : 'left-border right-border' }} num">{{ is_numeric($earnings[$i]['ytd']) ? $fmt($earnings[$i]['ytd']) : $earnings[$i]['ytd'] }}</td>
                    @endif
                    
                    @if ($deductions[$i]['head'])
                        <td class="head-row">{{ $deductions[$i]['label'] }}</td>
                        <td class="head-row center">P.M</td>
                        <td class="head-row center">Y.T.D</td>
                    @else
                        <td class="left-border right-border label">{{ $deductions[$i]['label'] }}</td>
                        <td class="left-border right-border num">{{ is_numeric($deductions[$i]['pm']) ? $fmt($deductions[$i]['pm']) : $deductions[$i]['pm'] }}</td>
                        <td class="left-border right-border num">{{ is_numeric($deductions[$i]['ytd']) ? $fmt($deductions[$i]['ytd']) : $deductions[$i]['ytd'] }}</td>
                    @endif
                    
                    @if ($contributions[$i]['head'])
                        <td class="head-row" colspan="2">{{ $contributions[$i]['label'] }}</td>
                        <td class="head-row num">{{ is_numeric($contributions[$i]['amount']) ? $fmt($contributions[$i]['amount']) : $contributions[$i]['amount'] }}</td>
                    @else
                        <td class="{{ $contributions[$i]['total'] ? 'total-cell' : 'left-border label' }}">{{ $contributions[$i]['label'] }}</td>
                        <td class="{{ $contributions[$i]['total'] ? 'total-cell' : '' }}"></td>
                        <td class="{{ $contributions[$i]['total'] ? 'total-cell' : 'right-border' }} num">{{ is_numeric($contributions[$i]['amount']) ? $fmt($contributions[$i]['amount']) : $contributions[$i]['amount'] }}</td>
                    @endif
                </tr>
            @endfor
            <tr>
                <td class="total-cell">Total Rs.</td><td class="total-cell num">{{ $money($grossPm + $enticementPm + $stopSalary) }}</td><td class="total-cell num">{{ $money($enticementYtd) }}</td>
                <td class="total-cell">Total Rs.</td><td class="total-cell num">{{ $money($deductionPm) }}</td><td class="total-cell num">{{ $money($deductionYtd) }}</td>
                <td class="total-cell" colspan="2">Cost to Company</td><td class="total-cell num">{{ $money($ctc) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="total-cell label">Total Amount Disbursed Rs.</td>
                <td class="disbursed">{{ $money($disbursed) }}</td>
                <td colspan="3" class="total-cell"></td>
            </tr>
            <tr><td colspan="9" class="muted">This is a system generated document and does not require a signature</td></tr>
            @if (!$loop->last)
                <tr><td colspan="9" class="dotted-separator"></td></tr>
            @endif
        </table>
    </div>
@endforeach
    </div>
@endforeach
