<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; ">
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Sr. No.') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Emp Code') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Employee Name') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Father Name') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('CNIC') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('EOBI No.') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('D.O.J') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('D.O.B') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Age') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Monthly Wages') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Working Days') }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __("Employee's Cont.") }}</th>
            <th
                style="font-size: 8px; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; text-align: center;">
                {{ __('Employer Cont.') }}</th>
        </tr>

    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @php
            $eobigrandTotal = 0;
            $eobi_employergrandTotal = 0;
        @endphp

        @foreach ($reportData as $branchName => $employees)
            <tr>
                <th colspan="13" style="border: 2px solid black; border-collapse: collapse;">{{ $branchName }}</th>
            </tr>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $index + 1 }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->employee_id }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->employee->name }}
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->employee->f_name }}
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->employee->cnic }}
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;"></td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">
                        {{ \Carbon\Carbon::parse($employee->employee->company_doj)->format('d F, Y') }}
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">
                        {{ \Carbon\Carbon::parse($employee->employee->dob)->format('d F, Y') }}
                    </td>
                    @php
                        $dob = \Carbon\Carbon::parse($employee->employee->dob);
                        $now = \Carbon\Carbon::now();
                        $ageyears = $now->diffInYears($dob);
                        $agemonths = $now->diffInMonths($dob) % 12;
                    @endphp
                    {{-- <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">
                        {{ $ageyears }} Y & {{ $agemonths }} M
                    </td> --}}
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">
                        {{ $ageyears }} Y {{ '&' }} {{ $agemonths }} M
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px; text-align: right;">
                        {{ number_format($employee->basics, 2) }}
                    </td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->sal_days }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->eobi }}</td>
                    <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px;">{{ $employee->eobi_employer }}</td>
                    {{-- @dd($dob, $now, $ageyears, $agemonths); --}}
                </tr>
                @php
                    $eobigrandTotal += $employee->eobi;
                    $eobi_employergrandTotal += $employee->eobi_employer;
                @endphp
            @endforeach
            <tr>
                <td colspan="11" style="border: 2px solid black; border-collapse: collapse; font-size: 8px;"><b>Branch Total</b></td>
                <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px; text-align: right;">
                    <b>{{ number_format($employees->sum('eobi'), 2) }}</b>
                </td>
                <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px; text-align: right;">
                    <b>{{ number_format($employees->sum('eobi_employer'), 2) }}</b>
                </td>
            </tr>
        @endforeach
        <tr>
            <td colspan="11" style="border: 2px solid black; border-collapse: collapse; font-size: 8px;"><b>Grand Total</b></td>
            <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px; text-align: right;">
                <b>{{ number_format($eobigrandTotal, 2) }}</b>
            </td>
            <td style="border: 2px solid black; border-collapse: collapse; font-size: 8px; text-align: right;">
                <b>{{ number_format($eobi_employergrandTotal, 2) }}</b>
            </td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table>
