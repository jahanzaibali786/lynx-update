<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyStatistics;
use App\Exports\AdmissionWithdrawalReport;
use App\Exports\PeriodWiseStatisticExport;
use App\Exports\AdmissionListingExport;
use App\Exports\StudentAccountStatementExport;
use App\Exports\studenttransferinReport;
use App\Exports\AdvanceChallanreport;
use App\Exports\StudentWithdrawlListingExport;
use App\Exports\AdmissionDetailReportExport;
use App\Exports\StudentFeeReceiptDetailExport;
use App\Exports\ClassWiseFeeStructureExport;
use App\Exports\MonthlyChallanreport;
use App\Exports\MonthlyPreChallanreport;
use App\Exports\Student_defaulterReport;
use App\Exports\StudentRegistrationExport;
use App\Exports\TuitionFeeReportExport;
use App\Exports\SessionWiseReportExport;
use App\Exports\SessionMonthWiseReportExport;
use App\Exports\StudentSecurityReportExport;
use App\Exports\FeeReceiptSummaryExport;
use App\Exports\SessionBranchWiseReportExport;
use App\Exports\SessionMonthWiseBranchReportExport;
use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\FeeHead;
use App\Models\JournalItem;
use App\Models\StudentPromotions;
use App\Models\ConcessionPolicyHead;
use App\Models\StudentFeeStructure;
use App\Models\Concession;
use App\Models\Registring_option;
use App\Models\Session;
use App\Models\StudentReceipt;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\StudentWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use App\Models\StudentSecurity;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;

class StudentReportController extends Controller
{
    public function admissionlist()
    {
        $studenttransfer = Challans::with('student', 'class', 'enrollment')->where('type', 'Admission')->where('created_by', Auth::user()->creatorId())->get();

        return view('studentReports.transferinindex', compact('studenttransfer'));
    }
    public function transferinindex(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }
        // ->get();
        $query = StudentTransfer::with('student', 'branchfrom', 'branchto', 'classto', 'classfrom', 'enrollment')->where('created_by', Auth::user()->creatorId());
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('branch_to', $request->branches);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('transfer_date', [$dateFrom, $dateTo]);


        if (!empty($request->type) && in_array($request->type, ['inter branch', 'inter city'])) {
            $query->where('transfer_type', $request->type);
        }

        $studenttransfer = $query->get();

