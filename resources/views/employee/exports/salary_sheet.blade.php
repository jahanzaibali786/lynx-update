@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th colspan="5">
                Employee Detail</th>
            <th colspan="8">Allowances</th>
            <th>
            </th>
            <th colspan="9">Deduction</th>
            <th>
            </th>
            <th colspan="4">Cost To School</th>
            <th colspan="3">CL</th>
            <th colspan="3">AL</th>
            <th>
            </th>
        </tr>
        <tr>
            {{-- <th >Departments.</th> --}}
            <th>
                Emp no.</th>
            <th>
                Scale</th>
            <th>
                Name</th>
            <th>
                Designation</th>
            <th>
                DOJ</th>

            <th>
                Earned Basic</th>
            @php
                $head_totals = [];
            @endphp
            @foreach (@$salaryHeads as $head)
                <th>
                    {{ @$head->head }}</th>
                @php
                    $head_totals[$head->id] = 0;
                @endphp
            @endforeach

            <th>
                Other Allowance</th>
            <th>
                Other</th>
            <th>
                Drns & Misc</th>
            <th>
                Stop Salary</th>

            <th>
                Gross Pay</th>

            <th>
                E.S</th>
            <th>
                I.Tax</th>
            <th>
                Salary Adv.</th>
            <th>
                EOBI Emp.</th>
            <th>
                Loan Emp Sec</th>
            <th>
                Stop Salary</th>
            <th>
                PESSI</th>
            <th>
                Other Deduction</th>
            <th>
                Loan</th>

            <th>
                Net</th>

            <th>
                PESSI Comp.</th>
            <th>
                EOBI Comp.</th>
            <th>
                Total</th>
            <th>
                Cost to Comp.</th>

            <th>
                OP</th>
            <th>
                LVs</th>
            <th>
                Bal</th>

            <th>
                OP</th>
            <th>
                LVs</th>
            <th>
                Bal</th>

            <th>
                Total Working Days</th>
        </tr>

    </thead>
    <tbody>
        @php
            $gross = 0;
            $total_basics = 0;
            $total_other = 0;
            $total_conv_other = 0;
            $total_other_misc = 0;
            $total_stop_sal = 0;
            $total_gross = 0;
            $total_emp_sec = 0;
            $total_it = 0;
            $total_advance = 0;
            $total_eobi = 0;
            $total_loan_emp_sec = 0;
            $total_stop_sal_deductions = 0;
            $total_pessi = 0;
            $total_other_deduction = 0;
            $total_loan = 0;
            $total_net_pay = 0;
            $total_pessi_employer = 0;
            $total_eobi_employer = 0;
            $total_total_cost = 0;
            $total_cost_to_comp = 0;
            $gross_bra = 0;
            $total_basics_bra = 0;
            $total_other_bra = 0;
            $total_conv_other_bra = 0;
            $total_other_misc_bra = 0;
            $total_stop_sal_bra = 0;
            $total_gross_bra = 0;
            $total_emp_sec_bra = 0;
            $total_it_bra = 0;
            $total_advance_bra = 0;
            $total_eobi_bra = 0;
            $total_loan_emp_sec_bra = 0;
            $total_stop_sal_deductions_bra = 0;
            $total_pessi_bra = 0;
            $total_other_deduction_bra = 0;
            $total_loan_bra = 0;
            $total_net_pay_bra = 0;
            $total_pessi_employer_bra = 0;
            $total_eobi_employer_bra = 0;
            $total_total_cost_bra = 0;
            $total_cost_to_comp_bra = 0;
            $currentDepartmentId = null; // To track the current department
        @endphp

        @foreach ($datas as $key => $data)
            @php
                $payscale = $data->employee->employee_payscale_details->last();
                $conv_other = $data->conv ?? 0;
                $other_misc = ($data->drns ?? 0) + ($data->misc ?? 0);
                $total_basics += $data->basics;
                $total_other += $data->other;
                $total_conv_other += $conv_other;
                $total_other_misc += $other_misc;
                $total_stop_sal += $data->stop_sal;
                $total_gross += !empty($data->gross) ? @$data->gross : '0';
                $total_emp_sec += !empty($data->emp_sec) ? @$data->emp_sec : '0';
                $total_it += !empty($data->it) ? @$data->it : '0';
                $total_advance += !empty($data->sal_advance) ? @$data->sal_advance : '0';
                $total_pessi += '0';
                $total_loan_emp_sec += !empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0';
                $total_eobi += !empty($data->eobi) ? @$data->eobi : '0';
                $total_loan += !empty($data->loan) ? @$data->loan : '0';
                $total_net_pay += !empty($data->net_pay) ? @$data->net_pay : '0';
                $total_stop_sal_deductions += !empty($data->stop_sal) ? @$data->stop_sal : '0';
                $total_other_deduction += !empty($data->dedu) ? @$data->dedu : '0';
                $total_pessi_employer += !empty($data->pessi_employer) ? @$data->pessi_employer : '0';
                $total_eobi_employer += !empty($data->eobi_employer) ? @$data->eobi_employer : '0';
                $total_total_cost += !empty($total_cost) ? @$total_cost : '0';
                // for branch totals
                $gross_bra += !empty($data->gross) ? @$data->gross : '0';
                $total_basics_bra += $data->basics;
                $total_other_bra += $data->other;
                $total_conv_other_bra += $conv_other;
                $total_other_misc_bra += $other_misc;
                $total_stop_sal_bra += $data->stop_sal;
                $total_gross_bra += !empty($data->gross) ? @$data->gross : '0';
                $total_emp_sec_bra += !empty($data->emp_sec) ? @$data->emp_sec : '0';
                $total_it_bra += !empty($data->it) ? @$data->it : '0';
                $total_advance_bra += !empty($data->sal_advance) ? @$data->sal_advance : '0';
                $total_pessi_bra += '0';
                $total_loan_emp_sec_bra += !empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0';
                $total_eobi_bra += !empty($data->eobi) ? @$data->eobi : '0';
                $total_loan_bra += !empty($data->loan) ? @$data->loan : '0';
                $total_net_pay_bra += !empty($data->net_pay) ? @$data->net_pay : '0';
                $total_stop_sal_deductions_bra += !empty($data->stop_sal) ? @$data->stop_sal : '0';
                $total_other_deduction_bra += !empty($data->dedu) ? @$data->dedu : '0';
                $total_pessi_employer_bra += !empty($data->pessi_employer) ? @$data->pessi_employer : '0';
                $total_eobi_employer_bra += !empty($data->eobi_employer) ? @$data->eobi_employer : '0';
                $total_total_cost_bra += !empty($total_cost) ? @$total_cost : '0';
                $total_cost_to_comp_bra += !empty($cost_to_comp) ? @$cost_to_comp : '0';
                
                
            @endphp
            @if ($currentDepartmentId != $data->department_id)
                {{-- show branch total row --}}
                <tr>
                    <th colspan="1"style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ !empty($data->employee->department->name) ? $data->employee->department->name : 'No Department Name' }} Total
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_basics_bra }}</th>
                    @foreach ($salaryHeads as $head)
                        <th style="border: none; font-weight: bold; background-color:lightgray;">
                            {{ !empty($head_totals_bra[$head->id]) ? $head_totals_bra[$head->id] : '0' }}</th>
                    @endforeach
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_other_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_conv_other_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_other_misc_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_stop_sal_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_gross_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_emp_sec_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_it_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_advance_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_eobi_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_loan_emp_sec_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_stop_sal_deductions_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_pessi_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_other_deduction_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_loan_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_net_pay_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_pessi_employer_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_eobi_employer_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_total_cost_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ $total_cost_to_comp_bra }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;">
                        {{ '' }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;"
                        >{{ '' }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray"
                        >{{ '' }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;"                        >{{ '' }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;"                        >{{ '' }}</th>
                    <th style="border: none; font-weight: bold; background-color:lightgray;"                        >{{ '' }}</th>
                </tr>
                <tr>
                    @php
                        $currentDepartmentId = $data->department_id;
                    @endphp
                    <th
                        colspan="1"style="border: none; font-weight: bold; background-color:gray;">
                        {{ !empty($data->employee->department->name) ? $data->employee->department->name : 'No Department Name' }}
                    </th>
                    <th
                        colspan="36"style="border: none; font-weight: bold; background-color:gray;">
                    </th>
                </tr>
            @endif
            <tr>
                {{-- Display the department name only when the department ID changes --}}

                <td>
                    {{ !empty($data->employee) ? @$data->employee->employee_id : '' }}</td>
                <td>
                    {{ !empty($data->scale_no) ? @$data->scale_no : '' }}</td>
                <td>
                    {{ !empty($data->employee->name) ? @$data->employee->name : '' }}
                </td>
                <td>
                    {{ !empty($data->employee->designation->name) ? @$data->employee->designation->name : '' }}</td>
                <td>
                    {{ !empty($data->employee->company_doj) ? @$data->employee->company_doj : '' }}</td>

                <td>
                    {{ !empty($data->basics) ? @$data->basics : '' }}</td>


                @foreach ($salaryHeads as $head)
                    @php
                        // Search for the matching salary head within the employee salary heads
                        $emp_sal_head = $data->salary_heads->firstWhere('head_id', $head->id);
                        $head_value = !empty($emp_sal_head) ? $emp_sal_head->head_value : 0;

                        // Accumulate the total for this head
                        $head_totals[$head->id] += $head_value;
                        // for departement
                        $head_totals_bra[$head->id] = isset($head_totals_bra[$head->id]) ? $head_totals_bra[$head->id] + $head_value : $head_value;
                        
                    @endphp

                    <td>
                        {{ !empty($emp_sal_head) ? $emp_sal_head->head_value : 0 }}
                    </td>
                @endforeach


                <td>
                    {{ !empty($data->other) ? @$data->other : '0' }}</td>
                <td>
                    {{ $conv_other }}</td>
                <td>
                    {{ $other_misc }}</td>
                <td>
                    {{ !empty($data->stop_sal) ? @$data->stop_sal : '0' }}</td>
                <td>
                    {{ !empty($data->gross) ? @$data->gross : '0' }}</td>

                <td>
                    {{ !empty($data->emp_sec) ? @$data->emp_sec : '0' }}</td>
                <td>
                    {{ !empty($data->it) ? @$data->it : '0' }}</td>
                <td>
                    {{ !empty($data->sal_advance) ? @$data->sal_advance : '0' }}</td>
                <td>
                    {{ !empty($data->eobi) ? @$data->eobi : '0' }}</td>
                <td>
                    {{ !empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0' }}
                </td>
                {{-- @php
            $net_deduction = (!empty($data->emp_sec) ? @$data->emp_sec : '0') + (!empty($data->it) ? @$data->it : '0')+(!empty($data->pessi) ? @$data->pessi : '0') + (!empty($data->sal_advance) ? @$data->sal_advance : '0') + (!empty($data->eobi) ? @$data->eobi : '0') + (!empty($data->emp_sec_loan) ? @$data->emp_sec_loan : '0')+ (!empty($data->stop_sal) ? @$data->stop_sal : '0') + (!empty($data->dedu) ? @$data->dedu : '0') + (!empty($data->loan) ? @$data->loan : '0');
            @endphp --}}

                <td>
                    {{ !empty($data->stop_sal) ? @$data->stop_sal : '0' }}</td>
                <td>
                    {{ !empty($data->pessi) ? @$data->pessi : '0' }}</td>
                <td>
                    {{ !empty($data->dedu) ? @$data->dedu : '0' }}</td>
                <td>
                    {{ !empty($data->loan) ? @$data->loan : '0' }}</td>
                <td>
                    {{ !empty($data->net_pay) ? @$data->net_pay : '0' }}</td>

                <td>
                    {{ !empty($data->pessi_employer) ? @$data->pessi_employer : '0' }}
                </td>
                <td>
                    {{ !empty($data->eobi_employer) ? @$data->eobi_employer : '0' }}
                </td>
                @php
                    $total_cost =
                        (!empty($data->pessi_employer) ? @$data->pessi_employer : '0') +
                        (!empty($data->eobi_employer) ? @$data->eobi_employer : '0');
                    $cost_to_comp =
                        (!empty($total_cost) ? @$total_cost : '0') + (!empty($data->gross) ? @$data->gross : '0');
                    $total_cost_to_comp += $cost_to_comp;
                @endphp

                <td>
                    {{ !empty($total_cost) ? @$total_cost : '0' }}</td>
                <td>
                    {{ !empty($cost_to_comp) ? @$cost_to_comp : '0' }}</td>
                @php
                    $empleaves = $data->employee->employee_monthly_salaries_attend->first();
                    $leav_cas =
                        (!empty($empleaves->total_casual) ? @$empleaves->total_casual : '0') -
                        (!empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0');

                    $leav_anul =
                        (!empty($empleaves->total_annual) ? @$empleaves->total_annual : '0') -
                        (!empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0');
                @endphp
                <td>
                    {{ !empty($empleaves->total_casual) ? @$empleaves->total_casual : '0' }}</td>
                <td>
                    {{ !empty($leav_cas) ? @$leav_cas : '0' }}</td>
                <td>
                    {{ !empty($empleaves->bal_casual) ? @$empleaves->bal_casual : '0' }}</td>

                <td>
                    {{ !empty($empleaves->total_annual) ? @$empleaves->total_annual : '0' }}</td>
                <td>
                    {{ !empty($leav_anul) ? @$leav_anul : '0' }}</td>
                <td>
                    {{ !empty($empleaves->bal_annual) ? @$empleaves->bal_annual : '0' }}</td>

                <td>
                    {{ !empty($data->sal_days) ? @$data->sal_days : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
