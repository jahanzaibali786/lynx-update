<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalarySlipExport implements FromArray, WithEvents
{
    protected array $data;
    protected array $merges = [];
    protected array $sectionRows = [];
    protected array $headerRows = [];
    protected array $totalRows = [];
    protected array $dottedRows = [];
    protected array $breakRows = [];
    protected array $logoRows = [];
    protected array $titleRows = [];
    protected array $infoRows = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        $datas = collect($this->data['datas'] ?? [])->values();
        $salaryHeads = collect($this->data['salaryHeads'] ?? []);
        $headNames = $salaryHeads->pluck('head', 'id');
        $ytdTotals = $this->data['salarySlipYtdTotals'] ?? [];
        $headYtdTotals = $this->data['salarySlipHeadYtdTotals'] ?? [];
        $branchName = $this->data['salarySlipBranchName'] ?? 'All Branches';
        $selectedMonth = date('F Y', strtotime($this->data['requestdata']['date'] ?? now()));
        $reportName = 'Employee Pay Slip Report for the month of ' . $selectedMonth;

        foreach ($datas as $index => $data) {
            if ($index > 0 && $index % 3 === 0) {
                $this->breakRows[] = count($rows) + 1;
            }

            $startRow = count($rows) + 1;
            $this->logoRows[] = $startRow + 1;
            $this->titleRows[] = $startRow + 1;

            $rows[] = $this->blankRow();
            $rows[] = $this->row(['', 'The Lynx School']);
            $this->merges[] = "B{$startRow}:L{$startRow}";
            $this->merges[] = 'B' . ($startRow + 1) . ':L' . ($startRow + 1);
            $rows[] = $this->row(['', $branchName]);
            $this->merges[] = 'B' . ($startRow + 2) . ':N' . ($startRow + 2);
            $rows[] = $this->row(['', $reportName]);
            $this->merges[] = 'B' . ($startRow + 3) . ':N' . ($startRow + 3);
            $rows[] = $this->blankRow();

            $att = optional(optional($data->employee)->employee_monthly_salaries_attend)->first();
            $clOpen = (int) ($att->total_casual ?? 0);
            $clBal = (int) ($att->bal_casual ?? 0);
            $clTaken = max(0, $clOpen - $clBal);
            $alOpen = (int) ($att->total_annual ?? 0);
            $alBal = (int) ($att->bal_annual ?? 0);
            $alTaken = max(0, $alOpen - $alBal);
            $workingDays = (int) ($att->working_days ?? ($data->sal_days ?? 0));
            $scale = optional(optional($data->employee)->employee_payscale_details)->first();
            $empId = $data->employee_id;
            $employeeYtd = $ytdTotals[$empId] ?? [];
            $employeeHeadYtd = $headYtdTotals[$empId] ?? [];
            $ytd = fn ($field) => (float) ($employeeYtd[$field] ?? 0);

            $rows[] = $this->row(['', '', 'Employee#', optional($data->employee)->employee_id ?? optional($data->employee)->id, '', '', 'Leaves Balances', 'C/L', 'A/L', '', 'Payment Date', '', $data->paid_date ? date('d-M-y', strtotime($data->paid_date)) : '-']);
            $rows[] = $this->row(['', '', 'Name', optional($data->employee)->name, '', '', 'O.Balance', $clOpen, $alOpen, '', 'Mode', '', strtolower($scale->paymode ?? '') === 'cash' ? 'Cash' : 'Bank']);
            $rows[] = $this->row(['', '', 'Corporate Title', optional(optional($data->employee)->designation)->name, '', '', 'Leaves', $clTaken, $alTaken, '', 'Bank/Branch', '', $scale->paymode ?? '-']);
            $rows[] = $this->row(['', '', 'Department', optional(optional($data->employee)->department)->name ?? '-', '', '', 'C.Balance', $clBal, $alBal, '', 'N.T.N', '', $scale->account_number ?? '-']);
            $rows[] = $this->row(['', '', '', '', '', '', 'Working Days', $workingDays]);
            $infoStartRow = count($rows) - 4;
            $this->infoRows = array_merge($this->infoRows, range($infoStartRow, $infoStartRow + 4));

            $sectionRow = count($rows) + 1;
            $rows[] = $this->row(['', '', 'EARNINGS', '', '', '', 'DEDUCTIONS', '', '', '', '', '', '']);
            $this->merges[] = "C{$sectionRow}:E{$sectionRow}";
            $this->merges[] = "G{$sectionRow}:I{$sectionRow}";
            $this->merges[] = "K{$sectionRow}:M{$sectionRow}";
            $this->sectionRows[] = $sectionRow;

            [$earnings, $deductions, $contributions, $totals] = $this->buildSlipRows($data, $headNames, $employeeHeadYtd, $ytd);
            $lineCount = max(count($earnings), count($deductions), count($contributions));
            $this->padRows($earnings, $lineCount, ['label' => '', 'pm' => '', 'ytd' => '', 'head' => false, 'total' => false]);
            $this->padRows($deductions, $lineCount, ['label' => '', 'pm' => '', 'ytd' => '', 'head' => false, 'total' => false]);
            $this->padRows($contributions, $lineCount, ['label' => '', 'amount' => '', 'head' => false, 'total' => false]);

            for ($i = 0; $i < $lineCount; $i++) {
                $rowNumber = count($rows) + 1;
                if (($earnings[$i]['head'] ?? false) || ($deductions[$i]['head'] ?? false) || ($contributions[$i]['head'] ?? false)) {
                    $this->headerRows[] = $rowNumber;
                }
                if (($earnings[$i]['total'] ?? false) || ($contributions[$i]['total'] ?? false)) {
                    $this->totalRows[] = $rowNumber;
                }

                $rows[] = $this->row([
                    '',
                    '',
                    $earnings[$i]['label'],
                    $this->displayNumber($earnings[$i]['pm']),
                    $this->displayNumber($earnings[$i]['ytd']),
                    '',
                    $deductions[$i]['label'],
                    $this->displayNumber($deductions[$i]['pm']),
                    $this->displayNumber($deductions[$i]['ytd']),
                    '',
                    $contributions[$i]['label'],
                    '',
                    $this->displayNumber($contributions[$i]['amount']),
                ]);
            }

            $totalRow = count($rows) + 1;
            $rows[] = $this->row(['', '', 'Total Rs.', $totals['earning_pm'], $totals['earning_ytd'], '', 'Total Rs.', $totals['deduction_pm'], $totals['deduction_ytd'], '', 'Cost to Company', '', $totals['ctc']]);
            $this->totalRows[] = $totalRow;
            $rows[] = $this->row(['', '']);
            $disbursedRow = count($rows) + 1;
            $rows[] = $this->row(['', '', 'Total Amount Disbursed Rs.', '', '', '', '', $totals['disbursed']]);
            $this->merges[] = "C{$disbursedRow}:G{$disbursedRow}";
            $this->totalRows[] = $disbursedRow;
            $rows[] = $this->row(['', '']);
            $rows[] = $this->row(['', 'This is a system generated document and does not require a signature']);
            $this->merges[] = 'B' . count($rows) . ':N' . count($rows);

            if ($index % 3 !== 2 && $index < $datas->count() - 1) {
                $dottedRow = count($rows) + 1;
                $rows[] = $this->blankRow();
                $this->dottedRows[] = $dottedRow;
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.25);
                $sheet->getPageMargins()->setBottom(0.25);
                $sheet->getPageMargins()->setLeft(0.2);
                $sheet->getPageMargins()->setRight(0.2);
                $sheet->setShowGridlines(false);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                foreach ($this->merges as $merge) {
                    $sheet->mergeCells($merge);
                }

                $widths = ['A' => 1, 'B' => 1, 'C' => 17, 'D' => 10, 'E' => 10, 'F' => 1, 'G' => 16, 'H' => 9, 'I' => 9, 'J' => 1, 'K' => 18, 'L' => 1, 'M' => 12, 'N' => 1];
                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:N{$highestRow}")->getFont()->setName('Calibri')->setSize(7);
                $sheet->getStyle("A1:N{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("D1:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H1:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("M1:M{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D1:E{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("H1:I{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("M1:M{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');

                foreach ($this->sectionRows as $row) {
                    $sheet->getStyle("C{$row}:M{$row}")->applyFromArray($this->headingStyle());
                }
                foreach ($this->headerRows as $row) {
                    $sheet->getStyle("C{$row}:M{$row}")->applyFromArray($this->headingStyle());
                }
                foreach ($this->totalRows as $row) {
                    $sheet->getStyle("C{$row}:M{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("C{$row}:M{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                foreach ($this->dottedRows as $row) {
                    $sheet->getStyle("B{$row}:N{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOTTED);
                }
                foreach ($this->breakRows as $row) {
                    $sheet->setBreak("A{$row}", Worksheet::BREAK_ROW);
                }

                foreach ($this->titleRows as $row) {
                    $branchRow = $row + 1;
                    $reportRow = $row + 2;
                    $sheet->getStyle("B{$branchRow}:N{$branchRow}")->getFont()->setBold(true)->setSize(10);
                    $sheet->getStyle("B{$branchRow}:N{$branchRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("B{$reportRow}:N{$reportRow}")->getFont()->setBold(true)->setSize(9);
                    $sheet->getStyle("B{$reportRow}:N{$reportRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(9);

                    if (in_array($row, $this->titleRows, true)) {
                        $sheet->getStyle("B{$row}")->getFont()->setName('Edwardian Script ITC')->setSize(28)->setBold(true);
                        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getRowDimension($row)->setRowHeight(28);
                    }

                    $hasSlipTableContent = trim((string) $sheet->getCell("C{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("D{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("E{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("G{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("H{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("I{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("K{$row}")->getValue()) !== ''
                        || trim((string) $sheet->getCell("M{$row}")->getValue()) !== '';

                    if (
                        $hasSlipTableContent
                        && !in_array($row, $this->infoRows, true)
                        && trim((string) $sheet->getCell("B{$row}")->getValue()) !== 'This is a system generated document and does not require a signature'
                    ) {
                        $sheet->getStyle("C{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle("G{$row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle("K{$row}:M{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }

                    if (trim((string) $sheet->getCell("C{$row}")->getValue()) === 'Employee#') {
                        $sheet->getStyle("C{$row}:M" . ($row + 4))->getFont()->setBold(false);
                        $sheet->getStyle("C{$row}:C" . ($row + 4))->getFont()->setBold(true);
                        $sheet->getStyle("G{$row}:G" . ($row + 4))->getFont()->setBold(true);
                        $sheet->getStyle("K{$row}:K" . ($row + 4))->getFont()->setBold(true);
                    }
                }

                $logoPath = public_path('assets/images/lynxlogo(2).png');
                if (file_exists($logoPath)) {
                    foreach ($this->logoRows as $row) {
                        $drawing = new Drawing();
                        $drawing->setPath($logoPath);
                        $drawing->setHeight(38);
                        $drawing->setCoordinates("M{$row}");
                        $drawing->setWorksheet($sheet);
                    }
                }
            },
        ];
    }

    protected function buildSlipRows($data, $headNames, $employeeHeadYtd, $ytd): array
    {
        $grossRows = [];
        foreach (($data->salary_heads ?? []) as $salaryHead) {
            $grossRows[] = [
                'label' => $headNames[$salaryHead->head_id] ?? 'Head ' . $salaryHead->head_id,
                'pm' => (float) $salaryHead->head_value,
                'ytd' => (float) ($employeeHeadYtd[$salaryHead->head_id] ?? 0),
                'head' => false,
                'total' => false,
            ];
        }

        $grossPm = (float) ($data->gross ?? 0);
        $grossYtd = $ytd('gross');
        $otherPm = (float) ($data->conv ?? 0);
        $miscPm = (float) ($data->misc ?? 0);
        $otherAllowancePm = (float) ($data->other ?? 0);
        $enticementPm = $otherPm + $miscPm + $otherAllowancePm;
        $enticementYtd = $ytd('conv') + $ytd('misc') + $ytd('other');
        $stopSalary = (float) ($data->stop_sal ?? 0);

        $earnings = array_merge(
            [['label' => 'Gross Salary', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false]],
            $grossRows,
            [
                ['label' => 'Gross Rs.', 'pm' => $grossPm, 'ytd' => $grossYtd, 'head' => false, 'total' => true],
                ['label' => 'Enticements', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false],
                ['label' => 'Other', 'pm' => $otherPm, 'ytd' => $ytd('conv'), 'head' => false, 'total' => false],
                ['label' => 'Drns, Misc', 'pm' => $miscPm, 'ytd' => $ytd('misc'), 'head' => false, 'total' => false],
                ['label' => 'Other Allowance', 'pm' => $otherAllowancePm, 'ytd' => $ytd('other'), 'head' => false, 'total' => false],
                ['label' => 'Net Gross Rs.', 'pm' => $grossPm + $enticementPm, 'ytd' => '', 'head' => true, 'total' => false],
                ['label' => 'Stop Salary', 'pm' => $stopSalary, 'ytd' => '', 'head' => false, 'total' => false],
            ]
        );

        $deductions = [
            ['label' => 'Heads', 'pm' => 'P.M', 'ytd' => 'Y.T.D', 'head' => true, 'total' => false],
            ['label' => 'Employee Security', 'pm' => (float) ($data->emp_sec ?? 0), 'ytd' => $ytd('emp_sec'), 'head' => false, 'total' => false],
            ['label' => 'E.O.B.I', 'pm' => (float) ($data->eobi ?? 0), 'ytd' => $ytd('eobi'), 'head' => false, 'total' => false],
            ['label' => 'P.E.S.S.I', 'pm' => (float) ($data->pessi ?? 0), 'ytd' => $ytd('pessi'), 'head' => false, 'total' => false],
            ['label' => 'Income Tax', 'pm' => (float) ($data->it ?? 0), 'ytd' => $ytd('it'), 'head' => false, 'total' => false],
            ['label' => 'Other Deduction', 'pm' => (float) ($data->dedu ?? 0), 'ytd' => $ytd('dedu'), 'head' => false, 'total' => false],
            ['label' => 'Advance', 'pm' => (float) ($data->sal_advance ?? 0), 'ytd' => $ytd('sal_advance'), 'head' => false, 'total' => false],
            ['label' => 'Stop Salary', 'pm' => 0, 'ytd' => '-', 'head' => false, 'total' => false],
            ['label' => 'Training Course', 'pm' => (float) ($data->tra_course ?? 0), 'ytd' => $ytd('tra_course'), 'head' => false, 'total' => false],
            ['label' => 'Loan Emp Security', 'pm' => (float) ($data->loan ?? 0), 'ytd' => $ytd('loan'), 'head' => false, 'total' => false],
        ];

        $empSecYtd = $ytd('emp_sec');
        $contributions = [
            ['label' => 'Employee Security Balance Y.T.D', 'amount' => '', 'head' => true, 'total' => false],
            ['label' => 'Employee Security', 'amount' => $empSecYtd, 'head' => false, 'total' => false],
            ['label' => 'Net Balance Rs.', 'amount' => $empSecYtd, 'head' => false, 'total' => true],
            ['label' => 'Employer Contribution P.M', 'amount' => '', 'head' => true, 'total' => false],
            ['label' => 'Eobi Contribution', 'amount' => (float) ($data->eobi_employer ?? 0), 'head' => false, 'total' => false],
            ['label' => 'Pessi Contribution', 'amount' => (float) ($data->pessi_employer ?? 0), 'head' => false, 'total' => false],
            ['label' => 'Child Concession', 'amount' => (float) ($data->chaild_con ?? 0), 'head' => false, 'total' => false],
        ];

        $deductionPm = array_sum(array_map(fn ($row) => is_numeric($row['pm']) ? (float) $row['pm'] : 0, $deductions));
        $deductionYtd = array_sum(array_map(fn ($row) => is_numeric($row['ytd']) ? (float) $row['ytd'] : 0, $deductions));

        return [$earnings, $deductions, $contributions, [
            'earning_pm' => $grossPm + $enticementPm + $stopSalary,
            'earning_ytd' => $enticementYtd,
            'deduction_pm' => $deductionPm,
            'deduction_ytd' => $deductionYtd,
            'ctc' => $grossPm + (float) ($data->eobi_employer ?? 0) + (float) ($data->pessi_employer ?? 0) + (float) ($data->chaild_con ?? 0),
            'disbursed' => $grossPm + $enticementPm + $stopSalary - $deductionPm,
        ]];
    }

    protected function row(array $values): array
    {
        return array_pad($values, 14, '');
    }

    protected function blankRow(): array
    {
        return array_fill(0, 14, '');
    }

    protected function padRows(array &$rows, int $count, array $empty): void
    {
        while (count($rows) < $count) {
            $rows[] = $empty;
        }
    }

    protected function displayNumber($value)
    {
        return is_numeric($value) ? (float) $value : $value;
    }

    protected function headingStyle(): array
    {
        return [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD8D8D8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
    }
}
