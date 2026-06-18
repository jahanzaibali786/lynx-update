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
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeReportExport implements FromView,WithColumnFormatting ,WithEvents
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        $report_name = __('Employee Report');
        $branch = $this->employees->first()->branch;
        $branch_name = !empty($branch) ? $branch->name : '';
        $is_signature = false;
        $is_period = false;
        // Pass only the table-related data to the export view
        return view('employees.exports.employees_report_table', [
            'employees' => $this->employees,
            'report_name' => $report_name,
            'branch' => $branch_name,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
        ]);
    }

        public function columnFormats(): array
    {
        return [
            'K' => 'dd-mmm-yyyy',
            'L' => 'dd-mmm-yyyy',
        ];
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
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Logo insertion
                $highestColumn = $sheet->getHighestColumn();
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

                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('School Logo (grayscale)');
                $drawing->setPath($tmpPath);
                $drawing->setHeight(75);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);
                $highestColumnLetter = $sheet->getHighestColumn();

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


                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(17);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(12);
                $sheet->getColumnDimension('L')->setWidth(12);
                $sheet->getColumnDimension('M')->setWidth(12);
                $sheet->getColumnDimension('N')->setWidth(12);
                $sheet->getColumnDimension('O')->setWidth(12);
                $sheet->getColumnDimension('P')->setWidth(15);
                $sheet->getColumnDimension('Q')->setWidth(15);
                $sheet->getColumnDimension('R')->setWidth(30);
                $sheet->getStyle("A7:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C7:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M7:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D7:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("G7:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("R7:R{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("A7:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
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
                $sheet->getStyle("A7:{$highestColumnLetter}7")->applyFromArray([
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
            },
        ];
    }
}
