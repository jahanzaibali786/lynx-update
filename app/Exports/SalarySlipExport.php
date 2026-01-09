<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SalarySlipExport implements FromView, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the Blade view for export.
     */
    public function view(): View
    {
        $selectedMonthYear = $this->data['requestdata']['date'];
        $selectedMonthYear = date('F Y', strtotime($selectedMonthYear));
        $report_name = __('Employee Pay Slip Report for the month of ' . $selectedMonthYear);
        return view('employee.monthly_salary_attendance.salary_slip_excel', [
            'datas' => $this->data['datas'],         // Employee/salary data
            'salaryHeads' => $this->data['salaryHeads'],
            'requestdata' => $this->data['requestdata'],
            'selectedMonthYear' => $selectedMonthYear,
            'report_name' => $report_name
        ]);

        // return view('employee.monthly_salary_attendance.salary_slip_excel', [
        //     'datas' => $this->data,
        //     'report_name' => $report_name,
        // ]);
    }

    /**
     * Apply styling, page setup, logo, etc.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Page layout
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setBottom(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setRight(0.5);

                $sheet->setShowGridlines(false);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                $sheet->getColumnDimension('A')->setWidth(1.86);
                $sheet->getColumnDimension('B')->setWidth(1.86);
                $sheet->getColumnDimension('C')->setWidth(23.29);
                $sheet->getColumnDimension('D')->setWidth(13.57);
                $sheet->getColumnDimension('E')->setWidth(12.29);
                $sheet->getColumnDimension('F')->setWidth(1.86);
                $sheet->getColumnDimension('G')->setWidth(18.57);
                $sheet->getColumnDimension('H')->setWidth(13.29);
                $sheet->getColumnDimension('I')->setWidth(12.57);
                $sheet->getColumnDimension('J')->setWidth(1.86);
                $sheet->getColumnDimension('K')->setWidth(19.29);
                $sheet->getColumnDimension('L')->setWidth(1.86);
                $sheet->getColumnDimension('M')->setWidth(23.57);
                $sheet->getColumnDimension('N')->setWidth(1.86);

                $sheet->setShowGridlines(false);
                $sheet->getHeaderFooter()->setOddFooter('&LGenerated on &D &T&RPage &P of &N');

                // Logo (grayscale)
                // $logoPath = public_path('assets/images/lynxlogo(2).png');
                // $employees = $this->data['datas'];  // Array of employee data
                // $rowsPerSlip = 31; // adjust as needed depending on how many rows each payslip uses
    

                // foreach ($employees as $index => $employee) {
                //     $drawing = new Drawing();
                //     $drawing->setName('Company Logo');
                //     $drawing->setDescription('Company Logo (Grayscale)');
                //     $drawing->setPath($logoPath);
                //     $drawing->setResizeProportional(false);
                //     $drawing->setWidth(100);
                //     $drawing->setOffsetX(25);
                //     $drawing->setHeight(75);

                //     // $drawing->setWidth(50);

                //     // Calculate vertical position
                //     $rowOffset = ($index * $rowsPerSlip) + 1;
                //     $drawing->setCoordinates('C' . $rowOffset); // e.g., C1, C31, C61...
    
                //     $drawing->setWorksheet($sheet);
                // }
            },
        ];
    }
}
