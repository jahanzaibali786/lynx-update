<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>
                {{ __('Sr#') }}</th>
            <th>
                {{ __('Br.Sr#') }}</th>
            <th>
                {{ __('Roll#') }}</th>
            <th>
                {{ __('Student Name') }}</th>
            <th>
                {{ __('Father Name') }}</th>
            <th>
                {{ __('Class') }}</th>
            <th>
                {{ __('Monthly Fee') }}</th>
            <th>
                {{ __('Date of Admission') }}</th>
            <th>
                {{ __('Security Deposit') }}</th>
            <th>
                {{ __('Admission Challan #') }}</th>
            <th>
                {{ __('D/Withdrawal') }}</th>
            <th>
                {{ __('Adjustment') }}</th>
            <th>
                {{ __('Refund') }}</th>
            <th>
                {{ __('D/O/Payment') }}</th>
            <th>
                {{ __('Balance') }}</th>
            <th>
                {{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $globalIndex = 1;
            $grandTotalMonthlyFee = 0;
            $grandTotalDeposit = 0;
            $grandTotalPaid = 0;
            $grandTotalAdjustment = 0;
            $grandTotalRefund = 0;
            $grandTotalBalance = 0;
        @endphp
        @foreach (($records ?? []) as $branchId => $branchRecords)
            {{-- Branch name row --}}
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="12" style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000; white-space: nowrap; overflow: visible; padding: 0;">
                </td>
            </tr>
            @php
                $branchTotalMonthlyFee = 0;
                $branchTotalDeposit = 0;
                $branchTotalPaid = 0;
                $branchTotalAdjustment = 0;
                $branchTotalRefund = 0;
                $branchTotalBalance = 0;
                $branchSr = 1;
            @endphp
            @foreach ($branchRecords as $record)
                @php
                    $stats = ($journals ?? collect())->get($record->challan_id, ['deposit' => 0, 'paid' => 0]);
                    $securityDeposit = $stats['deposit'];
                    $securityPaid = $stats['paid'];
                    $monthlyFee = floatval(@$record->challan->student->monthly_fee ?? 0);
                    $adjustment = 0; // Add your adjustment logic here
                    $refund = 0; // Add your refund logic here
                    $balance = $securityDeposit - $securityPaid;
                    
                    $branchTotalMonthlyFee += $monthlyFee;
                    $branchTotalDeposit += $securityDeposit;
                    $branchTotalPaid += $securityPaid;
                    $branchTotalAdjustment += $adjustment;
                    $branchTotalRefund += $refund;
                    $branchTotalBalance += $balance;
                    
                    $grandTotalMonthlyFee += $monthlyFee;
                    $grandTotalDeposit += $securityDeposit;
                    $grandTotalPaid += $securityPaid;
                    $grandTotalAdjustment += $adjustment;
                    $grandTotalRefund += $refund;
                    $grandTotalBalance += $balance;
                @endphp
                <tr>
                    <td>{{ $globalIndex++ }}</td>
                    <td>{{ $branchSr++ }}</td>
                    <td>{{ @$record->challan->student->roll_no ?? '-' }}</td>
                    <td>{{ @$record->challan->student->stdname ?? '-' }}</td>
                    <td>{{ @$record->challan->student->fathername ?? '-' }}</td>
                    <td>{{ @$record->challan->student->class->name ?? '-' }}</td>
                    <td>{{ number_format($monthlyFee, 0) }}</td>
                    <td>{{ @$record->challan->enrollstudent->created_at ? \Carbon\Carbon::parse(@$record->challan->enrollstudent->created_at)->format('d-M-y') : '-' }}</td>
                    <td>{{ number_format($securityDeposit, 0) }}</td>
                    <td>{{ number_format($securityPaid, 0) }}</td>
                    <td>{{ @$record->challan->student->withdrawal && @$record->challan->student->withdrawal->withdraw_date ? \Carbon\Carbon::parse(@$record->challan->student->withdrawal->withdraw_date)->format('d-M-y') : '-' }}</td>
                    <td>{{ number_format($adjustment, 0) }}</td>
                    <td>{{ number_format($refund, 0) }}</td>
                    <td>{{ @$record->payment_date ? \Carbon\Carbon::parse(@$record->payment_date)->format('d-M-y') : '-' }}</td>
                    <td>{{ number_format($balance, 0) }}</td>
                    <td>{{ @$record->challan->student->withdrawal ? 'Withdrawal' : 'Active' }}</td>
                </tr>
            @endforeach
            {{-- Branch total row --}}
            <tr>
                <td colspan="6" style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000; font-family: Calibri;">Branch Total</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalMonthlyFee, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: center; font-family: Calibri;"></td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalDeposit, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalPaid, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: center; font-family: Calibri;"></td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalAdjustment, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalRefund, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: center; font-family: Calibri;"></td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: right; font-family: Calibri;">{{ number_format($branchTotalBalance, 0) }}</td>
                <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align: center; font-family: Calibri;"></td>
            </tr>
            {{-- Add spacing between branches --}}
            <tr>
                <td colspan="16" style="height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="16" style="height: 10px; border: none;"></td>
            </tr>
            <tr>
                <td colspan="16" style="height: 10px; border: none;"></td>
            </tr>
        @endforeach
        {{-- Grand total row --}}
        <tr>
            <td colspan="6" style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; text-align: center; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; font-family: Calibri;">Grand Total</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalMonthlyFee, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: center; font-family: Calibri;"></td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalDeposit, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalPaid, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: center; font-family: Calibri;"></td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalAdjustment, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalRefund, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: center; font-family: Calibri;"></td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: right; font-family: Calibri;">{{ number_format($grandTotalBalance, 0) }}</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align: center; font-family: Calibri;"></td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table>
