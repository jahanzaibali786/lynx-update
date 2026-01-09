<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    th, td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }

    th {
        background-color: #f2f2f2;
    }

    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #ddd;
    }

    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>

<table class="datatable" style="margin-top: -50px;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>#</th>
            <th>{{ __('Branch Name') }}</th>
            <th>{{ __('Emp no') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('D.O.B') }}</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($employeesquery) && $employeesquery->count())
            @php
                $groupedEmployees = $employeesquery->groupBy(function($emp) {
                    return $emp->userbranch->name ?? 'Unknown Branch';
                });
            @endphp
    
            @foreach ($groupedEmployees as $branchName => $employees)
                <tr style="text-align: left; background-color: #919191;" >
                    <td style="text-align: left;" colspan="5" class="font-weight-bold bg-light">{{ $branchName }}</td>
                </tr>
                @foreach ($employees as $index => $emp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $emp->userbranch->name }}</td>
                        <td>{{ $emp->employee_id }}</td>
                        <td>{{ $emp->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($emp->dob)->format('d-M-Y') }}</td>
                    </tr>
                @endforeach
            @endforeach
        @else
            <tr>
                <td colspan="8" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
    </tbody>                    
</table>