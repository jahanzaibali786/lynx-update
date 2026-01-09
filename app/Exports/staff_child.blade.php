<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif; ">
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr#') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Br.Sr#') }}</th>
                <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Emp No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Employee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Service Period') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Designation') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Roll No') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Branch') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Name') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Class') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('D.O.A') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Class Fee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Fee') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 110px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Child Concession %') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $grand_class_fee = 0;
            $grand_child_fee = 0;
            $grand_concession = 0;
        @endphp

        @foreach ($groupedStudents as $branchId => $students)
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="11" style=" font-weight: bold; font-size: 8px;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>

            @php
                $bsr = 1;
                $branch_class_fee = 0;
                $branch_child_fee = 0;
                $branch_concession = 0;
            @endphp

            @foreach ($students as $index => $student)
                <tr>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">{{ $sr++ }}</td>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">{{ $bsr++ }}</td>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">{{ $student->emp_id ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->employee->name ?? '-' }}</td>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ @$student->employee->getEmployeeTenure(@$student->employee->id) ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->employee->designation->name ?? '-' }}
                    </td>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->student->roll_no ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->student->branches->name ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->student->stdname ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ @$student->student->class->name ?? '-' }}</td>
                    <td style="text-align: left; font-size: 8px; border: 1px solid #D3D3D3;">{{ date('d-M-Y', strtotime(@$student->enrollment->adm_date)) ?? '-' }}</td>
                    @php
                        $head = \App\Models\FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
                        $class_fee = null;
                        $amount = 0;
                        $discount = 0;
                        
                        // Get the student registration record
                        $studentReg = null;
                        if ($student->student_id) {
                            $studentReg = \App\Models\StudentRegistration::where('roll_no', $student->student_id)
                                ->orWhere('id', $student->student_id)
                                ->first();
                        }
                        
                        if ($studentReg && $studentReg->class_id) {
                            $class_fee = \App\Models\ClassWiseFee::where('class_id', $studentReg->class_id)
                                ->where('head_id', $head->id ?? null)
                                ->first();
                            $amount = $class_fee->amount ?? 0;
                            $discount = @$studentReg->concession->policy_head->where('head_id', $head->id)->first()->percentage ?? 0;
                        }
                        
                        $branch_class_fee += $amount;
                        $branch_child_fee += $discount;
                        $branch_concession += $discount;
                    @endphp

                    <td style="text-align: right; font-size: 8px; border: 1px solid #D3D3D3;">{{ $amount }}</td>
                    <td style="text-align: center; font-size: 8px; border: 1px solid #D3D3D3;">{{ $discount }}</td>
                </tr>
            @endforeach

            @php
                $grand_class_fee += $branch_class_fee;
                $grand_child_fee += $branch_child_fee;
                $grand_concession += $branch_concession;
            @endphp

            {{-- Branch Total Row --}}
            <tr style="background-color: gray; font-weight: bold;">
                <td colspan="11" style="text-align: center; background-color: gray; font-weight: bold; font-size: 8px;">Branch Total</td>
                <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px;">{{ number_format($branch_class_fee, 0) }}</td>
                <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px;">{{ number_format($branch_child_fee, 0) }}</td>
                <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px;">{{ number_format($branch_concession, 0) }}</td>
            </tr>
        @endforeach

    </tbody>

</table>
<table>
    <tbody>
        <tr style="background-color: gray; font-weight: bold;">
            <td colspan="11" style="text-align: center; background-color: gray; font-weight: bold; font-size: 8px; border-top: 2px double black; border-bottom: 2px double black; border-right: 2px solid black;">Grand Total:</td>
            <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; border-top: 2px double black; border-bottom: 2px double black; border-right: 2px solid black;">{{ number_format($grand_class_fee, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; border-top: 2px double black; border-bottom: 2px double black; border-right: 2px solid black;">{{ number_format($grand_child_fee, 0) }}</td>
            <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; border-top: 2px double black; border-bottom: 2px double black; border-right: 2px solid black;">-</td>
            {{-- <td style="background-color: gray; font-weight: bold; text-align: right; font-size: 8px; border-top: 2px double black; border-bottom: 2px double black; border-right: 2px solid black;">{{ number_format($grand_concession, 0) }}</td> --}}
        </tr>
    </tbody>

</table>
@include('student.exports.footer')