<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class EmployeeAdvanceExport implements FromArray, WithColumnFormatting, WithEvents
{
    protected $advances;
    protected $requestData;
    protected $groupRows = [];

    public function __construct($advances, array $requestData = [])
    {
        $this->advances = $advances;
        $this->requestData = $requestData;
    }

    public function array(): array
    {
        $rows = [];
        $header = [
            'Sr#',
            'Emp No',
            'Name',
            'Branch',
            'Department',
            'Designation',
            'Advance Month',
            'Approval Date',
            'Advance Amount',
            'Status',
            'Deduction',
            'Approved By',
            'Payment By',
            'Reference',
            'Reason',
        ];

        $rows[] = $header;
        $grouped = $this->advances->groupBy(function ($advance) {
            return optional($advance->employee)->branch_id ?: optional($advance->employee)->owned_by ?: 'none';
        });

        $globalSr = 1;
        $grandTotal = array_fill(0, count($header), 0);

        foreach ($grouped as $branchId => $items) {
            $branchName = $branchId !== 'none' ? (User::find($branchId)->name ?? 'Branch') : 'Branch';
            $this->groupRows[] = count($rows) + 1 + 7;
            $rows[] = [$branchName];

            $branchTotal = array_fill(0, count($header), 0);

            foreach ($items as $advance) {
                $employee = $advance->employee;
                $row = [
                    $globalSr++,
                    $employee->employee_id ?? '',
                    $employee->name ?? '',
                    $branchName,
                    optional($employee->department)->name ?? '',
                    optional($employee->designation)->name ?? '',
                    $advance->advance_date ? Date::PHPToExcel(new \DateTime($advance->advance_date)) : '',
                    $advance->approval_date ? Date::PHPToExcel(new \DateTime($advance->approval_date)) : '',
                    (float) ($advance->advance_amount ?? 0),
                    $this->statusLabel($advance->status),
                    !empty($advance->deducted_salary_id) ? 'Deducted' : 'Pending',
                    optional($advance->approvedBy)->name ?? '',
                    $advance->payment_method ? ucfirst($advance->payment_method) : '',
                    $advance->reference ?? '',
                    $advance->advance_reason ?? '',
                ];

                $rows[] = $row;
                $branchTotal[8] += (float) ($advance->advance_amount ?? 0);
                $grandTotal[8] += (float) ($advance->advance_amount ?? 0);
            }

            $branchTotal[0] = 'BRANCH TOTAL';
            $rows[] = $branchTotal;
        }

        $grandTotal[0] = 'GRAND TOTAL';
        $rows[] = $grandTotal;

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'G' => 'mmm-yyyy',
            'H' => 'dd-mmm-yyyy',
            'I' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 7);

                $highestColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);
                $sheet->freezePane('A9');
                $sheet->setShowGridlines(false);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $sheet->setCellValue('A1', 'The Lynx School');
                $sheet->setCellValue('A3', $this->branchTitle());
                $sheet->setCellValue('A5', $this->reportTitle());
                $sheet->setCellValue('A7', 'EMPLOYEES DETAIL');
                $sheet->setCellValue('G7', 'ADVANCE DETAIL');
                $sheet->setCellValue('J7', 'STATUS DETAIL');

                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->mergeCells("A5:{$highestColumn}5");
                $sheet->mergeCells('A7:F7');
                $sheet->mergeCells('G7:I7');
                $sheet->mergeCells('J7:O7');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 28, 'bold' => true, 'name' => 'Edwardian Script ITC'],
                ]);
                $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("A7:{$highestColumn}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFBFBFBF'],
                    ],
                ]);

                $sheet->getStyle("A7:{$highestColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD9D9D9'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getFont()->setSize(8);
                $sheet->getStyle("A9:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I9:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I9:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("C9:F{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("O9:O{$lastRow}")->getAlignment()->setWrapText(true);

                $fixedWidths = [
                    'A' => 6,
                    'B' => 10,
                    'C' => 24,
                    'D' => 20,
                    'E' => 18,
                    'F' => 18,
                    'G' => 13,
                    'H' => 13,
                    'I' => 14,
                    'J' => 12,
                    'K' => 12,
                    'L' => 18,
                    'M' => 12,
                    'N' => 18,
                    'O' => 35,
                ];

                foreach ($fixedWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                for ($r = 1; $r <= $lastRow; $r++) {
                    $cellValue = strtoupper((string) $sheet->getCell("A{$r}")->getValue());
                    if (strpos($cellValue, 'TOTAL') !== false) {
                        $sheet->mergeCells("A{$r}:H{$r}");
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFBFBFBF'],
                            ],
                        ]);
                    }
                }

                foreach ($this->groupRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:O{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'name' => 'Calibri'],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFD9D9D9'],
                        ],
                    ]);
                }

                $sigRow = $lastRow + 2;
                $sheet->setCellValue("B{$sigRow}", '________________________');
                $insetColumn = Coordinate::stringFromColumnIndex(max(1, Coordinate::columnIndexFromString($highestColumn) - 1));
                $sheet->setCellValue("{$insetColumn}{$sigRow}", '________________________');

                $originalPath = public_path('assets/images/lynx2.jpg');
                if (file_exists($originalPath) && function_exists('imagecreatefromjpeg')) {
                    $img = imagecreatefromjpeg($originalPath);
                    imagefilter($img, IMG_FILTER_GRAYSCALE);
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logo_gray.png';
                    imagepng($img, $tmpPath);
                    imagedestroy($img);

                    $drawing = new Drawing();
                    $drawing->setPath($tmpPath);
                    $drawing->setHeight(70);
                    $drawing->setCoordinates("{$insetColumn}1");
                    $drawing->setWorksheet($sheet);
                }
            },
        ];
    }

    protected function statusLabel($status): string
    {
        if ((int) $status === 1) {
            return 'Approved';
        }

        if ((int) $status === 2) {
            return 'Rejected';
        }

        return 'Pending';
    }

    protected function branchTitle(): string
    {
        if (!empty($this->requestData['branches'])) {
            return User::find($this->requestData['branches'])->name ?? 'Selected Branch';
        }

        return 'All Branches';
    }

    protected function reportTitle(): string
    {
        $from = !empty($this->requestData['from_month']) ? date('M Y', strtotime($this->requestData['from_month'] . '-01')) : '';
        $to = !empty($this->requestData['to_month']) ? date('M Y', strtotime($this->requestData['to_month'] . '-01')) : '';

        if ($from && $to) {
            return "Employee Advance Report from {$from} to {$to}";
        }

        if ($from) {
            return "Employee Advance Report from {$from}";
        }

        if ($to) {
            return "Employee Advance Report up to {$to}";
        }

        return 'Employee Advance Report';
    }
}
