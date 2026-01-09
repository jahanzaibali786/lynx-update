@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr.No') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Employee ID') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Date of Birth') }}</th>
            <th>{{ __('Retirement Age 60') }}</th>
            <th>{{ __('Date of Joining') }}</th>
            <th>{{ __('Service Period Y/M') }}</th>
            <th>{{ __('Left Service Tenure') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $emp)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $emp->userbranch->name }}</td>
                <td>{{ $emp->employee_id }}</td>
                <td>{{ $emp->name }}</td>

                {{-- Date of Birth --}}
                <td>{{ date('d-M-Y', strtotime($emp->dob)) }}</td>

                {{-- Retirement Date (DOB + 60 years) --}}
                <td>{{ date('d-M-Y', strtotime($emp->dob . ' +60 years')) }}</td>

                {{-- Date of Joining --}}
                <td>{{ date('d-M-Y', strtotime($emp->company_doj)) }}</td>
                
                {{-- Service Completed (from DOJ to Today) --}}
                @php
                    $doj = strtotime($emp->company_doj);
                    $now = time();
                    $diff = $now - $doj;

                    $years = floor($diff / (365 * 24 * 60 * 60));
                    $months = floor(
                        ($diff - $years * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60),
                    );
                @endphp
                <td>{{ $years }} years {{ $months }} months</td>

                {{-- Remaining Service (until Age 60) --}}
                @php
                    $retirement = strtotime($emp->dob . ' +60 years');
                    $remaining = $retirement - $now;

                    if ($remaining > 0) {
                        $ryears = floor($remaining / (365 * 24 * 60 * 60));
                        $rmonths = floor(
                            ($remaining - $ryears * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60),
                        );
                    } else {
                        $ryears = 0;
                        $rmonths = 0;
                    }
                @endphp
                <td>{{ $ryears }} years {{ $rmonths }} months</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
