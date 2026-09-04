<tr>
    <td colspan="8" style="height:18px;"></td>
</tr>
<tr>
    <td style="font-weight:bold; height:24px; white-space:nowrap;">Amount in Words:</td>
    <td colspan="7" style="height:24px; white-space:nowrap;">
        {{ $data['amount_words'] ?? '' }}-
    </td>
</tr>
<tr>
    <td colspan="8" style="height:18px;"></td>
</tr>
<tr>
    <td colspan="3" style="background:#d9d9d9; font-weight:bold; padding:4px 6px; height:24px;">
        Note for Payment:
    </td>
    <td colspan="5" style="height:24px;"></td>
</tr>
<tr>
    <td colspan="5" rowspan="4" style="height:88px; vertical-align:top; white-space:normal;">
        {{ $data['note'] ?? '' }}
    </td>
    <td colspan="3" rowspan="4" style="height:88px;"></td>
</tr>
<tr></tr>
<tr></tr>
<tr></tr>

<tr>
    <td colspan="8" style="height:24px;"></td>
</tr>
<tr>
     <td colspan="9" style="background:#d9d9d9; font-weight:bold; padding:4px 6px; height:24px;">
        Receiver Information:
    </td>
    <td colspan="5" style="height:24px;"></td>
</tr>
<tr>
    <td colspan="8" style="height:14px;"></td>
</tr>
@foreach($receiverRows as $label)
    <tr>
        <td style="font-weight:bold; height:28px; white-space:nowrap;">{{ $label }}</td>
        <td colspan="4" style="height:28px; border-bottom:1px solid #000000;"></td>
        <td colspan="3" style="height:28px;"></td>
    </tr>
@endforeach

<tr>
    <td colspan="8" style="height:30px;"></td>
</tr>
<tr>
    <td colspan="3" style="background:#d9d9d9; font-weight:bold; padding:4px 6px; height:24px;">
        Authorization:
    </td>
    <td colspan="5" style="height:24px;"></td>
</tr>
<tr>
    <td colspan="8" style="height:30px;"></td>
</tr>
<tr>
    <td colspan="2" style="height:70px; text-align:center; vertical-align:bottom; font-weight:bold; color:#999999;">
        Signature/Date
    </td>
    <td style="height:70px;"></td>
    <td colspan="2" style="height:70px; text-align:center; vertical-align:bottom; font-weight:bold; color:#999999;">
        Signature/Date
    </td>
    <td style="height:70px;"></td>
    <td colspan="2" style="height:70px; text-align:center; vertical-align:bottom; font-weight:bold; color:#999999;">
        Signature/Date
    </td>
</tr>
<tr>
    <td colspan="2" style="height:24px; text-align:center; font-weight:bold; border-top:2px solid #000000;">
        Manager Finance
    </td>
    <td style="height:24px;"></td>
    <td colspan="2" style="height:24px; text-align:center; font-weight:bold; border-top:2px solid #000000;">
        Director Finance
    </td>
    <td style="height:24px;"></td>
    <td colspan="2" style="height:24px; text-align:center; font-weight:bold; border-top:2px solid #000000;">
        Managing Director
    </td>
</tr>
