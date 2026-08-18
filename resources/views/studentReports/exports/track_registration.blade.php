<style>
    .datatable th,
    .datatable td {
        font-family: calibri;
        font-size: 8px;
    }
</style>
<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr>
            <th>Sr#</th>
            <th>Reg No</th>
            <th>Roll No</th>
            <th>Adm Date</th>
            <th>Student Name</th>
            <th>Father Name</th>
            <th>Registration Branch</th>
            <th>Current Branch</th>
            <th>Current Class</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $i => $student)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $student->StudentRegistration->reg_no ?? '-' }}</td>
                <td>{{ $student->StudentRegistration->roll_no ?? ($student->enrollId ?? '-') }}</td>
                <td>{{ !empty($student->StudentRegistration->regdate) ? date('d M Y', strtotime($student->StudentRegistration->regdate)) : '-' }}</td>
                <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>
                <td>{{ $student->StudentRegistration->fathername ?? '-' }}</td>
                <td>{{ $student->admbranch->name ??  '-' }}</td>
                <td>{{ $student->branch->name ?? '-' }}</td>
                <td>{{ $student->class->name ?? ($student->StudentRegistration->class->name ?? '-') }}</td>
                <td>{{ $student->StudentRegistration->student_status ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
