<style>
    table,
    tr,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<div class="card mt-2 p-4" style="width: 100%;">
    <div class="mt-4" style="margin: 0 auto; padding-top: 30px;">
        @php
            $grandTotalAmount = 0; // Initialize grand total accumulator
        @endphp
        @foreach ($studentData as $branchId => $students)
            <div style="width: 100%;">
                <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                    {{ $branches[$branchId] ?? 'All Branches' }}
                </span>
                <div class="table-responsive " style="margin-top: 50px;">
                    <table class="table" style="width:100%;">
                        <thead>
                            <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                                <th style="width:5%; border: 1px solid black;">{{ __('Sr No.') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('B Sr No.') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('Reg No #') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('Roll No #') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('Challan No #') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('Billing Month') }}</th>
                                <th style="width:15%; border: 1px solid black;">{{ __('Admission Date') }}</th>
                                <th style="width:15%; border: 1px solid black;">{{ __('Class') }}</th>
                                <th style="width:15%; border: 1px solid black;">{{ __('Student Name') }}</th>
                                @foreach ($heads as $head)
                                    <th style="width:5%; border: 1px solid black;">{{ $head->fee_head ?? '-' }}</th>
                                @endforeach
                                <th style="width:5%; border: 1px solid black;">{{ __('Amount') }}</th>
                                <th style="width:5%; border: 1px solid black;">{{ __('Adm. Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                @php
                                    $studentRegNo = @$student->StudentRegistration->reg_no;
                                    // Get pre-calculated challan data instead of querying database
                                    $challanData = $studentChallanData[$studentRegNo] ?? [
                                        'challan_no' => '',
                                        'heads' => [],
                                        'total' => 0,
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $student->owned_by }}</td>
                                    <td>{{ $student->id }}</td>
                                    <td>{{ $student->enrollId ?? '' }}</td>
                                    <td>{{ $challanData['challan_no'] }}</td>
                                    <td>{{ !empty($challanData['fee_month']) ? date('M Y', strtotime($challanData['fee_month'])) : '-' }}</td>
                                    <td>{{ date('d M Y', strtotime($student->adm_date ?? '')) }}</td>
                                    <td>{{ @$student->class->name }}</td>
                                    <td>{{ @$student->StudentRegistration->stdname ?? '' }}</td>
                                    @foreach ($heads as $head)
                                        <td>
                                            @php
                                                $amount = '';
                                                foreach ($challanData['heads'] as $challhead) {
                                                    if ($challhead['head_id'] == $head->id) {
                                                        $amount = $challhead['amount'];
                                                        break;
                                                    }
                                                }
                                                echo $amount;
                                            @endphp
                                        </td>
                                    @endforeach
                                    <td>{{ $challanData['total'] }}</td>
                                    <td>{{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="font-weight:bold;">
                                <td colspan="{{ 8 + count($heads) }}"
                                    style="text-align:right; border: 1px solid black;">Branch Total:</td>
                                <td style="border: 1px solid black;">{{ $branchTotals[$branchId] ?? '0' }}</td>
                                <td style="border: 1px solid black;"></td>
                            </tr>
                        </tbody>
                        {{-- <tbody>
                            @foreach ($students as $index => $student)
                                @php
                                    $challanHeads = [];
                                    $challan = App\Models\Challans::where(
                                        'student_id',
                                        @$student->StudentRegistration->reg_no,
                                    )
                                        ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
                                        ->with('heads', 'heads.feehead')
                                        ->first();
                                    if ($challan) {
                                        foreach ($challan->heads as $head) {
                                            $challanHeads[] = [
                                                'name' => $head->feehead->fee_head,
                                                'amount' => $head->price,
                                                'head_id' => $head->head_id,
                                            ];
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td style="width:5%; border: 1px solid black;">{{ $index + 1 }}</td>
                                    <td style="width:5%; border: 1px solid black;">{{ $student->owned_by }}</td>
                                    <td style="width:5%; border: 1px solid black;">{{ $student->id }}</td>
                                    <td style="width:5%; border: 1px solid black;">{{ $student->enrollId ?? '' }}</td>
                                    <td style="width:5%; border: 1px solid black;">{{ $challan->challanNo ?? '' }}</td>
                                    <td style="width:5%; border: 1px solid black;">
                                        {{ date('d M Y', strtotime($student->created_at ?? '')) }}</td>
                                    <td style="width:5%; border: 1px solid black;">{{ @$student->class->name }}</td>
                                    <td style="width:5%; border: 1px solid black;">
                                        {{ @$student->StudentRegistration->stdname ?? '' }}</td>
                                    @foreach ($heads as $head)
                                        <td style="width:5%; border: 1px solid black;">
                                            @foreach ($challanHeads as $challhead)
                                                @if ($challhead['head_id'] == $head->id)
                                                    {{ $challhead['amount'] }}
                                                    @break
                                                @endif
                                            @endforeach
                                        </td>
                                    @endforeach
                                    <td style="width:5%; border: 1px solid black;">{{ $student->total_amount ?? '0' }}
                                    </td>
                                    <td style="width:5%; border: 1px solid black;">
                                        {{ @$student->StudentRegistration->student_status == 'Enrolled' ? 'Yes' : 'No' }}
                                    </td>
                                </tr>
                                @php
                                    // Accumulate branch total for grand total
                                    $grandTotalAmount += $student->total_amount ?? 0;
                                @endphp
                            @endforeach

                            <!-- Branch Total Row -->
                            <tr style="font-weight:bold;">
                                <td colspan="{{ 8 + count($heads) }}"
                                    style="text-align:right; border: 1px solid black;">Branch Total:</td>
                                <td style="border: 1px solid black;">{{ $branchTotals[$branchId] ?? '0' }}</td>
                                <td style="border: 1px solid black;"></td>
                            </tr>
                        </tbody> --}}
                    </table>
                </div>
            </div>
        @endforeach
        <br>

        <!-- Grand Total Section -->
        <div class="card mt-2 p-4">
            <div class="subtotal row justify-content-end px-5"
                style="padding: 0px 100px !important; background-color:grey; color:#fff;">
                <div class="col-auto"><b>Grand Total</b></div>
                <div class="col-auto">{{ $grandTotalAmount }}</div>
            </div>
        </div>
    </div>
</div>
