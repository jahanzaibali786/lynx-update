<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Daily Closing - ') . $dailyClosing->from_date->format('d-M-Y') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            background-color: #fff;
            color: #000;
            font-family: 'Outfit', sans-serif, Arial;
            padding: 20px;
        }
        .print-container {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            background: #fff;
        }
        .header-title {
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
            font-size: 1.4rem;
        }
        .header-subtitle {
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        .header-date {
            text-align: center;
            font-weight: 700;
            /* margin-bottom: 25px; */
            font-size: 1rem;
            /* border-bottom: 2px double #000; */
            padding-bottom: 5px;
            display: inline-block;
            width: 100%;
        }
        .table-title {
            background-color: #666;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 3px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .table-closing {
            font-size: 0.8rem;
            border-collapse: collapse;
            width: 100%;
        }
        .table-closing tr {
            height: 25px;
        }
        .table-closing th, .table-closing td {
            /* border: 1px solid #000; */
            padding: 2px 6px;
            vertical-align: middle;
            height: 25px;
            box-sizing: border-box;
            overflow: hidden;
            white-space: nowrap;
        }
        .table-closing th {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px double #000;
        }
        .signature-section {
            margin-top: 400px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 30%;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .sig-box strong {
            font-size: 0.9rem;
            display: block;
        }
        .sig-box span {
            font-size: 0.8rem;
            color: #555;
        }
        .diff-section {
            margin-top: 30px;
            text-align: right;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .diff-amount {
            font-size: 1rem;
            margin-left: 10px;
        }
        .btn-print {
            background-color: #6a1b9a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-print:hover {
            background-color: #8e24aa;
            color: white;
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
        @media print {
            body {
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            * {
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
    </style>
</head>
<body>
@php 
     $schoolTitleSrc = !empty($isPdf) ? public_path('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
@endphp
    <div class="container text-center mb-4 no-print">
        <button onclick="window.print();" class="btn-print">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            {{ __('Print Closing Sheet') }}
        </button>
        <a href="{{ route('daily-closing.index') }}" class="btn btn-light ms-2">{{ __('Back to List') }}</a>
    </div>

    <div class="print-container">
        
        <!-- Header -->
        <div style="width: 100%; display: table; margin-bottom: 25px; border-bottom: 2px double #000; padding-bottom: 5px;">
            <div style="display: table-cell; width: 75%; vertical-align: top; text-align: left;">
                <h1 class="school-title" ><img src="{{ $schoolTitleSrc }}" class="school-title-image" alt="The Lynx School"></h1>
                <div style="font-size: 1.1rem; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">HEAD OFFICE DAILY CLOSING ( CSH )</div>
                <div style="font-size: 1rem; font-weight: 700;">Dated: {{ $dailyClosing->deposit_date ? $dailyClosing->deposit_date->format('d M Y') : '-' }}</div>
            </div>
            <div style="display: table-cell; width: 25%; vertical-align: middle; text-align: right;">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Income Received -->
            <div class="col-7 pe-3">
                <div class="table-title mb-1">{{ __('Cash Received') }}</div>
                <table class="table-closing">
                    <thead>
                        <tr style="border-bottom: 1px double #000;">
                            <th style="width: 8%;">Sr no</th>
                            <th style="width: 30%;">Branch Name</th>
                            <th style="width: 25%;">Period</th>
                            <th style="width: 15%;">Slip no</th>
                            <th style="text-align: right; width: 15%;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $emptyLinesCount = 25 - count($transferRows);
                            $emptyLinesCount = $emptyLinesCount < 0 ? 0 : $emptyLinesCount;
                        @endphp
                        @foreach ($transferRows as $row)
                            <tr >
                                <td style="text-align: center;">{{ $row->sr_no }}</td>
                              <td style="max-width: 200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
    {{ $row->branch_name }}
</td>
                                <td style="text-align: center; max-width: 50px">{{ $row->period }}</td>
                                <td style="text-align: center;">{{ $row->slip_no }}</td>
                                <td style="text-align: right;">{{ number_format($row->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @for ($i = 0; $i < $emptyLinesCount; $i++)
                            @php $sr = count($transferRows) + $i + 1; @endphp
                            <tr>
                                <td style="text-align: center;">{{ $sr <= 10 ? $sr : '' }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right;">Total</td>
                            <td style="text-align: right; border-top: 2px solid #000; border-bottom: 2px double #000;">
                                {{ number_format($dailyClosing->total_income_received, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right Side: Income Deposited -->
            <div class="col-5 ps-3">
                <div class="table-title mb-1">{{ __('Cash Deposited') }}</div>
                <table class="table-closing">
                    <thead>
                        <tr style="border-bottom: 1px double #000;">
                            <th style="width: 30%;">Value</th>
                            <th style="width: 30%;">Count</th>
                            <th style="text-align: right; width: 40%;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $denominations = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
                        @endphp
                        @foreach ($denominations as $denom)
                            @php
                                $field = 'note_' . $denom;
                                $count = $dailyClosing->$field ?? 0;
                                $subtotal = $count * $denom;
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: bold;">{{ $denom }}</td>
                                <td style="text-align: center;">{{ $count > 0 ? $count : '-' }}</td>
                                <td style="text-align: right;">{{ $subtotal > 0 ? number_format($subtotal, 2) : '-' }}</td>
                            </tr>
                        @endforeach
                        
                        <!-- Pad to match left column height exactly -->
                        @php
                            $leftTotalRows = max(25, count($transferRows));
                            $rightPaddingCount = $leftTotalRows - 10;
                        @endphp
                        @for ($i = 0; $i < $rightPaddingCount; $i++)
                            <tr>
                                <td style="color: transparent;">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor

                        <tr class="total-row">
                            <td colspan="2" style="text-align: center;">Total</td>
                            <td style="text-align: right; border-top: 2px solid #000; border-bottom: 2px double #000;">
                                {{ number_format($dailyClosing->total_income_deposited, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Difference section -->
        <div class="diff-section">
            {{ __('Difference in Income Deposited') }}:
            <span class="diff-amount">
                @if($dailyClosing->difference > 0)
                    ({{ number_format(abs($dailyClosing->difference), 2) }})
                @elseif($dailyClosing->difference < 0)
                    {{ number_format(abs($dailyClosing->difference), 2) }}
                @else
                    0.00
                @endif
            </span>
        </div>

        <!-- Signatures and Date -->
        <div class="signature-section">
            <div class="sig-box">
                <strong>{{ strtoupper($dailyClosing->issued_by ?? '') }}</strong>
                <span>Issued by: (Name/Sign)</span>
            </div>
            
            <div class="sig-box text-end">
                <strong>{{ strtoupper($dailyClosing->received_by ?? '') }}</strong>
                <span>Received by: (Name/Sign)</span>
                
            </div>
        </div>

    </div>

</body>
</html>
