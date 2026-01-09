@extends('layouts.admin')

@section('page-title')
    {{__('Manage Award')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Award')}}</li>
@endsection

@section('action-button')
    <div class="all-button-box row d-flex justify-content-end">
        @can('create award')
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
            <a href="#" data-url="{{ route('award.create') }}" class="btn btn-xs btn-white btn-icon-only width-auto" data-ajax-popup="true" data-bs-toggle="{{__('Create New Award')}}">
                <i class="fa fa-plus"></i> {{__('Create')}}
            </a>
            </div>

        @endcan
    </div>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('create award')
        <a href="#" data-size="lg" data-url="{{ route('award.create') }}" data-ajax-popup="true"
            data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create New Award')}}" class="btn btn-sm btn-primary">
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
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['award.index'], 'method' => 'GET', 'id' => 'award_submit']) }}
                            <div class="row d-flex justify-content-end ">
            
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' ]) }}
                                    </div>                               
                                </div>
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('award_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('award.index') }}" class="btn btn-sm btn-danger" 
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
                                @role('company')
                                <th>{{__('Employee')}}</th>
                                @endrole
                                <th>{{__('Award Type')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Gift')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(Gate::check('edit award') || Gate::check('delete award'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($awards as $award)
                                <tr>
                                    @role('company')
                                    <td>{{!empty( $award->employee())? $award->employee()->name:'' }}</td>
                                    @endrole
                                    <td>{{ !empty($award->awardType())?$award->awardType()->name:'' }}</td>
                                    <td>{{  \Auth::user()->dateFormat($award->date )}}</td>
                                    <td>{{ $award->gift }}</td>
                                    <td>{{ $award->description }}</td>

                                    @if(Gate::check('edit award') || Gate::check('delete award'))
                                        <td class="Action">
                                            <span>
                                                @can('edit award')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" data-url="{{ URL::to('award/'.$award->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Edit Award')}}" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i></a>
                                                </div>
                                                    @endcan
                                                @can('delete award')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['award.destroy', $award->id],'id'=>'delete-form-'.$award->id]) !!}
                                                 <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$award->id}}').submit();">
                                                     <i class="ti ti-trash text-white"></i></a>
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
