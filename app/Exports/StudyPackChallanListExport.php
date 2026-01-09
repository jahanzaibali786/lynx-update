<?php

namespace App\Exports;

use App\Models\StudentEnrollments;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Contracts\View\View;
class StudyPackChallanListExport implements FromView, WithEvents
{
    protected $studyPackData;
    protected $report_name;
    protected $is_signature;
    protected $branch;
    protected $is_period;
    protected $params;

    public function __construct($data, $branches, $report_name, $params)
    {
        $this->studyPackData = $data;
        $this->params = $params;
        $this->report_name = $report_name;
        $this->branches = $branches;
    }
    /**
     * Export the employee data to an Excel view.
     */
    public function view(): View
    {
        // $branch = @$this->studyPackData->first()->branches->name ?? '';
        $is_signature = false;
        $is_period = true;
        $report_name = $this->report_name;
        // Pass
        //  only the table-related data to the export view
        return view('student.exports.StudyPackChallanList', [
            'datas' => $this->studyPackData,
            // 'branch' => $branch,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
            'report_name' => $report_name,
            'params' => $this->params,
            'branches' => $this->branches,
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
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(9, 9);
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // Optional: Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $columns = range('A', $sheet->getHighestColumn());
                foreach ($columns as $column) {
                    $sheet->getColumnDimension($column)->setWidth(13); // Set all column widths to 25
                }
                $sheet->getColumnDimension('A')->setWidth(7);
                $sheet->getColumnDimension('B')->setWidth(7);
                $sheet->getColumnDimension('C')->setWidth(9);
                $sheet->getColumnDimension('E')->setWidth(9);
                $sheet->getColumnDimension('F')->setWidth(19);

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

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('School Logo (grayscale)');
                $drawing->setPath($tmpPath);
                $drawing->setHeight(75);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setCoordinates($highestColumn . '1');
                $drawing->setWorksheet($sheet);

                // $lastDataRow = $sheet->getHighestRow();
                // $sigLineRow = $lastDataRow + 2; // underscores
                // $sigTextRow = $lastDataRow + 3; // labels
                // $highestIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 8
                // $insetIndex = max(1, $highestIndex - 1);                       // at least 1
                // $insetColumn = Coordinate::stringFromColumnIndex($insetIndex);
                // $pageCountRow = $lastDataRow + 4;
                // $generatedDate = date('d-M-Y');
                // $sheet->setCellValue("B{$sigLineRow}", '________________________');
                // $sheet->setCellValue("B{$sigTextRow}", '');
                // $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
                //     ->getFont()->setBold(true);
                // $sheet->getStyle("B{$sigLineRow}:B{$sigTextRow}")
                //     ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // $sheet->setCellValue("{$insetColumn}{$sigLineRow}", '________________________');
                // $sheet->setCellValue("{$insetColumn}{$sigTextRow}", '');
                // $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                //     ->getFont()->setBold(true);
                // $sheet->getStyle("{$insetColumn}{$sigLineRow}:{$insetColumn}{$sigTextRow}")
                //     ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
