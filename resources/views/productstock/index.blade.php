@extends('layouts.admin')
@section('page-title')
{{__('Manage Product Stock')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#category-select').on('change', function () {
                var categoryId = $(this).val();
                if (categoryId) {
                    $.ajax({
                        url: '{{ url('/get-subcategories') }}/' + categoryId,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            var subcategorySelect = $('#subcategory-select');
                            subcategorySelect.empty();
                            subcategorySelect.append('<option value="">Select Subcategory</option>');
                            $.each(data.subcategories, function (index, subcategory) {
                                subcategorySelect.append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                            });
                        },
                        error: function (xhr, status, error) {
                            console.error('Error fetching subcategories:', error);
                        }
                    });
                } else {
                    $('#subcategory-select').empty().append('<option value="">Select Subcategory</option>');
                }
            });
        });
        function printproductslist() {
            var category = $('#category-select').val();
            var subcategory = $('#subcategory-select').val();
            var url = "{{ route('productstock.index') }}?category=" + category + "&subcategory=" + subcategory + "&print=true";
            window.open(url, '_blank');
        }
    </script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Product Stock')}}</li>
@endsection
@section('action-btn')
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 {{ isset($_GET['category']) ? 'show' : '' }}" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['productstock.index'], 'method' => 'GET', 'id' => 'product_service']) }}
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
                                {{ Form::select('subcategory', $subcategory, request()->subcategory, ['class' => 'js-searchBox form-control select', 'id' => 'subcategory-select', 'required' => 'required']) }}
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
        <div class="table-responsive">
            <table class="datatable">
                <thead class="table_heads">
                    <tr>
                        <th>{{ __('Sr.') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Product Code') }}</th>
                        <th>{{ __('New') }}</th>
                        <th>{{ __('Used') }}</th>
                        <th>{{ __('Damaged') }}</th>
                        <th>{{ __('Total Quantity') }}</th>
                        {{-- <th>{{ __('Action') }}</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productServices as $productService)
                        <tr class="font-style">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $productService->name }}</td>
                            <td>{{ $productService->sku }}</td>
                            <td>{{ $productService->quantity }}</td>
                            <td>{{ $productService->used_quantity }}</td>
                            <td>{{ $productService->damaged_quantity }}</td>
                            <td>{{ $productService->quantity + $productService->used_quantity + $productService->damaged_quantity }}</td>

                            {{-- <td class="Action">
                                <div class="action-btn bg-info ms-2">
                                    <a data-size="md" href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                        data-url="{{ route('productstock.edit', $productService->id) }}"
                                        data-ajax-popup="true" data-size="xl" 
                                        data-bs-title="{{__('Update Quantity')}}">
                                        <span class="btn-inner--icon"> Create</span>
                                    </a>
                                </div>


                            </td> --}}

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
</div>



@endsection