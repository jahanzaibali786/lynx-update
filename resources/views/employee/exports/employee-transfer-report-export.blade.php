<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>
                {{ __('Sr. No') }}</th>
            <th>
                {{ __('Reason of Transfer') }}</th>
            <th>
                {{ __('Transfer Date') }}</th>
            <th>
                {{ __('Employee No') }}</th>
            <th>
                {{ __('Employee Name') }}</th>
            <th>
                {{ __('Designation') }}</th>
            <th>
                {{ __('Gross Salary') }}</th>
            <th>
                {{ __('Transfer IN Branch') }}</th>
            <th>
                {{ __('Transfer OUT Branch') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $sr = 1; @endphp
        @foreach ($transfers as $transfer)
            @php
                // Initialize gross
                $gross = 0;

                // Fetch last payscale detail
                $lastPayscaleDetail = $transfer->employee->employee_payscale_details->last();

                if ($lastPayscaleDetail) {
                    $scale = \App\Models\EmployeeScale::with('employeeScaleHeads')->find(
                        $lastPayscaleDetail->pay_scale_id,
                    );

                    if ($scale) {
                        foreach ($scale->employeeScaleHeads as $head) {
                            $gross += $head->head_value ?? 0;
                        }

                        // Add other allowances
                        $gross +=
                            $lastPayscaleDetail->drns +
                            $lastPayscaleDetail->conv +
                            $lastPayscaleDetail->misc +
                            $lastPayscaleDetail->other_add;
                    }
                }
            @endphp
            <tr>
                {{-- Sr. No --}}
                <td>
                    {{ $sr++ }}
                </td>

                {{-- Reason of Transfer --}}
                <td>
                    {{ $transfer->transfer_reason ?? 'N/A' }}
                </td>

                {{-- Transfer Date --}}
                <td>
                    {{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d-m-Y') }}
                </td>

                {{-- Employee No --}}
                <td>
                    {{ $transfer->employee->employee_id ?? 'N/A' }}
                </td>
                {{-- Employee No --}}
                <td>
                    {{ $transfer->employee->name ?? 'N/A' }}
                </td>

                {{-- Designation --}}
                <td>
                    {{ optional($transfer->employee->designation)->name ?? 'N/A' }}
                </td>

                {{-- Gross Salary --}}
                <td>
                    {{ $gross > 0 ? number_format($gross) : 'N/A' }}
                </td>

                {{-- Transfer IN Branch --}}
                <td>
                    {{ $transfer->branch_to->name ?? 'N/A' }}
                </td>

                {{-- Transfer OUT Branch --}}
                <td>
                    {{ $transfer->branch_from->name ?? 'N/A' }}
                </td>
            </tr>
        @endforeach

        @include('student.exports.footer')
    </tbody>

    {{-- <tbody>
    </tbody> --}}
</table>
