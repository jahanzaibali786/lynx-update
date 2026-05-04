<table class="datatable">
    <thead>
        <tr>
            <td colspan="26" style="text-align: left; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="26" style="text-align: left; font-weight: 600; font-size: 12em;">
                <span>{{ __('Salary History Report') }}</span>
            </td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td colspan="26" >
                <span>{{ \Carbon\Carbon::now()->format('F Y') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="26" style="text-align: left;"></td>
        </tr>
        <tr >
            <!-- 26 headers as defined -->
            <th >Sr No</th>
            <th >{{ __('Emp No') }}</th>
            <th >{{ __('Employee Name') }}</th>
            <th >{{ __('CNIC') }}</th>
            <th >{{ __('DOJ') }}</th>
            <th >{{ __('Department') }}</th>
            <th >{{ __('Designation') }}</th>
            <th >{{ __('Branch') }}</th>
            <th >{{ __('Bank A/C') }}</th>
            <th >{{ __('ScaleNO') }}</th>
            @php
                $net_gross = 0;
            @endphp
            @foreach ($heads as $account)
                <th>{{ !empty($account->head) ? @$account->head : '-' }}</th>
            @endforeach
            <th >{{ __('Others') }}</th>
            <th >{{ __('Conveyance') }}</th>
            <th >{{ __('Gross Salary') }}</th>
            <th >{{ __('Emp Sec.') }}</th>
            <th >{{ __('Advances') }}</th>
            <th >{{ __('EOBI Ded') }}</th>
            <th >{{ __('PESSI Ded') }}</th>
            <th >{{ __('Loan Emp Sec.') }}</th>
            <th >{{ __('Income Tax') }}</th>
            <th >{{ __('Other Ded') }}</th>
            <th >{{ __('Other Loan') }}</th>
            <th >{{ __('Net Sal') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid black; border-collapse: collapse;">
        @foreach ($employees as $employee)
            @php
                $lastPayscaleDetail = $employee->employee_payscale_details->last();
                $gross = 0;
                if ($lastPayscaleDetail) {
                    $payscalesauto = \App\Models\EmployeeScale::with(
                        'employeeScaleHeads',
                        'employeeScaleHeads.SalaryHeads',
                        'employeepayScaledetailHeads',
                    )->where('id', $lastPayscaleDetail->pay_scale_id)->first();
                    foreach ($payscalesauto->employeeScaleHeads ?? [] as $scale_head) {
                        $gross += $scale_head->head_value ?? 0;
                    }
                    $gross += $lastPayscaleDetail->drns + $lastPayscaleDetail->conv + 
                              $lastPayscaleDetail->misc + $lastPayscaleDetail->other_add;
                }
        
                // Set defaults for missing fields
                $pessiDed = $employee->pessi ? $employee->pessi . '|' . $employee->pessi_employer : '0|0';
                
            @endphp
            <tr>
                <!-- Existing fields -->
                <td >{{ $loop->iteration }}</td>
                <td >
                    {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                </td>
                <td >{{ $employee->name }}</td>
                <td >{{ $employee->cnic }}</td>
                <td >{{ $employee->company_doj }}</td>
                <td >
                    {{ $employee->department_id ? \Auth::user()->getDepartment($employee->department_id)->name ?? '-' : '-' }}
                </td>
                <td >
                    {{ $employee->designation_id ? \Auth::user()->getDesignation($employee->designation_id)->name ?? '-' : '-' }}
                </td>
                <td >
                    {{ $employee->branch_id ? \Auth::user()->getBranch($employee->branch_id)->name ?? '-' : '-' }}
                </td>
                <td >
                    {{ $lastPayscaleDetail->account_number ?? '-' }}
                </td>
                <td >
                    {{ $lastPayscaleDetail->scale->scale_no ?? '-' }}
                </td>
                {{-- @dd($lastPayscaleDetail->scale->scale_no, $lastPayscaleDetail->basic ,$lastPayscaleDetail->scale->employeeScaleHeads); --}}
                <!-- New salary component fields -->
                @php
                    $net_gross = 0;
                    $basic_value = 0;
                @endphp
                @foreach ($heads as $head)

                    @php
                        // find matching scale head
                        $scale_head = $payscalesauto->employeeScaleHeads->firstWhere('head', $head->id);

                        $value = $scale_head->head_value ?? 0;
                        if ($head->head == 'Initial Basic') {
                            $basic_value = $value;
                        }

                        $net_gross += $value;
                    @endphp

                    <td>{{ $value > 0 ? $value : '0' }}</td>

                @endforeach
                <td >{{ $lastPayscaleDetail->others ?? '0' }}</td>
                <td >{{ $lastPayscaleDetail->conveyance ?? '0' }}</td>
                
                <!-- Gross salary -->
                <td >{{ $gross }}</td>
                
                <!-- Deduction fields -->
                <td >{{ $lastPayscaleDetail->emp_sec ?? '0' }}</td>
                <td >{{ $lastPayscaleDetail->advance ?? '0' }}</td>
                <td >
                    @php
                    $emp=$employee->eobi($employee->id,$employee->owned_by) @endphp
                    {{ $emp['employee_eobi'] }}|{{ $emp['employer_eobi'] }}
                </td>
                <td >
                    {{ $pessiDed }}
                </td>
                <td >{{ $lastPayscaleDetail->loan_emp_sec ?? '0' }}</td>
                <td >{{ $lastPayscaleDetail->itax ?? '0' }}</td>
                <td >{{ $lastPayscaleDetail->other_ded ?? '0' }}</td>
                <td >{{ $lastPayscaleDetail->other_loan ?? '0' }}</td>
                <td >
                    {{ $lastPayscaleDetail->net ?? '0' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>