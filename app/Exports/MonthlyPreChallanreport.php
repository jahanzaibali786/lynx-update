<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MonthlyPreChallanreport implements FromView, WithEvents
{
    protected $branches;
    protected $classes;
    protected $students;
    protected $report;
    protected $heads;
    protected $month;
    protected $params;

    public function __construct($branches, $classes, $students, $report, $heads, $month, $dateInput, $params)
    {
        $this->branches = $branches;
        $this->classes = $classes;
        $this->students = $students;
        $this->report = $report;
        $this->heads = $heads;
        $this->month = $month;
        $this->dateInput = $dateInput;
        $this->params = $params;
    }

    /**
     * Export the monthly challan data to an Excel view.
     */
    public function view(): View
    {
        // dd($this->heads);
        $is_signature = false;
        $is_period = false;
        $report_name = "Student Pre-Challan Report for the month of {$this->month}";
        // Pass only the table-related data to the export view
        return view('studentReports.exports.monthlyprechallanreport', [
            'branches' => $this->branches,
            'report' => $this->report,
            'heads' => $this->heads,
            'month' => $this->month,
            'dateInput' => $this->dateInput,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
            'report_name' => $report_name,
            'params' => $this->params,
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

                // 🔁 Repeat heading row (row 5)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 7);
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // Optional: Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                // $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Logo insertion
                $highestColumn = $sheet->getHighestColumn();
                $colIndex = Coordinate::columnIndexFromString($highestColumn); // Convert to number
                $colIndex--; // Move one column to the left
                $highestColumn = Coordinate::stringFromColumnIndex($colIndex); // Convert back to letter
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
                $pageCountRow = $lastDataRow + 4;
                $generatedDate = date('d-M-Y');
                // Merge the entire row (e.g., row 25)
                $highestColumnLetter = $sheet->getHighestColumn();
                $mergedRange = "A{$sigLineRow}:{$highestColumnLetter}{$sigLineRow}";
                $sheet->mergeCells($mergedRange);

                // Build signature line text with left and right alignment
                $signatureLine = new RichText();
                $signatureLine->createText('________________________');

                // Add enough space in between to push second line to right side
                $colCount = Coordinate::columnIndexFromString($highestColumnLetter);
                $space = str_repeat(' ', $colCount * 3); // Adjust spacing depending on column width
                $signatureLine->createText($space);

                $signatureLine->createText('________________________');

                // Set into merged cell
                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

                // for heading row
                $highestColumnLetter = $sheet->getHighestColumn();
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
                    ],
                ]);
                // Apply style to entire Heading Row
                $sheet->getStyle("A7:{$highestColumnLetter}8")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,

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

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension($highestColumnLetter)->setWidth(20);

                // style col font size 8px and align center
                $sheet->getStyle("B9:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("D9:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("G9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G9:{$highestColumnLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$highestColumnLetter}9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A9:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
            },
        ];
    }
}
