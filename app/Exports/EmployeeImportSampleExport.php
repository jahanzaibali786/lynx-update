<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeImportSampleExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $employees;
    protected $branchId;
    protected $departmentId;
    protected $designationId;
    protected $status;

    public function __construct($branchId = null, $departmentId = null, $designationId = null, $status = null)
    {
        $this->branchId = $branchId;
        $this->departmentId = $departmentId;
        $this->designationId = $designationId;
        $this->status = $status;

        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        $query = Employee::where('is_active', 1);

        if ($userType == 'company') {
            $query->where('created_by', $userCreatorId);
        } else {
            $query->where('owned_by', $userOwnedId);
        }

        if ($this->branchId && $this->branchId !== 'all') {
            $query->where('branch_id', $this->branchId);
        }
        if ($this->departmentId && $this->departmentId !== 'all') {
            $query->where('department_id', $this->departmentId);
        }
        if ($this->designationId && $this->designationId !== 'all') {
            $query->where('designation_id', $this->designationId);
        }
        if ($this->status !== null && $this->status !== '' && $this->status !== 'all') {
            $query->where('is_res_ter', $this->status);
        }

        $this->employees = $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Employee (ID - Name)',
            'Salute',
            'Father Name',
            'CNIC',
            'Date of Birth',
            'Gender',
            'Religion',
            'Blood Group',
            'Phone',
            'Email',
            'Area',
            'Address',
            'Present Address',
            'Category',
            'Date of Joining',
            'Probation Period',
            'Account Holder Name',
            'Account Number',
            'Bank Name',
            'Bank Identifier Code',
            'Branch Location',
            'Tax Payer ID',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->employees as $emp) {
            $prefix = \Auth::user()->employeeIdFormat($emp->employee_id);
            $rows[] = [
                $prefix . ' - ' . $emp->name,
                $emp->salute ?? '',
                $emp->f_name ?? '',
                $emp->cnic ?? '',
                $emp->dob ?? '',
                $emp->gender ?? '',
                $emp->religion ?? '',
                $emp->blood_group ?? '',
                $emp->phone ?? '',
                $emp->email ?? '',
                $emp->area ?? '',
                $emp->address ?? '',
                $emp->present_address ?? '',
                $emp->category ?? '',
                $emp->company_doj ?? '',
                $emp->probation_period ?? '',
                $emp->account_holder_name ?? '',
                $emp->account_number ?? '',
                $emp->bank_name ?? '',
                $emp->bank_identifier_code ?? '',
                $emp->branch_location ?? '',
                $emp->tax_payer_id ?? '',
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFBFBFBF');
        $sheet->getColumnDimension('A')->setWidth(35);
        foreach (range('B', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
        $sheet->getStyle('O:O')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            },
        ];
    }
}
