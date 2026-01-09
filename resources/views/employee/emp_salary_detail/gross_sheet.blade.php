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
            <h4 style="font-size: 2rem; font-weight: 800; margin: 0;">Gross Sheet</h4>
            <p style="font-family: 'Edwardian Script ITC'; text-align: center; margin: 0;">
                {!!\Auth::user()->getBranch($requestdata['branches'])
                ?\Auth::user()->getBranch($requestdata['branches'])->name : 'Main Branch' !!}</p>
        </div>
        <div style="display: table-cell; width: 40%; text-align: center; vertical-align: middle;">
            <p style="margin: 0; font-size:0.9rem; text-align:right;"><b>PAYROLL &nbsp;&nbsp;&nbsp;<i>M.I.S THE LYNX
                        SCHOOL</i></b></p>
            <p style="margin: 0; text-align:right;"><b>Pr Date: &nbsp;&nbsp;</b>{{\Carbon\Carbon::now()->format('M d, Y') }}</p>
        </div>
    </div>
    <div class="">
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000; ">sr.#</th>
                    <th style="border: 1px solid #000; ">Emp. Id</th>
                    <th style="border: 1px solid #000; ">Name</th>
                    <th style="border: 1px solid #000; ">Gross</th>
                    <th style="border: 1px solid #000; ">Net</th>
                    <th style="border: 1px solid #000;">Cost To Company</th>
                </tr>
            </thead>
            @php
                $gross = 0;
                $net_tot = 0;
                $gross_tot = 0;
                $cast_tot = 0;
            @endphp
            @foreach($datas as $key => $data)
            @php
            $payscale = $data->employee->employee_payscale_details->last();
            @endphp
            <tbody>
                <tr>
                    <td style="border: 1px solid #000; text-align:center;width:40px;">{{$loop->iteration}}</td>
                    <td style="border: 1px solid #000; text-align:center;width:60px;">{{!empty($data->employee->id) ? @$data->employee->id : ''}}</td>
                    <td style="border: 1px solid #000; text-align:center;width:150px;">{{!empty($data->employee->name) ? @$data->employee->name : ''}}</td>
                    @php
                     $total_cost = (!empty($data->pessi_employer) ? @$data->pessi_employer : '0') + (!empty($data->eobi_employer) ? @$data->eobi_employer : '0');
                     $cost_to_comp = (!empty($total_cost) ? @$total_cost : '0') + (!empty($data->gross) ? @$data->gross : '0');

                     $net_deduction = (!empty($data->emp_sec) ? @$data->emp_sec : '0') + (!empty($data->dedu) ? @$data->dedu : '0') + (!empty($data->it) ? @$data->it : '0')+(!empty($data->pessi) ? @$data->pessi : '0') + (!empty($payscale->advance) ? @$payscale->advance : '0') + (!empty($data->eobi) ? @$data->eobi : '0') + (!empty($data->loan_emp_sec) ? @$data->loan_emp_sec : '0')+ (!empty($data->stop_sal) ? @$data->stop_sal : '0');

                     $net = (!empty($data->gross) ? @$data->gross : '0') - $net_deduction;
                     $gross_tot += $data->gross;
                     $cast_tot += $cost_to_comp;
                     $net_tot += $net;

                    @endphp
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{!empty($data->gross) ? @$data->gross : '0'}}</td>
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{!empty($net) ? @$net : '0'}}</td>
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{!empty($cost_to_comp) ? @$cost_to_comp : '0'}}</td>
                </tr>
            </tbody>
            @endforeach
            <tfoot  style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <td style="border: 1px solid #000; text-align:right; width:120px;" colspan="3"> </td>
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{@$gross_tot}}</td>
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{@$net_tot}}</td>
                    <td style="border: 1px solid #000; text-align:right; width:120px;">{{@$cast_tot}}</td>

            </tfoot>
    </div>
</body>
