<div class="content" id="report-content">
    <div class="card p-4" style="margin-top: 20px;">
        <style>
            table,
            tr,
            th,
            td {
                border: 1px solid black;
                border-collapse: collapse;
                width: 100%;
            }
            /* Optional: Ensure that the last row has a distinct bottom border */
            tr:last-child td {
                border-bottom: 2px solid black; /* Thicker border for better visibility */
            }
        </style>

        @php
            $overallPurchasePriceTotal = 0;
            $overalltax = 0;
            $overalldiscount = 0;
        @endphp

        <!-- Loop through Purchases -->
        @foreach ($purchases as $purchase)
            @php
                $purchasePurchasePriceTotal = 0;
                $purchasetax = 0;
                $purchasediscount = 0;
            @endphp

            <table class="">
                <thead>
                    <tr style="background-color: grey; font-size: 0.9rem;">
                        <th>{{ __('Purchase Number') }}</th>
                        <th style="width:150px;">{{ __('Vendor') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th style="width:150px;">{{ __('Product Name') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Purchase Price') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Discount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8">
                            <strong>{{ Auth::user()->purchaseNumberFormat($purchase->id) }}</strong>
                        </td>
                    </tr>

                    @foreach ($purchase->items as $item)
                        @php
                            $productName = @$item->products->name;
                            $productCategory = @$item->category->name;
                            $itemQuantity = @$item->quantity;
                            $purchasePrice = @$item->price;
                            $itemTax = @$item->tax ?? 0;
                            $itemdiscount = @$item->discount ?? 0;

                            $itemPurchasePriceTotal = $itemQuantity * $purchasePrice;
                            $purchasePurchasePriceTotal += $itemPurchasePriceTotal;
                            $purchasetax += @$item->tax;
                            $purchasediscount += @$item->discount;
                        @endphp
                        <tr>
                            <td></td>
                            <td style="width:150px;">{{ $purchase->vender->name }}</td>
                            <td>{{ $productCategory }}</td>
                            <td style="width:150px;">{{ $productName }}</td>
                            <td>{{ $itemQuantity }}</td>
                            <td>{{ number_format($purchasePrice, 2) }}</td>
                            <td>{{ number_format($itemTax, 2) }}</td>
                            <td>{{ number_format($itemdiscount, 2) }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" class="text-right"><strong>Purchase Totals:</strong></td>
                        <td><strong>{{ number_format($purchasePurchasePriceTotal, 2) }}</strong></td>
                        <td><strong>{{ number_format($purchasetax, 2) }}</strong></td>
                        <td><strong>{{ number_format($purchasediscount, 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            @php
                $overallPurchasePriceTotal += $purchasePurchasePriceTotal;
                $overalltax += $purchasetax;
                $overalldiscount += $purchasediscount;
            @endphp
        @endforeach

        {{-- <table class="datatable">
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Overall Sub-Totals:</strong></td>
                    <td><strong>{{ number_format($overallPurchasePriceTotal, 2) }}</strong></td>
                    <td><strong>{{ number_format($overalltax, 2) }}</strong></td>
                    <td><strong>{{ number_format($overalldiscount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table> --}}
    </div>
</div>
