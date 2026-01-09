 @php
            $globalIndex = 1;
        @endphp
        @foreach ($registrations as $branchId => $students)
            {{-- Branch name row --}}
            <tr class="branch-header" style="background-color:#bcbcbc;">
                <td colspan="14" style="font-weight: bold; background-color:#bcbcbc; border: 1px solid #000;">
                    {{ $branches[$branchId] ?? 'Branch Not Specified' }}
                </td>
            </tr>

            @foreach ($students as $index => $student)

                <tr>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ $globalIndex++ }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ $index + 1 }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ $student->reg_no }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ $student->regdate == '0000-00-00' || !$student->regdate ? '' : date('d-M-Y', strtotime($student->regdate)) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; width: 100px; white-space: normal; word-wrap: break-word;">
                        {!! str_replace(' ', '&nbsp;', e($student->stdname )) !!}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; width: 100px; white-space: normal; word-wrap: break-word;">
                        {!! str_replace(' ', '&nbsp;', e($student->fathername )) !!}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px; white-space: normal; word-wrap: break-word;">
                        {!! nl2br(str_replace(',', ",\n", e($student->fathercell))) !!}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ !empty(@$student->session) ? @$student->session->year : '-' }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px;">
                        {{ !empty(@$student->class) ? @$student->class->name : '-' }}
                    </td>
                    <td style="text-align: center; font-family:calibri; font-size: 8px;">
                        {{ $student->dob == '0000-00-00' || !$student->dob ? '' : date('d-M-Y', strtotime($student->dob)) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px;">
                        {{ strtoupper($student->gender) }}
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px;">
                        @if($student->roll_no != null)
                            {{ 'Enrolled' }}
                        @else
                            {{ 'Not Enrolled' }}
                        @endif
                    </td>
                    <td style="text-align: left; font-family:calibri; font-size: 8px;">
                        {{ @$student->registeroption->name }}
                    </td>
                    <td style="text-align: right; font-family:calibri; font-size: 8px;">
                        {{ $student->registrationfee }}
                    </td>
                </tr>
                @endforeach
                {{-- branch total --}}
                <tr class="branch-total-row" style="border-top: 3px solid #000;">
                    <td colspan="13" style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; text-align: center; border: 2px solid #000;">Branch Total</td>
                    <td style="font-size: 8px; font-weight: bold; background-color: #B8B8B8; border: 2px solid #000; text-align:right;">
                        {{ number_format($branchTotals[$branchId], 0) }}
                    </td>
                </tr>
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
                {{ number_format($grandTotal, 0) }}
            </td>
        </tr>