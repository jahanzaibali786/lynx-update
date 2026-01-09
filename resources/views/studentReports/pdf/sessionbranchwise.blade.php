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
            <th colspan="{{ $branches->count() + 2 }} " style="background:gray; font-size: large;">Registration</th>
        </tr>
        <tr>
            <th>Session</th>
            @foreach ($branches as $branchId => $branch)
                <th>{{ $branch }}</th>
            @endforeach
            <th style="background:gray; text-align:center; color:#fff;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($select_session as $session)
        <tr>
            <td>{{$session->year}}</td>
            @foreach ($branches as $branchId => $branch)
                <td>{{ $registrationCounts[$session->id][$branchId] }}</td>
            @endforeach
            <td style="background:gray; text-align:center; color:#fff;">{{ $totalRegistrations[$session->id] }}</td>
        </tr>
        @endforeach
        <thead>
            <tr>
                <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Student Strength</th>
            </tr>
            <tr>
                <th>Session</th>
                @foreach ($branches as $branchId => $branch)
                    <th>{{ $branch }}</th>
                @endforeach
                <th style="background:gray; text-align:center; color:#fff;">Total</th>
            </tr>
        </thead>
        <tr>
            <td>{{$session->year}}</td>
            @foreach ($branches as $branchId => $branch)
                <td>{{ $strengthCounts[$session->id][$branchId] }}</td>
            @endforeach
            <td style="background:gray; text-align:center; color:#fff;">{{ $totalStrength[$session->id] }}</td>
        </tr>
        <thead>
            <tr>
                <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Enrollment</th>
            </tr>
            <tr>
                <th>Session</th>
                @foreach ($branches as $branchId => $branch)
                    <th>{{ $branch }}</th>
                @endforeach
                <th style="background:gray; text-align:center; color:#fff;">Total</th>
            </tr>
        </thead>
        <tr>
            <td>{{$session->year}}</td>
            @foreach ($branches as $branchId => $branch)
                <td>{{ $enrollmentCounts[$session->id][$branchId] }}</td>
            @endforeach
            <td style="background:gray; text-align:center; color:#fff;">{{ $totalEnrollments[$session->id] }}</td>
        </tr>
            <thead>
                <tr>
                    <th colspan="{{ $branches->count() + 2 }}" style="background:gray; font-size: large;">Withdrawal</th>
                </tr>
                <tr>
                    <th>Session</th>
                    @foreach ($branches as $branchId => $branch)
                        <th>{{ $branch }}</th>
                    @endforeach
                    <th style="background:gray; text-align:center; color:#fff;">Total</th>
                </tr>
            </thead>
            <tr>
                <td>{{$session->year}}</td>
                @foreach ($branches as $branchId => $branch)
                    <td>{{ $withdrawalCounts[$session->id][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff;">{{ $totalWithdrawals[$session->id] }}</td>
            </tr>

    </tbody>
</table>
