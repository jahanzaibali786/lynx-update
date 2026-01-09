{{-- @dd($branches[2]) --}}

<table>
    <tr>
        <td colspan="8" style="font-size: 20px; font-family: 'Edwardian Script ITC'; font-weight: bold;">The Lynx School</td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="5" style="font-weight: bold;">
            {{ $report_name }}
        </td>
    </tr>
    <tr></tr>
    <tr></tr>
    @php
        $serial = 1;
        $grandTotal = 0;
    @endphp
    <tr>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Sr.No
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            B.Sr#
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Challan #
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Date
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Roll#
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Name
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Class
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            B.Month
        </td>
        <td style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            Amount
        </td>
    </tr>
    @foreach ($datas as $dataId => $data)
    {{-- @dd($data[0]->branch_id) --}}
        <tr>
            <td colspan="9" style="background: #eeeeee;">
                {{ $branches[$data[0]->branch_id] }}
            </td>
        </tr>
        @php $total=0; @endphp
        {{-- @dd($data->branches) --}}
        @foreach ($data as $idst => $student)
            @php
                $total += $student->total_amount;
            @endphp
            <tr>
                <td style="border: 1px solid gray;">
                    {{ $serial++ }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $loop->iteration }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $student->challanNo }}
                </td>
                <td style="border: 1px solid gray; text-align: right;">
                    {{ $student->challan_date }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $student->student->roll_no }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $student->student->stdname }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $student->class->name }}
                </td>
                <td style="border: 1px solid gray; text-align: right;">
                    {{ $student->due_date }}
                </td>
                <td style="border: 1px solid gray;">
                    {{ $student->total_amount }}
                </td>
            </tr>
        @endforeach
        @php
            $grandTotal += $total;
        @endphp
        <tr>
            <td colspan="8"
                style="text-align: center; border: 1px solid black; background: #d8d8d8; font-weight: bold;"> TOTAL
            </td>
            <td style="border: 1px solid black; background: #d8d8d8; font-weight: bold;">{{ $total }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="8" style="text-align: center; border: 1px solid black; background: #d8d8d8; font-weight: bold;">
            G.TOTAL
        </td>
        <td style="border: 1px solid black; background: #d8d8d8; font-weight: bold;">{{ $grandTotal }}</td>
    </tr>
</table>
