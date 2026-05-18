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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PreChallanComparisonExport implements FromView, WithEvents
{
    protected $branches;
    protected $report;
    protected $selectedBranchId;
    protected $reportName;

    protected $type;
    public function __construct(
        $branches,
        $report,
        $selectedBranchId,
        $reportName,
        $type = 'excel'
    ) {
        $this->branches = $branches;
        $this->report = $report;
        $this->selectedBranchId = $selectedBranchId;
        $this->reportName = $reportName;
        $this->type = $type;
    }

    public function view(): View
    {
        return view('studentReports.exports.comparison_export', [
            'branches' => $this->branches,
            'report' => $this->report,
            'selectedBranchId' => $this->selectedBranchId,
            'report_name' => $this->reportName,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Page setup ────────────────────────────────────────────────
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

                $highestColumn = $sheet->getHighestColumn();          // e.g. "J"
                $highestColumnIdx = Coordinate::columnIndexFromString($highestColumn);
                $lastDataRow = $sheet->getHighestRow();

                // ── Logo ──────────────────────────────────────────────────────
                $logoColumn = Coordinate::stringFromColumnIndex($highestColumnIdx - 1);

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

                if (file_exists($tmpPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('School Logo');
                    $drawing->setPath($tmpPath);
                    $drawing->setHeight(75);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(10);
                    $drawing->setCoordinates($logoColumn . '1');
                    $drawing->setWorksheet($sheet);
                }

                // ── Header rows styling (rows 1–8 from the @include header) ──
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 28, 'name' => 'Edwardian Script ITC'],
                ]);

                // Header row (row 7 = column labels row based on standard header include)
                $headerRowRange = "A7:{$highestColumn}7";
                $sheet->getStyle($headerRowRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBFBFBF'],
                    ],
                ]);

                // ── Column widths ─────────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(20); // Branch Name
                $sheet->getColumnDimension('B')->setWidth(6);  // Sr #
                $sheet->getColumnDimension('C')->setWidth(10); // Roll #
                $sheet->getColumnDimension('D')->setWidth(22); // Name
                $sheet->getColumnDimension('E')->setWidth(14); // Class
                $sheet->getColumnDimension('F')->setWidth(12); // D/O/ADM
                $sheet->getColumnDimension('G')->setWidth(18); // Regular Challan Net
                $sheet->getColumnDimension('H')->setWidth(18); // Pre-Challan Net
                $sheet->getColumnDimension('I')->setWidth(14); // Difference
                $sheet->getColumnDimension('J')->setWidth(12); // Remarks
    
                // ── Data rows ─────────────────────────────────────────────────
                $dataStart = 8; // first data row after headers
    
                // Center align Sr, Roll, Class, D/O/ADM
                $sheet->getStyle("B{$dataStart}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$dataStart}:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$dataStart}:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$dataStart}:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Right-align numeric columns G, H, I
                $sheet->getStyle("G{$dataStart}:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G{$dataStart}:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');

                // Difference column: colour negative red, zero green via conditional
                // (PhpSpreadsheet doesn't support conditional fill natively via FromView,
                //  so we use a number format that shows negatives in brackets)
                $sheet->getStyle("I{$dataStart}:I{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;[Red]-#,##0;0');
                // Font size for all data
                $sheet->getStyle("A{$dataStart}:{$highestColumn}{$lastDataRow}")->getFont()->setSize(8)->setName('Calibri');

                // ── Signature lines ───────────────────────────────────────────
                $sigLineRow = $lastDataRow + 2;
                $sigTextRow = $lastDataRow + 3;

                $sheet->mergeCells("A{$sigLineRow}:{$highestColumn}{$sigLineRow}");

                $signatureLine = new RichText();
                $signatureLine->createText('________________________');
                $space = str_repeat(' ', $highestColumnIdx * 3);
                $signatureLine->createText($space);
                $signatureLine->createText('________________________');

                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);
                $sheet->getStyle("F{$dataStart}:F{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mmm-yyyy');
            },
        ];
    }
}