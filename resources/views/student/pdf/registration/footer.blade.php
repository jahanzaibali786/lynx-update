   {{-- branch/grand totals --}}
    <tr class="grand-total-row">
        <td colspan="13" style="font-size:8px; font-weight:bold; background:#A0A0A0; text-align:center;">
            Grand Total
        </td>
        <td style="font-size:8px; font-weight:bold; text-align:right;">
            {{ number_format($grandTotal, 0) }}
        </td>
    </tr>
    @include('student.exports.footer')
    </tbody>
</table>

{{-- Signature lines --}}
<div style="margin-top:40px; height:60px;">
    <table width="100%" style="font-size:10px; border:0; height:60px;">
        <tr>
            <td style="width:50%; text-align:left; vertical-align:bottom;">
                _________________________________
            </td>
            <td style="width:50%; text-align:right; vertical-align:bottom;">
                _________________________________
            </td>
        </tr>
    </table>
</div>