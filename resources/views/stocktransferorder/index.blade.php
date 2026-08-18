@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Stock Transfer Requisitions') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Stock Transfer Requisition') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create stock transfer order')
            <a href="#" data-url="{{ route('stock-transfer-order.create', 0) }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Create Stock Transfer Requisition') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['stock-transfer-order.index'], 'method' => 'GET', 'id' => 'stock-transfer-order_filter']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branchList, request('branch'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('stock-transfer-order_filter').submit(); return false;" data-bs-title="Search">
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
                            <th>{{ __('Stock Transfer Requisition') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Stock Transfer Requisition Date') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            @if (Gate::check('edit stock transfer order') || Gate::check('delete stock transfer order') || Gate::check('show stock transfer order') || Gate::check('forward stock transfer order') || Gate::check('approve stock transfer order') || Gate::check('reject stock transfer order') || Gate::check('convert stock transfer order to invoice'))
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($StockTransferOrders as $StockTransferOrder)
                            @php
                                $isCompany = \Auth::user()->type == 'company';
                                $isBranchOwner = \Auth::user()->type == 'branch' && $StockTransferOrder->branch_id == \Auth::user()->id;
                                $canShowOrder = Gate::check('show stock transfer order');
                                $canEditOrder = Gate::check('edit stock transfer order') && (($isCompany && in_array($StockTransferOrder->status, [\App\Models\StockTransferOrder::STATUS_DRAFT, \App\Models\StockTransferOrder::STATUS_REJECTED], true))
                                    || ($isBranchOwner && in_array($StockTransferOrder->status, [\App\Models\StockTransferOrder::STATUS_DRAFT, \App\Models\StockTransferOrder::STATUS_REJECTED], true)));
                                $canDeleteOrder = Gate::check('delete stock transfer order') && (($isCompany && in_array($StockTransferOrder->status, [\App\Models\StockTransferOrder::STATUS_DRAFT, \App\Models\StockTransferOrder::STATUS_REJECTED], true))
                                    || ($isBranchOwner && in_array($StockTransferOrder->status, [\App\Models\StockTransferOrder::STATUS_DRAFT, \App\Models\StockTransferOrder::STATUS_REJECTED], true)));
                                $canForwardToHo = Gate::check('forward stock transfer order') && (($isCompany || $isBranchOwner) && in_array($StockTransferOrder->status, [\App\Models\StockTransferOrder::STATUS_DRAFT, \App\Models\StockTransferOrder::STATUS_REJECTED], true));
                                $canApprove = Gate::check('approve stock transfer order') && $isCompany && $StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_SENT_TO_HO;
                                $canReject = Gate::check('reject stock transfer order') && $isCompany && $StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_SENT_TO_HO;
                                $canConvertToInvoice = Gate::check('convert stock transfer order to invoice') && Gate::check('create stock transfer note') && $isCompany && $StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_APPROVED && !$StockTransferOrder->invoice_converted;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="Id">
                                    @if($canShowOrder)
                                        <a href="{{ route('stock-transfer-order.show', Crypt::encrypt($StockTransferOrder->id)) }}"
                                            class="btn btn-outline-primary btnpurchase1">{{ Auth::user()->purchaseNumberFormat($StockTransferOrder->branch_purchase_no) }}</a>
                                    @else
                                        {{ Auth::user()->purchaseNumberFormat($StockTransferOrder->branch_purchase_no) }}
                                    @endif
                                </td>
                                <td>{{ !empty($StockTransferOrder->branchUser) ? $StockTransferOrder->branchUser->name : '' }}</td>
                                <td>{{ Auth::user()->dateFormat($StockTransferOrder->purchase_date) }}</td>
                                <td>{{ \Auth::user()->priceFormat($StockTransferOrder->getTotal()) }}</td>
                                <td>
                                    @if ($StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_DRAFT)
                                        <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\StockTransferOrder::$statues[$StockTransferOrder->status]) }}</span>
                                    @elseif($StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_SENT_TO_HO)
                                        <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\StockTransferOrder::$statues[$StockTransferOrder->status]) }}</span>
                                    @elseif($StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_APPROVED)
                                        <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\StockTransferOrder::$statues[$StockTransferOrder->status]) }}</span>
                                    @elseif($StockTransferOrder->status == \App\Models\StockTransferOrder::STATUS_REJECTED)
                                        <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\StockTransferOrder::$statues[$StockTransferOrder->status]) }}</span>
                                    @endif
                                </td>
                                @if (Gate::check('edit stock transfer order') || Gate::check('delete stock transfer order') || Gate::check('show stock transfer order') || Gate::check('forward stock transfer order') || Gate::check('approve stock transfer order') || Gate::check('reject stock transfer order') || Gate::check('convert stock transfer order to invoice'))
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn ms-2">
                                                <a href="{{ route('stock-transfer-order.print', Crypt::encrypt($StockTransferOrder->id)) }}"
                                                        class="mx-1 btn btn-outline-secondary btn-sm align-items-center" target="_blank"
                                                        data-bs-title="{{ __('Print') }}" title="{{ __('Print') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-printer"></i></span>
                                                    </a>
                                                @if($canShowOrder)
                                                    <a href="{{ route('stock-transfer-order.show', Crypt::encrypt($StockTransferOrder->id)) }}"
                                                        class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center" data-bs-title="{{ __('Detail') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                                    </a>
                                                @endif
                                                @if($canEditOrder)
                                                    <a href="#"
                                                        data-url="{{ route('stock-transfer-order.edit', Crypt::encrypt($StockTransferOrder->id)) }}"
                                                        data-size="modal-fullscreen" data-ajax-popup="true"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center" data-bs-title="{{ __('Edit') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                    
                                                @endif
                                                @if($canForwardToHo)
                                                    <a href="{{ route('stock-transfer-order.fw_to_ho', $StockTransferOrder->id) }}"
                                                        class="mx-1 btn btn-outline-warning btn-sm align-items-center"
                                                        data-bs-title="{{ __('Forward to HO') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to forward this Stock Transfer Requisition to Head Office?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-mail-forward"></i></span>
                                                    </a>
                                                @endif
                                                @if($canApprove)
                                                    {{ Form::open(['route' => ['stock-transfer-order.finalize', $StockTransferOrder->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                                                        <button type="submit"
                                                            class="mx-1 btn btn-outline-success btn-sm align-items-center"
                                                            data-bs-title="{{ __('Approve') }}"
                                                            onclick="return confirm('{{ __('Are you sure you want to approve this Stock Transfer Requisition?') }}')">
                                                            <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                                        </button>
                                                    {{ Form::close() }}
                                                @endif
                                                @if($canReject)
                                                    <a href="{{ route('stock-transfer-order.reject', $StockTransferOrder->id) }}"
                                                        class="mx-1 btn btn-outline-danger btn-sm align-items-center"
                                                        data-bs-title="{{ __('Reject') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to reject this Stock Transfer Requisition?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                                    </a>
                                                @endif
                                                @if($canDeleteOrder)
                                                    {{ Form::open(['route' => ['stock-transfer-order.destroy', $StockTransferOrder->id], 'method' => 'DELETE', 'class' => 'd-inline']) }}
                                                        <button type="submit"
                                                            class="mx-1 btn btn-outline-danger btn-sm align-items-center"
                                                            data-bs-title="{{ __('Delete') }}"
                                                            onclick="return confirm('{{ __('Are you sure you want to delete this Stock Transfer Requisition?') }}')">
                                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                        </button>
                                                    {{ Form::close() }}
                                                @endif
                                                @if($canConvertToInvoice)
                                                    <a href="#"
                                                        data-url="{{ route('stock-transfer-order.convert_to_invoice', $StockTransferOrder->id) }}"
                                                        data-size="modal-fullscreen"
                                                        data-ajax-popup="true"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center" title="Convert to Stock Transfer Note" data-bs-title="{{ __('Convert to Stock Transfer Note') }}">
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
                @if ($StockTransferOrders->hasPages())
                    <div class="pagination">
                        <ul>
                            @if ($StockTransferOrders->onFirstPage())
                                <li class="disabled">&laquo; Previous</li>
                            @else
                                <li><a href="{{ $StockTransferOrders->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                            @endif
                            @if ($StockTransferOrders->currentPage() > 1)
                                <li><a href="{{ $StockTransferOrders->appends(request()->query())->url(1) }}">First</a></li>
                            @endif
                            @php
                                $currentPage = $StockTransferOrders->currentPage();
                                $lastPage = $StockTransferOrders->lastPage();
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
                                <li class="{{ $page == $StockTransferOrders->currentPage() ? 'active' : '' }}">
                                    <a href="{{ $StockTransferOrders->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($StockTransferOrders->hasMorePages())
                                <li><a href="{{ $StockTransferOrders->appends(request()->query())->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                            @else
                                <li class="disabled">Next &raquo;</li>
                            @endif
                            @if ($StockTransferOrders->currentPage() < $StockTransferOrders->lastPage())
                                <li><a href="{{ $StockTransferOrders->appends(request()->query())->url($StockTransferOrders->lastPage()) }}">Last</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
