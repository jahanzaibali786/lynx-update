<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    td {
        padding-left: 3px;
    }
</style>

<div class="card p-4" style="margin-top: -80px;">
    <div class="mt-4">
        {{-- <div style="width: 100%; text-align: center;">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
            <p style="font-size: 1.1rem; text-align: center; margin-top:-20px"><b> </b></p>
        </div>
        <div class="" style="width: 100%; display: flex; justify-content: space-between;">
            <p><b>Period From: </b>{{date('d M Y', strtotime($request->input('date_from')))}}</p>
            <p><b>Branch: </b>{{ @$branches[request('branch')] ?? 'All Branches' }}</p>
            <p><b>Period To: </b>{{date('d M Y', strtotime($request->input('date_to')));}}</p>
        </div> --}}
        @php
            $i=1;
            $challansData = [];
            foreach ($studentData as $branchId => $students) {
                foreach ($students as $student) {
                    $challansData[$student->id] = $challans[$student->id] ?? [];
                }
            }
        @endphp
        @foreach ($studentData as $branchId => $students)
        <div class="" style="width: 100%;">
            <span style="font-size:1rem; font-weight:600; padding:10px; width:100%;">
                {{ @$branches[@$branchId] ?? 'All Branches' }}
            </span>
            <table  class="table" style="width: 100%;">
                <thead>
                    <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                        <th style="width:4%; border: 1px solid black;">{{ __('Sr No.') }}</th>
                        <th style="width:6%; border: 1px solid black;">{{ __('B Sr No.') }}</th>
                        <th style="width:6%; border: 1px solid black;">{{ __('Reg. #') }}</th>
                        <th style="width:9%; border: 1px solid black;">{{ __('Reg. Date') }}</th>
                        <th style="width:15%; border: 1px solid black;">{{ __('Student Name') }}</th>
                        <th style="width:15%; border: 1px solid black;">{{ __('Father Name') }}</th>
                        <th style="width:10%; border: 1px solid black;">{{ __('Class') }}</th>
                        <th style="width:10%; border: 1px solid black;">{{ __('Phone No') }}</th>
                        <th style="width:10%; border: 1px solid black;">{{ __('Gender') }}</th>
                        <th style="width:10%; border: 1px solid black;">{{ __('Registration Status') }}</th>
                        <th style="width:7%; border: 1px solid black;">{{ __('Amount') }}</th>
                        <th style="width:10%; border: 1px solid black;">{{ __('Adm. Status') }}</th>
                    </tr>
                </thead>
                <tbody style="font-size:0.7rem;">
                    @foreach ($students as $index => $student)
                    <tr>
                        <td style="width:4%; border: 1px solid black;">{{ $i }}</td>
                        <td style="width:6%; border: 1px solid black;">{{ $index + 1 }}</td>
                        <td style="width:6%; border: 1px solid black;">{{ $student->id }}</td>
                        <td style="width:9%; border: 1px solid black;">{{ date('d M Y', strtotime($student->regdate)) }}</td>
                        <td style="width:15%; border: 1px solid black;">{{ @$student->stdname ?? '' }}</td>
                        <td style="width:15%; border: 1px solid black;">{{ @$student->fathername ?? '' }}</td>
                        <td style="width:10%; border: 1px solid black;">{{ @$student->class->name ?? '' }}</td>
                        <td style="width:10%; border: 1px solid black;">{{ @$student->fatherphone ?? '' }}</td>
                        <td style="width:10%; border: 1px solid black;">{{ @$student->gender ?? '' }}</td>
                        <td style="width:10%; border: 1px solid black;">{{ @$student->registeroption->name ?? '' }}</td>
                        <td style="width:7%; border: 1px solid black;">{{ @$challans[$student->id]->paid_amount ?? '0' }}</td>
                        <td style="width:10%; border: 1px solid black;">{{ $student->student_status == 'Enrolled' ? 'Yes' : 'No' }}</td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @endforeach
                    <tr style="font-weight:bold;">
                        <td colspan="9" style="text-align:right; border: 1px solid black;">Branch Total:</td>
                        <td style="border: 1px solid black;">{{ $branchTotals[$branchId] ?? '0' }}</td>
                        <td style="border: 1px solid black;"></td>
                    </tr>
                </tbody>
            </table>
            {{-- <div class="subtotal row justify-content-end px-5" style="padding: 0px 120px !important;">
                <div class="" colspan="8"><b>Branch Total</b></div>
                <div class="col-auto">{{ $branchTotals[$branchId] }}</div>
            </div> --}}
            @endforeach
            <table  class="table" style="width:100%">
                <thead style="font-weight:bold; background-color:grey; color:#fff;">
                    <td  style="width:84%; text-align:right; border: 1px solid black;">Grand Total :</td>
                    <td style="width:8%; border: 1px solid black;">{{ $grandTotal }}</td>
                    <td style="width:8%; border: 1px solid black;"></td>
                </thead>
            </table>
        </div>
        {{-- <div class="card mt-2 p-4">
            <div class="subtotal row justify-content-end px-5" style="padding: 0px 100px !important; background-color:grey; color:#fff;">
                <div class="col-auto"><b>Grand Total</b></div>
                <div class="col-auto">{{ $grandTotal }}</div>
            </div>
        </div> --}}
    </div>
</div>
