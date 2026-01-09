{{-- @dd($resignations[0]->owned_by) --}}
<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 5px solid black; border-collapse: collapse; background-color:#CCCCCC;">
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 50px; background-color:#CCCCCC; text-align: center;">
                Sr No </th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Branch Name') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Emp No') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Name') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Father / Husband Name') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Designation') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Qualification') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('CNIC') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('DOB') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('DOJ') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Resigned Date') }}</th>
            <th
                style="font-size: 8px; font-family: Arial, Calibri, sans-serif; font-weight: 800; border: 20px solid black; border-collapse: collapse; width: 150px; background-color:#CCCCCC; text-align: center;">
                {{ __('Service Period') }}</th>
            <!-- {{-- <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;"> {{__('Last Login')}}</th> --}} -->
            {{-- <th  style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{__('Action')}}</th> --}}

        </tr>

    </thead>
    <tbody style="">
        @php
            $totalgross = 0;
            $totalnet = 0;
        @endphp
        @foreach ($resignations as $resignation)
            @php
                $lastscale = $resignation->employee->employee_payscale_details->last();
                $monthlysalary = $resignation->employee->employee_monthly_salaries->last();

            @endphp
            <tr>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif; text-align:center;">
                    {{ $loop->iteration }}</td>
                @if ($resignation->owned_by)
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                        {{ !empty(\Auth::user()->getBranch($resignation->owned_by)) ? \Auth::user()->getBranch($resignation->owned_by)->name : '' }}
                    </td>
                @else
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">-</td>
                @endif
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif; text-align:center;">

                    {{ \Auth::user()->employeeIdFormat($resignation->employee_id) }}

                </td>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">{{ $resignation->employee->name }}</td>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">{{ $resignation->employee->f_name }}</td>
                @if ($resignation->employee->designation_id)
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                        {{ !empty(\Auth::user()->getDesignation($resignation->employee->designation_id)) ? \Auth::user()->getDesignation($resignation->employee->designation_id)->name : '-' }}
                    </td>
                @else
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">-</td>
                @endif
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                    {{ !empty(\Auth::user()->getQualification($resignation->employee_id)) ? \Auth::user()->getQualification($resignation->employee_id) : '-' }}
                </td>

                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">{{ $resignation->employee->cnic }}</td>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">{{ $resignation->employee->dob ?? '-' }}
                </td>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                    @if ($resignation->employee->company_doj)
                        {{ \Carbon\Carbon::parse($resignation->employee->company_doj)->format('d-M-Y') }}
                    @else
                        -
                    @endif
                </td>
                <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                    {{ $resignation->notice_date ? \Auth::user()->dateFormat($resignation->notice_date) : '-' }}
                </td>
                <td style="font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                    @if ($resignation->employee->company_doj && $resignation->notice_date)
                        @php
                            $doj = \Carbon\Carbon::parse($resignation->employee->company_doj);
                            $now = \Carbon\Carbon::now();
                            $diff = $doj->diff($resignation->notice_date);
                        @endphp
                        {{ $diff->y }} Y {{ "&" }} {{ $diff->m }} M
                    @else
                        -
                    @endif
                </td>

                {{-- @if ($lastscale)
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                        {{ $lastscale->scale ? $lastscale->scale->scale_no : '' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">-</td>
                @endif
                @if ($monthlysalary)
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                        {{ !empty($monthlysalary) ? $monthlysalary->gross : '' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">-</td>
                @endif
                @if ($lastscale)
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">
                        {{ $lastscale ? $lastscale->net : '0' }}</td>
                @else
                    <td style=" font-size: 8px; font-family: Arial, Calibri, sans-serif;">0</td>
                @endif --}}
            </tr>
            @php
                $totalgross += @$monthlysalary->gross ?? 0;
                $totalnet += @$lastscale->net ?? 0;
            @endphp
        @endforeach
        @include('student.exports.footer')
    </tbody>
</table>
