@include('student.exports.header')
<table>
    <tbody>
        <thead>
            <tr>
                <th>{{ __('Sr. No.') }}</th>
                <th>{{ __('Br.Sr.') }}</th>
                <th>{{ __('Branch Name') }}</th>
                <th>{{ __('D/O/J') }}</th>
                <th>{{ __('Service Pr.') }}</th>
                <th>{{ __('Emp No.') }}</th>
                <th>{{ __('Employee Name') }}</th>
                <th>{{ __('From') }}</th>
                <th>{{ __('To') }}</th>
                <th>{{ __('Total W/O/P Days') }}</th>
            </tr>
        </thead>
    <tbody>
        @php
            $groupedByBranch = collect($data['data'])->groupBy(function ($emp) {
                return $emp->employees->userbranch->name ?? '';
            });
        @endphp

        @foreach ($data['data'] as $branchName => $empleaves)
            <tr>
                <td colspan="3" style="border: none; font-weight: bold; text-align: left; background-color: #bcbcbc;">{{ $branchName }}</td>
                <td colspan="7" style="border: none; font-weight: bold; text-align: left; background-color: #bcbcbc;"></td>
            </tr>
            @foreach ($empleaves as $index => $empleave)
                <tr class="text-center">
                    <td>{{ $loop->parent->index * $empleaves->count() + $index + 1 }}</td>
                    <td>{{ @$empleave->employees->userbranch->id ?? '' }}</td>
                    <td>{{ $branchName }}</td>
                    <td>{{ @$empleave->employees->company_doj ?? '' }}</td>
                    <td>{{ $empleave->employees ? @$empleave->employees->getEmployeeTenure($empleave->employees->id) : '' }}
                    </td>
                    <td>{{ @$empleave->employees->employee_id ?? '' }}</td>
                    <td>{{ @$empleave->employees->name ?? '' }}</td>
                    <td>{{ @$empleave->start_date ?? '' }}</td>
                    <td>{{ @$empleave->end_date ?? '' }}</td>
                    <td>{{ @$empleave->total_leave_days ?? '' }}</td>
                </tr>
            @endforeach
        @endforeach

    </tbody>
</table>
@include('student.exports.footer')
