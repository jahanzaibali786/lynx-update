<table>
    <thead>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 5px solid black; border-collapse: collapse; background-color:#CCCCCC;">
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 50px; background-color:#CCCCCC; text-align: center;">
                Sr No </th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Branch Name') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Emp No') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Name') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Father / Husband Name') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Designation') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Qualification') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('CNIC') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('EOBI No.') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('PESSI NO') }}</th>
            {{-- <th
                    style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                    {{ __('Department') }}</th> --}}
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('DOB') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('DOJ') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Service Period') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Email') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Mobile') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Address') }}</th>
            {{-- <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Pay Scale') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Gross Salary') }}</th>
            <th
                style="font-size: 8px; font-family: Calibri; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Net Salary') }}</th> --}}
            <!-- {{-- <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;"> {{__('Last Login')}}</th> --}} -->
            {{-- <th  style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{__('Action')}}</th> --}}

        </tr>

    </thead>
    <tbody>
        @php
            $totalgross = 0;
            $totalnet = 0;
        @endphp
        {{-- @dd($employees[0]) --}}
        @foreach ($employees as $employee)
            @php
                $lastscale = $employee->employee_payscale_details->last();
                $monthlysalary = $employee->employee_monthly_salaries->last();

            @endphp
            <tr>
                <td style=" font-size: 8px; font-family: Calibri; text-align:center;">
                    {{ $loop->iteration }}</td>
                @if ($employee->owned_by)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ !empty(\Auth::user()->getBranch($employee->owned_by)) ? \Auth::user()->getBranch($employee->owned_by)->name : '' }}
                    </td>
                @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                @endif
                <td style=" font-size: 8px; font-family: Calibri; text-align:center;">

                    {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}

                </td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->name }}</td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->f_name }}</td>
                @if ($employee->designation_id)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '' }}
                    </td>
                @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                @endif
                {{-- @if ($employee->qualification) --}}
                <td style=" font-size: 8px; font-family: Calibri;">
                    {{ !empty(\Auth::user()->getQualification($employee->id)) ? \Auth::user()->getQualification($employee->id) : '-' }}
                </td>
                {{-- @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                @endif --}}

                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->cnic }}</td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->eobi_employer }}
                </td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->pessi ?? '-' }}
                </td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->dob ?? '-' }}
                </td>
                {{-- @if ($employee->department_id)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '' }}
                    </td>
                    @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                    @endif --}}
                <td style=" font-size: 8px; font-family: Calibri;">
                    @if ($employee->company_doj)
                        {{ \Carbon\Carbon::parse($employee->company_doj)->format('d-M-Y') }}
                    @else
                        -
                    @endif
                </td>
                <td style="font-size: 8px; font-family: Calibri;">
                    @if ($employee->company_doj)
                        @php
                            $doj = \Carbon\Carbon::parse($employee->company_doj);
                            $now = \Carbon\Carbon::now();
                            $diff = $doj->diff($now);
                        @endphp
                        {{ $diff->y }} Y {{ "&" }} {{ $diff->m }} M
                    @else
                        -
                    @endif
                </td>

                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->email }}</td>
                <td style=" font-size: 8px; font-family: Calibri; text-align: right;">
                    {{ $employee->phone }}</td>
                <td style=" font-size: 8px; font-family: Calibri;">{{ $employee->address }}</td>

                {{-- @if ($lastscale)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ $lastscale->scale ? $lastscale->scale->scale_no : '' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                @endif
                @if ($monthlysalary)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ !empty($monthlysalary) ? $monthlysalary->gross : '' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Calibri;">-</td>
                @endif
                @if ($lastscale)
                    <td style=" font-size: 8px; font-family: Calibri;">
                        {{ $lastscale ? $lastscale->net : '0' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Calibri;">0</td>
                @endif --}}
            </tr>
            @php
                $totalgross += @$monthlysalary->gross ?? 0;
                $totalnet += @$lastscale->net ?? 0;
            @endphp
        @endforeach
        {{-- <tr>
            <td colspan="9"
                style="text-align: right; background-color: gray; border: 20px solid black; border-collapse: collapse; font-size: 8px; font-weight:bolder; font-family: Calibri;">
                Total</td>
            <td
                style=" background-color: gray; border: 20px solid black; border-collapse: collapse; font-size: 8px; font-family: Calibri;  font-weight:bolder;">
                {{ $totalgross }}</td>
            <td
                style=" background-color: gray; border: 20px solid black; border-collapse: collapse; font-size: 8px; font-family: Calibri;  font-weight:bolder;">
                {{ $totalnet }}</td>
        </tr> --}}
        @include('student.exports.footer')
    </tbody>
</table>
