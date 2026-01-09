<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
    }

    th,
    td {
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
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Emp no') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Father Name') }}</th>
            <th>{{ __('CNIC') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('D.O.J') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Emergency Contact') }}</th>
            <th>{{ __('Current Address') }}</th>
            <th>{{ __('Permanent Address') }}</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($employeesquery) && $employeesquery->count())
            @php
                $groupedEmployees = $employeesquery->groupBy(function ($emp) {
                    return (\Auth::user()->getBranch($emp->owned_by)->name ?? 'Unknown Branch');
                });
            @endphp

            @foreach ($groupedEmployees as $branchName => $employees)
                <tr style="text-align: left; background-color: #bcbbbb;">
                    <td style="text-align: left;" colspan="12" class="font-weight-bold bg-light">{{ $branchName }}</td>
                </tr>
                @foreach ($employees as $index => $emp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td width="150px;">
                            @if (!empty($emp->owned_by))
                                {{ !empty(\Auth::user()->getBranch($emp->owned_by)) ? \Auth::user()->getBranch($emp->owned_by)->name : '' }}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ @$emp->employee_id }}</td>
                        <td width="150px;">{{ @$emp->name }}</td>
                        <td width="150px;">{{ @$emp->f_name }}</td>
                        <td width="150px;">{{ @$emp->cnic }}</td>
                        <td width="5%">{{ @$emp->email }}</td>
                        <td width="150px;">{{ \Carbon\Carbon::parse(@$emp->company_doj)->format('d-M-Y') }}</td>
                        <td>{{ @$emp->phone }}</td>
                        <td>{{ @$emp->phone }}</td>
                        <td width="5%">{{ @$emp->present_address }}</td>
                        <td width="5%">{{ @$emp->address }}</td>
                    </tr>
                @endforeach
            @endforeach
        @else
            <tr>
                <td colspan="12" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
    </tbody>
</table>
