<?php

namespace App\Exports;

use App\Models\StudentEnrollments;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdmissionListingExport implements FromView, WithEvents
{
    protected $request;
    protected $report_name;
    protected $branches;
    protected $branchName;
    protected $params;

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
        $userType = Auth::user()->type;
        $userCreatorId = Auth::user()->creatorId(); // project-specific method
        $userOwnedId = Auth::user()->ownedId(); // project-specific method

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('owned_by', $userOwnedId);
        }

        if ($request->has('branch') && $request->branch != '') {
            if ($request->branch == 'all') {
                $query->whereIn('owned_by', $branches->keys()->except('all'));
            } else {
                $query->where('owned_by', $request->branch);
            }
        }

        if ($request->has('class') && $request->class != '') {
            $query->where('class_id', $request->class);
        }

        if ($request->has('student') && $request->student != '') {
            $query->where('regId', $request->student);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7)
                ? "$currentYear-07-01 00:00:00"
                : date('Y-07-01 00:00:00', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7)
                ? date('Y-06-30 23:59:59', strtotime('+1 year'))
                : "$currentYear-06-30 23:59:59";
            $request->merge(['date_from' => substr($dateFrom, 0, 10)]);
            $request->merge(['date_to' => substr($dateTo, 0, 10)]);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $query->whereBetween('adm_date', [$dateFrom, $dateTo]);
        }

        $studentData = $query->get()->groupBy('owned_by');
        $std_ids = $query->pluck('regId')->toArray();

        $heads = DB::table('challan_heads')
            ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->join('fee_heads', 'challan_heads.head_id', '=', 'fee_heads.id')
            ->whereIn('challans.student_id', $std_ids)
            ->whereRaw('LOWER(challans.challan_type) LIKE ?', [strtolower('%admission%')])
            ->distinct()
            ->select('challan_heads.head_id as id', 'fee_heads.fee_head')
            ->get();

        $challansData = Challans::whereIn('student_id', $std_ids)
            ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
            ->with(['heads.feehead'])
            ->get()
            ->keyBy('student_id');

        $branchTotals = [];
        $grandTotal = 0;
        $studentChallanData = [];

        foreach ($studentData as $branchId => $students) {
            $branchTotal = 0;
            foreach ($students as $student) {
                $studentTotal = 0;
                $studentRegNo = @$student->StudentRegistration->reg_no;
                $challanHeads = [];
                if (isset($challansData[$studentRegNo])) {
                    $challan = $challansData[$studentRegNo];
                    foreach ($challan->heads as $head) {
                        $studentTotal += (int) $head->price ?? 0;
                        $challanHeads[] = [
                            'name' => $head->feehead->fee_head,
                            'amount' => $head->price,
                            'head_id' => $head->head_id,
                        ];
                    }
                    $studentChallanData[$studentRegNo] = [
                        'challan_no' => $challan->challanNo,
                        'challan_id' => $challan->id,
                        'heads' => $challanHeads,
                        'total' => $studentTotal
                    ];
                } else {
                    $studentChallanData[$studentRegNo] = [
                        'challan_no' => '',
                        'challan_id' => '',
                        'heads' => [],
                        'total' => 0
                    ];
                }
                $branchTotal += $studentTotal;
            }
            $branchTotals[$branchId] = $branchTotal;
            $grandTotal += $branchTotal;
        }

        $classes = Classes::pluck('name', 'id');
        $classes->prepend('Select Class', '');
        $student = [];
        $report_name = $this->report_name;
        $is_signature = false;
        $is_period = true;
        $is_branch = true;
        return view('student.exports.admission_listing', [
            'studentData' => $studentData,
            'student' => $student,
            'heads' => $heads,
            'classes' => $classes,
            'branchTotals' => $branchTotals,
            'grandTotal' => $grandTotal,
            'studentChallanData' => $studentChallanData,
            'branchName' => $this->branchName,
            'branches' => $this->branches,
            'request' => $this->request,
            'is_signature' => $is_signature,
            'is_period' => $is_period,
            'report_name' => $report_name,
            'params' => $this->params,
            'is_branch' => $is_branch,
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
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 7);
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // Optional: Margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                // $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

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
                // Merge the entire row (e.g., row 25)
                $highestColumnLetter = $sheet->getHighestColumn();
                $mergedRange = "A{$sigLineRow}:{$highestColumnLetter}{$sigLineRow}";
                $sheet->mergeCells($mergedRange);

                // Build signature line text with left and right alignment
                $signatureLine = new RichText();
                $signatureLine->createText('________________________');

                // Add enough space in between to push second line to right side
                $colCount = Coordinate::columnIndexFromString($highestColumnLetter);
                $space = str_repeat(' ', $colCount * 3); // Adjust spacing depending on column width
                $signatureLine->createText($space);

                $signatureLine->createText('________________________');

                // Set into merged cell
                $sheet->setCellValue("A{$sigLineRow}", $signatureLine);
                $sheet->getStyle("A{$sigLineRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$sigLineRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_DISTRIBUTED);

                // for heading row
                $highestColumnLetter = $sheet->getHighestColumn();
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'name' => 'Edwardian Script ITC', // Will only work if the font is installed on the system
                    ],
                ]);
                // Apply style to entire Heading Row
                $sheet->getStyle("A9:{$highestColumnLetter}9")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,

                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // Black
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFBFBFBF', // Light gray
                        ],
                    ],
                ]);
                

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('H')->setWidth(20);

                // style col font size 8px and align center
                $sheet->getStyle("A10:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H10:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("I10:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I10:{$highestColumnLetter}{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("{$highestColumnLetter}10:{$highestColumnLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A10:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
            },
        ];
    }
    }
