@include('student.exports.header')
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 8px;">

    {{-- Student Details Section --}}

    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; border-bottom: 20px solid black;">
        <thead>
            <tr>
                <th class="header-column"></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="value-column"></th>
                <th class="empty-columns"></th>
                <th class="empty-columns"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Student Name</td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ $studentOnly }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Roll #</td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ $rollNo }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Account Title
                </td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ $accountTitle }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Branch Name :
                </td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ $branch }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Period :
                </td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ $from_date }} To
                    {{ $to_date }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            {{-- <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Opening
                    Receivable as of 01-May-2025 :</td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">5000</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Closing
                    Receivable as of 30-Aug-2025 :</td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">0</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr> --}}
            <tr>
                <td class="header-column" style="font-family: calibri; font-size:8px; font-weight:bold;">Statement run
                    date and time :</td>
                <td></td>
                <td></td>
                <th></th>
                <th></th>
                <td class="value-column" style="text-align: left; font-family: calibri; font-size:8px;">
                    {{ date('Y-m-d H:i:s') }}</td>
                <td class="empty-columns"></td>
                <td class="empty-columns"></td>
            </tr>
        </tbody>
    </table>


    {{-- Main Data Table --}}
    <table style="font-size:0.8rem;">
        <tr class="table_heads report_table">
            <th>sr#</th>
            <th>Student</th>
            <th>Class</th>
            <th>Billing Month</th>
            <th>Challan No</th>
            <th>Challan Type</th>
            <th>Challan Amount</th>
            <th>Payment Date</th>
            <th>Late Amount</th>
            <th>Receipt</th>
            <th>Arrears</th>
            <th>Receipt Mode</th>
            <th>T.HEAD/Product</th>
            <th>T.Amount</th>
            <th>Receipt Ref.</th>
        </tr>
        <tbody>
            @php
                $totalChallanAmount = 0;
                $totalLateAmount = 0;
                $totalReceipt = 0;
                $totalArrears = 0;
                $totalTAmount = 0;
            @endphp

            @foreach (@$receipts as $receipt)
                @php
                    if ($receipt->journalEntery->category == 'Studypack') {
                        $totalChallanAmount += $receipt->journalEntery->studypackchallan->total_amount ?? 0;
                        $totalLateAmount += $receipt->journalEntery->stdRecp->late_amount ?? 0;
                        $totalReceipt += $receipt->journalEntery->stdRecp->recipt_amount ?? 0;
                        $totalArrears += $receipt->journalEntery->stdRecp->arrears ?? 0;
                    } else {
                        $totalChallanAmount += $receipt->journalEntery->challan->total_amount ?? 0;
                        $totalLateAmount += $receipt->journalEntery->recipt->late_amount ?? 0;
                        $totalReceipt += $receipt->journalEntery->recipt->recipt_amount ?? 0;
                        $totalArrears += $receipt->journalEntery->recipt->arrears ?? 0;
                    }
                    $totalTAmount += $receipt->credit ?? 0;
                @endphp

                <tr>
                    <td>{{ @$loop->iteration }}</td>
                    <td>{{ @$receipt->user->stdname }}</td>
                    @if ($receipt->journalEntery->category == 'Studypack')
                        <td>{{ @$receipt->journalEntery->studypackchallan->class->name }}</td>
                        <td>{{ @$receipt->journalEntery->studypackchallan->fee_month }}</td>
                        <td>{{ @$receipt->journalEntery->studypackchallan->challanNo }}</td>
                        <td>{{ @$receipt->journalEntery->studypackchallan->challan_type }}</td>
                        <td>{{ @$receipt->journalEntery->studypackchallan->total_amount }}</td>
                        <td>{{ @$receipt->journalEntery->stdRecp->recipt_date }}</td>
                        <td>{{ @$receipt->journalEntery->stdRecp->late_amount }}</td>
                        <td>{{ @$receipt->journalEntery->stdRecp->recipt_amount }}</td>
                        <td>{{ @$receipt->journalEntery->stdRecp->arrears }}</td>
                        <td>{{ @$receipt->journalEntery->stdRecp->receive_type }}</td>
                        <td>-</td>
                    @else
                        <td>{{ @$receipt->journalEntery->challan->class->name }}</td>
                        <td>{{ @$receipt->journalEntery->challan->fee_month }}</td>
                        <td>{{ @$receipt->journalEntery->challan->challanNo }}</td>
                        <td>{{ @$receipt->journalEntery->challan->challan_type }}</td>
                        <td>{{ @$receipt->journalEntery->challan->total_amount }}</td>
                        <td>{{ @$receipt->journalEntery->recipt->recipt_date }}</td>
                        <td>{{ @$receipt->journalEntery->recipt->late_amount }}</td>
                        <td>{{ @$receipt->journalEntery->recipt->recipt_amount }}</td>
                        <td>{{ @$receipt->journalEntery->recipt->arrears }}</td>
                        <td>{{ @$receipt->journalEntery->recipt->receive_type }}</td>
                        @php
                            $headname = \App\Models\FeeHead::where('id', $receipt->head)->first();
                        @endphp
                        <td>{{ @$headname->fee_head }}</td>
                    @endif
                    <td>{{ @$receipt->credit }}</td>
                    <td>{{ @$receipt->journalEntery->recipt->referance }}</td>
                </tr>
            @endforeach

            {{-- Grand Total Row --}}
            <tr style="background-color: grey; color: white; font-weight: bold;">
                <td colspan="6" class="text-right">Grand Total</td>
                <td>{{ number_format($totalChallanAmount, 2) }}</td>
                <td></td>
                <td>{{ number_format($totalLateAmount, 2) }}</td>
                <td>{{ number_format($totalReceipt, 2) }}</td>
                <td>{{ number_format($totalArrears, 2) }}</td>
                <td></td>
                <td></td>
                <td>{{ number_format($totalTAmount, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

</div>
@include('student.exports.footer')
