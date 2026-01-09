<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    th,
    td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }

    tr,td{
        font-size: 0.8rem;
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

<table class="datatable" style="margin-top: -50px;">
    <thead>
        <tr>
            <th rowspan="2">Sr</th>
            <th rowspan="2">D.O.J</th>
            <th rowspan="2">Emp No</th>
            <th rowspan="2">Employee Name</th>
            <th rowspan="2">Designation</th>
            <th rowspan="2">Tenur</th>
            @php
                $prevSessions = array_slice($displaySessions, 0, count($displaySessions) - 1);
                $currentSession = end($displaySessions);
            @endphp

            @if (count($prevSessions))
                <th style="text-align:center;" colspan="{{ count($prevSessions) }}">Gross Salary (Previous)
                </th>
            @endif

            <th style="text-align:center;" rowspan="2">Gross Salary ({{ $currentSession }})</th>
        </tr>
        <tr>
            @foreach ($prevSessions as $session)
                <th style="text-align:center;">{{ $session }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $groupedByBranch = collect($processedData)->groupBy(function ($emp) {
                return $emp['branch_name'] ?? 'Unknown Branch'; 
            });
        @endphp

        @foreach ($groupedByBranch as $branchName => $employees)
            <tr style="background-color: #f0f0f0; text-align: left;">
                <td style="text-align: left;" colspan="{{ 6 + count($displaySessions) }}">
                    <strong>{{ $branchName }}</strong></td>
            </tr>

            @foreach ($employees as $emp)
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>{{ $emp['emp_doj'] }}</td>
                    <td>{{ $emp['emp_no'] }}</td>
                    <td>{{ $emp['name'] }}</td>
                    <td>{{ $emp['emp_designation'] }}</td>
                    <td>{{ $emp['emp_service_period'] }}</td>

                    @foreach (array_slice($displaySessions, 0, -1) as $session)
                        <td style="text-align:center;">
                            {{ number_format($emp['salaries'][$session] ?? 0, 2) }}</td>
                    @endforeach

                    <td style="text-align:center;">
                        {{ number_format($emp['salaries'][end($displaySessions)] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>

</table>