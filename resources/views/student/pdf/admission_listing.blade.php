@php
    // Flatten heads for table header
    $headNames = $heads->pluck('fee_head', 'id');
@endphp
<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
        </style>
        @include('student.exports.header')
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif; ">
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 40px; background-color:gray;">{{ __('Sr No.') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 40px; background-color:gray;">{{ __('B Sr No.') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 60px; background-color:gray;">{{ __('Reg No #') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 60px; background-color:gray;">{{ __('Roll No #') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 60px; background-color:gray;">{{ __('Challan No #') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 100px; background-color:gray;">{{ __('Admission Date') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 80px; background-color:gray;">{{ __('Class') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:left; border: 2px solid black; width: 120px; background-color:gray;">{{ __('Student Name') }}</th>
            @foreach($headNames as $head)
                @php
                    $width = strlen($head) <= 8
                        ? 50
                        : 50 + (strlen($head) - 8) * 7;
                @endphp
                <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; background-color:gray; width:{{ $width }}px;">{{ $head }}</th>
            @endforeach
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:right; border: 2px solid black; background-color:gray;">{{ __('Amount') }}</th>
            <th style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; background-color:gray;">{{ __('Adm. Status') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php $mainloop = 1; @endphp
        @foreach($studentData as $branchId => $students)
            @foreach($students as $index => $student)
                @php
                    $studentRegNo = @$student->StudentRegistration->reg_no;
                    $challanData = $studentChallanData[$studentRegNo] ?? [
                        'challan_no' => '',
                        'challan_id' => '',
                        'heads' => [],
                        'total' => 0,
                    ];
                    $challanId = $challanData['challan_id'] ?? '';
                    $headAmounts = collect($challanData['heads'] ?? [])->keyBy('head_id');
                @endphp
                <tr>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ $mainloop++ }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ $student->id }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ $student->enrollId ?? '' }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ $challanData['challan_no'] }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ !empty($student->created_at) ? date('d M Y', strtotime($student->created_at)) : '' }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ @$student->class->name }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: calibri;">{{ @$student->StudentRegistration->stdname ?? '' }}</td>
                    @foreach($headNames as $headId => $head)
                        <td style="text-align: right; font-size: 8px; font-family: calibri;">{{ $headAmounts[$headId]['amount'] ?? '' }}</td>
                    @endforeach
                    <td style="text-align: right; font-size: 8px; font-family: calibri;">{{ $challanData['total'] }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">{{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
            <tr style="font-weight:bold; background: gray;">
                <td colspan="{{ 8 + count($headNames) }}" style="text-align:center; font-size: 8px; background-color:gray; font-family: calibri; border: 1px solid #000;">Branch Total</td>
                <td style="text-align: right; font-size: 8px; font-family: calibri; background-color:gray; border: 1px solid #000;">{{ $branchTotals[$branchId] ?? 0 }}</td>
                <td style="text-align: right; font-size: 8px; font-family: calibri; background-color:gray; border: 1px solid #000;"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="{{ 11 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ 11 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr>
            <td colspan="{{ 11 + count($headNames) }}" style="height: 10px; border: none;"></td>
        </tr>
        <tr style="font-weight:bold; background: gray; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">
            <td colspan="{{ 8 + count($headNames) }}" style="text-align:center; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;" >Grand Total</td>
            <td style="text-align: right; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">{{ $grandTotal }}</td>
            <td style="text-align: right; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;"></td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table> 