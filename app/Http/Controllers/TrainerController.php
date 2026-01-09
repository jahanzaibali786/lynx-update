<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->can('manage trainer'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $query = Trainer::where('created_by', '=', \Auth::user()->creatorId());
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Trainer::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $trainers = $query->get();

            return view('trainer.index', compact('trainers','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create trainer'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }else{
                $branches = Branch::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }

            return view('trainer.create', compact('branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create trainer'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'branch' => 'required',
                                   'firstname' => 'required',
                                   'lastname' => 'required',
                                   'contact' => 'required',
                                   'email' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $trainer             = new Trainer();
            $trainer->branch     = $request->branch;
            $trainer->firstname  = $request->firstname;
            $trainer->lastname   = $request->lastname;
            $trainer->contact    = $request->contact;
            $trainer->email      = $request->email;
            $trainer->address    = $request->address;
            $trainer->expertise  = $request->expertise;
            $trainer->owned_by   = \Auth::user()->ownedId();
            $trainer->created_by = \Auth::user()->creatorId();
            $trainer->save();

            return redirect()->route('trainer.index')->with('success', __('Trainer  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Trainer $trainer)
    {
        return view('trainer.show', compact('trainer'));
    }


    public function edit(Trainer $trainer)
    {
        if(\Auth::user()->can('edit trainer'))
        {
            if (\Auth::user()->type == 'company') {
                $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }else{
                $branches = Branch::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }

            return view('trainer.edit', compact('branches', 'trainer'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Trainer $trainer)
    {
        if(\Auth::user()->can('edit trainer'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'branch' => 'required',
                                   'firstname' => 'required',
                                   'lastname' => 'required',
                                   'contact' => 'required',
                                   'email' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $trainer->branch    = $request->branch;
            $trainer->firstname = $request->firstname;
            $trainer->lastname  = $request->lastname;
            $trainer->contact   = $request->contact;
            $trainer->email     = $request->email;
            $trainer->address   = $request->address;
            $trainer->expertise = $request->expertise;
            $trainer->save();

            return redirect()->route('trainer.index')->with('success', __('Trainer  successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Trainer $trainer)
    {
        if(\Auth::user()->can('delete trainer'))
        {
            if($trainer->created_by == \Auth::user()->creatorId())
            {
                $trainer->delete();

                return redirect()->route('trainer.index')->with('success', __('Trainer successfully deleted.'));
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
