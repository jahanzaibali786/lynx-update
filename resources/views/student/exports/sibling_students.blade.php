<table>
    <thead>
        @include('student.exports.header')
        <tr>
            <th>
                {{ __('SR') }}</th>
            <th>
                {{ __('B.SR') }}</th>
            <th>
                {{ __('Roll No.') }}</th>
            <th>
                {{ __('Student Name') }}</th>
            <th>
                {{ __('Branch Name') }}</th>
            <th>
                {{ __('Class') }}</th>
            <th>
                {{ __('Father Name') }}</th>
            <th>
                {{ __('CNIC') }}</th>
            <th>
                {{ __('Mother Name') }}</th>
            <th>
                {{ __('CNIC') }}</th>
            <th>
                {{ __('CLASS FEE') }}</th>
            <th>
                {{ __('Discount %') }}</th>
            <th>
                {{ __('Monthly Fee') }}</th>
            <th>
                {{ __('STATUS') }}</th>
        </tr>
    </thead>
    @php
        $grand_month_fee = 0;
        $grand_total_fee = 0;
        $grand_total_discount = 0;
    @endphp

    <tbody>
        @foreach ($groupedStudents as $branchId => $students)
            <tr>
                <td colspan="3" style="font-weight: bold; background-color:#bcbcbc; font-size: 8px; font-family:Calibri;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
                <td colspan="11" style="font-weight: bold; background-color:#bcbcbc; font-size: 8px; font-family:Calibri;">
                </td>
            </tr>

            @php
                $branch_month_fee = 0;
                $branch_total_fee = 0;
                $branch_total_discount = 0;
            @endphp

            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $loop->parent->index * $students->count() + $index + 1 }}</td>
                    <td>{{ $student->roll_no ?? '-' }}</td>
                    <td>{{ $student->stdname ?? '-' }}</td>
                    <td>{{ $student->branches->name ?? '-' }}</td>
                    <td>{{ $student->class->name ?? '-' }}</td>
                    <td>{{ $student->fathername ?? '-' }}</td>
                    <td>{{ $student->fathercnic ?? '-' }}</td>
                    <td>{{ $student->mothername ?? '-' }}</td>
                    <td>{{ $student->mothercnic ?? '-' }}</td>

                    @php
                        $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                        $class_fee = \App\Models\ClassWiseFee::where('class_id', $student->class_id)
                            ->where('head_id', $head->id ?? null)
                            ->first();
                        $monthly_fee = \App\Models\StudentFeeStructure::where(function ($query) use ($student, $head) {
                            $query->where('reg_id', $student->id)->orWhere('student_id', $student->roll_no);
                        })
                            ->where('head_id', $head->id ?? null)
                            ->first();
                        $discount = 0;
                        if ($student->concession) {
                            $discount =
                                $student->concession->policy_head->where('head_id', $head->id)->first()->percentage ??
                                0;
                        }

                        // Branch totals
                        $branch_month_fee += $monthly_fee->amount ?? 0;
                        $branch_total_fee += $class_fee->amount ?? 0;
                        $branch_total_discount += $discount;

                        // Grand totals
                        $grand_month_fee += $monthly_fee->amount ?? 0;
                        $grand_total_fee += $class_fee->amount ?? 0;
                        $grand_total_discount += $discount;
                    @endphp

                    <td>{{ $class_fee->amount ?? '-' }}</td>
                    <td>{{ $discount ?? '-' }}</td>
                    <td>{{ $monthly_fee->amount ?? '-' }}</td>
                    <td>{{ $student->active_status == 1 ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach

            {{-- Branch Totals Row --}}
            <tr>
                <td colspan="10" style="background-color: gray; border: 2px solid black; font-weight: bold; text-align: right; font-size: 8px; font-family:Calibri;">Branch Totals</td>
                <td style="background-color: gray; border: 2px solid black; font-weight: bold; text-align: center; font-size: 8px; font-family:Calibri;">{{ number_format($branch_total_fee, 0) }}</td>
                <td style="background-color: gray; border: 2px solid black; font-weight: bold;text-align: center; font-size: 8px; font-family:Calibri;">{{ number_format($branch_total_discount, 0) }}</td>
                <td style="background-color: gray; border: 2px solid black; font-weight: bold;text-align: right; font-size: 8px; font-family:Calibri;">{{ number_format($branch_month_fee, 0) }}</td>
                <td colspan="1" style="background-color: gray; border: 2px solid black; font-weight: bold; text-align: left; font-size: 8px; font-family:Calibri;"></td>
            </tr>
            <tr>
                <td colspan="14" style="border: none; height: 10px;"></td>
            </tr>
            <tr>
                <td colspan="14" style="border: none; height: 10px;"></td>
            </tr>
            <tr>
                <td colspan="14" style="border: none; height: 10px;"></td>
            </tr>
        @endforeach

        {{-- Grand Totals Row --}}
        <tr>
            <td colspan="10" style="text-align: right; background-color: gray; font-weight: bold; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">Grand Totals</td>
            <td style="background-color: gray; font-weight: bold; text-align: center; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_total_fee, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: center; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_total_discount, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_month_fee, 0) }}</td>
            <td colspan="1" style="background-color: gray; font-weight: bold; text-align: left; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;"></td>
        </tr>

        @include('student.exports.footer')
    </tbody>

</table>
