<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeLoanReportExport;
use App\Exports\EmployeeAnnualSrsReportExport;
use App\Exports\EmployeeInsuranceReportExport;
use App\Exports\EmployeeTaxDedReportExport;
use App\Exports\PeriodWiseEmployeePayrollExport;
use App\Exports\PeriodWisePayrollExport;
use App\Exports\SalaryComparisonReportExport;
use App\Exports\EmployeeRetirementReportExport;
use App\Exports\SingleEmpSecReportExport;
use App\Exports\EmployeeBirthdayReportExport;
use App\Exports\EmployeeEducationReportExport;
use App\Exports\EmployeeWithoutPayLeaveReportExport;
use App\Exports\EmployeeSecReportExport;
use App\Exports\EmployeeEmploymentHistoryExport;
use App\Exports\EmployeeMonthlyReconsilationExport;
use App\Exports\EmployeeEmploymentHistoryReportExport;
use App\Models\Designation;
use App\Models\Department;
use App\Models\EmpChildrens;
use App\Models\EmployeePayscaleDetail;
use App\Models\EmpEducation;
use App\Models\Employee;
use App\Models\HealthInsurance;
use App\Models\EmployeeEOBIReportExport;
use App\Models\EmployeeLeaves;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySecurityDeductionReportExport;
use App\Models\EmployeeRejoin;
use App\Models\EmployeeTransfer;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\EmployeePessiReportExport;
use App\Exports\EmployeeTransferReportExport;
use App\Exports\EmployeeLeaveReportExport;
use App\Exports\EmployeeProfileReportExport;
use App\Exports\EmployeeStaffChildReport;



