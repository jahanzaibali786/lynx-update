@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th rowspan="2">Sr #</th>
            <th rowspan="2">Roll #</th>
            <th rowspan="2">Name of Student</th>
            <th rowspan="2">Date of Birth</th>
            <th rowspan="2">Father Name</th>
            <th rowspan="2">Occupation</th>
            <th rowspan="2">Address</th>
            <th rowspan="2">Phone #</th>
            <th style="border:1px solid #000; text-align:center; background:#d3d3d3; font-weight: bold; font-size: 10px; text-align:center; width:250px; font-family:Calibri;"
                colspan="4">ADMISSION BRANCH</th>
            <th style="border:1px solid #000; text-align:center; background:#d3d3d3; font-weight: bold; font-size: 10px; text-align:center; width:250px; font-family:Calibri;"
                colspan="4">WITHDRAWAL BRANCH</th>
            <th rowspan="2">Receivable</th>
            <th rowspan="2">Payable</th>
            <th rowspan="2">Reason Of Leaving</th>
        </tr>
        <tr>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Session</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Branch</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Date</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Class</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Session</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Branch</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Date</th>
            <th
                style="border:1px solid #000; text-align:center; background:#e9e7e7; font-weight: bold; font-size: 8px; text-align:center; font-family:Calibri;">
                Class</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach ($all_data as $row)
            @php
                $student = $row->StudentRegistration;
                $withdrawal = $row->withdrawal;
            @endphp
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $student->roll_no ?? '' }}</td>
                <td>
                    <?php
                    $name = $student->stdname ?? '';
                    echo wordwrap($name, 16, '<br>', true);
                    ?>
                </td>

                <td>
                    {{ $student->dob ?? '' }}
                </td>
                <td>
                    {{ $student->fathername ?? '' }}
                </td>

                <td>
                    {{ $student->fatherprofession ?? '' }}
                </td>

                <td>
                    {{ $student->address ?? '' }}
                </td>

                <td>
                    <?php
                    $phones = $student->fatherphone ?? '';
                    if (!empty($phones)) {
                        $phoneNumbers = explode(',', $phones);
                        foreach ($phoneNumbers as $phone) {
                            echo trim($phone) . '<br>';
                        }
                    }
                    ?>
                </td>
                <!-- Admission Branch -->
                <td
                    style="border:1px solid #D3D3D3; text-align:center; font-size:8px; width:60px; white-space:normal; padding:3px; word-break:break-all; font-family:Calibri;">
                    {{ $row->adm_session ?? '' }}</td>
                <td
                    style="border:1px solid #D3D3D3; text-align:left; font-size:8px; width:150px; white-space:normal; padding:3px; word-wrap:break-word; word-break:break-all; font-family:Calibri;">
                    {{ $row->adm_branch ?? '' }}</td>
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri;">
                    {{ $row->adm_date ?? '' }}</td>
                <td style="border:1px solid #D3D3D3; text-align:left; font-size:8px; font-family:Calibri;">
                    {{ $row->class_id ?? '' }}</td>
                <!-- Withdrawal Branch -->
                <td
                    style="border:1px solid #D3D3D3; text-align:center; font-size:8px; width:60px; white-space:normal; padding:3px; word-break:break-all; font-family:Calibri;">
                    {{ $withdrawal->session->name ?? '' }}</td>
                <td
                    style="border:1px solid #D3D3D3; text-align:left; font-size:8px; width:150px; white-space:normal; padding:3px; word-wrap:break-word; word-break:break-all; font-family:Calibri;">
                    {{ $withdrawal->branch->name ?? '' }}</td>
                <td style="border:1px solid #D3D3D3; text-align:center; font-size:8px; font-family:Calibri;">
                    {{ $withdrawal->withdraw_date ?? '' }}</td>
                <td style="border:1px solid #D3D3D3; text-align:left; font-size:8px; font-family:Calibri;">
                    {{ $withdrawal->class->name ?? '' }}</td>
                <!-- Receivable and Payable -->
                <td>{{ $withdrawal->receivable ?? '' }}</td>
                <td>{{ $withdrawal->payable ?? '' }}</td>
                <td>{{ $withdrawal->reason ?? '' }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="19" style="background:#ffffff; height: 24px; font-family:Calibri;"></td>
        </tr>
        <tr>
            <td colspan="16"
                style="border:1px solid #000; border-top:6px double #000; border-bottom:6px double #000; font-weight:bold; text-align:center; background:#e9e7e7; font-size:8px; font-family:Calibri;">
                TOTAL</td>
            <td
                style="border:1px solid #000; border-top:6px double #000; border-bottom:6px double #000; font-weight:bold; text-align:right; background:#e9e7e7; font-size:8px; font-family:Calibri;">
                {{ $all_data->sum(function ($row) {return $row->withdrawal->receivable ?? 0;}) }}</td>
            <td
                style="border:1px solid #000; border-top:6px double #000; border-bottom:6px double #000; font-weight:bold; text-align:right; background:#e9e7e7; font-size:8px; font-family:Calibri;">
                {{ $all_data->sum(function ($row) {return $row->withdrawal->payable ?? 0;}) }}</td>
            <td
                style="border:1px solid #000; border-top:6px double #000; border-bottom:6px double #000; background:#e9e7e7; font-family:Calibri;">
            </td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')
