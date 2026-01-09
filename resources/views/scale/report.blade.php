<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report</title>
    <style>

        table,
        tbody,
        th,
        tr,
        td {
            font-size: 0.7rem;
            border: 1px solid black;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            margin: 0 auto;
        }

        th {
            background-color: #cdcaca;
        }
    </style>
</head>

<body>
    <table class="" style="font-size: 1rem; border:1px solid black; border-collapse:collapse;">
        <thead style="font-size: 1rem;">
            <tr class="table_heads" style="border:1px solid black; border-collapse:collapse;">
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Sr.') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('ScaleNo') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Department') }}</th>

                @php
                    $net_gross = 0;
                @endphp
                @foreach ($heads as $account)
                    <th style="border:1px solid black; border-collapse:collapse;">
                        {{ !empty($account->head) ? @$account->head : '-' }}</th>
                @endforeach
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Net Gross') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Emp. Sec. 8% of Gross') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Employee Eobi Cont.') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Inc. Tax') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Net Payable') }}</th>
                {{-- <th style="border:1px solid black; border-collapse:collapse;">{{ __('Effect From') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('IsAdhoc') }}</th>
                <th style="border:1px solid black; border-collapse:collapse;">{{ __('Status') }}</th> --}}
            </tr>
        </thead>
        <tbody class="font-style">
            @foreach ($employee_scales as $scale)
                <tr style="border:1px solid black; border-collapse:collapse;">
                    <td style="border:1px solid black; border-collapse:collapse;">{{ $scale->id }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;">
                        {{ !empty($scale->scale_no) ? $scale->scale_no : '' }}</td>
                    <td style="border:1px solid black; border-collapse:collapse; text-transform: lowercase;">
                        {{ !empty($scale->department) ? $scale->department->name : '' }}</td>
                    @php
                        $net_gross = 0;
                        $emplastscal = @$scale->employeepayScaledetailHeads->last();
                        $employeedata = \App\Models\Employee::where(
                            'employee_id',
                            @$scale->employeepayScaledetailHeads->last()->employee_id ?? null,
                        )->first();
                    @endphp
                    @foreach ($heads as $account)
                        @php
                            $headValue = $scale->employeeScaleHeads->firstWhere('head', $account->id);
                            $net_gross += $headValue ? $headValue->head_value : 0;
                            if ($account->head == 'Initial Basic') {
                                $headValue = @$scale->employeeScaleHeads->firstWhere('head', $account->id);
                                $basic = $headValue ? $headValue->head_value : 0;
                            }
                        @endphp
                        <td style="border:1px solid black; border-collapse:collapse;">
                            {{ $headValue ? $headValue->head_value : '-' }}</td>
                    @endforeach
                    <td style="border:1px solid black; border-collapse:collapse;">{{ $net_gross }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;">{{ isset($basic) ? ($basic * 8) / 100 : 0 }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;">{{ @$employeedata->eobi ?? '-' }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;">{{ @$emplastscal->itax ?? '-' }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;">{{ @$emplastscal->net ?? '-' }}</td>
                    {{-- <td style="border:1px solid black; border-collapse:collapse;">{{ $scale->effect_from }}</td>
                    
                    <td style="border:1px solid black; border-collapse:collapse;">
                        {{ $scale->adhoc == 1 ? 'Yes' : 'No' }}</td>
                    <td style="border:1px solid black; border-collapse:collapse;"> --}}
                    {{-- {{ $scale->status == '1' ? 'Active' : 'In-Active' }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
