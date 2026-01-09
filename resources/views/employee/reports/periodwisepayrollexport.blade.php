<table class="datatable">
    @include('student.exports.header')
    <thead>
        <tr class="table_heads">
            <th>Sr. No.</th>
            <th>Period</th>
            <th>Total Of Emp.</th>
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
        @endphp
        @foreach ($branchTotals as $branchId => $monthsData)
            <tr>
                <td colspan="3"><b>{{ \Auth::user()->getBranch($branchId)->name }}</b></td>
                <td colspan="12"><b></b></td>
            </tr>
            @foreach ($monthsData as $month => $data)
                @php
                    $branchTotal = [
                        'total_employees' => 0,
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
                    foreach ($monthsData as $data) {
                        $branchTotal['total_employees'] += $data['total_employees'];
                        $branchTotal['gross'] += $data['gross'];
                        $branchTotal['employer_contribution_eobi'] += $data['employer_contribution_eobi'];
                        $branchTotal['employer_contribution_pessi'] += $data['employer_contribution_pessi'];
                        $branchTotal['employee_security'] += $data['employee_security'];
                        $branchTotal['employee_contribution_eobi'] += $data['employee_contribution_eobi'];
                        $branchTotal['employee_contribution_pessi'] += $data['employee_contribution_pessi'];
                        $branchTotal['income_tax'] += $data['income_tax'];
                        $branchTotal['other_deduction'] += $data['other_deduction'];
                        $branchTotal['net_payable'] += $data['net_payable'];
                        $branchTotal['child_concession'] += $data['child_concession'];
                        $branchTotal['cost_to_company'] += $data['cost_to_company'];
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $month }}</td>
                    <td>{{ $data['total_employees'] }}</td>
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

            <tr>
                <td colspan="2"><strong>Branch Total</strong></td>
                <td><b>{{ $branchTotal['total_employees'] }}</b></td>
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
        @endforeach
        <tr style="">
            <td colspan="2"><strong>Grand Total</strong></td>
            <td><b>{{ $grandTotal['total_employees'] ?? 0 }}</b></td>
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
