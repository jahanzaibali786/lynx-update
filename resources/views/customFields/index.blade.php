@extends('layouts.admin')
@section('page-title')
    {{__('Manage Custom Field')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Custom Field')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create constant custom field')
            <a href="#" data-url="{{ route('custom-field.create') }}"  data-bs-title="{{__('Create')}}" data-ajax-popup="true" data-bs-toggle="{{__('Create New Custom Field')}}" class="btn btn-sm btn-primary">
                Create
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            
                        <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th> {{__('Custom Field')}}</th>
                                <th> {{__('Type')}}</th>
                                <th> {{__('Module')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($custom_fields as $field)
                                <tr>
                                    <td>{{ $field->name}}</td>
                                    <td>{{ $field->type}}</td>
                                    <td>{{ $field->module}}</td>
                                    @if(Gate::check('edit constant custom field') || Gate::check('delete constant custom field'))
                                        <td class="Action">
                                            <span>
                                            @can('edit constant custom field')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('custom-field.edit',$field->id) }}" data-ajax-popup="true" data-bs-toggle="{{__('Edit Custom Field')}}"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                    </div>
                                                @endcan
                                                @can('delete constant custom field')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['custom-field.destroy', $field->id],'id'=>'delete-form-'.$field->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$field->id}}').submit();">
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
                        @if($custom_fields != "")
<div class="pagination">
    <ul>
        @if ($custom_fields->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $custom_fields->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $custom_fields->lastPage(); $page++)
            <li class="{{ $page == $custom_fields->currentPage() ? 'active' : '' }}">
                <a href="{{ $custom_fields->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($custom_fields->hasMorePages())
            <li><a href="{{ $custom_fields->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
        </div>
    </div>



@endsection
