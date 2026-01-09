<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr style="font-weight: 800; border: 2px solid black; background-color: gray;">
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Sr No.') }}
            </th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('B Sr No.') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Reg No #') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Roll No #') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Challan No #') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Admission Date') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Class') }}</th>

            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Student Name') }}</th>
            @foreach ($heads as $head)
                <th
                    style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                    {{ $head->fee_head ?? '-' }}</th>
            @endforeach
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Amount') }}</th>
            <th
                style="background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;">
                {{ __('Adm. Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $mainloop = 0; @endphp
        @foreach ($studentData as $branchId => $students)
                <tr>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;" colspan="10">
                        {{ $branches[$branchId] }}</td>
                </tr>
            @foreach ($students as $index => $student)
                @php
                    $studentRegNo = @$student->enrollId;
                    // Get pre-calculated challan data instead of querying database
                    $challanData = $studentChallanData[$studentRegNo] ?? [
                        'challan_no' => '',
                        'heads' => [],
                        'total' => 0,
                    ];
                @endphp

                
                <tr>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $mainloop++ }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $index + 1 }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$student->StudentRegistration->reg_no }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $student->enrollId ?? '' }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $challanData['challan_no'] }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ date('d M Y', strtotime($student->adm_date ?? '')) }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$student->class->name }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$student->StudentRegistration->stdname ?? '' }}</td>

                    @foreach ($heads as $head)
                        <td style="text-align: right; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">

                            @php
                                $amount = '';
                                foreach ($challanData['heads'] as $challhead) {
                                    if ($challhead['head_id'] == $head->id) {
                                        $amount = $challhead['amount'];
                                        break;
                                    }
                                }
                                echo $amount;
                            @endphp
                        </td>
                    @endforeach
                    <td style="text-align: right; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $challanData['total'] }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    @include('student.exports.footer')
</table>
