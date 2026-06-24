<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h3 style="text-align: center;">INVENTORY STOCK IN HAND REPORT</h3>
    <div>
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                     <th style="border: 1px solid #000;">{{ __('Sr.') }}</th>
                     <th style="border: 1px solid #000;">{{ __('Product Name') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Product code') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Purchase Price') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Sale Price') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Tax') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Category') }}</th>
                    <th style="border: 1px solid #000;">{{ __('SubCategory') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Unit') }}</th>
                    <th style="border: 1px solid #000;">{{ __('New Qty') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Used Qty') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Damaged Qty') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Total Qty') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($productServices as $productService)
                <tr class="font-style">
                    <td style="border: 1px solid #000;">{{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000;">{{ $productService->name }}</td>
                    <td style="border: 1px solid #000;">{{ $productService->sku }}</td>
                    <td style="border: 1px solid #000;">{{ \Auth::user()->priceFormat($productService->purchase_price) }}</td>
                    <td style="border: 1px solid #000;">{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
                    <td style="border: 1px solid #000;">
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
                    <td style="border: 1px solid #000;">{{ !empty($productService->category) ? $productService->category->name : '' }}</td>
                    <td style="border: 1px solid #000;">{{ !empty($productService->subcategory) ? $productService->subcategory->name : '' }}</td>
                    <td style="border: 1px solid #000;">{{ !empty($productService->unit()) ? $productService->unit()->name : '' }}</td>
                    @if ($productService->type == 'product')
                        <td style="border: 1px solid #000;">{{ $productService->quantity ?? 0 }}</td>
                        <td style="border: 1px solid #000;">{{ $productService->used_quantity ?? 0 }}</td>
                        <td style="border: 1px solid #000;">{{ $productService->damaged_quantity ?? 0 }}</td>
                        <td style="border: 1px solid #000;">{{ ($productService->quantity ?? 0) + ($productService->used_quantity ?? 0) + ($productService->damaged_quantity ?? 0) }}</td>
                    @else
                        <td style="border: 1px solid #000;">-</td>
                        <td style="border: 1px solid #000;">-</td>
                        <td style="border: 1px solid #000;">-</td>
                        <td style="border: 1px solid #000;">-</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>



    </div>

</body>

</html>
