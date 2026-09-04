<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RegistrationDetailReportExport implements FromView
{
    protected $studentData;
    protected $branches;
    protected $branchName;
    protected $reportName;
    protected $params;
    protected $branchTotals;
    protected $grandTotal;

    public function __construct($studentData, $branches, $branchName, $reportName, $params, $branchTotals, $grandTotal)
    {
        $this->studentData = $studentData;
        $this->branches = $branches;
        $this->branchName = $branchName;
        $this->reportName = $reportName;
        $this->params = $params;
        $this->branchTotals = $branchTotals;
        $this->grandTotal = $grandTotal;
    }

    public function view(): View
    {
        return view('studentReports.exports.registrationdetailreport_excel', [
            'studentData' => $this->studentData,
            'branches' => $this->branches,
            'branchName' => $this->branchName,
            'reportName' => $this->reportName,
            'params' => $this->params,
            'branchTotals' => $this->branchTotals,
            'grandTotal' => $this->grandTotal,
        ]);
    }
}
