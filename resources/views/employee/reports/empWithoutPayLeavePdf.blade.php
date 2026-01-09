<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.6rem;
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
    <tbody>
        <thead>
            <tr class="">
                <th class="text-sm font-weight-bolder ">{{ __('sr.') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Br.sr') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Branch Name') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('D/O/J') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Service Pr.') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Emp No.') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Employee Name') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('From') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('To') }}</th>
                <th class=" text-sm font-weight-bolder ">{{ __('Total W/O/P Days') }}</th>
            </tr>
        </thead>
    <tbody>
        @php
            $groupedByBranch = collect($data)->groupBy(function ($emp) {
                return $emp->employees->userbranch->name ?? '';
            });
        @endphp

        @foreach ($groupedByBranch as $branchName => $empleaves)
            <tr style="font-weight: bolder; text-align:left; background-color: #949191;;">
                <td colspan="10" style="text-align:left;">{{ $branchName }}</td>
            </tr>
            @foreach ($empleaves as $index => $empleave)
                <tr>
                    <td>{{ $loop->parent->index * $empleaves->count() + $index + 1 }}</td>
                    <td>{{ @$empleave->employees->userbranch->id ?? '' }}</td>
                    <td>{{ $branchName }}</td>
                    <td>{{ @$empleave->employees->company_doj ?? '' }}</td>
                    <td>{{ $empleave->employees ?  @$empleave->employees->getEmployeeTenure($empleave->employees->id) : '' }}</td>
                    <td>{{ @$empleave->employees->employee_id ?? '' }}</td>
                    <td>{{ @$empleave->employees->name ?? '' }}</td>
                    <td>{{ @$empleave->start_date ?? '' }}</td>
                    <td>{{ $empleave->end_date ?? '' }}</td>
                    <td>{{ $empleave->total_leave_days ?? '' }}</td>
                </tr>
            @endforeach
        @endforeach

    </tbody>
    </tbody>
</table> 