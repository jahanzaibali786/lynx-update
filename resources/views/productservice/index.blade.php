@extends('layouts.admin')
@section('page-title')
{{ __('Manage Products') }}
@endsection
@push('script-page')
<script>
    $(document).ready(function() {
        function refreshSubcategorySelect() {
            var subcategorySelect = $('#subcategory-select');
            var parent = subcategorySelect.closest('.btn-box');

            if (subcategorySelect[0].customSelectInstance) {
                subcategorySelect[0].customSelectInstance.destroy();
                subcategorySelect[0].customSelectInstance = null;
            }
            parent.find('.custom-select-wrapper').remove();
            subcategorySelect.removeClass('custom-select').show();

            setTimeout(function() {
                subcategorySelect.addClass('custom-select').show();
                if (window.CustomSelect) {
                    window.CustomSelect.initContainer(parent[0]);
                }
            }, 0);
        }

        function refreshCategorySelect() {
            var categorySelect = $('#category-select');
            if (categorySelect[0].customSelectInstance) {
                categorySelect[0].customSelectInstance.updateOptions();
            }
        }

        function setSubcategoryOptions(subcategories) {
            var subcategorySelect = $('#subcategory-select');
            subcategorySelect.empty();
            subcategorySelect.append('<option value="">Select Subcategory</option>');
            $.each(subcategories || [], function(index, subcategory) {
                subcategorySelect.append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
            });
            subcategorySelect.val('');
            refreshSubcategorySelect();
        }

        $('#category-select').on('change', function() {
            refreshCategorySelect();
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '{{ url('/get-subcategories') }}/' + categoryId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        setSubcategoryOptions(data.subcategories);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching subcategories:', error);
                    }
                });
            } else {
                setSubcategoryOptions([]);
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
@push('css-page')
    <style>
        #commonModal .modal-xl {
            max-width: calc(100vw - 24px);
            margin: 12px auto;
        }

        #commonModal .modal-xl .modal-content {
            min-height: calc(100vh - 24px);
        }

        #commonModal .product-service-create-modal {
            max-height: calc(100vh - 92px);
            overflow-y: auto;
            padding: 16px 18px;
        }
    </style>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Product Information ') }}</li>
@endsection
@section('action-btn')
@can('create product & service')
<div class="float-end">
    <a href="#" data-url="{{ route('productservice.create') }}" data-size="modal-fullscreen"
        data-ajax-popup="true" data-bs-title="{{ __('Create Product') }}"
        class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Create</span>
    </a>
</div>
@endcan
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
                                {{ Form::select('category', $category, request()->category, ['class' => 'form-control select custom-select', 'id' => 'category-select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('subcategory', __('Subcategory'), ['class' => 'form-label']) }}
                                {{ Form::select('subcategory',$subcategory, request()->subcategory, ['class' => 'form-control select custom-select', 'id' => 'subcategory-select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('item_type', __('Type'), ['class' => 'form-label']) }}
                                {{ Form::select('item_type', $itemTypes, request()->item_type, ['class' => 'form-control select custom-select', 'id' => 'item_type-select']) }}
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
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable" style="width: 99.5% !important;">
                        <thead class="table_heads">
                            <tr>
                                <th>{{ __('Sr.') }}</th>
                                <th>{{ __('Product Name') }}</th>
                                <th>{{ __('Product code') }}</th>
                                @can('show sale price product & service')
                                    <th>{{ __('Sale Price') }}</th>
                                @endcan
                                @can('show purchase price product & service')
                                    <th>{{ __('Purchase Price') }}</th>
                                @endcan
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('SubCategory') }}</th>
                                <th>{{ __('Unit') }}</th>
                                @can('show quantity product & service')
                                    <th>{{ __('New Qty') }}</th>
                                    <th>{{ __('Used Qty') }}</th>
                                    <th>{{ __('Damaged Qty') }}</th>
                                    <th>{{ __('Total Qty') }}</th>
                                @endcan
                                  @if (Gate::check('show product & service') || Gate::check('edit product & service') || Gate::check('delete product & service'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="product-service-table-body">
                            @foreach ($productServices as $productService)
                                @include('productservice.partials.row', ['productService' => $productService, 'index' => $loop->iteration])
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
