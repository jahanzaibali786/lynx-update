<?php
namespace App\Exports;

use App\Models\SalaryHeads;
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, WithChunkReading, WithEvents, WithColumnFormatting
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeeSalaryDetailReportExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithEvents,WithColumnFormatting
{
    protected $query;
    protected $heads;

    public function __construct($query, $heads)
    {
        $this->query = $query;

        // 🔥 optimized (id => name)
        $this->heads = $heads->pluck('head', 'id');
    }

    // ✅ Query with relations
    public function query()
    {
        return $this->query->with([
            'latestPayscale.scale.employeeScaleHeads',
            'department',
            'designation',
            'branch'
        ]);
    }

    // ✅ Headings
    public function headings(): array
    {
        return array_merge([
            'Sr No',
            'Branch',
            'Emp No',
            'Employee Name',
            'CNIC',
            'DOJ',
            'Department',
            'Designation',
            'Bank A/C',
            'Scale No',
            'Working Days',
        ],
        $this->heads->values()->toArray(),
        [
            'Other Allowance','Other','Drns & Misc','Gross Salary','Emp Sec.',
            'Advances','EOBI','PESSI','Loan Emp Sec.',
            'Income Tax','Other Ded','Other Loan','Net Sal'
        ]);
    }

      public function columnFormats(): array
    {
        return [
            'F' => 'dd-mmm-yyyy',
        ];
    }

    public function map($emp): array
{
    static $sr = 1;

    $last = $emp->latestPayscale;

    // ❌ If no salary record, return blank row
    if (!$last) {
        return array_fill(0, count($this->headings()), '-');
    }

    // ==============================
    // ✅ SCALE HEADS (SAFE ACCESS)
    // ==============================
    $scaleHeads = collect(optional($last->scale)->employeeScaleHeads)
        ->keyBy('head');

    $gross = 0;
    $headValues = [];

    foreach ($this->heads as $id => $name) {

        // SAFE: avoid undefined index crash
        // $val = optional($scaleHeads[$id])->head_value ?? 0;
            $val = data_get($scaleHeads, "$id.head_value", 0);

        $headValues[] = $val;
        $gross += $val;
    }

    // Add allowances to gross
    $gross += ($last->drns ?? 0)
            + ($last->conv ?? 0)
            + ($last->misc ?? 0)
            + ($last->other_add ?? 0);

    // ==============================
    // ✅ EOBI (CACHED FOR SPEED)
    // ==============================
    static $eobiCache = [];

    if (!isset($eobiCache[$emp->id])) {
        $eobiCache[$emp->id] = $emp->eobi($emp->id, $emp->owned_by);
    }


    $eobi = $eobiCache[$emp->id];

    $employeeEobi = $eobi['employee_eobi'] ?? 0;
    $employerEobi = $eobi['employer_eobi'] ?? 0;
    // ==============================
    // ✅ RETURN ROW
    // ==============================

    return array_merge([

        // Basic Info
        $sr++,
        $emp->userbranch->name ?? '-',
        $emp->employee_id,
        $emp->name,
        $emp->cnic,
        \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(new \DateTime($emp->company_doj)),
        $emp->department->name ?? '-',
        $emp->designation->name ?? '-',

        // Salary base info
        $last->account_number ?? '-',
        optional($last->scale)->scale_no ?? '-',
        $last->working_days ?? '-',

    ],
    $headValues,
    [

        // Allowances
        $last->others ?? 0,
        $last->conv ?? 0,
        ($last->drns ?? 0) + ($last->misc ?? 0),

        // Gross Salary
        $gross,

        // Deductions
        $last->emp_sec ?? 0,
        $last->advance ?? 0,

        // EOBI (FIXED FORMAT)
        $employeeEobi . '|' . $employerEobi,

        // PESSI
        ($emp->pessi ? $emp->pessi . '|' . $emp->pessi_employer : '0|0'),

        // Loans & Tax
        $last->loan_emp_sec ?? 0,
        $last->itax ?? 0,
        $last->other_ded ?? 0,
        $last->other_loan ?? 0,

        // Net Salary
        $last->net ?? 0,
    ]);
}

    // ✅ Chunk for speed
    public function chunkSize(): int
    {
        return 1000;
    }

    // ✅ Styling (same as your blade)
    public function registerEvents(): array
{
    return [
        AfterSheet::class => function ($event) {

            $sheet = $event->sheet->getDelegate();

            // ==============================
            // ✅ PAGE SETUP (PRINT SETTINGS)
            // ==============================
            $sheet->getPageSetup()->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );

            $sheet->getPageSetup()->setPaperSize(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
            );

            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            // Repeat header row
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 7);

            // Margins
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.5);
            $sheet->getPageMargins()->setRight(0.5);

            // Footer
            $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

            // Hide grid
            $sheet->setShowGridlines(false);

            // ==============================
            // ✅ TITLE SECTION
            // ==============================
            $sheet->insertNewRowBefore(1, 6);

            $sheet->setCellValue('A1', 'The Lynx School');
            $sheet->setCellValue('A3', 'Salary History Report');
            $sheet->setCellValue('A5', now()->format('F Y'));

            $highestColumn = $sheet->getHighestColumn();

            $sheet->mergeCells("A1:{$highestColumn}1");
            $sheet->mergeCells("A3:{$highestColumn}3");
            $sheet->mergeCells("A5:{$highestColumn}5");

            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['size' => 28, 'bold' => true ,'name' => 'Edwardian Script ITC',]            ]);
            
            $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);

            $sheet->getStyle('A1:A5')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            // ==============================
            // ✅ HEADER STYLE
            // ==============================
            $sheet->getStyle("A7:{$highestColumn}7")->applyFromArray([
                'font' => ['bold' => true, 'size' => 8],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFBFBFBF'],
                ],
            ]);

            // ==============================
            // ✅ DATA STYLE
            // ==============================
            $lastRow = $sheet->getHighestRow();

            // Borders
            // $sheet->getStyle("A7:{$highestColumn}{$lastRow}")
            //     ->getBorders()
            //     ->getAllBorders()
            //     ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

           $sheet->getStyle("A7:{$highestColumn}{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'argb' => 'FFD9D9D9',
                        ],
                    ],
                ],
            ]);
            // Font size
            $sheet->getStyle("A8:{$highestColumn}{$lastRow}")
                ->getFont()->setSize(8);

            // Alignment
            $sheet->getStyle("A8:D{$lastRow}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("E8:H{$lastRow}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            // Right align numeric columns
            // for ($col = 'I'; $col <= $highestColumn; $col++) {
            //     $sheet->getStyle("{$col}8:{$col}{$lastRow}")
            //         ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            // }

            // ==============================
            // ✅ COLUMN WIDTHS
            // ==============================
            // foreach (range('A', $highestColumn) as $col) {
                // if ($col == 'A' || $col == 'B') {
                    $sheet->getColumnDimension('B')->setWidth(8);
                // } elseif ($col == 'C') {
                    $sheet->getColumnDimension('D')->setWidth(20);
            //     } else {
            //         $sheet->getColumnDimension($col)->setAutoSize(true);
            //     }
            // }

            // ==============================
            // ✅ FOOTER SIGNATURE
            // ==============================
            $sigRow = $lastRow + 2;

            $sheet->setCellValue("B{$sigRow}", '________________________');
            $sheet->setCellValue("{$highestColumn}{$sigRow}", '________________________');

            $sheet->getStyle("B{$sigRow}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle("{$highestColumn}{$sigRow}")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // ==============================
            // ✅ LOGO
            // ==============================
            $originalPath = public_path('assets/images/lynx2.jpg');

            // ==============================
            // Convert to grayscale
            // ==============================
            $img = imagecreatefromjpeg($originalPath);
            imagefilter($img, IMG_FILTER_GRAYSCALE);

            $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
            imagepng($img, $tmpPath);
            imagedestroy($img); // IMPORTANT: free memory

            // ==============================
            // Add to Excel
            // ==============================
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($tmpPath);
            $drawing->setHeight(70);
            $drawing->setCoordinates("{$highestColumn}1");
            $drawing->setWorksheet($sheet);
        }
    ];
}
}
