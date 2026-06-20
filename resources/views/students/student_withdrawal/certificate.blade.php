@extends('layouts.admin')
@section('page-title')
{{__('Clearance Certificate')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{asset('js/jquery.repeater.min.js')}}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<style>
    .certificate-container {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        padding: 40px 50px;
        border-radius: 10px;
        box-shadow: 0 0 10px #e0e0e0;
    }
    .certificate-title {
        text-align: center;
        font-size: 2.2em;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .certificate-subtitle {
        text-align: center;
        font-size: 1.3em;
        font-weight: bold;
        margin-bottom: 30px;
    }
    .certificate-table {
        width: 100%;
        margin-bottom: 20px;
    }
    .certificate-table th,
    .certificate-table td {
        padding: 6px 10px;
        vertical-align: top;
    }
    .deduction-header {
        background: #888;
        color: #fff;
        font-weight: bold;
        padding: 6px 10px;
        border-radius: 3px;
    }
    .signature-line {
        border-top: 1px solid #222;
        width: 90%;
        margin: 30px auto 5px auto;
        text-align: center;
        padding-top: 2px;
    }
    .acknowledgement {
        text-align: center;
        font-weight: bold;
        margin: 20px 0 10px 0;
    }
    .ack-row {
        margin-top: 30px;
    }
    .ack-row .col {
        text-align: center;
    }
    .date-line {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 20px;
    }
    .date-line b {
        border-bottom: 1px solid #222;
        min-width: 180px;
        display: inline-block;
        margin-left: 10px;
    }
</style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Clearance Certificate')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    {{-- @can('create session') --}}
    {{-- @endcan --}}
    {{-- print button --}}
    <a href="{{ route('student_withdrawal.certificate_pdf', $withdrawal->id) }}" class="btn btn-sm btn-primary" target="_blank" data-bs-title="{{ __('Print') }}">
        <span class="btn-inner--icon">Print</span>
    </a>
</div>
@endsection
@section('content')
<div class="certificate-container">
    <div>
        <div class="certificate-title">The Lynx School</div>
        <div class="certificate-subtitle">Certificate / Final Settlement</div>
        <table class="certificate-table">
            <tr>
                <th>Branch:</th>
                <td>{{ $branch ? $branch->name : '-' }}</td>
            </tr>
            <tr>
                <th>Name:</th>
                <td style="text-transform:uppercase;"><b>{{ $student ? $student->stdname : '-' }}</b></td>
            </tr>
            <tr>
                <th>Roll No:</th>
                <td>{{ $student ? $student->roll_no : '-' }}</td>
            </tr>
            <tr>
                <th>Class:</th>
                <td>{{ $class ? $class->name : '-' }}</td>
            </tr>
            <tr>
                <th>Admission Date:</th>
                <td>{{ $enrollment ? \Carbon\Carbon::parse($enrollment->adm_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Application Date:</th>
                <td>{{ $withdrawal ? \Carbon\Carbon::parse($withdrawal->apply_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Withdrawal Date:</th>
                <td>{{ $withdrawal ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Reason of Leaving:</th>
                <td><b>{{ $withdrawal ? $withdrawal->reason : '-' }}</b></td>
            </tr>
            <tr>
                <th>Security Deposit:</th>
                <td>{{ number_format($securityDeposit, 2) }}</td>
            </tr>
            <tr>
                <th>Date of Deposit:</th>
                <td>{{ $securityDepositDate ? \Carbon\Carbon::parse($securityDepositDate)->format('d M Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Last Month Paid Amount:</th>
                <td>{{ $lastPaidChallan ? number_format($lastPaidChallan->paid_amount, 2) : '0.00' }}</td>
            </tr>
            <tr>
                <th>Tuition Fee Paid upto:</th>
                <td>{{ $lastPaidChallan ? strtoupper(\Carbon\Carbon::parse($lastPaidChallan->fee_month)->format('M-Y')) : '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="deduction-header">Deduction</td>
            </tr>
            <tr>
                <th>Notice Fee:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Other Fee:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Actual Fee:</th>
                <td>{{ $arrearsTotal ? number_format($arrearsTotal, 2) : '-' }}</td>
            </tr>
            <tr>
                <th>Refund:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Other Deduction:</th>
                <td>0.00</td>
            </tr>
            <tr>
                <th>Total Payables:</th>
                <td>{{ number_format($totalPayables, 2) }}</td>
            </tr>
            <tr>
                <th>Total Receivables:</th>
                <td>{{ number_format($totalReceivables, 2) }}</td>
            </tr>
            <tr>
                <th>Net Balance:</th>
                <td>{{ number_format($netBalance, 2) }}</td>
            </tr>
            <tr>
                <th>Check Issued in favour of:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Cheque No:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Bank Name:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>Cheque Date:</th>
                <td>-</td>
            </tr>
            <tr>
                <th>HO Remarks:</th>
                <td>{!! $withdrawal && $withdrawal->ho_remarks ? $withdrawal->ho_remarks : '-' !!}</td>
            </tr>
        </table>

        <div class="row ack-row">
            <div class="col">
                <div class="signature-line">Prepared by</div>
            </div>
            <div class="col">
                <div class="signature-line">Checked by</div>
            </div>
            <div class="col">
                <div class="signature-line">Approved by</div>
            </div>
        </div>
        <div class="acknowledgement">Acknowledgement of receipt</div>
        <div>
            <p>
                I <b>___________________________________________________________</b> do hereby confirm that I have received my entire dues from <span>The Lynx School</span>, SMC Pvt Ltd and I have no claim on the school.
            </p>
        </div>
        <div class="date-line">
            Date: <b>&nbsp;</b>
        </div>
    </div>
</div>
@endsection