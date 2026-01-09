<style>
    table,
    tr,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    th {
        text-align: left;
    }

    #periodtext {
        display: none;
    }
</style>
<table class=" mt-4 ">
    <thead>
        <tr class="table_heads">
            <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Registration</th>
        </tr>
        <tr>
            <th>Session</th>
            @foreach ($school_classes as $class)
                <th>{{ $class }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($select_session as $session)
            <tr>
                <td>{{ $session->year }}</td>
                @foreach ($school_classes as $class)
                    <td>{{ $registrationCounts[$session->id][$class] }}</td>
                @endforeach
                <td>{{ $totalRegistrations[$session->id] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr class="table_heads">
                <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Student
                    Strength</th>
            </tr>
            <tr>
                <th>Session</th>
                @foreach ($school_classes as $class)
                    <th>{{ $class }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tr>
            <td>{{ $session->year }}</td>
            @foreach ($school_classes as $class)
                <td>{{ $strengthCounts[$session->id][$class] }}</td>
            @endforeach
            <td>{{ $totalStrength[$session->id] }}</td>
        </tr>
        <thead>
            <tr class="table_heads">
                <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Enrollment
                </th>
            </tr>
            <tr>
                <th>Session</th>
                @foreach ($school_classes as $class)
                    <th>{{ $class }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tr>
            <td>{{ $session->year }}</td>
            @foreach ($school_classes as $class)
                <td>{{ $enrollmentCounts[$session->id][$class] }}</td>
            @endforeach
            <td>{{ $totalEnrollments[$session->id] }}</td>
        </tr>
        <thead>
            <tr class="table_heads">
                <th colspan="{{ count($school_classes) + 2 }}" style="background:gray; font-size: large;">Withdrawal
                </th>
            </tr>
            <tr>
                <th>Session</th>
                @foreach ($school_classes as $class)
                    <th>{{ $class }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tr>
            <td>{{ $session->year }}</td>
            @foreach ($school_classes as $class)
                <td>{{ $withdrawalCounts[$session->id][$class] }}</td>
            @endforeach
            <td>{{ $totalWithdrawals[$session->id] }}</td>
        </tr>

    </tbody>
</table>
