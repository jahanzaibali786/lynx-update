<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class EmployeeApraisalFroms extends Controller
{
    public function AppraisalFrom(Request $request)
    {
        return view('employee.apprisalFroms.index');
    }
    public function itTeacherAppraisalFrom(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        $bodyHtml = view('employee.apprisalForms.itTeacherAppraisalForm', compact( 'branches'))->render();
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
                <div class="header">' . $headerHtml . '</div>
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
        return $dompdf->stream('IT_teacher_appraisal_form.pdf', ['Attachment' => false]);
    }
    public function domesticStaff(Request $request)
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        $bodyHtml = view('employee.apprisalForms.domesticStaff', compact( 'branches'))->render();
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
                <div class="header">' . $headerHtml . '</div>
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
        return $dompdf->stream('domesticStaffAppraisaForm.pdf', ['Attachment' => false]);
    }
    public function sportsTeacher(Request $request){
        $filename = 'sportsTeacher';
        $printname= 'sportsTeacherAppraisalForm';
        $this->reportPrint($request ,$filename , $printname);
    }
    public function Teacherappr(Request $request){
        $filename = 'teacher';
        $printname= 'TeacherAppraisalForm';
        $this->reportPrint($request ,$filename , $printname);
    }
    public function directorHead(Request $request){
        $filename = 'director_head';
        $printname= 'HeadAppraisalForm';
        $this->reportPrint($request ,$filename , $printname);
    }

    public function reportPrint($request , $filename ,$printname){
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();
        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', $userOwnedId)->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        $bodyHtml = view('employee.apprisalForms.'.$filename, compact( 'branches'))->render();
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
                <div class="header">' . $headerHtml . '</div>
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

        return $dompdf->stream($printname.'.pdf', ['Attachment' => false]);
    }
}
