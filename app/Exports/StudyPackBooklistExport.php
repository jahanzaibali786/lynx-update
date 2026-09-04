<?php

namespace App\Exports;

use App\Models\StudyPackChallans;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudyPackBooklistExport implements FromView, WithEvents
{
    protected $studypacks;
    protected $branches;
    protected $branchName;
    protected $request;

    public function __construct($studypacks, $branches, $branchName, $request)
    {
        $this->studypacks = $studypacks;
        $this->branches = $branches;
        $this->branchName = $branchName;
        $this->request = $request;
    }

    public function view(): View
    {
        return view('student.exports.studypack_booklist', [
            'studypacks' => $this->studypacks,
            'branches' => $this->branches,
            'branchName' => $this->branchName,
            'request' => $this->request,
            'report_name' => 'StudyPack Booklist',
            'is_signature' => false,
            'is_period' => false,
            'is_branch' => true,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->setShowGridlines(false);

                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                $lastDataRow = $sheet->getHighestRow();
                $highestColumnLetter = $sheet->getHighestColumn();

                for ($row = 10; $row <= $lastDataRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if ($cellValue && stripos($cellValue, 'class') !== false && stripos($cellValue, 'name') === false) {
                        $sheet->getStyle("A{$row}:{$highestColumnLetter}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 9,
                                'name' => 'Calibri',
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFE0E0E0'],
                            ],
                        ]);
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(10);
            },
        ];
    }
}
