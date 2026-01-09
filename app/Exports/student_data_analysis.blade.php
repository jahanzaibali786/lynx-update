<table class="datatable" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; width:100%;">

    @include('student.exports.header')

    @php
        // Prepare Lynx totals accumulator per period
        $lynxTotals = [];
        foreach ($periods as $i => $p) {
            $lynxTotals[$i] = ['reg' => 0, 'adm' => 0, 'tf_in' => 0, 'tf_out' => 0, 'wd' => 0, 'gain' => 0];
        }
    @endphp

    @foreach ($branchesToLoop as $branchId => $branchName)
       

        {{-- Header Row 1: period labels --}}
        <tr>
            <th rowspan="2"
                style="border:1px solid black;
                       background-color: gray;
                       font-weight: 700;
                       text-align: center;
                        font-family: Arial,Helvetica,sans-serif;
                       font-size:8px;">
                CLASS
            </th>
            @foreach ($periods as $p)
                <th colspan="6"
                    style="border:1px solid black;
                           background-color: gray;
                           font-weight: 700;
                           text-align: center;
                           font-family: Arial,Helvetica,sans-serif;
                           font-size:8px;">
                    {{ $p['label'] }}
                </th>
            @endforeach
        </tr>

        {{-- Header Row 2: REG / ADM / TF. IN / TF. OUT / WD / NET GAIN --}}
        <tr>
            @foreach ($periods as $_)
                @foreach (['REG', 'ADM', 'TF. IN', 'TF. OUT', 'WD', 'NET GAIN'] as $hdr)
                    <th
                        style="border:1px solid black;
                               background-color: lightgray;
                               font-weight: 600;
                               text-align: center;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $hdr }}
                    </th>
                @endforeach
            @endforeach
        </tr>

         {{-- Branch Title --}}
         <tr>
            <td colspan="{{ 1 + count($periods) * 6 }}"
                style="border:1px solid black;
                       background-color: rgb(212, 209, 209);
                       font-weight: bold;
                       text-align: left;
                       font-family: Arial,Helvetica,sans-serif;
                       padding:8px;
                       font-size:8px;">
                {{ $branchName }}
            </td>
        </tr>

        {{-- Class Rows --}}
        @foreach ($classesByBranch[$branchId] as $classId => $classModel)
            <tr>
                <td
                    style="border:1px solid black;
                           font-weight: bold;
                           text-align: left;
                           font-family: Arial,Helvetica,sans-serif;
                           font-size:8px;">
                    {{ $classModel->name }}
                </td>

                @foreach ($periods as $i => $p)
                    @php
                        $c = $stats[$branchId][$classId][$i] ?? ['reg' => 0, 'adm' => 0, 'tf_in' => 0, 'tf_out' => 0, 'wd' => 0, 'gain' => 0];
                    @endphp
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-weight: bold;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['reg'] }}
                    </td>
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-weight: bold;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['adm'] }}
                    </td>
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-weight: bold;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['tf_in'] }}
                    </td>
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-weight: bold;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['tf_out'] }}
                    </td>
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-weight: bold;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['wd'] }}
                    </td>
                    <td
                        style="border:1px solid black;
                               text-align: center;
                               font-family: Arial,Helvetica,sans-serif;
                               font-size:8px;">
                        {{ $c['gain'] }}
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- Branch Total Row --}}
        <tr>
            <td
                style="border:1px solid black;
                       background-color: lightgray;
                       font-family: Arial,Helvetica,sans-serif;
                       font-weight: bold;
                       text-align: right;
                       font-size:8px;">
                Branch Total
            </td>
            @foreach ($periods as $i => $p)
                @php
                    $sumReg = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['reg'] ?? 0,
                    );
                    $sumAdm = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['adm'] ?? 0,
                    );
                    $sumTfIn = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['tf_in'] ?? 0,
                    );
                    $sumTfOut = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['tf_out'] ?? 0,
                    );
                    $sumWd = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['wd'] ?? 0,
                    );
                    $sumGain = collect($classesByBranch[$branchId]->keys())->sum(
                        fn($cid) => $stats[$branchId][$cid][$i]['gain'] ?? 0,
                    );

                    // Accumulate into Lynx totals
                    $lynxTotals[$i]['reg'] += $sumReg;
                    $lynxTotals[$i]['adm'] += $sumAdm;
                    $lynxTotals[$i]['tf_in'] += $sumTfIn;
                    $lynxTotals[$i]['tf_out'] += $sumTfOut;
                    $lynxTotals[$i]['wd'] += $sumWd;
                    $lynxTotals[$i]['gain'] += $sumGain;
                @endphp

                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumReg }}
                </td>
                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumAdm }}
                </td>
                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumTfIn }}
                </td>
                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumTfOut }}
                </td>
                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumWd }}
                </td>
                <td
                    style="border:1px solid black;
                           background-color: lightgray;
                           font-family: Arial,Helvetica,sans-serif;
                           font-weight: bold;
                           text-align: center;
                           font-size:8px;">
                    {{ $sumGain }}
                </td>
            @endforeach
        </tr>
    @endforeach
    <tr></tr>
    <tr></tr>
    {{-- Lynx Total Header --}}
    {{-- <tr>
        <td colspan="{{ 1 + count($periods) * 6 }}"
            style="border:1px solid black;
                   background-color: gray;
                   color: black;
                   font-weight: bold;
                   text-align: left;
                   font-family: Arial,Helvetica,sans-serif;
                   padding:8px;
                   font-size:8px;">
            LYNX TOTAL
        </td>
    </tr> --}}
    {{-- Lynx Total Row --}}
    <tr>
        <td
            style="border:1px solid black;
            border-top: 4px double black;
                   background-color: gray;
                   color: black;
                   font-weight: bold;
                   font-family: Arial,Helvetica,sans-serif;
                   text-align: right;
                   font-size:8px;">
            G.TOTAL
        </td>
        @foreach ($periods as $i => $p)
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['reg'] }}
            </td>
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['adm'] }}
            </td>
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['tf_in'] }}
            </td>
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['tf_out'] }}
            </td>
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['wd'] }}
            </td>
            <td
                style="border:1px solid black;
                       background-color: gray;
                       color: black;
                       font-weight: bold;
                       font-family: Arial,Helvetica,sans-serif;
                       text-align: center;
                       font-size:8px;">
                {{ $lynxTotals[$i]['gain'] }}
            </td>
        @endforeach
    </tr>
</table>

@include('student.exports.footer')