@php
    if (!function_exists('pathToUrl')) {
        function pathToUrl($path) {
            if (empty($path)) return '';
            if (strpos($path, 'http') === 0 || strpos($path, '//') === 0) {
                return $path;
            }
            $path = str_replace('\\', '/', $path);
            if (strpos($path, '/public/') !== false) {
                $parts = explode('/public/', $path);
                return asset(end($parts));
            }
            if (strpos($path, 'wamp64/www/lynx/') !== false) {
                $parts = explode('wamp64/www/lynx/', $path);
                $subParts = explode('/', end($parts));
                if ($subParts[0] === 'public') {
                    array_shift($subParts);
                }
                return asset(implode('/', $subParts));
            }
            $publicPath = str_replace('\\', '/', public_path());
            if (strpos($path, $publicPath) === 0) {
                return asset(substr($path, strlen($publicPath)));
            }
            return $path;
        }
    }

    $entryCount = max(1, $accounts->count());
    $entryRowHeight = match (true) {
        $entryCount <= 2 => 30,
        $entryCount <= 4 => 25,
        $entryCount <= 6 => 20,
        $entryCount <= 10 => 15,
        default => 12,
    };
    $fontSectionHeading = '15px';
    $fontLabel = '14px';
    $fontTableStrong = '14px';
    $fontData = '14px';
    $fontEntryHeading = '15px';
    $fontEntryStrong = '13px';
    $fontEntryData = '12px';
    $fontEntryTotal = '15px';
    $fontHeaderVoucherLabel = '16px';
    $fontHeaderVoucherNumber = '14px';
    $fontStamp = '28px';
    $sectionTitleStyle = "font-size: {$fontSectionHeading}; line-height: 18px; font-family: Arial, sans-serif; font-weight: bold;";
@endphp

