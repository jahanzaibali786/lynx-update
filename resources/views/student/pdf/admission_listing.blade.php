@php
    // Flatten heads for table header
    $headNames = $heads->pluck('fee_head', 'id');

    // Summary PDF => Paid + Remaining
    // Detailed PDF => Base + Discount + Payable + Paid + Remaining
    $isDetailReport = in_array(request('export'), ['detail_pdf']);
    $columnsPerHead = $isDetailReport ? 5 : 2;

    // Static columns:
    // Sr, B Sr, Reg, Roll, Challan, Admission Date, Class, Student Name,
    // Amount, Challan Status, Student Status = 11
    $totalColumns = 11 + (count($headNames) * $columnsPerHead);
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
            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 40px; background-color:gray;">
                {{ __('Sr No.') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 40px; background-color:gray;">
                {{ __('B Sr No.') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 60px; background-color:gray;">
                {{ __('Reg No #') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 60px; background-color:gray;">
                {{ __('Roll No #') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 90px; background-color:gray;">
                {{ __('Challan No #') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 100px; background-color:gray;">
                {{ __('Admission Date') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; width: 80px; background-color:gray;">
                {{ __('Class') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:left; border: 2px solid black; width: 120px; background-color:gray;">
                {{ __('Student Name') }}
            </th>

            @foreach($headNames as $head)
                @php
                    $width = strlen($head) <= 8
                        ? ($isDetailReport ? 220 : 100)
                        : (($isDetailReport ? 220 : 100) + (strlen($head) - 8) * 7);
                @endphp

                <th colspan="{{ $columnsPerHead }}"
                    style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; background-color:gray; width:{{ $width }}px;">
                    {{ $head }}
                </th>
            @endforeach

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:right; border: 2px solid black; background-color:gray;">
                {{ __('Amount') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; background-color:gray;">
                {{ __('Challan Status') }}
            </th>

            <th rowspan="2" style="font-size: 8px; font-family: calibri; font-weight: bold; text-align:center; border: 2px solid black; background-color:gray;">
                {{ __('Student Status') }}
            </th>
        </tr>

        <tr style="font-weight: bold; background-color:gray; border: 2px solid black;">
            @foreach($headNames as $headId => $head)
                @if($isDetailReport)
                    <th style="font-size: 8px; font-family: calibri; text-align:center; border: 2px solid black; background-color:gray;">
                        {{ __('Base') }}
                    </th>

                    <th style="font-size: 8px; font-family: calibri; text-align:center; border: 2px solid black; background-color:gray;">
                        {{ __('Discount') }}
                    </th>

                    <th style="font-size: 8px; font-family: calibri; text-align:center; border: 2px solid black; background-color:gray;">
                        {{ __('Payable') }}
                    </th>
                @endif

                <th style="font-size: 8px; font-family: calibri; text-align:center; border: 2px solid black; background-color:gray;">
                    {{ __('Paid') }}
                </th>

                <th style="font-size: 8px; font-family: calibri; text-align:center; border: 2px solid black; background-color:gray;">
                    {{ __('Remaining') }}
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php $mainloop = 1; @endphp

        @foreach($studentData as $branchId => $students)

            {{-- Branch Name Row --}}
            <tr style="font-weight:bold; background: #d9d9d9;">
                <td colspan="{{ $totalColumns }}"
                    style="text-align:left; font-size:8px; font-family:calibri; border:1px solid #000; background:#d9d9d9;">
                    {{ $branches[$branchId] ?? ($students->first()->branch->name ?? 'Unknown Branch') }}
                </td>
            </tr>

            @foreach($students as $index => $student)
                @php
                    $studentKey = $student->regId;

                    $challanData = $studentChallanData[$studentKey] ?? [
                        'challan_no' => '',
                        'challan_ids' => [],
                        'challan_count' => 0,
                        'fee_month' => '',
                        'challan_status' => '',
                        'heads' => [],
                        'total' => 0,
                    ];

                    $headAmounts = collect($challanData['heads'] ?? [])->keyBy('head_id');
                @endphp

                <tr>
                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $mainloop++ }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $index + 1 }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $student->StudentRegistration->reg_no ?? $student->regId ?? '' }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $student->enrollId ?? '' }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $challanData['challan_no'] ?? '' }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ !empty($student->adm_date)
                            ? date('d M Y', strtotime($student->adm_date))
                            : (!empty($student->created_at)
                                ? date('d M Y', strtotime($student->created_at))
                                : '')
                        }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $student->class->name ?? '' }}
                    </td>

                    <td style="text-align: left; font-size: 8px; font-family: calibri;">
                        {{ $student->StudentRegistration->stdname ?? '' }}
                    </td>

                    @foreach($headNames as $headId => $head)
                        @php
                            $headData = $headAmounts[$headId] ?? null;
                        @endphp

                        @if($isDetailReport)
                            <td style="text-align: right; font-size: 8px; font-family: calibri;">
                                {{ $headData ? number_format($headData['base_amount'] ?? 0, 2) : '' }}
                            </td>

                            <td style="text-align: right; font-size: 8px; font-family: calibri;">
                                {{ $headData ? number_format($headData['discount_amount'] ?? 0, 2) : '' }}
                            </td>

                            <td style="text-align: right; font-size: 8px; font-family: calibri;">
                                {{ $headData ? number_format($headData['payable_amount'] ?? 0, 2) : '' }}
                            </td>
                        @endif

                        <td style="text-align: right; font-size: 8px; font-family: calibri;">
                            {{ $headData ? number_format($headData['paid_amount'] ?? 0, 2) : '' }}
                        </td>

                        <td style="text-align: right; font-size: 8px; font-family: calibri;">
                            {{ $headData ? number_format($headData['remaining_amount'] ?? 0, 2) : '' }}
                        </td>
                    @endforeach

                    <td style="text-align: right; font-size: 8px; font-family: calibri;">
                        {{ number_format($challanData['total'] ?? 0, 2) }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ !empty($challanData['challan_status']) ? $challanData['challan_status'] : '-' }}
                    </td>

                    <td style="text-align: center; font-size: 8px; font-family: calibri;">
                        {{ $student->StudentRegistration->student_status ?? '-' }}
                    </td>
                </tr>
            @endforeach

            {{-- Branch Total --}}
            <tr style="font-weight:bold; background: gray;">
                <td colspan="8"
                    style="text-align:center; font-size: 8px; background-color:gray; font-family: calibri; border: 1px solid #000;">
                    Branch Total
                </td>

                @foreach($headNames as $headId => $head)
                    @php
                        $headTotal = $branchHeadTotals[$branchId][$headId] ?? [];
                    @endphp

                    @if($isDetailReport)
                        <td style="text-align:right; font-size:8px; background-color:gray; font-family:calibri; border:1px solid #000;">
                            {{ number_format($headTotal['base_amount'] ?? 0, 2) }}
                        </td>

                        <td style="text-align:right; font-size:8px; background-color:gray; font-family:calibri; border:1px solid #000;">
                            {{ number_format($headTotal['discount_amount'] ?? 0, 2) }}
                        </td>

                        <td style="text-align:right; font-size:8px; background-color:gray; font-family:calibri; border:1px solid #000;">
                            {{ number_format($headTotal['payable_amount'] ?? 0, 2) }}
                        </td>
                    @endif

                    <td style="text-align:right; font-size:8px; background-color:gray; font-family:calibri; border:1px solid #000;">
                        {{ number_format($headTotal['paid_amount'] ?? 0, 2) }}
                    </td>

                    <td style="text-align:right; font-size:8px; background-color:gray; font-family:calibri; border:1px solid #000;">
                        {{ number_format($headTotal['remaining_amount'] ?? 0, 2) }}
                    </td>
                @endforeach

                <td style="text-align: right; font-size: 8px; font-family: calibri; background-color:gray; border: 1px solid #000;">
                    {{ number_format($branchTotals[$branchId] ?? 0, 2) }}
                </td>

                <td style="background-color:gray; border:1px solid #000;"></td>
                <td style="background-color:gray; border:1px solid #000;"></td>
            </tr>
        @endforeach

        {{-- Spacer rows --}}
        <tr>
            <td colspan="{{ $totalColumns }}" style="height: 10px; border: none;"></td>
        </tr>

        <tr>
            <td colspan="{{ $totalColumns }}" style="height: 10px; border: none;"></td>
        </tr>

        <tr>
            <td colspan="{{ $totalColumns }}" style="height: 10px; border: none;"></td>
        </tr>

        {{-- Grand Total --}}
        <tr style="font-weight:bold; background: gray; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">

            <td colspan="8"
                style="text-align:center; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">
                Grand Total
            </td>

            @foreach($headNames as $headId => $head)
                @php
                    $headTotal = $grandHeadTotals[$headId] ?? [];
                @endphp

                @if($isDetailReport)
                    <td style="text-align:right; background-color:gray; font-size:8px; font-family:calibri; font-weight:bold; border:2px solid black; border-top:6px double black; border-bottom:6px double black;">
                        {{ number_format($headTotal['base_amount'] ?? 0, 2) }}
                    </td>

                    <td style="text-align:right; background-color:gray; font-size:8px; font-family:calibri; font-weight:bold; border:2px solid black; border-top:6px double black; border-bottom:6px double black;">
                        {{ number_format($headTotal['discount_amount'] ?? 0, 2) }}
                    </td>

                    <td style="text-align:right; background-color:gray; font-size:8px; font-family:calibri; font-weight:bold; border:2px solid black; border-top:6px double black; border-bottom:6px double black;">
                        {{ number_format($headTotal['payable_amount'] ?? 0, 2) }}
                    </td>
                @endif

                <td style="text-align:right; background-color:gray; font-size:8px; font-family:calibri; font-weight:bold; border:2px solid black; border-top:6px double black; border-bottom:6px double black;">
                    {{ number_format($headTotal['paid_amount'] ?? 0, 2) }}
                </td>

                <td style="text-align:right; background-color:gray; font-size:8px; font-family:calibri; font-weight:bold; border:2px solid black; border-top:6px double black; border-bottom:6px double black;">
                    {{ number_format($headTotal['remaining_amount'] ?? 0, 2) }}
                </td>
            @endforeach

            <td style="text-align: right; background-color:gray; font-size: 8px; font-family: calibri; font-weight:bold; background: grey; border: 2px solid black; border-collapse: collapse; border-top: 6px double black; border-bottom: 6px double black;">
                {{ number_format($grandTotal ?? 0, 2) }}
            </td>

            <td style="background-color:gray; border:2px solid black; border-top:6px double black; border-bottom:6px double black;"></td>
            <td style="background-color:gray; border:2px solid black; border-top:6px double black; border-bottom:6px double black;"></td>
        </tr>

        @include('student.exports.footer')
    </tbody>
</table>
