<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td colspan="14" style="text-align: center; font-family: 'Edwardian Script ITC'; font-size: 28pt; font-weight: bold; border: none;">
            The Lynx School
        </td>
    </tr>
    <tr>
        <td colspan="14" style="text-align: center; font-size: 14pt; font-weight: bold; border: none;">
            {{ $report_name }}
        </td>
    </tr>
    <tr>
        <td colspan="14" style="border: none; font-size: 10pt;">
            <b>Branch:</b> {{ $selectedBranchId && $selectedBranchId !== 'all' && isset($branches[$selectedBranchId]) ? $branches[$selectedBranchId] : 'All Branches' }}
            &nbsp;&nbsp;&nbsp;&nbsp;
            <b>Class:</b> {{ $selectedClassId && $selectedClassId !== 'all' && isset($classes[$selectedClassId]) ? $classes[$selectedClassId] : 'All Classes' }}
        </td>
    </tr>
    @if($filteredHeadPcts->isNotEmpty())
    <tr>
        <td colspan="14" style="border: none; font-size: 10pt;">
            <b>Filtered Heads:</b>
            @foreach($filteredHeadPcts as $headId => $pct)
                @php $headName = optional($feeHeads->firstWhere('id', $headId))->fee_head ?? 'Head#' . $headId; @endphp
                {{ $headName }}={{ $pct }}%
                @if(!$loop->last) | @endif
            @endforeach
        </td>
    </tr>
    @endif
</table>

<br>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #BFBFBF; font-size: 8pt; font-weight: bold; text-align: center;">
            <th style="border: 1px solid black; padding: 4px;">Sr#</th>
            <th style="border: 1px solid black; padding: 4px;">Bsr#</th>
            <th style="border: 1px solid black; padding: 4px;">Branch</th>
            <th style="border: 1px solid black; padding: 4px;">Roll No</th>
            <th style="border: 1px solid black; padding: 4px;">Student Name</th>
            <th style="border: 1px solid black; padding: 4px;">Father Name</th>
            <th style="border: 1px solid black; padding: 4px;">Class</th>
            <th style="border: 1px solid black; padding: 4px;">Section</th>
            <th style="border: 1px solid black; padding: 4px;">Fee Head</th>
            <th style="border: 1px solid black; padding: 4px;">Disc %</th>
            <th style="border: 1px solid black; padding: 4px;">Actual Fee</th>
            <th style="border: 1px solid black; padding: 4px;">Discount</th>
            <th style="border: 1px solid black; padding: 4px;">Payable</th>
            <th style="border: 1px solid black; padding: 4px;">Policy</th>
        </tr>
    </thead>
    <tbody>
        @php $sr = 1; @endphp
        @foreach ($groupedResults as $branchId => $branchStudents)
            @php $branchName = $branches[$branchId] ?? 'Branch #' . $branchId; @endphp
            <tr style="background-color: #e0e0e0; font-weight: bold; font-size: 8pt;">
                <td colspan="14" style="border: 1px solid black; padding: 2px;">{{ $branchName }}</td>
            </tr>
            @php $bsr = 1; @endphp
            @foreach ($branchStudents as $row)
                @php $student = $row['student']; @endphp
                @foreach ($row['items'] as $idx => $item)
                    <tr style="font-size: 8pt;">
                        @if ($idx === 0)
                            <td style="border: 1px solid black; padding: 2px; text-align: center;" rowspan="{{ count($row['items']) }}">{{ $sr++ }}</td>
                            <td style="border: 1px solid black; padding: 2px; text-align: center;" rowspan="{{ count($row['items']) }}">{{ $bsr++ }}</td>
                            <td style="border: 1px solid black; padding: 2px;" rowspan="{{ count($row['items']) }}">{{ $branchName }}</td>
                            <td style="border: 1px solid black; padding: 2px; text-align: center;" rowspan="{{ count($row['items']) }}">{{ $student->roll_no ?? optional($student->enrollment)->enrollId ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 2px;" rowspan="{{ count($row['items']) }}">{{ $student->stdname ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 2px;" rowspan="{{ count($row['items']) }}">{{ $student->fathername ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 2px; text-align: center;" rowspan="{{ count($row['items']) }}">{{ optional(optional($student->enrollment)->class)->name ?? optional($student->class)->name ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 2px; text-align: center;" rowspan="{{ count($row['items']) }}">{{ optional(optional($student->enrollment)->section)->name ?? '-' }}</td>
                        @endif
                        <td style="border: 1px solid black; padding: 2px;">{{ $item['head_name'] }}</td>
                        <td style="border: 1px solid black; padding: 2px; text-align: center;">{{ $item['discount_pct'] }}%</td>
                        <td style="border: 1px solid black; padding: 2px; text-align: right;">{{ number_format($item['actual_fee'], 2) }}</td>
                        <td style="border: 1px solid black; padding: 2px; text-align: right;">{{ number_format($item['discount_amount'], 2) }}</td>
                        <td style="border: 1px solid black; padding: 2px; text-align: right;">{{ number_format($item['payable'], 2) }}</td>
                        @if ($idx === 0)
                            <td style="border: 1px solid black; padding: 2px;" rowspan="{{ count($row['items']) }}">{{ $row['policy_name'] ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>

<br>
<p style="font-size: 10pt;"><strong>Total Students:</strong> {{ $totalStudents ?? 0 }}</p>
