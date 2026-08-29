<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationDateImportSummaryExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected array $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function headings(): array
    {
        return [
            'Row No',
            'Reg No',
            'Roll No',
            'Student Name',
            'Reg Branch',
            'Reg Branch ID',
            'Registration ID',
            'Enrollment ID',
            'Previous Reg Date',
            'Updated Reg Date',
            'Status',
            'Remarks',
            'Reason',
        ];
    }

    public function array(): array
    {
        return array_map(function (array $row) {
            return [
                $row['row_no'] ?? '',
                $row['reg_no'] ?? '',
                $row['roll_no'] ?? '',
                $row['student_name'] ?? '',
                $row['reg_branch_name'] ?? '',
                $row['reg_branch_id'] ?? '',
                $row['registration_id'] ?? '',
                $row['enrollment_id'] ?? '',
                $row['previous_reg_date'] ?? '',
                $row['reg_date'] ?? '',
                $row['status'] ?? '',
                $row['remarks'] ?? '',
                $row['reason'] ?? '',
            ];
        }, $this->report['rows'] ?? []);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:M1');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(34);
        $sheet->getColumnDimension('M')->setWidth(34);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->setCellValue('A1', 'Registration Date Import Report');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->setCellValue('A2', 'File: ' . ($this->report['file_name'] ?? '-') . ' | Generated: ' . ($this->report['generated_at'] ?? now()->format('Y-m-d H:i:s')) . ' | Total Rows: ' . ((int) ($this->report['total_rows'] ?? 0)) . ' | Updated: ' . ((int) ($this->report['updated_count'] ?? 0)) . ' | Skipped: ' . ((int) ($this->report['skipped_count'] ?? 0)));
                $sheet->getStyle('A2')->getAlignment()->setWrapText(true);

                $reasonCounts = $this->report['reason_counts'] ?? [];
                $reasonText = empty($reasonCounts)
                    ? 'Skip Reasons: -'
                    : 'Skip Reasons: ' . collect($reasonCounts)->map(function ($count, $reason) {
                        return $reason . ' (' . $count . ')';
                    })->implode(' | ');
                $sheet->mergeCells('A3:' . $highestColumn . '3');
                $sheet->setCellValue('A3', $reasonText);
                $sheet->getStyle('A3')->getAlignment()->setWrapText(true);

                $sheet->getStyle('A4:M4')->getFont()->setBold(true);
                $sheet->freezePane('A5');
            },
        ];
    }
}
