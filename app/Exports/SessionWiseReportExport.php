<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class SessionWiseReportExport implements FromView, WithEvents
{
    protected $branches;
    protected $classes;
    protected $sessions;
    protected $select_session;
    protected $registrationCounts;
    protected $enrollmentCounts;
    protected $withdrawalCounts;
    protected $strengthCounts;
    protected $totalRegistrations;
    protected $totalEnrollments;
    protected $totalWithdrawals;
    protected $totalStrength;
    protected $school_classes;
    protected $report_name;
    protected $request;
    protected $params;

    public function __construct($branches, $classes, $sessions,$select_session, $registrationCounts,$strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength, $school_classes, $report_name, $request, $params)
    {
        $this->branches = $branches;
        $this->classes = $classes;
        $this->sessions = $sessions;
        $this->select_session = $select_session;
        $this->registrationCounts = $registrationCounts;
        $this->enrollmentCounts = $enrollmentCounts;
        $this->withdrawalCounts = $withdrawalCounts;
        $this->strengthCounts = $strengthCounts;
        $this->totalRegistrations = $totalRegistrations;
        $this->totalEnrollments = $totalEnrollments;
        $this->totalWithdrawals = $totalWithdrawals;
        $this->totalStrength = $totalStrength;
        $this->school_classes = $school_classes;
        $this->report_name = $report_name;
        $this->request = $request;
        $this->params = $params;
    }
    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        // Pass only the table-related data to the export view
        return view('studentReports.exports.sessionwise', [
            'branches' => $this->branches,
            'classes' => $this->classes,
            'sessions' => $this->sessions,
            'select_session' => $this->select_session,
            'registrationCounts' => $this->registrationCounts,
            'enrollmentCounts' => $this->enrollmentCounts,
            'withdrawalCounts' => $this->withdrawalCounts,
            'strengthCounts' => $this->strengthCounts,
            'totalRegistrations' => $this->totalRegistrations,
            'totalEnrollments' => $this->totalEnrollments,
            'totalWithdrawals' => $this->totalWithdrawals,
            'totalStrength' => $this->totalStrength,
            'school_classes' => $this->school_classes,
            'report_name' => $this->report_name,
            'request' => $this->request,
            'params' => $this->params,
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
    
                // 🔁 Repeat heading row (row 5)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
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
