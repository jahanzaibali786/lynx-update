<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Challans;
use App\Models\StudentFeeStructure;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                $sectionsFrom = DB::table('class_sections')
                    ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                    ->where('class_sections.class_id', $request->class_from)
                    ->pluck('sections.name', 'sections.id')->toArray();
            }
            if ($request->filled('class_to')) {
                $sectionsTo = DB::table('class_sections')
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

                $students = $query->get()->map(function ($student) {
                    $student->latest_billing = Challans::where('student_id', $student->regId)
                        ->orderByDesc('fee_month')
                        ->orderByDesc('id')
                        ->first();

                    return $student;
                });
            }

            return view('students.student_transfer.bulk_transfer.index', compact('sessions', 'branches', 'classesFrom', 'classesTo', 'sectionsFrom', 'sectionsTo', 'students'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
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
                'transfer_billing' => 'nullable|boolean',
                'billing_month' => 'nullable|date_format:Y-m',
            ]);

            $studentIds = $request->input('student_ids');
            $branchTo = $request->input('branch_to');
            $classTo = $request->input('class_to');
            $sectionTo = $request->input('section_to');
            $sessionTo = $request->input('session_to');
            $transferBilling = $request->boolean('transfer_billing');
            $allowBillingTransfer = $transferBilling && Auth::user()->type === 'company';
            $billingMonth = $request->input('billing_month');

            if ($allowBillingTransfer) {
                $currentYear = Carbon::now()->year;
                $parsedMonth = Carbon::createFromFormat('Y-m', $billingMonth);
                $disabledMonths = [$currentYear . '-06', $currentYear . '-07'];

                if ((int) $parsedMonth->year !== $currentYear || in_array($parsedMonth->format('Y-m'), $disabledMonths, true)) {
                    return redirect()->back()->with('error', __('Only current year months are allowed and June/July are disabled.'));
                }

                $billingMonth = $parsedMonth->startOfMonth()->toDateString();
            }

            foreach ($studentIds as $enrollId) {
                $enrollment = StudentEnrollments::find($enrollId);

                if ($enrollment) {
                    $regId = $enrollment->regId;

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

                    StudentRegistration::where('id', $regId)->update([
                        'class_id' => $classTo,
                        'owned_by' => $branchTo,
                    ]);

                    $this->transferStudentFeeStructure($regId, $branchTo, $classTo);

                    StudentEnrollments::where('id', $enrollment->id)->update([
                        'owned_by' => $branchTo,
                        'class_id' => $classTo,
                        'section_id' => $sectionTo,
                        'session_id' => $sessionTo,
                    ]);

                    if ($allowBillingTransfer) {
                        $this->transferStudentBilling($regId, $branchTo, $classTo, $billingMonth);
                    }

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
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    protected function transferStudentFeeStructure(int $studentId, int $branchTo, int $classTo): void
    {
        StudentFeeStructure::where('reg_id', $studentId)
            ->update([
                'class_id' => $classTo,
                'branch_id' => $branchTo,
                'owned_by' => $branchTo,
            ]);
    }
    protected function transferStudentBilling(int $studentId, int $branchTo, int $classTo, string $billingMonth): void
    {
        $challans = Challans::where('student_id', $studentId)
            ->whereDate('fee_month', $billingMonth)
            ->whereNotIn('challan_type', ['Transfer', 'Withdrawal','Admission'])
            ->get();

        foreach ($challans as $challan) {
            $challan->owned_by = $branchTo;
            $challan->class_id = $classTo;
            $challan->save();

            if ($challan->voucher_id) {
                JournalEntry::where('id', $challan->voucher_id)->update([
                    'owned_by' => $branchTo,
                ]);

                JournalItem::where('journal', $challan->voucher_id)->update([
                    'branch_id' => $branchTo,
                ]);
            }
        }
    }
}
