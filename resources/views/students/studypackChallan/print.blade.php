@extends('layouts.admin')
@section('page-title')
    {{__('Generate StudyPack Challan')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Generate Challan')}}</li>
@endsection
@section('action-btn')
    <div class="float-end" style="display: flex; gap:10px;">
        <form action="{{ route('studypackchallan.download', $challan->id) }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="download">
            <button class="btn btn-outline-primary" data-bs-title="{{ __('Download Challan') }}" type="submit">Download Challan</button>
        </form>
        <form action="{{ route('studypackchallan.print', $challan->id) }}" method="GET">
            @csrf
            <input type="hidden" name="type" value="print">
            <button class="btn btn-outline-success" data-bs-title="{{ __('Print Challan') }}" type="submit">Print Challan</button>
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
        }
        .challan-details-table,
        .challan-details-table tr,
        .challan-details-table td {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        .challan-details-table {
            border-collapse: collapse !important;
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
            @page { size: A4 landscape; margin: 10mm; }
            .card#challan-content { width: 100% !important; max-width: 1120px !important; margin: 0 auto !important; }
            .row, .printRow { display: flex !important; flex-direction: row !important; width: 100% !important; }
            .col-md-4 { width: 33.33% !important; max-width: 33.33% !important; }
            .logo img { max-width: 70px !important; max-height: 70px !important; }
            body, html { font-size: 11px !important; }
        }
    </style>
    <div class="card mt-4" id="challan-content">
        <div class="row printRow" id="printRow" style="align-items: stretch;">
            @php
                $copies = ['Bank Copy', 'School Copy', 'Student Copy'];
                $st_id = $challan->student_id;
                $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section', 'branches', 'branch_name')->where('id', $st_id)->first();
                $items = \App\Models\StudyPackChallanItems::with('product','product.taxsingle')->where('challan_id', @$challan->id)->get();
                $amounttax = 0;
                $total_amnt = 0;
            @endphp
            @foreach ($copies as $copy)
                <div class="col-md-4 border p-4 d-flex flex-column" style="border: 1px solid black !important; min-height: 100%;">
                    <div class="d-flex justify-content-between align-items-center" style="flex-direction: row-reverse !important;">
                        <span class="copy-title">{{ $copy }}</span>
                    </div>
                    <div class="d-flex align-items-center mt-2 mb-2">
                        <div class="logo me-2">
                            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
                        </div>
                        <div class="heading ms-2 flex-fill" style="border: 3px solid black; text-align:center;">
                            <p class="bank-info mb-0">{{ @$studentData->branch_name->address }} <br> BANK AC#{{ @$studentData->branch_name->bank }}</p>
                        </div>
                    </div>
                    <div class="school-name mb-1">
                        <img style="width: 52%; height: 42px;" src="{{ asset('assets/images/lynxheadertext.png') }}" alt="logo">
                    </div>
                    <p class="branch-name mb-2">{{ @$studentData->branches->name }}</p>
                    <div class="d-flex justify-content-between mb-2">
                        <div style="width:48%">
                            <div class="date-heading">Issue Date</div>
                            <div style="width: 65%; height:3px; background-color:black; justify-self: center;"></div>
                            <div class="date-value">
                                {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                            </div>
                        </div>
                        <div style="width:48%">
                            <div class="date-heading">Due Date</div>
                            <div style="width: 65%; height:3px; background-color:black; justify-self: center;"></div>
                            <div class="date-value">
                                {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <table class="challan-details-table">
                        <tr>
                            <td class="challan-details-label">Challan#</td>
                            <td class="challan-details-value">{{ $challan->challanNo }}</td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Billing Month:</td>
                            <td class="challan-details-value">{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Name:</td>
                            <td class="challan-details-value">{{ @$studentData->stdname }}</td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Class:</td>
                            <td class="challan-details-value">
                                @if (@$studentData->enrollment && @$studentData->enrollment->class)
                                    {{ @$studentData->enrollment->class->name }}
                                @else
                                    NILL
                                @endif
                            </td>
                            <td class="challan-details-section-label">Section:</td>
                            <td class="challan-details-value">
                                @if (@$studentData->enrollment && @$studentData->enrollment->section)
                                    {{ @$studentData->enrollment->section->name }}
                                @else
                                    NILL
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Roll No:</td>
                            <td class="challan-details-value">
                                @if (@$studentData->enrollment && @$studentData->enrollment->enrollId)
                                    {{ @$studentData->enrollment->enrollId }}
                                @else
                                    Not Enrolled
                                @endif
                            </td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                    </table>
                    <div class="challan-type-header mb-2">
                        <div style="padding-bottom: 4px" class="challan-type">
                            {{ $challan->challan_type }} Challan
                        </div>
                    </div>
                    <div class="description-header mb-1">
                        <span>Product</span> <span>Amount(inc. tax)</span>
                    </div>
                    @php $total_amnt = 0; @endphp
                    @foreach ($items as $item)
                        @php $total_amnt += @$item->price + $amounttax; @endphp
                        <div class="description-item mb-1">
                            <span>{{ @$item->product->name }}</span>
                            <span>{{ @$item->price + $amounttax }}</span>
                        </div>
                    @endforeach
                    @if($challan->paid_amount != 0)
                        <div class="total-label mb-1">
                            <b>Received Amount</b>
                            <b>{{@$challan->paid_amount}}</b>
                        </div>
                    @endif
                    <div class="challan-totals-box">
                        <div class="challan-totals-row">
                            <span class="challan-totals-label">Total :</span>
                            <span class="challan-totals-value">{{ number_format(($total_amnt), 2) }}</span>
                        </div>
                        <div class="challan-totals-row" style="margin-top:2px;">
                            <span class="challan-payable-label">Payable By Due Date</span>
                            <span class="challan-payable-value">{{ number_format($total_amnt - @$challan->paid_amount, 2) }}</span>
                        </div>
                    </div>
                    <!-- Footer Section -->
                    <div class="footer-section mt-auto pt-3">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTION FOR BANK</div>
                            <div class="footer-text" style="font-size: 13px;">Please mention challan # / student name
                                in description to avoid descripancy</div>
                            <div style="background-color:rgb(206, 206, 206); padding: 4px;">
                                <div class="contact-info"><b>Email:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">{{ @$studentData->branches->email ?? 'Email' }}</a>
                                </div>
                                <div class="contact-info"><b>Web:</b> <a href=""
                                        style="text-decoration-color: black; color: black;">www.thelynxschool.edu.pk</a>
                                </div>
                                <div class="contact-info"><b>Phone:</b> {{ @$studentData->branch_name->phone_no ?? 'Phone' }} </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection