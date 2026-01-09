<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
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
        background-color: #ddd;
    }

    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>

<table class="datatable" style="margin-top: 0px;">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Emp No') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Working Days') }}</th>
            <th>{{ __('Net Salary Annex "A"') }}</th>
            <th>{{ __('Payable Salary') }}</th>
            <th>{{ __('Difference') }}</th>
            <th>{{ __('Per Day Salary') }}</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($data))
            @foreach ($data as $branchId => $empsalaries)
                @php
                    $branch = \Auth::user()->getBranch($branchId);
                    $totalGross = 0;
                    $totalNetPay = 0;
                    $totalDifference = 0;
                    $totalPerDaySalary = 0;
                    $totalSalDays = 0;
                @endphp
                <tr>
                    <td colspan="8" class="branch-title">
                        {{ $branch->name ?? 'Unknown Branch' }}
                    </td>
                </tr>
                @foreach ($empsalaries as $key => $empsalary)
                    @php
                        $difference = $empsalary->gross - $empsalary->net_pay;
                        $perDaySalary = $empsalary->sal_days > 0 ? $empsalary->net_pay / $empsalary->sal_days : 0;

                        $totalGross += $empsalary->gross;
                        $totalNetPay += $empsalary->net_pay;
                        $totalDifference += $difference;
                        $totalSalDays += $empsalary->sal_days;
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $empsalary->employee->employee_id }}</td>
                        <td>{{ $empsalary->employee->name }}</td>
                        <td>{{ $empsalary->sal_days }}</td>
                        <td>{{ number_format($empsalary->gross, 2) }}</td>
                        <td>{{ number_format($empsalary->net_pay, 2) }}</td>
                        <td>{{ number_format($difference, 2) }}</td>
                        <td>{{ number_format($perDaySalary, 2) }}</td>
                    </tr>
                @endforeach

                @php
                    $totalPerDaySalary = $totalSalDays > 0 ? $totalNetPay / $totalSalDays : 0;
                @endphp
                <tr class="total-row">
                    <td colspan="4">Total</td>
                    <td>{{ number_format($totalGross, 2) }}</td>
                    <td>{{ number_format($totalNetPay, 2) }}</td>
                    <td>{{ number_format($totalDifference, 2) }}</td>
                    <td>{{ number_format($totalPerDaySalary, 2) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
    </tbody>
</table>
