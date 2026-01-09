@extends('layouts.admin')

@section('page-title')
    {{__('Manage Training Type')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Training Type')}}</li>
@endsection



@section('action-btn')
    <div class="float-end">
        @can('create training type')
            <a href="#" data-url="{{ route('trainingtype.create') }}" data-ajax-popup="true" data-bs-toggle="{{__('Create New Training Type')}}"  data-bs-title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                Create
            </a>

        @endcan
    </div>
@endsection



@section('content')

    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            @if(\Auth::user()->type == 'company')
            <div class="row">
                <div class="col-sm-12">
                    <div class="mt-2 " id="multiCollapseExample1">
                        <div class="card">
                            <div class="card-body">
                                {{ Form::open(['route' => ['trainingtype.index'], 'method' => 'GET', 'id' => 'trainingtype_submit']) }}
                                <div class="row d-flex justify-content-end ">
                
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                        </div>                               
                                    </div>
        
                                    <div class="col-auto float-end ms-2 mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('trainingtype_submit').submit(); return false;"
                                             data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('trainingtype.index') }}" class="btn btn-sm btn-danger" 
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
                                <th>{{__('Training Type')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($trainingtypes as $trainingtype)
                                <tr>
                                    <td>{{ $trainingtype->name }}</td>

                                    <td>
{{--                                        @can('edit training type')--}}
{{--                                            <a href="#" data-url="{{ route('trainingtype.edit',$trainingtype->id) }}" data-size="lg" data-ajax-popup="true" data-bs-toggle="{{__('Edit Training Type')}}" class="edit-icon"><i class="ti ti-pencil text-white"></i></a>--}}
{{--                                        @endcan--}}

                                        @can('edit training type')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('trainingtype.edit',$trainingtype->id) }}" data-ajax-popup="true" data-bs-toggle="{{__('Edit Training Type')}}"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                        @endcan


                                        @can('delete training type')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['trainingtype.destroy', $trainingtype->id],'id'=>'delete-form-'.$trainingtype->id]) !!}
                                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endcan


                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
        </div>
    </div>

@endsection
