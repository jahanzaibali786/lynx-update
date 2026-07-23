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

class StudentFeeDetailExport implements FromView, WithEvents
{
    protected $branches;
    protected $classes;
    protected $groupedResults;
    protected $selectedBranchId;
    protected $selectedSession;
    protected $selectedClassId;
    protected $feeHeads;
    protected $filteredHeadPcts;
    protected $report_name;
    protected $totalStudents;
    protected $params;

    public function __construct($branches, $classes, $groupedResults, $selectedBranchId, $selectedSession, $selectedClassId, $feeHeads, $filteredHeadPcts, $report_name, $totalStudents, $params)
    {
        $this->branches = $branches;
        $this->classes = $classes;
        $this->groupedResults = $groupedResults;
        $this->selectedBranchId = $selectedBranchId;
        $this->selectedSession = $selectedSession;
        $this->selectedClassId = $selectedClassId;
        $this->feeHeads = $feeHeads;
        $this->filteredHeadPcts = $filteredHeadPcts;
        $this->report_name = $report_name;
        $this->totalStudents = $totalStudents;
        $this->params = $params;
    }

    public function view(): View
    {
        return view('studentReports.exports.student_fee_detail_excel', [
            'branches' => $this->branches,
            'classes' => $this->classes,
            'groupedResults' => $this->groupedResults,
            'selectedBranchId' => $this->selectedBranchId,
            'selectedSession' => $this->selectedSession,
            'selectedClassId' => $this->selectedClassId,
            'feeHeads' => $this->feeHeads,
            'filteredHeadPcts' => $this->filteredHeadPcts,
            'report_name' => $this->report_name,
            'totalStudents' => $this->totalStudents,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->setShowGridlines(false);
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

                // Auto-size all columns
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                foreach (range(1, Coordinate::columnIndexFromString($highestColumn)) as $col) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }

                // Find header row (contains "Sr#" in column A)
                $headerRow = null;
                $highestColLetter = $sheet->getHighestColumn();
                for ($row = 1; $row <= 10; $row++) {
                    $cellVal = $sheet->getCell("A{$row}")->getValue();
                    if ($cellVal !== null && trim((string) $cellVal) === 'Sr#') {
                        $headerRow = $row;
                        break;
                    }
                }

                if ($headerRow) {
                    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
                    $sheet->getStyle("A{$headerRow}:{$highestColLetter}{$headerRow}")->applyFromArray([
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
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFBFBFBF'],
                        ],
                    ]);
                }
            },
        ];
    }
}
