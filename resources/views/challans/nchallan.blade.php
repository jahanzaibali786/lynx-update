@extends('layouts.admin')
@section('page-title')
    {{ __('Generate Challan') }}
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js">
    </script>
    <script>
        function generatePDF() {
            console.log('generating');
            const element = document.getElementById('challan-content');
            const opt = {
                filename: 'challan.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: [1000, 1000],
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            console.log('printing');
            const element = document.getElementById('challan-content');
            const opt = {
                margin: 0.3,
                filename: 'challan.pdf',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'pt',
                    format: [1000, 1000],
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }

        // Custom print for all .printRow content on Ctrl+P
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
                e.preventDefault();
                // Get the full challan content
                var challanContent = document.getElementById('printRow');
                if (!challanContent) return;

                // Get all styles and CSS links
                var styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
                    .map(function(node) { return node.outerHTML; })
                    .join('');

                // Open print window
                var printWindow = window.open('', '', 'width=1000,height=700');
                printWindow.document.write('<html><head><title>Print Challan</title>');
                printWindow.document.write(styles);
                printWindow.document.write('<style>*{-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important;}</style>');
                printWindow.document.write('<style>@media print { #printRow { gap: 25px !important; } #printRow > .col-md-4 { width: calc(33.33% - 17px) !important; max-width: calc(33.33% - 17px) !important; } }</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(challanContent.outerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Generate Challan') }}</li>
@endsection
@section('action-btn')
    <div class="float-end" style="display: flex; gap:10px;">
        <form action="{{ route('challan.show', $challan->id) }}">
            @csrf
            <input type="hidden" name="type" value="print">
            <button class="btn btn-outline-primary"  data-bs-title="{{ __('Print Challan') }}" type="submit">Print Challan</button>
        </form>
        {{-- <form action="{{ route('challan.show', $challan->id) }}">
            @csrf
            <input type="hidden" name="type" value="download">
            <button class="btn btn-outline-success"  data-bs-title="{{ __('Download Challan') }}" type="submit">Download Challan</button>
        </form> --}}
    </div>
