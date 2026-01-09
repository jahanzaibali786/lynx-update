<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>{{ __('Sr.No') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Emp. No') }}</th>
            <th>{{ __('Employee') }}</th>
            <th>{{ __('D.O.J') }}</th>
            <th>{{ __('Service Period') }}</th>
            <th>{{ __('Department') }}</th>
            <th>{{ __('Designation') }}</th>
            <th>{{ __('Gross Salary') }}</th>
            <th>{{ __('ACR Score') }}</th>
            <th>{{ __('ACR Grade') }}</th>
            <th>{{ __('Payscale Department') }}</th>
            <th>{{ __('New Payscale No') }}</th>
            <th>{{ __('Proposed New Gross Salary') }}</th>
            <th>{{ __('No of Child') }}</th>
            <th>{{ __('Child Concession Rs.') }}</th>
            <th>{{ __('Eobi Employer') }}</th>
            <th>{{ __('Pessi Employer') }}</th>
            <th>{{ __('Other Additions') }}</th>
            <th>{{ __('Cost to Company') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandgrossprev = 0;
            $grandgrossProp = 0;
            $grandcostToCompany = 0;
        @endphp
        @foreach ($data['annualSrsData'] as $employeeId => $records)
            @php
                $latest = $records['latest'];
                $previous = $records['previous'];
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $latest->employee->userbranch->name ?? '' }}</td>
                <td>{{ $latest->employee->employee_id ?? '' }}</td>
                <td>{{ $latest->employee->name ?? '' }}</td>
                <td>{{ @$latest->employee->company_doj ? date('Y-m-d', strtotime($latest->employee->company_doj)) : '' }}</td>
                <td>{{ @$latest->employee ? $latest->employee->getEmployeeTenure($latest->employee->id) : '' }}</td>
                <td>{{ $latest->employee->department->name ?? '' }}</td>
                <td>{{ $latest->employee->designation->name ?? '' }}</td>

                {{-- From previous scale --}}
                @php
                    // If no previous exists, fall back to latest for gross
                    $gross = $previous
                        ? $previous->net -
                            ($previous->other_deduction +
                                $previous->advance +
                                $previous->pessi +
                                $previous->eobi +
                                $previous->itax +
                                $previous->emp_sec +
                                $previous->child_concession +
                                $previous->drns +
                                $previous->misc +
                                $previous->conv)
                        : $latest->net -
                            ($latest->other_deduction +
                                $latest->advance +
                                $latest->pessi +
                                $latest->eobi +
                                $latest->itax +
                                $latest->emp_sec +
                                $latest->child_concession +
                                $latest->drns +
                                $latest->misc +
                                $latest->conv);
                            $grandgrossprev += $gross;
                @endphp
                <td>{{ $gross ?? '0' }}</td>
                <td>{{ $latest->acr_score ?? '' }}</td>
                <td>{{ $latest->acr_grade ?? '' }}</td>
                <td>{{ $latest->department->name ?? '' }}</td>
                <td>{{ $latest->scale->scale_no ?? '' }}</td>
                @php
                    $proposedGross =
                        $latest->net -
                        ($latest->other_deduction +
                            $latest->advance +
                            $latest->pessi +
                            $latest->eobi +
                            $latest->itax +
                            $latest->emp_sec +
                            $latest->child_concession +
                            $latest->drns +
                            $latest->misc +
                            $latest->conv);
                            $grandgrossProp += $proposedGross;
                @endphp
                <td>{{ $proposedGross ?? '' }}</td>
                <td>{{ @$latest->employee->empChilds ? $latest->employee->empChilds->count() : '0' }}</td>
                <td>{{ $latest->child_concession ?? '0' }}</td>
                <td>{{ $latest->eobi_employer ?? '0' }}</td>
                <td>{{ $latest->pessi_employer ?? '0' }}</td>
                <td>{{ $latest->other_add ?? '0' }}</td>
                @php
                    $CostTC =
                        $proposedGross +
                        $latest->eobi_employer +
                        $latest->pessi_employer +
                        $latest->other_add;
                        $grandcostToCompany += $CostTC;
                @endphp
                <td>{{ $CostTC ?? '0' }}</td>
            </tr>
        @endforeach
            {{-- greand total --}}
            <tr>
                <td colspan="8"><b>Total</b></td>
                <td><b>{{ $grandgrossprev }}</b></td>
                <td colspan="4"></td>
                <td><b>{{ $grandgrossProp }}</b></td>
                <td colspan="5"></td>
                <td><b>{{ $grandcostToCompany }}</b></td>
            </tr>
    </tbody>
</table>
@include('student.exports.footer')