<table class="table">
    <thead class="table_heads">
        @include('student.exports.header')
        <tr>
            <td colspan="8"></td>
            <td style="font-weight: bold; font-size: 11px;">Organization Name</td>
            <td colspan="2" style="border-bottom: 1px solid black;"></td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="2" style="font-weight: bold; font-size: 11px;">REGISTRATION NUMBER</td>
            <td style="border-bottom: 1px solid black;"></td>
            <td colspan="1"></td>
            <td colspan="2" style="font-weight: bold; font-size: 11px;">CONTRIBUTION FOR THE MONTH OF</td>
            <td style="border-bottom: 1px solid black;"></td>
            <td colspan="1"></td>
            <td style="font-weight: bold; font-size: 11px;">Phone No</td>
            <td colspan="2" style="border-bottom: 1px solid black;"></td>
        </tr>
        <tr></tr>
        <tr>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Sr. No') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Emp No') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Emp Name') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Father Name') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('CNIC') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Social Security No') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Designation') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Employement Status') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Working Days') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Salary') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Wages Status') }}</th>
            <th
                style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">
                {{ __('Contribution Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $branchName => $employeesData)
            <tr class="table-secondary">
                <td colspan="18" style="font-size: 8px; font-weight: bold;">{{ $branchName }}</td>
            </tr>
            {{-- @dd($employeesData) --}}
            @foreach ($employeesData as $idx => $item)
                @php $emp = $item['employee']; @endphp
                <tr>
                    <td style="border: 1px solid black; font-size: 8px; text-align: center;">{{ $loop->iteration }}
                    </td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->employee_id ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->name ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->f_name ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->cnic ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->ssc_id ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">
                        {{ $designations[$emp->designation_id]->name }}</td> <!-- Missing in data -->
                    <td style="border: 1px solid black; font-size: 8px;">{{ $emp->category ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ $item->sal_days ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px; text-align: right">
                        {{ number_format($item->gross ?? 0, 2) }}
                    </td>
                    <td style="border: 1px solid black; font-size: 8px;">{{ ucfirst($item->status) ?? '-' }}</td>
                    <td style="border: 1px solid black; font-size: 8px; text-align: right">
                        {{ number_format($item->pessi ?? 0, 2) }}
                    </td>
                </tr>
            @endforeach
        @endforeach
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td style="font-weight: bold; font-size: 8px;">ORGANIZATION STAMP</td>
            <td style="border-bottom: 1px solid black;" colspan="5"></td>
            <td></td>
            <td colspan="2" style="font-size: 8px; font-weight: bold;">SIGNATURE EMPLOYER/REPRESENTATIVE</td>
            <td style="border-bottom: 1px solid black;" colspan="2"></td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table>
