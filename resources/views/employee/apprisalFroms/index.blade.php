@extends('layouts.admin')
@section('page-title')
{{__('Manage Employee Appraisal Form')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Employee Appraisal Froms')}}</li>
@endsection
@push('script-page')

@endpush 
@section('action-btn')
<div class="float-end">
   
</div>
@endsection

@section('content')

<div class="table-responsive">
<table class="">
    <thead>
        <tr class="table_heads">
            <th>#</th>
            <th >{{__('Form Title')}}</th>
            <th >{{__('Action')}}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>{{__('Teacher Appraisal Form')}}</td>
            <td>
                <a href="{{ route('Teacherappr') }}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary"  title="{{__('Print')}}">
                    <span class="btn-inner--icon">Print</span>
                </a>
            </td>
        </tr>
        <tr>
            <td>2</td>
            <td>{{__('IT Teacher Appraisal Form')}}</td>
            <td>
                <a href="{{ route('itTeacherAppraisalFrom') }}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary"  title="{{__('Print')}}">
                    <span class="btn-inner--icon">Print</span>
                </a>
            </td>
        </tr>
        <tr>
            <td>3</td>
            <td>{{__('Domestic Staff Appraisal Form')}}</td>
            <td>
                <a href="{{ route('domesticStaff') }}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary"  title="{{__('Print')}}">
                    <span class="btn-inner--icon">Print</span>
                </a>
            </td>
        </tr>
        <tr>
            <td>4</td>
            <td>{{__('Sports Teacher Appraisal Form')}}</td>
            <td>
                <a href="{{ route('sportsTeacher') }}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary"  title="{{__('Print')}}">
                    <span class="btn-inner--icon">Print</span>
                </a>
            </td>
        </tr>
        <tr>
            <td>5</td>
            <td>{{__('Director Head SM Appraisal Form')}}</td>
            <td>
                <a href="{{ route('directorHead') }}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary"  title="{{__('Print')}}">
                    <span class="btn-inner--icon">Print</span>
                </a>
            </td>
        </tr>
    </tbody>
</table>
</div>
@endsection
