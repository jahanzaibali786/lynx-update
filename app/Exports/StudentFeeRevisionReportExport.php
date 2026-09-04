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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class StudentFeeRevisionReportExport implements FromView, WithEvents
{
    protected $report;
    protected $branches;
    protected $reportName;
    protected $params;
    protected $studentDetail;
    protected $heads;
    protected $concessionHeadPercentages;

    public function __construct($report, $branches, $reportName, $params, $studentDetail, $heads, $concessionHeadPercentages = [])
    {
        $this->report = $report;
        $this->branches = $branches;
        $this->reportName = $reportName;
        $this->params = $params;
        $this->studentDetail = $studentDetail;
        $this->heads = $heads;
        $this->concessionHeadPercentages = $concessionHeadPercentages;
    }

    public function view(): View
    {
        return view('studentReports.exports.fee_revision_report', [
            'report' => $this->report,
            'branches' => $this->branches,
            'report_name' => $this->reportName,
            'params' => $this->params,
            'studentDetail' => $this->studentDetail,
            'heads' => $this->heads,
            'concessionHeadPercentages' => $this->concessionHeadPercentages,
            'is_signature' => false,
            'is_period' => false,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $lastDataRow = $sheet->getHighestRow();

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 7);
                $sheet->setShowGridlines(false);
                $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

                $originalPath = public_path('assets/images/lynx2.jpg');
                $tmpPath = $originalPath;
                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img = imagecreatefromjpeg($originalPath);
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);
                }

                if (file_exists($tmpPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo')
                        ->setDescription('School Logo')
                        ->setPath($tmpPath)
                        ->setHeight(75)
                        ->setOffsetX(10)
                        ->setOffsetY(10)
                        ->setCoordinates(Coordinate::stringFromColumnIndex(max(1, $highestColumnIndex - 1)) . '1')
                        ->setWorksheet($sheet);
                }

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(28)->setName('Edwardian Script ITC');

                $sheet->getStyle("A7:{$highestColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
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

                $sheet->getStyle("A8:{$highestColumn}{$lastDataRow}")->getFont()->setSize(8);
                $sheet->getStyle("A8:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("B8:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("B8:H{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                foreach (range('A', 'H') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
