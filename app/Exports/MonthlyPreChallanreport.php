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
                $numericStartCol    = Coordinate::columnIndexFromString('G'); // 7
                $dateColIndex       = $numericStartCol; // Column G = date column

                // ── Page setup ───────────────────────────────────────────
                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $pageSetup->setFitToPage(true);
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
                $pageSetup->setRowsToRepeatAtTopByStartAndEnd(7, 7);

                $sheet->setShowGridlines(false);

                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                // ── Logo ─────────────────────────────────────────────────
                $originalPath = public_path('assets/images/lynx2.jpg');
                $tmpPath      = $originalPath;

                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img     = imagecreatefromjpeg($originalPath);
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);
                }

                $drawing = new Drawing();
                $drawing->setName('Logo')
                    ->setDescription('School Logo (grayscale)')
                    ->setPath($tmpPath)
                    ->setHeight(75)
                    ->setOffsetX(10)
                    ->setOffsetY(10)
                    ->setCoordinates(Coordinate::stringFromColumnIndex($highestColumnIndex - 1) . '1')
                    ->setWorksheet($sheet);

                // ── Signature row ────────────────────────────────────────
                $sigLineRow = $lastDataRow + 2;
                $sheet->mergeCells("A{$sigLineRow}:{$highestColumn}{$sigLineRow}");
                $signatureLine = new RichText();
                $signatureLine->createText('________________________');
                $signatureLine->createText(str_repeat(' ', $highestColumnIndex * 3));
                $signatureLine->createText('________________________');
                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

                // ── Heading styles ───────────────────────────────────────
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(28)->setName('Edwardian Script ITC');

                $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
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

                // ── Column widths ────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension($highestColumn)->setWidth(20);

                // ── Bulk styles for data rows (range-based) ──────────────
                $sheet->getStyle("A9:{$highestColumn}{$lastDataRow}")->getFont()->setSize(8);
                $sheet->getStyle("B9:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

                // ── Column G: date format (range-based) ──────────────────
                $dateColLetter = Coordinate::stringFromColumnIndex($dateColIndex); // 'G'
                $sheet->getStyle("{$dateColLetter}9:{$dateColLetter}{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('MMM-YY');
                $sheet->getStyle("{$dateColLetter}9:{$dateColLetter}{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ── Columns H onward: number format (range-based) ────────
                $numericColStart  = Coordinate::stringFromColumnIndex($numericStartCol + 1); // 'H'
                $numericRange     = "{$numericColStart}9:{$highestColumn}{$lastDataRow}";
                $sheet->getStyle($numericRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($numericRange)->getNumberFormat()->setFormatCode('#,##0');

                // ── FIX: Safely parse Y-m dateInput to correct Excel date serial ──
                // ── FIX: Use noon to avoid UTC timezone boundary issues ──────
                try {
                    $carbonDate = \Carbon\Carbon::createFromFormat('Y-m', trim($this->dateInput))
                        ->startOfMonth()
                        ->setTime(12, 0, 0); // noon — safe from UTC drift
                } catch (\Exception $e) {
                    $carbonDate = \Carbon\Carbon::parse($this->dateInput)->startOfMonth()->setTime(12, 0, 0);
                }

                $excelDateSerial = ExcelDate::PHPToExcel($carbonDate->timestamp);

                for ($col = $numericStartCol; $col <= $highestColumnIndex; $col++) {

                    // ── Column G: write pre-computed date serial to all rows ─
                    if ($col === $dateColIndex) {
                        for ($row = 9; $row <= $lastDataRow; $row++) {
                            $rawValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                            // Only overwrite actual student data rows (skip empty/text branch or total rows)
                            if ($rawValue !== null && $rawValue !== '') {
                                $sheet->getCellByColumnAndRow($col, $row)->setValue($excelDateSerial);
                            }
                        }
                        continue;
                    }

                    // ── Columns H+: cast numeric strings to float ────────
                    $colData = [];
                    for ($row = 9; $row <= $lastDataRow; $row++) {
                        $rawValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                        if (is_numeric($rawValue) && $rawValue !== '') {
                            $colData[$row] = (float) $rawValue;
                        }
                    }
                    foreach ($colData as $row => $value) {
                        $sheet->getCellByColumnAndRow($col, $row)->setValue($value);
                    }
                }

                // ── Last column override: right-aligned ──────────────────
                $sheet->getStyle("{$highestColumn}9:{$highestColumn}{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}