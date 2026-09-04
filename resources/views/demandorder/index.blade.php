@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Demand Orders') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Demand Order') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create demand order')
            <a href="#" data-url="{{ route('demand-order.create', 0) }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Create Demand Order') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['demand-order.index'], 'method' => 'GET', 'id' => 'demand-order_filter']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branchList, request('branch'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('demand-order_filter').submit(); return false;" data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="">
                    <thead class="table_heads">
                        <tr>
                            <th>{{ __('S.No') }}</th>
                            <th>{{ __('Demand Order') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Demand Order Date') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            @if (Gate::check('edit demand order') || Gate::check('delete demand order') || Gate::check('show demand order') || Gate::check('forward demand order') || Gate::check('approve demand order') || Gate::check('reject demand order') || Gate::check('convert demand order to invoice'))
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($demandOrders as $demandOrder)
                            @php
                                $isCompany = \Auth::user()->type == 'company';
                                $isBranchOwner = \Auth::user()->type == 'branch' && $demandOrder->branch_id == \Auth::user()->id;
                                $canShowDemandOrder = Gate::check('show demand order');
                                $canEditDemandOrder = Gate::check('edit demand order') && (($isCompany && in_array($demandOrder->status, [0, 5]))
                                    || ($isBranchOwner && $demandOrder->status == 0));
                                $canForwardToHo = Gate::check('forward demand order') && $isBranchOwner && $demandOrder->status == 0;
                                $canApprove = Gate::check('approve demand order') && $isCompany && $demandOrder->status == 5;
                                $canReject = Gate::check('reject demand order') && $isCompany && $demandOrder->status == 5;
                                $canConvertToInvoice = Gate::check('convert demand order to invoice') && Gate::check('create invoice') && $isCompany && $demandOrder->status == 6 && !$demandOrder->invoice_converted;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="Id">
                                    @if($canShowDemandOrder)
                                        <a href="{{ route('demand-order.show', Crypt::encrypt($demandOrder->id)) }}"
                                            class="btn btn-outline-primary btnpurchase1">{{ Auth::user()->purchaseNumberFormat($demandOrder->branch_purchase_no) }}</a>
                                    @else
                                        {{ Auth::user()->purchaseNumberFormat($demandOrder->branch_purchase_no) }}
                                    @endif
                                </td>
                                <td>{{ !empty($demandOrder->branchUser) ? $demandOrder->branchUser->name : '' }}</td>
                                <td>{{ Auth::user()->dateFormat($demandOrder->purchase_date) }}</td>
                                <td>{{ \Auth::user()->priceFormat($demandOrder->getTotal()) }}</td>
                                <td>
                                    @if ($demandOrder->status == 0)
                                        <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                    @elseif($demandOrder->status == 5)
                                        <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                    @elseif($demandOrder->status == 6)
                                        <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                    @endif
                                </td>
                                @if (Gate::check('edit demand order') || Gate::check('delete demand order') || Gate::check('show demand order') || Gate::check('forward demand order') || Gate::check('approve demand order') || Gate::check('reject demand order') || Gate::check('convert demand order to invoice'))
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn ms-2">
                                                @if($canShowDemandOrder)
                                                    <a href="{{ route('demand-order.show', Crypt::encrypt($demandOrder->id)) }}"
                                                        class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center" data-bs-title="{{ __('Detail') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                                    </a>
                                                @endif
                                                @if($canEditDemandOrder)
                                                    <a href="#"
                                                        data-url="{{ route('demand-order.edit', Crypt::encrypt($demandOrder->id)) }}"
                                                        data-size="modal-fullscreen" data-ajax-popup="true"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center" data-bs-title="{{ __('Edit') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endif
                                                @if($canForwardToHo)
                                                    <a href="{{ route('demand-order.fw_to_ho', $demandOrder->id) }}"
                                                        class="mx-1 btn btn-outline-warning btn-sm align-items-center"
                                                        data-bs-title="{{ __('Fw to Ho') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to forward this Demand Order to Head Office?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-mail-forward"></i></span>
                                                    </a>
                                                @endif
                                                @if($canApprove)
                                                    {{ Form::open(['route' => ['demand-order.finalize', $demandOrder->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                                                        <button type="submit"
                                                            class="mx-1 btn btn-outline-success btn-sm align-items-center"
                                                            data-bs-title="{{ __('Approve') }}"
                                                            onclick="return confirm('{{ __('Are you sure you want to approve this Demand Order?') }}')">
                                                            <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                                        </button>
                                                    {{ Form::close() }}
                                                @endif
                                                @if($canReject)
                                                    <a href="{{ route('demand-order.reject', $demandOrder->id) }}"
                                                        class="mx-1 btn btn-outline-danger btn-sm align-items-center"
                                                        data-bs-title="{{ __('Reject') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to reject this Demand Order?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                                    </a>
                                                @endif
                                                @if($canConvertToInvoice)
                                                    <a href="#"
                                                        data-url="{{ route('demand-order.convert_to_invoice', $demandOrder->id) }}"
                                                        data-size="modal-fullscreen"
                                                        data-ajax-popup="true"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center" title="Convert to Invoice" data-bs-title="{{ __('Convert to Invoice') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-file-import"></i></span>
                                                    </a>
                                                @endif
                                            </div>
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($demandOrders->hasPages())
                    <div class="pagination">
                        <ul>
                            @if ($demandOrders->onFirstPage())
                                <li class="disabled">&laquo; Previous</li>
                            @else
                                <li><a href="{{ $demandOrders->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                            @endif
                            @if ($demandOrders->currentPage() > 1)
                                <li><a href="{{ $demandOrders->appends(request()->query())->url(1) }}">First</a></li>
                            @endif
                            @php
                                $currentPage = $demandOrders->currentPage();
                                $lastPage = $demandOrders->lastPage();
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
                                <li class="{{ $page == $demandOrders->currentPage() ? 'active' : '' }}">
                                    <a href="{{ $demandOrders->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($demandOrders->hasMorePages())
                                <li><a href="{{ $demandOrders->appends(request()->query())->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                            @else
                                <li class="disabled">Next &raquo;</li>
                            @endif
                            @if ($demandOrders->currentPage() < $demandOrders->lastPage())
                                <li><a href="{{ $demandOrders->appends(request()->query())->url($demandOrders->lastPage()) }}">Last</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
