@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2">
                Sr</th>
            <th rowspan="2">
                Br.Sr #</th>
            <th rowspan="2">
                Reg No</th>
            <th rowspan="2">
                Reg. Catg.</th>
            <th rowspan="2">
                Transfer Type</th>
            <th rowspan="2">
                Transfer Date</th>
            <th rowspan="2">
                Roll #</th>
            <th rowspan="2">
                Student Name</th>
            <th rowspan="2">
                Father Name</th>
            @if ($report_name == 'Transfer In Report')
                <th class="header-cell" colspan="2"
                    style="text-align: center; font-size: 8px; width: 245px; font-size: 10px; font-weight: bold; text-align: center; border: 6px solid black; background-color: #808080;">
                    Transfer IN</th>
                <th class="header-cell" colspan="2"
                    style="text-align: center; font-size: 8px; width: 245px; font-size: 10px; font-weight: bold; text-align: center; border: 6px solid black; background-color: #808080;">
                    Transfer OUT</th>
            @else
                <th class="header-cell" colspan="2"
                    style="text-align: center; font-size: 8px; width: 245px; font-size: 10px; font-weight: bold; text-align: center; border: 6px solid black; background-color: #808080;">
                    Transfer OUT</th>
                <th class="header-cell" colspan="2"
                    style="text-align: center; font-size: 8px; width: 245px; font-size: 10px; font-weight: bold; text-align: center; border: 6px solid black; background-color: #808080;">
                    Transfer IN</th>
            @endif

            <th rowspan="2">
                Transfer Fee</th>
            <th rowspan="2">
                Reason</th>
        </tr>
        <tr>
            <th class="header-cell"
                style="text-align: left; font-size: 8px; width: 165px; font-size: 10px; font-weight: bold; border: 6px solid black; background-color: #bebebe;">
                Branch</th>
            <th class="header-cell"
                style="text-align: left; font-size: 8px; width: 80px; font-size: 10px; font-weight: bold; border: 6px solid black; background-color: #bebebe;">
                Class</th>
            <th class="header-cell"
                style="text-align: left; font-size: 8px; width: 165px; font-size: 10px; font-weight: bold; border: 6px solid black; background-color: #bebebe;">
                Branch</th>
            <th class="header-cell"
                style="text-align: left; font-size: 8px; width: 80px; font-size: 10px; font-weight: bold; border: 6px solid black; background-color: #bebebe;">
                Class</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $branchSr = 1;
            $totalTransfers = 0;
            $totalTransferFee = 0;
            $branchTransferFee = 0;
            $branchTransferCount = 0;
            $previousBranchName = '';
        @endphp
        @foreach ($studenttransfer as $transfer)
            @if ($loop->first || $transfer->branchfrom->name != $previousBranchName)
                @if (!$loop->first)
                    {{-- Branch Total Row --}}
                    <tr>
                        <td colspan="13" class="total-cell-right"
                            style="border-top: 3px double black; border-bottom: 3px double black; background-color: #B8B8B8; font-size: 10px; font-weight: bold; text-align: center; padding-right: 10px;">
                            BRANCH TOTAL</td>
                        <td class="total-cell-center"
                            style="border: 2px double black; border-collapse: collapse; background-color: #B8B8B8; border-top: 3px double black; border-bottom: 3px double black; font-size: 8px; text-align: right; font-weight: bold;">
                            {{ number_format($branchTransferFee, 0) }}</td>
                        <td
                            style="border: 2px double black; border-collapse: collapse; background-color: #B8B8B8; border-top: 3px double black; border-bottom: 3px double black; font-size: 8px; text-align: center;">
                        </td>
                    </tr>
                    @php
                        $branchSr = 1;
                        $branchTransferFee = 0;
                        $branchTransferCount = 0;
                    @endphp
                @endif
                <tr>
                    <td colspan="15"
                        style="font-size: 10px; font-weight: bold; text-align: left; border: 2px solid #bebebe; border-collapse: collapse; background-color: #808080;">
                        {{ $transfer->branchfrom->name ?? '' }}</td>
                </tr>
            @endif

            <tr>
                <td>
                    {{ $sr }}</td>
                <td>
                    {{ $branchSr }}</td>
                <td>
                    {{ $transfer->enrollment->regId ?? '' }}</td>
                <td>
                    {{ $transfer->student->registeroption->name ?? '' }}</td>
                <td>
                    {($transfer->transfer_type ?? '')}
                </td>
                <td>
                    {{ !empty($transfer->transfer_date) ? date('d M Y', strtotime($transfer->transfer_date)) : '' }}
                </td>
                <td>
                    {{ $transfer->enrollment->enrollId ?? '' }}</td>
                <td>
                    {($transfer->student->stdname ?? '')}
                </td>
                <td>
                    {($transfer->student->fathername ?? '')}
                </td>
                @if ($report_name == 'Transfer In Report')
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->branchto->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->classto->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->branchfrom->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->classfrom->name ?? '')}
                    </td>
                @else
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->branchfrom->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->classfrom->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->branchto->name ?? '')}
                    </td>
                    <td class="data-cell" style="font-size: 8px; text-align: left; border: 2px solid #D3D3D3;">
                        {($transfer->classto->name ?? '')}
                    </td>
                @endif
                <td>
                    {{ number_format($transfer->transfer_fee ?? 0, 0) }}
                </td>
                <td>
                    {($transfer->status ?? '')}
                </td>
            </tr>
            @php
                $sr++;
                $branchSr++;
                $totalTransfers++;
                $totalTransferFee += $transfer->transfer_fee ?? 0;
                $branchTransferFee += $transfer->transfer_fee ?? 0;
                $branchTransferCount++;
                $previousBranchName = $transfer->branchfrom->name;
            @endphp
        @endforeach

        {{-- Last Branch Total Row --}}
        <tr>
            <td colspan="13" class="total-cell-right"
                style="border-top: 3px double black; border-bottom: 3px double black; background-color: #B8B8B8; font-size: 10px; font-weight: bold; text-align: center; padding-right: 10px;">
                BRANCH TOTAL</td>
            <td class="total-cell-center"
                style="border: 2px solid black; border-collapse: collapse; background-color: #B8B8B8; border-top: 3px double black; border-bottom: 3px double black; font-size: 8px; text-align: right; font-weight: bold;">
                {{ number_format($branchTransferFee, 0) }}</td>
            <td
                style="border: 2px solid black; border-collapse: collapse; background-color: #B8B8B8; border-top: 3px double black; border-bottom: 3px double black; font-size: 8px; text-align: center;">
            </td>
        </tr>

        {{-- Grand Total Row --}}
        {{-- <tr class="total-row" style="border: 2px solid black; border-collapse: collapse; background-color: #808080; border-top: 4px double black; border-bottom: 4px double black;">
                <td colspan="13" class="total-cell-right" style="border-top: 4px double black; border-bottom: 4px double black; background-color: #808080; font-size: 10px; font-weight: bold; text-align: right; padding-right: 10px;">GRAND TOTAL</td>
                <td class="total-cell-center" style="border: 2px solid black; border-collapse: collapse; background-color: #808080; border-top: 4px double black; border-bottom: 4px double black; font-size: 8px; text-align: right; font-weight: bold;">{{ number_format($totalTransferFee, 0) }}</td>
                <td style="border: 2px solid black; border-collapse: collapse; background-color: #808080; border-top: 4px double black; border-bottom: 4px double black; font-size: 8px; text-align: center;"></td>
            </tr> --}}
    </tbody>
</table>

@include('student.exports.footer')
