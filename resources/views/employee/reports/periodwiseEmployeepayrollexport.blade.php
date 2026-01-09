<table class="datatable">
    @include('student.exports.header')
    <thead>
        <tr class="table_heads">
            <th>Sr. No.</th>
            <th>Period</th>
            @if(!empty($employee_id))
                <th>Employee Name</th>
            @endif
            @foreach ($salaryHeads as $salaryhead)
                <th>{{ $salaryhead->head }}</th>
            @endforeach
            <th>Gross</th>
            <th>Employer Contribution E.O.B.I</th>
            <th>Employer Contribution P.E.S.S.I</th>
            <th>Employee Security</th>
            <th>Employee Contribution E.O.B.I</th>
            <th>Employee Contribution P.E.S.S.I</th>
            <th>Income Tax</th>
            <th>Other Deduction</th>
            <th>Net Payable</th>
            <th>Child Concession</th>
            <th>Cost to Company</th>
        </tr>
    </thead>

    <tbody>
        @php
            $branchTotalByHead = array_fill_keys($salaryHeads->pluck('id')->toArray(), 0);
            $rowCounter = 1;
        @endphp
        @foreach ($branchTotals as $branchId => $monthsData)
            @if(empty($employee_id))
                <tr>
                    <td colspan="{{ 3 + count($salaryHeads) + 12 }}"><b>{{ \Auth::user()->getBranch($branchId)->name }}</b></td>
                </tr>
            @endif
            @foreach ($monthsData as $month => $data)
                @php
                    $branchTotal = [
                        'gross' => 0,
                        'employer_contribution_eobi' => 0,
                        'employer_contribution_pessi' => 0,
                        'employee_security' => 0,
                        'employee_contribution_eobi' => 0,
                        'employee_contribution_pessi' => 0,
                        'income_tax' => 0,
                        'other_deduction' => 0,
                        'net_payable' => 0,
                        'child_concession' => 0,
                        'cost_to_company' => 0,
                    ];
                    foreach ($monthsData as $monthData) {
                        $branchTotal['gross'] += $monthData['gross'];
                        $branchTotal['employer_contribution_eobi'] += $monthData['employer_contribution_eobi'];
                        $branchTotal['employer_contribution_pessi'] += $monthData['employer_contribution_pessi'];
                        $branchTotal['employee_security'] += $monthData['employee_security'];
                        $branchTotal['employee_contribution_eobi'] += $monthData['employee_contribution_eobi'];
                        $branchTotal['employee_contribution_pessi'] += $monthData['employee_contribution_pessi'];
                        $branchTotal['income_tax'] += $monthData['income_tax'];
                        $branchTotal['other_deduction'] += $monthData['other_deduction'];
                        $branchTotal['net_payable'] += $monthData['net_payable'];
                        $branchTotal['child_concession'] += $monthData['child_concession'];
                        $branchTotal['cost_to_company'] += $monthData['cost_to_company'];
                    }
                @endphp
                <tr>
                    <td>{{ $rowCounter++ }}</td>
                    <td>{{ $month }}</td>
                    @if(!empty($employee_id))
                        @php
                            $employeeName = 'Unknown Employee';
                            if(is_array($employees) || is_object($employees)) {
                                $employeeName = $employees[$employee_id] ?? 'Unknown Employee';
                            }
                        @endphp
                        <td>{{ $employeeName }}</td>
                    @endif
                    @foreach ($salaryHeads as $head)
                        @php
                            $amount = $totalByHead[$month][$head->id] ?? 0;
                            $branchTotalByHead[$head->id] += $amount;
                        @endphp
                        <td>{{ $amount }}</td>
                    @endforeach
                    <td>{{ $data['gross'] }}</td>
                    <td>{{ $data['employer_contribution_eobi'] }}</td>
                    <td>{{ $data['employer_contribution_pessi'] }}</td>
                    <td>{{ $data['employee_security'] }}</td>
                    <td>{{ $data['employee_contribution_eobi'] }}</td>
                    <td>{{ $data['employee_contribution_pessi'] }}</td>
                    <td>{{ $data['income_tax'] }}</td>
                    <td>{{ $data['other_deduction'] }}</td>
                    <td>{{ $data['net_payable'] }}</td>
                    <td>{{ $data['child_concession'] }}</td>
                    <td>{{ $data['cost_to_company'] }}</td>
                </tr>
            @endforeach

            @if(empty($employee_id))
                <tr>
                    <td colspan="2"><strong>Branch Total</strong></td>
                    <td><b></b></td>
                    @foreach ($salaryHeads as $head)
                        <td><b>{{ $branchTotalByHead[$head->id] ?? 0 }}</b></td>
                    @endforeach
                    <td><b>{{ $branchTotal['gross'] }}</b></td>
                    <td><b>{{ $branchTotal['employer_contribution_eobi'] }}</b></td>
                    <td><b>{{ $branchTotal['employer_contribution_pessi'] }}</b></td>
                    <td><b>{{ $branchTotal['employee_security'] }}</b></td>
                    <td><b>{{ $branchTotal['employee_contribution_eobi'] }}</b></td>
                    <td><b>{{ $branchTotal['employee_contribution_pessi'] }}</b></td>
                    <td><b>{{ $branchTotal['income_tax'] }}</b></td>
                    <td><b>{{ $branchTotal['other_deduction'] }}</b></td>
                    <td><b>{{ $branchTotal['net_payable'] }}</b></td>
                    <td><b>{{ $branchTotal['child_concession'] }}</b></td>
                    <td><b>{{ $branchTotal['cost_to_company'] }}</b></td>
                </tr>
            @endif
        @endforeach
        <tr style="">
            <td colspan="2"><strong>{{ !empty($employee_id) ? 'Employee Total' : 'Grand Total' }}</strong></td>
            @if(empty($employee_id))
                <td><b></b></td>
            @else
                @php
                    $employeeName = 'Unknown Employee';
                    if(is_array($employees) || is_object($employees)) {
                        $employeeName = $employees[$employee_id] ?? 'Unknown Employee';
                    }
                @endphp
                <td><b>{{ $employeeName }}</b></td>
            @endif
            @foreach ($salaryHeads as $head)
                <td><b>{{ $grandTotal[$head->id] ?? 0 }}</b></td>
            @endforeach
            <td><b>{{ $grandTotal['gross'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['employer_contribution_eobi'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['employer_contribution_pessi'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['employee_security'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['employee_contribution_eobi'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['employee_contribution_pessi'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['income_tax'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['other_deduction'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['net_payable'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['child_concession'] ?? 0 }}</b></td>
            <td><b>{{ $grandTotal['cost_to_company'] ?? 0 }}</b></td>
        </tr>
    </tbody>
    @include('student.exports.footer')
</table>
