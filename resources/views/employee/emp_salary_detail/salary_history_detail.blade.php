<div class="modal-body">
    <div class="row" style="line-height: 1.5rem;">
        @php
        $gross = 0;
        $lastPayscaleDetail = $employee->employee_payscale_details->last();
        $payScale = \App\Models\EmployeeScale::with('employeeScaleHeads', 'employeeScaleHeads.salaryHeads')
        ->where('created_by', \Auth::user()->creatorId())
        ->find($lastPayscaleDetail->scale->id);
        @endphp
        <div class="col-md-4">
            <b>Branch:</b>{{!empty(\Auth::user()->getBranch($employee->branch_id)) ? \Auth::user()->getBranch($employee->branch_id)->name : ''}}
        </div>
        <div class="col-md-4"><b>Name :</b>{{@$employee->name}}</div>
        <div class="col-md-4"><b>Father Name :</b>{{@$employee->f_name}}</div>
        <div class="col-md-4"><b>Designation
                :</b>{{!empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : ''}}
        </div>
        <div class="col-md-4"><b>Scale
                :</b>{{!empty($lastPayscaleDetail->scale) ? $lastPayscaleDetail->scale->scale_no : '' }}</div>
        <div class="col-md-4"><b>Effect From
                :</b>{{!empty($lastPayscaleDetail->scale) ? $lastPayscaleDetail->scale->effect_from : '' }}</div>
    </div>
    <div style="background-color: gray; padding:5px; color:white; margin:5px 0px; font-size:1rem;">Salary Structure
    </div>
    <div class="row">

        @foreach (@$payScale->employeeScaleHeads as $salhead)
        @php
        $gross += $salhead->head_value;
        @endphp
        <div class="col-md-4"><b>{{@$salhead->salaryHeads->head}}
                :</b>{{!empty($salhead->salaryHeads) ? $salhead->head_value : '' }}</div>
        @endforeach
        <div class="col-md-4"><b>Gross
                :</b>{{!empty($lastPayscaleDetail) ? $lastPayscaleDetail->net + $lastPayscaleDetail->emp_sec : '0' }}
        </div>
        <div class="col-md-4"><b>Net
                :</b>{{!empty($lastPayscaleDetail) ? $lastPayscaleDetail->net : '-' }}
        </div>


    </div>
</div>