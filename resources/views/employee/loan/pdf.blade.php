@extends(!empty($isPdf) ? 'layouts.pdf' : 'layouts.admin')

@section('page-title')
{{ __('Employee Loan') }}
@endsection

@section('action-btn')
<div class="col text-end">
<a class="btn mx-1 btn-sm btn-outline-success" href="{{ route('printloan', ['id' => $loan->id, 'download' => 1]) }}"><span class="btn-inner--icon">Download PDF</span></a>
<a class="btn mx-1 btn-sm btn-outline-primary" href="{{ route('printloan', ['id' => $loan->id, 'preview' => 1]) }}" target="_blank"><span class="btn-inner--icon">Preview PDF</span></a>
<a class="btn mx-1 btn-sm btn-outline-primary" href="javascript:void(0);" onclick="window.print()"><span class="btn-inner--icon">Print</span></a>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Loan') }}</li>
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
    $employee = $loan->employee;
    $branch = \Auth::user()->getbranch(@$employee->branch_id);
    $employeeName = trim((@$employee->salute ? @$employee->salute . ' ' : '') . @$employee->name);
    $employeeNumber = @$employee->employee_id ?: @$employee->id;
    $joiningDate = @$employee->company_doj ? \Carbon\Carbon::parse($employee->company_doj)->format('d M Y') : '-';
    $approvalDate = @$loan->approval_date ? \Carbon\Carbon::parse($loan->approval_date)->format('d M Y') : '-';
    $startDate = @$loan->from_pay_month ? \Carbon\Carbon::parse($loan->from_pay_month)->startOfMonth() : now()->startOfMonth();
    $payPeriod = max(1, (int) @$loan->pay_period);
    $installmentRows = collect();

    if ($loan->relationLoaded('installments') && $loan->installments->isNotEmpty()) {
        $installmentRows = $loan->installments->map(function ($installment) {
            return [
                'no' => $installment->installment_no,
                'month' => \Carbon\Carbon::parse($installment->due_month)->format('M Y'),
                'amount' => (float) $installment->amount,
            ];
        });
    } else {
        $installmentAmounts = \App\Models\Loan::roundedInstallmentAmounts((float) @$loan->amount, $payPeriod);
        foreach ($installmentAmounts as $index => $amount) {
            $installmentRows->push([
                'no' => $index + 1,
                'month' => $startDate->copy()->addMonths($index)->format('M Y'),
                'amount' => (float) $amount,
            ]);
        }
    }

    $titleType = strtoupper(@$loan->emp_sec ?: 'GPF');
    $logoSrc = !empty($isPdf) ? public_path('assets/images/lynx2.jpg') : asset('assets/images/lynx2.jpg');
    $schoolTitleSrc = !empty($isPdf) ? public_path('assets/images/lynxheadertext.jpg') : asset('assets/images/lynxheadertext.jpg');
    $isCompactPlan = $installmentRows->count() >= 10;
    $total = 0;
@endphp

