<?php

namespace App\Exports;

use App\Models\StudentRegistration;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationDateImportSampleExport implements FromArray, WithHeadings, WithStyles, WithEvents, WithTitle
{
    protected $registrations;

    public function __construct()
    {
        $this->registrations = StudentRegistration::query()
            ->select('id', 'stdname', 'reg_no', 'regdate')
            ->with(['enrollment' => function ($query) {
                $query->select('id', 'regId', 'active_status')->where('active_status', 1);
            }])
            ->whereHas('enrollment', function ($query) {
                $query->where('active_status', 1);
            })
            ->orderBy('stdname')
            ->get();
    }

    public function title(): string
    {
        return 'Registration Update';
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Reg No',
            'Current Reg Date',
            'New Reg Date',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->registrations as $registration) {
            $rows[] = [
                $registration->stdname ?? '',
                $registration->reg_no ?? '',
                $registration->regdate ? date('d-M-Y', strtotime($registration->regdate)) : '',
                '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:D1');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->setCellValue('A1', 'Registration Date Update Template');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->setCellValue('A2', 'Match by Student Name + Reg No. Fill only the New Reg Date column.');
                $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
