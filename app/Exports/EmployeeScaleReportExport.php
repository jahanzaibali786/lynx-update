<?php

namespace App\Exports;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class EmployeeScaleReportExport implements FromView, WithEvents, WithColumnFormatting
{
    protected $employee_scales;

    protected $heads;

    public function __construct($employee_scales, $heads)
    {
        $this->employee_scales = $employee_scales;
        $this->heads = $heads;
    }

    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        $report_name = __('Employee Scale Report');
        // Pass only the table-related data to the export view
        return view('employees.exports.employee_scales_report_table', [
            'employee_scales' => $this->employee_scales,
            'heads' => $this->heads,
            'report_name' => $report_name,
        ]);
    }

     public function columnFormats(): array
    {
        return [
            'D' => 'dd-mmm-yyyy',
        ];
    }

    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setDescription('School Logo');
    //     $drawing->setPath(public_path('assets/images/lynx2.jpg')); // Path to your image
    //     $drawing->setOffsetX(20);
    //     $drawing->setOffsetY(20);
    //     $drawing->setHeight(75); // Set height in pixels
    //     $drawing->setCoordinates('C1'); // Set position on the Excel sheet (e.g., top left)

    //     return $drawing;
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Page setup: Fit to one page, Landscape, A4
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
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
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Logo insertion
                $highestColumn = $sheet->getHighestColumn();
                $originalPath = public_path('assets/images/lynx2.jpg');

                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img = imagecreatefromjpeg($originalPath);
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);
                } else {
                    $tmpPath = $originalPath;
                }

                $drawing = new Drawing;
                $drawing->setName('Logo');
                $drawing->setDescription('School Logo (grayscale)');
                $drawing->setPath($tmpPath);
                $drawing->setHeight(75);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates('J1');
                $drawing->setWorksheet($sheet);

                $lastDataRow = $sheet->getHighestRow();
                $sigLineRow = $lastDataRow + 2; // underscores
                $sigTextRow = $lastDataRow + 3; // labels
                $highestIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 8
                $insetIndex = max(1, $highestIndex - 1);                       // at least 1
                $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);
                $pageCountRow = $lastDataRow + 4;
                $generatedDate = date('d-M-Y');
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

                $highestColumnLetter = $sheet->getHighestColumn();
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
                    ],
                ]);
                // Apply style to entire row 9 Heading Row
                $sheet->getStyle("A7:{$highestColumnLetter}7")->applyFromArray([
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

                $highestColumnLetter = $sheet->getHighestColumn();

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
                    ],
                ]);
                // Apply style to entire row 9 Heading Row
                $sheet->getStyle("A7:{$highestColumnLetter}7")->applyFromArray([
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
                // footer styling for total
                $totalRowStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'alignment' => [
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
                ];

                for ($r = 1; $r <= $lastDataRow; $r++) {
                    $cellValue = strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));

                    // Use strict comparison to detect TOTAL or BRANCH
                    if (strpos($cellValue, 'TOTAL') !== false || strpos($cellValue, 'BRANCH') !== false) {
                        if ($r == 3) {
                            continue;
                        }
                        // This should now trigger
                        $sheet->getStyle("A{$r}:{$highestColumnLetter}{$r}")->applyFromArray($totalRowStyle);
                    }
                }

                // Adjust column widths
                $highestColumnLetter = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumnLetter);

                // Set widths dynamically
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);

                    if ($columnLetter === 'A') {
                        $sheet->getColumnDimension($columnLetter)->setWidth(8);
                    } elseif ($columnLetter === 'B') {
                        $sheet->getColumnDimension($columnLetter)->setWidth(8);
                    } elseif ($columnLetter === 'C') {
                        $sheet->getColumnDimension($columnLetter)->setWidth(20);
                    } elseif ($columnLetter === 'J') {
                        $sheet->getColumnDimension($columnLetter)->setWidth(8);
                    }else {
                        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                    }
                }

                // style col font size 8px and align center
                $sheet->getStyle("A8:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B8:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C8:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->getWrapText(true);
                // from D to J should be Right adding loop
                for ($i = 'D'; $i <= 'J'; $i++) {
                    $sheet->getStyle("{$i}8:{$i}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle("K8:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("L8:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("M8:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A8:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
            },
        ];
    }
}
