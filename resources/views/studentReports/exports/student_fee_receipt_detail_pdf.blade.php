<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    td {
        padding-left: 3px;
    }
</style>

<div class="card p-4" style="margin-top: 150px;">
    <div class="mt-4">
        @php
            $i = 1;
            $grandTotal = 0;
        @endphp
        @foreach ($branches as $branchId => $branchName)
            @php
                $branchReceipts = $recipts->filter(function($receipt) use ($branchId) {
                    return $receipt->owned_by == $branchId;
                });
                $branchTotal = 0;
            @endphp
            @if($branchReceipts->count())
            <div class="" style="width: 100%;">
                <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                    {{ $branchName }}
                </span>
                <table class="table" style="width: 100%;">
                    <thead>
                        <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                            <th style="width:4%; border: 1px solid black;">{{ __('Sr No.') }}</th>
                            <th style="width:6%; border: 1px solid black;">{{ __('B Sr No.') }}</th>
                            <th style="width:8%; border: 1px solid black;">{{ __('Rpt. Date') }}</th>
                            <th style="width:8%; border: 1px solid black;">{{ __('Ch Type') }}</th>
                            <th style="width:8%; border: 1px solid black;">{{ __('Roll No') }}</th>
                            <th style="width:15%; border: 1px solid black;">{{ __('Student') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Class') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Challan No') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Billing Period') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Bank') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('T.Head') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Ref.') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Amount') }}</th>
                            <th style="width:10%; border: 1px solid black;">{{ __('Over Receipt.') }}</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:0.7rem;">
                        @php $b = 1; @endphp
                        @foreach ($branchReceipts as $receipt)
                            @foreach ($receipt->voucher as $voucher)
                                <tr>
                                    <td style="width:4%; border: 1px solid black;">{{ $i }}</td>
                                    <td style="width:6%; border: 1px solid black;">{{ $b }}</td>
                                    <td style="width:8%; border: 1px solid black;">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $receipt->recipt_date)->format('d-M-Y') }}</td>
                                    <td style="width:8%; border: 1px solid black;">{{ $receipt->challan->challan_type }}</td>
                                    <td style="width:8%; border: 1px solid black;">{{ @$receipt->challan->enrollstudent->id }}</td>
                                    <td style="width:15%; border: 1px solid black;">{{ @$receipt->challan->student->stdname }}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ @$receipt->challan->class->name }}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ @$receipt->challan->challanNo }}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ $receipt->challan->fee_month ? \Carbon\Carbon::createFromFormat('Y-m', $receipt->challan->fee_month)->format('F Y') : '' }}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ $receipt->bank->bank_name }}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ @$voucher->heads->fee_head ? $voucher->heads->fee_head  : ''}}</td>
                                    <td style="width:10%; border: 1px solid black;">{{ $receipt->referance }}</td>
                                    <td style="width:10%; border: 1px solid black;">
                                        {{ $voucher->credit }}
                                        @php $branchTotal += $voucher->credit; $grandTotal += $voucher->credit; @endphp
                                    </td>
                                    <td style="width:10%; border: 1px solid black;">0.0</td>
                                </tr>
                                @php $i++; $b++; @endphp
                            @endforeach
                        @endforeach
                        <tr style="font-weight:bold;">
                            <td colspan="12" style="text-align:right; border: 1px solid black;">Branch Total:</td>
                            <td style="border: 1px solid black;">{{ number_format($branchTotal, 2) }}</td>
                            <td style="border: 1px solid black;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach
        <table class="table" style="width:100%">
            <thead style="font-weight:bold; background-color:grey; color:#fff;">
                <td style="width:90%; text-align:right; border: 1px solid black;">Grand Total :</td>
                <td style="width:10%; border: 1px solid black;">{{ number_format($grandTotal, 2) }}</td>
                <td style="width:10%; border: 1px solid black;"></td>
            </thead>
        </table>
    </div>
</div>