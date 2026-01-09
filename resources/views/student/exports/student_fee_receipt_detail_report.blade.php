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
            // Get all branch names
            $branchNames = \App\Models\User::whereIn('id', $recipts->pluck('owned_by')->unique())
                          ->pluck('name', 'id')
                          ->toArray();
            $groupedReceipts = $recipts->groupBy('owned_by');
        @endphp
        
        @foreach ($groupedReceipts as $branchId => $receipts)
            @php $branchSr = 1; @endphp
            
            {{-- Branch Header Row --}}
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="16">{{ $branchNames[$branchId] ?? 'Unknown Branch' }}</td>
            </tr>
            
            {{-- Branch Receipts --}}
            @foreach ($receipts as $receipt)
                @foreach ($receipt->voucher as $voucher)
                    <tr>
                        <td>{{ $globalSr++ }}</td>
                        <td>{{ $branchSr++ }}</td>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $receipt->recipt_date)->format('d-M-Y') }}</td>
                        <td>{{ $receipt->challan?->challan_type }}</td>
                        <td>{{ @$receipt->challan?->enrollstudent->id }}</td>
                        <td>{{ @$receipt->challan?->student->stdname }}</td>
                        <td>{{ @$receipt->challan?->class->name }}</td>
                        <td>{{ @$receipt->challan?->challanNo }}</td>
                        <td>
                            {{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}
                        </td>
                        <td>{{ @$receipt->challan?->billing_cycle }}</td>
                        <td>{{ $receipt->bank?->bank_name }}</td>
                        <td>{{ $receipt->receive_type }}</td>
                        <td>{{ @$voucher->heads?->fee_head ? $voucher->heads?->fee_head : '' }}</td>
                        <td>{{ $receipt->referance }}</td>
                        <td>{{ $voucher->credit }}</td>
                        <td>0.0</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>

@include('student.exports.footer')