<!doctype html>
<html>
<head>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            font-size: {{ $fontData }};
            color: #000000;
            background-color: #f4f6f9;
        }

        .school-title {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 30px;
            font-weight: 600;
            letter-spacing: .5px;
        }
        .school-title-image {
            width: 245px;
            max-width: 100%;
            height: auto;
            display: inline-block;
        }

        .print-container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            background: #fff;
            position: relative;
        }
        .btn-print {
            background-color: #6a1b9a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        .btn-print:hover {
            background-color: #8e24aa;
            color: white;
        }
        .btn-back {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body {
                padding: 0;
                background-color: #fff;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container {
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
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
            font-family: Arial, sans-serif !important;
            font-size: {{ $fontSectionHeading }} !important;
            line-height: 18px !important;
        }

        .section-title-inline {
            /* display: inline-block; */
            width: 100%;
            background: #d9d9d9;
            font-family: Arial, sans-serif !important;
            font-size: 18px !important;
            font-weight: bold;
            padding: 4px 8px;
            /* line-height: 18px !important;
            white-space: nowrap; */
        }

        .label {
            font-weight: bold;
            font-size: {{ $fontLabel }};
            white-space: nowrap;
        }

        .data-value {
            font-size: {{ $fontData }};
        }

        .table-strong {
            font-size: {{ $fontTableStrong }};
            font-weight: bold;
        }

        .voucher-meta-label {
            font-size: {{ $fontHeaderVoucherLabel }};
            font-weight: bold;
            white-space: nowrap;
        }

        .voucher-meta-number {
            font-size: {{ $fontHeaderVoucherNumber }};
            font-weight: bold;
            white-space: nowrap;
            /* text-decoration: underline; */
        }

        .line {
            border-bottom: 0.5px solid #dddddd;
            white-space: normal;
        }

        .entries {
            table-layout: fixed;
        }

        .entries th {
            font-size: {{ $fontEntryHeading }} !important;
            font-weight: bold;
            text-align: center;
            border-top: 2px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 2px 4px;
        }

        .entries td {
            font-size: {{ $fontEntryData }} !important;
            padding: 2px 4px;
            vertical-align: top;
            white-space: normal;
            word-wrap: break-word;
        }

        .entries .entry-strong {
            font-size: {{ $fontEntryStrong }} !important;
            font-weight: bold;
        }

        .entries .entry-total {
            font-size: {{ $fontEntryTotal }} !important;
            font-weight: bold;
        }

        .entries .amount-cell {
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
            font-size: {{ $fontTableStrong }};
            height: 24px;
        }

        .signature-date {
            font-weight: bold;
            font-size: {{ $fontLabel }};
            height: 22px;
            white-space: nowrap;
        }

        .stamp-mark {
            color: #cfcfcf;
            font-size: {{ $fontStamp }};
            font-weight: bold;
            letter-spacing: 5px;
            opacity: 0.35;
            transform: rotate(-15deg);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container text-center mb-4 no-print" style="margin-bottom: 20px; text-align: center; margin-top: 10px;">
        <button onclick="window.print();" class="btn-print">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 5px;">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            {{ __('Print Voucher') }}
        </button>
        <a href="{{ route('journal-entry.index') }}" class="btn-back">{{ __('Back to List') }}</a>
    </div>

    <div class="print-container">
        <div class="page">
            <table>
                <tr>
                    <td style="width:35%; height:72px; vertical-align:top;">
                        @if(!empty($titleLogo))
                            <img src="{{ pathToUrl($titleLogo) }}" style="width:230px; height:60px;">
                        @endif
                    </td>
                    <td style="width:35%;"></td>
                    <td style="width:30%; text-align:right; vertical-align:top;">
                        @if(!empty($headerLogo))
                            <img src="{{ asset('assets/images/lynx2.jpg') }}" width="95" height="65" style="width:95px; height:65px;"><br>
                        @endif
                    </td>
                </tr>
            </table>

    <table style="margin-top:4px;">
        <tr>
            <td style="width:66%; vertical-align:top;" class="table-strong">{{ $data['voucher_title'] ?? '' }}</td>
            <td style="width:34%; vertical-align:top;">
                <table align="right" style="width:70%; position:relative !important;">
                    <tr>
                        <td style="width:29%; text-align:left;" class="label">Date:</td>
                        <td style="width:71%; text-align:left;" class="data-value">{{ $data['date'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="text-align:left; width:40%;" class=" voucher-meta-label">{{ $data['voucher_type'] ?? '' }} no:</td>
                        <td style="text-align:left; width:60%; border-bottom: 1px solid #000;" class="voucher-meta-number">{{ $data['voucher_number'] ?? '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:48%; vertical-align:top;">
                <table>
                    <tr>
                        <td colspan="2" class="section-title-inline">Payee Information:</td>
                    </tr>
                    <tr><td colspan="2" style="height:8px;"></td></tr>
                    @foreach($payeeRows as $payeeRow)
                        <tr>
                            <td style="width:38%; height:22px;" class="label">{{ $payeeRow[0] }}</td>
                            <td style="width:62%; height:22px;" class="line data-value">{{ $payeeRow[1] }}</td>
                        </tr>
                        <tr><td colspan="2" style="height:4px;"></td></tr>
                    @endforeach
                </table>
            </td>
            <td style="width:4%;"></td>
            <td style="width:48%; vertical-align:top;">
                <table>
                    <tr>
                        <td colspan="2" class="section-title-inline">Payment Information:</td>
                    </tr>
                    <tr><td colspan="2" style="height:8px;"></td></tr>
                    @foreach($paymentRows as $paymentRow)
                        <tr>
                            <td style="width:30%; height:22px;" class="label">{{ $paymentRow[0] }}</td>
                            <td style="width:70%; height:22px; text-align:left;" class="line data-value">{{ $paymentRow[1] }}</td>
                        </tr>
                        <tr><td colspan="2" style="height:4px;"></td></tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <table class="entries" style="margin-top:12px;">
        <thead>
        <tr>
            <th style="width:20%; text-align:left; font-size:{{ $fontEntryHeading }};" class="gap-right">Account Head</th>
            <th style="width:45%; text-align:left; font-size:{{ $fontEntryHeading }};" class="gap-left gap-right">Description</th>
            <th style="width:15%; font-size:{{ $fontEntryHeading }};" class="gap-left gap-right">Debit (Rs)</th>
            <th style="width:15%; font-size:{{ $fontEntryHeading }};" class="gap-left">Credit (Rs)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($accounts as $account)
            <tr>
                <td style="height:{{ $entryRowHeight }}px; line-height:{{ max(17, $entryRowHeight - 4) }}px; font-size:{{ $fontEntryStrong }};" class="gap-right entry-strong">{{ $account['account_head'] ?? '' }}</td>
                <td style="height:{{ $entryRowHeight }}px; line-height:{{ max(17, $entryRowHeight - 4) }}px; font-size:{{ $fontEntryData }};" class="gap-left gap-right data-value">{{ $account['description'] ?? '' }}</td>
                <td style="height:{{ $entryRowHeight }}px; line-height:{{ max(17, $entryRowHeight - 4) }}px; font-size:{{ $fontEntryData }};" class="gap-left gap-right amount-cell data-value">{{ $account['debit'] ?? '' }}</td>
                <td style="height:{{ $entryRowHeight }}px; line-height:{{ max(17, $entryRowHeight - 4) }}px; font-size:{{ $fontEntryData }};" class="gap-left amount-cell data-value">{{ $account['credit'] ?? '' }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="gap-right"></td>
            <td style="text-align:center; font-size:{{ $fontEntryTotal }};" class="gap-left gap-right entry-total">Total</td>
            <td style="border-top:2px solid #000000; border-bottom:3px double #000000; font-size:{{ $fontEntryTotal }};" class="gap-left gap-right amount-cell entry-total">
                {{ $data['total_debit'] ?? '' }}
            </td>
            <td style="border-top:2px solid #000000; border-bottom:3px double #000000; font-size:{{ $fontEntryTotal }};" class="gap-left amount-cell entry-total">
                {{ $data['total_credit'] ?? '' }}
            </td>
        </tr>
        </tbody>
    </table>

    <table style="margin-top:5px;">
        <tr>
            <td style="width:18%;" class="label">Amount in Words:</td>
            <td style="width:82%; font-size:14px">{{ $data['amount_words'] ?? '' }}-</td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:25%; {{ $sectionTitleStyle }}" class="section-title">Note for Payment:</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="2" style="height:46px; vertical-align:top; padding-top:5px; font-size:{{ $fontEntryData }}">
                {{ $data['note'] ?? '' }}
            </td>
        </tr>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <td style="width:25%; {{ $sectionTitleStyle }}" class="section-title">Receiver Information:</td>
            <td style="width:75%;"></td>
        </tr>
    </table>
    <table style="margin-top:8px;">
        @foreach($receiverRows as $receiverRow)
            @php
                $isSignatureRow = stripos($receiverRow[0], 'Signature') !== false;
            @endphp
            <tr>
                <td style="width:14%; height:{{ $isSignatureRow ? 42 : 21 }}px; vertical-align:{{ $isSignatureRow ? 'bottom' : 'middle' }};" class="label">{{ $receiverRow[0] }}</td>
                <td style="width:32%; height:{{ $isSignatureRow ? 42 : 21 }}px; vertical-align:{{ $isSignatureRow ? 'bottom' : 'middle' }};" class="line data-value">{{ $receiverRow[1] }}</td>
                <td style="width:58%;"></td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top:32px;">
        <tr>
            <td style="width:25%; {{ $sectionTitleStyle }}" class="section-title">Authorization:</td>
            <td></td>
        </tr>
    </table>
    <table style="margin-top:50px;">
        <tr>
            <td style="width:29%; height:48px;" class="signature-text"></td>
            <td style="width:6%;"></td>
            <td style="width:29%; height:48px;" class="signature-text"></td>
            <td style="width:6%;"></td>
            <td style="width:30%; height:48px;" class="signature-text"></td>
        </tr>
        <tr>
            <td class="signature-line">Manager Finance</td>
            <td></td>
            <td class="signature-line">Director Finance</td>
            <td></td>
            <td class="signature-line">Managing Director</td>
        </tr>
        <tr>
            <td class="signature-date">
                <table>
                    <tr>
                        <td style="width:12%;"></td>
                        <td style="width:22%; text-align:right;" class="label">Date :</td>
                        <td style="width:54%;" class="line"></td>
                        <td style="width:12%;"></td>
                    </tr>
                </table>
            </td>
            <td></td>
            <td class="signature-date">
                <table>
                    <tr>
                        <td style="width:12%;"></td>
                        <td style="width:22%; text-align:right;" class="label">Date :</td>
                        <td style="width:54%;" class="line"></td>
                        <td style="width:12%;"></td>
                    </tr>
                </table>
            </td>
            <td></td>
            <td class="signature-date">
                <table>
                    <tr>
                        <td style="width:12%;"></td>
                        <td style="width:22%; text-align:right;" class="label">Date :</td>
                        <td style="width:54%;" class="line"></td>
                        <td style="width:12%;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="margin-top:25px;">
        <tr>
            <td style="width:65%;"></td>
            <td style="width:35%; height:34px; text-align:center; vertical-align:middle;">
                <div class="stamp-mark">STAMP</div>
            </td>
        </tr>
    </table>
</div>
</div>
</body>
</html>
