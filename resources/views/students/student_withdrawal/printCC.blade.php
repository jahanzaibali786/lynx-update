<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: A4;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }
        .certificate-container {
            width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 0;
        }
        .certificate-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .certificate-subtitle {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .certificate-table {
            width: 100%;
            padding-top: 50px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .certificate-table th,
        .certificate-table td {
            padding: 2px 6px;
            vertical-align: top;
            text-align: left;
        }
        .certificate-table tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        .certificate-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .deduction-header {
            background: #888 !important;
            color: #000;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 2px;
            text-align: left;
        }
        .signature-row td {
            padding-top: 25px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #222;
            width: 80%;
            margin: 0 auto 2px auto;
            text-align: center;
            padding-top: 2px;
            font-size: 11px;
        }
        .acknowledgement {
            text-align: center;
            font-weight: bold;
            margin: 8px 0 6px 0;
        }
        .date-line {
            text-align: right;
            margin-top: 8px;
            font-size: 12px;
        }
        .date-line b {
            border-bottom: 1px solid #222;
            min-width: 120px;
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-title" style="font-family: Edwardian Script ITC;">The Lynx School</div>
        <div class="certificate-subtitle">Certificate / Final Settlement</div>
        <table class="certificate-table">
            <tr>
                <th>Branch:</th>
                <td>{{ $branch ? $branch->name : '-' }}</td>
            </tr>
            <tr>
                <th>Name:</th>
                <td style="text-transform:uppercase;"><b>{{ $student ? $student->stdname : '-' }}</b></td>
            </tr>
            <tr>
                <th>Roll No:</th>
                <td>{{ $student ? $student->roll_no : '-' }}</td>
            </tr>
            <tr>
                <th>Class:</th>
                <td>{{ $class ? $class->name : '-' }}</td>
            </tr>
            <tr>
                <th>Admission Date:</th>
                <td>{{ $enrollment ? \Carbon\Carbon::parse($enrollment->adm_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Application Date:</th>
                <td>{{ $withdrawal ? \Carbon\Carbon::parse($withdrawal->apply_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Withdrawl Date:</th>
                <td>{{ $withdrawal ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Reason of Leaving:</th>
                <td><b>{{ $withdrawal ? $withdrawal->reason : '-' }}</b></td>
            </tr>
            <tr>
                <th>Security Deposit:</th>
                <td>{{ number_format($securityDeposit, 2) }}</td>
            </tr>
            <tr>
                <th>Date of Deposit:</th>
                <td>{{ $securityDepositDate ? \Carbon\Carbon::parse($securityDepositDate)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Last Month Paid Amount:</th>
                <td>{{ $lastPaidChallan ? number_format($lastPaidChallan->paid_amount, 2) : '0.00' }}</td>
            </tr>
            <tr>
                <th>Tution Fee Paid upto:</th>
                <td>{{ $lastPaidChallan ? strtoupper(\Carbon\Carbon::parse($lastPaidChallan->fee_month)->format('M-Y')) : '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="deduction-header">Deduction</td>
            </tr>
            <tr>
                <th>Notice Fee:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Other Fee:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Actual Fee:</th>
                <td>{{ $arrearsTotal ? number_format($arrearsTotal, 2) : '-' }}</td>
            </tr>
            <tr>
                <th>Refund:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Other Deduction:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Total Payables:</th>
                <td>{{ number_format($totalPayables, 2) }}</td>
            </tr>
            <tr>
                <th>Total Receivable:</th>
                <td>{{ number_format($totalReceivables, 2) }}</td>
            </tr>
            <tr>
                <th>Net Balance:</th>
                <td>{{ number_format($netBalance, 2) }}</td>
            </tr>
            <tr>
                <th>Check Issued in favor of:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Cheque No:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Bank Name:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Cheque Date:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Remarks:</th>
                <td>{{ $withdrawal ? $withdrawal->remark : '-' }}</td>
            </tr>
            <tr>
                <th>HO Remarks:</th>
                <td>{!! $withdrawal && $withdrawal->ho_remarks ? $withdrawal->ho_remarks : '-' !!}</td>
            </tr>
        </table>
        <table width="100%" style="margin-top: 15px;">
            <tr class="signature-row">
                <td><div class="signature-line">Prepared by</div></td>
                <td><div class="signature-line">Checked by</div></td>
                <td><div class="signature-line">Approved by</div></td>
            </tr>
        </table>
        <div class="acknowledgement">Acknowledgement of receipt</div>
        <div style="margin-bottom: 8px;">
            <p style="font-size:12px;">
                I <b>___________________________________________________________</b> do hereby confirm that I have received my entire dues from <span>The Lynx School</span>, SMC Pvt Ltd and I have no claim on the school.
            </p>
        </div>
        <div class="date-line">
            Date: <b>&nbsp;</b>
        </div>
    </div>
</body>
</html>