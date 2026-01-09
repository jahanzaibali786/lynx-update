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
            format: [700, 1000],
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
            format: [700, 1000],
            orientation: 'landscape'
        }
    };
    html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
        window.open(pdf);
    });
}
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Generate Challan')}}</li>
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
        <div class="col-md-4 border p-4">
            <p class="copy-title">Bank Copy</p><br>
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
            <p style="text-align:center; font-weight:900; font-size:1rem;">I-8/4 Senior Branch Islamabad</p>
            <div class="dates" style="display: flex; justify-content:space-between;">
                <div class="issue" style="width:150px; line-height:1.3rem">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Issue Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}</p>
                </div>
                <div class="due" style="border: 1px solid black; width:150px; line-height:1.3rem;">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Due Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}</p>
                </div>
            </div>
            <div class="details">
                <p style="width: 100%;"> <b>Challan#</b> <span>{{$challan->challanNo}}</span></p>
                <p style="width: 100%;"> <b>Billing Month</b>
                    <span>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</span>
                </p>
                @php
                $st_id = $challan->student_id;
                $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section')
                ->where('id', $st_id)
                ->first();
                @endphp

                <p style="width: 100%;"><b>Name:</b> <span>{{ $studentData->stname }}</span></p>

                @if($studentData->enrollment != null)
                <div class="row">
                    @if($studentData->enrollment->class != null)
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px">{{ $studentData->enrollment->class->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px;">null</p>
                    @endif

                    @if($studentData->enrollment->section != null)
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">{{ $studentData->enrollment->section->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">null</p>
                    @endif
                    <p style="width: 100%;"><b>Roll No:</b> <span>{{ $studentData->enrollment->enrollId }}</span></p>
                </div>
                @else
                <div class="row">
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px;">null</p>
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px;">null</p>
                </div>
                <p style="width: 100%;"><b>Roll No:</b> <span>Not Enrolled</span></p>
                @endif



            </div>
            <div class="withdrawhead"
                style="border-bottom: 5px solid black; border-top: 5px solid black; padding-top:10px;">
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{$challan->challan_type}}</p>
            </div>
            <p style="display:flex; justify-content:space-between; font-size:1rem; text-decoration:underline;">
                <b>Description</b> <b>Amount</b>
            </p>


            @foreach ($heads as $head)
            <p style="display:flex; justify-content:space-between; font-size:1rem;">
                <span>{{ $head['name'] }}</span>
                <span>{{ $head['amount'] }}</span>
            </p>
            @endforeach
            @php
            $totalAmount = 0;
            foreach ($heads as $head) {
            $totalAmount += $head['amount'];
            }
            @endphp
            <p style="float:right; font-size:1rem;">
                <span>Payable By Due Date</span>
                <span
                    style="border-bottom: 1px solid black; border-top: 1px solid black;padding:1px 0px 0px 50px ;">{{ number_format($totalAmount, 2) }}</span>
            </p>
            <br><br>

            <div style="background-color: gray; font-weight:900; font-size:.8rem; ">
                COMPULSORY INSTRUCTION FOR BANK
            </div>
            <P style="font-size:.8rem;">Please mention challan # / student name in description to avoid dispancy</P>
        </div>
        <div class="col-md-4 border p-4">
            <p class="copy-title">School Copy</p><br>
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
            <p style="text-align:center; font-weight:900; font-size:1rem;">I-8/4 Senior Branch Islamabad</p>
            <div class="dates" style="display: flex; justify-content:space-between;">
                <div class="issue" style="width:150px; line-height:1.3rem">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Issue Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}</p>
                </div>
                <div class="due" style="border: 1px solid black; width:150px; line-height:1.3rem;">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Due Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}</p>
                </div>
            </div>
            <div class="details">
                <p> <b>Challan#</b> <span>{{$challan->challanNo}}</span></p>
                <p><b>Billing Month:</b>
                    <span>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F . Y') }}</span>
                </p>
                </p>
                <p style="width: 100%;"><b>Name:</b> <span>{{ $studentData->stname }}</span></p>
                @if($studentData->enrollment != null)
                <div class="row">
                    @if($studentData->enrollment->class != null)
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px;">{{ $studentData->enrollment->class->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px;">null</p>
                    @endif

                    @if($studentData->enrollment->section != null)
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">{{ $studentData->enrollment->section->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">null</p>
                    @endif
                    <p style="width: 100%;"><b>Roll No:</b> <span>{{ $studentData->enrollment->enrollId }}</span></p>
                </div>
                @else
                <div class="row">
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px;">null</p>
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px;">null</p>
                </div>
                <p style="width: 100%;"><b>Roll No:</b> <span>Not Enrolled</span></p>
                @endif
            </div>
            <div class="withdrawhead"
                style="border-bottom: 5px solid black; border-top: 5px solid black; padding-top:10px;">
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{$challan->challan_type}}</p>
            </div>
            <p style="display:flex; justify-content:space-between; font-size:1rem; text-decoration:underline;">
                <b>Description</b> <b>Amount</b>
            </p>
            @foreach($heads as $head)
            <p style="display:flex; justify-content:space-between; font-size:1rem;">
                <span>{{ $head['name'] }}</span>
                <span>{{ $head['amount'] }}</span>
            </p>
            @endforeach
            <p style="float:right; font-size:1rem; "> <span>Payable By Due Date</span> <span
                    style="border-bottom: 1px solid black; border-top: 1px solid black;padding:1px 0px 0px 50px ;">{{ number_format($totalAmount, 2) }}</span>
            </p><br><br>
            <div style="background-color: gray; font-weight:900; font-size:.8rem; ">
                COMPULSORY INSTRUCTION FOR BANK
            </div>
            <P style="font-size:.8rem;">Please mention challan # / student name in description to avoid dispancy</P>
        </div>
        <div class="col-md-4 border p-4">
            <p class="copy-title">Student Copy</p><br>
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
            <p style="text-align:center; font-weight:900; font-size:1rem;">I-8/4 Senior Branch Islamabad</p>
            <div class="dates" style="display: flex; justify-content:space-between;">
                <div class="issue" style="width:150px; line-height:1.3rem">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Issue Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->issue_date)->format('d M Y') }}</p>
                </div>
                <div class="due" style="border: 1px solid black; width:150px; line-height:1.3rem;">
                    <p style="text-align:center; font-weight:900; font-size:1rem;">Due Date</p>
                    <div style="width: 100%; height:3px; background-color:black;"></div>
                    <p style="text-align:center; font-size:1rem;">
                        {{ \Carbon\Carbon::parse($challan->due_date)->format('d M Y') }}</p>
                </div>
            </div>
            <div class="details">
                <p> <b>Challan#</b> <span>{{$challan->challanNo}}</span></p>
                <p> <b>Billing Month</b>
                    <span>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F, Y') }}</span>
                </p>
                <p style="width: 100%;"><b>Name:</b> <span>{{ $studentData->stname }}</span></p>
                @if($studentData->enrollment != null)
                <div class="row">
                    @if($studentData->enrollment->class != null)
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px;">{{ $studentData->enrollment->class->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px; padding:0px;">null</p>
                    @endif

                    @if($studentData->enrollment->section != null)
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">{{ $studentData->enrollment->section->name }}</p>
                    @else
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px; padding:0px;">null</p>
                    @endif
                    <p style="width: 100%;"><b>Roll No:</b> <span>{{ $studentData->enrollment->enrollId }}</span></p>
                </div>
                @else
                <div class="row">
                    <p class="col-md-3" style="width: 75px;"><b>Class:</b></p>
                    <p class="col-md-3" style="width: 110px;">null</p>
                    <p class="col-md-3" style="width: 95px;"><b>Section:</b></p>
                    <p class="col-md-3" style="width: 40px;">null</p>
                </div>
                <p style="width: 100%;"><b>Roll No:</b> <span>Not Enrolled</span></p>
                @endif
            </div>
            <div class="withdrawhead"
                style="border-bottom: 5px solid black; border-top: 5px solid black; padding-top:10px;">
                <p style="text-align:center; font-weight:900; font-size:1rem;">{{$challan->challan_type}}</p>
            </div>
            <p style="display:flex; justify-content:space-between; font-size:1rem; text-decoration:underline;">
                <b>Description</b> <b>Amount</b>
            </p>
            @foreach($heads as $head)
            <p style="display:flex; justify-content:space-between; font-size:1rem;">
                <span>{{ $head['name'] }}</span>
                <span>{{ $head['amount'] }}</span>
            </p>
            @endforeach

            <p style="float:right; font-size:1rem; "> <span>Payable By Due Date</span> <span
                    style="border-bottom: 1px solid black; border-top: 1px solid black;padding:1px 0px 0px 50px ;">{{ number_format($totalAmount, 2) }}</span>
            </p><br><br>
            <div style="background-color: gray; font-weight:900; font-size:.8rem; ">
                COMPULSORY INSTRUCTION FOR BANK
            </div>
            <P style="font-size:.8rem;">Please mention challan # / student name in description to avoid dispancy</P>
        </div>

    </div>
</div>
<div class="mt-3">
    <div class="row " style="float: right;">
        <div class="col-md-4 d-flex gap-3">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div>

@endsection