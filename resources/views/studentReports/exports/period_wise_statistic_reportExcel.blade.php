@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>Sr</th>
            <th>Br.Sr</th>
            <th>Year</th>
            <th>JAN</th>
            <th>FEB</th>
            <th>MAR</th>
            <th>APR</th>
            <th>MAY</th>
            <th>JUN</th>
            <th>JUL</th>
            <th>AUG</th>
            <th>SEP</th>
            <th>OCT</th>
            <th>NOV</th>
            <th>DEC</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $brSr = 1;
            $grouped = collect($data)->groupBy(function($item) {
                return \Carbon\Carbon::parse($item['month_year'])->format('Y');
            });
        @endphp
        @foreach ($grouped as $year => $months)
            <tr>
                <td>{{ $sr++ }}</td>
                <td>{{ $brSr++ }}</td>
                <td>{{ $year }}</td>
                @php
                    $monthTotals = array_fill(1, 12, 0);
                    foreach ($months as $m) {
                        $monthNum = \Carbon\Carbon::parse($m['month_year'])->month;
                        $monthTotals[$monthNum] +=
                            ($m['admissions'] ?? 0)
                            + ($m['withdrawals'] ?? 0)
                            + ($m['transfers_in'] ?? 0)
                            + ($m['transfers_out'] ?? 0)
                            + ($m['passing_out'] ?? 0);
                    }
                @endphp
                @for ($i = 1; $i <= 12; $i++)
                    <td>{{ $monthTotals[$i] > 0 ? $monthTotals[$i] : '' }}</td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')