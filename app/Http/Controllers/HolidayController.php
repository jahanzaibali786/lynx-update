<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class HolidayController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage holiday'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', ''); 
                $query = Holiday::where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Holiday::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if(!empty($request->start_date))
            {
                $query->where('date', '>=', $request->start_date);
            }
            if(!empty($request->end_date))
            {
                $query->where('date', '<=', $request->end_date);
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $holidays = $query->get();

            return view('holiday.index', compact('holidays','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function create()
    {
        if(\Auth::user()->can('create holiday'))
        {
            $settings = Utility::settings();
            return view('holiday.create',compact('settings'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create holiday'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'occasion' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $holiday             = new Holiday();
            $holiday->date       = $request->date;
            $holiday->end_date     = $request->end_date;
            $holiday->occasion   = $request->occasion;
            $holiday->owned_by = \Auth::user()->ownedId();
            $holiday->created_by = \Auth::user()->creatorId();
            $holiday->save();

            //For Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $holidayNotificationArr = [
                'holiday_title' => $request->occasion,
                'holiday_date' => $request->date,
            ];
            //Slack Notification
            if(isset($setting['holiday_notification']) && $setting['holiday_notification'] ==1)
            {
                Utility::send_slack_msg('new_holiday', $holidayNotificationArr);
            }
            //Telegram Notification
            if(isset($setting['telegram_holiday_notification']) && $setting['telegram_holiday_notification'] ==1)
            {
                Utility::send_telegram_msg('new_holiday', $holidayNotificationArr);
            }

            //For Google Calendar
            if($request->get('synchronize_type')  == 'google_calender')
            {

                $type ='holiday';
                $request1=new Holiday();
                $request1->title=$request->occasion;
                $request1->start_date=$request->date;
                $request1->end_date=$request->end_date;

                Utility::addCalendarData($request1 , $type);

            }

            //webhook
            $module ='New Holiday';
            $webhook =  Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($holiday);
                $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);

                if($status == true)
                {
                    return redirect()->route('holiday.index')->with('success', 'Holiday successfully created.');
                }
                else
                {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully created.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(Holiday $holiday)
    {
        //
    }


    public function edit(Holiday $holiday)
    {
        if(\Auth::user()->can('edit holiday'))
        {
            return view('holiday.edit', compact('holiday'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function update(Request $request, Holiday $holiday)
    {
        if(\Auth::user()->can('edit holiday'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'occasion' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $holiday->date     = $request->date;
            $holiday->end_date       = $request->end_date;
            $holiday->occasion = $request->occasion;
            $holiday->save();

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully updated.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Holiday $holiday)
    {
        if(\Auth::user()->can('delete holiday'))
        {
            $holiday->delete();

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully deleted.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function calender(Request $request)
    {
        if(\Auth::user()->can('manage holiday'))
        {
            $transdate = date('Y-m-d', time());
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', ''); 
                $holidays = Holiday::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $holidays = Holiday::where('owned_by', '=', \Auth::user()->ownedId());
            }

            if(!empty($request->start_date))
            {
                $holidays->where('date', '>=', $request->start_date);
            }
            if(!empty($request->end_date))
            {
                $holidays->where('date', '<=', $request->end_date);
            }
            if (!empty($request->branches)) {
                $holidays->where('owned_by', '=', $request->branches);
            }

            $holidays = $holidays->get();

            $arrHolidays = [];

            foreach($holidays as $holiday)
            {
                $arr['id']        = $holiday['id'];
                $arr['title']     = $holiday['occasion'];
                $arr['start']     = $holiday['date'];
                $arr['end']       = $holiday['end_date'];
                $arr['className'] = 'event-primary';
                $arr['url']       = route('holiday.edit', $holiday['id']);
                $arrHolidays[]    = $arr;
            }
            $arrHolidays = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrHolidays)));

            return view('holiday.calender', compact('arrHolidays','transdate','holidays','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    //for Google Calendar
    public function get_holiday_data(Request $request)
    {
        if($request->get('calender_type') == 'goggle_calender')
        {
            $type ='holiday';
            $arrayJson =  Utility::getCalendarData($type);
        }
        else
        {
            if (\Auth::user()->type == 'company') {
                $query =Holiday::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $query =Holiday::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branch)) {
                $query->where('owned_by', '=', $request->branch);
            }
            $data = $query->get();

            $arrayJson = [];
            foreach($data as $val)
            {

                $end_date=date_create($val->end_date);
                date_add($end_date,date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id"=> $val->id,
                    "title" => $val->occasion,
                    "start" => $val->date,
                    "end" => date_format($end_date,"Y-m-d H:i:s"),
                    "className" => 'event-primary',
                    "textColor" => '#51459d',
                    'url'      => route('holiday.edit', $val->id),
                    "allDay" => true,
                ];
            }
        }

        return $arrayJson;
    }

}
