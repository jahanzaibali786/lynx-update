<tr>
    {{-- // <td colspan="7" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
    //     The Lynx School
    // </td> --}}
    <td colspan="7" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
        The Lynx School
    </td>
</tr>
<tr>
    <td colspan="7" style="text-align: center;">
    </td>
</tr>
@if (@$is_branch)
    <tr>
        <td colspan="7" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
            <span style="text-transform: uppercase;">
                {{ @$branchName }}
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center;">
        </td>
    </tr>
@else
    <tr>
        <td colspan="7" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
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
        <td colspan="7" style="text-align: center;">
        </td>
    </tr>
@endif
<tr>
    <td colspan="{{ $colspan ?? 7 }}"
        style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
        <span style="text-transform: uppercase;">
            @php $reportVal = $report_name ?? ''; @endphp
            {{ is_array($reportVal) ? $reportVal['name'] ?? '' : $reportVal }}
        </span>
    </td>
</tr>
<tr>
    <td colspan="7" style="text-align: center;">
    </td>
</tr>
@if (@$is_period)
    <tr>
        @php
            $reportName = is_array($report_name ?? '') ? $report_name['name'] ?? '' : $report_name ?? '';

            $fromDate = $date_from ?? '';
            $toDate = $date_to ?? '';
        @endphp

        @if (str_contains(strtolower($reportName), 'fee structure listing'))
            <td colspan="13" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
                @php
                    $sessions = [];

                    if (isset($params['session'])) {
                        if (is_array($params['session'])) {
                            $sessions = \App\Models\Session::whereIn('id', $params['session'])
                                ->pluck('year')
                                ->toArray();
                        } else {
                            $session = \App\Models\Session::find($params['session']);
                            if ($session) {
                                $sessions = [$session->year];
                            }
                        }
                    }

                    if (!empty($sessions)) {
                        $currentSession = $sessions[0];
                        $years = explode('-', $currentSession);

                        if (count($years) === 2) {
                            $fromSession = $years[0] - 1 . '-' . ($years[1] - 1);
                            echo "From $fromSession To $currentSession";
                        }

                        if (count($sessions) > 1) {
                            echo ' (Also: ' . implode(', ', array_slice($sessions, 1)) . ')';
                        }
                    } else {
                        echo 'All Sessions';
                    }
                @endphp
            </td>
        @else
            <td colspan="1" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
                From:
            </td>
            <td colspan="3" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
                {{ $fromDate ? date('d M Y', strtotime($fromDate)) : '' }}
            </td>
            <td colspan="3" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 10px;">
              To :{{ $toDate ? date('d M Y', strtotime($toDate)) : '' }}
            </td>
        @endif
    </tr>
    <tr>
        <td colspan="7" style="text-align: center;">
        </td>
    </tr>
@endif
