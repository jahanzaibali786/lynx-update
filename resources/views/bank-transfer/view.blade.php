<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funds Transfer Detail - The Lynx School</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --calibri: 'Calibri', 'Trebuchet MS', 'Segoe UI', Arial, sans-serif;
            --edwardian: 'Edwardian Script ITC';
            --fs-sm: 11pt;
            --ink: #111;
            --border: 2px solid #000;
        }

        /* ── PRINT: 10mm page margin, wrapper fills the entire printable area ── */
        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                display: block !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-wrapper {
                width: 100% !important;       /* fill the full printable width */
                max-width: 100% !important;
                padding:10  !important;         /* @page margin already provides the gap */
                margin: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            .no-print { display: none !important; }

            /* * { color: #000 !important; } */
            .col-header-row { background-color: #878787 !important; }
        }

        /* ── SCREEN ── */
        .no-print {
            position: fixed;
            top: 20px;
            right: 100px;
            z-index: 999;
        }

        .receipt-type-right {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--ink);
            text-align: right;
            position: relative;
            top: -20px;
        }

        body {
            font-family: var(--calibri);
            font-size: var(--fs-sm);
            font-weight: 400;
            background: #e0e0e0;
            display: flex;
            justify-content: center;
            padding: 30px 20px;
            color: var(--ink);
        }

        /* Screen preview: fixed width with padding to mimic the page */
        .page-wrapper {
            width: 794px;
            background: #fff;
            padding: 30px 38px;   /* ~10mm equivalent on screen */
            box-shadow: 0 2px 12px rgba(0,0,0,.18);
        }

        .separator {
            border: none;
            border-top: 2px dashed #555;
            margin: 24px 0;
        }

        /* ── HEADER ── */
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2px;
        }

        .school-info .school-name {
            font-family: var(--edwardian);
            font-size: 27px;
            font-weight: 800;
            line-height: 1.2;
        }

        .voucher-title {
            font-size: 10pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 1px;
        }

        .logo-area { height: 60px; }
        .logo-area img { height: 100%; }

        /* ── REF & DATE ── */
        .ref-date-row {
            display: flex;
            justify-content: flex-end;
            margin: 8px 0 4px 0;
        }

        .ref-date-table { border-collapse: collapse; }

        .ref-date-table td {
            padding: 1px 5px;
            font-size: var(--fs-sm);
            white-space: nowrap;
        }

        .ref-date-table td.lbl { font-weight: 700; text-align: right; }
        .ref-date-table td.val { font-weight: 400; text-align: right; }

        /* ══════════════════════════════════
           VOUCHER TABLE
        ══════════════════════════════════ */
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--fs-sm);
            margin-bottom: 8px;
        }

        .col-left          { width: 42%; }
        .col-debit         { width: 13%; }
        .col-right-section { width: 31%; }
        .col-credit        { width: 14%; }

        .voucher-table td,
        .voucher-table th {
            padding: 3px 6px;
            vertical-align: top;
            border: none;
        }

        /* Bank header row — top border to open the block */
        .bank-header-row { border-top: var(--border); }

        .voucher-table .bank-header-row td {
            font-weight: 700;
            font-size: 10pt;
            padding: 3px 0 4px 0;
        }

        /* Bank detail row — bottom border to close the block */
        .voucher-table .bank-detail-row td {
            font-size: 10pt;
            padding: 0 0 4px 0;
            border-bottom: var(--border);
        }

        .voucher-table .bank-header-row td.right-bank,
        .voucher-table .bank-detail-row td.right-bank {
            text-align: right;
        }

        /* Column header row (grey) */
        .voucher-table .col-header-row td {
            background-color: #878787;
            color: #ebeaea;
            font-weight: 700;
            font-size: 10pt;
            padding: 2px 6px;
            border: var(--border);
        }

        .voucher-table .col-header-row td.left  { text-align: left; }
        .voucher-table .col-header-row td.right { text-align: right; }

        /* Data rows */
        .voucher-table .data-row td { font-size: var(--fs-sm); }

        .voucher-table .data-row td:first-child  { border-left: var(--border); border-right: var(--border); }
        .voucher-table .data-row td:nth-child(2) { border-right: var(--border); }
        .voucher-table .data-row td:nth-child(3) { border-right: var(--border); }
        .voucher-table .data-row td:last-child   { border-right: var(--border); }

        .voucher-table .data-row:last-child td   { border-bottom: var(--border); }

        /* closing balance row gets a top border too */
        .voucher-table .data-row.closing-row td:nth-child(3),
        .voucher-table .data-row.closing-row td:nth-child(4) {
            border-top: var(--border);
        }

        .voucher-table .data-row td.amount { text-align: right; }

        /* Amount in words */
        .words-row {
            font-size: var(--fs-sm);
            margin: 6px 0 16px 0;
        }


        .words-row span { text-decoration: underline; }

        /* Signatures */
        .signature-section { margin-top: 20px; }

        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .sig-block {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 190px;
        }

        .sig-line {
            align-self: stretch;
            border-bottom: 1.2px solid var(--ink);
            height: 24px;
            margin-bottom: 3px;
        }

        .sig-label {
            font-size: var(--fs-sm);
            font-weight: 700;
            text-decoration: underline;
        }

        .sig-sublabel {
            font-size: 9pt;
            font-weight: 400;
            color: #444;
            margin-top: 1px;
        }

        .checked-row { display: flex; justify-content: flex-start; }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()"
            style="padding:8px 18px;margin-right:10px;cursor:pointer;border:none;background-color:#03f517;color:#fff;border-radius:5px;">
            <i class="fa-solid fa-print"></i>
        </button>
        <button onclick="downloadPDF()"
            style="padding:8px 18px;cursor:pointer;border:none;background-color:#e51b32;color:#fff;border-radius:5px;">
            <i class="fa-solid fa-download"></i>
        </button>
    </div>

    <div class="page-wrapper">

       @php
            if (!function_exists('numberToWords')) {
                function numberToWords($number)
                {
                    $ones = [
                        '',
                        'One',
                        'Two',
                        'Three',
                        'Four',
                        'Five',
                        'Six',
                        'Seven',
                        'Eight',
                        'Nine',
                        'Ten',
                        'Eleven',
                        'Twelve',
                        'Thirteen',
                        'Fourteen',
                        'Fifteen',
                        'Sixteen',
                        'Seventeen',
                        'Eighteen',
                        'Nineteen',
                    ];

                    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

                    $n = (int) $number;

                    if ($n == 0) {
                        return 'Zero';
                    }

                    $words = '';

                    if ($n >= 10000000) {
                        $words .= numberToWords((int) ($n / 10000000)) . ' Crore ';
                        $n %= 10000000;
                    }

                    if ($n >= 100000) {
                        $words .= numberToWords((int) ($n / 100000)) . ' Lakh ';
                        $n %= 100000;
                    }

                    if ($n >= 1000) {
                        $words .= numberToWords((int) ($n / 1000)) . ' Thousand ';
                        $n %= 1000;
                    }

                    if ($n >= 100) {
                        $words .= $ones[(int) ($n / 100)] . ' Hundred ';
                        $n %= 100;
                    }

                    if ($n >= 20) {
                        $words .= $tens[(int) ($n / 10)] . ' ';
                        $n %= 10;
                    }

                    if ($n > 0) {
                        $words .= $ones[$n] . ' ';
                    }

                    return trim($words);
                }
            }
            $amountWords = numberToWords($debit_amount ?? 0) . ' rupees only/-';
            $fmt = function ($val) {
                $val = (float) ($val ?? 0);
                return ($val < 0 ? '-' : '') . number_format(abs($val), 2);
            };
        @endphp

        {{-- ══════════════════════════════════ --}}
        {{--           SENDER'S COPY              --}}
        {{-- ══════════════════════════════════ --}}
        <div class="receipt">
            <div class="receipt-type-right">SENDER'S COPY</div>

            <div class="receipt-header">
                <div class="school-info">
                    <div class="school-name">The Lynx School</div>
                    <div class="voucher-title">IBFT Voucher</div>
                </div>
                <div class="logo-area">
                    <img src="{{ asset('assets/images/lynxLogo.png') }}" alt="logo">
                </div>
            </div>

            <div class="ref-date-row">
                <table class="ref-date-table">
                    <tr>
                        <td class="lbl">Ref:</td>
                        <td class="val">{{ $ref ?? '0000000' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Date:</td>
                        <td class="val">{{ $date ?? now()->format('l, F j, Y') }}</td>
                    </tr>
                </table>
            </div>

            <table class="voucher-table">
                <colgroup>
                    <col class="col-left">
                    <col class="col-debit">
                    <col class="col-right-section">
                    <col class="col-credit">
                </colgroup>

                <tr class="bank-header-row">
                    <td colspan="2"><u>Receiver Bank</u></td>
                    <td colspan="2" class="right-bank"><u>Sender Bank</u></td>
                </tr>
                <tr class="bank-detail-row">
                    <td colspan="2">
                        <strong>Account Title:</strong> {{ $toBankAccount->bank_name ?? '' }}<br>
                        <strong>Account No:</strong> {{ $toBankAccount->account_number ?? '' }}
                    </td>
                    <td colspan="2" class="right-bank">
                        <strong>Account Title:</strong> {{ $fromBankAccount->bank_name ?? '' }}<br>
                        <strong>Account No:</strong> {{ $fromBankAccount->account_number ?? '' }}
                    </td>
                </tr>

                <tr class="col-header-row">
                    <td class="left">Transferred In Account</td>
                    <td class="right">Debit <span style="font-size:0.7rem;">(Rs)</span></td>
                    <td class="left">Transferred Out Account</td>
                    <td class="right">Credit <span style="font-size:0.7rem;">(Rs)</span></td>
                </tr>

                <tr class="data-row">
                    <td>{{ $debit_account }}</td>
                    <td class="amount">{{ number_format($debit_amount ?? 0, 2) }}</td>
                    <td><b>Opening Balance</b></td>
                    <td class="amount"><b>{{ number_format($opening_balance ?? 0, 2) }}</b></td>
                </tr>
                <tr class="data-row">
                    <td></td>
                    <td></td>
                    <td>Funds Transferred</td>
                    <td class="amount">{{ number_format($credit_amount ?? 0, 2) }}</td>
                </tr>
                <tr class="data-row closing-row">
                    <td></td>
                    <td></td>
                    <td><b>Closing Balance</b></td>
                    <td class="amount"><b>{{ $fmt($closing_balance) }}</b></td>
                </tr>
            </table>

            <div class="words-row">
                <b>Received Rs. in Words:</b>&nbsp;<span>{{ ucwords(strtolower($amountWords)) }}</span>
            <br>
            <br>
                <b>Remarks:</b> {{ $transfer->description }}
            </div>
              
            <div class="signature-section">
                <div class="sig-row">
                     <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Issued by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accountant</div>
                    </div>
                    <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Received by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accountant</div>
                    </div>
                   
                {{-- </div> --}}
                {{-- <div class="checked-row"> --}}
                    <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Checked by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accounts Department (HO)</div>
                    </div>
                </div>
            </div>
        </div>{{-- /HO receipt --}}


        <hr class="separator">


        {{-- ══════════════════════════════════ --}}
        {{--        RECEIVER'S COPY             --}}
        {{-- ══════════════════════════════════ --}}
        <div class="receipt">
            <div class="receipt-type-right">RECEIVER'S COPY</div>

            <div class="receipt-header">
                <div class="school-info">
                    <div class="school-name">The Lynx School</div>
                    <div class="voucher-title">IBFT Voucher</div>
                </div>
                <div class="logo-area">
                    <img src="{{ asset('assets/images/lynxLogo.png') }}" alt="logo">
                </div>
            </div>

            <div class="ref-date-row">
                <table class="ref-date-table">
                    <tr>
                        <td class="lbl">Ref:</td>
                        <td class="val">{{ $ref ?? '0000000' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Date:</td>
                        <td class="val">{{ $date ?? now()->format('l, F j, Y') }}</td>
                    </tr>
                </table>
            </div>

            <table class="voucher-table">
                <colgroup>
                    <col class="col-left">
                    <col class="col-debit">
                    <col class="col-right-section">
                    <col class="col-credit">
                </colgroup>

                <tr class="bank-header-row">
                    <td colspan="2"><u>Receiver Bank</u></td>
                    <td colspan="2" class="right-bank"><u>Sender Bank</u></td>
                </tr>
                <tr class="bank-detail-row">
                    <td colspan="2">
                        <strong>Account Title:</strong> {{ $toBankAccount->bank_name ?? '' }}<br>
                        <strong>Account No:</strong> {{ $toBankAccount->account_number ?? '' }}
                    </td>
                    <td colspan="2" class="right-bank">
                        <strong>Account Title:</strong> {{ $fromBankAccount->bank_name ?? '' }}<br>
                        <strong>Account No:</strong> {{ $fromBankAccount->account_number ?? '' }}
                    </td>
                </tr>

                <tr class="col-header-row">
                    <td class="left">Transferred In Account</td>
                    <td class="right">Debit <span style="font-size:0.7rem;">(Rs)</span></td>
                    <td class="left">Transferred Out Account</td>
                    <td class="right">Credit <span style="font-size:0.7rem;">(Rs)</span></td>
                </tr>

                <tr class="data-row">
                    <td>{{ $debit_account }}</td>
                    <td class="amount">{{ number_format($debit_amount ?? 0, 2) }}</td>
                    <td><b>Opening Balance</b></td>
                    <td class="amount"><b>{{ number_format($opening_balance ?? 0, 2) }}</b></td>
                </tr>
                <tr class="data-row">
                    <td></td>
                    <td></td>
                    <td>Funds Transferred</td>
                    <td class="amount">{{ number_format($credit_amount ?? 0, 2) }}</td>
                </tr>
                <tr class="data-row closing-row">
                    <td></td>
                    <td></td>
                    <td><b>Closing Balance</b></td>
                    <td class="amount"><b>{{ $fmt($closing_balance) }}</b></td>
                </tr>
            </table>

            <div class="words-row">
                <b>Received Rs. in Words:</b>&nbsp;<span>{{ ucwords(strtolower($amountWords)) }}</span>
            <br>
            <br>
                    <b>Remarks:</b> {{ $transfer->description }}
            </div>
             
            <div class="signature-section">
                <div class="sig-row">
                     <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Issued by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accountant</div>
                    </div>
                    <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Received by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accountant</div>
                    </div>
                   
                {{-- </div> --}}
                {{-- <div class="checked-row"> --}}
                    <div class="sig-block" style="align-items:center;">
                        <div class="sig-line"></div>
                        <div class="sig-label">Checked by: (Name/Sign)</div>
                        <div class="sig-sublabel">Accounts Department (HO)</div>
                    </div>
                </div>
            </div>
        </div>{{-- /Branch receipt --}}

    </div>{{-- /page-wrapper --}}

    <script>
        function downloadPDF() {
            const element = document.querySelector('.page-wrapper');
            const opt = {
                margin: [10, 10, 10, 10],   /* 10mm on all sides */
                filename: "{{ 'Funds-Transfer-Detail-' . ($ref ?? '000000') }}.pdf",
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 3, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>