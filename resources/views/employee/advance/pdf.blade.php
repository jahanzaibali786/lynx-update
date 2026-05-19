@extends(!empty($isPdf) ? 'layouts.pdf' : 'layouts.admin')

@section('page-title')
{{ __('Employee Advance') }}
@endsection

@section('action-btn')
<div class="col text-end">
    <a class="btn mx-1 btn-sm btn-outline-success" href="{{ route('employee-advance.print', ['id' => $advance->id, 'download' => 1]) }}">
        <span class="btn-inner--icon">Download PDF</span>
    </a>
    <a class="btn mx-1 btn-sm btn-outline-primary" href="{{ route('employee-advance.print', ['id' => $advance->id, 'preview' => 1]) }}" target="_blank">
        <span class="btn-inner--icon">Preview PDF</span>
    </a>
    <a class="btn mx-1 btn-sm btn-outline-secondary" href="javascript:void(0);" onclick="window.print()">
        <span class="btn-inner--icon">Print</span>
    </a>
</div>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Advance') }}</li>
@endsection

@push('script-page')
<script>
    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'p') {
            event.preventDefault();
            window.print();
        }
    });
</script>
@endpush

@section('content')
@php
    $employee = $advance->employee;
    $branch = \Auth::user()->getbranch(@$employee->branch_id);
    $employeeName = trim((@$employee->salute ? @$employee->salute . ' ' : '') . @$employee->name);
    $employeeNumber = @$employee->employee_id ?: @$employee->id;
    $joiningDate = @$employee->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d M Y') : '-';
    $approvalDate = @$advance->approval_date ? \Carbon\Carbon::parse($advance->approval_date)->format('d M Y') : '-';
    $advanceMonth = @$advance->advance_date ? \Carbon\Carbon::parse($advance->advance_date)->startOfMonth()->format('d M Y') : '-';
    $logoSrc = !empty($isPdf) ? public_path('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
    $schoolTitleSrc = !empty($isPdf) ? public_path('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
    $total = (float) @$advance->advance_amount;
@endphp

<style>
    .advance-pdf-wrap {
        background: {{ !empty($isPdf) ? '#fff' : '#f4f4f4' }};
        padding: {{ !empty($isPdf) ? '0' : '20px 0' }};
        width: 100%;
    }
    .advance-pdf-page {
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
        line-height: 1.24;
        position: relative;
    }
    .advance-pdf-header {
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
    .advance-doc-title {
        display: inline-block;
        margin-top: 3px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: underline;
        text-transform: uppercase;
    }
    .advance-logo {
        position: absolute;
        top: -6px;
        right: 33px;
        width: 70px;
        height: 70px;
        object-fit: contain;
    }
    .advance-info {
        width: 100%;
        margin-top: 18px;
        border-collapse: collapse;
    }
    .advance-info td {
        padding: 3px 0;
        vertical-align: top;
    }
    .advance-info .label {
        width: 34%;
        font-weight: 600;
    }
    .advance-info .value {
        width: 66%;
        text-transform: uppercase;
    }
    .advance-amount-row td {
        padding-top: 18px;
        font-weight: 700;
    }
    .installment-heading {
        margin: 22px 0 8px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        text-decoration: underline;
        text-transform: uppercase;
    }
    .installment-table {
        width: 86%;
        margin: 0 auto;
        border-collapse: collapse;
        text-align: left;
        border: 1px solid #555;
    }
    .installment-table th,
    .installment-table td {
        padding: 4px 6px;
        border: 1px solid #555;
        text-align: left;
    }
    .installment-table th {
        background: #d9d9d9;
        font-size: 12px;
        font-weight: 700;
    }
    .installment-table .amount {
        text-align: right;
        padding-right: 16px;
    }
    .total-row td {
        font-weight: 700;
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
        margin: 46px auto 0;
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
        .advance-pdf-wrap,
        .advance-pdf-wrap * {
            visibility: visible !important;
        }
        .advance-pdf-wrap {
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
        .advance-pdf-page {
            width: 100%;
            max-width: 100%;
            min-height: auto;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="advance-pdf-wrap">
    <div class="advance-pdf-page" id="report-content">
        <div class="advance-pdf-header">
            <h1 class="school-title"><img src="{{ $schoolTitleSrc }}" class="school-title-image" alt="The Lynx School"></h1>
            <div class="advance-doc-title">Advance Salary Deduction Plan</div>
            <img src="{{ $logoSrc }}" class="advance-logo" alt="The Lynx School">
        </div>

        <table class="advance-info">
            <tr>
                <td class="label">Employee Name</td>
                <td class="value">{{ $employeeName }}</td>
            </tr>
            <tr>
                <td class="label">Date of Joining</td>
                <td class="value">{{ $joiningDate }}</td>
            </tr>
            <tr>
                <td class="label">Employee Number</td>
                <td class="value">{{ $employeeNumber }}</td>
            </tr>
            <tr>
                <td class="label">Date of Approval</td>
                <td class="value">{{ $approvalDate }}</td>
            </tr>
            <tr>
                <td class="label">Branch</td>
                <td class="value">{{ @$branch->name ?: '-' }}</td>
            </tr>
            <tr class="advance-amount-row">
                <td class="label">Approved Amount of Advance</td>
                <td class="value">{{ number_format($total, 2) }}</td>
            </tr>
        </table>

        <div class="installment-heading">Deduction Plan</div>
        <table class="installment-table">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Month</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $advanceMonth }}</td>
                    <td class="amount">{{ number_format($total, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td></td>
                    <td>TOTAL</td>
                    <td class="amount">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="reason-box">
            <div class="reason-title">Advance Reason</div>
            <div>{{ @$advance->advance_reason ?: '-' }}</div>
        </div>

        <div class="signature-grid">
            <div class="signature-row">
                <div class="signature">
                    <div class="signature-title">1. Prepared by</div>
                    <div>Account's Officer</div>
                </div>
                <div class="signature right">
                    <div class="signature-title">2. Checked by</div>
                    <div>Manager Finance</div>
                </div>
            </div>
            <div class="signature-row">
                <div class="signature">
                    <div class="signature-title">3. Received by</div>
                    <div class="signature-name">{{ @$employee->name }}</div>
                </div>
                <div class="signature right">
                    <div class="signature-title">4. Approved by</div>
                    <div>Managing Director</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
