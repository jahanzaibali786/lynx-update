<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profession Wise Listing Report</title>
</head>
<body>
    @include('student.exports.header')
    
    @foreach ($groupedStudents as $branchId => $professions)
        <table style="margin-bottom:10px; width:100%; border-collapse:collapse;">
            <thead>
                <tr style="font-size:8px; font-weight:800; background-color:gray; font-family:calibri;">
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Sr#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Br.Sr#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Roll#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Student Name') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Class') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Father Name') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Father Profession') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Contact#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Mother Name') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Mother Profession') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Contact#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Guardian Name') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Guardian Profession') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Contact#') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Home Address') }}</th>
                    <th style="text-align:center; font-family:calibri; border:2px solid #000; background-color:gray; font-size:8px; word-break:break-word; white-space:normal; padding:3px;">{{ __('Status') }}</th>
                </tr>
            </thead>

            <tbody style="font-size:8px; font-family:calibri;">
                @php $sr = 1; @endphp
                @foreach ($professions as $profession => $students)
                    @foreach ($students as $idx => $student)
                        @if($loop->first)
                        <tr style="background-color:#d6d3d3;">
                            <td colspan="16" style="font-weight:bold; background-color:#d6d3d3; font-size:8px; padding:3px;">
                                {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                            </td>
                        </tr>
                        @endif
                        
                        <tr style="border:2px solid #D3D3D3;">
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:40px; word-break:break-word; white-space:normal; padding:3px;">{{ $sr++ }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:40px; word-break:break-word; white-space:normal; padding:3px;">{{ $idx + 1 }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:40px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->enrollId ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->stdname ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:80px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->class->name ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->fathername ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->fatherprofession ?? '-' }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:80px; word-break:break-word; white-space:normal; padding:3px;">
                                @php $phones = explode(',', $student->StudentRegistration->fatherphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->mothername ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->motherprofession ?? '-' }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:80px; word-break:break-word; white-space:normal; padding:3px;">
                                @php $phones = explode(',', $student->StudentRegistration->motherphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->guardianname ?? '-' }}</td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->guardianprofession ?? '-' }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:80px; word-break:break-word; white-space:normal; padding:3px;">
                                @php $phones = explode(',', $student->StudentRegistration->guardianphone ?? ''); @endphp
                                {{ $phones[0] ?? '-' }}@if(isset($phones[1])),<br>{{ $phones[1] }}@endif
                            </td>
                            <td style="text-align:left; font-family:calibri; font-size:8px; width:100px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->StudentRegistration->address ?? '-' }}</td>
                            <td style="text-align:center; font-family:calibri; font-size:8px; width:50px; word-break:break-word; white-space:normal; padding:3px;">{{ $student->active_status == 1 ? 'Active' : 'WDR' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endforeach
    
    @include('student.exports.footer')
</body>
</html>