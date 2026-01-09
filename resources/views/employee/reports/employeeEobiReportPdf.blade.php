@php
    $report_name = isset($report_name) ? $report_name : 'EOBI Contribution Report';
@endphp

<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 13px;
        color: #222;
    }
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    td {
        padding-left: 3px;
    }
    th, td {
        font-size: 0.7rem;
        padding: 4px 6px;
        text-align: center;
    }
    th {
        background-color: #f2f2f2;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #949191;
        color: #fff;
        padding: 6px;
        font-size: 1rem;
    }
    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
    .grand-total-row {
        font-weight: bold;
        background-color: #dbeafe;
        font-size: 1rem;
    }
</style>

<div class="card p-4">
    <div class="mt-4">
        @php
            $sr = 1;
            $eobigrandTotal = 0;
            $eobi_employergrandTotal = 0;
        @endphp
        @foreach ($reportData as $branchName => $employees)
        <div class="" style="width: 100%;">
            <span class="branch-title">
                {{ $branchName }}
            </span>
            <table class="table" style="width: 100%; padding-top: -50px;">
                <thead>
                    <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                        <th style="width:4%;">Sr No.</th>
                        <th style="width:6%;">Br.sr</th>
                        <th style="width:6%;">Emp Code</th>
                        <th style="width:15%;">Employee Name</th>
                        <th style="width:15%;">Father Name</th>
                        <th style="width:10%;">CNIC</th>
                        <th style="width:10%;">EOBI No.</th>
                        <th style="width:9%;">D.O.J</th>
                        <th style="width:9%;">D.O.B</th>
                        <th style="width:7%;">Age</th>
                        <th style="width:7%;">Monthly Wages</th>
                        <th style="width:7%;">Working Days</th>
                        <th style="width:7%;">Employee's Cont.</th>
                        <th style="width:7%;">Employer's Cont.</th>
                    </tr>
                </thead>
                <tbody style="font-size:0.7rem;">
                    @php $branchEobi = 0; $branchEobiEmployer = 0; @endphp
                    @foreach ($employees as $index => $employee)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ @$employee->employee->userbranch->id ?? '' }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employee->employee->name }}</td>
                        <td>{{ $employee->employee->f_name }}</td>
                        <td>{{ $employee->employee->cnic }}</td>
                        <td></td>
                        <td>{{ $employee->employee->company_doj }}</td>
                        <td>{{ $employee->employee->dob }}</td>
                        @php
                            $dob = \Carbon\Carbon::parse($employee->employee->dob);
                            $now = \Carbon\Carbon::now();
                            $ageyears = $now->diffInYears($dob);
                            $agemonths = $now->diffInMonths($dob) % 12;
                        @endphp
                        <td>{{ $ageyears }} Y & {{ $agemonths }} M</td>
                        <td style="text-align:right;">{{ number_format($employee->basics, 2) }}</td>
                        <td style="text-align:right;">{{ $employee->sal_days }}</td>
                        <td style="text-align:right;">{{ $employee->eobi }}</td>
                        <td style="text-align:right;">{{ $employee->eobi_employer }}</td>
                    </tr>
                    @php
                        $branchEobi += $employee->eobi;
                        $branchEobiEmployer += $employee->eobi_employer;
                        $eobigrandTotal += $employee->eobi;
                        $eobi_employergrandTotal += $employee->eobi_employer;
                    @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="12" style="text-align:right;">Branch Total:</td>
                        <td style="text-align:right;">{{ number_format($branchEobi, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($branchEobiEmployer, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
        <table class="table" style="width:100%">
            <thead style="font-weight:bold; background-color:grey; color:#fff;">
                <tr>
                    <td style="width:84%; text-align:right;">Grand Total :</td>
                    <td style="width:8%; text-align:right;">{{ number_format($eobigrandTotal, 2) }}</td>
                    <td style="width:8%; text-align:right;">{{ number_format($eobi_employergrandTotal, 2) }}</td>
                </tr>
            </thead>
        </table>
    </div>
</div>