@extends('layouts.admin')
@section('page-title')
    {{__('Manage Product-Service & Income-Expense Category')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Category')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create constant category')
            <a href="#" data-url="{{ route('product-category.create') }}" data-ajax-popup="true"  data-bs-title="{{__('Create')}}" data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create New Category')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
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
                                <th> {{__('Category')}}</th>
                                <th> {{__('Type')}}</th>
                                <th> {{__('Account')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="font-style">{{ $category->name }}</td>
                                    <td class="font-style">
                                        {{ __(\App\Models\ProductServiceCategory::$catTypes[$category->type]) }}
                                    </td>
                                    <td>{{ (!empty($category->chartAccount)?$category->chartAccount->name :'-') }}</td>

                                    <td class="Action">
                                        <span>
                                            <div class="action-btn ms-2">
                                                @can('edit constant category')
                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center" data-url="{{ route('product-category.edit',$category->id) }}" data-ajax-popup="true"  data-bs-title="{{__('Edit')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endcan
                                                @can('delete constant category')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product-category.destroy', $category->id],'id'=>'delete-form-'.$category->id]) !!}
                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$category->id}}').submit();">
                                                       <span class="btn-inner--icon"> <i class="ti ti-trash"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endcan
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if($categories != "")
<div class="pagination">
    <ul>
        @if ($categories->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $categories->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $categories->lastPage(); $page++)
            <li class="{{ $page == $categories->currentPage() ? 'active' : '' }}">
                <a href="{{ $categories->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($categories->hasMorePages())
            <li><a href="{{ $categories->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
        </div>
    </div>

@endsection
