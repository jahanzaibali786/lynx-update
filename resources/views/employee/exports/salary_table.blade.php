
<table class="datatable">
    <thead>
        <tr>
            {{-- <td style="width: 75px;" rowspan="3"></td>
            <td rowspan="3" style="text-align: center;">
                <img src="{{ public_path('assets/images/lynx2.jpg') }}" alt="School Logo" width="75px" height="75px" style="width: 50px; height: 50px;">
            </td> --}}
            <td colspan="7" style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span >{{ __('Salary History Report') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span >{{ \Carbon\Carbon::now()->format('F Y') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center;">
            </td>
        </tr>
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; ">
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Name') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('FatherName') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Designation') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Scale') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Effect From') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Gross') }}</th>
            <th style="font-size: 15rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray;">{{ __('Net') }}</th>
        </tr>

    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach($employees as $employee)
            @php
                $lastPayscaleDetail = $employee->employee_payscale_details->first();
            @endphp
            <tr style="border: 2px solid black; border-collapse: collapse;">
                <td style="border: 2px solid black; border-collapse: collapse;">{{ @$employee->name }}</td>
                <td style="border: 2px solid black; border-collapse: collapse;">{{ @$employee->f_name ?? 'N/A' }}</td>
                @if($employee->designation_id)
                    <td class="font-style" style="border: 2px solid black; border-collapse: collapse;">
                        {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '' }}
                    </td>
                @else
                    <td style="border: 2px solid black; border-collapse: collapse;">-</td>
                @endif
                <td style="border: 2px solid black; border-collapse: collapse;">{{ !empty($lastPayscaleDetail->scale) ? $lastPayscaleDetail->scale->scale_no : '' }}</td>
                <td style="border: 2px solid black; border-collapse: collapse;">{{ !empty($lastPayscaleDetail->scale) ? $lastPayscaleDetail->scale->effect_from : '' }}</td>
                <td style="border: 2px solid black; border-collapse: collapse;">{{ !empty($lastPayscaleDetail) ? $lastPayscaleDetail->net + $lastPayscaleDetail->emp_sec : '0' }}</td>
                <td style="border: 2px solid black; border-collapse: collapse;">{{ !empty($lastPayscaleDetail) ? $lastPayscaleDetail->net : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
