<table>
    <thead>
        @include('student.exports.header', [
            'is_branch' => true,
            'report_name' => 'Bank Transfer Report',
            'is_period' => true,
            'params' => ['date_from' => $date_from, 'date_to' => $date_to],
            'colspan' => 7
        ])
        <tr>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">#</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Date</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Voucher</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Account Name</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Memo</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Reference</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Debit</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Credit</th>
            <th style="font-weight: bold; background-color: #BFBFBF; border: 1px solid #000000; text-align: center; font-size: 8px; font-family: calibri;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfers as $index => $transfer)
          {{-- @dd($transfer,$transfer['date']); --}}
            <tr>
                <td style="font-size: 8px; font-family: calibri;">{{ $index + 1 }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($transfer['date'])) }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer['journal'] }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer['account'] }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer['memo'] ?? '-' }}</td>
                <td style="font-size: 8px; font-family: calibri;">{{ $transfer['detail'] ?? '-' }}</td>
                <td style="font-size: 8px; font-family: calibri;" data-format="#,0.00">{{ (float) $transfer['debit'] }}</td>
                <td style="font-size: 8px; font-family: calibri;" data-format="#,##0.00">{{ (float) $transfer['credit'] }}</td>
                <td style="font-size: 8px; font-family: calibri;" data-format="#,##0.00">{{ (float) $transfer['balance'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
