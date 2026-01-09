@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr.') }}</th>
            <th>{{ __('Product Name') }}</th>
            <th>{{ __('Product Code') }}</th>
            <th>{{ __('Sale Price') }}</th>
            <th>{{ __('Purchase Price') }}</th>
            <th>{{ __('Tax') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('SubCategory') }}</th>
            <th>{{ __('Unit') }}</th>
            <th>{{ __('Quantity') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productServices as $productService)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $productService->name }}</td>
                <td>{{ $productService->sku }}</td>
                <td>{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
                <td>{{ \Auth::user()->priceFormat($productService->purchase_price) }}</td>
                <td>
                    @php
                        $taxes = !empty($productService->tax_id) ? \App\Models\Utility::tax($productService->tax_id) : [];
                    @endphp
                    @forelse ($taxes as $tax)
                        @if ($tax)
                            <span>{{ $tax->name }} ({{ $tax->rate }}%)</span><br>
                        @endif
                    @empty
                        -
                    @endforelse
                </td>
                <td>{{ optional($productService->category)->name }}</td>
                <td>{{ optional($productService->subcategory)->name }}</td>
                <td>{{ optional($productService->unit())->name }}</td>
                <td>
                    @if ($productService->type === 'product')
                        {{ $productService->quantity }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
