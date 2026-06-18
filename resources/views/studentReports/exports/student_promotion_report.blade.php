@php
    $branchKey = $params['branch_from'] ?? 'all';
    $branchName = $branchKey !== 'all' ? ($branches[$branchKey] ?? 'Selected Branch') : 'All Branches';
@endphp

<tr>
    <td colspan="13" style="text-align:left; font-family:'Edwardian Script ITC'; font-weight:bold; font-size:28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="13"></td>
</tr>
<tr>
    <td colspan="13" style="text-align:left; font-family:Calibri; font-weight:bold; font-size:12px;">
        {{ strtoupper($branchName) }}
    </td>
</tr>
<tr>
    <td colspan="13" style="text-align:left; font-family:Calibri; font-weight:bold; font-size:15px;">
        {{ strtoupper($report_name) }}
    </td>
</tr>
<tr>
    <td colspan="13"></td>
</tr>

@include('studentReports.partials.student_promotion_report_table')
