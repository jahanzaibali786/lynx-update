<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        // if(\Auth::user()->can('manage session'))
        // {

            $sessions = Session::where('created_by',Auth::user()->creatorId())->paginate(25);
            return view('students.session.index', compact('sessions'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }


    public function create()
    {
        // if(\Auth::user()->can('create session'))
        // {
            return view('students.session.create');
        // }
        // else
        // {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }

    }


    public function store(Request $request)
    {
        // if(\Auth::user()->can('create session'))
        // {
            $validator = \Validator::make(
                $request->all(), [
                                   'year' => 'required',
                                //    'title' => 'required',
                                   'starting_date' => 'required',
                                   'ending_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $session             = new Session();
            $session->year       = $request->year;
            // $session->title      = $request->title;
            $session->starting_date      = $request->starting_date;
            $session->ending_date      = $request->ending_date;
            $session->owned_by = \Auth::user()->ownedId();
            $session->created_by = \Auth::user()->creatorId();
            $session->save();

            return redirect()->route('session.index')->with('Session has been created successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }


    }

    public function edit(Session $session)
    {
        // if(\Auth::user()->can('edit session'))
        // {

            return view('students.session.edit', compact('session'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }


    }

    public function update(Request $request, Session $session)
    {

        // if(\Auth::user()->can('edit session'))
        // {
            $validator = \Validator::make(
                $request->all(), [
                                   'year' => 'required',
                                //    'title' => 'required',
                                   'starting_date' => 'required',
                                   'ending_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $session->year       = $request->year;
            // $session->title      = $request->title;
            $session->starting_date      = $request->starting_date;
            $session->ending_date      = $request->ending_date;
            $session->save();

            return redirect()->route('session.index')->with( 'Session has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }


    public function destroy(Session $session)
    {
        // if(\Auth::user()->can('delete session'))
        // {
            $session->delete();

            return redirect()->route('session.index')->with('success', 'Session has been deleted successfully.' );
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }


    }
    public function updateSessionStatus(Request $request, $sessionId)
    {
        $session = Session::findOrFail($sessionId);
        $existingActiveSession = Session::
            // where('year', $session->year)
            where('active_status', 1)
            ->where('id', '!=', $sessionId)
            ->exists();

        if ($existingActiveSession && $request->input('active_status') == 1) {
            return redirect()->back()->with('error', 'Session is deactivated but you try to reactivate the session.');
        }
        $session->active_status = $request->input('active_status');
        $session->save();
        return redirect()->back()->with('success', 'Session status updated successfully.');
    }

}
