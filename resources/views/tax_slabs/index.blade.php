@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Tax Slabs') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Tax Slabs') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @if(date('m') == 7)
        <a href="{{ route('tax-slab.showRevisePage') }}" class="btn mx-1 btn-sm btn-outline-primary"
            data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Revise Employee Taxes') }}">
            <span class="btn-inner--icon"><i class="ti ti-refresh"></i></span>
        </a>
        @endif
        <a href="#" data-url="{{ route('tax-slab.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Create') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon"><i class="ti ti-plus"></i></span>
        </a>
    </div>
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['tax-slab.index'], 'method' => 'GET', 'id' => 'tax_slab_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('from_year', __('From Year'), ['class' => 'form-label']) }}
                                        {{ Form::select('from_year', $years, $fromYear, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('to_year', __('To Year'), ['class' => 'form-label']) }}
                                        {{ Form::select('to_year', $years, $toYear, ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('tax_slab_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" data-bs-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>

                                    <a href="{{ route('tax-slab.index') }}"
                                        class="btn mx-1 btn-sm btn-outline-danger"
                                        data-bs-toggle="tooltip" data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="table_heads">
                                <tr>
                                    <th>{{ __('Slab No.') }}</th>
                                    <th>{{ __('Year') }}</th>
                                    <th>{{ __('Salary Slabs Lower Limit') }}</th>
                                    <th>{{ __('Salary Slabs Upper Limit') }}</th>
                                    <th>{{ __('Fixed Tax Amount') }}</th>
                                    <th>{{ __('Prev Limit %') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tax_slabs as $tax_slab)
                                    <tr>
                                        <td>{{ @$tax_slab->no }}</td>
                                        <td>{{ @$tax_slab->year }}</td>
                                        <td>{{ @$tax_slab->lower_limit }}</td>
                                        <td>{{ @$tax_slab->upper_limit }}</td>
                                        <td>{{ @$tax_slab->fixed_tax_amount }}</td>
                                        <td>{{ @$tax_slab->prev_limit_percentage }}</td>
                                        <td>
                                            <div class="action-btn ms-2">
                                                <a href="#" class="mx-3 btn btn-sm btn-primary align-items-center"
                                                    data-url="{{ route('tax-slab.edit', $tax_slab->id) }}"
                                                    data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-toggle="{{ __('Edit Tax Slab') }}"
                                                    data-bs-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['tax-slab.destroy', $tax_slab->id],
                                                    'id' => 'delete-form-' . $tax_slab->id,
                                                ]) !!}
                                                <a href="#" class="mx-1 btn btn-sm btn-danger align-items-center bs-pass-para"
                                                    data-bs-title="{{ __('Delete') }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $tax_slab->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
