@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Chart of Account Sub Categories') }}
@endsection

@section('breadcrumb')
    <style>
        td a {
            text-decoration: none !important;
        }
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('chart-of-account.index') }}">{{ __('Chart of Accounts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Sub Categories') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create chart of account')
            <a href="#" data-url="{{ route('chart-of-account-sub-category.create') }}" data-bs-title="{{ __('Create Sub Category') }}"
                data-size="lg" data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">
                    {{ __('Create') }}
                </span>
            </a>
        @endcan
        <a href="{{ route('chart-of-account.index') }}" class="btn mx-1 btn-sm btn-outline-secondary">
            <span class="btn-inner--icon">
                {{ __('Back') }}
            </span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        @forelse ($subCategories->groupBy(function ($subCategory) {
            return !empty($subCategory->accountType) ? $subCategory->accountType->name : __('Without Account Type');
        }) as $type => $items)
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>{{ $type }}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="datatable">
                                <thead>
                                    <tr>
                                        <th width="45%">{{ __('Name') }}</th>
                                        <th width="45%">{{ __('Account Type') }}</th>
                                        <th width="10%">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $subCategory)
                                        <tr>
                                            <td>{{ $subCategory->name }}</td>
                                            <td>{{ !empty($subCategory->accountType) ? $subCategory->accountType->name : '-' }}</td>
                                            <td>
                                            @can('edit chart of account')
                                                <div class="action-btn ms-2">
                                                    <a href="#"
                                                        class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                                                        data-url="{{ route('chart-of-account-sub-category.edit', $subCategory->id) }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true" title="{{ __('Edit Sub Category') }}"
                                                        data-bs-title="{{ __('Edit') }}">
                                                        <span class="btn-inner--icon">
                                                            <i class="ti ti-pencil"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-4">
                        {{ __('No sub categories found.') }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
