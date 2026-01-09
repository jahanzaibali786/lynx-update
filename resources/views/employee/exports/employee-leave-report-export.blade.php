<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr. No') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Emp No') }}</th>
            <th>{{ __('Emp Name') }}</th>
            <th>{{ __('Casual Leave Opening Bal') }}</th>
            <th>{{ __('Leave Availed') }}</th>
            <th>{{ __('Casual Leave Closing Bal') }}</th>
            <th>{{ __('Last Year Bal B/F') }}</th>
            <th>{{ __('Annual Leave Opening Balance') }}</th>
            <th>{{ __('Leave Availed') }}</th>
            <th>{{ __('Annual Leave Closing Bal') }}</th>
            <th>{{ __('ML OB') }}</th>
            <th>{{ __('ML Avail') }}</th>
            <th>{{ __('ML CB') }}</th>
            <th>{{ __('Unpaid') }}</th>
            <th>{{ __('Unpaid Total') }}</th>
            <th>{{ __('Total Availed') }}</th>
            <th>{{ __('Total Balance') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['reportData'] as $branchName => $employeesData)
            {{-- <tr class="table-secondary">
                <td colspan="18">{{ $branchName }}</td>
            </tr> --}}
            @foreach ($employeesData as $idx => $item)
                @php $emp = $item['employee']; @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $branchName }}</td>
                    <td>{{ $emp->employee_id }}</td>
                    <td>{{ $emp->salute }} {{ $emp->name }}</td>
                    <!-- Casual Leave -->
                    <td>{{ $item['ob']['CL'] }}</td>
                    <td>{{ $item['availed']['CL'] }}</td>
                    <td>{{ $item['cb']['CL'] }}</td>
                    <!-- Last Year Bal B/F - Unavailable -->
                    <td>-</td>
                    <!-- Annual Leave -->
                    <td>{{ $item['ob']['AL'] }}</td>
                    <td>{{ $item['availed']['AL'] }}</td>
                    <td>{{ $item['cb']['AL'] }}</td>
                    <!-- Medical Leave -->
                    <td>{{ $item['ob']['ML'] }}</td>
                    <td>{{ $item['availed']['ML'] }}</td>
                    <td>{{ $item['cb']['ML'] }}</td>
                    <!-- Unpaid Leave -->
                    <td>{{ $item['availed']['Unpaid'] }}</td>
                    <td>{{ $item['availed']['Unpaid'] }}</td>
                    <!-- Totals -->
                    <td>{{ $item['totalAvailed'] }}</td>
                    <td>{{ $item['totalBalance'] }}</td>
                </tr>
            @endforeach
        @endforeach
        @include('student.exports.footer')
    </tbody>
</table>