@extends('layouts.admin')

@section('page-title')
    {{ __('Accounts GRN Approval') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Inventory') }}</li>
    <li class="breadcrumb-item">{{ __('GRN Approval') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['grn.accounts_index'], 'method' => 'GET', 'id' => 'grn_filter']) }}
                        <div class="row d-flex align-items-end justify-content-end">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                {{ Form::label('start_date', __('From Date'), ['class' => 'form-label']) }}
                                {{ Form::date('start_date', request('start_date'), ['class' => 'form-control']) }}
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                {{ Form::label('end_date', __('To Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', request('end_date'), ['class' => 'form-control']) }}
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                {{ Form::label('warehouse_id', __('Store'), ['class' => 'form-label']) }}
                                {{ Form::select('warehouse_id', $warehouses, request('warehouse_id'), ['class' => 'form-control select']) }}
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                {{ Form::label('vendor_id', __('Vendor'), ['class' => 'form-label']) }}
                                {{ Form::select('vendor_id', $vendors, request('vendor_id'), ['class' => 'form-control select']) }}
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['' => __('All')] + $statuses, request('status'), ['class' => 'form-control select']) }}
                            </div>
                            <div class="col-auto mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('grn_filter').submit(); return false;">
                                    {{ __('Search') }}
                                </a>
                                <a href="{{ route('grn.accounts_index') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table class="">
        <thead>
            <tr class="table_heads">
                <th>{{ __('S.No') }}</th>
                <th>{{ __('GRN No') }}</th>
                <th>{{ __('Store') }}</th>
                <th>{{ __('Vendor') }}</th>
                <th>{{ __('GRN Date') }}</th>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($grns as $grn)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('grn.show', $grn->id) }}" class="btn btn-outline-primary btnpurchase1">
                            GRN-{{ sprintf('%05d', $grn->grn_no) }}
                        </a>
                    </td>
                    <td>{{ optional($grn->warehouse)->name ?? '-' }}</td>
                    <td>{{ optional($grn->vendor)->name ?? '-' }}</td>
                    <td>{{ \Auth::user()->dateFormat($grn->grn_date) }}</td>
                    <td>{{ $grn->reference_no ?? '-' }}</td>
                    <td>{{ \Auth::user()->priceFormat($grn->getTotal()) }}</td>
                    <td>
                        @php
                            $statusLabel = App\Models\Grn::$statues[$grn->status] ?? 'Draft';
                        @endphp
                        <span class="status_badge badge
                            @if($grn->status == 0) bg-secondary
                            @elseif($grn->status == 5) bg-info
                            @elseif($grn->status == 6) bg-primary
                            @elseif($grn->status == 7) bg-warning
                            @elseif($grn->status == 8) bg-success
                            @else bg-secondary
                            @endif p-2 px-3 rounded">
                            {{ __($statusLabel) }}
                        </span>
                    </td>
                    <td class="Action">
                        @can('show grn')
                        <a href="{{ route('grn.show', $grn->id) }}"
                            class="mx-1 btn btn-sm btn-outline-info align-items-center" title="{{ __('Show') }}">
                            <i class="ti ti-eye"></i>
                        </a>
                        @endcan
                        @if($grn->status == 7)
                        @can('account approve grn')
                        <a href="{{ route('grn.accounts_approve', $grn->id) }}"
                            class="mx-1 btn btn-sm btn-outline-success text-white align-items-center" title="{{ __('Review Voucher') }}">
                            <span class="btn-inner--icon"><i class="ti ti-checks"></i></span>
                        </a>
                        @endcan
                        <a href="{{ route('grn.reject', $grn->id) }}"
                            class="mx-1 btn btn-sm btn-outline-danger text-white align-items-center" title="{{ __('Reject') }}"
                            onclick="return confirm('{{ __('Are you sure you want to reject this GRN?') }}')">
                            <span class="btn-inner--icon">
                            <i class="ti ti-x"></i>
                            </span>
                        </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('No GRN found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
