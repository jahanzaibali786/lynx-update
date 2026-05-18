<?php

namespace App\Http\Controllers;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\StudentEnrollments;
use App\Models\User;
use Auth;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if(\Auth::user()->can('manage session'))
        // {

        $sections = Section::where('created_by', Auth::user()->creatorId())->paginate(25);
        return view('students.section.index', compact('sections'));
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
        return view('students.section.create');
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
            $request->all(),
            [
                'name' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $session = new Section();
        $session->name = $request->name;
        $session->owned_by = \Auth::user()->ownedId();
        $session->created_by = \Auth::user()->creatorId();
        $session->save();

        return redirect()->route('section.index')->with('Section has been created successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $studentEnrollments = \App\Models\StudentEnrollments::findOrFail($id);

        $classsections = \App\Models\ClassSection::where('created_by', Auth::user()->creatorId())
            ->where('class_id', $studentEnrollments->class_id)
            ->pluck('section_id');

        $sections = \App\Models\Section::whereIn('id', $classsections)->get();
        $sectionHistory = \App\Models\SectionHistory::where('student_id', $studentEnrollments->regId)->get()->sortByDesc('date');
        // dd($id);
        return view('students.section.show', compact('studentEnrollments', 'sections', 'sectionHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        // if(\Auth::user()->can('edit session'))
        // {

        return view('students.section.edit', compact('section'));
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
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Section $section)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $section->name = $request->name;
        $section->save();

        return redirect()->route('section.index')->with('Section has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section $section)
    {
        // if(\Auth::user()->can('delete session'))
        // {
        $section->delete();

        return redirect()->route('section.index')->with('success', 'Section has been deleted successfully.');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }
    // section chanfge 
    public function changeSection(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'date' => 'required|date',
                'section' => 'required|exists:sections,id',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->messages()->first());
        }

        DB::beginTransaction();

        try {
            $studentEnrollments = \App\Models\StudentEnrollments::where('regId', $id)->firstOrFail();

            // Save section history
            $sectionHistory = new \App\Models\SectionHistory();
            $sectionHistory->student_id = $id;
            $sectionHistory->section_id = $request->section;
            $sectionHistory->class_id = $studentEnrollments->class_id;
            $sectionHistory->previous_section_id = $studentEnrollments->section_id;
            $sectionHistory->date = $request->date;
            $sectionHistory->changed_by = Auth::id();
            $sectionHistory->owned_by = Auth::user()->ownedId();
            $sectionHistory->created_by = Auth::user()->creatorId();
            $sectionHistory->save();

            // Update enrollment
            $studentEnrollments->section_id = $request->section;
            $studentEnrollments->save();

            // Save student history
            $studentHistory = new \App\Models\StudentHistory();
            $studentHistory->reg_id = $id;
            $studentHistory->student_id = $studentEnrollments->enrollId;
            $studentHistory->event_type = 'section';
            $studentHistory->from_session_id = $studentEnrollments->session_id;
            $studentHistory->from_class_id = $studentEnrollments->class_id;
            $studentHistory->from_section_id = $sectionHistory->previous_section_id;
            $studentHistory->from_branch_id = $studentEnrollments->branch_id;
            $studentHistory->to_session_id = $studentEnrollments->session_id;
            $studentHistory->to_class_id = $studentEnrollments->class_id;
            $studentHistory->to_section_id = $sectionHistory->section_id;
            $studentHistory->to_branch_id = $studentEnrollments->branch_id;
            $studentHistory->effective_date = $request->date;
            $studentHistory->remarks = 'Section changed from '
                . optional($sectionHistory->previousSection)->name
                . ' to '
                . optional($sectionHistory->section)->name
                . ' on '
                . $request->date;
            $studentHistory->user_id = Auth::id();
            $studentHistory->owned_by = Auth::user()->ownedId();
            $studentHistory->created_by = Auth::user()->creatorId();
            $studentHistory->save();

            DB::commit();

            return redirect()->back()->with('success', 'Section has been changed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Section Change Error: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
    public function bulksectionindex(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', \Auth::user()->creatorId())
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', \Auth::user()->ownedId())
                ->pluck('name', 'id');
        }

        $branches->prepend('Select Branch', '');

        $students = [];
        $classes = collect();
        $sections = collect();

        if (!empty($request->branches)) {
            $classes = Classes::where('owned_by', $request->branches)
                ->pluck('name', 'id');
        }
        $classes->prepend('Select Class', '');

        if (!empty($request->class_id)) {
            $students = StudentEnrollments::with(['StudentRegistration', 'class', 'section'])
                ->where('class_id', $request->class_id)
                ->get();

            $sections = ClassSection::with('sectionName')
                ->where('class_id', $request->class_id)
                ->get()
                ->pluck('sectionName.name', 'section_id');
        }

        return view('students.section.bulkindex', compact('branches', 'classes', 'students', 'sections'));
    }
    public function bulksectionupdate(Request $request)
    {
        // Validate array structure
        $validator = \Validator::make($request->all(), [
            'studentData' => 'required|array',
            'studentData.*.enroll_id' => 'required|exists:student_enrollments,regId',
            'studentData.*.section_id' => 'required|exists:sections,id',
            'studentData.*.date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()->first()
            ], 422);
        }

        \DB::beginTransaction();

        try {

            foreach ($request->studentData as $data) {

                $studentEnrollment = StudentEnrollments::where('regId', $data['enroll_id'])->first();

                if ($studentEnrollment) {
                    $studentEnrollment->section_id = $data['section_id'];
                    $studentEnrollment->save();
                    // Save section history
                    $sectionHistory = new \App\Models\SectionHistory();
                    $sectionHistory->student_id = $data['enroll_id'];
                    $sectionHistory->class_id = $studentEnrollment->class_id;
                    $sectionHistory->previous_section_id = $studentEnrollment->section_id;
                    $sectionHistory->section_id = $data['section_id'];
                    $sectionHistory->date = $data['date'];
                    $sectionHistory->save();
                    // Save student history
                    $studentHistory = new \App\Models\StudentHistory();
                    $studentHistory->reg_id = $data['enroll_id'];
                    $studentHistory->student_id = $studentEnrollment->enrollId;
                    $studentHistory->event_type = 'section';
                    $studentHistory->from_session_id = $studentEnrollment->session_id;
                    $studentHistory->from_class_id = $studentEnrollment->class_id;
                    $studentHistory->from_section_id = $sectionHistory->previous_section_id ?? $studentEnrollment->section_id ?? null;
                    $studentHistory->from_branch_id = $studentEnrollment->branch_id;
                    $studentHistory->to_session_id = $studentEnrollment->session_id;
                    $studentHistory->to_class_id = $studentEnrollment->class_id;
                    $studentHistory->to_section_id = $sectionHistory->section_id;
                    $studentHistory->to_branch_id = $studentEnrollment->branch_id;
                    $studentHistory->effective_date = $data['date'];
                    $studentHistory->remarks = 'Section changed from '
                        . optional($sectionHistory->previousSection)->name
                        . ' to '
                        . optional($sectionHistory->section)->name
                        . ' on '
                        . $data['date'];
                    $studentHistory->user_id = Auth::id();
                    $studentHistory->owned_by = Auth::user()->ownedId();
                    $studentHistory->created_by = Auth::user()->creatorId();
                    $studentHistory->save();
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sections updated successfully'
            ]);

        } catch (\Exception $e) {

            \DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
