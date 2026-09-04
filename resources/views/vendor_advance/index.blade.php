@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Vendor Advance') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vendor Advance') }}</li>
@endsection

@section('action-btn')
    @can('create vender')
        <div class="col text-end">
            <a href="#" data-url="{{ route('vendor-advance.create') }}" data-size="lg" data-ajax-popup="true"
                data-bs-title="{{ __('Create Vendor Advance') }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip">
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
                        {{ Form::open(['route' => ['vendor-advance.index'], 'method' => 'GET', 'id' => 'vendor_advance_filter']) }}
                        <div class="row align-items-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('vender_id', __('Vendor'), ['class' => 'form-label']) }}
                                    {{ Form::select('vender_id', $vendors, request('vender_id'), ['class' => 'form-control custom-select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('status', ['' => __('Select Status'), '0' => __('Pending'), '1' => __('Approved'), '2' => __('Rejected')], request('status'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('from_month', __('From Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('from_month', request('from_month'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                                <div class="btn-box">
                                    {{ Form::label('to_month', __('To Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('to_month', request('to_month'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12">
                                <a href="#" class="btn btn-sm btn-outline-primary mx-1"
                                    onclick="document.getElementById('vendor_advance_filter').submit(); return false;">
                                    {{ __('Search') }}
                                </a>
                                <a href="{{ route('vendor-advance.index') }}" class="btn btn-sm btn-outline-danger mx-1">
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

    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$advances->isEmpty())
                <table class="">
                    <thead>
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('Vendor') }}</th>
                            <th>{{ __('Advance Month') }}</th>
                            <th>{{ __('Approval Date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Bank') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Approved By') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($advances as $advance)
                            <tr>
                                <td>{{ ($advances->currentPage() - 1) * $advances->perPage() + $loop->iteration }}</td>
                                <td>{{ $advance->vendor->display_name ?? '-' }}</td>
                                <td>{{ !empty($advance->advance_date) ? \Carbon\Carbon::parse($advance->advance_date)->format('M Y') : '-' }}</td>
                                <td>{{ !empty($advance->approval_date) ? \Carbon\Carbon::parse($advance->approval_date)->format('d-M-Y') : '-' }}</td>
                                <td>{{ \Auth::user()->priceFormat($advance->advance_amount) }}</td>
                                <td>{{ !empty($advance->bank) ? $advance->bank->bank_name . ' - ' . $advance->bank->holder_name : '-' }}</td>
                                <td>
                                    @if ($advance->status == 0)
                                        <a href="#" data-url="{{ route('vendor-advance.status', $advance->id) }}"
                                            data-size="lg" data-ajax-popup="true"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Approve / Reject') }}"
                                            class="btn btn-sm btn-outline-warning w-100">{{ __('Pending') }}</a>
                                    @elseif ($advance->status == 1)
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                    @endif
                                </td>
                                <td><small>{{ $advance->approvedBy->name ?? '-' }}</small></td>
                                <td>
                                    <div class="action-btn d-flex align-items-center gap-1">
                                        @if($advance->status == 0)
                                            @can('edit vender')
                                                <a href="#" data-url="{{ route('vendor-advance.status', $advance->id) }}"
                                                    data-size="lg" data-ajax-popup="true"
                                                    data-bs-toggle="tooltip" data-bs-title="{{ __('Approve / Reject') }}"
                                                    class="btn btn-sm btn-outline-success">
                                                    <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                                </a>
                                                <a href="#" data-url="{{ route('vendor-advance.edit', $advance->id) }}"
                                                    data-size="lg" data-ajax-popup="true"
                                                    data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                </a>
                                            @endcan
                                            <a href="{{ route('vendor-advance.print', ['id' => $advance->id, 'preview' => 1]) }}"
                                                target="_blank"
                                                data-bs-toggle="tooltip" data-bs-title="{{ __('Preview PDF') }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <span class="btn-inner--icon"><i class="fas fa-print"></i></span>
                                            </a>
                                            @can('delete vender')
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['vendor-advance.destroy', $advance->id],
                                                    'id' => 'delete-form-' . $advance->id,
                                                    'class' => 'd-inline',
                                                ]) !!}
                                                <a type="button" class="btn btn-sm btn-outline-danger bs-pass-para"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $advance->id }}').submit();"
                                                    data-bs-toggle="tooltip" data-bs-title="{{ __('Delete') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endcan
                                        @else
                                            <a href="{{ route('vendor-advance.print', ['id' => $advance->id, 'preview' => 1]) }}"
                                                target="_blank"
                                                data-bs-toggle="tooltip" data-bs-title="{{ __('Preview PDF') }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <span class="btn-inner--icon"><i class="fas fa-print"></i></span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mt-2 text-center">{{ __('No Vendor Advance Data Found!') }}</div>
            @endif
        </div>
    </div>

    @if ($advances->hasPages())
        <div class="pagination">
            {{ $advances->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
