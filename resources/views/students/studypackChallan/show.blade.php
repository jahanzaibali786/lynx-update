@extends('layouts.admin')

@section('page-title')
    {{ __('Generate StudyPack Challan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Generate Challan') }}</li>
@endsection

@section('action-btn')
    <div class="float-end" style="display: flex; gap: 10px;">
        <form action="{{ route('studypackchallan.download', $challan->id) }}" method="POST">
            @csrf

            <input type="hidden" name="type" value="download">

            <button
                class="btn btn-outline-primary"
                data-bs-title="{{ __('Download Challan') }}"
                type="submit"
            >
                Download Challan
            </button>
        </form>

        <form action="{{ route('studypackchallan.print', $challan->id) }}" method="GET">
            <input type="hidden" name="type" value="print">

            <button
                class="btn btn-outline-success"
                data-bs-title="{{ __('Print Challan') }}"
                type="submit"
            >
                Print Challan
            </button>
        </form>
    </div>
@endsection

@section('content')
    <style>
        .copy-title {
            float: right;
            font-size: 8px;
            font-weight: bold;
        }

        .bank-info {
            font-size: 16px;
            font-weight: 600;
        }

        .school-name {
            font-family: Arial, sans-serif;
            font-size: 20px;
            text-align: center;
            font-weight: 600;
        }

        .branch-name {
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        .date-heading {
            text-align: center;
            font-weight: 900;
            font-size: 12px;
        }

        .date-value {
            text-align: center;
            font-size: 12px;
        }

        .challan-details-table {
            width: 100% !important;
            margin-bottom: 10px !important;
            border-collapse: collapse !important;
        }

        .challan-details-table,
        .challan-details-table tr,
        .challan-details-table td {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        .challan-details-table td {
            padding: 2px 8px 2px 0 !important;
            font-size: 13px !important;
            vertical-align: top !important;
        }

        .challan-details-label {
            font-weight: bold !important;
            min-width: 90px !important;
            white-space: nowrap !important;
        }

        .challan-details-section-label {
            font-weight: bold !important;
            min-width: 70px !important;
            white-space: nowrap !important;
            text-align: right !important;
        }

        .challan-details-value {
            font-weight: normal !important;
        }

        .challan-type-header {
            border-bottom: 4px solid black;
            border-top: 4px solid black;
            margin: 0.5rem 0;
            clear: both;
        }

        .challan-type {
            text-align: center;
            font-weight: 900;
            font-size: 18px;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
        }

        .description-header {
            font-size: 12px;
            text-decoration: underline;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .description-item {
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        .total-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .challan-totals-box {
            width: 70%;
            margin-left: auto;
            margin-top: 10px;
        }

        .challan-totals-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 13px;
            margin-bottom: 0;
        }

        .challan-totals-label {
            min-width: 110px;
            text-align: right;
            margin-right: 10px;
        }

        .challan-totals-value {
            min-width: 120px;
            text-align: right;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 0 8px;
            font-size: 13px;
        }

        .challan-payable-label {
            min-width: 110px;
            text-align: right;
            margin-right: 10px;
            font-weight: bold;
            font-size: 15px;
        }

        .challan-payable-value {
            min-width: 120px;
            text-align: right;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 0 8px;
            font-size: 15px;
        }

        .footer-heading {
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 800;
            background-color: rgb(165, 161, 161);
            color: black;
        }

        .footer-text {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: black;
        }

        .payment-terms {
            font-size: 12px;
            color: black;
        }

        .contact-info {
            font-size: 14px;
            color: black;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            .card#challan-content {
                width: 100% !important;
                max-width: 1120px !important;
                margin: 0 auto !important;
            }

            .row,
            .printRow {
                display: flex !important;
                flex-direction: row !important;
                width: 100% !important;
            }

            .col-md-4 {
                width: 33.33% !important;
                max-width: 33.33% !important;
            }

            .logo img {
                max-width: 70px !important;
                max-height: 70px !important;
            }

            body,
            html {
                font-size: 11px !important;
            }
        }
    </style>

    @php
        /*
        |--------------------------------------------------------------------------
        | Challan copies
        |--------------------------------------------------------------------------
        */
        $copies = [
            'Bank Copy',
            'School Copy',
            'Student Copy',
        ];

        /*
        |--------------------------------------------------------------------------
        | Student details
        |--------------------------------------------------------------------------
        */
        $studentData = \App\Models\StudentRegistration::with([
            'enrollment.class',
            'enrollment.section',
            'branches',
            'branch_name',
        ])->find($challan->student_id);

        /*
        |--------------------------------------------------------------------------
        | Study Pack items
        |--------------------------------------------------------------------------
        */
        $items = \App\Models\StudyPackChallanItems::with([
            'product',
            'product.taxsingle',
        ])
            ->where('challan_id', $challan->id)
            ->get();

        $studyPack = \App\Models\StudyPack::find($challan->studypack_id);

        /*
        |--------------------------------------------------------------------------
        | Calculate current challan amount once
        |--------------------------------------------------------------------------
        | Previously this total was calculated inside the copies loop, causing
        | each following copy to add the amount from the previous copy.
        */
        $calculatedItemTotal = $items->sum(function ($item) {
            $quantity = (float) ($item->quantity ?? $item->qty ?? 0);
            $price = (float) ($item->price ?? 0);

            return $quantity * $price;
        });

        /*
        |--------------------------------------------------------------------------
        | Use one consistent total
        |--------------------------------------------------------------------------
        | Prefer the saved challan total when available. Otherwise, use the
        | total calculated from the challan items.
        */
        $storedTotalAmount = (float) ($challan->total_amount ?? 0);

        $totalAmount = $storedTotalAmount > 0
            ? $storedTotalAmount
            : $calculatedItemTotal;

        $paidAmount = (float) ($challan->paid_amount ?? 0);
        $concessionAmount = (float) ($challan->concession_amount ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Previous unpaid challans
        |--------------------------------------------------------------------------
        */
        $arrearsTotal = collect($previousUnpaidChallans ?? [])
            ->sum(function ($previousChallan) {
                $previousTotal = (float) ($previousChallan->total_amount ?? 0);
                $previousPaid = (float) ($previousChallan->paid_amount ?? 0);
                $previousConcession = (float) ($previousChallan->concession_amount ?? 0);

                return max(
                    0,
                    $previousTotal - ($previousPaid + $previousConcession)
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Payable amount
        |--------------------------------------------------------------------------
        | Current challan payable amount does not increase for each printed copy.
        */
        $currentPayableAmount = max(
            0,
            $totalAmount - ($paidAmount + $concessionAmount)
        );

        $payableByDueDate = $currentPayableAmount;

        /*
        | Uncomment this line instead when previous arrears must be included
        | in the payable amount:
        |
        | $payableByDueDate = $currentPayableAmount + $arrearsTotal;
        */
    @endphp

    <div class="card mt-4" id="challan-content">
        <div
            class="row printRow"
            id="printRow"
            style="align-items: stretch;"
        >
            @foreach ($copies as $copy)
                <div
                    class="col-md-4 border p-4 d-flex flex-column"
                    style="border: 1px solid black !important; min-height: 100%;"
                >
                    <div
                        class="d-flex justify-content-between align-items-center"
                        style="flex-direction: row-reverse !important;"
                    >
                        <span class="copy-title">{{ $copy }}</span>
                    </div>

                    <div class="d-flex align-items-center mt-2 mb-2">
                        <div class="logo me-2">
                            <img
                                src="{{ asset('assets/images/lynx2.jpg') }}"
                                style="max-width: 90px; max-height: 90px;"
                                alt="School logo"
                            >
                        </div>

                        <div
                            class="heading ms-2 flex-fill"
                            style="border: 3px solid black; text-align: center;"
                        >
                            <p class="bank-info mb-0">
                                {{ $studentData?->branch_name?->address ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="school-name mb-1">
                        <img
                            src="{{ asset('assets/images/lynxheadertext.png') }}"
                            style="width: 52%; height: 42px;"
                            alt="The Lynx School"
                        >
                    </div>

                    <p class="branch-name mb-2">
                        {{ $studentData?->branches?->name ?? '' }}
                    </p>

                    <div class="d-flex justify-content-between mb-2">
                        <div style="width: 48%;">
                            <div class="date-heading">Issue Date</div>

                            <div
                                style="
                                    width: 65%;
                                    height: 3px;
                                    background-color: black;
                                    justify-self: center;
                                "
                            ></div>

                            <div class="date-value">
                                {{ $challan->issue_date
                                    ? \Carbon\Carbon::parse($challan->issue_date)->format('d M Y')
                                    : 'N/A' }}
                            </div>
                        </div>

                        <div style="width: 48%;">
                            <div class="date-heading">Due Date</div>

                            <div
                                style="
                                    width: 65%;
                                    height: 3px;
                                    background-color: black;
                                    justify-self: center;
                                "
                            ></div>

                            <div class="date-value">
                                {{ $challan->due_date
                                    ? \Carbon\Carbon::parse($challan->due_date)->format('d M Y')
                                    : 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <table class="challan-details-table">
                        <tr>
                            <td class="challan-details-label">
                                Challan#
                            </td>

                            <td class="challan-details-value">
                                {{ $challan->challanNo }}
                            </td>

                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="challan-details-label">
                                Billing Month:
                            </td>

                            <td class="challan-details-value">
                                {{ $challan->challan_date
                                    ? \Carbon\Carbon::parse($challan->challan_date)->format('F, Y')
                                    : 'N/A' }}
                            </td>

                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="challan-details-label">
                                Name:
                            </td>

                            <td class="challan-details-value">
                                {{ $studentData?->stdname ?? 'N/A' }}
                            </td>

                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="challan-details-label">
                                Class:
                            </td>

                            <td class="challan-details-value">
                             @if (@$challan->class)
                                    {{ @$challan->class->name }}
                                @else
                                    nill
                                @endif
                            </td>

                            <td class="challan-details-section-label">
                                Section:
                            </td>

                            <td class="challan-details-value">
                                {{ $studentData?->enrollment?->section?->name ?? 'NILL' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="challan-details-label">
                                Roll No:
                            </td>

                            <td class="challan-details-value">
                                {{ $studentData?->enrollment?->enrollId ?? 'Not Enrolled' }}
                            </td>

                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                    </table>

                    <div class="challan-type-header mb-2">
                        <div
                            class="challan-type"
                            style="padding-bottom: 4px;"
                        >
                            {{ $challan->challan_type }} Challan
                        </div>
                    </div>

                    <div class="description-header mb-1">
                        <span>Product</span>
                        <span>Amount (inc. tax)</span>
                    </div>

                    <div
                        class="mb-1"
                        style="display: flex; justify-content: space-between;"
                    >
                        <span>
                            {{ $studyPack?->title ?? 'StudyPack' }}
                        </span>

                        <span>
                            {{ number_format($totalAmount, 2) }}
                        </span>
                    </div>

                    @if ($paidAmount > 0)
                        <div class="total-label mb-1">
                            <b>Received Amount</b>

                            <b>
                                {{ number_format($paidAmount, 2) }}
                            </b>
                        </div>
                    @endif

                    @if ($concessionAmount > 0)
                        <div class="total-label mb-1">
                            <b>Concession</b>

                            <b>
                                {{ number_format($concessionAmount, 2) }}
                            </b>
                        </div>
                    @endif

                    <div class="challan-totals-box">
                        <div class="challan-totals-row">
                            <span class="challan-totals-label">
                                Total:
                            </span>

                            <span class="challan-totals-value">
                                {{ number_format($totalAmount, 2) }}
                            </span>
                        </div>

                        <div
                            class="challan-totals-row"
                            style="margin-top: 2px;"
                        >
                            <span class="challan-payable-label">
                                Payable By Due Date
                            </span>

                            <span class="challan-payable-value">
                                {{ number_format($payableByDueDate, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="footer-section mt-auto pt-3">
                        <div class="regular-footer-text">
                            <div
                                class="footer-heading"
                                style="color: black;"
                            >
                                TRANSACTION INSTRUCTIONS
                            </div>

                            <div
                                class="footer-text"
                                style="font-size: 13px; line-height: 1.45;"
                            >
                                1. Payment for the Study Pack shall be accepted in cash only.
                                <br>

                                2. Online payments and bank transfers are not permitted.
                                <br>

                                3. It is mandatory to ensure that the challan is duly stamped
                                with a "Paid" seal and bears the signature of the accountant.
                            </div>

                            <div
                                style="
                                    background-color: rgb(206, 206, 206);
                                    padding: 4px;
                                "
                            >
                                <div class="contact-info">
                                    <b>Email:</b>

                                    <span>
                                        {{ $studentData?->branches?->email ?? 'Email' }}
                                    </span>
                                </div>

                                <div class="contact-info">
                                    <b>Web:</b>

                                    <span>
                                        www.thelynxschool.edu.pk
                                    </span>
                                </div>

                                <div class="contact-info">
                                    <b>Phone:</b>

                                    <span>
                                        {{ $studentData?->branch_name?->phone_no ?? 'Phone' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection