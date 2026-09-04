@extends('layouts.admin')

@section('page-title')
    {{ __('StudyPack Booklist') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('studypackchallan.index') }}">{{ __('StudyPack Challans') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Booklist') }}</li>
@endsection

@section('action-btn')
    <div class="float-end" style="display: flex; gap: 10px;">
        <form action="{{ route('studypackchallan.booklist.download', $challan->id) }}" method="POST">
            @csrf

            <input type="hidden" name="type" value="download">

            <button
                class="btn btn-outline-primary"
                data-bs-title="{{ __('Download Booklist') }}"
                type="submit"
            >
                Download Booklist
            </button>
        </form>

        <form action="{{ route('studypackchallan.booklist.print', $challan->id) }}" method="GET">
            <input type="hidden" name="type" value="print">

            <button
                class="btn btn-outline-success"
                data-bs-title="{{ __('Print Booklist') }}"
                type="submit"
            >
                Print Booklist
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

        .booklist-details-table {
            width: 100% !important;
            margin-bottom: 10px !important;
            border-collapse: collapse !important;
        }

        .booklist-details-table,
        .booklist-details-table tr,
        .booklist-details-table td {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        .booklist-details-table td {
            padding: 2px 8px 2px 0 !important;
            font-size: 13px !important;
            vertical-align: top !important;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #cfcfcf;
            padding: 3px 6px;
            font-size: 12px;
            line-height: 1.15;
        }

        .items-table th {
            font-weight: 700;
            text-align: center;
        }

        .items-table td:nth-child(1),
        .items-table td:nth-child(3) {
            text-align: center;
            width: 60px;
        }

        .items-table td:nth-child(2) {
            width: auto;
        }

        .student-info {
            margin-top: 50px;
            width: 100%;
            max-width: 420px;
        }

        .student-info-row {
            display: table;
            width: 100%;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .student-info-label,
        .student-info-value {
            display: table-cell;
            vertical-align: bottom;
        }

        .student-info-label {
            width: 140px;
            font-weight: 700;
            white-space: nowrap;
        }

        .student-info-value {
            border-bottom: 1px solid #000;
            height: 18px;
            padding-left: 10px;
            float: left;
        }

        .remarks-section {
            margin-top: 26px;
            width: 100%;
            font-size: 13px;
        }

        .remarks-line {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            height: 18px;
            margin: 6px 0 0 6px;
        }

        .signature-section {
            margin-top: 32px;
            width: 100%;
            font-size: 13px;
        }

        .signature-row {
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .sig-line {
            display: inline-block;
            width: 58%;
            border-bottom: 1px solid #000;
            height: 18px;
            vertical-align: bottom;
            margin-left: 6px;
        }
    </style>

    <div class="card">
        <div class="card-body">
            @php
                $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section', 'branches', 'branch_name')->where('id', $challan->student_id)->first();
                $items = \App\Models\StudyPackChallanItems::with('product', 'product.taxsingle')->where('challan_id', $challan->id)->get();
                $totalItems = $items->count();
                $totalQuantity = 0;
                $totalAmount = 0;
            @endphp

            <div class="page">
                <div class="title-block" style="display: flex; align-items: center; justify-content: flex-start; gap: 18px; margin-top: 18px; margin-bottom: 28px;">
                    <img class="title-logo" src="{{ asset('assets/images/lynx2.jpg') }}" alt="logo" style="display: block; width: 86px; height: auto; flex: 0 0 auto; align-self: center;">
                    <div class="title-copy" style="text-align: center; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding-top: 0; margin-top: -60px;">
                        <img class="school-title" src="{{ asset('assets/images/lynxheadertext.png') }}" alt="School Header Text" style="display: block; width: 330px; max-width: 100%; height: auto; margin: 0 auto 6px auto;">
                        @php
                            $className = optional($challan->class)->name ?? '';
                            $sessionYear = optional($challan->session)->year;
                            if (empty($sessionYear)) {
                                $activeSession = \App\Models\Session::where('active_status', 1)->first();
                                if (empty($activeSession)) {
                                    $activeSession = \App\Models\Session::orderByDesc('id')->first();
                                }
                                $sessionYear = optional($activeSession)->year;
                            }
                        @endphp
                        <div class="subtitle" style="font-family: 'Times New Roman', serif; font-size: 18px; font-weight: 700; margin: 0;">Book List {{ $className }} {{ $sessionYear }}</div>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Sr #</th>
                            <th>Books Name</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)

                            @php
                                $qty = (int) ($item->quantity ?? $item->qty ?? 0);
                                $price = (float) ($item->price ?? 0);
                                $lineAmount = $qty * $price;
                                $totalQuantity += $qty;
                                $totalAmount += $lineAmount;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name ?? '' }}</td>
                                <td>{{ $qty }}</td>
                            </tr>
                        @endforeach
                        {{-- // total Items --}}
                        <tr style="background: #f8f6f6; color: #000; font-weight: bold;">
                            <td colspan="2" style="text-align: center; font-weight: bold;">Total Items</td>
                            <td style="text-align: center;">{{ $totalQuantity }}</td>
                        </tr>
                        {{-- //total Amount --}}
                        <tr style="background: #f8f6f6; color: #000; font-weight: bold;">
                            <td colspan="2" style="text-align: center; font-weight: bold;">Total Amount</td>
                            <td style="text-align: center;">{{ number_format($totalAmount, 0) }}</td>
                        </tr>

                    </tbody>
                </table>
                <div class="student-info">
                    @isset($studentData->roll_no)  
                    <div class="student-info-row">
                        <div class="student-info-label">Roll No:</div>
                        <div class="student-info-value">{{ $studentData->roll_no ?? '' }}</div>
                    </div>
                    @endisset
                    <div class="student-info-row">
                        <div class="student-info-label">Student Name:</div>
                        <div class="student-info-value">{{ $studentData->stdname ?? '' }}</div>
                    </div>
                    <div class="student-info-row">
                        <div class="student-info-label">Missing Items if any:</div>
                        <div class="student-info-value"></div>
                    </div>
                </div>

                <div class="remarks-section">
                    <b>Remarks:</b>
                    <span class="remarks-line"></span>
                    <span class="remarks-line"></span>
                    <span class="remarks-line"></span>
                </div>
                <br><br>
                <div class="signature-section">
                    <div class="signature-row">
                        <div class="signature-col">Recevied by : <span class="sig-line"></span></div>
                        <div class="signature-col" style="text-align:right;">Date: <span class="sig-line"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
