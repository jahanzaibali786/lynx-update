<table>
    <thead>
        @include('student.exports.header', [
            'is_branch' => false,
            'report_name' => 'Bank Transfer Report',
            'is_period' => true,
            'params' => ['date_from' => $date_from, 'date_to' => $date_to],
            'colspan' => 7
        ])
        <tr>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">#</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Date</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">From Account</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">To Account</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Amount</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Reference</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Description</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfers as $index => $transfer)
            @php
                $fromBankName = !empty($transfer->fromBankAccount()) ? $transfer->fromBankAccount()->bank_name . ' (' . $transfer->fromBankAccount()->holder_name . ')' : '';
                $toBankName = !empty($transfer->toBankAccount()) ? $transfer->toBankAccount()->bank_name . ' (' . $transfer->toBankAccount()->holder_name . ')' : '';
            @endphp
            <tr>
                <td style="font-size: 8px; font-family: calibri;">{{ $index + 1 }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($transfer->date)) }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $fromBankName }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $toBankName }}</td>
                <td style="font-size: 8px; font-family: calibri;" data-format="#,##0.00">{{ (float) $transfer->amount }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer->reference }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer->description }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
