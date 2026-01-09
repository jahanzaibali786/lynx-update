<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
class chartofaccountexport implements FromView ,WithEvents
{
    protected $chartAccounts;    
    protected $filter;
    protected $companyName;

    public function __construct($chartAccounts, $filter, $companyName){
        $this->chartAccounts = $chartAccounts;
        $this->filter = $filter;
        $this->companyName = $companyName;
    }
    /**
     * Export the employees data to an Excel view.
     */
    public function view(): View
    {
        // dd($this->data);
        // Pass only the table-related data to the export view
        return view('chartOfAccount.excel', [
            'chartAccounts' => $this->chartAccounts,
            'filter' => $this->filter,
            'companyName' => $this->companyName,
            'reportName' => 'Chart of Accounts'
          
        ]);
    }

    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setDescription('School Logo');
    //     $drawing->setPath(public_path('assets/images/lynx2.jpg')); // Path to your image
    //     $drawing->setOffsetX(20);
    //     $drawing->setOffsetY(20);
    //     $drawing->setHeight(75); // Set height in pixels
    //     $letter = 'A';

        
    //     // Set the coordinates for the Excel sheet using the calculated column letter
    //     $drawing->setCoordinates($letter.'1');

    //     return $drawing;
    // }

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
