<table>
    <thead>
    @include('student.exports.header')
        <tr>
            <th>
                {{ __('Sr No') }}</th>
            <th>
                {{ __('B.Sr No') }}</th>
            <th>
                {{ __('Emp No') }}</th>
            <th>
                {{ __('Employee') }}</th>
            <th>
                {{ __('Service Period') }}</th>
            <th>
                {{ __('Designation') }}</th>
            <th>
                {{ __('Child Roll No') }}</th>
            <th>
                {{ __('Child Branch') }}</th>
            <th>
                {{ __('Child Name') }}</th>
            <th>
                {{ __('Child Class') }}</th>
            <th>
                {{ __('D.O.A') }}</th>
            <th>
                {{ __('Class Fee') }}</th>
            <th>
                {{ __('Child Fee') }}</th>
            <th>
                {{ __('Child Concession %') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $grand_monthly_fee = 0;
            $grand_discount = 0;
        @endphp

        @foreach ($groupedStudents as $branchId => $students)
            <tr></tr>
            <tr class="branch-header" style="background-color:#F2F2F2;">
                <td colspan="3" style=" font-weight: bold; font-size: 8px; background-color:#F2F2F2;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="11" style=" font-weight: bold; font-size: 8px; background-color:#F2F2F2;">
                </td>
            </tr>

            @php
                $bsr = 1;
                $branch_monthly_fee = 0;
                $branch_discount = 0;
            @endphp

            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $sr++ }}</td>
                    <td>{{ $bsr++ }}</td>
                    <td>{{ $student->emp_id ?? '-' }}</td>
                    <td>{{ @$student->employee->name ?? '-' }}</td>
                    <td>
                        {{ @$student->employee->getEmployeeTenure(@$student->employee->id) ?? '-' }}</td>
                    <td>{{ @$student->employee->designation->name ?? '-' }}
                    </td>
                    <td>{{ @$student->student->roll_no ?? '-' }}</td>
                    <td>{{ @$student->student->branches->name ?? '-' }}</td>
                    <td>{{ @$student->student->stdname ?? '-' }}</td>
                    <td>{{ @$student->student->class->name ?? '-' }}</td>
                    <td>
                        {{ date('d-M-Y', strtotime(@$student->enrollment->adm_date)) ?? '-' }}</td>
                    @php
                        $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                        $monthly_fee = \App\Models\StudentFeeStructure::where(function ($query) use ($student, $head) {
                            $query->where('reg_id', $student->id)->orWhere('student_id', $student->roll_no);
                        })
                            ->where('head_id', $head->id ?? null)
                            ->first();

                        $amount = $monthly_fee->amount ?? 0;
                        $discount =
                            @$student->student->concession->policy_head->where('head_id', $head->id)->first()
                                ->percentage ?? 0;

                        $discountfee = $amount * ((100 - $discount) / 100);

                        $branch_monthly_fee += $amount;
                        $branch_discount += $discountfee;

                        $grand_monthly_fee += $amount;
                        $grand_discount += $discountfee;
                    @endphp

                    <td>{{ $amount }}</td>
                    <td>{{ number_format($discountfee) }}</td>
                    <td>{{ $discount }}%</td>
                </tr>
            @endforeach

            {{-- Branch Total Row --}}
            <tr></tr>
            <tr>
                <td colspan="12"
                    style="text-align: center; background-color: #D9D9D9; font-weight: bold; font-size: 8px; border: 1px solid black;">
                    Branch Total</td>
                <td
                    style="background-color: #D9D9D9; font-weight: bold; text-align: right; font-size: 8px; border: 1px solid black">
                    {{ number_format($branch_monthly_fee, 0) }}</td>
                <td colspan=""
                    style="background-color: #D9D9D9; font-weight: bold; text-align: right; font-size: 8px; border: 1px solid black">
                    {{ number_format($branch_discount, 0) }}</td>
                <td colspan=""
                    style="background-color: white; font-weight: bold; text-align: right; font-size: 8px;"></td>
            </tr>
            <tr></tr>
        @endforeach

        <tr></tr>

        {{-- Grand Total Row --}}
        <tr>
            <td colspan="12"
                style="text-align: center; background-color: #D9D9D9; font-weight: bold; font-size: 8px; border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black;">
                Grand Total</td>
            <td
                style="background-color: #D9D9D9; font-weight: bold; text-align: right; font-size: 8px; border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black;">
                {{ number_format($grand_monthly_fee, 0) }}</td>
            <td colspan=""
                style="background-color: #D9D9D9; font-weight: bold; text-align: right; font-size: 8px; border: 1px solid black; border-top: 1px double black; border-bottom: 1px double black;">
                {{ number_format($grand_discount, 0) }}</td>
            <td colspan=""
                style="background-color: white;">
            </td>
        </tr>
    </tbody>

</table>
