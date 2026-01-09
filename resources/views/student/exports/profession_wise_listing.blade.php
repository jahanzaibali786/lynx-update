<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profession Wise Listing Report</title>
</head>
<body>
    @include('student.exports.header')
    
        <table>
            <thead>
                <tr>
                    <th>{{ __('Sr#') }}</th>
                    <th>{{ __('Br.Sr#') }}</th>
                    <th>{{ __('Roll#') }}</th>
                    <th>{{ __('Student Name') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Father Name') }}</th>
                    <th>{{ __('Father Profession') }}</th>
                    <th>{{ __('Contact#') }}</th>
                    <th>{{ __('Mother Name') }}</th>
                    <th>{{ __('Mother Profession') }}</th>
                    <th>{{ __('Contact#') }}</th>
                    <th>{{ __('Guardian Name') }}</th>
                    <th>{{ __('Guardian Profession') }}</th>
                    <th>{{ __('Contact#') }}</th>
                    <th>{{ __('Home Address') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>

            <tbody>
                @php $sr = 1; @endphp
                @foreach ($groupedStudents as $branchId => $professions)
                @foreach ($professions as $profession => $students)
                    @foreach ($students as $idx => $student)
                        @if($loop->first)
                        <tr>
                            <td colspan="4" style="font-weight:bold; background-color:#d6d3d3; font-size:8px; padding:3px;">
                                {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                            </td>
                            <td colspan="12" style="font-weight:bold; background-color:#d6d3d3; font-size:8px; padding:3px;">
                            </td>
                        </tr>
                        @endif
                        
                        <tr>
                            <td>{{ $sr++ }}</td>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $student->enrollId ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->stdname ?? '-' }}</td>
                            <td>{{ $student->class->name ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->fathername ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->fatherprofession ?? '-' }}</td>
                            <td>
                                @php $phones = explode(',', $student->StudentRegistration->fatherphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td>{{ $student->StudentRegistration->mothername ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->motherprofession ?? '-' }}</td>
                            <td>
                                @php $phones = explode(',', $student->StudentRegistration->motherphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td>{{ $student->StudentRegistration->guardianname ?? '-' }}</td>
                            <td>{{ $student->StudentRegistration->guardianprofession ?? '-' }}</td>
                            <td>
                                @php $phones = explode(',', $student->StudentRegistration->guardianphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td>{{ $student->StudentRegistration->address ?? '-' }}</td>
                            <td>{{ $student->active_status == 1 ? 'Active' : 'WDR' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                @endforeach
            </tbody>
        </table>
    
    @include('student.exports.footer')
</body>
</html>