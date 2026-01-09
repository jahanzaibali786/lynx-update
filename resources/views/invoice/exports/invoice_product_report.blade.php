@include('student.exports.header')

<table class="">
    <thead>
        <tr>
            <th>Sr</th>
            <th>{{ __('Invoice') }}</th>
            <th>{{ __('Product No') }}</th>
            <th>{{ __('Product Name') }}</th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Purchase Price') }}</th>
            <th>{{ __('Tax') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </thead>

    <tbody>
        @php
            $overallPurchaseTotal = 0;
            $overallTaxTotal = 0;
            $overallNetTotal = 0; // purchase - tax
        @endphp

        @foreach ($data['invoices'] as $invoice)
            @php
                $invoicePurchaseTotal = 0;
                $invoiceTaxTotal = 0;
                $invoiceNetTotal = 0;
            @endphp

            <tr>
                <td colspan="9">
                    <strong>{{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}</strong>
                </td>
            </tr>

            @foreach ($invoice->items as $item)
                @php
                    $quantity       = (float) ($item->quantity ?? 0);
                    $purchasePrice  = (float) data_get($item, 'products.purchase_price', 0);
                    $tax            = (float) ($item->tax ?? 0);

                    $rowPurchase    = $quantity * $purchasePrice;
                    $rowNet         = $rowPurchase - $tax;

                    $invoicePurchaseTotal += $rowPurchase;
                    $invoiceTaxTotal      += $tax;
                    $invoiceNetTotal      += $rowNet;
                @endphp

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td></td>
                    <td>{{ data_get($item, 'products.sku', '-') }}</td>
                    <td>{{ data_get($item, 'products.name', '-') }}</td>
                    <td>{{ data_get($item, 'products.category.name', '-') }}</td>
                    <td>{{ $quantity }}</td>
                    <td>{{ number_format($purchasePrice, 2) }}</td>
                    <td>{{ number_format($tax, 2) }}</td>
                    <td>{{ number_format($rowNet, 2) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="6" style="border: 1px solid black; background-color: gray;"><strong>Invoice Totals:</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ number_format($invoicePurchaseTotal, 2) }}</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ number_format($invoiceTaxTotal, 2) }}</strong></td>
                <td style="border: 1px solid black; background-color: gray;"><strong>{{ number_format($invoiceNetTotal, 2) }}</strong></td>
            </tr>

            @php
                $overallPurchaseTotal += $invoicePurchaseTotal;
                $overallTaxTotal      += $invoiceTaxTotal;
                $overallNetTotal      += $invoiceNetTotal;
            @endphp
        @endforeach
        <tr>
            <td colspan="9" style="border: none; height: 50px;"></td>
        </tr>
        <tr>
            <td colspan="6" style="border: 1px double black; background-color: gray;"><strong>Sub-Totals:</strong></td>
            <td style="border: 1px double black; background-color: gray;"><strong>{{ number_format($overallPurchaseTotal, 2) }}</strong></td>
            <td style="border: 1px double black; background-color: gray;"><strong>{{ number_format($overallTaxTotal, 2) }}</strong></td>
            <td style="border: 1px double black; background-color: gray;"><strong>{{ number_format($overallNetTotal, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')
