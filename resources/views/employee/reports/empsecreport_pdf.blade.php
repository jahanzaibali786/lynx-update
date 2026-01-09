<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    /* Print styles */
    @media print {
        /* Ensure top margin is applied to all pages */
        body {
            margin-top: 20px; /* Adjust the value to suit your needs */
        }

        /* Add page breaks after the card */
        .card {
            page-break-after: always;
        }

        /* Ensure that margins persist on all pages */
        .table-responsive {
            margin-top: 20px; /* Adjust the value as necessary */
            margin-bottom: 20px;
        }

        /* Additional margin for all pages, excluding the first */
        @page {
            margin: 20px;
        }
    }
</style>

<div class="card p-4">
    <p style="text-align:center; font-weight:900; font-size:1rem;">
    </p>
    <div class="table-responsive">
        <table style="width: 100%; table-layout: fixed; word-wrap: break-word; margin-top: -50px;">
            <thead>
                <tr style="background-color: grey; font-size: 0.9rem;">
                    <th>Sr. No.</th>
                    <th>Employee Name</th>
                    <th>Designation</th>
                    <th>D.O.J</th>
                    <th>Previous Adj.</th>
                    @foreach ($months as $month)
                        <th>{{ \Carbon\Carbon::createFromFormat('M Y', $month)->format('F') }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotals = array_fill_keys($months, 0);
                @endphp
                @foreach ($salaries->groupBy('employee_id') as $employeeId => $employeeSalaries)
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
                        @foreach ($months as $month)
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
    </div>
</div>
