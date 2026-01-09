<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <div style="width: 100%; position: relative; bottom: 30px; display: table;">
        <div style="display: table-cell; width: 25%; text-align: center; vertical-align: middle;">
            <div class="logo">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
            </div>
        </div>
        <div style="display: table-cell; width: 35%; text-align: center; vertical-align: middle;">
            <h4 style="font-size: 2rem; font-weight: 800; margin: 0;">Deduction Form</h4>
            <p style="font-family: 'Edwardian Script ITC'; text-align: center; margin: 0;">The Lynx School</p>
        </div>
        <div style="display: table-cell; width: 40%; text-align: center; vertical-align: middle;">
            <p style="margin: 0; font-size:0.9rem; text-align:right;"><b>PAYROLL &nbsp;&nbsp;&nbsp;<i>M.I.S THE LYNX SCHOOL</i></b></p>
            <p style="margin: 0; text-align:right;">{{date('F j, Y')}}</p>
        </div>
    </div>
    <div>
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000; width: 5%;" rowspan="2">sr.#</th>
                    <th style="border: 1px solid #000; width: 20%;" rowspan="2">Name</th>
                    <th style="border: 1px solid #000; width: 15%;" rowspan="2">Designation</th>
                    <th style="border: 1px solid #000; width: 7%;" colspan="8">Deduction</th>
                    <th style="border: 1px solid #000; width: 7%;" rowspan="2">Total Ded</th>
                    <th style="border: 1px solid #000; width: 7%;" rowspan="2">Net</th>
                </tr>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000;">Gross</th>
                    <th style="border: 1px solid #000;">E.s</th>
                    <th style="border: 1px solid #000;">I.Tax</th>
                    <th style="border: 1px solid #000;">Adv</th>
                    <th style="border: 1px solid #000;">EOBI</th>
                    <th style="border: 1px solid #000;">PESSI</th>
                    <th style="border: 1px solid #000;">Loan</th>
                    <th style="border: 1px solid #000;">Oth</th>
                </tr>
            </thead>
            @php
                $tot_gross = 0;
                $tot_emp_sec = 0;
                $tot_it = 0;
                $tot_sal_advance = 0;
                $tot_eobi = 0;
                $tot_pessi = 0;
                $tot_loan = 0;
                $tot_other = 0;
                $tot_dec = 0;
                $tot_net = 0;
            @endphp
            <tbody>
                @foreach($datas as $key => $data)

                @php
                    $tot_gross += !empty($data->gross) ? $data->gross : 0;
                    $tot_emp_sec += !empty($data->emp_sec) ? $data->emp_sec : 0;
                    $tot_it += !empty($data->it) ? $data->it : 0;
                    $tot_sal_advance += !empty($data->sal_advance) ? $data->sal_advance : 0;
                    $tot_eobi += !empty($data->eobi) ? $data->eobi : 0;
                    $tot_pessi += !empty($data->pessi) ? $data->pessi : 0;
                    $tot_loan += !empty($data->loan) ? $data->loan : 0;
                    $tot_other += !empty($data->other) ? $data->other : 0;
                    $tot_net += !empty($data->net_pay) ? $data->net_pay : 0;

                @endphp

                    <tr style="border: 1px solid #000; font-size:0.7rem;">
                        <td style="border: 1px solid #000;">{{$key + 1}}</td>
                        <td style="border: 1px solid #000;">{{!empty($data->employee->name) ? @$data->employee->name : ''}}</td>
                        <td style="border: 1px solid #000;">{{!empty($data->employee->designation->name) ? @$data->employee->designation->name : '' }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->gross) ? $data->gross : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->emp_sec) ? $data->emp_sec : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->it) ? $data->it : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->sal_advance) ? $data->sal_advance : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->eobi) ? $data->eobi : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->pessi) ? $data->pessi : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->loan) ? $data->loan : 0 }}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->other) ? $data->other : 0 }}</td>
                        @php
                            $total_deduction = (!empty($data->emp_sec) ? $data->emp_sec : 0 ) + (!empty($data->it) ? $data->it : 0) + (!empty($data->sal_advance) ? $data->sal_advance : 0) + (!empty($data->eobi) ? $data->eobi : 0 ) + (!empty($data->pessi) ? $data->pessi : 0 ) + (!empty($data->loan) ? $data->loan : 0) + (!empty($data->other) ? $data->other : 0);
                            $net = (!empty($data->gross) ? $data->gross : 0) - $total_deduction ;
                            $tot_dec += $total_deduction;
                        @endphp
                        <td style="border: 1px solid #000;">{{$total_deduction}}</td>
                        <td style="border: 1px solid #000;">{{ !empty($data->net_pay) ? $data->net_pay : 0 }}</td>
                        {{-- <td style="border: 1px solid #000;">{{$net}}</td> --}}
                    </tr>
                @endforeach
            </tbody>
            <tbody>
                    <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                        <td style="border: 1px solid #000; text-align: center;" colspan="3" >Total : </td>
                        <td style="border: 1px solid #000;">{{@$tot_gross}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_emp_sec}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_it}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_sal_advance}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_eobi }}</td>
                        <td style="border: 1px solid #000;">{{@$tot_pessi}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_loan}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_other}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_dec}}</td>
                        <td style="border: 1px solid #000;">{{@$tot_net}}</td>
                        {{-- <td style="border: 1px solid #000;">{{$net}}</td> --}}
                    </tr>
            </tbody>
        </table>



    </div>

</body>

</html>
