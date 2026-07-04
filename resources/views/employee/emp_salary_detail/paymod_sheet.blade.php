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
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                    alt="logo">
            </div>
        </div>
        <div style="display: table-cell; width: 65%; text-align: center; vertical-align: middle;">
            <h4 style="font-size: 1.7rem; font-weight: 800; margin: 0;">EMPLOYEES SALARY DETAIL</h4>
            <p style="font-family: 'Edwardian Script ITC'; text-align: center; margin: 0;">The Lynx School</p>
        </div>
        <div style="display: table-cell; width: 10%; text-align: center; vertical-align: middle;">
        </div>
    </div>
    <div>
        <div class="">
            <b>
                <p>Bank Name : <span>{!! $requestdata ? $requestdata['paymode'] : '------' !!}</span> </p>
            </b>
            <b>
                <p>Month : <span>{{ \Carbon\Carbon::parse($requestdata['date'])->format('F-Y') }}</span> </p>
            </b>
            <b>
                <p>Date : <span>{{ now()->format('d-F-Y') }}</span> </p>
            </b>
        </div>
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000;">Sr#</th>
                    <th style="border: 1px solid #000;">Name</th>
                    <th style="border: 1px solid #000;">CNIC</th>
                    <th style="border: 1px solid #000;">Email</th>
                    <th style="border: 1px solid #000;">Contact Number</th>
                    @if (isset($requestdata) &&
                            (strtolower($requestdata['paymode']) != 'cheque' && strtolower($requestdata['paymode']) != 'cash'))
                        <th style="border: 1px solid #000;">Account Number</th>
                    @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cheque')
                        <th style="border: 1px solid #000;">Cheque Number</th>
                    @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                        <th style="border: 1px solid #000;">Cash</th>
                    @endif

                    <th style="border: 1px solid #000;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gross = 0;
                    $tot_pay = 0;
                @endphp
                @foreach ($datas as $key => $data)
                    @php
                        $tot_pay += !empty($data->net_pay) ? $data->net_pay : '';
                        $gross += ($data->gross ?? 0) + ($data->stop_sal ?? 0);
                        $payscale = $data->employee->employee_payscale_details->last();

                    @endphp
                    <tr style="border: 1px solid #000; font-size:0.7rem;">
                        <td style="border: 1px solid #000;">{{ $key + 1 }}</td>
                        <td style="border: 1px solid #000;">
                            {{ !empty($data->employee->name) ? @$data->employee->name : '' }}</td>
                        <td style="border: 1px solid #000;">
                            {{ !empty($data->employee->cnic) ? @$data->employee->cnic : '' }}</td>
                        <td style="border: 1px solid #000;">
                            {{ !empty($data->employee->email) ? @$data->employee->email : '' }}</td>
                        <td style="border: 1px solid #000;">
                            {{ !empty($data->employee->phone) ? @$data->employee->phone : '' }}</td>
                        @if (isset($requestdata) &&
                                (strtolower($requestdata['paymode']) != 'cash'))
                        <td style="border: 1px solid #000;">
                            {{ !empty($payscale->account_number) ? @$payscale->account_number : '' }}</td>
                        @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                        <td style="border: 1px solid #000;">
                           cash</td>
                        @endif
                        <td style="border: 1px solid #000;">{{ !empty($data->net_pay) ? $data->net_pay : '' }}</td>
                    </tr>
                @endforeach
                <tr style="border: 1px solid #000; font-size:0.7rem;  background-color:gray; font-size:0.9rem;">
                    <td style="border: 1px solid #000; text-align: center;" colspan="2"> Grand Total</td>
                    <td colspan="3"></td>
                    <td style="border: 1px solid #000; text-align: center;" colspan="2">{{ @$tot_pay }}</td>
                </tr>
            </tbody>
        </table>



    </div>

</body>

</html>
