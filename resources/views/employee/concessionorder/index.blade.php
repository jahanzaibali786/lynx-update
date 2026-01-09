@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Concession Orders') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Concession Orders') }}</li>
@endsection

@section('action-btn')
    <div class="col text-end">
        <a href="{{ route('emp-concession-order.create') }}"  title="create"
            class="apply-btn btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
    </div>
@endsection
@push('script-page')
@endpush
@section('content')
    <div class="table-responsive" style="margin-top: 20px">
        <table class="datatable">
            <thead class="table_heads">
                <tr>
                    <th>Emp. Id</th>
                    <th>Emp. Name</th>
                    <th>Total Discount</th>
                    <th>Actual</th>
                    <th>Payable</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>12345</td>
                    <td>John Doe</td>
                    <td>100</td>
                    <td>900</td>
                    <td>800</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ url('emp-concession-order/1') }}" class="mx-1 btn btn-sm  btn-outline-primary align-items-center"
                                    data-bs-title="{{ __('Show') }}" 
                                    data-bs-title="{{ __('Show') }}"><span class="btn-inner--icon">
                                        <i class="ti ti-eye"></i></span></a>
                            {{-- <a href="{{ url('emp-concession-order/1') }}">Show</a> --}}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    {{-- @if ($loans->hasPages())
        <div class="pagination">
            <ul>
                @if ($loans->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $loans->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;
                            Previous</a></li>
                @endif
                @if ($loans->currentPage() > 1)
                    <li><a href="{{ $loans->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $loans->currentPage();
                    $lastPage = $loans->lastPage();
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
                    <li class="{{ $page == $loans->currentPage() ? 'active' : '' }}">
                        <a href="{{ $loans->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($loans->hasMorePages())
                    <li><a href="{{ $loans->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($loans->currentPage() < $loans->lastPage())
                    <li><a href="{{ $loans->appends(request()->query())->url($loans->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}

@endsection
