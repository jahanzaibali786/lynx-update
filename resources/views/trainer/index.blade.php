@extends('layouts.admin')
@section('page-title')
    {{__('Manage Trainer')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Trainer')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
    @can('create trainer')
    
            <a href="#" data-size="lg" data-url="{{ route('trainer.create') }}" data-ajax-popup="true"  data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create New Trainer')}}" class="btn btn-sm btn-primary">
            Create
        </a>
        @endcan
    </div>

@endsection

@section('content')
    @if(\Auth::user()->type == 'company')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['trainer.index'], 'method' => 'GET', 'id' => 'trainer_submit']) }}
                        <div class="row d-flex justify-content-end ">
        
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                </div>                               
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('trainer_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('trainer.index') }}" class="btn btn-sm btn-danger" 
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

                    <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th>{{__('Branch')}}</th>
                                <th>{{__('Full Name')}}</th>
                                <th>{{__('Contact')}}</th>
                                <th>{{__('Email')}}</th>
                                @if( Gate::check('edit trainer') ||Gate::check('delete trainer') ||Gate::check('show trainer'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($trainers as $trainer)
                                <tr>
                                    <td>{{ !empty($trainer->branches)?$trainer->branches->name:'' }}</td>
                                    <td>{{$trainer->firstname .' '.$trainer->lastname}}</td>
                                    <td>{{$trainer->contact}}</td>
                                    <td>{{$trainer->email}}</td>
                                    @if( Gate::check('edit trainer') ||Gate::check('delete trainer') || Gate::check('show trainer'))
                                        <td>
                                            @can('show trainer')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" data-url="{{ route('trainer.show',$trainer->id) }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Trainer Detail')}}" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('View')}}" data-bs-title="{{__('View Detail')}}">
                                                <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                                @endcan
                                            @can('edit trainer')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ route('trainer.edit',$trainer->id) }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Edit Trainer')}}" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                                @endcan
                                            @can('delete trainer')
                                            <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['trainer.destroy', $trainer->id],'id'=>'delete-form-'.$trainer->id]) !!}

                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$trainer->id}}').submit();"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}">
                                                <i class="ti ti-trash text-white"></i>

                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
@endsection
