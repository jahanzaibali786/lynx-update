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
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MonthlyPreChallanreport implements FromView, WithEvents
{
    protected $branches;
    protected $classes;
    protected $students;
    protected $report;
    protected $heads;
    protected $month;
    protected $params;
    protected $dateInput;
    protected $averageTuitionFee;

    public function __construct($branches, $classes, $students, $report, $heads, $month, $dateInput, $params, $averageTuitionFee)
    {
        $this->branches  = $branches;
        $this->classes   = $classes;
        $this->students  = $students;
        $this->report    = $report;
        $this->heads     = $heads;
        $this->month     = $month;
        $this->dateInput = $dateInput;
        $this->params    = $params;
        $this->averageTuitionFee = $averageTuitionFee;
    }

    public function view(): View
    {
        return view('studentReports.exports.monthlyprechallanreport', [
            'branches'     => $this->branches,
            'report'       => $this->report,
            'heads'        => $this->heads,
            'month'        => $this->month,
            'dateInput'    => $this->dateInput,
            'is_signature' => false,
            'is_period'    => false,
            'report_name'  => 'PRE-CHALLAN REPORT - ' . strtoupper(\Carbon\Carbon::parse($this->month)->format('F Y')),
            'params'       => $this->params,
            'averageTuitionFee' => $this->averageTuitionFee,
        ]);
    }

   public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet              = $event->sheet->getDelegate();
            $highestColumn      = $sheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            $lastDataRow        = $sheet->getHighestRow();

            // Correct columns according to Blade:
            // G = Class
            // H = Billing Month
            // J onward = numeric amount columns
            $classColIndex   = Coordinate::columnIndexFromString('G');
            $sectionColIndex = Coordinate::columnIndexFromString('H');
            $dateColIndex    = Coordinate::columnIndexFromString('I');
            $numericStartCol = Coordinate::columnIndexFromString('J');

            // Page setup
            $pageSetup = $sheet->getPageSetup();
            $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $pageSetup->setFitToPage(true);
            $pageSetup->setFitToWidth(1);
            $pageSetup->setFitToHeight(0);
            $pageSetup->setRowsToRepeatAtTopByStartAndEnd(7, 8);

            $sheet->setShowGridlines(false);

            $sheet->getPageMargins()
                ->setTop(0.5)
                ->setBottom(0.5)
                ->setLeft(0.5)
                ->setRight(0.5);

            // Logo
            $originalPath = public_path('assets/images/lynx2.jpg');
            $tmpPath      = $originalPath;

            if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                $img = imagecreatefromjpeg($originalPath);

                if ($img) {
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);
                }
            }

            if (file_exists($tmpPath)) {
                $drawing = new Drawing();
                $drawing->setName('Logo')
                    ->setDescription('School Logo')
                    ->setPath($tmpPath)
                    ->setHeight(75)
                    ->setOffsetX(10)
                    ->setOffsetY(10)
                    ->setCoordinates(Coordinate::stringFromColumnIndex($highestColumnIndex - 1) . '1')
                    ->setWorksheet($sheet);
            }

            // Signature row
            $sigLineRow = $lastDataRow + 2;
            $sheet->mergeCells("A{$sigLineRow}:{$highestColumn}{$sigLineRow}");

            $signatureLine = new RichText();
            $signatureLine->createText('________________________');
            $signatureLine->createText(str_repeat(' ', $highestColumnIndex * 3));
            $signatureLine->createText('________________________');

            $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
            $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$sigLineRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

            // Main heading
            $sheet->getStyle('A1')
                ->getFont()
                ->setBold(true)
                ->setSize(28)
                ->setName('Edwardian Script ITC');

            // Table heading rows
            $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
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
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF000000'],
                    ],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFBFBFBF'],
                ],
            ]);

            // Freeze heading rows
            $sheet->freezePane('A9');

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(5);
            $sheet->getColumnDimension('C')->setWidth(10);
            $sheet->getColumnDimension('D')->setWidth(22);
            $sheet->getColumnDimension('E')->setWidth(14);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(18);
            $sheet->getColumnDimension('H')->setWidth(14);
