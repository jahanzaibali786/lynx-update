<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class PurchaseMemoExport implements FromView, WithEvents
{
    protected $data;
    protected $params;

    public function __construct($data, $params)
    {
        $this->data   = $data;
        $this->params = $params;
    }

    public function view(): View
    {
        return view('purchase.exports.purchase_memo_report', [
            'data'         => $this->data,
            'is_signature' => false,
            'is_period'    => true,
            'report_name'  => __('Purchase Memo Report'),
            'params'       => $this->params,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ---------- Page setup ----------
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(9, 9);

                $sheet->setShowGridlines(false);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                // ---------- LOCK THE WORK AREA ----------
                // We read the header row (row 9) to know the real table width (A..G).
                $headerRow   = 9;
                $lastDataCol = $sheet->getHighestDataColumn($headerRow); // e.g. 'G'
                $lastDataRow = $sheet->getHighestDataRow();              // before we add signature rows
                $lastColIdx  = Coordinate::columnIndexFromString($lastDataCol);

                // ---------- Logo (anchor INSIDE the real last column) ----------
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

                if (is_file($tmpPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('School Logo (grayscale)');
                    $drawing->setPath($tmpPath);
                    $drawing->setHeight(75);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(10);
                    // Place on the last real column, row 1
                    $drawing->setCoordinates($lastDataCol . '1');
                    $drawing->setWorksheet($sheet);
                }

                // ---------- Heading styles (row 9) ----------
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC',
                    ],
                ]);

                $sheet->getStyle("A{$headerRow}:{$lastDataCol}{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
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

                // ---------- Column widths ----------
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(20);

                // ---------- Body styles (confined to A..G) ----------
                $sheet->getStyle("A10:A{$lastDataRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("B10:D{$lastDataRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                      ->setWrapText(true);

                $sheet->getStyle("E10:F{$lastDataRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("E10:F{$lastDataRow}")
                      ->getNumberFormat()->setFormatCode('#,##0');

                // Status column (G)
                $sheet->getStyle("G10:G{$lastDataRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Font size for all data cells
                $sheet->getStyle("A10:{$lastDataCol}{$lastDataRow}")
                      ->getFont()->setSize(8);

                // ---------- Signature line (merged only across A..G) ----------
                $sigLineRow = $lastDataRow + 2;
                $sigTextRow = $lastDataRow + 3;

                $sheet->mergeCells("A{$sigLineRow}:{$lastDataCol}{$sigLineRow}");

                $signatureLine = new RichText();
                $signatureLine->createText('________________________');

                // spaces based on number of columns to visually separate
                $space = str_repeat(' ', $lastColIdx * 3);
                $signatureLine->createText($space);
                $signatureLine->createText('________________________');

                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);
            },
        ];
    }
}
