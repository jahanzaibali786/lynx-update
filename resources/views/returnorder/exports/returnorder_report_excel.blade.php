{{-- @include('student.exports.header') --}}
<tr>
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
        <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 14px; font-weight: bold;">
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
    <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 8px;">
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
                    echo "To ".($toDate ? date('d M Y', strtotime($toDate)) : '');
                }
            }
        @endphp
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;"></td>
</tr>
@endif
{{--  --}}
<table>
    <thead>
        <tr>
            <th>{{ __('Sr. No.') }}</th>
            <th> {{ __('Return Order') }}</th>
            <th> {{ __('Store From') }}</th>
            <th> {{ __('Store To') }}</th>
            <th> {{ __('Returnorder Date') }}</th>
            <th> {{ __('Approve Amount') }}</th>
            <th> {{ __('Pending Amount') }}</th>
            {{-- <th>{{ __('Status') }}</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($data['purchases'] as $purchase)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="Id">
                    {{ Auth::user()->quotationNumberFormat($purchase->quotation_id) }}
                </td>
                <td> {{ !empty($purchase->customer) ? $purchase->customer->name : '' }}</td>
                <td>{{ !empty($purchase->warehouse) ? $purchase->warehouse->name : '' }}</td>
                <td>{{ Auth::user()->dateFormat($purchase->quotation_date) }}</td>
                <td>{{ \Auth::user()->priceFormat($purchase->getapproveTotal()) }}</td>
                <td>{{ \Auth::user()->priceFormat($purchase->getpendingTotal()) }}</td>
            </tr>
        @endforeach
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
