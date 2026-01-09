<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th rowspan="2">Sr. No</th>
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
                <th colspan="{{ count($prevSessions) }}">Gross Salary (Previous)
                </th>
            @endif

            <th rowspan="2">Gross Salary ({{ $currentSession }})</th>
        </tr>
        <tr>
            @foreach ($prevSessions as $session)
                <th>{{ $session }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $groupedByBranch = collect($processedData)->groupBy(function ($emp) {
                return $emp['branch_name'] ?? 'Unknown Branch';
            });

            $grandTotals = array_fill_keys($displaySessions, 0); // Initialize grand totals for each session
        @endphp

        @foreach ($groupedByBranch as $branchName => $employees)
            @php
                $branchTotals = array_fill_keys($displaySessions, 0); // Initialize branch totals
            @endphp

            <tr>
                <td colspan="3" style="text-align: left; font-weight: bold; border:none; background-color: #f0f0f0;">
                    <strong>{{ $branchName }}</strong>
                </td>
                <td colspan="{{ 3 + count($displaySessions) }}" style="text-align: left; font-weight: bold; border:none; background-color: #f0f0f0;">
                </td>
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
                        @php
                            $val = $emp['salaries'][$session] ?? 0;
                            $branchTotals[$session] += $val;
                            $grandTotals[$session] += $val;
                        @endphp
                        <td style="text-align:center;">{{ number_format($val, 2) }}</td>
                    @endforeach

                    @php
                        $currentVal = $emp['salaries'][end($displaySessions)] ?? 0;
                        $branchTotals[end($displaySessions)] += $currentVal;
                        $grandTotals[end($displaySessions)] += $currentVal;
                    @endphp
                    <td style="text-align:center;">{{ number_format($currentVal, 2) }}</td>
                </tr>
            @endforeach

            {{-- Branch total row --}}
            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="6" style="text-align:right;">Branch Total</td>
                @foreach ($displaySessions as $session)
                    <td style="text-align:center;">{{ number_format($branchTotals[$session], 2) }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td colspan="{{ 6 + count($displaySessions) }}" style="height: 30px;"></td>
        </tr>
        {{-- Grand total row --}}
        <tr>
            <td colspan="6" style="text-align:right; font-weight: bold; background-color: #f0f0f0; border: 2px solid black; border-top: 4px double black; border-bottom: 4px double black;">Grand Total</td>
            @foreach ($displaySessions as $session)
                <td style="text-align:center; font-weight: bold; background-color: #f0f0f0; border: 2px solid black; border-top: 4px double black; border-bottom: 4px double black;">{{ number_format($grandTotals[$session], 2) }}</td>
            @endforeach
        </tr>
    </tbody>


</table>
@include('student.exports.footer')