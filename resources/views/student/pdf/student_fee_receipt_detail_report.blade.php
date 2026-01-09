@include('student.exports.header')

<table style="width: 100%; margin-bottom: 18px; margin-top: 100px; font-size: 8px; font-family: calibri; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f0f0f0; font-weight: bold; font-size: 8px; font-family: calibri;">
            <th style="border: 1px solid black; width:40px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Sr#</th>
            <th style="border: 1px solid black; width:40px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Br.Sr#</th>
            <th style="border: 1px solid black; width:60px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Date</th>
            <th style="border: 1px solid black; width:50px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Ch. Type</th>
            <th style="border: 1px solid black; width:60px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Roll #.</th>
            <th style="border: 1px solid black; width:120px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Student</th>
            <th style="border: 1px solid black; width:65px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Class</th>
            <th style="border: 1px solid black; width:80px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Challan No</th>
            <th style="border: 1px solid black; width:80px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Billing Month</th>
            <th style="border: 1px solid black; width:80px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Billing Cycle</th>
            <th style="border: 1px solid black; width:120px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Bank/Cash</th>
            <th style="border: 1px solid black; width:60px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Mode</th>
            <th style="border: 1px solid black; width:120px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">T.Head</th>
            <th style="border: 1px solid black; width:100px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Ref</th>
            <th style="border: 1px solid black; width:100px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Rs.</th>
            <th style="border: 1px solid black; width:120px; font-size: 8px; font-family: calibri; text-align: center; vertical-align: middle;">Over Receipt</th>
        </tr>
    </thead>
    <tbody>
        @php $SrNo = 1; @endphp
        @foreach ($recipts as $recipt)
            <tr>
                <td>{{ $globalSr++ }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $SrNo++ }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $receipt->recipt_date)->format('d-M-Y') }}
                                    </td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $receipt->challan?->challan_type }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ @$receipt->challan?->enrollstudent->id }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ @$receipt->challan?->student->stdname }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ @$receipt->challan?->class->name }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ @$receipt->challan?->challanNo }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">
                                        {{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}
                                    </td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ @$receipt->challan?->billing_cycle }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $receipt->bank?->bank_name }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $receipt->receive_type }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">
                                        {{ @$voucher->heads?->fee_head ? $voucher->heads?->fee_head : '' }}
                                    </td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">{{ $receipt->referance }}</td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">
                                        {{ $voucher->credit }}
                                    </td>
                                    <td style="border: 1px solid black; font-size: 8px; font-family: calibri; text-align: center;">
                                        0.0
                                    </td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')