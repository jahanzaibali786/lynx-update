<table class="datatable">
    <thead>
        <tr>
            <td colspan="10"
                style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ __('Employee Scales Report') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ \Carbon\Carbon::now()->format('F Y') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="10" style="text-align: center;">
            </td>
        </tr>
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; ">
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray;">
                {{ __('Sr.') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray;">
                {{ __('ScaleNo') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 140px; background-color:gray;">
                {{ __('Scale Department') }}</th>
            @php
                $net_gross = 0;
            @endphp
            @foreach ($heads as $account)
                <th
                    style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 130px; background-color:gray;">
                    {{ !empty($account->head) ? @$account->head : '-' }}</th>
            @endforeach
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray;">
                {{ __('Gross Salary') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray;">
                {{ __('Employee Security  8% Of Basic') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 130px; background-color:gray;">
                {{ __('Effect From') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray;">
                {{ __('IsAdhoc') }}</th>
            <th
                style="font-size: 8px; text-align: center; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray;">
                {{ __('Status') }}</th>
        </tr>

    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach ($employee_scales as $scale)
            <tr>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $loop->iteration }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; text-align: left; border-collapse: collapse;">
                    {{ !empty($scale->scale_no) ? $scale->scale_no : '-' }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ !empty($scale->department) ? $scale->department->name : '-' }}
                </td>
    
                @php
                    $net_gross = 0;
                    $basic_value = 0;
                @endphp
                @foreach ($heads as $account)
                    @php
                        $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
                        $value = !empty($headValue) ? $headValue->head_value : 0;
    
                        // Identify the 'Basic' head by name (case-insensitive, trimmed)
                        if ($account->head === 'Initial Basic') {
                            $basic_value = $value;
                        }
    
                        $net_gross += $value;
                    @endphp
                    <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse; text-align: right;">
                        {{ $value > 0 ? number_format($value) : '-' }}
                    </td>
                @endforeach
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse; text-align: right;">
                    {{ number_format($net_gross) }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse; text-align: right;">
                    {{ $basic_value > 0 ? number_format($basic_value * 0.08, 2) : '-' }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $scale->effect_from ?? '-' }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $scale->adhoc == 1 ? 'Yes' : 'No' }}
                </td>
    
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $scale->status == '1' ? 'Active' : 'In-Active' }}
                </td>
            </tr>
        @endforeach
        @include('student.exports.footer')
    </tbody>
    

</table>
