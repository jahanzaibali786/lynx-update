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
    <tbody>
        <thead>
            <tr class="text-center">
                {{-- from --}}
                <th colspan="3" style="font-size: 1rem;" class="">{{ __('From :') }} {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }}</th>
                {{-- repotrt name  --}}
                <th colspan="8" style="font-size: 1rem;" class="">{{ __('Employee Tax Report') }}</th>
                {{-- to --}}
                <th colspan="3" style="font-size: 1rem;" class="">{{ __('To :') }} {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}</th>
            </tr>
            <tr class="">
                <th class="">{{ __('sr.') }}</th>
                <th class="">{{ __('Br.sr') }}</th>
                <th class="">{{ __('Branch Name') }}</th>
                <th class="">{{ __('Payroll Month') }}</th>
                <th class="">{{ __('Emp No.') }}</th>
                <th class="">{{ __('Employee Name') }}</th>
                <th class="">{{ __('D.O.J') }}</th>
                <th class="">{{ __('Designation') }}</th>
                @foreach ($salaryHeads as $salaryhead)
                    <th>{{ $salaryhead->head }}</th>
                @endforeach
                <th class="">{{ __('Gross Salary') }}</th>
                <th class="">{{ __('Tax Ded. Rs.') }}</th>
            </tr>
        </thead>
    <tbody>
        @foreach ($data as $monthsalary)
            @php
                $employee = \App\Models\Employee::where('id',$monthsalary->employee_id)->first();
                $branch = \App\Models\User::find($employee->branch_id);
                $designation = \App\Models\Designation::find($employee->designation_id);
            @endphp
            <tr class="text-center">
                <td>{{ ++$loop->index }}</td>
                <td>{{ @$branch->id }}</td>
                <td>{{ @$branch->name }}</td>
                <td>{{ \Carbon\Carbon::parse(@$monthsalary->salary_date)->format('F Y') }}</td>
                <td>{{ @$employee->employee_id }}</td>
                <td>{{ @$employee->name }}</td>
                <td>{{ \Carbon\Carbon::parse(@$employee->company_doj)->format('d-m-Y') }}</td>
                <td>{{ !empty($designation) ? $designation->name : '' }}</td>
                @foreach ($salaryHeads as $head)
                    @php
                        $empscale = \App\Models\EmployeeMonthlySalaryHeads::where('employee_id', $monthsalary->employee_id)->where('head_id', $head->id)->first();
                    @endphp
                    <td>{{ \Auth::user()->priceFormat(@$empscale->head_value) }}</td>
                @endforeach
                <td>{{ \Auth::user()->priceFormat(@$monthsalary->gross) }}</td>
                <td>{{ \Auth::user()->priceFormat(@$monthsalary->it) }}</td>
            </tr>
        @endforeach
    </tbody>
    </tbody>
</table>
