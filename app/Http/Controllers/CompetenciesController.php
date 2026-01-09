<?php

namespace App\Http\Controllers;

use App\Models\Competencies;
use App\Models\PerformanceType;
use App\Models\User;
use Illuminate\Http\Request;

class CompetenciesController extends Controller
{

    public function index(Request $request)
    {
        // if(\Auth::user()->can('Manage Competencies'))
        // {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = Competencies::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Competencies::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $competencies = $query->paginate(25);

            return view('competencies.index', compact('competencies','branches'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }


    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        }else{
            $performance     = PerformanceType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        return view('competencies.create', compact('performance'));

    }


    public function store(Request $request)
    {
        // if(\Auth::user()->can('Create Competencies'))
        // {

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $competencies             = new Competencies();
            $competencies->name       = $request->name;
            $competencies->type       = $request->type;
            $competencies->owned_by = \Auth::user()->ownedId();
            $competencies->created_by = \Auth::user()->creatorId();
            $competencies->save();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully created.'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }


    public function show(Competencies $competencies)
    {
        //
    }


    public function edit($id)
    {
        $competencies = Competencies::find($id);
        if (\Auth::user()->type == 'company') {
            $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        }else{
            $performance     = PerformanceType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        return view('competencies.edit', compact('performance', 'competencies'));

    }


    public function update(Request $request, $id)
    {
        // if(\Auth::user()->can('Edit Competencies'))
        // {

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $competencies       = Competencies::find($id);
            $competencies->name = $request->name;
            $competencies->type = $request->type;
            $competencies->save();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully updated.'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }


    public function destroy($id)
    {
        // if(\Auth::user()->can('Delete Competencies'))
        // {
            $competencies = Competencies::find($id);
            $competencies->delete();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully deleted.'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }
}
