<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        body {
            font-size: 0.8rem;
        }

        table {
            width: 100%;
            margin-top: 50px;
        }
        table,
        thead,
        tbody,
        tr,
        td,
        th {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
            font-size: 0.6rem;
        }
        
        th {
            font-size: 0.8rem;
            background-color: gray;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center; margin-top: -20px; ">{{ 'Employee Service Record' }}</h2>
    <table class="datatable">
        <thead>
            <tr>
                <th style="width: 5%;">{{ __('Sr. No.') }}</th>
                <th style="width: 25%;">{{ __('Employee Name') }}</th>
                <th style="width: 20%;">{{ __('Designation') }}</th>
                <th style="width: 20%;">{{ __('Department') }}</th>
                <th style="width: 15%;">{{ __('D.o.J') }}</th>
                <th style="width: 15%;">{{ __('Tenure') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (count($employeesquery) > 0)
            @php $srNo = 1; @endphp
            @foreach ($employeesquery as $employee)
                @if ($employee->employee_rejoin && count($employee->employee_rejoin) > 0)
                    @foreach ($employee->employee_rejoin as $rejoin)
                        {{-- Previous Rejoin Period Row --}}
                        <tr>
                            <td>{{ $srNo++ }}</td>
                            <td>{{ $employee->name }} (Previous)</td>
                            <td>{{ $employee->designation->name ?? '' }}</td>
                            <td>{{ $employee->department->name ?? '' }}</td>
                            <td>{{ \Auth::user()->dateFormat($rejoin->prev_doj) }}</td>
                            <td>
                                @php
                                    $prevDoj = \Carbon\Carbon::parse($rejoin->prev_doj);
                                    $prevLeaving = \Carbon\Carbon::parse($rejoin->prev_leaving_date);
                                    $prevTenure = $prevDoj->diff($prevLeaving);
                                @endphp
                                {{ $prevTenure->y }} years {{ $prevTenure->m }} months
                            </td>
                        </tr>
                    @endforeach
                    {{-- Current Rejoin Period Row --}}
                    <tr>
                        <td>{{ $srNo++ }}</td>
                        <td>{{ $employee->name }} (Rejoined)</td>
                        <td>{{ $employee->designation->name ?? '' }}</td>
                        <td>{{ $employee->department->name ?? '' }}</td>
                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                        <td>
                            @php
                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                $currentTenure = $currentDoj->diff(now());
                            @endphp
                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                        </td>
                    </tr>
                @else
                    {{-- Single row if no rejoin --}}
                    <tr>
                        <td>{{ $srNo++ }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->designation->name ?? '' }}</td>
                        <td>{{ $employee->department->name ?? '' }}</td>
                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                        <td>
                            @php
                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                $currentTenure = $currentDoj->diff(now());
                            @endphp
                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                        </td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="6" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
        
        </tbody>
    </table>
</body>

</html>
