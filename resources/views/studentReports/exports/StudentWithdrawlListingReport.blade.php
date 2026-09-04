@include('student.exports.header')

<table>
    <thead>
        <tr>
            <th>Sr#</th>
            <th>B.Sr#</th>
            <th>Roll #</th>
            <th>Student Name</th>
            <th>Class</th>
            <th>Adm Date</th>
            <th>Father Name</th>
            <th>Address</th>
            <th>Phone#</th>
            <th>Withdrawal Date</th>
            <th>Reason of Leaving</th>
            <th>WO#</th>
            <th>Security Deposit</th>
            <th>Net Receivable</th>
            <th>Net Paid</th>
            <th>Net Payable</th>
        </tr>
    </thead>
    <tbody>
        @php $overall_sr = 1; @endphp
        @foreach ($all_data as $branchId => $students)
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="3"
                    style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="13"
                    style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000;">
                </td>
            </tr>
            @php $branch_sr = 1; @endphp
            @foreach ($students as $data)
                <tr>
                    <td>{{ $overall_sr++ }}</td>
                    <td>{{ $branch_sr++ }}</td>
                    <td>{{ !empty($data->student) ? $data->student->roll_no : '-' }}</td>
                    <td>
                        {!! nl2br(
                            wordwrap(!empty($data->student) ? $data->student->stdname : '-', 20, "\n", true),
                        ) !!}
                    </td>
                    <td>
                        {{ !empty($data->student->class) ? $data->student->class->name : '-' }}
                    </td>
                    <td>
                        {{ !empty($data->student->enrollment) && $data->student->enrollment->adm_date ? \Carbon\Carbon::parse($data->student->enrollment->adm_date)->format('d-M-Y') : '-' }}
                    </td>
                    <td>
                        {!! nl2br(
                            wordwrap(!empty($data->student) ? $data->student->fathername : '-', 20, "\n", true),
                        ) !!}
                    </td>
                    <td>
                        {!! nl2br(
                            wordwrap(!empty($data->student) ? $data->student->address : '-', 20, "\n", true),
                        ) !!}
                    </td>
                    <td>
                        @php
                            $phones = !empty($data->student)
                                ? $data->student->fatherphone
                                : '-';
                            if ($phones && $phones !== '-') {
                                $phonesArr = array_map('trim', explode(',', $phones));
                                echo implode(',<br>', $phonesArr);
                            } else {
                                echo '-';
                            }
                        @endphp
                    </td>
                    <td>
                        {{ !empty($data) && $data->withdraw_date ? \Carbon\Carbon::parse($data->withdraw_date)->format('d-M-Y') : '-' }}
                    </td>
                    <td>
                        {!! nl2br(wordwrap(!empty($data) ? $data->reason : '-', 20, "\n", true)) !!}
                    </td>
                    <td>{{ !empty($data) ? $data->wo_no : '-' }}</td>
                    <td>{{ $data->security_deposit ?? 0 }}</td>
                    <td>{{ $data->receivable ?? 0 }}</td>
                    <td>{{ $data->paid ?? 0 }}</td>
                    <td>{{ $data->payable ?? 0 }}</td>


                </tr>
            @endforeach
            <tr>
                <td colspan="12"
                    style="font-size:8px; font-family:Calibri; text-align:center; border: 2px solid black; background:gray; font-weight:bold;">
                    Branch Total</td>
                <td style="text-align:right; border: 2px solid black; background:gray;">
                    {{ $branchTotals[$branchId]['security_deposit'] ?? 0 }}</td>
                <td style="text-align:right; border: 2px solid black; background:gray;">
                    {{ $branchTotals[$branchId]['receivable'] ?? 0 }}</td>
                <td style="text-align:right; border: 2px solid black; background:gray;">
                    {{ $branchTotals[$branchId]['paid'] ?? 0 }}</td>
                <td style="text-align:right; border: 2px solid black; background:gray;">
                    {{ $branchTotals[$branchId]['payable'] ?? 0 }}</td>
            </tr>
            <tr>
                <td colspan="16" style="height: 50px; border: none;"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="12"
                style="font-size:8px; font-family:Calibri; text-align:center; background:gray; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; font-weight:bold;">
                Grand Total</td>
            <td
                style="text-align:right; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; background:gray; font-weight:bold;">
                {{ $grandTotals['security_deposit'] ?? 0 }}</td>
            <td
                style="text-align:right; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; background:gray; font-weight:bold;">
                {{ $grandTotals['receivable'] ?? 0 }}</td>
            <td
                style="text-align:right; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; background:gray; font-weight:bold;">
                {{ $grandTotals['paid'] ?? 0 }}</td>
            <td
                style="text-align:right; border: 2px solid black; border-top: 2px double black; border-bottom: 2px double black; background:gray; font-weight:bold;">
                {{ $grandTotals['payable'] ?? 0 }}</td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')
