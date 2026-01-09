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
            <h4 style="font-size: 2rem; font-weight: 800; margin: 0;">Advance</h4>
        </div>
        <div style="display: table-cell; width: 40%; text-align: center; vertical-align: middle;">
            <p style="margin: 0; font-size:0.9rem; text-align:right;"><b>PAYROLL &nbsp;&nbsp;&nbsp;<i>M.I.S THE LYNX
                        SCHOOL</i></b></p>
            <p style="margin: 0; text-align:right;"><b>Pr Date: &nbsp;&nbsp;</b>{{date('d-F-Y')}}</p>
        </div>
    </div>
    <div class="">
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000; ">sr.#</th>
                    <th style="border: 1px solid #000; ">Name</th>
                    <th style="border: 1px solid #000; ">Designation</th>
                    <th style="border: 1px solid #000; ">Adv</th>
                    <th style="border: 1px solid #000; ">EOBI</th>
                    <th style="border: 1px solid #000;">I.Tax</th>
                    <th style="border: 1px solid #000;">Loan</th>
                </tr>
            </thead>
            @php
            $gross = 0;
            $tot_adv =0;
            $tot_eobi =0;
            $tot_tax =0;
            $tot_loan =0;
            @endphp
            @foreach($datas as $key => $data)
            @php
            $payscale = $data->employee->employee_payscale_details->last();
            $tot_adv +=!empty($payscale->advance) ? @$payscale->advance : '0';
            $tot_eobi +=!empty($data->eobi) ? @$data->eobi : '0';
            $tot_tax +=!empty($data->it) ? @$data->it : '0';
            $tot_loan +=!empty($data->loan) ? @$data->loan : '0';
            @endphp
            <tbody>
                <tr>
                    <td style="border: 1px solid #000; text-align:center;width:40px;">{{$loop->iteration}}</td>
                    <td style="border: 1px solid #000; text-align:center;width:150px;">{{!empty($data->employee->name) ? @$data->employee->name : ''}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->employee->designation->name) ? @$data->employee->designation->name : ''}}</td>
                    <td style="border: 1px solid #000;">{{!empty($payscale->advance) ? @$payscale->advance : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->eobi) ? @$data->eobi : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->it) ? @$data->it : '0'}}</td>
                    <td style="border: 1px solid #000;">{{!empty($data->loan) ? @$data->loan : '0'}}</td>
                </tr>
            </tbody>
            @endforeach
            <tfoot  style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <td style="border: 1px solid #000;" colspan="3"> Total </td>
                    <td style="border: 1px solid #000;">{{@$tot_adv}}</td>
                    <td style="border: 1px solid #000;">{{@$tot_eobi}}</td>
                    <td style="border: 1px solid #000;">{{@$tot_tax}}</td>
                    <td style="border: 1px solid #000;">{{@$tot_loan}}</td>
            </tfoot>
    </div>
</body>
