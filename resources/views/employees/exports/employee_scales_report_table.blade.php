<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr.') }}</th>
            <th>{{ __('ScaleNo') }}</th>
            <th>{{ __('Scale Department') }}</th>
            @php
                $net_gross = 0;
            @endphp
            @foreach ($heads as $account)
                <th>{{ !empty($account->head) ? @$account->head : '-' }}</th>
            @endforeach
            <th>{{ __('Gross Salary') }}</th>
            <th>{{ __('Employee Security 8% Of Basic') }}</th>
            <th>{{ __('Effect From') }}</th>
            <th>{{ __('IsAdhoc') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            // Initialize totals
            $totals = array_fill(0, count($heads), 0);
            $total_gross = 0;
            $total_security = 0;
        @endphp

        @foreach ($employee_scales as $scale)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ !empty($scale->scale_no) ? $scale->scale_no : '-' }}</td>
                <td>{{ !empty($scale->department) ? $scale->department->name : '-' }}</td>

                @php
                    $net_gross = 0;
                    $basic_value = 0;
                @endphp

                @foreach ($heads as $hIndex => $account)
                    @php
                        $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
                        $value = !empty($headValue) ? $headValue->head_value : 0;

                        if ($account->head === 'Initial Basic') {
                            $basic_value = $value;
                        }

                        $net_gross += $value;
                        $totals[$hIndex] += $value;
                    @endphp
                    <td>{{ $value > 0 ? $value : '-' }}</td>
                @endforeach

                @php
                    $total_gross += $net_gross;
                    $security = $basic_value > 0 ? round($basic_value * 0.08, 2) : 0;
                    $total_security += $security;
                @endphp

                <td>{{ $net_gross }}</td>
                <td>{{ $security > 0 ? $security : '-' }}</td>
                <td>{{ $scale->effect_from ?? '-' }}</td>
                <td>{{ $scale->adhoc == 1 ? 'Yes' : 'No' }}</td>
                <td>{{ $scale->status == '1' ? 'Active' : 'In-Active' }}</td>
            </tr>
        @endforeach

        {{-- Totals Row --}}
        <tr>
            <td><strong>TOTAL</strong></td>
            <td></td>
            <td></td>
            @foreach ($totals as $total)
                <td><strong>{{ $total > 0 ? $total : '-' }}</strong></td>
            @endforeach
            <td><strong>{{ $total_gross }}</strong></td>
            <td><strong>{{ $total_security }}</strong></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        @include('student.exports.footer')
    </tbody>
</table>
