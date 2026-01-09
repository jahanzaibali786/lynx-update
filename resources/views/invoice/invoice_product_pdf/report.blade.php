<div class="content" id="report-content">
    <div class="card p-4" style="margin-top: 20px;"> <!-- Adjust the value as needed -->
        <style>
            table, tr, th, td {
                border: 1px solid black;
                font-size:0.7rem;
                border-collapse: collapse;
                width: 100%; /* Ensuring full width for the table */
            }
        </style>

        @php
            $overallSaleTotal = 0;
            $overallPurchaseTotal = 0;
            $overallProfitTotal = 0;
        @endphp      

        <!-- Loop through Invoices -->
        @foreach ($invoices as $invoice)
            @php
                $invoiceSaleTotal = 0;
                $invoicePurchaseTotal = 0;
                $invoiceProfitTotal = 0;
                $totalTaxItem = 0;
            @endphp
            
            <table class="">
                <thead>
                    <tr style="background-color: grey; font-size: 0.9rem;">
                        <th>{{ __('Invoice') }}</th>
                        <th style="width:200px;">{{ __('Product No') }}</th>
                        <th style="width:200px;">{{ __('Product Name') }}</th>
                        <th style="width:150px;">{{ __('Category') }}</th>
                        <th style="width:50px;">{{ __('Quantity') }}</th>
                        <th>{{ __('Purchase Price') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th style="width:100px;">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7">
                            <strong>{{ Auth::user()->invoiceNumberFormat(@$invoice[0]['invoice_id']) }}</strong>
                        </td>
                        <td></td>
                    </tr>

                    @foreach($invoice[0]['items'] as $item)
                    @php
                    $quantity = is_numeric(@$item->quantity) ? $item->quantity : 0;
                    $salePrice = is_numeric(@$item->products->sale_price) ? $item->products->sale_price : 0;
                    $purchasePrice = is_numeric(@$item->products->purchase_price)
                        ? $item->products->purchase_price
                        : 0;
                    $tax = is_numeric(@$item->tax) ? $item->tax : 0;

                    $itemSalePriceTotal = $quantity * $salePrice;
                    $itemPurchasePriceTotal = $quantity * $purchasePrice;
                    $itemProfitTotal = $itemSalePriceTotal - $itemPurchasePriceTotal - $tax;

                    $totalTaxItem += $tax;
                    $invoiceSaleTotal += $itemSalePriceTotal;
                    $invoicePurchaseTotal += $itemPurchasePriceTotal;
                    $invoiceProfitTotal += $itemProfitTotal;
                @endphp
                        <tr>
                            <td></td>
                            <td style="width:200px;">{{ @$item->products->sku }}</td>
                            <td style="width:200px;">{{ @$item->products->name }}</td>
                            <td style="width:150px;">{{ @$item->products->category->name }}</td>
                            <td style="width:50px;">{{ @$item->quantity }}</td>
                            <td>{{ @$item->products->purchase_price }}</td>
                            <td>{{ !empty($item->tax) ? $item->tax : '-' }}</td>
                            <td style="width:100px;">{{ $itemPurchasePriceTotal - $totalTaxItem }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" class="text-right"><strong>Invoice Totals:</strong></td>
                        <td><strong>{{ $invoicePurchaseTotal }}</strong></td>
                        <td></td>
                        <td><strong>{{ $invoiceSaleTotal }}</strong></td>
                        <td></td>
                    </tr>

                    <!-- Add subtotal row here -->
                    <tr>
                        <td colspan="4" class="text-right"><strong>Sub-Totals:</strong></td>
                        <td><strong>{{ $invoicePurchaseTotal }}</strong></td>
                        <td></td>
                        <td><strong>{{ $invoiceSaleTotal }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            @php
                $overallSaleTotal += $invoiceSaleTotal;
                $overallPurchaseTotal += $invoicePurchaseTotal;
                $overallProfitTotal += $invoiceProfitTotal;
            @endphp
        @endforeach
        
        {{-- <table class="">
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Overall Sub-Totals:</strong></td>
                    <td><strong>{{ $overallPurchaseTotal }}</strong></td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ $overallSaleTotal }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right"><strong>Overall Profit:</strong></td>
                    <td colspan="4"><strong>{{ $overallProfitTotal }}</strong></td>
                </tr>
            </tfoot>
        </table> --}}
    </div>
</div>
