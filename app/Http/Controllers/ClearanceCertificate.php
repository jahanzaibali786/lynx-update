<?php

namespace App\Http\Controllers;

use App\Models\Challans;
use App\Models\Classes;
use App\Models\FeeHead;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ClearanceCertificate extends Controller
{
   public function index(Request $request)
{
    $date = $request->date;
    $std_reg = StudentRegistration::where('id', $request->student)->first();

    $studentDetail = null;
    if ($std_reg) {
        $studentDetail = StudentEnrollments::with('StudentRegistration', 'class', 'branch', 'section')
            ->where('enrollId', $std_reg->roll_no)
            ->first();
    }

    if (\Auth::user()->type == 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', \Auth::user()->creatorId())
            ->get()->pluck('name', 'id');

        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend('Select Branch', '');
    } else {
        $branches = User::where('id', \Auth::user()->ownedId())
            ->get()->pluck('name', 'id');

        $branches->prepend('Select Branch', '');
    }

    $session = [];
    $class = [];
    $students = [];

    if ($request->branches) {
        $class = Classes::where('owned_by', $request->branches)->pluck('name', 'id');
    }

    if ($request->class) {
        $students = StudentRegistration::where('class_id', $request->class)
            ->where('student_status', 'Enrolled')
            ->get()
            ->mapWithKeys(function ($student) {
                return [
                    $student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername
                ];
            });
    }

    $security = 0;

    $lastchallan = Challans::where('student_id', $request->student)
        ->whereNotIn('challan_type', ['Registration', 'withdrawal', 'transfer'])
        ->where('status', 'paid')
        ->latest()
        ->first();

    $admissionChallan = Challans::where('student_id', $request->student)
        ->whereIn('challan_type', ['Admission', 'readmission'])
        ->latest()
        ->first();

    $feehead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', ['%security%'])->first();

    if ($admissionChallan && $feehead) {
        $head = $admissionChallan->heads()->where('head_id', $feehead->id)->first();
        $security = $head ? $head->paid : 0;
    }

    // ✅ If print request
    if ($request->has('print')) {
        $pdf = Pdf::loadView('students.clearanceCertificate.pdf', compact(
            'session', 'class', 'students', 'branches', 'date',
            'studentDetail', 'lastchallan', 'security'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('clearance-certificate.pdf');
    }

    return view('students.clearanceCertificate.template', compact(
        'session', 'class', 'students', 'branches', 'date',
        'studentDetail', 'lastchallan', 'security'
    ));
}
}