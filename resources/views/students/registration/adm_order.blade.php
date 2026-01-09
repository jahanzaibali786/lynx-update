@extends('layouts.admin')
@section('page-title')
{{__('Admission Order')}}
@endsection
@push('script-page')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
<script>
function generatePDF() {
    console.log('generating');
    const element = document.getElementById('card');
    const opt = {
        filename: 'admission-order.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            format: 'a4',
        }
    };
    html2pdf().from(element).set(opt).save();
}

function printPDF() {
    console.log('printing');
    const element = document.getElementById('card');
    const opt = {
        filename: 'admission-order.pdf',
        html2canvas: {
            scale: 1
        },
        jsPDF: {
            format: 'a4',
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
<li class="breadcrumb-item">{{__('Admission Order')}}</li>
@endsection
@section('content')
<style>
.cont {
    width: 650px;
    position: relative;
    padding: 40px 0px;
}

.dash-content {
    display: flex;
    flex-direction: column;
}

.inpdiv {
    border: none;
    border-bottom: 2px solid black;
}
</style>
<div class="my-3">
    <div class="row " style="float: right;">
        <div class="col-md-4 d-flex gap-3">
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div>
<div class="card" id="card" style="padding:0px 10%;">
    <div class="cont" id="cont">
        <div class="header d-flex justify-content-center " style="gap:10%;margin-left:25%;">
            <div class="sch_name">
                <p style="font-family:Edwardian Script ITC;font-size:3rem; text-align:center; font-weight:500;">The Lynx School
                </p>
                <p style="text-align:center; font-size:1rem;">{{@$adm_order->branches->name}}</p>
            </div>
            <div class="sch_logo">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
            </div>
        </div>
        <h2 class="issue" style=" font-weight:203; text-align:center;"> Admission Order </h2><br>
        <div class="d-flex">
            <label for="name">Name : </label><div class="inpdiv" style="width: 85%; padding-left:40%;" id="">{{$adm_order->stdname}}</div>
        </div><br>
        <div class="d-flex">
            <label for="name">Date Of Birth :</label><div class="inpdiv"  id="" style="width: 78%; padding-left:30%;">{{\Carbon\Carbon::createFromFormat('Y-m-d', $adm_order->dob)->format('d-M-Y')}}</div>
        </div>
        <br>
        <div class="d-flex">
            <label for="name">Father's Name :</label><div class="inpdiv"  id="" style="width: 76%; padding-left:29%;">{{$adm_order->fathername}}</div>
        </div>
        <br>
        <div class="d-flex">
            <label for="name">Date of Admission :</label><div class="inpdiv"  id="" style="width: 73%;"></div>
        </div><br>
        <div class="d-flex">
            <div class="d-flex" style="width:100%;">
                <label for="name">Class to which Admitted :</label><div class="inpdiv" id="" style="width:50%; padding-left:10%;">{{$adm_order->class->name}}</div>
            </div>
            <div class="d-flex" style="width:100%;">
                <label for="name">section :</label><div class="inpdiv"  id="" style="width:66%; padding-left:10%; ">{{@$adm_order->enrollment->section->name}}</div>
            </div>
        </div>
        <br>
        <div class="d-flex">
            <label for="name">Permanent Address :</label><div class="inpdiv" id="" style="width: 70%; padding-left:5%;">{{$adm_order->address}}</div>
        </div>
        <br>
        <div class="d-flex">
            <div class="d-flex" style="width:100%;">
                <label for="name">Telephone No :</label><div class="inpdiv"  id="" style="width: 65%; padding-left:10% ;">{{$adm_order->fatherphone}}</div>
            </div>
            <div class="d-flex" style="width:100%;">
                <label for="name">Mobile :</label><div class="inpdiv"  id="" style="width: 65%; padding-left:10%;">{{$adm_order->fathercell}}</div>
            </div>
        </div>
        <br>
        <div class="d-flex">
            <label for="name">Entered in admission Register and Allotted Roll No :</label><div class="inpdiv"  id="" style="width: 42%; padding-left:20%;">{{@$adm_order->enrollment->regId}}</div>
        </div>
        <br><br>
        <label for="">Copies to :Parents/ Personal file / Class Teacher / School File.</label><br><br><br>
        <label for="">Remarks:</label>
        <br><br><br><br><br>
        <div class="dates" style="display: flex; justify-content:space-between;">
            <div class="issue" style="margin-top: 18px;">
                <p 
                style="width:150px; margin:0px; border-top: 1px solid black; text-align:center;"></p>
                <p style="text-align:center; font-size:1rem;">School Stamp</p>
            </div>
            <div class="issue" style="margin-top:{{@date('Y-m-d') ? '0px' : "18px" }};">
                {{-- <p style="width:150px; line-height:1.3rem;  border-top: 1px solid black; text-align:center;">{{ date('Y-m-d')}}</p>
                <p style="text-align:center; font-size:1rem;">Date</p> --}}
                <p style="width:150px; margin:0px; border-bottom: 1px solid black; text-align:center;">{{ date('Y-m-d')}}</p>
                <p style="text-align:center; font-size:1rem;">Date</p>
            </div>
            <div class="due" style="margin-top:{{@$adm_order->branch_name->headmaster_name ? '0px' : "18px" }};">
                <p style="width:150px; margin:0px; border-bottom: 1px solid black; text-align:center;">{{ @$adm_order->branch_name->headmaster_name->name ?? "" }}</p>
                <p style="text-align:center; font-size:1rem;">Head of institute</p>
            </div>
        </div>
    </div>
</div>
@endsection
