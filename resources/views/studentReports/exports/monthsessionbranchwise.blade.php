@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Registration</th>
        </tr>
        <tr >
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
            @foreach ($branches as $branchId => $branch)
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">{{ $branch }}</th>
            @endforeach
            <th style="font-size: 10rem; font-weight: 600; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; color:black; background:gray;">Total</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $registrationCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; border-collapse: collapse; color:black;" >{{ $totalRegistrations[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style="  background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Student Strength</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">Month</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $branch }}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; text-align:center; border: 2px solid black; border-collapse: collapse; color:black; width: 100px; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $strengthCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; color:black; border-collapse: collapse;">{{ $totalStrength[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Enrollment</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $branch }}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; text-align:center; border-collapse: collapse; color:black; width: 100px; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $enrollmentCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; color:black; border-collapse: collapse;">{{ $totalEnrollments[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Withdrawal</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $branch}}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; text-align:center; border-collapse: collapse; color:black; background:gray; width: 100px; ">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $withdrawalCounts[$session][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:black; border: 2px solid black; border-collapse: collapse;">{{ $totalWithdrawals[$session] }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
@include('student.exports.footer')