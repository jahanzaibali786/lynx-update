<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($settings['stock_transfer_note_prefix'] ?? '#STN') . sprintf('%05d', $invoice->stn_id) }}</title>
        <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: ''Times New Roman'', Times, serif;
            background: #efefef;
            color: #3c2f24;
        }
        .page {
            width: 841px;
            min-height: 841px;
            height: auto;
            margin: 0 auto;
            background: #fffdf7;
            position: relative;
            overflow: visible;
            border: 1px solid #6b5b4e;
            padding-bottom: 130px;
        }
        .paper-edge {
            position: absolute;
            inset: 0;
            pointer-events: none;
            border: 1px solid rgba(92, 78, 68, 0.5);
        }
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 24px 24px 0 26px;
        }
        .brand-block {
            position: relative;
            min-height: 96px;
            width: 430px;
        }
        .brand-logo {
            position: absolute;
    left: 0;
    top: 21px;
    width: 70px;
    height: auto;
        }
        .brand-text {
            display: block;
            margin-left: 78px;
            margin-top: 16px;
            width: 250px;
            height: auto;
        }
        .note-line {
            margin-top: 5px;
    margin-left: 85px;
    font-family: ''Times New Roman'', Times, serif;
    font-size: 18px;
    color: #000;
    display: inline-block;
    padding-bottom: 0;
    border-bottom: 0;
    white-space: nowrap;
        }
        .right-head { width: 220px; text-align: right; }
        .invoice-title {
            font-family: ''Times New Roman'', Times, serif;
            font-size: 30px;
            font-weight: 700;
            line-height: 1;
            color: #2d2720;
        }
        .meta-box {
            width: 218px;
            border: 2px solid #8b7768;
            margin-top: 10px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 13px;
            color: #3b2f25;
        }
        .meta-box th, .meta-box td {
            border: 1px solid #8b7768;
            padding: 6px 8px;
            text-align: center;
            font-weight: 400;
        }
        .stamp {
            position: absolute;
            top: 146px;
            left: 318px;
            transform: rotate(-22deg);
            color: #232020;
            font-weight: 700;
            font-size: 32px;
            opacity: 0.22;
            text-align: center;
            line-height: 0.9;
            pointer-events: none;
        }
        .stamp span { display: block; font-size: 14px; letter-spacing: 1px; }
        .watermark {
            position: absolute;
            bottom: 25px;
            left: 300px;
            transform: rotate(-12deg);
            font-weight: 600;
            font-size: 33px;
            color: rgb(20 20 20 / 15%);
            letter-spacing: 1px;
            pointer-events: none;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* align-items: center; */
            text-align: left;
        }
        .section-row { display: flex; justify-content: space-between; gap: 18px; padding: 52px 36px 0 36px; }
        .box {
            width: calc(50% - 9px);
            height: 100px;
            border: 2px solid #8b7768;
            position: relative;
            background: rgba(255, 255, 255, 0.3);
        }
        .box .label {
            position: absolute;
            top: -12px;
            left: 10px;
            background: #fffdf7;
            padding: 0 6px;
            font-family: ''Times New Roman'', Times, serif;
            font-size: 15px;
            font-weight: 600;
            color: #39332f;
        }
        .box .content {
            padding: 10px;
            font-family: ''Times New Roman'', Times, serif;
            color: #5a4637;
            font-size: 14px;
            line-height: 1.5;
        }
        .signature-wrap b {
            font-weight: 900;
            font-size: larger;
        }
        .box .content .small-hand { font-size: 16px; margin-top: 1px; }
        .mid-table-wrap { padding: 14px 36px 0 36px; }
        .mid-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 13px; color: #45352b; }
        .mid-table td { border: 1px solid #8b7768; padding: 7px 8px; height: 34px; vertical-align: middle; }
        .mid-table .topcell { height: 32px; background: #e6e6e6; font-weight: 700; color: #000; text-align: center; }
        .mid-table .center { text-align: center; }
        .mid-table .right { text-align: right; }
        .mid-table .left { text-align: left; }
        .item-table-wrap { padding: 0 36px 0 36px; }
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 2px; font-size: 13px; }
        .item-table th, .item-table td { border: 1px solid #8b7768; padding: 4px 5px; color: #000; }
        .item-table th { background: #e6e6e6; font-weight: 700; text-align: center; color: #y; }
        .item-table td { height: 23px; }
        .item-table thead { display: table-header-group; }
        .item-table tbody { display: table-row-group; }
        .item-table tr { break-inside: avoid; page-break-inside: avoid; }
        .qty { width: 8%; text-align: center; }
        .code { width: 14%; }
        .desc { width: 42%; }
        .um { width: 9%; text-align: center; }
        .price { width: 13%; text-align: right; }
        .amount { width: 14%; text-align: right; }
        .footer-line { position: absolute; left: 40px; right: 35px; bottom: 88px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
        .signature-wrap { position: absolute; left: 40px; right: 35px; bottom: 16px; display: flex; justify-content: space-between; gap: 20px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
        /* .signature-wrap .sig { width: 48%; border-top: 1px solid #6b5b4e; padding-top: 6px; } */
        .signature-wrap .sig.right { text-align: right; }
        @page {
            size: A4 portrait;
            margin: 15mm 0 14mm 0;

            @bottom-left {
                content: "System Generated: {{ now()->format('d M Y h:i A') }}";
                border-top: 1px solid #8b7768;
                padding-left: 36px;
                font-family: "Times New Roman", Times, serif;
                font-size: 9pt;
                color: #3c2f24;
                text-align: left;
            }

            @bottom-center {
                content: "";
                border-top: 1px solid #8b7768;
            }

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                border-top: 1px solid #8b7768;
                padding-right: 36px;
                font-family: "Times New Roman", Times, serif;
                font-size: 9pt;
                color: #3c2f24;
                text-align: right;
            }
        }
        @page :first { margin-top: 0; }
        @media print {
            html, body { background: #ffffff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body * { visibility: hidden; }
            .page, .page * { visibility: visible; }
            .page { position: relative; left: auto; top: auto; width: 100% !important; min-height: 297mm; height: auto !important; overflow: visible !important; margin: 0; padding-bottom: 0; border: none !important; background: #ffffff !important; }
            .paper-edge { border-color: rgba(92, 78, 68, 0.12); }
            .print-toolbar, .no-print { display: none !important; }
            .item-table-wrap { overflow: visible !important; }
            .item-table { page-break-inside: auto; }
            .item-table thead { display: table-header-group; }
            .item-table tbody { display: table-row-group; }
            .item-table tr { break-inside: avoid; page-break-inside: avoid; }
            .footer-line { position: static; margin: 20px 40px 0 40px; padding: 0; display: flex; justify-content: space-between; gap: 20px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
            .footer-line, .signature-wrap { break-inside: avoid; page-break-inside: avoid; }
            .signature-wrap  { position: static; margin: 100px 40px 0 40px; padding: 0; display: flex; justify-content: space-between; gap: 20px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
            .signature-wrap .sig { width: 48%; padding-top: 0; border-top: 0; }
            .watermark {
                bottom: 50px;
            }
        }
    </style></style>
</head>

<body>
    {{-- //button for print. --}}
    <button onclick="window.print()" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">Print</button>
    <div class="page">  
        <div class="paper-edge"></div>
        <div class="top-row">
            <div class="brand-block">
                <img class="brand-logo" src="{{ asset('assets/images/lynxlogo(2).png') }}" alt="Lynx Logo">
                <img class="brand-text" src="{{ asset('assets/images/lynxheadertext.png') }}" alt="The Lynx School">
                <div class="note-line">STOCK TRANSFER NOTE</div>
            </div>
            <div class="right-head">
                <div class="invoice-title">STN</div>
                <table class="meta-box">
                    <tr>
                        <th>Date</th>
                        <th>STN #</th>
                    </tr>
                    <tr>
                        <td>{{ \App\Models\Utility::dateFormat($settings, $invoice->issue_date) }}</td>
                        <td>{{ ($settings['stock_transfer_note_prefix'] ?? '#STN') . sprintf('%05d', $invoice->stn_id) }}</td>
                    </tr>
                </table>
            </div>
        </div>


        <div class="section-row">
            <div class="box">
                <div class="label">Transfer From</div>
                <div class="content">
                    <div>{{ $invoice->fromStore->name ?? '-' }}</div>
                    <div>{{ $invoice->fromStore->branch->name ?? '-' }}</div>
                    <div class="small-hand">{{ $invoice->fromStore->address ?? '-' }}</div>
                </div>
            </div>
            <div class="box">
                <div class="label">Transfer To</div>
                <div class="content">
                    <div>{{ $invoice->toStore->name ?? '-' }}</div>
                    <div>{{ $invoice->toStore->branch->name ?? '-' }}</div>
                    <div class="small-hand">{{ $invoice->toStore->address ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="mid-table-wrap">
            <table class="mid-table">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                </colgroup>
                <tr>
                    <td class="topcell">STO #</td>
                    <td class="topcell">Session</td>
                    <td class="topcell">Delivery Date</td>
                    <td class="topcell">Via</td>
                    <td class="topcell">Project</td>
                </tr>
                <tr>
<td class="center">
    {{ $invoice->sto_id
        ? ($settings['stock_transfer_order_prefix'] ?? 'STO') . sprintf('%05d', $invoice->sto_id)
        : $invoice->ref_number }}
</td>                    <td class="center">{{ $session ?? '-' }}</td>
                    <td class="center">{{ \App\Models\Utility::dateFormat($settings, $invoice->issue_date) }}</td>
                    <td class="center">{{ $invoice->shipping_via ?: '-' }}</td>
                    <td class="center">{{ $invoice->stn_type ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="item-table-wrap">
            <table class="item-table">
                <colgroup>
                    <col class="code">
                    <col class="desc">
                    <col style="width: 11%;">
                    <col class="qty">
                    <col class="um">
                    <col class="price">
                    <col class="amount">
                </colgroup>
                <thead>
                    <tr>
                        <th style="text-align: left;">Item Code</th>
                        <th style="text-align: left;">Description</th>
                        <th>Status</th>
                        <th>Qty</th>
                        <th>U/M</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedPrintItems = $invoice->items->groupBy(function ($item) {
                            return !empty($item->study_pack_id) ? 'group_' . $item->study_pack_id : 'item_' . $item->id;
                        });
                        $printedRowCount = 0;
                    @endphp
                    @foreach ($groupedPrintItems as $printGroup)
                        @php
                            $firstPrintItem = $printGroup->first();
                            $isStudyPackPrintGroup = !empty($firstPrintItem->study_pack_id);
                            $groupRowCount = $printGroup->count();
                            $groupQtyTotal = $printGroup->sum('quantity');
                            $groupUnitPriceTotal = $printGroup->sum('price');
                            $groupAmountTotal = $printGroup->sum(function ($groupItem) {
                                return (float) $groupItem->price * (float) $groupItem->quantity;
                            });
                        @endphp
                        @if ($isStudyPackPrintGroup)
                            <tr>
                                <td colspan="7" style="font-weight:700;background:#f5f5f5;text-align:left;">
                                    {{ $firstPrintItem->study_pack_title ?: 'Study Pack' }}
                                </td>
                            </tr>
                            @php $printedRowCount++; @endphp
                        @endif
                        @foreach ($printGroup as $item)
                            <tr>
                                <td>{{ $item->product->sku ?? '-' }}</td>
                                <td>{{ $item->product->name ?? $item->description ?? '-' }}</td>
                                <td class="center" style="text-align: center;">{{ ucfirst($item->type ?? 'new') }}</td>
                                <td class="center" style="text-align: center;">{{ $item->quantity }}</td>
                                <td class="center" style="text-align: center;">{{ $unitNames[$item->product->unit_id ?? 0] ?? '-' }}</td>
                                <td class="price">{{ number_format($item->price, 2) }}</td>
                                <td class="price">{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @php $printedRowCount++; @endphp
                        @endforeach
                        @if ($isStudyPackPrintGroup)
                            <tr>
                                <td  style="text-align:left;font-weight:700;background:#fafafa;">
                                    Items {{ $groupRowCount }}                                </td>
                                <td  style="text-align:left;font-weight:700;background:#fafafa;">
                                    Group Total
                                </td>
                                <td class="center" style="text-align:center;font-weight:700;background:#fafafa;">-</td>
                                <td class="center" style="text-align:center;font-weight:700;background:#fafafa;">
                                    {{ number_format($groupQtyTotal, 0) }}
                                </td>
                                <td class="center" style="text-align:center;font-weight:700;background:#fafafa;">-</td>
                                <td class="price" style="font-weight:700;background:#fafafa;">
                                    {{ number_format($groupUnitPriceTotal, 2) }}
                                </td>
                                <td class="price" style="font-weight:700;background:#fafafa;">
                                    {{ number_format($groupAmountTotal, 2) }}
                                </td>
                            </tr>
                            @php $printedRowCount++; @endphp
                        @endif
                    @endforeach
                    @for ($row = $printedRowCount; $row < 12; $row++)
                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td class="center"></td>
                            <td class="center" style="text-align: center;"></td>
                            <td class="center"></td>
                            <td class="price"></td>
                            <td class="price"></td>
                        </tr>
                    @endfor
                    <tr>
                        <td colspan="5" style="text-align:left;border-left:1px solid #8b7768;font-weight:700;">
                            Total item {{ $invoice->items->count() }} / Total quantity {{ number_format($invoice->items->sum('quantity'), 0) }}</td>
                        <td style="text-align:center;font-weight:700;border-left:1px solid #8b7768;">Total</td>
                        <td style="text-align:right;font-weight:400;">{{ number_format($invoice->getTotal(), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer-line">
            ALL STOCK HAS BEEN CHECKED AND VERIFIED BY THE RECEIVER AS PER THE INVOICE.
        </div>
        <div class="signature-wrap">
            <div class="sig"><b>Stock issued By : </b> <span style="border-bottom:2px solid black;">{{ $issuedBy->name ?? '_________________________' }}</span></div>
            <div class="sig right"><b>Stock Received By : </b> <span style="border-bottom:2px solid black;"> {{ $receivedBy->name ?? '__________________________' }} </span></div>
            <div class="watermark"><div>{{ strtoupper(\App\Models\StockTransferNote::$statues[$invoice->status] ?? '') }}</div><div style="font-size: 20px;text-align: center;">{{ \App\Models\Utility::dateFormat($settings, $invoice->issued_at ?? $invoice->approve_date ?? $invoice->rejected_at ?? $invoice->forwarded_at ?? $invoice->issue_date) }}</div></div>
        </div>
    </div>
</body>

</html>
