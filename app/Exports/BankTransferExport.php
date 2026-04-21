<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class BankTransferExport implements FromView, WithColumnFormatting, WithEvents
{
    protected $transfers;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($transfers, $dateFrom = '', $dateTo = '')
    {
        $this->transfers = $transfers;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        return view('bank-transfer.export', [
            'transfers' => $this->transfers,
            'date_from' => $this->dateFrom,
            'date_to'   => $this->dateTo,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'B' => 'dd-mmm-yyyy',
            'E' => '#,##0.00', // Excel number format for amount
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
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
                $lastDataRow = $sheet->getHighestRow();
                // Ensure the column widths are properly adjusted
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(45);
                $sheet->getStyle("A9:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F9:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G9:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
            },
            
        ];
    }
}
