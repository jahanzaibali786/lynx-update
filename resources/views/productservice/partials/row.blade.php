<tr class="font-style" data-product-id="{{ $productService->id }}">
    <td class="product-row-number">{{ $index ?? '' }}</td>
    <td>{{ $productService->name }}</td>
    <td>{{ $productService->sku }}</td>
    @can('show sale price product & service')
        <td>{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
    @endcan
    @can('show purchase price product & service')
        <td>{{ \Auth::user()->priceFormat($productService->purchase_price) }}</td>
    @endcan
    <td>{{ !empty($productService->category) ? $productService->category->name : '' }}</td>
    <td>{{ !empty($productService->subcategory) ? $productService->subcategory->name : '' }}</td>
    <td>{{ !empty($productService->unit()) ? $productService->unit()->name : '' }}</td>
    @can('show quantity product & service')
        @if ($productService->type == 'product')
            <td>{{ $productService->quantity ?? 0 }}</td>
            <td>{{ $productService->used_quantity ?? 0 }}</td>
            <td>{{ $productService->damaged_quantity ?? 0 }}</td>
            <td>{{ ($productService->quantity ?? 0) + ($productService->used_quantity ?? 0) + ($productService->damaged_quantity ?? 0) }}</td>
        @else
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        @endif
    @endcan

    @if (Gate::check('show product & service') || Gate::check('edit product & service') || Gate::check('delete product & service'))
        <td class="Action">
            <div class="action-btn ms-2">
                @can('show product & service')
                    <a href="{{ route('productservice.show', $productService->id) }}" class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center"
                        data-bs-title="{{ __('View') }}">
                        <span class="btn-inner--icon"><i class="fas fa-eye"></i></span>
                    </a>
                @endcan

                @can('edit product & service')
                    <a href="#" data-url="{{ route('productservice.edit', $productService->id) }}" data-size="modal-fullscreen"
                        data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center"
                        data-bs-title="{{ __('Edit') }}">
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
