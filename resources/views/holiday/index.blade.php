@extends('layouts.admin')

@section('page-title')
    {{__('Manage Holiday')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Holiday')}}</li>
@endsection

@section('action-btn')
    @can('create holiday')
        <div class="float-end">
            <a href="{{ route('holiday.calender') }}" class="btn btn-sm btn-primary"  data-bs-title="{{__('Calender View')}}" data-bs-title="{{__('Calender View')}}">
                <i class="ti ti-calendar"></i>
            </a>
            <a href="#" data-size="lg" data-url="{{ route('holiday.create') }}" data-ajax-popup="true"  data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create New Holiday')}}" class="btn btn-sm btn-primary">
                Create
            </a>
        </div>
    @endcan
@endsection


@section('content')
    @can('create holiday')
        <div class="row">
            <div class="col-sm-12">
                <div class=" mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(array('route' => array('holiday.index'),'method'=>'get','id'=>'holiday_filter')) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-xl-10">
                                    <div class="row justify-content-end">
                                        @if(\Auth::user()->type == 'company')
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                                <div class="btn-box">
                                                    {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                                </div>                               
                                            </div>
                                        @endif
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{Form::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                                                {{Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',array('class'=>'month-btn form-control'))}}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                            <div class="btn-box">
                                                {{Form::label('end_date',__('End Date'),['class'=>'form-label'])}}
                                                {{Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',array('class'=>'month-btn form-control '))}}                                        </div>
                                            </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="row">
                                        <div class="col-auto mt-4">
                                            <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('holiday_filter').submit(); return false;"  data-bs-title="{{__('Apply')}}" data-bs-title="{{__('apply')}}">
                                                <span class="btn-inner--icon">Search</span>
                                            </a>
                                            <a href="{{route('holiday.index')}}" class="btn btn-sm btn-danger "   data-bs-title="{{ __('Reset') }}" data-bs-title="{{__('Reset')}}">
                                                <span class="btn-inner--icon">Clear</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    @endcan

                        <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th>{{__('Occasion')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th>
                                @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($holidays as $holiday)
                                <tr>
                                    <td>{{ $holiday->occasion }}</td>
                                    <td>{{ \Auth::user()->dateFormat($holiday->date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($holiday->end_date) }}</td>
                                    @if(Gate::check('edit holiday') || Gate::check('delete holiday'))
                                        <td class="Action">
                                            <span>
                                                @can('edit holiday')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('holiday.edit',$holiday->id) }}" data-ajax-popup="true" data-bs-toggle="{{__('Edit Holiday')}}"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete holiday')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id],'id'=>'delete-form-'.$holiday->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"   data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$holiday->id}}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

@endsection
