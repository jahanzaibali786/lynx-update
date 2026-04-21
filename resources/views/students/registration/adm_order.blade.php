@extends('layouts.admin')

@section('page-title')
    {{ __('Admission Order') }}
@endsection

@push('script-page')
<script>
    function printPDF() {
        let url = "{{ route('admission.order', $adm_order->id) }}?print=pdf";
        window.open(url, '_blank');
    }
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Admission Order') }}</li>
@endsection

@section('content')

<style>
    .admission-wrapper {
        max-width: 750px;
        margin: 0 auto;
        padding: 30px;
        background: #fff;
    }

    .header-table {
        width: 100%;
    }

    .title {
        text-align: center;
        margin-top: 10px;
        font-size: 20px;
        font-weight: 500;
    }

    .field-row {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .field-label {
        width: 220px;
        font-weight: 500;
    }

    .field-value {
        flex: 1;
        border-bottom: 1px solid #000;
        min-height: 20px;
    }

    .two-col {
        display: flex;
        gap: 20px;
    }

    .two-col .col {
        flex: 1;
    }

    .signature {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
        text-align: center;
    }

    .signature div {
        width: 150px;
    }

    .line-top {
        border-top: 1px solid #000;
        height: 20px;
    }

    .line-bottom {
        border-bottom: 1px solid #000;
        height: 20px;
    }

    .action-bar {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 15px;
    }
</style>

<!-- Buttons -->
<div class="action-bar">
    <button class="btn btn-outline-primary btn-sm" onclick="generatePDF()">
        <i class="ti ti-download"></i> Download
    </button>

    <button class="btn btn-outline-success btn-sm" onclick="printPDF()">
        <i class="ti ti-printer"></i> Print
    </button>
</div>

<div class="card p-3">
    <div class="admission-wrapper">

        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="text-align:center;">
                    <p style="font-family: 'Edwardian Script ITC'; font-size:32px; margin:0;">
                        The Lynx School
                    </p>
                    <p style="margin:0;">{{ @$adm_order->branches->name }}</p>

                    <!-- Title under school -->
                    <div class="title">Admission Order</div>
                </td>

                <td style="text-align:right; width:120px;">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}"
                         style="max-width:90px;">
                </td>
            </tr>
        </table>

        <br>

        <!-- Fields -->
        <div class="field-row">
            <div class="field-label">Name :</div>
            <div class="field-value">{{ $adm_order->stdname }}</div>
        </div>

        <div class="field-row">
            <div class="field-label">Date Of Birth :</div>
            <div class="field-value">
                {{ \Carbon\Carbon::parse($adm_order->dob)->format('d-M-Y') }}
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">Father's Name :</div>
            <div class="field-value">{{ $adm_order->fathername }}</div>
        </div>

        <div class="field-row">
            <div class="field-label">Mother's Name :</div>
            <div class="field-value">{{ $adm_order->mothername }}</div>
        </div>

        <div class="field-row">
            <div class="field-label">Date of Admission :</div>
            <div class="field-value"></div>
        </div>

        <!-- Class + Section -->
        <div class="two-col">
            <div class="col">
                <div class="field-label">Class to which Admitted :</div>
                <div class="field-value">{{ $adm_order->class->name }}</div>
            </div>

            <div class="col">
                <div class="field-label">Section :</div>
                <div class="field-value">
                    {{ @$adm_order->enrollment->section->name }}
                </div>
            </div>
        </div>

        <br>

        <div class="field-row">
            <div class="field-label">Permanent Address :</div>
            <div class="field-value">{{ $adm_order->address }}</div>
        </div>

        <div class="two-col">
            <div class="col">
                <div class="field-label">Telephone No :</div>
                <div class="field-value">{{ $adm_order->fatherphone }}</div>
            </div>

            <div class="col">
                <div class="field-label">Mobile :</div>
                <div class="field-value">{{ $adm_order->fathercell }}</div>
            </div>
        </div>

        <br>

        <div class="field-row">
            <div class="field-label">
                Entered in admission Register and Allotted Roll No :
            </div>
            <div class="field-value">
                {{ @$adm_order->enrollment->enrollId }}
            </div>
        </div>

        <br>

        <p>Copies to : Parents / Personal file / Class Teacher / School File.</p>

        <br>

        <p>Remarks:</p>

        <br><br>

        <!-- Signatures -->
        <div class="signature">

            <div>
                <div class="line-top"></div>
                <p>School Stamp</p>
            </div>

            <div>
                <div class="line-bottom">{{ date('Y-m') }}</div>
                <p>Date</p>
            </div>

            <div>
                <div class="line-bottom">
                    {{ @$adm_order->branch_name->headmaster_name->name ?? '' }}
                </div>
                <p>Head of institute</p>
            </div>

        </div>

    </div>
</div>

@endsection