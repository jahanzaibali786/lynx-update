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
            <!-- <th>Billing Cycle</th> -->
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
                <td colspan="15">{{ $branchNames[$branchId] ?? 'Unknown Branch' }}</td>
            </tr>
            
            {{-- Process each receipt and its heads --}}
            @foreach ($receipts as $receipt)
                @php
                    $studentId = $receipt->student_id;
                    $receiptId = $receipt->id;
                @endphp
                
                {{-- Loop through each challan head/items to create separate rows --}}
                @if($receipt->challan && $receipt->challan->heads)
                    @foreach($receipt->challan->heads as $challanHead)
                        @php
                            $matchedItem = null;
                            
                            // Find matching voucher item for this specific head
                            foreach ($receipt->voucher as $voucherItem) {
                                $itemKey = $voucherItem->journal . '_' . $voucherItem->id;
                                
                                // Match by head and ensure not already used
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
                        
                        {{-- Show row for this head if matched --}}
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
                                <!-- <td>{{ $receipt->challan?->billing_cycle }}</td> -->
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
                @elseif($receipt->challan && $receipt->challan->items)
                    @php
                        $studyPack = \App\Models\StudyPack::find($receipt->challan->studypack_id);
                        $studyPackTitle = $studyPack ? $studyPack->title : '';
                        $combinedAmount = $receipt->recipt_amount;
                    @endphp
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
                        <!-- <td>{{ $receipt->challan?->billing_cycle }}</td> -->
                        <td>{{ $receipt->bank?->bank_name }}</td>
                        <td>{{ $receipt->receive_type }}</td>
                        <td>{{ $studyPackTitle }}</td>
                        <td>{{ $receipt->referance }}</td>
                        <td>{{ $combinedAmount }}</td>
                        @php
                            $branchTotal += $combinedAmount;
                        @endphp
                        <td>0.0</td>
                    </tr>
                @else
                    {{-- Fallback: iterate voucher items directly --}}
                    @foreach ($receipt->voucher as $voucherItem)
                        @php
                            $itemKey = $voucherItem->journal . '_' . $voucherItem->id;
                            if ($voucherItem->credit <= 0 || in_array($itemKey, $usedVoucherItems)) {
                                continue;
                            }
                            $usedVoucherItems[] = $itemKey;
                        @endphp
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
                            <!-- <td>{{ $receipt->challan?->billing_cycle }}</td> -->
                            <td>{{ $receipt->bank?->bank_name }}</td>
                            <td>{{ $receipt->receive_type }}</td>
                            <td>{{ $voucherItem->heads?->fee_head ?? '' }}</td>
                            <td>{{ $receipt->referance }}</td>
                            <td>{{ $voucherItem->credit }}</td>
                            @php
                                $branchTotal += $voucherItem->credit;
                            @endphp
                            <td>0.0</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            
            @php
                $grandTotal += $branchTotal;
            @endphp
            
            {{-- Branch Total Row --}}
            <tr style="background-color: #f0f0f0;">
                <td colspan="13" style="font-weight: bold;">Total</td>
                <td style="font-weight: bold;">{{ number_format($branchTotal, 2) }}</td>
                <td>0.0</td>
            </tr>
        @endforeach
        
        {{-- Grand Total Row --}}
        <tr style="background-color: #f0f0f0;">
            <td colspan="13" style="font-weight: bold;">Grand Total</td>
            <td style="font-weight: bold;">{{ number_format($grandTotal, 2) }}</td>
            <td>0.0</td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')