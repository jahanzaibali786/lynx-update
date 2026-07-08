<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyStatistics;
use App\Exports\AdmissionWithdrawalReport;
use App\Exports\PeriodWiseStatisticExport;
use App\Exports\AdmissionListingExport;
use App\Exports\StudentSectionsStatisticsExport;
use App\Exports\StudentAccountStatementExport;
use App\Exports\studenttransferinReport;
use App\Exports\StudentFeeDetailExport;
use App\Exports\AdvanceChallanreport;
use App\Exports\StudentWithdrawlListingExport;
use App\Exports\StudentSTSReportExport;
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
use App\Exports\StudentFeeRevisionReportExport;
use App\Exports\StudentPromotionReportExport;
use App\Models\StudentFeeRevisionBatch;
use App\Models\Section;
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
use App\Exports\StudentProfileReportExport;
use App\Models\StudentSecurity;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StudentAccountPreviousDataFile;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $query = Classes::with([
            'classhead' => function ($query) {
                $query->with('feeHead'); // Eager load feeHead relationship
            }
        ]);

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
                'classwise_fee_structure_report.' . $extension,
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
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('active_status', 1)->where('created_by', $userCreatorId)->groupBy('owned_by', 'section_id');
        } else {
            $query = StudentEnrollments::with('branch', 'class', 'section')->select('owned_by', 'class_id', 'section_id', DB::raw('COUNT(*) as student_count'))->where('active_status', 1)->where('owned_by', $userOwnedId)->groupBy('owned_by', 'section_id');
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
                'id',
                'reg_no',
                'regdate',
                'stdname',
                'fathername',
                'session_id',
                'class_id',
                'dob',
                'gender',
                'fatherphone',
                'roll_no',
                'register_option',
                'registrationfee',
                'owned_by',
                'created_by',
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
                new StudentRegistrationExport($studentData, $branches, $branchName, $report_name, $request->all(), $branchTotals, $grandTotal),
                'student_registration_report.xlsx'
            );
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Student Registration Report';
            $branchName = $branches[$request->branch] ?? 'All Branches';
            return Excel::download(
                new StudentRegistrationExport($studentData, $branches, $branchName, $report_name, $request->all(), $branchTotals, $grandTotal),
                'student_registration_report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }

        return view('studentReports.registrationdetailreport', compact(
            'studentData',
            'branches',
            'classes',
            'status',
            'branchTotals',
            'grandTotal',
            'request',
            'registerOption'
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

    $user = \Auth::user();
    $userType = $user->type;
    $userCreatorId = $user->creatorId();
    $userOwnedId = $user->ownedId();

    if ($userType == 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', $userCreatorId)
            ->pluck('name', 'id');

        $branches->prepend($user->name, $user->id);
        $branches->prepend('All Branches', 'all');

        $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
            ->where('active_status', 1)
            ->where('created_by', $userCreatorId);

        $classes = Classes::where('created_by', $userCreatorId)
            ->where('active_status', 1)
            ->pluck('name', 'id');

        $classes->prepend('All Classes', 'all');
    } else {
        $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
        $branches->prepend('All Branches', 'all');

        $query = StudentEnrollments::with(['class', 'branch', 'StudentRegistration'])
            ->where('active_status', 1)
            ->where('owned_by', $userOwnedId);

        $classes = Classes::where('owned_by', $userOwnedId)
            ->where('active_status', 1)
            ->pluck('name', 'id');

        $classes->prepend('All Classes', 'all');
    }

    $classIds = $classes->keys()->filter(fn ($id) => $id != 'all')->values();

    $sections = DB::table('class_sections')
        ->join('sections', 'class_sections.section_id', '=', 'sections.id')
        ->whereIn('class_sections.class_id', $classIds)
        ->select('sections.id', 'sections.name')
        ->distinct()
        ->pluck('sections.name', 'sections.id');

    $sections->prepend('All Sections', 'all');

    if ($request->filled('branch') && $request->branch != 'all') {
        $query->where('owned_by', $request->branch);
    }

    if ($request->filled('class') && $request->class != 'all') {
        $query->where('class_id', $request->class);
    }

    if ($request->filled('student') && $request->student != 'all') {
        $query->where('regId', $request->student);
    }

    if ($request->filled('sections') && $request->sections != 'all') {
        $query->where('section_id', $request->sections);
    }

    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');

    if (empty($dateFrom) && empty($dateTo)) {
        $currentYear = date('Y');
        $currentMonth = date('m');

        $dateFrom = ($currentMonth >= 7)
            ? "$currentYear-07-01"
            : date('Y-07-01', strtotime('-1 year'));

        $dateTo = ($currentMonth >= 7)
            ? date('Y-06-30', strtotime('+1 year'))
            : "$currentYear-06-30";

        $request->merge([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    if (!empty($dateFrom) && !empty($dateTo)) {
        $query->whereBetween('adm_date', [$dateFrom, $dateTo]);
    }

    $studentsCollection = $query->get();
    $studentData = $studentsCollection->groupBy('owned_by');

    $regIds = $studentsCollection
        ->pluck('regId')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    $challans = Challans::whereIn('student_id', $regIds)
        ->whereRaw('LOWER(challan_type) LIKE ?', ['%admission%'])
        ->with(['heads.feehead'])
        ->get()
        ->keyBy('student_id');

    $heads = $challans
        ->flatMap(fn ($challan) => $challan->heads)
        ->filter(fn ($head) => !empty($head->feehead))
        ->map(fn ($head) => (object) [
            'id' => $head->head_id,
            'fee_head' => $head->feehead->fee_head,
        ])
        ->unique('id')
        ->values();

    $branchTotals = [];
    $grandTotal = 0;

    $branchHeadTotals = [];
    $grandHeadTotals = [];

    $studentChallanData = [];

    foreach ($studentData as $branchId => $students) {
        $branchTotal = 0;
        $branchHeadTotals[$branchId] = [];

        foreach ($students as $student) {
            $studentKey = $student->regId;
            $studentTotal = 0;
            $challanHeads = [];

            $challan = $challans[$studentKey] ?? null;

            if ($challan) {
                foreach ($challan->heads as $head) {
                    $amount = (float) ($head->price ?? 0);
                    $studentTotal += $amount;

                    $challanHeads[$head->head_id] = [
                        'name' => $head->feehead->fee_head ?? '',
                        'amount' => $amount,
                        'head_id' => $head->head_id,
                    ];

                    $branchHeadTotals[$branchId][$head->head_id] =
                        ($branchHeadTotals[$branchId][$head->head_id] ?? 0) + $amount;

                    $grandHeadTotals[$head->head_id] =
                        ($grandHeadTotals[$head->head_id] ?? 0) + $amount;
                }

                $studentChallanData[$studentKey] = [
                    'challan_no' => $challan->challanNo,
                    'challan_id' => $challan->id,
                    'heads' => $challanHeads,
                    'total' => $studentTotal,
                ];
            } else {
                $studentChallanData[$studentKey] = [
                    'challan_no' => '',
                    'challan_id' => '',
                    'heads' => [],
                    'total' => 0,
                ];
            }

            $student->total_amount = $studentTotal;
            $branchTotal += $studentTotal;
        }

        $branchTotals[$branchId] = $branchTotal;
        $grandTotal += $branchTotal;
    }

    $student = [];

    if ($request->has('export') && $request->export == 'excel') {
        $report_name = 'Admission Listing Report';
        $branchName = $branches[$request->branch] ?? 'All Branches';

        return Excel::download(
            new AdmissionListingExport($request, $branchName, $report_name, $branches, $request->all()),
            'admission_listing_report.xlsx'
        );
    }

    if ($request->has('export') && $request->export == 'pdf') {
        $report_name = 'Admission Listing Report';
        $branchName = $branches[$request->branch] ?? 'All Branches';

        return Excel::download(
            new AdmissionListingExport($request, $branchName, $report_name, $branches, $request->all()),
            'admission_listing_report.pdf',
            \Maatwebsite\Excel\Excel::MPDF
        );
    }

    if ($request->has('print') && $request->print == 'pdf') {
        $report_name = 'Admission Listing';

        $html = view('studentReports.admissiondetailreport_pdf', compact(
            'studentData',
            'student',
            'branches',
            'heads',
            'classes',
            'sections',
            'branchTotals',
            'grandTotal',
            'branchHeadTotals',
            'grandHeadTotals',
            'request',
            'studentChallanData'
        ))->render();

        $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'))->render();
        $footerHtml = view('students.concession.report.pdf.footer')->render();

        $html = '<html>
            <head>
                <style>
                    @page {
                        margin-top: 100px;
                        margin-bottom: 100px;
                    }

                    .footer {
                        position: fixed;
                        bottom: -60px;
                        height: 50px;
                        left: 0;
                        right: 0;
                    }
                </style>
            </head>
            <body>
                ' . $headerHtml . '
                <div class="footer">' . $footerHtml . '</div>
                ' . $html . '
            </body>
        </html>';

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        return $dompdf->stream('Admission-Listing.pdf', ['Attachment' => false]);
    }

    return view('studentReports.admissiondetailreport', compact(
        'studentData',
        'student',
        'branches',
        'heads',
        'classes',
        'sections',
        'branchTotals',
        'grandTotal',
        'branchHeadTotals',
        'grandHeadTotals',
        'request',
        'studentChallanData'
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
            $branches->prepend('All Branches', 'all');
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
                        ->orWhere('types', 'Challan Payment');
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

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $fromDate = $request->from_date ?? date('Y-m-d');
            $toDate = $request->to_date ?? $fromDate;
            $query->whereBetween('recipt_date', [$fromDate, $toDate]);
        }
        if ($request->filled('default_bank') && $request->default_bank !== 'allbank') {
            $query->where('bank_id', $request->default_bank);
        }
        if ($request->filled('branches') && $request->branches !== 'all') {
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
        ini_set('memory_limit', '512M');
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
                'voucher' => function ($q) {
                    $q->where('credit', '!=', 0)
                        ->orWhere('types', 'Challan Payment');
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
                'voucher' => function ($q) {
                    $q->where('credit', '!=', 0)
                        ->orWhere('types', 'Challan Payment');
                },
                'voucher.heads',
                'challan.heads.feeHead',
                'challan.student',
                'challan.enrollstudent',
                'challan.class'
            ])->where('owned_by', \Auth::user()->ownedId());
        }
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
         *  REQUEST FILTERS - SAME AS MAIN FUNCTION
         *  ========================= */
        $dateFrom = $request->from_date;
        $dateTo = $request->to_date;

        // Date filter
        $query->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('recipt_date', [$dateFrom, $dateTo]);
        });

        // Bank filter
        $query->when(
            $request->default_bank && $request->default_bank !== 'allbank',
            fn($q) => $q->where('bank_id', $request->default_bank)
        );

         // Branch filter
        // $query->when(
        //     $request->branches,
        //     fn($q) => $q->where('owned_by', $request->branches)
        // );
		if ($request->filled('branches') && $request->branches !== 'all') {
            $query->where('owned_by', $request->branches);
        }

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
    // public function student_defaulter(Request $request)
    // {
    //     $reportData = [];
    //     $class = [];
    //     $user = \Auth::user();
    //     $filtersApplied = false;

    //     if ($user->type == 'company') {
    //         // Branches for dropdown
    //         $branches = User::where('type', '=', 'branch')
    //             ->where('created_by', $user->creatorId())
    //             ->where('is_active', 1)
    //             ->get()
    //             ->pluck('name', 'id');
    //         $branches->prepend($user->name, $user->id);

    //         // Selected branches for frontend (autoselect in dropdown)
    //         $selected_branches = User::where('type', '=', 'branch')
    //             ->where('created_by', $user->creatorId())
    //             ->where('is_active', 1)
    //             ->get()
    //             ->pluck('name', 'id');
    //         $selected_branches->prepend($user->name, $user->id);

    //         $branches->prepend('All Branches', '');

    //         // Determine which branches to process in the REPORT
    //         if (!empty($request->branches) && $request->branches != '') {
    //             // Specific branch selected - process only that branch
    //             $branchesToProcess = User::where('id', '=', $request->branches)
    //                 ->where('is_active', 1)
    //                 ->get()
    //                 ->pluck('name', 'id');
    //             $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
    //             $filtersApplied = true;
    //         } else {
    //             // No specific branch selected - process ALL branches
    //             $branchesToProcess = User::where('type', '=', 'branch')
    //                 ->where('created_by', $user->creatorId())
    //                 ->where('is_active', 1)
    //                 ->get()
    //                 ->pluck('name', 'id');
    //             // Also include company's own branch
    //             $branchesToProcess->prepend($user->name, $user->id);
    //         }

    //     } else {
    //         // Branch user
    //         $branches = User::where('id', '=', $user->ownedId())
    //             ->where('is_active', 1)
    //             ->get()
    //             ->pluck('name', 'id');
    //         $selected_branches = User::where('id', '=', $user->ownedId())
    //             ->where('is_active', 1)
    //             ->get()
    //             ->pluck('name', 'id');
    //         $branches->prepend('All Branches', '');

    //         // For branch user, only their own branch
    //         $branchesToProcess = $selected_branches;
    //     }

    //     // Calculate date range and months array ONCE (outside the loop)
    //     if (empty($request->date_from) || empty($request->date_to)) {
    //         $currentYear = date('Y');
    //         $currentMonth = date('m');
    //         $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
    //         $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

    //         $request->merge(['date_from' => $dateFrom]);
    //         $request->merge(['date_to' => $dateTo]);
    //     } else {
    //         $dateFrom = $request->date_from;
    //         $dateTo = $request->date_to;
    //     }

    //     // Generate months array ONCE
    //     $start = strtotime($dateFrom);
    //     $end = strtotime($dateTo);
    //     $monthsArray = [];

    //     while ($start <= $end) {
    //         $monthsArray[] = date('m-Y', $start);
    //         $start = strtotime("+1 month", $start);
    //     }

    //     // Loop through branches to process for the REPORT
    //     foreach ($branchesToProcess as $branchId => $branchName) {
    //         // dd($branchesToProcess);
    //         // Create a fresh query for EACH branch
    //         if ($user->type == 'company') {
    //             // For company, use created_by to get all data under company
    //             $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
    //                 ->where('created_by', $user->id)
    //                 ->whereNotIn('challan_type', ['Registration'])
    //                 ->where('status', '!=', 'Paid')
    //                 ->whereHas('student', function ($q) {
    //                     $q->where('active_status', 1);
    //                 });
    //         } else {
    //             // For branch users
    //             $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
    //                 ->where('owned_by', $user->id)
    //                 ->whereNotIn('challan_type', ['Registration'])
    //                 ->where('status', '!=', 'Paid')
    //                 ->whereHas('student', function ($q) {
    //                     $q->where('active_status', 1);
    //                 });
    //         }

    //         // Filter by specific branch - this ensures each branch gets only its students
    //         $query->whereHas('student', function ($q) use ($branchId) {
    //             $q->where('owned_by', $branchId);
    //         });
    //         // Apply date filters
    //         $query->whereBetween('due_date', [$dateFrom, $dateTo]);

    //         // Apply class filter if provided
    //         if (!empty($request->class) && $request->class != 'all') {
    //             $query->where('class_id', '=', $request->class);
    //         }

    //         // Get challans for THIS branch only
    //         $challans = $query->orderBy('student_id')->orderBy('fee_month')->get()->groupBy('student_id');

    //         // Only add to report if this branch has challans
    //         if ($challans->count() > 0) {
    //             $reportData[] = [
    //                 'branch' => $branchName,
    //                 'challans' => $challans,
    //             ];
    //         }
    //     }

    //     $report_name = 'Student Defaulter Report';

    //     if ($request->has('export') && $request->export == 'excel') {
    //         return Excel::download(new Student_defaulterReport($branches, $monthsArray, $reportData, $report_name, $request, $request->all()), 'student_defaulter_report.xlsx');
    //     }

    //     if ($request->has('print') && $request->print == 'pdf') {
    //         // Increase memory and execution time for large PDFs
    //         ini_set('memory_limit', '512M');
    //         set_time_limit(120);

    //         $pdf = new Dompdf();
    //         $html = view('studentReports.student_defaulter_pdf', compact('branches', 'class', 'monthsArray', 'reportData', 'report_name'))->render();
    //         $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'));
    //         $footerHtml = view('students.concession.report.pdf.footer')->render();
    //         $html = '<html><head>
    //     <style>
    //         @page {
    //             margin-top: 100px;
    //             margin-bottom: 100px;
    //         }
    //         body { font-size: 10px; }
    //         .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
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
    //         $dompdf->setPaper('A4', 'landscape');
    //         $dompdf->render();
    //         return $dompdf->stream('student_defaulter.pdf', ['Attachment' => false]);
    //     }

    //     return view('studentReports.student_defaulter', compact('branches', 'class', 'monthsArray', 'reportData', 'report_name'));
    // }

  public function student_defaulter(Request $request)
    {
        $reportData = [];
        $class = [];
        $user = \Auth::user();
        $filtersApplied = false;

        if ($user->type == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $selected_branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $selected_branches->prepend($user->name, $user->id);
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', '');

            if (!empty($request->branches)) {
                $branchesToProcess = User::where('id', $request->branches)
                    ->where('is_active', 1)
                    ->pluck('name', 'id');

                $class = Classes::where('owned_by', $request->branches)
                    ->pluck('name', 'id');

                $class->prepend('All Classes', 'all');
                $filtersApplied = true;
            } else {
                $branchesToProcess = User::where('type', 'branch')
                    ->where('created_by', $user->creatorId())
                    ->where('is_active', 1)
                    ->pluck('name', 'id');

                $branchesToProcess->prepend($user->name, $user->id);
            }
        } else {
            $branches = User::where('id', $user->ownedId())
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $selected_branches = $branches;
            $branches->prepend('All Branches', '');

            $branchesToProcess = $selected_branches;

            if (!empty($request->branches) && $request->branches != '') {
                $class = Classes::where('owned_by', $request->branches)
                    ->pluck('name', 'id');

                $class->prepend('All Classes', 'all');
            }
        }

        // Date range
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');

            $dateFrom = ($currentMonth >= 7)
                ? "$currentYear-07-01"
                : date('Y-07-01', strtotime('-1 year'));

            $dateTo = ($currentMonth >= 7)
                ? date('Y-06-30', strtotime('+1 year'))
                : "$currentYear-06-30";

            $request->merge([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        } else {
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;
        }

        // Months array
        $start = strtotime($dateFrom);
        $end = strtotime($dateTo);
        $monthsArray = [];

        while ($start <= $end) {
            $monthsArray[] = date('m-Y', $start);
            $start = strtotime("+1 month", $start);
        }

        // Loop branches
        foreach ($branchesToProcess as $branchId => $branchName) {

            if ($user->type == 'company') {
                $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
                    ->where('created_by', $user->id);
            } else {
                $query = Challans::with('student', 'enrollstudent', 'class', 'enrollstudent.section')
                    ->where('owned_by', $user->id);
            }

            $query
                // Admission challans will not be included in this report
                ->whereNotIn('challans.challan_type', [
                    'Registration',
                    // 'Admission',
                    'Withdrawal',
                ])

                // Only unpaid / issued challans
                ->where('challans.status', '!=', 'Paid')

                // Student must be active and must have roll no generated
                // This checks student_registrations table through challans.student_id = student_registrations.id
                ->whereHas('student', function ($q) use ($branchId) {
                    $q->where('active_status', 1)
                        ->where('owned_by', $branchId)
                        ->whereNotNull('roll_no')
                        ->where('roll_no', '!=', '');
                })

                // Date filter
                ->whereBetween('challans.due_date', [$dateFrom, $dateTo]);

            // Class filter
            if (!empty($request->class) && $request->class != 'all') {
                $query->where('challans.class_id', $request->class);
            }

            $challans = $query
                ->orderBy('challans.student_id')
                ->orderBy('challans.fee_month')
                ->get()
                ->groupBy('student_id');

            // Remove student groups where no challan has an outstanding balance
            $challans = $challans->filter(function ($studentChallans) {
                return $studentChallans->contains(function ($challan) {
                    return ($challan->total_amount - ($challan->paid_amount + $challan->concession_amount)) > 0;
                });
            });

            if ($challans->count() > 0) {
                $reportData[] = [
                    'branch' => $branchName,
                    'challans' => $challans,
                ];
            }
        }

        $report_name = 'Student Defaulter Report';

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(
                new Student_defaulterReport(
                    $branches,
                    $monthsArray,
                    $reportData,
                    $report_name,
                    $request,
                    $request->all()
                ),
                'student_defaulter_report.xlsx'
            );
        }

        if ($request->has('print') && $request->print == 'pdf') {
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $html = view('studentReports.student_defaulter_pdf', compact(
                'branches',
                'class',
                'monthsArray',
                'reportData',
                'report_name'
            ))->render();

            $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name'))->render();
            $footerHtml = view('students.concession.report.pdf.footer')->render();

            $html = '<html><head>
            <style>
                @page { margin-top: 100px; margin-bottom: 100px; }
                body { font-size: 10px; }
                .header { position: fixed; top: -60px; left: 0; right: 0; height: 100px; text-align: center; }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0; right:0; }
            </style>
            </head><body>'
                . $headerHtml .
                '<div class="footer">' . $footerHtml . '</div>'
                . $html .
                '</body></html>';

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return $dompdf->stream('student_defaulter.pdf', ['Attachment' => false]);
        }

        return view('studentReports.student_defaulter', compact(
            'branches',
            'class',
            'monthsArray',
            'reportData',
            'report_name'
        ));
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

    $new_from_date = ($currentMonth >= 7)
        ? "$currentYear-07-01"
        : date('Y-07-01', strtotime('-1 year'));

    $new_to_date = ($currentMonth >= 7)
        ? date('Y-06-30', strtotime('+1 year'))
        : "$currentYear-06-30";

    $from_date = $request->input('from_date', $new_from_date);
    $to_date = $request->input('to_date', $new_to_date);

    $selected_branch = $request->input('branches', '');
    $selected_class = $request->input('class', '');
    $selected_status = $request->input('status', '');
    $selected_student = $request->input('student', '');

    if (\Auth::user()->type == 'company') {

        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id')
            ->prepend(\Auth::user()->name, \Auth::user()->id)
            ->prepend('Select Branch', '');

        $studentQuery = StudentRegistration::select(
            \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
            'roll_no'
        )
            ->where('created_by', \Auth::user()->creatorId())
            ->whereNotNull('roll_no');

        // Default only enrolled students
        if (!empty($selected_status)) {
            $studentQuery->where('student_status', $selected_status);
        } else {
            $studentQuery->where('student_status', 'Enrolled');
        }

        $students = $studentQuery
            ->get()
            ->pluck('stdname', 'roll_no')
            ->prepend('Select Student', '');

        // Keep selected student available even if it doesn't match current filter
        if (!empty($selected_student)) {
            $extra = StudentRegistration::select(
                \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
                'roll_no'
            )
                ->where('roll_no', $selected_student)
                ->where('created_by', \Auth::user()->creatorId())
                ->first();

            if ($extra && !$students->has($selected_student)) {
                $students->put($extra->roll_no, $extra->stdname);
            }
        }

    } else {

        $branches = User::where('id', \Auth::user()->ownedId())
            ->get()
            ->pluck('name', 'id')
            ->prepend('Select Branch', '');

        $studentQuery = StudentRegistration::select(
            \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
            'roll_no'
        )
            ->where('owned_by', \Auth::user()->ownedId())
            ->whereNotNull('roll_no');

      	if ($selected_status == 'Withdrawn') {
			$studentQuery->whereHas('withdrawal');
		} else {
			// Default = Enrolled
			$studentQuery->whereDoesntHave('withdrawal');
		}

        $students = $studentQuery
            ->get()
            ->pluck('stdname', 'roll_no')
            ->prepend('Select Student', '');

        // Keep selected student available even if it doesn't match current filter
        if (!empty($selected_student)) {
            $extra = StudentRegistration::select(
                \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
                'roll_no'
            )
                ->where('roll_no', $selected_student)
                ->where('owned_by', \Auth::user()->ownedId())
                ->first();

            if ($extra && !$students->has($selected_student)) {
                $students->put($extra->roll_no, $extra->stdname);
            }
        }
    }

    if (!empty($selected_branch)) {
        $class = Classes::where('owned_by', $selected_branch)
            ->get()
            ->pluck('name', 'id');

        $filtersApplied = true;
    }

    if (!empty($selected_class)) {
        $student = StudentRegistration::select(
            \DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'),
            'roll_no'
        )
            ->whereNotNull('roll_no')
            ->where('class_id', $selected_class)
            ->get()
            ->pluck('stdname', 'roll_no');

        $filtersApplied = true;
    }

    $std = null;
    $accountStatement = collect();

    if (!empty($request->student)) {

        $std = StudentRegistration::with(
            'enrollment',
            'enrollment.section',
            'class'
        )->where('roll_no', $request->student)->first();

        if ($std) {

            $openingBalance = $this->calculateOpeningBalance(
                $std->id,
                $from_date
            );

            $accountStatement = $this->getAccountStatement(
                $std->id,
                $from_date,
                $to_date,
                $openingBalance
            );

            $filtersApplied = true;
        }
    }

    if (
        $request->has('export') &&
        ($request->export == 'excel' || $request->export == 'pdf')
    ) {
        if (empty($selected_student) || $selected_student == 'all') {
            return redirect()->back()->with(
                'error',
                'Please select a specific student before exporting.'
            );
        }
    }

    if ($request->has('export') && $request->export == 'excel') {

        return Excel::download(
            new StudentAccountStatementExport(
                $branches,
                $students,
                $class,
                $accountStatement,
                $from_date,
                $to_date,
                $selected_branch,
                $selected_class,
                $selected_student,
                $std
            ),
            'Student_Account_Statement_Report.xlsx'
        );
    }

    if ($request->has('export') && $request->export == 'pdf') {

        return Excel::download(
            new StudentAccountStatementExport(
                $branches,
                $students,
                $class,
                $accountStatement,
                $from_date,
                $to_date,
                $selected_branch,
                $selected_class,
                $selected_student,
                $std
            ),
            'Student_Account_Statement_Report.pdf',
            \Maatwebsite\Excel\Excel::MPDF
        );
    }

    $previousStatementBranchId = $std ? $std->owned_by : null;

    $previousStatementBranchName = $previousStatementBranchId
        ? User::where('id', $previousStatementBranchId)->value('name')
        : null;

    $previousStatementFile = $std
        ? StudentAccountPreviousDataFile::where('student_id', $std->id)
            ->where('created_by', \Auth::user()->creatorId())
            ->latest()
            ->first()
        : null;

    $viewData = compact(
        'branches',
        'students',
        'class',
        'accountStatement',
        'from_date',
        'to_date',
        'selected_branch',
        'selected_class',
        'selected_student',
        'previousStatementFile',
        'previousStatementBranchId',
        'previousStatementBranchName'
    );

    if (isset($std)) {
        $viewData['std'] = $std;
    }

    return view('studentReports.student_single_account', $viewData);
}

    public function uploadStudentAccountPreviousData(Request $request)
    {
        $user = \Auth::user();

        $request->validate([
            'student_id' => 'required|exists:student_registrations,id',
            'previous_data_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $student = StudentRegistration::query()
            ->where('id', $request->student_id)
            ->when(
                $user->type == 'company',
                fn($q) => $q->where('created_by', $user->creatorId()),
                fn($q) => $q->where('owned_by', $user->ownedId())
            )
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', __('Invalid student selected.'));
        }

        $branchId = $student->owned_by;

        $branch = User::where('id', $branchId)
            ->where('created_by', $user->creatorId())
            ->first();

        if (!$branch && (int) $branchId !== (int) $user->creatorId()) {
            return redirect()->back()->with('error', __('Invalid branch selected.'));
        }

        $activeFile = StudentAccountPreviousDataFile::where('student_id', $student->id)
            ->where('created_by', $user->creatorId())
            ->latest()
            ->first();

        if ($user->type != 'company' && $activeFile && $activeFile->finalized_at) {
            return redirect()->back()->with('error', __('Previous data sheet is finalized. You can only download it.'));
        }

        if ($activeFile && ($user->type == 'company' || !$activeFile->finalized_at)) {
            if (Storage::exists($activeFile->file_path)) {
                Storage::delete($activeFile->file_path);
            }

            $activeFile->deleted_by = $user->id;
            $activeFile->save();
            $activeFile->delete();
        }

        $file = $request->file('previous_data_file');
        $safeRollNo = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim((string) $student->roll_no));
        $safeStudentName = preg_replace('/[^A-Za-z0-9_-]+/', '_', trim((string) $student->stdname));
        $safeRollNo = $safeRollNo ?: 'student_' . $student->id;
        $safeStudentName = $safeStudentName ?: 'account_statement';
        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $storedFileName = $safeRollNo . '_' . $safeStudentName . '.' . $extension;
        $path = 'student_previous_account_statements/student_' . $student->id . '/' . $storedFileName;
        Storage::put($path, file_get_contents($file->getRealPath()));

        StudentAccountPreviousDataFile::create([
            'student_id' => $student->id,
            'branch_id' => $branchId, // optional if you still need it
            'original_name' => $storedFileName,
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $user->id,
            'created_by' => $user->creatorId(),
            'finalized_at' => $user->type == 'company' ? now() : null,
        ]);

        return redirect()->back()->with('success', __('Previous data sheet uploaded successfully.'));
    }

    public function finalizeStudentAccountPreviousData($id)
    {
        $user = \Auth::user();
        $file = StudentAccountPreviousDataFile::where('created_by', $user->creatorId())->findOrFail($id);

        if ($user->type != 'company' && (int) $file->branch_id !== (int) $user->ownedId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $file->finalized_at = now();
        $file->save();

        return redirect()->back()->with('success', __('Previous data sheet finalized successfully.'));
    }

    public function rollbackStudentAccountPreviousData($id)
    {
        $user = \Auth::user();

        if ($user->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $file = StudentAccountPreviousDataFile::where('created_by', $user->creatorId())->findOrFail($id);
        $file->finalized_at = null;
        $file->save();

        return redirect()->back()->with('success', __('Previous data sheet rolled back. Branch can upload again.'));
    }

    public function downloadStudentAccountPreviousData($id)
    {
        $user = \Auth::user();
        $file = StudentAccountPreviousDataFile::findOrFail($id);

        if ($file->created_by != $user->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($user->type != 'company' && (int) $file->branch_id !== (int) $user->ownedId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!Storage::exists($file->file_path)) {
            return redirect()->back()->with('error', __('Previous data sheet file was not found in storage.'));
        }

        return Storage::download($file->file_path, $file->original_name);
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
            ->whereHas('journalEntery.challan', function ($q) {
                $q->where('challan_type', '!=', 'Registration');
            })
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
        $challans = $challanItems->groupBy(function ($item) {
            $challan = $item->journalEntery->challan ?? null;
            $challanNo = $challan->challanNo ?? 'unknown';
            $feeMonth = $challan->fee_month ?? 'unknown';
            return $challanNo . '_' . $feeMonth; // Group by challan + month
        });
        // dd($challans);
        // Get all payment entries (to reduce receivables)
        $payments = JournalItem::where('user_id', $studentId)
            ->where('user_type', 'student')
            ->where('credit', '!=', 0)
            ->where('types', '!=', 'challan') // Exclude challan entries
            ->where('types', '!=', 'challan adjustment') // Exclude adjustment entries (fetched separately)
            ->where(function ($query) {
                $query->whereHas('journalEntery.challan', function ($q) {
                    $q->where('challan_type', '!=', 'Registration');
                })
                    ->orWhereHas('journalEntery.recipt.challan', function ($q) {
                        $q->where('challan_type', '!=', 'Registration');
                    });
            })
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
                'date' => '-',
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

            if ($challan == null || $challan->fee_month == null) {
                dd($challan, $challans, $challanItems);
            }
            $billingMonth = Carbon::parse($challan->fee_month)->format('M-Y');
            if ($challan->other_months != null && $challan->other_months != '') {
                if (!empty($challan->other_months) && $challan->other_months != null) {
                    $billingMonth = collect(explode(',', $challan->other_months))
                        ->map(fn($date) => Carbon::parse(trim($date))->format('M-Y'))
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

        // Get adjustment entries (from security adjustment)
        $adjustments = JournalItem::where('user_id', $studentId)
            ->where('user_type', 'student')
            ->where('types', 'challan adjustment')
            ->where('credit', '!=', 0)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->with([
                'user',
                'accounts',
                'heads',
                'journalEntery',
                'journalEntery.securityAdjustment',
                'journalEntery.securityAdjustment.challan',
                'journalEntery.securityAdjustment.challan.class'
            ])
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($adjustments as $transaction) {
            $debit = (float) $transaction->credit;
            $adjustment = $transaction->journalEntery->securityAdjustment ?? null;
            $challan = $adjustment ? $adjustment->challan : null;
            $headname = \App\Models\FeeHead::where('id', $transaction->head)->first();
            $chlnBillingMonth = $challan ? Carbon::parse($challan->fee_month)->format('M-Y') : '-';
            $headNameDisplay = $headname->fee_head ?? '-';

            $paymentTransactions->push([
                'date' => $transaction->created_at,
                'journal_id' => $transaction->journal_id,
                'fee_month' => $challan->fee_month ?? 'zzz',
                'type' => 'payment',
                'data' => [
                    'type' => 'payment',
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'description' => $transaction->description ?? 'Adjustment of Challan no: ' . ($challan->challanNo ?? '-'),
                    'challan_no' => $challan->challanNo ?? '-',
                    'billing_month' => $chlnBillingMonth,
                    'challan_type' => 'Security Adjustment',
                    'head_name' => $headNameDisplay,
                    'receipt_mode' => 'Adjustment',
                    'receipt_ref' => $adjustment ? 'Adj #' . $adjustment->id : '-',
                    'class' => $challan->class->name ?? '-',
                    'credit' => 0,
                    'debit' => $debit,
                    'balance' => 0,
                    'late_amount' => 0,
                    'arrears' => 0,
                    'bank_name' => '',
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
            'date' => '-',
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
        $class = collect(['all' => 'All Classes']);
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentWithdrawal::with('student', 'student.enrollment', 'student.class', 'student.branches', 'branch', 'class')
                ->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentWithdrawal::with('student', 'student.enrollment', 'student.class', 'student.branches', 'branch', 'class')
                ->where('owned_by', $userOwnedId);
        }
        // Only withdrawals where the student's enrollment actually shows withdrawn status
        $query->whereHas('student.enrollment', function ($q) {
            $q->where('active_status', 0);
        });
        if (!empty($request->branches) && $request->branches !== 'All Branches') {
            $query->where('branch_id', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'all');
        }
        if (!empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('withdraw_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('withdraw_date', '<=', $request->date_to);
        }
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
            $query->whereBetween('withdraw_date', [$dateFrom, $dateTo]);
        }
        $all_data = $query->get();
        return view('studentReports.student_withdrawl_listing', compact('all_data', 'branches', 'class', 'request'));
    }
    public function student_withdarawl_listingReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        $class = collect(['all' => 'All Classes']);
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentWithdrawal::with('student', 'student.enrollment', 'student.class', 'student.branches', 'branch', 'class')
                ->where('created_by', $userCreatorId);
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('All Branches', 'All Branches');
            $query = StudentWithdrawal::with('student', 'student.enrollment', 'student.class', 'student.branches', 'branch', 'class')
                ->where('owned_by', $userOwnedId);
        }
        // Only withdrawals where the student's enrollment actually shows withdrawn status
        $query->whereHas('student.enrollment', function ($q) {
            $q->where('active_status', 0);
        });
        if (!empty($request->branches) && $request->branches !== 'All Branches') {
            $query->where('branch_id', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $class->prepend('All Classes', 'all');
        }
        if (!empty($request->class) && $request->class != 'all') {
            $query->where('class_id', '=', $request->class);
        }
        if (!empty($request->date_from)) {
            $query->whereDate('withdraw_date', '>=', $request->date_from);
        }
        if (!empty($request->date_to)) {
            $query->whereDate('withdraw_date', '<=', $request->date_to);
        }
        if (empty($request->date_from) || empty($request->date_to)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
            $query->whereBetween('withdraw_date', [$dateFrom, $dateTo]);
        }
        $all_data = $query->get()->groupBy('owned_by'); // Group by branch

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
                'Student_Withdrawal_Listing_Report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
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
    $currentMonth = intval(date('n'));
    $currentYear  = intval(date('Y'));
    $startYear    = ($currentMonth > 6) ? $currentYear : $currentYear - 1;

    $defaultStartDate = sprintf('%04d-06-01', $startYear);
    $defaultEndDate   = date('Y-m-t');

    $dateFrom = $request->filled('start_date') ? $request->input('start_date') : $defaultStartDate;
    $dateTo   = $request->filled('end_date')   ? $request->input('end_date')   : $defaultEndDate;

    ini_set('max_execution_time', 0);

    $user      = \Auth::user();
    $isCompany = ($user->type === 'company');

    /* ---------- Bank accounts ---------- */
    $bankQuery = BankAccount::selectRaw("id, CONCAT(bank_name, ' ', holder_name) AS name");
    if ($isCompany) {
        $bankQuery->where('created_by', $user->creatorId());
    } else {
        $bankQuery->where('owned_by', $user->ownedId());
    }
    $accounts = ['allbank' => 'Select all banks'] + $bankQuery->pluck('name', 'id')->toArray();

    /* ---------- Branches ---------- */
    if ($isCompany) {
        $branches = User::where('type', 'branch')
            ->where('created_by', $user->creatorId())
            ->pluck('name', 'id')
            ->prepend($user->name, $user->id);
    } else {
        $branches = User::where('id', $user->ownedId())
            ->pluck('name', 'id');
    }
    $branches->prepend('All Branches', '');

    /* ---------- Main query ---------- */
    $query = $isCompany
        ? StudentReceipt::where('created_by', $user->creatorId())
        : StudentReceipt::where('owned_by',   $user->ownedId());

    $query->with([
        'bank',
        'voucher.heads',
        'challan' => function ($q) use ($isCompany) {
            $q->with([
                'heads' => function ($q) use ($isCompany) {
                    if (!$isCompany) {
                        $q->with('feeHead');
                    }
                },
                'student',
                'enrollstudent',
            ]);
            if (!$isCompany) {
                $q->with('class');
            }
        },
    ]);

    if ($isCompany && $request->filled('branches')) {
        $query->where('owned_by', $request->input('branches'));
    }

    if ($request->filled('default_bank') && $request->input('default_bank') !== 'allbank') {
        $query->where('bank_id', $request->input('default_bank'));
    }

    $query->whereBetween('recipt_date', [$dateFrom, $dateTo]);
    $recipts = $query->get();

    /* ---------- Selected bank label ---------- */
    $bankAccounts = ($request->filled('default_bank') && $request->input('default_bank') !== 'allbank')
        ? BankAccount::find($request->input('default_bank'))
        : null;

    /* ---------- Fee heads ---------- */
    $heads = FeeHead::where('owned_by', $user->ownedId())
        ->pluck('fee_head', 'id')
        ->prepend('Select Account', '');

    $selectedBranch = $request->input('branches');

    return view('studentReports.fee_receipt_summry', [
        'accounts'      => $accounts,
        'vouchers'      => ['all' => 'All'],
        'recipts'       => $recipts,
        'session'       => [],
        'class'         => [],
        'students'      => [],
        'branches'      => $branches,
        'bank_accounts' => $bankAccounts,
        'heads'         => $heads,
        'start_date'    => $dateFrom,
        'end_date'      => $dateTo,
        'selectedBranch'=> $selectedBranch,
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
                $q->with([
                    'heads' => function ($q) use ($isCompany) {
                        if (!$isCompany) {
                            $q->with('feeHead');
                        }
                    },
                    'student',
                    'enrollstudent'
                ]);
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
                'Student_Statics_Session_Classwise_Report.' . $extension,
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

    // public function monthlychallanreport(Request $request)
    // {
    //     // dd($request->all());
    //     set_time_limit(1000);
    //     ini_set('memory_limit', '512M');

    //     $user = \Auth::user();
    //     $creatorId = $user->creatorId();
    //     $isCompany = $user->type == 'company';
    //     $feeMonth = date('Y-m-01', strtotime($request->date ?? date('Y-m-d')));

    //     // Cache branches (1 hour)
    //     $branches = \Cache::remember("branches_{$creatorId}_{$isCompany}", 3600, function () use ($isCompany, $creatorId, $user) {
    //         if ($isCompany) {
    //             $branches = User::select('id', 'name')
    //                 ->where('type', 'branch')
    //                 ->where('created_by', $creatorId)
    //                 ->pluck('name', 'id');
    //             $branches->prepend($user->name, $user->id);
    //             return $branches->prepend('All Branches', '');
    //         } else {
    //             return User::select('id', 'name')
    //                 ->where('id', $user->ownedId())
    //                 ->pluck('name', 'id')
    //                 ->prepend('Select Branch', '');
    //         }
    //     });

    //     // Cache sessions
    //     $sessions = \Cache::remember("sessions_{$creatorId}", 3600, function () use ($creatorId) {
    //         return Session::select('id', 'year')
    //             ->where('created_by', $creatorId)
    //             ->pluck('year', 'id')
    //             ->prepend('Select Session', '');
    //     });

    //     // Cache fee heads
    //     $heads = \Cache::remember("fee_heads_{$creatorId}", 3600, function () use ($creatorId) {
    //         return FeeHead::where('created_by', $creatorId)
    //             ->get();
    //     });

    //     // Initialize collections
    //     $class = collect();
    //     $students = collect()->prepend('Select Student', '');
    //     $report = collect();

    //     // Load classes if branch selected
    //     if (!empty($request->branches)) {
    //         $class = \Cache::remember("classes_{$request->branches}", 1800, function () use ($request) {
    //             return Classes::select('id', 'name')
    //                 ->where('owned_by', $request->branches)
    //                 ->pluck('name', 'id')
    //                 ->prepend('All Class', 'all');
    //         });
    //     }

    //     // Load students if class selected
    //     if (!empty($request->branches) && !empty($request->class)) {
    //         $cacheKey = "students_{$request->branches}_{$request->class}";
    //         $students = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId) {
    //             $query = StudentRegistration::select(
    //                 \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
    //                 'roll_no'
    //             )
    //                 ->where('student_status', 'Enrolled')
    //                 ->where('active_status', 1)
    //                 ->whereNotNull('roll_no');

    //             if ($isCompany) {
    //                 $query->where('owned_by', $request->branches);
    //             } else {
    //                 $query->where('owned_by', \Auth::user()->ownedId());
    //             }

    //             if ($request->class != 'all') {
    //                 $query->where('class_id', $request->class);
    //             }

    //             return $query->pluck('stdname', 'roll_no')->prepend('Select Student', '');
    //         });
    //     }

    //     // Only load report if exporting or filters applied
    //     if ($request->has('export') || $request->has('print') || !empty($request->branches)) {

    //         $query = Challans::select(
    //             'challans.id',
    //             'challans.owned_by',
    //             'challans.student_id',
    //             'challans.class_id',
    //             'challans.challanNo',
    //             'challans.issue_date',
    //             'challans.due_date',
    //             'challans.total_amount',
    //             'challans.status',
    //             'challans.fee_month'
    //         )
    //             ->where('fee_month', $feeMonth);

    //         // Apply ownership
    //         if ($isCompany) {
    //             $query->where('created_by', $creatorId);
    //         } else {
    //             $query->where('owned_by', $user->ownedId());
    //         }

    //         // Apply filters
    //         if (!empty($request->branches)) {
    //             $query->where('owned_by', $request->branches);
    //         }

    //         if (!empty($request->class) && $request->class != 'all') {
    //             $query->where('class_id', $request->class);
    //         }

    //         if (!empty($request->student) && $request->student != 'all') {
    //             $query->where('student_id', $request->student);
    //         }

    //         // Load relationships only for export
    //         if ($request->has('export') || $request->has('print')) {
    //             $query->with([
    //                 'student' => function ($q) {
    //                     $q->select('id', 'roll_no', 'stdname', 'fathername');
    //                 },
    //                 'enrollstudent' => function ($q) {
    //                     $q->select('id', 'regId', 'section_id', 'class_id');
    //                 },
    //                 'enrollstudent.section' => function ($q) {
    //                     $q->select('id', 'name');
    //                 },
    //                 'class' => function ($q) {
    //                     $q->select('id', 'name');
    //                 }
    //             ]);

    //             // Chunk for large exports
    //             $report = collect();
    //             $query->chunk(1000, function ($challans) use (&$report) {
    //                 foreach ($challans as $challan) {
    //                     if (!isset($report[$challan->owned_by])) {
    //                         $report[$challan->owned_by] = collect();
    //                     }
    //                     $report[$challan->owned_by]->push($challan);
    //                 }
    //             });
    //         } else {
    //             // For view, limit and group
    //             $report = $query->limit(500)->get()->groupBy('owned_by');
    //         }

    //         // Excel export
    //         if ($request->has('export') && $request->export == 'excel') {
    //             $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
    //             return Excel::download(
    //                 new MonthlyChallanreport($branches, $report, $request->branches ?? null, $heads, $report_name, $request->all()),
    //                 'Regular_Challan_Report.xlsx'
    //             );
    //         }

    //         // PDF export
    //         if ($request->has('print') && $request->print == 'pdf') {
    //             $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
    //             return Excel::download(
    //                 new MonthlyChallanreport($branches, $report, $request->branches ?? null, $heads, $report_name, $request->all()),
    //                 'Regular_Challan_Report.pdf',
    //                 \Maatwebsite\Excel\Excel::MPDF
    //             );
    //         }
    //     }

    //     return view('studentReports.monthlychallanreport', compact('branches', 'report', 'sessions', 'heads', 'class', 'students'));
    // }
    
    private function applyMonthlySplit($challan)
    {
        $monthsCount = 1;

        if (!empty($challan->other_months)) {
            $monthsCount = count(explode(',', $challan->other_months));
        }

        $challan->total_amount /= $monthsCount;
        $challan->concession_amount /= $monthsCount;

        if ($challan->heads) {
            foreach ($challan->heads as $head) {
                $head->price /= $monthsCount;

                if (isset($head->concession)) {
                    $head->concession /= $monthsCount;
                }
            }
        }

        return $challan;
    }
    public function monthlychallanreport(Request $request)
{
    set_time_limit(0);
    ini_set('memory_limit', '512M');

    $user = \Auth::user();
    $creatorId = $user->creatorId();
    $isCompany = $user->type == 'company';
    $userId = $user->id; // ✅ Unique per logged-in user
    $feeMonth = date('Y-m-01', strtotime($request->date ?? date('Y-m-d')));
    $feeMonthFormatted = date('Y-m', strtotime($feeMonth));

    // ✅ Cache key now includes $userId to isolate per-user data
    $branches = \Cache::remember("branches_{$userId}_{$creatorId}_{$isCompany}", 3600, function () use ($isCompany, $creatorId, $user) {
        if ($isCompany) {
            $branchList = User::select('id', 'name')
                ->where('type', 'branch')
                ->where('created_by', $creatorId)
                ->where('is_active', 1)
                ->pluck('name', 'id');
            $branchList->prepend($user->name, $user->id);
            return $branchList->prepend('All Branches', 'all');
        } else {
            return User::select('id', 'name')
                ->where('id', $user->ownedId())
                ->where('is_active', 1)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        }
    });

    // ✅ Cache key includes $userId
    $sessions = \Cache::remember("sessions_{$userId}_{$creatorId}", 3600, function () use ($creatorId) {
        return Session::select('id', 'year')
            ->where('created_by', $creatorId)
            ->pluck('year', 'id')
            ->prepend('Select Session', '');
    });

    // ✅ Cache key includes $userId
    $heads = \Cache::remember("fee_heads_{$userId}_{$creatorId}", 3600, function () use ($creatorId) {
        return FeeHead::where('created_by', $creatorId)->get();
    });

    $class    = collect();
    $students = collect()->prepend('Select Student', '');
    $report   = collect();
    $admissionSummary = ['count' => 0, 'total' => 0];
    $advanceSummary   = ['count' => 0, 'total' => 0];

    // ✅ Cache key includes $userId and $creatorId
    if (!empty($request->branches) && $request->branches != 'all') {
        $cacheKey = "classes_{$userId}_{$creatorId}_{$request->branches}";
        $class = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId) {
            $query = Classes::select('id', 'name');
            if ($request->branches != 'all') {
                $query->where('owned_by', $request->branches);
            } elseif ($isCompany) {
                $query->whereIn('owned_by', function ($q) use ($creatorId) {
                    $q->select('id')->from('users')
                        ->where('type', 'branch')
                        ->where('created_by', $creatorId)
                        ->where('is_active', 1)
                        ->orWhere('id', $creatorId);
                });
            }
            return $query->pluck('name', 'id')->prepend('All Class', 'all');
        });
    }

    // ✅ Cache key includes $userId and $creatorId
    if (!empty($request->branches) && !empty($request->class)) {
        $cacheKey = "students_{$userId}_{$creatorId}_{$request->branches}_{$request->class}";
        $students = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId, $user) {
            $query = StudentRegistration::select(
                \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
                'roll_no'
            )
                ->where('student_status', 'Enrolled')
                ->where('active_status', 1)
                ->whereNotNull('roll_no');

            if ($request->branches != 'all') {
                $query->where('owned_by', $request->branches);
            } elseif ($isCompany) {
                $query->whereIn('owned_by', function ($q) use ($creatorId) {
                    $q->select('id')->from('users')
                        ->where('type', 'branch')
                        ->where('created_by', $creatorId)
                        ->orWhere('id', $creatorId);
                });
            } else {
                $query->where('owned_by', $user->ownedId());
            }

            if ($request->class != 'all') {
                $query->where('class_id', $request->class);
            }

            return $query->pluck('stdname', 'roll_no')->prepend('Select Student', '');
        });
    }

    if ($request->has('export') || $request->has('print') || !empty($request->branches)) {

        $applyOwnership = function ($query) use ($isCompany, $creatorId, $user, $request) {
            if ($isCompany) {
                $query->where('created_by', $creatorId);
            } else {
                $query->where('owned_by', $user->ownedId());
            }
            if (!empty($request->branches) && $request->branches != 'all') {
                $query->where('owned_by', $request->branches);
            }
            if (!empty($request->class) && $request->class != 'all') {
                $query->where('class_id', $request->class);
            }
            if (!empty($request->student) && $request->student != 'all') {
                $query->where('student_id', $request->student);
            }
            return $query;
        };

        $regularQuery = Challans::select(
            'challans.id',
            'challans.owned_by',
            'challans.student_id',
            'challans.class_id',
            'challans.concession_id',
            'challans.challanNo',
            'challans.issue_date',
            'challans.due_date',
            'challans.total_amount',
            'challans.concession_amount',
            'challans.status',
            'challans.fee_month',
            'challans.challan_type',
            'challans.other_months'
        )
            ->where('challan_type', 'regular')
            ->where(function ($q) use ($feeMonth, $feeMonthFormatted) {
                $q->where('fee_month', $feeMonth)
                    ->orWhere(function ($q2) use ($feeMonthFormatted) {
                        $q2->whereNotNull('other_months')
                            ->whereRaw("FIND_IN_SET(?, other_months)", [$feeMonthFormatted . '-01']);
                    });
            });
        $applyOwnership($regularQuery);

        $admissionQuery = Challans::select(
            'owned_by',
            \DB::raw('COUNT(*) as count'),
            \DB::raw('SUM(total_amount - concession_amount) as total')
        )
            ->where('challan_type', 'admission')
            ->where('fee_month', $feeMonth)
            ->groupBy('owned_by');
        $applyOwnership($admissionQuery);
        $admissionSummary = $admissionQuery->get()
            ->mapWithKeys(fn($row) => [
                $row->owned_by => ['count' => (int) $row->count, 'total' => (float) $row->total]
            ])->toArray();

        $fee_monthforadvance = date('Y-m', strtotime($feeMonth));

        $advanceQuery = Challans::select(
            'owned_by',
            \DB::raw('COUNT(*) as count'),
            \DB::raw('SUM(total_amount - concession_amount) as total')
        )
            ->where('challan_type', 'Advance')
            ->where(function ($q) use ($fee_monthforadvance) {
                $q->where(function ($q1) use ($fee_monthforadvance) {
                    $q1->whereNotNull('other_months')
                        ->whereRaw("FIND_IN_SET(?, other_months)", [$fee_monthforadvance . '-01']);
                })
                    ->orWhere(function ($q2) use ($fee_monthforadvance) {
                        $q2->whereNull('other_months')
                            ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') = ?", [$fee_monthforadvance]);
                    });
            })
            ->groupBy('owned_by');
        $applyOwnership($advanceQuery);

        $advanceSummary = $advanceQuery->get()
            ->mapWithKeys(fn($row) => [
                $row->owned_by => ['count' => (int) $row->count, 'total' => (float) $row->total]
            ])->toArray();

        $withRelations = [
            'student'                 => fn($q) => $q->select('id', 'roll_no', 'stdname', 'fathername'),
            'student.registeroption'  => fn($q) => $q->select('id', 'name'),
            'enrollstudent'           => fn($q) => $q->select('id', 'regId', 'class_id', 'adm_date'),
            'enrollstudent.section'   => fn($q) => $q->select('id', 'name'),
            'class'                   => fn($q) => $q->select('id', 'name'),
            'heads',
            'concession'              => fn($q) => $q->select('id', 'concession_id'),
            'concession.policy'       => fn($q) => $q->select('id', 'title'),
        ];

        if ($request->has('export') || $request->has('print')) {

            $regularQuery->with($withRelations);
            $report = collect();

            $regularQuery->chunk(1000, function ($challans) use (&$report) {
                foreach ($challans as $challan) {
                    $challan = $this->applyMonthlySplit($challan);
                    if (!isset($report[$challan->owned_by])) {
                        $report[$challan->owned_by] = collect();
                    }
                    $report[$challan->owned_by]->push($challan);
                }
            });

            $report = collect($report)
                ->sortKeys()
                ->map(function ($branchGroup) {
                    return $branchGroup->sortBy(function ($challan) {
                        return strtolower($challan->student->stdname ?? '');
                    })->values();
                });

        } else {

            $report = $regularQuery->with($withRelations)->get()
                ->map(fn($c) => $this->applyMonthlySplit($c))
                ->sort(function ($a, $b) {
                    if ($a->owned_by != $b->owned_by) {
                        return $a->owned_by <=> $b->owned_by;
                    }
                    return strcmp(
                        strtolower($a->student->stdname ?? ''),
                        strtolower($b->student->stdname ?? '')
                    );
                })
                ->groupBy('owned_by');
        }

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
            return Excel::download(
                new MonthlyChallanreport(
                    $branches, $report, $request->branches ?? null,
                    $heads, $report_name, $request->all(),
                    $admissionSummary, $advanceSummary
                ),
                'Regular_Challan_Report.xlsx'
            );
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Regular Challan Report for the Month of ' . date('M-Y', strtotime($request->date ?? date('Y-m-d')));
            return Excel::download(
                new MonthlyChallanreport(
                    $branches, $report, $request->branches ?? null,
                    $heads, $report_name, $request->all(),
                    $admissionSummary, $advanceSummary
                ),
                'Regular_Challan_Report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }
    }

    return view('studentReports.monthlychallanreport', compact(
        'branches',
        'report',
        'sessions',
        'heads',
        'class',
        'students'
    ));
}
     public function advancechallanreport(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $user = \Auth::user();
        $userId = $user->id;
        $creatorId = $user->creatorId();
        $isCompany = $user->type == 'company';

        // ---------------------------------------------------------------
        // Selected date / month helpers
        // ---------------------------------------------------------------
        $selectedDate = $request->date ?? date('Y-m-d');
        $feeMonth = date('Y-m-01', strtotime($selectedDate));   // e.g. 2026-04-01
        $feeMonthFormatted = date('Y-m', strtotime($selectedDate));   // e.g. 2026-04

        // ---------------------------------------------------------------
        // BRANCHES  (user-scoped cache)
        // ---------------------------------------------------------------
        $branches = \Cache::remember("branches_{$userId}_{$creatorId}_{$isCompany}", 3600, function () use ($isCompany, $creatorId, $user) {
            if ($isCompany) {
                $list = User::select('id', 'name')
                    ->where('type', 'branch')
                    ->where('created_by', $creatorId)
                    ->where('is_active', 1)
                    ->pluck('name', 'id');
                $list->prepend($user->name, $user->id);
                return $list->prepend('All Branches', 'all');
            }
            return User::select('id', 'name')
                ->where('id', $user->ownedId())
                ->where('is_active', 1)
                ->pluck('name', 'id')
                ->prepend('Select Branch', '');
        });

        // ---------------------------------------------------------------
        // SESSIONS  (user-scoped cache)
        // ---------------------------------------------------------------
        $sessions = \Cache::remember("sessions_{$userId}_{$creatorId}", 3600, function () use ($creatorId) {
            return Session::select('id', 'year')
                ->where('created_by', $creatorId)
                ->pluck('year', 'id')
                ->prepend('Select Session', '');
        });

        // ---------------------------------------------------------------
        // FEE HEADS  (user-scoped cache)
        // ---------------------------------------------------------------
        $heads = \Cache::remember("fee_heads_{$userId}_{$creatorId}", 3600, function () use ($creatorId) {
            return FeeHead::where('created_by', $creatorId)->get();
        });

        // ---------------------------------------------------------------
        // CLASSES  (depends on selected branch)
        // ---------------------------------------------------------------
        $class = collect();
        if (!empty($request->branches) && $request->branches != 'all') {
            $cacheKey = "classes_{$userId}_{$creatorId}_{$request->branches}";
            $class = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId) {
                $query = Classes::select('id', 'name');
                if ($request->branches != 'all') {
                    $query->where('owned_by', $request->branches);
                } elseif ($isCompany) {
                    $query->whereIn('owned_by', function ($q) use ($creatorId) {
                        $q->select('id')->from('users')
                            ->where('type', 'branch')
                            ->where('created_by', $creatorId)
                            ->where('is_active', 1)
                            ->orWhere('id', $creatorId);
                    });
                }
                return $query->pluck('name', 'id')->prepend('All Classes', 'all');
            });
        }

        // ---------------------------------------------------------------
        // STUDENTS  (depends on branch + class)
        // ---------------------------------------------------------------
        $students = collect()->prepend('Select Student', '');
        if (!empty($request->branches) && !empty($request->class)) {
            $cacheKey = "students_{$userId}_{$creatorId}_{$request->branches}_{$request->class}";
            $students = \Cache::remember($cacheKey, 1800, function () use ($request, $isCompany, $creatorId, $user) {
                $query = StudentRegistration::select(
                    \DB::raw('CONCAT(`roll_no`, " - ", `stdname`, " s/d/o ", `fathername`) AS stdname'),
                    'roll_no'
                )
                    ->where('student_status', 'Enrolled')
                    ->where('active_status', 1)
                    ->whereNotNull('roll_no');

                if ($request->branches != 'all') {
                    $query->where('owned_by', $request->branches);
                } elseif ($isCompany) {
                    $query->whereIn('owned_by', function ($q) use ($creatorId) {
                        $q->select('id')->from('users')
                            ->where('type', 'branch')
                            ->where('created_by', $creatorId)
                            ->orWhere('id', $creatorId);
                    });
                } else {
                    $query->where('owned_by', $user->ownedId());
                }

                if ($request->class != 'all') {
                    $query->where('class_id', $request->class);
                }

                return $query->pluck('stdname', 'roll_no')->prepend('Select Student', '');
            });
        }

        // ---------------------------------------------------------------
        // REPORT  (only when filters are applied)
        // ---------------------------------------------------------------
        $report = collect();
        $selectedBranchId = $request->branches ?? null;

        if ($request->has('export') || $request->has('print') || !empty($request->branches)) {

            // -----------------------------------------------------------
            // REUSABLE OWNERSHIP SCOPE
            // -----------------------------------------------------------
            $applyOwnership = function ($query) use ($isCompany, $creatorId, $user, $request) {
                if ($isCompany) {
                    $query->where('created_by', $creatorId);
                } else {
                    $query->where('owned_by', $user->ownedId());
                }
                if (!empty($request->branches) && $request->branches != 'all') {
                    $query->where('owned_by', $request->branches);
                }
                if (!empty($request->class) && $request->class != 'all') {
                    $query->where('class_id', $request->class);
                }
                if (!empty($request->student) && $request->student != 'all') {
                    $query->where('student_id', $request->student);
                }
                return $query;
            };

            // -----------------------------------------------------------
            // ADVANCE CHALLAN QUERY
            // Same logic as monthly challan report's advance summary but
            // here we fetch full challan rows for the breakdown report.
            // Matches challans where:
            //   (a) other_months contains the selected month-01, OR
            //   (b) other_months is null AND fee_month = selected month
            // -----------------------------------------------------------
            $advanceQuery = Challans::select(
                'challans.id',
                'challans.owned_by',
                'challans.student_id',
                'challans.class_id',
                'challans.concession_id',
                'challans.challanNo',
                'challans.issue_date',
                'challans.due_date',
                'challans.paid_date',
                'challans.total_amount',
                'challans.concession_amount',
                'challans.status',
                'challans.fee_month',
                'challans.challan_type',
                'challans.other_months'
            )
                ->where('challan_type', 'Advance')
                ->where(function ($q) use ($feeMonthFormatted, $feeMonth) {
                    // (a) Subscription-style: other_months contains this month
                    $q->where(function ($q1) use ($feeMonthFormatted) {
                        $q1->whereNotNull('other_months')
                            ->whereRaw("FIND_IN_SET(?, other_months)", [$feeMonthFormatted . '-01']);
                    })
                        // (b) Single-month advance: fee_month matches directly
                        ->orWhere(function ($q2) use ($feeMonthFormatted) {
                        $q2->whereNull('other_months')
                            ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') = ?", [$feeMonthFormatted]);
                    });
                });

            $applyOwnership($advanceQuery);

            // -----------------------------------------------------------
            // LOAD RELATIONSHIPS
            // -----------------------------------------------------------
            $withRelations = [
                'student' => fn($q) => $q->select('id', 'roll_no', 'stdname', 'fathername'),
                'student.registeroption' => fn($q) => $q->select('id', 'name'),
                'enrollstudent' => fn($q) => $q->select('id', 'regId', 'class_id', 'adm_date'),
                'enrollstudent.section' => fn($q) => $q->select('id', 'name'),
                'class' => fn($q) => $q->select('id', 'name'),
                'heads',
                'concession' => fn($q) => $q->select('id', 'concession_id'),
                'concession.policy' => fn($q) => $q->select('id', 'title'),
            ];

            // -----------------------------------------------------------
            // EXPORT PATH  →  chunk for memory safety
            // -----------------------------------------------------------
            if ($request->has('export') || $request->has('print')) {

                $advanceQuery->with($withRelations);
                $report = collect();

                $advanceQuery->chunk(1000, function ($challans) use (&$report) {
                    foreach ($challans as $challan) {
                        if (!isset($report[$challan->owned_by])) {
                            $report[$challan->owned_by] = collect();
                        }
                        $report[$challan->owned_by]->push($challan);
                    }
                });

                // Sort branches ASC, students alphabetically within each branch
                $report = collect($report)
                    ->sortKeys()
                    ->map(fn($group) => $group->sortBy(
                        fn($c) => strtolower($c->student->stdname ?? '')
                    )->values());

            } else {
                // -----------------------------------------------------------
                // SCREEN / VIEW PATH
                // -----------------------------------------------------------
                $report = $advanceQuery->with($withRelations)->get()
                    ->sort(function ($a, $b) {
                        if ($a->owned_by != $b->owned_by) {
                            return $a->owned_by <=> $b->owned_by;
                        }
                        return strcmp(
                            strtolower($a->student->stdname ?? ''),
                            strtolower($b->student->stdname ?? '')
                        );
                    })
                    ->groupBy('owned_by');
            }

            // -----------------------------------------------------------
            // EXCEL EXPORT
            // -----------------------------------------------------------
            if ($request->has('export') && $request->export == 'excel') {
                $report_name = 'Advance Challan Report for the Month of ' . date('M-Y', strtotime($selectedDate));
                $selectedBranchName = $selectedBranchId ? ($branches[$selectedBranchId] ?? 'All Branches') : 'All Branches';

                return Excel::download(
                    new AdvanceChallanreport(
                        $branches,
                        $report,
                        $selectedBranchId,
                        $selectedBranchName,
                        $heads,
                        $report_name,
                        $request->all()
                    ),
                    'Advance_Challan_Report.xlsx'
                );
            }

            // -----------------------------------------------------------
            // PDF EXPORT
            // -----------------------------------------------------------
            if ($request->has('print') && $request->print == 'pdf') {
                $report_name = 'Advance Challan Report for the Month of ' . date('M-Y', strtotime($selectedDate));
                $selectedBranchName = $selectedBranchId ? ($branches[$selectedBranchId] ?? 'All Branches') : 'All Branches';

                return Excel::download(
                    new AdvanceChallanreport(
                        $branches,
                        $report,
                        $selectedBranchId,
                        $selectedBranchName,
                        $heads,
                        $report_name,
                        $request->all()
                    ),
                    'Advance_Challan_Report.pdf',
                    \Maatwebsite\Excel\Excel::MPDF
                );
            }
        }

        return view('studentReports.advancechallanreport', compact(
            'branches',
            'report',
            'sessions',
            'heads',
            'class',
            'students'
        ));
    }


public function monthlyprechallanreport(Request $request)
    {
        // dd($request->all());
        set_time_limit(0);

        $user = Auth::user();
        $creatorId = $user->type == 'company'
            ? $user->creatorId()
            : $user->ownedId();

        // ─────────────────────────────────────────────
        // 1. Branch dropdown
        // ─────────────────────────────────────────────
        $branches = DB::table('users')
            ->when(
                $user->type == 'company',
                fn($q) => $q->where('type', 'branch')->where('created_by', $creatorId),
                fn($q) => $q->where('id', $creatorId)
            )
            ->where('is_active', 1)
            ->pluck('name', 'id')
            ->prepend('All Branches', 'all');

        $sections = collect();
        $class = collect(['all' => 'All Classes']);
        $students = collect(['all' => 'All Students']);

        // ─────────────────────────────────────────────
        // 2. Base student query with filters
        // ─────────────────────────────────────────────
        $baseQ = StudentRegistration::query()
            ->with(['registeroption', 'class', 'section', 'enrollment'])
            ->where('student_status', 'Enrolled')
            ->where($user->type == 'company' ? 'created_by' : 'owned_by', $creatorId);

        if (($branchId = $request->input('branches')) && $branchId !== 'all') {
            $baseQ->where('owned_by', $branchId);

            $class = Classes::where('owned_by', $branchId)
                ->pluck('name', 'id')
                ->prepend('All Classes', 'all');
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
            ->prepend('All Students', 'all');

        // ─────────────────────────────────────────────
        // 3. Date handling
        // ─────────────────────────────────────────────
        $dateInput = $request->input('date', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $dateInput);
        $monthLabel = $currentMonth->format('M-Y');

        $reportGroups = collect();
        $heads = collect();
        $totalTuitionNet = 0;
        $tuitionStudentCount = 0;
        $tuitionHead = null;

        if ($request->filled('date')) {

            $studentsList = $baseQ->with(['registeroption', 'class', 'section', 'enrollment'])->get();
            $studentIds = $studentsList->pluck('id')->all();

            // ─────────────────────────────────────────────
            // Fee structures
            // ─────────────────────────────────────────────
            $feeStructures = StudentFeeStructure::with('feehead')
                ->whereIn('reg_id', $studentIds)
                ->where('checked_status', 1)
                ->get()
                ->groupBy('reg_id');

            $currentYearMonth = $currentMonth->format('Y-m');

            // ─────────────────────────────────────────────
            // Arrears (unpaid challans before current month, from 2026-01 onwards)
            // ─────────────────────────────────────────────
            $arrearsData = Challans::
                select(
                    'student_id',
                    DB::raw('SUM(total_amount - paid_amount - concession_amount) AS arrears')
                )->
                whereIn('student_id', $studentIds)
                ->whereNotIn('challan_type', ['Registration', 'Withdrawal'])
                ->where('status', '!=', 'Paid')
                ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') < ?", [$currentYearMonth])
                ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') >= '2026-01'")
                ->groupBy('student_id')
                ->pluck('arrears', 'student_id');
            // ->get();
            // ─────────────────────────────────────────────
            // Concessions
            // ─────────────────────────────────────────────
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

            $policyHeads = ConcessionPolicyHead::whereIn(
                'concession_id',
                $concessions->pluck('concession_id')->unique()->all()
            )
                ->get()
                ->groupBy('concession_id')
                ->map(fn($rows) => $rows->keyBy('head_id'));

            // ─────────────────────────────────────────────
            // Fee heads
            // ─────────────────────────────────────────────
            $heads = FeeHead::orderBy('id')->get();

            $tuitionHead = $heads->first(
                fn($h) => stripos($h->fee_head, 'tuition') !== false
            );

            $lateFeeHead = $heads->first(
                fn($h) => stripos($h->fee_head, 'late fee') !== false
                || stripos($h->fee_head, 'late-fee') !== false
                || stripos($h->fee_head, 'latefee') !== false
            );

            // ─────────────────────────────────────────────
            // Keywords for heads that must ALWAYS be skipped
            // (admission, security, readmission, late fee)
            // ─────────────────────────────────────────────
            $alwaysSkipKeywords = [
                'admission',
                'readmission',
                'security',
                'late fee',
                'late-fee',
                'latefee',
            ];

            // Annual-charge keywords (one-time per subscription period)
            $annualKeywords = ['annual', 'yearly'];

            // ─────────────────────────────────────────────
            // Previous-month challan lookup
            // Pass 1: challan whose fee_month == last month
            // Pass 2: challan whose other_months contains last month
            // ─────────────────────────────────────────────
            $lastMonthDate = $currentMonth->copy()->subMonth()->format('Y-m-01');
            $currentMonthDate = $currentMonth->format('Y-m-01');
            $lastYearMonth = date('Y-m', strtotime($lastMonthDate));

            $prevChallansPass1 = Challans::whereIn('student_id', $studentIds)
                ->whereNotIn('challan_type', ['registration'])
                ->whereRaw("DATE_FORMAT(STR_TO_DATE(fee_month, '%Y-%m-%d'), '%Y-%m') = ?", [$lastYearMonth])
                ->get()
                ->groupBy('student_id');

            $prevChallansPass2 = Challans::whereIn('student_id', $studentIds)
                ->whereNotIn('challan_type', ['registration'])
                ->where(function ($q) use ($lastMonthDate, $currentMonthDate) {
                    $q->whereRaw('FIND_IN_SET(?, other_months)', [$lastMonthDate])
                        ->orWhereRaw('FIND_IN_SET(?, other_months)', [$currentMonthDate]);
                })
                ->get()
                ->groupBy('student_id');

            $prevChallans = $prevChallansPass1
                ->map(function ($rows, $studentId) use ($prevChallansPass2) {
                    return $rows->merge($prevChallansPass2->get($studentId, collect()))->unique('id')->values();
                })
                ->union($prevChallansPass2->diffKeys($prevChallansPass1));

            $prevChallanIds = $prevChallans->flatten()->pluck('id')->all();

            $prevChallanItems = ChallanHead::with('feehead')
                ->whereIn('challan_id', $prevChallanIds)
                ->get()
                ->groupBy('challan_id');
            // dd($prevChallans, $prevChallanItems);
            // ─────────────────────────────────────────────
            // Helper: parse other_months into sorted array of 'Y-m-d' strings
            // ─────────────────────────────────────────────
            $parseOtherMonths = function (?string $raw): array {
                if (empty($raw))
                    return [];
                return array_values(
                    array_filter(
                        array_map('trim', explode(',', $raw))
                    )
                );
            };

            // ─────────────────────────────────────────────
            // Build report
            // ─────────────────────────────────────────────
            $reportGroups = $studentsList
                ->sort(function ($a, $b) {

                    // 1. Branch order (owned_by ASC)
                    if ($a->owned_by != $b->owned_by) {
                        return $a->owned_by <=> $b->owned_by;
                    }

                    // 2. Student name alphabetical
                    return strcmp(
                        strtolower($a->stdname ?? ''),
                        strtolower($b->stdname ?? '')
                    );
                })
                ->groupBy('owned_by')
                ->map(function ($groupOfStudents) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads, $prevChallans, $prevChallanItems, $currentMonth, &$totalTuitionNet, &$tuitionStudentCount, $tuitionHead, $lateFeeHead, $alwaysSkipKeywords, $annualKeywords, $parseOtherMonths, $lastMonthDate) {
                    return $groupOfStudents->sortBy(fn($s) => strtolower($s->stdname ?? ''))->map(function ($student) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads, $prevChallans, $prevChallanItems, $currentMonth, &$totalTuitionNet, &$tuitionStudentCount, $tuitionHead, $lateFeeHead, $alwaysSkipKeywords, $annualKeywords, $parseOtherMonths, $lastMonthDate) {
                        $studentFeeStructures = $feeStructures->get($student->id, collect());
                        $concession = $concessions->get($student->id);

                        $concessionPolicyHeads = $concession && isset($policyHeads[$concession->concession_id])
                            ? $policyHeads[$concession->concession_id]
                            : collect();

                        $arrears = $arrearsData->get($student->id, 0);

                        $studentChallans = $prevChallans->get($student->id, collect());

                        // Priority 1: Regular
                        $prevChallan = $studentChallans->where('challan_type', 'Regular')->last();

                        // Priority 2: Admission (if no Regular found)
                        if (!$prevChallan) {
                            $prevChallan = $studentChallans->where('challan_type', 'Admission')->last();
                        }

                        // Priority 3: Fallback to last available (any type)
                        if (!$prevChallan) {
                            $prevChallan = $studentChallans->last();
                        }
                        // ─── Challan type label ───────────────────────────────
                        $challanTypeShort = 'RV';

                        if ($prevChallan) {
                            $months = $parseOtherMonths($prevChallan->other_months);
                            if (count($months) > 1) {
                                $challanTypeShort = 'AV';
                            }
                        }

                        // ─── Determine if this student already has an
                        //     advance/subscription challan that covers the
                        //     CURRENT month being reported (e.g. running May
                        //     report but challan covers Jan-June).
                        //
                        //     If yes → we use fee-structure data directly
                        //     (same as no prev-challan path) and only show
                        //     annual charges for the month that immediately
                        //     follows the challan's fee_month (first month
                        //     after the challan was created).
                        // ─────────────────────────────────────────────────────
                        $advanceChallanForCurrentMonth = null;  // challan that covers current month in other_months
                        $isFirstMonthAfterChallan = false; // should annual charges appear?
    
                        $currentMonthStr = $currentMonth->format('Y-m-01');
                        foreach ($studentChallans->sortByDesc('id') as $candidateChallan) {
                            $months = $parseOtherMonths($candidateChallan->other_months);
                            if (count($months) <= 1 || !in_array($currentMonthStr, $months, true)) {
                                continue;
                            }

                            $advanceChallanForCurrentMonth = $candidateChallan;
                            $sortedMonths = $months;
                            sort($sortedMonths);

                            $challanFeeMonth = Carbon::parse($candidateChallan->fee_month)->format('Y-m-01');
                            $firstSubsequent = null;

                            foreach ($sortedMonths as $m) {
                                if ($m > $challanFeeMonth) {
                                    $firstSubsequent = $m;
                                    break;
                                }
                            }

                            $isFirstMonthAfterChallan = $firstSubsequent && $firstSubsequent === $currentMonthStr;
                            break;
                        }

                        // ─── Per-head amounts ─────────────────────────────────
                        $applyJunJulFeeExemption = !empty($student->fee_exempt_jun_jul)
                            && $student->enrollment
                            && !empty($student->enrollment->adm_date)
                            && in_array((int) $currentMonth->month, [6, 7], true)
                            && (int) \Carbon\Carbon::parse($student->enrollment->adm_date)->year === (int) $currentMonth->year;

                        $totalAmount = 0;
                        $totalDiscount = 0;
                        $totalNet = 0;
                        $headDetails = [];

                        foreach ($heads as $head) {

                            $feeHead = strtolower(trim($head->fee_head));

                            // Skip Security & Admission heads completely
                            if (
                                str_contains($feeHead, 'admission') ||
                                str_contains($feeHead, 'security') ||
                                $feeHead === 'late fee' ||
                                $feeHead === 'late charges' ||
                                str_contains($feeHead, 'late fee') ||
                                str_contains($feeHead, 'late charges')
                            ) {
                                continue;
                            }


                            $isAnnualHead = false;
                            foreach ($annualKeywords as $kw) {
                                if (str_contains($feeHead, strtolower($kw))) {
                                    $isAnnualHead = true;
                                    break;
                                }
                            }

                            if ($applyJunJulFeeExemption || ($advanceChallanForCurrentMonth && $isAnnualHead && !$isFirstMonthAfterChallan)) {
                                $amount = $discount = $netAmount = 0;
                                $discountPercentage = 0;
                            } else {

                                $feeStructure = $studentFeeStructures
                                    ->where('branch_id', $student->owned_by)
                                    ->firstWhere('head_id', $head->id);

                                if ($feeStructure) {

                                    $amount = (float) $feeStructure->amount;

                                    $discountPercentage = 0;

                                    if ($amount > 0) {

                                        if ($concessionPolicyHeads->has($head->id)) {
                                            $discountPercentage = (float) $concessionPolicyHeads
                                                ->get($head->id)
                                                ->percentage;
                                        } else {
                                            $discountPercentage = (float) $feeStructure->discount;
                                        }
                                    }

                                    $discount = round(($amount * $discountPercentage) / 100);

                                    $netAmount = $amount - $discount;

                                } else {

                                    $amount = $discount = $netAmount = 0;
                                    $discountPercentage = 0;
                                }
                            }

                            $totalAmount += $amount;
                            $totalDiscount += $discount;
                            $totalNet += $netAmount;

                            if ($tuitionHead && $head->id == $tuitionHead->id) {
                                $totalTuitionNet += $netAmount;
                            }

                            $headDetails[$head->id] = [
                                'head_name' => $head->fee_head,
                                'amount' => $amount,
                                'discount_pct' => $amount > 0
                                    ? round(($discount / $amount) * 100, 2)
                                    : 0,
                                'discount_amount' => $discount,
                                'net_amount' => $netAmount,
                            ];
                        }

                        $gross = $totalAmount - $totalDiscount;
                        $netReceivable = $totalNet + $arrears;

                        // ─── Previous-month amount (for difference column) ────
                        //
                        // For advance/subscription challans (e.g. Jan–Jun):
                        //   • Annual charges count in prevMonthAmount ONLY when
                        //     the PREVIOUS month (lastMonthDate) is the very first
                        //     subsequent month after the challan's fee_month.
                        //     e.g. challan fee_month = Jan → first subsequent = Feb
                        //          running Feb report  → prevMonth is Jan  → annual included  ✓
                        //          running Mar report  → prevMonth is Feb  → annual excluded  ✗
                        //          running Apr–Jun     → prevMonth is Mar+ → annual excluded  ✗
                        //   • For regular single-month challans annual is always included.
                        // ─────────────────────────────────────────────────────────────────
                        $prevMonthAmount = 0;
                        // dd($feeStructures, $student->id, $head->id);
    
                        if ($prevChallan) {

                            $items = $prevChallanItems->get($prevChallan->id, collect());
                            $months = $parseOtherMonths($prevChallan->other_months);
                            $monthCount = max(1, count($months));

                            // ── Work out whether annual charges belong in this
                            //    prevMonthAmount calculation.
                            //
                            //    They belong only when the challan is a single-month
                            //    challan, OR when the challan is an advance challan
                            //    AND the previous month (lastMonthDate) is the first
                            //    subsequent month after the challan's fee_month.
                            // ────────────────────────────────────────────────────
                            $includeAnnualInPrev = true; // default: single-month challan
    
                            if ($monthCount > 1) {
                                // Advance challan: find the first subsequent month
                                $sortedAdvMonths = $months;
                                sort($sortedAdvMonths);
                                $challanFeeMonthStr = Carbon::parse($prevChallan->fee_month)->format('Y-m-01');
                                $firstSubsequentAdv = null;

                                foreach ($sortedAdvMonths as $m) {
                                    if ($m > $challanFeeMonthStr) {
                                        $firstSubsequentAdv = $m;
                                        break;
                                    }
                                }

                                // lastMonthDate is the "previous month" we are
                                // computing the amount for.
                                // Annual charges appear only if that previous month
                                // IS the first subsequent month.
                                $includeAnnualInPrev = ($firstSubsequentAdv !== null)
                                    && ($firstSubsequentAdv === $lastMonthDate);
                            }

                            $monthly = 0;
                            $annual = 0;

                            foreach ($items as $item) {

                                $name = strtolower($item->feehead->fee_head ?? '');

                                // Skip always-excluded heads
                                $skipItem = false;
                                foreach ($alwaysSkipKeywords as $ex) {
                                    if (str_contains($name, strtolower($ex))) {
                                        $skipItem = true;
                                        break;
                                    }
                                }
                                if ($skipItem)
                                    continue;

                                $net = (float) $item->price - (float) ($item->concession ?? 0);

                                $isItemAnnual = false;
                                foreach ($annualKeywords as $kw) {
                                    if (str_contains($name, $kw)) {
                                        $isItemAnnual = true;
                                        break;
                                    }
                                }

                                if ($isItemAnnual) {
                                    if ($includeAnnualInPrev) {
                                        $annual += $net;
                                    }
                                    // else: suppress annual for Mar–Jun reports
                                } else {
                                    $monthly += $net;
                                }
                            }

                            $prevMonthAmount = round(($monthly / $monthCount) + $annual);
                        }

                        $difference = $totalNet - $prevMonthAmount;

                        // ─── Late fee calculation ─────────────────────────────
                        // (only from prev challan status; never from fee heads
                        //  since those are always skipped above)
                        $lateFeeAmount = 0;

                        if ($prevChallan && $lateFeeHead) {

                            $challanStatus = strtolower($prevChallan->status);

                            $lateFeeItem = $prevChallanItems
                                ->get($prevChallan->id, collect())
                                ->firstWhere('head_id', $lateFeeHead->id);

                            if ($challanStatus == 'partially paid' && $lateFeeItem) {

                                $lateFeeNet = (float) $lateFeeItem->price - (float) ($lateFeeItem->concession ?? 0);
                                $lateFeePaid = (float) ($lateFeeItem->paid_amount ?? 0);

                                if ($lateFeePaid < $lateFeeNet) {
                                    $lateFeeAmount = $lateFeeNet - $lateFeePaid;
                                }

                            } elseif (in_array($challanStatus, ['issued', 'pending'])) {

                                $dueDate = Carbon::parse($prevChallan->due_date)->startOfDay();
                                $today = Carbon::now()->startOfDay();

                                // Only adjust due date if due date itself is weekend
                                if ($dueDate->isSaturday()) {
                                    $dueDate->addDays(2);
                                } elseif ($dueDate->isSunday()) {
                                    $dueDate->addDay();
                                }

                                if ($today->greaterThan($dueDate)) {

                                    // Count all calendar days after adjusted due date
                                    $lateDays = $dueDate->diffInDays($today);

                                    $lateFeeAmount = min($lateDays * 120, 1200);
                                }
                            }
                        }
                        // dd($headDetails);
    
                        $tuitionStudentCount++;

                        return [
                            'student_id' => $student->id,
                            'roll_no' => $student->roll_no,
                            'adm_date' => $student->enrollment->adm_date ?? '-',
                            'student_name' => $student->stdname,
                            'father_name' => $student->fathername,
                            'registration_type' => $student->registeroption->name ?? 'N/A',
                            'challan_type_short' => $challanTypeShort,
                            'class_name' => $student->class->name ?? 'N/A',
                            'section_name' => $student->section->name ?? 'N/A',
                            'concession_category' => $concession && $concession->concession
                                ? $concession->concession->title
                                : 'No Concession',
                            'jun_jul_fee_exempt' => $applyJunJulFeeExemption,
                            'head_details' => $headDetails,
                            'total_amount' => $totalAmount,
                            'total_discount' => $totalDiscount,
                            'total_net' => $totalNet,
                            'arrears' => $arrears,
                            'late_fee' => $lateFeeAmount,
                            'net_receivable' => $netReceivable + $lateFeeAmount,
                            'gross' => $gross,
                            'prev_month_amount' => $prevMonthAmount,
                            'difference' => $difference,
                        ];

                    })->values();
                });

            $heads = $heads->filter(function ($head) use ($reportGroups) {
                foreach ($reportGroups as $branchStudents) {
                    foreach ($branchStudents as $studentRow) {
                        if (!empty($studentRow['jun_jul_fee_exempt']) && array_key_exists($head->id, $studentRow['head_details'] ?? [])) {
                            return true;
                        }

                        $details = $studentRow['head_details'][$head->id] ?? [
                            'amount' => 0,
                            'discount_amount' => 0,
                            'net_amount' => 0,
                        ];

                        if ($details['amount'] != 0 || $details['discount_amount'] != 0 || $details['net_amount'] != 0) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        $averageTuitionFee = $tuitionStudentCount > 0
            ? round($totalTuitionNet / $tuitionStudentCount, 2)
            : 0;

        // ─────────────────────────────────────────────
        // Export or view
        // ─────────────────────────────────────────────
        if (in_array($request->export, ['excel', 'pdf'])) {
            $reportGroups = collect($reportGroups)
                ->sortKeys() // branch ASC
                ->map(function ($students) {
                    return collect($students)
                        ->sortBy(fn($s) => strtolower($s['student_name'] ?? ''))
                        ->values();
                });
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
                    $request->all(),
                    $averageTuitionFee
                ),
                $filename,
                $request->export === 'pdf'
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
	public function student_sections_statistics(Request $request)
    {
        $user = \Auth::user();
        // dd($request->all());
        // ================= BRANCHES =================
        if ($user->type == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $branches->prepend('All Branches', 'all');
        } else {
            $branches = User::where('id', $user->ownedId())
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $branches->prepend('All Branches', 'all');
        }

        // ================= FILTER =================
        $selectedBranchId = $request->input('branches');
        // dd($request->all());
        if (!empty($selectedBranchId) && $selectedBranchId !== 'all') {
            $branchIds     = [(string) $selectedBranchId];
            $displayBranch = $branches->get($selectedBranchId, 'All Branches');
        } else {
            $branchIds = $branches->keys()
                ->filter(fn($k) => (string) $k !== 'all')
                ->map(fn($k) => (string) $k)
                ->toArray();

            $displayBranch = 'All Branches';
        }

        // ================= SECTIONS =================
        $sections = Section::get()->toBase();
        $sections->push((object)['id' => 0, 'name' => 'Unassigned']);

        $report      = [];
        $grandTotals = [];

        foreach ($sections as $section) {
            $grandTotals[$section->id] = 0;
        }
        $grandTotals['total'] = 0;

        // ================= MAIN LOOP =================
        foreach ($branchIds as $branchId) {

            $branchId = (string) $branchId;

            if (!$branches->has($branchId)) continue;

            if ($user->type == 'company') {
                $branchQuery = \App\Models\StudentEnrollments::where('created_by', $user->creatorId())
                    ->where('active_status', 1)
                    ->where('owned_by', $branchId);
            } else {
                $branchQuery = \App\Models\StudentEnrollments::where('owned_by', $branchId)
                    ->where('active_status', 1);
            }

            $branchName = $branches[$branchId];
            $classes    = Classes::where('active_status', 1)
                ->where('owned_by', $branchId)
                ->get();

            $branchTotals = [];
            $branchRows   = [];

            foreach ($sections as $section) {
                $branchTotals[$section->id] = 0;
            }
            $branchTotals['total'] = 0;

            foreach ($classes as $class) {

                $studentsQuery = (clone $branchQuery)->where('class_id', $class->id);

                $sectionData = [];
                $rowTotal    = 0;

                foreach ($sections as $section) {
                    if ($section->id == 0) {
                        $count = (clone $studentsQuery)->whereNull('section_id')->count();
                    } else {
                        $count = (clone $studentsQuery)->where('section_id', $section->id)->count();
                    }

                    $sectionData[$section->id]       = $count;
                    $rowTotal                        += $count;
                    $branchTotals[$section->id]      += $count;
                    $grandTotals[$section->id]       += $count;
                }

                $branchTotals['total'] += $rowTotal;
                $grandTotals['total']  += $rowTotal;

                $branchRows[] = [
                    'class'    => $class->name,
                    'sections' => $sectionData,
                    'total'    => $rowTotal,
                ];
            }

            $report[] = [
                'branch' => $branchName,
                'rows'   => $branchRows,
                'totals' => $branchTotals,
            ];
        }

        // ================= REMOVE ZERO SECTIONS =================
        $usedSections = [];
        foreach ($report as $branchData) {
            foreach ($branchData['rows'] as $row) {
                foreach ($row['sections'] as $sectionId => $count) {
                    if ($count > 0) {
                        $usedSections[$sectionId] = true;
                    }
                }
            }
        }

        $sections = $sections->filter(fn($section) => isset($usedSections[$section->id]));

        // ================= EXCEL =================
        if ($request->export == 'excel') {
            return Excel::download(
                new StudentSectionsStatisticsExport(
                    $report,
                    $sections,
                    $branches,
                    $grandTotals,
                    $request->all()
                ),
                'student_sections_statistics.xlsx'
            );
        }
        if ($request->export == 'pdf') {

            $report_name = 'STUDENT STATISTICS';
            $branch = $displayBranch ?? 'ALL BRANCHES';

            $isAllBranches = empty($selectedBranchId) || $selectedBranchId === 'all';

            $pdf = Pdf::loadView(
                'studentReports.exports.student_sections_statistics_print',
                compact(
                    'report',
                    'report_name',
                    'branch',
                    'sections',
                    'branches',
                    'grandTotals',
                    'displayBranch',
                    'isAllBranches'
                )
            );

            if ($isAllBranches) {
                $pdf->setPaper('A4', 'landscape');
            } else {
                $pdf->setPaper('A4', 'portrait');
            }

            return $pdf->stream('Student_Statistics.pdf');
        }

        // ================= WEB VIEW =================
        return view('studentReports.student_sections_statistics', compact(
            'report',
            'sections',
            'branches',
            'grandTotals',
            'displayBranch'
        ));
    }
	public function feeRevisionReport(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();

        if ($user->type == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $ownedId)->pluck('name', 'id');
        }
        $branches->prepend('All Branches', 'all');

        $sessions = Session::where('created_by', $creatorId)
            ->pluck('year', 'id')
            ->prepend('All Sessions', 'all');

        $classes = Classes::where('created_by', $creatorId)
            ->when($request->filled('branches') && $request->branches !== 'all', function ($q) use ($request) {
                $q->where('owned_by', $request->branches);
            })
            ->pluck('name', 'id')
            ->prepend('All Classes', 'all');

        $students = StudentRegistration::where('created_by', $creatorId)
            ->where('student_status', 'Enrolled')
            ->where('active_status', 1)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($request->filled('branches') && $request->branches !== 'all', fn($q) => $q->where('owned_by', $request->branches))
            ->when($request->filled('class') && $request->class !== 'all', function ($q) use ($request) {
                $q->where('class_id', $request->class);
            })
            ->orderBy('stdname')
            ->get()
            ->mapWithKeys(function ($student) {
                $label = trim(($student->roll_no ? $student->roll_no . ' - ' : '') . ($student->stdname ?? '') . ' s/d/o ' . ($student->fathername ?? ''));
                return [$student->id => $label];
            });
        $students->prepend('Select Student', '');

        $query = StudentFeeRevisionBatch::with([
            'registration',
            'student.class',
            'student.section',
            'student.branch',
            'sessionFrom',
            'sessionTo',
            'branchFrom',
            'branchTo',
            'classFrom',
            'classTo',
            'sectionFrom',
            'sectionTo',
            'items.feehead',
        ])->where('created_by', $creatorId);

        if ($user->type != 'company') {
            $query->where(function ($q) use ($ownedId) {
                $q->where('owned_by', $ownedId)
                    ->orWhere('branch_from_id', $ownedId)
                    ->orWhere('branch_to_id', $ownedId);
            });
        }

        if ($request->filled('student')) {
            $query->where('reg_id', $request->student);
        }

        if ($request->filled('branches') && $request->branches !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('branch_from_id', $request->branches)
                    ->orWhere('branch_to_id', $request->branches)
                    ->orWhere('owned_by', $request->branches);
            });
        }

        if ($request->filled('class') && $request->class !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('class_from_id', $request->class)
                    ->orWhere('class_to_id', $request->class);
            });
        }

        if ($request->filled('session_from_id') && $request->session_from_id !== 'all') {
            $query->where('session_from_id', $request->session_from_id);
        }

        if ($request->filled('session_to_id') && $request->session_to_id !== 'all') {
            $query->where('session_to_id', $request->session_to_id);
        }

        $report = $query
            ->orderBy('effective_from', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $concessionHeadPercentages = Concession::with('policy_head')
            ->whereIn('student_id', $report->pluck('reg_id')->filter()->unique()->all())
            ->where('status', 'Approved')
            ->where('active_status', 1)
            ->where(function ($q) {
                $q->where('end_date', '>=', date('Y-m-d'))
                    ->orWhereNull('end_date');
            })
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->mapWithKeys(function ($concession) {
                return [
                    $concession->student_id => $concession->policy_head
                        ->mapWithKeys(fn($head) => [$head->head_id => (float) $head->percentage])
                        ->all(),
                ];
            })
            ->all();

        $studentDetail = $request->filled('student')
            ? StudentRegistration::with(['enrollment.class', 'enrollment.section', 'enrollment.branch', 'class', 'section', 'branches'])
                ->find($request->student)
            : null;

        $heads = FeeHead::where('created_by', $creatorId)->orderBy('id')->get();

        $reportName = 'Student Fee Revision Report';

        if ($request->export === 'excel') {
            return Excel::download(
                new StudentFeeRevisionReportExport($report, $branches, $reportName, $request->all(), $studentDetail, $heads, $concessionHeadPercentages),
                'Student_Fee_Revision_Report.xlsx'
            );
        }

        return view('studentReports.fee_revision_report', compact(
            'branches',
            'sessions',
            'classes',
            'students',
            'studentDetail',
            'heads',
            'concessionHeadPercentages',
            'report',
            'reportName',
            'request'
        ));
    }

    public function reportFilterStudents(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();
        $branchId = $request->input('branch_id');
        $classId = $request->input('class_id');

        $classes = Classes::where('created_by', $creatorId)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('owned_by', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $students = StudentRegistration::where('created_by', $creatorId)
            ->where('student_status', 'Enrolled')
            ->where('active_status', 1)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('owned_by', $branchId))
            ->when($classId && $classId !== 'all', fn($q) => $q->where('class_id', $classId))
            ->orderBy('stdname')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => trim(($student->roll_no ? $student->roll_no . ' - ' : '') . ($student->stdname ?? '') . ' s/d/o ' . ($student->fathername ?? '')),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'classes' => $classes,
            'students' => $students,
        ]);
    }

    public function studentPromotionReport(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();
        $promotionType = $request->input('promotion_type', 'promotion');

        if ($user->type == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $creatorId)->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $ownedId)->pluck('name', 'id');
        }
        $branches->prepend('All Branches', 'all');

        $sessions = Session::where('created_by', $creatorId)->pluck('year', 'id')->prepend('All Sessions', 'all');
        $hasFilter = collect([
            $request->input('session_from_id'),
            $request->input('session_to_id'),
            $request->input('branch_from'),
            $request->input('branch_to'),
            $request->input('class_from'),
            $request->input('class_to'),
        ])->contains(fn($value) => $value !== null && $value !== '' && $value !== 'all');

        $classesFrom = Classes::where('created_by', $creatorId)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($request->filled('branch_from') && $request->branch_from !== 'all', fn($q) => $q->where('owned_by', $request->branch_from))
            ->pluck('name', 'id')
            ->prepend('All Classes', 'all');

        $classesTo = Classes::where('created_by', $creatorId)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($promotionType === 'branch_promotion' && $request->filled('branch_to') && $request->branch_to !== 'all', fn($q) => $q->where('owned_by', $request->branch_to))
            ->when($promotionType !== 'branch_promotion' && $request->filled('branch_from') && $request->branch_from !== 'all', fn($q) => $q->where('owned_by', $request->branch_from))
            ->pluck('name', 'id')
            ->prepend('All Classes', 'all');

        $query = StudentPromotions::with([
            'student.StudentRegistration',
            'prevSession',
            'newSession',
            'classFrom',
            'classTo',
            'sectionFrom',
            'sectionTo',
            'branchFrom',
            'branchTo',
        ])->where('created_by', $creatorId);

        if ($user->type != 'company') {
            $query->where(function ($q) use ($ownedId) {
                $q->where('owned_by', $ownedId)
                    ->orWhere('branch_from', $ownedId)
                    ->orWhere('branch_to', $ownedId);
            });
        }

        if ($promotionType === 'branch_promotion') {
            $query->whereColumn('branch_from', '!=', 'branch_to');
        } else {
            $query->whereColumn('branch_from', 'branch_to');
        }

        if ($request->filled('session_from_id') && $request->session_from_id !== 'all') {
            $query->where('prev_session', $request->session_from_id);
        }

        if ($request->filled('session_to_id') && $request->session_to_id !== 'all') {
            $query->where('new_session', $request->session_to_id);
        }

        if ($request->filled('branch_from') && $request->branch_from !== 'all') {
            $query->where('branch_from', $request->branch_from);
        }

        if ($promotionType === 'branch_promotion' && $request->filled('branch_to') && $request->branch_to !== 'all') {
            $query->where('branch_to', $request->branch_to);
        }

        if ($request->filled('class_from') && $request->class_from !== 'all') {
            $query->where('class_from', $request->class_from);
        }

        if ($request->filled('class_to') && $request->class_to !== 'all') {
            $query->where('class_to', $request->class_to);
        }

        $report = $hasFilter
            ? $query->orderBy('branch_from')
                ->orderBy('class_from')
                ->orderBy('promotion_date', 'desc')
                ->get()
                ->groupBy(fn($row) => ($row->branch_from ?: 'unassigned') . '_' . ($row->class_from ?: 'unassigned') . '_' . ($row->class_to ?: 'unassigned'))
            : collect();

        $reportName = 'Student Promotion Report';

        if ($request->export === 'excel') {
            return Excel::download(
                new StudentPromotionReportExport($report, $branches, $reportName, $request->all()),
                'Student_Promotion_Report.xlsx'
            );
        }

        return view('studentReports.student_promotion_report', compact(
            'branches',
            'sessions',
            'classesFrom',
            'classesTo',
            'report',
            'reportName',
            'promotionType',
            'request'
        ));
    }
public function studentProfileReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

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

        $query = StudentRegistration::with([
            'class:id,name',
            'branches:id,name',
            'session:id,year',
            'registeroption:id,name'
        ]);

        $query = $userType == 'company'
            ? $query->where('created_by', $userCreatorId)
            : $query->where('owned_by', $userOwnedId);

        if ($request->has('branch') && $request->branch != '' && $request->branch != 'all') {
            $query->where('owned_by', $request->branch);
        }

        $classes = Classes::where('created_by', $userCreatorId)->where('active_status', 1)->get()->pluck('name', 'id');
        $classes->prepend('All Classes', '');

        if ($request->has('class') && $request->class != '' && $request->class != 'all') {
            $query->where('class_id', $request->class);
        }

        if ($request->has('session_id') && $request->session_id != '' && $request->session_id != 'all') {
            $query->where('session_id', $request->session_id);
        }

        $status = [
            'Enrolled' => 'Enrolled (Active)',
            'Registered' => 'Registered',
        ];

        $filterStatus = $request->input('status', 'Enrolled');
        if ($filterStatus == 'Enrolled') {
            $query->where('student_status', 'Enrolled')
                ->whereHas('enrollment', function ($q) {
                    $q->where('active_status', 1);
                });
        } elseif ($filterStatus == 'Registered') {
            $query->where('student_status', 'Registered');
        }

        $students = $query->orderBy('stdname')->get();

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Student Profile Report';
            $branchName = $branches[$request->branch] ?? 'All Branches';
            return Excel::download(
                new StudentProfileReportExport($students, $branches, $branchName, $report_name, $request->all()),
                'student_profile_report.xlsx'
            );
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $report_name = 'Student Profile Report';
            $branchName = $branches[$request->branch] ?? 'All Branches';
            return Excel::download(
                new StudentProfileReportExport($students, $branches, $branchName, $report_name, $request->all()),
                'student_profile_report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }

        $sessions = Session::where('created_by', $userCreatorId)->get()->pluck('year', 'id');
        $sessions->prepend('All Sessions', '');

        return view('studentReports.studentprofilereport', compact(
            'students',
            'branches',
            'classes',
            'sessions',
            'status',
            'request'
        ));
    }
	public function studentSTSReport(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->where('is_active', 1)->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
        } else {
            $branches = User::where('id', $userOwnedId)->where('is_active', 1)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
        }

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();

        $months = [];
        $current = $startDate->copy()->startOfMonth();
        while ($current->lte($endDate)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $branchIds = ($request->filled('branch') && $request->branch != 'all')
            ? [$request->branch]
            : User::where('type', 'branch')->where('is_active', 1)->pluck('id')->toArray();

        $reportData = [];
        $grandTotals = array_fill_keys($months, ['adm' => 0, 'wd' => 0, 'po' => 0, 'ti' => 0, 'to' => 0]);
        $grandTotals['total_adm'] = 0;
        $grandTotals['total_wd'] = 0;
        $grandTotals['total_po'] = 0;
        $grandTotals['total_ti'] = 0;
        $grandTotals['total_to'] = 0;

        foreach ($branchIds as $branchId) {
            $branch = User::find($branchId);
            if (!$branch) continue;

            $row = ['branch_name' => $branch->name, 'months' => []];
            $totalAdm = 0;
            $totalWd = 0;
            $totalPo = 0;
            $totalTi = 0;
            $totalTo = 0;

            foreach ($months as $month) {
                $monthStart = Carbon::parse($month . '-01')->startOfMonth();
                $monthEnd = Carbon::parse($month . '-01')->endOfMonth();

               $adm = StudentEnrollments::where('owned_by', $branchId)
			    ->whereBetween('adm_date', [
			        $monthStart->toDateString(),
			        $monthEnd->toDateString(),
			    ])
			    ->count();
                $wd = StudentWithdrawal::where('owned_by', $branchId)
                    ->where(function ($q) {
                        $q->where('is_po', 0)->orWhereNull('is_po');
                    })
                    ->whereBetween('withdraw_date', [$monthStart, $monthEnd])
                    // ->get()->dd();
                    ->count();

                $po = StudentWithdrawal::where('branch_id', $branchId)
                    ->where('is_po', 1)
                    ->whereBetween('withdraw_date', [$monthStart, $monthEnd])
                    ->count();

                $ti = StudentTransfer::where('branch_to', $branchId)
                    ->where('status', 'approved')
                    ->whereBetween('transfer_date', [$monthStart, $monthEnd])
                    ->where('created_by', $userCreatorId)
                    ->count();

                $to = StudentTransfer::where('branch_from', $branchId)
                    ->where('status', 'approved')
                    ->whereBetween('transfer_date', [$monthStart, $monthEnd])
                    ->where('created_by', $userCreatorId)
                    ->count();

                $row['months'][$month] = ['adm' => $adm, 'wd' => $wd, 'po' => $po, 'ti' => $ti, 'to' => $to];
                $totalAdm += $adm;
                $totalWd += $wd;
                $totalPo += $po;
                $totalTi += $ti;
                $totalTo += $to;

                $grandTotals[$month]['adm'] += $adm;
                $grandTotals[$month]['wd'] += $wd;
                $grandTotals[$month]['po'] += $po;
                $grandTotals[$month]['ti'] += $ti;
                $grandTotals[$month]['to'] += $to;
            }

            $row['total_adm'] = $totalAdm;
            $row['total_wd'] = $totalWd;
            $row['total_po'] = $totalPo;
            $row['total_ti'] = $totalTi;
            $row['total_to'] = $totalTo;
            $row['gains'] = $totalAdm + $totalTi - $totalWd - $totalPo - $totalTo;

            $grandTotals['total_adm'] += $totalAdm;
            $grandTotals['total_wd'] += $totalWd;
            $grandTotals['total_po'] += $totalPo;
            $grandTotals['total_ti'] += $totalTi;
            $grandTotals['total_to'] += $totalTo;

            $reportData[] = $row;
        }

        $grandTotals['gains'] = $grandTotals['total_adm'] + $grandTotals['total_ti'] - $grandTotals['total_wd'] - $grandTotals['total_po'] - $grandTotals['total_to'];

         if ($request->has('export') && $request->export == 'excel') {
            $branchName = ($request->filled('branch') && $request->branch != 'all')
                ? ($branches[$request->branch] ?? 'All Branches')
                : 'All Branches';
            return Excel::download(
                new StudentSTSReportExport($reportData, $grandTotals, $months, $branchName, $startDate, $endDate),
                'student_sts_report.xlsx'
            );
        }

        return view('studentReports.student_sts_report', compact(
            'branches', 'months', 'reportData', 'grandTotals', 'startDate', 'endDate', 'request'
        ));
    }
	public function studentFeeDetail(Request $request)
    {
        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();

        if ($user->type == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $ownedId)->pluck('name', 'id');
        }
        $branches->prepend('All Branches', 'all');

        $sessions = Session::where('created_by', $creatorId)
            ->pluck('year', 'id')
            ->prepend('All Sessions', 'all');

        $classes = Classes::where('created_by', $creatorId)
            ->when($user->type != 'company', fn($q) => $q->where('owned_by', $ownedId))
            ->when($request->filled('branches') && $request->branches !== 'all', function ($q) use ($request) {
                $q->where('owned_by', $request->branches);
            })
            ->pluck('name', 'id')
            ->prepend('All Classes', 'all');

        $feeHeads = FeeHead::where('created_by', $creatorId)
            ->where('active_status', 1)
            ->orderBy('fee_head')
            ->get();

        $results = collect();
        $selectedBranch = $request->branches ?? 'all';
        $selectedSession = $request->session ?? 'all';
        $selectedClass = $request->class ?? 'all';

        $filteredHeadPcts = collect();
        foreach ($feeHeads as $head) {
            $inputKey = 'pct_' . $head->id;
            $val = $request->input($inputKey);
            if (is_numeric($val) && $val > 0) {
                $filteredHeadPcts[$head->id] = (float) $val;
            }
        }
        if ($filteredHeadPcts->isNotEmpty()) {
            $totalInputHeads = $filteredHeadPcts->count();

            // Build CASE expressions — same approach as ConcessionController::buildMatchCase
            $caseParts = [];
            foreach ($filteredHeadPcts as $headId => $pct) {
                $hid = (int) $headId;
                $pctVal = (float) $pct;
                $caseParts[] = "(concession_policy_heads.head_id = {$hid} AND concession_policy_heads.percentage = {$pctVal})";
            }
            $caseSql = implode(' THEN 1 WHEN ', $caseParts);

            // Find policies matching any of the (head_id, percentage) pairs, ordered by match_count
            $policies = ConcessionPolicy::join('concession_policy_heads', 'concession_policies.id', '=', 'concession_policy_heads.concession_id')
                ->select(
                    'concession_policies.id',
                    'concession_policies.title',
                    \DB::raw("SUM(CASE WHEN {$caseSql} THEN 1 ELSE 0 END) as match_count")
                )
                ->groupBy('concession_policies.id', 'concession_policies.title')
                ->havingRaw('match_count = ?', [$totalInputHeads])
                ->get();

            if ($policies->isNotEmpty()) {
                $allPolicyIds = $policies->pluck('id');
                $registerOptionNames = Registring_option::pluck('name', 'id');

                $concessionQuery = Concession::with([
                    'student.enrollment.class',
                    'student.enrollment.section',
                    'student.branches',
                    'student.fee_structure' => fn($q) => $q->whereIn('head_id', $filteredHeadPcts->keys()),
                    'student.fee_structure.feehead',
                    'policy',
                    'policy.policy_head' => fn($q) => $q->whereIn('head_id', $filteredHeadPcts->keys()),
                ])
                ->whereIn('concession_id', $allPolicyIds)
                ->where('status', 'Approved')
                ->where('active_status', 1)
                ->where(function ($q) {
                    $q->whereDate('end_date', '>=', date('Y-m-d'))
                        ->orWhereNull('end_date');
                })
                ->whereHas('student', fn($q) => $q->where('student_status', 'Enrolled'));

                if ($selectedBranch !== 'all') {
                    $concessionQuery->whereHas('student', fn($q) => $q->where('branch', $selectedBranch));
                }
                if ($selectedSession !== 'all') {
                    $concessionQuery->whereHas('student', fn($q) => $q->where('session_id', $selectedSession));
                }
                if ($selectedClass !== 'all') {
                    $concessionQuery->whereHas('student', fn($q) => $q->where('class_id', $selectedClass));
                }

                $activeConcessions = $concessionQuery->orderByDesc('id')->get()->unique('student_id');

                $headNames = FeeHead::whereIn('id', $filteredHeadPcts->keys())->pluck('fee_head', 'id');

                $results = $activeConcessions->map(function ($concession) use ($filteredHeadPcts, $registerOptionNames, $headNames) {
                    $student = $concession->student;
                    if (!$student) return null;

                    $policyName = optional($concession->policy)->title
                        ?? $registerOptionNames->get($student->register_option)
                        ?? '-';

                    $policyHeads = $concession->policy ? ($concession->policy->policy_head ?? collect()) : collect();

                    // Batch-load ClassWiseFee for this student once
                    $classWiseFees = ClassWiseFee::where('class_id', $student->class_id)
                        ->whereIn('head_id', $filteredHeadPcts->keys())
                        ->where('session_id', $student->session_id)
                        ->get()
                        ->keyBy('head_id');

                    $items = collect();
                    foreach ($filteredHeadPcts as $headId => $searchedPct) {
                        $policyHead = $policyHeads->firstWhere('head_id', $headId);
                        if (!$policyHead) continue;

                        $discountPct = (float) $policyHead->percentage;

                        $cwf = $classWiseFees->get($headId);
                        $feeStructureRow = $student->fee_structure->firstWhere('head_id', $headId);

                        if ($cwf) {
                            $latestAmount = (float) $cwf->amount;
                        } elseif ($feeStructureRow) {
                            $latestAmount = (float) $feeStructureRow->amount;
                        } else {
                            $latestAmount = 0;
                        }

                        $headName = $headNames[$headId] ?? optional($feeStructureRow->feehead)->fee_head ?? '-';
                        $discountAmt = round(($latestAmount * $discountPct) / 100, 2);
                        $payable = $latestAmount - $discountAmt;

                        $items->push([
                            'head_name' => $headName,
                            'actual_fee' => $latestAmount,
                            'discount_amount' => $discountAmt,
                            'discount_pct' => $discountPct,
                            'payable' => $payable,
                        ]);
                    }

                    if ($items->isEmpty()) return null;

                    return [
                        'student' => $student,
                        'items' => $items,
                        'policy_name' => $policyName,
                    ];
                })->filter()->values();
            }
        }

        $groupedResults = $results->groupBy(fn($r) => $r['student']->branch);
        $totalStudents = $results->count();

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Student Fee Detail Report';
            return Excel::download(new StudentFeeDetailExport($branches, $classes, $groupedResults, $selectedBranch, $selectedSession, $selectedClass, $feeHeads, $filteredHeadPcts, $report_name, $totalStudents, $request->all()), 'Student_Fee_Detail_Report.xlsx');
        }

        return view('studentReports.student_fee_detail', compact(
            'branches',
            'sessions',
            'classes',
            'feeHeads',
            'groupedResults',
            'totalStudents',
            'selectedBranch',
            'selectedSession',
            'selectedClass',
            'request'
        ));
    }
}
