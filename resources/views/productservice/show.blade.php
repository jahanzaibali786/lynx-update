@extends('layouts.admin')

@section('page-title')
    {{ __('Product Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('productservice.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item">{{ __('Show') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('edit product & service')
            <a href="{{ route('productservice.edit', $productService->id) }}" class="btn btn-sm btn-outline-primary">
                {{ __('Edit') }}
            </a>
        @endcan
        <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-outline-secondary">
            {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $itemType = $productService->item_type ?: ($productService->type === 'service' ? 'service' : 'inventory_part');
        $taxes = !empty($productService->tax_id) ? \App\Models\Utility::tax($productService->tax_id) : collect();
        $imageUrl = $productService->pro_image
            ? asset(Storage::url('uploads/pro_image/' . $productService->pro_image))
            : asset(Storage::url('uploads/pro_image/user-2_1654779769.jpg'));
        $accountName = function ($account) {
            return $account ? trim(($account->code ? $account->code . ' - ' : '') . $account->name) : '-';
        };
    @endphp

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Item Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Type') }}</small>
                            <span>{{ $itemTypes[$itemType] ?? ucfirst(str_replace('_', ' ', $itemType)) }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Item Name / Number') }}</small>
                            <span>{{ $productService->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Product Code') }}</small>
                            <span>{{ $productService->sku ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Manufacturer Part Number') }}</small>
                            <span>{{ $productService->manufacturer_part_number ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Subitem') }}</small>
                            <span>{{ $productService->is_subitem ? __('Yes') : __('No') }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Parent Item') }}</small>
                            <span>{{ optional($productService->parentItem)->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Unit of Measure') }}</small>
                            <span>{{ optional($productService->unit())->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Category') }}</small>
                            <span>{{ optional($productService->category)->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('SubCategory') }}</small>
                            <span>{{ optional($productService->subcategory)->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">{{ __('Tax') }}</small>
                            @forelse($taxes as $tax)
                                <span class="badge bg-primary p-2 me-1">{{ $tax->name ?? '-' }}{{ isset($tax->rate) ? ' (' . $tax->rate . '%)' : '' }}</span>
                            @empty
                                <span>-</span>
                            @endforelse
                        </div>
                        @if(!empty($customFields) && !$customFields->isEmpty())
                            @foreach($customFields as $field)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ $field->name }}</small>
                                    <span>{{ $productService->customField[$field->id] ?? '-' }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Product Image') }}</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $imageUrl }}" alt="{{ $productService->name }}" class="img-fluid rounded" style="max-height: 260px; object-fit: contain;">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Purchase Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">{{ __('Description on Purchase Transactions') }}</small>
                        <span>{{ $productService->purchase_description ?: '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">{{ __('Cost') }}</small>
                        <span>{{ \Auth::user()->priceFormat($productService->purchase_price) }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('COGS Account') }}</small>
                        <span>{{ $accountName($productService->expenseAccount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Sales Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">{{ __('Description on Sales Transactions') }}</small>
                        <span>{{ $productService->sales_description ?: '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">{{ __('Sales Price') }}</small>
                        <span>{{ \Auth::user()->priceFormat($productService->sale_price) }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('Income Account') }}</small>
                        <span>{{ $accountName($productService->saleAccount) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Inventory Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">{{ __('Inventory Asset Account') }}</small>
                        <span>{{ $accountName($productService->inventoryAssetAccount) }}</span>
                    </div>
                    @if($productService->type == 'product')
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('New') }}</small>
                                <span>{{ $productService->quantity }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Used') }}</small>
                                <span>{{ $productService->used_quantity }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Damaged') }}</small>
                                <span>{{ $productService->damaged_quantity }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">{{ __('Total') }}</small>
                                <span>{{ $productService->quantity + $productService->used_quantity + $productService->damaged_quantity }}</span>
                            </div>
                        </div>
                    @else
                        <div>
                            <small class="text-muted d-block">{{ __('Quantity') }}</small>
                            <span>-</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Warehouse Details') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Warehouse') }}</th>
                                    <th>{{ __('New') }}</th>
                                    <th>{{ __('Used') }}</th>
                                    <th>{{ __('Damaged') }}</th>
                                    <th>{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouseProducts as $warehouseProduct)
                                    <tr>
                                        <td>{{ optional($warehouseProduct->warehousedetail)->name ?? '-' }}</td>
                                        <td>{{ $warehouseProduct->quantity }}</td>
                                        <td>{{ $warehouseProduct->used_quantity }}</td>
                                        <td>{{ $warehouseProduct->damaged_quantity }}</td>
                                        <td>{{ $warehouseProduct->quantity + $warehouseProduct->used_quantity + $warehouseProduct->damaged_quantity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('Product not select in warehouse') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
