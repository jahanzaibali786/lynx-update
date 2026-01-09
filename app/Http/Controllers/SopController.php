<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sop;
class SopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sops = Sop::get();
        return view('sop.index', compact('sops'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sop.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
    [
                'sop_title' =>'required',
                'sop_type' =>'required',
                'description' =>'required',
                'date' =>'required',
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $sop = new Sop();
        $sop->sop_title = $request->sop_title;
        $sop->sop_type = $request->sop_type;
        $sop->sop_date = $request->date;
        $sop->sop_description = $request->description;
        $sop->owned_by = \Auth::user()->ownedId();
        $sop->created_by = \Auth::user()->creatorId();
        $sop->save();
        return redirect()->route('sops.index')->with('success', 'SOP created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sop = Sop::find($id);
        return view('sop.edit', compact('sop'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
               'sop_title' =>'required',
               'sop_type' =>'required',
               'sop_description' =>'required',
               'sop_date' =>'required',
            ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $sop = Sop::find($id);
        $sop->sop_title = $request->sop_title;
        $sop->sop_type = $request->sop_type;
        $sop->sop_date = $request->sop_date;
        $sop->sop_description = $request->sop_description;
        $sop->save();
        return redirect()->route('sops.index')->with('success', 'SOP updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Sop::find($id)->delete();
        return redirect()->route('sops.index')->with('success', 'SOP deleted successfully.');
    }
}
