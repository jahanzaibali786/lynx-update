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

class AverageMonthlyMatrixReportExport implements FromView, WithEvents
{
    public function __construct(
        protected $branches,
        protected array $reportRows,
        protected array $months,
        protected array $yearGroups,
        protected array $grandTotals,
        protected string $branchName,
        protected string $reportName,
        protected string $fromLabel,
        protected string $toLabel,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
        protected ?string $yearLabel = null
    ) {
    }

    public function view(): View
    {
        /*
         * Fixed columns:
         * 1 = SR
         * 2 = BRANCH NAME
         *
         * Per month:
         * 1 = AVG.FEE
         * 2 = TOTAL STUDENTS
         * 3 = DISCOUNT%
         */
        $colspan = 2 + (count($this->months) * 3);

        return view('studentReports.exports.average_monthly_report', [
            'branches' => $this->branches,
            'reportRows' => $this->reportRows,
            'months' => $this->months,
            'yearGroups' => $this->yearGroups,
            'grandTotals' => $this->grandTotals,

            'branchName' => $this->branchName,
            'branch' => $this->branchName,

            'reportName' => $this->reportName,
            'report_name' => $this->reportName,

            'fromLabel' => $this->fromLabel,
            'toLabel' => $this->toLabel,

            'date_from' => $this->fromDate,
            'date_to' => $this->toDate,

            'is_period' => true,
            'is_signature' => false,

            'colspan' => $colspan,
            'yearLabel' => $this->yearLabel,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                 * ---------------------------------------------------------
                 * EXACT REPORT COLUMN COUNT
                 * ---------------------------------------------------------
                 *
                 * 2 fixed columns:
                 * SR + BRANCH NAME
                 *
                 * 3 columns per month:
                 * AVG.FEE + TOTAL STUDENTS + DISCOUNT%
                 */
                $monthCount = count($this->months);

                $reportColumnCount = 2 + ($monthCount * 3);

                $lastReportColumn =
                    Coordinate::stringFromColumnIndex(
                        $reportColumnCount
                    );

                /*
                 * ---------------------------------------------------------
                 * REMOVE EXTRA COLUMNS
                 * ---------------------------------------------------------
                 *
                 * The common report header can sometimes force Excel to
                 * generate more columns than this report actually needs.
                 *
                 * Example:
                 *
                 * One month requires:
                 * A = SR
                 * B = BRANCH NAME
                 * C = AVG.FEE
                 * D = TOTAL STUDENTS
                 * E = DISCOUNT%
                 *
                 * If common header creates F/G etc., remove them.
                 */
                $currentHighestColumn =
                    $sheet->getHighestColumn();

                $currentHighestColumnIndex =
                    Coordinate::columnIndexFromString(
                        $currentHighestColumn
                    );

                if (
                    $currentHighestColumnIndex
                    > $reportColumnCount
                ) {
                    $firstExtraColumn =
                        Coordinate::stringFromColumnIndex(
                            $reportColumnCount + 1
                        );

                    $extraColumnCount =
                        $currentHighestColumnIndex
                        - $reportColumnCount;

                    $sheet->removeColumn(
                        $firstExtraColumn,
                        $extraColumnCount
                    );
                }

                /*
                 * ---------------------------------------------------------
                 * PAGE SETTINGS
                 * ---------------------------------------------------------
                 */
                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setPaperSize(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                    );

                $sheet->getPageSetup()
                    ->setFitToPage(true);

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->getPageSetup()
                    ->setFitToHeight(0);

                $sheet->setShowGridlines(false);

                $sheet->getPageMargins()
                    ->setTop(0.5);

                $sheet->getPageMargins()
                    ->setBottom(0.5);

                $sheet->getPageMargins()
                    ->setLeft(0.5);

                $sheet->getPageMargins()
                    ->setRight(0.5);

                /*
                 * Print area must stop exactly at the last
                 * actual report column.
                 */
                $sheet->getPageSetup()
                    ->setPrintArea(
                        'A1:'
                        . $lastReportColumn
                        . $sheet->getHighestRow()
                    );

                /*
                 * ---------------------------------------------------------
                 * LOGO
                 * ---------------------------------------------------------
                 */
                $originalPath =
                    public_path(
                        'assets/images/lynx2.jpg'
                    );

                $tmpPath = null;

                if (
                    file_exists($originalPath)
                    && function_exists(
                        'imagecreatefromjpeg'
                    )
                ) {
                    $img =
                        imagecreatefromjpeg(
                            $originalPath
                        );

                    if ($img) {
                        imagefilter(
                            $img,
                            IMG_FILTER_GRAYSCALE
                        );

                        $tmpPath =
                            sys_get_temp_dir()
                            . DIRECTORY_SEPARATOR
                            . 'logo_gray.png';

                        imagepng(
                            $img,
                            $tmpPath
                        );

                        imagedestroy($img);
                    }
                }

                if (! $tmpPath) {
                    $tmpPath = $originalPath;
                }

                if (
                    $tmpPath
                    && file_exists($tmpPath)
                ) {
                    $drawing = new Drawing();

                    $drawing->setName('Logo');

                    $drawing->setDescription(
                        'School Logo (grayscale)'
                    );

                    $drawing->setPath(
                        $tmpPath
                    );

                    $drawing->setHeight(75);

                    $drawing->setOffsetX(10);

                    $drawing->setOffsetY(10);

                    /*
                     * Logo stays within actual report area.
                     */
                    $drawing->setCoordinates(
                        $lastReportColumn . '1'
                    );

                    $drawing->setWorksheet(
                        $sheet
                    );
                }

                /*
                 * ---------------------------------------------------------
                 * AUTO SIZE
                 * ---------------------------------------------------------
                 *
                 * Only resize actual report columns.
                 */
                for (
                    $col = 1;
                    $col <= $reportColumnCount;
                    $col++
                ) {
                    $columnLetter =
                        Coordinate::stringFromColumnIndex(
                            $col
                        );

                    $sheet
                        ->getColumnDimension(
                            $columnLetter
                        )
                        ->setAutoSize(true);
                }

                /*
                 * ---------------------------------------------------------
                 * FIND REPORT HEADER ROW
                 * ---------------------------------------------------------
                 */
                $headerRow = null;

                for (
                    $row = 1;
                    $row <= 15;
                    $row++
                ) {
                    $cellValue =
                        $sheet
                            ->getCell(
                                "A{$row}"
                            )
                            ->getValue();

                    $cellValue =
                        trim(
                            (string) $cellValue
                        );

                    if (
                        in_array(
                            $cellValue,
                            [
                                'Sr#',
                                'SR',
                                'Sr',
                            ],
                            true
                        )
                    ) {
                        $headerRow = $row;
                        break;
                    }
                }

                if ($headerRow) {

                    /*
                     * Repeat the main table heading
                     * when printing multiple pages.
                     */
                    $sheet
                        ->getPageSetup()
                        ->setRowsToRepeatAtTopByStartAndEnd(
                            $headerRow,
                            $headerRow + 2
                        );

                    /*
                     * -----------------------------------------------------
                     * FIRST HEADER ROW
                     * -----------------------------------------------------
                     */
                    $sheet
                        ->getStyle(
                            "A{$headerRow}:{$lastReportColumn}{$headerRow}"
                        )
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 8,
                                'name' => 'Calibri',
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,

                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,

                                    'color' => [
                                        'argb' =>
                                            'FF000000',
                                    ],
                                ],
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'argb' =>
                                        'FFBFBFBF',
                                ],
                            ],
                        ]);

                    /*
                     * -----------------------------------------------------
                     * MONTH + CHILD HEADER ROWS
                     * -----------------------------------------------------
                     */
                    $sheet
                        ->getStyle(
                            'A'
                            . ($headerRow + 1)
                            . ':'
                            . $lastReportColumn
                            . ($headerRow + 2)
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    /*
                     * First normal data row.
                     */
                    $dataStart =
                        $headerRow + 3;

                    $dataEnd =
                        $sheet->getHighestRow();

                    /*
                     * -----------------------------------------------------
                     * MONTH COLUMNS
                     * -----------------------------------------------------
                     *
                     * First month:
                     *
                     * C = AVG.FEE
                     * D = TOTAL STUDENTS
                     * E = DISCOUNT%
                     *
                     * Second month:
                     *
                     * F = AVG.FEE
                     * G = TOTAL STUDENTS
                     * H = DISCOUNT%
                     */
                    for (
                        $i = 0;
                        $i < $monthCount;
                        $i++
                    ) {
                        $amountColumn =
                            Coordinate::stringFromColumnIndex(
                                3 + ($i * 3)
                            );

                        $studentColumn =
                            Coordinate::stringFromColumnIndex(
                                4 + ($i * 3)
                            );

                        $percentColumn =
                            Coordinate::stringFromColumnIndex(
                                5 + ($i * 3)
                            );

                        /*
                         * AVG.FEE
                         */
                        $sheet
                            ->getStyle(
                                "{$amountColumn}{$dataStart}:{$amountColumn}{$dataEnd}"
                            )
                            ->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_RIGHT
                            );

                        $sheet
                            ->getStyle(
                                "{$amountColumn}{$dataStart}:{$amountColumn}{$dataEnd}"
                            )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '#,##0.00'
                            );

                        /*
                         * TOTAL STUDENTS
                         */
                        $sheet
                            ->getStyle(
                                "{$studentColumn}{$dataStart}:{$studentColumn}{$dataEnd}"
                            )
                            ->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_CENTER
                            );

                        $sheet
                            ->getStyle(
                                "{$studentColumn}{$dataStart}:{$studentColumn}{$dataEnd}"
                            )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '0'
                            );

                        /*
                         * DISCOUNT %
                         */
                        $sheet
                            ->getStyle(
                                "{$percentColumn}{$dataStart}:{$percentColumn}{$dataEnd}"
                            )
                            ->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_RIGHT
                            );

                        $sheet
                            ->getStyle(
                                "{$percentColumn}{$dataStart}:{$percentColumn}{$dataEnd}"
                            )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '0.00"%"'
                            );
                    }
                }

                /*
                 * ---------------------------------------------------------
                 * LYNX TOTAL ROW
                 * ---------------------------------------------------------
                 */
                $highestRow =
                    $sheet->getHighestRow();

                for (
                    $row = 1;
                    $row <= $highestRow;
                    $row++
                ) {
                    $valueA =
                        trim(
                            (string) $sheet
                                ->getCell(
                                    "A{$row}"
                                )
                                ->getValue()
                        );

                    $valueB =
                        trim(
                            (string) $sheet
                                ->getCell(
                                    "B{$row}"
                                )
                                ->getValue()
                        );

                    if (
                        $valueA === 'LYNX TOTAL'
                        || $valueB === 'LYNX TOTAL'
                    ) {
                        $sheet
                            ->getStyle(
                                "A{$row}:{$lastReportColumn}{$row}"
                            )
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 8,
                                    'name' =>
                                        'Calibri',
                                ],

                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' =>
                                            Border::BORDER_THIN,

                                        'color' => [
                                            'argb' =>
                                                'FF000000',
                                        ],
                                    ],
                                ],

                                'fill' => [
                                    'fillType' =>
                                        Fill::FILL_SOLID,

                                    'startColor' => [
                                        'argb' =>
                                            'FFBFBFBF',
                                    ],
                                ],
                            ]);

                        break;
                    }
                }
            },
        ];
    }
}