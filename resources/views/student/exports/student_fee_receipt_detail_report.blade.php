@include('student.exports.header')

<table>
    <thead>
        <tr>
            <th>Sr#</th>
            <th>Br.Sr#</th>
            <th>Date</th>
            <th>Ch. Type</th>
            <th>Roll #.</th>
            <th>Student</th>
            <th>Class</th>
            <th>Challan No</th>
            <th>Billing Month</th>
            <th>Bank/Cash</th>
            <th>Mode</th>
            <th>T.Head</th>
            <th>Ref</th>
            <th>Rs.</th>
            <th>Over Receipt</th>
        </tr>
    </thead>

    <tbody>
        @php 
            $globalSr = 1;
            $grandTotal = 0;

            $branchNames = \App\Models\User::whereIn('id', $recipts->pluck('owned_by')->unique())
                ->pluck('name', 'id')
                ->toArray();

            $groupedReceipts = $recipts->groupBy('owned_by');

            $usedVoucherItems = [];
        @endphp
        
        @foreach ($groupedReceipts as $branchId => $receipts)

            @php 
                $branchSr = 1; 
                $branchTotal = 0;
            @endphp
            
            {{-- ✅ Branch Header --}}
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="15">
                    {{ $branchNames[$branchId] ?? 'Unknown Branch' }}
                </td>
            </tr>
            
            @foreach ($receipts as $receipt)

                @php
                    $studentId = $receipt->student_id;
                @endphp
              
                {{-- ✅ With challan heads --}}
                @if($receipt->challan && $receipt->challan->heads)

                    @foreach($receipt->challan->heads as $challanHead)
                        @dd($receipt)
                        @php
                            $matchedItem = null;

                            foreach ($receipt->voucher as $voucherItem) {
                                $itemKey = $voucherItem->journal . '_' . $voucherItem->id;

                                if ($voucherItem->credit > 0 &&
                                    $voucherItem->head == $challanHead->head_id &&
                                    $voucherItem->user_id == $studentId &&
                                    !in_array($itemKey, $usedVoucherItems)) {

                                    $matchedItem = $voucherItem;
                                    $usedVoucherItems[] = $itemKey;
                                    break;
                                }
                            }
                        @endphp
                        {{-- @dd($matchedItem) --}}
                        @if($matchedItem)
                            <tr>
                                <td>{{ $globalSr++ }}</td>
                                <td>{{ $branchSr++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($receipt->recipt_date)->format('d-M-Y') }}</td>
                                <td>{{ $receipt->challan?->challan_type }}</td>
                                <td>{{ $receipt->challan?->enrollstudent?->enrollId ?? $receipt->challan?->student?->roll_no }}</td>
                                <td>{{ $receipt->challan?->student?->stdname }}</td>
                                <td>{{ $receipt->challan?->class?->name }}</td>
                                <td>{{ $receipt->challan?->challanNo }}</td>
                                <td>{{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}</td>
                                <td>{{ $receipt->bank?->bank_name }}</td>
                                <td>{{ $receipt->receive_type }}</td>
                                <td>{{ $matchedItem->heads?->fee_head ?? '' }}</td>
                                <td>{{ $receipt->referance }}</td>
                                <td>{{ $matchedItem->credit }}</td>
                                <td>0.0</td>

                                @php
                                    $branchTotal += $matchedItem->credit;
                                @endphp
                            </tr>
                        @endif

                    @endforeach

                @else
                    {{-- ✅ Fallback --}}
                    @php
                        $receiptAmount = $receipt->recipt_amount;
                        $matchedItem = null;

                        foreach ($receipt->voucher as $voucherItem) {
                            $itemKey = $voucherItem->journal . '_' . $voucherItem->id;

                            if ($voucherItem->credit > 0 &&
                                $voucherItem->credit == $receiptAmount &&
                                $voucherItem->user_id == $studentId &&
                                $voucherItem->head > 0 &&
                                !in_array($itemKey, $usedVoucherItems)) {

                                $matchedItem = $voucherItem;
                                $usedVoucherItems[] = $itemKey;
                                break;
                            }
                        }
                    @endphp

                    @if($matchedItem)
                        <tr>
                            <td>{{ $globalSr++ }}</td>
                            <td>{{ $branchSr++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($receipt->recipt_date)->format('d-M-Y') }}</td>
                            <td>{{ $receipt->challan?->challan_type }}</td>
                            <td>{{ $receipt->challan?->enrollstudent?->enrollId ?? $receipt->challan?->student?->roll_no }}</td>
                            <td>{{ $receipt->challan?->student?->stdname }}</td>
                            <td>{{ $receipt->challan?->class?->name }}</td>
                            <td>{{ $receipt->challan?->challanNo }}</td>
                            <td>{{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}</td>
                            <td>{{ $receipt->bank?->bank_name }}</td>
                            <td>{{ $receipt->receive_type }}</td>
                            <td>{{ $matchedItem->heads?->fee_head ?? '' }}</td>
                            <td>{{ $receipt->referance }}</td>
                            <td>{{ $matchedItem->credit }}</td>
                            <td>0.0</td>

                            @php
                                $branchTotal += $matchedItem->credit;
                            @endphp
                        </tr>
                    @endif
                @endif

            @endforeach
            
            @php
                $grandTotal += $branchTotal;
            @endphp
            
            {{-- ✅ Branch Total --}}
            <tr style="background-color: #f0f0f0;">
                <td colspan="13" style="font-weight: bold;">Total</td>
                <td style="font-weight: bold;">{{ number_format($branchTotal, 2) }}</td>
                <td>0.0</td>
            </tr>

        @endforeach
        
        {{-- ✅ Grand Total --}}
        <tr style="background-color: #f0f0f0;">
            <td colspan="13" style="font-weight: bold;">Grand Total</td>
            <td style="font-weight: bold;">{{ number_format($grandTotal, 2) }}</td>
            <td>0.0</td>
        </tr>

    </tbody>
</table>
@include('student.exports.footer')