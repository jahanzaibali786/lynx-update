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
        }

        .signature .sig-item {
            width: 150px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .signature .line {
            border-bottom: 1px solid #000;
            height: 25px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .signature p {
            margin-top: 5px;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 15px;
        }

        .remarks-lines {
            margin-top: 10px;
        }

        .remarks-line {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 8px;
        }
    </style>

    <!-- Buttons -->
    <div class="action-bar">
        <a class="btn btn-outline-primary btn-sm" onclick="printPDF()">
            <i class="ti ti-download" style="color:#fff;"></i> Download
        </a>
    </div>

    <div class="card p-3">
        <div class="admission-wrapper">

            <!-- Header -->
            <div class="two-col" style="align-items:center;">
                <div style="width:80px;"></div>

                <div style="flex:1; text-align:center;">
                    <img src="{{ asset('assets/images/lynxheadertext.png') }}" style="max-height:50px;" alt="logo">

                    <p style="margin:4px 0 0; font-size:12px;">
                        {{ @$adm_order->branches->name }}
                    </p>

                    <p class="title"><strong>ADMISSION ORDER</strong></p>
                </div>

                <div style="width:80px; text-align:right;">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="width:80px;">
                </div>
            </div>

            <br><br>

            <!-- Fields -->
            <div class="field-row">
                <div class="field-label">Student Name :</div>
                <div class="field-value">{{ $adm_order->stdname }}</div>
            </div>

            <div class="field-row">
                <div class="field-label">Father's Name :</div>
                <div class="field-value">{{ $adm_order->fathername }}</div>
            </div>

            <div class="field-row">
                <div class="field-label">Date of Birth :</div>
                <div class="field-value">
                    {{ \Carbon\Carbon::parse($adm_order->dob)->format('d-M-Y') }}
                </div>
            </div>

            <div class="field-row">
                <div class="field-label">Date of Admission :</div>
                <div class="field-value">
                    {{ $adm_order->enrollment ? date('d-M-Y', strtotime($adm_order->enrollment->adm_date)) : '' }}
                </div>
            </div>

            <!-- Class + Section -->
            <div class="field-row">
                <div class="field-label">Class to which Admitted :</div>

                <div class="field-value">
                    <div class="two-col">
                        <div class="line-bottom" style="flex:1;">
                            {{ $adm_order->class->name }}
                        </div>

                        <div style="white-space:nowrap;">Section:</div>

                        <div class="line-bottom" style="flex:1;">
                            {{ @$adm_order->enrollment->section->name }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field-label">Permanent Address :</div>
                <div class="field-value">{{ $adm_order->address }}</div>
            </div>

            <!-- Phone + Mobile -->
            <div class="field-row">
                <div class="field-label">Landline No :</div>

                <div class="field-value" style="border:none;">
                    <div class="two-col">
                        <div class="line-bottom" style="flex:1;">
                            {{ $adm_order->fatherphone }}
                        </div>

                        <div style="white-space:nowrap;">Mobile No :</div>

                        <div class="line-bottom" style="flex:1;">
                            {{ $adm_order->fathercell }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roll No -->
            <div class="field-row">
                <div class="field-label" style="width:auto;">
                    Entered in Admission Register and Allotted Roll No :
                </div>

                <div class="field-value">
                    {{ @$adm_order->enrollment->enrollId }}
                </div>
            </div>

            <br><br>
            <div class="field-row">
                <div class="field-label">Remarks:</div>
                <div style="flex:1;">
                    <div class="remarks-line"></div>
                    <div class="remarks-line"></div>
                    <div class="remarks-line"></div>
                </div>
            </div>
            <br><br><br>

            <!-- Footer -->
            <div class="signature">
                <div class="sig-item">
                    <div class="line"></div>
                    <p>School Stamp</p>
                </div>

                <div class="sig-item">
                    <div class="line">{{ date('Y-m-d') }}</div>
                    <p>Print Date</p>
                </div>

                <div class="sig-item">
                    <div class="line">
                        {{ @$adm_order->branch_name->headmaster_name->name ?? '' }}
                    </div>
                    <p>Head of institute</p>
                </div>
            </div>

            <br><br>

            <p>Copies to : Parents / Personal file / Class Teacher .</p>

        </div>
    </div>
@endsection
