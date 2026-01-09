<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->can('manage leave type'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = LeaveType::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = LeaveType::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $leavetypes = $query->paginate(25);

            return view('leavetype.index', compact('leavetypes','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create leave type'))
        {
            return view('leavetype.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create leave type'))
        {

            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                // 'days' => 'numeric',
                'Status' => 'required',
            ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $leavetype             = new LeaveType();
            $leavetype->title      = $request->title;
            $leavetype->days       = 0;
            $leavetype->status       = $request->Status;
            $leavetype->owned_by = \Auth::user()->ownedId();
            $leavetype->created_by = \Auth::user()->creatorId();
            $leavetype->save();
            return redirect()->route('leavetype.index')->with('success', __('LeaveType  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(LeaveType $leavetype)
    {
        return redirect()->route('leavetype.index');
    }

    public function edit(Request $request, $id)
    {
        if (\Auth::user()->can('edit leave type'))
        {
            $pattern1 = '%annual%';
            $pattern2 = '%casual%';
            $pattern3 = '%maternity%';

            $leavetype = LeaveType::where('id', $id)->first();

            if ($leavetype->created_by == \Auth::user()->creatorId())
            {
                $isReadOnly = false;
                if (stripos($leavetype->title, str_replace('%', '', $pattern1)) !== false ||
                    stripos($leavetype->title, str_replace('%', '', $pattern2)) !== false ||
                    stripos($leavetype->title, str_replace('%', '', $pattern3)) !== false)
                {
                    $isReadOnly = true;
                }
                return view('leavetype.edit', compact('leavetype', 'isReadOnly'));
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


    public function update(Request $request, LeaveType $leavetype)
    {
        if(\Auth::user()->can('edit leave type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                    'title' => 'required',
                    // 'days' => 'numeric',
                    'Status' => 'required',
                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leavetype->title = $request->title;
                $leavetype->days  = $request->days;
                $leavetype->status  = $request->Status;
                $leavetype->save();

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully updated.'));
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

    public function destroy(LeaveType $leavetype)
    {
        if(\Auth::user()->can('delete leave type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                $leavetype->delete();

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully deleted.'));
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
}