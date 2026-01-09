<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th>{{ __('Emp No.') }}</th>
            <th>{{ __('Employee') }}</th>
            <th>{{ __('CNIC') }}</th>
            <th>{{ __('DOJ') }}</th>
            <th>{{ __('Department') }}</th>
            <th>{{ __('Designation') }}</th>
            <th>{{ __('Plan Name') }}</th>
            <th>{{ __('Plan Amount') }}</th>
            <th>{{ __('Plan Type') }}</th>  
            <th>{{ __('Plan Start') }}</th>  
            <th>{{ __('Plan End') }}</th>  
            <th>{{ __('Description') }}</th>  
            <th>{{ __('Plan Status') }}</th>  
        </tr>
    </thead>
    <tbody>
        @foreach ($branchWiseData as $branchName => $insurances)
            <tr>
                <td colspan="3" style="text-align:left;">
                    {{ $branchName }}
                </td>
                <td colspan="11" style="text-align:left;">
                </td>
            </tr>
            @foreach ($insurances as $insurance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ @$insurance->employee->employee_id }}</td>
                    <td>{{ @$insurance->employee->name }}</td>
                    <td>{{ @$insurance->employee->cnic }}</td>
                    <td>{{ @$insurance->employee->company_doj }}</td>
                    <td>{{ @$insurance->employee->department->name }}</td>
                    <td>{{ @$insurance->employee->designation->name }}</td>
                    <td>{{ $insurance->plan_name }}</td>
                    <td>{{ $insurance->plan_amount }}</td>
                    <td>{{ $insurance->plan_type }}</td>
                    <td>{{ $insurance->plan_start }}</td>
                    <td>{{ $insurance->plan_end }}</td>
                    <td>{{ $insurance->description }}</td>
                    <td>
                        {{ $insurance->status == 0 || $insurance->plan_end < date('Y-m-d') ? 'Inactive' : 'Active' }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')