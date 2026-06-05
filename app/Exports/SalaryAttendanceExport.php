<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SalaryAttendanceExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected $datas;
    protected $requestdata;
    protected $reportType;
    protected $branchRows = [];

    public function __construct($datas, $requestdata, $reportType = 'summary')
    {
        $this->datas = $datas;
        $this->requestdata = $requestdata;
        $this->reportType = $reportType === 'details' ? 'details' : 'summary';
    }

    public function array(): array
    {
        $rows = [];
        $header = $this->reportType === 'details'
            ? [
                'Sr#',
                'Branch Sr#',
                'Emp No',
                'Name',
                'Salary Month',
                'Month Days',
                'Holidays',
                'Working Days',
                'Present',
                'Leave',
                'Absent',
                'Total Annual',
                'Bal Annual',
                'Total Casual',
                'Bal Casual',
                'Status',
            ]
            : [
                'Sr#',
                'Branch Sr#',
                'Emp No',
                'Name',
                'Month Days',
                'Holidays',
                'Working Days',
                'Present',
                'Leave',
                'Absent',
                'Status',
            ];

        $rows[] = $header;

        $globalSr = 1;
        $groupedItems = $this->datas
            ->sortBy([
                fn($row) => optional(optional($row->employee)->user)->name,
                fn($row) => optional($row->employee)->name,
            ])
            ->groupBy(fn($row) => optional(optional($row->employee)->user)->name ?: 'Branch');

        foreach ($groupedItems as $branchName => $items) {
            $branchSr = 1;
            $this->branchRows[] = count($rows) + 1 + 7;
            $rows[] = [$branchName];

            foreach ($items->sortBy(fn($row) => optional($row->employee)->name) as $data) {
                $leaveDays = (float) ($data->leave ?? 0);
                $absentDays = (float) ($data->absents ?? 0);
                $monthDays = $this->workingDays($data, $absentDays);
                $holidays = $this->holidayCount($data, $monthDays, $absentDays);
                $workingDays = max(0, $monthDays - $holidays);
                $presentDays = max(0, $workingDays - $leaveDays - $absentDays);

                $rows[] = $this->reportType === 'details'
                    ? [
                        $globalSr++,
                        $branchSr++,
                        optional($data->employee)->employee_id ?? '',
                        optional($data->employee)->name ?? '',
                        !empty($data->for_month_of) ? Carbon::parse($data->for_month_of)->format('M-Y') : '',
                        $monthDays,
                        $holidays,
                        $workingDays,
                        $presentDays,
                        $leaveDays,
                        $absentDays,
                        $data->total_annual ?? 0,
                        $data->bal_annual ?? 0,
                        $data->total_casual ?? 0,
                        $data->bal_casual ?? 0,
                        $this->statusText($data),
                    ]
                    : [
                        $globalSr++,
                        $branchSr++,
                        optional($data->employee)->employee_id ?? '',
                        optional($data->employee)->name ?? '',
                        $monthDays,
                        $holidays,
                        $workingDays,
                        $presentDays,
                        $leaveDays,
                        $absentDays,
                        $this->statusText($data),
                    ];
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 7);

                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $lastRow = $sheet->getHighestRow();

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
                $sheet->freezePane('A9');
                $sheet->setShowGridlines(false);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $sheet->getRowDimension(1)->setRowHeight(52);
                $sheet->getRowDimension(2)->setRowHeight(6);

                $sheet->setCellValue('A1', 'The Lynx School');
                $sheet->setCellValue('A3', $this->branchTitle());
                $sheet->setCellValue('A5', $this->reportTitle());
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A3:{$highestColumn}3");
                $sheet->mergeCells("A5:{$highestColumn}5");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['size' => 28, 'bold' => true, 'name' => 'Edwardian Script ITC'],
                ]);
                $sheet->getStyle('A3')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A5')->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                if ($this->isPdfExport()) {
                    $headerTextPath = public_path('assets/images/lynxheadertext.jpg');
                    if (file_exists($headerTextPath)) {
                        $sheet->setCellValue('A1', '');
                        $headerDrawing = new Drawing();
                        $headerDrawing->setPath($headerTextPath);
                        $headerDrawing->setHeight(45);
                        $headerDrawing->setCoordinates('A1');
                        $headerDrawing->setOffsetX(4);
                        $headerDrawing->setOffsetY(4);
                        $headerDrawing->setWorksheet($sheet);
                    }
                }

                $sheet->getStyle("A8:{$highestColumn}8")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
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

                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFD9D9D9'],
                        ],
                    ],
                ]);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getFont()->setSize(8);
                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D8:D{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("{$highestColumn}8:{$highestColumn}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                foreach ($this->branchRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:{$highestColumn}{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'name' => 'Calibri'],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFD9D9D9'],
                        ],
                    ]);
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $logoPath = public_path('assets/images/lynx2.jpg');
                if (file_exists($logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(62);
                    $drawing->setCoordinates(Coordinate::stringFromColumnIndex(max(1, $highestColumnIndex - 1)) . '1');
                    $drawing->setOffsetX(18);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }

                $sigRow = $lastRow + 2;
                $leftEndColumn = Coordinate::stringFromColumnIndex(min($highestColumnIndex, 3));
                $rightColumnIndex = max(1, $highestColumnIndex - 1);
                $rightColumn = Coordinate::stringFromColumnIndex($rightColumnIndex);
                $rightMergeStartColumn = Coordinate::stringFromColumnIndex(max(1, $rightColumnIndex - 1));

                $sheet->mergeCells("B{$sigRow}:{$leftEndColumn}{$sigRow}");
                $sheet->setCellValue("B{$sigRow}", '________________________');

                $sheet->mergeCells("{$rightMergeStartColumn}{$sigRow}:{$rightColumn}{$sigRow}");
                $sheet->setCellValue("{$rightMergeStartColumn}{$sigRow}", '________________________');

                $sheet->getStyle("B{$sigRow}:{$leftEndColumn}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("{$rightMergeStartColumn}{$sigRow}:{$rightColumn}{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    private function statusText($data): string
    {
        if (!empty($data->gm_final)) {
            return 'GM Final';
        }
        if (!empty($data->sal_final)) {
            return 'Salary Final';
        }
        if (!empty($data->adm_final)) {
            return 'Finalized';
        }
        if (!empty($data->accountant_finalize)) {
            return 'Fwd to Admin';
        }
        return 'Generated';
    }

    private function workingDays($data, float $absentDays): float
    {
        $employeeMonthDays = (float) ($data->working_days ?? 0) + $absentDays;
        if ($employeeMonthDays > 0 && $employeeMonthDays < 24) {
            return $employeeMonthDays;
        }

        return (float) ($data->month_days ?? $employeeMonthDays);
    }

    private function holidayCount($data, float $workingDays, float $absentDays): int
    {
        $employeeMonthDays = (float) ($data->working_days ?? 0) + $absentDays;
        if ($employeeMonthDays > 0 && $employeeMonthDays < 24) {
            return 0;
        }

        if (empty($data->for_month_of)) {
            return 0;
        }

        $month = Carbon::parse($data->for_month_of);
        $sundays = 0;
        for ($day = $month->copy()->startOfMonth(); $day->lte($month->copy()->endOfMonth()); $day->addDay()) {
            if ($day->isSunday()) {
                $sundays++;
            }
        }

        return min($sundays, (int) $workingDays);
    }

    private function branchTitle(): string
    {
        $branchId = $this->requestdata['branches'] ?? null;
        if (!empty($branchId) && $branchId !== 'all') {
            return User::find($branchId)->name ?? 'Selected Branch';
        }
        return 'All Branches';
    }

    private function reportTitle(): string
    {
        $date = $this->requestdata['date'] ?? now()->format('Y-m-d');
        $type = $this->reportType === 'details' ? 'Details' : 'Summary';
        return 'Salary Attendance ' . $type . ' Report for ' . date('F Y', strtotime($date));
    }

    private function isPdfExport(): bool
    {
        return ($this->requestdata['export_type'] ?? '') === 'pdf';
    }
}
