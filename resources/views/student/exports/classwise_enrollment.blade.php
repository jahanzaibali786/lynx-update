<style>
    .datatable th,
    .datatable td {
        font-family: Arial, Helvetica, sans-serif !important;
    }

    .tr th {
        background-color: gray;
        font-weight: bold;
        text-align: center;
        border: 2px solid black;
        border-collapse: collapse;
    }

    .datatable tbody {
        font-size: 8px !important;
    }
</style>

<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr style="font-weight: 800; border: 2px solid black; background-color: gray;">
            <th
                style="width: 50px; background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Sr No') }}</th>
            <th
                style="width: 50px;background-color: gray; font-weight: bold; text-align: left; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Br No') }}</th>
            <th
                style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Reg No') }}</th>
            <th
                style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Roll No') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Student name') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('FatherName') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Class') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Section') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Session') }}</th>
            <th
                style="width: 150px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Admission Date') }}</th>
            <th
                style="width:100px; background-color:gray; font-weight:bold; text-align:center; border:2px solid black;">
                {{ __('DOB') }}
            </th>

            <th style="width:70px; background-color:gray; font-weight:bold; text-align:center; border:2px solid black;">
                {{ __('Age') }}
            </th>
        </tr>
    </thead>
    <tbody>
        @php $enrollments =$enrollments->sortBy('class_id')->sortBy('section_id')->groupBy('owned_by'); @endphp
        @php $tot=1; @endphp
        @foreach ($enrollments as $bra => $enro)
            <tr style="font-weight: 800; border: 2px solid black; background-color: gray;">
                <td colspan="12"
                    style="text-align: left; font-size: 8px; font-weight: 800; font-family: Arial, Helvetica, sans-serif;  border: 2px solid black; background-color: gray;">
                    {{ $branches[$bra] }}</td>
            </tr>
            @foreach ($enro as $enrollment)
                @php
                    $studentData = App\Models\StudentRegistration::with(
                        'enrollment.class',
                        'enrollment.section',
                        'session',
                    )
                        ->where('id', $enrollment->regId)
                        ->first();
                @endphp
                <tr>
                    <td style="text-align: center;font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$tot++ }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ $loop->iteration }}</td>
                    <td style="text-align: center;font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$studentData->reg_no }}</td>
                    <td style="text-align: center;font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->enrollId }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->StudentRegistration->stdname }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->StudentRegistration->fathername }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->class->name }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->section->name }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->session->year }}</td>
                    <td style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ @$enrollment->adm_date }}</td>
                    <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                        {{ !empty($studentData->dob) && $studentData->dob != '0000-00-00'
                            ? \Carbon\Carbon::parse($studentData->dob)->format('M d Y')
                            : '-' }}
                    </td>
                    <td
                        style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif; white-space: normal;">
                        @if ($studentData->dob && $studentData->dob != '0000-00-00')
                            @php
                                $dob = \Carbon\Carbon::parse($studentData->dob);
                                $diff = $dob->diff(\Carbon\Carbon::now());
                                $years = $diff->y;
                                $months = $diff->m;
                            @endphp

                            {{ $years }} years {{ $months }} <br> month
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
        @include('student.exports.footer')
    </tbody>
</table>
