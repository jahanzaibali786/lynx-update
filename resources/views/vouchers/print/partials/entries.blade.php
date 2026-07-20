<tr>
    <td colspan="2" style="font-weight:bold; text-align:center; border-top:2px solid #000000; border-bottom:1px solid #000000; border-right:5px solid #ffffff; height:22px;">
        Account Head
    </td>
    <td colspan="3" style="font-weight:bold; text-align:center; border-top:2px solid #000000; border-bottom:1px solid #000000; border-left:5px solid #ffffff; border-right:5px solid #ffffff; height:22px;">
        Description
    </td>
    <td style="font-weight:bold; text-align:center; border-top:2px solid #000000; border-bottom:1px solid #000000; border-left:5px solid #ffffff; border-right:5px solid #ffffff; height:22px;">
        Debit (Rs)
    </td>
    <td colspan="2" style="font-weight:bold; text-align:center; border-top:2px solid #000000; border-bottom:1px solid #000000; border-left:5px solid #ffffff; height:22px;">
        Credit (Rs)
    </td>
</tr>

@foreach($accounts as $account)
    <tr>
        <td colspan="2" style="font-weight:bold; font-size:12px; height:22px; vertical-align:top; border-right:5px solid #ffffff; white-space:nowrap;">
            {{ $account['account_head'] ?? '' }}
        </td>
        <td colspan="3" style="font-size:12px; height:22px; vertical-align:top; border-left:5px solid #ffffff; border-right:5px solid #ffffff; white-space:nowrap;">
            {{ $account['description'] ?? '' }}
        </td>
        <td style="font-size:12px; height:22px; vertical-align:top; text-align:right; border-left:5px solid #ffffff; border-right:5px solid #ffffff; white-space:nowrap;">
            {{ $account['debit'] ?? '' }}
        </td>
        <td colspan="2" style="font-size:12px; height:22px; vertical-align:top; text-align:right; border-left:5px solid #ffffff; white-space:nowrap;">
            {{ $account['credit'] ?? '' }}
        </td>
    </tr>
@endforeach

@for($i = 0; $i < $emptyRows; $i++)
    <tr>
        <td colspan="2" style="height:22px; border-right:5px solid #ffffff;"></td>
        <td colspan="3" style="height:22px; border-left:5px solid #ffffff; border-right:5px solid #ffffff;"></td>
        <td style="height:22px; border-left:5px solid #ffffff; border-right:5px solid #ffffff;"></td>
        <td colspan="2" style="height:22px; border-left:5px solid #ffffff;"></td>
    </tr>
@endfor

<tr>
    <td colspan="2" style="height:22px; border-right:5px solid #ffffff;"></td>
    <td colspan="3" style="font-weight:bold; height:22px; text-align:center; border-left:5px solid #ffffff; border-right:5px solid #ffffff;">
        Total
    </td>
    <td style="font-weight:bold; height:22px; text-align:right; border-top:2px solid #000000; border-bottom:3px double #000000; border-left:5px solid #ffffff; border-right:5px solid #ffffff;">
        {{ $data['total_debit'] ?? '' }}
    </td>
    <td colspan="2" style="font-weight:bold; height:22px; text-align:right; border-top:2px solid #000000; border-bottom:3px double #000000; border-left:5px solid #ffffff;">
        {{ $data['total_credit'] ?? '' }}
    </td>
</tr>
