@extends('layouts.admin')
@php
$profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
{{__('Manage Space')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('All Space')}}</li>
@endsection
@section('action-btn')
<div class="float-end">
    <a href="#" data-size="md" data-url="{{ route('space.create') }}" data-ajax-popup="true" 
        data-bs-title="{{__('Create')}}" class="btn mx-1 btn-sm btn-outline-primary">
        <i class="ti ti-plus text-black"></i>
    </a>
</div>
@endsection
@section('content')
@if(\Auth::user()->type == 'company')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['space.index'], 'method' => 'GET', 'id' => 'space_submit']) }}
                    <div class="row d-flex justify-content-end ">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('search', __('Search'), ['class'=>'form-label']) }}
                                {{ Form::text('search', isset($_GET['search']) ? $_GET['search'] : '', ['class' => 'form-control', 'placeholder' => __('Search')]) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('space_submit').submit(); return false;"
                                 data-bs-title="{{ __('Apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('space.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    <tr class="table_heads">
        <th>Name</th>
        <th>Type</th>
        <th>Capacity</th>
        <th>Price</th>
        <th>Meeting</th>
        <th>Window</th>
        <th>Description</th>
        <th>Action</th>
    </tr>
    <tbody>
        @foreach ($spaces as $space)
        <tr>
            <td>{{ $space->name ?? '-' }}</td>
            <td>{{ $space->type->name ?? '-' }}</td>
            <td>{{ $space->capacity ?? '-' }}</td>
            <td>{{ $space->price ?? '-' }}</td>
            <td>{{ $space->meeting ?? '-' }}</td>
            <td>{{ $space->window ?? '-' }}</td>
            <td style="max-width:300px !important; overflow-y: auto;">{{ $space->description ?? '-' }}</td>
            <td>
                @can('edit space')
                <div class="action-btn bg-primary ms-2">
                    <a href="#!" data-url="{{ route('space.edit', $space->id) }}" data-ajax-popup="true"
                        class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
                        data-bs-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                </div>
                @endcan
                @can('delete space')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => ['space.destroy', $space->id], 'id' =>
                    'delete-form-'.$space->id]) !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                        data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                        data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                        data-confirm-yes="document.getElementById('delete-form-{{$space->id}}').submit();"><i
                            class="ti ti-trash text-white"></i></a>
                    {!! Form::close() !!}
                </div>
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination">
    <ul>
        @if ($spaces->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $spaces->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $spaces->lastPage(); $page++)
            <li class="{{ $page == $spaces->currentPage() ? 'active' : '' }}">
                <a href="{{ $spaces->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($spaces->hasMorePages())
            <li><a href="{{ $spaces->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>


@endsection