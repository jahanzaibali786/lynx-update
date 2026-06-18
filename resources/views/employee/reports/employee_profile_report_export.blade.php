<tr>
    <td colspan="17" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="17" style="text-align: center;"></td>
</tr>
<tr>
    <td colspan="17" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
        <span style="text-transform: uppercase;">{{ $branchName }}</span>
    </td>
</tr>
<tr>
    <td colspan="17" style="text-align: center;"></td>
</tr>
<tr>
    <td colspan="17" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px;">
        <span style="text-transform: uppercase;">{{ $report_name }}</span>
    </td>
</tr>
<tr>
    <td colspan="17" style="text-align: center;"></td>
</tr>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Emp ID') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Father Name') }}</th>
            <th>{{ __('CNIC') }}</th>
            <th>{{ __('DOB') }}</th>
            <th>{{ __('Gender') }}</th>
            <th>{{ __('Religion') }}</th>
            <th>{{ __('Blood Group') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Department') }}</th>
            <th>{{ __('Designation') }}</th>
            <th>{{ __('DOJ') }}</th>
            <th>{{ __('Address') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $rowIndex = 1; @endphp
        @forelse ($employees as $emp)
            <tr>
                <td>{{ $rowIndex++ }}</td>
                <td>{{ $emp->employee_id }}</td>
                <td>{{ $emp->name }}</td>
                <td>{{ $emp->f_name }}</td>
                <td>{{ $emp->cnic }}</td>
                <td>{{ $emp->dob == '0000-00-00' || !$emp->dob ? '' : \Carbon\Carbon::parse($emp->dob)->format('d-M-Y') }}</td>
                <td>{{ strtoupper($emp->gender) }}</td>
                <td>{{ $emp->religion }}</td>
                <td>{{ $emp->blood_group }}</td>
                <td>{{ $emp->phone }}</td>
                <td>{{ $emp->email }}</td>
                <td>{{ optional($emp->userbranch)->name }}</td>
                <td>{{ optional($emp->department)->name }}</td>
                <td>{{ optional($emp->designation)->name }}</td>
                <td>{{ $emp->company_doj == '0000-00-00' || !$emp->company_doj ? '' : \Carbon\Carbon::parse($emp->company_doj)->format('d-M-Y') }}</td>
                <td>{{ $emp->address }}</td>
                <td>
                    @if($emp->is_res_ter == 1) Resigned
                    @elseif($emp->is_res_ter == 2) Terminated
                    @else Active
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="17" class="text-center">{{ __('No employees found') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
