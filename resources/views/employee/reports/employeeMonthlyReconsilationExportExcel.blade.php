@include('student.exports.header')
<table class="datatable">
    <thead class="table_heads">
        <tr>
            <th>{{ __('Sr No.') }}</th>
            <th>{{ __('Emp no') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Working Days') }}</th>
            <th>{{ __('Net Salary Annex "A"') }}</th>
            <th>{{ __('Payable Salary') }}</th>
            <th>{{ __('Difference') }}</th>
            <th>{{ __('Per Day Salary') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $dataset = $data['data'] ?? [];
        @endphp

        @if (!empty($dataset))
            @if (request()->has('branches') && request()->branches != '')
                @php
                    $totalGross = $totalNet = $totalDiff = $totalPerDay = 0;
                    $totalDays = 0;
                @endphp
                <tr>
                    <td colspan="3" style="background-color:#cdcaca; font-weight:bold; text-align:left; border:none;">{{ $branchName }}</td>
                    <td colspan="5" style="background-color:#cdcaca; font-weight:bold; text-align:left; border:none;"></td>
                </tr>

                @foreach ($dataset as $empsalaries)
                    @foreach ($empsalaries as $empsalary)
                        @php
                            $gross = (float) $empsalary->gross;
                            $net = (float) $empsalary->net_pay;
                            $days = (int) ($empsalary->sal_days ?? 0);
                            $perDaySalary = $days > 0 ? $net / $days : 0;

                            $totalGross += $gross;
                            $totalNet += $net;
                            $totalDiff += $gross - $net;
                            $totalPerDay += $perDaySalary;
                            $totalDays++;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $empsalary->employee->employee_id }}</td>
                            <td>{{ $empsalary->employee->name }}</td>
                            <td>{{ $days }}</td>
                            <td>{{ number_format($gross, 2) }}</td>
                            <td>{{ number_format($net, 2) }}</td>
                            <td>{{ number_format($gross - $net, 2) }}</td>
                            <td>{{ number_format($perDaySalary, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach

                <tr>
                    <td colspan="4">Branch Total:</td>
                    <td>{{ number_format($totalGross, 2) }}</td>
                    <td>{{ number_format($totalNet, 2) }}</td>
                    <td>{{ number_format($totalDiff, 2) }}</td>
                    <td>{{ $totalDays > 0 ? number_format($totalPerDay / $totalDays, 2) : 0 }}</td>
                </tr>
            @else
                @foreach ($dataset as $branchId => $branchData)
                    @php
                        $branch = \Auth::user()->getBranch($branchId);
                        $totalGross = $totalNet = $totalDiff = $totalPerDay = 0;
                        $totalDays = 0;
                    @endphp

                    <tr>
                        <td colspan="8" style="font-weight:bold; text-align:left;">
                            {{ $branch->name ?? 'Unknown Branch' }}</td>
                    </tr>

                    @foreach ($branchData as $empsalary)
                        @php
                            $gross = (float) $empsalary->gross;
                            $net = (float) $empsalary->net_pay;
                            $days = (int) ($empsalary->sal_days ?? 0);
                            $perDaySalary = $days > 0 ? $net / $days : 0;

                            $totalGross += $gross;
                            $totalNet += $net;
                            $totalDiff += $gross - $net;
                            $totalPerDay += $perDaySalary;
                            $totalDays++;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $empsalary->employee->employee_id }}</td>
                            <td>{{ $empsalary->employee->name }}</td>
                            <td>{{ $days }}</td>
                            <td>{{ number_format($gross, 2) }}</td>
                            <td>{{ number_format($net, 2) }}</td>
                            <td>{{ number_format($gross - $net, 2) }}</td>
                            <td>{{ number_format($perDaySalary, 2) }}</td>
                        </tr>
                    @endforeach

                    <tr style="font-weight:bold; background-color:#cdcaca;">
                        <td colspan="4" style="text-align:right;">Branch Total:</td>
                        <td>{{ number_format($totalGross, 2) }}</td>
                        <td>{{ number_format($totalNet, 2) }}</td>
                        <td>{{ number_format($totalDiff, 2) }}</td>
                        <td>{{ $totalDays > 0 ? number_format($totalPerDay / $totalDays, 2) : 0 }}</td>
                    </tr>
                @endforeach
            @endif
        @else
            <tr>
                <td colspan="8" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
    </tbody>


</table>
@include('student.exports.footer')
