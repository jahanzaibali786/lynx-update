@extends('layouts.admin')
@php
   // $profile=asset(Storage::url('uploads/avatar/'));
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    {{__('Manage Visitor')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('All Visitor')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('create vistor')
            <a href="#" data-size="md" data-url="{{ route('isvisitor.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                Create
            </a>
        @endif
    </div>
@endsection
@section('content')
    @if(\Auth::user()->type == 'company')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['isvisitor.index'], 'method' => 'GET', 'id' => 'isvisitor_submit']) }}
                        <div class="row d-flex justify-content-end ">
        
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' ]) }}
                                </div>                               
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('isvisitor_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('isvisitor.index') }}" class="btn btn-sm btn-danger" 
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
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('CNIC')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
    
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($isvisitors as $isvisitor)
                                    <tr>
                                    
                                        <td>
                                            {{ (!empty($isvisitor->name)) ? $isvisitor->name : '-' }}
                                        </td>
                                        <td>
                                            {{ (!empty($isvisitor->date_time)) ? $isvisitor->date_time : '-' }}
                                        </td>
                                        <td>
                                            {{ (!empty($isvisitor->cnic)) ? $isvisitor->cnic : '-' }}
                                        </td>
                                        @if(Gate::check('edit vistor') || Gate::check('delete vistor'))
                                            <td>
                                                    @can('edit vistor')
                                                    <div class="action-btn bg-primary ms-2">
                                                      
                                                        <a href="#!"data-url="{{route('isvisitor.edit',$isvisitor->id)}}"  data-ajax-popup="true" class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
                                                         data-bs-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                    </div>
    
                                                    @endcan
                                                    @can('delete vistor')
                                                    <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['isvisitor.destroy', $isvisitor->id],'id'=>'delete-form-'.$isvisitor->id]) !!}
    
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$isvisitor->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @endcan
                                                {{-- @else
    
                                                    <i class="ti ti-lock"></i>
                                                @endif --}}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($isvisitors != "")
<div class="pagination">
    <ul>
        @if ($isvisitors->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $isvisitors->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $isvisitors->lastPage(); $page++)
            <li class="{{ $page == $isvisitors->currentPage() ? 'active' : '' }}">
                <a href="{{ $isvisitors->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($isvisitors->hasMorePages())
            <li><a href="{{ $isvisitors->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif

@endsection
