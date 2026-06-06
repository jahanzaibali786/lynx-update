<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SalarySheetExport implements FromArray, WithColumnFormatting, WithEvents
{
    protected $salaryHeads;

    protected $datas;

    protected $requestdata;

    protected $departmentRows = [];

    public function __construct($salaryHeads, $datas, $requestdata)
    {
        $this->salaryHeads = $salaryHeads;
        $this->datas = $datas;
        $this->requestdata = $requestdata;
    }

    public function array(): array
    {
        $rows = [];

        // ==============================
        // HEADER
        // ==============================
        $header = ['Sr#','Dept Sr#','Emp No', 'Scale', 'Name', 'Designation', 'DOJ', 'Basic'];

        foreach ($this->salaryHeads as $h) {
            $header[] = $h->head;
        }

        $header = array_merge($header, [
            'Other Allowance', 'Other', 'Drns & Misc', 'Stop Salary', 'Gross',
            'ES', 'IT', 'Salary Adv.', 'EOBI', 'Loan Sec', 'Stop', 'PESSI', 'Other Deduction', 'Loan',
            'Net', 'PESSI Comp', 'EOBI Comp', 'Total Cost', 'Cost To Comp',
            'OP', 'Lvs', 'Bal','OP', 'Lvs', 'Bal','Days',
        ]);

        $rows[] = $header;

        // ==============================
        // GROUPING
        // ==============================
        $grouped = $this->datas->sortBy('salarydepartment.name')->groupBy('department_id');

        $grandTotals = array_fill(0, count($header), 0);
        $globalSr = 1;
        foreach ($grouped as $deptId => $items) {

            // ==============================
            // DEPARTMENT ROW (TRACK INDEX)
            // ==============================
            $deptSr = 1;
            $deptRowIndex = count($rows) + 1 + 7; // +6 for title rows
            $this->departmentRows[] = $deptRowIndex;

            $rows[] = [
                $items->first()->employee->department->name ?? 'Department',
            ];

            $deptTotals = array_fill(0, count($header), 0);

            foreach ($items as $data) {
                $p = $data->employee->employee_payscale_details->last();
                $e = $data->employee->employee_monthly_salaries_attend->first();

                $row = [];
                $row[] = $globalSr++;
                $row[] = $deptSr++;
                $row[] = $data->employee->employee_id ?? '';
                $row[] = $data->scale_no ?? '';
                $row[] = $data->employee->name ?? '';
                $row[] = $data->employee->designation->name ?? '';
                $row[] = Date::PHPToExcel(new \DateTime($data->employee->company_doj));
                $row[] = $data->basics ?? 0;

                // Salary Heads
                foreach ($this->salaryHeads as $h) {
                    $val = optional($data->salary_heads->firstWhere('head_id', $h->id))->head_value ?? 0;
                    $row[] = $val;
                }

                // Other
                $row[] = $data->other_add ?? 0;
                $row[] = $data->conv ?? 0;
                $row[] = ($data->drns ?? 0) + ($data->misc ?? 0);
                $row[] = $data->stop_sal ?? 0;
                $row[] = $data->gross ?? 0;

                $row[] = $data->emp_sec ?? 0;
                $row[] = $data->it ?? 0;
                $row[] = $data->sal_advance ?? 0;
                $row[] = $data->eobi ?? 0;
                $row[] = $data->emp_sec_loan ?? 0;
                $row[] = $data->stop_sal ?? 0;
                $row[] = $data->pessi ?? 0;
                $row[] = $data->dedu ?? 0;
                $row[] = $data->loan ?? 0;

                $row[] = $data->net_pay ?? 0;

                $row[] = $data->pessi_employer ?? 0;
                $row[] = $data->eobi_employer ?? 0;

                $totalCost = ($data->pessi_employer ?? 0) + ($data->eobi_employer ?? 0);
                $costComp = $totalCost + ($data->gross ?? 0);

                $row[] = $totalCost;
                $row[] = $costComp;

                // Leaves
                $cas = ($e->total_casual ?? 0) - ($e->bal_casual ?? 0);
                $ann = ($e->total_annual ?? 0) - ($e->bal_annual ?? 0);

                $row[] = $e->total_casual ?? 0;
                $row[] = $cas;
                $row[] = $e->bal_casual ?? 0;

                $row[] = $e->total_annual ?? 0;
                $row[] = $ann;
                $row[] = $e->bal_annual ?? 0;

                $row[] = $data->sal_days ?? 0;

                $rows[] = $row;
                // Totals
                foreach ($row as $i => $val) {
                    if (is_numeric($val)) {
                        $deptTotals[$i] += $val;
                        $grandTotals[$i] += $val;
                    }
                }
            }

            // Department Total
            $deptTotals[0] = 'DEPARTMENT TOTAL';
            $rows[] = $deptTotals;
        }

        // Grand Total
        $grandTotals[0] = 'GRAND TOTAL';
        $rows[] = $grandTotals;

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'G' => 'dd-mmm-yyyy',
        ];
    }

    // ✅ Styling (same as your blade)
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow(); 
                // ==============================
                // ✅ PAGE SETUP (PRINT SETTINGS)
                // ==============================
                $sheet->getPageSetup()->setOrientation(
                    PageSetup::ORIENTATION_LANDSCAPE
                );

                $sheet->getPageSetup()->setPaperSize(
                    PageSetup::PAPERSIZE_A4
                );

                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Repeat header row
                $sheet->freezePane('A2'); 
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);


                // Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                // Footer
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Hide grid
                $sheet->setShowGridlines(false);

                // ==============================
                // ✅ TITLE SECTION
                // ==============================
                $sheet->insertNewRowBefore(1, 7);

                $sheet->setCellValue('A1', 'The Lynx School');
                   if ($this->requestdata['branches'] && $this->requestdata['branches'] != 'all') {
                    $branchName = \App\Models\User::find($this->requestdata['branches'])->name ?? '';
                    $sheet->setCellValue('A3', "{$branchName}");
                } else {
                    $sheet->setCellValue('A3', "All Branches");
                }
                $sheet->setCellValue('A5', 'Salary Sheet Report for '.date('F Y', strtotime($this->requestdata['date'])));

                $allowanceStartIndex = 8; // H: Basic + salary heads + allowance columns
                $allowanceEndIndex = $allowanceStartIndex + count($this->salaryHeads) + 5;
                $deductionStartIndex = $allowanceEndIndex + 1;
                $deductionEndIndex = $deductionStartIndex + 8;
                $costStartIndex = $deductionEndIndex + 2;
                $costEndIndex = $costStartIndex + 3;
                $clStartIndex = $costEndIndex + 1;
                $clEndIndex = $clStartIndex + 2;
                $alStartIndex = $clEndIndex + 1;
                $alEndIndex = $alStartIndex + 2;

                $allowanceStartColumn = Coordinate::stringFromColumnIndex($allowanceStartIndex);
                $allowanceEndColumn = Coordinate::stringFromColumnIndex($allowanceEndIndex);
                $deductionStartColumn = Coordinate::stringFromColumnIndex($deductionStartIndex);
                $deductionEndColumn = Coordinate::stringFromColumnIndex($deductionEndIndex);
                $costStartColumn = Coordinate::stringFromColumnIndex($costStartIndex);
                $costEndColumn = Coordinate::stringFromColumnIndex($costEndIndex);
                $clStartColumn = Coordinate::stringFromColumnIndex($clStartIndex);
                $clEndColumn = Coordinate::stringFromColumnIndex($clEndIndex);
                $alStartColumn = Coordinate::stringFromColumnIndex($alStartIndex);
                $alEndColumn = Coordinate::stringFromColumnIndex($alEndIndex);

                $sheet->setCellValue('A7', 'EMPLOYEES DETAIL');
                $sheet->setCellValue("{$allowanceStartColumn}7", 'ALLOWANCES');
                $sheet->setCellValue("{$deductionStartColumn}7", 'DEDUCTION');
                $sheet->setCellValue("{$costStartColumn}7", 'Cost to School');
                $sheet->setCellValue("{$clStartColumn}7", 'CL');
                $sheet->setCellValue("{$alStartColumn}7", 'AL');
                $sheet->mergeCells('A7:G7');   // Employee Detail
                $sheet->mergeCells("{$allowanceStartColumn}7:{$allowanceEndColumn}7");   // Allowances
                $sheet->mergeCells("{$deductionStartColumn}7:{$deductionEndColumn}7");   // Deduction
                $sheet->mergeCells("{$costStartColumn}7:{$costEndColumn}7"); // Cost to School
                $sheet->mergeCells("{$clStartColumn}7:{$clEndColumn}7"); // CL
                $sheet->mergeCells("{$alStartColumn}7:{$alEndColumn}7"); // AL

                $highestColumn = $sheet->getHighestColumn();

                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->mergeCells("A5:{$highestColumn}5");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 28, 'bold' => true, 'name' => 'Edwardian Script ITC']]);

                $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);

                $sheet->getStyle('A1:A5')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ==============================
                // ✅ HEADER STYLE
                // ==============================
                $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBFBFBF'],
                    ],
                ]);

                // ==============================
                // ✅ DATA STYLE
                // ==============================
                $lastRow = $sheet->getHighestRow();
                for ($r = 1; $r <= $lastRow; $r++) {
                    $cellValue = strtoupper($sheet->getCell("A{$r}")->getValue());
                    if ((strpos($cellValue, 'GRAND') !== false && strpos($cellValue, 'TOTAL') !== false) || (strpos($cellValue, 'DEPARTMENT') !== false && strpos($cellValue, 'TOTAL') !== false)) {
                        $sheet->mergeCells("A{$r}:G{$r}");
                        $sheet->setCellValue("E{$r}", '');
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'], // Black
                                ],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFBFBFBF', // Light gray
                                ],
                            ],
                        ]);
                    }
                }
                $sheet->getStyle("A7:{$highestColumn}{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'argb' => 'FFD9D9D9',
                                ],
                            ],
                        ],
                    ]);
                // Font size
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")
                    ->getFont()->setSize(8);

                // Alignment
                $sheet->getStyle("A9:D{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H9:{$highestColumn}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("H9:{$highestColumn}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Right align numeric columns
                // for ($col = 'I'; $col <= $highestColumn; $col++) {
                //     $sheet->getStyle("{$col}8:{$col}{$lastRow}")
                //         ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                // }

                // ==============================
                // ✅ COLUMN WIDTHS
                // ==============================

                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(20);

                $sheet->getStyle("E1:F{$lastRow}")->getAlignment()->setWrapText(true);

                // ==============================
                // ✅ FOOTER SIGNATURE
                // ==============================
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

                // ==============================
                // ✅ LOGO
                // ==============================
                $originalPath = public_path('assets/images/lynx2.jpg');

                // ==============================
                // Convert to grayscale
                // ==============================
                $img = imagecreatefromjpeg($originalPath);
                imagefilter($img, IMG_FILTER_GRAYSCALE);

                $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'logo_gray.png';
                imagepng($img, $tmpPath);
                imagedestroy($img); // IMPORTANT: free memory

                // ==============================
                // Add to Excel
                // ==============================
                $drawing = new Drawing;
                $drawing->setPath($tmpPath);
                $drawing->setHeight(70);
                $drawing->setCoordinates("{$rightStartColumn}1");
                $drawing->setWorksheet($sheet);

                foreach ($this->departmentRows as $rowIndex) {

                    // ✅ Merge A → F
                    $sheet->mergeCells("A{$rowIndex}:H{$rowIndex}");

                    // 🎨 Style department row
                    $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 9,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFD9D9D9', // light gray
                                ],
                            ],
                        ]);
                }
            },
        ];
    }
}
