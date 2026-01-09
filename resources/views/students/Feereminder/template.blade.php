@extends('layouts.admin')
@section('page-title')
    {{__('Fee Reminder Slip')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Fee Reminder Slip')}}</li>
@endsection
@section('content')
    <div class="card p-4 mt-5" id="slip-content">
        <div style="width: 100%;">
            <div style="float: left; width: 33.33%; text-align: center;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
                </div>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;">
                <p style=" font-family: 'Edwardian Script ITC'; font-size:2rem; text-align: center; font-weight: 800;">The Lynx School</p>
            </div>
            <div style="float: left; width: 33.33%; text-align: center;"></div>
        </div><br><br><br>
        <div style="width: 100%; text-align: right;">
            <p><b>Date : {{ \Carbon\Carbon::now()->format('m/d/Y') }}</b></p>
        </div><br>
        <div style="width: 100%;">
            <p><b>Branch Name : <span style="text-transform: uppercase;">{{ $data['branch'] }}</span></b></p>
        </div>
        <div style="width: 100%;">
        <p><b>Subject : <span style="text-transform: uppercase; padding-bottom: 10px; border-bottom: 2px solid black;">
        @if($reminder == 'first')
            1st Reminder Fee Collection
        @elseif($reminder == 'second')
            2nd Reminder Fee Collection
        @elseif($reminder == 'final')
            Final Reminder Fee Collection
        @else
            {{ $reminder }}
        @endif
        </span></b></p>
        </div>
        <div style="width: 100%;">
            <p><b>Dear Parents, </b></p>
        </div>
        <div style="line-height: 1.5rem;">
        <p>This is to remind you that your child <span style="text-transform:uppercase; padding-bottom:3px; border-bottom:2px solid black;"><b>&nbsp;&nbsp;&nbsp;&nbsp;{{ @$data['studentName'] }}&nbsp;&nbsp;&nbsp;&nbsp;</b></span>
            class <span style="text-transform:uppercase; padding-bottom:3px; border-bottom:2px solid black;"><b>&nbsp;&nbsp;&nbsp;&nbsp;{{ @$data['studentClass']}}&nbsp;&nbsp;&nbsp;&nbsp;</b></span> section <span
                style="text-transform:uppercase; padding-bottom:3px; border-bottom:2px solid black;"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ @$data['section'] }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></span> Roll NO. <span
                style="text-transform:uppercase;padding-bottom:3px; border-bottom:2px solid black;"><b>&nbsp;&nbsp;&nbsp;{{ @$data['rollno'] }}&nbsp;&nbsp;&nbsp;</b></span> Fee for the Month's
                <span style="text-transform:uppercase;padding-bottom:3px; border-bottom:2px solid black;"><b>&nbsp;-&nbsp;{{ $data['totalmonth'] }},
                </b></span>
            i.e Subtotal Rs
            <span style="text-transform:uppercase;padding-bottom:3px; border-bottom:2px solid black;">
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $data['totalAmount'] }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
            </span> Is Unpaid. Please pay the amount within
            <span style="padding-bottom:3px; border-bottom:2px solid black;">&nbsp;&nbsp;<b>&nbsp;&nbsp;
            @if($reminder == 'first')
            3 Days
            @elseif($reminder == 'second')
            2 Days
            @elseif($reminder == 'final')
            1 Days
            @endif
            &nbsp;&nbsp;</b>&nbsp;&nbsp;</span>
            as this is required to run the school affairs effectively. Please provide
            us the school voucher copy and ignore this reminder, if you have already paid the dues.
        </p><br><br>
        </div>
        <div style="width: 100%;">
            <p>We appreciate your cooperation,</p><br><br>
        </div>
        <div style="width: 100%;">
            {{ $data['headmaster_name'] }}
            <p><b  style="border-top:1px solid black;">Headmistress</b></p>
        </div>
        <hr style="height: 4px; background-color: black;">
        <hr style="height: 4px; background-color: black;">
        <div style="width: 100%; text-align: center;">
            <p><b>@if($reminder == 'first')
            1st Reminder Fee Collection
        @elseif($reminder == 'second')
            2nd Reminder Fee Collection
        @elseif($reminder == 'final')
            Final Reminder Fee Collection
        @else
            {{ $reminder }}
        @endif
    </b></p>
        </div><br>
        <div style="width: 100%;">
            <p class="text-decoration:underline;"><b>Office Copy</b></p>
        </div>
        <div style="width: 100%;">
            <p>Date <span>{{ \Carbon\Carbon::now()->format('m/d/Y') }}</span></p>
        </div>
        <div style="width: 100%;">
            <div style="float: left; width: 40%;">
                <p> <b>Name: </b> <span style="text-transform: uppercase;">{{ $data['studentName'] }}</span></p>
            </div>
            <div style="float: left; width: 30%;">
                <p> <b>Class: </b> <span>{{ @$data['studentClass'] }}</span></p>
            </div>
            <div style="float: left; width: 15%;">
                <p> <b>Section: </b> <span>{{ @$data['section'] }}</span></p>
            </div>
            <div style="float: left; width: 15%;">
                <p> <b>Roll #  </b><span>{{ @$data['rollno']}}</span></p>
            </div>
        </div><br><br>
        <div style="width:100%; position:relative;left:-700px;">
            <div style="float:left; margin-right:50%;">
            <p><b>Billing Month,s: </b> <span >{{ $data['feeMonths'] }}</span></p>
            </div>
            <div style="float:left; position: relative; left:-170px">
                <p> <b>Amount:  </b><span style="text-transform: uppercase;">{{ $data['totalAmount'] }}</span></p>
            </div>
        </div>
        <br><br>
        <div style="width: 100%;">
            <p>Fee Reminder copy should be filed in student file.</p>
        </div>
    </div>
@endsection
