@include('student.exports.header')
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
    @if (isset($std))
        <table
            style="width:100%; margin-top:10px; font-family: Arial, Helvetica, sans-serif; font-size:10px; border-collapse:collapse;">

            <tr>

                <td colspan="4" style="padding:5px;">
                    <b>Student Name:</b>
                    <span style="font-weight:normal;">{{ $std->stdname }}</span>
                </td>

                <td colspan="3" style="padding:5px;">
                    <b>Class:</b>
                    <span style="font-weight:normal;">{{ $std->class->name ?? '' }}</span>
                </td>

                <td colspan="3" style="padding:5px;">
                    <b>Section:</b>
                    <span style="font-weight:normal;">{{ $std->enrollment->section->name ?? '' }}</span>
                </td>

                <td colspan="3" style="padding:5px;">
                    <b>Roll No:</b>
                    <span style="font-weight:normal;">{{ $std->enrollment->enrollId ?? '' }}</span>
                </td>

            </tr>

        </table>
    @endif
    <table border="1" cellspacing="0" cellpadding="5"
        style="width:100%; margin-top:15px; font-size:12px; border-collapse:collapse;">
        <thead>
            <tr style="background-color:#d3d3d3; font-weight:bold; text-align:center;">
                <th>Sr#</th>
                <th>Date</th>
                <th>Description</th>
                <th>Challan No</th>
                <th>Billing Month</th>
                <th>Class</th>
                <th>Challan Type</th>
                <th>Fee Head</th>
                <th>Receipt Mode</th>
                <th>Receipt Ref.</th>
                <th>Bank</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCredit = 0;
                $totalDebit = 0;
            @endphp
            @foreach ($accountStatement as $index => $item)
                @php
                    if ($item['type'] != 'opening' && $item['type'] != 'closing') {
                        $totalCredit += $item['credit'];
                        $totalDebit += $item['debit'];
                    }
                @endphp <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
    {{
        (!empty($item['date']) && $item['date'] !== '-')
            ? \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel(
                \Carbon\Carbon::parse($item['date'])
            )
            : '-'
    }}
</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['challan_no'] ?? '-' }}</td>
                    <td>{{ $item['billing_month'] ?? '-' }}</td>
                    <td>{{ $item['class'] ?? '-' }}</td>
                    <td>{{ $item['challan_type'] ?? '-' }}</td>
                    <td>{{ $item['head_name'] ?? '-' }}</td>
                    <td>{{ $item['receipt_mode'] ?? '-' }}</td>
                    <td>{{ $item['receipt_ref'] ?? '-' }}</td>
                    <td>{{ $item['bank_name'] ?? '-' }}</td>
                    @if ($item['type'] == 'closing')
                        <td><b>{{ number_format($totalDebit, 2) }}</b></td>
                        <td><b>{{ number_format($totalCredit, 2) }}</b></td>
                    @else
                        {{-- //if entry is not empty (-) then show in red (only amount not dashes) for debit and green (only on amounts not on dashes)for credit, otherwise show dash --}}
                        <td style="color: black;" data-format="#,##0.00">
                            {{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '-' }}
                        </td>
                        <td style="color: black;" data-format="#,##0.00">
                            {{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '-' }}
                        </td>
                    @endif
                    <td><b>{{ number_format($item['balance'], 2) }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@include('student.exports.footer')

