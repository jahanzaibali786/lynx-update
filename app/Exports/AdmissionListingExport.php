<?php

namespace App\Exports;

use App\Models\Challans;
use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AdmissionListingExport implements FromView, WithEvents, ShouldAutoSize
{
    protected Request $request;
    protected string $report_name;
    protected $branches;
    protected string $branchName;
    protected array $params;

    public function __construct(Request $request, $branchName, $report_name, $branches, $params)
    {
        $this->request = $request;
        $this->branchName = $branchName;
        $this->report_name = $report_name;
        $this->branches = $branches;
        $this->params = $params;
    }

    public function view(): View
    {
        $request = $this->request;

        $user = Auth::user();
        $userType = $user->type;
        $userCreatorId = $user->creatorId();
        $userOwnedId = $user->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $userCreatorId)
                ->pluck('name', 'id');

            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', 'all');

            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('active_status', 1)
                ->where('created_by', $userCreatorId);

            $classes = Classes::where('created_by', $userCreatorId)
                ->where('active_status', 1)
                ->pluck('name', 'id');

            $classes->prepend('All Classes', 'all');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');

            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('active_status', 1)
                ->where('owned_by', $userOwnedId);

            $classes = Classes::where('owned_by', $userOwnedId)
                ->where('active_status', 1)
                ->pluck('name', 'id');

            $classes->prepend('All Classes', 'all');
        }

        $classIds = $classes->keys()->filter(fn($id) => $id != 'all')->values();

        $sections = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->whereIn('class_sections.class_id', $classIds)
            ->select('sections.id', 'sections.name')
            ->distinct()
            ->pluck('sections.name', 'sections.id');

        $sections->prepend('All Sections', 'all');

        if ($request->filled('branch') && $request->branch != 'all') {
            $query->where('owned_by', $request->branch);
        }

        if ($request->filled('class') && $request->class != 'all') {
            $query->where('class_id', $request->class);
        }

        if ($request->filled('student') && $request->student != 'all') {
            $query->where('regId', $request->student);
        }

        if ($request->filled('sections') && $request->sections != 'all') {
            $query->where('section_id', $request->sections);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');

            $dateFrom = ($currentMonth >= 7)
                ? "$currentYear-07-01"
                : date('Y-07-01', strtotime('-1 year'));

            $dateTo = ($currentMonth >= 7)
                ? date('Y-06-30', strtotime('+1 year'))
                : "$currentYear-06-30";

            $request->merge([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('adm_date', [$dateFrom, $dateTo]);
        }

        $studentsCollection = $query->get();
        $studentData = $studentsCollection->groupBy('owned_by');

        $regIds = $studentsCollection
            ->pluck('regId')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $challans = Challans::whereIn('student_id', $regIds)
            ->whereRaw('LOWER(challan_type) LIKE ?', ['%admission%'])
            ->with(['heads.feehead'])
            ->get()
            ->keyBy('student_id');

        $heads = $challans
            ->flatMap(fn($challan) => $challan->heads)
            ->filter(fn($head) => !empty($head->feehead))
            ->map(fn($head) => (object) [
                'id' => $head->head_id,
                'fee_head' => $head->feehead->fee_head,
            ])
            ->unique('id')
            ->values();

        $branchTotals = [];
        $grandTotal = 0;
        $branchHeadTotals = [];
        $grandHeadTotals = [];
        $studentChallanData = [];

        foreach ($studentData as $branchId => $students) {
            $branchTotal = 0;
            $branchHeadTotals[$branchId] = [];

            foreach ($students as $student) {
                $studentKey = $student->regId;
                $studentTotal = 0;
                $challanHeads = [];

                $challan = $challans[$studentKey] ?? null;

                if ($challan) {
                    foreach ($challan->heads as $head) {
                        $amount = (float) ($head->price ?? 0);
                        $studentTotal += $amount;

                        $challanHeads[$head->head_id] = [
                            'name' => $head->feehead->fee_head ?? '',
                            'amount' => $amount,
                            'head_id' => $head->head_id,
                        ];

                        $branchHeadTotals[$branchId][$head->head_id] =
                            ($branchHeadTotals[$branchId][$head->head_id] ?? 0) + $amount;

                        $grandHeadTotals[$head->head_id] =
                            ($grandHeadTotals[$head->head_id] ?? 0) + $amount;
                    }

                    $studentChallanData[$studentKey] = [
                        'challan_no' => $challan->challanNo,
                        'challan_id' => $challan->id,
                        'heads' => $challanHeads,
                        'total' => $studentTotal,
                    ];
                } else {
                    $studentChallanData[$studentKey] = [
                        'challan_no' => '',
                        'challan_id' => '',
                        'heads' => [],
                        'total' => 0,
                    ];
                }

                $student->total_amount = $studentTotal;
                $branchTotal += $studentTotal;
            }

            $branchTotals[$branchId] = $branchTotal;
            $grandTotal += $branchTotal;
        }

        $student = [];
        $report_name = $this->report_name;
        $fromDate = $request->input('date_from');
        $toDate = $request->input('date_to');
        $is_signature = false;
        $is_branch = true;

        return view('student.exports.admission_listing', compact(
            'studentData',
            'student',
            'heads',
            'classes',
            'sections',
            'branchTotals',
            'grandTotal',
            'branchHeadTotals',
            'grandHeadTotals',
            'studentChallanData',
            'branches',
            'request',
            'report_name',
            'is_signature',
            'fromDate',
            'toDate',
            'is_branch'
        ))->with([
                    'branchName' => $this->branchName,
                    'params' => $this->params,
                ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                $headerRow = 9;
                $firstDataRow = 10;

                $sheet->setShowGridlines(false);

                // Freeze column heading row
                $sheet->freezePane('A10');

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setRight(0.3);

                // School name
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Column heading row
                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'Calibri',
                    ],
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

                // All data font
                $sheet->getStyle("A{$firstDataRow}:{$highestColumn}{$highestRow}")->applyFromArray([
                    'font' => [
                        'size' => 8,
                        'name' => 'Calibri',
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Basic alignment
                $sheet->getStyle("A{$firstDataRow}:G{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("H{$firstDataRow}:H{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setWrapText(true);

                // Amount columns
                if ($highestColumnIndex > 8) {
                    $amountStartColumn = Coordinate::stringFromColumnIndex(9);
                    $amountEndColumn = Coordinate::stringFromColumnIndex($highestColumnIndex - 1);

                    $sheet->getStyle("{$amountStartColumn}{$firstDataRow}:{$amountEndColumn}{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("{$amountStartColumn}{$firstDataRow}:{$amountEndColumn}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // Status column
                $sheet->getStyle("{$highestColumn}{$firstDataRow}:{$highestColumn}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Column widths
                foreach (range(1, $highestColumnIndex) as $columnIndex) {
                    $column = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(7);
                $sheet->getColumnDimension('B')->setWidth(8);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getColumnDimension('H')->setWidth(25);

                // Style branch name / branch total / grand total rows
                for ($row = $firstDataRow; $row <= $highestRow; $row++) {
                    $rowValues = [];

                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = Coordinate::stringFromColumnIndex($col);
                        $rowValues[$col] = trim((string) $sheet->getCell("{$columnLetter}{$row}")->getValue());
                    }

                    $rowText = strtolower(implode(' ', array_filter($rowValues)));

                    $firstCell = trim($rowValues[1] ?? '');
                    $nonEmptyCells = array_filter($rowValues, fn($value) => trim((string) $value) !== '');

                    $isBranchTotalRow = str_contains($rowText, 'branch total');
                    $isGrandTotalRow = str_contains($rowText, 'grand total');

                    /*
                     * Branch name row detection:
                     * - first cell has branch name
                     * - only one cell has value
                     * - not total rows
                     * This works even if you removed "Branch:" text.
                     */
                    $isBranchNameRow = !empty($firstCell)
                        && count($nonEmptyCells) === 1
                        && !$isBranchTotalRow
                        && !$isGrandTotalRow
                        && $row > $headerRow;

                    if ($isBranchNameRow) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 9,
                                'name' => 'Calibri',
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFD9D9D9'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]);

                        $sheet->getRowDimension($row)->setRowHeight(20);
                    }

                    if ($isBranchTotalRow || $isGrandTotalRow) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'Calibri',
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFBFBFBF'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]);
                    }

                    if ($isGrandTotalRow) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                            'borders' => [
                                'top' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]);
                    }
                }

                // Logo
                $logoPath = public_path('assets/images/lynx2.jpg');

                if (file_exists($logoPath)) {
                    $logoColumnIndex = max(1, $highestColumnIndex - 1);
                    $logoColumn = Coordinate::stringFromColumnIndex($logoColumnIndex);

                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('School Logo');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(75);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(10);
                    $drawing->setCoordinates($logoColumn . '1');
                    $drawing->setWorksheet($sheet);
                }

                // Signature line
                $lastDataRow = $sheet->getHighestRow();
                $sigLineRow = $lastDataRow + 2;
                $highestColumnLetter = $sheet->getHighestColumn();

                $sheet->mergeCells("A{$sigLineRow}:{$highestColumnLetter}{$sigLineRow}");

                $signatureLine = new RichText();
                $signatureLine->createText('________________________');
                $signatureLine->createText(str_repeat(' ', $highestColumnIndex * 3));
                $signatureLine->createText('________________________');

                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);

                $sheet->getStyle("A{$sigLineRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$sigLineRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);
            },
        ];
    }
}