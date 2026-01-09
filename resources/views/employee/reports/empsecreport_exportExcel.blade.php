@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>Sr. No.</th>
            <th>Employee Name</th>
            <th>Designation</th>
            <th>D.O.J</th>
            <th>Previous Adj.</th>
            @foreach ($data['months'] as $month)
                <th>{{ \Carbon\Carbon::createFromFormat('M Y', $month)->format('F') }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotals = array_fill_keys($data['months'], 0);
        @endphp
        @foreach ($data['salaries']->groupBy('employee_id') as $employeeId => $employeeSalaries)
            @php
                $employee = $employeeSalaries->first()->employee;
                $employeeTotal = 0;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->designation->name }}</td>
                <td>{{ \Carbon\Carbon::parse($employee->company_doj)->format('d-m-Y') }}</td>
                <td>{{ number_format($employeeSalaries->first()->emp_sec ?? 0, 2) }}</td>
                @php
                    $employeeTotal = 0;
                @endphp
                @foreach ($data['months'] as $month)
                    @php
                        $monthYear = \Carbon\Carbon::createFromFormat('M Y', $month)->format('Y-m');
                        $monthlyTotal = $employeeSalaries
                            ->filter(function ($salary) use ($monthYear) {
                                $salaryDate = \Carbon\Carbon::parse($salary->salary_date);
                                return $salaryDate->format('Y-m') == $monthYear;
                            })
                            ->sum('emp_sec');
                        $employeeTotal += $monthlyTotal;
                        $grandTotals[$month] = isset($grandTotals[$month])
                            ? $grandTotals[$month] + $monthlyTotal
                            : $monthlyTotal;
                        $displayMonth = \Carbon\Carbon::createFromFormat('M Y', $month)->format('F');
                    @endphp
                    <td style="text-align:right;">{{ number_format($monthlyTotal, 2) }}</td>
                @endforeach
                <td style="text-align:right;"><b>{{ number_format($employeeTotal, 2) }}</b></td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
