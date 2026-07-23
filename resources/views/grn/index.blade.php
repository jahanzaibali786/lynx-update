@extends('layouts.admin')

@section('page-title')
    {{ __('Manage GRN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('GRN') }}</li>
@endsection

@section('action-btn')
    @can('create grn')
    <div class="float-end">
        <a href="#" data-url="{{ route('grn.create') }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Create GRN') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">{{ __('Create') }}</span>
        </a>
    </div>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['grn.index'], 'method' => 'GET', 'id' => 'grn_filter']) }}
                        <div class="row d-flex align-items-end justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">
                                {{ Form::label('grn_date', __('GRN Date'), ['class' => 'form-label']) }}
                                {{ Form::date('grn_date', request('grn_date'), ['class' => 'form-control']) }}
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                {{ Form::label('warehouse_id', __('Store'), ['class' => 'form-label']) }}
                                {{ Form::select('warehouse_id', $warehouses, request('warehouse_id'), ['class' => 'form-control select']) }}
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                {{ Form::label('vendor_id', __('Vendor'), ['class' => 'form-label']) }}
                                {{ Form::select('vendor_id', $vendors, request('vendor_id'), ['class' => 'form-control select']) }}
                            </div>
                            <div class="col-auto mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('grn_filter').submit(); return false;">
                                    {{ __('Search') }}
                                </a>
                                <a href="{{ route('grn.index') }}" class="btn mx-1 btn-sm btn-outline-danger">
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
                        @can('show grn')
                            <a href="{{ route('grn.show', $grn->id) }}" class="btn btn-outline-primary btnpurchase1">
                                GRN-{{ sprintf('%05d', $grn->grn_no) }}
                            </a>
                        @else
                            GRN-{{ sprintf('%05d', $grn->grn_no) }}
                        @endcan
                    </td>
                    <td>{{ optional($grn->warehouse)->name ?? '-' }}</td>
                    <td>{{ optional($grn->vendor)->name ?? '-' }}</td>
                    <td>{{ \Auth::user()->dateFormat($grn->grn_date) }}</td>
                    <td>{{ $grn->reference_no ?? '-' }}</td>
                    <td>{{ \Auth::user()->priceFormat($grn->getSubTotal()) }}</td>
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
                            @elseif($grn->status == 9) bg-danger
                            @elseif($grn->status == 10) bg-danger
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
                        @if (in_array($grn->status, [0, 9, 10]))
                            @can('edit grn')
                            <a href="#" data-url="{{ route('grn.edit', $grn->id) }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Edit GRN') }}"
                                class="mx-1 btn btn-sm btn-outline-info align-items-center">
                                <i class="ti ti-pencil"></i>
                            </a>
                            @endcan
                            <a href="{{ route('grn.fw_to_ho', $grn->id) }}"
                                class="mx-1 btn btn-sm btn-outline-info align-items-center" title="{{ __('Fw to Ho') }}">
                                <i class="ti ti-mail-forward"></i>
                            </a>
                        @endif
                        @if($grn->status == 5 && \Auth::user()->can('finalize grn'))
                            
                            <a href="{{ route('grn.finalize', $grn->id) }}"
                                class="mx-1 btn btn-sm btn-outline-info align-items-center" title="{{ __('Finalize') }}"
                                onclick="return confirm('{{ __('Finalize this GRN? It will be ready to forward to Accounts.') }}')">
                                <i class="ti ti-check"></i>
                            </a>
                           
                            <a href="{{ route('grn.reject', $grn->id) }}"
                                class="mx-1 btn btn-sm btn-outline-info align-items-center" title="{{ __('Reject') }}">
                                <i class="ti ti-x"></i>
                            </a>
                        @endif
                        @can('forward grn to accounts')
                            @if($grn->status == 6 )
                            <a href="{{ route('grn.fw_to_accounts', $grn->id) }}"
                                class="mx-1 btn btn-sm btn-outline-info align-items-center" title="{{ __('Fw to Accounts') }}"
                                onclick="return confirm('{{ __('Forward this GRN to Accounts?') }}')">
                                <i class="ti ti-send"></i>
                            </a>
                            @endif
                        @endcan

                    @if($grn->status != 7 )
                            @can('delete grn')
                            {{ Form::open(['route' => ['grn.destroy', $grn->id], 'method' => 'DELETE', 'class' => 'd-inline']) }}
                                <button type="submit" class="mx-1 btn btn-sm btn-outline-info"
                                    onclick="return confirm('{{ __('Are you sure you want to delete this GRN? Stock will be reversed.') }}')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            {{ Form::close() }}
                            @endcan
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