<style>
    .loan-pdf-wrap {
        background: {{ !empty($isPdf) ? '#fff' : '#f4f4f4' }};
        padding: {{ !empty($isPdf) ? '0' : '20px 0' }};
        width: 100%;
    }
    .loan-pdf-page {
        width: {{ !empty($isPdf) ? '100%' : '794px' }};
        max-width: 100%;
        min-height: {{ !empty($isPdf) ? 'auto' : '1123px' }};
        margin: 0 auto;
        padding: {{ !empty($isPdf) ? '50px 0 20px' : '112px 50px 70px' }};
        box-sizing: border-box;
        background: #fff;
        color: #222;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
        line-height: 1.35;
        position: relative;
    }
    .loan-pdf-header {
        position: relative;
        min-height: 82px;
        text-align: center;
    }
    .loan-school-title {
        margin: 0;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 30px;
        font-weight: 600;
        letter-spacing: .5px;
    }
    .loan-school-title-image {
        width: 245px;
        max-width: 100%;
        height: auto;
        display: inline-block;
    }
    .loan-doc-title {
        display: inline-block;
        margin-top: 6px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: underline;
        text-transform: uppercase;
    }
    .loan-logo {
        position: absolute;
        top: -10px;
        right: 33px;
        width: 85px;
        height: 84px;
        object-fit: contain;
    }
    .loan-info {
        width: 100%;
        margin-top: 26px;
        border-collapse: collapse;
    }
    .loan-info td {
        padding: 5px 0;
        vertical-align: top;
    }
    .loan-info .label {
        width: 34%;
        font-weight: 600;
    }
    .loan-info .value {
        width: 66%;
        text-transform: uppercase;
    }
    .loan-amount-row td {
        padding-top: 30px;
        font-weight: 700;
    }
    .installment-heading {
        margin: 34px 0 12px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        text-decoration: underline;
        text-transform: uppercase;
    }
    .installment-table {
        width: 82%;
        margin: 0 auto;
        border-collapse: collapse;
        text-align: left;
        border: 1px solid #555;
    }
    .installment-table th,
    .installment-table td {
        padding: 6px 8px;
        border: 1px solid #555;
        text-align: left;
    }
    .installment-table th {
        background: #d9d9d9;
        font-size: 13px;
        font-weight: 700;
    }
    .installment-table .amount {
        text-align: right;
        padding-right: 28px;
    }
    .installment-total td {
        padding-top: 6px;
        font-weight: 700;
    }
    .signature-grid {
        width: 86%;
        margin: 66px auto 0;
        display: table;
        border-collapse: collapse;
        font-size: 12px;
    }
    .signature-row {
        display: table-row;
    }
    .signature {
        display: table-cell;
        width: 50%;
        min-height: 54px;
        padding-bottom: 66px;
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
    .loan-pdf-page.compact {
        padding: {{ !empty($isPdf) ? '25px 0 8px' : '64px 50px 42px' }};
        font-size: 12px;
        line-height: 1.22;
    }
    .compact .loan-pdf-header {
        min-height: 64px;
    }
    .compact .loan-school-title {
        margin: 0;
    }
    .compact .loan-school-title-image {
        width: 215px;
    }
    .compact .loan-doc-title {
        margin-top: 2px;
        font-size: 12px;
    }
    .compact .loan-logo {
        top: -6px;
        width: 70px;
        height: 70px;
    }
    .compact .loan-info {
        margin-top: 12px;
    }
    .compact .loan-info td {
        padding: 3px 0;
    }
    .compact .loan-amount-row td {
        padding-top: 16px;
    }
    .compact .installment-heading {
        margin: 18px 0 7px;
        font-size: 13px;
    }
    .compact .installment-table {
        width: 86%;
    }
    .compact .installment-table th,
    .compact .installment-table td {
        padding: 3px 6px;
    }
    .compact .installment-table th {
        font-size: 12px;
    }
    .compact .installment-table .amount {
        padding-right: 16px;
    }
    .compact .signature-grid {
        margin-top: 34px;
        font-size: 11px;
    }
    .compact .signature {
        padding-bottom: 28px;
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
        .loan-pdf-wrap,
        .loan-pdf-wrap * {
            visibility: visible !important;
        }
        .loan-pdf-wrap {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            margin: 0 !important;
            overflow: visible !important;
        }
        .loan-pdf-wrap {
            padding: 0;
            background: #fff;
        }
        .loan-pdf-page {
            width: 100%;
            max-width: 100%;
            min-height: auto;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .loan-pdf-page.compact {
            padding: 0 !important;
        }
    }
</style>

<div class="loan-pdf-wrap">
    <div class="loan-pdf-page {{ $isCompactPlan ? 'compact' : '' }}" id="report-content">
        <div class="loan-pdf-header">
            <h1 class="loan-school-title"><img src="{{ $schoolTitleSrc }}" class="loan-school-title-image" alt="The Lynx School"></h1>
            <div class="loan-doc-title">{{ $titleType }} Loan Deduction Plan</div>
            <img src="{{ $logoSrc }}" class="loan-logo" alt="The Lynx School">
        </div>

        <table class="loan-info">
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
            <tr class="loan-amount-row">
                <td class="label">Approved Amount of Loan</td>
                <td class="value">{{ number_format((float) @$loan->amount, 2) }}</td>
            </tr>
        </table>

        <div class="installment-heading">Installment Plan</div>
        <table class="installment-table">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Months</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($installmentRows as $row)
                    @php
                        $total += (float) $row['amount'];
                    @endphp
                    <tr>
                        <td>{{ $row['no'] }}</td>
                        <td>{{ $row['month'] }}</td>
                        <td class="amount">{{ number_format((float) $row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="installment-total">
                    <td></td>
                    <td>TOTAL</td>
                    <td class="amount">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-grid">
            <div class="signature-row">
                <div class="signature">
                    <div class="signature-title">1. Prepared by</div>
                    <div>Account's Officer</div>
                </div>
                <div class="signature right">
                    <div class="signature-title" style="padding-right: 17px;">2. Checked by</div>
                    <div>Manager Finance</div>
                </div>
            </div>
            <div class="signature-row">
                <div class="signature">
                    <div class="signature-title" >3. Received by</div>
                    <div class="signature-name">{{ @$employee->name }}</div>
                </div>
                <div class="signature right">
                    <div class="signature-title" style="padding-right: 18px;">4. Approved by</div>
                    <div>Managing Director</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
