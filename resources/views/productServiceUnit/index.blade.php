@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Product & Service Unit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Unit') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create constant unit')
            <a href="#" data-url="{{ route('product-unit.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Create') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
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
        <div class="col-9 table-responsive">
            <table class="datatable">
                <thead>
                    <tr class="table_heads">
                        <th> {{ __('Unit') }}</th>
                        <th width="10%"> {{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td>{{ $unit->name }}</td>
                            <td class="Action">
                                <span>
                                    <div class="action-btn ms-2">
                                        @can('edit constant category')
                                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                                data-url="{{ route('product-unit.edit', $unit->id) }}" data-ajax-popup="true"
                                                
                                                data-bs-title="{{ __('Edit') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            </a>
                                        @endcan
                                        @can('delete constant category')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['product-unit.destroy', $unit->id],
                                                'id' => 'delete-form-' . $unit->id,
                                            ]) !!}
                                            <a href="#"
                                                class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                 data-bs-title="{{ __('Delete') }}"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $unit->id }}').submit();">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
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
            @if ($units != '')
                <div class="pagination">
                    <ul>
                        @if ($units->onFirstPage())
                            <li class="disabled">&laquo;</li>
                        @else
                            <li><a href="{{ $units->appends(request()->query())->previousPageUrl() }}"
                                    rel="prev">&laquo;</a></li>
                        @endif
                        @for ($page = 1; $page <= $units->lastPage(); $page++)
                            <li class="{{ $page == $units->currentPage() ? 'active' : '' }}">
                                <a href="{{ $units->appends(request()->query())->url($page) }}">{{ $page }}</a>
                            </li>
                        @endfor
                        @if ($units->hasMorePages())
                            <li><a href="{{ $units->appends(request()->query())->nextPageUrl() }}"
                                    rel="next">&raquo;</a></li>
                        @else
                            <li class="disabled">&raquo;</li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
    </div>

@endsection
