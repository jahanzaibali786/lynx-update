<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AdvanceSheetExport implements FromArray, WithColumnFormatting, WithEvents
{
    protected $salaryHeads;
    protected $datas;
    protected $requestdata;
    protected $groupRows = [];

    public function __construct($salaryHeads, $datas, $requestdata)
    {
        $this->salaryHeads = $salaryHeads;
        $this->datas = $datas;
        $this->requestdata = $requestdata;
    }

    public function array(): array
    {
        $rows = [];
        $header = ['Sr#', 'Branch Sr', 'Emp No', 'Name', 'Designation', 'DOJ', 'Adv', 'EOBI', 'I.Tax', 'Loan'];
        $rows[] = $header;

        $grandTotals = array_fill(0, count($header), 0);
        $globalSr = 1;
        $grouped = $this->datas
            ->sortBy(function ($data) {
                return strtolower(
                    (optional(optional($data->employee)->user)->name ?? '') . '|' .
                    (optional($data->employee)->name ?? '')
                );
            })
            ->groupBy(function ($data) {
                return optional(optional($data->employee)->user)->name ?: 'No Branch';
            });

        foreach ($grouped as $branchName => $branchItems) {
            $this->groupRows[] = count($rows) + 8;
            $rows[] = [$branchName];
            $branchTotals = array_fill(0, count($header), 0);
            $branchSr = 1;

            foreach ($branchItems->sortBy(fn ($data) => strtolower(optional($data->employee)->name ?? '')) as $data) {
                $row = [
                    $globalSr++,
                    $branchSr++,
                    $data->employee->employee_id ?? '',
                    $data->employee->name ?? '',
                    $data->employee->designation->name ?? '',
                    !empty($data->employee->company_doj) ? Date::PHPToExcel(new \DateTime($data->employee->company_doj)) : '',
                    $data->sal_advance ?? 0,
                    $data->eobi ?? 0,
                    $data->it ?? 0,
                    $data->loan ?? 0,
                ];

                $rows[] = $row;

                foreach ($row as $i => $val) {
                    if (is_numeric($val)) {
                        $branchTotals[$i] += $val;
                        $grandTotals[$i] += $val;
                    }
                }
            }

            $branchTotals[0] = 'BRANCH TOTAL (Count: ' . $branchItems->count() . ')';
            $rows[] = $branchTotals;
        }

        $grandTotals[0] = 'GRAND TOTAL (Count: ' . $this->datas->count() . ')';
        $rows[] = $grandTotals;

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'F' => 'dd-mmm-yyyy',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->insertNewRowBefore(1, 7);
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
                $sheet->setShowGridlines(false);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.8);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $sheet->setCellValue('A1', 'The Lynx School');
                if (!empty($this->requestdata['branches']) && $this->requestdata['branches'] !== 'all') {
                    $branchName = \App\Models\User::find($this->requestdata['branches'])->name ?? '';
                    $sheet->setCellValue('A3', $branchName);
                } else {
                    $sheet->setCellValue('A3', 'All Branches');
                }
                $sheet->setCellValue('A5', 'Advance Sheet Report for ' . date('F Y', strtotime($this->requestdata['date'])));
                $sheet->setCellValue('A7', 'EMPLOYEES DETAIL');
                $sheet->setCellValue('G7', 'ADVANCE / DEDUCTIONS');
                $sheet->mergeCells('A7:F7');
                $sheet->mergeCells('G7:J7');
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->mergeCells("A5:{$highestColumn}5");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 28, 'bold' => true, 'name' => 'Edwardian Script ITC'],
                ]);
                $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFBFBFBF']],
                ]);

                for ($r = 1; $r <= $lastRow; $r++) {
                    $cellValue = strtoupper((string) $sheet->getCell("A{$r}")->getValue());
                    if (
                        strpos($cellValue, 'TOTAL') !== false
                        || in_array($r, $this->groupRows, true)
                    ) {
                        $sheet->mergeCells("A{$r}:F{$r}");
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => strpos($cellValue, 'TOTAL') !== false ? 'FFBFBFBF' : 'FFD9D9D9'],
                            ],
                        ]);
                    }
                }

                $sheet->getStyle("A7:{$highestColumn}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getFont()->setSize(8);
                $sheet->getStyle("A9:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("G9:{$highestColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G9:{$highestColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

                $fixedWidths = [
                    'A' => 5,
                    'B' => 8,
                    'C' => 8,
                    'D' => 26,
                    'E' => 20,
                    'F' => 10,
                    'G' => 12,
                    'H' => 12,
                    'I' => 12,
                    'J' => 12,
                ];
                foreach ($fixedWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                $sigRow = $lastRow + 2;

                $sheet->setCellValue("B{$sigRow}", '________________________');
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $rightStartIndex = max(2, $highestColumnIndex - 1);
                $rightStartColumn = Coordinate::stringFromColumnIndex($rightStartIndex);

                $sheet->mergeCells("{$rightStartColumn}{$sigRow}:{$highestColumn}{$sigRow}")
                    ->setCellValue("{$rightStartColumn}{$sigRow}", '________________________');

                $sheet->getStyle("B{$sigRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("{$rightStartColumn}{$sigRow}:{$highestColumn}{$sigRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $originalPath = public_path('assets/images/lynx2.jpg');
                if (file_exists($originalPath)) {
                    if (function_exists('imagecreatefromjpeg')) {
                        $img = imagecreatefromjpeg($originalPath);
                        imagefilter($img, IMG_FILTER_GRAYSCALE);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'advance_logo_gray.png';
                        imagepng($img, $tmpPath);
                        imagedestroy($img);
                    } else {
                        $tmpPath = $originalPath;
                    }

                    $drawing = new Drawing();
                    $drawing->setPath($tmpPath);
                    $drawing->setHeight(70);
                    $drawing->setCoordinates("{$rightStartColumn}1");
                    $drawing->setWorksheet($sheet);
                }
            },
        ];
    }
}
