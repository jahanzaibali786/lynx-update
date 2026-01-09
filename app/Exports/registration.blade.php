<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family:calibri !important;
            }
        </style>
        @include('student.exports.header')
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr No') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('B.Sr#') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Reg No') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Reg Date') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Student name') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Father Name') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Cell No') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Session') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Class') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('DOB') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Gender') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Status') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('RegCtg.') }}</th>
            {{-- reg fee --}}
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Rg.Fee') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php
            $globalIndex = 1;
            $grandTotalRegFee = 0;
        @endphp
        @foreach ($registrations as $branchId => $students)
            {{-- Branch name row --}}
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="14" style="font-weight: bold; background-color:#bcbcbc; border: 1px solid #000;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>
            @php
                $branchTotalRegFee = 0;
                $branchSr = 1;
            @endphp
            @foreach ($students as $student)
                @php
                    $branchTotalRegFee += $student->registrationfee;
                    $grandTotalRegFee += $student->registrationfee;
                @endphp
                <tr>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ $globalIndex++ }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ $branchSr++ }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ @$student->reg_no }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ $student->regdate == '0000-00-00' || !$student->regdate ? '' : date('d-M-Y', strtotime($student->regdate)) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; width: 100px; white-space: normal; word-wrap: break-word; border: 1px solid #D3D3D3;">
                        {!! str_replace(' ', '&nbsp;', e(@$student->stdname ?? '')) !!}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; width: 100px; white-space: normal; word-wrap: break-word; border: 1px solid #D3D3D3;">
                        {!! str_replace(' ', '&nbsp;', e(@$student->fathername ?? '')) !!}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; white-space: normal; word-wrap: break-word; border: 1px solid #D3D3D3;">
                        {!! nl2br(str_replace(',', ",\n", e(@$student->fathercell ?? ''))) !!}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ !empty(@$student->session) ? @$student->session->year : '-' }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ !empty(@$student->class) ? @$student->class->name : '-' }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ $student->dob == '0000-00-00' || !$student->dob ? '' : date('d-M-Y', strtotime($student->dob)) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ strtoupper($student->gender) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        @if($student->roll_no != null)
                            {{ 'Enrolled' }}
                        @else
                            {{ 'Not Enrolled' }}
                        @endif
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ @$student->registeroption->name }}
                    </td>
                    <td style="text-align: right; font-family:calibri; font-size: 8px; border: 1px solid #D3D3D3;">
                        {{ number_format($student->registrationfee, 0) }}
                    </td>
                </tr>
                {{-- branch total --}}
                @if ($loop->last)
                    <tr class="branch-total-row" style="border-top: 3px solid #000;">
                        <td colspan="13" style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000;">Branch Total</td>
                        <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align:right;">
                            {{ number_format($branchTotalRegFee, 0) }}
                        </td>
                    </tr>
                @endif
            @endforeach
        @endforeach
        <tr>
            <td colspan="13" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="13" style="height: 20px;"></td>
        </tr>
        {{-- Grand total row --}}
        <tr class="grand-total-row" style="border-top: 3px solid #000;">
            <td colspan="13" style="font-size: 8px; font-weight: bold; background-color: #A0A0A0; text-align: center; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000;">Grand Total</td>
            <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; border-top: 3px double #000; border-bottom: 3px double #000; text-align:right;">
                {{ number_format($grandTotalRegFee, 0) }}
            </td>
        </tr>
        @include('student.exports.footer')
    </tbody>
</table>