class EmployeeReportsController extends Controller
{
    public function monthlysecurtiyded(Request $request)
    {
        // dd('');
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $month = $request->has('month') ? $request->month : date('m');
        $year = $request->has('year') ? $request->year : date('Y');
        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }
        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);
        $branchessalaries = $query->distinct('owned_by')->pluck('owned_by');
        $reportData = [];
        foreach ($branchessalaries as $branchId) {
            $branchName = User::find($branchId)->name;
            $branchQuery = $query->where('owned_by', $branchId);
            $branchData = $branchQuery->get();
            $reportData[$branchName] = $branchData;
        }
        $year = intval($year);
        $month = intval($month);
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new EmployeeMonthlySecurityDeductionReportExport($reportData, $month, $year), 'employee_monthly_security_deduction.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeeMonthlySecurityDeductionReportExport($reportData, $month, $year), 'employee_monthly_security_deduction.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        $reportData = collect($reportData);
        // if ($request->has('print') && $request->print == 'pdf') {
        //     $report_name = 'Employee Security Deduction Report for the month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;
        //     $pdf = new Dompdf();
        //     $html = view('employee.reports.monthly_sec_ded_report', compact('branches', 'reportData', 'month', 'year'))->render();
        //     $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $html = '<html><head>
        //         <style>
        //             @page {
        //                 margin-top: 100px;
        //                 margin-bottom: 100px;
        //             }
        //             /* Only the footer is fixed */
        //             .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        //         </style>
        //         </head><body>
        //         ' . $headerHtml . '
        //         <div class="footer">' . $footerHtml . '</div>
        //         ' . $html . '
        //         </body></html>';
        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($html);
        //     $dompdf->setPaper('A4', 'portrait');
        //     $dompdf->render();
        //     return $dompdf->stream('employee_security_deduction_report.pdf', ['Attachment' => false]);
        // }
        return view('employee.reports.monthly_sec_ded', compact('branches', 'reportData', 'month', 'year'));
    }
    public function monthlysecurtiydedReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $month = $request->has('month') ? $request->month : date('m');
        $year = $request->has('year') ? $request->year : date('Y');
        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }

        if (empty($request->input('start_date')) || empty($request->input('end_date'))) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
        }
        $query->whereBetween('salary_date', [$request->input('start_date'), $request->input('end_date')]);

        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);
        $branchessalaries = $query->distinct('owned_by')->pluck('owned_by');
        $reportData = [];
        foreach ($branchessalaries as $branchId) {
            $branchName = User::find($branchId)->name;
            $branchQuery = $query->where('owned_by', $branchId);
            $branchData = $branchQuery->get();
            $reportData[$branchName] = $branchData;
        }
        $reportData = collect($reportData);

        $year = intval($year);
        $month = intval($month);
        if ($month < 1 || $month > 12 || $year < 1900 || $year > now()->year) {
            throw new \Exception('Invalid month or year format.');
        }
        $report_name = 'Employee Security Deduction Report for the month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;

        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('employee.reports.monthly_sec_ded_report', compact('branches', 'reportData', 'month', 'year'))->render();
            $headerHtml = view('invoice.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('invoice.report.pdf.footer')->render();
            $html = '<html><head>
                <style>
                    @page {
                        margin-top: 100px;
                        margin-bottom: 100px;
                    }
                    /* Only the footer is fixed */
                    .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
                </style>
                </head><body>
                ' . $headerHtml . '
                <div class="footer">' . $footerHtml . '</div>
                ' . $html . '
                </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('employee_security_deduction_report.pdf', ['Attachment' => false]);
        }

        return view('employee.reports.monthly_sec_ded', compact('branches', 'reportData', 'month', 'year'));
    }
    public function employeeEobiReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $monthYear = $request->input('month');
        if ($monthYear) {
            [$year, $month] = explode('-', $monthYear);
        } else {
            $year = date('Y');
            $month = date('m');
        }

        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }
        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);


        $branchesData = $query->distinct('owned_by')->pluck('owned_by');
        $reportData = [];
        foreach ($branchesData as $branchId) {
            $branchName = User::find($branchId)->name;

            $branchQuery = (clone $query)->where('owned_by', $branchId);
            $branchData = $branchQuery->get();

            $reportData[$branchName] = $branchData;
        }
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new EmployeeEOBIReportExport($reportData), 'employee_EOBI_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeeEOBIReportExport($reportData), 'employee_EOBI_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        $reportData = collect($reportData);
        if ($request->filled('is_print') && $request->is_print == 1) {
            $bodyHtml = view('employee.reports.employeeEobiReportPrint', compact('branches', 'reportData', 'month', 'year'))->render();
            $headerHtml = view('employee.report.pdf.header')->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $finalHtml = '
            <html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -60px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
            </style>
            </head>
            <body>
                ' . $headerHtml . '
                <div class="footer">' . $footerHtml . '</div>
                ' . $bodyHtml . '
            </body></html>';
            // dd($finalHtml);

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($finalHtml);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('employee_eobi_report.pdf', ['Attachment' => false]);
        }
        return view('employee.reports.employeeEobiReport', compact('branches', 'reportData', 'month', 'year'));
    }
    public function employeeEobiPdfReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $monthYear = $request->input('month');

        if ($monthYear) {
            [$year, $month] = explode('-', $monthYear);
        } else {
            $year = date('Y');
            $month = date('m');
        }

        if (!empty($year) && !empty($month)) {
            $dateFrom = "$year-$month-01";
            $dateTo = date('Y-m-t', strtotime($dateFrom));
        } else {
            $currentYear = date('Y');
            $currentMonth = date('m');

            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
        }
        $request->merge(['start_date' => $dateFrom]);
        $request->merge(['end_date' => $dateTo]);
        $query->whereBetween('salary_date', [$dateFrom, $dateTo]);
        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }
        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);
        $branchesData = $query->distinct('owned_by')->pluck('owned_by');

        $reportData = [];
        foreach ($branchesData as $branchId) {
            $branchName = User::find($branchId)->name;

            $branchQuery = (clone $query)->where('owned_by', $branchId);
            $branchData = $branchQuery->get();

            $reportData[$branchName] = $branchData;
        }
        $reportData = collect($reportData);

        $year = intval($year);
        $month = intval($month);
        if ($month < 1 || $month > 12 || $year < 1900 || $year > now()->year) {
            throw new \Exception('Invalid month or year format.');
        }
        $report_name = 'EOBI Contribution for the Month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;

        $pdf = new Dompdf();
        $html = view('employee.reports.employeeEobiReportPdf', compact('branches', 'reportData', 'month', 'year', 'report_name'))->render();
        $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            ' . $headerHtml . '
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
                </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function employeepessiReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $monthYear = $request->input('month');
        if ($monthYear) {
            [$year, $month] = explode('-', $monthYear);
        } else {
            $year = date('Y');
            $month = date('m');
        }

        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }
        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);
        $branchesData = $query->distinct('owned_by')->pluck('owned_by');
        $reportData = [];

        foreach ($branchesData as $branchId) {
            $branchName = User::find($branchId)->name;

            $branchQuery = (clone $query)->where('owned_by', $branchId);
            $branchData = $branchQuery->get();

            $reportData[$branchName] = $branchData;
        }

        $reportData = collect($reportData);
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Pessi Contribution for the Month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;
            $pdf = new Dompdf();
            $html = view('employee.reports.employeepessireportPdf', compact('branches', 'reportData', 'month', 'year', 'report_name'))->render();
            $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            ' . $headerHtml . '
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
                </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('employee_pessi_report.pdf', ['Attachment' => false]);
        }

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new EmployeePessiReportExport($reportData, $branches, Designation::get(), "Employee Pessi Report"), 'employee_pessi_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeePessiReportExport($reportData, $branches, Designation::get(), "Employee Pessi Report"), 'employee_pessi_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.employeepessireport', compact('branches', 'reportData', 'month', 'year'));
    }
    public function employeepessiReportPdf(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
        }
        $monthYear = $request->input('month');
        if ($monthYear) {
            [$year, $month] = explode('-', $monthYear);
        } else {
            $year = date('Y');
            $month = date('m');
        }
        if (!empty($year) && !empty($month)) {
            $dateFrom = "$year-$month-01";
            $dateTo = date('Y-m-t', strtotime($dateFrom));
        } else {
            $currentYear = date('Y');
            $currentMonth = date('m');

            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
        }
        $request->merge(['start_date' => $dateFrom]);
        $request->merge(['end_date' => $dateTo]);
        $query->whereBetween('salary_date', [$dateFrom, $dateTo]);

        $branch = $request->has('branches') ? $request->branches : '';
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }
        $query->whereYear('salary_date', $year)
            ->whereMonth('salary_date', $month);
        $branchesData = $query->distinct('owned_by')->pluck('owned_by');
        $reportData = [];
        foreach ($branchesData as $branchId) {
            $branchName = User::find($branchId)->name;

            $branchQuery = (clone $query)->where('owned_by', $branchId);
            $branchData = $branchQuery->get();

            $reportData[$branchName] = $branchData;
        }
        $reportData = collect($reportData);
        $year = intval($year);
        $month = intval($month);
        if ($month < 1 || $month > 12 || $year < 1900 || $year > now()->year) {
            throw new \Exception('Invalid month or year format.');
        }
        $report_name = 'pessi Contribution for the Month of' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;
        $report_name = 'EOBI Contribution for the Month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;

        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('employee.reports.employeepessireportPdf', compact('branches', 'reportData', 'month', 'year'))->render();
            $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -60px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
                /* Only the footer is fixed */
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            ' . $headerHtml . '
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('employee_pessi_report.pdf', ['Attachment' => false]);
        }
        return view('employee.reports.employeepessireport', compact('branches', 'reportData', 'month', 'year'));
    }
    public function periodwisepayroll(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $branch = $request->has('branches') ? $request->branches : '';
        $employee_id = $request->input('employee_id'); // Get selected employee ID
        $dateFrom = $request->input('datefrom');
        $dateTo = $request->input('dateto');
        $grandTotal = [];
        $totalByMonth = [];
        $totalByHead = [];
        $branchTotals = [];

        if ($dateFrom && $dateTo) {
            [$fromYear, $fromMonth] = explode('-', $dateFrom);
            [$toYear, $toMonth] = explode('-', $dateTo);

            $startDate = date('Y-m-01', strtotime($dateFrom));
            $endDate = Carbon::createFromFormat('Y-m', $dateTo)->endOfMonth();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $fromYear = $startDate->year;
            $fromMonth = $startDate->month;
            $toYear = $endDate->year;
            $toMonth = $endDate->month;
        }

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
            $query = EmployeeMonthlySalary::with('employee', 'salary_heads')->where('created_by', $userCreatorId);
            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');

        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', '');
            $query = EmployeeMonthlySalary::with('employee', 'salary_heads')->where('owned_by', $userOwnedId);
            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
        }

        // Add branch filter
        if ($branch != '') {
            $query->where('owned_by', $branch);
        }

        // Add employee filter if specific employee is selected
        if (!empty($employee_id)) {
            $query->where('employee_id', $employee_id);
        }

        $salaries = $query->whereBetween('salary_date', [$startDate, $endDate])->get();
        $monthlyData = $salaries->groupBy(function ($item) {
            return Carbon::parse($item->salary_date)->format('M Y');
        });
        $salaryHeads = \App\Models\SalaryHeads::all();
        $months = $monthlyData->keys();

        $salaryHeadTotals = \DB::table('employee_monthly_salary_heads')
            ->selectRaw('DATE_FORMAT(employee_monthly_salaries.salary_date, "%b %Y") as month, employee_monthly_salary_heads.head_id, SUM(employee_monthly_salary_heads.head_value) as total')
            ->join('employee_monthly_salaries', 'employee_monthly_salary_heads.sal_id', '=', 'employee_monthly_salaries.id')
            ->whereBetween('employee_monthly_salaries.salary_date', [$startDate, $endDate])
            ->when($branch, function ($query, $branch) {
                return $query->where('employee_monthly_salaries.owned_by', $branch);
            })
            ->when(!empty($employee_id), function ($query) use ($employee_id) {
                return $query->where('employee_monthly_salaries.employee_id', $employee_id);
            })
            ->groupBy('month', 'employee_monthly_salary_heads.head_id')
            ->get();

        foreach ($salaryHeadTotals as $record) {
            $month = $record->month;
            $headId = $record->head_id;
            $amount = $record->total;

            if (!isset($totalByHead[$month])) {
                $totalByHead[$month] = [];
            }
            $totalByHead[$month][$headId] = $amount;

            if (!isset($grandTotal[$headId])) {
                $grandTotal[$headId] = 0;
            }
            $grandTotal[$headId] += $amount;
        }
        foreach ($monthlyData as $month => $salaries) {
            foreach ($salaries as $salary) {
                $branch = $salary->owned_by;

                if (!isset($totalByMonth[$month])) {
                    $totalByMonth[$month] = [
                        'branch' => $branch,
                        'total_employees' => 0,
                        'gross' => 0,
                        'employer_contribution_eobi' => 0,
                        'employer_contribution_pessi' => 0,
                        'employee_security' => 0,
                        'employee_contribution_eobi' => 0,
                        'employee_contribution_pessi' => 0,
                        'income_tax' => 0,
                        'other_deduction' => 0,
                        'net_payable' => 0,
                        'child_concession' => 0,
                        'cost_to_company' => 0,
                    ];
                }
                $totalByMonth[$month]['total_employees'] += 1;
                $totalByMonth[$month]['gross'] += $salary->gross;
                $totalByMonth[$month]['employer_contribution_eobi'] += $salary->eobi_employer;
                $totalByMonth[$month]['employer_contribution_pessi'] += $salary->pessi_employer;
                $totalByMonth[$month]['employee_security'] += $salary->emp_sec;
                $totalByMonth[$month]['employee_contribution_eobi'] += $salary->eobi;
                $totalByMonth[$month]['employee_contribution_pessi'] += $salary->pessi;
                $totalByMonth[$month]['income_tax'] += $salary->it;
                $totalByMonth[$month]['other_deduction'] += $salary->dedu;
                $totalByMonth[$month]['net_payable'] += $salary->net_payable;
                $totalByMonth[$month]['child_concession'] += $salary->conv;
                $totalByMonth[$month]['cost_to_company'] += $salary->cost_to_company;
            }
        }
        foreach ($totalByMonth as $month => $data) {
            $branch = $data['branch'];

            if (!isset($branchTotals[$branch])) {
                $branchTotals[$branch] = [];
            }

            if (!isset($branchTotals[$branch][$month])) {
                $branchTotals[$branch][$month] = [
                    'total_employees' => 0,
                    'gross' => 0,
                    'employer_contribution_eobi' => 0,
                    'employer_contribution_pessi' => 0,
                    'employee_security' => 0,
                    'employee_contribution_eobi' => 0,
                    'employee_contribution_pessi' => 0,
                    'income_tax' => 0,
                    'other_deduction' => 0,
                    'net_payable' => 0,
                    'child_concession' => 0,
                    'cost_to_company' => 0,
                ];
            }
            foreach ($data as $key => $value) {
                if ($key != 'branch') {
                    $branchTotals[$branch][$month][$key] += $value;
                }
            }

            foreach ($totalByMonth[$month] as $key => $value) {
                if (!isset($grandTotal[$key])) {
                    $grandTotal[$key] = 0.0;
                }
                $grandTotal[$key] += floatval($value);
            }
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = !empty($employee_id) ? 'Period Wise Employee Payroll Report' : 'Period Wise Payroll Report';
            $pdf = new Dompdf();
            $html = view('employee.reports.periodwisepayroll_report', compact('branches', 'branchTotals', 'totalByMonth', 'months', 'salaryHeads', 'totalByHead', 'grandTotal', 'employee_id', 'employees'))->render();
            $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -60px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
            </style>
            </head>
            <body>
                ' . $headerHtml . '
                <div class="footer">' . $footerHtml . '</div>
                ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('period_wise_payroll_report.pdf', ['Attachment' => false]);
        }
        if ($request->has('export') && $request->export == 'excel') {
            if (empty($request->employee_id)) {
                return Excel::download(new PeriodWisePayrollExport($branches, $branchTotals, $totalByMonth, $months, $salaryHeads, $totalByHead, $grandTotal), 'period_wise_payroll_report.xlsx');

            } else {
                return Excel::download(new PeriodWiseEmployeePayrollExport($branches, $branchTotals, $totalByMonth, $months, $salaryHeads, $totalByHead, $grandTotal, $request->employee_id, $employees), 'period_wise_payroll_report.xlsx');
            }
        }
        if ($request->has('export') && $request->export == 'pdf') {
            if (empty($request->employee_id)) {
                return Excel::download(new PeriodWisePayrollExport($branches, $branchTotals, $totalByMonth, $months, $salaryHeads, $totalByHead, $grandTotal), 'period_wise_payroll_report.pdf', \Maatwebsite\Excel\Excel::MPDF);

            } else {
                return Excel::download(new PeriodWiseEmployeePayrollExport($branches, $branchTotals, $totalByMonth, $months, $salaryHeads, $totalByHead, $grandTotal, $request->employee_id, $employees), 'period_wise_payroll_report.xlsx');
            }
        }
        return view('employee.reports.periodwisepayroll', compact('branches', 'branchTotals', 'totalByMonth', 'months', 'salaryHeads', 'totalByHead', 'grandTotal', 'employees', 'employee_id'));
    }
    public function periodwisepayrollReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $branch = $request->has('branches') ? $request->branches : '';
        $dateFrom = $request->input('datefrom');
        $dateTo = $request->input('dateto');
        $grandTotal = [];
        $totalByMonth = [];
        $totalByHead = [];
        $branchTotals = [];

        if ($dateFrom && $dateTo) {
            [$fromYear, $fromMonth] = explode('-', $dateFrom);
            [$toYear, $toMonth] = explode('-', $dateTo);

            $startDate = date('Y-m-01', strtotime($dateFrom));
            $endDate = Carbon::createFromFormat('Y-m', $dateTo)->endOfMonth();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $fromYear = $startDate->year;
            $fromMonth = $startDate->month;
            $toYear = $endDate->year;
            $toMonth = $endDate->month;
        }

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee', 'salary_heads')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = EmployeeMonthlySalary::with('employee', 'salary_heads')->where('owned_by', $userOwnedId);
        }

        if ($branch != '') {
            $query->where('owned_by', $branch);
        }

        $salaries = $query->whereBetween('salary_date', [$startDate, $endDate])->get();
        $monthlyData = $salaries->groupBy(function ($item) {
            return Carbon::parse($item->salary_date)->format('M Y');
        });
        $salaryHeads = \App\Models\SalaryHeads::all();
        $months = $monthlyData->keys();

        $salaryHeadTotals = \DB::table('employee_monthly_salary_heads')
            ->selectRaw('DATE_FORMAT(employee_monthly_salaries.salary_date, "%b %Y") as month, employee_monthly_salary_heads.head_id, SUM(employee_monthly_salary_heads.head_value) as total')
            ->join('employee_monthly_salaries', 'employee_monthly_salary_heads.sal_id', '=', 'employee_monthly_salaries.id')
            ->whereBetween('employee_monthly_salaries.salary_date', [$startDate, $endDate])
            ->when($branch, function ($query, $branch) {
                return $query->where('employee_monthly_salaries.owned_by', $branch);
            })
            ->groupBy('month', 'employee_monthly_salary_heads.head_id')
            ->get();

        foreach ($salaryHeadTotals as $record) {
            $month = $record->month;
            $headId = $record->head_id;
            $amount = $record->total;

            if (!isset($totalByHead[$month])) {
                $totalByHead[$month] = [];
            }
            $totalByHead[$month][$headId] = $amount;

            if (!isset($grandTotal[$headId])) {
                $grandTotal[$headId] = 0;
            }
            $grandTotal[$headId] += $amount;
        }
        foreach ($monthlyData as $month => $salaries) {
            foreach ($salaries as $salary) {
                $branch = $salary->owned_by;

                if (!isset($totalByMonth[$month])) {
                    $totalByMonth[$month] = [
                        'branch' => $branch,
                        'total_employees' => 0,
                        'gross' => 0,
                        'employer_contribution_eobi' => 0,
                        'employer_contribution_pessi' => 0,
                        'employee_security' => 0,
                        'employee_contribution_eobi' => 0,
                        'employee_contribution_pessi' => 0,
                        'income_tax' => 0,
                        'other_deduction' => 0,
                        'net_payable' => 0,
                        'child_concession' => 0,
                        'cost_to_company' => 0,
                    ];
                }
                $totalByMonth[$month]['total_employees'] += 1;
                $totalByMonth[$month]['gross'] += $salary->gross;
                $totalByMonth[$month]['employer_contribution_eobi'] += $salary->eobi_employer;
                $totalByMonth[$month]['employer_contribution_pessi'] += $salary->pessi_employer;
                $totalByMonth[$month]['employee_security'] += $salary->emp_sec;
                $totalByMonth[$month]['employee_contribution_eobi'] += $salary->eobi;
                $totalByMonth[$month]['employee_contribution_pessi'] += $salary->pessi;
                $totalByMonth[$month]['income_tax'] += $salary->it;
                $totalByMonth[$month]['other_deduction'] += $salary->dedu;
                $totalByMonth[$month]['net_payable'] += $salary->net_payable;
                $totalByMonth[$month]['child_concession'] += $salary->conv;
                $totalByMonth[$month]['cost_to_company'] += $salary->cost_to_company;
            }
        }
        foreach ($totalByMonth as $month => $data) {
            $branch = $data['branch'];

            if (!isset($branchTotals[$branch])) {
                $branchTotals[$branch] = [];
            }

            if (!isset($branchTotals[$branch][$month])) {
                $branchTotals[$branch][$month] = [
                    'total_employees' => 0,
                    'gross' => 0,
                    'employer_contribution_eobi' => 0,
                    'employer_contribution_pessi' => 0,
                    'employee_security' => 0,
                    'employee_contribution_eobi' => 0,
                    'employee_contribution_pessi' => 0,
                    'income_tax' => 0,
                    'other_deduction' => 0,
                    'net_payable' => 0,
                    'child_concession' => 0,
                    'cost_to_company' => 0,
                ];
            }
            foreach ($data as $key => $value) {
                if ($key != 'branch') {
                    $branchTotals[$branch][$month][$key] += $value;
                }
            }

            foreach ($totalByMonth[$month] as $key => $value) {
                if (!isset($grandTotal[$key])) {
                    $grandTotal[$key] = 0.0;
                }
                $grandTotal[$key] += floatval($value);
            }
        }
        // return view('employee.reports.periodwisepayroll', compact('branches', 'branchTotals', 'totalByMonth', 'months', 'salaryHeads', 'totalByHead', 'grandTotal'));
        // $year = intval($year);
        // $month = intval($month);
        // if ($month < 1 || $month > 12 || $year < 1900 || $year > now()->year) {
        //     throw new \Exception('Invalid month or year format.');
        // }
        // $report_name = 'pessi Contribution for the Month of' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;
        // $report_name = 'EOBI Contribution for the Month of ' . Carbon::createFromDate($year, $month, 1)->format('F') . '-' . $year;
        $report_name = 'Period Wise Payroll';

        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('employee.reports.periodwisepayroll_report', compact('branches', 'branchTotals', 'totalByMonth', 'months', 'salaryHeads', 'totalByHead', 'grandTotal'))->render();
            $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -60px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
            </style>
            </head>
            <body>
                ' . $headerHtml . '
                <div class="footer">' . $footerHtml . '</div>
                ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('period_wise_payroll_report.pdf', ['Attachment' => false]);
        }
    }
    public function empsecreport(Request $request)
    {
        $userType      = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId   = \Auth::user()->ownedId();
    
        // keep selected IDs separate from option lists
        $selectedBranchId = $request->input('branches');
        $selectedEmployeeId = $request->input('employee_id');
    
        $dateFrom = $request->input('datefrom')
            ? Carbon::createFromFormat('Y-m', $request->input('datefrom'))->startOfMonth()
            : Carbon::now()->startOfMonth()->subMonths(10);
    
        $dateTo = $request->input('dateto')
            ? Carbon::createFromFormat('Y-m', $request->input('dateto'))->endOfMonth()
            : Carbon::now()->endOfMonth();
    
        // build months array once
        $months = [];
        $currentMonth = $dateFrom->copy();
        while ($currentMonth->lte($dateTo)) {
            $months[] = $currentMonth->format('M Y');
            $currentMonth->addMonth();
        }
    
        if ($userType == 'company') {
            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
            $branchOptions = User::where('type', 'branch')->pluck('name', 'id');
            $branchOptions->prepend(\Auth::user()->name, \Auth::user()->id);
            $branchOptions->prepend('Select Branch', '');
    
            $query = EmployeeMonthlySalary::with('employee')
                ->where('created_by', $userCreatorId)
                ->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        } else {
            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
            $branchOptions = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branchOptions->prepend('Select Branch', '');
    
            $query = EmployeeMonthlySalary::with('employee')
                ->where('owned_by', $userOwnedId)
                ->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        }
    
        // filters (use selected IDs, not the options list)
        if (!empty($selectedBranchId) && is_numeric($selectedBranchId)) {
            $query->whereHas('employee', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
        }
    
        if (!empty($selectedEmployeeId) && is_numeric($selectedEmployeeId)) {
            $query->whereHas('employee', function ($q) use ($selectedEmployeeId) {
                $q->where('id', $selectedEmployeeId);
            });
        }
    
        $salaries = $query->get();
    
        // Common payload for exports
        $exportData = [
            'branches'  => $branchOptions,
            'employees' => $employees,
            'salaries'  => $salaries,
            'months'    => $months,
            'request'   => $request,
        ];
    
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
            $branchName = $exportData['branches'][$request->branches] ?? 'All Branches';
            return Excel::download(new EmployeeSecReportExport($exportData, $branchName, $request->all()), 'employee_security_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
            $branchName = $exportData['branches'][$request->branches] ?? 'All Branches';
            return Excel::download(new EmployeeSecReportExport($exportData, $branchName, $request->all()), 'employee_security_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
    
        // normal page render
        return view('employee.reports.empsecreport', [
            'branches' => $branchOptions,
            'employees'=> $employees,
            'salaries' => $salaries,
            'months'   => $months,
        ]);
    }
     public function singleEmpSecReport(Request $request)
    {
        $user = \Auth::user();
        $userType = $user->type;
        $userCreatorId = $user->creatorId();
        $userOwnedId = $user->ownedId();

        $employee_id = $request->input('employee_id');
        $fromYear = $request->input('from_year') ?? 2016;
        $toYear = $request->input('to_year') ?? date('Y');
        $empFilter = false;

        // Branches & Employees
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $query = EmployeeMonthlySalary::with('employee')->where('created_by', $userCreatorId);
            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $query = EmployeeMonthlySalary::with('employee')->where('owned_by', $userOwnedId);
            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
        }
        $branches->prepend('Select Branch', '');

        // Filter by employee
        if (!empty($employee_id) && is_numeric($employee_id)) {
            $empFilter = true;
            $query->where('employee_id', $employee_id);
        }

        // Get salaries within selected years
        $salaries = $query->whereYear('salary_date', '>=', $fromYear)
            ->whereYear('salary_date', '<=', $toYear)
            ->orderBy('salary_date')
            ->get();

        // Prepare salary data by [year][month]
        $salaryData = [];
        foreach ($salaries as $salary) {
            $year = date('Y', strtotime($salary->salary_date));
            $month = date('n', strtotime($salary->salary_date));
            $salaryData[$year][$month] = $salary->emp_sec ?? 0;
        }
        $yearlyTotals = [];

        foreach ($salaryData as $year => $months) {
            $yearlyTotals[$year] = array_sum($months);
        }
        $openingBalance = EmployeeMonthlySalary::where('employee_id', $employee_id)
            ->whereYear('salary_date', '<', $fromYear)
            ->sum('emp_sec');

        // Closing Balance = Opening + all salaries in range
        $closingBalance = $openingBalance + $salaries->sum('emp_sec');

        // Employee details
        $employee = !empty($employee_id) ? Employee::find($employee_id) : null;
        $selectedEmployeeName = !empty($employee) ? $employee->name : 'All Employees';
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new SingleEmpSecReportExport(
                $employees,
                $salaries,
                $salaryData,
                $employee,
                $yearlyTotals,
                $openingBalance,
                $closingBalance,
                $branches,
                $empFilter,
                $fromYear,
                $toYear
            ), 'single_emp_sec_report(' . $selectedEmployeeName . ').xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new SingleEmpSecReportExport(
                $employees,
                $salaries,
                $salaryData,
                $employee,
                $yearlyTotals,
                $openingBalance,
                $closingBalance,
                $branches,
                $empFilter,
                $fromYear,
                $toYear
            ), 'single_emp_sec_report(' . $selectedEmployeeName . ').pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.singleEmpSecReport', compact(
            'employees',
            'salaries',
            'salaryData',
            'employee',
            'yearlyTotals',
            'openingBalance',
            'closingBalance',
            'branches',
            'empFilter',
            'fromYear',
            'toYear'
        ));
    }

    public function empsecreportPdf(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $branches = $request->input('branches');
        $employee_id = $request->input('employee_id');
        $dateFrom = $request->input('datefrom')
            ? Carbon::createFromFormat('Y-m', $request->input('datefrom'))->startOfMonth()
            : Carbon::now()->startOfMonth()->subMonths(10);

        $dateTo = $request->input('dateto')
            ? Carbon::createFromFormat('Y-m', $request->input('dateto'))->endOfMonth()
            : Carbon::now()->endOfMonth();
        if (!$request->input('datefrom')) {
            $request->merge(['datefrom' => $dateFrom->format('Y-m')]);
        }

        if (!$request->input('dateto')) {
            $request->merge(['dateto' => $dateTo->format('Y-m')]);
        }
        $months = [];
        $currentMonth = $dateFrom->copy();
        while ($currentMonth->lte($dateTo)) {
            $months[] = $currentMonth->format('M Y');
            $currentMonth->addMonth();
        }
        if ($userType == 'company') {
            $employees = Employee::where('created_by', $userCreatorId)->get()->pluck('name', 'id');
            $branches = User::where('type', 'branch')->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $query = EmployeeMonthlySalary::with('employee')
                ->where('created_by', $userCreatorId)
                ->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        } else {
            $employees = Employee::where('owned_by', $userOwnedId)->get()->pluck('name', 'id');
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $query = EmployeeMonthlySalary::with('employee')
                ->where('owned_by', $userOwnedId)
                ->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        }

        if (!empty($branches) && is_numeric($branches)) {
            $query->whereHas('employee', function ($query) use ($branches) {
                $query->where('branch_id', $branches);
            });
        }
        if (!empty($employee_id) && is_numeric($employee_id)) {
            $query->whereHas('employee', function ($query) use ($employee_id) {
                $query->where('id', $employee_id);
            });
        }
        $salaries = $query->get();
        // return view('employee.reports.empsecreport', compact('branches', 'employees', 'salaries', 'months'));
        $report_name = 'Employee Security Report';

        $pdf = new Dompdf();
        $html = view('employee.reports.empsecreport_pdf', compact('branches', 'employees', 'salaries', 'months'))->render();
        $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
            .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        </style>
        </head><body>
        ' . $headerHtml . '
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
            </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    /*public function transferreport(Request $request)
    {
        $branches = $request->input('branches');
        $datefrom = $request->input('datefrom');
        $dateto = $request->input('dateto');
        $type = $request->input('type');
        $status = $request->input('status');
        $startDate = \Carbon\Carbon::parse($datefrom)->startOfMonth()->format('Y-m-d');
        $endDate = \Carbon\Carbon::parse($dateto)->endOfMonth()->format('Y-m-d');

        $transfers = EmployeeTransfer::orderBy('id', 'asc');
        if (!empty($branches)) {
            if (!empty($type) && $type == 'transfer_in') {
                $transfers = $transfers->where('branch_to_id', $branches)
                    ->whereBetween('transfer_date', [$startDate, $endDate]);
            } elseif (!empty($type) && $type == 'transfer_out') {
                $transfers = $transfers->where('branch_from_id', $branches)
                    ->whereBetween('transfer_date', [$startDate, $endDate]);
            }
        } else {
            $transfers = $transfers->whereBetween('transfer_date', [$startDate, $endDate]);
        }
        if (!empty($status)) {
            $transfers = $transfers->where('status', $status);
        }
        $transfers = $transfers->get();
        if ($transfers->isEmpty()) {
            return redirect()->back()->with('error', 'No transfers found for the selected criteria');
            // return response()->json(['status' => 'error', 'message' => 'No transfers found for the selected criteria']);
        }


        $html = view('employee.reports.transferreport', compact('transfers', 'type'))->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
            .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        </style>
        </head><body>
        ' . $headerHtml . '
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->render();
        $pdfContent = $pdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['status' => 'success', 'base64Pdf' => $base64Pdf]);
    } */

    public function transferreport(Request $request)
    {
        $selectedBranch = $request->input('branches');
        $datefrom = $request->input('datefrom');
        $dateto = $request->input('dateto');
        $type = $request->input('type');
        $status = $request->input('status');

        $userType = Auth::user()->type;
        $userCreatorId = Auth::user()->creatorId();
        $userOwnedId = Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }

        // Start base query
        $transfersQuery = EmployeeTransfer::query();

        // Apply branch filters only if selected
        if (!empty($selectedBranch)) {
            if ($type === 'transfer_in') {
                $transfersQuery->where('branch_to_id', $selectedBranch);
            } elseif ($type === 'transfer_out') {
                $transfersQuery->where('branch_from_id', $selectedBranch);
            } else {
                // If no type, match either from or to
                $transfersQuery->where(function ($q) use ($selectedBranch) {
                    $q->where('branch_to_id', $selectedBranch)
                        ->orWhere('branch_from_id', $selectedBranch);
                });
            }
        }

        // Apply date filter if both dates provided
        if (!empty($datefrom) && !empty($dateto)) {
            $startDate = \Carbon\Carbon::parse($datefrom)->startOfMonth()->format('Y-m-d');
            $endDate = \Carbon\Carbon::parse($dateto)->endOfMonth()->format('Y-m-d');
            $transfersQuery->whereBetween('transfer_date', [$startDate, $endDate]);
        }

        // Apply status filter
        if (!empty($status)) {
            $transfersQuery->where('status', $status);
        }

        // Final get
        $transfers = $transfersQuery->orderBy('id')->get();

        if ($transfers->isEmpty()) {
            return redirect()->back()->with('error', 'No transfers found for the selected criteria');
        }

        // Prepare PDF rendering
        // $html = view('employee.reports.transferreport', compact('transfers', 'type'))->render();
        // $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        // $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        // $html = '<html><head>
        // <style>
        //     @page {
        //         margin-top: 100px;
        //         margin-bottom: 100px;
        //     }
        //     .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
        //     .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        // </style>
        // </head><body>
        // ' . $headerHtml . '
        // <div class="footer">' . $footerHtml . '</div>
        // ' . $html . '
        // </body></html>';

        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);

        // $pdf = new Dompdf($options);
        // $pdf->loadHtml($html);
        // $pdf->render();
        // $pdfContent = $pdf->output();
        // $base64Pdf = base64_encode($pdfContent);

        $startDate = \Carbon\Carbon::parse($datefrom)->startOfMonth()->format('Y-m-d');
        $endDate = \Carbon\Carbon::parse($dateto)->endOfMonth()->format('Y-m-d');
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $startDate]);
            $request->merge(['date_to' => $endDate]);
            return Excel::download(new EmployeeTransferReportExport($transfers, $branches, "Employee Transfer Report", $request->all()), 'Employee_Transfer_Report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $startDate]);
            $request->merge(['date_to' => $endDate]);
            return Excel::download(new EmployeeTransferReportExport($transfers, $branches, "Employee Transfer Report", $request->all()), 'Employee_Transfer_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        // Send all needed data to view
        return view('employee.transfer.index', compact('transfers', 'branches', 'selectedBranch', 'datefrom', 'dateto', 'type', 'status'));
    }

    public function employmentHistory(Request $request)
    {
        $userType = Auth::user()->type;
        $userCreatorId = Auth::user()->creatorId();
        $userOwnedId = Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('created_by', $userCreatorId)->get()->pluck('name', 'id');
            $query = Employee::with('employee_rejoin')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('owned_by', $userOwnedId)->get()->pluck('name', 'id');
            $query = Employee::with('employee_rejoin')->where('owned_by', $userOwnedId);
        }
        if ($request->has('branches') && $request->branches != '') {
            $query->where('branch_id', $request->branches);
        }
        if ($request->has('employee_id') && $request->employee_id != '') {
            $query->where('id', $request->employee_id);
        }
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'regular') {
                $query->whereDoesntHave('employee_rejoin', function ($q) {
                    $q->whereNotNull('prev_doj');
                });
            } else {
                $query->whereHas('employee_rejoin');
            }
        }
        // if ($request->filled('is_print') && $request->is_print == 1) {
        //     $employeesquery = $query->get();
        //     $bodyHtml = view('employee.reports.employee_service_record', compact('employeesquery', 'branches'))->render();
        //     $headerHtml = view('employee.report.pdf.header')->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $finalHtml = '
        //     <html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         body { font-family: sans-serif; font-size: 12px; }
        //         .header {
        //             position: fixed;
        //             top: -60px;
        //             left: 0;
        //             right: 0;
        //             height: 100px;
        //             text-align: center;
        //         }
        //         .footer {
        //             position: fixed;
        //             bottom: -60px;
        //             left: 0;
        //             right: 0;
        //             height: 50px;
        //             text-align: center;
        //             font-size: 10px;
        //             color: #888;
        //         }
        //     </style>
        //     </head>
        //     <body>
        //         ' . $headerHtml . '
        //         <div class="footer">' . $footerHtml . '</div>
        //         ' . $bodyHtml . '
        //     </body></html>';
        //     // dd($finalHtml);

        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($finalHtml);
        //     $dompdf->setPaper('A4', 'portrait');
        //     $dompdf->render();
        //     return $dompdf->stream('employee_service_record.pdf', ['Attachment' => false]);
        // }
        $fromDate = $request->filled('from_date') ? date('Y-m-d', strtotime($request->input('from_date'))) : null;
        $toDate = $request->filled('to_date') ? date('Y-m-d', strtotime($request->input('to_date'))) : null;
        $employeesquery = $query->get();
        $branchName = $request->has('branches') ? User::find($request->branches)->name : '';
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $data = [
                'employeesquery' => $employeesquery,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeEmploymentHistoryExport($data, $request->all()), 'employee_employment_history.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $data = [
                'employeesquery' => $employeesquery,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeEmploymentHistoryExport($data, $request->all()), 'employee_employment_history.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.employmentHistory', compact('branches', 'employeesquery', 'employees'));
    }
    public function employeeMonthlyReconsilation(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
        }

        $data = [];

        if ($request->filled('branches') && $request->branches != '') {
            // Specific branch selected
            $query = EmployeeMonthlySalary::with('employee')
                ->whereHas('employee', function ($q) use ($request) {
                    $q->where('branch_id', $request->branches);
                });

            if ($userType == 'company') {
                $query->where('created_by', $userCreatorId);
            } else {
                $query->where('owned_by', $userOwnedId);
            }

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('month')) {
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $query->whereMonth('salary_date', $month->month)
                    ->whereYear('salary_date', $month->year);
            }
            $data[$request->branches] = $query->get();

        } else {
            // All branches selected
            $branchIds = $branches->keys()->filter(fn($id) => $id !== '')->all();

            foreach ($branchIds as $branchId) {
                $query = EmployeeMonthlySalary::with('employee')
                    ->whereHas('employee', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });

                if ($userType == 'company') {
                    $query->where('created_by', $userCreatorId);
                } else {
                    $query->where('owned_by', $userOwnedId);
                }

                if ($request->filled('employee_id')) {
                    $query->where('employee_id', $request->employee_id);
                }
                if ($request->filled('month')) {
                    $month = Carbon::createFromFormat('Y-m', $request->month);
                    $query->whereMonth('salary_date', $month->month)
                        ->whereYear('salary_date', $month->year);
                }
                $results = $query->get();
                if ($results->count()) {
                    $data[$branchId] = $results;
                }
            }
        }
        // if ($request->filled('is_print') && $request->is_print == 1) {
        //     $bodyHtml = view('employee.reports.reconcilationprint', compact('branches', 'data'))->render();
        //     $headerHtml = view('employee.report.pdf.header')->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $finalHtml = '
        //     <html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         body { font-family: sans-serif; font-size: 12px; }
        //         .header {
        //             position: fixed;
        //             top: -60px;
        //             left: 0;
        //             right: 0;
        //             height: 100px;
        //             text-align: center;
        //         }
        //         .footer {
        //             position: fixed;
        //             bottom: -60px;
        //             left: 0;
        //             right: 0;
        //             height: 50px;
        //             text-align: center;
        //             font-size: 10px;
        //             color: #888;
        //         }
        //     </style>
        //     </head>
        //     <body>
        //         ' . $headerHtml . '
        //         <div class="footer">' . $footerHtml . '</div>
        //         ' . $bodyHtml . '
        //     </body></html>';
        //     // dd($finalHtml);

        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($finalHtml);
        //     $dompdf->setPaper('A4', 'portrait');
        //     $dompdf->render();
        //     return $dompdf->stream('employee_monthly_reconcilation.pdf', ['Attachment' => false]);
        // }
        $branchName = $request->has('branches') ? User::find($request->branches)->name : '';
        if ($request->has('export') && $request->export == 'excel') {
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeMonthlyReconsilationExport($data, $request->all()), 'employee_monthly_reconcilation.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeMonthlyReconsilationExport($data, $request->all()), 'employee_monthly_reconcilation.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.employeeMonthlyReconsilation', compact('branches', 'employees', 'data'));
    }
    public function staffChildReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $userCreatorId)
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
            $query = EmpChildrens::with(['employee', 'student', 'enrollment', 'employee.userbranch']);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
            $query = EmpChildrens::with(['employee', 'student', 'enrollment', 'employee.userbranch'])->where('branch_id', $userOwnedId);
        }

        if ($request->filled('branches')) {
            $query->where('branch_id', $request->branches);
        }

        if ($request->filled('employee_id')) {
            $query->where('emp_id', $request->employee_id);
        }

        $data = $query->get();
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Staff Child Report';
            $pdf = new Dompdf();
            $html = view('employee.reports.staffChildReportPdf', compact('branches', 'employees', 'data'))->render();
            $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            ' . $headerHtml . '
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
                </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('staff_child_report.pdf', ['Attachment' => false]);
        }

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new EmployeeStaffChildReport($data, $branches, "Employee Child Report"), 'employee_child_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeeStaffChildReport($data, $branches, "Employee Child Report"), 'employee_child_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.staffChildReport', compact('branches', 'employees', 'data'));
    }
    public function employeeTaxReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $fromDate = $request->input('from_date') ?? date('Y-m-d');
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $userCreatorId)
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
            $query = EmployeeMonthlySalary::with(['employee', 'salary_heads']);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
            $query = EmployeeMonthlySalary::with(['employee', 'salary_heads'])->where('owned_by', $userOwnedId);
        }

        if ($request->filled('branches')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->branches);
            });
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $dateFrom = date('Y-m-d', strtotime($request->input('from_date')));
            $dateTo = date('Y-m-d', strtotime($request->input('to_date')));

            if ($dateFrom && $dateTo) {
                $query->whereDate('salary_date', '>=', $dateFrom)
                    ->whereDate('salary_date', '<=', $dateTo);
            }
        } else {
            $dateFrom = Carbon::now()->subMonths(12)->startOfMonth();
            $dateTo = Carbon::now()->endOfMonth();
            $query->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        }
        $data = $query->get();
        $salaryHeads = \App\Models\SalaryHeads::all();
        // if ($request->has('print') && $request->print == 'pdf') {
        //     $report_name = 'Employee Tax Report';
        //     $pdf = new Dompdf();
        //     $html = view('employee.reports.employeeTaxReportPrint', compact('branches', 'data', 'salaryHeads', 'dateTo', 'dateFrom'))->render();
        //     $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $html = '<html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
        //         .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        //     </style>
        //     </head><body>
        //     ' . $headerHtml . '
        //     <div class="footer">' . $footerHtml . '</div>
        //     ' . $html . '
        //         </body></html>';
        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($html);
        //     $dompdf->setPaper('A4', 'landscape');
        //     $dompdf->render();
        //     return $dompdf->stream('employee_tax_report.pdf', ['Attachment' => false]);
        // }
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $dateTo]);
            return Excel::download(new EmployeeTaxDedReportExport($data, $salaryHeads, "Employee Tax Report", $request->all()), 'employee_tax_deduction_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $dateTo]);
            return Excel::download(new EmployeeTaxDedReportExport($data, $salaryHeads, "Employee Tax Report", $request->all()), 'employee_tax_deduction_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.employeeTaxReport', compact('branches', 'employees', 'data', 'salaryHeads'));
    }
    public function salaryComparisonRpt(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $userCreatorId)
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');
            $query = EmployeeMonthlySalary::with(['employee']);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');
            $query = EmployeeMonthlySalary::with(['employee'])->where('owned_by', $userOwnedId);
        }

        if ($request->filled('branches')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->branches);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Handle from_monthyear and to_monthyear filters
        $filtered = false;
        if ($request->filled('from_monthyear') && $request->filled('to_monthyear')) {
            $filtered = true;
            $fromDate = Carbon::parse($request->from_monthyear)->startOfMonth();
            $toDate = Carbon::parse($request->to_monthyear)->endOfMonth();
            $query->whereBetween('salary_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
        } else {
            // Default date range (last 5 years)
            $dateFrom = Carbon::now()->subYears(5)->startOfYear();
            $dateTo = Carbon::now()->endOfYear();
            $query->whereBetween('salary_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);
        }

        // Get all salaries based on filters
        $data = $query->get();

        // Group data by employee and salary date to find the latest salary
        $employeeSalaries = [];
        $employeeLatestDates = [];

        foreach ($data as $salary) {
            if (!$salary->employee)
                continue;

            $empId = @$salary->employee->id;
            $salaryDate = Carbon::parse($salary->salary_date);

            // Initialize or update latest date for employee
            if (!isset($employeeLatestDates[$empId]) || $salaryDate > $employeeLatestDates[$empId]) {
                $employeeLatestDates[$empId] = $salaryDate;
            }

            // Store salary data by date
            if (!isset($employeeSalaries[$empId])) {
                $employeeSalaries[$empId] = [];
            }

            $dateKey = $salaryDate->format('Y-m-d');
            if (!isset($employeeSalaries[$empId][$dateKey])) {
                $employeeSalaries[$empId][$dateKey] = [
                    'date' => $salaryDate,
                    'session' => $this->getSessionYear($salary->salary_date),
                    'gross' => $salary->gross,
                    'employee' => $salary->employee
                ];
            } else {
                // If multiple records for same date, add the gross
                $employeeSalaries[$empId][$dateKey]['gross'] += $salary->gross;
            }
        }

        // Process the data to get most recent salary and earlier sessions
        $processedData = [];
        $sessions = [];

        foreach ($employeeSalaries as $empId => $salaries) {
            // Get the latest salary date for this employee
            $latestDate = $employeeLatestDates[$empId];
            $latestDateKey = $latestDate->format('Y-m-d');

            // Get the employee record from the latest salary entry
            $currentEmployee = isset($salaries[$latestDateKey]) ? $salaries[$latestDateKey]['employee'] : null;
            // Only attempt to fetch designation if we have a valid employee
            $empDesignation = '';
            if ($currentEmployee && $currentEmployee->designation_id) {
                $designationRecord = Designation::where('id', $currentEmployee->designation_id)->first();
                $empDesignation = $designationRecord ? $designationRecord->name : '';
            }

            // Get service period for this specific employee
            $empServicePeriod = $currentEmployee ? $currentEmployee->getEmployeeTenure($empId) : '';
            $processedData[$empId] = [
                'emp_no' => $empId,
                'branch_name' => $currentEmployee ? $currentEmployee->userbranch->name : '',
                'emp_doj' => $currentEmployee ? $currentEmployee->company_doj : '',
                'emp_designation' => $empDesignation,
                'emp_service_period' => $empServicePeriod,
                'name' => $currentEmployee ? $currentEmployee->name : '',
                'salaries' => [],
            ];
            // Collect all sessions from all salaries for this employee
            foreach ($salaries as $dateKey => $salaryData) {
                $session = $salaryData['session'];

                // Track all sessions
                if (!in_array($session, $sessions)) {
                    $sessions[] = $session;
                }

                // Add salary data to the employee's record
                if (!isset($processedData[$empId]['salaries'][$session])) {
                    $processedData[$empId]['salaries'][$session] = $salaryData['gross'];
                } else {
                    // We'll take the latest salary within a session
                    $currentSalaryDate = Carbon::parse($dateKey);
                    $existingSalaryDate = isset($processedData[$empId]['dates'][$session]) ?
                        $processedData[$empId]['dates'][$session] : null;

                    if (!$existingSalaryDate || $currentSalaryDate > $existingSalaryDate) {
                        $processedData[$empId]['salaries'][$session] = $salaryData['gross'];
                        $processedData[$empId]['dates'][$session] = $currentSalaryDate;
                    }
                }
            }
        }

        sort($sessions);

        // Handle display logic for previous sessions
        $displaySessions = $sessions;
        if (!$filtered && count($sessions) > 1) {
            // If no filter is applied and there are multiple sessions,
            // only show the most recent session and one previous session for comparison
            $currentSession = end($sessions);
            $prevSession = $sessions[count($sessions) - 2];
            $displaySessions = [$prevSession, $currentSession];
        }

        // if ($request->filled('is_print') && $request->is_print == 1) {
        //     $bodyHtml = view('employee.reports.salaryComparisonRptPrint', compact('branches', 'processedData', 'displaySessions'))->render();
        //     $headerHtml = view('employee.report.pdf.header')->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        //     $finalHtml = '
        // <html><head>
        // <style>
        //     @page {
        //         margin-top: 100px;
        //         margin-bottom: 100px;
        //     }
        //     body { font-family: sans-serif; font-size: 12px; }
        //     .header {
        //         position: fixed;
        //         top: -60px;
        //         left: 0;
        //         right: 0;
        //         height: 100px;
        //         text-align: center;
        //     }
        //     .footer {
        //         position: fixed;
        //         bottom: -60px;
        //         left: 0;
        //         right: 0;
        //         height: 50px;
        //         text-align: center;
        //         font-size: 10px;
        //         color: #888;
        //     }
        // </style>
        // </head>
        // <body>
        //     ' . $headerHtml . '
        //     <div class="footer">' . $footerHtml . '</div>
        //     ' . $bodyHtml . '
        // </body></html>';

        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);

        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($finalHtml);
        //     $dompdf->setPaper('A3', 'landscape');
        //     $dompdf->render();

        //     return $dompdf->stream('salary_comparison_report.pdf', ['Attachment' => false]);
        // }
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            return Excel::download(new SalaryComparisonReportExport($processedData, $displaySessions, "Salary Comparison Report", $request->all()), 'salary_comparison_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            return Excel::download(new SalaryComparisonReportExport($processedData, $displaySessions, "Salary Comparison Report", $request->all()), 'salary_comparison_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.salaryComparisonRpt', compact('branches', 'processedData', 'displaySessions', 'employees'));
    }
    private function getSessionYear($date)
    {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        if ((int) $month < 4) {
            return ($year - 1) . '-' . $year;
        } else {
            return $year . '-' . ($year + 1);
        }
    }

    public function empBirthdayRpt(Request $request)
    {
        $userType = Auth::user()->type;
        $userCreatorId = Auth::user()->creatorId();
        $userOwnedId = Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('created_by', $userCreatorId)->get()->pluck('name', 'id');
            $query = Employee::with('userbranch')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('owned_by', $userOwnedId)->get()->pluck('name', 'id');
            $query = Employee::with('userbranch')->where('owned_by', $userOwnedId);
        }
        if ($request->filled('branches')) {
            $query = Employee::where('branch_id', $request->branches);
        }

        if ($request->filled('employee_id')) {
            $query->where('id', $request->employee_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('dob', '=', date('m', strtotime($request->month)));
        }
        $employeesquery = $query->get();
        if ($request->has('export') && $request->export == 'excel') {
            $data = [
                'employeesquery' => $employeesquery,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
            ];
            return Excel::download(new EmployeeBirthdayReportExport($data), 'employee_birthday_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $data = [
                'employeesquery' => $employeesquery,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
            ];
            return Excel::download(new EmployeeBirthdayReportExport($data), 'employee_birthday_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.empBirthdayRpt', compact('branches', 'employeesquery', 'employees'));
    }
    public function empEduRpt(Request $request)
    {
        $userType = Auth::user()->type;
        $userCreatorId = Auth::user()->creatorId();
        $userOwnedId = Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('created_by', $userCreatorId)->get()->pluck('name', 'id');
            $query = EmpEducation::with(['employee', 'employee.userbranch']);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $employees = Employee::where('owned_by', $userOwnedId)->get()->pluck('name', 'id');
            $query = EmpEducation::with(['employee', 'employee.userbranch'])
                ->whereHas('employee', function ($q) use ($userOwnedId) {
                    $q->where('owned_by', $userOwnedId);
                });
        }
        if ($request->filled('branches')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('branch_id', $request->branches);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('emp_id', $request->employee_id);
        }
        $data = $query->get();
        // if ($request->has('print') && $request->print == 'pdf') {
        //     $report_name = 'Employee Education Report';
        //     $pdf = new Dompdf();
        //     $html = view('employee.reports.empEduRptPdf', compact('branches', 'employees', 'data'))->render();
        //     $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $html = '<html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         /* Only the footer is fixed */
        //         .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        //     </style>
        //     </head><body>
        //     ' . $headerHtml . '
        //     <div class="footer">' . $footerHtml . '</div>
        //     ' . $html . '
        //     </body></html>';
        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($html);
        //     $dompdf->setPaper('A4', 'landscape');
        //     $dompdf->render();
        //     return $dompdf->stream('employee_education_report.pdf', ['Attachment' => false]);
        // }
                if ($request->has('export') && $request->export == 'excel') {
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
            ];
            return Excel::download(new EmployeeEducationReportExport($data), 'employee_education_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
            ];
            return Excel::download(new EmployeeEducationReportExport($data), 'employee_education_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.empEduRpt', compact('branches', 'employees', 'data'));
    }
    public function empwithoutPayleave(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('created_by', $userCreatorId)->pluck('name', 'id');

            $query = Leave::with('employees', 'leaveType', 'employees.employee_leaves')
                ->where('created_by', $userCreatorId)
                ->where('leave_type_id', 2)
                ->whereHas('leaveType', function ($q) {
                    $q->where('title', 'like', '%leave without pay%');
                });

        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');

            $employees = Employee::where('owned_by', $userOwnedId)->pluck('name', 'id');

            $query = Leave::with('employees', 'leaveType', 'employees.employee_leaves')
                ->where('owned_by', $userOwnedId)
                ->where('leave_type_id', 2)
                ->whereHas('leaveType', function ($q) {
                    $q->where('title', 'like', '%leave without pay%');
                });
        }

        if ($request->filled('branches')) {
            $query->whereHas('employees', function ($q) use ($request) {
                $q->where('branch_id', $request->branches);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = date('Y-m-d', strtotime($request->input('from_date')));
            $toDate = date('Y-m-d', strtotime($request->input('to_date')));
            $query->whereBetween('start_date', [$fromDate, $toDate]);
        }

        $data = $query->get();

        // if ($request->has('print') && $request->print == 'pdf') {
        //     $report_name = 'Employee Without Pay Leave Report';
        //     $pdf = new Dompdf();
        //     $html = view('employee.reports.empWithoutPayLeavePdf', compact('branches', 'employees', 'data'))->render();
        //     $headerHtml = view('employee.report.pdf.header', compact('report_name', 'request'))->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        //     $html = '<html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         /* Only the footer is fixed */
        //         .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        //     </style>
        //     </head><body>
        //     ' . $headerHtml . '
        //     <div class="footer">' . $footerHtml . '</div>
        //     ' . $html . '
        //     </body></html>';
        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($html);
        //     $dompdf->setPaper('A4', 'landscape');
        //     $dompdf->render();

        //     return $dompdf->stream('employee_without_pay_leave_report.pdf', ['Attachment' => false]);
        // }

        $fromDate = $request->filled('from_date') ? date('Y-m-d', strtotime($request->input('from_date'))) : null;
        $toDate = $request->filled('to_date') ? date('Y-m-d', strtotime($request->input('to_date'))) : null;
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
            ];
            return Excel::download(new EmployeeWithoutPayLeaveReportExport($data, $request->all()), 'employee_without_pay_leave_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $data = [
                'branches' => $branches,
                'employees' => $employees,
                'data' => $data,
                'request' => $request,
            ];
            return Excel::download(new EmployeeWithoutPayLeaveReportExport($data, $request->all()), 'employee_without_pay_leave_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.empWithoutPayLeave', compact('branches', 'employees', 'data'));
    }
    public function empleaveReport(Request $request)
    {
        // Determine user scope
        $userType = auth()->user()->type;
        $creatorId = auth()->user()->creatorId();
        $ownedId = auth()->user()->ownedId();

        // Branch and employee filters
        if ($userType === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id')
                ->prepend(auth()->user()->name, auth()->user()->id)
                ->prepend('Select Branch', '');

            $employees = Employee::where('created_by', $creatorId)
                ->pluck('name', 'id');

            $baseQuery = Leave::with('employees.userbranch', 'leaveType', 'employees.employee_leaves')
                ->where('created_by', $creatorId);
        } else {
            $branches = User::where('id', $ownedId)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');

            $employees = Employee::where('owned_by', $ownedId)
                ->pluck('name', 'id');

            $baseQuery = Leave::with('employees.userbranch', 'leaveType', 'employees.employee_leaves')
                ->where('owned_by', $ownedId);
        }

        // Apply branch/employee filters
        if ($request->filled('branches')) {
            $baseQuery->whereHas('employees', function ($q) use ($request) {
                $q->where('branch_id', $request->branches);
            });
        }
        if ($request->filled('employee_id')) {
            $baseQuery->where('employee_id', $request->employee_id);
        }

        // Fetch all relevant leaves (no date filter here)
        $allLeaves = $baseQuery->get();

        // Determine period bounds
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
        } else {
            $fromDate = Carbon::now()->startOfMonth();
            $toDate = Carbon::now()->endOfMonth();
        }

        // Separate opening vs period leaves
        if ($fromDate && $toDate) {
            $openingLeaves = $allLeaves->filter(function ($l) use ($fromDate) {
                return \Carbon\Carbon::parse($l->start_date)->lt($fromDate);
            });
            $periodLeaves = $allLeaves->filter(function ($l) use ($fromDate, $toDate) {
                $sd = \Carbon\Carbon::parse($l->start_date);
                return $sd->between($fromDate, $toDate);
            });
        }

        // Build report data grouped by branch and employee
        $reportData = [];
        $byBranch = $periodLeaves->groupBy(fn($l) => $l->employees->userbranch->name ?? 'Unknown Branch');

        foreach ($byBranch as $branchName => $branchLeaves) {
            $groupedByEmp = $branchLeaves->groupBy(fn($l) => $l->employee_id);
            foreach ($groupedByEmp as $empId => $empPeriodLeaves) {
                $employee = $empPeriodLeaves->first()->employees;

                // Entitlements
                $entCL = $employee->employee_leaves->casual_total ?? 0;
                $entAL = $employee->employee_leaves->annual_total ?? 0;
                $entML = strtolower($employee->gender ?? '') === 'female' ? 40 : 0;

                // Opening used (before fromDate)
                $openingCL = $openingLeaves
                    ->where('employee_id', $empId)
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'casual') ? $l->total_leave_days : 0), 0);
                $openingAL = $openingLeaves
                    ->where('employee_id', $empId)
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'annual') ? $l->total_leave_days : 0), 0);
                $openingML = $openingLeaves
                    ->where('employee_id', $empId)
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'maternity') ? $l->total_leave_days : 0), 0);

                // Opening Balances
                $obCL = $entCL - $openingCL;
                $obAL = $entAL - $openingAL;
                $obML = $entML - $openingML;

                // Period availed
                $availedCL = $empPeriodLeaves
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'casual') ? $l->total_leave_days : 0), 0);
                $availedAL = $empPeriodLeaves
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'annual') ? $l->total_leave_days : 0), 0);
                $availedML = $empPeriodLeaves
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'maternity') ? $l->total_leave_days : 0), 0);
                $availedUnpaid = $empPeriodLeaves
                    ->reduce(fn($sum, $l) => $sum + (str_contains(strtolower($l->leaveType->title), 'unpaid') ? $l->total_leave_days : 0), 0);

                // Closing Balances
                $cbCL = $obCL - $availedCL;
                $cbAL = $obAL - $availedAL;
                $cbML = $obML - $availedML;

                // Totals
                $totalAvailed = $availedCL + $availedAL + $availedML + $availedUnpaid;
                $totalBalance = $cbCL + $cbAL + $cbML;

                $reportData[$branchName][] = [
                    'employee' => $employee,
                    'ob' => ['CL' => $obCL, 'AL' => $obAL, 'ML' => $obML],
                    'availed' => ['CL' => $availedCL, 'AL' => $availedAL, 'ML' => $availedML, 'Unpaid' => $availedUnpaid],
                    'cb' => ['CL' => $cbCL, 'AL' => $cbAL, 'ML' => $cbML],
                    'totalAvailed' => $totalAvailed,
                    'totalBalance' => $totalBalance,
                ];
            }
        }

        // if ($request->filled('is_print') && $request->is_print == 1) {
        //     $bodyHtml = view('employee.reports.empLeavesRptPrint', compact('branches', 'employees', 'reportData', 'fromDate', 'toDate'))->render();
        //     $headerHtml = view('employee.report.pdf.header')->render();
        //     $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();

        //     $finalHtml = '
        //         <html><head>
        //         <style>
        //             @page {
        //                 margin-top: 100px;
        //                 margin-bottom: 100px;
        //             }
        //             body { font-family: sans-serif; font-size: 12px; }
        //             .header {
        //                 position: fixed;
        //                 top: -60px;
        //                 left: 0;
        //                 right: 0;
        //                 height: 100px;
        //                 text-align: center;
        //             }
        //             .footer {
        //                 position: fixed;
        //                 bottom: -60px;
        //                 left: 0;
        //                 right: 0;
        //                 height: 50px;
        //                 text-align: center;
        //                 font-size: 10px;
        //                 color: #888;
        //             }
        //         </style>
        //         </head>
        //         <body>
        //             ' . $headerHtml . '
        //             <div class="footer">' . $footerHtml . '</div>
        //             ' . $bodyHtml . '
        //         </body></html>';

        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);

        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($finalHtml);
        //     $dompdf->setPaper('A4', 'landscape');
        //     $dompdf->render();

        //     return $dompdf->stream('employee_leave_report.pdf', ['Attachment' => false]);
        // }

        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $branchName = $request->branches ?? 'All Branches';
            $data = [
                'reportData' => $reportData,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeLeaveReportExport($data, $request->all()), 'employee_leave_report.xlsx');
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $branchName = $request->branches ?? 'All Branches';
            $data = [
                'reportData' => $reportData,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeLeaveReportExport($data, $request->all()), 'employee_leave_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        // Regular view
        return view('employee.reports.empLeaveRpt', compact(
            'branches',
            'employees',
            'reportData',
            'fromDate',
            'toDate'
        ));
    }

    public function staffTurnOver(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = Employee::where('created_by', \Auth::user()->creatorId());
        } else {
            $query = Employee::where('owned_by', \Auth::user()->ownedId());
        }
        $from_date = date('Y-m-d', strtotime('start of this month'));
        $to_date = date('Y-m-d');
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = date('Y-m-d', strtotime($request->input('from_date')));
            $to_date = date('Y-m-d', strtotime($request->input('to_date')));
            $query->whereHas('resignation', function ($q) use ($from_date, $to_date) {
                $q->whereBetween('last_attendance_date', [$from_date, $to_date]);
            });
        }
        $employees = $query->get();
        // @dd($employees);
        return view('employee.reports.staff_turnover', compact('employees', 'from_date', 'to_date'));
    }
    public function empLoan(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = Loan::where('created_by', \Auth::user()->creatorId());
        } else {
            $query = Loan::where('owned_by', \Auth::user()->ownedId());
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = date('Y-m-d', strtotime($request->input('start_date')));
            $end_date = date('Y-m-d', strtotime($request->input('end_date')));

            $query->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('from_pay_month', [$start_date, $end_date])
                    ->orWhereBetween('loan_ended', [$start_date, $end_date])
                    ->orWhere(function ($q2) use ($start_date, $end_date) {
                        $q2->where('from_pay_month', '<=', $start_date)
                            ->where('loan_ended', '>=', $end_date);
                    });
            });
        }

        $loans = $query->get();

        $start_date = $request->input('start_date') ? date('Y-m-d', strtotime($request->input('start_date'))) : null;
        $end_date = $request->input('end_date') ? date('Y-m-d', strtotime($request->input('end_date'))) : null;
        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $start_date]);
            $request->merge(['date_to' => $end_date]);
            return Excel::download(new EmployeeLoanReportExport($loans, $request->all()), 'employee_loan_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $start_date]);
            $request->merge(['date_to' => $end_date]);
            return Excel::download(new EmployeeLoanReportExport($loans, $request->all()), 'employee_loan_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.loan_report', compact('loans'));
    }
    public function empInsuranceReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = HealthInsurance::where('created_by', \Auth::user()->creatorId());
        } else {
            $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = HealthInsurance::where('owned_by', \Auth::user()->ownedId());
        }
        if ($request->filled('branch') && $request->branch != '') {
            $query->where('owned_by', $request->branch);
        }
        if ($request->filled('employee_id') && $request->employee_id != '') {
            $query->where('employee_id', $request->employee_id);
        }
        $data = $query->with(['employee.userbranch', 'employee.department', 'employee.designation'])->get();

        // Group insurance data by branch
        $branchWiseData = $data->groupBy(function ($insurance) {
            return optional($insurance->employee->userbranch)->name ?? 'No Branch';
        });

        if ($request->filled('export') && $request->export == 'excel') {
            return Excel::download(new EmployeeInsuranceReportExport($branchWiseData, $employees, $branches), 'employee_insurance_report.xlsx');
        }
        if ($request->filled('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeeInsuranceReportExport($branchWiseData, $employees, $branches), 'employee_insurance_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        
        return view('employee.reports.emp_insurance_report', compact('employees', 'branches', 'branchWiseData'));
    }
    public function empAnnualSrs(Request $request)
    {
        // Date range setup
        $fromDate = $request->input('from_date')
            ? date('Y-m-01', strtotime($request->from_date))
            : date('Y-m-01', strtotime('-1 year'));

        $toDate = $request->input('to_date')
            ? date('Y-m-t', strtotime($request->to_date))
            : date('Y-m-t');

        if (\Auth::user()->type == 'company') {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())
                ->get()->pluck('name', 'id');

            $branches = User::where('type', 'branch')
                ->where('created_by', \Auth::user()->creatorId())
                ->pluck('name', 'id');

            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            $query = EmployeePayscaleDetail::where('created_by', \Auth::user()->creatorId());
        } else {
            $employees = Employee::where('owned_by', \Auth::user()->ownedId())
                ->get()->pluck('name', 'id');

            $branches = User::where('id', \Auth::user()->ownedId())
                ->pluck('name', 'id');

            $branches->prepend('Select Branch', '');

            $query = EmployeePayscaleDetail::where('owned_by', \Auth::user()->ownedId());
        }

        // Branch filter
        if ($request->filled('branch') && $request->branch != '') {
            $query->where('owned_by', $request->branch);
        }

        // Employee filter
        if ($request->filled('employee_id') && $request->employee_id != '') {
            $query->where('employee_id', $request->employee_id);
        }

        // Always apply date filter
        $query->whereBetween('effect_from', [$fromDate, $toDate]);

        // Get all data within range
        $allData = $query->with('employee.userbranch', 'employee.department', 'employee.designation')->get();

        // Group by employee and pick last 2 payscale records
        $annualSrsData = $allData->groupBy('employee_id')->map(function ($records) {
            $sorted = $records->sortByDesc('id');
            return [
                'latest' => $sorted->first(),         // latest
                'previous' => $sorted->skip(1)->first() // second latest
            ];
        });

        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $branchName = $request->branches ?? 'All Branches';
            $data = [
                'annualSrsData' => $annualSrsData,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeAnnualSrsReportExport($data, $request->all()), 'employee_annual_srs_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $fromDate]);
            $request->merge(['date_to' => $toDate]);
            $branchName = $request->branches ?? 'All Branches';
            $data = [
                'annualSrsData' => $annualSrsData,
                'branches' => $branches,
                'employees' => $employees,
                'request' => $request,
                'branchName' => $branchName,
            ];
            return Excel::download(new EmployeeAnnualSrsReportExport($data, $request->all()), 'employee_annual_srs_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.emp_annual_srs', compact('employees', 'branches', 'annualSrsData'));
    }
    
    public function empRetirementReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = Employee::where('created_by', \Auth::user()->creatorId())->where('is_res_ter', 0);
        } else {
            $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = Employee::where('owned_by', \Auth::user()->ownedId())->where('is_res_ter', 0);
        }
        if ($request->filled('branch') && $request->branch != '') {
            $query->where('owned_by', $request->branch);
        }
        if ($request->filled('employee_id') && $request->employee_id != '') {
            $query->where('id', $request->employee_id);
        }
        $employeesdata = $query->get();
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new EmployeeRetirementReportExport($employeesdata), 'employee_retirement_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new EmployeeRetirementReportExport($employeesdata), 'employee_retirement_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('employee.reports.emp_retirement_report', compact('employees', 'branches', 'employeesdata'));
    }
	public function employeeProfileReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $query = Employee::with('userbranch', 'department', 'designation')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $query = Employee::with('userbranch', 'department', 'designation')->where('owned_by', $userOwnedId);
        }

        if ($request->filled('branch') && $request->branch != 'all') {
            $query->where('branch_id', $request->branch);
        }

        if ($request->filled('department_id') && $request->department_id != 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('designation_id') && $request->designation_id != 'all') {
            $query->where('designation_id', $request->designation_id);
        }

        if ($request->filled('status')) {
            $query->where('is_res_ter', $request->status);
        }

        $employees = $query->orderBy('name')->get();

        $departments = Department::where('created_by', $userCreatorId)->pluck('name', 'id');
        $departments->prepend('All Departments', 'all');

        $designations = Designation::where('created_by', $userCreatorId)->pluck('name', 'id');
        $designations->prepend('All Designations', 'all');

        $statuses = [
            '' => 'All Statuses',
            '0' => 'Active',
            '1' => 'Resigned',
            '2' => 'Terminated',
        ];

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Employee Profile Report';
            $branchName = isset($branches[$request->branch]) ? $branches[$request->branch] : 'All Branches';
            return Excel::download(new EmployeeProfileReportExport($employees, $branchName, $report_name), 'employee_profile_report.xlsx');
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Employee Profile Report';
            $branchName = isset($branches[$request->branch]) ? $branches[$request->branch] : 'All Branches';
            return Excel::download(new EmployeeProfileReportExport($employees, $branchName, $report_name), 'employee_profile_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('employee.reports.employee_profile_report', compact('employees', 'branches', 'departments', 'designations', 'statuses', 'request'));
    }
}
