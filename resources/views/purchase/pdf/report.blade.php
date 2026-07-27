{{-- <div class="content" id="report-content">
    <div class="card p-4 mt-3">
        <table class="">
            <thead class="table_heads">
                <tr>
                    <th> {{ __('Purchase') }}</th>
                    <th> {{ __('Vendor') }}</th>
                    <th> {{ __('Category') }}</th>
                    <th> {{ __('Purchase Date') }}</th>
                    <th> {{ __('Total Amount') }}</th>
                    <th> {{ __('Due Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td class="Id">
                            {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}
                        </td>
                        <td> {{ !empty($purchase->vender) ? $purchase->vender->display_name : '' }} </td>
                        <td>{{ !empty($purchase->category) ? $purchase->category->name : '' }}</td>
                        <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                        <td>{{ \Auth::user()->priceFormat($purchase->getTotal()) }}</td>
                        <td>{{ \Auth::user()->priceFormat($purchase->getDue()) }}</td>
                        <td>
                            {{ __(\App\Models\Purchase::$statues[$purchase->status]) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> --}}

<div class="content" id="report-content">
    <div class="card p-4">
        

        <!-- Table CSS Styling -->
        <style>
            table, tr, th, td {
                border: 1px solid black;
                border-collapse: collapse;
                width: 100%;
            }
        </style>
        {{-- @foreach($purchases as $vendorId => $vendorPurchases) --}}
        <div style="width: 100%; margin-top: 20px;">
            <p style="text-align: center; font-weight: 900; font-size: 1rem; margin: 0;">Purchaes Memo Report</p>
            <br>
            <!-- Display Vendor Name -->
            {{-- <span style="font-size: 1rem; font-weight: 600; padding: 10px; width: 100%;">
                {{ $vendors[$vendorId] ?? 'Unknown Vendor' }}
            </span> --}}
            <table style="width: 100%; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: grey; font-size: 0.9rem;">
                        <th style="width: 20%;">{{ __('Purchase No.') }}</th>
                        <th style="width: 15%;">{{ __('Vendor') }}</th>
                        <th style="width: 20%;">{{ __('Category') }}</th>
                        <th style="width: 15%;">{{ __('Purchase Date') }}</th>
                        <th style="width: 15%;">{{ __('Total Amount') }}</th>
                        <th style="width: 15%;">{{ __('Due Amount') }}</th>
                        <th style="width: 10%;">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                    {{-- @dd($purchase->purchase_id) --}}
                    <tr style="font-size: 0.7rem;">
                        <td class="Id" style="width: 20%;">
                            {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</td> 
                        <td style="width: 15%;"> {{ !empty($purchase->vender) ? $purchase->vender->display_name : '' }} </td>
                        <td style="width: 20%;">{{ !empty($purchase->category) ? $purchase->category->name : '' }}</td>
                        <td style="width: 15%;">{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                        <td style="width: 15%;">{{ \Auth::user()->priceFormat($purchase->getTotal()) }}</td>
                        <td style="width: 15%;">{{ \Auth::user()->priceFormat($purchase->getDue()) }}</td>
                        <td style="width: 10%;">
                            <span>{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
