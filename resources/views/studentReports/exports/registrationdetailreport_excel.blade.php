<table>
    <thead>
        <tr>
            <th colspan="14" style="font-weight:bold; text-align:center;">{{ $reportName }}</th>
        </tr>
        <tr>
            <th colspan="5" style="text-align:left;">
                {{ __('Period From') }}:
                {{ !empty($params['date_from']) ? date('d M Y', strtotime($params['date_from'])) : '-' }}
            </th>
            <th colspan="4" style="text-align:center;">
                {{ __('Branch') }}: {{ $branchName ?? 'All Branches' }}
            </th>
            <th colspan="5" style="text-align:right;">
                {{ __('Period To') }}:
                {{ !empty($params['date_to']) ? date('d M Y', strtotime($params['date_to'])) : '-' }}
            </th>
        </tr>
        <tr>
            <th>{{ __('Sr No.') }}</th>
            <th>{{ __('Report Source') }}</th>
            <th>{{ __('Report Branch') }}</th>
            <th>{{ __('Reg. #') }}</th>
            <th>{{ __('Student Name') }}</th>
            <th>{{ __('Reg. Date') }}</th>
            <th>{{ __('Reg Class') }}</th>
            <th>{{ __('Class Branch') }}</th>
            <th>{{ __('Reg Branch Id') }}</th>
            <th>{{ __('Registration Owned By') }}</th>
            <th>{{ __('Challan Owned By') }}</th>
            <th>{{ __('Added By') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Reg. Fee') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $globalIndex = 1; @endphp
        @foreach ($studentData as $branchId => $students)
            <tr>
                <td colspan="14" style="font-weight:bold; background:#d9d9d9;">
                    {{ $branches[$branchId] ?? ('Branch ID: ' . $branchId) }}
                </td>
            </tr>
            @foreach ($students as $student)
            @dd($student);
                <tr>
                    <td>{{ $globalIndex++ }}</td>
                    <td>{{ $student->report_source ?? '-' }}</td>
                    <td>{{ $branches[$student->owned_by] ?? $student->owned_by }}</td>
                    <td>{{ $student->reg_no }}</td>
                    <td>{{ $student->stdname }}</td>
                    <td>{{ !empty($student->regdate) ? date('d M Y', strtotime($student->regdate)) : '' }}</td>
                    <td>{{ @$student->class->name }}</td>
                    <td>{{ $student->class_branch_name ?? ($branches[$student->class_branch_id] ?? ($student->class_branch_id ?? '-')) }}</td>
                    <td>{{ $student->registration_reg_branch_id ?? $student->reg_branch_id ?? '-' }}</td>
                    <td>{{ $student->registration_owned_by_name ?? ($branches[$student->registration_owned_by] ?? ($student->registration_owned_by ?? $student->owned_by ?? '-')) }}</td>
                    <td>{{ $student->challan_owned_by_name ?? ($branches[$student->challan_owned_by] ?? ($student->challan_owned_by ?? '-')) }}</td>
                    <td>{{ $student->registration_added_by ?? $student->added_by ?? '-' }}</td>
                    <td>{{ $student->student_status }}</td>
                    <td>{{ $student->registrationfee }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="13" style="text-align:right; font-weight:bold;">Branch Total</td>
                <td>{{ $branchTotals[$branchId] ?? 0 }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="13" style="text-align:right; font-weight:bold;">Grand Total</td>
            <td>{{ $grandTotal }}</td>
        </tr>
    </tbody>
</table>