$sheet->getColumnDimension('H')->setWidth(15); // Section
$sheet->getColumnDimension('I')->setWidth(14); // Billing Month
$sheet->getColumnDimension($highestColumn)->setWidth(16);

            // General data styling
           if ($lastDataRow >= 9) {

    $sheet->getStyle("A9:{$highestColumn}{$lastDataRow}")
        ->getFont()
        ->setSize(8);

    // Center
    $sheet->getStyle("A9:C{$lastDataRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Left align Student/Father/Register/Class/Section
    $sheet->getStyle("D9:H{$lastDataRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
        ->setWrapText(true);

    // Billing Month
    $sheet->getStyle("I9:I{$lastDataRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    /*
    |--------------------------------------------------------------------------
    | Text Columns
    |--------------------------------------------------------------------------
    */

    // Class
    $sheet->getStyle("G9:G{$lastDataRow}")
        ->getNumberFormat()
        ->setFormatCode('@');

    // Section
    $sheet->getStyle("H9:H{$lastDataRow}")
        ->getNumberFormat()
        ->setFormatCode('@');

    /*
    |--------------------------------------------------------------------------
    | Billing Month
    |--------------------------------------------------------------------------
    */

    $sheet->getStyle("I9:I{$lastDataRow}")
        ->getNumberFormat()
        ->setFormatCode('MMM-YY');

    /*
    |--------------------------------------------------------------------------
    | Numeric Columns (J onwards)
    |--------------------------------------------------------------------------
    */

    $numericColStartLetter = Coordinate::stringFromColumnIndex($numericStartCol);

    $numericRange = "{$numericColStartLetter}9:{$highestColumn}{$lastDataRow}";

    $sheet->getStyle($numericRange)
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $sheet->getStyle($numericRange)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    /*
    |--------------------------------------------------------------------------
    | Billing Month Value
    |--------------------------------------------------------------------------
    */

    try {

        $carbonDate = \Carbon\Carbon::createFromFormat(
            'Y-m',
            trim($this->dateInput)
        )
            ->startOfMonth()
            ->setTime(12, 0, 0);

    } catch (\Exception $e) {

        $carbonDate = \Carbon\Carbon::parse($this->dateInput)
            ->startOfMonth()
            ->setTime(12, 0, 0);
    }

    $excelDateSerial = ExcelDate::PHPToExcel($carbonDate->timestamp);

    /*
    |--------------------------------------------------------------------------
    | Populate Billing Month & Keep Text Columns as String
    |--------------------------------------------------------------------------
    */

    for ($row = 9; $row <= $lastDataRow; $row++) {

        $rollNo = $sheet->getCell("C{$row}")->getValue();

        if (!empty($rollNo) && is_numeric($rollNo)) {

            // Billing Month -> Column I
            $sheet->getCell("I{$row}")
                ->setValue($excelDateSerial);

            /*
            |--------------------------------------------------------------------------
            | Force Class & Section to Text
            |--------------------------------------------------------------------------
            */

            $classValue = $sheet->getCell("G{$row}")->getValue();

            $sheet->setCellValueExplicit(
                "G{$row}",
                $classValue,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $sectionValue = $sheet->getCell("H{$row}")->getValue();

            $sheet->setCellValueExplicit(
                "H{$row}",
                $sectionValue,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Convert Amount Columns Only (J onwards)
    |--------------------------------------------------------------------------
    */

    for ($col = $numericStartCol; $col <= $highestColumnIndex; $col++) {

        for ($row = 9; $row <= $lastDataRow; $row++) {

            $value = $sheet
                ->getCellByColumnAndRow($col, $row)
                ->getValue();

            if ($value !== null && $value !== '' && is_numeric($value)) {

                $sheet->getCellByColumnAndRow($col, $row)
                    ->setValue((float) $value);
            }
        }
    }
}

            // Last column right aligned
            $sheet->getStyle("{$highestColumn}9:{$highestColumn}{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        },
    ];
}
}