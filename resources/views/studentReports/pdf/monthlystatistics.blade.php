<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size:0.8rem;
    }
    th{text-align: left;
        font-size: 0.7rem;
    }
    #periodtext{
        display:none;}
</style>
<table style="margin-top:50px; width:100%;">
    @php $currentBranch = null; @endphp
    @foreach ($report as $row)
        @if ($currentBranch != $row['branch'])
            <tr style="background: gray">
                <td colspan="8">{{ $row['branch'] }}</td>
                @php $currentBranch = $row['branch']; @endphp
            </tr>
            <tr style="margin-top:20px;">
                <th>CLASS</th>
                <th>SECTION</th>
                <th>OPENING</th>
                <th>NEW ADMISSIONS</th>
                <th>TRANSFER IN</th>
                <th>WITHDRAWALS</th>
                <th>TRANSFER OUT</th>
                <th>CLOSING BALANCE</th>
            </tr>
        @endif
        <tr>
            <td>{{ $row['class'] }}</td>
            <td>{{ $row['section'] }}</td>
            <td>{{ $row['opening'] }}</td>
            <td>{{ $row['new_admissions'] }}</td>
            <td>{{ $row['transfer_in'] }}</td>
            <td>{{ $row['withdrawals'] }}</td>
            <td>{{ $row['transfer_out'] }}</td>
            <td>{{ $row['closing_balance'] }}</td>
        </tr>
    @endforeach
</table>
