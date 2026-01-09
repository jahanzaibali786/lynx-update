@extends('layouts.admin')
@section('page-title')
{{ __('Manage Products') }}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#category-select').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '{{ url('/get-subcategories') }}/' + categoryId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var subcategorySelect = $('#subcategory-select');
                        subcategorySelect.empty();
                        subcategorySelect.append('<option value="">Select Subcategory</option>');
                        $.each(data.subcategories, function(index, subcategory) {
                            subcategorySelect.append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching subcategories:', error);
                    }
                });
            } else {
                $('#subcategory-select').empty().append('<option value="">Select Subcategory</option>');
            }
        });
    });
    function printproductslist() {
        var form = document.getElementById('product_service');
        var formData = new FormData(form);
        var queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: "{{ route('printproductslist') }}?" + queryString,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
    }
    </script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Product Information ') }}</li>
@endsection
@section('action-btn')
<div class="float-end">
    <a href="#" data-size="lg" data-url="{{ route('productservice.create') }}" data-ajax-popup="true"
         data-bs-title="{{ __('Create New Product') }}" class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Create</span>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 {{ isset($_GET['category']) ? 'show' : '' }}" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['productservice.index'], 'method' => 'GET', 'id' => 'product_service']) }}
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                                {{ Form::select('category', $category, request()->category, ['class' => 'js-searchBox form-control select', 'id' => 'category-select', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('subcategory', __('Subcategory'), ['class' => 'form-label']) }}
                                {{ Form::select('subcategory',$subcategory, request()->subcategory, ['class' => 'js-searchBox form-control select', 'id' => 'subcategory-select', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('product_service').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('productservice.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                 data-bs-title="{{ __('Reset') }}">
                                <span class="btn-inner--icon">Clear</span>
                            </a>
                                {{-- <a href="#" onclick="printproductslist(); return false;"
                                    class="btn mx-1 btn-sm btn-outline-primary" title="" title="Print">
                                    <span class="btn-inner--icon"><i class="ti ti-file-export"> Print</i></span>
                                </a> --}}
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12">
        <table style="width: 99.5% !important;">
            <thead class="table_heads">
                <tr>
                    <th>{{ __('Sr.') }}</th>
                    <th>{{ __('Product Name') }}</th>
                    <th>{{ __('Product code') }}</th>
                    <th>{{ __('Sale Price') }}</th>
                    <th>{{ __('Purchase Price') }}</th>
                    <th>{{ __('Tax') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('SubCategory') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th>{{ __('Quantity') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productServices as $productService)
                <tr class="font-style">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $productService->name }}</td>
                    <td>{{ $productService->sku }}</td>
                    <td>{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
                    <td>{{ \Auth::user()->priceFormat($productService->purchase_price) }}</td>
                    <td>
                        @if (!empty($productService->tax_id))
                        @php
                        $taxes = \App\Models\Utility::tax($productService->tax_id);
                        @endphp

                        @foreach ($taxes as $tax)
                        <span class="">{{ !empty($tax) ? $tax->name : '' . ' (' . $tax->rate . '%)' }}</span><br>
                        @endforeach
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ !empty($productService->category) ? $productService->category->name : '' }}</td>
                    <td>{{ !empty($productService->subcategory) ? $productService->subcategory->name : '' }}</td>
                    <td>{{ !empty($productService->unit()) ? $productService->unit()->name : '' }}</td>
                    @if ($productService->type == 'product')
                    <td>{{ $productService->quantity }}</td>
                    @else
                    <td>-</td>
                    @endif


                    @if (Gate::check('edit product & service') || Gate::check('delete product & service'))
                    <td class="Action">
                        <div class="action-btn ms-2">

                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center"
                                data-url="{{ route('productservice.detail', $productService->id) }}"
                                data-ajax-popup="true"  data-bs-title="{{ __('Store Details') }}"
                                data-bs-toggle="{{ __('Store Details') }}">
                                <span class="btn-inner--icon"><i class="fas fa-eye"></i></span>
                            </a>

                            @can('edit product & service')
                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center"
                                data-url="{{ route('productservice.edit', $productService->id) }}"
                                data-ajax-popup="true" data-size="lg "  data-bs-title="{{ __('Edit') }}"
                                data-bs-toggle="{{ __('Edit Product') }}">
                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                            </a>
                            @endcan
                            @can('delete product & service')
                            {!! Form::open([
                            'method' => 'DELETE',
                            'route' => ['productservice.destroy', $productService->id],
                            'id' => 'delete-form-' . $productService->id,
                            ]) !!}
                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                 data-bs-title="{{ __('Delete') }}">
                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                            </a>
                            {!! Form::close() !!}
                            @endcan
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach

            </tbody>
        </table>
@if ($productServices->hasPages())
<div class="pagination">
    <ul>
        @if ($productServices->onFirstPage())
            <li class="disabled">&laquo; Previous</li>
        @else
            <li><a href="{{ $productServices->appends(request()->query())->previousPageUrl() }}"
                    rel="prev">&laquo; Previous</a></li>
        @endif
        @if ($productServices->currentPage() > 1)
            <li><a href="{{ $productServices->appends(request()->query())->url(1) }}">First</a></li>
        @endif
        @php
            $currentPage = $productServices->currentPage();
            $lastPage = $productServices->lastPage();
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
            <li class="{{ $page == $productServices->currentPage() ? 'active' : '' }}">
                <a href="{{ $productServices->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
        @endfor
        @if ($productServices->hasMorePages())
            <li><a href="{{ $productServices->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                    &raquo;</a></li>
        @else
            <li class="disabled">Next &raquo;</li>
        @endif
        @if ($productServices->currentPage() < $productServices->lastPage())
            <li><a
                    href="{{ $productServices->appends(request()->query())->url($productServices->lastPage()) }}">Last</a>
            </li>
        @endif
    </ul>
</div>
@endif
    </div>
</div>

@endsection