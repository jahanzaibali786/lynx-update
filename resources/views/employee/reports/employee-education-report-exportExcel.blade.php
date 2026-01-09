@include('student.exports.header')
<table>
    <thead>
        <tr>
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
        @if (!empty($data['data']) && $data['data']->count())
            @php
                $groupedEmployees = $data['data']->groupBy(function ($emp) {
                    return $emp->employee->userbranch->name ?? '-';
                });
            @endphp
            @foreach ($groupedEmployees as $branchName => $employees)
                <tr>
                    <td colspan="3" class="border: none; font-weight: bold; text-align: left; background-color: #bcbcbc;">{{ $branchName }}</td>
                    <td colspan="5" class="border: none; font-weight: bold; text-align: left; background-color: #bcbcbc;"></td>
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
@include('student.exports.footer')