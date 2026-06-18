<table class="{{ $tableClass ?? '' }}" style="width:100%;">
    <thead class="{{ $theadClass ?? '' }}">
        <tr class="{{ $headerClass ?? '' }}">
            <th>Sr#</th>
            <th>Roll#</th>
            <th>Student Name</th>
            <th>Father Name</th>
            <th>Session From</th>
            <th>Session To</th>
            <th>Branch From</th>
            <th>Branch To</th>
            <th>Class From</th>
            <th>Class To</th>
            <th>Section From</th>
            <th>Section To</th>
            <th>Promotion Date</th>
        </tr>
    </thead>
    <tbody>
        @php $sr = 1; @endphp
        @forelse ($report as $groupKey => $rows)
            @php $first = $rows->first(); @endphp
            <tr>
                <td colspan="13" style="background:#bcbcbc; font-weight:bold; text-align:left;">
                    {{ optional($first->branchFrom)->name ?? '-' }}
                    @if (($params['promotion_type'] ?? 'promotion') === 'branch_promotion')
                        to {{ optional($first->branchTo)->name ?? '-' }}
                    @endif
                    |
                    {{ optional($first->classFrom)->name ?? '-' }} to {{ optional($first->classTo)->name ?? '-' }}
                    |
                    Total: {{ $rows->count() }}
                </td>
            </tr>
            @foreach ($rows as $row)
                @php $registration = optional($row->student)->StudentRegistration; @endphp
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>{{ optional($row->student)->enrollId ?? '-' }}</td>
                    <td>{{ optional($registration)->stdname ?? '-' }}</td>
                    <td>{{ optional($registration)->fathername ?? '-' }}</td>
                    <td>{{ optional($row->prevSession)->year ?? '-' }}</td>
                    <td>{{ optional($row->newSession)->year ?? '-' }}</td>
                    <td>{{ optional($row->branchFrom)->name ?? '-' }}</td>
                    <td>{{ optional($row->branchTo)->name ?? '-' }}</td>
                    <td>{{ optional($row->classFrom)->name ?? '-' }}</td>
                    <td>{{ optional($row->classTo)->name ?? '-' }}</td>
                    <td>{{ optional($row->sectionFrom)->name ?? '-' }}</td>
                    <td>{{ optional($row->sectionTo)->name ?? '-' }}</td>
                    <td>{{ $row->promotion_date ? \Carbon\Carbon::parse($row->promotion_date)->format('d-M-Y') : '-' }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="13" style="text-align:center;">No promotion record found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
