@include('student.exports.header')
{{-- session from and to --}}
<tr>
    <td colspan="7" style="text-align: left; font-family: Calibri; font-weight: bold; font-size: 8px;">
        <span style="font-size: 8px; font-weight: 600;">Session From {{ $select_session->first()->year ?? '' }} {{' To'}} {{ $select_session->last()->year ?? '' }}</span>
    </td>
</tr>
<table class="datatable">
    <thead>
        <tr>
            <td colspan="{{ count($school_classes) + 2 }}" style="text-align: center; font-family: Calibri; font-weight: bold; font-size: 8px;">
            </td>
        </tr>
        <tr>
            <th  colspan="{{ count($school_classes) + 2 }}" style=" background:gray; font-size: 12px; font-weight: bold; border: 2px solid black; border-collapse: collapse;">Registration</th>
        </tr>
        <tr >
            <th style="font-size: 8px; font-weight: bold; border: 2px solid black; border-collapse: collapse; width: 100px; font-family: Calibri;">Session</th>
            @foreach ($school_classes as $class)
                <th style="font-size: 8px; font-weight: bold; border: 2px solid black; font-family: Calibri; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px;">{{ $class }}</th>
            @endforeach
            <th style="font-size: 8px; font-weight: bold; border: 2px solid black; font-family: Calibri; border-collapse: collapse; width: 100px; background:gray; text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 8px; font-weight: bold; font-family: Calibri; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($school_classes as $class)
                    <td style="font-size: 8px; font-family: Calibri; border: 2px solid black; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px;">
                        {{ $registrationCounts[$session->id][$class] ?? 0 }}
                    </td>
                @endforeach
                <td style="background:gray; font-weight: bold; font-family: Calibri; text-align:center; color:black;">{{ $totalRegistrations[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ count($school_classes) + 2 }}" style="  background:gray; font-size: 12px; font-weight: bold; border: 2px solid black; border-collapse: collapse;">Student Strength</th>
            </tr>
            <tr >
                <th style="font-size: 8px; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; font-family: Calibri;">Session</th>
                @foreach ($school_classes as $class)
                    <th style="font-size: 8px; font-weight: 600; border: 2px solid black; font-family: Calibri; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">{{ $class }}</th>
                @endforeach
                <th style="font-size: 8px; font-weight: bold; border: 2px solid black; border-collapse: collapse; width: 100px;  background:gray; text-align:center;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 8px; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($school_classes as $class)
                    <td style="font-size: 8px; font-weight: 500; border: 2px solid black; font-family: Calibri; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">
                        {{ $strengthCounts[$session->id][$class] ?? 0 }}
                    </td>
                @endforeach
                <td style="background:gray; font-weight: bold; font-family: Calibri; text-align:center; color:black;">{{ $totalStrength[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ count($school_classes) + 2 }}" style=" background:gray; font-size: 12px; font-weight: bold; border: 2px solid black; border-collapse: collapse;">Enrollment</th>
            </tr>
            <tr >
                <th style="font-size: 8px; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; font-family: Calibri;">Session</th>
                @foreach ($school_classes as $class)
                    <th style="font-size: 8px; font-weight: 600; border: 2px solid black; font-family: Calibri; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">{{ $class }}</th>
                @endforeach
                <th style="font-size: 8px; font-weight: bold; border: 2px solid black; border-collapse: collapse; width: 100px; background:gray; text-align:center;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 8px; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($school_classes as $class)
                    <td style="font-size: 8px; font-weight: 500; border: 2px solid black; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">
                        {{ $enrollmentCounts[$session->id][$class] ?? 0 }}
                    </td>
                @endforeach
                <td style="background:gray; font-weight: bold; font-family: Calibri; text-align:center; color:black;">{{ $totalEnrollments[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ count($school_classes) + 2 }}" style=" background:gray; font-size: 12px; font-weight: bold; border: 2px solid black; border-collapse: collapse;">Withdrawal</th>
            </tr>
            <tr >
                <th style="font-size: 8px; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Session</th>
                @foreach ($school_classes as $class)
                    <th style="font-size: 8px; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">{{ $class }}</th>
                @endforeach
                <th style="font-size: 8px; font-weight: bold; border: 2px solid black; border-collapse: collapse; width: 100px; background:gray; text-align:center;">Total</th>
            </tr>
        </thead>
        @foreach($select_session as $session)
            <tr>
                <td style="font-size: 8px; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $session->year }}</td>
                @foreach ($school_classes as $class)
                    <td style="font-size: 8px; font-weight: 500; border: 2px solid black; border-collapse: collapse; width: {{ 50 + max(0, strlen($class) - 7) * 4 }}px; ">
                        {{ $withdrawalCounts[$session->id][$class] ?? 0 }}
                    </td>
                @endforeach
                <td style="background:gray; font-weight: bold; font-family: Calibri; text-align:center; color:black;">{{ $totalWithdrawals[$session->id] }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
@include('student.exports.footer')