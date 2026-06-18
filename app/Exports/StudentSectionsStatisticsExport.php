<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


class StudentSectionsStatisticsExport implements FromView, WithEvents
{
    protected $report, $sections, $branches, $grandTotals, $params;

    public function __construct($report, $sections, $branches, $grandTotals, $params = [])
    {
        $this->branches = $branches;
        $this->sections = $sections;
        $this->report = $report;
        $this->grandTotals = $grandTotals;
        $this->params = $params;
        
    }

    public function view(): View
    {
        $report_name = 'Student Statistics';
        $is_signature = false;
        $is_period = false;
        $is_branch = true;

        // Get branch name
        if (isset($this->params['branches']) && $this->params['branches'] != 'all') {
            
            $branchName = $this->branches->get($this->params['branches']);
        } else {
            $branchName = 'ALL BRANCHES';
        }
        return view('studentReports.exports.student_sections_statistics', [
            'report' => $this->report,
            'sections' => $this->sections,
            'branches' => $this->branches,
            'grandTotals' => $this->grandTotals,
            'report_name' => $report_name,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
            'is_branch' => $is_branch,
            'branchName' => $branchName,
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
                $sheet->getPageSetup()->setFitToHeight(0);

                // Repeat heading rows (rows 7 and 8)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
                $sheet->setShowGridlines(false);

                // Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                // Logo insertion
                $highestColumn = $sheet->getHighestColumn();
                $colIndex = Coordinate::columnIndexFromString($highestColumn);
                $colIndex--;
                $highestColumn = Coordinate::stringFromColumnIndex($colIndex);
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

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('School Logo (grayscale)');
                $drawing->setPath($tmpPath);
                $drawing->setHeight(75);
                $drawing->setOffsetX(8);
                $drawing->setOffsetY(8);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);

                $lastDataRow = $sheet->getHighestRow();
                $highestColumnLetter = $sheet->getHighestColumn();

                // ===== BRANCH NAME & TOTAL STYLING =====
                for ($row = 11; $row <= $lastDataRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    $cellB     = $sheet->getCell('B' . $row)->getValue();

                    // Detect branch name rows: A has value, B is empty
                    if (!empty($cellValue) && empty($cellB) && stripos($cellValue, 'Total') === false) {
                        $sheet->mergeCells("A{$row}:{$highestColumnLetter}{$row}");

                        $sheet->getStyle("A{$row}:{$highestColumnLetter}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                        ]);
                    }

                    // Detect Total / Grand Total rows
                    if (stripos($cellValue, 'Total') !== false) {
                        $sheet->mergeCells("A{$row}:C{$row}");

                        $sheet->getStyle("A{$row}:{$highestColumnLetter}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            ],
                            'fill' => [
                                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                        ]);
                    }
                }
                // ===== END BRANCH NAME & TOTAL STYLING =====

                $sigLineRow = $lastDataRow + 2;
                $sigTextRow = $lastDataRow + 3;
                $highestIndex = Coordinate::columnIndexFromString($highestColumn);
                $insetIndex   = max(1, $highestIndex - 1);
                $insetColumn  = Coordinate::stringFromColumnIndex($insetIndex);
                $pageCountRow = $lastDataRow + 4;
                $generatedDate = date('d-M-Y');

                $highestColumnLetter = $sheet->getHighestColumn();
                $mergedRange = "A{$sigLineRow}:{$highestColumnLetter}{$sigLineRow}";
                $sheet->mergeCells($mergedRange);

                $signatureLine = new RichText();
                $signatureLine->createText('________________________');
                $colCount = Coordinate::columnIndexFromString($highestColumnLetter);
                $space = str_repeat(' ', $colCount * 3);
                $signatureLine->createText($space);
                $signatureLine->createText('________________________');

                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

                // Title row style
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC',
                    ],
                ]);

                // ===== HEADER ROWS 7 & 8 — unified style + rowspan merges =====
                $highestColumnLetter = $sheet->getHighestColumn();

                // Apply same style to BOTH header rows
                $sheet->getStyle("A7:{$highestColumnLetter}8")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBFBFBF'],
                    ],
                ]);

                // Merge rowspan=2 columns vertically (Sr, B.Sr#, CLASS, TOTAL)
                // These match the blade template's rowspan="2" attributes
                $sheet->mergeCells("A7:A8");  // Sr
                $sheet->mergeCells("B7:B8");  // B.Sr#
                $sheet->mergeCells("C7:C8");  // CLASS
                $sheet->mergeCells("{$highestColumnLetter}7:{$highestColumnLetter}8"); // TOTAL

                // ===== END HEADER ROWS =====

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('C')->setWidth(8);
                $sheet->getColumnDimension('D')->setWidth(8);
                $sheet->getColumnDimension('F')->setWidth(20);

                // Auto-width for section columns (D to second-last column)
                $totalColIndex = Coordinate::columnIndexFromString($highestColumnLetter);
                for ($col = 4; $col < $totalColIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);

                    // Calculate width based on the longest content in that column
                    $maxLength = 0;

                    // Check header row 8 (section name)
                    $headerVal = $sheet->getCell("{$colLetter}8")->getValue();
                    if ($headerVal !== null) {
                        $maxLength = max($maxLength, mb_strlen((string) $headerVal));
                    }

                    // Check data rows too
                    for ($row = 11; $row <= $lastDataRow; $row++) {
                        $cellVal = $sheet->getCell("{$colLetter}{$row}")->getValue();
                        if ($cellVal !== null) {
                            $maxLength = max($maxLength, mb_strlen((string) $cellVal));
                        }
                    }

                    // Set width with a small buffer, minimum 8
                    $sheet->getColumnDimension($colLetter)->setWidth(max(8, $maxLength + 2));
                }

                // Font size for all data rows
                $sheet->getStyle("A8:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);

                // Sr# and B.Sr# — left aligned (columns A and B)
                $sheet->getStyle("A11:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B11:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // CLASS column (C) — left aligned text
                $sheet->getStyle("C11:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Section columns (D onwards, excluding last TOTAL column) — right aligned numbers
                $totalColIndex = Coordinate::columnIndexFromString($highestColumnLetter);
                for ($col = 4; $col <= $totalColIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getStyle("{$colLetter}11:{$colLetter}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // TOTAL column — right aligned + bold
                $sheet->getStyle("{$highestColumnLetter}11:{$highestColumnLetter}{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
