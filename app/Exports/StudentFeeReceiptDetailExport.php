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
use PhpOffice\PhpSpreadsheet\Shared\Date; // ✅ ADDED
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // ✅ ADDED

class StudentFeeReceiptDetailExport implements FromView, WithEvents
{
    protected $receipts;
    protected $branches;
    protected $branchId;
    protected $report_name;
    protected $params;
    protected $request;

    public function __construct($receipts, $branches, $branchId, $report_name, $params, $request)
    {
        $this->receipts = $receipts;
        $this->branches = $branches;
        $this->branchId = $branchId;
        $this->report_name = $report_name;
        $this->params = $params;
        $this->request = $request;
    }

    /**
     * Export the student fee receipt data to an Excel view.
     */
    public function view(): View
    {
        $is_signature = false;
        $is_period = true;
        $is_branch = true;
        // dd($this->request->all());
        // Get branch name
        $branchName = $this->branches->get($this->branchId) ?? 'All Branches';

        // Prepare date range if available
        $dateFrom = $this->request['from_date'] ?? null;
        $dateTo = $this->request['to_date'] ?? null;

        return view('student.exports.student_fee_receipt_detail_report', [
            'recipts' => $this->receipts,
            'branch' => $branchName,
            'branchName' => $branchName,
            'branches' => $this->branches,
            'report_name' => $this->report_name,
            'request' => $this->request,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
            'is_branch' => $is_branch,
            'params' => $this->params,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
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
    
                // Repeat heading row (row 9)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(9, 9);
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
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);

                $lastDataRow = $sheet->getHighestRow();
                $highestColumnLetter = $sheet->getHighestColumn();

                // ===== COLUMN MAPPING (15 columns total) =====
                // A=Sr#, B=Br.Sr#, C=Date, D=Ch.Type, E=Roll#, F=Student,
                // G=Class, H=ChallanNo, I=BillingMonth, J=Bank/Cash,
                // K=Mode, L=T.Head, M=Ref, N=Rs., O=Over Receipt
                //
                // Total rows use colspan=13 in blade → label fills A:M (13 cols)
                // → Rs. lands in N (col 14), Over Receipt in O (col 15)
                // So mergeCells must be A:M (13 cols) to match
    
                for ($row = 10; $row <= $lastDataRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    $cellB = $sheet->getCell('B' . $row)->getValue();

                    // Detect branch name rows: A has value, B is empty
                    if (!empty($cellValue) && empty($cellB) && stripos($cellValue, 'Total') === false) {
                        // Branch header row — merge all columns
                        $sheet->mergeCells("A{$row}:{$highestColumnLetter}{$row}");

                        $sheet->getStyle("A{$row}:{$highestColumnLetter}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 10,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                        ]);
                    }

                    // Detect Total / Grand Total rows
                    if (stripos($cellValue, 'Total') !== false) {
                        // FIX: merge A to M (13 columns) so Rs. stays in col N
                        // This must match colspan=13 used in the blade template
                        $sheet->mergeCells("A{$row}:M{$row}");

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
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                        ]);
                    }
                }
                // ===== END BRANCH NAME & TOTAL STYLING =====
    
                $sigLineRow = $lastDataRow + 2;
                $sigTextRow = $lastDataRow + 3;
                $highestIndex = Coordinate::columnIndexFromString($highestColumn);
                $insetIndex = max(1, $highestIndex - 1);
                $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);
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
                // ===== ✅ DATE FIX START =====
                for ($row = 10; $row <= $lastDataRow; $row++) {
                    $cell = $sheet->getCell('C' . $row);
                    $value = $cell->getValue();

                    if (!empty($value) && !is_numeric($value)) {
                        try {
                            $excelDate = Date::stringToExcel($value);
                            $cell->setValue($excelDate);
                        } catch (\Exception $e) {
                            // skip invalid
                        }
                    }
                }
                $sheet->getStyle("C10:C{$lastDataRow}")
                    ->getNumberFormat()
                    ->setFormatCode('dd mmm yyyy'); // ✅ correct
                // Heading row style
                $highestColumnLetter = $sheet->getHighestColumn();
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC',
                    ],
                ]);

                $sheet->getStyle("A9:{$highestColumnLetter}9")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'Calibri',
                    ],
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

                $sheet->getStyle("A10:{$highestColumnLetter}10")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'Calibri',
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(20);

                $sheet->getStyle("F10:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                $sheet->getStyle("A10")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("F11:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("M11:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A10:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
            },
        ];
    }
}