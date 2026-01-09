<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h3 style="text-align: center;">INVENTORY STOCK</h3>
    <div>
        <table style="border: 1px solid #000; border-collapse: collapse; width: 100% !important;">
            <thead>
                <tr style="border: 1px solid #000; background-color:gray; font-size:0.9rem;">
                    <th style="border: 1px solid #000;">{{ __('Sr.') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Product Name') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Product code') }}</th>
                    <th style="border: 1px solid #000;">{{ __('Quantity') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productServices as $productService)
                    <tr class="font-style">
                        <td style="border: 1px solid #000;">{{ $loop->iteration }}</td>
                        <td style="border: 1px solid #000;">{{ $productService->name }}</td>
                        <td style="border: 1px solid #000;">{{ $productService->sku }}</td>
                        <td style="border: 1px solid #000;">{{ $productService->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
