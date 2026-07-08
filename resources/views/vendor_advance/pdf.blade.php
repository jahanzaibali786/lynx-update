@extends(!empty($isPdf) ? 'layouts.pdf' : 'layouts.admin')

@section('page-title')
    {{ __('Vendor Advance') }}
@endsection

@section('action-btn')
    <div class="col text-end">
        <a class="btn mx-1 btn-sm btn-outline-success" href="{{ route('vendor-advance.print', ['id' => $advance->id, 'download' => 1]) }}">
            <span class="btn-inner--icon">{{ __('Download PDF') }}</span>
        </a>
        <a class="btn mx-1 btn-sm btn-outline-primary" href="{{ route('vendor-advance.print', ['id' => $advance->id, 'preview' => 1]) }}" target="_blank">
            <span class="btn-inner--icon">{{ __('Preview PDF') }}</span>
        </a>
        <a class="btn mx-1 btn-sm btn-outline-secondary" href="javascript:void(0);" onclick="window.print()">
            <span class="btn-inner--icon">{{ __('Print') }}</span>
        </a>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vendor Advance') }}</li>
@endsection

@push('script-page')
    <script>
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'p') {
                event.preventDefault();
                window.print();
            }
        });
    </script>
@endpush

@section('content')
    @php
        $vendor = $advance->vendor;
        $approvalDate = !empty($advance->approval_date) ? \Carbon\Carbon::parse($advance->approval_date)->format('d M Y') : '-';
        $advanceMonth = !empty($advance->advance_date) ? \Carbon\Carbon::parse($advance->advance_date)->startOfMonth()->format('M Y') : '-';
        $logoSrc = !empty($isPdf) ? public_path('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
        $schoolTitleSrc = !empty($isPdf) ? public_path('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
        $amount = (float) $advance->advance_amount;
        $paymentMethod = strtoupper($advance->payment_method == 'cash' ? 'CSH' : ($advance->payment_method == 'cheque' || $advance->payment_method == 'check' ? 'CHQ' : ($advance->payment_method ?: '-')));
        $voucherType = $advance->payment_method == 'cash' ? 'CPV' : 'BPV';
    @endphp

    <style>
        .vendor-advance-wrap {
            background: {{ !empty($isPdf) ? '#fff' : '#f4f4f4' }};
            padding: {{ !empty($isPdf) ? '0' : '20px 0' }};
            width: 100%;
        }

        .vendor-advance-page {
            width: {{ !empty($isPdf) ? '100%' : '794px' }};
            max-width: 100%;
            min-height: {{ !empty($isPdf) ? 'auto' : '1123px' }};
            margin: 0 auto;
            padding: {{ !empty($isPdf) ? '36px 0 8px' : '80px 50px 42px' }};
            box-sizing: border-box;
            background: #fff;
            color: #222;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.25;
            position: relative;
        }

        .vendor-advance-header {
            position: relative;
            min-height: 70px;
            text-align: center;
        }

        .school-title {
            margin: 0;
        }

        .school-title-image {
            width: 215px;
            max-width: 100%;
            height: auto;
            display: inline-block;
        }

        .vendor-doc-title {
            display: inline-block;
            margin-top: 3px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .vendor-logo {
            position: absolute;
            top: -6px;
            right: 33px;
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .vendor-info {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }

        .vendor-info td {
            padding: 4px 0;
            vertical-align: top;
        }

        .vendor-info .label {
            width: 34%;
            font-weight: 600;
        }

        .vendor-info .value {
            width: 66%;
            text-transform: uppercase;
        }

        .amount-row td {
            padding-top: 18px;
            font-weight: 700;
        }

        .detail-heading {
            margin: 24px 0 8px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .detail-table {
            width: 86%;
            margin: 0 auto;
            border-collapse: collapse;
            text-align: left;
            border: 1px solid #555;
        }

        .detail-table th,
        .detail-table td {
            padding: 6px 8px;
            border: 1px solid #555;
            text-align: left;
        }

        .detail-table th {
            background: #d9d9d9;
            font-size: 12px;
            font-weight: 700;
        }

        .detail-table .amount {
            text-align: right;
            padding-right: 16px;
        }

        .reason-box {
            width: 86%;
            margin: 16px auto 0;
            border: 1px solid #555;
            padding: 8px;
            min-height: 42px;
        }

        .reason-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .signature-grid {
            width: 86%;
            margin: 56px auto 0;
            display: table;
            border-collapse: collapse;
            font-size: 11px;
        }

        .signature-row {
            display: table-row;
        }

        .signature {
            display: table-cell;
            width: 50%;
            padding-bottom: 34px;
        }

        .signature.right {
            text-align: right;
        }

        .signature-title {
            text-decoration: underline;
        }

        .signature-name {
            margin-top: 2px;
            text-transform: uppercase;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 50px;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .vendor-advance-wrap,
            .vendor-advance-wrap * {
                visibility: visible !important;
            }

            .vendor-advance-wrap {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                margin: 0 !important;
                padding: 0;
                background: #fff;
                overflow: visible !important;
            }

            .vendor-advance-page {
                width: 100%;
                max-width: 100%;
                min-height: auto;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="vendor-advance-wrap">
        <div class="vendor-advance-page" id="report-content">
            <div class="vendor-advance-header">
                <h1 class="school-title"><img src="{{ $schoolTitleSrc }}" class="school-title-image" alt="The Lynx School"></h1>
                <div class="vendor-doc-title">{{ __('Vendor Advance Payment Voucher') }}</div>
                <img src="{{ $logoSrc }}" class="vendor-logo" alt="The Lynx School">
            </div>

            <table class="vendor-info">
                <tr>
                    <td class="label">{{ __('Vendor Name') }}</td>
                    <td class="value">{{ $vendor->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Vendor Number') }}</td>
                    <td class="value">{{ $vendor->vender_id ?? ($vendor->id ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Contact') }}</td>
                    <td class="value">{{ $vendor->contact ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Advance Month') }}</td>
                    <td class="value">{{ $advanceMonth }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Date of Approval') }}</td>
                    <td class="value">{{ $approvalDate }}</td>
                </tr>
                <tr class="amount-row">
                    <td class="label">{{ __('Approved Amount of Advance') }}</td>
                    <td class="value">{{ number_format($amount, 2) }}</td>
                </tr>
            </table>

            <div class="detail-heading">{{ __('Payment Detail') }}</div>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>{{ __('Voucher Type') }}</th>
                        <th>{{ __('Payment By') }}</th>
                        <th>{{ __('Bank Account') }}</th>
                        <th class="amount">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $voucherType }}</td>
                        <td>{{ $paymentMethod }}</td>
                        <td>{{ !empty($advance->bank) ? $advance->bank->bank_name . ' - ' . $advance->bank->holder_name : '-' }}</td>
                        <td class="amount">{{ number_format($amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="reason-box">
                <div class="reason-title">{{ __('Advance Reason') }}</div>
                <div>{{ $advance->advance_reason ?: '-' }}</div>
            </div>

            <div class="signature-grid">
                <div class="signature-row">
                    <div class="signature">
                        <div class="signature-title">1. {{ __('Prepared by') }}</div>
                        <div>{{ __("Account's Officer") }}</div>
                    </div>
                    <div class="signature right">
                        <div class="signature-title">2. {{ __('Checked by') }}</div>
                        <div>{{ __('Manager Finance') }}</div>
                    </div>
                </div>
                <div class="signature-row">
                    <div class="signature">
                        <div class="signature-title">3. {{ __('Received by') }}</div>
                        <div class="signature-name">{{ $vendor->name ?? '-' }}</div>
                    </div>
                    <div class="signature right">
                        <div class="signature-title">4. {{ __('Approved by') }}</div>
                        <div>{{ __('Managing Director') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
