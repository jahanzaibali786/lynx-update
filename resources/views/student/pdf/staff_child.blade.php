<table class="datatable">
    <thead>
    @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif; ">
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('B.Sr No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Emp No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Employee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Service Period') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Designation') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Roll No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Branch') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Class') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('D.O.A') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Class Fee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Fee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:#D9D9D9; font-family:Arial,Helvetica,sans-serif;">
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
                <td colspan="15 " style=" font-weight: bold; font-size: 8px; background-color:#F2F2F2;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>

            @php
                $bsr = 1;
                $branch_monthly_fee = 0;
                $branch_discount = 0;
            @endphp

            @foreach ($students as $index => $student)
                <tr>
                    <td style="text-align: center; font-size: 8px; font-weight: bold;">{{ $sr++ }}</td>
                    <td style="text-align: center; font-size: 8px; font-weight: bold;">{{ $bsr++ }}</td>
                    <td style="text-align: center; font-size: 8px; font-weight: bold;">{{ $student->emp_id ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">{{ @$student->employee->name ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">
                        {{ @$student->employee->getEmployeeTenure(@$student->employee->id) ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">{{ @$student->employee->designation->name ?? '-' }}
                    </td>
                    <td style="text-align: center; font-size: 8px; font-weight: bold;">{{ @$student->student->roll_no ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">{{ @$student->student->branches->name ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">{{ @$student->student->stdname ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">{{ @$student->student->class->name ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; font-weight: bold;">
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

                    <td style="text-align: right; font-size: 8px; font-weight: bold;">{{ $amount }}</td>
                    <td style="text-align: right; font-size: 8px; font-weight: bold;">{{ number_format($discountfee) }}</td>
                    <td style="text-align: right; font-size: 8px; font-weight: bold;">{{ $discount }}%</td>
                </tr>
            @endforeach

            {{-- Branch Total Row --}}
            <tr></tr>
            <tr style="background-color: #D9D9D9; font-weight: bold;">
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
        <tr style="background-color: #D9D9D9; font-weight: bold; text-align: right; font-size: 8px;">
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
