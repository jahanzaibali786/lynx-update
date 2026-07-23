<?php

namespace App\Http\Controllers;

use App\Exports\StudentRegistrationExport;
use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Concession;
use App\Models\Registring_option;
use App\Models\Section;
use App\Models\StudentReceipt;
use App\Models\Session;
use App\Models\StudentFeeStructure;
use App\Models\User;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
use App\Models\StudentRegistration as ModelsStudentRegistration;
use App\Models\Utility;
use Aws\AppRegistry\AppRegistryClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use App\Models\Challans;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class StudentRegistration extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {       // if (\Auth::user()->can('view space')) {
        $user = \Auth::user();
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $classes = Classes::where('created_by', $user->creatorId())->pluck('name', 'id');
            $classes->prepend('Select Class', '');
            // $query = ModelsStudentRegistration::with('session', 'class')->where('created_by', '=', \Auth::user()->creatorId());
            $query = ModelsStudentRegistration::with('session', 'class')->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $classes = Classes::where('owned_by', $user->ownedId())->where('active_status', 1)->pluck('name', 'id');
            $classes->prepend('Select Class', '');
            // dd($user->ownedId)
            $query = ModelsStudentRegistration::with('session', 'class')->where('owned_by', '=', \Auth::user()->ownedId());
        }
        $sessions = Session::where('created_by', $user->creatorId())->pluck('year', 'id');
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
        }
        if (!empty($request->type) && $request->type != 'Registered' && $request->type != 'Enrolled') {
            $query->where('student_status', '!=', null)->orWhere('student_status', '!=', 'Registered');
        } else if (!empty($request->type) && $request->type == 'Registered') {
            $query->where('student_status', '=', 'Registered');
        } else if (!empty($request->type) && $request->type == 'Enrolled') {
            $query->where('student_status', '=', 'Enrolled');
        } else {
            $query->where('student_status', '=', 'Registered');
        }
        // dd($query->get());
        if (!empty($request->classes)) {
            $query->where('class_id', '=', $request->classes);
        }
        if (!empty($request->sessions)) {
            $query->where('session_id', '=', $request->sessions);
        }
        if (!empty($request->gender)) {
            $query->where('gender', '=', $request->gender);
        }
        if (!empty($request->sort)) {
            switch ($request->sort) {
                case 'asc':
                    $query->orderBy('stdname', 'asc');
                    break;

                case 'desc':
                    $query->orderBy('stdname', 'desc');
                    break;

                case 'gender':
                    $query->orderBy('gender', 'asc');
                    break;
            }
        } else {
            $query->orderBy('reg_no', 'desc');
        }
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $columns = ['reg_no', 'stdname', 'fathername', 'fatherprofession', 'mothername', 'motherprofession', 'city', 'mothercnic', 'fathercnic', 'session_id'];
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$searchTerm}%");
                }
            });
        }
        $registrations = $query->orderBy('id', 'Desc')->get();

        $branchTotals = [];
        $grandTotal = 0;

        foreach ($registrations->groupBy('owned_by') as $branchId => $branchRegs) {
            // If you want count of registrations per branch
            $branchTotals[$branchId] = $branchRegs->count();

            // If you want sum of a numeric column (e.g. fees or amount)
            // $branchTotals[$branchId] = $branchRegs->sum('amount_column');

            $grandTotal += $branchTotals[$branchId];
        }

        if ($request->has('export') && $request->export == 'excel') {
            $branchName = $branches[$request->branches] ?? 'All Branches';
            $report_name = 'Student Registration Report';
            $registrations = $query->orderBy('id', 'Desc')->get()->groupBy('owned_by');
            return Excel::download(new StudentRegistrationExport($registrations, $branches, $branchName, $branchTotals, $grandTotal, $report_name, $request->all()), 'student_registration.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $branchName = $branches[$request->branches] ?? 'All Branches';
            $report_name = 'Student Registration Report';
            $registrations = $query->orderBy('id', 'Desc')->get()->groupBy('owned_by');
            return Excel::download(new StudentRegistrationExport($registrations, $branches, $branchName, $branchTotals, $grandTotal, $report_name, $request->all()), 'student_registration.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        // $registrations=ModelsStudentRegistration::paginate(10);
        return view('students.registration.index', compact('registrations', 'branches', 'classes', 'sessions'));
        // } else {
        //     return redirect()->back()->with('error', __('Permission Denied.'));
        // }
    }
    // public function exportToExcel($registrations)
    // {
    //     return Excel::download(new StudentRegistrationExport($registrations), 'student_registration.xlsx');
    // }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pattern = '%registration%';
        $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();

        if (!$adm_fee_head) {
            return redirect()->back()->with('error', 'Please create an "Registration" fee head first.');
        }
        if (\Auth::user()->type == 'company') {
            $branch = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branch = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $session = Session::orderBy('id', 'Desc')->where('active_status', '1')->where('created_by', '=', \Auth::user()->creatorId())->pluck('year', 'id');
        $registerOption = Registring_option::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
        return view('students.registration.create', compact('branch', 'session', 'registerOption'));
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
        $validator = Validator::make($request->all(), [
            'regdate' => 'required|date',
            'stdname' => 'required|string',
            'dob' => 'required|date',
            // 'religion' => 'required|string',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'prevschool' => 'nullable|string',
            'prevclass' => 'nullable|string',
            'fathername' => 'required|string',
            // 'fathercnic' => 'required|string|max:15',
            'fatherphone' => 'nullable|string',
            'fathercell' => 'nullable|string',
            'fatherprofession' => 'required|string',
            'city' => 'nullable|string',
            'mothername' => 'nullable|string',
            'mothercnic' => 'nullable|string|max:15',
            'motherprofession' => 'nullable|string',
            'register_option' => 'required|integer',
            'email' => 'nullable|email',
			 'father_email' => 'required|email',
            'mother_email' => 'required|email',
            // 'address' => 'required|string',
            'branch' => 'required',
            'class_id' => 'required',
            'session_id' => 'required',
            // 'registrationfee' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $olddata = ModelsStudentRegistration::where('stdname', $request->stdname)->where('fathername', $request->fathername)->where('fathercnic', $request->fathercnic)->first();
            if ($olddata) {
                if ($olddata->student_status == 'Registered') {
                    return back()->withErrors(['stdname' => 'Student Already Registered'])->withInput();
                } else {
                    return back()->withErrors(['stdname' => 'Student Already Exist'])->withInput();
                }
            }
            $prevRegNo = ModelsStudentRegistration::selectRaw(
                'MAX(CAST(reg_no AS UNSIGNED)) as max_reg_no'
            )->value('max_reg_no');

            $newRegNo = $prevRegNo ? $prevRegNo + 1 : 1;
            $registration = new ModelsStudentRegistration();
            $registration->regdate = $request->input('regdate');
            $registration->reg_no = $newRegNo;
            $registration->stdname = strtoupper($request->input('stdname'));
            $registration->dob = $request->input('dob');
            $registration->religion = $request->input('religion');
            $registration->gender = $request->input('gender');
            $registration->prevschool = $request->input('prevschool');
            $registration->prevclass = $request->input('prevclass');
            $registration->fathername = $request->input('fathername');
            $registration->fathercnic = $request->input('fathercnic');
            $registration->fatherphone = $request->input('fatherphone');
            $registration->fathercell = $request->input('fathercell');
            $registration->fatherprofession = $request->input('fatherprofession');
            $registration->city = $request->input('city');
            $registration->mothername = $request->input('mothername');
            $registration->mothercnic = $request->input('mothercnic');
            $registration->motherprofession = $request->input('motherprofession');
            $registration->register_option = $request->input('register_option');
            $registration->father_email = $request->input('father_email');
            $registration->mother_email = $request->input('mother_email');
            $presentParts = array_filter([
                $request->input('present_house'),
                $request->input('present_street'),
                $request->input('present_area'),
                $request->input('present_sector'),
                $request->input('present_city_addr'),
                $request->input('present_district_addr'),
            ]);
            $registration->address = strtoupper(implode(', ', $presentParts));
            $permanentParts = array_filter([
                $request->input('permanent_house'),
                $request->input('permanent_street'),
                $request->input('permanent_area'),
                $request->input('permanent_sector'),
                $request->input('permanent_city_addr'),
                $request->input('permanent_district_addr'),
            ]);
            $registration->permanent_address = strtoupper(implode(', ', $permanentParts));
            $registration->branch = $request->input('branch');
            $registration->class_id = $request->input('class_id');
            $registration->reg_class = $request->input('class_id');
            $registration->session_id = $request->input('session_id');
            $registration->registrationfee = $request->input('registrationfee');
            $registration->remarks = $request->input('remarks');
            $registration->custody_relation = $request->input('custody_relation');
            $registration->custody_name = $request->input('custody_name');
            $registration->custody_cnic = $request->input('custody_cnic');
            $registration->student_status = 'Registered';
            $registration->owned_by = $request->input('branch');
            $registration->created_by = \Auth::user()->creatorId();
            $registration->save();

            if ($registration) {
                $reg_dis = Registring_option::where('id', $request->input('register_option'))->where('created_by', \Auth::user()->creatorId())->first();
                $challan = new Challans();
                $challan->student_id = $registration->id;

                $challan->class_id = $request->input('class_id');
                $challan->rollno = $registration->id;
                $challan->challanNo = $this->challanNo();
                $challan->challan_date = $request->input('regdate');
                $challan->challan_type = 'Registration';
                $challan->total_amount = $reg_dis->discount;
                $challan->paid_amount = $reg_dis->discount;
                $challan->issue_date = $request->input('regdate');
                $challan->due_date = Carbon::parse($request->input('regdate'))->addDays(7)->format('Y-m-d');
                $challan->fee_month = date('Y-m-01');
                $challan->status = 'Paid';
                $challan->session_id = $registration->session_id;
                $challan->owned_by = $registration->owned_by;
                $challan->created_by = \Auth::user()->creatorId();
                $challan->save();
                $bankAccount = BankAccount::where('owned_by', $challan->owned_by)
                    ->where(function ($query) {
                        $query->where('bank_name', 'LIKE', '%cash%')
                            ->orWhere('bank_name', 'LIKE', '%csh%');
                    })
                    ->first();
                $recipts = StudentReceipt::create(
                    [
                        'recipt_date' => date('Y-m-d'),
                        'challan_id' => $challan->id,
                        'recipt_amount' => $challan->paid_amount,
                        'student_id' => $challan->student_id,
                        'challan_amount' => $challan->paid_amount,
                        'late_amount' => 0,
                        'arrears' => 0,
                        'bank_id' => $bankAccount->id,
                        'account_id' => @$bankAccount->chart_account_id,
                        'referance' => 'Registration Fee',
                        'receive_type' => 'CD',
                        'received_by' => Auth::user()->id,
                        'owned_by' => $challan->owned_by,
                        'created_by' => \Auth::user()->creatorId(),
                    ]
                );

                $pattern = '%registration%';
                $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                $challan_head = new ChallanHead();
                $challan_head->challan_id = $challan->id;
                $challan_head->head_id = $adm_fee_head->id;
                $challan_head->price = $reg_dis->discount ? $reg_dis->discount : 0;
                $challan_head->concession = 0;
                $challan_head->paid = $reg_dis->discount ? $reg_dis->discount : 0;
                $challan_head->save();
                $item['0']['head'] = $challan_head->head_id;
                $item['0']['price'] = $reg_dis->discount ? $reg_dis->discount : 0;
                $item['0']['quantity'] = 1;
                $item['0']['concession'] = 0;
                $item['0']['total'] = $reg_dis->discount ? $reg_dis->discount : 0;
                $data['id'] = $challan->id;
                $data['no'] = $challan->challanNo;
                $data['date'] = $request->input('regdate');
                $data['challan_id'] = $challan->id;
                $data['recipt'] = $recipts->id;
                $data['reference'] = $challan->student_id;
                $data['description'] = 'Registration Fee Amount';
                $data['user_id'] = $challan->student_id;
                $data['amount'] = $challan->paid_amount;
                $data['total'] = $challan->paid_amount;
                $data['user_type'] = 'Student';
                $data['bank_id'] = $bankAccount->id;
                $data['branch_id'] = $registration->owned_by;
                $data['branch_name'] = $registration->branches->name;
                $data['std_name'] = $registration->stdname;
                $data['bank_name'] = $bankAccount->bank_name;
                $data['fee_month'] = date('Y-m-01');
                $data['category'] = 'Registration';
                $data['owned_by'] = $challan->owned_by;
                $data['created_by'] = $challan->created_by;
                $data['account_id'] = $bankAccount->chart_account_id;
                $data['items'] = $item;
                $registration = ModelsStudentRegistration::where('id', $registration->id)->update([
                    'registrationfee' => $reg_dis->discount
                ]);

                Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');
                $invoicePayment = Challans::where('challanNo', $request->challan_id)->first();

                $dataret = Utility::crv_entry($data);
                // $dataret = Utility::jrentry($data);
                $challan->voucher_id = $dataret;
                $challan->save();
                // if ($challan_head) {
                //     $item = [];
                //     $itemIndex = 0;
                //     $item[$itemIndex]['head'] = $adm_fee_head->id;
                //     $item[$itemIndex]['price'] = $reg_dis->discount ? $reg_dis->discount : 0;
                //     $item[$itemIndex]['quantity'] = 1;
                //     $item[$itemIndex]['concession'] = 0;
                //     $item[$itemIndex]['total'] = $reg_dis->discount ? $reg_dis->discount : 0;
                //     $itemIndex++;

                //     Utility::bankAccountBalance($request->account_id, $challan->amount, 'credit');
                //     $invoicePayment = Challans::where('challanNo',$challan->challanNo)->first();
                //     $bankAccount = BankAccount::find($request->bank);
                //     $data['id'] = $invoicePayment->id;
                //     $data['date'] = $invoicePayment->paid_date;
                //     $data['reference'] = $invoicePayment->reference;
                //     $data['description'] = $invoicePayment->description;
                //     $data['amount'] = $invoicePayment->amount;
                //     $data['category'] = $invoicePayment->challan_type;
                //     $data['owned_by'] = \Auth::user()->ownedId();
                //     $data['created_by'] = \Auth::user()->creatorId();
                //     $data['account_id'] = $bankAccount->chart_account_id;
                //     $data['items'] = $item;
                //     $data['total'] = $reg_dis->discount ? $reg_dis->discount : 0;
                //     $dataret = Utility::crv_entry($data);
                // }
            }
            DB::commit();
            return redirect()->route('registration.index')->with('success', 'Student Registered Successfull.');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return back()->withInput()->with('error', 'Error occurred while saving registration: ' . $e->getMessage());
        }
    }
    public function regNo()
    {
        $latest = ModelsStudentRegistration::where('created_by', \Auth::user()->creatorId())->orderBY('id', 'desc')->first();
        if (!$latest) {
            return 1;
        }
        return $latest->reg_no + 1;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $query = ModelsStudentRegistration::with('session', 'class')->where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $query = ModelsStudentRegistration::with('session', 'class')->where('owned_by', '=', \Auth::user()->ownedId());
        }
        $student = ModelsStudentRegistration::where('id', $id)->with('class', 'session', 'branches', 'enrollment')->first();

        // Determine type from student's register_option
        $teacherChildOption = Registring_option::where('name', 'TEACHER CHILD')->first();
        $type = ($teacherChildOption && $student->register_option == $teacherChildOption->id) ? 'teacher_child' : 'regular';

        $classes = Classes::where('owned_by', $student->owned_by)->get()->pluck('name', 'id');
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', $student->id)->where('owned_by', $student->owned_by)->get();

        if ($classfee->isEmpty()) {
            $fee_head = ClassWiseFee::with('account')->where('session_id', $student->session_id)->where('class_id', $student->class_id)->where('owned_by', $student->owned_by)->where('type', $type)->get();
            if ($fee_head) {
                for ($i = 0; $i < count($fee_head); $i++) {
                    $classfee = StudentFeeStructure::updateOrCreate(
                        [
                            'reg_id' => $student->id,
                            'branch_id' => $student->owned_by,
                            'head_id' => $fee_head[$i]['head_id'],
                        ],
                        [
                            'amount' => $fee_head[$i]['amount'],
                            'class_id' => $student->class_id,
                            'discount' => '0',
                            'owned_by' => $student->owned_by,
                            'created_by' => $student->created_by,
                        ]
                    );
                }
            }
        }
        $classfee = StudentFeeStructure::with('feehead')->where('reg_id', $student->id)->where('owned_by', $student->owned_by)->get();

        $selectedRegisterOptionId = $student->register_option;
        $registerOptions = Registring_option::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
        $studentchallanexist = Challans::where('student_id', $id)->where('challan_type', 'Admission')->first();
        // $concession = Concession::with('concession')->where('student_id', $id)->where('status', 'Approved')->first();
        $concession = Concession::with('concession')->where('student_id', $id)
            ->where('end_date', '>=', date('Y-m-d'))
            ->orderBy('id', 'desc')
            ->where('active_status', '!=', 0)
            ->where('status', 'Approved')
            ->first();
        if (!$concession) {
            $concession = Concession::with('concession')->where('student_id', $id)
                ->orderBy('id', 'desc')
                ->whereNull('end_date')
                ->where('active_status', '!=', 0)
                ->where('status', 'Approved')
                ->first();
        }

        $showJunJulFeeExempt = $this->canShowJunJulFeeExempt($student);

        return view('students.registration.show', ['branches' => $branches, 'concession' => $concession, 'student' => $student, 'classfee' => $classfee, 'studentchallanexist' => $studentchallanexist, 'registerOption' => $registerOptions, 'selectedOptionId' => $selectedRegisterOptionId, 'classes' => $classes, 'showJunJulFeeExempt' => $showJunJulFeeExempt]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $student = \App\Models\StudentRegistration::where('id', $id)->first();
        $classes = Classes::pluck('name', 'id');
        $session = Session::pluck('title');
        return view('students.registration.edit_registration', compact('student', 'classes', 'session'));
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
        // dd($request->all());
        $student = ModelsStudentRegistration::findOrFail($id);
        $student->load('enrollment');
        $sectionData = $this->transformSectionData($request->input('sectionData'));
        if ($request->sectionName == 'section1') {
            $profileImage = $sectionData['profile_image'] ?? null;
            unset($sectionData['profile_image']);
            \DB::beginTransaction();
            try {
                $validator = Validator::make($sectionData, [
                    'regdate' => 'required|date',
                    'name' => 'nullable|string',
                    'dob' => 'nullable|date',
                    // 'religion' => 'string',
                    'gender' => ['nullable', Rule::in(['male', 'female'])],
                    'city' => 'nullable|string',
                    'prevschool' => 'nullable|string',
                    'adm_session' => 'nullable|string',
                    'present_address' => 'nullable|string',
                    'permanent_address' => 'nullable|string',

                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                        'message' => 'Validation failed'
                    ], 422);
                }
                $student->regdate = $sectionData['regdate'];
                $student->stdname = strtoupper($sectionData['name']);
                $student->dob = $sectionData['dob'];
                $student->religion = $sectionData['religion'];
                $student->gender = $sectionData['gender'];
                $student->fatherphone = $sectionData['mobile_phone'];
                $student->fathercell = $sectionData['mobile_cell'];
                $student->nationality = $sectionData['nationality'];
                $student->birth_place = $sectionData['birth_place'];
                $student->district = $sectionData['district'];
                $student->city = $sectionData['city'];
               $student->email = $sectionData['email'] ?? '';
                $student->register_option = $sectionData['register_option'];
                $student->prevschool = $sectionData['prevschool'];
                $presentParts = array_filter([
                    $sectionData['present_house'] ?? '',
                    $sectionData['present_street'] ?? '',
                    $sectionData['present_area'] ?? '',
                    $sectionData['present_sector'] ?? '',
                    $sectionData['present_city_addr'] ?? '',
                    $sectionData['present_district_addr'] ?? '',
                ]);
                $student->address = strtoupper(implode(', ', $presentParts));
                $permanentParts = array_filter([
                    $sectionData['permanent_house'] ?? '',
                    $sectionData['permanent_street'] ?? '',
                    $sectionData['permanent_area'] ?? '',
                    $sectionData['permanent_sector'] ?? '',
                    $sectionData['permanent_city_addr'] ?? '',
                    $sectionData['permanent_district_addr'] ?? '',
                ]);
                $student->permanent_address = strtoupper(implode(', ', $permanentParts));                
                $student->save();
                if (isset($sectionData['branch']) && $sectionData['branch']) {
                    if ($sectionData['branch'] != $student->owned_by) {
                        $pattern = '%Registration%';
                        $challan = challans::whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])->first();
                        if ($challan) {
                            $journal = JournalEntry::where('category', 'Registration')->where('id', $challan->voucher_id)->first();
                            if ($journal) {
                                $journal->owned_by = $sectionData['branch'];
                                $journal->save();
                            }
                            $challan->owned_by = $sectionData['branch'];
                            $challan->save();
                        }
                        $patternadm = '%Admission%';
                        $challanadm = challans::whereRaw('LOWER(challan_type) LIKE ?', [strtolower($patternadm)])->first();
                        if ($challanadm) {
                            $journaladm = JournalEntry::where('category', 'Admission')->where('id', $challanadm)->first();
                            if ($journaladm) {
                                $journalItem = JournalItem::where('journal', $journaladm->id)->delete();
                                $journaladm->delete();
                            }
                            $challanadm->delete();
                            StudentFeeStructure::where('reg_id', $student->id)->delete();
                        }
                        $student->owned_by = $sectionData['branch'];
                        $student->branch = $sectionData['branch'];
                        $student->save();

                    }

                }
                if ($profileImage) {

                    if (strpos($profileImage, 'base64,') !== false) {

                        $imageParts = explode('base64,', $profileImage);
                        $imageBase64 = $imageParts[1];

                        $image = base64_decode($imageBase64);

                        // 🟢 clean student name
                        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $sectionData['name'] ?? 'student');

                        // 🟢 folder inside storage
                        $folder = 'studentPictures';

                        // 🟢 unique file name
                        $fileName = $safeName . '_' . $student->id . '_' . time() . '.png';

                        $filePath = $folder . '/' . $fileName;

                        // 🟢 store in storage/app/public/studentPictures
                        Storage::disk('public')->put($filePath, $image);

                        // 🟢 delete old image if exists
                        if ($student->profile_image && Storage::disk('public')->exists($student->profile_image)) {
                            Storage::disk('public')->delete($student->profile_image);
                        }

                        // 🟢 save relative path in DB
                        $student->profile_image = $filePath;
                    }

                    $student->save();
                }
                DB::commit();
                return response(['success' => 'Student Updated Successfully']);
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', __('Failed to student registration'))->withInput();
            }
        } else if ($request->sectionName == 'section2') {
            \DB::beginTransaction();
            try {
                $validator = Validator::make($sectionData, [
                    'father_name' => 'required|string',
                    'father_cnic' => 'nullable|string|max:15',
                    'home_phone' => 'nullable|string',
                    'mobile_phone' => 'nullable|string',
                    'father_occupation' => 'nullable|string',
                    'mother_name' => 'required|string',
                    'mother_cnic' => 'required|string|max:15',
                    'mother_occupation' => 'required|string',
                    'remarks' => 'nullable|string',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                        'message' => 'Validation failed'
                    ], 422);
                }
                // dd($sectionData);
                $student->fathername = $sectionData['father_name'];
                $student->fathercnic = $sectionData['father_cnic'];
                $student->fatherphone = isset($sectionData['home_phone']) ? $sectionData['home_phone'] : '';
                $student->fathercell = isset($sectionData['mobile_phone']) ? $sectionData['mobile_phone'] : '';
                $student->fatherprofession = $sectionData['father_occupation'];
				$student->father_email = $sectionData['father_email'] ?? '';
                $student->mothername = $sectionData['mother_name'];
                $student->mothercnic = $sectionData['mother_cnic'];
                $student->motherprofession = $sectionData['mother_occupation'];
				$student->mother_email = $sectionData['mother_email'] ?? '';
                $student->guardianname = isset($sectionData['guardian_name']) ? $sectionData['guardian_name'] : '';
                $student->guardianrelation = isset($sectionData['guardian_relation']) ? $sectionData['guardian_relation'] : '';
                $student->guardianprofession = isset($sectionData['guardian_occupation']) ? $sectionData['guardian_occupation'] : '';
                $student->guardiancnic = isset($sectionData['guardian_cnic']) ? $sectionData['guardian_cnic'] : '';
                $student->guardianphone = isset($sectionData['guardian_phone']) ? $sectionData['guardian_phone'] : '';
                $student->guardianaddress = isset($sectionData['guardian_address']) ? $sectionData['guardian_address'] : '';
                if (isset($sectionData['remarks'])) {
                    $student->remarks = $sectionData['remarks'];
                } else {
                    $student->remarks = '';
                }
                $student->save();
                DB::commit();
                return response(['success' => 'Student Updated Successfully']);
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', __('Failed to student registration'))->withInput();
            }

        } else if ($request->sectionName == 'section3') {
            \DB::beginTransaction();
            try {
				$feeStructureBefore = $this->feeStructureAmountSnapshot($student);
                if ($this->canShowJunJulFeeExempt($student)) {
                    $requestedExempt = !empty($sectionData['fee_exempt_jun_jul'])
                        && (string) $sectionData['fee_exempt_jun_jul'] === '1';

                    if (\Auth::user()->type === 'company') {
                        $student->fee_exempt_jun_jul = $requestedExempt;
                    } elseif (!$student->fee_exempt_jun_jul && $requestedExempt) {
                        $student->fee_exempt_jun_jul = true;
                    }
                }

                if ($sectionData['adm_class']) {
                    // dd($sectionData['adm_class'],$student->reg_class);
                    if ($sectionData['adm_class'] != $student->reg_class && $student->student_status == 'Registered') {
                        $pattern = '%Registration%';
                        $challan = challans::whereRaw('LOWER(challan_type) LIKE ?', [strtolower($pattern)])
                            ->where('student_id', $student->id)->first();
                        if ($challan) {
                            $challan->class_id = $sectionData['adm_class'];
                            $challan->save();
                        }

                        $patternadm = '%Admission%';
                        $challanadm = challans::whereRaw('LOWER(challan_type) LIKE ?', [strtolower($patternadm)])
                            ->where('student_id', $student->id)->first();
                        if ($challanadm) {
                            $challan->class_id = $sectionData['adm_class'];
                            StudentFeeStructure::where('reg_id', $student->id)->delete();
                        }

                        if ($student->student_status == 'Registered') {
                            $student->reg_class = $sectionData['reg_class'];
                            $student->class_id = $sectionData['adm_class'];
                            $student->save();
                        }
                    }

                }
                $concession = Concession::with('concession')->where('student_id', $id)
                    ->where('end_date', '>=', date('Y-m-d'))
                    ->orderBy('id', 'desc')
                    ->where('status', 'Approved')
                    ->first();

                if (!$concession) {
                    $concession = Concession::with('concession')->where('student_id', $id)
                        ->orderBy('id', 'desc')
                        ->whereNull('end_date')
                        ->where('status', 'Approved')
                        ->first();
                }

                if (@$request->checkedRows) {
                    foreach ($request->checkedRows as $row) {
                        // dd($row);
                        $stdfeestructure = StudentFeeStructure::with('feehead')
                            ->where('head_id', $row[5])->where('reg_id', $id)->where('owned_by', $student->owned_by)->first();
                        // dd($stdfeestructure,$row[5]);
                        if ($stdfeestructure) {
                            $stdfeestructure->amount = $row[1] ?? 0;
                            if ($concession) {
                                $concessionPolicyHeads = \App\Models\ConcessionPolicyHead::where(
                                    'concession_id',
                                    $concession->concession_id,
                                )
                                    ->where('head_id', $row[5])->first();
                            }
                            if (@$concession && @$concessionPolicyHeads) {
                                $stdfeestructure->discount = 0;
                            } else {
                                $stdfeestructure->discount = round($row[2] ?? 0);

                            }
                            // $stdfeestructure->head_id = $row[5] ?? 0;
                            $stdfeestructure->checked_status = 1;
                            $stdfeestructure->save();
                        }
                        // dd('sad',$stdfeestructure,$request->all(),$request->checkedRows[2] );
                    }
                }
                if (@$request->uncheckedRows) {
                    foreach ($request->uncheckedRows as $unrow) {
                        // dd($request->uncheckedRows);
                        $stdfees = StudentFeeStructure::where('reg_id', $id)->where('head_id', $unrow[5])->where('owned_by', $student->owned_by)->first();
                        if ($stdfees) {
                            $stdfees->checked_status = 0;
                            $stdfees->save();
                        }
                        // dd('asds',$stdfees,$unrow[5]);
                    }
                }
                // if ($sectionData['discount_policy_id']) {

                // } else {
                //     foreach ($request->checkedRows as $row) {
                //         $stdfeestructure = StudentFeeStructure::where('reg_id', $id)->where('head_id', $row[5])->first();
                //         if ($stdfeestructure) {
                //             $stdfeestructure->amount = $row[1] ?? 0;
                //             $stdfeestructure->discount = $row[2] ?? 0;
                //             $stdfeestructure->head_id = $row[5] ?? 0;
                //             $stdfeestructure->checked_status = 1;
                //             $stdfeestructure->save();
                //         }
                //         // dd('sssss',$stdfeestructure);
                //     }
                // }
                // dd($request->all());

                $student->save();
				$this->recordFeeStructureAmountHistory($student, $feeStructureBefore, 'manual_fee_structure', 'Manual fee structure update');
                \DB::commit();
                return response(['success' => 'Student Updated Successfully']);
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', __('Failed to student registration'))->withInput();
            }
        }
    }
	private function feeStructureAmountSnapshot(ModelsStudentRegistration $student)
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

    private function recordFeeStructureAmountHistory(ModelsStudentRegistration $student, $before, string $revisionType, string $remarks): void
    {
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

    private function transformSectionData($sectionData)
    {
        $transformedData = [];
        foreach ($sectionData as $item) {
            $transformedData[$item['name']] = $item['value'];
        }
        return $transformedData;
    }

    private function canShowJunJulFeeExempt(ModelsStudentRegistration $student): bool
    {
        if (!in_array(\Auth::user()->type, ['company', 'branch'], true)) {
            return false;
        }

        $admDate = optional($student->enrollment)->adm_date;
        if (empty($admDate)) {
            return false;
        }

        $today = Carbon::today();
        $admissionDate = Carbon::parse($admDate);

        return (int) $today->year === (int) $admissionDate->year
            && (int) $today->month >= 1
            && (int) $today->month <= 5;
    }

    function challanNo()
    {
        $latest = Challans::where('created_by', '=', \Auth::user()->creatorId())->orderBY('id', 'desc')->first();
        if (!$latest) {
            return 1;
        }
        return $latest->challanNo + 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ///
    }

    public function receipt($id)
    {
        $reg_recipt = ModelsStudentRegistration::with('session', 'class', 'branches', 'branch_name', 'branch_name.headmaster_name')->find($id);
        return view('students.registration.reg_slip', compact('reg_recipt'));
    }
   public function admission_order(Request $request, $id)
    {
        $adm_order = ModelsStudentRegistration::with(
            'session',
            'class',
            'branches',
            'enrollment',
            'branch_name',
            'branch_name.headmaster_name'
        )->find($id);

        if ($request->has('print') && $request->print == 'pdf') {

            $report_name = 'Admission Order';
            $branch = 'All Branches'; // you were using undefined $branches

            // Load ONE clean blade (important)
            $pdf = Pdf::loadView('students.registration.adm_order_print', compact(
                'adm_order',
                'report_name',
                'branch'
            ));

            // Paper settings
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('Admission_Order.pdf');
        }

        return view('students.registration.adm_order', compact('adm_order'));
    }


    public function SiblingonFathercnic(Request $request)
    {
        $registrations = ModelsStudentRegistration::with('session', 'class', 'branches', 'fee_structure')->where('student_status', 'Enrolled')->where('fathercnic', $request->fatherCnic)->get();
        $pattern = '%TUITION%';
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
        return response()->json(['siblings' => $registrations, 'head' => $head]);
    }

    public function getconcession(Request $request)
    {
        $pre = 0;
        $pattern = '%TUITION%';
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
        $concession = Concession::with('concession', 'concession.policy_head')
            ->where('student_id', $request->studentId)
            ->where('end_date', '>=', date('Y-m-d'))
            ->where('status', 'Approved')
            ->orderBy('id', 'desc')
            ->first();

        if (!$concession) {
            $concession = Concession::with('concession', 'concession.policy_head')
                ->where('student_id', $request->studentId)
                ->where('status', 'Approved')
                ->orderBy('id', 'desc')
                ->first();
        }
        if ($concession) {
            $concession = ConcessionPolicyHead::where('concession_id', $concession->concession_id)->where('head_id', $head->id)->first();
            $pre = $concession->percentage;
        }

        return response()->json($pre);
    }

}
