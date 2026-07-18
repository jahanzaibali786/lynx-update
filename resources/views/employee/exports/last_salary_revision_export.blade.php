<table>
    <thead>
        @include('student.exports.header', ['colspan' => count($taxableHeads) + 16])
        <tr>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('S.No') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Branch Name') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Date') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Emp No') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Emp Name') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Department') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Payscale No') }}</th>
            @foreach ($taxableHeads as $headName)
                <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __($headName) }}</th>
            @endforeach
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Other Income') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Gross Salary') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Previous Monthly Tax') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Previous Net Salary') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Current Monthly Tax') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Current Net Salary') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Tax Difference') }}</th>
            <th style="background-color: #1f385c; color: #ffffff; font-weight: bold; text-align: center;">{{ __('Net Salary Difference') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totals = [
                'heads' => array_fill_keys($taxableHeads, 0),
                'other_income' => 0,
                'gross' => 0,
                'oldTax' => 0,
                'oldNet' => 0,
                'newTax' => 0,
                'newNet' => 0,
                'taxChange' => 0,
                'netChange' => 0,
            ];
        @endphp
        @foreach ($data as $index => $row)
            @php
                $rowArr = (array) $row;
                $rowHeads = (array) ($rowArr['heads'] ?? []);
                
                $totals['other_income'] += ($rowArr['other_income'] ?? 0);
                $totals['gross'] += ($rowArr['gross'] ?? 0);
                $totals['oldTax'] += ($rowArr['oldTax'] ?? 0);
                $totals['oldNet'] += ($rowArr['oldNet'] ?? 0);
                $totals['newTax'] += ($rowArr['newTax'] ?? 0);
                $totals['newNet'] += ($rowArr['newNet'] ?? 0);
                $totals['taxChange'] += ($rowArr['taxChange'] ?? 0);
                $totals['netChange'] += ($rowArr['netChange'] ?? 0);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $rowArr['branch_name'] ?? '-' }}</td>
                <td>{{ $rowArr['date'] ?? '-' }}</td>
                <td>{{ $rowArr['employee_id'] ?? '-' }}</td>
                <td>{{ $rowArr['name'] ?? '-' }}</td>
                <td>{{ $rowArr['department'] ?? '-' }}</td>
                <td>{{ $rowArr['scale_no'] ?? '-' }}</td>
                @foreach ($taxableHeads as $headName)
                    @php
                        $val = (float) ($rowHeads[$headName] ?? 0);
                        $totals['heads'][$headName] += $val;
                    @endphp
                    <td>{{ $val }}</td>
                @endforeach
                <td>{{ $rowArr['other_income'] ?? 0 }}</td>
                <td>{{ $rowArr['gross'] ?? 0 }}</td>
                <td>{{ $rowArr['oldTax'] ?? 0 }}</td>
                <td>{{ $rowArr['oldNet'] ?? 0 }}</td>
                <td>{{ $rowArr['newTax'] ?? 0 }}</td>
                <td>{{ $rowArr['newNet'] ?? 0 }}</td>
                <td>{{ $rowArr['taxChange'] ?? 0 }}</td>
                <td>{{ $rowArr['netChange'] ?? 0 }}</td>
            </tr>
        @endforeach

        {{-- Totals Row --}}
        <tr>
            <td colspan="7" style="border: 1px solid black; background-color: #BFBFBF; text-align: center; font-weight: bold;">Total</td>
            @foreach ($taxableHeads as $headName)
                <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['heads'][$headName] }}</td>
            @endforeach
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['other_income'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['gross'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['oldTax'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['oldNet'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['newTax'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['newNet'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['taxChange'] }}</td>
            <td style="border: 1px solid black; background-color: #BFBFBF; text-align: right; font-weight: bold;">{{ $totals['netChange'] }}</td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')
