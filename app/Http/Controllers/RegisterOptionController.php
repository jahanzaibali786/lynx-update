<?php

namespace App\Http\Controllers;

use App\Models\Registring_option;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $registeroptions = Registring_option::where('created_by',Auth::user()->creatorId())->paginate(25);
        return view('students.resgiteroption.list',compact('registeroptions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // dd('');
        // if (\Auth::user()->type == 'company') {
        //     $branch = User::where('type', '=', 'branch')->get()->where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
        //     $branch->prepend(\Auth::user()->name, \Auth::user()->id);
        // } else {
        //     $branch = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        // }
        return view('students.resgiteroption.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'discount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            // dd($request->all());
            $registeroption = new Registring_option();
            $registeroption->name=strtoupper($request->name);
            $registeroption->discount=$request->discount;
            $registeroption->owned_by = \Auth::user()->ownedId();
            $registeroption->created_by = \Auth::user()->creatorId();
            $registeroption->save();

            DB::commit();
            return redirect()->route('registerOption.index')->with('success', 'RegisterOption Successfull.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error occurred while saving RegisterOption: ' . $e->getMessage());
        }
        // dd($request->all());

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
        $registerOption = Registring_option::findOrFail($id);
        // Only company can edit TEACHER CHILD
        $isTeacherChild = strtoupper($registerOption->name) === 'TEACHER CHILD';
        if ($isTeacherChild && \Auth::user()->type !== 'company') {
            return redirect()->back()->with('error', 'Permission denied. Only company can edit Teacher Child option.');
        }
        return view('students.resgiteroption.edit', compact('registerOption'));
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
        $registeroption = Registring_option::findOrFail($id);
        $isTeacherChild = strtoupper($registeroption->name) === 'TEACHER CHILD';

        $rules = [
            'discount' => 'required|integer',
        ];
        if (!$isTeacherChild) {
            $rules['name'] = 'required|string';
        }
        if ($isTeacherChild && \Auth::user()->type !== 'company') {
            return redirect()->back()->with('error', 'Permission denied. Only company can edit Teacher Child option.');
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            if (!$isTeacherChild) {
                $registeroption->name = strtoupper($request->name);
            }
            $registeroption->discount = $request->discount;
            $registeroption->save();
            DB::commit();
            return redirect()->route('registerOption.index')->with('success', 'RegisterOption Updated Successfull.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error occurred while saving RegisterOption: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
