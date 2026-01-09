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

<table class="datatable">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>#</th>
            <th>{{ __('Branch Name') }}</th>
            <th>{{ __('Emp no') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('D.O.J') }}</th>
            <th>{{ __('Degree Title') }}</th>
            <th>{{ __('Subject') }}</th>
            <th>{{ __('Year of Completion') }}</th>

        </tr>
    </thead>
    <tbody>
        @if (!empty($data) && $data->count())
            @php
                $groupedEmployees = $data->groupBy(function($emp) {
                    return $emp->employee->userbranch->name ?? 'Unknown Branch';
                });
            @endphp
    
            @foreach ($groupedEmployees as $branchName => $employees)
                <tr style="text-align: left; background-color: #bcbbbb;">
                    <td style="text-align: left;" colspan="8" class="font-weight-bold bg-light">{{ $branchName }}</td>
                </tr>
                @foreach ($employees as $index => $emp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ @$emp->employee->userbranch->name }}</td>
                        <td>{{ @$emp->employee->employee_id }}</td>
                        <td>{{ @$emp->employee->name }}</td>
                        <td>{{ \Carbon\Carbon::parse(@$emp->employee->company_doj)->format('d-M-Y') }}</td>
                        <td>{{ @$emp->degree }}</td>
                        <td>{{ @$emp->subject }}</td>
                        <td>{{ @$emp->pass_date }}</td>
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
