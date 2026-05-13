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

class DeductionSheetExport implements FromArray, WithColumnFormatting, WithEvents
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
        $header = ['Sr#','Dep.Sr','Emp No', 'Scale', 'Name', 'Designation', 'DOJ','Gross',
                 'ES', 'I.Tax', 'Adv', 'Eobi', 'PESSI' , 'Loan Sec', 'Loan', 'other','Total Ded', 'NET' ];

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
                $row = [];
                $row[] = $globalSr++;
                $row[] = $deptSr++;
                $row[] = $data->employee->employee_id ?? '';
                $row[] = $data->scale_no ?? '';
                $row[] = $data->employee->name ?? '';
                $row[] = $data->employee->designation->name ?? '';
                $row[] = Date::PHPToExcel(new \DateTime($data->employee->company_doj));
                $row[] = $data->gross ?? 0;

                $row[] = $data->emp_sec ?? 0;
                $row[] = $data->it ?? 0;
                $row[] = $data->advance ?? 0;
                $row[] = $data->eobi ?? 0;
                $row[] = $data->pessi ?? 0;
                $row[] = $data->emp_sec_loan ?? 0;
                $row[] = $data->loan ?? 0;
                $row[] = $data->dedu ?? 0;

                $row[] = $data->emp_sec + $data->it + $data->advance + $data->eobi + $data->pessi + $data->emp_sec_loan + $data->loan + $data->dedu;
                $row[] = $data->net_pay ?? 0;

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
                $sheet->setCellValue('A5', 'Deduction Sheet Report for '.date('F Y', strtotime($this->requestdata['date'])));

                $sheet->setCellValue('A7', 'EMPLOYEES DETAIL');
                $sheet->setCellValue('I7', 'DEDUCTION');
                $sheet->mergeCells('A7:G7');   // Employee Detail
                $sheet->mergeCells('I7:N7');   // Deductions


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
                    // 'borders' => [
                    //     'allBorders' => [
                    //         'borderStyle' => Border::BORDER_THIN,
                    //     ],
                    // ],
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
                        $sheet->setCellValue("F{$r}", '');
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
                            // 'borders' => [
                            //     'allBorders' => [
                            //         'borderStyle' => Border::BORDER_THIN,
                            //         'color' => ['argb' => 'FF000000'], // Black
                            //     ],
                            // ],
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

               $fixedWidths = [
                    'A' => 4,
                    'B' => 6,
                    'C' => 6,
                    'D' => 9,
                    'E' => 26,
                    'F' => 20,
                    'G' => 10,
                ];

                // Apply fixed widths
                foreach ($fixedWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }
                foreach (range('H', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->getStyle("E1:F{$lastRow}")->getAlignment()->setWrapText(true);

                // ==============================
                // ✅ FOOTER SIGNATURE
                // ==============================
                $sigRow = $lastRow + 2;

                $sheet->setCellValue("B{$sigRow}", '________________________');


                $from = Coordinate::stringFromColumnIndex($lastRow - 1);
                $to   = Coordinate::stringFromColumnIndex($lastRow);

                $sheet->mergeCells("{$from}{$sigRow}:{$to}{$sigRow}")
                    ->setCellValue("{$from}{$sigRow}", '________________________');

                $sheet->getStyle("B{$sigRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("{$highestColumn}{$sigRow}")
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
                $drawing->setCoordinates("{$from}1");
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

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $sheet = $event->sheet->getDelegate();

    //             // Page setup: Fit to one page, Landscape, A4
    //             $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    //             $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    //             $sheet->getPageSetup()->setFitToPage(true);
    //             $sheet->getPageSetup()->setFitToWidth(1);
    //             $sheet->getPageSetup()->setFitToHeight(0); // unlimited height

    //             // 🔁 Repeat heading row (row 8)
    //             $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
    //             $sheet->setShowGridlines(false);
    //             // Optional: Margins
    //             $sheet->getPageMargins()->setTop(0.5);
    //             $sheet->getPageMargins()->setBottom(0.5);
    //             $sheet->getPageMargins()->setLeft(0.5);
    //             $sheet->getPageMargins()->setRight(0.5);
    //             $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

    //             // Logo insertion
    //             $highestColumn = $sheet->getHighestColumn();
    //             $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
    //             $originalPath = public_path('assets/images/lynx2.jpg');

    //             if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
    //                 $img = imagecreatefromjpeg($originalPath);
    //                 imagefilter($img, IMG_FILTER_GRAYSCALE);
    //                 $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
    //                 imagepng($img, $tmpPath);
    //                 imagedestroy($img);
    //             } else {
    //                 $tmpPath = $originalPath;
    //             }

    //             for ($col = 1; $col <= $highestColumnIndex; $col++) {
    //                 $columnLetter = Coordinate::stringFromColumnIndex($col);
    //                 $sheet->getColumnDimension($columnLetter)->setWidth(19);
    //             }

    //             $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    //             $drawing->setName('Logo');
    //             $drawing->setDescription('School Logo (grayscale)');
    //             $drawing->setPath($tmpPath);
    //             $drawing->setHeight(75);
    //             $drawing->setOffsetX(10);
    //             $drawing->setOffsetY(10);
    //             $drawing->setCoordinates($highestColumn . '1');
    //             $drawing->setWorksheet($sheet);

    //             $lastDataRow = $sheet->getHighestRow();
    //             $sigLineRow = $lastDataRow + 2; // underscores
    //             $sigTextRow = $lastDataRow + 3; // labels
    //             $highestIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 8
    //             $insetIndex = max(1, $highestIndex - 1);                       // at least 1
    //             $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);
    //             $sheet->setCellValue("B{$sigLineRow}", '________________________');
    //             $sheet->setCellValue("B{$sigTextRow}", '');
    //             $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
    //                 ->getFont()->setBold(true);
    //             $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
    //                 ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    //             $sheet->setCellValue("{$insetColumn}{$sigLineRow}", '________________________');
    //             $sheet->setCellValue("{$insetColumn}{$sigTextRow}", '');
    //             $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
    //                 ->getFont()->setBold(true);
    //             $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
    //                 ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    //             // for heading row
    //             $highestColumnLetter = $sheet->getHighestColumn();

    //             $sheet->getStyle('A1')->applyFromArray([
    //                 'font' => [
    //                     'bold' => true,
    //                     'size' => 28,
    //                     'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
    //                 ],
    //             ]);
    //             // Apply style to entire row 9 Heading Row
    //             $sheet->getStyle("A7:{$highestColumnLetter}8")->applyFromArray([
    //                 'font' => [
    //                     'bold' => true,
    //                     'size' => 8,
    //                     'name' => 'calibri',
    //                 ],
    //                 'alignment' => [
    //                     'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    //                     'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    //                     'wrapText' => true,
    //                 ],
    //                 'borders' => [
    //                     'allBorders' => [
    //                         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                         'color' => ['argb' => 'FF000000'], // Black
    //                     ],
    //                 ],
    //                 'fill' => [
    //                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //                     'startColor' => [
    //                         'argb' => 'FFBFBFBF', // Light gray
    //                     ],
    //                 ],
    //             ]);
    //             //footer styling for total
    //             $totalRowStyle = [
    //                 'font' => [
    //                     'bold' => true,
    //                     'size' => 8,
    //                     'name' => 'calibri',
    //                 ],
    //                 'alignment' => [
    //                     'wrapText' => true,
    //                 ],
    //                 'borders' => [
    //                     'allBorders' => [
    //                         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                         'color' => ['argb' => 'FF000000'], // Black
    //                     ],
    //                 ],
    //                 'fill' => [
    //                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //                     'startColor' => [
    //                         'argb' => 'FFBFBFBF', // Light gray
    //                     ],
    //                 ],
    //             ];

    //             // Loop through all rows to find "total" rows
    //             for ($r = 1; $r <= $lastDataRow; $r++) {
    //                 $cellValue = strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));

    //                 // Match rows containing "TOTAL" OR "BRANCH" in column A
    //                 if (strpos($cellValue, 'TOTAL') != false || strpos($cellValue, 'BRANCH') != false) {
    //                     if ($r == 3) {
    //                         continue;
    //                     }
    //                     $sheet->getStyle("A{$r}:{$highestColumnLetter}{$r}")->applyFromArray($totalRowStyle);
    //                 }
    //             }

    //             $sheet->getColumnDimension('A')->setWidth(7);

    //             // style col font size 8px and align center
    //             $sheet->getStyle("A9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    //             $sheet->getStyle("A9:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    //             // $sheet->getStyle("E9:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    //             $sheet->getStyle("D9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //             $sheet->getStyle("D9:{$highestColumnLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
    //             $sheet->getStyle("A9:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
    //         },
    //     ];
    // }

}
