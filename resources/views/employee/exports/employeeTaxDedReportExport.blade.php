<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th>{{ __('Branch Name') }}</th>
            <th>{{ __('Payroll Month') }}</th>
            <th>{{ __('Emp No.') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('D.O.J') }}</th>
            <th>{{ __('Designation') }}</th>
            @foreach ($salaryHeads as $salaryhead)
                <th>{{ $salaryhead->head }}</th>
            @endforeach
            <th>{{ __('Gross Salary') }}</th>
            <th>{{ __('Tax Ded. Rs.') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totals = [
                'heads' => array_fill_keys($salaryHeads->pluck('id')->toArray(), 0),
                'gross' => 0,
                'it' => 0,
            ];
        @endphp
        @foreach ($data as $index => $monthsalary)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $monthsalary->employee->branch->name ?? '' }}</td>
                <td>{{ date('F Y', strtotime($monthsalary->salary_date)) }}</td>
                <td>{{ $monthsalary->employee->employee_id ?? '' }}</td>
                <td>{{ $monthsalary->employee->name ?? '' }}</td>
                <td>{{ optional($monthsalary->employee->company_doj)->format('d-m-Y') }}</td>
                <td>{{ $monthsalary->employee->designation->name ?? '' }}</td>

                @foreach ($salaryHeads as $head)
                @php
                    $headValue =
                        optional($monthsalary->salary_heads->firstWhere('head_id', $head->id))
                            ->head_value ?? 0;
                    $totals['heads'][$head->id] += $headValue;
                @endphp
                <td>{{ $headValue }}</td>
            @endforeach


                @php
                    $displayGross = ($monthsalary->gross ?? 0) + ($monthsalary->stop_sal ?? 0);
                    $totals['gross'] += $displayGross;
                    $totals['it'] += $monthsalary->it;
                @endphp
                <td>{{ $displayGross }}</td>
                <td>{{ $monthsalary->it }}</td>
            </tr>
        @endforeach

        {{-- Totals Row --}}
        <tr>
            <td colspan="7" style="border: 1px solid black; background-color: gray; text-align: center; font-weight: bold;">Total</td>
            @foreach ($salaryHeads as $head)
                <td style="border: 1px solid black; background-color: gray; text-align: right; font-weight: bold;">{{ $totals['heads'][$head->id] }}</td>
            @endforeach
            <td style="border: 1px solid black; background-color: gray; text-align: right; font-weight: bold;">{{ $totals['gross'] }}</td>
            <td style="border: 1px solid black; background-color: gray; text-align: right; font-weight: bold;">{{ $totals['it'] }}</td>
        </tr>
    </tbody>

</table>
@include('student.exports.footer')