        return view('studentReports.transferinindex', compact('studenttransfer', 'branches', 'request'));
    }
    public function transferinReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }
        // ->get();
        $query = StudentTransfer::with('student', 'branchfrom', 'branchto', 'classto', 'classfrom', 'enrollment')->where('created_by', Auth::user()->creatorId());
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('branch_to', $request->branches);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('transfer_date', [$dateFrom, $dateTo]);


        if (!empty($request->type) && in_array($request->type, ['inter branch', 'inter city'])) {
            $query->where('transfer_type', $request->type);
        }

        $studenttransfer = $query->get();
        $report_name = 'Transfer In Report';

         if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Transfer In Report';
            return Excel::download(new studenttransferinReport($studenttransfer, $branches, $report_name, $request->all()), 'transferin_report.xlsx');
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Transfer In Report';
            return Excel::download(new studenttransferinReport($studenttransfer, $branches, $report_name, $request->all()), 'transferin_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        $pdf = new Dompdf();
        $html = view('studentReports.transferinreport', compact('studenttransfer', 'branches', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('request', 'branches', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
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
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function transferoutindex(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }
        $query = StudentTransfer::with('student', 'branchfrom', 'branchto', 'classto', 'classfrom', 'enrollment')->where('created_by', Auth::user()->creatorId());
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('branch_from', $request->branches);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('transfer_date', [$dateFrom, $dateTo]);


        if (!empty($request->type) && in_array($request->type, ['inter branch', 'inter city'])) {
            $query->where('transfer_type', $request->type);
        }

        $studenttransfer = $query->get();
        return view('studentReports.transferoutindex', compact('studenttransfer', 'branches', 'request'));
    }
    public function transferoutReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }
        $query = StudentTransfer::with('student', 'branchfrom', 'branchto', 'classto', 'classfrom', 'enrollment')->where('created_by', Auth::user()->creatorId());
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('branch_from', $request->branches);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('transfer_date', [$dateFrom, $dateTo]);


        if (!empty($request->type) && in_array($request->type, ['inter branch', 'inter city'])) {
            $query->where('transfer_type', $request->type);
        }

        $studenttransfer = $query->get();
        $report_name = 'Transfer Out Report';

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Transfer Out Report';
            return Excel::download(new studenttransferinReport($studenttransfer, $branches, $report_name, $request->all()), 'transferout_report.xlsx');
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Transfer Out Report';
            return Excel::download(new studenttransferinReport($studenttransfer, $branches, $report_name, $request->all()), 'transferout_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        $pdf = new Dompdf();
        $html = view('studentReports.transferoutreport', compact('studenttransfer', 'branches', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('request', 'branches', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
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
    public function classwisefeereportindex(Request $request)
    {
        // Get fee heads with their IDs as a collection of objects
        $heads = FeeHead::select('id', 'fee_head')->orderBy('fee_head')->get();
    
        // Branch logic remains the same
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }
    
        $session = Session::where('created_by', \Auth::user()->creatorId())->pluck('year', 'id');
    
        // Query classes with their fee heads
        $query = Classes::with(['classhead' => function($query) {
            $query->with('feeHead'); // Eager load feeHead relationship
        }]);
    
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('owned_by', $request->branches);
        }
    
        $classes = $query->get();
        $report_name = 'Classwise Fee Structure Report';
    
        if ($request->has('export') && in_array($request->export, ['excel', 'pdf'])) {
    $branchId = $request->branches ?? null;
    $exportType = $request->export == 'pdf' ? \Maatwebsite\Excel\Excel::MPDF : \Maatwebsite\Excel\Excel::XLSX;
    $extension = $request->export == 'pdf' ? 'pdf' : 'xlsx';
    
    return Excel::download(
        new ClassWiseFeeStructureExport(
            $classes, 
            $branches, 
            $branchId,
            $heads,
            $session, 
            $report_name, 
            $request->all()
        ), 
        'classwise_fee_structure_report.'.$extension, 
        $exportType
    );
}
    
        return view('studentReports.class_wisefee_report', compact('heads', 'classes', 'branches', 'session'));
    }
    public function classwisefeeStructurereport(Request $request)
    {
        $heads = FeeHead::all();

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $session = Session::where('created_by', \Auth::user()->creatorId())->pluck('year', 'id');
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $session = Session::where('owned_by', \Auth::user()->ownedId())->pluck('year', 'id');
        }
        // $classWiseFees = ClassWiseFee::where('class_id', $classId)
        // ->join('fee_heads', 'class_wise_fees.head_id', '=', 'fee_heads.id')
        // ->select('class_wise_fees.head_id', 'fee_heads.fee_head as head_name', 'class_wise_fees.amount')
        // ->get();
        $query = Classes::with('classhead');
        // $query = Classes::join('class_wise_fees','classes.id','=','class_wise_fees.class_id');
        if ($request->has('branches') && !empty($request->branches)) {
            $query->where('owned_by', $request->branches);
        }
        // if ($request->has('session') && !empty($request->session)) {
        //     $query->where('session_id', $request->session);
        // }
        
        $classes = $query->get();
        $report_name = 'Classwise Fee Structure';

        $pdf = new Dompdf();
        $html = view('studentReports.classwisefeereport', compact('classes', 'heads', 'branches'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('classes', 'request', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
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
    public function admissionwithdrawal(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $filterApplied = false;
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal', 'withdrawal.class')->where('owned_by', $userOwnedId);
        }

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $filterApplied = true;
        }
        if (!empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
            $filterApplied = true;
        }
        if (!empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
            $filterApplied = true;
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('created_at', [$dateFrom, $dateTo]);

        if (!empty($request->type)) {
            if ($request->type == 'Admissions') {
                $query->whereDoesntHave('withdrawal');
                $filterApplied = true;
            } elseif ($request->type == 'Withdrawal') {
                $query->whereHas('withdrawal');
                $filterApplied = true;
            }
        }
        if ($filterApplied) {
            $all_data = $query->get();
        } else {
            $all_data = collect();
        }

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Admission & Withdrawal Report';
            return Excel::download(new AdmissionWithdrawalReport($all_data, $branches, $report_name, $request->all()), 'AdmissionWithdrawal_report.xlsx');
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Admission & Withdrawal Report';
            return Excel::download(new AdmissionWithdrawalReport($all_data, $branches, $report_name, $request->all()), 'AdmissionWithdrawal_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('studentReports.admission_withdrawal_report', compact('branches', 'all_data', 'request'));
    }
    public function admissionwithdrawalPdfReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $filterApplied = false;
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal', 'withdrawal.class')->where('owned_by', $userOwnedId);
        }

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $filterApplied = true;
        }
        if (!empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
            $filterApplied = true;
        }
        if (!empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
            $filterApplied = true;
        }
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('created_at', [$dateFrom, $dateTo]);

        if (!empty($request->type)) {
            if ($request->type == 'Admissions') {
                $query->whereDoesntHave('withdrawal');
                $filterApplied = true;
            } elseif ($request->type == 'Withdrawal') {
                $query->whereHas('withdrawal');
                $filterApplied = true;
            }
        }
        if ($filterApplied) {
            $all_data = $query->get();
        } else {
            $all_data = collect();
        }

        $report_name = 'Admission & Withdrawal Report';

        $pdf = new Dompdf();
        $html = view('studentReports.admission_withdrawal_pdf_report', compact('all_data', 'branches', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('request', 'report_name', 'branches'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    public function studentstrength(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('active_status',1)->where('created_by', $userCreatorId)->groupBy('owned_by', 'section_id');
        } else {
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('active_status',1)->where('owned_by', $userOwnedId)->groupBy('owned_by', 'section_id');
        }

        if (!empty($request->date)) {
            $query->Wheredate('adm_date', '<=', $request->date);
        }
        $all_data = $query->orderBy('owned_by')->get();
        if ($request->has('is_print') && $request->is_print == 1) {
            $html = view('studentReports.student_strength_pdf_report', compact('all_data'))->render();
            $headerHtml = view('studentReports.pdf_header', compact('request'))->render();
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();
            return $dompdf->stream('StudentStrength.pdf', ['Attachment' => false]);

        }
        return view('studentReports.student_strength_report', compact('all_data'));
    }
    public function studentstatistic(Request $request)
    {

        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('created_by', $userCreatorId)->groupBy('owned_by', 'section_id');
        } else {
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('owned_by', $userOwnedId)->groupBy('owned_by', 'section_id');

        }

        if (!empty($request->date)) {
            $query->Wheredate('adm_date', '<=', $request->date);
        }
        $all_data = $query->orderBy('owned_by')->get();
        // dd($all_data);
        return view('studentReports.student_statistic_report', compact('all_data'));
    }
public function registrationDetailReport(Request $request)
{
    $userType = \Auth::user()->type;
    $userCreatorId = \Auth::user()->creatorId();
    $userOwnedId = \Auth::user()->ownedId();
    $branch = 'All Branches';

    // Optimized branch query
    if ($userType == 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', $userCreatorId)
            ->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('All Branches', 'all');
    } else {
        $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
        $branches->prepend('All Branches', 'all');
    }

    // Base query with optimized eager loading and select
    $query = StudentRegistration::with([
            'class:id,name',
            'branches:id,name',
            'session:id,year',
            'registeroption:id,name'
        ])
        ->select([
            'id', 'reg_no', 'regdate', 'stdname', 'fathername', 'session_id', 
            'class_id', 'dob', 'gender', 'fatherphone', 'roll_no', 
            'register_option', 'registrationfee', 'owned_by', 'created_by',
            'student_status'
        ]);
    $classes = Classes::where('created_by', $userCreatorId)->where('active_status', 1)->get()->pluck('name', 'id');

    $query = $userType == 'company' 
        ? $query->where('created_by', $userCreatorId)
        : $query->where('owned_by', $userOwnedId);

    // Filter conditions
    if ($request->has('branch') && $request->branch != '') {
        if ($request->branch == 'all') {
            $query->where('created_by', $userCreatorId);
        } else {
            $query->where('owned_by', $request->branch);
            $branch = $branches[$request->branch];
            $classes = Classes::where('owned_by', $request->branch)->where('active_status', 1)->get()->pluck('name', 'id');
        }
    }

    $classes->prepend('All Classes', '');

    if ($request->has('class') && $request->class != '' && $request->class != 'all') {
        $query->where('class_id', $request->class);
    }
    if ($request->has('register') && $request->register != '') {
        $query->where('register_option', $request->register);
    }
    if ($request->date_from && $request->date_from != '') {
        $query->where('regdate', '>=', $request->date_from);
    }
    if ($request->date_to && $request->date_to != '') {
        $query->where('regdate', '<=', $request->date_to);
    }
    if (!empty($request->status)) {
        if ($request->status == 'Registered') {
            $query->where('student_status', 'Registered');
        } elseif ($request->status == 'Enrolled') {
            $query->where('student_status', '!=', 'Registered');
        }
    }

    if (empty($request->date_from) && empty($request->date_to)) {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
        $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
        $request->merge(['date_from' => $dateFrom]);
        $request->merge(['date_to' => $dateTo]);
        $query->whereBetween('regdate', [$dateFrom, $dateTo]);
    }

    $status = [
        'all' => 'All Status',
        'Enrolled' => 'Enrolled',
        'Registered' => 'Not Enrolled',
    ];

    // Get all student IDs first to optimize challan query
    $studentIds = $query->pluck('id');
    
    // Get all challans in one query
    // $challansData = Challans::whereIn('student_id', $studentIds)
    //     ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%registration%')])
    //     ->get()
    //     ->keyBy('student_id');

    // Get grouped student data
    $studentData = $query->get()->groupBy('owned_by');
    
    $branchTotals = [];
    $grandTotal = 0;

    foreach ($studentData as $branchId => $students) {
        $branchTotal = 0;
        foreach ($students as $student) {
            // $challan = $challansData[$student->id] ?? null;
            // $branchTotal += $challan ? $challan->paid_amount : 0;
            $branchTotal += $student->registrationfee;
        }
        $branchTotals[$branchId] = $branchTotal;
        $grandTotal += $branchTotal;
    }

    $registerOption = Registring_option::where('created_by', $userCreatorId)->get()->pluck('name', 'id');
    $registerOption->prepend('Select Option', '');
    if ($request->has('export') && $request->export == 'excel') {
        $report_name = 'Student Registration Report';
        $branchName = $branches[$request->branch] ?? 'All Branches';
        return Excel::download(
            new StudentRegistrationExport($studentData, $branches, $branchName, $report_name, $request->all(),$branchTotals, $grandTotal), 
            'student_registration_report.xlsx'
        );
    }
    
    if ($request->has('export') && $request->export == 'pdf') {
        $report_name = 'Student Registration Report';
        $branchName = $branches[$request->branch] ?? 'All Branches';
        return Excel::download(
            new StudentRegistrationExport($studentData, $branches, $branchName, $report_name, $request->all(),$branchTotals, $grandTotal), 
            'student_registration_report.pdf', 
            \Maatwebsite\Excel\Excel::MPDF
        );
    }

    return view('studentReports.registrationdetailreport', compact(
        'studentData', 'branches', 'classes', 'status', 
        'branchTotals', 'grandTotal', 'request', 'registerOption'
    ));
}
    public function registrationdetailReportPdf(Request $request)
    {

        $registerOption = Registring_option::pluck('name', 'id');
        $registerOption->prepend('Select Option', '');
        $classes = Classes::pluck('name', 'id');
        $classes->prepend('Select Class', '');
        $report_name = 'Student Registration Detail';
        $pdf = new Dompdf();
        $html = view('studentReports.registrationdetail_report_pdf', compact('studentData', 'request', 'branchTotals', 'grandTotal', 'challans', 'branches'))->render();
        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    // public function admissionlisting(Request $request)
    // {
    //     set_time_limit(0);  
    //     $userType = \Auth::user()->type;
    //     $userCreatorId = \Auth::user()->creatorId();
    //     $userOwnedId = \Auth::user()->ownedId();


    //     if ($userType == 'company') {
    //         $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
    //         $branches->prepend(\Auth::user()->name, \Auth::user()->id);
    //         $branches->prepend('All Branches', 'all');
    //         $query = StudentEnrollments::with('class', 'branch', 'StudentRegistration')->where('created_by', $userCreatorId);
    //     } else {
    //         $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
    //         $branches->prepend('All Branches', 'all');
    //         $query = StudentEnrollments::with('class', 'branch', 'StudentRegistration')->where('owned_by', $userOwnedId);
    //     }
    //     if ($request->has('branch') && $request->branch != '') {
    //         if ($request->branch == 'all') {
    //             $query->whereIn('owned_by', $branches->keys()->except('all'));
    //         } else {
    //             $query->where('owned_by', $request->branch);
    //         }
    //     }
    //     if ($request->has('class') && $request->class != '') {
    //         $query->where('class_id', $request->class);
    //     }
    //     // dd($query->get(),$request->branch);
    //     // dd($query->get());
    //     if ($request->has('student') && $request->student != '') {
    //         // dd($request->student);
    //         $query->where('regId', $request->student);
    //     }
    //     if ($request->has('student') && $request->student != '') {
    //         // dd($request->student);
    //         $query->where('regId', $request->student);
    //     }
    //     if ($request->has('student') && $request->student != '') {
    //         // dd($request->student);
    //         $query->where('regId', $request->student);
    //     }

    //     $dateFrom = $request->input('date_from');
    //     $dateTo = $request->input('date_to');

    //     if (empty($dateFrom) && empty($dateTo)) {
    //         $currentYear = date('Y');
    //         $currentMonth = date('m');

    //         $dateFrom = ($currentMonth >= 7)
    //             ? "$currentYear-07-01 00:00:00"
    //             : date('Y-07-01 00:00:00', strtotime('-1 year'));

    //         $dateTo = ($currentMonth >= 7)
    //             ? date('Y-06-30 23:59:59', strtotime('+1 year'))
    //             : "$currentYear-06-30 23:59:59";
    //         $request->merge(['date_from' => substr($dateFrom, 0, 10)]);
    //         $request->merge(['date_to' => substr($dateTo, 0, 10)]);
    //     }
    //     if (!empty($dateFrom) && !empty($dateTo)) {
    //         $query->whereBetween('adm_date', [$dateFrom, $dateTo]);
    //     }

    //     $studentData = $query->get()->groupBy('owned_by');
    //     $std_ids = $query->pluck('regId')->toArray();
    //     $branchTotals = [];
    //     $grandTotal = 0;
    //     $heads = DB::table('challan_heads')
    //         ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
    //         ->join('fee_heads', 'challan_heads.head_id', '=', 'fee_heads.id')
    //         ->whereIn('challans.student_id', $std_ids)
    //         ->whereRaw('LOWER(challans.challan_type) LIKE ?', [strtolower('%admission%')])
    //         ->distinct()
    //         ->select('challan_heads.head_id as id', 'fee_heads.fee_head')->get();

    //     foreach ($studentData as $branchId => $students) {

    //         $branchTotal = 0;
    //         foreach ($students as $student) {
    //             $studentTotal = 0;
    //             $challan = Challans::where('student_id', @$student->StudentRegistration->reg_no)
    //                 ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
    //                 ->with('heads', 'heads.feehead')
    //                 ->first();
    //             if ($challan) {
    //                 foreach ($challan->heads as $head) {
    //                     $studentTotal += (int) $head->price ?? 0;
    //                 }
    //             }
    //             $branchTotal += $studentTotal;
    //             $student->total_amount = $studentTotal;
    //         }
    //         $branchTotals[$branchId] = $branchTotal;
    //         $grandTotal += $branchTotal;
    //     }

    //     $classes = Classes::pluck('name', 'id');
    //     $classes->prepend('Select Class', '');
    //     $student = [];
    //     if ($request->has('print') && $request->print == 'pdf') {
    //         $report_name = 'Admission Listing';

    //         $pdf = new Dompdf();
    //         $html = view('studentReports.admissiondetailreport_pdf', compact('studentData', 'student', 'branches', 'heads', 'classes', 'branchTotals', 'grandTotal', 'request'))->render();
    //         $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
    //         $footerHtml = view('students.concession.report.pdf.footer')->render();

    //         $html = '<html><head>
    //     <style>
    //         @page {
    //             margin-top: 100px;
    //             margin-bottom: 100px;
    //         }
    //         .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
    //     </style>
    //     </head><body>
    //     ' . $headerHtml . '
    //     <div class="footer">' . $footerHtml . '</div>
    //     ' . $html . '
    //     </body></html>';

    //         $options = new Options();
    //         $options->set('isHtml5ParserEnabled', true);
    //         $options->set('isRemoteEnabled', true);
    //         $dompdf = new Dompdf($options);
    //         $dompdf->loadHtml($html);
    //         $dompdf->setPaper('A3', 'landscape');
    //         $dompdf->render();
    //         // $pdfContent = $dompdf->output();
    //         // $base64Pdf = base64_encode($pdfContent);
    //         // return response()->json(['base64Pdf' => $base64Pdf]);
    //         $dompdf->stream("document.pdf", ["Attachment" => false]);
    //         return $dompdf->stream('Admission-Listing.pdf');
    //     }
    //     // dd($studentData);
    //     return view('studentReports.admissiondetailreport', compact('studentData', 'student', 'branches', 'heads', 'classes', 'branchTotals', 'grandTotal', 'request'));
    // }

    public function admissionlisting(Request $request)
    {
        set_time_limit(0);
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        // Build branches query
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('created_by', $userCreatorId);
             $classes = Classes::where('created_by', $userCreatorId)->where('active_status', 1)->pluck('name', 'id');
            $classes->prepend('All Classes', 'all');
            $sections = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->whereIn('class_sections.class_id', $classes->keys()->except('all'))
            ->select('sections.id', 'sections.name')
            ->distinct()->pluck('sections.name', 'sections.id');
        $sections->prepend('All Sections', 'all');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
                ->where('owned_by', $userOwnedId);
                $classes = Classes::where('owned_by', $userOwnedId)->where('active_status', 1)->pluck('name', 'id');
            $classes->prepend('All Classes', 'all');
            $sections = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->whereIn('class_sections.class_id', $classes->keys()->except('all'))
            ->select('sections.id', 'sections.name')
            ->distinct()->pluck('sections.name', 'sections.id');
              $sections->prepend('All Sections', 'all');
        }

        $sections = DB::table('class_sections')
        ->join('sections', 'class_sections.section_id', '=', 'sections.id')
        ->whereIn('class_sections.class_id', $classes->keys()->except('all'))
        ->select('sections.id', 'sections.name')
        ->distinct()->pluck('sections.name', 'sections.id');
          $sections->prepend('All Sections', 'all');

        // Apply filters
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

        // Remove duplicate student filter code (you had it 3 times)
        if ($request->has('student') && $request->student != '') {
            $query->where('regId', $request->student);
        }

        if ($request->has('sections') && $request->sections != '') {
            $query->where('section_id', $request->sections);
        }

        // Handle date filtering
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

        // Get student data
        $studentData = $query->get()->groupBy('owned_by');
        $std_ids = $query->pluck('enrollId')->toArray();

        // OPTIMIZATION 1: Get all challan heads in one query instead of multiple queries
        $heads = DB::table('challan_heads')
            ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->join('fee_heads', 'challan_heads.head_id', '=', 'fee_heads.id')
            ->whereIn('challans.rollno', $std_ids)
            ->whereRaw('LOWER(challans.challan_type) LIKE ?', [strtolower('%admission%')])
            ->distinct()
            ->select('challan_heads.head_id as id', 'fee_heads.fee_head')
            ->get();
        // OPTIMIZATION 2: Get all challans with their heads in one query
        $challansData = Challans::whereIn('rollno', $std_ids)
            ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
            ->with(['heads.feehead'])
            ->get()
            ->keyBy('rollno');
         // Key by student_id for fast lookup
        // OPTIMIZATION 3: Pre-calculate all totals and organize challan data
        $branchTotals = [];
        $grandTotal = 0;
        $studentChallanData = []; // Store processed challan data

        foreach ($studentData as $branchId => $students) {
            $branchTotal = 0;

            foreach ($students as $student) {
                $studentTotal = 0;
                $studentRegNo = @$student->enrollId;
                $challanHeads = [];
                
                // Get challan data from our pre-loaded collection
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

                    // Store challan info for template use
                    $studentChallanData[$studentRegNo] = [
                        'challan_no' => $challan->challanNo,
                        'challan_id' => $challan->id,
                        'heads' => $challanHeads,
                        'total' => $studentTotal
                    ];
                    
                } else {
                    // No challan found for this student
                    $studentChallanData[$studentRegNo] = [
                        'challan_no' => '',
                        'challan_id' => '',
                        'heads' => [],
                        'total' => 0
                    ];
                }
                $branchTotal += $studentTotal;
                $student->total_amount = $studentTotal;
            }

            $branchTotals[$branchId] = $branchTotal;
            $grandTotal += $branchTotal;
        }
       
        $student = [];

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Admission Listing Report';
            $branchName = $branches[$request->branch] ?? 'All Branches';
            return Excel::download(new AdmissionListingExport($request, $branchName, $report_name, $branches, $request->all()), 'admission_listing_report.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Admission Listing Report';
            $branchName = $branches[$request->branch] ?? 'All Branches';
            return Excel::download(new AdmissionListingExport($request, $branchName, $report_name, $branches, $request->all()), 'admission_listing_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        
        // Handle PDF generation
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Admission Listing';

            $pdf = new Dompdf();
            $html = view('studentReports.admissiondetailreport_pdf', compact(
                'studentData',
                'student',
                'branches',
                'heads',
                'classes',
                'branchTotals',
                'grandTotal',
                'request',
                'studentChallanData' // Pass pre-calculated data
            ))->render();

            $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();

            $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
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
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();

            $dompdf->stream("document.pdf", ["Attachment" => false]);
            //         return $dompdf->stream('Admission-Listing.pdf');
        }
        if($request->has('export') && $request->export == 'excel'){
            return Excel::download(new AdmissionDetailReportExport($studentData, $student, $branches, $heads, $classes, $branchTotals, $grandTotal, $request, $studentChallanData), 'Admission-Listing.xlsx');
        }
        // Return view with pre-calculated data
        return view('studentReports.admissiondetailreport', compact(
            'studentData',
            'student',
            'branches',
            'heads',
            'classes',
            'sections',
            'branchTotals',
            'grandTotal',
            'request',
            'studentChallanData' // Pass pre-calculated data to view
        ));
    }
    public function admissionlistingReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $heads = FeeHead::all();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with('class', 'branch', 'StudentRegistration')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $query = StudentEnrollments::with('class', 'branch', 'StudentRegistration')->where('owned_by', $userOwnedId);
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
            // dd($request->student);
            $query->where('regId', $request->student);
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }
        $query->whereBetween('created_at', [$dateFrom, $dateTo]);

        // if (!empty($dateFrom) && !empty($dateTo)) {
        //     $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        // }

        $studentData = $query->get()->groupBy('owned_by');
        $branchTotals = [];
        $grandTotal = 0;

        foreach ($studentData as $branchId => $students) {
            $branchTotal = 0;
            foreach ($students as $student) {
                $studentTotal = 0;
                $challan = Challans::where('student_id', $student->StudentRegistration->id)
                    ->whereRaw('LOWER(challan_type) LIKE ?', [strtolower('%admission%')])
                    ->with('heads', 'heads.feehead')
                    ->first();
                if ($challan) {
                    foreach ($challan->heads as $head) {
                        $studentTotal += $head->price ?? 0;
                    }
                }
                $branchTotal += $studentTotal;
                $student->total_amount = $studentTotal;
            }
            $branchTotals[$branchId] = $branchTotal;
            $grandTotal += $branchTotal;
        }

        $classes = Classes::pluck('name', 'id');
        $classes->prepend('Select Class', '');
        $student = [];
        $report_name = 'Admission Listing';

        $pdf = new Dompdf();
        $html = view('studentReports.admissiondetailreport_pdf', compact('studentData', 'student', 'branches', 'heads', 'classes', 'branchTotals', 'grandTotal', 'request'))->render();
        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
        $footerHtml = view('students.concession.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
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

        // return $dompdf->stream('concession.pdf');
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
public function student_fee_receipt_detail(Request $request)
{
    /* =======================
     * Bank Accounts
     * ======================= */
    $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
        ->where('created_by', \Auth::user()->creatorId())
        ->pluck('name', 'id')
        ->toArray();
    $accounts = ['allbank' => 'Select all banks'] + $accounts;

    /* =======================
     * Branches & Base Query
     * ======================= */
    if (\Auth::user()->type === 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('All Branches', '');
        $query = StudentReceipt::where('created_by', \Auth::user()->creatorId());
    } else {
        $branches = User::where('id', \Auth::user()->ownedId())
            ->pluck('name', 'id');
        $branches->prepend('All Branches', '');
        $query = StudentReceipt::where('owned_by', \Auth::user()->ownedId());
    }

    /* =======================
     * Detect Filters
     * ======================= */
    $hasFilters = $request->filled('from_date') ||
                  $request->filled('to_date') ||
                  $request->filled('default_bank') ||
                  $request->filled('branches') ||
                  $request->filled('head') ||
                  $request->filled('voucher') ||
                  $request->filled('class') ||
                  $request->filled('student');

    /* =======================
     * Apply Voucher Filter
     * ======================= */
    if ($hasFilters) {
        $query->whereHas('voucher', function ($q) {
            $q->where('credit', '!=', 0)
              ->orWhere('types', 'Challan Payment');
        });
    }

    /* =======================
     * Eager Load
     * ======================= */
    $query->with([
        'bank:id,bank_name',
        'voucher' => function ($q) use ($hasFilters) {
            if ($hasFilters) {
                $q->where('credit', '!=', 0)
                  ->orWhere('types','Challan Payment');
            }
        },
        'voucher.heads:id,fee_head',
        'challan',
        'challan.student:id,stdname,roll_no',
        'challan.enrollstudent:id,enrollId',
        'challan.class:id,name',
    ]);

    /* =======================
     * Apply Filters
     * ======================= */

    if($request->filled('from_date') || $request->filled('to_date')) {
        $fromDate = $request->from_date ?? date('Y-m-d');
        $toDate = $request->to_date ?? $fromDate;
        $query->whereBetween('recipt_date', [$fromDate, $toDate]);
    }
    if ($request->filled('default_bank') && $request->default_bank !== 'allbank') {
        $query->where('bank_id', $request->default_bank);
    }
    if ($request->filled('branches')) {
        $query->where('owned_by', $request->branches);
    }
    if ($request->filled('head')) {
        $query->whereHas('challan.heads', function ($q) use ($request) {
            $q->where('head_id', $request->head);
        });
    }
    if ($request->filled('voucher') && $request->voucher !== 'all') {
        $query->where('voucher_id', $request->voucher);
    }
    if ($request->filled('class')) {
        $query->whereHas('challan', function ($q) use ($request) {
            $q->where('class_id', $request->class);
        });
    }
    if ($request->filled('student')) {
        // Check both enrollstudent.enrollId and student.roll_no
        $query->where(function ($q) use ($request) {
            $q->whereHas('challan.enrollstudent', function ($sq) use ($request) {
                $sq->where('enrollId', $request->student);
            })
            ->orWhereHas('challan.student', function ($sq) use ($request) {
                $sq->where('roll_no', $request->student);
            });
        });
    }

    /* =======================
     * Fetch Data
     * ======================= */
    if (!$hasFilters) {
        $receipts = collect();
    } else {
        $receipts = $query->get();
    }

    /* =======================
     * Aggregate by branch → voucher
     * ======================= */
    $groupedVouchers = $receipts
        ->groupBy('owned_by')
        ->map(function ($branchReceipts) {
            return $branchReceipts->groupBy('voucher_id')->map(function ($voucherReceipts) {
                $first = $voucherReceipts->first();
                return [
                    'voucher_id' => $first->voucher_id,
                    'challan_id' => $first->challan_id,
                    'student_id' => $first->student_id,
                    'voucher_items' => $first->voucher, // JournalItems
                    'total_amount' => $voucherReceipts->sum('recipt_amount'),
                    'bank' => $first->bank,
                    'challan' => $first->challan,
                    'receipts' => collect($voucherReceipts),
                ];
            });
        });

    /* =======================
     * Branch Names
     * ======================= */
    $branchNames = User::whereIn('id', $receipts->pluck('owned_by')->unique())
        ->pluck('name', 'id')
        ->toArray();

    /* =======================
     * Dropdown Defaults
     * ======================= */
    $heads = FeeHead::where('created_by', \Auth::user()->creatorId())
        ->pluck('fee_head', 'id');
    $heads->prepend('Select Fee Head', '');

    // Voucher dropdown - simple array for dropdown
    $voucherOptions = ['all' => 'All Vouchers'];
    
    // Get unique vouchers for dropdown if we have receipts
    if ($receipts->isNotEmpty()) {
        $uniqueVouchers = $receipts->pluck('voucher_id')->unique()->filter();
        foreach ($uniqueVouchers as $voucherId) {
            $voucherOptions[$voucherId] = 'Voucher #' . $voucherId;
        }
    }

    $session = [];
    $class = [];
    $students = [];

    return view(
        'studentReports.student_fee_receipt_detail',
        compact(
            'accounts',
            'groupedVouchers',
            'voucherOptions',
            'heads',
            'receipts',
            'session',
            'class',
            'students',
            'branches',
            'branchNames'
        )
    );
}



public function student_fee_receipt_detail_report(Request $request)
{
    /** =========================
     *  BANK ACCOUNTS
     *  ========================= */
    $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
        ->where('created_by', \Auth::user()->creatorId())
        ->pluck('name', 'id');

    $accounts = ['allbank' => 'Select all banks'] + $accounts->toArray();

    /** =========================
     *  BASE QUERY (ROLE BASED)
     *  ========================= */
    if (\Auth::user()->type === 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->pluck('name', 'id');

        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('All Branches', '');

        $query = StudentReceipt::with([
            'challan',
            'bank',
            'voucher' => function($q) {
                // Only load credit entries with fee heads for student payments
                $q->where('credit', '>', 0)
                  ->where('head', '>', 0)
                  ->where('types', 'Challan Payment');
            },
            'voucher.heads',
            'challan.heads.feeHead',
            'challan.student',
            'challan.enrollstudent',
            'challan.class'
        ])->where('created_by', \Auth::user()->creatorId());

    } else {
        $branches = User::where('id', \Auth::user()->ownedId())
            ->pluck('name', 'id');

        $branches->prepend('All Branches', '');

        $query = StudentReceipt::with([
            'challan',
            'bank',
            'voucher' => function($q) {
                // Only load credit entries with fee heads for student payments
                $q->where('credit', '>', 0)
                  ->where('head', '>', 0)
                  ->where('types', 'Challan Payment');
            },
            'voucher.heads',
            'challan.heads.feeHead',
            'challan.student',
            'challan.enrollstudent',
            'challan.class'
        ])->where('owned_by', \Auth::user()->ownedId());
    }

    /** =========================
     *  REQUEST FILTERS - SAME AS MAIN FUNCTION
     *  ========================= */
    $dateFrom = $request->from_date;
    $dateTo   = $request->to_date;

    // Date filter
    $query->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
        $q->whereBetween('recipt_date', [$dateFrom, $dateTo]);
    });

    // Bank filter
    $query->when(
        $request->default_bank && $request->default_bank !== 'allbank',
        fn ($q) => $q->where('bank_id', $request->default_bank)
    );

    // Branch filter
    $query->when(
        $request->branches,
        fn ($q) => $q->where('owned_by', $request->branches)
    );

    // Fee head filter
    $query->when($request->head, function ($q) use ($request) {
        $q->whereHas('challan.heads', function ($hq) use ($request) {
            $hq->where('head_id', $request->head);
        });
    });

    // Voucher filter
    $query->when($request->voucher && $request->voucher !== 'all', function ($q) use ($request) {
        $q->where('voucher_id', $request->voucher);
    });

    // Class filter
    $query->when($request->class, function ($q) use ($request) {
        $q->whereHas('challan', function ($cq) use ($request) {
            $cq->where('class_id', $request->class);
        });
    });

    // Student filter - FIXED: Check both enrollId and roll_no
    $query->when($request->student, function ($q) use ($request) {
        $q->where(function ($subQuery) use ($request) {
            $subQuery->whereHas('challan.enrollstudent', function ($sq) use ($request) {
                $sq->where('enrollId', $request->student);
            })
            ->orWhereHas('challan.student', function ($sq) use ($request) {
                $sq->where('roll_no', $request->student);
            });
        });
    });

    /** =========================
     *  FINAL RECEIPTS
     *  ========================= */
    $recipts = $query->get();

    /** =========================
     *  STUDENT DROPDOWN
     *  ========================= */
    $students = $recipts
        ->pluck('challan.enrollstudent.enrollId')
        ->unique()
        ->filter()
        ->values();

    /** =========================
     *  FEE HEADS
     *  ========================= */
    $heads = FeeHead::where('created_by', \Auth::user()->creatorId())
        ->pluck('fee_head', 'id')
        ->prepend('Select Fee Head', '');

    /** =========================
     *  GROUPING BY BRANCH
     *  ========================= */
    $branchNames = User::whereIn('id', $recipts->pluck('owned_by')->unique())
        ->pluck('name', 'id')
        ->toArray();

    $groupedReceipts = $recipts->groupBy('owned_by');

    /** =========================
     *  EXPORTS
     *  ========================= */
    if ($request->export === 'excel') {
        return Excel::download(
            new StudentFeeReceiptDetailExport(
                $recipts,
                $branches,
                $request->branches,
                'Fee Receipt Detail Report',
                $request->all(),
                $request
            ),
            'student_fee_receipt_detail_report.xlsx'
        );
    }

    if ($request->export === 'pdf') {
        return Excel::download(
            new StudentFeeReceiptDetailExport(
                $recipts,
                $branches,
                $request->branches,
                'Fee Receipt Detail Report',
                $request->all(),
                $request
            ),
            'student_fee_receipt_detail_report.pdf',
            \Maatwebsite\Excel\Excel::MPDF
        );
    }

    /** =========================
     *  PDF PREVIEW (DOMPDF)
     *  ========================= */
    $session = [];
    $class = [];
    $voucherOptions = ['all' => 'All Vouchers'];

    $html = view(
        'studentReports.student_fee_receipt_detail_pdf',
        compact(
            'accounts',
            'voucherOptions',
            'heads',
            'recipts',
            'session',
            'class',
            'students',
            'branches',
            'groupedReceipts',
            'branchNames'
        )
    )->render();

    $headerHtml = view('studentReports.pdf_header', compact('request'))->render();
    $footerHtml = view('students.concession.report.pdf.footer')->render();

    $html = "
        <html>
        <head>
            <style>
                @page { margin: 100px 0; }
                .header { position: fixed; top: -60px; width: 100%; }
                .footer { position: fixed; bottom: -60px; width: 100%; }
            </style>
        </head>
        <body>
            <div class='header'>{$headerHtml}</div>
            <div class='footer'>{$footerHtml}</div>
            {$html}
        </body>
        </html>
    ";

    $dompdf = new Dompdf(new Options([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]));

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return response()->json([
        'base64Pdf' => base64_encode($dompdf->output())
    ]);
}

    public function student_security_report(Request $request)
    {
        $classes = [];
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $head = FeeHead::where('fee_head', 'like', '%security%')->where('created_by', $userCreatorId)->first();
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $head = FeeHead::where('fee_head', 'like', '%security%')->where('owned_by', $userOwnedId)->first();
        }
        if (!$head) {
            return redirect()->back()->with('error', 'Security head not found.');
        }
        $query = ChallanHead::with('challan', 'challan.student', 'challan.student.class', 'challan.student.withdrawal', 'challan.enrollstudent')
            ->where('head_id', $head->id);
        if ($request->has('branches') && $request->branch != 'all') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('branch_id', $request->branch);
            });
        }
        if ($request->has('class') && $request->class != '') {
            $query->whereHas('challan.student', function ($q) use ($request) {
                $q->where('class_id', $request->class);
            });
        }
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challan_date', '>=', $request->date_from);
            });
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challan_date', '<=', $request->date_to);
            });
        }
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }

        // Ensure you include whereHas in this part
        $query->whereHas('challan', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('challan_date', [$dateFrom, $dateTo]);
        });
        if ($request->status == 'withdraw' && $request->status != 'all') {
            $status = $request->status;
            $query->whereHas('challan.student', function ($q) use ($status) {
                if ($status == 'withdraw') {
                    $q->whereHas('withdrawal');
                } else {
                    $q->whereDoesntHave('withdrawal');
                }
            });
        }
        if ($request->status == 'active') {
            $status = $request->status;
            $query->whereHas('challan.student', function ($q) use ($status) {
                $q->whereDoesntHave('withdrawal');
            });
        }
        $classes = Classes::pluck('name', 'id');
        $classes->prepend('Select Class', '');
        $records = $query->get();

        $journalSums = JournalItem::select([
            DB::raw("CAST(
                REGEXP_REPLACE(
                  description,
                  '.*Reciveable of Challan id : ([0-9]+).*',
                  '\\1'
                ) 
                AS UNSIGNED
              ) AS challan_id"),
            DB::raw("SUM(CASE WHEN debit = 0 THEN credit ELSE 0 END) AS security_deposit"),
            DB::raw("SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END)  AS security_paid"),
        ])
            ->where('head', $head->id)
            ->whereRaw("description REGEXP 'Reciveable of Challan id : [0-9]+'")
            ->groupBy('challan_id')
            ->get();
        $journals = $journalSums
            ->mapWithKeys(function ($item) {
                return [
                    $item->challan_id => [
                        'deposit' => $item->security_deposit,
                        'paid' => $item->security_paid,
                    ]
                ];
            });

        if ($request->has('export') && $request->export == 'excel') {
            $records = $query->get()->groupBy('challan.owned_by');
            $report_name = 'Student Security Deposit Report';
            return Excel::download(new StudentSecurityReportExport($branches, $records, $request->branch, $head, $journals, $request, $report_name, $request->all()), 'Student_Security_Report.xlsx');
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $records = $query->get()->groupBy('challan.owned_by');
            $report_name = 'Student Security Deposit Report';
            return Excel::download(new StudentSecurityReportExport($branches, $records, $request->branch, $head, $journals, $request, $report_name, $request->all()), 'Student_Security_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        // 3) pass both to the view
        return view('studentReports.StudentSecurityReport', [
            'branches' => $branches,
            'classes' => $classes,
            'records' => $records,
            'journals' => $journals,
        ]);
    }
    public function student_security_reportPdf(Request $request)
    {
        $classes = [];
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
            $head = FeeHead::where('fee_head', 'like', '%security%')->where('created_by', $userCreatorId)->first();
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $head = FeeHead::where('fee_head', 'like', '%security%')->where('owned_by', $userOwnedId)->first();
        }
        if (!$head) {
            return redirect()->back()->with('error', 'Security head not found.');
        }
        $query = ChallanHead::with('challan', 'challan.student', 'challan.student.class', 'challan.student.withdrawal', 'challan.enrollstudent')
            ->where('head_id', $head->id);
        if ($request->has('branches') && $request->branch != 'all') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('branch_id', $request->branch);
            });
        }

        if ($request->has('class') && $request->class != '') {
            $query->whereHas('challan.student', function ($q) use ($request) {
                $q->where('class_id', $request->class);
            });
        }
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challan_date', '>=', $request->date_from);
            });
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereHas('challan', function ($q) use ($request) {
                $q->where('challan_date', '<=', $request->date_to);
            });
        }
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (empty($dateFrom) && empty($dateTo)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
        }

        // Ensure you include whereHas in this part
        $query->whereHas('challan', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('challan_date', [$dateFrom, $dateTo]);
        });

        if ($request->status == 'withdraw' && $request->status != 'all') {
            $status = $request->status;
            $query->whereHas('challan.student', function ($q) use ($status) {
                if ($status == 'withdraw') {
                    $q->whereHas('withdrawal');
                } else {
                    $q->whereDoesntHave('withdrawal');
                }
            });
        }
        if ($request->status == 'active') {
            $status = $request->status;
            $query->whereHas('challan.student', function ($q) use ($status) {
                $q->whereDoesntHave('withdrawal');
            });
        }

        $records = $query->get();

        $journalSums = JournalItem::select([
            DB::raw("CAST(
                REGEXP_REPLACE(
                  description,
                  '.*Reciveable of Challan id : ([0-9]+).*',
                  '\\1'
                ) 
                AS UNSIGNED
              ) AS challan_id"),
            DB::raw("SUM(CASE WHEN debit = 0 THEN credit ELSE 0 END) AS security_deposit"),
            DB::raw("SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END)  AS security_paid"),
        ])
            ->where('head', $head->id)
            ->whereRaw("description REGEXP 'Reciveable of Challan id : [0-9]+'")
            ->groupBy('challan_id')
            ->get();
        $journals = $journalSums
            ->mapWithKeys(function ($item) {
                return [
                    $item->challan_id => [
                        'deposit' => $item->security_deposit,
                        'paid' => $item->security_paid,
                    ]
                ];
            });




        $report_name = 'Student Security Report';

        $classes = Classes::pluck('name', 'id');
        $classes->prepend('Select Class', '');
        $pdf = new Dompdf();
        $html = view('studentReports.StudentSecurityReport_pdf', [
            'branches' => $branches,
            'classes' => $classes,
            'records' => $records,
            'journals' => $journals,
        ])->render();
        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
        $footerHtml = view('students.concession.report.pdf.footer')->render();

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

        // return $dompdf->stream('concession.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);
    }
    // student_defaulter report
    public function student_defaulter(Request $request)
{
    $reportData = [];
    $class = [];
    $user = \Auth::user();
    $filtersApplied = false;
    
    if ($user->type == 'company') {
        // Branches for dropdown
        $branches = User::where('type', '=', 'branch')
        ->where('created_by', $user->creatorId())
        ->where('is_active', 1)
        ->get()
        ->pluck('name', 'id');
        $branches->prepend($user->name, $user->id);
        
        // Selected branches for frontend (autoselect in dropdown)
        $selected_branches = User::where('type', '=', 'branch')
            ->where('created_by', $user->creatorId())
            ->where('is_active', 1)
            ->get()
            ->pluck('name', 'id');
        $selected_branches->prepend($user->name, $user->id);
        
        $branches->prepend('All Branches', '');
        
        // Determine which branches to process in the REPORT
        if (!empty($request->branches) && $request->branches != '') {
            // Specific branch selected - process only that branch
            $branchesToProcess = User::where('id', '=', $request->branches)
                ->where('is_active', 1)
                ->get()
                ->pluck('name', 'id');
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filtersApplied = true;
        } else {
            // No specific branch selected - process ALL branches
            $branchesToProcess = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->where('is_active', 1)
                ->get()
                ->pluck('name', 'id');
            // Also include company's own branch
            $branchesToProcess->prepend($user->name, $user->id);
        }
        
    } else {
        // Branch user
        $branches = User::where('id', '=', $user->ownedId())
            ->where('is_active', 1)
            ->get()
            ->pluck('name', 'id');
        $selected_branches = User::where('id', '=', $user->ownedId())
            ->where('is_active', 1)
            ->get()
            ->pluck('name', 'id');
        $branches->prepend('All Branches', '');
        
        // For branch user, only their own branch
        $branchesToProcess = $selected_branches;
    }
    
    // Calculate date range and months array ONCE (outside the loop)
    if (empty($request->date_from) || empty($request->date_to)) {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
        $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
        
        $request->merge(['date_from' => $dateFrom]);
        $request->merge(['date_to' => $dateTo]);
    } else {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
    }
    
    // Generate months array ONCE
    $start = strtotime($dateFrom);
    $end = strtotime($dateTo);
    $monthsArray = [];
    
    while ($start <= $end) {
        $monthsArray[] = date('m-Y', $start);
        $start = strtotime("+1 month", $start);
    }
    
    // Loop through branches to process for the REPORT
    foreach ($branchesToProcess as $branchId => $branchName) {
        // dd($branchesToProcess);
        // Create a fresh query for EACH branch
        if ($user->type == 'company') {
            // For company, use created_by to get all data under company
            $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
                ->where('created_by', $user->id)
                ->where('status', '!=', 'Paid')
                ->whereHas('student', function ($q) {
                    $q->where('active_status', 1);
                });
        } else {
            // For branch users
            $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
                ->where('owned_by', $user->id)
                ->where('status', '!=', 'Paid')
                ->whereHas('student', function ($q) {
                    $q->where('active_status', 1);
                });
        }
        
        // Filter by specific branch - this ensures each branch gets only its students
        $query->where('owned_by', $branchId);
        
        // Apply date filters
        $query->whereBetween('due_date', [$dateFrom, $dateTo]);
        
        // Apply class filter if provided
        if (!empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
        }
        
        // Get challans for THIS branch only
        $challans = $query->orderBy('student_id')->orderBy('fee_month')->get()->groupBy('student_id');
        
        // Only add to report if this branch has challans
        if ($challans->count() > 0) {
            $reportData[] = [
                'branch' => $branchName,
                'challans' => $challans,
            ];
        }
    }
    
    $report_name = 'Student Defaulter Report';
    
    if ($request->has('export') && $request->export == 'excel') {
        return Excel::download(new Student_defaulterReport($branches, $monthsArray, $reportData, $report_name, $request, $request->all()), 'student_defaulter_report.xlsx');
    }
    
    if ($request->has('print') && $request->print == 'pdf') {
        // Increase memory and execution time for large PDFs
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        
        $pdf = new Dompdf();
        $html = view('studentReports.student_defaulter_pdf', compact('branches', 'class', 'monthsArray', 'reportData', 'report_name'))->render();
        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
        $footerHtml = view('students.concession.report.pdf.footer')->render();
        $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            body { font-size: 10px; }
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
        return $dompdf->stream('student_defaulter.pdf', ['Attachment' => false]);
    }
    
    return view('studentReports.student_defaulter', compact('branches', 'class', 'monthsArray', 'reportData', 'report_name'));
}

    // public function student_defaulter(Request $request)
    // {
    //     $reportData = [];
    //     $class = [];
    //     $user = \Auth::user();
    //     $filtersApplied = false;
    //     if ($user->type == 'company') {
    //         $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
    //         $branches->prepend($user->name, $user->id);
    //         $selected_branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
    //         $selected_branches->prepend($user->name, $user->id);
    //         $branches->prepend('All Branches', '');
    //         $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 'Paid');
    //     } else {
    //         $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
    //         $selected_branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
    //         $branches->prepend('All Branches', '');
    //         $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')->where('owned_by', \Auth::user()->ownedId())->where('status', '!=', 'Paid');
    //     }
    //     if (!empty($request->branches)) {
    //         $selected_branches = User::where('id', '=', $request->branches)->get()->pluck('name', 'id');
    //         $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
    //         $filtersApplied = true;
    //     }
    //     // new method
    //     $selectedBranchId = $request->branches ?? null;
    //     foreach ($selected_branches as $branchId => $branch) {
    //         $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')->where('owned_by', $branchId)->where('status', '!=', 'Paid');
    //         if (!empty($request->date_from)) {
    //             $query->whereDate('due_date', '>=', $request->date_from);
    //             $filtersApplied = true;
    //         }

    //         if (!empty($request->date_to)) {
    //             $query->whereDate('due_date', '<=', $request->date_to);
    //             $filtersApplied = true;
    //         }
    //         if (empty($request->date_from) || empty($request->date_to)) {
    //             $currentYear = date('Y');
    //             $currentMonth = date('m');
    //             $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
    //             $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

    //             $request->merge(['date_from' => $dateFrom]);
    //             $request->merge(['date_to' => $dateTo]);
    //             $query->whereBetween('due_date', [$dateFrom, $dateTo]);
    //             // Initialize variables for the loop
    //             $start = strtotime($dateFrom);
    //             $end = strtotime($dateTo);
    //             $monthsArray = [];

    //             // Loop through each month from start to end
    //             while ($start <= $end) {
    //                 $monthsArray[] = date('m-Y', $start);  // Format as MM-YYYY
    //                 $start = strtotime("+1 month", $start);  // Move to the next month
    //             }
    //         } else {
    //             // Initialize variables for the loop
    //             $start = strtotime($request->date_from);
    //             $end = strtotime($request->date_to);
    //             $monthsArray = [];

    //             // Loop through each month from start to end
    //             while ($start <= $end) {
    //                 $monthsArray[] = date('m-Y', $start);  // Format as MM-YYYY
    //                 $start = strtotime("+1 month", $start);  // Move to the next month
    //             }
    //             // dd($monthsArray);
    //         }

    //         if (!empty($request->class)) {
    //             if ($request->class != 'all') {
    //                 $query->where('class_id', '=', $request->class);
    //             }

    //         }
    //         $challans = $query->orderBy('student_id')->orderBy('fee_month')->get()->groupBy('student_id');
    //         $report_name = 'Student Defaulter Report';
    //         $reportData[] = [
    //             'branch' => $branch,
    //             'challans' => $challans,
    //         ];

    //     }

    //     if ($request->has('export') && $request->export == 'excel') {
    //         return Excel::download(new Student_defaulterReport($branches, $monthsArray, $reportData, ), 'student_defaulter.xlsx');
    //     }
    //     if ($request->has('print') && $request->print == 'pdf') {
    //         // set execution time to 0
    //         ini_set('max_execution_time', 0);
    //         $report_name = 'Student Defaulter Report';
    //         $branch = $branches[$selectedBranchId];
    //         $pdf = new Dompdf();
    //         $html = view('studentReports.student_defaulter_pdf', compact('branches', 'class', 'monthsArray', 'reportData','report_name','request'))->render();
    //         $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name', 'branch'));
    //         $footerHtml = view('students.concession.report.pdf.footer')->render();

    //         $html = '<html><head>
    //         <style>
    //             @page {
    //                 margin-top: 100px;
    //                 margin-bottom: 100px;
    //             }
    //             .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
    //         </style>
    //         </head><body>
    //         ' . $headerHtml . '
    //         <div class="footer">' . $footerHtml . '</div>
    //         ' . $html . '
    //         </body></html>';
    //         $options = new Options();
    //         $options->set('isHtml5ParserEnabled', true);
    //         $options->set('isRemoteEnabled', true);
    //         $dompdf = new Dompdf($options);
    //         $dompdf->loadHtml($html);
    //         $dompdf->setPaper('A3', 'potrait');
    //         $dompdf->render();

    //         return $dompdf->stream('StudentDefaulter.pdf');
    //     }

    //     return view('studentReports.student_defaulter', compact('branches', 'class', 'monthsArray', 'reportData','report_name'));
    // }

    public function student_defaulter_report(Request $request)
    {
        $session = [];
        $class = [];
        $filtersApplied = false;

        $query = Challans::select(
            'student_id',
            'class_id',
            'fee_month',
            'id',
            \DB::raw('(total_amount - (paid_amount + concession_amount)) as total')
        )
            ->with('student', 'enrollstudent', 'class', 'enrollstudent.section')
            ->where('status', '!=', 'Paid');

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id')->prepend(\Auth::user()->name, \Auth::user()->id)->prepend('Select Branch', '');
            $query->where('created_by', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id')->prepend('Select Branch', '');
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        if (!empty($request->date_from)) {
            $query->whereDate('due_date', '>=', $request->date_from);
            $filtersApplied = true;
        }

        if (!empty($request->date_to)) {
            $query->whereDate('due_date', '<=', $request->date_to);
            $filtersApplied = true;
        }
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);
            $query->whereBetween('due_date', [$dateFrom, $dateTo]);
        }

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filtersApplied = true;
        }

        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $student = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
            $filtersApplied = true;
        }
        $challans = $query->get();
        $groupedChallans = $challans->groupBy(['student_id', 'fee_month']);
        $groupedChallans = [];
        $report_name = 'Student Defaulter';
        $pdf = new Dompdf();
        $html = view('studentReports.student_defaulter_pdf', compact('branches', 'class', 'challans', 'groupedChallans'))->render();
        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
        $footerHtml = view('students.concession.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
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

        // return $dompdf->stream('concession.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);

    }
    // public function student_defaulter_sm(Request $request)
    // {
    //     $session = [];
    //     $class = [];
    //     $filtersApplied = false;
    //     $start_date = $request->from_date ?? date('Y-m-01');
    //     $end_date = $request->to_date ?? date('Y-m-d');

    //     $query = Challans::select(
    //         'student_id',
    //         'class_id',
    //         'fee_month',
    //         'id',
    //         \DB::raw('(total_amount - (paid_amount + concession_amount)) as total')
    //     )
    //         ->with('student', 'enrollstudent', 'class', 'enrollstudent.section')
    //         ->where('status', '!=', 'Paid');

    //     if (\Auth::user()->type == 'company') {
    //         $branches = User::where('type', '=', 'branch')
    //             ->get()
    //             ->pluck('name', 'id')
    //             ->prepend(\Auth::user()->name, \Auth::user()->id)
    //             ->prepend('Select Branch', '');

    //         $query->where('created_by', \Auth::user()->creatorId());
    //     } else {
    //         $branches = User::where('id', '=', \Auth::user()->ownedId())
    //             ->get()
    //             ->pluck('name', 'id')
    //             ->prepend('Select Branch', '');

    //         $query->where('owned_by', \Auth::user()->ownedId());
    //     }

    //     if (!empty($start_date)) {
    //         $query->whereDate('due_date', '>=', $start_date);
    //         $filtersApplied = true;
    //     }

    //     if (!empty($end_date)) {
    //         $query->whereDate('due_date', '<=', $end_date);
    //         $filtersApplied = true;
    //     }

    //     if (!empty($request->branches)) {
    //         $query->where('owned_by', '=', $request->branches);
    //         $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
    //         $filtersApplied = true;
    //     }

    //     if (!empty($request->class)) {
    //         $query->where('class_id', '=', $request->class);
    //         $student = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
    //         $filtersApplied = true;
    //     }
    //     $start = new \DateTime($start_date);
    //     $end = new \DateTime($end_date);
    //     $end->modify('first day of next month');

    //     $interval = \DateInterval::createFromDateString('1 month');
    //     $period = new \DatePeriod($start, $interval, $end);

    //     $monthss = [];
    //     foreach ($period as $dt) {
    //         $monthss[] = $dt->format("F");
    //     }
    //     $months = $monthss;

    //     if ($filtersApplied) {
    //         $challans = $query->get();
    //         $groupedChallans = $challans->groupBy(['student_id', 'fee_month']);
    //         return view('studentReports.student_defaulter_sm', compact('branches', 'class', 'groupedChallans', 'months'));
    //     } else {
    //         $challans = collect();
    //     }

    //     $groupedChallans = [];
    //     return view('studentReports.student_defaulter_sm', compact('branches', 'class', 'challans', 'groupedChallans', 'months'));
    // }
    public function withdarawl_notice(Request $request)
    {
        // dd($request->all());
        $class = [];
        $students = [];
        $filtersApplied = false;
        $query = Challans::select(
            'student_id',
            'class_id',
            'fee_month',
            'id',
            \DB::raw('(total_amount - (paid_amount + concession_amount)) as total')
        )
            ->with('student', 'enrollstudent', 'class', 'enrollstudent.section')
            ->where('status', '!=', 'Paid')
            ->where('student_id', '=', $request->students);
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->get()
                ->pluck('name', 'id')
                ->prepend(\Auth::user()->name, \Auth::user()->id)
                ->prepend('Select Branch', '');

            $query->where('created_by', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');

            $query->where('owned_by', \Auth::user()->ownedId());
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filtersApplied = true;
        }

        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $students = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
            $filtersApplied = true;
        }
        if (!empty($request->students)) {
            $query->where('student_id', '=', $request->students);
            $students = StudentRegistration::where('id', '=', $request->students)->get()->pluck('stdname', 'id');
            $filtersApplied = true;
        }
        if ($filtersApplied) {
            $challans = $query->get();
            $groupedChallans = $challans->groupBy(['student_id', 'fee_month']);
        } else {
            $challans = collect();
            $groupedChallans = [];
        }
        return view('studentReports.withdrawl_notice', compact('class', 'students', 'branches', 'groupedChallans'));
    }
    // public function student_single_account(Request $request)
    // {
    //     $class = [];
    //     $student = [];
    //     $receipts = [];
    //     $filtersApplied = false;
    //     $currentYear = date('Y');
    //     $currentMonth = date('m');
    //     $new_from_date = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
    //     $new_to_date = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
    //     $from_date = $request->input('from_date', $new_from_date);
    //     $to_date = $request->input('to_date', $new_to_date);
    //     $selected_branch = $request->input('branches', '');
    //     $selected_class = $request->input('class', '');
    //     $selected_student = $request->input('student', '');

    //     if (\Auth::user()->type == 'company') {
    //         $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id')
    //             ->prepend(\Auth::user()->name, \Auth::user()->id)
    //             ->prepend('Select Branch', '');
    //         // $query = StudentReceipt::with('challan', 'bank', 'voucher', 'voucher.heads', 'challan.heads', 'challan.student', 'challan.enrollstudent')
    //         //     ->where('created_by', \Auth::user()->creatorId());
    //         $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
    //             ->where('student_status', 'Enrolled')
    //             ->where('created_by', '=', \Auth::user()->creatorId())
    //             ->whereNotNull('roll_no')
    //             ->get()
    //             ->pluck('stdname', 'roll_no')->prepend('Select Student', '');
    //     } else {
    //         $branches = User::where('id', '=', \Auth::user()->ownedId())
    //             ->get()
    //             ->pluck('name', 'id')
    //             ->prepend('Select Branch', '');
    //         // $query = StudentReceipt::with('challan', 'bank', 'voucher', 'voucher.heads.feeHead', 'challan.student', 'challan.enrollstudent', 'challan.class')
    //         //     ->where('owned_by', \Auth::user()->ownedId());

    //         $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
    //             ->where('owned_by', '=', \Auth::user()->ownedId())
    //             ->whereNotNull('roll_no')
    //             ->get()
    //             ->pluck('stdname', 'roll_no')->prepend('Select Student', '');
    //     }

    //     if (!empty($selected_branch)) {
    //         $class = Classes::where('owned_by', '=', $selected_branch)->get()->pluck('name', 'id');
    //         $filtersApplied = true;
    //     }
    //     if (!empty($selected_class)) {
    //         $student = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->whereNotNull('roll_no')
    //             ->where('class_id', '=', $selected_class)->get()->pluck('stdname', 'roll_no');
    //         $filtersApplied = true;
    //     }

    //     $query = collect();
    //     if (!empty($request->student)) {
    //         $std = StudentRegistration::with('enrollment', 'enrollment.section')->where('roll_no', $request->student)->first();
    //         // dd($std,$request->student);
    //         $query = JournalItem::where('user_id', $std->id)->where('user_type', 'student');
    //         $filtersApplied = true;
    //     }
    //     // $std = StudentRegistration::with('enrollment', 'enrollment.section')->where('roll_no', '=', $request->student)->first();
    //     if (!empty($request->input('from_date'))) {
    //         $query->whereDate('updated_at', '>=', $from_date);
    //         $filtersApplied = true;
    //     }
        
    //     if (!empty($request->input('to_date'))) {
    //         $query->whereDate('updated_at', '<=', $to_date);
    //         $filtersApplied = true;
    //     }

    //     // if (!empty($selected_student)) {
    //     //     $query->where('student_id', $selected_student);
    //     //     $filtersApplied = true;
    //     // }

    //     // Validate student selection for export
        
    //     // Handle view display
    //     if ($filtersApplied) {
    //         $receipts = $query->where(function ($q) {
    //             $q->where('types', 'like', '%Challan Payment%')
    //                 ->orWhere('types', 'like', '%Studypack Challan Pay%');
    //         })->where('user_type', 'student')
    //             ->where('credit', '!=', 0)
    //             ->with('user', 'accounts', 'heads', 'receiptheads', 'journalEntery', 'journalEntery.challan',
    //             //  'journalEntery.studypackchallan', 'journalEntery.stdRecp',
    //             'journalEntery.recipt', 'journalEntery.recipt.challan')->get();
    //     } else {
    //         $receipts = collect();
    //     }

    //     if ($request->has('export') && ($request->export == 'excel' || $request->export == 'pdf')) {
    //         if (empty($selected_student) || $selected_student == 'all') {
    //             return redirect()->back()->with('error', 'Please select a specific student before exporting.');
    //         }
    //     }

    //     if ($request->has('export') && $request->export == 'excel') {
    //         $heads = FeeHead::where('owned_by', \Auth::user()->ownedId())->get()->pluck('fee_head', 'id');
    //         $report_name = 'Student Fee Receipt Detail Report';
    //         return Excel::download(new StudentAccountStatementExport($branches,$students,$class,$receipts,$from_date,$to_date,$selected_branch,$selected_class,$selected_student), 'Student_Account_Statement_Report.xlsx');
    //     }

    //     if ($request->has('export') && $request->export == 'pdf') {
    //         $heads = FeeHead::where('owned_by', \Auth::user()->ownedId())->get()->pluck('fee_head', 'id');
    //         $report_name = 'Student Fee Receipt Detail Report';
    //         return Excel::download(new StudentAccountStatementExport($branches,$students,$class,$receipts,$from_date,$to_date,$selected_branch,$selected_class,$selected_student), 'Student_Account_Statement_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
    //     }
    //     // dd($receipts);
    //     // Prepare view data
    //     $viewData = compact(
    //         'branches',
    //         'students',
    //         'class',
    //         'receipts',
    //         'from_date',
    //         'to_date',
    //         'selected_branch',
    //         'selected_class',
    //         'selected_student'
    //     );

    //     // Add std only if it exists
    //     if (isset($std)) {
    //         $viewData['std'] = $std;
    //     }

    //     return view('studentReports.student_single_account', $viewData);
    // }
