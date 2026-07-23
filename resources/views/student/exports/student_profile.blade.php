<table class="datatable">
    <thead>
        <style>
            tr th, tr td { font-size: 8px !important; font-family: calibri !important; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        </style>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr No') }}</th>
            <th>{{ __('Reg. No') }}</th>
            <th>{{ __('Roll No') }}</th>
            <th>{{ __('Reg Date') }}</th>
            <th>{{ __('Student Name') }}</th>
            <th>{{ __('Father Name') }}</th>
            <th>{{ __('Mother Name') }}</th>
            <th>{{ __('DOB') }}</th>
            <th>{{ __('Gender') }}</th>
            <th>{{ __('Religion') }}</th>
            <th>{{ __('Nationality') }}</th>
            <th>{{ __('Father CNIC') }}</th>
            <th>{{ __('Father Phone') }}</th>
            <th>{{ __('Father Cell') }}</th>
            <th>{{ __('Mother CNIC') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('City') }}</th>
            <th>{{ __('District') }}</th>
            <th>{{ __('Address') }}</th>
            <th>{{ __('Prev School') }}</th>
            <th>{{ __('Prev Class') }}</th>
            <th>{{ __('Session') }}</th>
            <th>{{ __('Class') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Reg. Option') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Reg. Fee') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @foreach ($students as $index => $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->reg_no }}</td>
                <td>{{ $student->roll_no }}</td>
                <td>{{ $student->regdate == '0000-00-00' || !$student->regdate ? '' : date('d-M-Y', strtotime($student->regdate)) }}</td>
                <td>{!! str_replace(' ', '&nbsp;', e($student->stdname)) !!}</td>
                <td>{!! str_replace(' ', '&nbsp;', e($student->fathername)) !!}</td>
                <td>{!! str_replace(' ', '&nbsp;', e($student->mothername)) !!}</td>
                <td>{{ $student->dob == '0000-00-00' || !$student->dob ? '' : date('d-M-Y', strtotime($student->dob)) }}</td>
                <td>{{ strtoupper($student->gender) }}</td>
                <td>{{ $student->religion }}</td>
                <td>{{ $student->nationality }}</td>
                <td>{{ $student->fathercnic }}</td>
                <td>{{ $student->fatherphone }}</td>
                <td>{{ $student->fathercell }}</td>
                <td>{{ $student->mothercnic }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->city }}</td>
                <td>{{ $student->district }}</td>
                <td>{!! str_replace(' ', '&nbsp;', e($student->address)) !!}</td>
                <td>{{ $student->prevschool }}</td>
                <td>{{ $student->prevclass }}</td>
                <td>{{ !empty($student->session) ? $student->session->year : '-' }}</td>
                <td>{{ !empty($student->class) ? $student->class->name : '-' }}</td>
                <td>{{ !empty($student->branches) ? $student->branches->name : '-' }}</td>
                <td>{{ @$student->registeroption->name }}</td>
                <td>{{ $student->roll_no ? 'Enrolled' : 'Not Enrolled' }}</td>
                <td style="text-align:right;">{{ number_format((float)$student->registrationfee, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
