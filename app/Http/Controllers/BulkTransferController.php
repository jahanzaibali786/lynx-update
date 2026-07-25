<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkTransferController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage student') || Auth::user()->type == 'company') {
            $sessions = Session::where('created_by', Auth::user()->creatorId())->pluck('year', 'id');
            $userCreatorId = Auth::user()->creatorId();
            $userType = Auth::user()->type;

            if ($userType == 'company') {
                $branches = User::where('type', 'branch')
                    ->where('created_by', $userCreatorId)
                    ->where('is_active', 1)
                    ->pluck('name', 'id');
                $branches->prepend(Auth::user()->name, Auth::user()->id);
            } else {
                $branches = User::where('id', Auth::user()->ownedId())
                    ->where('is_active', 1)
                    ->pluck('name', 'id');
            }

            $classesFrom = [];
            $classesTo = [];
            $sectionsFrom = [];
            $sectionsTo = [];

            if ($request->filled('branch_from')) {
                $classesFrom = Classes::where('owned_by', $request->branch_from)->pluck('name', 'id')->toArray();
            }
            if ($request->filled('branch_to')) {
                $classesTo = Classes::where('owned_by', $request->branch_to)->pluck('name', 'id')->toArray();
            }
            if ($request->filled('class_from')) {
                $sectionsFrom = \Illuminate\Support\Facades\DB::table('class_sections')
                    ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                    ->where('class_sections.class_id', $request->class_from)
                    ->pluck('sections.name', 'sections.id')->toArray();
            }
            if ($request->filled('class_to')) {
                $sectionsTo = \Illuminate\Support\Facades\DB::table('class_sections')
                    ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                    ->where('class_sections.class_id', $request->class_to)
                    ->pluck('sections.name', 'sections.id')->toArray();
            }

            $students = [];

            if ($request->has('search')) {
                $query = StudentEnrollments::with(['StudentRegistration', 'class', 'section', 'session'])
                    ->where('created_by', Auth::user()->creatorId())
                    ->where('active_status', 1);

                if ($request->filled('session_from')) {
                    $query->where('session_id', $request->session_from);
                }
                if ($request->filled('branch_from')) {
                    $query->where('owned_by', $request->branch_from);
                }
                if ($request->filled('class_from')) {
                    $query->where('class_id', $request->class_from);
                }
                if ($request->filled('section_from')) {
                    $query->where('section_id', $request->section_from);
                }

                $students = $query->get();
            }

            return view('students.student_transfer.bulk_transfer.index', compact('sessions', 'branches', 'classesFrom', 'classesTo', 'sectionsFrom', 'sectionsTo', 'students'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function processTransfer(Request $request)
    {
        if (Auth::user()->can('manage student') || Auth::user()->type == 'company') {
            $request->validate([
                'student_ids' => 'required|array',
                'student_ids.*' => 'integer',
                'session_to' => 'required|integer',
                'branch_to' => 'required|integer',
                'class_to' => 'required|integer',
                'section_to' => 'required|integer',
            ]);

            $studentIds = $request->input('student_ids');
            $branchTo = $request->input('branch_to');
            $classTo = $request->input('class_to');
            $sectionTo = $request->input('section_to');
            $sessionTo = $request->input('session_to');

            foreach ($studentIds as $enrollId) {
                $enrollment = StudentEnrollments::find($enrollId);

                if ($enrollment) {
                    $regId = $enrollment->regId;

                    // 1. Create Student Transfer Record
                    $studentTransfer = new StudentTransfer();
                    $studentTransfer->student_id = $regId;
                    $studentTransfer->class_id = $enrollment->class_id;
                    $studentTransfer->challan_id = 0;
                    $studentTransfer->transfer_date = date('Y-m-d');
                    $studentTransfer->transfer_type = 'bulk';
                    $studentTransfer->branch_from = $enrollment->owned_by;
                    $studentTransfer->class_from = $enrollment->class_id;
                    $studentTransfer->section_from = $enrollment->section_id;
                    $studentTransfer->branch_to = $branchTo;
                    $studentTransfer->class_to = $classTo;
                    $studentTransfer->section_to = $sectionTo;
                    $studentTransfer->reason = 'Bulk Student Transfer';
                    $studentTransfer->status = 'approved';
                    $studentTransfer->session_id = $sessionTo;
                    $studentTransfer->owned_by = Auth::user()->creatorId();
                    $studentTransfer->created_by = Auth::user()->creatorId();
                    $studentTransfer->save();

                    // 2. Update Student Registration
                    StudentRegistration::where('id', $regId)
                        ->update([
                            'class_id' => $classTo,
                            'owned_by' => $branchTo,
                        ]);

                    // 3. Update Student Enrollments
                    StudentEnrollments::where('id', $enrollment->id)
                        ->update([
                            'owned_by' => $branchTo,
                            'class_id' => $classTo,
                            'section_id' => $sectionTo,
                            'session_id' => $sessionTo,
                        ]);

                    // 4. Add Student History
                    $his = new StudentHistory();
                    $his->reg_id = $regId;
                    $his->student_id = $enrollment->enrollId;
                    $his->event_type = 'transfer';
                    $his->from_session_id = $enrollment->session_id;
                    $his->from_class_id = $enrollment->class_id;
                    $his->to_class_id = $classTo;
                    $his->from_section_id = $enrollment->section_id;
                    $his->to_section_id = $sectionTo;
                    $his->from_branch_id = $enrollment->owned_by;
                    $his->to_branch_id = $branchTo;
                    $his->to_session_id = $sessionTo;
                    $his->effective_date = date('Y-m-d');
                    $his->remarks = 'Bulk Student Transfer';
                    $his->owned_by = Auth::user()->creatorId();
                    $his->created_by = Auth::user()->creatorId();
                    $his->save();
                }
            }

            return redirect()->route('bulk-transfer.index')->with('success', __('Students successfully transferred.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
