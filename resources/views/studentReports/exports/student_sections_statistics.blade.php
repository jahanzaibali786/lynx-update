@include('student.exports.header')

<div class="card p-4" style="margin-top: 150px;">
    <div class="mt-4">
        <table class="">
            <thead class="table_heads sticky-headerNew">
                {{-- FIRST ROW --}}
                <tr style="background-color: #100773; color: white;">
                    <th rowspan="2">Sr</th>
                    <th rowspan="2">B.Sr#</th>
                    <th rowspan="2">CLASS</th>
                    <th style="text-align: center;" colspan="{{ $sections->count() }}">
                        SECTIONS
                    </th>
                    <th rowspan="2">TOTAL</th>
                </tr>
                {{-- SECOND ROW (ONLY SECTIONS) --}}
                <tr style="background-color: #100773; color: white;">
                    @foreach ($sections as $section)
                        <th>{{ $section->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($report as $branchData)
                    {{-- BRANCH NAME --}}
                    <tr style="background:#eee; font-weight:bold;">
                        <td colspan="{{ 4 + $sections->count() }}">
                            {{ $branchData['branch'] }}
                        </td>
                    </tr>
                    {{-- CLASS ROWS --}}
                    @foreach ($branchData['rows'] as $row)
                        <tr>
                            <td>{{ $loop->parent->iteration }}</td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['class'] }}</td>
                            @foreach ($sections as $section)
                                <td>{{ $row['sections'][$section->id] ?? 0 }}</td>
                            @endforeach
                            <td><b>{{ $row['total'] }}</b></td>
                        </tr>
                    @endforeach
                    {{-- BRANCH TOTAL --}}
                    <tr style="font-weight:bold;">
                        <td colspan="3" style="background:#b4b4b4;">Branch Total</td>
                        @foreach ($sections as $section)
                            <td style="background:#b4b4b4;">
                                {{ $branchData['totals'][$section->id] }}
                            </td>
                        @endforeach
                        <td style="background:#b4b4b4;">
                            {{ $branchData['totals']['total'] }}
                        </td>
                    </tr>
                @endforeach
                {{-- GRAND TOTAL --}}
                <tr style="font-weight:bold;">
                    <td colspan="3" style="background:#cfcfcf;">GRAND TOTAL</td>
                    @foreach ($sections as $section)
                        <td style="background:#cfcfcf;">
                            {{ $grandTotals[$section->id] }}
                        </td>
                    @endforeach
                    <td style="background:#adadad;">
                        {{ $grandTotals['total'] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@include('student.exports.footer')