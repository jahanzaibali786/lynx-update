<table class="datatable">
    <thead>
        <tr>
            <td colspan="{{ $branches->count() + 2 }}"
                style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="{{ $branches->count() + 2 }}" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ __('Session Branch Wise Report') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="{{ $branches->count() + 2 }}" style="text-align: center; font-weight: 600; font-size: 15rem;">
                {{-- <span>{{ \Carbon\Carbon::now()->format('F Y') }}</span> --}}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $branches->count() + 2 }}" style="text-align: center;">
            </td>
        </tr>
        <tr>
            <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Registration</th>
        </tr>
        <tr >
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Session</th>
            @foreach ($branches as $branchId => $branch)
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">{{ $branch}}</th>
            @endforeach
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; color:red; background:gray;" >Total</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $registrationCounts[$session->id][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red;">{{ $totalRegistrations[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style="  background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Student Strength</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">Session</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">{{ $branch}}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; color:red; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $strengthCounts[$session->id][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red;">{{ $totalStrength[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Enrollment</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Session</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">{{ $branch}}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; color:red; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $enrollmentCounts[$session->id][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red;">{{ $totalEnrollments[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $branches->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Withdrawal</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Session</th>
                @foreach ($branches as $branchId => $branch)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">{{ $branch}}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; text-align:center; color:red; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($branches as $branchId => $branch)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $withdrawalCounts[$session->id][$branchId] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red;">{{ $totalWithdrawals[$session->id] }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
