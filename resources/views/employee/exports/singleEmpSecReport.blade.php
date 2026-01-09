<table>
    <thead>
        <tr>
            {{-- // <td colspan="7" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
    //     The Lynx School
    // </td> --}}
            <td colspan="4"
                style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: bold; font-size: 28px;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;">
            </td>
        </tr>
        @if (@$is_branch)
            <tr>
                <td colspan="4" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                    <span style="text-transform: uppercase;">
                        {{ @$branchName }}
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center;">
                </td>
            </tr>
        @else
            <tr>
                <td colspan="4" style="text-align: left; font-family: calibri; font-weight: bold; font-size: 12px;">
                    <span style="text-transform: uppercase;">
                        @php
                            $branchKey = request()->get('branches');
                            $branchName = 'All Branches';

                            // Ensure branchKey is a valid array offset type (string or integer)
                            if ($branchKey && (is_string($branchKey) || is_int($branchKey))) {
                                if (
                                    isset($branches) &&
                                    is_array($branches) &&
                                    array_key_exists($branchKey, $branches)
                                ) {
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
                <td colspan="4" style="text-align: center;">
                </td>
            </tr>
        @endif
        @if (@$report_name)
            <tr>
                <td colspan="{{ $colspan ?? 10 }}"
                    style="text-align: left; font-family: calibri; font-weight: bold; font-size: 15px; font-weight: bold;">
                    <span style="text-transform: uppercase;">
                        @php $reportVal = $report_name ?? ''; @endphp
                        {{ is_array($reportVal) ? $reportVal['name'] ?? '' : $reportVal }}
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center;">
                </td>
            </tr>
        @endif
        <tr>
            <th class="">Employee Name:</th>
            <td class="">{{ $employee->name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="">Designation:</th>
            <td class="">{{ $employee->designation->name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="">Date of Joining:</th>
            <td class="">{{ $employee->company_doj ?? '-' }}</td>
        </tr>
        <tr>
            <th class="">Period From:</th>
            <td class="">{{ $fromYear }}</td>
        </tr>
        <tr>
            <th class="">Period To:</th>
            <td class="">{{ $toYear }}</td>
        </tr>
        <tr>
            <th class="">Opening Balance:</th>
            <td class="">{{ ($openingBalance) }}</td>
        </tr>
        <tr>
            <th class="">Closing Balance:</th>
            <td class="">{{ ($closingBalance) }}</td>
        </tr>
        <tr></tr>
        <tr>
            <th style="position: sticky; left: 0; background: #fff; z-index: 2;" width="150px">Months
            </th>
            @for ($year = $fromYear; $year <= $toYear; $year++)
                <th width="150px">{{ $year }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @for ($month = 1; $month <= 12; $month++)
            <tr>
                <td style="position: sticky; left: 0; background: #f8f9fa; z-index: 1; width: 100px;" width="150px">
                    {{ \Carbon\Carbon::create(null, $month)->format('F') }}
                </td>
                @for ($year = $fromYear; $year <= $toYear; $year++)
                    <td width="150px">{{ $salaryData[$year][$month] ?? 0 }}</td>
                @endfor
            </tr>
        @endfor
        <tr>
            <th style="position: sticky; left: 0; background: #f8f9fa; z-index: 1;">
                Total</th>
            @for ($year = $fromYear; $year <= $toYear; $year++)
                <th>{{ $yearlyTotals[$year] ?? 0 }}</th>
            @endfor
        </tr>
    </tbody>
</table>
@include('student.exports.footer')