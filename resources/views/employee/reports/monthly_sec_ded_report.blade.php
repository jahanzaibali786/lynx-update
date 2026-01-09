@php
    $report_name = isset($report_name) ? $report_name : 'Employee Security Deduction Report';
@endphp

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.6rem;
    }
    th, td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }
    th {
        background-color: #f2f2f2;
    }
    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #949191;
        color: #fff;
    }
    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>


<table class="datatable" style="margin-top: -50px;">
    <thead>
        <tr>
            <th>{{ __('Sr.') }}</th>
            <th>{{ __('Br.sr') }}</th>
            <th>{{ __('Branch Name') }}</th>
            <th>{{ __('Emp No.') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Father Name') }}</th>
            <th>{{ __('Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotal = 0;
            $sr = 1;
        @endphp
        @foreach ($reportData as $branchName => $employees)
            <tr class="branch-title">
                <td colspan="7" style="text-align:left;">{{ $branchName }}</td>
            </tr>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>{{ @$employee->employee->userbranch->id ?? '' }}</td>
                    <td>{{ $branchName }}</td>
                    <td>{{ @$employee->employee_id }}</td>
                    <td>{{ @$employee->employee->name }}</td>
                    <td>{{ @$employee->employee->f_name }}</td>
                    <td style="text-align:right;">{{ number_format($employee->emp_sec, 2) }}</td>
                </tr>
                @php $grandTotal += $employee->emp_sec; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="6" style="text-align:right;">Branch Total</td>
                <td style="text-align:right;">{{ number_format($employees->sum('emp_sec'), 2) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="6" style="text-align:right;">Grand Total</td>
            <td style="text-align:right;">{{ number_format($grandTotal, 2) }}</td>
        </tr>
    </tbody>
</table>