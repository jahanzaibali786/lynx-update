<table class="table">
    <thead class="table_heads">
        @include('student.exports.header')
        <tr>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Sr. No') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Branch') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Emp No') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Emp Name') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Emp Service Period') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Designation') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Child Roll No') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Child Branch Name') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Child Name') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Child Class') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('DOA') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Current Tuition Fee') }}</th>
            <th style="text-align: center; font-weight: bold; font-size: 8px; background-color: gray; border: 1px solid black;">{{ __('Child Concession %') }}</th>
        </tr>
    </thead>
    <tbody>
        @if($datas->count() > 0)
            @foreach ($datas as $key => $child)
                @php
                    // Tuition Fee Calculation
                    $tutionfeehead = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                    $studentId = optional($child->student)->id;
                    $headId = optional($tutionfeehead)->id;
                    $stdfeestr = $studentId && $headId 
                        ? \App\Models\StudentFeeStructure::where('reg_id', $studentId)
                            ->where('head_id', $headId)
                            ->first()
                        : null;

                    // Concession Calculation
                    $totalpercentage = 0;
                    $concession = $studentId 
                        ? \App\Models\Concession::with('student', 'class')
                            ->where('student_id', $studentId)
                            ->first()
                        : null;

                    if ($concession && $concession->concession_id) {
                        $concession_policy = \App\Models\ConcessionPolicy::find($concession->concession_id);
                        if ($concession_policy) {
                            $concession_heads = \App\Models\ConcessionPolicyHead::where('concession_id', $concession_policy->id)
                                ->where('percentage', '!=', 0)
                                ->get();
                            $totalpercentage = $concession_heads->sum('percentage');
                        }
                    }
                @endphp
                <tr>
                    <td style="text-align: right; border: 1px solid black;">{{ $key + 1 }}</td>
                    <td style=" border: 1px solid black;">{{ optional(optional($child->employee)->userbranch)->name ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid black;">{{ optional($child->employee)->employee_id ?? '-' }}</td>
                    <td style=" border: 1px solid black;">{{ optional($child->employee)->name ?? '-' }}</td>
                    <td style=" border: 1px solid black;">
                        {{ $child->employee ? $child->employee->getEmployeeTenure($child->employee->id) : '-' }}
                    </td>
                    <td style=" border: 1px solid black;">{{ optional(optional($child->employee)->designation)->name ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid black;">{{ optional($child->student)->roll_no ?? '-' }}</td>
                    <td style=" border: 1px solid black;">{{ optional(optional($child->student)->branches)->name ?? '-' }}</td>
                    <td style=" border: 1px solid black;">{{ optional($child->student)->stdname ?? '-' }}</td>
                    <td style=" border: 1px solid black;">{{ optional(optional($child->student)->class)->name ?? '-' }}</td>
                    <td style=" border: 1px solid black;">{{ optional($child->enrollment)->adm_date ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid black;">{{ $stdfeestr->amount ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid black;">{{ $totalpercentage ?: '-' }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="13" style="text-align: center; border: 1px solid black;">No Data Available</td>
            </tr>
        @endif
        @include('student.exports.footer')
    </tbody>
</table>