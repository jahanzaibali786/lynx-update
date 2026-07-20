@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Bank Recipt Voucher Entry') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Vouchers') }}</li>
    <li class="breadcrumb-item">{{ __('Bank Recipt Voucher Entry') }}</li>
@endsection

@section('action-btn')
    <style>
        .wrap-td {
            max-width: 500px !important;
            text-wrap: auto !important;
        }
    </style>
    <div class="float-end">
        @can('create journal entry')
            <a href="{{ route('bank-recipt-voucher.create') }}" title="{{ __('Create New Bank Recipt Voucher') }}"
                 data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create </span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['bank-recipt-voucher.index'], 'method' => 'GET', 'id' => 'bank-recipt-voucher_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('bank-recipt-voucher_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('bank-recipt-voucher.index') }}"
                                        class="btn mx-1 btn-sm btn-outline-danger" 
                                        data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- @endif --}}
    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th> {{ __('Voucher ID') }}</th>
                <th> {{ __('Date') }}</th>
                <th> {{ __('Payee') }}</th>
                <th> {{ __('Receiver') }}</th>
                <th> {{ __('Payment Mode') }}</th>
                <th> {{ __('Amount') }}</th>
                <th class="wrap-td"> {{ __('Description') }}</th>
                <th width="10%"> {{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($journalEntries as $journalEntry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="Id">
                        <a href="{{ route('bank-recipt-voucher.show', $journalEntry->id) }}"
                            class="btn btn-outline-primary"><span class="btn-inner--icon">{{ $journalEntry->getVoucherNumber() }}</span></a>
                    </td>
                    <td>{{ Auth::user()->dateFormat($journalEntry->date) }}</td>
                    <td>
                        <div><strong>{{ $journalEntry->payee_account_title ?? '-' }}</strong></div>
                        <small class="text-muted">{{ $journalEntry->payee_account_no ?? '' }}</small>
                    </td>
                    <td>{{ $journalEntry->receiver_name ?? '-' }}</td>
                    <td>
                        <div>{{ strtoupper($journalEntry->payment_mode ?? ($journalEntry->mode ?? '-')) }}</div>
                        @if(!empty($journalEntry->payment_date))
                            <small class="text-muted">{{ \Auth::user()->dateFormat($journalEntry->payment_date) }}</small>
                        @endif
                    </td>
                    <td>
                        {{ \Auth::user()->priceFormat($journalEntry->totalCredit()) }}
                    </td>
                    <td class="wrap-td">{{ !empty($journalEntry->description) ? $journalEntry->description : '-' }}</td>
                    <td>
                        <div class="action-btn ms-2" style="display: flex; gap: 5px;">
                            @can('show journal entry')
                                <a title="{{ __('Voucher Print') }}" href="{{ route('journal-entry.voucher-print', $journalEntry->id) }}" target="_blank" class="mx-1 btn mx-1 btn-sm btn-outline-secondary align-items-center" data-bs-title="{{ __('Print') }}">
                                    <span class="btn-inner--icon"> <i class="ti ti-printer"></i> </span>
                                </a>
                            @endcan
                            @can('edit journal entry')
                                <a title="{{ __('Edit') }}"
                                    href="{{ route('journal-entry.edit', [$journalEntry->id]) }}"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center" 
                                    data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"> <i class="ti ti-pencil"></i> </span>
                                </a>
                            @endcan
                            @can('delete journal entry')
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['bank-recipt-voucher.destroy', $journalEntry->id],
                                    'id' => 'delete-form-' . $journalEntry->id,
                                    'style' => 'display:inline;'
                                ]) !!}
                                <a href="#"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                     data-bs-title="{{ __('Delete') }}"
                                    data-bs-title="{{ __('Delete') }}"
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('delete-form-{{ $journalEntry->id }}').submit();">
                                    <span class="btn-inner--icon"> <i class="ti ti-trash"></i> </span>
                                </a>
                                {!! Form::close() !!}
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- has pagination  --}}
    @if ($journalEntries->hasPages())
        <div class="pagination">
            <ul>
                @if ($journalEntries->onFirstPage())
                    <li class="disabled">&laquo;</li>
                @else
                    <li><a href="{{ $journalEntries->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo;</a></li>
                @endif
                @if ($journalEntries->currentPage() > 1)
                    <li><a href="{{ $journalEntries->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $journalEntries->currentPage();
                    $lastPage = $journalEntries->lastPage();
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
                    <li class="{{ $page == $journalEntries->currentPage() ? 'active' : '' }}">
                        <a href="{{ $journalEntries->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($journalEntries->hasMorePages())
                    <li><a href="{{ $journalEntries->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                @else
                    <li class="disabled">&raquo;</li>
                @endif
                @if ($journalEntries->currentPage() < $journalEntries->lastPage())
                    <li><a href="{{ $journalEntries->appends(request()->query())->url($journalEntries->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif





@endsection
