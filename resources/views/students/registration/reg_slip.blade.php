@extends('layouts.admin')
@section('page-title')
{{__('Registration Receipt')}}
@endsection
@push('script-page')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
<script>
    function printPDF() {
        let url = "{{ route('reg.receipt', $reg_recipt->id) }}?print=pdf";
        window.open(url, '_blank');
    }
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Registration Receipt')}}</li>
@endsection
@section('content')
<style>
.cont {
    width: 650px;
    position: relative;
    padding:40px 0px;
}
.dash-content{
    display: flex;
    flex-direction: column;
}
</style>
<div class="my-3">
    <div class="row " style="float: right;">
        <div class="col-md-4 d-flex gap-3">
            <a href="{{ route('registration.show', $reg_recipt->id) }}" class="btn btn-outline-primary"> Admission Process </a>
            <button class="btn btn-outline-primary" onclick="generatePDF()">Download PDF</button>
            <button class="btn btn-outline-success" onclick="printPDF()">Print PDF</button>
        </div>
    </div>
</div>
<div class="card" id="card" style="padding:0px 10%;">
    <div class="cont" id="cont">
        <div class="header d-flex justify-content-center" style="gap:10%;">
            <div class="sch_name">
                <p style="font-family:Edwardian Script ITC;font-size:3rem; text-align:center; font-weight:500;">The Lynx School
                </p>
                <p style="text-align:center; font-size:1rem;">{{@$reg_recipt->branches->name}}</p>
            </div>
            <div class="sch_logo">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; position:absolute; margin-left:500px; max-height: 90px;" alt="logo">
            </div>
        </div>
        <div class="reg-no-date d-flex " style=" justify-content:space-between;">
            <p style="font-size:1rem;">Registration No: <span> {{@$reg_recipt->id}}</span></p>
            <p style="font-size:1rem;">Date: <span>{{ now()->format('d M Y') }}</span></p>
        </div>
        <h2 class="issue" style=" font-weight:203; text-align:center;"> Registration Receipt </h2><br>
        <p style="font-size: 1rem;">
            Received Rs <span>&nbsp;{{@$reg_recipt->registrationfee}}&nbsp;&nbsp;</span> with thanks from Mr.
            &nbsp;&nbsp;&nbsp;<span
                style="border-bottom: 1px solid black; padding-bottom: 2px;">&nbsp;{{@$reg_recipt->fathername}}&nbsp;&nbsp;</span> for the
                registration of 
                {{@$reg_recipt->gender == "male" ? 'his' : 'her'}}
                ward&nbsp;&nbsp;&nbsp;&nbsp;
            <span style="border-bottom: 1px solid black; padding-bottom: 2px;">{{@$reg_recipt->stdname}}</span> of class
            <span
                style="border-bottom: 1px solid black; padding-bottom: 2px;">&nbsp;&nbsp;&nbsp;&nbsp;{{@$reg_recipt->class->name}}&nbsp;&nbsp;&nbsp;&nbsp;</span>
             session
            <span style="border-bottom: 1px solid black; padding-bottom: 2px;">&nbsp;{{@$reg_recipt->session->year}}&nbsp;</span>.
        </p>
        <br><br><br><br><br>
        <div class="dates" style="display: flex; justify-content:space-between;">
            <div class="issue" style="margin-top:{{@$reg_recipt->branch_name->headmaster_name ? '0px' : "18px" }};"> 
                <p style="width:150px; margin:0px; border-bottom: 1px solid black; text-align:center;">{{ @$reg_recipt->branch_name->headmaster_name->name ?? "" }}</p>
                <p style="text-align:center; font-size:1rem;">Headmistress</p>
            </div>
            <div class="due" style="margin-top: 18px;">
                <p style="width:150px; margin:0px; border-bottom: 1px solid black; text-align:center;"> </p>
                <p style="text-align:center; font-size:1rem;">Accountant</p>
            </div>
        </div>
    </div>
</div>
{{--<div class="card mt-4" id="challan-content">
    <div class="row  border p-4">
        <div class="col-md-12">
            <div class="logo" style="position: absolute; margin-left: 66%;">
                <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
alt="logo">
</div>
<p style="font-family:Edwardian Script ITC;;font-size:X-Large; text-align:center; font-weight:500;">The Lynx School</p>
<p style="text-align:center; font-size:1rem;">{{@$reg_recipt->branches->name}}</p>
<div class="dates" style="display: flex; justify-content:space-between;">
    <div class="issue" style="width:150px; line-height:1.3rem">
        <p style="text-align:center; font-size:1rem;">Registration No : {{@$reg_recipt->id}}</p>
    </div>
    <div class="due" style="width:150px; margin-right: 15%; line-height:1.3rem;">
        <p style="text-align:center; font-size:1rem;">Date : {{date('d M Y', strtotime(@$reg_recipt->regdate))}}</p>

    </div>
</div>
<div class="dates" style="display: flex; justify-content:center;">
    <h2 class="issue" style=" font-weight:203; "> Registration Receipt </h2>
</div>

<div class="flex-container">
    <div class="item" style=" flex: 0 0 9%;">Received Rs.</div>
    <div class="item" style="width: 10%; border-bottom: 1px solid black; text-align:center;"> 1,000 </div>
    <div class="item " style="flex: 0 0 11%;"> thanks from Mr. </div>
    <div class="item" style="width: 25%; border-bottom: 1px solid black; text-align:left; flex: 0 0 24%;">
        {{@$reg_recipt->fathername}} </div>
    <div class="item" style="flex: 0 0 14%;">for the registration of</div><br>
    <div class="item"> his ward </div>
    <div class="item" style="width: 25%; border-bottom: 1px solid black; text-align:left; flex: 0 0 24%;">
        {{@$reg_recipt->stdname}} </div>
    <div class="item"> of class </div>
    <div class="item" style="width: 25%; border-bottom: 1px solid black; text-align:left;">{{@$reg_recipt->class->name}}
    </div>
    <div class="item"> the session </div>
    <div class="item" style="width: 25%; border-bottom: 1px solid black; text-align:left;">
        {{@$reg_recipt->session->title}}</div>

</div>
<br><br><br><br>
<div class="dates" style="display: flex; justify-content:space-between;">
    <div class="issue" style="width:150px; line-height:1.3rem;  border-top: 1px solid black; text-align:center;">
        <p style="text-align:center; font-size:1rem;">Headmistress</p>
    </div>
    <div class="due"
        style="width:150px; margin-right: 15%; line-height:1.3rem;  border-top: 1px solid black; text-align:center;">
        <p style="text-align:center; font-size:1rem;">Accountant</p>

    </div>
</div>

</div>

</div>
</div>--}}


@endsection
