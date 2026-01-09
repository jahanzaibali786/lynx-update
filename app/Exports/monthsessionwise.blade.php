<table class="datatable">
    <thead>
        <tr>
            <td colspan="{{ $classes->count() + 2 }}"
                style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="{{ $classes->count() + 2 }}" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ __('Month Wise Report') }}</span>
            </td>
        </tr>

        <tr>
            <td  style="text-align: center; font-weight: 500; font-size: 12rem;">
                <span>Month From:</span>
            </td>
            
            <td  style="text-align: center; font-weight: 500; font-size: 12rem;">
                <span>{{$month_f}} {{$year}}</span>
            </td>
            
            @if(($classes->count() - 2) > 0)
                <td colspan="{{ ($classes->count() - 2) > 0 ? $classes->count() - 2 : 0}}" style="text-align: center; font-weight: 500; font-size: 12rem;"></td>
            @endif
            <td  style="text-align: center; font-weight: 500; font-size: 12rem;">
                <span>Month To:</span>
            </td>
            <td  style="text-align: center; font-weight: 500; font-size: 12rem;">
                <span>{{$month_t}} {{$year}}</span>
            </td>
        </tr>
        
        <tr>
            <td colspan="{{ $classes->count() + 2 }}" style="text-align: center;">
            </td>
        </tr>
        <tr>
            <th  colspan="{{ $classes->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Registration</th>
        </tr>
        <tr >
            <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
            @foreach ($classes as $class)
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $class->name }}</th>
            @endforeach
            <th style="font-size: 10rem; font-weight: 600; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; color:red; background:gray;">Total</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($classes as $class)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $registrationCounts[$session][$class->id] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; border-collapse: collapse; color:red;" >{{ $totalRegistrations[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $classes->count() + 2 }}" style="  background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Student Strength</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px;">Month</th>
                @foreach ($classes as $class)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $class->name }}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; text-align:center; border: 2px solid black; border-collapse: collapse; color:red; width: 100px; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($classes as $class)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $strengthCounts[$session][$class->id] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; color:red; border-collapse: collapse;">{{ $totalStrength[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $classes->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Enrollment</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
                @foreach ($classes as $class)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $class->name }}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; text-align:center; border-collapse: collapse; color:red; width: 100px; background:gray;">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($classes as $class)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $enrollmentCounts[$session][$class->id] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:#fff; border: 2px solid black; color:red; border-collapse: collapse;">{{ $totalEnrollments[$session] }}</td>
            </tr>
        @endforeach
        <thead>
            <tr>
                <th  colspan="{{ $classes->count() + 2 }}" style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">Withdrawal</th>
            </tr>
            <tr >
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">Month</th>
                @foreach ($classes as $class)
                    <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; border-collapse: collapse; width: 100px; ">{{ $class->name }}</th>
                @endforeach
                <th style="font-size: 10rem; font-weight: 600; border: 2px solid black; text-align:center; border-collapse: collapse; color:red; background:gray; width: 100px; ">Total</th>
            </tr>
        </thead>
        @foreach($months as $session)
            <tr>
                <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ date('F', mktime(0, 0, 0, $session, 1)) }}</td>
                @foreach ($classes as $class)
                    <td style="font-size: 10rem; font-weight: 500; border: 2px solid black; border-collapse: collapse;">{{ $withdrawalCounts[$session][$class->id] }}</td>
                @endforeach
                <td style="background:gray; text-align:center; color:red; border: 2px solid black; border-collapse: collapse;">{{ $totalWithdrawals[$session] }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
