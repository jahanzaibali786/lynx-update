<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Contracts\View\View;


class SessionMonthWiseBranchReportExport implements FromView, WithEvents
{
    protected $branches;
    protected $months;
    protected $year;
    protected $month_f;
    protected $month_t;
    protected $registrationCounts;
    protected $enrollmentCounts;
    protected $withdrawalCounts;
    protected $strengthCounts;
    protected $totalRegistrations;
    protected $totalEnrollments;
    protected $totalWithdrawals;
    protected $totalStrength;

    public function __construct($branches,$months, $year,$month_f,$month_t, $registrationCounts,$strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength)
    {
        $this->branches = $branches;
        $this->months = $months;
        $this->year = $year;
        $this->month_f = $month_f;
        $this->month_t = $month_t;
        $this->registrationCounts = $registrationCounts;
        $this->enrollmentCounts = $enrollmentCounts;
        $this->withdrawalCounts = $withdrawalCounts;
        $this->strengthCounts = $strengthCounts;
        $this->totalRegistrations = $totalRegistrations;
        $this->totalEnrollments = $totalEnrollments;
        $this->totalWithdrawals = $totalWithdrawals;
        $this->totalStrength = $totalStrength;

    }
    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        $report_name = "Session Wise Branch Report from ".$this->month_f.' to '.$this->month_t.' of '.$this->year ;
        // Pass only the table-related data to the export view
        return view('studentReports.exports.monthsessionbranchwise', [
            'branches' => $this->branches,
            'months' => $this->months,
            'year' => $this->year,
            'month_f' => $this->month_f,
            'month_t' => $this->month_t,
            'registrationCounts' => $this->registrationCounts,
            'enrollmentCounts' => $this->enrollmentCounts,
            'withdrawalCounts' => $this->withdrawalCounts,
            'strengthCounts' => $this->strengthCounts,
            'totalRegistrations' => $this->totalRegistrations,
            'totalEnrollments' => $this->totalEnrollments,
            'totalWithdrawals' => $this->totalWithdrawals,
            'totalStrength' => $this->totalStrength,
            'report_name' => $report_name,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Page setup: Fit to one page, Landscape, A4
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
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
            },
        ];
    }
}
