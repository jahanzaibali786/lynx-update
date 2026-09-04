<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>STN</title>
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
            height: 841px;
            margin: 0 auto;
            background: #fffdf7;
            position: relative;
            overflow: hidden;
            border: 1px solid #6b5b4e;
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
            top: 6px;
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
            margin-top: 8px;
            margin-left: 74px;
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
            top: 160px;
            left: 317px;
            transform: rotate(-12deg);
            font-weight: 700;
            font-size: 64px;
            color: rgb(20 20 20 / 19%);
            letter-spacing: 2px;
            pointer-events: none;
            z-index: 1;
        }
        .watermark small { display: block; font-size: 18px; letter-spacing: 1px; text-align: center; }
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
        .box .content .small-hand { font-size: 16px; margin-top: 10px; }
        .mid-table-wrap { padding: 14px 36px 0 36px; }
        .mid-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 13px; color: #45352b; }
        .mid-table td { border: 1px solid #8b7768; padding: 7px 8px; height: 34px; vertical-align: middle; }
        .mid-table .topcell { height: 32px; background: #e6e6e6; font-weight: 700; color: #000; text-align: center; }
        .mid-table .center { text-align: center; }
        .mid-table .right { text-align: right; }
        .item-table-wrap { padding: 0 36px 0 36px; }
        .item-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 2px; font-size: 13px; }
        .item-table th, .item-table td { border: 1px solid #8b7768; padding: 4px 5px; color: #000; }
        .item-table th { background: #e6e6e6; font-weight: 700; text-align: center; color: #y; }
        .item-table td { height: 23px; }
        .qty { width: 8%; text-align: center; }
        .code { width: 14%; }
        .desc { width: 42%; }
        .um { width: 9%; text-align: center; }
        .price { width: 13%; text-align: right; }
        .amount { width: 14%; text-align: right; }
        .footer-line { position: absolute; left: 40px; right: 35px; bottom: 88px; border-top: 1px solid rgba(120, 100, 85, 0.25); }
        .signature-wrap { position: absolute; left: 40px; right: 35px; bottom: 16px; display: flex; justify-content: space-between; gap: 20px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
        /* .signature-wrap .sig { width: 48%; border-top: 1px solid #6b5b4e; padding-top: 6px; } */
        .signature-wrap .sig.right { text-align: right; }
        @page { size: A4 portrait; margin: 0; }
        @media print {
            @page { size: A4 portrait; margin: 0; }
            html, body { background: #ffffff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body * { visibility: hidden; }
            .page, .page * { visibility: visible; }
            .page { position: absolute; left: 0; top: 0; width: 100% !important; min-height: 297mm; margin: 0; border: none !important; background: #ffffff !important; }
            .paper-edge { border-color: rgba(92, 78, 68, 0.12); }
            .print-toolbar, .no-print { display: none !important; }
            .signature-wrap { position: static; margin: 100px 40px 0 40px; padding: 0; display: flex; justify-content: space-between; gap: 20px; font-family: ''Times New Roman'', Times, serif; font-size: 14px; color: #3c2f24; }
            .signature-wrap .sig { width: 48%; padding-top: 0; border-top: 0; }
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
                <div class="note-line">STOCK TRANSFER NOTE (STN)</div>
            </div>
            <div class="right-head">
                <div class="invoice-title">STN</div>
                <table class="meta-box">
                    <tr>
                        <th>Date</th>
                        <th>STN #</th>
                    </tr>
                    <tr>
                        <td>1/24/2024</td>
                        <td>514</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="watermark">PAID<small>07/29/2026</small></div>
        <div class="section-row">
            <div class="box">
                <div class="label">Transfer From</div>
                <div class="content">
                    <div>1-8 NURSERY STUDY PACK</div>
                    <div>SYED ITASSAN HASHMI</div>
                    <div class="small-hand">Head Qtr</div>
                </div>
            </div>
            <div class="box">
                <div class="label">Transfer To</div>
                <div class="content"></div>
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
                    <td class="topcell">Ship. date</td>
                    <td class="topcell">Via</td>
                    <td class="topcell">Project</td>
                </tr>
                <tr>
                    <td class="center">40-2023-24-</td>
                    <td class="center">2023-2024</td>
                    <td class="center">1/24/2024</td>
                    <td class="center">Self</td>
                    <td class="center">Study Pack</td>
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
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Condition</th>
                        <th>Qty</th>
                        <th>U/M</th>
                        <th>Price Each</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001277</td>
                        <td>The Lynx Workbbok For English NUR</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">550.00</td>
                        <td class="price">550.00</td>
                    </tr>
                    <tr>
                        <td>001286</td>
                        <td>The Lynx Workbok For Maths NUR</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">550.00</td>
                        <td class="price">550.00</td>
                    </tr>
                    <tr>
                        <td>001288</td>
                        <td>The Lynx workbook For Urdu NUR</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">550.00</td>
                        <td class="price">550.00</td>
                    </tr>
                    <tr>
                        <td>9780198406952</td>
                        <td>ORT readers A set of 6 Stage 3 (SRM)</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">1,535.00</td>
                        <td class="price">1,535.00</td>
                    </tr>
                    <tr>
                        <td>001262</td>
                        <td>Broad 4-line interleaf (Eng+Gk)</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">300.00</td>
                        <td class="price">300.00</td>
                    </tr>
                    <tr>
                        <td>001265</td>
                        <td>Broad Square Interleaf</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">2</td>
                        <td class="center">Number</td>
                        <td class="price">300.00</td>
                        <td class="price">600.00</td>
                    </tr>
                    <tr>
                        <td>001263</td>
                        <td>Broad Single Line Interleaf</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">300.00</td>
                        <td class="price">300.00</td>
                    </tr>
                    <tr>
                        <td>001287</td>
                        <td>The Lynx Tear off G-Knowledge NUR</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">550.00</td>
                        <td class="price">550.00</td>
                    </tr>
                    <tr>
                        <td>001272</td>
                        <td>The Lynx School Diary</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">2</td>
                        <td class="center">Number</td>
                        <td class="price">200.00</td>
                        <td class="price">400.00</td>
                    </tr>
                    <tr>
                        <td>001290</td>
                        <td>Diary Cover</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">50.00</td>
                        <td class="price">50.00</td>
                    </tr>
                    <tr>
                        <td>001291</td>
                        <td>Copy Cover</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">60.00</td>
                        <td class="price">60.00</td>
                    </tr>
                    <tr>
                        <td>001292</td>
                        <td>Stationary Pack</td>
                        <td class="center">N/A</td>
                        <td class="center" style="text-align: center;">1</td>
                        <td class="center">Number</td>
                        <td class="price">5,000.00</td>
                        <td class="price">5,000.00</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align:left;border-left:1px solid #8b7768;font-weight:700;">
                            Total no of items 12 / Total no of quantities 13</td>
                        <td style="text-align:center;font-weight:700;border-left:1px solid #8b7768;">Total</td>
                        <td style="text-align:right;font-weight:400;">10,445.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- <div class="footer-line"></div> --}}
        <div class="signature-wrap">
            <div class="sig">Stock issued By : _________________________</div>
            <div class="sig right">Stock Received By : __________________________</div>
        </div>
    </div>
</body>

</html>












