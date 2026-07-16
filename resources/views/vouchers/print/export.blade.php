@php
    $emptyRows = max(0, 6 - $accounts->count());
@endphp

<!doctype html>
<html>
<head>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .page {
            width: 100%;
        }

        .section-title {
            background: #d9d9d9;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 14px;
        }

        .label {
            font-weight: bold;
            white-space: nowrap;
        }

        .line {
            border-bottom: 1px solid #000000;
            white-space: nowrap;
        }

        .entries th {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            border-top: 2px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 2px 4px;
        }

        .entries td {
            font-size: 12px;
            padding: 2px 4px;
            height: 20px;
            vertical-align: top;
            white-space: nowrap;
        }

        .gap-right {
            border-right: 5px solid #ffffff;
        }

        .gap-left {
            border-left: 5px solid #ffffff;
        }

        .amount-cell {
            text-align: right;
        }

        .signature-text {
            text-align: center;
            vertical-align: bottom;
            height: 70px;
            color: #999999;
            font-weight: bold;
        }

        .signature-line {
            border-top: 2px solid #000000;
            text-align: center;
            font-weight: bold;
            height: 24px;
        }
    </style>
</head>
<body>
<div class="page">
    <table>
        <tr>
            <td style="width:35%; height:72px; vertical-align:top;">
                @if(!empty($titleLogo))
                    <img src="{{ $titleLogo }}" style="width:230px; height:60px;">
                @endif
            </td>
            <td style="width:35%;"></td>
            <td style="width:30%; text-align:right; vertical-align:top;">
                @if(!empty($headerLogo))
                    <img src="{{ $headerLogo }}" width="105" height="80" style="width:105px; height:80px;"><br>
                @endif
            </td>
        </tr>
    </table>

    <table style="margin-top:4px;">
        <tr>
            <td style="width:50%; font-weight:bold;">{{ $data['voucher_title'] ?? '' }}</td>
            <td style="width:20%;"></td>
            <td style="width:10%; text-align:right;" class="label">Date:</td>
            <td style="width:20%; text-align:right;" class="line">{{ $data['date'] ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td style="text-align:right;" class="label">{{ $data['voucher_type'] ?? '' }} no:</td>
            <td style="text-align:right;" class="line">{{ $data['voucher_number'] ?? '' }}</td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:65%; vertical-align:top; padding-right:28px;">
                <table>
                    <tr>
                        <td colspan="2" class="section-title">Payee Information:</td>
                    </tr>
                    <tr><td colspan="2" style="height:8px;"></td></tr>
                    @foreach($payeeRows as $payeeRow)
                        <tr>
                            <td style="width:38%; height:22px;" class="label">{{ $payeeRow[0] }}</td>
                            <td style="width:62%; height:22px;" class="line">{{ $payeeRow[1] }}</td>
                        </tr>
                        <tr><td colspan="2" style="height:4px;"></td></tr>
                    @endforeach
                </table>
            </td>
            <td style="width:35%; vertical-align:top; padding-left:16px;">
                <table style="font-size:11px;">
                    <tr>
                        <td colspan="2" class="section-title" style="font-size:14px;">Payment Information:</td>
                    </tr>
                    <tr><td colspan="2" style="height:8px;"></td></tr>
                    @foreach($paymentRows as $paymentRow)
                        <tr>
                            <td style="width:42%; height:19px; font-size:11px;" class="label">{{ $paymentRow[0] }}</td>
                            <td style="width:58%; height:19px; font-size:11px; text-align:right;" class="line">{{ $paymentRow[1] }}</td>
                        </tr>
                        <tr><td colspan="2" style="height:3px;"></td></tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <table class="entries" style="margin-top:12px;">
        <thead>
        <tr>
            <th style="width:25%;" class="gap-right">Account Head</th>
            <th style="width:40%;" class="gap-left gap-right">Description</th>
            <th style="width:15%;" class="gap-left gap-right">Debit (Rs)</th>
            <th style="width:15%;" class="gap-left">Credit (Rs)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($accounts as $account)
            <tr>
                <td style="font-weight:bold;" class="gap-right">{{ $account['account_head'] ?? '' }}</td>
                <td class="gap-left gap-right">{{ $account['description'] ?? '' }}</td>
                <td class="gap-left gap-right amount-cell">{{ $account['debit'] ?? '' }}</td>
                <td class="gap-left amount-cell">{{ $account['credit'] ?? '' }}</td>
            </tr>
        @endforeach
        @for($i = 0; $i < $emptyRows; $i++)
            <tr>
                <td class="gap-right">&nbsp;</td>
                <td class="gap-left gap-right">&nbsp;</td>
                <td class="gap-left gap-right">&nbsp;</td>
                <td class="gap-left">&nbsp;</td>
            </tr>
        @endfor
        <tr>
            <td class="gap-right"></td>
            <td style="font-weight:bold; text-align:center;" class="gap-left gap-right">Total</td>
            <td style="font-weight:bold; border-top:2px solid #000000; border-bottom:3px double #000000;" class="gap-left gap-right amount-cell">
                {{ $data['total_debit'] ?? '' }}
            </td>
            <td style="font-weight:bold; border-top:2px solid #000000; border-bottom:3px double #000000;" class="gap-left amount-cell">
                {{ $data['total_credit'] ?? '' }}
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-top:14px;">
        <tr>
            <td style="width:18%; font-weight:bold;">Amount in Words:</td>
            <td style="width:82%;">{{ $data['amount_words'] ?? '' }}-</td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:38%;" class="section-title">Note for Payment:</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2" style="height:46px; vertical-align:top; padding-top:6px;">
                {{ $data['note'] ?? '' }}
            </td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:38%;" class="section-title">Receiver Information:</td>
            <td style="width:62%;"></td>
        </tr>
    </table>
    <table style="margin-top:8px;">
        @foreach($receiverRows as $label)
            <tr>
                <td style="width:16%; height:21px;" class="label">{{ $label }}</td>
                <td style="width:50%;" class="line"></td>
                <td style="width:34%;"></td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top:32px;">
        <tr>
            <td style="width:38%;" class="section-title">Authorization:</td>
            <td></td>
        </tr>
    </table>

    <table style="margin-top:16px;">
        <tr>
            <td style="width:29%; height:48px;" class="signature-text">Signature/Date</td>
            <td style="width:6%;"></td>
            <td style="width:29%; height:48px;" class="signature-text">Signature/Date</td>
            <td style="width:6%;"></td>
            <td style="width:30%; height:48px;" class="signature-text">Signature/Date</td>
        </tr>
        <tr>
            <td class="signature-line">Manager Finance</td>
            <td></td>
            <td class="signature-line">Director Finance</td>
            <td></td>
            <td class="signature-line">Managing Director</td>
        </tr>
    </table>
</div>
</body>
</html>