public function student_single_account(Request $request)
{
    $class = [];
    $student = [];
    $receipts = [];
    $filtersApplied = false;
    $currentYear = date('Y');
    $currentMonth = date('m');
    $new_from_date = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
    $new_to_date = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
    $from_date = $request->input('from_date', $new_from_date);
    $to_date = $request->input('to_date', $new_to_date);
    $selected_branch = $request->input('branches', '');
    $selected_class = $request->input('class', '');
    $selected_student = $request->input('student', '');

    if (\Auth::user()->type == 'company') {
        $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id')
            ->prepend(\Auth::user()->name, \Auth::user()->id)
            ->prepend('Select Branch', '');
        $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
            ->where('student_status', 'Enrolled')
            ->where('created_by', '=', \Auth::user()->creatorId())
            ->whereNotNull('roll_no')
            ->get()
            ->pluck('stdname', 'roll_no')->prepend('Select Student', '');
    } else {
        $branches = User::where('id', '=', \Auth::user()->ownedId())
            ->get()
            ->pluck('name', 'id')
            ->prepend('Select Branch', '');
        $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
            ->where('owned_by', '=', \Auth::user()->ownedId())
            ->whereNotNull('roll_no')
            ->get()
            ->pluck('stdname', 'roll_no')->prepend('Select Student', '');
    }

    if (!empty($selected_branch)) {
        $class = Classes::where('owned_by', '=', $selected_branch)->get()->pluck('name', 'id');
        $filtersApplied = true;
    }
    if (!empty($selected_class)) {
        $student = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->whereNotNull('roll_no')
            ->where('class_id', '=', $selected_class)->get()->pluck('stdname', 'roll_no');
        $filtersApplied = true;
    }

    $std = null;
    $accountStatement = collect();
    
    if (!empty($request->student)) {
        $std = StudentRegistration::with('enrollment', 'enrollment.section', 'class')->where('roll_no', $request->student)->first();
        
        if ($std) {
            // Get opening balance (transactions before from_date)
            $openingBalance = $this->calculateOpeningBalance($std->id, $from_date);
            
            // Get all transactions (challans and receipts) within date range
            $accountStatement = $this->getAccountStatement($std->id, $from_date, $to_date, $openingBalance);
            
            $filtersApplied = true;
        }
    }

    if ($request->has('export') && ($request->export == 'excel' || $request->export == 'pdf')) {
        if (empty($selected_student) || $selected_student == 'all') {
            return redirect()->back()->with('error', 'Please select a specific student before exporting.');
        }
    }

    if ($request->has('export') && $request->export == 'excel') {
        return Excel::download(new StudentAccountStatementExport($branches, $students, $class, $accountStatement, $from_date, $to_date, $selected_branch, $selected_class, $selected_student), 'Student_Account_Statement_Report.xlsx');
    }

    if ($request->has('export') && $request->export == 'pdf') {
        return Excel::download(new StudentAccountStatementExport($branches, $students, $class, $accountStatement, $from_date, $to_date, $selected_branch, $selected_class, $selected_student), 'Student_Account_Statement_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
    }

    // Prepare view data
    $viewData = compact(
        'branches',
        'students',
        'class',
        'accountStatement',
        'from_date',
        'to_date',
        'selected_branch',
        'selected_class',
        'selected_student'
    );

    // Add std only if it exists
    if (isset($std)) {
        $viewData['std'] = $std;
    }

    return view('studentReports.student_single_account', $viewData);
}

/**
 * Calculate opening balance before the from_date
 * Opening Balance = Total Receivables (Credit Side) - Discounts
 */
private function calculateOpeningBalance($studentId, $fromDate)
{
    // Get all regular receivables (challans) before from_date
    $totalReceivables = JournalItem::where('user_id', $studentId)
        ->where('user_type', 'student')
        ->where('types', 'challan')
        ->where('credit', '!=', 0)
        ->where('is_discount', '!=', 1) // Regular heads only
        ->whereDate('updated_at', '<', $fromDate)
        ->sum('credit');

    // Get all discount amounts before from_date
    $totalDiscounts = JournalItem::where('user_id', $studentId)
        ->where('user_type', 'student')
        ->where('types', 'challan')
        ->where('credit', '!=', 0)
        ->where('is_discount', '=', 1) // Discount heads only
        ->whereDate('updated_at', '<', $fromDate)
        ->sum('credit');

    // Opening Balance = Receivables - Discounts
    return $totalReceivables - $totalDiscounts;
}

/**
 * Get complete account statement with receivables (credit) and payments (debit)
 */
private function getAccountStatement($studentId, $fromDate, $toDate, $openingBalance)
{
    // Get all challan entries (including discounts for subtraction)
    $challanItems = JournalItem::where('user_id', $studentId)
        ->where('user_type', 'student')
        ->where('types', 'challan')
        ->where('credit', '!=', 0)
        ->whereDate('created_at', '>=', $fromDate)
        ->whereDate('created_at', '<=', $toDate)
        ->with([
            'user',
            'accounts',
            'heads',
            'journalEntery',
            'journalEntery.challan',
            'journalEntery.challan.heads',
            'journalEntery.challan.class'
        ])
        ->orderBy('created_at', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    // Group by challan number and fee_month to separate different months

    $challans = $challanItems->groupBy(function($item) {
        $challan = $item->journalEntery->challan ?? null;
        $challanNo = $challan->challanNo ?? 'unknown';
        $feeMonth = $challan->fee_month ?? 'unknown';
        return $challanNo . '_' . $feeMonth; // Group by challan + month
    });
    // Get all payment entries (to reduce receivables)
    $payments = JournalItem::where('user_id', $studentId)
        ->where('user_type', 'student')
        ->where('credit', '!=', 0)
        ->where('types', '!=', 'challan') // Exclude challan entries
        ->whereDate('created_at', '>=', $fromDate)
        ->whereDate('created_at', '<=', $toDate)
        ->with([
            'user',
            'accounts',
            'heads',
            'journalEntery',
            'journalEntery.recipt',
            'journalEntery.recipt.challan',
            'journalEntery.recipt.bank', // Add bank account relation
            'journalEntery.challan',
            'journalEntery.challan.class'
        ])
        ->orderBy('created_at', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    $statement = collect();
    $runningBalance = $openingBalance;

    // Add opening balance row
    if ($openingBalance != 0) {
        $statement->push([
            'type' => 'opening',
            'date' => $fromDate,
            'description' => 'Opening Balance',
            'challan_no' => '-',
            'billing_month' => '-',
            'challan_type' => '-',
            'head_name' => '-',
            'receipt_mode' => '-',
            'receipt_ref' => '-',
            'credit' => $openingBalance, // Show on credit (receivable) side
            'debit' => 0,
            'balance' => $runningBalance,
            'raw_data' => null
        ]);
    }

    // Process challans (grouped by challan number and fee_month)
    $challanTransactions = collect();
    foreach ($challans as $groupKey => $challanGroup) {
        $firstItem = $challanGroup->first();
        
        // Calculate total: sum regular credits - sum discount credits
        $regularCredit = $challanGroup->where('is_discount', '!=', 1)->sum('credit');
        $discountCredit = $challanGroup->where('is_discount', '=', 1)->sum('credit');
        $totalCredit = $regularCredit - $discountCredit;
        
        // Get all regular head names (exclude discounts from display)
        $allHeads = $challanGroup->where('is_discount', '!=', 1)
            ->pluck('heads.fee_head')
            ->filter()
            ->implode(', ');
        
        $challan = $firstItem->journalEntery->challan ?? null;

        $billingMonth = Carbon::parse($challan->fee_month)->format('M-Y');

        if ($challan->other_months != null && $challan->other_months != '') {
            if (!empty($challan->other_months) && $challan->other_months != null) {
                $billingMonth = collect(explode(',', $challan->other_months))
                    ->map(fn ($date) => Carbon::parse(trim($date))->format('M-Y'))
                    ->implode(', ');
            } else {
                $billingMonth = '-';
            }
        }

        $challanTransactions->push([
            'date' => $firstItem->created_at,
            'journal_id' => $firstItem->journal_id,
            'fee_month' => $challan->fee_month ?? '-',
            'type' => 'challan',
            'data' => [
                'type' => 'challan',
                'date' => $firstItem->created_at->format('Y-m-d'),
                'description' => $firstItem->description ?? 'Income Account: Roll no ' . $firstItem->user_id . ' Challan no ' . ($challan->challanNo ?? '-'),
                'challan_no' => $challan->challanNo ?? '-',
                'billing_month' => $billingMonth,
                'challan_type' => $challan->challan_type ?? '-',
                'head_name' => $allHeads ?: '-',
                'receipt_mode' => '-',
                'receipt_ref' => '-',
                'class' => $challan->class->name ?? '-',
                'credit' => $totalCredit, // Already has discount subtracted
                'debit' => 0,
                'balance' => 0, // Will be calculated
                'raw_data' => $firstItem
            ]
        ]);
    }

    // Process payments
    $paymentTransactions = collect();
    foreach ($payments as $transaction) {
        $debit = (float) $transaction->credit; // The credit in journal is shown as debit in report
        $receipt = $transaction->journalEntery->recipt ?? null;
        
        $challan = null;
        if ($receipt) {
            $challan = $receipt->challan ?? null;
        } else {
            $challan = $transaction->journalEntery->challan ?? null;
        }
        
        $headname = \App\Models\FeeHead::where('id', $transaction->head)->first();
        $bankName = '';
        $receive_type = '-';
        $referance = '-';
        $recpDate = null;
        
        // Get bank name and receipt info
        if ($receipt) {
            // Get bank name
            $bankName = $receipt->bank->bank_name . '-' . $receipt->bank->account_number ?? '';
            if (empty($bankName) && isset($receipt->bank)) {
                $bankName = $receipt->bank->bank_name ?? $receipt->bank->name ?? '';
            }
            
            $receive_type = $receipt->receive_type ?? '-';
            $referance = $receipt->referance ?? '-';
            
            // Parse receipt date and preserve the time from transaction for proper chronological sorting
            if ($receipt->recipt_date) {
                $recpDate = \Carbon\Carbon::parse($receipt->recipt_date);
                // Set the time from the actual transaction to maintain chronological order
                $recpDate->setTimeFrom($transaction->updated_at);
            }
        }
        
        // Get from accounts relation in journal item if bank name is still empty
        if (empty($bankName) && $transaction->bank_id) {
            $bankAccount = BankAccount::find($transaction->bank_id);
            $bankName = $bankAccount->bank_name . '-' . $bankAccount->account_number ?? '';
        }
        
        $chlnBillingMonth = $challan ? Carbon::parse($challan->fee_month)->format('M-Y') : '-';
        $headNameDisplay = $headname->fee_head ?? '-';
        
        // Use the date with preserved time for sorting
        $sortDate = $recpDate ?? $transaction->created_at;
        
        $paymentTransactions->push([
            'date' => $sortDate, // Use date with time for proper chronological sorting
            'journal_id' => $transaction->journal_id,
            'fee_month' => $challan->fee_month ?? 'zzz', // Add fee_month for sorting, use 'zzz' as fallback
            'type' => 'payment',
            'data' => [
                'type' => 'payment',
                'date' => ($recpDate ?? $transaction->created_at)->format('Y-m-d'), // Display only date
                'description' => $transaction->description ?? 'Receive of Challan no: ' . ($challan->challanNo ?? '-'),
                'challan_no' => $challan->challanNo ?? '-',
                'billing_month' => $chlnBillingMonth,
                'challan_type' => $challan->challan_type ?? '-',
                'head_name' => $headNameDisplay,
                'receipt_mode' => $receive_type,
                'receipt_ref' => $referance,
                'class' => $challan->class->name ?? '-',
                'credit' => 0,
                'debit' => $debit,
                'balance' => 0, // Will be calculated
                'late_amount' => $receipt->late_amount ?? 0,
                'arrears' => $receipt->arrears ?? 0,
                'bank_name' => $bankName,
                'raw_data' => $transaction
            ]
        ]);
    }

    // Merge and sort all transactions by date (with time), fee_month, and journal_id
    $allTransactions = $challanTransactions->merge($paymentTransactions)
        ->sortBy([
            ['date', 'asc'],
            ['fee_month', 'asc'],
            ['journal_id', 'asc']
        ]);

    // Calculate running balance
    foreach ($allTransactions as $transaction) {
        $row = $transaction['data'];
        
        if ($row['type'] == 'challan') {
            // Challan increases receivable (add to balance)
            $runningBalance += $row['credit'];
        } else {
            // Payment decreases receivable (subtract from balance)
            $runningBalance -= $row['debit'];
        }
        
        $row['balance'] = $runningBalance;
        $statement->push($row);
    }

    // Add closing balance row
    $statement->push([
        'type' => 'closing',
        'date' => $toDate,
        'description' => 'Closing Balance',
        'challan_no' => '-',
        'billing_month' => '-',
        'challan_type' => '-',
        'head_name' => '-',
        'receipt_mode' => '-',
        'receipt_ref' => '-',
        'debit' => 0,
        'credit' => 0,
        'balance' => $runningBalance,
        'raw_data' => null
    ]);

    return $statement;
}
    public function student_single_account_details(Request $request)
    {
        $class = [];
        $student = [];
        $filtersApplied = false;
        $from_date = $request->input('from_date', '');
        $to_date = $request->input('to_date', '');
        $selected_branch = $request->input('branches', '');
        $selected_class = $request->input('class', '');
        $selected_student = $request->input('student', '');

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->get()
                ->pluck('name', 'id')
                ->prepend(\Auth::user()->name, \Auth::user()->id)
                ->prepend('Select Branch', '');

            $query = Challans::with([
                'receipts',
                'vouchers' => function ($query) {
                    $query->whereIn('voucher_type', ['BRV', 'CRV']);
                },
                'vouchers.accounts'
            ])
                ->where('created_by', \Auth::user()->creatorId())
                ->whereHas('vouchers', function ($query) {
                    $query->whereIn('voucher_type', ['BRV', 'CRV']);
                })
                ->orderBy('challan_date', 'asc');

        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');

            // Fetch the challans with the correct voucher type filtering
            $query = Challans::with([
                'receipts',
                'vouchers' => function ($query) {
                    // Filter vouchers within the relationship
                    $query->whereIn('voucher_type', ['BRV', 'CRV']);
                },
                'vouchers.accounts'
            ])
                ->where('owned_by', \Auth::user()->ownedId())
                ->whereHas('vouchers', function ($query) {
                    $query->whereIn('voucher_type', ['BRV', 'CRV']);
                })
                ->orderBy('challan_date', 'asc');

        }

        // Apply additional filters if they are set
        if (!empty($selected_branch)) {
            $query->where('owned_by', '=', $selected_branch);
            $class = Classes::where('owned_by', '=', $selected_branch)->get()->pluck('name', 'id');
            $filtersApplied = true;
        }
        if (!empty($selected_class)) {
            $student = StudentRegistration::where('class_id', '=', $selected_class)->get()->pluck('stdname', 'id');
            $filtersApplied = true;
        }
        $std = StudentRegistration::with('enrollment', 'enrollment.section')->where('id', '=', $request->student)->first();
        if (!empty($from_date)) {
            $query->whereDate('created_at', '>=', $from_date);
            $filtersApplied = true;
        }
        if (!empty($to_date)) {
            $query->whereDate('created_at', '<=', $to_date);
            $filtersApplied = true;
        }
        if (!empty($selected_student)) {
            $query->where('student_id', '=', $selected_student);
            $filtersApplied = true;
        }

        // Retrieve and group the challans
        $challans = $query->get()->groupBy(function ($challan) {
            return $challan->challan_date; // Group after fetching the data
        });
        return view('studentReports.student_single_account_detail', compact('branches', 'std', 'student', 'class', 'challans', 'from_date', 'to_date', 'selected_branch', 'selected_class', 'selected_student'));
    }

    public function student_withdarawl_listing(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $class = [];
        $filterApplied = false;
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal', 'withdrawal.class')->where('owned_by', $userOwnedId);
        }
        $query->whereHas('withdrawal');
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filterApplied = true;
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $student = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
            $filterApplied = true;
        }
        if (!empty($request->date_from)) {
            $query->whereHas('withdrawal', function ($q) use ($request) {
                $q->whereDate('withdraw_date', '>=', $request->date_from);
            });
            $filterApplied = true;
        }

        if (!empty($request->date_to)) {
            $query->whereHas('withdrawal', function ($q) use ($request) {
                $q->whereDate('withdraw_date', '<=', $request->date_to);
            });
            $filtersApplied = true;
        }
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);

            $query->whereHas('withdrawal', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('withdraw_date', [$dateFrom, $dateTo]);
            });
        }
        // if (!empty($request->from_date) || !empty($request->to_date)) {
        //     $query->whereHas('withdrawal', function ($query) use ($request) {
        //         if (!empty($request->from_date)) {
        //             $query->whereDate('withdraw_date', '>=', $request->from_date);
        //         }
        //         if (!empty($request->to_date)) {
        //             $query->whereDate('withdraw_date', '<=', $request->to_date);
        //         }
        //     });
        // }
        if ($filterApplied) {
            $all_data = $query->get();
        } else {
            $all_data = collect();
        }
        return view('studentReports.student_withdrawl_listing', compact('all_data', 'branches', 'class', 'request'));
    }
    public function student_withdarawl_listingReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $class = [];
        $filterApplied = false;
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal')->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = StudentEnrollments::with('StudentRegistration', 'StudentRegistration.class', 'withdrawal', 'withdrawal.class')->where('owned_by', $userOwnedId);
        }
        $query->whereHas('withdrawal');
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filterApplied = true;
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $student = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
            $filterApplied = true;
        }
        if (!empty($request->date_from)) {
            $query->whereHas('withdrawal', function ($q) use ($request) {
                $q->whereDate('withdraw_date', '>=', $request->date_from);
            });
            $filterApplied = true;
        }
        if (!empty($request->date_to)) {
            $query->whereHas('withdrawal', function ($q) use ($request) {
                $q->whereDate('withdraw_date', '<=', $request->date_to);
            });
            $filterApplied = true;
        }
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['date_from' => $dateFrom]);
            $request->merge(['date_to' => $dateTo]);

            $query->whereHas('withdrawal', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('withdraw_date', [$dateFrom, $dateTo]);
            });
            $filterApplied = true;
        }
        if ($filterApplied) {
            $all_data = $query->get()->groupBy('owned_by'); // Group by branch
        } else {
            $all_data = collect();
        }
    
        $report_name = 'Student Withdrawal Listing';

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Student Withdrawal Listing Report';
            return Excel::download(
                new StudentWithdrawlListingExport($all_data, $branches, $class, $request, $report_name, $request->all()),
                'Student_Withdrawal_Listing_Report.xlsx'
            );
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Student Withdrawal Listing Report';
            return Excel::download(
                new StudentWithdrawlListingExport($all_data, $branches, $class, $request, $report_name, $request->all()),
                'Student_Withdrawal_Listing_Report.pdf', \Maatwebsite\Excel\Excel::MPDF
            );
        }

        $pdf = new Dompdf();
        $html = view('studentReports.student_withdrawl_listing_report', compact('all_data', 'branches', 'class', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('request', 'branches', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
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

    public function fee_receipt_summary(Request $request)
    {
        // Common date range logic
        $currentMonth = intval(date('n'));    // 1..12
        $currentYear = intval(date('Y'));

        if ($currentMonth > 6) {
            $startYear = $currentYear;
        } else {
            $startYear = $currentYear - 1;
        }

        $defaultStartDate = sprintf('%04d-06-01', $startYear);
        $defaultEndDate = date('Y-m-t');
        
        $dateFrom = $request->filled('start_date') 
            ? $request->input('start_date') 
            : $defaultStartDate;
        $dateTo = $request->filled('end_date') 
            ? $request->input('end_date') 
            : $defaultEndDate;

        ini_set('max_execution_time', 0);
        $user = \Auth::user();
        $ownedId = $user->ownedId();
        $creatorId = $user->creatorId();
        $isCompany = ($user->type === 'company');

        // Bank accounts query
        $bankQuery = BankAccount::selectRaw("id, CONCAT(bank_name, ' ', holder_name) AS name");
        if ($isCompany) {
            $bankQuery->where('created_by', $creatorId);
        } else {
            $bankQuery->where('owned_by', $ownedId);
        }
        $rawBankList = $bankQuery->pluck('name', 'id');
        $accounts = ['allbank' => 'Select all banks'] + $rawBankList->toArray();

        // Branches query
        if ($isCompany) {
            $branches = User::where('type', 'branch')
                ->pluck('name', 'id')
                ->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $ownedId)
                ->pluck('name', 'id');
        }
        $branches->prepend('All Branches', '');

        // Main query
        $baseQuery = StudentReceipt::query();

        $eagerLoads = [
            'bank',
            'voucher.heads',
            'challan' => function ($q) use ($isCompany) {
                $q->with(['heads' => function($q) use ($isCompany) {
                    if (!$isCompany) {
                        $q->with('feeHead');
                    }
                }, 'student', 'enrollstudent']);
                if (!$isCompany) {
                    $q->with('class');
                }
            },
        ];

        $baseQuery->with($eagerLoads);

        if ($isCompany) {
            $baseQuery->where('created_by', $creatorId);
        } else {
            $baseQuery->where('owned_by', $ownedId);
        }

        if ($isCompany && $request->filled('branches')) {
            $baseQuery->where('owned_by', $request->input('branches'));
        }

        if ($request->filled('default_bank') && $request->input('default_bank') !== 'allbank') {
            $baseQuery->where('bank_id', $request->input('default_bank'));
        }

        $baseQuery->whereBetween('recipt_date', [$dateFrom, $dateTo]);
        $recipts = $baseQuery->get();

        $bankAccounts = null;
        if ($request->filled('default_bank') && $request->input('default_bank') !== 'allbank') {
            $bankAccounts = BankAccount::find($request->input('default_bank'));
        }

        $heads = FeeHead::where('owned_by', $ownedId)
            ->pluck('fee_head', 'id')
            ->prepend('Select Account', '');

        $vouchers = ['all' => 'All'];
        $session = [];
        $classArr = [];
        $students = [];

        return view('studentReports.fee_receipt_summry', [
            'accounts' => $accounts,
            'vouchers' => $vouchers,
            'recipts' => $recipts,
            'session' => $session,
            'class' => $classArr,
            'students' => $students,
            'branches' => $branches,
            'bank_accounts' => $bankAccounts,
            'heads' => $heads,
            'start_date' => $dateFrom,
            'end_date' => $dateTo,
        ]);
    }
    public function fee_receipt_summary_report(Request $request)
    {
        // Use same date logic as index
        $currentMonth = intval(date('n'));    // 1..12
        $currentYear = intval(date('Y'));

        if ($currentMonth > 6) {
            $startYear = $currentYear;
        } else {
            $startYear = $currentYear - 1;
        }

        $defaultStartDate = sprintf('%04d-06-01', $startYear);
        $defaultEndDate = date('Y-m-t');
        
        $dateFrom = $request->filled('start_date') 
            ? $request->input('start_date') 
            : $defaultStartDate;
        $dateTo = $request->filled('end_date') 
            ? $request->input('end_date') 
            : $defaultEndDate;

        $selectedBranch = $request->input('branches');
        $user = \Auth::user();
        $isCompany = ($user->type === 'company');

        // Bank accounts query
        $bankQuery = BankAccount::selectRaw("id, CONCAT(bank_name, ' ', holder_name) AS name");
        if ($isCompany) {
            $bankQuery->where('created_by', $user->creatorId());
        } else {
            $bankQuery->where('owned_by', $user->ownedId());
        }
        $accounts = $bankQuery->pluck('name', 'id');
        $accounts = ['allbank' => 'Select all banks'] + $accounts->toArray();

        // Branches query
        if ($isCompany) {
            $branches = User::where('type', 'branch')
                ->pluck('name', 'id')
                ->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $user->ownedId())
                ->pluck('name', 'id');
        }
        $branches->prepend('All Branches', '');

        // Main query
        $baseQuery = StudentReceipt::query();

        $eagerLoads = [
            'bank',
            'voucher.heads',
            'challan' => function ($q) use ($isCompany) {
                $q->with(['heads' => function($q) use ($isCompany) {
                    if (!$isCompany) {
                        $q->with('feeHead');
                    }
                }, 'student', 'enrollstudent']);
                if (!$isCompany) {
                    $q->with('class');
                }
            },
        ];

        $baseQuery->with($eagerLoads);

        if ($isCompany) {
            $baseQuery->where('created_by', $user->creatorId());
        } else {
            $baseQuery->where('owned_by', $user->ownedId());
        }

        if ($isCompany && $request->filled('branches')) {
            $baseQuery->where('owned_by', $request->input('branches'));
        }

        if ($request->filled('default_bank') && $request->input('default_bank') !== 'allbank') {
            $baseQuery->where('bank_id', $request->input('default_bank'));
        }

        $baseQuery->whereBetween('recipt_date', [$dateFrom, $dateTo]);
        $recipts = $baseQuery->get();

        $heads = FeeHead::where('owned_by', $user->ownedId())
            ->pluck('fee_head', 'id')
            ->prepend('Select Account', '');

        $report = 'Fee Receipt Summary';
        $report_name = 'Fee Receipt Summary Report';
        $params = [
            'date_from' => \Carbon\Carbon::parse($dateFrom),
            'date_to' => \Carbon\Carbon::parse($dateTo),
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
        ];

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new FeeReceiptSummaryExport($recipts, $branches, $selectedBranch, $report_name, $request, $params), 
                'fee_receipt_summary_report.xlsx'
            );
        }
        
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(
                new FeeReceiptSummaryExport($recipts, $branches, $selectedBranch, $report_name, $request, $params), 
                'fee_receipt_summary_report.pdf', 
                \Maatwebsite\Excel\Excel::MPDF
            );
        }

        if ($request->filled('is_print') && $request->is_print == 1) {
            $bodyHtml = view('studentReports.fee_reciept_summary_print', compact('recipts', 'branches', 'accounts', 'heads', 'dateFrom', 'dateTo', 'selectedBranch'))->render();
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

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($finalHtml);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return $dompdf->stream('fee_receipt_summary_report.pdf', ['Attachment' => false]);
        }
    }

    public function period_wise_statistic_report(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        
        $fromDate = $request->input('from_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
        
        // Merge the dates into the request
        $request->merge([
            'date_from' => $fromDate,
            'date_to' => $toDate
        ]);
        
        $data = [];
        $period = Carbon::parse($fromDate)->startOfMonth();
        
        $branchId = $request->get('branches', null);
        $validBranchSelected = !empty($branchId) && $branches->has($branchId);
        $selectedBranchName = $validBranchSelected ? $branches->get($branchId) : 'All Branches';
        
        while ($period->lessThanOrEqualTo(Carbon::parse($toDate))) {
            $monthYear = $period->format('F Y');
            $startOfMonth = $period->startOfMonth()->format('Y-m-d');
            $endOfMonth = $period->endOfMonth()->format('Y-m-d');
        
            // Admissions
            $admissionsQuery = StudentEnrollments::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if ($validBranchSelected) {
                $admissionsQuery->where('owned_by', $branchId);
            }
            $admissionsCount = $admissionsQuery->count();
        
            // Withdrawals
            $withdrawalsQuery = StudentWithdrawal::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if ($validBranchSelected) {
                $withdrawalsQuery->where('owned_by', $branchId);
            }
            $withdrawalsCount = $withdrawalsQuery->count();
        
            // Transfers In
            $transfersInQuery = StudentTransfer::where('transfer_type', 'inter branch')->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if ($validBranchSelected) {
                $transfersInQuery->where('branch_from', $branchId);
            }
            $transfersInCount = $transfersInQuery->count();
        
            // Transfers Out
            $transfersOutQuery = StudentTransfer::where('transfer_type', 'outer branch')->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            if ($validBranchSelected) {
                $transfersOutQuery->where('branch_to', $branchId);
            }
            $transfersOutCount = $transfersOutQuery->count();
        
            if ($admissionsCount == 0 && $withdrawalsCount == 0 && $transfersInCount == 0 && $transfersOutCount == 0) {
                $period->addMonth();
                continue;
            }
            $data[] = [
                'month_year' => $monthYear,
                'admissions' => $admissionsCount,
                'withdrawals' => $withdrawalsCount,
                'transfers_in' => $transfersInCount,
                'transfers_out' => $transfersOutCount,
                'passing_out' => 0,
                'closing' => 0
            ];
            $period->addMonth();
        }

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'STATISTICS YEAR AND MONTHWISE';
            $branchName = $branchId ? ($branches[$branchId] ?? 'All Branches') : 'All Branches';
            return Excel::download(
                new PeriodWiseStatisticExport(
                    $branches,
                    $data,
                    $branchId,
                    $branchName,
                    $fromDate,
                    $report_name,
                    $request,
                    $request->all()
                ),
                'period_wise_statistic_report(STATISTICS YEAR AND MONTHWISE REPORT).xlsx'
            );
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'STATISTICS YEAR AND MONTHWISE';
            $branchName = $branchId ? ($branches[$branchId] ?? 'All Branches') : 'All Branches';
            return Excel::download(
                new PeriodWiseStatisticExport(
                    $branches,
                    $data,
                    $branchId,
                    $branchName,
                    $fromDate,
                    $report_name,
                    $request,
                    $request->all()
                ),
                'period_wise_statistic_report(STATISTICS YEAR AND MONTHWISE REPORT).pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }
        if ($request->is_print == 1) {
            $pdf = new Dompdf();
            $html = view('studentReports.period_wise_statistic_report_pdf', compact('branches', 'data', 'fromDate', 'toDate'))->render();
            $headerHtml = view('studentReports.pdf_header', compact('branches', 'data', 'fromDate', 'toDate'))->render();
            $footerHtml = view('students.concession.report.pdf.footer')->render();

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
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();
            return $dompdf->stream('period_wise_statistic_report.pdf', ['Attachment' => false]);
        }
        $selectedBranch = $branchId;
return view('studentReports.period_wise_statistic_report', compact('branches', 'data', 'fromDate', 'toDate', 'selectedBranch'));
    }
    public function student_wise_statistic_report(Request $request)
    {
        $userType = \Auth::user()->type;
        $class = [];
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $Date = $request->input('date', Carbon::now()->format('Y-m-d'));
        if ($userType == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
            $class = Classes::where('created_by', '=', $userCreatorId)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'All Classes');
            $query = StudentTransfer::with('student', 'student.class', 'student.branches', 'sectionto')
                ->where('created_by', $userCreatorId)
                ->where('status', '!=', 'draft')
                ->whereColumn('class_from', 'class_to')
                ->whereColumn('branch_from', 'branch_to')
                ->groupBy('branch_from', 'class_from', 'section_from', 'section_to', 'transfer_date');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
            $class = Classes::where('owned_by', '=', $userOwnedId)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'All Classes');
            $query = StudentTransfer::with('student', 'student.class', 'student.branches', 'sectionto')
                ->where('owned_by', $userOwnedId)
                ->where('status', '!=', 'draft')
                ->whereColumn('class_from', 'class_to')
                ->whereColumn('branch_from', 'branch_to')
                ->groupBy('branch_from', 'class_from', 'section_from', 'section_to', 'transfer_date');
        }
        if ($request->has('date')) {
            $query->whereDate('transfer_date', '<=', $request->input('date'));
        }
        $dateFrom = $request->date;
        if (empty($dateFrom)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $request->merge(['date' => $dateFrom]);
        }
        $query->where('transfer_date', $dateFrom);

        if ($request->has('branches')) {
            $query->where('branch_from', $request->input('branches'));
        }
        if ($request->has('class')) {
            if (!empty($request->input('class'))) {
                $query->where('class_from', $request->input('class'));
            }
        }
        $all_data = $query->orderBy('branch_from')->get();
        return view('studentReports.student_wise_statistic_report', compact('all_data', 'Date', 'branches', 'class', 'request'));
    }
    public function student_wise_statistic_report_pdf(Request $request)
    {
        $userType = \Auth::user()->type;
        $class = [];
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $Date = $request->input('date', Carbon::now()->format('Y-m-d'));
        if ($userType == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $class = Classes::where('created_by', '=', $userCreatorId)->get()->pluck('name', 'id');
            $class->prepend('All Classes', '');
            $query = StudentTransfer::with('student', 'student.class', 'student.branches', 'sectionto')
                ->where('created_by', $userCreatorId)
                ->where('status', '!=', 'draft')
                ->whereColumn('class_from', 'class_to')
                ->whereColumn('branch_from', 'branch_to')
                ->groupBy('branch_from', 'class_from', 'section_from', 'section_to', 'transfer_date');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $class = Classes::where('owned_by', '=', $userOwnedId)->get()->pluck('name', 'id');
            $class->prepend('All Classes', '');
            $query = StudentTransfer::with('student', 'student.class', 'student.branches', 'sectionto')
                ->where('owned_by', $userOwnedId)
                ->where('status', '!=', 'draft')
                ->whereColumn('class_from', 'class_to')
                ->whereColumn('branch_from', 'branch_to')
                ->groupBy('branch_from', 'class_from', 'section_from', 'section_to', 'transfer_date');
        }
        if ($request->has('date')) {
            $query->whereDate('transfer_date', '<=', $request->input('date'));
        }
        $dateFrom = $request->date;
        if (empty($dateFrom)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $request->merge(['date' => $dateFrom]);
        }
        $query->where('transfer_date', $dateFrom);

        if ($request->has('branches')) {
            $query->where('branch_from', $request->input('branches'));
        }
        if ($request->has('class')) {
            if (!empty($request->input('class'))) {
                $query->where('class_from', $request->input('class'));
            }
        }
        $all_data = $query->orderBy('branch_from')->get();

        // return view('studentReports.student_wise_statistic_report', compact('all_data','Date','branches','class','request'));

        $report_name = 'Student Wise Statistic Report';

        $pdf = new Dompdf();
        $html = view('studentReports.student_wise_statistic_report_pdf', compact('all_data', 'Date', 'branches', 'class', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('request', 'branches', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'landscape');
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

    //Session Wise Glance Reports
       public function sessionWise(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', 'all');
            $classes = Classes::where('created_by', $user->creatorId())->get();
            $branchId = $user->creatorId();
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
            $classes = Classes::where('owned_by', $user->ownedId())->get();
            $branchId = $user->ownedId();
        }
        $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        $sessions->prepend('All Sessions', '');
        $current_session = Session::where('active_status', 1)->where('created_by', $user->creatorId())->first()->id;

        if (!empty($session_from) && !empty($session_to)) {
            $session_from = $request->$session_from;
            $session_to = $request->session_to;
            $select_session = Session::whereBetween('id', [$session_from, $session_to])->where('created_by', $user->creatorId())->get();
            // $session_data
        } else {
            $session_from = $current_session;
            $session_to = $current_session;
            $select_session = Session::where('id', $current_session)->where('created_by', $user->creatorId())->get();
        }
        if (!empty($request->branches)) {
            if ($request->branches == '' || $request->branches == 'all') {
                $classes = Classes::where('created_by', $user->creatorId())->groupBy('name')->get();
            } else {
                $classes = Classes::where('owned_by', $request->branches)->get();
            }
            $branchId == $request->branches;
        }
        $registrationCounts = [];
        $enrollmentCounts = [];
        $withdrawalCounts = [];
        $promoteCounts = [];
        $transferinCounts = [];
        $transferoutCounts = [];
        $strengthCounts = [];
        $totalRegistrations = [];
        $totalEnrollments = [];
        $totalWithdrawals = [];
        $totalStrength = [];
        foreach ($select_session as $sel_sec) {
            $totalRegistrations[$sel_sec->id] = 0;
            $totalEnrollments[$sel_sec->id] = 0;
            $totalWithdrawals[$sel_sec->id] = 0;
            $totalStrength[$sel_sec->id] = 0;
            // dd($classes);
            $school_classes = array(
                "BUTTER FLIES" => "BUTTER FLIES",
                "INFANT" => "INFANT",
                "PLAY GROUP" => "PLAY GROUP",
                "DAYCARE" => "DAYCARE",
                "KG" => "KG",
                "PRE NURSERY" => "PRE NURSERY",
                "NURSERY" => "NURSERY",
                "Grade-1" => "Grade-1",
                "Grade-2" => "Grade-2",
                "Grade-3" => "Grade-3",
                "Grade-4" => "Grade-4",
                "Grade-5" => "Grade-5",
                "Grade-6" => "Grade-6",
                "Grade-7" => "Grade-7",
                "Grade-8" => "Grade-8",
                "MATRIC-8" => "MATRIC-8",
                "IGCSE-8" => "IGCSE-8",
                "MATRIC-9" => "MATRIC-9",
                "IGCSE-9" => "IGCSE-9",
                "MATRIC-10" => "MATRIC-10",
                "IGCSE-10" => "IGCSE-10",
                "Summer Camp" => "Summer Camp",
                "LITTLE ANGLES" => "LITTLE ANGLES"
            );
            foreach ($school_classes as $class) {
                if (!empty($request->branches) && $request->branches != '' && $request->branches != 'all') {
                    $a = Classes::where('name', $class)->where('owned_by', $request->branches)->pluck('id')->toArray();
                } else {
                    $a = Classes::where('name', $class)->pluck('id')->toArray();
                }
                $registrationCounts[$sel_sec->id][$class] = StudentRegistration::where('session_id', $sel_sec->id)->whereIn('reg_class', $a);
                // $registrationCounts[$sel_sec->id][$class->id] = StudentRegistration::where('session_id', $sel_sec->id)->where('class_id', $class->id);
                // dd($a,$class,$registrationCounts[$sel_sec->id][$class]->get());

                $enrollmentCounts[$sel_sec->id][$class] = StudentEnrollments::where('active_status', 1)->whereIn('class_id', $a);

                $promoteCounts[$sel_sec->id][$class] = StudentPromotions::where('new_session', $sel_sec->id)->whereIn('class_to', $a);
                $transferinCounts[$sel_sec->id][$class] = StudentTransfer::where('session_id', $sel_sec->id)->whereIn('class_to', $a)->where('branch_to', $branchId)->count();

                $transferoutCounts[$sel_sec->id][$class] = StudentTransfer::where('session_id', $sel_sec->id)->whereIn('class_from', $a)->where('branch_from', $branchId)->count();

                $withdrawalCounts[$sel_sec->id][$class] = StudentWithdrawal::where('session_id', $sel_sec->id)->whereIn('class_id', $a);
                if ($user->type != 'company') {
                    $registrationCounts[$sel_sec->id][$class] = $registrationCounts[$sel_sec->id][$class]->where('owned_by', $branchId)->count();
                    $enrollmentCounts[$sel_sec->id][$class] = $enrollmentCounts[$sel_sec->id][$class]->where('owned_by', $branchId)->count();
                    $promoteCounts[$sel_sec->id][$class] = $promoteCounts[$sel_sec->id][$class]->where('owned_by', $branchId)->count();
                    $withdrawalCounts[$sel_sec->id][$class] = $withdrawalCounts[$sel_sec->id][$class]->where('owned_by', $branchId)->count();
                } else {
                    $registrationCounts[$sel_sec->id][$class] = $registrationCounts[$sel_sec->id][$class]->count();
                    $enrollmentCounts[$sel_sec->id][$class] = $enrollmentCounts[$sel_sec->id][$class]->count();
                    $promoteCounts[$sel_sec->id][$class] = $promoteCounts[$sel_sec->id][$class]->count();
                    $withdrawalCounts[$sel_sec->id][$class] = $withdrawalCounts[$sel_sec->id][$class]->count();
                }
                // dd($enrollmentCounts);
                // Calculate the strength
                $strengthCounts[$sel_sec->id][$class] = ($enrollmentCounts[$sel_sec->id][$class] + $promoteCounts[$sel_sec->id][$class] + $transferinCounts[$sel_sec->id][$class]) - ($withdrawalCounts[$sel_sec->id][$class] + $transferoutCounts[$sel_sec->id][$class]);

                $totalRegistrations[$sel_sec->id] += $registrationCounts[$sel_sec->id][$class];
                $totalEnrollments[$sel_sec->id] += $enrollmentCounts[$sel_sec->id][$class];
                $totalWithdrawals[$sel_sec->id] += $withdrawalCounts[$sel_sec->id][$class];
                $totalStrength[$sel_sec->id] += $strengthCounts[$sel_sec->id][$class];
            }
        }
        if ($request->has('export') && in_array($request->export, ['excel', 'pdf'])) {
            $report_name = 'STUDENT STATICS SESSION & CLASSWISE';
            $params = $request->all();
            
            $exportType = $request->export == 'pdf' ? \Maatwebsite\Excel\Excel::MPDF : \Maatwebsite\Excel\Excel::XLSX;
            $extension = $request->export == 'pdf' ? 'pdf' : 'xlsx';
            
            return Excel::download(
                new SessionWiseReportExport(
                    $branches, 
                    $classes, 
                    $sessions, 
                    $select_session, 
                    $registrationCounts, 
                    $strengthCounts, 
                    $enrollmentCounts, 
                    $withdrawalCounts, 
                    $totalRegistrations, 
                    $totalEnrollments, 
                    $totalWithdrawals, 
                    $totalStrength, 
                    $school_classes, 
                    $report_name, 
                    $request->all(), 
                    $params
                ), 
                'Student_Statics_Session_Classwise_Report.'.$extension, 
                $exportType
            );
        }
        return view('studentReports.sessionwise', compact(
            'branches',
            'classes',
            'sessions',
            'select_session',
            'registrationCounts',
            'strengthCounts',
            'enrollmentCounts',
            'withdrawalCounts',
            'totalRegistrations',
            'totalEnrollments',
            'totalWithdrawals',
            'totalStrength',
            'a',
            'school_classes'
        ));
    }
    //Session Month Wise Glance Reports
    public function sessionMonthWise(Request $request)
    {
        $user = Auth::user();

        // 1. Determine branches & classes
        if ($user->type === 'company') {
            $branchOptions = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->pluck('name', 'id');
            $branchOptions->prepend($user->name, $user->id);
            $branchOptions->prepend('Select Branch', '');
            $classesQueryOwner = $user->creatorId();
            $defaultBranchId = $user->id;
        } else {
            $branchOptions = User::where('id', $user->ownedId())
                ->pluck('name', 'id');
            $branchOptions->prepend('Select Branch', '');
            $classesQueryOwner = $user->ownedId();
            $defaultBranchId = $user->ownedId();
        }

        // 2. Year dropdown range
        $startYear = 2015;
        $currentYear = date('Y') + 2;
        $years = array_combine(
            range($startYear, $currentYear),
            range($startYear, $currentYear)
        );

        // 3. Month name lookup
        $monthNames = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        // 4. Read & sanitize incoming filters
        $monthFrom = (int) $request->input('month_from', 1);
        $monthTo = (int) $request->input('month_to', 12);
        $year = (int) $request->input('year', date('Y'));
        $branchId = $defaultBranchId;

        // if user chose “all” or a specific branch
        if ($request->filled('branches') && $request->branches != 'all') {
            $branchId = $request->branches;
        }

        // reload classes if branch filter applied
        $classes = Classes::where('owned_by', $classesQueryOwner)
            ->when($request->filled('branches') && $request->branches !== 'all', function ($q) use ($branchId) {
                $q->where('owned_by', $branchId);
            })
            ->get();

        // 5. Build months array (handles wrap-around)
        if ($monthFrom <= $monthTo) {
            $months = range($monthFrom, $monthTo);
        } else {
            $months = array_merge(
                range($monthFrom, 12),
                range(1, $monthTo)
            );
        }

        $labelFrom = $monthNames[$monthFrom];
        $labelTo = $monthNames[$monthTo];

        // 6. Define your fixed school classes list
        $schoolClasses = [
            "BUTTER FLIES",
            "INFANT",
            "PLAY GROUP",
            "DAYCARE",
            "KG",
            "PRE NURSERY",
            "NURSERY",
            "Grade-1",
            "Grade-2",
            "Grade-3",
            "Grade-4",
            "Grade-5",
            "Grade-6",
            "Grade-7",
            "Grade-8",
            "MATRIC-8",
            "IGCSE-8",
            "MATRIC-9",
            "IGCSE-9",
            "MATRIC-10",
            "IGCSE-10",
            "Summer Camp",
            "LITTLE ANGLES"
        ];

        // 7. Initialize counters
        $totals = [
            'registrations' => [],
            'enrollments' => [],
            'withdrawals' => [],
            'strength' => [],
        ];

        foreach ($months as $m) {
            // zero out each month
            $totals['registrations'][$m] = 0;
            $totals['enrollments'][$m] = 0;
            $totals['withdrawals'][$m] = 0;
            $totals['strength'][$m] = 0;
        }

        // 8. Fetch per-class counts
        foreach ($schoolClasses as $className) {
            // find all class IDs for this name (filtered by branch if needed)
            $classIds = Classes::where('name', $className)
                ->when($request->filled('branches') && $request->branches !== 'all', function ($q) use ($branchId) {
                    $q->where('owned_by', $branchId);
                })
                ->pluck('id')
                ->toArray();

            foreach ($months as $m) {
                // format month as two digits
                $mm = str_pad($m, 2, '0', STR_PAD_LEFT);

                $regs = StudentRegistration::whereYear('regdate', $year)
                    ->whereMonth('regdate', $mm)
                    ->whereIn('reg_class', $classIds)
                    ->count();

                $enrs = StudentEnrollments::whereYear('adm_date', $year)
                    ->whereMonth('adm_date', $mm)
                    ->whereIn('class_id', $classIds)
                    ->count();

                $prom = StudentPromotions::whereYear('promotion_date', $year)
                    ->whereMonth('promotion_date', $mm)
                    ->whereIn('class_to', $classIds)
                    ->count();

                $tin = StudentTransfer::whereYear('transfer_date', $year)
                    ->whereMonth('transfer_date', $mm)
                    ->whereIn('class_to', $classIds)
                    ->where('status', 'approved')
                    ->where('branch_to', $branchId)
                    ->count();

                $tout = StudentTransfer::whereYear('transfer_date', $year)
                    ->whereMonth('transfer_date', $mm)
                    ->whereIn('class_from', $classIds)
                    ->where('status', 'approved')
                    ->where('branch_from', $branchId)
                    ->count();

                $withd = StudentWithdrawal::whereYear('withdraw_date', $year)
                    ->whereMonth('withdraw_date', $mm)
                    ->whereIn('class_id', $classIds)
                    ->where('status', 'approved')
                    ->where('owned_by', $branchId)
                    ->count();

                // compute strength
                $strength = ($enrs + $prom + $tin) - ($withd + $tout);

                // aggregate into totals
                $totals['registrations'][$m] += $regs;
                $totals['enrollments'][$m] += $enrs;
                $totals['withdrawals'][$m] += $withd;
                $totals['strength'][$m] += $strength;

                // store per‐class if you need to display that detail
                $registrationCounts[$m][$className] = $regs;
                $enrollmentCounts[$m][$className] = $enrs;
                $promoteCounts[$m][$className] = $prom;
                $transferinCounts[$m][$className] = $tin;
                $transferoutCounts[$m][$className] = $tout;
                $withdrawalCounts[$m][$className] = $withd;
                $strengthCounts[$m][$className] = $strength;
            }
        }

        // 9. Handle exports/printing
        if ($request->export === 'excel') {
            return Excel::download(new SessionMonthWiseReportExport(
                $branchOptions,
                $classes,
                $months,
                $year,
                $labelFrom,
                $labelTo,
                $registrationCounts,
                $strengthCounts,
                $enrollmentCounts,
                $withdrawalCounts,
                $totals['registrations'],
                $totals['enrollments'],
                $totals['withdrawals'],
                $totals['strength']
            ), 'sessionwise.xlsx');
        }

        if ($request->print === 'pdf') {
            $report_name = 'MonthSessionWise Report';
            $periods = false;
            $html = view('studentReports.pdf.monthsessionwise', [
                'branches' => $branchOptions,
                'classes' => $classes,
                'months' => $months,
                'years' => $years,
                'monthNames' => $monthNames,
                'registrationCounts' => $registrationCounts,
                'enrollmentCounts' => $enrollmentCounts,
                'withdrawalCounts' => $withdrawalCounts,
                'transferinCounts' => $transferinCounts,
                'transferoutCounts' => $transferoutCounts,
                'promoteCounts' => $promoteCounts,
                'strengthCounts' => $strengthCounts,
                'totalRegistrations' => $totals['registrations'],
                'totalEnrollments' => $totals['enrollments'],
                'totalWithdrawals' => $totals['withdrawals'],
                'totalStrength' => $totals['strength'],
                'labelFrom' => $labelFrom,
                'labelTo' => $labelTo,
                'selectedYear' => $year,
                'selectedBranchId' => $branchId,
                'selectedMonthFrom' => $monthFrom,
                'selectedMonthTo' => $monthTo,
                'schoolClasses' => $schoolClasses,
            ])->render();
            $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name', 'periods'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();
            return $dompdf->stream('sessionMonthWiseReport.pdf', ['Attachment' => false]);
        }

        // 10. Finally render html view
        return view('studentReports.monthsessionwise', [
            'branches' => $branchOptions,
            'classes' => $classes,
            'months' => $months,
            'years' => $years,
            'monthNames' => $monthNames,
            'registrationCounts' => $registrationCounts,
            'enrollmentCounts' => $enrollmentCounts,
            'withdrawalCounts' => $withdrawalCounts,
            'transferinCounts' => $transferinCounts,
            'transferoutCounts' => $transferoutCounts,
            'promoteCounts' => $promoteCounts,
            'strengthCounts' => $strengthCounts,
            'totalRegistrations' => $totals['registrations'],
            'totalEnrollments' => $totals['enrollments'],
            'totalWithdrawals' => $totals['withdrawals'],
            'totalStrength' => $totals['strength'],
            'labelFrom' => $labelFrom,
            'labelTo' => $labelTo,
            'selectedYear' => $year,
            'selectedBranchId' => $branchId,
            'selectedMonthFrom' => $monthFrom,
            'selectedMonthTo' => $monthTo,
            'schoolClasses' => $schoolClasses,

        ]);
    }

        // statistics session and class wise
    public function monthBranchWiseReport(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            // $branches->prepend('Select Branch', '');
            // $classes = Classes::where('owned_by', $user->creatorId())->get();
            $branchId = $user->creatorId();
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            // $classes = Classes::where('owned_by', $user->ownedId())->get();
            $branchId = $user->ownedId();
        }
        // $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        // $sessions->prepend('Select Session', '');
        // $current_session = Session::where('active_status', 1)->where('created_by', $user->creatorId())->first()->id;

        $startYear = 2015;
        $currentYear = date('Y') + 2; // Get the current year and add 2

        // Create an array to hold the years
        $years = [];

        // Populate the array with years from startYear to currentYear
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $years[$year] = $year;
        }

        $month_data = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        $month_f = 'January';
        $month_t = 'December';
        if (!empty($request->month_from) && !empty($request->month_to)) {
            // If month_from is greater than month_to, it wraps around the year
            if ($request->month_from > $request->month_to) {
                $months = array_merge(range($request->month_from, 12), range(1, $request->month_to));
            } else {
                $months = range($request->month_from, $request->month_to);
            }
            $month_f = $month_data[$request->month_from];
            $month_t = $month_data[$request->month_to];
        } else {
            $month_from = 1;
            $month_to = 12;
            // If month_from is greater than month_to, it wraps around the year
            if ($month_from > $month_to) {
                $months = array_merge(range($month_from, 12), range(1, $month_to));
            } else {
                $months = range($month_from, $month_to);
            }
            $month_f = $month_data[$month_from];
            $month_t = $month_data[$month_to];
        }
        if (!empty($request->year)) {
            $year = date('Y', strtotime($request->year));
        } else {
            $year = date('Y');
        }

        $registrationCounts = [];
        $enrollmentCounts = [];
        $withdrawalCounts = [];
        $promoteCounts = [];
        $transferinCounts = [];
        $transferoutCounts = [];
        $strengthCounts = [];
        $totalRegistrations = [];
        $totalEnrollments = [];
        $totalWithdrawals = [];
        $totalStrength = [];
        foreach ($months as $monthName) {
            $mon = date('m', mktime(0, 0, 0, $monthName, 1));
            $totalRegistrations[$monthName] = 0;
            $totalEnrollments[$monthName] = 0;
            $totalWithdrawals[$monthName] = 0;
            $totalStrength[$monthName] = 0;
            foreach ($branches as $branchId => $branch) {
                $registrationCounts[$monthName][$branchId] = StudentRegistration::whereMonth('regdate', $mon)->whereYear('regdate', $year)->where('owned_by', $branchId)->count();

                $enrollmentCounts[$monthName][$branchId] = StudentEnrollments::whereMonth('adm_date', $mon)->whereYear('adm_date', $year)->where('owned_by', $branchId)->count();

                $promoteCounts[$monthName][$branchId] = StudentPromotions::whereMonth('promotion_date', $mon)->whereYear('promotion_date', $year)->where('owned_by', $branchId)->count();

                $transferinCounts[$monthName][$branchId] = StudentTransfer::whereMonth('transfer_date', $mon)->whereYear('transfer_date', $year)->where('status', 'approved')->where('branch_to', $branchId)->count();

                $transferoutCounts[$monthName][$branchId] = StudentTransfer::whereMonth('transfer_date', $mon)->whereYear('transfer_date', $year)->where('status', 'approved')->where('branch_from', $branchId)->count();

                $withdrawalCounts[$monthName][$branchId] = StudentWithdrawal::whereMonth('withdraw_date', $mon)->whereYear('withdraw_date', $year)->where('status', 'approved')->where('owned_by', $branchId)->count();

                // Calculate the strength
                $strengthCounts[$monthName][$branchId] = ($enrollmentCounts[$monthName][$branchId] + $promoteCounts[$monthName][$branchId] + $transferinCounts[$monthName][$branchId]) - ($withdrawalCounts[$monthName][$branchId] + $transferoutCounts[$monthName][$branchId]);

                $totalRegistrations[$monthName] += $registrationCounts[$monthName][$branchId];
                $totalEnrollments[$monthName] += $enrollmentCounts[$monthName][$branchId];
                $totalWithdrawals[$monthName] += $withdrawalCounts[$monthName][$branchId];
                $totalStrength[$monthName] += $strengthCounts[$monthName][$branchId];
            }
        }

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new SessionMonthWiseBranchReportExport($branches, $months, $year, $month_f, $month_t, $registrationCounts, $strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength), 'session_month_branch_wise.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new SessionMonthWiseBranchReportExport($branches, $months, $year, $month_f, $month_t, $registrationCounts, $strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength), 'session_month_branch_wise.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('studentReports.monthsessionbranchwise', compact(
            'branches',
            'months',
            'years',
            'month_data',
            'registrationCounts',
            'strengthCounts',
            'enrollmentCounts',
            'withdrawalCounts',
            'totalRegistrations',
            'totalEnrollments',
            'totalWithdrawals',
            'totalStrength'
        ));
    }

    //Session Month Wise Glance Reports
    public function sessionMonthBranchWise(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            // $branches->prepend('Select Branch', '');
            // $classes = Classes::where('owned_by', $user->creatorId())->get();
            $branchId = $user->creatorId();
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            // $classes = Classes::where('owned_by', $user->ownedId())->get();
            $branchId = $user->ownedId();
        }
        // $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        // $sessions->prepend('Select Session', '');
        // $current_session = Session::where('active_status', 1)->where('created_by', $user->creatorId())->first()->id;

        $startYear = 2015;
        $currentYear = date('Y') + 2; // Get the current year and add 2

        // Create an array to hold the years
        $years = [];

        // Populate the array with years from startYear to currentYear
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $years[$year] = $year;
        }

        $month_data = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        $month_f = 'January';
        $month_t = 'December';
        if (!empty($request->month_from) && !empty($request->month_to)) {
            // If month_from is greater than month_to, it wraps around the year
            if ($request->month_from > $request->month_to) {
                $months = array_merge(range($request->month_from, 12), range(1, $request->month_to));
            } else {
                $months = range($request->month_from, $request->month_to);
            }
            $month_f = $month_data[$request->month_from];
            $month_t = $month_data[$request->month_to];
        } else {
            $month_from = 1;
            $month_to = 12;
            // If month_from is greater than month_to, it wraps around the year
            if ($month_from > $month_to) {
                $months = array_merge(range($month_from, 12), range(1, $month_to));
            } else {
                $months = range($month_from, $month_to);
            }
            $month_f = $month_data[$month_from];
            $month_t = $month_data[$month_to];
        }
        if (!empty($request->year)) {
            $year = date('Y', strtotime($request->year));
        } else {
            $year = date('Y');
        }

        $registrationCounts = [];
        $enrollmentCounts = [];
        $withdrawalCounts = [];
        $promoteCounts = [];
        $transferinCounts = [];
        $transferoutCounts = [];
        $strengthCounts = [];
        $totalRegistrations = [];
        $totalEnrollments = [];
        $totalWithdrawals = [];
        $totalStrength = [];
        foreach ($months as $monthName) {
            $mon = date('m', mktime(0, 0, 0, $monthName, 1));
            $totalRegistrations[$monthName] = 0;
            $totalEnrollments[$monthName] = 0;
            $totalWithdrawals[$monthName] = 0;
            $totalStrength[$monthName] = 0;
            foreach ($branches as $branchId => $branch) {
                $registrationCounts[$monthName][$branchId] = StudentRegistration::whereMonth('regdate', $mon)->whereYear('regdate', $year)->where('owned_by', $branchId)->count();

                $enrollmentCounts[$monthName][$branchId] = StudentEnrollments::whereMonth('adm_date', $mon)->whereYear('adm_date', $year)->where('owned_by', $branchId)->count();

                $promoteCounts[$monthName][$branchId] = StudentPromotions::whereMonth('promotion_date', $mon)->whereYear('promotion_date', $year)->where('owned_by', $branchId)->count();

                $transferinCounts[$monthName][$branchId] = StudentTransfer::whereMonth('transfer_date', $mon)->whereYear('transfer_date', $year)->where('status', 'approved')->where('branch_to', $branchId)->count();

                $transferoutCounts[$monthName][$branchId] = StudentTransfer::whereMonth('transfer_date', $mon)->whereYear('transfer_date', $year)->where('status', 'approved')->where('branch_from', $branchId)->count();

                $withdrawalCounts[$monthName][$branchId] = StudentWithdrawal::whereMonth('withdraw_date', $mon)->whereYear('withdraw_date', $year)->where('status', 'approved')->where('owned_by', $branchId)->count();

                // Calculate the strength
                $strengthCounts[$monthName][$branchId] = ($enrollmentCounts[$monthName][$branchId] + $promoteCounts[$monthName][$branchId] + $transferinCounts[$monthName][$branchId]) - ($withdrawalCounts[$monthName][$branchId] + $transferoutCounts[$monthName][$branchId]);

                $totalRegistrations[$monthName] += $registrationCounts[$monthName][$branchId];
                $totalEnrollments[$monthName] += $enrollmentCounts[$monthName][$branchId];
                $totalWithdrawals[$monthName] += $withdrawalCounts[$monthName][$branchId];
                $totalStrength[$monthName] += $strengthCounts[$monthName][$branchId];
            }
        }
        // dd($branches, $classes, $months, $month_data, $registrationCounts, $enrollmentCounts, $withdrawalCounts, $transferinCounts, $transferoutCounts, $strengthCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength);
        // if ($request->has('export') && $request->export == 'excel') {

        //     return Excel::download(new SessionMonthWiseBranchReportExport($branches, $months, $year, $month_f, $month_t, $registrationCounts, $strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength), 'sessionwise.xlsx');
        // }
                if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Monthly Statistics Report for the Month of ' . date('M-Y', strtotime($selectedDate));
            return Excel::download(new MonthlyStatistics($branches, $report, $selectedBranchId, $selectedDate, $report_name, $request->all()), 'MonthlyStatistics_Report.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Monthly Statistics Report for the Month of ' . date('M-Y', strtotime($selectedDate));
            return Excel::download(new MonthlyStatistics($branches, $report, $selectedBranchId, $selectedDate, $report_name, $request->all()), 'MonthlyStatistics_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('studentReports.pdf.monthsessionbranchwise', compact(
                'branches',
                'months',
                'year',
                'month_data',
                'registrationCounts',
                'strengthCounts',
                'enrollmentCounts',
                'withdrawalCounts',
                'totalRegistrations',
                'totalEnrollments',
                'totalWithdrawals',
                'totalStrength'
            ))->render();
            $headerHtml = view('studentReports.pdf_header', compact('request'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();

            return $dompdf->stream('sessionMonthWiseReport.pdf', ['Attachment' => false]);
        }
        return view('studentReports.monthsessionbranchwise', compact(
            'branches',
            'months',
            'years',
            'month_data',
            'registrationCounts',
            'strengthCounts',
            'enrollmentCounts',
            'withdrawalCounts',
            'totalRegistrations',
            'totalEnrollments',
            'totalWithdrawals',
            'totalStrength'
        ));
    }

    //Session Wise Glance Reports
    public function sessionBranchWise(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
        }
        $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        $sessions->prepend('Select Session', '');
        $current_session = Session::where('active_status', 1)->where('created_by', $user->creatorId())->first()->id;

        if (!empty($session_from) && !empty($session_to)) {
            $session_from = $request->$session_from;
            $session_to = $request->session_to;
            $select_session = Session::whereBetween('id', [$session_from, $session_to])->where('created_by', $user->creatorId())->get();
            // $session_data
        } else {
            $session_from = $current_session;
            $session_to = $current_session;
            $select_session = Session::where('id', $current_session)->where('created_by', $user->creatorId())->get();
        }

        $registrationCounts = [];
        $enrollmentCounts = [];
        $withdrawalCounts = [];
        $promoteCounts = [];
        $transferinCounts = [];
        $transferoutCounts = [];
        $strengthCounts = [];
        $totalRegistrations = [];
        $totalEnrollments = [];
        $totalWithdrawals = [];
        $totalStrength = [];
        foreach ($select_session as $sel_sec) {
            $totalRegistrations[$sel_sec->id] = 0;
            $totalEnrollments[$sel_sec->id] = 0;
            $totalWithdrawals[$sel_sec->id] = 0;
            $totalStrength[$sel_sec->id] = 0;
            foreach ($branches as $branchId => $branch) {
                $registrationCounts[$sel_sec->id][$branchId] = StudentRegistration::where('session_id', $sel_sec->id)->where('owned_by', $branchId)->count();

                $enrollmentCounts[$sel_sec->id][$branchId] = StudentEnrollments::
                where('adm_session', $sel_sec->id)->
                // where('owned_by', $branchId)->
                count();

                $promoteCounts[$sel_sec->id][$branchId] = StudentPromotions::where('new_session', $sel_sec->id)->where('owned_by', $branchId)->count();

                $transferinCounts[$sel_sec->id][$branchId] = StudentTransfer::where('session_id', $sel_sec->id)->where('status', 'approved')->where('branch_to', $branchId)->count();

                $transferoutCounts[$sel_sec->id][$branchId] = StudentTransfer::where('session_id', $sel_sec->id)->where('status', 'approved')->where('branch_from', $branchId)->count();

                $withdrawalCounts[$sel_sec->id][$branchId] = StudentWithdrawal::where('session_id', $sel_sec->id)->where('status', 'approved')->where('owned_by', $branchId)->count();

                // Calculate the strength
                $strengthCounts[$sel_sec->id][$branchId] = ($enrollmentCounts[$sel_sec->id][$branchId] + $promoteCounts[$sel_sec->id][$branchId] + $transferinCounts[$sel_sec->id][$branchId]) - ($withdrawalCounts[$sel_sec->id][$branchId] + $transferoutCounts[$sel_sec->id][$branchId]);

                $totalRegistrations[$sel_sec->id] += $registrationCounts[$sel_sec->id][$branchId];
                $totalEnrollments[$sel_sec->id] += $enrollmentCounts[$sel_sec->id][$branchId];
                $totalWithdrawals[$sel_sec->id] += $withdrawalCounts[$sel_sec->id][$branchId];
                $totalStrength[$sel_sec->id] += $strengthCounts[$sel_sec->id][$branchId];
            }
        }
        // dd($branches, $classes, $sessions, $registrationCounts, $enrollmentCounts, $withdrawalCounts, $transferinCounts, $transferoutCounts, $strengthCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength);
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new SessionBranchWiseReportExport($branches, $sessions, $select_session, $registrationCounts, $strengthCounts, $enrollmentCounts, $withdrawalCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength), 'session_branch_wise.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('studentReports.pdf.sessionbranchwise', compact(
                'branches',
                'sessions',
                'select_session',
                'current_session',
                'registrationCounts',
                'strengthCounts',
                'enrollmentCounts',
                'withdrawalCounts',
                'totalRegistrations',
                'totalEnrollments',
                'totalWithdrawals',
                'totalStrength'
            ))->render();
            $headerHtml = view('studentReports.pdf_header', compact('request'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();

            return $dompdf->stream('sessionWiseReport.pdf');
        }
        return view('studentReports.sessionbranchwise', compact(
            'branches',
            'sessions',
            'select_session',
            'current_session',
            'registrationCounts',
            'strengthCounts',
            'enrollmentCounts',
            'withdrawalCounts',
            'totalRegistrations',
            'totalEnrollments',
            'totalWithdrawals',
            'totalStrength'
        ));
    }
    //Session Wise Glance Reports
    public function tuition_feereport(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
        }
        $branches->prepend('All Branches', 'All Branches');
        if (!empty($request->month)) {
            $year = date('Y', strtotime($request->month));
            $month = date('m', strtotime($request->month));
        } else {
            $year = date('Y');
            $month = date('m');
        }
        $month_name = \Carbon\Carbon::parse(request()->get('month') ?? date('Y-m'))->format('F Y');

        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower('%TUITION%')])->first();

        // Base query for both cases
        $query = DB::table('challan_heads')
            ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->select(
                DB::raw('(challan_heads.price - challan_heads.concession) as final_price'),
                DB::raw('COUNT(*) as price_count'),
                DB::raw('SUM(challan_heads.price - challan_heads.concession) as total_price'),
                DB::raw('SUM(challan_heads.concession) as total_concession')
            )
            ->whereYear('challans.fee_month', $year)
            ->whereMonth('challans.fee_month', $month)
            ->where('challan_heads.head_id', $head->id);

        // Apply branch filtering if requested
        if (!empty($request->branches)) {
            $type = $branches[$request->branches];
            $query->where('challans.owned_by', $request->branches);
        } else {
            $query->where('challans.created_by', $user->creatorId());
            $type = 'LYNX NETWORK';
        }

        // Fetch the results
        $challanHeads = $query->groupBy('final_price')->get();

        // Min and max prices query
        $minMaxQuery = DB::table('challan_heads')
            ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
            ->select(
                DB::raw('MIN(challan_heads.price - challan_heads.concession) as min_final_price'),
                DB::raw('MAX(challan_heads.price - challan_heads.concession) as max_final_price')
            )
            ->whereYear('challans.fee_month', $year)
            ->whereMonth('challans.fee_month', $month)
            ->where('challan_heads.head_id', $head->id);

        // Apply branch filtering for min-max query
        if (!empty($request->branches)) {
            $minMaxQuery->where('challans.owned_by', $request->branches);
        } else {
            $minMaxQuery->where('challans.created_by', $user->creatorId());
        }

        // Fetch the min and max prices
        $minMaxPrices = $minMaxQuery->first();

        $minPrice = $minMaxPrices->min_final_price;
        $maxPrice = $minMaxPrices->max_final_price;
        $rangeStep = round(($maxPrice - $minPrice) / 6);

        $ranges = [];
        $currentRangeStart = 0;

        // Create 6 ranges and count students in each
        for ($i = 0; $i < 6; $i++) {
            $currentRangeEnd = ($i == 5) ? $maxPrice : $currentRangeStart + $rangeStep;

            $studentsInRangeQuery = DB::table('challan_heads')
                ->join('challans', 'challan_heads.challan_id', '=', 'challans.id')
                ->whereYear('challans.fee_month', $year)
                ->whereMonth('challans.fee_month', $month)
                ->where('challan_heads.head_id', $head->id)
                ->whereBetween(DB::raw('(challan_heads.price - challan_heads.concession)'), [$currentRangeStart, $currentRangeEnd]);

            // Apply branch filtering for students in range query
            if (!empty($request->branches)) {
                $studentsInRangeQuery->where('challans.owned_by', $request->branches);
            } else {
                $studentsInRangeQuery->where('challans.created_by', $user->creatorId());
            }

            // Count students in this range
            $studentsInRange = $studentsInRangeQuery->count();

            $ranges[] = [
                'start' => $currentRangeStart,
                'end' => $currentRangeEnd,
                'total_students' => $studentsInRange
            ];

            $currentRangeStart = $currentRangeEnd;
        }

        // dd($branches, $classes, $sessions, $registrationCounts, $enrollmentCounts, $withdrawalCounts, $transferinCounts, $transferoutCounts, $strengthCounts, $totalRegistrations, $totalEnrollments, $totalWithdrawals, $totalStrength);
        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Fee Insight Report ' . $month_name;
            return Excel::download(new TuitionFeeReportExport($branches, $challanHeads, $ranges, $month_name, $type, $report_name, $request->all()), 'Fee_Insight_Report(Tuition_fee).xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Fee Insight Report ' . $month_name;
            return Excel::download(new TuitionFeeReportExport($branches, $challanHeads, $ranges, $month_name, $type, $report_name, $request->all()), 'Fee_Insight_Report(Tuition_fee).pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('studentReports.pdf.tuition_feereport', compact(
                'branches',
                'challanHeads',
                'ranges',
            ))->render();
            $headerHtml = view('studentReports.pdf_header', compact('request'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();

            return $dompdf->stream('Tuition_fee.pdf', ['Attachment' => false]);
        }
        return view('studentReports.tuition_feereport', compact(
            'branches',
            'challanHeads',
            'ranges',
        ));
    }

    public function monthlystatistics(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', 'All Branches');
        } else {
            $branches = User::where('id', '=', $user->ownedId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
        }

        $selectedBranchId = $request->branches ?? null;
        $selectedDate = $request->date ?? date('Y-m-d');
        $selectedMonth = date('m', strtotime($selectedDate));
        $selectedYear = date('Y', strtotime($selectedDate));
        $previousMonth = date('m', strtotime('-1 month', strtotime($selectedDate)));
        $previousYear = date('Y', strtotime('-1 month', strtotime($selectedDate)));
        // dd($previousMonth,$previousYear);
        $report = [];

        if ($selectedBranchId) {
            $branchIds = [$selectedBranchId];
        } else {
            $branchIds = $branches->keys();
        }
        foreach ($branchIds as $branchId) {
            // $branchId = 53;
            $branchName = $branches[$branchId];

            $classes = Classes::with(['classSection'])
                ->where('active_status', 1)
                ->where('owned_by', $branchId)
                ->get();
            // dd($classes);
            foreach ($classes as $class) {
                foreach ($class->classSection as $index => $section) {
                    // if($index == 0){
                    //     dd($section);
                    // }
                    $opening = StudentEnrollments::
                        where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->where(function ($query) use ($previousMonth, $previousYear) {
                            $query->whereYear('adm_date', '<=', $previousYear)
                                ->whereMonth('adm_date', '<=', $previousMonth);
                        })
                        ->count();

                    // dd($section,$opening,$class,$branchId);
                    $newAdmissions = StudentEnrollments::where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->whereMonth('adm_date', $selectedMonth)
                        ->whereYear('adm_date', $selectedYear)
                        ->count();
                    $withdrawals = StudentWithdrawal::where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->whereMonth('withdraw_date', $selectedMonth)
                        ->whereYear('withdraw_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $transferIn = StudentTransfer::where('section_to', $section->section_id)
                        ->where('class_to', $class->id)
                        ->where('branch_to', $branchId)
                        ->whereMonth('transfer_date', $selectedMonth)
                        ->whereYear('transfer_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $transferOut = StudentTransfer::where('section_from', $section->section_id)
                        ->where('class_from', $class->id)
                        ->where('branch_from', $branchId)
                        ->whereMonth('transfer_date', $selectedMonth)
                        ->whereYear('transfer_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $closingBalance = $opening + $newAdmissions + $transferIn - $withdrawals - $transferOut;

                    $report[] = [
                        'branch' => $branchName,
                        'class' => $class->name,
                        'section' => $section->sectionName->name,
                        'opening' => $opening,
                        'new_admissions' => $newAdmissions,
                        'transfer_in' => $transferIn,
                        'withdrawals' => $withdrawals,
                        'transfer_out' => $transferOut,
                        'closing_balance' => $closingBalance,
                    ];

                }
            }
        }
        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Branch Month Wise Report for the Month of ' . date('M-Y', strtotime($selectedDate));
            return Excel::download(new MonthlyStatistics($branches, $report, $selectedBranchId, $selectedDate, $report_name, $request->all()), 'Branch_Month_Wise_Report.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Branch Month Wise Report for the Month of ' . date('M-Y', strtotime($selectedDate));
            return Excel::download(new MonthlyStatistics($branches, $report, $selectedBranchId, $selectedDate, $report_name, $request->all()), 'Branch_Month_Wise_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $pdf = new Dompdf();
            $html = view('studentReports.pdf.monthlystatistics', compact('branches', 'report', 'selectedBranchId', 'selectedDate'))->render();
            $headerHtml = view('studentReports.pdf_header', compact('request'));
            $footerHtml = view('students.concession.report.pdf.footer')->render();
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
                        <div class="header">' . $headerHtml . '</div>
                        <div class="footer">' . $footerHtml . '</div>
                        ' . $html . '
                        </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();

            return $dompdf->stream('MonthlyStatistics.pdf', ['Attachment' => false]);
        }
        return view('studentReports.monthlystatistics', compact('branches', 'report', 'selectedBranchId', 'selectedDate'));
    }

    public function monthlychallanreport(Request $request)
{
    // dd($request->all());
    set_time_limit(1000);
    ini_set('memory_limit', '512M');
    
    $user = \Auth::user();
    $creatorId = $user->creatorId();
    $isCompany = $user->type == 'company';
    $feeMonth = date('Y-m-01', strtotime($request->date ?? date('Y-m-d')));

    // Cache branches (1 hour)
    $branches = \Cache::remember("branches_{$creatorId}_{$isCompany}", 3600, function () use ($isCompany, $creatorId, $user) {
        if ($isCompany) {
            $branches = User::select('id', 'name')
                ->where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            return $branches->prepend('All Branches', '');
        } else {
            return User::select('id', 'name')
                ->where('id', $user->ownedId())
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        }
    });

    // Cache sessions
    $sessions = \Cache::remember("sessions_{$creatorId}", 3600, function () use ($creatorId) {
        return Session::select('id', 'year')
            ->where('created_by', $creatorId)
            ->pluck('year', 'id')
            ->prepend('Select Session', '');
    });

    // Cache fee heads
    $heads = \Cache::remember("fee_heads_{$creatorId}", 3600, function () use ($creatorId) {
        return FeeHead::where('created_by', $creatorId)
            ->get();
    });

    // Initialize collections
    $class = collect();
    $students = collect()->prepend('Select Student', '');
    $report = collect();

    // Load classes if branch selected
    if (!empty($request->branches)) {
        $class = \Cache::remember("classes_{$request->branches}", 1800, function () use ($request) {
            return Classes::select('id', 'name')
                ->where('owned_by', $request->branches)
                ->pluck('name', 'id')
                ->prepend('All Class', 'all');
        });
    }

    // Load students if class selected
    if (!empty($request->branches) && !empty($request->class)) {
        $cacheKey = "students_{$request->branches}_{$request->class}";
        $students = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId) {
            $query = StudentRegistration::select(
                \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
                'roll_no'
            )
            ->where('student_status', 'Enrolled')
            ->where('active_status', 1)
            ->whereNotNull('roll_no');

            if ($isCompany) {
                $query->where('owned_by', $request->branches);
            } else {
                $query->where('owned_by', \Auth::user()->ownedId());
            }

            if ($request->class != 'all') {
                $query->where('class_id', $request->class);
            }

            return $query->pluck('stdname', 'roll_no')->prepend('Select Student', '');
        });
    }

    // Only load report if exporting or filters applied
    if ($request->has('export') || $request->has('print') || !empty($request->branches)) {
        
        $query = Challans::select(
            'challans.id',
            'challans.owned_by',
            'challans.student_id',
            'challans.class_id',
            'challans.challanNo',
            'challans.issue_date',
            'challans.due_date',
            'challans.total_amount',
            'challans.status',
            'challans.fee_month'
        )
        ->where('fee_month', $feeMonth);

        // Apply ownership
        if ($isCompany) {
            $query->where('created_by', $creatorId);
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        // Apply filters
        if (!empty($request->branches)) {
            $query->where('owned_by', $request->branches);
        }

        if (!empty($request->class) && $request->class != 'all') {
            $query->where('class_id', $request->class);
        }

        if (!empty($request->student) && $request->student != 'all') {
            $query->where('student_id', $request->student);
        }

        // Load relationships only for export
        if ($request->has('export') || $request->has('print')) {
            $query->with([
                'student' => function($q) {
                    $q->select('id', 'roll_no', 'stdname', 'fathername');
                },
                'enrollstudent' => function($q) {
                    $q->select('id', 'regId', 'section_id', 'class_id');
                },
                'enrollstudent.section' => function($q) {
                    $q->select('id', 'name');
                },
                'class' => function($q) {
                    $q->select('id', 'name');
                }
            ]);

            // Chunk for large exports
            $report = collect();
            $query->chunk(1000, function ($challans) use (&$report) {
                foreach ($challans as $challan) {
                    if (!isset($report[$challan->owned_by])) {
                        $report[$challan->owned_by] = collect();
                    }
                    $report[$challan->owned_by]->push($challan);
                }
            });
        } else {
            // For view, limit and group
            $report = $query->limit(500)->get()->groupBy('owned_by');
        }

        // Excel export
        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
            return Excel::download(
                new MonthlyChallanreport($branches, $report, $request->branches ?? null, $heads, $report_name, $request->all()),
                'Regular_Challan_Report.xlsx'
            );
        }

        // PDF export
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
            return Excel::download(
                new MonthlyChallanreport($branches, $report, $request->branches ?? null, $heads, $report_name, $request->all()),
                'Regular_Challan_Report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }
    }

    return view('studentReports.monthlychallanreport', compact('branches', 'report', 'sessions', 'heads', 'class', 'students'));
}
        public function advancechallanreport(Request $request)
    {
        // dd($request->all()); 
        $user = \Auth::user();
        $class = collect();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', $user->creatorId())->get()->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', 'All Branches');
            $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')->where('created_by', \Auth::user()->creatorId());
            $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
                ->where('student_status', 'Enrolled')
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->whereNotNull('roll_no')
                ->get()->pluck('stdname', 'roll_no')->prepend('Select Student', '');
        } else {
            $branches = User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
            $class = Classes::where('owned_by', '=', $user->ownedId())->where('active_status', 1)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'all');
            $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')->where('owned_by', \Auth::user()->ownedId());
            $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
                ->where('student_status', 'Enrolled')
                ->where('owned_by', \Auth::user()->ownedId())
                ->whereNotNull('roll_no')
                ->get()->pluck('stdname', 'roll_no')->prepend('Select Student', '');
        }
        $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        $sessions->prepend('Select Session', '');
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        $query = $query->where('challan_type', 'Regular')->where('status', 'paid');
        $selectedBranchId = $request->branches ?? null;
        $selectedDate = $request->date ?? date('Y-m-d');
        $selectedMonth = date('m', strtotime($selectedDate));
        $selectedYear = date('Y', strtotime($selectedDate));
        $previousMonth = date('m', strtotime('-1 month', strtotime($selectedDate)));
        $previousYear = date('Y', strtotime('-1 month', strtotime($selectedDate)));

        $report = [];

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->where('active_status', 1)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'all');
            $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
                ->where('student_status', 'Enrolled')
                ->where('owned_by', $request->branches)
                ->where('class_id', '=', $request->class)
                ->whereNotNull('roll_no')
                ->get()->pluck('stdname', 'rollno')->prepend('Select Student', '');
        }

        if (!empty($request->class)) {
            if ($request->class != 'all') {
                $query->where('class_id', '=', $request->class);
                $students = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
                    ->where('student_status', 'Enrolled')->where('class_id', '=', $request->class)->get()->pluck('stdname', 'roll_no');
            } else {
                $to = StudentRegistration::select(\DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'), 'roll_no')
                    ->where('student_status', 'Enrolled');
                if (!empty($request->branches)) {
                    $to->where('owned_by', $request->branches);
                }
                $students = $to->whereNotNull('roll_no')->get()->pluck('stdname', 'rollno')->prepend('Select Student', '');
            }
        }

        if (!empty($request->student)) {
            if ($request->student != 'all') {
                $query->where('student_id', '=', $request->student);
            }
        }
        if (!empty($request->date)) {
            $currentYear = date('Y', strtotime($request->date));
            $currentMonth = date('m', strtotime($request->date));
            $query->whereYear('paid_date', '>=', $selectedMonth)
                ->whereMonth('paid_date', '<', $currentMonth)
                ->whereMonth('fee_month', '>=', $currentMonth);
        } else {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $query->whereYear('paid_date', '=', $currentYear)
                ->whereMonth('paid_date', '=', $currentMonth)
                ->whereMonth('fee_month', '>=', $currentMonth);
        }
        $report = $query->get()->groupBy('owned_by');

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Advance Challan Report';
            $selectedBranchName = $selectedBranchId ? ($branches[$selectedBranchId] ?? 'All Branches') : 'All Branches';
            return Excel::download(new AdvanceChallanreport( $branches, $report, $selectedBranchId, $selectedBranchName, $heads, $report_name, $request->all()), 'Advance_Challan_Report.xlsx');
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Advance Challan Report';
            $selectedBranchName = $selectedBranchId ? ($branches[$selectedBranchId] ?? 'All Branches') : 'All Branches';
            return Excel::download(new AdvanceChallanreport( $branches, $report, $selectedBranchId, $selectedBranchName, $heads, $report_name, $request->all()), 'Advance_Challan_Report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('studentReports.advancechallanreport', compact('branches', 'report', 'sessions', 'heads', 'class', 'students'));
    }
    public function monthlyprechallanreport(Request $request)
{
    set_time_limit(0);

    $user = Auth::user();
    $creatorId = $user->type === 'company'
        ? $user->creatorId()
        : $user->ownedId();

    // 1. Branch dropdown
    $branches = DB::table('users')
        ->when(
            $user->type === 'company',
            fn($q) => $q->where('type', 'branch')->where('created_by', $creatorId),
            fn($q) => $q->where('id', $creatorId)
        )
        ->pluck('name', 'id')
        ->prepend('All Branches', '');
    
    $sections = collect();
    $class = collect();
    $students = collect(['' => 'All Students']);

    // 2. Base student query with filters
    $baseQ = StudentRegistration::query()
        ->where('student_status', 'Enrolled')
        ->where($user->type === 'company' ? 'created_by' : 'owned_by', $creatorId);

    if ($branchId = $request->input('branches')) {
        $baseQ->where('owned_by', $branchId);
        $class = Classes::where('owned_by', $branchId)
            ->pluck('name', 'id')
            ->prepend('All Classes', 'all');
        
        // Get sections for selected branch
        if ($classId = $request->input('class')) {
            if ($classId !== 'all') {
                $sections = Section::where('class_id', $classId)
                    ->pluck('name', 'id')
                    ->prepend('All Sections', 'all');
            }
        }
    }

    if ($classId = $request->input('class')) {
        if ($classId !== 'all') {
            $baseQ->where('class_id', $classId);
        }
    }
    
    if ($sectionId = $request->input('section')) {
        if ($sectionId !== 'all') {
            $baseQ->where('section_id', $sectionId);
        }
    }
    
    if ($studentRoll = $request->input('student')) {
        if ($studentRoll !== 'all') {
            $baseQ->where('roll_no', $studentRoll);
        }
    }

    // Rebuild student dropdown
    $students = (clone $baseQ)
        ->whereNotNull('roll_no')
        ->select(DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS name'), 'roll_no')
        ->pluck('name', 'roll_no')
        ->prepend('Select Student', '');

    // 3. Date handling
    $dateInput = $request->input('date', Carbon::now()->format('Y-m'));
    $currentMonth = Carbon::createFromFormat('Y-m', $dateInput);
    $monthLabel = $currentMonth->format('M-Y');

    $reportGroups = collect();
    $heads = collect();

    if ($request->filled('date')) {
        // 4. Fetch students with relations
        $studentsList = $baseQ
            ->with(['registeroption', 'class', 'section'])
            ->get();

        $studentIds = $studentsList->pluck('id')->all();

        // 5. Fetch all fee structures (only checked ones)
        $feeStructures = StudentFeeStructure::with('feehead')
            ->whereIn('reg_id', $studentIds)
            ->where('checked_status', 1)
            ->get()
            ->groupBy('reg_id');

        // 6. Calculate unpaid arrears (previous months only)
        $arrearsData = Challans::select('student_id', DB::raw('SUM(total_amount - paid_amount - concession_amount) AS arrears'))
            ->whereIn('student_id', $studentIds)
            ->where('status', '!=', 'Paid')
            ->where('fee_month', '<', $currentMonth)
            ->groupBy('student_id')
            ->pluck('arrears', 'student_id');

        // 7. Get active approved concessions
        $concessions = Concession::with('concession.policy_head')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'Approved')
            ->where('active_status', '1')
            ->where(function ($q) use ($currentMonth) {
                $q->where('end_date', '>=', $currentMonth->toDateString())
                    ->orWhereNull('end_date');
            })
            ->orderBy('student_id')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');

        // 8. Get all concession policy heads for quick lookup
        $policyHeads = ConcessionPolicyHead::whereIn(
            'concession_id',
            $concessions->pluck('concession_id')->unique()->all()
        )
            ->get()
            ->groupBy('concession_id')
            ->map(function ($rows) {
                return $rows->keyBy('head_id');
            });

        // 9. Get all fee heads
        $heads = FeeHead::where('created_by', $creatorId)
            ->orderBy('id')
            ->get();
        // dd($policyHeads, $concessions, $heads);
        // 10. Build report data grouped by branch
        $reportGroups = $studentsList
            ->groupBy('owned_by')
            ->map(function ($groupOfStudents) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads) {
                return $groupOfStudents->map(function ($student) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads) {
                    
                    // Get student's fee structures
                    $studentFeeStructures = $feeStructures->get($student->id, collect());
                    
                    // Get student's concession
                    $concession = $concessions->get($student->id);
                    
                    // Get policy heads for this concession
                    $concessionPolicyHeads = $concession && isset($policyHeads[$concession->concession_id])
                        ? $policyHeads[$concession->concession_id]
                        : collect();

                    // Get arrears
                    $arrears = $arrearsData->get($student->id, 0);

                    // Initialize totals
                    $totalAmount = 0;
                    $totalDiscount = 0;
                    $totalNet = 0;
                    $headDetails = [];

                    // Process each fee head
                    foreach ($heads as $head) {
                        // Find fee structure for this head
                        $feeStructure = $studentFeeStructures->where('branch_id', $student->owned_by)->firstWhere('head_id', $head->id);
                        
                        if ($feeStructure) {
                            // dd($feeStructure);
                            // Get the base amount from fee structure
                            $amount = (float) $feeStructure->amount;
                            
                            // Calculate discount
                            $discountPercentage = 0;
                            $discountAmount = 0;
                            
                            if ($amount > 0) {
                                // Check if concession policy applies to this head
                                if ($concessionPolicyHeads->has($head->id)) {
                                    // Use concession policy percentage
                                    $discountPercentage = (float) $concessionPolicyHeads->get($head->id)->percentage;
                                } else {
                                    // Use individual fee structure discount
                                    $discountPercentage = (float) $feeStructure->discount;
                                }
                                
                                // Calculate discount amount
                                $discountAmount = round(($amount * $discountPercentage) / 100);
                            }
                            
                            // Calculate net amount
                            $netAmount = $amount - $discountAmount;
                            
                            // Add to totals
                            $totalAmount += $amount;
                            $totalDiscount += $discountAmount;
                            $totalNet += $netAmount;
                            
                            // Store head details
                            $headDetails[$head->id] = [
                                'head_name' => $head->fee_head,
                                'amount' => $amount,
                                'discount_pct' => $discountPercentage,
                                'discount_amount' => $discountAmount,
                                'net_amount' => $netAmount,
                            ];
                        } else {
                            // Head not in fee structure or unchecked
                            $headDetails[$head->id] = [
                                'head_name' => $head->fee_head,
                                'amount' => 0,
                                'discount_pct' => 0,
                                'discount_amount' => 0,
                                'net_amount' => 0,
                            ];
                        }
                    }

                    // Calculate net receivable (current month net + arrears)
                    $netReceivable = $totalNet + $arrears;

                    return [
                        'student_id' => $student->id,
                        'roll_no' => $student->roll_no,
                        'student_name' => $student->stdname,
                        'father_name' => $student->fathername,
                        'registration_type' => $student->registeroption->name ?? 'N/A',
                        'class_name' => $student->class->name ?? 'N/A',
                        'section_name' => $student->section->name ?? 'N/A',
                        'concession_category' => $concession && $concession->concession 
                            ? $concession->concession->title 
                            : 'No Concession',
                        'head_details' => $headDetails,
                        'total_amount' => $totalAmount,
                        'total_discount' => $totalDiscount,
                        'total_net' => $totalNet,
                        'arrears' => $arrears,
                        'net_receivable' => $netReceivable,
                    ];
                })->values();
            });
    }

    // 11. Export or view
    if (in_array($request->export, ['excel', 'pdf'])) {
        $filename = "Student_Pre_Challan_Report_{$monthLabel}."
            . ($request->export === 'excel' ? 'xlsx' : 'pdf');

        return Excel::download(
            new MonthlyPreChallanreport(
                $branches,
                $class,
                $students,
                $reportGroups,
                $heads,
                $monthLabel,
                $dateInput,
                $request->all()
            ),
            $filename,
            $request->export == 'pdf'
                ? \Maatwebsite\Excel\Excel::MPDF
                : null
        );
    }

    return view('studentReports.monthlyprechallanreport', [
        'branches' => $branches,
        'class' => $class,
        'sections' => $sections,
        'students' => $students,
        'report' => $reportGroups,
        'heads' => $heads,
        'month' => $monthLabel,
        'selectedBranchId' => $request->branches,
        'selectedClassId' => $request->class,
        'selectedSectionId' => $request->section,
        'selectedStudent' => $request->student,
    ]);
}
}
