@extends('layouts.admin')
@section('page-title')
{{__('Generate Challan')}}
@endsection
@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
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
            html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
                window.open(pdf);
            });
        }
    </script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Generate Challan')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    {{-- <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button> --}}
    <form action="{{ route('installment-challan', $challan->id) }}" method="POST" >
        @csrf
        @foreach($feeHeadIds as $index => $feeHeadId)
            <input type="hidden" name="type" value="submit">
            <input type="hidden" name="fee_head_id[]" value="{{ $feeHeadId }}">
            <input type="hidden" name="head_amount_inst1[]" value="{{ $inst1Amounts[$index] }}">
            <input type="hidden" name="head_amount_inst2[]" value="{{ $inst2Amounts[$index] }}">
        @endforeach

        <button class="btn btn-outline-info" type="submit" style="float: right">Save</button>
    <button class="btn btn-outline-success" style="margin-right: 10px" onclick="printPDF()">Print PDF</button>
</div>
@endsection
@section('content')
<style>
    .copy-title {
        float: right;
    }

    .details {
        margin-top: 30px;
    }

    .details p {
        font-size: 1rem;
        line-height: 1rem;
    }

    .col-md-6 button {
        width: 100%;
    }
</style>
<div class="card mt-4" id="challan-content">
    <div class="row">
        <div class="col-md-6 border p-4">
            @php
                $st_id = $challan->student_id;
                $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section', 'branches')
                    ->where('id', $st_id)
                    ->first();
            @endphp
            <p class="copy-title">1st installment </p><br>
            <div class="logo-heading"
                style="display: flex; gap:20px; justify-content:space-between; align-items:center; width:100%;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
                <div class="heading ms-4" style="border: 3px solid black; text-align:center;">
                    <p><b>HBL CMD SATELLITE TOWN NURSERY BRANCH RAWALPINDI 00427991875503</b></p>
                </div>
            </div><br>
            <p style="font-family:Curlz MT;font-size:X-Large; text-align:center; font-weight:600;">The Lynx School</p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">{{@$studentData->branches->name}}</p>
            <div class="dates" style="display: flex; justify-content:space-between;">
                <div class="issue" style="width:150px; line-height:1.3rem">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Issue Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                    </p>
                </div>
                <div class="due" style="border: 1px solid black; width:150px; line-height:1.3rem;">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Due Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="details row">
                <div  class="col-md-6">
                    <p style="width: 100%;"> <b>Challan#</b> <span>{{$challan->challanNo}}</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"> <b>Billing Month</b>
                        <span>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</span>
                    </p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Name:</b> <span>{{ @$studentData->stdname }}</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Class:</b> <span>{{@$studentData->class->name}}</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Roll No:</b> <span>Not Enrolled</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Section:</b> <span>null</span></p>
                </div>

            </div>
            <div class="withdrawhead"
                style="border-bottom: 5px solid black; border-top: 5px solid black; padding-top:10px;">
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{$challan->challan_type}} 1st Installment </p>
            </div>
            <p style="display:flex; justify-content:space-between; font-size:1rem; text-decoration:underline;">
                <b>Description</b> <b>Amount</b>
            </p>
            @php $arrearsTotal = 0 @endphp

            @php
                $totalAmount = 0;
            @endphp

            @foreach ($challan->heads as $key => $head)
                @if(in_array($head->feeHead['id'], $feeHeadIds) && $inst1Amounts[$key] == 100)
                    <p style="display:flex; justify-content:space-between; font-size:0.8rem; line-height:0.5rem;">
                        <span>{{ $head->feeHead['fee_head'] }} ({{ $head['price'] }})</span>
                        <span>{{ $head['price'] - $head['concession'] }}</span>
                        @php
                            $totalAmount += $head['price'] - $head['concession'] ;
                        @endphp
                    </p>
                @endif
            @endforeach


            <div class="row">
                <p class="d-flex justify-content-end" style=" font-size:1rem;">
                    <span>Total :</span>
                    <span
                        style="border-bottom: 1px solid black; border-top: 1px solid black; padding:1px 0px 0px 50px;">{{ number_format(($totalAmount - $challan->paid_amount), 2) }}</span>
                </p>

            </div>
            <br>
            <p style="float:right; font-size:1rem;">
                <span>Payable By Due Date</span>
                <span
                    style="border-bottom: 1px solid black; border-top: 1px solid black; padding:1px 0px 0px 50px;">{{ number_format($totalAmount, 2) }}</span>
            </p>
        </div>
        <div class="col-md-6 border p-4">
            <p class="copy-title">2nd installment </p><br>
            <div class="logo-heading"
                style="display: flex; gap:20px; justify-content:space-between; align-items:center; width:100%;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
                <div class="heading ms-4" style="border: 3px solid black; text-align:center;">
                    <p><b>HBL CMD SATELLITE TOWN NURSERY BRANCH RAWALPINDI 00427991875503</b></p>
                </div>
            </div><br>
            <p style="font-family:Curlz MT;font-size:X-Large; text-align:center; font-weight:600;">The Lynx School</p>
            <p style="text-align:center; font-weight:900; font-size:1rem;">{{@$studentData->branches->name}}</p>
            <div class="dates" style="display: flex; justify-content:space-between;">
                <div class="issue" style="width:150px; line-height:1.3rem">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Issue Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}
                    </p>
                </div>
                <div class="due" style="border: 1px solid black; width:150px; line-height:1.3rem;">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Due Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->due_date)->addMonth()->format('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="details row">
                <div  class="col-md-6">
                    <p style="width: 100%;"> <b>Challan#</b> <span>...................</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"> <b>Billing Month</b>
                        <span>{{ \Carbon\Carbon::parse($challan->challan_date)->addMonth()->format('F,Y') }}</span>
                    </p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Name:</b> <span>{{ @$studentData->stdname }}</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Class:</b> <span>{{@$studentData->class->name}}</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Roll No:</b> <span>Not Enrolled</span></p>
                </div>
                <div  class="col-md-6">
                    <p style="width: 100%;"><b>Section:</b> <span>null</span></p>
                </div>

            </div>
            <div class="withdrawhead"
                style="border-bottom: 5px solid black; border-top: 5px solid black; padding-top:10px;">
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{$challan->challan_type}} 2nd Installment </p>
            </div>
            <p style="display:flex; justify-content:space-between; font-size:1rem; text-decoration:underline;">
                <b>Description</b> <b>Amount</b>
            </p>
            @php
                $totalAmount2 = 0;
            @endphp

            @foreach ($challan->heads as $key => $head)
                @if(in_array($head->feeHead['id'], $feeHeadIds) && $inst2Amounts[$key] == 100)
                    <p style="display:flex; justify-content:space-between; font-size:0.8rem; line-height:0.5rem;">
                        <span>{{ $head->feeHead['fee_head'] }} ({{ $head['price'] }})</span>
                        <span>{{ $head['price'] - $head['concession'] }}</span>
                        @php
                            $totalAmount2 += $head['price'] - $head['concession'] ;
                        @endphp
                    </p>
                @endif
            @endforeach

            <div class="row">
                <p class="d-flex justify-content-end" style=" font-size:1rem;">
                    <span>Total :</span>
                    <span
                        style="border-bottom: 1px solid black; border-top: 1px solid black; padding:1px 0px 0px 50px;">{{ number_format(($totalAmount2 - $challan->paid_amount), 2) }}</span>
                </p>

            </div>
            <br>
            <p style="float:right; font-size:1rem;">
                <span>Payable By Due Date</span>
                <span
                    style="border-bottom: 1px solid black; border-top: 1px solid black; padding:1px 0px 0px 50px;">{{ number_format($totalAmount2, 2) }}</span>
            </p>
        </div>


    </div>
</div>
@endsection
