@php
    $headNames = $heads->pluck('fee_head', 'id');
    $isDetailReport = in_array(request('export'), ['detail_excel', 'detail_pdf']);
    $columnsPerHead = $isDetailReport ? 5 : 2;

    // 9 student/info columns
    // + dynamic fee-head columns
    // + Amount, Challan Status, Student Status, Discount Policy
    $totalColumns = 13 + (count($headNames) * $columnsPerHead);
@endphp

<table>
    <thead>
        @include('student.exports.header')

        <tr>
            <th rowspan="2">{{ __('Sr No.') }}</th>
            <th rowspan="2">{{ __('B Sr No.') }}</th>
            <th rowspan="2">{{ __('Reg No #') }}</th>
            <th rowspan="2">{{ __('Roll No #') }}</th>
            <th rowspan="2">{{ __('Challan No #') }}</th>
            <th rowspan="2">{{ __('Billing Month') }}</th>
            <th rowspan="2">{{ __('Admission Date') }}</th>
            <th rowspan="2">{{ __('Class') }}</th>
            <th rowspan="2">{{ __('Student Name') }}</th>

            @foreach ($headNames as $head)
                <th colspan="{{ $columnsPerHead }}">{{ $head }}</th>
            @endforeach

            <th rowspan="2">{{ __('Amount') }}</th>
            <th rowspan="2">{{ __('Challan Status') }}</th>
            <th rowspan="2">{{ __('Student Status') }}</th>
            <th rowspan="2">{{ __('Discount Policy') }}</th>
        </tr>

        <tr>
            @foreach ($headNames as $head)
                @if ($isDetailReport)
                    <th>{{ __('Base Amount') }}</th>
                    <th>{{ __('Discount') }}</th>
                    <th>{{ __('Payable') }}</th>
                @endif
                <th>{{ __('Paid') }}</th>
                <th>{{ __('Remaining') }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @php $mainloop = 1; @endphp

        @forelse ($studentData as $branchId => $students)
            <tr class="branch-name-row">
                <td colspan="{{ $totalColumns }}">
                    <strong>{{ $branches[$branchId] ?? ($students->first()->branch->name ?? 'Unknown Branch') }}</strong>
                </td>
            </tr>

            @foreach ($students as $index => $student)
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
                        'discount_policy' => '',
                    ];
                @endphp

                <tr>
                    <td>{{ $mainloop++ }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->StudentRegistration->reg_no ?? $student->regId ?? '-' }}</td>
                    <td>{{ $student->enrollId ?? '-' }}</td>
                    <td>{{ $challanData['challan_no'] ?: '-' }}</td>
                    <td>{{ $challanData['fee_month'] ?: '-' }}</td>
                    <td>{{ !empty($student->adm_date) ? date('d M Y', strtotime($student->adm_date)) : '-' }}</td>
                    <td>{{ $student->class->name ?? '-' }}</td>
                    <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>

                    @foreach ($headNames as $headId => $head)
                        @php $headData = $challanData['heads'][$headId] ?? null; @endphp

                        @if ($isDetailReport)
                            <td>{{ $headData ? ($headData['base_amount'] ?? 0) : '' }}</td>
                            <td>{{ $headData ? ($headData['discount_amount'] ?? 0) : '' }}</td>
                            <td>{{ $headData ? ($headData['payable_amount'] ?? 0) : '' }}</td>
                        @endif

                        <td>{{ $headData ? ($headData['paid_amount'] ?? 0) : '' }}</td>
                        <td>{{ $headData ? ($headData['remaining_amount'] ?? 0) : '' }}</td>
                    @endforeach

                    <td>{{ $challanData['total'] ?? 0 }}</td>
                    <td>{{ $challanData['challan_status'] ?: '-' }}</td>
                    <td>{{ $student->StudentRegistration->student_status ?? '-' }}</td>
                    <td>{{ !empty($challanData['discount_policy']) ? $challanData['discount_policy'] : '-' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="9"><strong>Branch Total</strong></td>

                @foreach ($headNames as $headId => $head)
                    @php $headTotal = $branchHeadTotals[$branchId][$headId] ?? []; @endphp

                    @if ($isDetailReport)
                        <td>{{ $headTotal['base_amount'] ?? 0 }}</td>
                        <td>{{ $headTotal['discount_amount'] ?? 0 }}</td>
                        <td>{{ $headTotal['payable_amount'] ?? 0 }}</td>
                    @endif

                    <td>{{ $headTotal['paid_amount'] ?? 0 }}</td>
                    <td>{{ $headTotal['remaining_amount'] ?? 0 }}</td>
                @endforeach

                <td>{{ $branchTotals[$branchId] ?? 0 }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $totalColumns }}">No admission record found.</td>
            </tr>
        @endforelse

        <tr>
            <td colspan="9"><strong>Grand Total</strong></td>

            @foreach ($headNames as $headId => $head)
                @php $headTotal = $grandHeadTotals[$headId] ?? []; @endphp

                @if ($isDetailReport)
                    <td>{{ $headTotal['base_amount'] ?? 0 }}</td>
                    <td>{{ $headTotal['discount_amount'] ?? 0 }}</td>
                    <td>{{ $headTotal['payable_amount'] ?? 0 }}</td>
                @endif

                <td>{{ $headTotal['paid_amount'] ?? 0 }}</td>
                <td>{{ $headTotal['remaining_amount'] ?? 0 }}</td>
            @endforeach

            <td>{{ $grandTotal ?? 0 }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        @include('student.exports.footer')
    </tbody>
</table>
