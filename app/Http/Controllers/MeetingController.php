<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Meeting;
use App\Models\MeetingEmployee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->can('manage meeting'))
        {   
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', ''); 
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get();
                $query = Meeting::where('created_by', '=', \Auth::user()->creatorId());
            }elseif(Auth::user()->type == 'Employee')
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $current_employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $meetings         = Meeting::orderBy('meetings.id', 'desc')
                    ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                                           ->where('meeting_employees.employee_id', '=', $current_employee->id)
                    ->orWhere(function($q) {
                        $q->where('meetings.department_id', '["0"]')
                            ->where('meetings.employee_id', '["0"]');
                    });
            }
            else
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $employees = Employee::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $query = Meeting::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $meetings = $query->get();

            return view('meeting.index', compact('meetings', 'employees','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create meeting'))
        {
            if (\Auth::user()->type == 'company') {
                $branch      = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
                $departments = Department::where('created_by', '=', Auth::user()->creatorId())->get();
                $employees   = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $settings = Utility::settings();
            }elseif(Auth::user()->type == 'Employee')
            {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('user_id', '!=', \Auth::user()->id)->get()->pluck('name', 'id');
                $settings = Utility::settings();
            }
            else
            {
                $branch      = Branch::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $departments = Department::where('owned_by', '=', Auth::user()->ownedId())->get();
                $employees   = Employee::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $settings = Utility::settings();
            }

            return view('meeting.create', compact('employees', 'departments', 'branch','settings'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'branch_id' => 'required',
                               'employee_id' => 'required',
                               'department_id' => 'required',
                               'title' => 'required',
                               'date' => 'required',
                               'time' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if(\Auth::user()->can('create meeting'))
        {
            $meeting                = new Meeting();
            $meeting->branch_id     = $request->branch_id;
            $meeting->department_id = json_encode($request->department_id);
            $meeting->employee_id   = json_encode($request->employee_id);
            $meeting->title         = $request->title;
            $meeting->date          = $request->date;
            $meeting->time          = $request->time;
            $meeting->note          = $request->note;
            $meeting->owned_by    = \Auth::user()->ownedId();
            $meeting->created_by    = \Auth::user()->creatorId();
            $meeting->save();

            if(in_array('0', $request->employee_id))
            {
                $departmentEmployee = Employee::whereIn('department_id', $request->department_id)->get()->pluck('id');
                $departmentEmployee = $departmentEmployee;
            }
            else
            {
                $departmentEmployee = $request->employee_id;
            }
            foreach($departmentEmployee as $employee)
            {
                $meetingEmployee              = new MeetingEmployee();
                $meetingEmployee->meeting_id  = $meeting->id;
                $meetingEmployee->employee_id = $employee;
                $meetingEmployee->owned_by  = \Auth::user()->ownedId();
                $meetingEmployee->created_by  = \Auth::user()->creatorId();
                $meetingEmployee->save();
            }


            //For Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $branch = Branch::find($request->branch_id);
            $meetingNotificationArr = [
                'meeting_title' =>  $request->title,
                'branch_name' =>  @$branch->name,
                'meeting_date' =>  $request->date,
                'meeting_time' =>  $request->time,
            ];
            //Slack Notification
            if(isset($setting['support_notification']) && $setting['support_notification'] ==1)
            {
                Utility::send_slack_msg('new_meeting', $meetingNotificationArr);
            }
            //Telegram Notification
            if(isset($setting['telegram_meeting_notification']) && $setting['telegram_meeting_notification'] ==1)
            {
                Utility::send_telegram_msg('new_meeting', $meetingNotificationArr);
            }

            //For Google Calendar
            if($request->get('synchronize_type')  == 'google_calender')
            {
                $type ='meeting';
                $request1=new Meeting();
                $request1->title=$request->title;
                $request1->start_date=$request->date;
                $request1->end_date=$request->date;
                Utility::addCalendarData($request1 , $type);
            }

            //webhook
            $module ='New Meeting';
            $webhook =  Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($meetingEmployee);
                $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                if($status == true)
                {
                    return redirect()->route('meeting.index')->with('success', __('Meeting  successfully created.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('meeting.index')->with('success', __('Meeting  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Meeting $meeting)
    {
        return redirect()->route('meeting.index');
    }

    public function edit($meeting)
    {
        if(\Auth::user()->can('edit meeting'))
        {
            $meeting = Meeting::find($meeting);
            if($meeting->created_by == Auth::user()->creatorId())
            {
                if (\Auth::user()->type == 'company') {
                    $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                }elseif(Auth::user()->type == 'Employee')
                {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('user_id', '!=', Auth::user()->id)->get()->pluck('name', 'id');
                }
                else
                {
                    $employees = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                }

                return view('meeting.edit', compact('meeting', 'employees'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Meeting $meeting)
    {
        if(\Auth::user()->can('edit meeting'))
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'title' => 'required',
                                   'date' => 'required',
                                   'time' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if($meeting->created_by == \Auth::user()->creatorId())
            {
                $meeting->title = $request->title;
                $meeting->date  = $request->date;
                $meeting->time  = $request->time;
                $meeting->note  = $request->note;
                $meeting->save();

                return redirect()->route('meeting.index')->with('success', __('Meeting successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Meeting $meeting)
    {
        if(\Auth::user()->can('delete meeting'))
        {
            if($meeting->created_by == \Auth::user()->creatorId())
            {
                $meeting->delete();

                return redirect()->route('meeting.index')->with('success', __('Meeting successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getdepartment(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            if($request->branch_id == 0)
            {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            }
            else
            {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
            }
        }else{
            if($request->branch_id == 0)
            {
                $departments = Department::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id')->toArray();
            }
            else
            {
                $departments = Department::where('owned_by', '=', \Auth::user()->ownedId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
            }
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            if(in_array('0', $request->department_id))
            {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            }
            else
            {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereIn('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
            }
        }else{
            if(in_array('0', $request->department_id))
            {
                $employees = Employee::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id')->toArray();
            }
            else
            {
                $employees = Employee::where('owned_by', '=', \Auth::user()->ownedId())->whereIn('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
            }
        }

        return response()->json($employees);
    }

    public function calender(Request $request)
    {

        if(\Auth::user()->can('manage meeting'))
        {
            $transdate = date('Y-m-d', time());

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', ''); 
                $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $meetings = Meeting::where('owned_by', '=', \Auth::user()->ownedId());
            }

            if(!empty($request->start_date))
            {
                $meetings->where('date', '>=', $request->start_date);
            }
            if(!empty($request->end_date))
            {
                $meetings->where('date', '<=', $request->end_date);
            }
            if (!empty($request->branches)) {
                $meetings->where('owned_by', '=', $request->branches);
            }
            $meetings = $meetings->get();

            $arrMeetings = [];

            foreach($meetings as $meeting)
            {
                $arr['id']        = $meeting['id'];
                $arr['title']     = $meeting['title'];
                $arr['start']     = $meeting['date'];
                $arr['time']     = $meeting['time'];
                $arr['className'] = 'event-primary';
                $arr['url']       = route('meeting.edit', $meeting['id']);
                $arrMeetings[]    = $arr;
            }
            $arrMeetings = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrMeetings)));

            return view('meeting.calender', compact('arrMeetings','transdate','meetings','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    //for Google Calendar
    public function get_meeting_data(Request $request)
    {

        if($request->get('calender_type') == 'goggle_calender')
        {
            $type ='meeting';
            $arrayJson =  Utility::getCalendarData($type);
        }
        else
        {
            if (\Auth::user()->type == 'company') {
                $data =Meeting::where('created_by', '=', \Auth::user()->creatorId())->get();
            }else{
                $data =Meeting::where('owned_by', '=', \Auth::user()->ownedId())->get();
            }
            $arrayJson = [];
            foreach($data as $val)
            {
                $arrayJson[] = [
                    "id"=> $val->id,
                    "title" => $val->title,
                    "start" => $val->date.' '.$val->time,
                    "className" =>'event-primary',
                    "textColor" => '#51459d',
                    'url'      => route('meeting.edit', $val->id),
                    "allDay" => false,
                ];
            }
        }

        return $arrayJson;
    }

}
