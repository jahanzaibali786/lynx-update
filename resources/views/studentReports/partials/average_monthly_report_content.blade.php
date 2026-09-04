<style>
    .amr-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        font-size: 11px;
    }

    .amr-table th,
    .amr-table td {
        border: 1px solid #ffffff;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .amr-left {
        text-align: left !important;
    }

    .amr-title {
        font-family: Edwardian Script ITC;
        font-size: 3rem;
        text-align: center;
        margin: 0;
    }

    .amr-report {
        font-size: 1rem;
        text-align: center;
        font-weight: 800;
        margin: 0;
    }

    .amr-total td {
        font-weight: 700;
    }
</style>

<div class="container card">

    <div style="width: 100%; text-align: center;">
        <p class="amr-title">
            <b>The Lynx School</b>
        </p>
    </div>

    <div style="width: 100%; text-align: center;">
        <p style="font-size:1rem; text-align: center; font-weight: 800;">
            <b>Branch Name:</b> {{ $branchName }}
        </p>
    </div>

    <div style="width: 100%; text-align: center;">
        <p class="amr-report">
            {{ $reportName }}
        </p>
    </div>

    <div
        style="
            width:100%;
            overflow:hidden;
            margin-bottom: 12px;
        "
    >
        <p style="width: 34%; float:left;">
            <b>From:</b> {{ $fromLabel }}
        </p>

        <p style="width: 34%; float:left;"></p>

        <p
            style="
                width: 30%;
                float:left;
                padding-left:100px;
            "
        >
            <b>To:</b> {{ $toLabel }}
        </p>
    </div>

    <div style="overflow-x:auto; width:100%;">

        <table class="amr-table mt-4">

            <thead class="table_heads">

                {{-- =========================================
                     YEAR HEADER
                ========================================== --}}
                <tr>

                    <th rowspan="3">
                        SR
                    </th>

                    <th
                        rowspan="3"
                        class="amr-left"
                    >
                        BRANCH NAME
                    </th>

                    @foreach ($yearGroups as $year => $groupMonths)

                        <th
                            colspan="{{ count($groupMonths) * 3 }}"
                        >
                            {{ $year }}
                        </th>

                    @endforeach

                </tr>

                {{-- =========================================
                     MONTH HEADER
                ========================================== --}}
                <tr>

                    @foreach ($yearGroups as $year => $groupMonths)

                        @foreach ($groupMonths as $month)

                            <th colspan="3">
                                {{ $month['month_label'] }}
                            </th>

                        @endforeach

                    @endforeach

                </tr>

                {{-- =========================================
                     MONTH COLUMN HEADERS
                ========================================== --}}
                <tr>

                    @foreach ($months as $month)

                        <th>
                            AVG.FEE
                        </th>

                        <th>
                            TOTAL STUDENTS
                        </th>

                        <th>
                            DISCOUNT%
                        </th>

                    @endforeach

                </tr>

            </thead>

            <tbody>

                {{-- =========================================
                     BRANCH DATA
                ========================================== --}}
                @forelse ($reportRows as $index => $row)

                    <tr>

                        <td>
                            {{ $index + 1 }}
                        </td>

                        <td class="amr-left">
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
                                 * Total students is based on challan count.
                                 *
                                 * Prefer total_students from updated
                                 * controller and use head_count as fallback.
                                 */
                                $studentCount =
                                    (int) (
                                        $cell['total_students']
                                        ?? $cell['head_count']
                                        ?? 0
                                    );
                            @endphp

                            {{-- AVG FEE --}}
                            <td>
                                {{
                                    number_format(
                                        (float) (
                                            $cell['avg_fee']
                                            ?? 0
                                        ),
                                        2
                                    )
                                }}
                            </td>

                            {{-- TOTAL STUDENTS / CHALLAN COUNT --}}
                            <td>
                                {{ $studentCount }}
                            </td>

                            {{-- DISCOUNT --}}
                            <td>
                                {{
                                    number_format(
                                        (float) (
                                            $cell['discount_percent']
                                            ?? 0
                                        ),
                                        2
                                    )
                                }}%
                            </td>

                        @endforeach

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="{{ 2 + (count($months) * 3) }}"
                        >
                            No data found
                        </td>

                    </tr>

                @endforelse

                {{-- =========================================
                     LYNX TOTAL
                ========================================== --}}
                <tr class="amr-total">

                    <td
                        colspan="2"
                        class="amr-left"
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
                        <td>
                            {{
                                number_format(
                                    (float) (
                                        $cell['avg_fee']
                                        ?? 0
                                    ),
                                    2
                                )
                            }}
                        </td>

                        {{-- TOTAL STUDENTS --}}
                        <td>
                            {{ $totalStudents }}
                        </td>

                        {{-- TOTAL DISCOUNT --}}
                        <td>
                            {{
                                number_format(
                                    (float) (
                                        $cell['discount_percent']
                                        ?? 0
                                    ),
                                    2
                                )
                            }}%
                        </td>

                    @endforeach

                </tr>

            </tbody>

        </table>

    </div>

</div>