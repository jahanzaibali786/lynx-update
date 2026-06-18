<?php

namespace App\Exports;

use App\Models\StudentRegistration;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentImportSampleExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $students;
    protected $branchId;
    protected $classId;
    protected $sessionId;
    protected $status;

    public function __construct($branchId = null, $classId = null, $sessionId = null, $status = null)
    {
        $this->branchId = $branchId;
        $this->classId = $classId;
        $this->sessionId = $sessionId;
        $this->status = $status;

        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();

        $query = StudentRegistration::where('student_status', 'Enrolled')
            ->whereHas('enrollment', function ($q) {
                $q->where('active_status', 1);
            });

        if ($userType == 'company') {
            $query->where('created_by', $userCreatorId);
        } else {
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        if ($this->branchId && $this->branchId !== 'all') {
            $query->where('owned_by', $this->branchId);
        }
        if ($this->classId && $this->classId !== 'all') {
            $query->where('class_id', $this->classId);
        }
        if ($this->sessionId && $this->sessionId !== 'all') {
            $query->where('session_id', $this->sessionId);
        }
        if ($this->status !== null && $this->status !== '' && $this->status !== 'all') {
            $query->where('student_status', $this->status);
        }

        $this->students = $query->orderBy('stdname')->get();
    }

    public function headings(): array
    {
        return [
            'Student (Name - Roll No)',
            'Reg Date',
            'Reg No',
            'Father Name',
            'Mother Name',
            'DOB',
            'Gender',
            'Religion',
            'Nationality',
            'Father CNIC',
            'Father Phone',
            'Father Cell',
            'Mother CNIC',
            'Email',
            'City',
            'District',
            'Address',
            'Permanent Address',
            'Previous School',
            'Previous Class',
            'Birth Place',
            'Remarks',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->students as $s) {
            $rows[] = [
                $s->stdname . ' - ' . $s->roll_no,
                $s->regdate ?? '',
                $s->reg_no ?? '',
                $s->fathername ?? '',
                $s->mothername ?? '',
                $s->dob ?? '',
                $s->gender ?? '',
                $s->religion ?? '',
                $s->nationality ?? '',
                $s->fathercnic ?? '',
                $s->fatherphone ?? '',
                $s->fathercell ?? '',
                $s->mothercnic ?? '',
                $s->email ?? '',
                $s->city ?? '',
                $s->district ?? '',
                $s->address ?? '',
                $s->permanent_address ?? '',
                $s->prevschool ?? '',
                $s->prevclass ?? '',
                $s->birth_place ?? '',
                $s->remarks ?? '',
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
        $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
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
