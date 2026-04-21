<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th >
                Sr No </th>
            <th>
                {{ __('Branch Name') }}</th>
            <th>
                {{ __('Emp No') }}</th>
            <th>
                {{ __('Name') }}</th>
            <th>
                {{ __('Father / Husband Name') }}</th>
            <th>
                {{ __('Designation') }}</th>
            <th>
                {{ __('Qualification') }}</th>
            <th>
                {{ __('CNIC') }}</th>
            <th>
                {{ __('EOBI No.') }}</th>
            <th>
                {{ __('PESSI NO') }}</th>
            {{-- <th> {{ __('Department') }}</th> --}}
            <th>
                {{ __('DOB') }}</th>
            <th>
                {{ __('DOJ') }}</th>
            <th>
                {{ __('Service Period') }}</th>
            <th>
                {{ __('Email') }}</th>
            <th>
                {{ __('Mobile') }}</th>
            <th>
                {{ __('Address') }}</th>
            {{-- <th>
                {{ __('Pay Scale') }}</th>
            <th>
                {{ __('Gross Salary') }}</th>
            <th>
                {{ __('Net Salary') }}</th> --}}
            <!-- {{-- <th> {{__('Last Login')}}</th> --}} -->
            {{-- <th>{{__('Action')}}</th> --}}

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
                <td >
                    {{ $loop->iteration }}</td>
                @if ($employee->owned_by)
                    <td >
                        {{ !empty($employee->ownedBranch) ? $employee->ownedBranch->name : '' }}
                    </td>   
                @else
                    <td >-</td>
                @endif
                <td >

                    {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}

                </td>
                <td >{{ $employee->name }}</td>
                <td >{{ $employee->f_name }}</td>
                @if ($employee->designation_id)
                    <td >
                        {{ !empty($employee->designation) ? $employee->designation->name : '' }}
                    </td>
                @else
                    <td >-</td>
                @endif
                {{-- @if ($employee->qualification) --}}
                <td >
                    {{ !empty($employee->latestEducation) ? $employee->latestEducation->degree : '-' }}
                </td>
                {{-- @else
                    <td >-</td>
                @endif --}}

                <td >{{ $employee->cnic }}</td>
                <td >{{ $employee->eobi_id ?? '-' }}</td>
                 <td >{{ $employee->ssc_id ?? '-' }}</td>
                </td>
                {{-- <td >{{ $employee->pessi ?? '-' }}
                </td> --}}
                <td >
                    @if ($employee->dob)
                        {{ \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($employee->dob)) }}
                    @else
                        -
                    @endif
                </td>
                {{-- @if ($employee->department_id)
                    <td >
                        {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '' }}
                    </td>
                    @else
                    <td >-</td>
                    @endif --}}
                <td >
                    @if ($employee->company_doj)
                        {{ \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($employee->company_doj)) }}
                    @else
                        -
                    @endif
                </td>
                <td >
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

                <td >{{ $employee->email }}</td>
                <td>
                    {{ $employee->phone }}</td>
                <td >{{ $employee->address }}</td>

                {{-- @if ($lastscale)
                    <td >
                        {{ $lastscale->scale ? $lastscale->scale->scale_no : '' }}</td>
                @else
                    <td >-</td>
                @endif
                @if ($monthlysalary)
                    <td >
                        {{ !empty($monthlysalary) ? $monthlysalary->gross : '' }}</td>
                @else
                    <td >-</td>
                @endif
                @if ($lastscale)
                    <td >
                        {{ $lastscale ? $lastscale->net : '0' }}</td>
                @else
                    <td >0</td>
                @endif --}}
            </tr>
            @php
                $totalgross += @$monthlysalary->gross ?? 0;
                $totalnet += @$lastscale->net ?? 0;
            @endphp
        @endforeach
        {{-- <tr>
            <td colspan="9">
                Total</td>
            <td>
                {{ $totalgross }}</td>
            <td>
                {{ $totalnet }}</td>
        </tr> --}}
        @include('student.exports.footer')
    </tbody>
</table>
