@php
    $colspan = 1 + count($months) * 3 + 4;
    $is_period = true;
    $date_from = $startDate->format('Y-m-d');
    $date_to = $endDate->format('Y-m-d');
@endphp
@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2" style="border:1px solid #000; text-align:center; background:#d3d3d3; font-weight:bold; font-size:10px; font-family:Calibri;">{{ __('Branch') }}</th>
            @foreach ($months as $month)
                <th colspan="3" style="border:1px solid #000; text-align:center; background:#d3d3d3; font-weight:bold; font-size:10px; font-family:Calibri;">
                    {{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }}
                </th>
            @endforeach
            <th colspan="4" style="border:1px solid #000; text-align:center; background:#d3d3d3; font-weight:bold; font-size:10px; font-family:Calibri;">{{ __('Total') }}</th>
        </tr>
        <tr>
            @foreach ($months as $month)
                <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('Adm') }}</th>
                <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('WD') }}</th>
                <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('PO') }}</th>
            @endforeach
            <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('Adm') }}</th>
            <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('WD') }}</th>
            <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('PO') }}</th>
            <th style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight:bold; font-size:8px; font-family:Calibri;">{{ __('Gains(+/-)') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $row)
            <tr>
                <td style="border:1px solid #D3D3D3; text-align:left; font-size:8px; font-family:Calibri;">{{ $row['branch_name'] }}</td>
                @foreach ($months as $month)
                    <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri;">{{ $row['months'][$month]['adm'] }}</td>
                    <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri;">{{ $row['months'][$month]['wd'] }}</td>
                    <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri;">{{ $row['months'][$month]['po'] }}</td>
                @endforeach
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri; font-weight:bold;">{{ $row['total_adm'] }}</td>
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri; font-weight:bold;">{{ $row['total_wd'] }}</td>
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri; font-weight:bold;">{{ $row['total_po'] }}</td>
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri; font-weight:bold;">
                    {{ $row['gains'] >= 0 ? '+' : '' }}{{ $row['gains'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ __('Total') }}</td>
            @foreach ($months as $month)
                <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals[$month]['adm'] }}</td>
                <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals[$month]['wd'] }}</td>
                <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals[$month]['po'] }}</td>
            @endforeach
            <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals['total_adm'] }}</td>
            <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals['total_wd'] }}</td>
            <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">{{ $grandTotals['total_po'] }}</td>
            <td style="border:1px solid #000; border-top:4px double #000; border-bottom:4px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">
                {{ $grandTotals['gains'] >= 0 ? '+' : '' }}{{ $grandTotals['gains'] }}
            </td>
        </tr>
    </tfoot>
</table>
@include('student.exports.footer')
