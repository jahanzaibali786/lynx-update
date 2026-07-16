<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Student Final Settlement Certificate') }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 2;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .certificate-wrapper {
            width: 100%;
            max-width: 180mm;
            margin: 0 auto;
            background: #fff;
        }
        .certificate-content {
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-table .logo-cell {
            width: 75px;
            text-align: right;
        }
        .header-table .logo-cell img {
            width: 90px;
            height: 70px;
        }
        .header-table .title-cell {
            text-align: center;
        }
        .header-table .school-name img {
            height: 40px;
            width: auto;
        }
        .header-table .cert-title {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.info-table td {
            padding: 1px 3px;
            vertical-align: top;
        }
        table.info-table .label {
            font-weight: bold;
            text-align: left;
        }
        table.info-table .rvalue {
            text-align: right;
            padding-left: 8px;
        }
        .section-title-bar {
            background: #e0e0e0;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            padding: 3px 0;
            border: 1px solid #000;
            margin: 6px 0 3px 0;
        }
        .bold {
            font-weight: bold;
        }
        .signature-section {
            margin-top: 28px;
            text-align: center;
        }
        .signature-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-section td {
            text-align: center;
            padding: 40px 8px 0 8px;
        }
        .signature-section .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .acknowledgement {
            margin-top: 14px;
            border: 1px solid #000;
            padding: 8px 12px;
        }
        .acknowledgement .ack-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .acknowledgement p {
            font-style: italic;
            margin: 3px 0;
        }
        .acknowledgement .sig-line {
            border-bottom: 1px solid #000;
            width: 180px;
            display: inline-block;
            margin-left: 4px;
        }
        .float-right {
            float: right;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .status-label {
            font-weight: bold;
        }
        .blank-line-table {
            width: 100%;
            
            border-collapse: collapse;
        }
        .blank-line-table td.label {
            font-weight: bold;
            white-space: nowrap;
            vertical-align: top;
            padding: 1px 0 1px 5px;
            width: auto;
        }
        .blank-line-table td.blank {
            /* border-bottom: 1px solid #000; */
            width: 100%;
        }
        .adj-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .adj-table td {
            padding: 1px 3px;
            vertical-align: top;
        }
        .mt-1 { margin-top: 2px; }
        .mt-2 { margin-top: 4px; }
        .mt-3 { margin-top: 6px; }
        .net-row td { background-color: #e0e0e0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-content">

            {{-- HEADER --}}
            <table class="header-table">
                <tr>
                    <td class="title-cell">
                        <div class="school-name"><img src="{{ public_path('assets/images/lynxheadertext.jpg') }}" alt="The Lynx School"></div>
                        <div class="cert-title">Student Final Settlement Certificate</div>
                    </td>
                    <td class="logo-cell">
                        <img src="{{ public_path('assets/images/lynx2.jpg') }}" alt="Logo">
                    </td>
                </tr>
            </table>

            {{-- FIRST INFORMATION SECTION --}}
            <table class="info-table mt-2" style="table-layout:fixed;">
                <tr>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr><td class="label" style="width:40%; white-space:nowrap;">Branch</td><td class="rvalue" style="width:60%; white-space:nowrap;">{{ $branch->name ?? '-' }}</td></tr>
                            <tr><td class="label">Withdrawal Order No</td><td class="rvalue">{{ $withdrawal->id ?? '-' }}</td></tr>
                            <tr><td class="label">Student Name</td><td class="rvalue">{{ $student->stdname ?? '-' }}</td></tr>
                            <tr><td class="label">Father Name</td><td class="rvalue">{{ $student->fathername ?? '-' }}</td></tr>
                            <tr><td class="label">Admission Challan #</td><td class="rvalue">{{ $admissionChallanNo }}</td></tr>
                            <tr><td class="label">Admission Date</td><td class="rvalue">{{ $enrollment && $enrollment->adm_date ? \Carbon\Carbon::parse($enrollment->adm_date)->format('d M Y') : '-' }}</td></tr>
                            <tr><td class="label" style="white-space:nowrap;">Withdrawal Application Date</td><td class="rvalue">{{ $withdrawal->apply_date ? \Carbon\Carbon::parse($withdrawal->apply_date)->format('d M Y') : '-' }}</td></tr>
                            <tr><td class="label" style="white-space:nowrap;">Reason of Leaving by Parents</td><td class="rvalue">{{ $withdrawal->reason ?? '-' }}</td></tr>
                        </table>
                    </td>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr><td class="label" style="width:55%;">Print Date</td><td class="rvalue" style="width:45%;">{{ now()->format('d M Y') }}</td></tr>
                            <tr><td class="label">Roll No</td><td class="rvalue">{{ $student->roll_no ?? '-' }}</td></tr>
                            <tr><td class="label">Mother Name</td><td class="rvalue">{{ $student->mothername ?? '-' }}</td></tr>
                            <tr><td class="label">Admission Class</td><td class="rvalue">{{ $enrollment && $enrollment->class ? $enrollment->class->name : ($class->name ?? '-') }}</td></tr>
                            <tr><td class="label" style="white-space:nowrap;">Admission Branch</td><td class="rvalue" style="white-space:nowrap;">{{ $admissionBranch ?? '-' }}</td></tr>
                            <tr><td class="label" style="white-space:nowrap;">Withdrawal Application Received On</td><td class="rvalue">{{ $withdrawal->withdraw_date ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('d M Y') : '-' }}</td></tr>
                            <tr><td class="label">Current Class</td><td class="rvalue">{{ $class->name ?? '-' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- REASON OF LEAVING BY SCHOOL --}}
            <table class="blank-line-table mt-1">
                <tr><td class="label">Reason of Leaving by School</td><td class="blank">&nbsp;</td></tr>
                {{-- <tr><td class="label">&nbsp;</td><td class="blank">&nbsp;</td></tr> --}}
            </table>

            {{-- SECURITY / FINANCIAL --}}
            <table class="info-table mt-2" style="table-layout:fixed;">
                <tr>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr><td class="label">Security Deposit</td><td class="rvalue">Rs. {{ number_format($securityDeposit, 2) }}</td></tr>
                            <tr><td class="label">Security Deposit Date</td><td class="rvalue">{{ $securityDepositDate ?: 'N/A' }}</td></tr>
                            <tr><td class="label">Last Billing Generated</td><td class="rvalue">{{ $lastPaidChallan ? strtoupper(\Carbon\Carbon::parse($lastPaidChallan->fee_month)->format('M Y')) : '-' }}</td></tr>
                            <tr><td class="label">Fee Paid Upto</td><td class="rvalue">{{ $lastReceiptDate ?  \Carbon\Carbon::parse($lastReceiptDate)->format('d M Y') : 'N/A' }}</td></tr>
                        </table>
                    </td>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr><td class="label">Challan #</td><td class="rvalue">{{ $securityChallanNo ?? '-' }}</td></tr>
                            <tr><td class="label">Last Date of Attendance</td><td class="rvalue">{{ $withdrawal->withdraw_date ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('d M Y') : '-' }}</td></tr>
                            <tr><td colspan="2" style="text-align:left;">{{ $lastPaidChallan ? 'Rs. ' . number_format(($lastPaidChallan->total_amount - $lastPaidChallan->concession_amount), 2) : '-' }}</td></tr>
                            <tr><td colspan="2" style="text-align:left;">{{ $lastPaidChallan ? 'Rs. ' . number_format($lastReceiptAmount, 2) : '-' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- ADJUSTMENTS --}}
            <div class="section-title-bar">ADJUSTMENTS</div>

            <table class="adj-table">
                <tr>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr><td class="label">Outstanding Fee</td><td class="rvalue">Rs. {{ number_format($arrearsTotal, 2) }}</td></tr>
                            <tr><td class="label">Other Deduction</td><td class="rvalue">Rs. {{ number_format($otherDeduction, 2) }}</td></tr>
                            <tr><td class="label">Payable / Receivable</td><td class="rvalue">Rs. {{ number_format($netBalance, 2) }}</td></tr>
                            <tr><td class="label">Excess Fee Refund</td><td class="rvalue">Rs. {{ number_format($excessRefund, 2) }}</td></tr>
                            <tr><td class="label">Other Refund</td><td class="rvalue">Rs. 0.00</td></tr>
                            <tr class="net-row"><td class="label">Net Payable / Receivable</td><td class="rvalue">Rs. {{ number_format($netBalance, 2) }}</td></tr>
                        </table>
                    </td>
                    <td style="width:50%;">
                        <table class="info-table">
                            <tr>
                                <td style="color:#fff;">-</td>
                            </tr>
                            <tr>
                                <td style="color:#fff;">-</td>
                                
                            </tr>
                            <tr>
                                <td style="color:#fff;">-</td>
                                
                            </tr>
                            <tr>
                                <td style="color:#fff;">-</td>
                            </tr>
                            <tr>
                                <td style="color:#fff;">-</td>
                            </tr>
                            <tr>
                                <td class="label">
                                    @if($netBalance < 0)<strong>RECEIVABLE</strong>@elseif($netBalance > 0)<strong>PAYABLE</strong>@endif
                                </td>
                            </tr>
                        </table>
                </tr>
            </table>

            {{-- HO REMARKS --}}
            <table class="blank-line-table mt-1">
                <tr><td class="label">HO Remarks</td><td class="blank">{{  $withdrawal->ho_remarks ?? '' }}&nbsp;</td></tr>
                <tr><td class="label">&nbsp;</td><td class="blank">&nbsp;</td></tr>
            </table>

            <div style="height:10px;"></div>

            {{-- PAYMENT DETAILS --}}
            <table class="info-table mt-2" style="table-layout:fixed;">
                <tr>
                    <td style="width:50%;">
                        <table class="info-table" style="table-layout:auto;">
                            <tr><td class="label" style="white-space:nowrap; padding-right:0;">Cheque #</td><td style="border-bottom:1px solid #000; width:100%; padding-left:0;">&nbsp;</td></tr>
                            <tr><td class="label" style="white-space:nowrap; padding-right:0;">Beneficiary Name</td><td style="border-bottom:1px solid #000; padding-left:0;">&nbsp;</td></tr>
                        </table>
                    </td>
                    <td style="width:50%;">
                        <table class="info-table" style="table-layout:auto;">
                            <tr><td class="label" style="white-space:nowrap; padding-right:0;">Bank</td><td style="border-bottom:1px solid #000; width:100%; padding-left:0;">&nbsp;</td></tr>
                            <tr><td class="label" style="white-space:nowrap; padding-right:0;">Date</td><td style="border-bottom:1px solid #000; padding-left:0;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
                <br>
                <br>
                <br>
            {{-- SIGNATURE --}}
            <div class="signature-section">
                <table>
                    <tr>
                        <td><div class="sig-line"></div>Prepared By</td>
                        <td><div class="sig-line"></div>Checked By</td>
                        <td><div class="sig-line"></div>Approved By</td>
                        <td><div class="sig-line"></div>Auditor</td>
                    </tr>
                </table>
            </div>

            {{-- ACKNOWLEDGEMENT --}}
            <div class="acknowledgement">
                <div class="ack-title">Acknowledgement of Receipt</div>
                <p>
                    I ___________________________________________________________ do hereby confirm that I have received my entire dues from
                    The Lynx School, SMC Pvt Ltd and I have no claim on the school.
                </p>
                <div class="clearfix" style="margin-top:12px;">
                    <div style="float:right; text-align:right;">
                        Parent's Signature: <span class="sig-line"></span>
                        <br><br>
                        Date: <span class="sig-line"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>