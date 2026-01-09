@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>
                Sr</th>
            <th>
                B.Sr#</th>
            <th>
                Class</th>
            <th>
                Section</th>
            <th>
                Opening Balance</th>
            <th>
                Admission</th>
            <th>
                Withdrawals</th>
            <th>
                Transfer In</th>
            <th>
                Transfer Out</th>
            <th>
                Closing Balance</th>
        </tr>
    </thead>
    <tbody>
        @php $currentBranch = null; @endphp
        @php $i = 1; @endphp
        @php
            $branchTotal = [
                'opening' => 0,
                'new_admissions' => 0,
                'withdrawals' => 0,
                'transfer_in' => 0,
                'transfer_out' => 0,
                'closing_balance' => 0,
            ];
            $grandTotal = [
                'opening' => 0,
                'new_admissions' => 0,
                'withdrawals' => 0,
                'transfer_in' => 0,
                'transfer_out' => 0,
                'closing_balance' => 0,
            ];
        @endphp
        @foreach ($report as $key => $row)
            @if ($currentBranch != $row['branch'])
                @if (!is_null($currentBranch))
                    <!-- Output branch total row before starting new branch -->
                    <tr>
                        <td colspan="4" style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:right; border-top: 4px double black; border-bottom: 4px double black; background: gray;">Branch Total</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['opening'] }}</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['new_admissions'] }}</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['withdrawals'] }}</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['transfer_in'] }}</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['transfer_out'] }}</td>
                        <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['closing_balance'] }}</td>
                    </tr>
                    <tr>
                        <td colspan="10" style="height: 50px; border: none;"></td>
                    </tr>
                    @php
                        $branchTotal = [
                            'opening' => 0,
                            'new_admissions' => 0,
                            'withdrawals' => 0,
                            'transfer_in' => 0,
                            'transfer_out' => 0,
                            'closing_balance' => 0,
                        ];
                    @endphp
                @endif
                <tr>
                    <td colspan="3"
                        style="font-size: 8px; font-family: Calibri; font-weight: 500; border: 2px solid black; border-left: none; border-collapse: collapse;">
                        {{ $row['branch'] }}</td>
                    <td colspan="7"
                        style="font-size: 8px; font-family: Calibri; font-weight: 500; border: 2px solid black; border-right: none; border-collapse: collapse;"></td>
                    @php $currentBranch = $row['branch']; @endphp
                </tr>
            @endif
            <tr>
                <td>
                    {{ $i++ }}</td>
                <td>
                    {{ $i++ }}</td>
                <td>
                    {{ $row['class'] }}</td>
                <td>
                    {{ $row['section'] }}</td>
                <td>
                    {{ $row['opening'] }}</td>
                <td>
                    {{ $row['new_admissions'] }}</td>
                <td>
                    {{ $row['withdrawals'] }}</td>
                <td>
                    {{ $row['transfer_in'] }}</td>
                <td>
                    {{ $row['transfer_out'] }}</td>
                <td>
                    {{ $row['closing_balance'] }}</td>
            </tr>
            @php
                $branchTotal['opening'] += $row['opening'];
                $branchTotal['new_admissions'] += $row['new_admissions'];
                $branchTotal['withdrawals'] += $row['withdrawals'];
                $branchTotal['transfer_in'] += $row['transfer_in'];
                $branchTotal['transfer_out'] += $row['transfer_out'];
                $branchTotal['closing_balance'] += $row['closing_balance'];
                // Add to grand total as well
                $grandTotal['opening'] += $row['opening'];
                $grandTotal['new_admissions'] += $row['new_admissions'];
                $grandTotal['withdrawals'] += $row['withdrawals'];
                $grandTotal['transfer_in'] += $row['transfer_in'];
                $grandTotal['transfer_out'] += $row['transfer_out'];
                $grandTotal['closing_balance'] += $row['closing_balance'];
            @endphp
            {{--branch total --}}
            @if ($loop->last)
                <tr>
                    <td colspan="4" style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:right; border-top: 4px double black; border-bottom: 4px double black; background: gray;">Branch Total</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['opening'] }}</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['new_admissions'] }}</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['withdrawals'] }}</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['transfer_in'] }}</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['transfer_out'] }}</td>
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">{{ $branchTotal['closing_balance'] }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
{{-- all brnches grand total --}}
<table>
    <thead>
        <tr>
            <td colspan="4"
                style="font-size: 8px; font-family: Calibri; font-weight: 500; border: 2px solid black; border-collapse: collapse; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                Grand Total</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['opening'] }}</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['new_admissions'] }}</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['withdrawals'] }}</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['transfer_in'] }}</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['transfer_out'] }}</td>
            <td style="font-size: 8px; font-family: Calibri;  border: 2px solid black; border-collapse: collapse; text-align:center; border-top: 4px double black; border-bottom: 4px double black; background: gray;">
                {{ $grandTotal['closing_balance'] }}</td>
        </tr>
    </thead>
</table>




@include('student.exports.footer')