<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Employee::where('created_by', \Auth::user()->creatorId());

        if (\Auth::user()->type !== 'company') {
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        if ($this->request) {
            $branchId = $this->request->input('branches', $this->request->input('branch'));
            if (! empty($branchId)) {
                $query->where('owned_by', $branchId);
            }

            if ($this->request->filled('department_id') && $this->request->department_id !== 'all') {
                $query->where('department_id', $this->request->department_id);
            }

            if ($this->request->filled('designation_id') && $this->request->designation_id !== 'all') {
                $query->where('designation_id', $this->request->designation_id);
            }

            if ($this->request->filled('ter_status')) {
                $query->where('is_res_ter', $this->request->ter_status);
            }
        }

        if (! $this->request || ! $this->request->filled('ter_status')) {
            $query->where('is_res_ter', 0);
        }

        $data = $query->get();
        foreach($data as $k => $employee)
        {
            unset($employee->id,$employee->password,$employee->user_id,$employee->employee_id,$employee->documents,$employee->salary_type,$employee->tax_payer_id,$employee->is_active,$employee->created_by,$employee->created_at,$employee->updated_at);
            $data[$k]["branch_id"]=!empty($employee->ownedBranch)?$employee->ownedBranch->name:'-';
            $data[$k]["department_id"]=!empty($employee->department)?$employee->department->name:'-';
            $data[$k]["designation_id"]= !empty($employee->designation) ? $employee->designation->name : '-';
            $data[$k]["salary"]=Employee::employee_salary($employee->salary);

        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Name",
            "Date of Birth",
            "Gender",
            "Phone Number",
            "Address",
            "Email ID",
            "Branch",
            "Department",
            "Designation",
            "Date of Join",
            "Account Holder Name",
            "Account Number",
            "Bank Name",
            "Bank Identifier Code",
            "Branch Location",
            "Salary",

        ];
    }
}
