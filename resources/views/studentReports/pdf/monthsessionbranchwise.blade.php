<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size:0.8rem;
    }
    th{text-align: left;}
    #periodtext{
        display:none;}
</style>
<table style="width:100%; margin-top:100px;">
    <thead>
        <tr>
            <th colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Registration</th>
        </tr>
        <tr>
            <th>Month</th>
            @foreach ($branches as $branchId => $branch)
                    <th>{{ $branch }}</th>
            @endforeach
            <th style="background:gray; text-align:center; color:#fff; color:red;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($months as $session)
            <tr>
                <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                <td>{{ $registrationCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; color:red">{{ $totalRegistrations[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Student Strength</th>
            </tr>
            <tr>
                <th>Month</th>
                @foreach ($branches as $branchId => $branch)
                    <th>{{ $branch }}</th>
                @endforeach
                <th style="background:gray; text-align:center; color:#fff; color:red;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td>{{ $strengthCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center;  color:red;">{{ $totalStrength[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th colspan="{{ $branches->count() + 2 }}"  style="background:gray; font-size: large;">Enrollment</th>
            </tr>
            <tr>
                <th>Month</th>
                @foreach ($branches as $branchId => $branch)
                    <th>{{ $branch }}</th>
                @endforeach
                <th style="background:gray; text-align:center;  color:red;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td>{{ $enrollmentCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red;">{{ $totalEnrollments[$session] }}</td>
            </tr>
        @endforeach
            <thead>
                <tr>
                    <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Withdrawal</th>
                </tr>
                <tr>
                    <th>Month</th>
                    @foreach ($branches as $branchId => $branch)
                        <th>{{ $branch }}</th>
                    @endforeach
                    <th style="background:gray; text-align:center; color:red;">Total</th>
                </tr>
            </thead>
            @foreach($months as $session)
                <tr>
                    <td>{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                    @foreach ($branches as $branchId => $branch)
                        <td>{{ $withdrawalCounts[$session][$branchId] }}</td>
                    @endforeach
                    <td style="background:gray; text-align:center; color:red;">{{ $totalWithdrawals[$session] }}</td>
                </tr>
            @endforeach
    </tbody>
</table>
