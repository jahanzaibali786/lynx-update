@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Purchase') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase') }}</li>
@endsection
@push('script-page')
    <script>
        $('.copy_link').click(function(e) {
            e.preventDefault();
            var copyText = $(this).attr('href');

            document.addEventListener('copy', function(e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        });
    </script>
@endpush


@section('action-btn')
    <div class="float-end">


        {{--        <a href="{{ route('bill.export') }}" class="btn btn-sm btn-primary"  data-bs-title="{{__('Export')}}"> --}}
        {{--            Export --}}
        {{--        </a> --}}

        @can('create purchase')
            <a href="{{ route('purchase.create', 0) }}" class="btn mx-1 btn-sm btn-outline-primary" 
                data-bs-title="{{ __('Create') }}">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection


@section('content')

        <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['purchase.index'], 'method' => 'GET', 'id' => 'purchase_filter']) }}
            <div class="row d-flex justify-content-start">

                {{-- Vendor Dropdown --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('vender', __('Vendors'), ['class' => 'form-label']) }}
                        {{ Form::open(['method' => 'GET', 'id' => 'purchase_filter']) }}
                        {{ Form::select('vender', $vendorList, request('vender'), ['class' => 'form-control select']) }}
                        {{ Form::close() }}
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('purchase_filter').submit(); return false;" data-bs-title="Search">
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
                            <th> {{ __('Purchase') }}</th>
                            <th> {{ __('Vendor') }}</th>
                            {{-- <th> {{ __('Category') }}</th> --}}
                            <th> {{ __('Purchase Date') }}</th>
                            <th> {{ __('Total Amount') }}</th>
                            <th> {{ __('Due Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            @if (Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                <th> {{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>


                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="Id">
                                    <a href="{{ route('purchase.show', \Crypt::encrypt($purchase->id)) }}"
                                        class="btn btn-outline-primary btnpurchase1">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</a>

                                </td>

                                <td> {{ !empty($purchase->vender) ? $purchase->vender->name : '' }} </td>

                                {{-- <td>{{ !empty($purchase->category) ? $purchase->category->name : '' }}</td> --}}
                                <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                                <td>{{ \Auth::user()->priceFormat($purchase->getTotal()) }}</td>
                                <td>{{ \Auth::user()->priceFormat($purchase->getDue()) }}</td>
                                <td>
                                    @if ($purchase->status == 0)
                                        <span
                                            class="purchase_status badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 1)
                                        <span
                                            class="purchase_status badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 2)
                                        <span
                                            class="purchase_status badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 3)
                                        <span
                                            class="purchase_status badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 4)
                                        <span
                                            class="purchase_status badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 5)
                                        <span
                                            class="purchase_status badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @elseif($purchase->status == 6)
                                        <span
                                            class="purchase_status badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                    @endif
                                </td>



                                @if (Gate::check('edit purchase') || Gate::check('delete purchase') || Gate::check('show purchase'))
                                    <td class="Action">
                                        <span>

                                            <div class="action-btn ms-2">
                                                @can('show purchase')
                                                    <a href="{{ route('purchase.show', \Crypt::encrypt($purchase->id)) }}"
                                                        class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center"
                                                         data-bs-title="{{ __('Show') }}"
                                                        data-bs-title="{{ __('Detail') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                                    </a>
                                                @endcan
                                                @can('edit purchase')
                                                    <a href="{{ route('purchase.edit', \Crypt::encrypt($purchase->id)) }}"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center"
                                                         title="Edit"
                                                        data-bs-title="{{ __('Edit') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endcan
                                                @if($purchase->status == 6 && \Auth::user()->type == 'company' && !$purchase->grn_converted)
                                                    <a href="{{ route('purchase.convert_to_grn', $purchase->id) }}"
                                                        class="mx-1 btn btn-outline-primary btn-sm align-items-center"
                                                        title="Convert to GRN"
                                                        data-bs-title="{{ __('Convert to GRN') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-file-import"></i></span>
                                                    </a>
                                                @endif
                                                {{-- @can('delete purchase')
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['purchase.destroy', $purchase->id],
                                                        'class' => 'delete-form-btn',
                                                        'id' => 'delete-form-' . $purchase->id,
                                                    ]) !!}
                                                    <a href="#"
                                                        class="mx-1 btn btn-outline-danger btn-sm align-items-center bs-pass-para"
                                                         data-bs-title="{{ __('Delete') }}"
                                                        data-bs-title="{{ __('Delete') }}"
                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('delete-form-{{ $purchase->id }}').submit();">
                                                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endcan --}}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
@if ($purchases->hasPages())
<div class="pagination">
    <ul>
        @if ($purchases->onFirstPage())
            <li class="disabled">&laquo; Previous</li>
        @else
            <li><a href="{{ $purchases->appends(request()->query())->previousPageUrl() }}"
                    rel="prev">&laquo; Previous</a></li>
        @endif
        @if ($purchases->currentPage() > 1)
            <li><a href="{{ $purchases->appends(request()->query())->url(1) }}">First</a></li>
        @endif
        @php
            $currentPage = $purchases->currentPage();
            $lastPage = $purchases->lastPage();
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
            <li class="{{ $page == $purchases->currentPage() ? 'active' : '' }}">
                <a href="{{ $purchases->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
        @endfor
        @if ($purchases->hasMorePages())
            <li><a href="{{ $purchases->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                    &raquo;</a></li>
        @else
            <li class="disabled">Next &raquo;</li>
        @endif
        @if ($purchases->currentPage() < $purchases->lastPage())
            <li><a
                    href="{{ $purchases->appends(request()->query())->url($purchases->lastPage()) }}">Last</a>
            </li>
        @endif
    </ul>
</div>
@endif
            </div>
        </div>
    </div>

@endsection
