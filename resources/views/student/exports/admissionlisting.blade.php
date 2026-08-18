@php
    // Flatten heads for table header
    $headNames = $heads->pluck('fee_head', 'id');
@endphp
<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr No.') }}</th>
            <th>{{ __('B Sr No.') }}</th>
            <th>{{ __('Reg No #') }}</th>
            <th>{{ __('Roll No #') }}</th>
            <th>{{ __('Challan No #') }}</th>
            <th>{{ __('Billing Month') }}</th>
            <th>{{ __('Admission Date') }}</th>
            <th>{{ __('Class') }}</th>
            <th>{{ __('Student Name') }}</th>
            @foreach($headNames as $head)
                @php
                    $width = strlen($head) <= 8
                        ? 50
                        : 50 + (strlen($head) - 8) * 7;
                @endphp
                <th>{{ $head }}</th>
            @endforeach
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Adm. Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $mainloop = 1; @endphp
        @foreach($studentData as $branchId => $students)
            @foreach($students as $index => $student)
                @php
                    $studentRegNo = @$student->StudentRegistration->reg_no;
                    $challanData = $studentChallanData[$studentRegNo] ?? [
                        'challan_no' => '',
                        'challan_id' => '',
                        'fee_month' => '',
                        'heads' => [],
                        'total' => 0,
                    ];
                    $challanId = $challanData['challan_id'] ?? '';
                    $headAmounts = collect($challanData['heads'] ?? [])->keyBy('head_id');
                @endphp
                <tr>
                    <td>{{ $mainloop++ }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->enrollId ?? '' }}</td>
                    <td>{{ $challanData['challan_no'] }}</td>
                    <td>{{ !empty($challanData['fee_month']) ? date('M Y', strtotime($challanData['fee_month'])) : '-' }}</td>
                    <td>{{ !empty($student->created_at) ? date('d M Y', strtotime($student->created_at)) : '' }}</td>
                    <td>{{ @$student->class->name }}</td>
                    <td>{{ @$student->StudentRegistration->stdname ?? '' }}</td>
                    @foreach($headNames as $headId => $head)
                        <td>{{ $headAmounts[$headId]['amount'] ?? '' }}</td>
                    @endforeach
                    <td>{{ $challanData['total'] }}</td>
                    <td>{{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="{{ 9 + count($headNames) }}" style="text-align:center; font-size: 8px; background-color:gray; font-family: calibri; border: 1px solid #000;">Branch Total</td>
                <td style="text-align: right; font-size: 8px; font-family: calibri; background-color:gray; border: 1px solid #000;">{{ $branchTotals[$branchId] ?? 0 }}</td>
                <td style="text-align: right; font-size: 8px; font-family: calibri; background-color:gray; border: 1px solid #000;"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="{{ 9 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ 9 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ 9 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ 9 + count($headNames) }}" style="text-align:center; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;" >Grand Total</td>
            <td style="text-align: right; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">{{ $grandTotal }}</td>
            <td style="text-align: right; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;"></td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table> 