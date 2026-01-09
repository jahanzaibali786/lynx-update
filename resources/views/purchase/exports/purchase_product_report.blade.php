@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th>{{ __('Purchase Number') }}</th>
            <th>{{ __('Vendor') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Product Code') }}</th>
            <th>{{ __('Product Name') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Purchase Price') }}</th>
            <th>{{ __('Tax') }}</th>
            <th>{{ __('Discount') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $overallPurchasePriceTotal = 0;
            $overalltax = 0;
            $overalldiscount = 0;
        @endphp

        @foreach ($data['purchases'] as $purchase)
            @php
                $purchasePurchasePriceTotal = 0;
                $purchasetax = 0;
                $purchasediscount = 0;
            @endphp
            <tr>
                <td colspan="2">
                    <strong>{{ Auth::user()->purchaseNumberFormat($purchase->id) }}</strong>
                </td>
                <td colspan="8">
                
                </td>
            </tr>
            @foreach ($purchase->items as $item)
                @php
                    $productName = @$item->products->name;
                    $productCode = @$item->products->sku;
                    $productCategory = @$item->category->name;
                    $itemQuantity = is_numeric(@$item->quantity) ? $item->quantity : 0;
                    $purchasePrice = is_numeric(@$item->price) ? $item->price : 0;
                    $itemTax = is_numeric(@$item->tax) ? $item->tax : 0;
                    $itemdiscount = is_numeric(@$item->discount) ? $item->discount : 0;

                    $itemPurchasePriceTotal = $itemQuantity * $purchasePrice;

                    $purchasePurchasePriceTotal = isset($purchasePurchasePriceTotal)
                        ? $purchasePurchasePriceTotal + $itemPurchasePriceTotal
                        : $itemPurchasePriceTotal;
                    $purchasetax = isset($purchasetax) ? $purchasetax + $itemTax : $itemTax;
                    $purchasediscount = isset($purchasediscount)
                        ? $purchasediscount + $itemdiscount
                        : $itemdiscount;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ Auth::user()->purchaseNumberFormat($purchase->id) }}</td>
                    <td>{{ $purchase->vender->name }}</td>
                    <td>{{ $productCategory }}</td>
                    <td>{{ $productCode }}</td>
                    <td>{{ $productName }}</td>
                    <td>{{ $itemQuantity }}</td>
                    <td>{{ $purchasePrice }}</td>
                    <td>{{ $itemTax }}</td>
                    <td>{{ $itemdiscount }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="7" style="border: 1px solid black; background-color: gray;"><strong>Purchase Totals:</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ $purchasePurchasePriceTotal }}</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ $purchasetax }}</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ $purchasediscount }}</strong></td>
            </tr>

            @php
                $overallPurchasePriceTotal += $purchasePurchasePriceTotal;
                $overalltax += $purchasetax;
                $overalldiscount += $purchasediscount;
            @endphp
        @endforeach
        <tr>
            <td colspan="10" style="border: none; height: 50px;"></td>
        </tr>
        <tr>
            <td colspan="7" style="border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>Sub-Totals:</strong></td>
            <td style="border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ $overallPurchasePriceTotal }}</strong></td>
            <td style="border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ $overalltax }}</strong></td>
            <td style="border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ $overalldiscount }}</strong></td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')