<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(Request $request)
    {

        if(\Auth::user()->can('manage designation'))
        {
            // if (\Auth::user()->type == 'company') {
            //     $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            //     $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            //     $branches->prepend('Select Branch', '');
            //     $query = Designation::where('created_by', '=', \Auth::user()->creatorId());
            // }else{
            //     $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            //     $branches->prepend('Select Branch', '');
            //     $query = Designation::where('owned_by', '=', \Auth::user()->ownedId());
            // }
            // if (!empty($request->branches)) {
            //     $query->where('owned_by', '=', $request->branches);
            // }
            // $designations = $query->get();
            $designations = Designation::where('created_by', '=', \Auth::user()->creatorId())->paginate(25);
            return view('designation.index', compact('designations'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('create designation'))
        {
            // if (\Auth::user()->type == 'company') {
            //     $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get();
            //     $departments = $departments->pluck('name', 'id');
            // }else{
                //     $departments = Department::where('owned_by', '=', \Auth::user()->ownedId())->get();
                //     $departments = $departments->pluck('name', 'id');
                // }
                    $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('designation.create', compact('departments'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('create designation'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'department_id' => 'required',
                                   'name' => 'required',
                                   'code' => 'required|max:10',
                                   'rank' => 'required',
                                   'job_description'=>'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $designation                = new Designation();
            $designation->department_id = $request->department_id;
            $designation->name          = $request->name;
            $designation->code          = $request->code;
            $designation->rank          = $request->rank;
            $designation->job_description = $request->job_description;
            $designation->owned_by      = \Auth::user()->ownedId();
            $designation->created_by    = \Auth::user()->creatorId();

            $designation->save();

            return redirect()->route('designation.index')->with('success', __('Designation  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Designation $designation)
    {
        return redirect()->route('designation.index');
    }

    public function edit(Designation $designation)
    {

        if(\Auth::user()->can('edit designation'))
        {
            if($designation->created_by == \Auth::user()->creatorId())
            {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                return view('designation.edit', compact('designation', 'departments'));
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

    public function update(Request $request, Designation $designation)
    {
        if(\Auth::user()->can('edit designation'))
        {
            if($designation->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'department_id' => 'required',
                                       'name' => 'required',
                                       'code' => 'required|max:10',
                                       'rank' => 'required',
                                       'job_description'=>'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $designation->name          = $request->name;
                $designation->code          = $request->code;
                $designation->department_id = $request->department_id;
                $designation->rank = $request->rank;
                $designation->job_description = $request->job_description;
                $designation->save();

                return redirect()->route('designation.index')->with('success', __('Designation  successfully updated.'));
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

    public function destroy(Designation $designation)
    {
        if(\Auth::user()->can('delete designation'))
        {
            if($designation->created_by == \Auth::user()->creatorId())
            {
                $designation->delete();

                return redirect()->route('designation.index')->with('success', __('Designation successfully deleted.'));
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
