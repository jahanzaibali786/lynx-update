<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyPack Challan</title>
    <style>
        /* Base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .card {
            border: 1px solid #f1f1f1;
        }

        .challan-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .challan-column {
            width: 32%;
            padding: 5px 18px;
            border: 1px solid #000000;
            vertical-align: top;
        }

        .challan-separator {
            width: 2%;
            padding: 18px 0;
            vertical-align: top;
            border: 1px solid #000000;
        }

        .copy-title {
            float: right;
            font-size: 0.55rem;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .bank-info {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .school-name {
            font-family: arial;
            font-size: 1.1rem;
            text-align: center;
            font-weight: 600;
            margin: 4px 0 8px;
        }

        .branch-name {
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .date-heading {
            text-align: center;
            font-weight: 900;
            font-size: 0.7rem;
        }

        .date-value {
            text-align: center;
            font-size: 0.7rem;
        }

        .detail-label {
            font-weight: bold;
            font-size: 0.75rem;
        }

        .detail-value {
            font-size: 0.75rem;
        }

        .challan-type {
            text-align: center;
            font-weight: 900;
            font-size: 1rem;
            font-family: arial;
            text-transform: uppercase;
            padding: 6px 0;
        }

        .description-header {
            font-size: 0.7rem;
            text-decoration: underline;
            font-weight: bold;
            margin: 6px 0;
        }

        .description-item {
            font-size: 0.7rem;
            margin: 4px 0;
        }

        .total-label {
            font-size: 0.8rem;
            font-weight: bold;
        }

        .footer-heading {
            font-family: arial;
            font-size: 0.7rem;
            font-weight: 800;
            background-color: rgb(165, 161, 161);
            color: black;
            padding: 6px;
        }

        .footer-text {
            font-family: arial;
            font-size: 0.8rem;
            color: black;
            margin: 6px 0;
        }

        .payment-terms {
            font-size: 0.7rem;
            color: black;
        }

        .contact-info {
            font-size: 0.8rem;
            color: black;
        }

        /* Logo and heading section */
        .logo-heading-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
        }

        .logo-cell {
            width: 75px;
            vertical-align: middle;
        }

        .logo-cell img {
            max-width: 75px;
            max-height: 75px;
        }

        .spacer-cell {
            width: 20px;
        }

        .heading-cell {
            border: 1.5px solid black;
            text-align: center;
            padding: 6px;
            vertical-align: middle;
        }

        /* Dates section */
        .dates-table {
            width: 100%;
            margin: 12px 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .date-cell {
            width: 120px;
            height: 40px;
            vertical-align: top;
        }

        .date-spacer {
            width: auto;
        }

        .date-divider {
            width: 100%;
            height: 1.5px;
            background-color: black;
            margin: 3px 0;
        }

        /* Details section */
        .details {
            margin: 12px 0;
        }

        .challan-type-header {
            border-bottom: 3px solid black;
            border-top: 3px solid black;
            margin: 8px 0;
        }

        .fee-header-table {
            width: 100%;
            margin-bottom: 6px;
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
        }

        .fee-name-cell {
            text-align: left;
        }

        .fee-value-cell {
            text-align: right;
        }

        .total-row {
            width: 100%;
            clear: both;
            margin-top: 12px;
        }

        .total-container {
            float: right;
            margin-top: 6px;
        }

        .total-label-span {
            float: left;
            margin-right: 6px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .total-value-span {
            float: left;
            border-bottom: 1px solid black;
            border-top: 1px solid black;
            padding: 0px 0 0 40px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .payable-container {
            float: right;
            clear: both;
            margin-top: 12px;
        }

        .payable-label-span {
            float: left;
            margin-right: 6px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .payable-value-span {
            float: left;
            border-bottom: 1px solid black;
            border-top: 1px solid black;
            padding: 0px 0 0 150px;
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .details-table td {
            padding: 0 6px 3px 0;
        }

        .footer-section {
            padding-top: 12px;
        }

        /* Print-specific styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                font-size: 0.9rem !important;
            }

            html,
            body {
                width: 100%;
                height: auto;
                overflow: visible !important;
            }

            .card {
                width: 100%;
                box-sizing: border-box;
            }

            .challan-column {
                padding: 6px 20px !important;
            }

            .copy-title {
                font-size: 0.65rem !important;
                margin-bottom: 14px !important;
            }

            .school-name {
                font-size: 1.2rem !important;
            }
        }
    </style>

    @php
        $amounttax = 0;
        $total_amnt = 0;
        $total_price = 0;
        $total_tax = 0;
        $total_disc = 0;
        $items = \App\Models\StudyPackChallanItems::with('product', 'product.taxsingle')
            ->where('challan_id', @$challan->id)
            ->get();
    @endphp
    @php
        $itemCount = count($items) * 2;
        $baseFont = 14; // Reduced base font size
        $scaleFactor = max(0.4, 1 - $itemCount * 0.01);
        $finalFont = round($baseFont * $scaleFactor, 2);
    @endphp

    <style>
        html {
            font-size: {{ $finalFont }}px !important;
        }

        body {
            /* display: none!important; */
        }

        .card {
            transform: scale(0.97);
        }
    </style>
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
                                <p class="bank-info">{{ @$studentData->branch_name->address }} <br> BANK
                                    AC#{{ @$studentData->branch_name->bank }}</p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name">The Lynx School</div>
                    <p class="branch-name">{{ @$studentData->branches->name }}</p>

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
                            <td class="date-cell">
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
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ \Carbon\Carbon::parse($challan->challan_date)->format('F, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    {{ @$studentData->stdname }}
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->class)
                                        {{ @$studentData->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label"
                                    style="width: 0px !important; padding-right: 8px !important; font-size: 0.75rem;">
                                    <b>Section:</b>
                                </td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->section)
                                        {{ @$studentData->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value" style="font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->enrollId)
                                        {{ @$studentData->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div class="challan-type">
                            {{ $challan->challan_type }} Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Product</td>
                            <td class="fee-amount-cell">Amount (inc. tax)</td>
                        </tr>
                    </table>

                    @php
                        $amounttax = 0;
                        $total_amnt = 0;
                        $total_price = 0;
                        $total_tax = 0;
                        $total_disc = 0;
                        $items = \App\Models\StudyPackChallanItems::with('product', 'product.taxsingle')
                            ->where('challan_id', @$challan->id)
                            ->get();
                    @endphp
                    @php $totalAmount = 0; @endphp
                    @foreach ($items as $item)
                        @php $totalAmount += $item->price; @endphp
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell">{{ $item->product->name }}</td>
                                <td class="fee-value-cell">
                                    {{ number_format($item->price, 2) }}</td>
                            </tr>
                        </table>
                    @endforeach

                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell"><b>Received Amount</b></td>
                                <td class="fee-value-cell">
                                    <b>{{ number_format($challan->paid_amount, 2) }}</b>
                                </td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span">Total :</div>
                            <div class="total-value-span">{{ number_format($totalAmount, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span">Payable By Due Date:</div>
                        <div class="payable-value-span">
                            {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Footer Section -->
                    <div class="footer-section">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTION FOR BANK</div>
                            <div class="footer-text">Please mention challan # / student name
                                in description to avoid descripancy</div>

                            <div style="background-color:rgb(206, 206, 206); padding: 3px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? 'Phone' }} </div>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Separator Column -->
                <td class="challan-separator"></td>

                <!-- School Copy Column -->
                <td class="challan-column">
                    <!-- Content same as Bank Copy -->
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
                                <p class="bank-info">{{ @$studentData->branch_name->address }} <br> BANK
                                    AC#{{ @$studentData->branch_name->bank }}</p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name">The Lynx School</div>
                    <p class="branch-name">{{ @$studentData->branches->name }}</p>

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
                            <td class="date-cell">
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
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ \Carbon\Carbon::parse($challan->challan_date)->format('F, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    {{ @$studentData->stdname }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->class)
                                        {{ @$studentData->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label"
                                    style="width: 0px !important; padding-right: 8px !important; font-size: 0.75rem;">
                                    <b>Section:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->section)
                                        {{ @$studentData->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value" style="font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->enrollId)
                                        {{ @$studentData->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div class="challan-type">
                            {{ $challan->challan_type }} Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Product</td>
                            <td class="fee-amount-cell">Amount (inc. tax)</td>
                        </tr>
                    </table>

                    @php
                        $amounttax = 0;
                        $total_amnt = 0;
                        $total_price = 0;
                        $total_tax = 0;
                        $total_disc = 0;
                        $items = \App\Models\StudyPackChallanItems::with('product', 'product.taxsingle')
                            ->where('challan_id', @$challan->id)
                            ->get();
                    @endphp
                    @php $totalAmount = 0; @endphp
                    @foreach ($items as $item)
                        @php $totalAmount += $item->price; @endphp
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell">{{ $item->product->name }}</td>
                                <td class="fee-value-cell">
                                    {{ number_format($item->price, 2) }}</td>
                            </tr>
                        </table>
                    @endforeach

                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell"><b>Received Amount</b></td>
                                <td class="fee-value-cell">
                                    <b>{{ number_format($challan->paid_amount, 2) }}</b>
                                </td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span">Total :</div>
                            <div class="total-value-span">
                                {{ number_format($totalAmount, 2) }}</div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span">Payable By Due Date:</div>
                        <div class="payable-value-span">
                            {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Footer Section -->
                    <div class="footer-section">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTION FOR BANK</div>
                            <div class="footer-text">Please mention challan # / student name
                                in description to avoid descripancy</div>

                            <div style="background-color:rgb(206, 206, 206); padding: 3px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? 'Phone' }} </div>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Separator Column -->
                <td class="challan-separator"></td>

                <!-- Student Copy Column -->
                <td class="challan-column">
                    <!-- Content same as Bank Copy -->
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
                                <p class="bank-info">{{ @$studentData->branch_name->address }} <br> BANK
                                    AC#{{ @$studentData->branch_name->bank }}</p>
                            </td>
                        </tr>
                    </table><br>

                    <div class="school-name">The Lynx School</div>
                    <p class="branch-name">{{ @$studentData->branches->name }}</p>

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
                            <td class="date-cell">
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
                            <tr>
                                <td class="detail-label"><b>Billing Month:</b></td>
                                <td class="detail-value" style="text-transform: uppercase;">
                                    {{ \Carbon\Carbon::parse($challan->challan_date)->format('F, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Name:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    {{ @$studentData->stdname }}</td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Class:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->class)
                                        {{ @$studentData->enrollment->class->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                                <td class="detail-label"
                                    style="width: 0px !important; padding-right: 8px !important; font-size: 0.75rem;">
                                    <b>Section:</b></td>
                                <td class="detail-value" style="text-transform: uppercase; font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->section)
                                        {{ @$studentData->enrollment->section->name }}
                                    @else
                                        nill
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="detail-label"><b>Roll No:</b></td>
                                <td class="detail-value" style="font-size: 0.75rem;">
                                    @if (@$studentData->enrollment && @$studentData->enrollment->enrollId)
                                        {{ @$studentData->enrollment->enrollId }}
                                    @else
                                        Not Enrolled
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="challan-type-header">
                        <div class="challan-type">
                            {{ $challan->challan_type }} Challan
                        </div>
                    </div>

                    <table class="fee-header-table description-header">
                        <tr>
                            <td class="fee-description-cell">Product</td>
                            <td class="fee-amount-cell">Amount (inc. tax)</td>
                        </tr>
                    </table>

                    @php
                        $amounttax = 0;
                        $total_amnt = 0;
                        $total_price = 0;
                        $total_tax = 0;
                        $total_disc = 0;
                        $items = \App\Models\StudyPackChallanItems::with('product', 'product.taxsingle')
                            ->where('challan_id', @$challan->id)
                            ->get();
                    @endphp
                    @php $totalAmount = 0; @endphp
                    @foreach ($items as $item)
                        @php $totalAmount += $item->price; @endphp
                        <table class="fee-item-table description-item">
                            <tr>
                                <td class="fee-name-cell">{{ $item->product->name }}</td>
                                <td class="fee-value-cell">
                                    {{ number_format($item->price, 2) }}</td>
                            </tr>
                        </table>
                    @endforeach

                    @if ($challan->paid_amount != 0)
                        <table class="fee-item-table total-label">
                            <tr>
                                <td class="fee-name-cell"><b>Received Amount</b></td>
                                <td class="fee-value-cell">
                                    <b>{{ number_format($challan->paid_amount, 2) }}</b>
                                </td>
                            </tr>
                        </table>
                    @endif

                    <div class="total-row clearfix">
                        <div class="total-container">
                            <div class="total-label-span">Total :</div>
                            <div class="total-value-span">
                                {{ number_format($totalAmount, 2) }}</div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="payable-container">
                        <div class="payable-label-span">Payable By Due Date:</div>
                        <div class="payable-value-span">
                            {{ number_format($totalAmount - $challan->paid_amount, 2) }}</div>
                    </div>
                    <div class="clearfix"></div>

                    <!-- Footer Section -->
                    <div class="footer-section">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTION FOR BANK</div>
                            <div class="footer-text">Please mention challan # / student name
                                in description to avoid descripancy</div>

                            <div style="background-color:rgb(206, 206, 206); padding: 3px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                <div class="contact-info"><b>Phone:</b>
                                    {{ @$studentData->branch_name->phone_no ?? 'Phone' }} </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
