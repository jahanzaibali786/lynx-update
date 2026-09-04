<style>
    table,tr,th,td{
        border: 1px solid black;
        border-collapse: collapse;
    }

</style>
<table  style="width:100%; font-size:0.9rem;">
    <thead>
        <tr style="background-color:grey; font-size:0.9rem;">
            <th style="">{{__('Emp. Id')}}</th>
            <th style="">{{__('Name')}}</th>
            <th style="">{{__('CNIC')}}</th>
            <th style="">{{__('DoJ')}}</th>
            <th style="">{{__('Dep.') }}</th>
            <th style="">{{__('Des.') }}</th>
            <th style="">{{__('Branch') }}</th>
            <th style="">{{__('Bank A/C') }}</th>
            <!-- //salaryheads -->
             @foreach (\App\Models\SalaryHeads::get() as $salary_head)
             <th style="">{{!empty($salary_head->head) ?  $salary_head->head : ''}}</th>
             @endforeach

             <th style=""> {{__('Gross')}}</th>
             <th style=""> {{__('Employee PFund')}}</th>
            <th style=""> {{__('EOBI')}}</th>
            <th style=""> {{__('Emp Sec.')}}</th>
            <th style=""> {{__('I.Tax')}}</th>
            <th style=""> {{__('Other Ded')}}</th>
            <th style=""> {{__('Other Loan')}}</th>
            <th style=""> {{__('Net')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($employees as $employee)
        @php
            $lastPayscaleDetail = $employee->employee_payscale_details->last();
            $scaleHeads = \App\Models\EmployeeScaleHeads::where('scale_id',@$lastPayscaleDetail->pay_scale_id)->get();

        @endphp
        <tr style="font-size:0.7rem;">
            <td>{{$employee->employee_id }}</td>
            <td class="font-style">{{ $employee->name }}</td>
            <td class="font-style">{{ $employee->cnic }}</td>
            <td class="font-style">{{ $employee->company_doj}}</td>
            <td class="font-style">{{!empty(\Auth::user()->getDepartment($employee->department_id ))?\Auth::user()->getDepartment($employee->department_id )->name:''}}</td>
            <td class="font-style">{{!empty(\Auth::user()->getDesignation($employee->designation_id ))?\Auth::user()->getDesignation($employee->designation_id )->name:''}}</td>
            <td class="font-style">{{!empty(\Auth::user()->getBranch($employee->branch_id ))?\Auth::user()->getBranch($employee->branch_id )->name:''}}</td>
            <td class="font-style">{{ !empty($lastPayscaleDetail) ?$lastPayscaleDetail->account_number  : '-' }}</td>
            @foreach (\App\Models\SalaryHeads::get() as $salary_head)
            @php
            $scaleHeads = \App\Models\EmployeeScaleHeads::where('scale_id',@$lastPayscaleDetail->pay_scale_id)->where('head',$salary_head->id)->first();
            @endphp
            @if($scaleHeads)
            <td class="font-style">{{ !empty($scaleHeads->head_value) ? $scaleHeads->head_value: '-' }}</td>
            @else
            <td>-</td>
            @endif
            @endforeach
            <td class="font-style">{{ number_format(optional($lastPayscaleDetail)->resolved_gross_salary ?? 0, 2) }}</td>
            <td class="font-style">{{!empty(@$lastPayscaleDetail->eobi_employer) ? @$lastPayscaleDetail->eobi_employer  : '-' }}</td>
            <td class="font-style">{{ !empty($employee) ? $employee->eobi . '|' . $employee->eobi_employer : '-' }}</td>
            <td class="font-style">{{ !empty($lastPayscaleDetail->emp_sec) ?$lastPayscaleDetail->emp_sec  : '-'}}</td>
            <td class="font-style">{{ isset($lastPayscaleDetail->itax) ? $lastPayscaleDetail->itax  : '0'}}</td>
            <td class="font-style">{{ isset($lastPayscaleDetail->other_loan) ? $lastPayscaleDetail->other_loan  : '0'}}</td>
            <td class="font-style">{{ isset($lastPayscaleDetail->pessi) ? $lastPayscaleDetail->pessi  : '0'}}</td>
            <td class="font-style">{{!empty(@$lastPayscaleDetail->net) ? @$lastPayscaleDetail->net  : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
