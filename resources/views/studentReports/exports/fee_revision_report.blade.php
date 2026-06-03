@php
    $branchKey = $params['branches'] ?? 'all';
    $branchName = $branchKey !== 'all' ? ($branches[$branchKey] ?? 'Selected Branch') : 'All Branches';
    $sessionFrom = !empty($params['session_from_id']) && $params['session_from_id'] !== 'all'
        ? optional(\App\Models\Session::find($params['session_from_id']))->year
        : 'All';
    $sessionTo = !empty($params['session_to_id']) && $params['session_to_id'] !== 'all'
        ? optional(\App\Models\Session::find($params['session_to_id']))->year
        : 'All';
@endphp

<tr>
    <td colspan="8" style="text-align:left; font-family:'Edwardian Script ITC'; font-weight:bold; font-size:28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="8"></td>
</tr>
<tr>
    <td colspan="8" style="text-align:left; font-family:Calibri; font-weight:bold; font-size:12px;">
        {{ strtoupper($branchName) }}
    </td>
</tr>
<tr>
    <td colspan="8" style="text-align:left; font-family:Calibri; font-weight:bold; font-size:15px;">
        {{ strtoupper($report_name) }}
    </td>
</tr>
<tr>
    <td colspan="4" style="text-align:left; font-family:Calibri; font-size:10px;">
        Session From: {{ $sessionFrom }}
    </td>
    <td colspan="4" style="text-align:left; font-family:Calibri; font-size:10px;">
        Session To: {{ $sessionTo }}
    </td>
</tr>
<tr>
    <td colspan="8"></td>
</tr>

@if ($studentDetail)
    <tr>
        <td colspan="2"><b>Roll No:</b> {{ optional($studentDetail->enrollment)->enrollId ?? $studentDetail->roll_no ?? '-' }}</td>
        <td colspan="3"><b>Student:</b> {{ $studentDetail->stdname ?? '-' }}</td>
        <td colspan="3"><b>Father:</b> {{ $studentDetail->fathername ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="2"><b>Branch:</b> {{ optional(optional($studentDetail->enrollment)->branch)->name ?? optional($studentDetail->branches)->name ?? '-' }}</td>
        <td colspan="3"><b>Class:</b> {{ optional(optional($studentDetail->enrollment)->class)->name ?? optional($studentDetail->class)->name ?? '-' }}</td>
        <td colspan="3"><b>Section:</b> {{ optional(optional($studentDetail->enrollment)->section)->name ?? optional($studentDetail->section)->name ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
@endif

@include('studentReports.partials.fee_revision_report_table')
