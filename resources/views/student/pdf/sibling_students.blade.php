<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: Calibri !important;
            }
        </style>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Calibri; ">
            {{-- SR	B.SR	Roll No.	Student Name	Class	Father Name	CNIC	Mother Name	CNIC	CLASS FEE	Discount %	Monthly Fee	STATUS --}}
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Calibri;">
                {{ __('SR') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Calibri;">
                {{ __('B.SR') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Calibri;">
                {{ __('Roll No.') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Student Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Branch Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Class') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Father Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('CNIC') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Mother Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('CNIC') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('CLASS FEE') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Discount %') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('Monthly Fee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Calibri;">
                {{ __('STATUS') }}</th>
        </tr>
    </thead>
    @php
        $grand_month_fee = 0;
        $grand_total_fee = 0;
        $grand_total_discount = 0;
    @endphp

    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @foreach ($groupedStudents as $branchId => $students)
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="14" style="font-weight: bold; background-color:#bcbcbc; font-size: 8px; font-family:Calibri;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>

            @php
                $branch_month_fee = 0;
                $branch_total_fee = 0;
                $branch_total_discount = 0;
            @endphp

            @foreach ($students as $index => $student)
                <tr>
                    <td style="text-align: center; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $index + 1 }}</td>
                    <td style="text-align: center; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $loop->parent->index * $students->count() + $index + 1 }}</td>
                    <td style="text-align: center; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->roll_no ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->stdname ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->branches->name ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->class->name ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->fathername ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->fathercnic ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->mothername ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $student->mothercnic ?? '-' }}</td>

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

                    <td style="text-align: center; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $class_fee->amount ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $discount ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">{{ $monthly_fee->amount ?? '-' }}</td>
                    <td style="text-align: center;border: 1px solid #D3D3D3; font-size: 8px; font-family:Calibri;">
                        {{ $student->active_status == 1 ? 'Active' : 'Inactive' }}</td>
                </tr>
            @endforeach

            {{-- Branch Totals Row --}}
            <tr style="background-color: gray; font-weight: bold;">
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
        <tr style="background-color: gray; font-weight: bold; border-top: 3px double black; border-bottom: 3px double black;">
            <td colspan="10" style="text-align: right; background-color: gray; font-weight: bold; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">Grand Totals</td>
            <td style="background-color: gray; font-weight: bold; text-align: center; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_total_fee, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: center; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_total_discount, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;">{{ number_format($grand_month_fee, 0) }}</td>
            <td colspan="1" style="background-color: gray; font-weight: bold; text-align: left; font-size: 8px; font-family:Calibri; border: 2px solid black; border-top: 3px double black; border-bottom: 3px double black;"></td>
        </tr>

        @include('student.exports.footer')
    </tbody>

</table>
