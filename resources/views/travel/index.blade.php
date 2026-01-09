@extends('layouts.admin')

@section('page-title')
    {{__('Manage Trip')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Trip')}}</li>
@endsection


@section('action-btn')
    <div class="float-end">
    @can('create travel')
            <a href="#" data-url="{{ route('travel.create') }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Create New Trip')}}"  data-bs-title="{{__('Create')}}"  class="btn btn-sm btn-primary">
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
                            {{ Form::open(['route' => ['travel.index'], 'method' => 'GET', 'id' => 'travel_submit']) }}
                            <div class="row d-flex justify-content-end ">
            
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                    </div>                               
                                </div>
    
                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('travel_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('travel.index') }}" class="btn btn-sm btn-danger" 
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
                                <th>{{__('Employee Name')}}</th>
                                @endrole
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th>
                                <th>{{__('Purpose of Trip')}}</th>
                                <th>{{__('Country')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(Gate::check('edit travel') || Gate::check('delete travel'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($travels as $travel)
                                <tr>
                                    @role('company')
                                    <td>{{ !empty($travel->employee())?$travel->employee()->name:'' }}</td>
                                    @endrole
                                    <td>{{ \Auth::user()->dateFormat( $travel->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat( $travel->end_date) }}</td>
                                    <td>{{ $travel->purpose_of_visit }}</td>
                                    <td>{{ $travel->place_of_visit }}</td>
                                    <td>{{ $travel->description }}</td>
                                    @if(Gate::check('edit travel') || Gate::check('delete travel'))
                                        <td>

                                            @can('edit travel')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('travel/'.$travel->id.'/edit') }}" data-ajax-popup="true" data-bs-toggle="{{__('Edit Trip')}}"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                </div>
                                           @endcan


                                            @can('delete travel')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['travel.destroy', $travel->id],'id'=>'delete-form-'.$travel->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$travel->id}}').submit();">
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
