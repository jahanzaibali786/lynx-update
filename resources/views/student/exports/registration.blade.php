<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: calibri !important;
            }
        </style>
        <style>
            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        </style>
        @include('student.exports.header')
        <tr>
            <th>
                {{ __('Sr No') }}</th>
            <th>
                {{ __('B.Sr#') }}</th>
            <th>
                {{ __('Reg. No') }}</th>
            <th>
                {{ __('Reg Date') }}</th>
            <th>
                {{ __('Student Name') }}</th>
            <th>
                {{ __('Father Name') }}</th>
            <th>
                {{ __('Cell No') }}</th>
            <th>
                {{ __('Session') }}</th>
            <th>
                {{ __('Class') }}</th>
            <th>
                {{ __('DOB') }}</th>
            <th>
                {{ __('Gender') }}</th>
            <th>
                {{ __('Status') }}</th>
            <th>
                {{ __('Reg. Ctg') }}</th>
            {{-- reg fee --}}
            <th>
                {{ __('Rg.Fee') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php
            $globalIndex = 1;
            $grandTotal = 0;
        @endphp

        @foreach ($registrations as $branchId => $students)
            @php
                $branchTotal = 0;
            @endphp

            {{-- Branch name row --}}
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="3"
                    style="font-weight: bold; text-align: left; background-color:#bcbcbc; border: 1px 0px 1px 1px solid #000;">
                    {!! str_replace(' ', '&nbsp;', e($branches[$branchId] ?? 'Branch Not Specified')) !!}
                </td>
                <td colspan="11"
                    style="font-weight: bold; background-color:#bcbcbc; border: 1px 1px 1px 0px solid #000;">
                </td>
            </tr>

            @foreach ($students as $index => $student)
                @php
                    $fee = (float) $student->registrationfee;
                    $branchTotal += $fee;
                @endphp

                <tr>
                    <td>{{ $globalIndex++ }}</td>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $student->reg_no }}</td>
                    <td>{{ $student->regdate == '0000-00-00' || !$student->regdate ? '' : date('d-M-Y', strtotime($student->regdate)) }}
                    </td>
                    <td>{!! str_replace(' ', '&nbsp;', e($student->stdname)) !!}</td>
                    <td>{!! str_replace(' ', '&nbsp;', e($student->fathername)) !!}</td>
                    <td>{!! nl2br(str_replace(',', ",\n", e($student->fatherphone))) !!}</td>
                    <td>{{ !empty($student->session) ? $student->session->year : '-' }}</td>
                    <td>{{ !empty($student->class) ? $student->class->name : '-' }}</td>
                    <td>{{ $student->dob == '0000-00-00' || !$student->dob ? '' : date('d-M-Y', strtotime($student->dob)) }}
                    </td>
                    <td>{{ strtoupper($student->gender) }}</td>
                    <td>{{ $student->roll_no ? 'Enrolled' : 'Not Enrolled' }}</td>
                    <td>{{ @$student->registeroption->name }}</td>
                    <td style="text-align:right;">
                        {{ number_format($fee, 0) }}
                    </td>
                </tr>
            @endforeach

            @php
                $grandTotal += $branchTotal;
            @endphp

            {{-- Branch total --}}
            <tr class="branch-total-row" style="border-top: 3px solid #000;">
                <td colspan="13"
                    style="font-size:8px;font-weight:bold;background-color:#B8B8B8;text-align:center;border:2px solid #000;">
                    Branch Total
                </td>
                <td
                    style="font-size:8px;font-weight:bold;background-color:#B8B8B8;border:2px solid #000;text-align:right;">
                    {{ number_format($branchTotal, 0) }}
                </td>
            </tr>
        @endforeach


        <tr>
            <td colspan="13" style="height:20px;"></td>
        </tr>

        {{-- Grand total --}}
        <tr class="grand-total-row" style="border-top:3px solid #000;">
            <td colspan="13"
                style="font-size:8px;font-weight:bold;background-color:#A0A0A0;text-align:center;border:2px solid #000;border-top:3px double #000;border-bottom:3px double #000;">
                Grand Total
            </td>
            <td style="font-size:8px;font-weight:bold;background-color:#B8B8B8;border:2px solid #000;text-align:right;">
                {{ number_format($grandTotal, 0) }}
            </td>
        </tr>

    </tbody>
</table>
@include('student.exports.footer')
