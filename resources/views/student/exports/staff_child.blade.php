<table>
    <thead>
    @include('student.exports.header')
        <tr>
            <th>{{ __('Sr#') }}</th>
            <th>{{ __('Bsr#') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Emp No') }}</th>
            <th>{{ __('Emp Branch') }}</th>
            <th>{{ __('Emp Name') }}</th>
            <th>{{ __('Roll No') }}</th>
            <th>{{ __('Child Name') }}</th>
            <th>{{ __('Child Branch') }}</th>
            <th>{{ __('Child Class') }}</th>
            <th>{{ __('D.O.A') }}</th>
            <th>{{ __('Tuition Fee') }}</th>
            <th>{{ __('Concession') }}</th>
            <th>{{ __('Payable') }}</th>
            <th>{{ __('Discount Policy') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
        @endphp

        @foreach ($groupedStudents as $branchId => $students)
            @php
                $bsr = 1;
            @endphp
            <tr></tr>
            <tr class="branch-header" style="background-color:#F2F2F2;">
                <td colspan="15" style="font-weight: bold; font-size: 8px; background-color:#F2F2F2;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>

            @foreach ($students as $index => $student)
                @php
                                $headId = $head->id ?? 0;

                                $feeStructure = $student->fee_structure
                                    ->where('head_id', $headId)
                                    ->first();

                                $amount = (float) ($feeStructure->amount ?? 0);

                                $discountPct = 0;
                                $policyName = '';

                                $concession = \App\Models\Concession::with('concession')->where('student_id', $student->id)
                                    ->where('end_date', '>=', date('Y-m-d'))
                                    ->orderBy('id', 'desc')
                                    ->where('active_status', '!=', 0)
                                    ->where('status', 'Approved')
                                    ->first();
                                if (!$concession) {
                                    $concession = \App\Models\Concession::with('concession')->where('student_id', $student->id)
                                        ->orderBy('id', 'desc')
                                        ->whereNull('end_date')
                                        ->where('active_status', '!=', 0)
                                        ->where('status', 'Approved')
                                        ->first();
                                }
                                if ($concession) {
                                    $policyHead = $concession
                                                    ->concession
                                                    ->policy_head()
                                                    ->where('head_id', $headId)
                                                    ->latest('id')
                                                    ->first();

                                                // dd($policyHead);

                                    $discountPct = (float) ($policyHead->percentage ?? 0);
                                    $discAmnt = ($amount * $discountPct) / 100;
                                    $policyName = $concession->concession->title ?? '';
                                }

                                $payable = $amount - ($amount * $discountPct / 100);

                                $employee = $student->employee ?? null;
                            @endphp
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>{{ $bsr++ }}</td>
                    <td>{{ $branches[$branchId] ?? '' }}</td>
                    <td>{{ $employee->employee_id ?? '' }}</td>
                    <td>{{ !empty($employee->owned_by) ? ($branchLookup[$employee->owned_by] ?? '') : '' }}</td>
                    <td>{{ $employee->name ?? '' }}</td>
                    <td>{{ $student->roll_no ?? '' }}</td>
                    <td>{{ $student->stdname ?? '' }}</td>
                    <td>{{ $student->branches->name ?? '' }}</td>
                    <td>{{ $student->class->name ?? '' }}</td>
                    <td>{{ $student->enrollment ? date('d-M-Y', strtotime($student->enrollment->adm_date)) : '' }}</td>
                    <td>{{ number_format($amount, 0) }}</td>
                        <td>{{ number_format($discAmnt, 0) }}</td>
                    <td>{{ number_format($payable, 0) }}</td>
                    <td>{{ $policyName }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>