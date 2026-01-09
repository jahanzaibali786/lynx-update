<?php

namespace App\Http\Controllers;

use App\Models\PerformanceType;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceTypeController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage performance type'))
        {
            if(\Auth::user()->type == 'company')
            {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = PerformanceType::where('created_by', '=', \Auth::user()->creatorId());
                if (!empty($request->branches)) {
                    $query->where('owned_by', '=', $request->branches);
                }
                $types = $query->paginate(25);
                return view('performanceType.index', compact('types','branches'));
            }elseif(\Auth::user()->type == 'branch')
            {
                $types = PerformanceType::where('owned_by', '=', \Auth::user()->ownedId())->paginate(25);
                return view('performanceType.index', compact('types'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    public function create()
    {
        return view('performanceType.create');
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create performance type'))
        {
            if(\Auth::user()->type == 'company' ||  \Auth::user()->type == 'branch')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $types             = new PerformanceType();
                $types->name       = $request->name;
                $types->owned_by = \Auth::user()->ownedId();
                $types->created_by = \Auth::user()->creatorId();
                $types->save();

                return redirect()->route('performanceType.index')->with('success', __('Performance Type successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(PerformanceType $performanceType)
    {
        //
    }


    public function edit(PerformanceType $performanceType)
    {
        return view('performanceType.edit', compact('performanceType'));
    }


    public function update(Request $request, PerformanceType $performanceType)
    {

        if(\Auth::user()->can('edit performance type'))
        {
            if(\Auth::user()->type == 'company' ||  \Auth::user()->type == 'branch')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $performanceType->name       = $request->name;
                $performanceType->created_by = \Auth::user()->creatorId();
                $performanceType->save();

                return redirect()->route('performanceType.index')->with('success', __('Performance Type successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

        }


    }


    public function destroy(PerformanceType $performanceType)
    {

        if(\Auth::user()->can('delete performance type'))
        {
            if(\Auth::user()->type == 'company')
            {

                $performanceType->delete();

                return redirect()->route('performanceType.index')->with('success', __('Performance Type successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }


    }
}
