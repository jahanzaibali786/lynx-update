<!-- resources/views/employee/reports/empLeavesRptPrint.blade.php -->
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.6rem;
    }

    th,
    td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }

    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #ddd;
    }

    .no-border {
        border: none !important;
        background-color: transparent;
    }
</style>

<h2 style="text-align: center;">Employee Leaves Report</h2>
<p style="text-align: center;">Period: {{ date('d M Y', strtotime($request->input('start_date'))) }} to {{ date('d M Y', strtotime($request->input('end_date'))) }}</p>

@php
    // Organize purchases by vendor
    $purchasesByVendor = [];
    foreach ($purchases as $purchase) {
        $vendorId = $purchase->vender_id;
        $vendorName = !empty($purchase->vender) ? $purchase->vender->name : 'Unknown Vendor';

        if (!isset($purchasesByVendor[$vendorId])) {
            $purchasesByVendor[$vendorId] = [
                'name' => $vendorName,
                'purchases' => [],
            ];
        }

        $purchasesByVendor[$vendorId]['purchases'][] = $purchase;
    }
@endphp
@php
    $balance = 0;
    $totalcostAmount = 0;
    $totalAmount = 0;
@endphp
@foreach ($purchasesByVendor as $vendorId => $vendorData)
    <div class="vendor-section mt-4">
      
        <table class="">
            <thead class="table_heads" style="background: #878585;" >
                <tr style="background: #878585;">
                    <th style="background: #878585;"> {{ __('Purchase Date') }}</th>
                    <th style="background: #878585;"> {{ __('Purchase No.') }}</th>
                    <th style="background: #878585;"> {{ __('Memo') }}</th>
                    <th style="background: #878585;"> {{ __('Quantity') }}</th>
                    <th style="background: #878585;"> {{ __('Cost Price') }}</th>
                    <th style="background: #878585;"> {{ __('Amount') }}</th>
                    <th style="background: #878585;">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #a09e9e; text-align: left; padding: 5px 10px;">
                    <td colspan="7" style="text-align: left;"><b>{{ $vendorData['name'] }}</b></td>
                </tr>

                @foreach ($vendorData['purchases'] as $purchase)
                    @foreach (@$purchase->items as $item)
                        <tr>
                            <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                            <td class="Id">
                                {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}
                            </td>
                            <td>{{ !empty($item) ? $item->products->name : '' }}</td>
                            <td>{{ \Auth::user()->priceFormat(@$item->products->quantity) }}</td>
                            <td>{{ \Auth::user()->priceFormat(@$item->products->purchase_price) }}</td>
                            <td>{{ \Auth::user()->priceFormat(@$item->products->purchase_price * @$item->products->quantity) }}
                            </td>
                            @php
                                $balance += @$item->products->purchase_price * @$item->products->quantity;
                                $totalcostAmount += @$item->products->purchase_price;
                                $totalAmount += @$item->products->purchase_price * @$item->products->quantity;
                            @endphp
                            <td>
                                {{ \Auth::user()->priceFormat($balance) }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>{{ __('Total') }}:</strong></td>
                    <td>
                        <strong>
                            {{ \Auth::user()->priceFormat($totalcostAmount) }}
                        </strong>
                    </td>
                    <td>
                        <strong>
                            {{ \Auth::user()->priceFormat($totalAmount) }}
                        </strong>
                    </td>
                    <td>
                        <strong>
                            {{ \Auth::user()->priceFormat($balance) }}
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endforeach
