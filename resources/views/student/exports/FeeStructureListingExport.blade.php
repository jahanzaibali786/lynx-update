@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr No') }}</th>
            <th>{{ __('Br Sr#') }}</th>
            <th>{{ __('Session') }}</th>
            <th>{{ __('Class') }}</th>
            @foreach ($heads as $head)
            <th>
                {{ $head->fee_head }}
            </th>
        @endforeach
        </tr>
    </thead>
    <tbody>
        @php $sr = 1; @endphp
        @foreach ($class_wise_fee as $branchId => $sessions)
            <tr>
                <td colspan="4" style="font-weight:bold; background:#bcbcbc; text-align:left; font-size: 12px; border: 2px solid black; border-right: none;">{{ $branches[$branchId] ?? 'All Branches' }}</td>
                <td colspan="{{ count($heads) }}" style="font-weight: bold; text-align: left; background-color:#bcbcbc; font-size: 12px; border: 2px solid black; border-left: none;" ></td>
            </tr>
            @php $branch_sr = 1; @endphp
            @foreach ($sessions as $sessionId => $students)
                @php $class_sr = 1; @endphp
                @foreach ($students->groupBy('class.name') as $className => $groupedStudents)
                    <tr>
                        <td>{{ $sr }}</td>
                        <td>{{ $branch_sr }}</td>
                        <td>{{ optional($groupedStudents->first()->session)->year ?? '' }}</td>
                        <td>{{ $className }}</td>
                        @foreach ($heads as $head)
    @php
        $amount = optional($groupedStudents->where('feehead.fee_head', $head->fee_head)->first())->amount ?? '0';
    @endphp
    <td>
        {{ $amount }}
    </td>
@endforeach
                    </tr>
                    @php $sr++; $branch_sr++; $class_sr++; @endphp
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
