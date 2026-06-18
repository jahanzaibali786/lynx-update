<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Strength Report</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;

            /* IMPORTANT FIX */
            margin: 0;
            padding: 0;
        }

        body {
            padding-right: 10px !important;
            padding-right: 5px;
            /* extra right breathing space */
        }

        .report-wrapper {
            /* width: 100%; */
            padding: 25px 50px;
            /* IMPORTANT */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            font-size: 8px;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            word-break: break-word;
        }

        .thead-main th {
            background-color: #646464;
            color: #fff;
            font-weight: bold;
        }

        .row-branch td {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .row-data td:nth-child(3) {
            text-align: left;
        }

        .row-branch-total td {
            background-color: #b4b4b4;
            font-weight: bold;
        }

        .row-branch-total td:first-child {
            text-align: left;
        }

        .row-grand-total td {
            background-color: #cfcfcf;
            font-weight: bold;
        }

        .row-grand-total td:first-child {
            text-align: left;
        }

        .row-grand-total td:last-child {
            background-color: #adadad;
        }

        .col-sr {
            width: 24px;
        }
    </style>
</head>

<body>

    <div class="report-wrapper">
        <div class="header" style="width:100%; position:relative; text-align:left;">

            <!-- Center Content -->
            <div>
                <p style="margin:0;">
                    <img src="{{ public_path('assets/images/lynxheadertext.png') }}" style="max-height:45px;">
                </p>
                <br>
                <p style="margin:0; font-weight:600; font-size:12px;">
                    {{ request()->get('branches') ? @$branches[request()->get('branches')] : 'All Branches' }}
                </p>
                <br>
                <p style="margin:0; font-weight:600; font-size:14px;">
                    {{ @$report_name }}
                </p>
            </div>

            <!-- Right Logo -->
            <div style="position:absolute; right:0; top:0;">
                <img src="{{ public_path('assets/images/lynx2.jpg') }}" style="max-width:90px; max-height:90px;">
            </div>

        </div>


        <br>
        <table>
            <thead>

                {{-- ── FIRST HEADER ROW ── --}}
                <tr class="thead-main">
                    <th rowspan="2" class="col-sr">Sr</th>
                    <th rowspan="2" class="col-sr">B.Sr#</th>
                    <th rowspan="2" style="text-align:center; min-width:90px;">CLASS</th>

                    <th colspan="{{ $sections->count() }}" style="text-align:center;">
                        SECTIONS
                    </th>

                    <th rowspan="2">TOTAL</th>
                </tr>

                {{-- ── SECOND HEADER ROW (sections only) ── --}}
                <tr class="thead-main">
                    @foreach ($sections as $section)
                        <th>{{ $section->name }}</th>
                    @endforeach
                </tr>

            </thead>

            <tbody>

                @foreach ($report as $branchData)
                    {{-- ── BRANCH LABEL ── --}}
                    <tr class="row-branch">
                        <td colspan="{{ 4 + $sections->count() }}" style="padding-left:5px; text-align: left">
                            {{ $branchData['branch'] }}
                        </td>
                    </tr>

                    {{-- ── CLASS ROWS ── --}}
                    @foreach ($branchData['rows'] as $row)
                        <tr class="row-data">
                            <td class="col-sr">{{ $loop->parent->iteration }}</td>
                            <td class="col-sr">{{ $loop->iteration }}</td>
                            <td style="text-align:left; padding-left:4px;">{{ $row['class'] }}</td>

                            @foreach ($sections as $section)
                                <td>{{ $row['sections'][$section->id] ?? 0 }}</td>
                            @endforeach

                            <td><strong>{{ $row['total'] }}</strong></td>
                        </tr>
                    @endforeach

                    {{-- ── BRANCH TOTAL ── --}}
                    <tr class="row-branch-total">
                        <td colspan="3" style="padding-left:4px;">Branch Total</td>

                        @foreach ($sections as $section)
                            <td>{{ $branchData['totals'][$section->id] ?? 0 }}</td>
                        @endforeach

                        <td>{{ $branchData['totals']['total'] }}</td>
                    </tr>
                @endforeach

                {{-- ── GRAND TOTAL ── --}}
                <tr class="row-grand-total">
                    <td colspan="3" style="padding-left:4px;">GRAND TOTAL</td>

                    @foreach ($sections as $section)
                        <td>{{ $grandTotals[$section->id] ?? 0 }}</td>
                    @endforeach

                    <td style="background-color:#adadad;">{{ $grandTotals['total'] }}</td>
                </tr>

            </tbody>
        </table>
        <br><br><br><br>
        {{-- <div style="width: 100%; position: relative; display: table; font-size: 10px;">
            <div style="display: table-cell; width: 33%; text-align: center; vertical-align: middle;">
                House No 931 Street No 91 I-8/4, Islamabad,
            </div>
            <div style="display: table-cell; width: 33%; text-align: center; vertical-align: middle;"><u>
                    <?php echo 'Generated at: ' . now(); ?></u>
            </div>
            <div style="display: table-cell; width: 33%; text-align: center; vertical-align: middle;"><u>
                    Generated by: {{ \Auth::user()->name }}</u>
            </div>
        </div> --}}
    </div>

</body>

</html>
