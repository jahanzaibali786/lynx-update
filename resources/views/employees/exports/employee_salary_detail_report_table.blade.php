<table class="datatable">
    <thead>
        <tr>
            <td colspan="26" style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 35rem;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="26" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ __('Salary History Report') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="26" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>{{ \Carbon\Carbon::now()->format('F Y') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="26" style="text-align: center;"></td>
        </tr>
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray;">
            <!-- 26 headers as defined -->
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">Sr No</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Emp No') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Employee Name') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('DOJ') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Service Period') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Department') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Designation') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Branch') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Bank A/C') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('ScaleNO') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Basic') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('House Rent') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Medical') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Others') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Conveyance') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Gross Salary') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Emp Sec.') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Training Course') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Advances') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('EOBI Ded') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('PESSI Ded') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Loan Emp Sec.') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Income Tax') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Other Ded') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Other Loan') }}</th>
            <th style="font-size: 8px; text-align: center; width: 150px; border: 2px solid black; font-weight: bold; background-color: gray;">{{ __('Net Sal') }}</th>
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
                $servicePeriod = $employee->service ?? "-";
                $pessiDed = $employee->pessi ? $employee->pessi . '|' . $employee->pessi_employer : '0|0';
            @endphp
            <tr>
                <!-- Existing fields -->
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $loop->iteration }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse; text-align: right;">
                    {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $employee->name }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $employee->company_doj }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $servicePeriod }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $employee->department_id ? \Auth::user()->getDepartment($employee->department_id)->name ?? '-' : '-' }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $employee->designation_id ? \Auth::user()->getDesignation($employee->designation_id)->name ?? '-' : '-' }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $employee->branch_id ? \Auth::user()->getBranch($employee->branch_id)->name ?? '-' : '-' }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $lastPayscaleDetail->account_number ?? '-' }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $lastPayscaleDetail->scale->scale_no ?? '-' }}
                </td>
                
                <!-- New salary component fields -->
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->basic ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->house_rent ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->medical ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->others ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->conveyance ?? '0' }}</td>
                
                <!-- Gross salary -->
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $gross }}</td>
                
                <!-- Deduction fields -->
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->emp_sec ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->training_course ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->advances ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $employee->eobi }}|{{ $employee->eobi_employer }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $pessiDed }}
                </td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->loan_emp_sec ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->itax ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->other_ded ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">{{ $lastPayscaleDetail->other_loan ?? '0' }}</td>
                <td style="border: 2px solid black; font-size: 8px; border-collapse: collapse;">
                    {{ $lastPayscaleDetail->net ?? '0' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>