<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MonthlyChallanreport implements FromView, WithEvents
{
    protected $branches;
    protected $report;
    protected $selectedBranchId;
    protected $heads;
    protected $report_name;
    protected $admissionSummary;
    protected $advanceSummary;

    public function __construct(
        $branches,
        $report,
        $selectedBranchId,
        $heads,
        $report_name,
        $params = [],
        $admissionSummary = ['count' => 0, 'total' => 0],
        $advanceSummary = ['count' => 0, 'total' => 0]
    ) {
        $this->branches = $branches;
        $this->report = $report;
        $this->selectedBranchId = $selectedBranchId;
        $this->heads = $heads;
        $this->report_name = $report_name;
        $this->admissionSummary = $admissionSummary;
        $this->advanceSummary = $advanceSummary;
    }

    public function view(): View
    {
        return view('studentReports.exports.monthlychallanreport', [
            'branches' => $this->branches,
            'report' => $this->report,
            'selectedBranchId' => $this->selectedBranchId,
            'heads' => $this->heads,
            'report_name' => $this->report_name,
            'admissionSummary' => $this->admissionSummary,
            'advanceSummary' => $this->advanceSummary,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Page setup: Landscape A4, fit to width
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
                $sheet->setShowGridlines(false);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                // ── Logo ────────────────────────────────────────────────
                $highestColumn = $sheet->getHighestColumn();
                $colIndex = Coordinate::columnIndexFromString($highestColumn) - 1;
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
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);

                // ── Signature lines ──────────────────────────────────────
                $lastDataRow = $sheet->getHighestRow();
                $sigLineRow = $lastDataRow + 2;
                $sigTextRow = $lastDataRow + 3;
                $highestColumnLetter = $sheet->getHighestColumn();
                $highestIndex = Coordinate::columnIndexFromString($highestColumnLetter);

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
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

                // ── Heading row styles ───────────────────────────────────
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 28, 'name' => 'Edwardian Script ITC'],
                ]);

                $sheet->getStyle("A7:{$highestColumnLetter}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
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

                $sheet->getStyle("A8:{$highestColumnLetter}8")->applyFromArray([
                    'font' => ['size' => 8, 'name' => 'calibri'],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('E')->setWidth(20);

                // ── Data cell alignment & formatting ─────────────────────
                $sheet->getStyle("A9:C{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D9:G{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("E9:E{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("I9:I{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("J9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("J9:{$highestColumnLetter}{$lastDataRow}")->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle("{$highestColumnLetter}9:{$highestColumnLetter}{$lastDataRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A9:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
                // ----------------------------------
// ✅ DATE FORMAT (Column H)
// ----------------------------------
                $sheet->getStyle("H9:H{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('yyyy-mm-dd');

                // ----------------------------------
// ✅ MONTH-YEAR FORMAT (Column I)
// ----------------------------------
                $sheet->getStyle("I9:I{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('mmm-yyyy');
            },
        ];
    }
}