<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use App\Models\EmpChildrens;
use App\Models\FeeHead;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeStructure;
use App\Models\StudentRegistration;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class ConcessionController extends Controller
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
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = Concession::with('student', 'concession')->where('created_by', Auth::user()->creatorId());

            $students = StudentRegistration::select('id', 'roll_no', 'stdname', 'fathername', 'student_status')
            ->where('created_by', \Auth::user()->creatorId())
            ->whereIn('student_status', ['Enrolled', 'Registered'])
            ->where('active_status', 1)
            ->get()
            ->mapWithKeys(function ($student) {
                if ($student->student_status == 'Enrolled') {
                    return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
                } else {
                    return [$student->id => $student->stdname . ' s/d/o ' . $student->fathername];
                }
            });

            $students->prepend('All Students', 'all');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            // $branches->prepend('Select Branch', '');
            $query = Concession::with('student', 'concession')->where('owned_by', '=', \Auth::user()->ownedId());

            $students = StudentRegistration::select('id', 'roll_no', 'stdname', 'fathername', 'student_status')
            ->where('owned_by', '=', \Auth::user()->ownedId())
            ->whereIn('student_status', ['Enrolled', 'Registered'])
            ->where('active_status', 1)
            ->get()
            ->mapWithKeys(function ($student) {
                if ($student->student_status == 'Enrolled') {
                    return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
                } else {
                    return [$student->id => $student->stdname . ' s/d/o ' . $student->fathername];
                }
            });

            $students->prepend('All Students', 'all');
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);

            $students = StudentRegistration::select('id', 'roll_no', 'stdname', 'fathername', 'student_status')
            ->where('owned_by', '=', $request->branches)
            ->whereIn('student_status', ['Enrolled', 'Registered'])
            ->where('active_status', 1)
            ->get()
            ->mapWithKeys(function ($student) {
                if ($student->student_status == 'Enrolled') {
                    return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
                } else {
                    return [$student->id => $student->stdname . ' s/d/o ' . $student->fathername];
                }
            });

            $students->prepend('All Students', 'all');
        }
        if (!empty($request->status)) {
            $query->where('status', '=', $request->status);
        }else{
            $query->whereIn('status', ['For Approval','Draft']);
        }
        if (!empty($request->student) && $request->student != 'all') {
            $query->where('student_id', '=', $request->student);
        }
        if (!empty($request->start_date)) {
            $query->whereDate('start_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('end_date', '<', $request->end_date);
        }
        if (empty($request->start_date) && empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            // $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $query->whereDate('start_date', '>', $request->start_date);
        }
        $concessions = $query->orderBy('id', 'Desc')->get();

        /*
         * Presentation-only date format.
         * Database values and filter inputs remain Y-m-d.
         */
        $concessions->each(function ($concession) {
            $this->formatConcessionDatesForPresentation($concession);
        });

        $status = [
            '' => 'Select Status',
            'Draft' => 'Draft',
            'For Approval' => 'For Approval',
            'Approved' => 'Approved',
            'Rollbacked' => 'Rollback',
            'Canceled' => 'Canceled',
            'Rejected' => 'Rejected',
        ];
        
        return view('students.concession.index', compact('concessions', 'status', 'branches', 'students', 'request'));
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
    public function create(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);

            $classes = Classes::where('owned_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id');

            $classes = Classes::where('owned_by', '=', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id');
        }

        $concession_policy = ConcessionPolicy::where(
            'created_by',
            '=',
            \Auth::user()->creatorId()
        )->get()->pluck('title', 'id');

        $concession_policy->prepend('Select Concession Policy', '');
        $classes->prepend('Select Class', '');

        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();

        // Readmission popup can pre-fill and lock branch/class/student.
        $fromReadmission = $request->boolean('from_readmission');
        $selectedBranchId = $request->input('branch_id');
        $selectedClassId = $request->input('class_id');
        $selectedStudentId = $request->input('student_id');

        if ($selectedBranchId && ! $branches->has($selectedBranchId)) {
            $branch = User::find($selectedBranchId);
            if ($branch) {
                $branches->put($branch->id, $branch->name);
            }
        }

        if ($selectedClassId && ! $classes->has($selectedClassId)) {
            $class = Classes::find($selectedClassId);
            if ($class) {
                $classes->put($class->id, $class->name);
            }
        }

        $students = collect();
        if ($selectedStudentId) {
            $student = StudentRegistration::find($selectedStudentId);
            if ($student) {
                $students->put(
                    $student->id,
                    ($student->roll_no ? $student->roll_no.' - ' : '')
                    .$student->stdname
                    .' s/d/o '
                    .$student->fathername
                );
            }
        }

        $readmissionContext = [
            'enabled' => $fromReadmission,
            'branch_id' => $selectedBranchId,
            'class_id' => $selectedClassId,
            'student_id' => $selectedStudentId,
            'roll_no' => $request->input('readmission_roll_no'),
            'period_from' => $request->input('period_from') ?: date('Y-m-d'),

            /*
             * Billing Month (Effective From) is a month/year field in UI.
             * Prefer an explicitly supplied effective_from; otherwise use the
             * Period From month for Readmission, or the current month.
             */
            'effective_from' => $request->input('effective_from')
                ?: Carbon::parse(
                    $request->input('period_from') ?: date('Y-m-d')
                )->format('Y-m'),

            // Readmission concession applications default to Withdrawal type.
            'concession_type' => $fromReadmission
                ? 'withdrawal'
                : $request->input('concession_type'),
        ];

        return view(
            'students.concession.create',
            compact(
                'branches',
                'concession_policy',
                'classes',
                'heads',
                'students',
                'readmissionContext'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            /*
             * Readmission popup:
             * even if the frontend does not explicitly submit concession_type,
             * backend will save this application as Withdrawal concession.
             */
            if (
                $request->boolean('from_readmission')
                && !$request->filled('concession_type')
            ) {
                $request->merge([
                    'concession_type' => 'withdrawal',
                ]);
            }

            $validator = \Validator::make(
                $request->all(),
                [
                    'concession_id' => 'required',
                    'class_id' => 'required',
                    'student_id' => 'required',
                    'concession_type' => 'required|in:regular,registration,withdrawal',
                    'effective_from' => 'required|date_format:Y-m',
                    'period_from' => 'required|date',
                    'period_to' => 'nullable|date',
                ]
            );

            if ($validator->fails()) {
                DB::rollBack();

                if ($request->boolean('from_readmission')) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->getMessageBag()->first(),
                    ], 422);
                }

                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $std = StudentEnrollments::where('regId', $request->student_id)->first();

            $concession = new Concession();
            $concession->student_id = $request->student_id;
            $concession->class_id = $request->class_id;
            $concession->concession_order = $this->concession_order();
            $concession->concession_id = $request->concession_id;
            $concession->concession_by = \Auth::user()->name;
            $concession->type = $this->concessionTypeToInt($request->concession_type);
            $concession->session_id = optional($std)->session_id;
            $concession->apply_date = date('Y-m-d');

            /*
             * Store the selected billing month as the first day of that month.
             * Example: 2026-09 -> 2026-09-01
             */
            $concession->effective_from =
                $this->normalizeEffectiveMonth(
                    $request->effective_from
                );
            
            $concession->start_date = $request->period_from;
            $concession->end_date = $request->period_to;
            $concession->remarks = $request->bill_remarks;
            $concession->owned_by = $request->branch_id;
            $concession->created_by = \Auth::user()->creatorId();

            if ($request->cancle_date != null || $request->cancle_remarks) {
                $prev_con = Concession::with('concession')
                    ->where('student_id', $request->student_id)
                    ->where('status', 'Approved')
                    ->orderByDesc('id')
                    ->first();

                if ($prev_con) {
                    $prev_con->cancel_date = $request->cancle_date;
                    $prev_con->cancel_remarks = $request->cancle_remarks;
                    $prev_con->save();
                }
            }

            if (Auth::user()->type == 'company') {
                $concession->status = 'Approved';
                $concession->active_status = 1;
                $concession->approved_by = \Auth::user()->name;
                $concession->approval_date = date('Y-m-d');
            } elseif ($request->boolean('from_readmission')) {
                // Readmission popup applications from branch go straight for approval.
                $concession->status = 'For Approval';
                $concession->active_status = 0;
            }

            $concession->save();

            /*
             * Company-created concessions are approved immediately.
             * Capture the historical applied fee snapshot once, after the
             * concession has an ID. Never recalculate an existing snapshot.
             */
            if (
                strtolower((string) $concession->status) === 'approved'
                && empty($concession->approval_snapshot)
            ) {
                $concession->approval_snapshot =
                    $this->buildConcessionApprovalSnapshot($concession);

                $concession->save();
            }

            $concession->loadMissing('concession');

            DB::commit();

            if ($request->boolean('from_readmission')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Concession application has been created successfully.',
                    'policy' => [
                        'id' => $concession->id,
                        'policy_title' => optional($concession->concession)->title,
                        'status' => $concession->status,
                        'active_status' => $concession->active_status,
                        'apply_date' => $this->displayDate($concession->apply_date),
                        'effective_from' => $this->displayDate($concession->effective_from),
                        'start_date' => $this->displayDate($concession->start_date),
                        'end_date' => $this->displayDate($concession->end_date),
                        'approval_date' => $this->displayDate($concession->approval_date),
                    ],
                ]);
            }

            return redirect()->route('concession.index')
                ->with('success', 'Concession has been created successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->boolean('from_readmission')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function concession_order()
    {
        $concession = Concession::where('created_by', Auth::user()->creatorId())->orderBy('id', 'desc')->first();
        return $concession ? $concession->concession_order + 1 : 1;

    }
    public function show(Concession $Concession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function edit(Concession $Concession)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $concession_policy = ConcessionPolicy::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $classes = Classes::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $student = StudentRegistration::where('class_id', $Concession->class_id)->pluck('stdname', 'id');
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$Concession->class_id)->pluck('student_registrations.stname','student_registrations.id');
        return view('students.concession.edit', compact('Concession', 'branches', 'concession_policy', 'classes', 'student', 'heads'));
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
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Concession $Concession)
    {
        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'concession_id' => 'required',
                'class_id' => 'required',
                'student_id' => 'required',
                'concession_type' => 'nullable|in:regular,registration,withdrawal,0,1,2',
                'effective_from' => 'nullable|date_format:Y-m',
                // 'date' => 'required|date',
                'period_from' => 'required|date',
                'period_to' => 'nullable|date',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $Concession->student_id = $request->student_id;
        $Concession->class_id = $request->class_id;
        $Concession->concession_id = $request->concession_id;

        if ($request->filled('concession_type')) {
            $Concession->type =
                $this->concessionTypeToInt(
                    $request->concession_type
                );
        }

        // $Concession->apply_date = $request->date;

        if ($request->filled('effective_from')) {
            $Concession->effective_from =
                $this->normalizeEffectiveMonth(
                    $request->effective_from
                );
        }

        $Concession->start_date = $request->period_from;
        $Concession->end_date = $request->period_to;
        $Concession->remarks = $request->bill_remarks;
        $Concession->owned_by = $request->branch_id;
        if($request->cancle_date != null || $request->cancle_remarks){
            $prev_con = Concession::with('concession')
            ->where('student_id', $request->student_id)
            ->where('status', 'Approved')
            ->orderByDesc('id')
            ->first();
            if($prev_con){
                $prev_con->cancel_date = $request->cancle_date;
                $prev_con->cancel_remarks = $request->cancle_remarks;
                $prev_con->save();
            }
        }
        $Concession->save();

        return redirect()->route('concession.index')->with('success', 'Concession has been Updated successfully.');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Concession  $Concession
     * @return \Illuminate\Http\Response
     */
    public function destroy(Concession $Concession)
    {
        //
    }
public function endconcession($id)
    {
        return view('students.concession.end_concession', compact('id'));
    }

    public function updateendconcession(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'end_remarks' => 'required',
                'end_date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $concession = Concession::findOrFail($id);
        $concession->end_date = $request->end_date;
        $concession->cancel_remarks = $request->end_remarks;
        $concession->status = 'Canceled';
        $concession->active_status = 0;
        $concession->save();

        return redirect()->route('concession.index')->with('success', 'Concession ended successfully.');
    }
    public function class_student(Request $request)
    {
        $student = StudentRegistration::where('class_id', $request->class_id)->get();
        // $student = DB::table('student_enrollments')->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
        // ->where('student_enrollments.class_id',$request->class_id)->get();
        return $student;
    }


    public function changeStatus(Request $request, $id, $status)
    {
        DB::beginTransaction();

        try {
            $concession = Concession::findOrFail($id);

            $concession->status = $status;
            $concession->active_status = 0;

            if ($status == 'Approved') {
                $concession->active_status = 1;
                $concession->approved_by = Auth::user()->name;
                $concession->approval_date = date('Y-m-d');

                if ($request->filled('effective_from')) {
                    $concession->effective_from = $this->normalizeEffectiveMonth(
                        $request->effective_from
                    );
                }

                /*
                 * Persist approval state first, then capture the exact applied
                 * structure/policy snapshot. If a snapshot already exists,
                 * NEVER rebuild it from today's fee structure.
                 */
                $concession->save();

                if (empty($concession->approval_snapshot)) {
                    $concession->approval_snapshot =
                        $this->buildConcessionApprovalSnapshot(
                            $concession
                        );

                    $concession->save();
                }

                $prev_con = Concession::with('concession')
                    ->where('student_id', $concession->student_id)
                    ->where('id', '!=', $concession->id)
                    ->where('status', 'Approved')
                    ->orderByDesc('id')
                    ->first();

                if ($prev_con) {
                    $prev_con->cancel_date = date('Y-m-d');
                    $prev_con->status = 'Canceled';
                    $prev_con->active_status = 0;
                    $prev_con->cancel_remarks =
                        'New Concession Approved';
                    $prev_con->save();
                }

                DB::commit();

                return redirect()
                    ->route('concession.index')
                    ->with(
                        'success',
                        'Concession Approved Successfully.'
                    );
            }

            if ($status == 'Rejected') {
                $concession->active_status = 0;
                $concession->save();

                DB::commit();

                return redirect()
                    ->route('concession.index')
                    ->with(
                        'success',
                        'Concession Rejected Successfully.'
                    );
            }

            $concession->save();

            DB::commit();

            return redirect()
                ->route('concession.index')
                ->with(
                    'success',
                    'Concession Send For Approval Successfully.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }


    public function cancelconcession($id)
    {
        return view('students.concession.cancel_concession', compact('id'));
    }
    public function removeconcession(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'cancel_remarks' => 'required',
                'cancel_date' => 'required|date',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $concession = Concession::findOrFail($request->id);
        $concession->cancel_date = $request->cancel_date;
        $concession->cancel_remarks = $request->cancel_remarks;
        $concession->status = 'Canceled';
        $concession->active_status = 0;
        $concession->save();
        return redirect()->route('concession.index')->with('success', 'Concession Cancel Successfully.');
    }

    public function concession_student_detail(Request $request, $id)
    {
        $student = StudentRegistration::with('concession')->find($id);

        $enroll = StudentEnrollments::with('class', 'section')
            ->where('regId', $student?->id)
            ->first();

        $concession = Concession::with('concession')
            ->where('student_id', $student?->id)
            ->where('status', 'Approved')
            ->orderByDesc('id')
            ->first();
        $teacherCh = EmpChildrens::with('employee', 'employee.designation', 'employee.userbranch')
            ->where('student_id', $student?->id)
            ->first();

        // employee tenure
        $emptenure = $teacherCh?->employee
            ? $teacherCh->employee->getEmployeeTenure($teacherCh->employee->id)
            : null;

        if ($teacherCh?->employee) {
            $teacherCh->employee->tenure = $emptenure;

            $empScaleNo = $teacherCh->employee->employee_payscale_details()
                ->orderByDesc('id')
                ->first();

            $teacherCh->employee->ScaleNo = $empScaleNo?->scale ?? null;
        }
        $concessionDisplay = $concession
            ? [
                'id' => $concession->id,
                'status' => $concession->status,
                'type' => $concession->type,
                'policy_title' => optional($concession->concession)->title,
                'apply_date' => $this->displayDate($concession->apply_date),
                'start_date' => $this->displayDate($concession->start_date),
                'end_date' => $this->displayDate($concession->end_date),
                'approval_date' => $this->displayDate($concession->approval_date),
                'cancel_date' => $this->displayDate($concession->cancel_date),
            ]
            : null;

        return response([
            'data' => $student,
            'tc' => $teacherCh,
            'enroll' => $enroll,
            'class' => $enroll?->class?->name ?? 'N/A',
            'section' => $enroll?->section?->name ?? 'N/A',

            // Keep original payload for backward compatibility.
            'concession' => $concession ?? 'No Concession',

            // Use this for presentation; all dates are d-M-y.
            'concession_display' => $concessionDisplay,
        ]);
    }

    public function concession_search(Request $request)
    {
        try {
            $titleIds = $request->input('title_id');
            $concessions = $request->input('concession');

            // Build filtered data — only heads where user entered a non-zero percentage
            $filteredData = [];
            foreach ($concessions as $index => $concession) {
                if ($concession !== "" && $concession != 0) {
                    $filteredData[] = [
                        'title_id' => $titleIds[$index],
                        'concession' => $concession,
                    ];
                }
            }

            if (empty($filteredData)) {
                return response()->json(['message' => 'No data to filter'], 400);
            }

            $totalInputHeads = count($filteredData);

            // For each concession policy, count how many of the user's
            // (head_id + percentage) pairs exist in that policy's heads.
            // Order: exact full match first, then descending partial matches.
            $policies = ConcessionPolicy::join(
                'concession_policy_heads',
                'concession_policies.id',
                '=',
                'concession_policy_heads.concession_id'
            )
                ->select(
                    'concession_policies.id',
                    'concession_policies.title',
                    'concession_policies.order_no',
                    \DB::raw('SUM(CASE WHEN ' .
                        $this->buildMatchCase($filteredData) .
                        ' THEN 1 ELSE 0 END) as match_count'),
                    // Total non-zero heads defined on the policy. An exact match
                    // requires the policy to have EXACTLY the searched heads —
                    // i.e. every input head matched AND no extra non-zero head.
                    \DB::raw('SUM(CASE WHEN concession_policy_heads.percentage <> 0 THEN 1 ELSE 0 END) as nonzero_head_count')
                )
                ->groupBy('concession_policies.id', 'concession_policies.title', 'concession_policies.order_no')
                ->havingRaw('match_count > 0')
                ->orderByRaw('match_count DESC')
                ->get()
                ->map(function ($policy) use ($totalInputHeads) {
                    $policy->is_exact = ($policy->match_count == $totalInputHeads
                        && $policy->nonzero_head_count == $totalInputHeads);
                    return $policy;
                });

            return response()->json($policies);

        } catch (\Exception $e) {
            \Log::error('Error filtering policies: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while filtering policies'], 500);
        }
    }

    /**
     * Build a CASE expression that checks each (head_id, percentage) pair.
     */
    private function buildMatchCase(array $filteredData): string
    {
        $cases = [];
        foreach ($filteredData as $match) {
            $headId = (int) $match['title_id'];
            $percentage = (float) $match['concession'];
            $cases[] = "(concession_policy_heads.head_id = {$headId} AND concession_policy_heads.percentage = {$percentage})";
        }
        return implode(' OR ', $cases);
    }



    public function concessionstatus($id)
    {
        $concession = Concession::with(
            'student',
            'class',
            'concession',
            'student.enrollment',
            'branches'
        )->where('id', $id)->first();

        if ($concession) {
            $prev_concession = Concession::with(
                'concession',
                'student.enrollment'
            )
                ->where(
                    'student_id',
                    $concession->student->id
                )
                ->where('id', '!=', $concession->id)
                ->where('status', 'Approved')
                ->get();

            /*
             * Historical snapshot saved at approval.
             * Legacy records may not have one and will fall back in the view.
             */
            $concessionSnapshot =
                $this->decodeConcessionSnapshot(
                    $concession->approval_snapshot
                );

            // Presentation only: d-M-y.
            $this->formatConcessionDatesForPresentation(
                $concession
            );

            $prev_concession->each(function ($row) {
                $this->formatConcessionDatesForPresentation(
                    $row
                );
            });

            return view(
                'students.concession.status_concession',
                compact(
                    'concession',
                    'prev_concession',
                    'concessionSnapshot'
                )
            );
        }

        $concessionSnapshot = [];

        return view(
            'students.concession.status_concession',
            compact(
                'concession',
                'concessionSnapshot'
            )
        );
    }

    public function concessionrejection(Request $request, $id)
    {
        $concession = Concession::findOrFail($id);

        // Rollback and Rejection share this endpoint but submit different
        // fields: the rollback form sends `rollback_reason` + type=Rollback,
        // the rejection form sends `reject_reason` + type=Rejected.
        $isRollback = ($request->type == 'Rollback');
        $reason = $isRollback ? $request->rollback_reason : $request->reject_reason;

        if (trim((string) $reason) === '') {
            $action = $isRollback ? 'rollback' : 'reject';
            return redirect()->back()->with('error', "Please give remarks to {$action} !");
        }

        $concession->cancel_remarks = $reason;
        $concession->active_status = 0;

        if ($isRollback) {
            // Send the concession back to Draft so the branch can edit it and
            // resubmit it for approval.
            $concession->status = 'Draft';
            $concession->save();
            return redirect()->back()->with('success', 'Concession has been rolled back to Draft successfully');
        }

        $concession->status = 'Rejected';
        $concession->save();
        return redirect()->back()->with('success', 'Concession has been Rejected Successfully');
    }

    public function concessionReport(Request $request)
    {

        $query = Concession::with('student', 'concession', 'branches', 'class')
            ->where('created_by', \Auth::user()->creatorId());
        $brnches = User::where('id', $request->branches)->first();
        if (!empty($request->branches)) {
            $query->where('owned_by', $request->branches);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif (!empty($request->start_date)) {
            $query->where('start_date', '>=', $request->start_date);
        } elseif (!empty($request->end_date)) {
            $query->where('end_date', '<=', $request->end_date);
        } else {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
            $query->whereBetween('start_date', [$dateFrom, $dateTo]);
        }

        $concessions = $query->get()->groupBy('owned_by');

        /*
         * Report/PDF presentation only.
         * All concession dates shown to users use d-M-y.
         */
        $concessions->each(function ($branchRows) {
            $branchRows->each(function ($concession) {
                $this->formatConcessionDatesForPresentation($concession);
            });
        });

        /*
         * Querying is already complete, so request date filters can now be
         * converted for report/header presentation without affecting DB logic.
         */
        if (!empty($request->start_date)) {
            $request->merge([
                'start_date' => $this->displayDate($request->start_date),
            ]);
        }

        if (!empty($request->end_date)) {
            $request->merge([
                'end_date' => $this->displayDate($request->end_date),
            ]);
        }

        $branches_name = User::where('type', 'branch')->where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
        $branches_name->prepend(\Auth::user()->name, \Auth::user()->id);
        $pdf = new Dompdf();
        $html = view('students.concession.report', compact('concessions', 'branches_name', 'request'))->render();
        $headerHtml = view('students.concession.report.pdf.header', compact('concessions', 'brnches', 'request'))->render();
        $footerHtml = view('students.concession.report.pdf.footer')->render();

        $html = '<html><head>
        <style>
            @page {
                margin-top: 100px;
                margin-bottom: 100px;
            }
            .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
            .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        </style>
        </head><body>
        <div class="header">' . $headerHtml . '</div>
        <div class="footer">' . $footerHtml . '</div>
        ' . $html . '
        </body></html>';
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', true);
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->render();

        // return $dompdf->stream('concession.pdf');
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        return response()->json(['base64Pdf' => $base64Pdf]);

    }

    /**
     * Database mapping for Concession.type integer column.
     *
     * 0 = Registration
     * 1 = Regular
     * 2 = Withdrawal
     */
    private function concessionTypeToInt($type): int
    {
        if (is_numeric($type)) {
            $type = (int) $type;

            if (in_array($type, [0, 1, 2], true)) {
                return $type;
            }
        }

        return match (
            strtolower(trim((string) $type))
        ) {
            'registration' => 0,
            'withdrawal' => 2,
            'regular' => 1,
            default => 1,
        };
    }

    private function concessionTypeLabel($type): string
    {
        return match (
            $this->concessionTypeToInt($type)
        ) {
            0 => 'Registration',
            2 => 'Withdrawal',
            default => 'Regular',
        };
    }

    /**
     * Capture the exact concession values at approval time.
     *
     * StudentFeeStructure is authoritative for the student's current base
     * amount. ClassWiseFee is only a fallback when the student does not yet
     * have a fee-structure row (common for Registration concessions).
     */
    private function buildConcessionApprovalSnapshot(
        Concession $concession
    ): array {
        $concession->loadMissing(
            'student',
            'class',
            'concession',
            'branches'
        );

        $policyHeads = ConcessionPolicyHead::where(
            'concession_id',
            $concession->concession_id
        )
            ->where('percentage', '!=', 0)
            ->get();

        $heads = $policyHeads
            ->map(function ($policyHead) use ($concession) {
                $feeHead = FeeHead::find(
                    $policyHead->head_id
                );

                $studentStructure =
                    StudentFeeStructure::where(
                        'reg_id',
                        $concession->student_id
                    )
                        ->where(
                            'head_id',
                            $policyHead->head_id
                        )
                        ->orderByDesc('id')
                        ->first();

                $classWiseFee = null;

                if (!$studentStructure) {
                    $classWiseFee =
                        ClassWiseFee::where(
                            'class_id',
                            $concession->class_id
                        )
                            ->where(
                                'head_id',
                                $policyHead->head_id
                            )
                            ->orderByDesc('id')
                            ->first();
                }

                $baseAmount = (float) (
                    optional($studentStructure)->amount
                    ?? optional($classWiseFee)->amount
                    ?? 0
                );

                $existingDiscountPercentage =
                    (float) (
                        optional($studentStructure)->discount
                        ?? 0
                    );

                $existingDiscountAmount = round(
                    $baseAmount
                    * $existingDiscountPercentage
                    / 100,
                    2
                );

                $existingPayableAmount = round(
                    $baseAmount
                    - $existingDiscountAmount,
                    2
                );

                $concessionPercentage = (float) (
                    $policyHead->percentage
                    ?? 0
                );

                $concessionAmount = round(
                    $baseAmount
                    * $concessionPercentage
                    / 100,
                    2
                );

                $payableAmount = round(
                    $baseAmount
                    - $concessionAmount,
                    2
                );

                return [
                    'head_id' =>
                        (int) $policyHead->head_id,
                    'head_name' =>
                        optional($feeHead)->fee_head,
                    'percentage' =>
                        $concessionPercentage,

                    /*
                     * Keep both keys for clarity/backward display use.
                     */
                    'actual_amount' =>
                        $baseAmount,
                    'base_amount' =>
                        $baseAmount,

                    'existing_discount_percentage' =>
                        $existingDiscountPercentage,
                    'existing_discount_amount' =>
                        $existingDiscountAmount,
                    'existing_payable_amount' =>
                        $existingPayableAmount,

                    'concession_amount' =>
                        $concessionAmount,
                    'payable_amount' =>
                        $payableAmount,

                    'amount_source' =>
                        $studentStructure
                            ? 'student_fee_structure'
                            : 'class_wise_fee',

                    'student_fee_structure_id' =>
                        optional($studentStructure)->id,
                    'class_wise_fee_id' =>
                        optional($classWiseFee)->id,
                ];
            })
            ->values();

        return [
            'version' => 1,
            'captured_at' => now()->toISOString(),
            'approval_date' =>
                $concession->approval_date
                ?: date('Y-m-d'),

            'concession' => [
                'id' => (int) $concession->id,
                'concession_order' =>
                    $concession->concession_order,
                'policy_id' =>
                    (int) $concession->concession_id,
                'policy_title' =>
                    optional($concession->concession)->title,
                'type' =>
                    (int) $concession->type,
                'type_label' =>
                    $this->concessionTypeLabel(
                        $concession->type
                    ),
                'apply_date' =>
                    $concession->apply_date,
                'effective_from' =>
                    $concession->effective_from,
                'start_date' =>
                    $concession->start_date,
                'end_date' =>
                    $concession->end_date,
            ],

            'student' => [
                'id' =>
                    (int) $concession->student_id,
                'roll_no' =>
                    optional($concession->student)->roll_no,
                'name' =>
                    optional($concession->student)->stdname,
                'father_name' =>
                    optional($concession->student)->fathername,
                'class_id' =>
                    (int) $concession->class_id,
                'class_name' =>
                    optional($concession->class)->name,
                'session_id' =>
                    $concession->session_id,
                'branch_id' =>
                    (int) $concession->owned_by,
                'branch_name' =>
                    optional($concession->branches)->name,
            ],

            'heads' => $heads->all(),

            'totals' => [
                'base_amount' =>
                    round(
                        (float) $heads->sum('base_amount'),
                        2
                    ),
                'concession_amount' =>
                    round(
                        (float) $heads->sum(
                            'concession_amount'
                        ),
                        2
                    ),
                'payable_amount' =>
                    round(
                        (float) $heads->sum(
                            'payable_amount'
                        ),
                        2
                    ),
            ],

            'approved_by' => [
                'id' => Auth::id(),
                'name' => optional(Auth::user())->name,
            ],
        ];
    }

    private function decodeConcessionSnapshot($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode(
            (string) $value,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    /**
     * Convert a month/year UI value (Y-m) to a DATE value representing the
     * first day of that billing month.
     */
    private function normalizeEffectiveMonth($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::createFromFormat(
            '!Y-m',
            (string) $value
        )->toDateString();
    }

    /**
     * Format a date strictly for user-facing presentation.
     *
     * Storage, validation and HTML date inputs remain Y-m-d.
     */
    private function displayDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d-M-y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    /**
     * Mutate an already-loaded Concession model only for rendering.
     * This method is never used before save/update/query conditions.
     */
    private function formatConcessionDatesForPresentation($concession)
    {
        if (!$concession) {
            return $concession;
        }

        foreach ([
            'apply_date',
            'effective_from',
            'start_date',
            'end_date',
            'approval_date',
            'cancel_date',
            'created_at',
            'updated_at',
        ] as $field) {
            $value = $concession->getAttribute($field);

            if (!empty($value)) {
                $concession->setAttribute(
                    $field,
                    $this->displayDate($value)
                );
            }
        }

        return $concession;
    }

    public function concessionorder($id)
    {
        // dd($id);
        $concession = Concession::with('student', 'class')->find($id);
        if (!$concession) {
            return redirect()->back()->with('error', __('Concession Not Found.'));
        }
        $concession_policy = ConcessionPolicy::with('concession', 'concession.student', 'concession.student.enrollment', 'concession.class', 'concession.student.session')->findOrFail($concession->concession_id);
        $concession_heads = ConcessionPolicyHead::where('concession_id', $concession_policy->id)->where('percentage', '!=', 0)->get();
        // dd($concession_policy,$concession_heads);
        $html = view('students.concession.template', [
            'data' => $concession_heads,
            'student' => @$concession->student,
            'class' => $concession->class
        ])->render();
        $headerHtml = view('students.concession.header')->render();
        $footerHtml = view('students.concession.footer')->render();
        $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // To load images, fonts, etc.
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $base64Pdf = base64_encode($pdfContent);
        $pdfDecoded = base64_decode($base64Pdf);
        return response($pdfDecoded)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="concession_order.pdf"')
            ->header('Content-Length', strlen($pdfDecoded));

        // return response($pdfDecoded)
        //     ->header('Content-Type', 'application/pdf')
        //     ->header('Content-Disposition', 'attachment; filename="concession_order.pdf"')
        //     ->header('Content-Length', strlen($pdfDecoded));
    }

}