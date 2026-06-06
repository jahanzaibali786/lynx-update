@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2">sr.#</th>
            <th rowspan="2">Name</th>
            <th rowspan="2">Designation</th>
            <th colspan="9">Deduction</th>
            <th rowspan="2">Total Ded</th>
            <th rowspan="2">Net</th>
        </tr>
        <tr>
            <th>
                Gross</th>
            <th>
                E.s</th>
            <th>
                I.Tax</th>
            <th>
                Salary Adv.</th>
            <th>
                EOBI</th>
            <th>
                PESSI</th>
            <th>
                Loan Sec</th>
            <th>
                Loan</th>
            <th>
                Other Deduction</th>
        </tr>

    </thead>
    <tbody>
        @php
            $tot_gross = 0;
            $tot_emp_sec = 0;
            $tot_it = 0;
            $tot_sal_advance = 0;
            $tot_eobi = 0;
            $tot_pessi = 0;
            $tot_emp_sec_loan = 0;
            $tot_loan = 0;
            $tot_other_deduction = 0;
            $tot_dec = 0;
            $tot_net = 0;
        @endphp
        @foreach ($datas as $key => $data)
            @php
                $tot_gross += !empty($data->gross) ? $data->gross : 0;
                $tot_emp_sec += !empty($data->emp_sec) ? $data->emp_sec : 0;
                $tot_it += !empty($data->it) ? $data->it : 0;
                $tot_sal_advance += !empty($data->sal_advance) ? $data->sal_advance : 0;
                $tot_eobi += !empty($data->eobi) ? $data->eobi : 0;
                $tot_pessi += !empty($data->pessi) ? $data->pessi : 0;
                $tot_emp_sec_loan += !empty($data->emp_sec_loan) ? $data->emp_sec_loan : 0;
                $tot_loan += !empty($data->loan) ? $data->loan : 0;
                $tot_other_deduction += !empty($data->dedu) ? $data->dedu : 0;
                $tot_net += !empty($data->net_pay) ? $data->net_pay : 0;

            @endphp

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>
                    {{ !empty($data->employee->name) ? @$data->employee->name : '' }}
                </td>
                <td>
                    {{ !empty($data->employee->designation->name) ? @$data->employee->designation->name : '' }}</td>
                <td>
                    {{ !empty($data->gross) ? $data->gross : 0 }}</td>
                <td>
                    {{ !empty($data->emp_sec) ? $data->emp_sec : 0 }}</td>
                <td>{{ !empty($data->it) ? $data->it : 0 }}
                </td>
                <td>
                    {{ !empty($data->sal_advance) ? $data->sal_advance : 0 }}</td>
                <td>
                    {{ !empty($data->eobi) ? $data->eobi : 0 }}</td>
                <td>
                    {{ !empty($data->pessi) ? $data->pessi : 0 }}</td>
                <td>
                    {{ !empty($data->emp_sec_loan) ? $data->emp_sec_loan : 0 }}</td>
                <td>
                    {{ !empty($data->loan) ? $data->loan : 0 }}</td>
                <td>
                    {{ !empty($data->dedu) ? $data->dedu : 0 }}</td>
                @php
                    $total_deduction =
                        (!empty($data->emp_sec) ? $data->emp_sec : 0) +
                        (!empty($data->it) ? $data->it : 0) +
                        (!empty($data->sal_advance) ? $data->sal_advance : 0) +
                        (!empty($data->eobi) ? $data->eobi : 0) +
                        (!empty($data->pessi) ? $data->pessi : 0) +
                        (!empty($data->emp_sec_loan) ? $data->emp_sec_loan : 0) +
                        (!empty($data->loan) ? $data->loan : 0) +
                        (!empty($data->dedu) ? $data->dedu : 0);
                    $net = (!empty($data->gross) ? $data->gross : 0) - $total_deduction;
                    $tot_dec += $total_deduction;
                @endphp
                <td>{{ $total_deduction }}</td>
                <td>
                    {{ !empty($data->net_pay) ? $data->net_pay : 0 }}</td>
            </tr>
        @endforeach
    </tbody>
    <tbody>
        <tr>
            <td style="border: 2px solid black; text-align:center; background-color:gray; border-collapse: collapse;"
                colspan="3">Total:</td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_gross }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_emp_sec }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_it }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">
                {{ @$tot_sal_advance }}</td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_eobi }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_pessi }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_emp_sec_loan }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_loan }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_other_deduction }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_dec }}
            </td>
            <td style="border: 2px solid black; background-color:gray; border-collapse: collapse;">{{ @$tot_net }}
            </td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')
