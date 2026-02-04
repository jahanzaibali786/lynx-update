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
            <th>Billing Cycle</th>
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
            // Get all branch names
            $branchNames = \App\Models\User::whereIn('id', $recipts->pluck('owned_by')->unique())
                          ->pluck('name', 'id')
                          ->toArray();
            
            // Group by branch
            $groupedReceipts = $recipts->groupBy('owned_by');
            
            // Track which voucher items have been used
            $usedVoucherItems = [];
        @endphp
        
        @foreach ($groupedReceipts as $branchId => $receipts)
            @php 
                $branchSr = 1; 
                $branchTotal = 0;
            @endphp
            
            {{-- Branch Header Row --}}
            <tr style="background-color: #f0f0f0; font-weight: bold; text-align: center;">
                <td colspan="16">{{ $branchNames[$branchId] ?? 'Unknown Branch' }}</td>
            </tr>
            
            {{-- Process each unique receipt once --}}
            @foreach ($receipts as $receipt)
                @php
                    $receiptAmount = $receipt->recipt_amount;
                    $studentId = $receipt->student_id;
                    $receiptId = $receipt->id;
                    
                    // Create a unique key for this receipt
                    $receiptKey = $receipt->voucher_id . '_' . $receipt->challan_id . '_' . $receiptId;
                    
                    // Find the ONE voucher item that matches this specific receipt
                    // Match by: amount, student_id, and not already used
                    $matchedItem = null;
                    
                    foreach ($receipt->voucher as $voucherItem) {
                        $itemKey = $voucherItem->journal . '_' . $voucherItem->id;
                        
                        // Check if this voucher item matches and hasn't been used
                        if ($voucherItem->credit > 0 && 
                            $voucherItem->credit == $receiptAmount &&
                            $voucherItem->user_id == $studentId &&
                            $voucherItem->head > 0 &&
                            !in_array($itemKey, $usedVoucherItems)) {
                            
                            $matchedItem = $voucherItem;
                            $usedVoucherItems[] = $itemKey; // Mark as used
                            break; // Found the match, stop looking
                        }
                    }
                    
                    // If exact amount match not found, try matching by head from challan
                    if (!$matchedItem && $receipt->challan && $receipt->challan->heads) {
                        foreach ($receipt->challan->heads as $challanHead) {
                            foreach ($receipt->voucher as $voucherItem) {
                                $itemKey = $voucherItem->journal . '_' . $voucherItem->id;
                                
                                if ($voucherItem->credit > 0 &&
                                    $voucherItem->head == $challanHead->head_id &&
                                    $voucherItem->user_id == $studentId &&
                                    !in_array($itemKey, $usedVoucherItems)) {
                                    
                                    $matchedItem = $voucherItem;
                                    $usedVoucherItems[] = $itemKey;
                                    break 2; // Break both loops
                                }
                            }
                        }
                    }
                @endphp
                
                {{-- Only show row if we found a matched voucher item --}}
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
                        <td>{{ $receipt->challan?->billing_cycle }}</td>
                        <td>{{ $receipt->bank?->bank_name }}</td>
                        <td>{{ $receipt->receive_type }}</td>
                        <td>{{ $matchedItem->heads?->fee_head ?? '' }}</td>
                        <td>{{ $receipt->referance }}</td>
                        <td>{{ $matchedItem->credit }}</td>
                        @php
                            $branchTotal += $matchedItem->credit;
                        @endphp
                        <td>0.0</td>
                    </tr>
                @endif
            @endforeach
            
                        @php
                            $grandTotal += $branchTotal;
                        @endphp
            {{-- Branch Total Row --}}
            <tr style="background-color: #f0f0f0;">
                <td colspan="14" style="font-weight: bold;">Total</td>
                <td style="font-weight: bold;">{{ number_format($branchTotal, 2) }}</td>
                <td>0.0</td>
            </tr>
        @endforeach
        <tr style="background-color: #f0f0f0;">
            <td colspan="14" style="font-weight: bold;">Grand Total</td>
            <td style="font-weight: bold;">{{ number_format($grandTotal, 2) }}</td>
            <td>0.0</td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')