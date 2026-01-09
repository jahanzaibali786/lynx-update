<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    th,
    td {
        border: 1px solid #aaa;
        padding: 4px;
        text-align: center;
    }

    tr,
    td {
        font-size: 0.6rem;
    }

    th {
        background-color: #f2f2f2;
    }

    .branch-title {
        font-weight: bold;
        text-align: left;
        background-color: #ddd;
    }

    .total-row {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>

<table class="datatable" style="margin-top: -50px;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>Sr#</th>
            <th>Branch</th>
            <th>Employee</th>
            <th>Emp. Service Period</th>
            <th>Designation</th>
            <th>Child Roll No</th>
            <th>Child Branch</th>
            <th>Child Name</th>
            <th>Child Class</th>
            <th>D.O.A</th>
            <th>Tuition Fee </th>
            <th>Child Concession % </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $key => $child)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ @$child->employee ? @$child->employee->userbranch->name : '-' }}</td>
                <td>{{ @$child->employee ? @$child->employee->name : '-' }}</td>
                <td>{{ @$child->employee ? @$child->employee->getEmployeeTenure(@$child->employee->id) : '-' }}</td>
                <td>{{ @$child->employee ? @$child->employee->designation->name : '-' }}</td>
                <td>{{ @$child->student ? @$child->student->roll_no : '-' }}</td>
                <td>{{ @$child->student ? @$child->student->branches->name : '-' }}</td>
                <td>{{ @$child->student ? @$child->student->stdname : '-' }}</td>
                <td>{{ @$child->student ? @$child->student->class->name : '-' }}</td>
                <td>{{ @$child->enrollment ? @$child->enrollment->adm_date : '-' }}</td>
                @php

                    // 1. Get Tuition Fee Head
                    $tutionfeehead = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();

                    // 2. Get Student Fee Structure (safe access to student and fee head)
                    $studentId = optional(optional($child)->student)->id;
                    $headId = optional($tutionfeehead)->id;
                    $stdfeestr = \App\Models\StudentFeeStructure::where('reg_id', $studentId)
                        ->where('head_id', $headId)
                        ->first();

                    // 3. Get Concession for student
                    $concession = \App\Models\Concession::with('student', 'class')
                        ->where('student_id', $studentId)
                        ->first();

                    // Initialize concession policy and related values
                    $concession_policy = null;
                    $concession_heads = collect();
                    $concession_amt = 0;
                    $totalpercentage = 0;

                    if ($concession && $concession->concession_id) {
                        // 4. Fetch concession policy only if the concession exists
                        $concession_policy = \App\Models\ConcessionPolicy::with([
                            'concession',
                            'concession.student',
                            'concession.student.enrollment',
                            'concession.class',
                            'concession.student.session',
                        ])->find($concession->concession_id);

                        // 5. Get Concession Policy Heads
                        if ($concession_policy) {
                            $concession_heads = \App\Models\ConcessionPolicyHead::where(
                                'concession_id',
                                $concession_policy->id,
                            )
                                ->where('percentage', '!=', 0)
                                ->get();
                        }

                        // 6. Calculate Total Percentage
                        foreach ($concession_heads as $dta) {
                            $totalpercentage += $dta->percentage;
                        }
                    }
                @endphp
                <td>{{ @$stdfeestr->amount ?? '-' }}</td>
                <td>{{ @$totalpercentage }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No Data Available</td>
            </tr>
        @endforelse
    </tbody>
</table> 