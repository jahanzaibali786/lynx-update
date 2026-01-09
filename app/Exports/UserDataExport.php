<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class UserDataExport implements FromArray, WithStyles, WithTitle, WithEvents
{
    public function array(): array
    {
        return [['', '', '', ''], ['ID', 'Name', 'Email', 'Created At'], [1, 'John Doe', 'john@example.com', '2024-09-10'], [2, 'Jane Smith', 'jane@example.com', '2024-09-11']];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Sheet1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:A3');

                $sheet->setCellValue('B1', 'Main Heading');
                $sheet->mergeCells('B1:D1');
                $sheet->getStyle('B1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B1')->getAlignment()->setVertical('center');

                $sheet->setCellValue('B2', 'Sub-heading 1');
                $sheet->mergeCells('B2:D2');
                $sheet->getStyle('B2')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B2')->getAlignment()->setVertical('center');

                $sheet->setCellValue('B3', 'Sub-heading 2');
                $sheet->mergeCells('B3:D3');
                $sheet->getStyle('B3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B3')->getAlignment()->setVertical('center');

                $drawing = new Drawing();
                $drawing->setPath(public_path('assets/images/favicon.png'));
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(20);
                $drawing->setOffsetY(20);
                $drawing->setWorksheet($sheet);

                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(30);

                $dataStartRow = 3;
                $data = $this->array();

                foreach ($data as $rowIndex => $rowData) {
                    foreach ($rowData as $colIndex => $cellData) {
                        $sheet->setCellValueByColumnAndRow($colIndex + 1, $dataStartRow + $rowIndex, $cellData);
                    }
                }
                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'ffffff'],
                        ],
                    ],
                ];

                $sheet->getStyle('A1:A3')->applyFromArray($styleArray);
                $sheet->getStyle('B1:D1')->applyFromArray($styleArray);
                $sheet->getStyle('B2:D2')->applyFromArray($styleArray);
                $sheet->getStyle('B3:D3')->applyFromArray($styleArray);

                $event->sheet
                    ->getDelegate()
                    ->getStyle('A4:D4')
                    ->applyFromArray([
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'eeeeee'],
                        ],
                    ]);
                $event->sheet
                    ->getDelegate()
                    ->getStyle('B2:D2')
                    ->applyFromArray([
                        'font' => [
                            'name' => 'Curlz MT',
                            'bold' => true,
                            'size' => 16,
                            'color' => ['rgb' => 'FF0000'],
                        ],
                    ]);
            },
        ];
    }
}
