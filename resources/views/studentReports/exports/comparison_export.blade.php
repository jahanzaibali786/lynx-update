@include('student.exports.header')
<table>
    <thead>
        @php
            $i = 1;
            $grandReg = 0;
            $grandPre = 0;
            $grandDiff = 0;
        @endphp
        <tr>
            <th>Branch Name</th>
            <th>Sr #</th>
            <th>Roll #</th>
            <th>Name</th>
            <th>Class</th>
            <th>D/O/ADM</th>
            <th>Regular Challan Net Payable</th>
            <th>Pre Challan Net Payable</th>
            <th>Difference</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $branchId => $rows)
            {{-- Branch header --}}
            <tr>
                <td colspan="10"
                    style="font-weight:bold; background-color:#bcbcbc; border:1px solid #000; text-align:left;">
                    {{ $branches[$branchId] ?? 'Branch #' . $branchId }}
                </td>
            </tr>

            @php
                $branchReg = 0;
                $branchPre = 0;
                $branchDiff = 0;
                $branchSr = 1;
            @endphp

            @foreach ($rows as $row)
                @php
                    $diff = $row['difference'];
                    $isOk = $diff == 0;
                    $branchReg += $row['regular_net'];
                    $branchPre += $row['pre_challan_net'];
                    $branchDiff += $diff;
                @endphp
                <tr>
                    <td>{{ $branches[$branchId] ?? '' }}</td>
                    <td>{{ $branchSr++ }}</td>
                    <td>{{ $row['roll_no'] }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['class_name'] }}</td>
                    <td>
                        {{ !empty($row['adm_date'])
                            ? \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel(\Carbon\Carbon::parse($row['adm_date']))
                            : '' }}
                    </td>
                    <td>{{ $row['regular_net'] }}</td>
                    <td>{{ $row['pre_challan_net'] }}</td>
                    <td>{{ $diff }}</td>
                    <td>{{ $isOk ? 'OK' : 'Mismatch' }}</td>
                </tr>
                @php $i++; @endphp
            @endforeach

            {{-- Branch subtotal --}}
            <tr>
                <td colspan="6"
                    style="font-weight:bold; background-color:#B8B8B8; text-align:center; border:2px solid #000;">
                    Branch Total
                </td>
                <td style="font-weight:bold; background-color:#B8B8B8; border:2px solid #000; text-align:right;">
                    {{ $branchReg }}</td>
                <td style="font-weight:bold; background-color:#B8B8B8; border:2px solid #000; text-align:right;">
                    {{ $branchPre }}</td>
                <td style="font-weight:bold; background-color:#B8B8B8; border:2px solid #000; text-align:right;">
                    {{ $branchDiff }}</td>
                <td style="background-color:#B8B8B8; border:2px solid #000;"></td>
            </tr>

            {{-- Spacer rows --}}
            <tr>
                <td colspan="10" style="border:none; height:10px;"></td>
            </tr>

            @php
                $grandReg += $branchReg;
                $grandPre += $branchPre;
                $grandDiff += $branchDiff;
            @endphp
        @endforeach

        {{-- Grand Total --}}
        <tr>
            <td colspan="6"
                style="font-weight:bold; background-color:#8B8B8B; text-align:center;
                       border:2px solid #000; border-top:3px double #000; border-bottom:3px double #000;">
                Grand Total
            </td>
            <td
                style="font-weight:bold; background-color:#A9A9A9; border:2px solid #000;
                       border-top:3px double #000; border-bottom:3px double #000; text-align:right;">
                {{ $grandReg }}</td>
            <td
                style="font-weight:bold; background-color:#A9A9A9; border:2px solid #000;
                       border-top:3px double #000; border-bottom:3px double #000; text-align:right;">
                {{ $grandPre }}</td>
            <td
                style="font-weight:bold; background-color:#A9A9A9; border:2px solid #000;
                       border-top:3px double #000; border-bottom:3px double #000; text-align:right;">
                {{ $grandDiff }}</td>
            <td
                style="background-color:#A9A9A9; border:2px solid #000;
                       border-top:3px double #000; border-bottom:3px double #000;">
            </td>
        </tr>

        <tr>
            <td colspan="10" style="border:none; height:20px;"></td>
        </tr>

    </tbody>
</table>

@include('student.exports.footer')
