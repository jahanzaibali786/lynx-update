<?php

namespace App\Http\Controllers;
use DB;
use Auth;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\Section;
use App\Models\StudentRegistration;

use App\Models\User;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       // if(\Auth::user()->can('manage session'))
        // {

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by',\Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Classes::with('classSectionAll')->where('created_by', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Classes::with('classSectionAll')->where('owned_by', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $classes = $query->get();
            // $classes = Classes::with('classSectionAll')->where('created_by',Auth::user()->creatorId())->get();
            return view('students.class.index', compact('classes','branches'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if(\Auth::user()->can('create session'))
        // {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by',\Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                // $branches->prepend('Select Branch', '');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                // $branches->prepend('Select Branch', '');
            }
            $grades = array(
                "DAYCARE" => "DAYCARE",
                "PLAY GROUP" => "PLAY GROUP",
                "PRE-NURSERY" => "PRE-NURSERY",
                "NURSERY" => "NURSERY",
                "KG" => "KG",
                "EXTRA CARE/AFTER SCHOOL" => "EXTRA CARE/AFTER SCHOOL",
                "GRADE-1" => "GRADE-1",
                "GRADE-2" => "GRADE-2",
                "GRADE-3" => "GRADE-3",
                "GRADE-4" => "GRADE-4",
                "GRADE-5" => "GRADE-5",
                "GRADE-6" => "GRADE-6",
                "GRADE-7" => "GRADE-7",
                "MATRIC-8" => "MATRIC-8",
                "MATRIC-9" => "MATRIC-9",
                "MATRIC-10" => "MATRIC-10",
                "IGCSE-8" => "IGCSE-8",
                "IGCSE-9" => "IGCSE-9",
                "IGCSE-10" => "IGCSE-10"
            );
            $sections = Section::where('created_by',Auth::user()->creatorId())->get();
            return view('students.class.create',compact('sections','branches','grades'));
        // }
        // else
        // {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            // if(\Auth::user()->can('create session'))
            // {
                $validator = \Validator::make(
                    $request->all(), [
                                    'name' => 'required',
                                    'branch_id' => 'required',
                                ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $class             = new Classes();
                $class->name       = strtoupper($request->name);
                $class->owned_by   = $request->branch_id;
                $class->created_by = \Auth::user()->creatorId();
                $class->save();
                for($i=0; $i<count($request->section); $i++){
                    $class_session             = new ClassSection();
                    $class_session->class_id       = $class->id;
                    $class_session->section_id       = $request->section[$i];
                    $class_session->owned_by   =  $request->branch_id;
                    $class_session->created_by = \Auth::user()->creatorId();
                    $class_session->save();
                }
                return redirect()->route('classes.index')->with('Class has been created successfully');
            // }
            // else
            // {
            //     return redirect()->back()->with('error', 'Permission denied.');
            // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function show(Classes $classes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function edit(Classes $classes,$id)
    {
        // if(\Auth::user()->can('edit session'))
        // {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by',\Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                // $branches->prepend('Select Branch', '');
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            $classes = Classes::where('id',$id)->first();
            $class_sec = ClassSection::where('class_id',$classes->id)->pluck('section_id')->toArray();
            $sections = Section::where('created_by',Auth::user()->creatorId())->get();
            $grades = array(
                "DAYCARE" => "DAYCARE",
                "PLAY GROUP" => "PLAY GROUP",
                "PRE-NURSERY" => "PRE-NURSERY",
                "NURSERY" => "NURSERY",
                "KG" => "KG",
                "EXTRA CARE/AFTER SCHOOL" => "EXTRA CARE/AFTER SCHOOL",
                "GRADE-1" => "GRADE-1",
                "GRADE-2" => "GRADE-2",
                "GRADE-3" => "GRADE-3",
                "GRADE-4" => "GRADE-4",
                "GRADE-5" => "GRADE-5",
                "GRADE-6" => "GRADE-6",
                "GRADE-7" => "GRADE-7",
                "MATRIC-8" => "MATRIC-8",
                "MATRIC-9" => "MATRIC-9",
                "MATRIC-10" => "MATRIC-10",
                "IGCSE-8" => "IGCSE-8",
                "IGCSE-9" => "IGCSE-9",
                "IGCSE-10" => "IGCSE-10"
            );
            return view('students.class.edit', compact('branches','classes','sections','class_sec','grades'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Classes $classes,$id)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(), [
                               'name' => 'required',
                               'branch_id' => 'required',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        DB::beginTransaction();
        try {
            $class = Classes::find($id);
            $class->name = strtoupper($request->name);
            $class->owned_by   =  $request->branch_id;
            $class->save();
            // dd($request->section);
                $originalsectionIds = ClassSection::where('class_id', $class->id)->pluck('section_id')->toArray();
                foreach ($request->section as $section) {
                    $existingSection = ClassSection::where('class_id', $class->id)
                    ->where('section_id', $section)->first();

                    if ($existingSection) {
                        // Updating existing record
                    } else {
                        // for($i=0; $i<count($request->section); $i++){
                            $class_session             = new ClassSection();
                            $class_session->class_id       = $class->id;
                            $class_session->section_id       = $section;
                            $class_session->active_status = $request->status;
                            $class_session->owned_by   = \Auth::user()->ownedId();
                            $class_session->created_by = \Auth::user()->creatorId();
                            $class_session->save();
                        // }
                    }
                }
                $removedSectionIds = array_diff($originalsectionIds, $request->section);
                // dd($removedSectionIds);
                ClassSection::where('class_id', $class->id)->whereIn('section_id', $removedSectionIds)->delete();
                DB::commit();
                return redirect()->route('classes.index')->with( 'Class has been updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
        }
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Classes  $classes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Classes $classes ,$id)
    {
       // if(\Auth::user()->can('delete session'))
        // {
            try {
                // Delete associated class sections
                $std = StudentRegistration::where('class_id', $id)->first();
                if($std){
                    return redirect()->route('classes.index')->with('error', 'This  class is associated with student, cannot delete.');
                }else{
                    $classsec = Classes::where('id', $id)->first();
                    $classsection = ClassSection::where('class_id', $classsec->id)->delete();
                    // Delete the class
                    $classsec->delete();
                }
                return redirect()->route('classes.index')->with('success', 'Class has been deleted successfully.');
            } catch (\Exception $e) {
                return redirect()->route('classes.index')->with('error', 'Error deleting class: ' . $e->getMessage());
            }
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }
}
