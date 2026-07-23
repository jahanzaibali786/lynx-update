@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Branch Purchase') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Branch Purchase') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(\Auth::user()->type == 'company' && Gate::check('create purchase') || \Auth::user()->type == 'branch')
            <a href="#" data-url="{{ route('branchpurchase.create', 0) }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Create Branch Purchase') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['branchpurchase.index'], 'method' => 'GET', 'id' => 'branchpurchase_filter']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branchList, request('branch'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('branchpurchase_filter').submit(); return false;" data-bs-title="Search">
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
                            <th>{{ __('Branch Purchase') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Purchase Date') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            @if (Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase') || \Auth::user()->type == 'branch')
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branchPurchases as $branchPurchase)
                            @php
                                $isCompany = \Auth::user()->type == 'company';
                                $isBranchOwner = \Auth::user()->type == 'branch' && $branchPurchase->branch_id == \Auth::user()->id;
                                $canEditBranchPurchase = ($isCompany && Gate::check('edit purchase') && in_array($branchPurchase->status, [0, 5]))
                                    || ($isBranchOwner && $branchPurchase->status == 0);
                                $canForwardToHo = $isBranchOwner && $branchPurchase->status == 0;
                                $canApproveReject = $isCompany && $branchPurchase->status == 5;
                                $canConvertToInvoice = $isCompany && $branchPurchase->status == 6 && !$branchPurchase->invoice_converted;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="Id">
                                    <a href="{{ route('branchpurchase.show', Crypt::encrypt($branchPurchase->id)) }}"
                                        class="btn btn-outline-primary btnpurchase1">{{ Auth::user()->purchaseNumberFormat($branchPurchase->branch_purchase_no) }}</a>
                                </td>
                                <td>{{ !empty($branchPurchase->branchUser) ? $branchPurchase->branchUser->name : '' }}</td>
                                <td>{{ Auth::user()->dateFormat($branchPurchase->purchase_date) }}</td>
                                <td>{{ \Auth::user()->priceFormat($branchPurchase->getTotal()) }}</td>
                                <td>
                                    @if ($branchPurchase->status == 0)
                                        <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\BranchPurchase::$statues[$branchPurchase->status]) }}</span>
                                    @elseif($branchPurchase->status == 5)
                                        <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\BranchPurchase::$statues[$branchPurchase->status]) }}</span>
                                    @elseif($branchPurchase->status == 6)
                                        <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\BranchPurchase::$statues[$branchPurchase->status]) }}</span>
                                    @endif
                                </td>
                                @if (Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase') || \Auth::user()->type == 'branch')
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn ms-2">
                                                @if(\Auth::user()->type == 'company' && Gate::check('show purchase') || \Auth::user()->type == 'branch')
                                                    <a href="{{ route('branchpurchase.show', Crypt::encrypt($branchPurchase->id)) }}"
                                                        class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center" data-bs-title="{{ __('Detail') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                                    </a>
                                                @endif
                                                @if($canEditBranchPurchase)
                                                    <a href="#"
                                                        data-url="{{ route('branchpurchase.edit', Crypt::encrypt($branchPurchase->id)) }}"
                                                        data-size="modal-fullscreen" data-ajax-popup="true"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center" data-bs-title="{{ __('Edit') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endif
                                                @if($canForwardToHo)
                                                    <a href="{{ route('branchpurchase.fw_to_ho', $branchPurchase->id) }}"
                                                        class="mx-1 btn btn-outline-warning btn-sm align-items-center"
                                                        data-bs-title="{{ __('Fw to Ho') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to forward this branch purchase to Head Office?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-mail-forward"></i></span>
                                                    </a>
                                                @endif
                                                @if($canApproveReject)
                                                    {{ Form::open(['route' => ['branchpurchase.finalize', $branchPurchase->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                                                        <button type="submit"
                                                            class="mx-1 btn btn-outline-success btn-sm align-items-center"
                                                            data-bs-title="{{ __('Approve') }}"
                                                            onclick="return confirm('{{ __('Are you sure you want to approve this branch purchase?') }}')">
                                                            <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                                        </button>
                                                    {{ Form::close() }}
                                                    <a href="{{ route('branchpurchase.reject', $branchPurchase->id) }}"
                                                        class="mx-1 btn btn-outline-danger btn-sm align-items-center"
                                                        data-bs-title="{{ __('Reject') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to reject this branch purchase?') }}')">
                                                        <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                                    </a>
                                                @endif
                                                @if($canConvertToInvoice)
                                                    <a href="#"
                                                        data-url="{{ route('branchpurchase.convert_to_invoice', $branchPurchase->id) }}"
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
                @if ($branchPurchases->hasPages())
                    <div class="pagination">
                        <ul>
                            @if ($branchPurchases->onFirstPage())
                                <li class="disabled">&laquo; Previous</li>
                            @else
                                <li><a href="{{ $branchPurchases->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                            @endif
                            @if ($branchPurchases->currentPage() > 1)
                                <li><a href="{{ $branchPurchases->appends(request()->query())->url(1) }}">First</a></li>
                            @endif
                            @php
                                $currentPage = $branchPurchases->currentPage();
                                $lastPage = $branchPurchases->lastPage();
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
                                <li class="{{ $page == $branchPurchases->currentPage() ? 'active' : '' }}">
                                    <a href="{{ $branchPurchases->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($branchPurchases->hasMorePages())
                                <li><a href="{{ $branchPurchases->appends(request()->query())->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                            @else
                                <li class="disabled">Next &raquo;</li>
                            @endif
                            @if ($branchPurchases->currentPage() < $branchPurchases->lastPage())
                                <li><a href="{{ $branchPurchases->appends(request()->query())->url($branchPurchases->lastPage()) }}">Last</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
