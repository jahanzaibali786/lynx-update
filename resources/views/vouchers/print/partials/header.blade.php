<tr>
    <td colspan="3" rowspan="4" style="height:80px; vertical-align:top;">
        @if(!empty($titleLogo))
            <img src="{{ $titleLogo }}" style="width:250px; height:70px;">
        @endif
    </td>
    <td colspan="3" style="height:22px;"></td>
    <td colspan="2" rowspan="5" style="text-align:right; vertical-align:top;">
        @if(!empty($headerLogo))
            <img src="{{ $headerLogo }}" style="width:120px !important; height:120px !important;">
        @endif
    </td>
</tr>
<tr>
    <td colspan="3" style="height:22px;"></td>
</tr>
<tr>
    <td colspan="3" style="height:22px;"></td>
</tr>
<tr>
    <td colspan="3" style="height:22px;"></td>
</tr>
<tr>
    <td colspan="3" style="font-weight:bold; font-size:14px; height:24px;">
        {{ $data['voucher_title'] ?? '' }}
    </td>
    <td colspan="2" style="height:24px;"></td>
    <td style="font-weight:bold; text-align:right; height:24px;">Date:</td>
    <td colspan="2" style="text-align:right; border-bottom:1px solid #000000; height:24px; white-space:nowrap;">
        {{ $data['date'] ?? '' }}
    </td>
</tr>
<tr>
    <td colspan="5" style="height:24px;"></td>
    <td style="font-weight:bold; text-align:right; height:24px;">{{ $data['voucher_type'] ?? '' }} no:</td>
    <td colspan="2" style="text-align:right; border-bottom:1px solid #000000; height:24px; white-space:nowrap;">
        {{ $data['voucher_number'] ?? '' }}
    </td>
</tr>