@endsection
@section('content')

    <style>
        /* Adapted from challanPdf.blade.php, for div/flex layout */
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
        .detail-label {
            font-weight: bold;
            font-size: 13px;
        }
        .detail-value {
            font-size: 13px;
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
            font-size: 14px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
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
        .challan-type-header {
            border-bottom: 4px solid black;
            border-top: 4px solid black;
            margin: 0.5rem 0;
            clear: both;
        }
        .arrears-title {
            margin-top: 0.5rem;
            clear: both;
            font-weight: bold;
            font-size: 16px;
        }
        .arrears-item {
            margin-bottom: 0.1rem;
            font-size: 14px;
        }
        .payable-container {
            float: right;
            clear: both;
        }
        .payable-label-span {
            float: left;
            margin-right: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        .payable-value-span {
            float: left;
            border-bottom: 1px solid black;
            border-top: 1px solid black;
            padding: 1px 0 0 50px;
            margin-left: 40px;
            font-size: 12px;
        }
        .vertical-separator {
            border-left: 2px solid #000;
            height: 100%;
            margin: 0 0.5rem;
        }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .card#challan-content { width: 100% !important; max-width: 1120px !important; margin: 0 auto !important; }
            .row, .printRow { display: flex !important; flex-direction: row !important; width: 100% !important; }
            .col-md-4 { width: 33.33% !important; max-width: 33.33% !important; }
            .logo img { max-width: 70px !important; max-height: 70px !important; }
            body, html { font-size: 11px !important; }
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
        .challan-details-table {
            width: 100% !important;
            margin-bottom: 10px !important;
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
    </style>
    <div class="card mt-4" id="challan-content">
        <div class="row printRow" id="printRow" style="align-items: stretch;">
            @php
                $copies = ['Bank Copy', 'School Copy', 'Student Copy'];
            @endphp
            @foreach ($copies as $copy)
                <div class="col-md-4 border p-4 d-flex flex-column" style="border: 1px solid black !important; min-height: 100%;">
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
                    <div class="d-flex justify-content-between align-items-center" style="flex-direction: row-reverse !important;">
                        <span class="copy-title">{{ $copy }}</span>
                    </div>
                    <div class="d-flex align-items-center mt-2 mb-2">
                        <div class="logo me-2">
                            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
                        </div>
                        <div class="heading ms-2 flex-fill" style="border: 3px solid black; text-align:center;">
                            <p class="bank-info mb-0">{{ @$challan->student->branch_name->address }} <br> BANK AC#{{ @$challan->student->branch_name->bankname->account_number }}</p>
                        </div>
                    </div>
                    <div class="school-name mb-1">
                        <img style="width: 52%; height: 42px;" src="{{ asset('assets/images/lynxheadertext.png') }}" alt="logo">
                    </div>
                    <p class="branch-name mb-2">{{ @$challan->student->branches->name }}</p>
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
                            <td class="challan-details-value">{{ \Carbon\Carbon::parse($challan->fee_month)->format('F,Y') }}</td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Name:</td>
                            <td class="challan-details-value">{{ @$challan->student->stdname }}</td>
                            <td class="challan-details-section-label"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Class:</td>
                            <td class="challan-details-value">
                                @if (@$challan->student->enrollment && @$challan->student->enrollment->class)
                                    {{ @$challan->student->enrollment->class->name }}
                                @else
                                    NILL
                                @endif
                            </td>
                            <td class="challan-details-section-label">Section:</td>
                            <td class="challan-details-value">
                                @if (@$challan->student->enrollment && @$challan->student->enrollment->section)
                                    {{ @$challan->student->enrollment->section->name }}
                                @else
                                    NILL
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="challan-details-label">Roll No:</td>
                            <td class="challan-details-value">
                                @if (@$challan->student->enrollment && @$challan->student->enrollment->enrollId)
                                    {{ @$challan->student->enrollment->enrollId }}
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
                    <div class="description-header mb-1">
                        <span>Description</span> <span>Amount</span>
                    </div>
                    @php $arrearsTotal = 0 @endphp
                    @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                        @foreach ($previousUnpaidChallans as $prevchallan)
                            @php
                                $arrearsTotal +=
                                    $prevchallan->total_amount - ($prevchallan->paid_amount + $prevchallan->concession_amount);
                            @endphp
                        @endforeach
                    @endif
                    @php
                        $totalAmount = 0;
                        foreach ($heads as $head) {
                            $totalAmount += $head['amount'] - $head['concession'];
                        }
                        $grandTotal = $totalAmount - $challan->paid_amount + $arrearsTotal;
                    @endphp
                    @foreach ($heads as $head)
                        <div class="description-item mb-1">
                            <span>{{ $head['name'] }} @if ($challan->challan_type == 'Admission') (Rs. {{ $head['amount'] }}) @endif</span>
                            <span>Rs. {{ $head['amount'] - $head['concession'] }}</span>
                        </div>
                    @endforeach
                    @if ($challan->paid_amount != 0)
                        <div class="total-label mb-1">
                            <b>Received Amount</b>
                            <b>Rs. {{ @$challan->paid_amount }}</b>
                        </div>
                    @endif
                    <div class="challan-totals-box">
                        <div class="challan-totals-row">
                            <span class="challan-totals-label">Total :</span>
                            <span class="challan-totals-value">Rs. {{ number_format($totalAmount - $challan->paid_amount, 2) }}</span>
                        </div>
                        
                    </div>
                    @if (count($previousUnpaidChallans) > 0)
                        @if ($challan->challan_type != 'Admission' && $challan->challan_type != 'Registration')
                            <div class="arrears-title">Arrears</div>
                            <div style="display: inline-block; font-size: {{ count($previousUnpaidChallans) > 10 ? '10px' : '10px' }}; line-height: 1.2;">
                                @foreach ($previousUnpaidChallans as $prevchallan)
                                    <span style="display: inline-block; margin-right: 10px;">
                                        ({{ \Carbon\Carbon::parse($prevchallan->fee_month)->format('M') }} - {{ $prevchallan->challanNo }} - Rs. {{ $prevchallan->total_amount - ($prevchallan->paid_amount + $prevchallan->concession_amount) }})
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    <div class="challan-totals-row" style="margin-top:2px;">
                            <span class="challan-payable-label">Payable By Due Date</span>
                            <span class="challan-payable-value">Rs. {{ number_format($grandTotal, 2) }}</span>
                        </div>
                    <!-- Footer Section -->
                    <div class="footer-section mt-auto pt-3">
                        <div class="regular-footer-text">
                            <div class="footer-heading" style="color: black;">
                                COMPULSORY INSTRUCTIONS FOR BANK</div>
                            <div class="footer-text" style="font-size: 11px;">Please mention challan # / student name
                                in description to avoid any descripancy</div>
                            @if($challan->challan_type == 'Regular')
                            <div class="footer-headi" style="background-color: none; padding-bottom: 10px; font-weight: 800; color: black; font-size: 13px;">PAYMENT TERMS</div>
                            <div class="payment-terms" style="padding-bottom: 6px;">
                                <div style="font-size: 12px;">1. LATE PAYMENT SURCHARGE @ RS 120.00 PER DAY WILL BE CALCULATED AND CHARGED BY THE BANK / Branch AFTER DUE DATE</div>
                                <div style="font-size: 12px;">2. ANY ERROR IN THE CALCULATION OF FINE BY THE BANK / Branch WILL BE ADJUSTED IN THE NEXT FEE BILL</div>
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
                                <div class="contact-info"><b>Phone:</b> {{ @$studentData->branch_name->phone_no ?? '' }} </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection