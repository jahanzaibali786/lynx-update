<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
        </style>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif; ">
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Reg No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:left; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Reg Date') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 200px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Student name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 200px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Father Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 150px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Cell No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Session') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Class') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('DOB') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Gender') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Status') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Register Option') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @foreach ($registrations as $registration)
            <tr style="">
                <td
                    style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ $loop->iteration }}</td>
                <td
                    style=" text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->reg_no }}</td>
                <td
                    style=" text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ $registration->regdate == '0000-00-00' || !$registration->regdate ? '' : date('d-M-Y', strtotime($registration->regdate)) }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->stdname }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->fathername }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->fathercell }}</td>
                {{-- <td style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;" style="white-space: normal !important;">{{ $registration->email }}</td> --}}
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ !empty(@$registration->session) ? @$registration->session->year : '-' }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ !empty(@$registration->class) ? @$registration->class->name : '-' }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                   {{ $registration->dob == '0000-00-00' || !$registration->dob ? '' : date('d-M-Y', strtotime($registration->dob)) }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->gender }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->student_status }}</td>
                <td
                    style=" text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">
                    {{ @$registration->registeroption->name }}</td>
            </tr>
        @endforeach
        @include('student.exports.footer');
    </tbody>
</table>
