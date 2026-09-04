<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class LastSalaryRevisionExport implements FromView, WithEvents
{
    protected $data;
    protected $taxableHeads;
    protected $branchName;

    public function __construct(array $data, array $taxableHeads, $branchName = 'All Branches')
    {
        $this->data = $data;
        $this->taxableHeads = $taxableHeads;
        $this->branchName = $branchName;
    }

    public function view(): View
    {
        return view('employee.exports.last_salary_revision_export', [
            'data' => $this->data,
            'taxableHeads' => $this->taxableHeads,
            'branchName' => $this->branchName,
            'is_branch' => true,
            'report_name' => 'Last Salary Revision Report',
            'is_period' => true,
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d'),
        ]);
    }

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
    
                // 🔁 Repeat heading row (row 9)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(9, 9);
                $sheet->setShowGridlines(true);

                // Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                // Logo insertion
                $originalPath = public_path('assets/images/lynx2.jpg');
                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img = @imagecreatefromjpeg($originalPath);
                    if ($img) {
                        imagefilter($img, IMG_FILTER_GRAYSCALE);
                        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                        imagepng($img, $tmpPath);
                        imagedestroy($img);
                    } else {
                        $tmpPath = $originalPath;
                    }
                } else {
                    $tmpPath = $originalPath;
                }

                if (file_exists($tmpPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('School Logo (grayscale)');
                    $drawing->setPath($tmpPath);
                    $drawing->setHeight(75);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(10);
                    $drawing->setCoordinates($highestColumn . '1');
                    $drawing->setWorksheet($sheet);
                }

                $lastDataRow = $sheet->getHighestRow();

                // Style standard headings for school info
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC',
                    ],
                ]);

                // Apply styling to Heading Row (Row 9)
                $sheet->getStyle("A9:{$highestColumn}9")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                        'color' => ['argb' => 'FFFFFFFF'], // White text
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black border
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FF1F385C', // Dark blue background matching the image
                        ],
                    ],
                ]);

                // Total Row Index (last row containing data)
                $totalRowIndex = null;
                for ($r = 10; $r <= $lastDataRow; $r++) {
                    $cellVal = $sheet->getCell("A{$r}")->getValue();
                    if ($cellVal && stripos((string)$cellVal, 'total') !== false) {
                        $totalRowIndex = $r;
                        break;
                    }
                }
                if (!$totalRowIndex) {
                    $totalRowIndex = $lastDataRow;
                }

                // Border and alignment for body cells (from Row 10 down to Total row)
                $sheet->getStyle("A10:{$highestColumn}{$totalRowIndex}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => 'FFD9D9D9', // Light gray border for gridlines
                            ],
                        ],
                    ],
                ]);

                // Font size for body
                $sheet->getStyle("A10:{$highestColumn}{$totalRowIndex}")->getFont()->setSize(8)->setName('calibri');

                // Alignments
                $sheet->getStyle("A10:A{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B10:B{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("C10:D{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E10:F{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("G10:G{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Numeric columns start from H (first salary head) to highest column
                $sheet->getStyle("H10:{$highestColumn}{$totalRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H10:{$highestColumn}{$totalRowIndex}")->getNumberFormat()->setFormatCode('#,##0');

                // Apply thick borders and background to the Total Row
                $sheet->getStyle("A{$totalRowIndex}:{$highestColumn}{$totalRowIndex}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFBFBFBF', // Gray background for totals row
                        ],
                    ],
                ]);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(6);   // S.No
                $sheet->getColumnDimension('B')->setWidth(20);  // Branch Name
                $sheet->getColumnDimension('C')->setWidth(12);  // Date
                $sheet->getColumnDimension('D')->setWidth(12);  // Emp No
                $sheet->getColumnDimension('E')->setWidth(22);  // Emp Name
                $sheet->getColumnDimension('F')->setWidth(18);  // Department
                $sheet->getColumnDimension('G')->setWidth(12);  // Payscale No

                // Auto size dynamic columns (H to highest)
                for ($col = 8; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Signature lines
                $sigLineRow = $totalRowIndex + 2;
                $sigTextRow = $totalRowIndex + 3;
                $insetIndex = max(1, $highestColumnIndex - 1);
                $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);

                $sheet->setCellValue("B{$sigLineRow}", '________________________');
                $sheet->setCellValue("B{$sigTextRow}", '');
                $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->setCellValue("{$insetColumn}{$sigLineRow}", '________________________');
                $sheet->setCellValue("{$insetColumn}{$sigTextRow}", '');
                $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        ];
    }
}
