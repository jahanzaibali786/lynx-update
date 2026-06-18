<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Challan</title>


    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px 0 0 15px;
        }

        .card {
            border: 1px solid #f1f1f1;
            page-break-inside: avoid;
            page-break-before: auto;
        }

        .challan-table {
            width: 100%;
            height: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .challan-column {
            width: 30%;
            padding: 0.3rem 1.125rem;
            border: 1px solid #000000;
            vertical-align: top;
        }

        .challan-separator {
            width: 1.5%;
            padding: 1.125rem 0;
            vertical-align: top;
            border-left: 1px solid #000000;
            border-right: 1px solid #000000;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        .copy-title {
            float: right;
            font-size: 0.714rem;
            font-weight: bold;
        }

        .bank-info {
            font-size: 0.9rem;
            font-weight: 600;
            margin: auto;
        }

        .school-name {
            font-family: arial;
            font-size: 1.429rem;
            text-align: center;
            font-weight: 600;
        }

        .branch-name {
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 3px;
        }

        .date-heading {
            text-align: center;
            font-weight: 900;
            font-size: 0.857rem;
        }

        .date-value {
            text-align: center;
            font-size: 0.857rem;
        }

        .detail-label {
            font-weight: bold;
            font-size: 0.929rem;
        }

        .detail-value {
            font-size: 0.929rem;
        }

        .challan-type {
            text-align: center;
            font-weight: 900;
            font-size: 1.286rem;
            font-family: arial;
            text-transform: uppercase;
        }

        .description-header {
            font-size: 0.857rem;
            text-decoration: underline;
            font-weight: bold;
        }

        .description-item {
            font-size: 0.857rem;
        }

        .total-label {
            font-size: 1rem;
            font-weight: bold;
        }

        .footer-heading {
            font-family: arial;
            font-size: 0.9rem;
            font-weight: 800;
            background-color: rgb(165, 161, 161);
            color: black;
            padding-bottom: 4px;
            padding-top: 4px;
            padding-left: 2px;
        }

        .footer-text {
            font-family: arial;
            font-size: 1rem;
            color: black;
            margin-bottom: 8px;
            margin-top: 4px;
        }

        .payment-terms {
            font-size: 0.857rem;
            color: black;
        }

        .contact-info {
            font-size: 1rem;
            color: black;
        }

        .logo-heading-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .logo-cell {
            width: 68px;
            vertical-align: middle;
        }

        .logo-cell img {
            max-width: 68px;
            max-height: 68px;
        }

        .spacer-cell {
            width: 23px;
        }

        .heading-cell {
            border: 1.5px solid black;
            text-align: center;
            padding: 2.25px;
            vertical-align: middle;
        }

        .dates-table {
            width: 100%;
            margin: 0.4rem 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .date-cell {
            width: 113px;
            height: 30px;
            vertical-align: top;
        }

        .date-spacer {
            width: auto;
        }

        .date-divider {
            width: 100%;
            height: 1.5px;
            background-color: black;
            margin: 1.5px 0;
        }

        .details {
            margin-top: 3.75px;
        }

        .info-row {
            width: 100%;
            clear: both;
        }

        .info-label {
            float: left;
            width: 56px;
            font-weight: bold;
            font-size: 0.929rem;
        }

        .info-value {
            float: left;
            width: 83px;
            font-size: 0.929rem;
        }

        .section-label {
            float: left;
            width: 71px;
            font-weight: bold;
            font-size: 0.929rem;
        }

        .section-value {
            float: left;
            width: 30px;
            font-size: 0.929rem;
        }

        .challan-type-header {
            border-bottom: 3px solid black;
            border-top: 3px solid black;
            margin: 0.375rem 0;
            clear: both;
        }

        .fee-header-table {
            width: 100%;
            margin-bottom: 0.375rem;
        }

        .fee-description-cell {
            font-weight: bold;
            text-align: left;
        }

        .fee-amount-cell {
            text-align: right;
            font-weight: bold;
        }

        .fee-item-table {
            width: 100%;
            font-size: 0.929rem !important;
        }

        .fee-name-cell,
        .fee-value-cell,
        .fee-description-cell,
        .fee-amount-cell {
            /* text-align: left; */
            font-size: 0.857rem !important;
        }

        .fee-value-cell {
            text-align: right;
        }

        .total-row {
            width: 100%;
            clear: both;
        }

        .total-container {
            float: right;
            margin-top: 0;
        }

        .total-label-span {
            float: left;
            margin-right: 3.75px;
        }

        .payable-label-span,
        .payable-value-span,
        .total-label-span,
        .total-value-span {
            font-size: 0.929rem !important;
            font-weight: bold !important;
        }

        .total-value-span {
            float: left;
            border-bottom: 1px solid black;
            border-top: 1px solid black;
            padding: 0.75px 0 0 37.5px;
            margin-left: 39.75px;
        }

        .arrears-title {
            margin-top: 0.1rem;
            clear: both;
            font-weight: bold;
            font-size: 1.143rem;
        }

        .arrears-item {
            margin-bottom: 0.075rem;
            font-size: 1rem;
        }

        .payable-container {
            margin-top: 2px;
            float: right;
            clear: both;
        }

        .payable-label-span {
            float: left;
            margin-right: 3.75px;
            font-size: 1.143rem;
            font-weight: bold;
        }

        .payable-value-span {
            float: left;
            border-bottom: 1px solid black;
            border-top: 1px solid black;
            padding: 0.75px 0 0 37.5px;
            margin-left: 135px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .details-table td {
            padding: 0 4.5px 1.5px 0;
        }

        .details-table .detail-label {
            width: 75px;
            white-space: nowrap;
        }

        .fee-item-table-scroll {
            overflow-y: scroll;
            max-height: 52.5px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            html {
                font-size: 1px !important;
            }
        }
    </style>

    @php
        $itemCount = count($heads) * 2;
        $baseFont = 14; // Reduced from 14 to 12
        $scaleFactor = max(0.4, 1 - $itemCount * 0.01);
        $finalFont = round($baseFont * $scaleFactor, 2);
    @endphp

    <style>
        html {
            font-size: {{ $finalFont }}px !important;
        }
        body {
            transform: scale(0.98) !important;
            transform-origin: top left !important;
        }
    </style>

    {{-- </style> --}}
</head>

<body>
    <div class="card" id="challan-content">
        <table class="challan-table">
            <tr>
                <!-- Bank Copy Column -->
                <td class="challan-column">
                    @php
                        $st_id = $challan->student_id;
                        $studentData = App\Models\StudentRegistration::with(
                            'enrollment.class',
                            'enrollment.section',
                            'branches',
                            'branch_name',
                        )
                            ->where('id', $st_id)
                            ->first();
                    @endphp
                    <p class="copy-title">Bank Copy</p><br>

                    <table class="logo-heading-table">
                        <tr>
                            <td class="logo-cell">
                                <img src="{{ asset('assets/images/lynx2.jpg') }}" alt="logo">
                            </td>
                            <td class="spacer-cell"></td>
                            <td class="heading-cell">
                                <p class="bank-info">
                                    {{ @$challan->student->branch_name->address }}
                                    <br>
                                    BANK AC#{{ @$challan->student->branch_name->bankname->account_number }}
                                </p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name"><img style="width: 45%; height: 28px;"
                            src="{{ asset('assets/images/lynxheadertext.png') }}" alt="logo"></div>
                    <p class="branch-name">{{ @$challan->student->branches->name }}</p>

                    <table class="dates-table">
                        <tr>
                            <td class="date-cell">
                                <div class="date-heading">Issue Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="date-spacer"></td>
                            <td class="date-cell due-date">
                                <div class="date-heading">Due Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="details">
                        <table class="details-table" style="width: 100%;">
                            <tr>
                                <td class="detail-label"><b>Challan#</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">{{ $challan->challanNo }}
                                </td>
                            </tr>
                            @php
                                $fromMonth = \Carbon\Carbon::parse($challan->fee_month);

                                $toMonth = null;
                                if (!empty($challan->other_months)) {
                                    $months = array_map('trim', explode(',', $challan->other_months));
                                    $lastMonth = end($months);
                                    $toMonth = \Carbon\Carbon::parse($lastMonth);
                                }
                            @endphp
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ $fromMonth->format('F, Y') }}

                                    @if ( $toMonth != null && $toMonth != $fromMonth)
                                        - {{ $toMonth->format('F, Y') }}
                                    @endif
									@if (!empty($showJunJulExemptionLabel))
                                        <small style="font-size: 0.7rem; text-transform: none;">(exempted)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ @$challan->student->stdname }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->class)
                                        {{ @$challan->student->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label" style="width: 0px !important; padding-right: 10px !important;">
                                    <b>Section:</b>
                                </td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->section)
                                        {{ @$challan->student->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->enrollId)
                                        {{ @$challan->student->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div style="padding-bottom: 4px" class="challan-type">
                            {{ $challan->challan_type }}
                            @if ($challan->challan_type == 'Admission')
                                {{ App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->count() == 2
                                    ? (App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->last()->id == $challan->id
                                        ? '2nd Installment'
                                        : '1st Installment')
                                    : '' }}
                            @endif
                            Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Description</td>
                            <td class="fee-amount-cell">Amount</td>
                        </tr>
                    </table>

                    @php $arrearsTotal = 0 @endphp
                    @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                        @foreach ($previousUnpaidChallans as $prevchallan)
                            @php
                                $arrearsTotal +=
                                    $prevchallan->total_amount -
                                    ($prevchallan->paid_amount + $prevchallan->concession_amount);
                            @endphp
                        @endforeach
                    @endif

                    @php
                        $totalAmount = 0;
                        foreach ($heads as $head) {
                            $totalAmount += $head['headamount'] - $head['concession'];
                        }
                        $grandTotal = $totalAmount - $challan->paid_amount + $arrearsTotal;
                    @endphp

                    @foreach ($heads as $head)
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell" style="">
                                    {{ $head['name'] }} @if ($challan->challan_type == 'Admission')
                                        (Rs. {{ $head['amount'] }})
                                    @endif
                                </td>
                                <td class="fee-value-cell" style="">Rs.
                                    {{ $head['headamount'] - $head['concession'] }}</td>
                            </tr>
                        </table>
                    @endforeach


                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell" style="font-size: 12px;"><b>Received Amount</b></td>
                                <td class="fee-value-cell" style="font-size: 12px;"><b>Rs.
                                        {{ @$challan->paid_amount }}</b></td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span" style="font-size: 16px;">Total :</div>
                            <div class="total-value-span" style="font-size: 16px;">Rs.
                                {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    @if (count($previousUnpaidChallans) > 0)
                        @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                            <p class="arrears-title">Arrears</p>
                            <div
                                style="display: inline-block; font-size: {{ count($previousUnpaidChallans) > 10 ? '0.75rem' : '0.75rem' }}; line-height: 1.2; margin-top: 3px;">
                                @foreach ($previousUnpaidChallans as $prevchallan)
                                    <span style="display: inline-block; margin-right: 10px;">
                                        ({{ \Carbon\Carbon::parse($prevchallan->fee_month)->format('M') }} -
                                        {{ $prevchallan->challanNo }} - Rs.
                                        {{ $prevchallan->total_amount - ($prevchallan->paid_amount + $prevchallan->concession_amount) }})
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span" style="font-size: 16px;">Payable By Due Date</div>
                        <div class="payable-value-span" style="font-size: 16px;">Rs.
                            {{ number_format($grandTotal, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Conditional Footer Section -->
                    <div class="footer-section" style="padding-top: 8px;">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTIONS FOR BANK</div>
                            <div class="footer-text" style="font-size: 0.75rem;">Please mention challan # / student name
                                in description to avoid any descripancy</div>

                            @if ($challan->challan_type == 'Regular')
                                <div class="footer-headi"
                                    style="background-color: none; padding-bottom: 3px; font-weight: 800; color: black; font-size: 0.9rem;">
                                    PAYMENT TERMS</div>
                                <div class="payment-terms" style="padding-bottom: 6px;">
                                    <div style="font-size: 0.75rem;">1. LATE PAYMENT SURCHARGE @ RS 120.00 PER DAY WILL
                                        BE
                                        CALCULATED AND CHARGED BY THE BANK / Branch AFTER DUE DATE</div>
                                    <div style="font-size: 0.75rem;">2. ANY ERROR IN THE CALCULATION OF FINE BY THE BANK / Branch
                                        WILL BE ADJUSTED IN THE NEXT FEE BILL</div>
                                </div>
                            @endif

                            <div style="background-color:rgb(206, 206, 206); padding: 4px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                @if(@$studentData->branch_name->phone_no)
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? '' }} </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Separator Column -->
                <td class="challan-separator"></td>

                <!-- School Copy Column -->
                <td class="challan-column">
                    @php
                        $st_id = $challan->student_id;
                        $studentData = App\Models\StudentRegistration::with(
                            'enrollment.class',
                            'enrollment.section',
                            'branches',
                            'branch_name',
                        )
                            ->where('id', $st_id)
                            ->first();
                    @endphp
                    <p class="copy-title">School Copy</p><br>

                    <table class="logo-heading-table">
                        <tr>
                            <td class="logo-cell">
                                <img src="{{ asset('assets/images/lynx2.jpg') }}" alt="logo">
                            </td>
                            <td class="spacer-cell"></td>
                            <td class="heading-cell">
                                <p class="bank-info">{{ @$challan->student->branch_name->address }} <br> BANK
                                    AC#{{ @$challan->student->branch_name->bankname->account_number }} </p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name"><img style="width: 45%; height: 28px;"
                            src="{{ asset('assets/images/lynxheadertext.png') }}" alt="logo"></div>
                    <p class="branch-name">{{ @$challan->student->branches->name }}</p>

                    <table class="dates-table">
                        <tr>
                            <td class="date-cell">
                                <div class="date-heading">Issue Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="date-spacer"></td>
                            <td class="date-cell due-date">
                                <div class="date-heading">Due Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="details">
                        <table class="details-table" style="width: 100%;">
                            <tr>
                                <td class="detail-label"><b>Challan#</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">{{ $challan->challanNo }}
                                </td>
                            </tr>
                            @php
                                $fromMonth = \Carbon\Carbon::parse($challan->fee_month);

                                $toMonth = null;
                                if (!empty($challan->other_months)) {
                                    $months = array_map('trim', explode(',', $challan->other_months));
                                    $lastMonth = end($months);
                                    $toMonth = \Carbon\Carbon::parse($lastMonth);
                                }
                            @endphp
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ $fromMonth->format('F, Y') }}

                                    @if ($toMonth)
                                        - {{ $toMonth->format('F, Y') }}
                                    @endif
									@if (!empty($showJunJulExemptionLabel))
                                        <small style="font-size: 0.7rem; text-transform: none;">(exempted)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ @$challan->student->stdname }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->class)
                                        {{ @$challan->student->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label"
                                    style="width: 0px !important; padding-right: 10px !important;"><b>Section:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->section)
                                        {{ @$challan->student->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->enrollId)
                                        {{ @$challan->student->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div style="padding-bottom: 4px;" class="challan-type">
                            {{ $challan->challan_type }}
                            @if ($challan->challan_type == 'Admission')
                                {{ App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->count() == 2
                                    ? (App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->last()->id == $challan->id
                                        ? '2nd Installment'
                                        : '1st Installment')
                                    : '' }}
                            @endif
                            Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Description</td>
                            <td class="fee-amount-cell">Amount</td>
                        </tr>
                    </table>

                    @php $arrearsTotal = 0 @endphp
                    @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                        @foreach ($previousUnpaidChallans as $prevchallan)
                            @php
                                $arrearsTotal +=
                                    $prevchallan->total_amount -
                                    ($prevchallan->paid_amount + $prevchallan->concession_amount);
                            @endphp
                        @endforeach
                    @endif

                    @foreach ($heads as $head)
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell" style="">
                                    {{ $head['name'] }} @if ($challan->challan_type == 'Admission')
                                        (Rs. {{ $head['amount'] }})
                                    @endif
                                </td>
                                <td class="fee-value-cell" style="">Rs.
                                    {{ $head['headamount'] - $head['concession'] }}</td>
                            </tr>
                        </table>
                    @endforeach

                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell" style="font-size: 12px;"><b>Received Amount</b></td>
                                <td class="fee-value-cell" style="font-size: 12px;"><b>Rs.
                                        {{ @$challan->paid_amount }}</b></td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span" style="font-size: 16px;">Total :</div>
                            <div class="total-value-span" style="font-size: 16px;">Rs.
                                {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    @if (count($previousUnpaidChallans) > 0)
                        @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                            <p class="arrears-title">Arrears</p>
                            <div
                                style="display: inline-block; font-size: {{ count($previousUnpaidChallans) > 10 ? '0.75rem' : '0.75rem' }}; line-height: 1.2; margin-top: 3px;">
                                @foreach ($previousUnpaidChallans as $prevchallan)
                                    <span style="display: inline-block; margin-right: 10px;">
                                        ({{ \Carbon\Carbon::parse($prevchallan->fee_month)->format('M') }} -
                                        {{ $prevchallan->challanNo }} - Rs.
                                        {{ $prevchallan->total_amount - ($prevchallan->paid_amount + $prevchallan->concession_amount) }})
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span" style="font-size: 16px;">Payable By Due Date</div>
                        <div class="payable-value-span" style="font-size: 16px;">Rs.
                            {{ number_format($grandTotal, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Conditional Footer Section -->
                    <div class="footer-section" style="padding-top: 8px;">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTIONS FOR BANK</div>
                            <div class="footer-text" style="font-size: 0.9rem;">Please mention challan # / student name
                                in description to avoid any descripancy</div>

                            @if ($challan->challan_type == 'Regular')
                                <div class="footer-headi"
                                    style="background-color: none; padding-bottom: 3px; font-weight: 800; color: black; font-size: 0.9rem;">
                                    PAYMENT TERMS</div>
                                <div class="payment-terms" style="padding-bottom: 6px;">
                                    <div style="font-size: 0.75rem;">1. LATE PAYMENT SURCHARGE @ RS 120.00 PER DAY WILL
                                        BE
                                        CALCULATED AND CHARGED BY THE BANK / Branch AFTER DUE DATE</div>
                                    <div style="font-size: 0.75rem;">2. ANY ERROR IN THE CALCULATION OF FINE BY THE BANK / Branch
                                        WILL BE ADJUSTED IN THE NEXT FEE BILL</div>
                                </div>
                            @endif

                            <div style="background-color:rgb(206, 206, 206); padding: 4px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                @if(@$studentData->branch_name->phone_no)
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? '' }} </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Separator Column -->
                <td class="challan-separator"></td>

                <!-- Student Copy Column -->
                <td class="challan-column">
                    @php
                        $st_id = $challan->student_id;
                        $studentData = App\Models\StudentRegistration::with(
                            'enrollment.class',
                            'enrollment.section',
                            'branches',
                            'branch_name',
                        )
                            ->where('id', $st_id)
                            ->first();
                    @endphp
                    <p class="copy-title">Student Copy</p><br>

                    <table class="logo-heading-table">
                        <tr>
                            <td class="logo-cell">
                                <img src="{{ asset('assets/images/lynx2.jpg') }}" alt="logo">
                            </td>
                            <td class="spacer-cell"></td>
                            <td class="heading-cell">
                                <p class="bank-info">{{ @$challan->student->branch_name->address }} <br> BANK
                                    AC#{{ @$challan->student->branch_name->bankname->account_number }} </p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name"><img style="width: 45%; height: 28px;"
                            src="{{ asset('assets/images/lynxheadertext.png') }}" alt="logo"></div>
                    <p class="branch-name">{{ @$challan->student->branches->name }}</p>


                    <table class="dates-table">
                        <tr>
                            <td class="date-cell">
                                <div class="date-heading">Issue Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="date-spacer"></td>
                            <td class="date-cell due-date">
                                <div class="date-heading">Due Date</div>
                                <div class="date-divider"></div>
                                <div class="date-value">
                                    {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="details">
                        <table class="details-table" style="width: 100%;">
                            <tr>
                                <td class="detail-label"><b>Challan#</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">{{ $challan->challanNo }}
                                </td>
                            </tr>
                            @php
                                $fromMonth = \Carbon\Carbon::parse($challan->fee_month);

                                $toMonth = null;
                                if (!empty($challan->other_months)) {
                                    $months = array_map('trim', explode(',', $challan->other_months));
                                    $lastMonth = end($months);
                                    $toMonth = \Carbon\Carbon::parse($lastMonth);
                                }
                            @endphp
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ $fromMonth->format('F, Y') }}

                                    @if ($toMonth)
                                        - {{ $toMonth->format('F, Y') }}
                                    @endif
									@if (!empty($showJunJulExemptionLabel))
                                        <small style="font-size: 0.7rem; text-transform: none;">(exempted)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ @$challan->student->stdname }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->class)
                                        {{ @$challan->student->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label"
                                    style="width: 0px !important; padding-right: 10px !important;"><b>Section:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->section)
                                        {{ @$challan->student->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value">
                                    @if (@$challan->student->enrollment && @$challan->student->enrollment->enrollId)
                                        {{ @$challan->student->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div style="padding-bottom: 4px" class="challan-type">
                            {{ $challan->challan_type }}
                            @if ($challan->challan_type == 'Admission')
                                {{ App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->count() == 2
                                    ? (App\Models\Challans::where('student_id', $challan->student_id)->where('challan_type', 'Admission')->orderBy('fee_month', 'asc')->get()->last()->id == $challan->id
                                        ? '2nd Installment'
                                        : '1st Installment')
                                    : '' }}
                            @endif
                            Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Description</td>
                            <td class="fee-amount-cell">Amount</td>
                        </tr>
                    </table>

                    @php $arrearsTotal = 0 @endphp
                    @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                        @foreach ($previousUnpaidChallans as $prevchallan)
                            @php
                                $arrearsTotal +=
                                    $prevchallan->total_amount -
                                    ($prevchallan->paid_amount + $prevchallan->concession_amount);
                            @endphp
                        @endforeach
                    @endif

                    @php
                        $totalAmount = 0;
                        foreach ($heads as $head) {
                            $totalAmount += $head['headamount'] - $head['concession'];
                        }
                        $grandTotal = $totalAmount - $challan->paid_amount + $arrearsTotal;
                    @endphp


                    @foreach ($heads as $head)
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell" style="">
                                    {{ $head['name'] }} @if ($challan->challan_type == 'Admission')
                                        (Rs. {{ $head['amount'] }})
                                    @endif
                                </td>
                                <td class="fee-value-cell" style="">Rs.
                                    {{ $head['headamount'] - $head['concession'] }}</td>
                            </tr>
                        </table>
                    @endforeach


                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell" style="font-size: 12px;"><b>Received Amount</b></td>
                                <td class="fee-value-cell" style="font-size: 12px;"><b>Rs.
                                        {{ @$challan->paid_amount }}</b></td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span" style="font-size: 16px;">Total :</div>
                            <div class="total-value-span" style="font-size: 16px;">Rs.
                                {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    @if (count($previousUnpaidChallans) > 0)
                        @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                            <p class="arrears-title">Arrears</p>
                            <div
                                style="display: inline-block; font-size: {{ count($previousUnpaidChallans) > 10 ? '0.75rem' : '0.75rem' }}; line-height: 1.2; margin-top: 3px;">
                                @foreach ($previousUnpaidChallans as $prevchallan)
                                    <span style="display: inline-block; margin-right: 10px;">
                                        ({{ \Carbon\Carbon::parse($prevchallan->fee_month)->format('M') }} -
                                        {{ $prevchallan->challanNo }} - Rs.
                                        {{ $prevchallan->total_amount - ($prevchallan->paid_amount + $prevchallan->concession_amount) }})
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span" style="font-size: 16px;">Payable By Due Date</div>
                        <div class="payable-value-span" style="font-size: 16px;">Rs.
                            {{ number_format($grandTotal, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Conditional Footer Section -->
                    <div class="footer-section" style="padding-top: 8px;">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTIONS FOR BANK</div>
                            <div class="footer-text" style="font-size: 0.9rem;">Please mention challan # / student name
                                in description to avoid any descripancy</div>

                            @if ($challan->challan_type == 'Regular')
                                <div class="footer-headi"
                                    style="background-color: none; padding-bottom: 3px; font-weight: 800; color: black; font-size: 0.9rem;">
                                    PAYMENT TERMS</div>
                                <div class="payment-terms" style="padding-bottom: 6px;">
                                    <div style="font-size: 0.75rem;">1. LATE PAYMENT SURCHARGE @ RS 120.00 PER DAY WILL
                                        BE
                                        CALCULATED AND CHARGED BY THE BANK / Branch AFTER DUE DATE</div>
                                    <div style="font-size: 0.75rem;">2. ANY ERROR IN THE CALCULATION OF FINE BY THE BANK / Branch
                                        WILL BE ADJUSTED IN THE NEXT FEE BILL</div>
                                </div>
                            @endif

                            <div style="background-color:rgb(206, 206, 206); padding: 4px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                @if(@$studentData->branch_name->phone_no)
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? '' }} </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
