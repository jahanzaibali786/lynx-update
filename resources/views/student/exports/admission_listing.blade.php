@php
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
            <th>{{ __('Admission Date') }}</th>
            <th>{{ __('Class') }}</th>
            <th>{{ __('Student Name') }}</th>

            @foreach ($headNames as $head)
                <th>{{ $head }}</th>
            @endforeach

            <th>{{ __('Amount') }}</th>
            <th>{{ __('Adm. Status') }}</th>
        </tr>
    </thead>

    <tbody>
        @php
            $mainloop = 1;
        @endphp

        @forelse ($studentData as $branchId => $students)
            @foreach ($students as $index => $student)
                @php
                    $studentKey = $student->regId;

                    $challanData = $studentChallanData[$studentKey] ?? [
                        'challan_no' => '',
                        'challan_id' => '',
                        'heads' => [],
                        'total' => 0,
                    ];
                @endphp

                <tr>
                    <td>{{ $mainloop++ }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->StudentRegistration->reg_no ?? $student->regId ?? '-' }}</td>
                    <td>{{ $student->enrollId ?? '-' }}</td>
                    <td>{{ $challanData['challan_no'] ?: '-' }}</td>
                    <td>{{ !empty($student->adm_date) ? date('d M Y', strtotime($student->adm_date)) : '-' }}</td>
                    <td>{{ $student->class->name ?? '-' }}</td>
                    <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>

                    @foreach ($headNames as $headId => $head)
                        <td>
                            {{ isset($challanData['heads'][$headId]) ? $challanData['heads'][$headId]['amount'] : '' }}
                        </td>
                    @endforeach

                    <td>{{ $challanData['total'] ?? 0 }}</td>
                    <td>{{ $student->StudentRegistration->student_status ?? '-' }}</td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Branch Total</td>

                @foreach ($headNames as $headId => $head)
                    <td>{{ $branchHeadTotals[$branchId][$headId] ?? 0 }}</td>
                @endforeach

                <td>{{ $branchTotals[$branchId] ?? 0 }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 10 + count($headNames) }}">No admission record found.</td>
            </tr>
        @endforelse

        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Grand Total</td>
            
            @foreach ($headNames as $headId => $head)
                <td>{{ $grandHeadTotals[$headId] ?? 0 }}</td>
            @endforeach

            <td>{{ $grandTotal ?? 0 }}</td>
            <td></td>
        </tr>

        @include('student.exports.footer')
    </tbody>
</table>