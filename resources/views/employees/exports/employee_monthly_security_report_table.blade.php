{{-- @include('student.exports.header') --}}
<tr>
    {{-- // <td colspan="5" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
    //     The Lynx School
    // </td> --}}
    <td colspan="5" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
@if(@$is_branch)
<tr>
    <td colspan="5" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
        <span style="text-transform: uppercase;">
            {{ @$branchName }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
@else
<tr>
    <td colspan="5" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
        <span style="text-transform: uppercase;">
            @php 
                $branchKey = request()->get('branches');
                $branchName = 'All Branches';
                
                // Ensure branchKey is a valid array offset type (string or integer)
                if ($branchKey && (is_string($branchKey) || is_int($branchKey))) {
                    if (isset($branches) && is_array($branches) && array_key_exists($branchKey, $branches)) {
                        $branchName = $branches[$branchKey];
                    } elseif (isset($branches) && is_object($branches) && method_exists($branches, 'get')) {
                        $branchName = $branches->get($branchKey, 'All Branches');
                    }
                }
                
                // Fallback to branch variable if available
                if (isset($branch) && $branch && $branchName === 'All Branches') {
                    $branchName = $branch;
                }
            @endphp
            {{ $branchName }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
@endif
<tr>
        <td colspan="{{ $colspan ?? 5 }}" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px; font-weight: bold;">
        <span style="text-transform: uppercase;">
            @php $reportVal = $report_name ?? ''; @endphp
            {{ is_array($reportVal) ? ($reportVal['name'] ?? '') : $reportVal }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
@if(@$is_period)
<tr>
    <td colspan="5" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
        @php
            $reportName = is_array($report_name ?? '') ? ($report_name['name'] ?? '') : ($report_name ?? '');
            
            if(str_contains(strtolower($reportName), 'fee structure listing')) {
                $sessions = [];
                
                // Handle both array and single session cases
                if(isset($params['session'])) {
                    if(is_array($params['session'])) {
                        // Multiple sessions selected - get all session years
                        $sessions = \App\Models\Session::whereIn('id', $params['session'])
                                        ->pluck('year')
                                        ->toArray();
                    } else {
                        // Single session selected
                        $session = \App\Models\Session::find($params['session']);
                        if($session) {
                            $sessions = [$session->year];
                        }
                    }
                }
                
                if(!empty($sessions)) {
                    // For first session in list, show "From prev_year To current_year"
                    $currentSession = $sessions[0];
                    $years = explode('-', $currentSession);
                    if(count($years) === 2) {
                        $fromSession = ($years[0] - 1).'-'.($years[1] - 1);
                        echo "From $fromSession To $currentSession";
                    }
                    
                    // If multiple sessions, append the rest
                    if(count($sessions) > 1) {
                        echo " (Also: " . implode(', ', array_slice($sessions, 1)) . ")";
                    }
                } else {
                    echo "All Sessions";
                }
            } else {
                // Handle date period display
                $fromDate = $params['date_from'] ?? '';
                $toDate = $params['date_to'] ?? '';
                
                if($fromDate || $toDate) {
                    echo "From: ".($fromDate ? date('d M Y', strtotime($fromDate)) : '')." ";
                    echo "    To ".($toDate ? date('d M Y', strtotime($toDate)) : '');
                }
            }
        @endphp
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;"></td>
</tr>
@endif
{{--  --}}
<table>
    <thead>
        <tr>
            <th>
                Sr. No.</th>
            <th>
                Emp Code</th>
            <th>
                Employee Name</th>
            <th>
                Father Name</th>
            <th>
                Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $branchName => $employees)
            <tr>
                <td colspan="3" style="background-color:gray; border:none;">{{ @$branchName }}</td>
                <td colspan="2" style="background-color:gray; border:none;"></td>
            </tr>
            @php
                $grandTotal = 0;
            @endphp
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ @$employee->employee_id }}</td>
                    <td>{{ @$employee->employee->name ?? 'N/A' }}</td>
                    <td>{{ @$employee->employee->f_name ?? 'N/A' }}</td>
                    <td>{{ number_format((float) @$employee->emp_sec, 2) }}</td>
                </tr>
                @php
                    $grandTotal += (float) @$employee->emp_sec;
                @endphp
            @endforeach
            <tr>
                <td colspan="4" style="text-align:right;"><b>Branch Total</b></td>
                <td style="text-align:right;">
                    <b>{{ number_format($grandTotal, 2) }}</b>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="5" style="text-align: center;">
    </td>
</tr>