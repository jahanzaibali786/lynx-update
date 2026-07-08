<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
        }
        .report-header {
            width: 100%;
            display: table;
            margin-bottom: 12px;
        }
        .report-header-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .report-logo {
            width: 20%;
            text-align: center;
        }
        .report-title {
            width: 60%;
            text-align: center;
        }
        .report-title-image {
            width: 330px;
            max-width: 330px;
            height: auto;
            display: inline-block;
        }
        .report-branch {
            margin: 4px 0;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .report-month {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }
        .paymode-meta {
            margin: 8px 0 28px 0;
            font-size: 12px;
            font-weight: bold;
        }
        .paymode-meta table {
            border-collapse: collapse;
        }
        .paymode-meta td {
            padding: 4px 8px 4px 0;
            white-space: nowrap;
        }
        .paymode-meta .label {
            width: 72px;
        }
        .paymode-table {
            border: 1px solid #000;
            border-collapse: collapse;
            width: 100% !important;
            font-size: 12px;
        }
        .paymode-table th,
        .paymode-table td {
            border: 1px solid #000;
            padding: 3px 6px;
        }
        .paymode-table th {
            background: #bfbfbf;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .paymode-table .sr {
            width: 34px;
            text-align: center;
        }
        .paymode-table .amount {
            text-align: right;
        }
        .paymode-grand-total td {
            background: #bfbfbf;
            font-weight: bold;
            text-align: center;
        }
        .paymode-signatures {
            width: 100%;
            margin-top: 62px;
            border-collapse: collapse;
            font-size: 12px;
            font-weight: bold;
        }
        .paymode-signatures td {
            width: 50%;
            text-align: center;
            border: 0;
        }
        .paymode-sign-line {
            display: inline-block;
            width: 145px;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
    </style>
</head>

<body>
    @php
        $branchName = '';
        if (!empty($requestdata['branches'])) {
            $branchName = optional(\App\Models\User::find($requestdata['branches']))->name;
        }
        if (empty($branchName)) {
            $branchName = 'All Branches';
        }
        $logoSrc = asset('assets/images/lynx2.jpg');
        $schoolTitleSrc = asset('assets/images/lynxheadertext.jpg');
        $paymodeLabel = $requestdata['paymode'] ?? '------';
        $bankNames = [
            'Bank Deposit HBL' => 'Habib Bank Ltd.',
            'Bank Deposit AF' => 'Bank Deposit AF',
            'Demand Draft' => 'Demand Draft',
            'Cheque' => 'Cheque',
            'Bank' => 'Bank Deposite',
            'Cash' => 'Cash',
        ];
        $bankName = $bankNames[$paymodeLabel] ?? $paymodeLabel;
    @endphp
    <div class="report-header">
        <div class="report-header-cell report-logo">
            <img src="{{ $logoSrc }}" style="max-width: 90px; max-height: 90px;" alt="logo">
        </div>
        <div class="report-header-cell report-title">
            <img src="{{ $schoolTitleSrc }}" class="report-title-image" alt="The Lynx School">
            <div class="report-branch">{{ $branchName }}</div>
            <div class="report-month">Employees Salary Detail</div>
        </div>
        <div class="report-header-cell report-logo">
        </div>
    </div>
    <div>
        <div class="paymode-meta">
            <table>
                <tr>
                    <td class="label">Bank Name</td>
                    <td>{{ $bankName }}</td>
                </tr>
                <tr>
                    <td class="label">Month:</td>
                    <td>{{ \Carbon\Carbon::parse($requestdata['date'])->format('F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Date</td>
                    <td>{{ now()->format('F d, Y') }}</td>
                </tr>
            </table>
        </div>
        <table class="paymode-table">
            <thead>
                <tr>
                    <th class="sr">Sr.#</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Contact Number</th>
                    @if (isset($requestdata) &&
                            (strtolower($requestdata['paymode']) != 'cheque' && strtolower($requestdata['paymode']) != 'cash'))
                        <th>Account No</th>
                    @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cheque')
                        <th>Cheque Number</th>
                    @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                        <th>Cash</th>
                    @endif

                    <th>Amount</th>
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
                    <tr>
                        <td class="sr">{{ $key + 1 }}</td>
                        <td>
                            {{ !empty($data->employee->name) ? @$data->employee->name : '' }}</td>
                        <td>
                            {{ !empty($data->employee->cnic) ? @$data->employee->cnic : '' }}</td>
                        <td>
                            {{ !empty($data->employee->phone) ? @$data->employee->phone : '' }}</td>
                        @if (isset($requestdata) &&
                                (strtolower($requestdata['paymode']) != 'cash'))
                        <td>
                            {{ !empty($payscale->account_number) ? @$payscale->account_number : '' }}</td>
                        @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                        <td>
                           cash</td>
                        @endif
                        <td class="amount">{{ !empty($data->net_pay) ? number_format($data->net_pay) : '' }}</td>
                    </tr>
                @endforeach
                <tr class="paymode-grand-total">
                    <td colspan="5">Grand Total</td>
                    <td class="amount">{{ number_format(@$tot_pay) }}</td>
                </tr>
            </tbody>
        </table>
        <table class="paymode-signatures">
            <tr>
                <td><span class="paymode-sign-line">Checked By</span></td>
                <td><span class="paymode-sign-line">Authorised By</span></td>
            </tr>
        </table>



    </div>

</body>

</html>
