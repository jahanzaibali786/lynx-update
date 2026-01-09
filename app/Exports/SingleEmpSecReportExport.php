<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
class SingleEmpSecReportExport implements FromView, WithEvents
{
    protected $employees;
    protected $salaries;
    protected $salaryData;
    protected $employee;
    protected $yearlyTotals;
    protected $openingBalance;
    protected $closingBalance;
    protected $branches;
    protected $empFilter;
    protected $fromYear;
    protected $toYear;
    public function __construct($employees, $salaries, $salaryData, $employee, $yearlyTotals, $openingBalance, $closingBalance, $branches, $empFilter, $fromYear, $toYear)
    {
        $this->employees = $employees;
        $this->salaries = $salaries;
        $this->salaryData = $salaryData;
        $this->employee = $employee;
        $this->yearlyTotals = $yearlyTotals;
        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
        $this->branches = $branches;
        $this->empFilter = $empFilter;
        $this->fromYear = $fromYear;
        $this->toYear = $toYear;
    }
    public function view(): View
    {
        // Pass only the table-related data to the export view
        return view('employee.exports.singleEmpSecReport', [
            'employees' => $this->employees,
            'salaries' => $this->salaries,
            'salaryData' => $this->salaryData,
            'employee' => $this->employee,
            'yearlyTotals' => $this->yearlyTotals,
            'openingBalance' => $this->openingBalance,
            'closingBalance' => $this->closingBalance,
            'branches' => $this->branches,
            'empFilter' => $this->empFilter,
            'fromYear' => $this->fromYear,
            'toYear' => $this->toYear,
        ]);
    }
    //events
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Page setup: Fit to one page, Landscape, A4
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0); // unlimited height
    
                // 🔁 Repeat heading row (row 8)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
                $sheet->setShowGridlines(false);
                // Optional: Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Logo insertion
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $originalPath = public_path('assets/images/lynx2.jpg');

                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img = imagecreatefromjpeg($originalPath);
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);
                } else {
                    $tmpPath = $originalPath;
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setWidth(19);
                }

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('School Logo (grayscale)');
                $drawing->setPath($tmpPath);
                $drawing->setHeight(75);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);

                $lastDataRow = $sheet->getHighestRow();
                $sigLineRow = $lastDataRow + 2; // underscores
                $sigTextRow = $lastDataRow + 3; // labels
                $highestIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 8
                $insetIndex = max(1, $highestIndex - 1);                       // at least 1
                $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);
                $sheet->setCellValue("A{$sigLineRow}", '________________________');
                $sheet->setCellValue("A{$sigTextRow}", '');
                $sheet->getStyle("A{$sigLineRow}:A{$sigTextRow}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}:A{$sigTextRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("{$insetColumn}{$sigLineRow}", '________________________');
                $sheet->setCellValue("{$insetColumn}{$sigTextRow}", '');
                $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // for heading row
                $highestColumnLetter = $sheet->getHighestColumn();

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
                    ],
                ]);
                // Apply style to entire row 9 Heading Row
                // Determine the column to stop before the last 3
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumnLetter);
                $lastColumnIndex = $highestColumnIndex ; // exclude last 3 columns
                $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);

                // Apply style to A5 up to calculated last column
                $sheet->getStyle("A13:{$lastColumnLetter}13")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFBFBFBF', // Light gray
                        ],
                    ],
                ]);


                //footer styling for total
                // Find the row with the word "Total"
                for ($r = 1; $r <= $lastDataRow; $r++) {
                    $cellValue = strtoupper($sheet->getCell("A{$r}")->getValue());

                    if (strpos($cellValue, 'TOTAL') !== false || strpos($cellValue, 'GRAND TOTAL') !== false) {

                        // Skip last 3 columns
                        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumnLetter);
                        $lastColumnIndex = $highestColumnIndex; // skip last 3 columns
                        $lastColumnLetterToStyle = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);

                        // Set alignment differently for Grand Total
                        $horizontalAlign = strpos($cellValue, 'GRAND TOTAL') !== false
                            ? \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                            : \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;

                        $sheet->getStyle("A{$r}:{$lastColumnLetterToStyle}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => $horizontalAlign,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFBFBFBF'],
                            ],
                        ]);
                    }
                }


            }
        ];
    }
}
