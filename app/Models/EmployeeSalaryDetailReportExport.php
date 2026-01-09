<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class EmployeeSalaryDetailReportExport implements FromView, WithDrawings ,WithEvents
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        // Pass only the table-related data to the export view
        return view('employees.exports.employee_salary_detail_report_table', [
            'employees' => $this->employees,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('School Logo');
        $drawing->setPath(public_path('assets/images/lynx2.jpg')); // Path to your image
        $drawing->setOffsetX(20);
        $drawing->setOffsetY(20);
        $drawing->setHeight(75); // Set height in pixels
        $drawing->setCoordinates('B1'); // Set position on the Excel sheet (e.g., top left)

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                //  // Apply a background color to the first three rows
                //  foreach (range(1, 3) as $row) {
                //     // Set a light gray background for better visibility
                //     $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                //     // $sheet->getStyle("A{$row}:E{$row}")->getFill(); // Light gray background
                // }
            }
        ];
    }
}
