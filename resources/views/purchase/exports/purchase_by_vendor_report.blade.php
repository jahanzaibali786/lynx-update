@include('student.exports.header')
@php
    // Group purchases by vendor once
    $purchasesByVendor = [];
    foreach ($data['purchases'] as $purchase) {
        $vendorId = $purchase->vender_id;
        $vendorName = optional($purchase->vender)->name ?? 'Unknown Vendor';

        if (!isset($purchasesByVendor[$vendorId])) {
            $purchasesByVendor[$vendorId] = [
                'name' => $vendorName,
                'purchases' => [],
            ];
        }
        $purchasesByVendor[$vendorId]['purchases'][] = $purchase;
    }
@endphp

<table>
    <thead>
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th>{{ __('Purchase Date') }}</th>
            <th>{{ __('Purchase No.') }}</th>
            <th>{{ __('Memo') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Cost Price') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($purchasesByVendor as $vendorData)
            {{-- Vendor header row --}}
            <tr>
                <td colspan="2"><strong>{{ $vendorData['name'] }}</strong></td>
                <td colspan="6"></td>
            </tr>

            @php
                // reset totals per vendor
                $balance = 0.0;
                $totalCostAmount = 0.0;
                $totalAmount = 0.0;
            @endphp

            @foreach ($vendorData['purchases'] as $purchase)
                @foreach ($purchase->items ?? [] as $item)
                    @php
                        $qty = (float) data_get($item, 'products.quantity', 0);
                        $price = (float) data_get($item, 'products.purchase_price', 0);
                        $line = $qty * $price;

                        $balance += $line;
                        $totalCostAmount += $price;
                        $totalAmount += $line;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                        <td class="Id">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</td>
                        <td>{{ data_get($item, 'products.name', '') }}</td>
                        <td>{{ Auth::user()->priceFormat($qty) }}</td>
                        <td>{{ Auth::user()->priceFormat($price) }}</td>
                        <td>{{ Auth::user()->priceFormat($line) }}</td>
                        <td>{{ Auth::user()->priceFormat($balance) }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- spacer row between vendors (optional) --}}
            <tr>
                <td colspan="8" style="height: 50px; border: none;"></td>
            </tr>

            {{-- Vendor subtotal --}}
            <tr>
                <td colspan="5" style="border:1px double black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ __('Total') }}:</strong></td>
                <td style="border:1px double black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ Auth::user()->priceFormat($totalCostAmount) }}</strong></td>
                <td style="border:1px double black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ Auth::user()->priceFormat($totalAmount) }}</strong></td>
                <td style="border:1px double black; border-top: 1px double black; border-bottom: 1px double black; background-color: gray;"><strong>{{ Auth::user()->priceFormat($balance) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
