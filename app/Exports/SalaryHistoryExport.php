<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SalaryHistoryExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected $payscaleDetails;
    protected $requestData;
    protected $branchRows = [];

    public function __construct($payscaleDetails, array $requestData = [])
    {
        $this->payscaleDetails = $payscaleDetails;
        $this->requestData = $requestData;
    }

    public function array(): array
    {
        $rows = [[
            'Sr#',
            'Branch Sr#',
            'Emp No',
            'Employee Name',
            'Father Name',
            'Department',
            'Designation',
            'Scale',
            'Effect From',
            'Gross',
            'Net',
        ]];

        $globalSr = 1;
        $groupedItems = $this->payscaleDetails
            ->sortBy([
                fn($row) => optional(optional($row->employee)->userbranch)->name,
                fn($row) => optional($row->employee)->name,
            ])
            ->groupBy(fn($row) => optional(optional($row->employee)->userbranch)->name ?: 'Branch');

        foreach ($groupedItems as $branchName => $items) {
            $branchSr = 1;
            $this->branchRows[] = count($rows) + 1 + 7;
            $rows[] = [$branchName];

            foreach ($items->sortBy(fn($row) => optional($row->employee)->name) as $detail) {
                $gross = (float) ($detail->net ?? 0) + (float) ($detail->emp_sec ?? 0);

                $rows[] = [
                    $globalSr++,
                    $branchSr++,
                    optional($detail->employee)->employee_id ?? '',
                    optional($detail->employee)->name ?? '',
                    optional($detail->employee)->f_name ?? '',
                    optional(optional($detail->employee)->department)->name ?? '',
                    optional(optional($detail->employee)->designation)->name ?? '',
                    optional($detail->scale)->scale_no ?? '',
                    !empty($detail->effect_from) ? Carbon::parse($detail->effect_from)->format('d-M-Y') : '',
                    $gross,
                    (float) ($detail->net ?? 0),
                ];
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 7);

                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $lastRow = $sheet->getHighestRow();

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A9');

                $sheet->setCellValue('A1', 'The Lynx School');
                $sheet->setCellValue('A3', $this->branchTitle());
                $sheet->setCellValue('A5', 'Employee Salary History Report');
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->mergeCells("A5:{$highestColumn}5");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 28, 'bold' => true, 'name' => 'Edwardian Script ITC'],
                ]);
                $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("A8:{$highestColumn}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBFBFBF'],
                    ],
                ]);

                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD9D9D9'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getFont()->setSize(8);
                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("J9:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("J9:K{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

                foreach ($this->branchRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:{$highestColumn}{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'name' => 'Calibri'],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFD9D9D9'],
                        ],
                    ]);
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $logoPath = public_path('assets/images/lynx2.jpg');
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(62);
                    $drawing->setCoordinates(Coordinate::stringFromColumnIndex(max(1, $highestColumnIndex - 1)) . '1');
                    $drawing->setOffsetX(18);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }

                $sigRow = $lastRow + 2;
                $leftEndColumn = Coordinate::stringFromColumnIndex(min($highestColumnIndex, 3));
                $rightColumnIndex = max(1, $highestColumnIndex - 1);
                $rightColumn = Coordinate::stringFromColumnIndex($rightColumnIndex);
                $rightMergeStartColumn = Coordinate::stringFromColumnIndex(max(1, $rightColumnIndex - 1));

                $sheet->mergeCells("B{$sigRow}:{$leftEndColumn}{$sigRow}");
                $sheet->setCellValue("B{$sigRow}", '________________________');
                $sheet->mergeCells("{$rightMergeStartColumn}{$sigRow}:{$rightColumn}{$sigRow}");
                $sheet->setCellValue("{$rightMergeStartColumn}{$sigRow}", '________________________');
                $sheet->getStyle("B{$sigRow}:{$leftEndColumn}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("{$rightMergeStartColumn}{$sigRow}:{$rightColumn}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    private function branchTitle(): string
    {
        $branchId = $this->requestData['branches'] ?? null;
        if (!empty($branchId) && $branchId !== 'all') {
            return \App\Models\User::find($branchId)->name ?? 'Selected Branch';
        }

        return 'All Branches';
    }
}
