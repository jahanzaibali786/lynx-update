<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Exports\FeeStructureListingExport;
use App\Models\ChartOfAccountType;
use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudyPack;
use Auth;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\Session;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
use App\Models\StudentFeeStructure;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;

class ClassWiseFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::orderBy('id', 'Desc');
        }
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');

        $class = [];
        $structureTypes = [
            'regular' => 'Regular',
            'teacher_child' => 'Teacher Child',
        ];

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        if (!empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }

        $type = $request->type ?? 'regular';
        if ($type === 'teacher_child') {
            $teacherQuery = clone $query;
            $teacherQuery->where('type', 'teacher_child');
            $classfee = $teacherQuery->get()->pluck('amount', 'head_id');
            if ($classfee->isEmpty()) {
                $query->where('type', 'regular');
                $classfee = $query->get()->pluck('amount', 'head_id');
            }
        } else {
            $query->where('type', 'regular');
            $classfee = $query->get()->pluck('amount', 'head_id');
        }

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            $classfee = [];
        }
        return view('students.classwisefee.index', compact('heads', 'branches', 'session', 'class', 'classfee', 'structureTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create($id)
    {
        // if(\Auth::user()->can('create session'))
        // {
        // $fee_heads = FeeHead::get()->pluck('fee_head','id');
        // $sections = Session::where('created_by',Auth::user()->creatorId())->get()->pluck('title','id');
        // return view('students.classwisefee.create',compact('fee_heads','sections','id'));
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
        // dd($request->all());
        // if(\Auth::user()->can('create session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                'branch' => 'required',
                'session' => 'required',
                'class' => 'required',
                'account_id' => 'required',
                'account_value' => 'required',
                // 'fee_head' => 'required|unique:class_wise_fees,head_id,NULL,id,class_id,' . $request->class_id,
            ]
            // , [
            //     'fee_head.unique' => 'This fee head already exists for the selected class.',
            // ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $type = $request->type ?? 'regular';

        if ($request->account_id > 0) {
            for ($i = 0; $i < count($request->account_id); $i++) {
                $classWiseFee = ClassWiseFee::updateOrCreate(
                    [
                        'session_id' => $request->session,
                        'class_id' => $request->class,
                        'head_id' => $request->account_id[$i],
                        'type' => $type,
                    ],
                    [
                        'amount' => $request->account_value[$i],
                        'discount' => '0',
                        'owned_by' => $request->branch,
                        'created_by' => Auth::user()->creatorId(),
                    ]
                );
            }
        }
        return redirect()->route('class_wise_fee.index', ['branches' => $request->branch, 'session' => $request->session, 'class' => $request->class, 'type' => $type])->with('success', 'Fee Structure has been created or updated successfully');
        // return redirect()->route('class_wise_fee.index')->with('success','Session Class Wise Fee has been created successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function show(ClassWiseFee $classWiseFee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function edit(ClassWiseFee $classWiseFee)
    {

        // if(\Auth::user()->can('edit session'))
        // {
        $fee_heads = FeeHead::get()->pluck('fee_head', 'id');
        $sections = Session::where('created_by', Auth::user()->creatorId())->get()->pluck('year', 'id');
        return view('students.classwisefee.edit', compact('classWiseFee', 'fee_heads', 'sections'));
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
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassWiseFee $classWiseFee)
    {

        // if(\Auth::user()->can('edit session'))
        // {
        $validator = \Validator::make(
            $request->all(),
            [
                // 'class_id' => 'required',
                'head_id' => 'required|unique:class_wise_fees,head_id,' . $classWiseFee->id . ',id,class_id,' . $classWiseFee->class_id,
                'amount' => 'required',
                'session_id' => 'required',
            ],
            [
                'fee_head.unique' => 'This fee head already exists for the selected class.',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $classWiseFee->head_id = $request->head_id;
        $classWiseFee->amount = $request->amount;
        $classWiseFee->session_id = $request->session_id;
        $classWiseFee->save();

        return redirect()->route('class_fee', ['id' => $classWiseFee->class_id])->with('Class Wise Fee Head has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClassWiseFee  $classWiseFee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClassWiseFee $classWiseFee)
    {
        //
    }

    public function sessionclass(Request $request)
    {
        if (isset($request->type) && $request->type == 'studypack') {
            $selectedClass = Classes::where('id', $request->id)->first();
            $classIds = [];
            if ($selectedClass) {
                $classIds = Classes::where('name', $selectedClass->name)->pluck('id')->values()->toArray();
            }

            $Studypack = StudyPack::where('session_id', '=', $request->session)
                ->where(function ($query) use ($classIds) {
                    foreach ($classIds as $index => $classId) {
                        $classId = (int) $classId;
                        if ($index === 0) {
                            $query->whereJsonContains('class', $classId);
                        } else {
                            $query->orWhereJsonContains('class', $classId);
                        }
                    }
                })
                ->get();

            $students = StudentEnrollments::whereIn('class_id', $classIds)
                ->where('session_id', $request->session)
                ->get();

            $class = Classes::whereIn('id', $classIds)->pluck('name', 'id');
        } else {
            $Studypack = [];
            $class = Classes::where('owned_by', '=', $request->id)
            ->where('active_status', 1)
            ->where('created_by', \Auth::user()->creatorId())
            ->get();
            if (empty($request->id) || is_null($request->id) && Auth::user()->type == 'company') {
                $students = StudentRegistration::where('created_by', \Auth::user()->creatorId())->where('student_status', '!=', 'Registered')->get();
            } else {
                $students = StudentRegistration::where('owned_by', '=', $request->id)->where('student_status', '!=', 'Registered')->get();
            }
        }
        $session = Session::where('created_by', \Auth::user()->creatorId())->get();
        $result = [
            'status' => 'success',
            'session' => $session,
            'class' => $class,
            'Studypack' => $Studypack,
            'students' => $students,
        ];
        return response()->json($result);
    }
    public function getClassStudents(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request.',
            ]);
        }

        $type = strtolower(
            trim((string) $request->input('type', 'regular'))
        );

        $baseQuery = StudentRegistration::where(
            'class_id',
            $request->class_id
        )
            ->where(
                'created_by',
                \Auth::user()->creatorId()
            );

        /*
         * Direct Concession Application student source:
         *
         * regular      => active Enrolled students
         * registration => active Registered students
         * withdrawal   => students whose LATEST withdrawal is Draft/Approved
         *
         * Withdrawal intentionally does NOT require active_status = 1,
         * because a withdrawn student can already be inactive.
         */
        if ($type === 'registration') {
            $students = $baseQuery
                ->where('student_status', 'Registered')
                ->where('active_status', 1)
                ->get();
        } elseif ($type === 'withdrawal') {
            $students = $baseQuery
                ->get()
                ->filter(function ($student) {
                    $withdrawal = \App\Models\StudentWithdrawal::where(
                        'student_id',
                        $student->id
                    )
                        ->orderByDesc('id')
                        ->first();

                    return $withdrawal
                        && in_array(
                            strtolower(
                                trim(
                                    (string) $withdrawal->status
                                )
                            ),
                            ['draft', 'approved'],
                            true
                        );
                })
                ->values();
        } else {
            $students = $baseQuery
                ->where('student_status', 'Enrolled')
                ->where('active_status', 1)
                ->get();
        }

        /*
         * Optional branch guard supplied by Concession Create.
         */
        $branchId = $request->input('branch_id');

        if (!empty($branchId)) {
            $students = $students
                ->filter(function ($student) use ($branchId) {
                    return (int) $student->owned_by === (int) $branchId
                        || (int) $student->branch === (int) $branchId;
                })
                ->values();
        }

        $students = $students->mapWithKeys(
            function ($student) use ($type) {
                $prefix = $student->roll_no
                    ? $student->roll_no . ' - '
                    : '';

                /*
                 * Registration normally has no roll number yet.
                 */
                if ($type === 'registration') {
                    $prefix = '';
                }

                return [
                    $student->id =>
                        $prefix
                        . $student->stdname
                        . ' s/d/o '
                        . $student->fathername,
                ];
            }
        );

        return response()->json([
            'status' => 'success',
            'students' => $students,
        ]);
    }

    public function getbranchstudent(Request $request)
    {
        $includeRegistered = $request->input('include_registered', false);
        $forReadmission = $request->boolean('readmission');

        if ($forReadmission) {
            // Readmission selection is branch-based, not class-based. Include students whose
            // LATEST withdrawal is still draft/approved even when active_status is already 0.
            $records = StudentRegistration::with('enrollment')
                ->where(function ($query) use ($request) {
                    $query->where('owned_by', $request->branch_id)
                        ->orWhere('branch', $request->branch_id);
                })
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->filter(function ($student) {
                    $withdrawal = \App\Models\StudentWithdrawal::where('student_id', $student->id)
                        ->orderByDesc('id')
                        ->first();

                    return $withdrawal && in_array(
                        strtolower(trim((string) $withdrawal->status)),
                        ['draft', 'approved'],
                        true
                    );
                })
                ->values();

            $students = $records->mapWithKeys(function ($student) {
                $roll = optional($student->enrollment)->enrollId ?: $student->roll_no;
                return [
                    $student->id => trim(
                        ($roll ? $roll . ' - ' : '')
                        . $student->stdname
                        . ($student->fathername ? ' s/d/o ' . $student->fathername : '')
                    )
                ];
            });

            $studentRecords = $records->map(function ($student) {
                $enrollment = $student->enrollment;
                $branchId = (int) (optional($enrollment)->owned_by ?: $student->owned_by ?: $student->branch);
                $classId = (int) (optional($enrollment)->class_id ?: $student->class_id);
                $sectionId = (int) (optional($enrollment)->section_id ?: $student->section_id);
                $sessionId = (int) (optional($enrollment)->session_id ?: $student->session_id);

                $doa = null;
                foreach (['admission_date', 'date_of_admission', 'admissionDate', 'doa'] as $attribute) {
                    $value = $student->{$attribute} ?? null;
                    if (!empty($value)) {
                        try {
                            $doa = \Carbon\Carbon::parse($value)->toDateString();
                        } catch (\Throwable $e) {
                            $doa = $value;
                        }
                        break;
                    }
                }
                if (!$doa && $student->created_at) {
                    $doa = \Carbon\Carbon::parse($student->created_at)->toDateString();
                }

                return [
                    'reg_id' => (int) $student->id,
                    'id' => (int) $student->id,
                    'roll_no' => optional($enrollment)->enrollId ?: $student->roll_no,
                    'stdname' => $student->stdname,
                    'fathername' => $student->fathername,
                    'branch_id' => $branchId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'session_id' => $sessionId,
                    'class' => optional(Classes::find($classId))->name,
                    'section' => optional(\App\Models\Section::find($sectionId))->name,
                    'session' => optional(Session::find($sessionId))->year,
                    'date_of_admission' => $doa,
                    'student_status' => $student->student_status,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'students' => $students,
                'student_records' => $studentRecords,
            ]);
        }

        // Existing behaviour preserved for every other screen using this route.
        $query = StudentRegistration::where('owned_by', $request->branch_id)
            ->where('active_status', 1);

        if ($includeRegistered) {
            $query->whereIn('student_status', ['Enrolled', 'Registered']);
        } else {
            $query->where('student_status', 'Enrolled');
        }

        $students = $query->get()
            ->mapWithKeys(function ($student) {
                if ($student->student_status == 'Enrolled') {
                    return [$student->id => $student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername];
                }

                return [$student->id => $student->stdname . ' s/d/o ' . $student->fathername];
            });

        return response()->json(['status' => 'success', 'students' => $students]);
    }
    public function classStudents(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request.',
            ]);
        }

        $type = strtolower(
            trim((string) $request->input('type', 'regular'))
        );

        $baseQuery = StudentRegistration::where(
            'class_id',
            $request->class_id
        )
            ->where(
                'created_by',
                \Auth::user()->creatorId()
            );

        /*
         * Direct Concession Application student source:
         *
         * regular      => active Enrolled students
         * registration => active Registered students
         * withdrawal   => students whose LATEST withdrawal is Draft/Approved
         *
         * Withdrawal intentionally does NOT require active_status = 1,
         * because a withdrawn student can already be inactive.
         */
        if ($type === 'registration') {
            $students = $baseQuery
                ->where('student_status', 'Registered')
                ->where('active_status', 1)
                ->get();
        } elseif ($type === 'withdrawal') {
            $students = $baseQuery
                ->get()
                ->filter(function ($student) {
                    $withdrawal = \App\Models\StudentWithdrawal::where(
                        'student_id',
                        $student->id
                    )
                        ->orderByDesc('id')
                        ->first();

                    return $withdrawal
                        && in_array(
                            strtolower(
                                trim(
                                    (string) $withdrawal->status
                                )
                            ),
                            ['draft', 'approved'],
                            true
                        );
                })
                ->values();
        } else {
            $students = $baseQuery
                ->where('student_status', 'Enrolled')
                ->where('active_status', 1)
                ->get();
        }

        /*
         * Optional branch guard supplied by Concession Create.
         */
        $branchId = $request->input('branch_id');

        if (!empty($branchId)) {
            $students = $students
                ->filter(function ($student) use ($branchId) {
                    return (int) $student->owned_by === (int) $branchId
                        || (int) $student->branch === (int) $branchId;
                })
                ->values();
        }

        $students = $students->mapWithKeys(
            function ($student) use ($type) {
                $prefix = $student->roll_no
                    ? $student->roll_no . ' - '
                    : '';

                /*
                 * Registration normally has no roll number yet.
                 */
                if ($type === 'registration') {
                    $prefix = '';
                }

                return [
                    $student->id =>
                        $prefix
                        . $student->stdname
                        . ' s/d/o '
                        . $student->fathername,
                ];
            }
        );

        return response()->json([
            'status' => 'success',
            'students' => $students,
        ]);
    }

    public function updateFeeStructureSelection(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_registrations,id',
            'session_id' => 'nullable|integer',
            'selected_heads' => 'nullable|array',
            'selected_heads.*' => 'integer',
        ]);

        try {
            $student = StudentRegistration::findOrFail($request->student_id);

            $selectedHeadIds = collect($request->input('selected_heads', []))
                ->map(function ($headId) {
                    return (int) $headId;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            /*
            |--------------------------------------------------------------------------
            | StudentFeeStructure is ONE continuing student structure
            |--------------------------------------------------------------------------
            |
            | Do NOT filter StudentFeeStructure by session. Session is used only when
            | the student has no structure and we need to attach the initial complete
            | ClassWiseFee structure selected in the readmission application.
            |
            */
            $hasStudentStructure = StudentFeeStructure::where(
                'reg_id',
                $student->id
            )->exists();

            if (!$hasStudentStructure) {
                $sessionId = (int) $request->input('session_id');

                if (!$sessionId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select the application session. The student fee structure does not exist yet.',
                    ], 422);
                }

                $teacherChildOption = \App\Models\Registring_option::where(
                    'name',
                    'TEACHER CHILD'
                )->first();

                $structureType = (
                    $teacherChildOption
                    && (int) $student->register_option === (int) $teacherChildOption->id
                ) ? 'teacher_child' : 'regular';

                $classFees = ClassWiseFee::where('session_id', $sessionId)
                    ->where('class_id', $student->class_id)
                    ->where('owned_by', $student->owned_by)
                    ->where('type', $structureType)
                    ->get();

                if (
                    $structureType === 'teacher_child'
                    && $classFees->isEmpty()
                ) {
                    $classFees = ClassWiseFee::where('session_id', $sessionId)
                        ->where('class_id', $student->class_id)
                        ->where('owned_by', $student->owned_by)
                        ->where('type', 'regular')
                        ->get();
                }

                if ($classFees->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No ClassWiseFee structure was found for the selected application session, student branch and class.',
                    ], 422);
                }

                \DB::transaction(function () use ($student, $classFees) {
                    foreach ($classFees as $fee) {
                        StudentFeeStructure::firstOrCreate(
                            [
                                'reg_id' => $student->id,
                                'head_id' => $fee->head_id,
                            ],
                            [
                                'student_id' => $student->roll_no,
                                'branch_id' => $student->owned_by,
                                'class_id' => $student->class_id,
                                'amount' => $fee->amount,
                                'discount' => 0,
                                'checked_status' => 0,
                                'owned_by' => $student->owned_by,
                                'created_by' => $student->created_by
                                    ?: \Auth::user()->creatorId(),
                            ]
                        );
                    }
                });
            }

            $studentHeadIds = StudentFeeStructure::where('reg_id', $student->id)
                ->pluck('head_id')
                ->map(function ($headId) {
                    return (int) $headId;
                })
                ->unique();

            $invalidHeadIds = collect($selectedHeadIds)->diff($studentHeadIds);

            if ($invalidHeadIds->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected fee heads are not attached to this student fee structure.',
                ], 422);
            }

            \DB::transaction(function () use ($student, $selectedHeadIds) {
                StudentFeeStructure::where('reg_id', $student->id)
                    ->update([
                        'checked_status' => 0,
                    ]);

                if (!empty($selectedHeadIds)) {
                    StudentFeeStructure::where('reg_id', $student->id)
                        ->whereIn('head_id', $selectedHeadIds)
                        ->update([
                            'checked_status' => 1,
                        ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Student fee structure selection updated successfully.',
                'data' => [
                    'student_id' => $student->id,
                    'total_heads' => StudentFeeStructure::where(
                        'reg_id',
                        $student->id
                    )->count(),
                    'selected_head_ids' => $selectedHeadIds,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
     public function getClasswithdrawStudents(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $status = $request->input('status');

            $query = StudentRegistration::select(\DB::raw('CONCAT(roll_no, " - ", stdname, " s/d/o ", fathername) AS stdname'), 'roll_no')->whereNotNull('roll_no');
            if ($status == 'active') {
                if ($request->class_id != 'all' && $request->class_id != null) {
                    $query->where('class_id', $request->class_id);
                }
                $query->where('active_status',1)->where('student_status','Enrolled');
            } else if ($status == 'withdraw') {
                $query->whereHas('withdrawal');
            }
            $students = $query->get()->pluck('stdname', 'roll_no');;
            return response()->json(['status' => 'success', 'students' => $students]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid request.']);
    }

    public function studentfeestructure(Request $request)
    {
        $session = Session::where('owned_by', '=', $request->id)->get();
        $class = Classes::where('owned_by', '=', $request->id)->get();

        $result = [
            'status' => 'success',
            'session' => $session,
            'class' => $class,
        ];
        return response()->json($result);
    }

    public function student_fee_generate(Request $request ,$id)
    {
        $student = StudentRegistration::where('id', $id)->first();
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }

        // Determine type from student's register_option
        $teacherChildOption = \App\Models\Registring_option::where('name', 'TEACHER CHILD')->first();
        $type = ($teacherChildOption && $student->register_option == $teacherChildOption->id) ? 'teacher_child' : 'regular';

        \DB::beginTransaction();
        try {
            $feeStructureBefore = $this->feeStructureAmountSnapshot($student);
            $classfee = ClassWiseFee::with('account')
                ->where('session_id', $student->session_id)
                ->where('class_id', $student->class_id)
                ->where('owned_by', $student->owned_by)
                ->where('type', $type)
                ->get();
            // dd($classfee);
            if ($classfee->isNotEmpty()) {
                foreach ($classfee as $fee) {
                    $keys = [
                        'reg_id' => $student->id,
                        'branch_id' => $student->owned_by,
                        'head_id' => $fee['head_id'],
                    ];
                    $values = [
                        'amount' => $fee['amount'],
                        'class_id' => $student->class_id,
                        'owned_by' => $student->owned_by,
                        'created_by' => $student->created_by,
                    ];
                    $sfs = StudentFeeStructure::where($keys)->first();
                    if ($sfs) {
                        if ($sfs->checked_status == 1) {
                            $sfs->update($values);
                        }
                    } else {
                        $keys['student_id'] = @$student->roll_no;
                        StudentFeeStructure::create(array_merge($keys, $values));
                    }
                }
            }

            $this->recordFeeStructureAmountHistory($student, $feeStructureBefore, 'classwise_fee_structure', 'Classwise fee structure generated');
            \DB::commit();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student Fee Structure Generated Successfully.',
                    'reload_preview' => true,
                ]);
            }
            return redirect()->route('registration.show', ['registration' => $student->id])->with('success', 'Student Fee Structure Genereated Successfull.');
        } catch (\Exception $e) {
            \DB::rollBack();
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function classWiseFeeReport(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        }
        ;

        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
        }
        if (!empty($request->session)) {
            $query->where('session_id', '=', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        $classfee = $query->get()->pluck('amount', 'head_id');

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            // $classfee = ClassWiseFee::where('id','0')->get();
            $classfee = [];

        }

        $class_wise_fee = $query->get()->groupBy('owned_by');
        $pdf = new Dompdf();
        $html = view('students.classwisefee.report', compact('class_wise_fee', 'branches', 'request'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('class_wise_fee', 'brnches_name', 'request'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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

        // return $dompdf->stream('class_wise_fee.pdf');
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
    public function feestructurelisting(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class', 'session')->orderBy('id', 'Desc');
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class', 'session')->orderBy('id', 'Desc');
        }

        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');

        if (!empty($request->branches) && is_array($request->branches)) {
            $query->whereIn('owned_by', $request->branches);
            $class = Classes::whereIn('owned_by', $request->branches)->get()->pluck('name', 'id');
        }


        if (!empty($request->session) && is_array($request->session)) {
            $query->whereIn('session_id', $request->session);

        }
        if (!empty($request->branches) || !empty($request->session)) {
            $class_wise_fee = $query->get()->groupBy('owned_by')->map(function ($group) {
                return $group->groupBy('session_id');
            });
            $classfee = $query->get()->pluck('amount', 'head_id');
        } else {
            $class_wise_fee = [];
            $classfee = [];
        }
        return view('students.classwisefee.list', compact('heads', 'branches', 'session', 'classfee', 'class_wise_fee'));
    }


    public function feeStructureReport(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('All Branches', '');
            $query = ClassWiseFee::with('feehead', 'class')->orderBy('id', 'Desc');
            $brnches_name = User::where('id', $request->branches)->first();
        }
        ;
        $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get();

        if (!empty($request->branches) && is_array($request->branches)) {
            $query->whereIn('owned_by', $request->branches);
            $class = Classes::whereIn('owned_by', $request->branches)->get()->pluck('name', 'id');
        }


        if (!empty($request->session) && is_array($request->session)) {
            $query->whereIn('session_id', $request->session);
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
        }
        $classfee = $query->get()->pluck('amount', 'head_id');

        if (empty($request->branches) && empty($request->session) && empty($request->class)) {
            // $classfee = ClassWiseFee::where('id','0')->get();
            $classfee = [];

        }

        $class_wise_fee = $query->get()->groupBy('owned_by')->map(function ($group) {
            return $group->groupBy('session_id');
        });
        $branchName = 'All Branches'; // Default value

        if (!empty($request->branches)) {
            // Convert to array if it's not already (for single selection case)
            $selectedBranches = is_array($request->branches) ? $request->branches : [$request->branches];

            $query->whereIn('owned_by', $selectedBranches);
            $class = Classes::whereIn('owned_by', $selectedBranches)->get()->pluck('name', 'id');

            // Get branch names
            if (count($selectedBranches) > 1) {
                $branchName = 'All Branches';
            } else {
                $branch = User::find($selectedBranches[0]);
                $branchName = $branch ? $branch->name : 'All Branches';
            }
        }

        if ($request->has('export') && $request->export == 'excel') {
            $report_name = 'Fee Structure Listing Report';
            return Excel::download(new FeeStructureListingExport($class_wise_fee, $branchName, $branches, $heads, $report_name, $request, $request->all()), 'Fee_structure_listing_report.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Fee Structure Listing Report';
            return Excel::download(new FeeStructureListingExport($class_wise_fee, $branchName, $branches, $heads, $report_name, $request, $request->all()), 'Fee_structure_listing_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        $report_name = 'Fee Structure';
        $pdf = new Dompdf();
        $html = view('students.classwisefee.listreport', compact('class_wise_fee', 'branches', 'request', 'heads'))->render();
        $headerHtml = view('students.classwisefee.report.pdf.header', compact('class_wise_fee', 'brnches_name', 'request', 'report_name'))->render();
        $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

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
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();

        // return $dompdf->stream('class_wise_fee.pdf');
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

    private function feeStructureAmountSnapshot(StudentRegistration $student)
    {
        $concessionPolicyHeads = $this->activeConcessionPolicyHeads($student->id);

        return StudentFeeStructure::where('reg_id', $student->id)
            ->where('owned_by', $student->owned_by)
            ->get()
            ->mapWithKeys(function ($structure) use ($concessionPolicyHeads) {
                $baseAmount = (float) ($structure->amount ?? 0);

                return [
                    (int) $structure->head_id => [
                        'structure_id' => $structure->id,
                        'base_amount' => $baseAmount,
                        'payable_amount' => $this->feeStructurePayableAmount(
                            $baseAmount,
                            (int) $structure->head_id,
                            (float) ($structure->discount ?? 0),
                            $concessionPolicyHeads
                        ),
                    ],
                ];
            });
    }

    private function activeConcessionPolicyHeads(int $studentId)
    {
        $concession = Concession::where('student_id', $studentId)
            ->where('status', 'Approved')
            ->where('active_status', 1)
            ->where(function ($query) {
                $query->where('end_date', '>=', date('Y-m-d'))
                    ->orWhereNull('end_date');
            })
            ->orderByDesc('id')
            ->first();

        if (!$concession) {
            return collect();
        }

        return ConcessionPolicyHead::where('concession_id', $concession->concession_id)
            ->pluck('percentage', 'head_id');
    }

    private function feeStructurePayableAmount(float $baseAmount, int $headId, float $structureDiscount, $concessionPolicyHeads): float
    {
        $discountPercentage = $concessionPolicyHeads->has($headId)
            ? (float) $concessionPolicyHeads->get($headId)
            : $structureDiscount;

        return round($baseAmount - (($baseAmount * $discountPercentage) / 100));
    }

    private function recordFeeStructureAmountHistory(StudentRegistration $student, $before, string $revisionType, string $remarks): void
    {
        $student->loadMissing('enrollment');
        $after = $this->feeStructureAmountSnapshot($student);
        $items = collect();

        foreach ($after as $headId => $current) {
            if (!$before->has($headId)) {
                continue;
            }

            $previous = $before->get($headId);
            $prevBase = (float) $previous['base_amount'];
            $newBase = (float) $current['base_amount'];
            $prevPayable = (float) $previous['payable_amount'];
            $newPayable = (float) $current['payable_amount'];

            if (round($prevBase, 2) === round($newBase, 2) && round($prevPayable, 2) === round($newPayable, 2)) {
                continue;
            }

            $items->push([
                'student_fee_structure_id' => $current['structure_id'],
                'student_id' => optional($student->enrollment)->enrollId,
                'reg_id' => $student->id,
                'head_id' => $headId,
                'percentage' => $prevBase > 0 ? round((($newBase - $prevBase) / $prevBase) * 100, 2) : 0,
                'prev_base_amount' => $prevBase,
                'new_base_amount' => $newBase,
                'prev_payable_amount' => $prevPayable,
                'new_payable_amount' => $newPayable,
            ]);
        }

        if ($items->isEmpty()) {
            return;
        }

        $batch = StudentFeeRevisionBatch::create([
            'revision_type' => $revisionType,
            'student_id' => optional($student->enrollment)->enrollId,
            'reg_id' => $student->id,
            'session_from_id' => $student->session_id,
            'session_to_id' => $student->session_id,
            'branch_from_id' => $student->owned_by,
            'branch_to_id' => $student->owned_by,
            'class_from_id' => $student->class_id,
            'class_to_id' => $student->class_id,
            'section_from_id' => $student->section_id,
            'section_to_id' => $student->section_id,
            'effective_from' => date('Y-m-d'),
            'status' => 'applied',
            'remarks' => $remarks,
            'owned_by' => $student->owned_by,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        foreach ($items as $item) {
            StudentFeeRevisionItem::create(array_merge(['batch_id' => $batch->id], $item));
        }
    }
}