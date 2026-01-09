<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class SessionMonthWiseReportExport implements FromView, WithDrawings ,WithEvents
{
    protected $branches;
    protected $classes;
    protected $months;
    protected $year;
    protected $month_f;
    protected $month_t;
    protected $registrationCounts;
    protected $enrollmentCounts;
    protected $withdrawalCounts;
    protected $strengthCounts;
    protected $totalRegistrations;
    protected $totalEnrollments;
    protected $totalWithdrawals;
    protected $totalStrength;

    public function __construct($branches, $classes,$months, $year,$month_f,$month_t, $registrationCounts,$strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength)
    {
        $this->branches = $branches;
        $this->classes = $classes;
        $this->months = $months;
        $this->year = $year;
        $this->month_f = $month_f;
        $this->month_t = $month_t;
        $this->registrationCounts = $registrationCounts;
        $this->enrollmentCounts = $enrollmentCounts;
        $this->withdrawalCounts = $withdrawalCounts;
        $this->strengthCounts = $strengthCounts;
        $this->totalRegistrations = $totalRegistrations;
        $this->totalEnrollments = $totalEnrollments;
        $this->totalWithdrawals = $totalWithdrawals;
        $this->totalStrength = $totalStrength;

    }
    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        // Pass only the table-related data to the export view
        return view('studentReports.exports.monthsessionwise', [
            'branches' => $this->branches,
            'classes' => $this->classes,
            'months' => $this->months,
            'year' => $this->year,
            'month_f' => $this->month_f,
            'month_t' => $this->month_t,
            'registrationCounts' => $this->registrationCounts,
            'registrationCounts' => $this->registrationCounts,
            'enrollmentCounts' => $this->enrollmentCounts,
            'withdrawalCounts' => $this->withdrawalCounts,
            'strengthCounts' => $this->strengthCounts,
            'totalRegistrations' => $this->totalRegistrations,
            'totalEnrollments' => $this->totalEnrollments,
            'totalWithdrawals' => $this->totalWithdrawals,
            'totalStrength' => $this->totalStrength,
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('School Logo');
        $drawing->setPath(public_path('assets/images/lynx2.jpg')); // Path to your image
        $drawing->setOffsetX(20);
        $drawing->setOffsetY(10);
        $drawing->setHeight(70); // Set height in pixels

        $number =$this->classes->count() / 3;
        // Convert number to Excel column letters (e.g., 27 -> AA)
        if($number >= 1){
            $letter = $this->numberToAlphabet($number);
        }else{
            $letter = 'A';
        }
        // Set the coordinates for the Excel sheet using the calculated column letter
        $drawing->setCoordinates($letter.'1');

        return $drawing;
    }

    public function numberToAlphabet($number) {
        $alphabet = '';
        while ($number > 0) {
            $mod = ($number - 1) % 26;
            if ($mod >= 1) {
                $alphabet = chr(65 + $mod) . $alphabet;
                $number = (int)(($number - 1) / 26);
            } else {
                $alphabet ='A';
            }
        }
        return $alphabet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
            }
        ];
    }
}
