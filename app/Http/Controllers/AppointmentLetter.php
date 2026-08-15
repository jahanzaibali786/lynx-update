<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AppointmentLetter as ModelsAppointmentLetter;
use App\Models\EmployeePayscaleDetail;
use App\Services\AppointmentLetterPlaceholderService;
use Illuminate\Http\Request;

class AppointmentLetter extends Controller
{
    public function index(){
        if(\Auth::user()->type == 'company'){
            $letters = ModelsAppointmentLetter::where('created_by', '=', \Auth::user()->creatorId())->paginate(25);
        }else{
            $letters = ModelsAppointmentLetter::where('owned_by', '=', \Auth::user()->ownedId())->paginate(25);
        }
        return view('employee.appointmentletter.appointmentletter',compact('letters'));
    }
    public function create(){
        $variables = AppointmentLetterPlaceholderService::variables();

        return view('employee.appointmentletter.create', compact('variables'));
    }
    public function store(Request $request){
        // dd($request->all());
        $validator = \Validator::make(
            $request->all(), [
                'no' => 'required|unique:appointment_letters,no',
                'date' => 'required',
                'type'=>'required',
                'datacontent' => 'required',
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $letter = new ModelsAppointmentLetter();
        $letter->no = $request->no;
        $letter->date = $request->date;
        $letter->type = $request->type;
        $letter->datacontent = $request->datacontent;
        $letter->owned_by     = \Auth::user()->ownedId();
        $letter->created_by     = \Auth::user()->creatorId();
        $letter->save();
        return redirect()->route('appointment-letter')->with('success', __('Appointment Letter successfully created.'));
    }

    public function edit(Request $request,$id){
        $letter = ModelsAppointmentLetter::find($id);
        $variables = AppointmentLetterPlaceholderService::variables();

        return view('employee.appointmentletter.edit',compact('letter', 'variables'));
    }

    public function preview($id)
    {
        if (\Auth::user()->type == 'company') {
            $letter = ModelsAppointmentLetter::where('id', $id)
                ->where('created_by', \Auth::user()->creatorId())
                ->firstOrFail();
        } else {
            $letter = ModelsAppointmentLetter::where('id', $id)
                ->where('owned_by', \Auth::user()->ownedId())
                ->firstOrFail();
        }

        $employee = new Employee([
            'name' => '',
            'company_doj' => $letter->date,
            'probation_period' => '',
        ]);

        $payscaleDetail = new EmployeePayscaleDetail([
            'effect_from' => $letter->date,
            'working_days' => '',
        ]);

        $appointmentLetterContent = AppointmentLetterPlaceholderService::render(
            (string) $letter->datacontent,
            $employee,
            $payscaleDetail
        );

        return view('employee.emp_salary_detail.emp-appointment-letter', [
            'employee' => $employee,
            'appointmentletterdata' => $letter,
            'lastPayscaleDetail' => $payscaleDetail,
            'appointmentLetterContent' => $appointmentLetterContent,
        ]);
    }

    public function update(Request $request ,$id){
        $validator = \Validator::make(
            $request->all(), [
                'no' => 'required',
                'date' => 'required',
                'datacontent' => 'required',
                'type'=>'required',
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $letter = ModelsAppointmentLetter::find($id);
        $letter->no = $request->no;
        $letter->date = $request->date;
        $letter->type = $request->type;
        $letter->datacontent = $request->datacontent;
        $letter->owned_by     = \Auth::user()->ownedId();
        $letter->created_by     = \Auth::user()->creatorId();
        $letter->save();
        return redirect()->route('appointment-letter')->with('success', __('Appointment Letter successfully Updated.'));
    }
}
