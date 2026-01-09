<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;

class ClearanceCertificate extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date;
        $std_reg = StudentRegistration::where('id',$request->student)->first();

        $studentDetail = null;
        if ($std_reg) {
            $studentDetail = StudentEnrollments::with('StudentRegistration', 'class', 'branch', 'section')
                ->where('enrollId', $std_reg->roll_no)
                ->first();
        }

        // dd($request->all(),$studentDetail,$std_reg);
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        $session = [];
        $class = [];

        $students = [];
        if($request->branches){
          $class =  Classes::where('owned_by',$request->branches)->pluck('name','id');
        }
        if($request->class){
            $students = StudentRegistration::
            where('class_id', $request->class)
              ->where('student_status', 'Enrolled')
              ->get()
              ->mapWithKeys(function ($student) {
                  return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
              });
            //   dd($students);
        }
        return view('students.clearanceCertificate.template', compact('session', 'class', 'students', 'branches', 'date', 'studentDetail'));
    }
}