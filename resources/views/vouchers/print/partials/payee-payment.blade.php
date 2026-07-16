<tr>
    <td colspan="8" style="vertical-align:top;">
        <table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif; font-size:14px; color:#000000;">
            <tr>
                <td style="width:65%; vertical-align:top; padding-right:26px;">
                    <table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif; font-size:14px; color:#000000;">
                        <tr>
                            <td colspan="2" style="background:#d9d9d9; font-weight:bold; font-size:14px; padding:4px 6px; height:24px;">
                                Payee Information:
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height:16px;"></td></tr>
                        @foreach($payeeRows as $payeeRow)
                            <tr>
                                <td style="width:38%; font-weight:bold; height:27px; white-space:nowrap; vertical-align:middle;">
                                    {{ $payeeRow[0] }}
                                </td>
                                <td style="width:62%; height:27px; border-bottom:1px solid #000000; white-space:nowrap; vertical-align:middle;">
                                    {{ $payeeRow[1] }}
                                </td>
                            </tr>
                            <tr><td colspan="2" style="height:8px;"></td></tr>
                        @endforeach
                    </table>
                </td>
                <td style="width:35%; vertical-align:top; padding-left:16px;">
                    <table style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif; font-size:12px; color:#000000;">
                        <tr>
                            <td colspan="2" style="background:#d9d9d9; font-weight:bold; font-size:14px; padding:4px 6px; height:24px;">
                                Payment Information:
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height:16px;"></td></tr>
                        @foreach($paymentRows as $paymentRow)
                            <tr>
                                <td style="width:45%; font-weight:bold; height:23px; white-space:nowrap; vertical-align:middle; font-size:12px;">
                                    {{ $paymentRow[0] }}
                                </td>
                                <td style="width:55%; height:23px; border-bottom:1px solid #000000; text-align:right; white-space:nowrap; vertical-align:middle; font-size:12px;">
                                    {{ $paymentRow[1] }}
                                </td>
                            </tr>
                            <tr><td colspan="2" style="height:6px;"></td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
