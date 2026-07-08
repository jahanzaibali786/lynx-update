<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
class StudentAccountStatementExport implements FromView, WithEvents, WithColumnFormatting
{
    // StudentAccountStatementExport($branches,$students,$class,$receipts,$from_date,$to_date,$selected_branch,$selected_class,$selected_student)
    protected $branches;
    protected $students;
    protected $class;
    protected $accountStatement;
    protected $from_date;
    protected $to_date;
    protected $selected_branch;
    protected $selected_class;
    protected $selected_student;
    protected $std;

    public function __construct(
        $branches,
        $students,
        $class,
        $accountStatement,
        $from_date,
        $to_date,
        $selected_branch,
        $selected_class,
        $selected_student,
        $std,
    ) {
        $this->branches = $branches;
        $this->students = $students;
        $this->class = $class;
        $this->accountStatement = $accountStatement;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->selected_branch = $selected_branch;
        $this->selected_class = $selected_class;
        $this->selected_student = $selected_student;
        $this->std = $std;
    }
    /**
     * Export the employees data to an Excel view.
     */
    public function columnFormats(): array
    {
        return [
            'B' => 'dd-mmm-yyyy', // Date as text to preserve formatting
            'E' => '@', // Challan No as text
            'F' => '@', // Billing Month as text
            'G' => '@', // Debit as text to preserve formatting
            'L' => '#,##0.00', // Credit as text to preserve formatting
            'K' => '#,##0.00', // Balance as text to preserve formatting
        ];
    }
    public function view(): View
    {
        $is_signature = false;
        $is_period = false;
        $is_branch = false;
        // Determine branch name
        $branch = '';
        $report_name = 'Student Account Statement';
        if (!empty($this->branches) && $this->selected_branch && isset($this->branches[$this->selected_branch])) {
            $branch = $this->branches[$this->selected_branch];
        }
        if (!empty($this->students) && $this->selected_student && isset($this->students[$this->selected_student])) {
            $studentName = $this->students[$this->selected_student];
            list($rollNo, $rest) = explode('-', $studentName, 2);
            $rollNo = trim($rollNo);
            list($studentOnly, $fatherPart) = explode('s/d/o', $rest, 2);
            $studentOnly = trim($studentOnly);
            $accountTitle = trim(preg_replace('/^\d+\s*-\s*/', '', $studentName));
        }
        // dd($this->std->branches);
        $branchName = $this->std->branches ? $this->std->branches->name : '';
        // dd($branchName);
        // Pass only the table-related data to the export view
        return view('studentReports.exports.studentaccountstatementexport', [
            'branches' => $this->branches,
            'students' => $this->students,
            'class' => $this->class,
            'accountStatement' => $this->accountStatement,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'branchName' => $branchName,
            'selected_class' => $this->selected_class,
            'selected_student' => $this->selected_student,
            'std' => $this->std,
            'is_signature' => $is_signature,
            'is_period' => true,
            'is_branch' => true,
            'report_name' => $report_name,
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
                $generatedDate = date('d-M-Y H:i:s');
                // Merge the entire row (e.g., row 25)

				$sheet->getHeaderFooter()->setOddFooter("&LGenerated by: " . auth()->user()->name . " &RGenerated on: {$generatedDate} &P of &N");
                $highestColumnLetter = $sheet->getHighestColumn();
                $mergedRange = "A{$sigLineRow}:{$highestColumnLetter}{$sigLineRow}";
                $sheet->mergeCells($mergedRange);
                // Detect branch name rows: A has value, B is empty
                for ($row = 10; $row <= $lastDataRow; $row++) {

                    $cellValue = $sheet->getCell('C' . $row)->getValue();

                    if (stripos($cellValue, 'Closing Balance') !== false) {

                        $sheet->getStyle("A{$row}:{$highestColumnLetter}{$row}")
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 10,
                                    'name' => 'Calibri',
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                ],
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFE0E0E0'], // grey
                                ],
                            ]);
                    }
                }
                for ($row = 12; $row <= $lastDataRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }
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
                // Apply style to entire row 9 Heading Row
                $sheet->getStyle("A11:{$highestColumnLetter}11")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                        'name' => 'calibri',
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
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
                //footer styling for total
                // Find the row with the word "Total"
                $totalRowIndex = null;
                for ($r = 1; $r <= $lastDataRow; $r++) {
                    if (stripos($sheet->getCell("A{$r}")->getValue(), 'total') !== false) {
                        $totalRowIndex = $r;
                        break;
                    }
                }

                if ($totalRowIndex) {
                    $sheet->getStyle("A{$totalRowIndex}:{$highestColumnLetter}{$totalRowIndex}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 8,
                            'name' => 'calibri',
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
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
                }

                // Adjust column widths
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(10);
                $sheet->getColumnDimension('J')->setWidth(12);

                // style col font size 8px and align center
                $sheet->getRowDimension(9)->setRowHeight(33);
                $sheet->getStyle("A9")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("A12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("C12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("D12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                $sheet->getStyle("E12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("F12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle("G12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("H12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("k12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("L12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("M12:M{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A12:{$highestColumnLetter}{$lastDataRow}")->getFont()->setSize(8);
                // add a line for generated date and page number at the bottom of the page in end column or in center column.
                
            },
        ];
    }
}
