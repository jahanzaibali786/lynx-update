@php
    $report_name =
        $reportName
        ?? $report_name
        ?? 'Average Monthly Fee & Discount Percentage Report';

    $branch =
        $branchName
        ?? $branch
        ?? '';

    $is_period = true;
    $is_signature = false;

    /*
     * 2 fixed columns:
     * SR
     * BRANCH NAME
     *
     * 3 columns for every month:
     * AVG.FEE
     * TOTAL STUDENTS
     * DISCOUNT%
     */
    $colspan = 2 + (count($months) * 3);
@endphp

<table
    class="datatable"
    style="
        width: 100%;
        border-collapse: collapse;
    "
>
    <thead>

        @include(
            'student.exports.header',
            compact(
                'branch',
                'report_name',
                'is_period',
                'colspan',
                'date_from',
                'date_to'
            )
        )

        {{-- ===============================================
             YEAR ROW
        =============================================== --}}
        <tr>

            <th
                rowspan="3"
                style="
                    border: 1px solid black;
                    background: #bfbfbf;
                    font-size: 8px;
                    font-family: Calibri;
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    padding: 4px;
                "
            >
                SR
            </th>

            <th
                rowspan="3"
                style="
                    border: 1px solid black;
                    background: #bfbfbf;
                    font-size: 8px;
                    font-family: Calibri;
                    font-weight: bold;
                    text-align: left;
                    vertical-align: middle;
                    padding: 4px;
                "
            >
                BRANCH NAME
            </th>

            @foreach ($yearGroups as $year => $groupMonths)

                <th
                    colspan="{{ count($groupMonths) * 3 }}"
                    style="
                        border: 1px solid black;
                        background: #bfbfbf;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ $year }}
                </th>

            @endforeach

        </tr>

        {{-- ===============================================
             MONTH ROW
        =============================================== --}}
        <tr>

            @foreach ($yearGroups as $year => $groupMonths)

                @foreach ($groupMonths as $month)

                    <th
                        colspan="3"
                        style="
                            border: 1px solid black;
                            background: #e9e7e7;
                            font-size: 8px;
                            font-family: Calibri;
                            font-weight: bold;
                            text-align: center;
                            vertical-align: middle;
                            padding: 4px;
                        "
                    >
                        {{ $month['month_label'] }}
                    </th>

                @endforeach

            @endforeach

        </tr>

        {{-- ===============================================
             COLUMN NAME ROW
        =============================================== --}}
        <tr>

            @foreach ($months as $month)

                <th
                    style="
                        border: 1px solid black;
                        background: #e9e7e7;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    AVG.FEE
                </th>

                <th
                    style="
                        border: 1px solid black;
                        background: #e9e7e7;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    TOTAL STUDENTS
                </th>

                <th
                    style="
                        border: 1px solid black;
                        background: #e9e7e7;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    DISCOUNT%
                </th>

            @endforeach

        </tr>

    </thead>

    <tbody>

        {{-- ===============================================
             BRANCH ROWS
        =============================================== --}}
        @forelse ($reportRows as $index => $row)

            <tr>

                {{-- SR --}}
                <td
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ $index + 1 }}
                </td>

                {{-- BRANCH --}}
                <td
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        text-align: left;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ $row['branch_name'] }}
                </td>

                @foreach ($months as $month)

                    @php
                        $cell =
                            $row['months'][$month['key']]
                            ?? [
                                'avg_fee' => 0,
                                'total_students' => 0,
                                'head_count' => 0,
                                'discount_percent' => 0,
                            ];

                        /*
                         * Prefer explicit total_students field.
                         *
                         * Fall back to head_count so this will
                         * also work with the controller version
                         * where challan count is stored there.
                         */
                        $studentCount =
                            (int) (
                                $cell['total_students']
                                ?? $cell['head_count']
                                ?? 0
                            );
                    @endphp

                    {{-- AVG FEE --}}
                    <td
                        style="
                            border: 1px solid black;
                            font-size: 8px;
                            font-family: Calibri;
                            text-align: right;
                            vertical-align: middle;
                            padding: 4px;
                        "
                    >
                        {{ number_format(
                            (float) (
                                $cell['avg_fee']
                                ?? 0
                            ),
                            2
                        ) }}
                    </td>

                    {{-- TOTAL STUDENTS / CHALLAN COUNT --}}
                    <td
                        style="
                            border: 1px solid black;
                            font-size: 8px;
                            font-family: Calibri;
                            text-align: center;
                            vertical-align: middle;
                            padding: 4px;
                        "
                    >
                        {{ $studentCount }}
                    </td>

                    {{-- DISCOUNT --}}
                    <td
                        style="
                            border: 1px solid black;
                            font-size: 8px;
                            font-family: Calibri;
                            text-align: right;
                            vertical-align: middle;
                            padding: 4px;
                        "
                    >
                        {{ number_format(
                            (float) (
                                $cell['discount_percent']
                                ?? 0
                            ),
                            2
                        ) }}%
                    </td>

                @endforeach

            </tr>

        @empty

            <tr>

                <td
                    colspan="{{ $colspan }}"
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    No data found
                </td>

            </tr>

        @endforelse

        {{-- ===============================================
             LYNX TOTAL
        =============================================== --}}
        <tr
            style="
                font-weight: bold;
                background: #e9e7e7;
            "
        >

            <td
                colspan="2"
                style="
                    border: 1px solid black;
                    font-size: 8px;
                    font-family: Calibri;
                    font-weight: bold;
                    text-align: left;
                    vertical-align: middle;
                    padding: 4px;
                "
            >
                LYNX TOTAL
            </td>

            @foreach ($months as $month)

                @php
                    $cell =
                        $grandTotals[$month['key']]
                        ?? [
                            'avg_fee' => 0,
                            'total_students' => 0,
                            'discount_percent' => 0,
                        ];

                    $totalStudents =
                        (int) (
                            $cell['total_students']
                            ?? 0
                        );
                @endphp

                {{-- TOTAL AVG FEE --}}
                <td
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: right;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ number_format(
                        (float) (
                            $cell['avg_fee']
                            ?? 0
                        ),
                        2
                    ) }}
                </td>

                {{-- TOTAL STUDENTS --}}
                <td
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ $totalStudents }}
                </td>

                {{-- TOTAL DISCOUNT --}}
                <td
                    style="
                        border: 1px solid black;
                        font-size: 8px;
                        font-family: Calibri;
                        font-weight: bold;
                        text-align: right;
                        vertical-align: middle;
                        padding: 4px;
                    "
                >
                    {{ number_format(
                        (float) (
                            $cell['discount_percent']
                            ?? 0
                        ),
                        2
                    ) }}%
                </td>

            @endforeach

        </tr>

        @include(
            'student.exports.footer',
            compact(
                'is_signature'
            )
        )

    </tbody>
</table>