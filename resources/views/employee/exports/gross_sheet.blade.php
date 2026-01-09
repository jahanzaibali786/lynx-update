{{-- header --}}
<tr>
    <td colspan="6" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@if (@$is_branch)
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
    <td colspan="{{ $colspan ?? 6 }}"
        style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
        <span style="text-transform: uppercase;">
            @php $reportVal = $report_name ?? ''; @endphp
            {{ is_array($reportVal) ? $reportVal['name'] ?? '' : $reportVal }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="6" style="text-align: center;">
    </td>
</tr>
@if (@$is_period)
    <tr>
        <td colspan="6" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
            @php
                $reportName = is_array($report_name ?? '') ? $report_name['name'] ?? '' : $report_name ?? '';

                if (str_contains(strtolower($reportName), 'fee structure listing')) {
                    $sessions = [];

                    // Handle both array and single session cases
                    if (isset($params['session'])) {
                        if (is_array($params['session'])) {
                            // Multiple sessions selected - get all session years
                            $sessions = \App\Models\Session::whereIn('id', $params['session'])
                                ->pluck('year')
                                ->toArray();
                        } else {
                            // Single session selected
                            $session = \App\Models\Session::find($params['session']);
                            if ($session) {
                                $sessions = [$session->year];
                            }
                        }
                    }

                    if (!empty($sessions)) {
                        // For first session in list, show "From prev_year To current_year"
                        $currentSession = $sessions[0];
                        $years = explode('-', $currentSession);
                        if (count($years) === 2) {
                            $fromSession = $years[0] - 1 . '-' . ($years[1] - 1);
                            echo "From $fromSession To $currentSession";
                        }

                        // If multiple sessions, append the rest
                        if (count($sessions) > 1) {
                            echo ' (Also: ' . implode(', ', array_slice($sessions, 1)) . ')';
                        }
                    } else {
                        echo 'All Sessions';
                    }
                } else {
                    // Handle date period display
                    $fromDate = $params['date_from'] ?? '';
                    $toDate = $params['date_to'] ?? '';

                    if ($fromDate || $toDate) {
                        echo 'From: ' . ($fromDate ? date('d M Y', strtotime($fromDate)) : '') . ' ';
                        echo '    To ' . ($toDate ? date('d M Y', strtotime($toDate)) : '');
                    }
                }
            @endphp
        </td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center;"></td>
    </tr>
@endif

{{-- end --}}
<table>
    <thead>
        <tr>
            <th>
                sr.#</th>
            <th>
                Emp. Id</th>
            <th>
                Name</th>
            <th>
                Gross</th>
            <th>
                Net</th>
            <th>
                Cost To Company</th>
        </tr>
    </thead>
    @php
        $gross = 0;
        $net_tot = 0;
        $gross_tot = 0;
        $cast_tot = 0;
    @endphp
    @foreach ($datas as $key => $data)
        @php
            $payscale = $data->employee->employee_payscale_details->last();
        @endphp
        <tbody>
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    {{ !empty($data->employee->id) ? @$data->employee->id : '' }}</td>
                <td>
                    {{ !empty($data->employee->name) ? @$data->employee->name : '' }}</td>
                @php
                    $total_cost =
                        (!empty($data->pessi_employer) ? @$data->pessi_employer : '0') +
                        (!empty($data->eobi_employer) ? @$data->eobi_employer : '0');
                    $cost_to_comp =
                        (!empty($total_cost) ? @$total_cost : '0') + (!empty($data->gross) ? @$data->gross : '0');

                    $net_deduction =
                        (!empty($data->emp_sec) ? @$data->emp_sec : '0') +
                        (!empty($data->it) ? @$data->it : '0') +
                        (!empty($data->pessi) ? @$data->pessi : '0') +
                        (!empty($data->dedu) ? @$data->dedu : '0') +
                        (!empty($payscale->advance) ? @$payscale->advance : '0') +
                        (!empty($data->eobi) ? @$data->eobi : '0') +
                        (!empty($data->loan_emp_sec) ? @$data->loan_emp_sec : '0') +
                        (!empty($data->stop_sal) ? @$data->stop_sal : '0');

                    $net = (!empty($data->gross) ? @$data->gross : '0') - $net_deduction;
                    $gross_tot += $data->gross;
                    $cast_tot += $cost_to_comp;
                    $net_tot += $net;
                @endphp
                <td>
                    {{ !empty($data->gross) ? @$data->gross : '0' }}</td>
                <td>{{ !empty($net) ? @$net : '0' }}</td>
                <td>
                    {{ !empty($cost_to_comp) ? @$cost_to_comp : '0' }}</td>
            </tr>
    @endforeach
    <tr>
        <td style="border: 2px solid black; font-weight: bold; background-color:gray; text-align:center; border-collapse: collapse;" colspan="3"> Total </td>
        <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$gross_tot }}</td>
        <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$net_tot }}</td>
        <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$cast_tot }}</td>

    </tr>
    </tbody>
</table>
{{-- footer --}}
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
