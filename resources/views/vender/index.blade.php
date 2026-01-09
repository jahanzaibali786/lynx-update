@extends('layouts.admin')
@php
    $profile = asset(Storage::url('uploads/avatar/'));
@endphp
@push('script-page')
    <script>
        $(document).on('click', '#billing_data', function() {
            $("[name='shipping_name']").val($("[name='billing_name']").val());
            $("[name='shipping_country']").val($("[name='billing_country']").val());
            $("[name='shipping_state']").val($("[name='billing_state']").val());
            $("[name='shipping_city']").val($("[name='billing_city']").val());
            $("[name='shipping_phone']").val($("[name='billing_phone']").val());
            $("[name='shipping_zip']").val($("[name='billing_zip']").val());
            $("[name='shipping_address']").val($("[name='billing_address']").val());
        })
    </script>
@endpush
@section('page-title')
    {{ __('Manage Vendors') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vendor') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- <a href="#" class="btn btn-sm btn-primary" data-url="{{ route('vender.file.import') }}" data-ajax-popup="true"
         data-bs-title="{{ __('Import') }}">
        <i class="ti ti-file-import"></i>
    </a>

    <a href="{{ route('vender.export') }}" class="btn btn-sm btn-primary" 
        data-bs-title="{{ __('Export') }}">
        Export
    </a> --}}
        @can('create vender')
            <a href="#" data-size="lg" data-url="{{ route('vender.create') }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary"><span
                    class="btn-inner--icon">Create</span>
            </a>
        @endcan

    </div>
@endsection
@section('content')

    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['vender.index'], 'method' => 'GET', 'id' => 'vender_filter']) }}
            <div class="row d-flex justify-content-start">

                {{-- Vendors Dropdown --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('vender', __('Vendors'), ['class' => 'form-label']) }}
                        {{ Form::select('vender', $vendorList, request('vender'), ['class' => 'form-control select']) }}
                    </div>
                    
                    
                </div>
                {{-- Action Buttons --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('vender_filter').submit(); return false;" data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                </div>

            </div>
            {{ Form::close() }}
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12">
            <div class="table-responsive">
                <table class="">
                    <thead>
                        <tr class="table_heads">
                            <th>{{ __('S.No.') }}</th>
                            <th>#</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Balance') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venders as $k => $Vender)
                            <tr class="cust_tr" id="vend_detail">
                                <td>{{ $loop->iteration }}</td>
                                <td class="Id">
                                    @can('show vender')
                                        <a href="{{ route('vender.show', \Crypt::encrypt($Vender['id'])) }}"
                                            class="btn btn-outline-primary">
                                            {{ AUth::user()->venderNumberFormat($Vender['vender_id']) }}
                                        </a>
                                    @else
                                        <a href="#" class="btn btn-outline-primary">
                                            {{ AUth::user()->venderNumberFormat($Vender['vender_id']) }}
                                        </a>
                                    @endcan
                                </td>
                                <td>{{ $Vender['name'] }}</td>
                                <td>{{ $Vender['contact'] }}</td>
                                <td>{{ $Vender['email'] }}</td>
                                <td>{{ @$Vender->ChartAccount ? @$Vender->ChartAccount->name : '-' }}</td>
                                <td>{{ \Auth::user()->priceFormat($Vender['balance']) }}</td>
                                <td class="Action">
                                    {{-- <span> --}}
                                    <div class="action-btn ms-2">
                                        @if ($Vender['is_active'] == 0)
                                            <i class="fa fa-lock" title="Inactive"></i>
                                        @else
                                            @can('show vender')
                                                {{-- <a href="{{ route('vender.show', \Crypt::encrypt($Vender['id'])) }}"
                                                        class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center" 
                                                        data-bs-title="{{ __('View') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                                    </a> --}}
                                                {{-- <a class="btn mx-1 btn-sm btn-outline-warning"><span class="btn-inner--icon"><i class="ti ti-eye">></span></a> --}}
                                                <a href="{{ route('vender.show', \Crypt::encrypt($Vender['id'])) }}"
                                                    class="btn mx-1 btn-sm btn-outline-info"><span class="btn-inner--icon"><i
                                                            class="fas fa-eye"
                                                            data-bs-title="{{ __('View') }}"></i></span></a>
                                            @endcan
                                            @can('edit vender')
                                                <a href="#" class="mx-1 btn btn-outline-primary btn-sm align-items-center"
                                                    data-size="lg" data-url="{{ route('vender.edit', $Vender['id']) }}"
                                                    data-ajax-popup="true" data-bs-title="{{ __('Edit') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                </a>
                                            @endcan
                                            @can('delete vender')
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['vender.destroy', $Vender['id']],
                                                    'id' => 'delete-form-' . $Vender['id'],
                                                ]) !!}
                                                <a href="#"
                                                    class="mx-1 btn btn-outline-danger btn-sm align-items-center bs-pass-para"
                                                    data-bs-title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $Vender['id'] }}').submit();">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endcan
                                        @endif
                                    </div>
                                    {{-- </span> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($venders->hasPages())
                    <div class="pagination">
                        <ul>
                            @if ($venders->onFirstPage())
                                <li class="disabled">&laquo; Previous</li>
                            @else
                                <li><a href="{{ $venders->appends(request()->query())->previousPageUrl() }}"
                                        rel="prev">&laquo; Previous</a></li>
                            @endif
                            @if ($venders->currentPage() > 1)
                                <li><a href="{{ $venders->appends(request()->query())->url(1) }}">First</a></li>
                            @endif
                            @php
                                $currentPage = $venders->currentPage();
                                $lastPage = $venders->lastPage();
                                $startPage = max(1, $currentPage - 4);
                                $endPage = min($lastPage, $currentPage + 5);
                                if ($endPage - $startPage < 9) {
                                    if ($currentPage < $lastPage - 9) {
                                        $endPage = $startPage + 9;
                                    } else {
                                        $startPage = max(1, $lastPage - 9);
                                    }
                                }
                            @endphp
                            @for ($page = $startPage; $page <= $endPage; $page++)
                                <li class="{{ $page == $venders->currentPage() ? 'active' : '' }}">
                                    <a
                                        href="{{ $venders->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($venders->hasMorePages())
                                <li><a href="{{ $venders->appends(request()->query())->nextPageUrl() }}"
                                        rel="next">Next
                                        &raquo;</a></li>
                            @else
                                <li class="disabled">Next &raquo;</li>
                            @endif
                            @if ($venders->currentPage() < $venders->lastPage())
                                <li><a
                                        href="{{ $venders->appends(request()->query())->url($venders->lastPage()) }}">Last</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
