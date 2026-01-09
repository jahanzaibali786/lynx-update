<tr>
    {{-- // <td colspan="6" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
    //     The Lynx School
    // </td> --}}
    <td colspan="6" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@if(@$is_branch)
<tr>
    <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
        <span style="text-transform: uppercase;">
            {{ @$branchName }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@else
<tr>
    <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
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
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@endif
<tr>
        <td colspan="{{ $colspan ?? 6 }}" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
        <span style="text-transform: uppercase;">
            @php $reportVal = $report_name ?? ''; @endphp
            {{ is_array($reportVal) ? ($reportVal['name'] ?? '') : $reportVal }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@if(@$is_period)
<tr>
    <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
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
    <td colspan="6" style="text-align: center;"></td>
</tr>
@endif




<table>
    <thead>
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th>{{ __('Employee Name') }}</th>
            <th>{{ __('Designation') }}</th>
            <th>{{ __('Department') }}</th>
            <th>{{ __('D.O.J') }}</th>
            <th>{{ __('Tenure') }}</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($data['employeesquery']) && $data['employeesquery']->count())
            @php $srNo = 1; @endphp
            @foreach ($data['employeesquery'] as $employee)
                @if ($employee->employee_rejoin && count($employee->employee_rejoin) > 0)
                    @foreach ($employee->employee_rejoin as $rejoin)
                        {{-- Previous Rejoin Period Row --}}
                        <tr>
                            <td>{{ $srNo++ }}</td>
                            <td>{{ $employee->name }} (Previous)</td>
                            <td>{{ $employee->designation->name ?? '' }}</td>
                            <td>{{ $employee->department->name ?? '' }}</td>
                            <td>{{ \Auth::user()->dateFormat($rejoin->prev_doj) }}</td>
                            <td>
                                @php
                                    $prevDoj = \Carbon\Carbon::parse($rejoin->prev_doj);
                                    $prevLeaving = \Carbon\Carbon::parse($rejoin->prev_leaving_date);
                                    $prevTenure = $prevDoj->diff($prevLeaving);
                                @endphp
                                {{ $prevTenure->y }} years {{ $prevTenure->m }} months
                            </td>
                        </tr>
                    @endforeach
                    {{-- Current Rejoin Period Row --}}
                    <tr>
                        <td>{{ $srNo++ }}</td>
                        <td>{{ $employee->name }} (Rejoined)</td>
                        <td>{{ $employee->designation->name ?? '' }}</td>
                        <td>{{ $employee->department->name ?? '' }}</td>
                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                        <td>
                            @php
                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                $currentTenure = $currentDoj->diff(now());
                            @endphp
                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                        </td>
                    </tr>
                @else
                    {{-- Single row if no rejoin --}}
                    <tr>
                        <td>{{ $srNo++ }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->designation->name ?? '' }}</td>
                        <td>{{ $employee->department->name ?? '' }}</td>
                        <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                        <td>
                            @php
                                $currentDoj = \Carbon\Carbon::parse($employee->company_doj);
                                $currentTenure = $currentDoj->diff(now());
                            @endphp
                            {{ $currentTenure->y }} years {{ $currentTenure->m }} months
                        </td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="6" class="text-center">{{ __('No Record Found') }}</td>
            </tr>
        @endif
    </tbody>
</table>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>