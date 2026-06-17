<?php

namespace App\Exports;

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

class SalaryHistoryReportExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected $employees;
    protected $requestData;
    protected $salaryHeads;
    protected $branchRows = [];
    protected $grandTotalRows = [];

    public function __construct($employees, array $requestData = [])
    {
        $this->employees = $employees;
        $this->requestData = $requestData;
        $this->salaryHeads = $this->collectSalaryHeads();
    }

    public function array(): array
    {
        $rows = [$this->headings()];
        $globalSr = 1;
        $grandTotals = array_fill(0, count($this->headings()), 0);
        $detailRowsCount = 0;

        $groupedEmployees = $this->employees
            ->sortBy([
                fn($employee) => optional($employee->userbranch)->name,
                fn($employee) => $employee->name,
            ])
            ->groupBy(fn($employee) => optional($employee->userbranch)->name ?: 'Branch');

        foreach ($groupedEmployees as $branchName => $employees) {
            $this->branchRows[] = count($rows) + 1 + 7;
            $rows[] = [$branchName];

            foreach ($employees as $employee) {
                $salaries = collect($employee->employee_monthly_salaries ?? [])->sortBy('salary_date');

                foreach ($salaries as $salary) {
                    $payscale = $this->payscaleForSalary($employee, $salary);
                    $attendance = $this->attendanceForSalary($employee, $salary);
                    $headValues = $this->salaryHeadValues($salary);
                    $deductionNet = $this->netSalary($salary, $payscale);
                    $companyContribution = (float) ($payscale->eobi_employer ?? 0) + (float) ($payscale->pessi_employer ?? 0) + (float) ($salary->loan_adj ?? 0);

                    $row = array_merge([
                        $globalSr++,
                        $employee->employee_id ?? '',
                        $employee->name ?? '',
                        !empty($salary->salary_date) ? Carbon::parse($salary->salary_date)->format('d-M-Y') : '',
                        $salary->scale_no ?? optional($payscale)->scale_no ?? '',
                        optional($employee->designation)->name ?? '',
                    ], $headValues, [
                        (float) ($salary->basics ?? 0),
                        (float) ($salary->other_add ?? $salary->other ?? 0),
                        (float) ($salary->drns ?? 0) + (float) ($salary->misc ?? 0),
                        (float) ($salary->stop_sal ?? 0),
                        (float) ($salary->gross ?? 0),
                        (float) ($salary->emp_sec ?? 0),
                        (float) ($salary->it ?? 0),
                        (float) ($payscale->eobi ?? 0),
                        (float) ($salary->loan ?? 0),
                        (float) ($salary->dedu ?? $salary->other ?? 0),
                        (float) ($salary->stop_sal ?? 0),
                        (float) ($payscale->pessi ?? 0),
                        (float) ($salary->loan_adj ?? 0),
                        $deductionNet,
                        (float) ($payscale->pessi_employer ?? 0),
                        (float) ($payscale->eobi_employer ?? 0),
                        $companyContribution,
                        $deductionNet + $companyContribution,
                        (float) ($attendance->total_annual ?? 0),
                        (float) (($attendance->total_annual ?? 0) - ($attendance->bal_annual ?? 0)),
                        (float) ($attendance->bal_annual ?? 0),
                        (float) ($attendance->total_casual ?? 0),
                        (float) (($attendance->total_casual ?? 0) - ($attendance->bal_casual ?? 0)),
                        (float) ($attendance->bal_casual ?? 0),
                        (float) ($attendance->working_days ?? 0),
                    ]);

                    foreach ($row as $index => $value) {
                        if (is_numeric($value) && $index >= 6) {
                            $grandTotals[$index] += (float) $value;
                        }
                    }

                    $detailRowsCount++;
                    $rows[] = $row;
                }
            }
        }

        $grandTotals[0] = 'GRAND TOTAL (Count: ' . $detailRowsCount . ')';
        $this->grandTotalRows[] = count($rows) + 1 + 7;
        $rows[] = $grandTotals;

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

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.35);
                $sheet->getPageMargins()->setRight(0.35);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A9');

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
                $sheet->getStyle("A8:{$highestColumn}{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle("A9:{$highestColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C9:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $numericStart = 7;
                if ($numericStart <= $highestColumnIndex) {
                    $numericStartColumn = Coordinate::stringFromColumnIndex($numericStart);
                    $sheet->getStyle("{$numericStartColumn}9:{$highestColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("{$numericStartColumn}9:{$highestColumn}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                }

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

                foreach ($this->grandTotalRows as $rowIndex) {
                    $sheet->mergeCells("A{$rowIndex}:F{$rowIndex}");
                    $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 8, 'name' => 'Calibri'],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFBFBFBF'],
                        ],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_DOUBLE],
                            'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                    ]);
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
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
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $rightStartIndex = max(2, $highestColumnIndex - 1);
                $rightStartColumn = Coordinate::stringFromColumnIndex($rightStartIndex);

                $sheet->setCellValue("B{$sigRow}", '________________________');
                $sheet->mergeCells("{$rightStartColumn}{$sigRow}:{$highestColumn}{$sigRow}")
                    ->setCellValue("{$rightStartColumn}{$sigRow}", '________________________');

                $sheet->getStyle("B{$sigRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("{$rightStartColumn}{$sigRow}:{$highestColumn}{$sigRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    private function headings(): array
    {
        return array_merge([
            'Sr#',
            'Emp No',
            'Employee Name',
            'Date',
            'Pay Scale',
            'Designation',
        ], $this->salaryHeads->pluck('head')->values()->all(), [
            'Earned Basic',
            'Other Allowance',
            'Drns & Misc',
            'Stop Salary',
            'Gross',
            'Emp Sec',
            'Adv Tax',
            'EOBI',
            'Loan E.S',
            'Other Deduction',
            'Stop Salary Ded',
            'PESSI',
            'Loan Adj',
            'Net',
            'PESSI Comp',
            'EOBI Comp',
            'Total',
            'Cost to Comp',
            'Annual OP',
            'Annual LVS',
            'Annual Bal',
            'Casual OP',
            'Casual LVS',
            'Casual Bal',
            'Working Days',
        ]);
    }

    private function collectSalaryHeads()
    {
        return $this->employees
            ->flatMap(fn($employee) => collect($employee->employee_monthly_salaries ?? []))
            ->flatMap(fn($salary) => collect($salary->salaryheads ?? []))
            ->map(fn($salaryHead) => [
                'id' => $salaryHead->SalaryHead->id ?? $salaryHead->salary_head_id,
                'head' => $salaryHead->SalaryHead->head ?? '',
            ])
            ->filter(fn($head) => !empty($head['id']) && !empty($head['head']))
            ->unique('id')
            ->values();
    }

    private function salaryHeadValues($salary): array
    {
        $values = collect($salary->salaryheads ?? [])
            ->keyBy(fn($salaryHead) => $salaryHead->SalaryHead->id ?? $salaryHead->salary_head_id);

        return $this->salaryHeads
            ->map(fn($head) => (float) (optional($values->get($head['id']))->head_value ?? 0))
            ->all();
    }

    private function payscaleForSalary($employee, $salary)
    {
        $salaryDate = !empty($salary->salary_date) ? Carbon::parse($salary->salary_date)->startOfMonth() : null;
        $details = collect($employee->employee_payscale_details ?? [])->sortBy('effect_from');

        if (!$salaryDate) {
            return $details->last();
        }

        return $details
            ->filter(fn($detail) => !empty($detail->effect_from) && Carbon::parse($detail->effect_from)->startOfMonth()->lte($salaryDate))
            ->last() ?: $details->last();
    }

    private function attendanceForSalary($employee, $salary)
    {
        if (empty($salary->salary_date)) {
            return null;
        }

        $targetMonth = Carbon::parse($salary->salary_date)->format('Y-m');

        return collect($employee->employee_monthly_salaries_attend ?? [])
            ->first(fn($attendance) => !empty($attendance->for_month_of) && Carbon::parse($attendance->for_month_of)->format('Y-m') === $targetMonth);
    }

    private function netSalary($salary, $payscale): float
    {
        return (float) ($salary->gross ?? 0) - (
            (float) ($salary->emp_sec ?? 0)
            + (float) ($salary->it ?? 0)
            + (float) ($payscale->eobi ?? 0)
            + (float) ($salary->loan ?? 0)
            + (float) ($salary->dedu ?? $salary->other ?? 0)
            + (float) ($salary->stop_sal ?? 0)
            + (float) ($salary->loan_adj ?? 0)
            + (float) ($payscale->pessi ?? 0)
        );
    }

    private function branchTitle(): string
    {
        $branchId = $this->requestData['branches'] ?? null;
        if (!empty($branchId) && $branchId !== 'all') {
            return User::find($branchId)->name ?? 'Selected Branch';
        }

        return 'All Branches';
    }

    private function reportTitle(): string
    {
        $from = $this->requestData['from_date'] ?? null;
        $to = $this->requestData['to_date'] ?? null;

        if ($from && $to) {
            return 'Employee Payroll History Report from ' . Carbon::parse($from)->format('d-M-Y') . ' to ' . Carbon::parse($to)->format('d-M-Y');
        }

        return 'Employee Payroll History Report';
    }
}
