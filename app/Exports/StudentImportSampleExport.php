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
            'Father Email',
            'Mother Email',
            'City',
            'District',
            'Address',
            'Permanent Address',
            'Previous School',
            'Previous Class',
            'Birth Place',
            'Father Occupation',
            'Mother Occupation',
            'Guardian Name',
            'Guardian Relation',
            'Guardian Occupation',
            'Guardian CNIC',
            'Guardian Phone',
            'Guardian Address',
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
                $s->father_email ?? '',
                $s->mother_email ?? '',
                $s->city ?? '',
                $s->district ?? '',
                $s->address ?? '',
                $s->permanent_address ?? '',
                $s->prevschool ?? '',
                $s->prevclass ?? '',
                $s->birth_place ?? '',
                $s->fatherprofession ?? '',
                $s->motherprofession ?? '',
                $s->guardianname ?? '',
                $s->guardianrelation ?? '',
                $s->guardianprofession ?? '',
                $s->guardiancnic ?? '',
                $s->guardianphone ?? '',
                $s->guardianaddress ?? '',
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
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(28);
        $sheet->getColumnDimension('N')->setWidth(28);
        $sheet->getColumnDimension('O')->setWidth(28);
        $sheet->getColumnDimension('P')->setWidth(14);
        $sheet->getColumnDimension('Q')->setWidth(14);
        $sheet->getColumnDimension('R')->setWidth(30);
        $sheet->getColumnDimension('S')->setWidth(30);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('U')->setWidth(14);
        $sheet->getColumnDimension('V')->setWidth(16);
        $sheet->getColumnDimension('W')->setWidth(16);
        $sheet->getColumnDimension('X')->setWidth(20);
        $sheet->getColumnDimension('Y')->setWidth(20);
        $sheet->getColumnDimension('Z')->setWidth(20);
        $sheet->getColumnDimension('AA')->setWidth(14);
        $sheet->getColumnDimension('AB')->setWidth(20);
        $sheet->getColumnDimension('AC')->setWidth(18);
        $sheet->getColumnDimension('AD')->setWidth(20);
        $sheet->getColumnDimension('AE')->setWidth(20);
        $sheet->getColumnDimension('AF')->setWidth(30);
        $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
        $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('YYYY-MM-DD');
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('K:K')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('L:L')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('AB:AB')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('AC:AC')->getNumberFormat()->setFormatCode('@');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();

                // Insert instruction row after header
                $sheet->insertNewRowBefore(2, 1);
                $sheet->setCellValue('A2', 'Date format: YYYY-MM-DD (e.g., 2026-07-06). Invalid dates will be skipped.');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->getStyle('A2')->getFont()->setItalic(true);
                $sheet->getStyle('A2')->getFont()->getColor()->setARGB('FFFF0000');

                $highest = $highestColumn . $sheet->getHighestRow();
                $sheet->getStyle('A1:' . $highest)
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $sheet->getStyle('A1:' . $highest)
                    ->getAlignment()->setWrapText(true);
            },
        ];
    }
}
