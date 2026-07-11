<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\EmployeeExport;
use App\Imports\EmployeesImport;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\EmpChildrens;
use App\Models\EmpEducation;
use App\Models\EmpFacility;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLeaves;
use App\Models\EmployeeRejoin;
use App\Models\EmployeeScale;
use App\Models\ExperienceCertificate;
use App\Models\FeeHead;
use App\Models\JoiningLetter;
use App\Models\EmpExperience;
use App\Exports\EmployeeReportExport;
use App\Models\EmployeeEmergencyContact;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\NOC;
use App\Models\Plan;
use App\Models\Resignation;
use App\Models\StudentFeeStructure;
use App\Models\StudentRegistration;
use App\Models\Termination;
use App\Models\SchoolDetails;
use App\Models\User;
use App\Models\Utility;
use Dompdf\Dompdf;
use Dompdf\Options;
use File;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

//use Faker\Provider\File;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage employee')) {
            $query = Employee::with(['ownedBranch', 'department', 'designation', 'employee_payscale_details', 'employee_monthly_salaries', 'latestEducation']);

            if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query->where('user_id', '=', Auth::user()->id);
            } else if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query->where('created_by', \Auth::user()->creatorId());
            } else {
                // dd(\Auth::user()->ownedId());
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $query->where('owned_by', \Auth::user()->ownedId());
            }
            $departments = Department::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $departments->prepend('All', 'all');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $designations->prepend('All', 'all');
            if (!empty($request->branches) && $request->branches != null) {
                $query->where('owned_by', '=', $request->branches);
            }
            if (!empty($request->ter_status)) {
                $query->where('is_res_ter', $request->ter_status);
            } else {
                $query->where('is_res_ter', 0);
            }
            if (!empty($request->department_id) && $request->department_id != 'all') {
                $query->where('department_id', $request->department_id);
            }
            if (!empty($request->designation_id) && $request->designation_id != 'all') {
                $query->where('designation_id', $request->designation_id);
            }
            if ($request->sort == 'desc') {
                $query->orderBy('name', 'desc');
            } else {
                $query->orderBy('name', 'asc');
            }
            if ($request->has('export') && $request->export == 'excel') {
                $employees = $query->get();
                return Excel::download(new EmployeeReportExport($employees), 'employee_report.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $employees = $query->get();
                return Excel::download(new EmployeeReportExport($employees), 'employee_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            // dd($request->is_print);
            if ($request->filled('is_print') && $request->is_print == 1) {
                $employeesquery = $query->get();
                $bodyHtml = view('employee.reports.employee_directory', compact('employeesquery', 'branches'))->render();
                $headerHtml = view('employee.report.pdf.header')->render();
                $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
                $finalHtml = '
                <html><head>
                <style>
                    @page {
                        margin-top: 100px;
                        margin-bottom: 100px;
                    }
                    .logo img{
                        max-width:200px !important;
                        max-height:150px !important;
                        margin-top:-50px;
                    }
                    .lynxtextimg{
                        width:50% !important;
                    }
                    .branchname{
                        font-size:2rem !important;
                    }
                    body { font-family: sans-serif; font-size: 12px; }
                    .header {
                        position: fixed;
                        top: -60px;
                        left: 0;
                        right: 0;
                        height: 100px;
                        text-align: center;
                    }
                    .footer {
                        position: fixed;
                        bottom: -60px;
                        left: 0;
                        right: 0;
                        height: 50px;
                        text-align: center;
                        font-size: 10px;
                        color: #888;
                    }
                </style>
                </head>
                <body>
                    ' . $headerHtml . '
                    <div class="footer">' . $footerHtml . '</div>
                    ' . $bodyHtml . '
                </body></html>';
                // dd($finalHtml);

                $options = new Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($finalHtml);
                $dompdf->setPaper('A2', 'landscape');
                $dompdf->render();
                return $dompdf->stream('employee_directory.pdf', ['Attachment' => false]);
            }

            $employees = $query->get();
            return view('employee.index', compact('employees', 'branches', 'departments', 'designations'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create employee')) {
            $company_settings = Utility::settings();
            if (Auth::user()->type == 'company') {
                $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
                $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $employees = User::where('created_by', \Auth::user()->creatorId());
            } else {
                $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $employees = User::where('owned_by', \Auth::user()->ownedId());
            }
            $employees = $employees->get();
            $employeesId = \Auth::user()->employeeIdFormat($this->employeeNumber());

            return view('employee.create', compact('employees', 'employeesId', 'departments', 'designations', 'documents', 'branches', 'company_settings'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('create employee')) {
            DB::beginTransaction();
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'salute' => 'required',
                        'name' => 'required',
                        'cnic' => 'required',
                        'f_name' => 'required',
                        // 'dob' => 'required',
                        'gender' => 'required',
                        // 'religion' => 'required',
                        // 'branch_id' => 'required',
                        // 'blood_group' => 'required',
                        // 'phone' => 'required',
                        // 'address' => 'required',
                        // 'present_address' => 'required',
                        'area' => 'required',
                        // 'category' => 'required',
                        // 'email' => 'required|unique:users',
                        // 'password' => 'required',
                        'department_id' => 'required',
                        'designation_id' => 'required',
                        // 'probation_period' => 'required',
                        // 'probation_end' => 'required',
                        //         'document.*' => 'mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:20480',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->withInput()->with('error', $messages->first());
                }

                $objUser = User::find(\Auth::user()->creatorId());
                $total_employee = $objUser->countEmployees();
                $plan = Plan::find($objUser->plan);

                // if($total_employee < $plan->max_employees || $plan->max_employees == -1)
                // {
                $user = User::create(
                    [
                        'name' => $request['name'],
                        // 'email' => $request['email'],
                        // // 'gender'=>$request['gender'],
                        // 'password' => Hash::make($request['password']),
                        'type' => 'employee',
                        'lang' => 'en',
                        'owned_by' => \Auth::user()->ownedId(),
                        'created_by' => \Auth::user()->creatorId(),
                    ]
                );
                $user->save();
                $user->assignRole('Employee');
                // }
                // else
                // {
                //     return redirect()->back()->with('error', __('Your employee limit is over, Please upgrade plan.'));
                // }


                if (!empty($request->document) && !is_null($request->document)) {
                    $document_implode = implode(',', array_keys($request->document));
                } else {
                    $document_implode = null;
                }
                $employee = Employee::create(
                    [
                        'user_id' => $user->id,
                        'salute' => $request['salute'],
                        'name' => $request['name'],
                        'f_name' => $request['f_name'],
                        'cnic' => $request['cnic'],
                        'dob' => $request['dob'],
                        'gender' => $request['gender'],
                        'religion' => $request['religion'],
                        'blood_group' => $request['blood_group'],
                        'phone' => $request['phone'],
                        'area' => $request['area'],
                        'address' => $request['address'],
                        'present_address' => $request['present_address'],
                        'category' => $request['category'],
                        'email' => $request['email'],
                        'password' => Hash::make($request['password']),
                        'employee_id' => $this->employeeNumber(),
                        'branch_id' => $request['branch_id'],
                        'department_id' => $request['department_id'],
                        'designation_id' => $request['designation_id'],
                        'company_doj' => $request['company_doj'],
                        'probation_period' => $request['probation_period'],
                        'probation_end' => $request['probation_end'],
                        'documents' => $document_implode,
                        'account_holder_name' => $request['account_holder_name'],
                        'account_number' => $request['account_number'],
                        'bank_name' => $request['bank_name'],
                        'bank_identifier_code' => $request['bank_identifier_code'],
                        'branch_location' => $request['branch_location'],
                        'tax_payer_id' => $request['tax_payer_id'],
                        'owned_by' => $request['branch_id'],
                        'created_by' => \Auth::user()->creatorId(),
                    ]
                );
                if ($request->hasFile('document')) {
                    foreach ($request->document as $key => $document) {

                        $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension = $request->file('document')[$key]->getClientOriginalExtension();
                        $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                        $dir = storage_path('uploads/document/');
                        $image_path = $dir . $filenameWithExt;

                        if (File::exists($image_path)) {
                            File::delete($image_path);
                        }

                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $path = $request->file('document')[$key]->storeAs('uploads/document/', $fileNameToStore);
                        $employee_document = EmployeeDocument::create(
                            [
                                'employee_id' => $employee['employee_id'],
                                'document_id' => $key,
                                'document_value' => $fileNameToStore,
                                'owned_by' => \Auth::user()->ownedId(),
                                'created_by' => \Auth::user()->creatorId(),
                            ]
                        );
                        $employee_document->save();

                    }

                }
                if ($employee) {
                    $employee = Employee::where('id', $employee->id)->first();
                    if($employee->category == 'Regular'){
                        $today = now();
                        $joiningDate = \Carbon\Carbon::parse($employee->joining_date);
                        $emp_probation_endDate = \Carbon\Carbon::parse($employee->probation_end);
                        $annualTotal = null;
                        $casualTotal = 0;
                        if ($joiningDate->year < $today->year) {
                            $annualTotal = 12 * 2.5;
                            $casualTotal = 12 * 0.80;
                        } elseif ($emp_probation_endDate->year < $today->year) {
                            $annualTotal = 12 * 2.5;
                            $casualTotal = 12 * 0.80;
                        } else {
                            $remainingMonths = 12 - $emp_probation_endDate->month + 1;
                            if ($emp_probation_endDate->lessThanOrEqualTo($today)) {
                                $annualTotal = $remainingMonths * 2.5;
                            }
                            $remainingCasualMonths = 12 - $today->month + 1;
                            $casualTotal = $remainingCasualMonths * 0.80;
                        }
                    }else{
                        $casualTotal = 0;
                        $annualTotal = 0;
                    }
                    EmployeeLeaves::create([
                        'employee_id' => $employee->id,
                        'casual_total' => $casualTotal,
                        'annual_total' => $annualTotal,
                        'owned_by' => \Auth::user()->ownedId(),
                        'created_by' => \Auth::user()->creatorId(),
                    ]);
                }
                ;
                $setings = Utility::settings();
                DB::commit();
                if ($setings['new_user'] == 1) {
                    $userArr = [
                        'email' => $user->email,
                        'password' => $user->password,
                    ];

                    // $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);
                    // return redirect()->route('employee.index')->with('success', __('Employee successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                    return redirect()->route('employee.index')->with('success', __('Employee successfully created.'));

                }
                DB::commit();
                return redirect()->route('employee.index')->with('success', __('Employee  successfully created.'));
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        if (\Auth::user()->can('edit employee')) {
            if (Auth::user()->type == 'company') {
                $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
                $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $employee = Employee::find($id);
                //  $employeesId  = \Auth::user()->employeeIdFormat($employee->employee_id);
                $employeesId = \Auth::user()->employeeIdFormat(!empty($employee) ? $employee->employee_id : '');

                // $departmentData = Department::where('created_by', \Auth::user()->creatorId())->where('branch_id', $employee->branch_id)->get()->pluck('name', 'id');
            } else {

                $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $employee = Employee::find($id);
                //  $employeesId  = \Auth::user()->employeeIdFormat($employee->employee_id);
                $employeesId = \Auth::user()->employeeIdFormat(!empty($employee) ? $employee->employee_id : '');

                // $departmentData = Department::where('created_by', \Auth::user()->creatorId())->where('branch_id', $employee->branch_id)->get()->pluck('name', 'id');
            }

            return view('employee.edit', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit employee')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'dob' => 'required',
                    'phone' => 'required|numeric',
                    'address' => 'required',
                    //                                   'document.*' => 'mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:20480',
                ]
            );
            if ($validator->fails()) {

                $messages = $validator->getMessageBag();
                dd($messages);
                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::findOrFail($id);

            if ($request->document) {
                foreach ($request->document as $key => $document) {
                    if (!empty($document)) {


$filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
$extension = $request->file('document')[$key]->getClientOriginalExtension();
$fileNameToStore = $filename . '_' . time() . '.' . $extension;

// Directory inside storage/app/
$dir = 'uploads/document/';

// Full file path for deletion (check old file with same original name)
$oldFilePath = storage_path('app/' . $dir . $filenameWithExt);
if (File::exists($oldFilePath)) {
    File::delete($oldFilePath);
}

// Create directory if not exists
if (!Storage::exists($dir)) {
    Storage::makeDirectory($dir, 0777, true, true);
}

// Store the file
$path = $request->file('document')[$key]->storeAs($dir, $fileNameToStore);

// If upload successful
if ($path) {
    $employee_document = EmployeeDocument::where('employee_id', $employee->employee_id)
        ->where('document_id', $key)
        ->first();

    if ($employee_document) {
        $employee_document->document_value = $fileNameToStore;
        $employee_document->save();
    } else {
        EmployeeDocument::create([
            'employee_id' => $employee->employee_id,
            'document_id' => $key,
            'document_value' => $fileNameToStore,
        ]);
    }
} else {
    return redirect()->back()->with('error', __('File upload failed.'));
}

                    }
                }
            }
            $employee = Employee::findOrFail($id);
            $input = $request->all();
            $employee->fill($input)->save();
            $employee = Employee::find($id);
            $user = User::where('id', $employee->user_id)->first();
            if (!empty($user)) {
                $user->name = $employee->name;
                $user->email = $employee->email;
                $user->save();
            }
            if ($request->salary) {
                return redirect()->route('setsalary.index')->with('success', 'Employee successfully updated.');
            }

            if (\Auth::user()->type != 'employee') {
                return redirect()->route('employee.index')->with('success', 'Employee successfully updated.');
            } else {
                return redirect()->route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))->with('success', 'Employee successfully updated.');
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {

        if (Auth::user()->can('delete employee')) {
            $employee = Employee::findOrFail($id);
            $user = User::where('id', '=', $employee->user_id)->first();
            $emp_documents = EmployeeDocument::where('employee_id', $employee->employee_id)->get();
            $employee->delete();
            $user->delete();
            $dir = storage_path('uploads/document/');
            foreach ($emp_documents as $emp_document) {
                $emp_document->delete();
                if (!empty($emp_document->document_value)) {
                    unlink($dir . $emp_document->document_value);
                }

            }

            return redirect()->route('employee.index')->with('success', 'Employee successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function show($id, Request $request )
    {

        if (\Auth::user()->can('view employee')) {
            try {
                $empId = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Employee Not Found.'));
            }
            $class = [];
            $student = [];
            $empId = Crypt::decrypt($id);
            $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
            if (Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $leaves = Leave::with('leaveType')->where('employee_id', '=', $empId)->get();
                // $class = Classes::where('owned_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                $payscale = EmployeeScale::where('created_by', \Auth::user()->creatorId())->get()->pluck('scale_no', 'id');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $leaves = Leave::with('leaveType')->where('employee_id', '=', $empId)->get();
                // $class = Classes::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $departments->prepend('Select Department', '');
                $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $designations->prepend('Select Designation', '');
                $payscale = EmployeeScale::where('created_by', \Auth::user()->ownedId())->get()->pluck('scale_no', 'id');
            }

            $employee = Employee::where('id', $empId)->with('employee_payscale_details')->first();
            $resignation = Resignation::where('employee_id', $empId)->first();
            $isResigned = !is_null($resignation);
            $employeesId = \Auth::user()->employeeIdFormat(!empty($employee) ? $employee->employee_id : '');
            $branches_school = SchoolDetails::where('branch_id', $employee->owned_by)->first();
            $emp_exp = EmpExperience::where('emp_id', $empId)->get();
            $emp_edu = EmpEducation::where('emp_id', $empId)->get();
            $emp_fac = EmpFacility::where('emp_id', $empId)->get();
            // dd($emp_child);
            if($request->print){
                $bodyHtml = view('employee.printProfile', compact('employee', 'emp_edu', 'emp_fac', 'emp_exp', 'branches_school', 'payscale', 'leaves', 'class', 'student', 'leavetypes', 'isResigned', 'resignation', 'employeesId', 'branches', 'departments', 'designations', 'documents'))->render();

                $finalHtml = '<html><head><style>body { font-family: sans-serif; font-size: 12px; }</style></head><body>' . $bodyHtml . '</body></html>';

                $options = new \Dompdf\Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($finalHtml);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->stream('employee_profile.pdf', ['Attachment' => false]);
            }
              return view('employee.show', compact('employee', 'emp_edu', 'emp_fac', 'emp_exp', 'branches_school', 'payscale', 'leaves', 'class', 'student', 'leavetypes', 'isResigned', 'resignation', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function employee_job_info(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->company_doj = $request->input('company_doj');
        $employee->probation_end = $request->input('probation_end');
        $employee->probation_period = $request->input('probation_period');
        $employee->is_res_ter = $request->input('includeIn_sal');
        $employee->security = $request->input('emp_security');
        $employee->pessi = $request->input('pessi');
        $employee->pessi_employer = $request->input('pessi_employer');
        $employee->eobi = $request->input('eobi');
        $employee->eobi_employer = $request->input('eobi_employer');
        $employee->save();
        return redirect()->back()->with('success', 'Job Info updated successfully.');
    }

    public function employee_exp_info(Request $request, $id)
{
    $validator = \Validator::make(
        $request->all(),
        [
            'exp_organization' => 'required',
            'exp_from' => 'required|date',
            'exp_to' => 'required|date|after:exp_from',
        ]
    );
    
    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->withInput()->with('error', $messages->first());
    }

    // Check if we're updating an existing record
    if ($request->input('experience_id')) {
        $emp_exp = EmpExperience::find($request->input('experience_id'));
        if (!$emp_exp) {
            return redirect()->back()->with('error', 'Experience record not found.');
        }
    } else {
        // Creating new record
        $emp_exp = new EmpExperience();
        $emp_exp->emp_id = $id;
    }

    $emp_exp->organization = $request->input('exp_organization');
    $emp_exp->designation = $request->input('exp_designation');
    $emp_exp->from = $request->input('exp_from');
    $emp_exp->to = $request->input('exp_to');
    $emp_exp->reason = $request->input('reason_of_leaving');
    $emp_exp->save();
    
    return redirect()->back()->with('success', 'Experience saved successfully.');
}

    public function employee_edu_info(Request $request, $id)
    {
        // $validator = \Validator::make(
        //     $request->all(),
        //     [
        //         'adm_date' => 'required|date',
        //         'passing_year' => [
        //             'required',
        //             'date',
        //             function ($attribute, $value, $fail) use ($request) {
        //                 $admDate = \Carbon\Carbon::parse($request->input('adm_date'));
        //                 $passingYear = \Carbon\Carbon::parse($value);

        //                 if ($passingYear->diffInYears($admDate) < 1) {
        //                     $fail('The Passing Year must be at least 1 year after the Admission Date.');
        //                 }
        //             }
        //         ],
        //     ]
        // );
        // if ($validator->fails()) {
        //     $messages = $validator->getMessageBag();
        //     return redirect()->back()->withInput()->with('error', $messages->first());
        // }
		$rules = ['degree_level' => 'required'];
		
		if ($request->degree_level !== 'illiterate') {
		    $rules += [
		        'institute_name' => 'required',
		        'adm_date' => 'required|digits:4|integer',
		        'passing_year' => 'required|digits:4|integer',
		        'grade' => 'required',
		    ];
		}
		
		$validator = \Validator::make($request->all(), $rules);
		
		if ($validator->fails()) {
		    return redirect()->back()
		        ->with('error', $validator->errors()->first());
		}
        // Check if updating or creating
        if ($request->filled('education_id')) {
            $emp_edu = \App\Models\EmpEducation::find($request->input('education_id'));
            if (!$emp_edu) {
                return redirect()->back()->with('error', 'Education record not found.');
            }
        } else {
            $emp_edu = new \App\Models\EmpEducation();
            $emp_edu->emp_id = $id;
        }

        $emp_edu->institute = $request->input('institute_name');
        $emp_edu->degree = $request->input('degree_level');
        $emp_edu->title = $request->input('degree_title');
        $emp_edu->subject = $request->input('subject');
        $emp_edu->adm_date = $request->input('adm_date');
        $emp_edu->pass_date = $request->input('passing_year');
        $emp_edu->grade = $request->input('grade');
        $emp_edu->reason = $request->input('reason_of_leaving');
        $emp_edu->save();

        $msg = $request->filled('education_id') ? 'Education Record Updated successfully.' : 'Education Record Added successfully.';
        return redirect()->back()->with('success', $msg);
    }
    public function employee_facility_info(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'facility_title' => 'required',
                'facility_from' => 'required|date',
                'facility_to' => 'required|date|after:facility_from',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withInput()->with('error', $messages->first());
        }

        // Check if updating or creating
        if ($request->filled('facility_id')) {
            $emp_facility = \App\Models\EmpFacility::find($request->input('facility_id'));
            if (!$emp_facility) {
                return redirect()->back()->with('error', 'Facility record not found.');
            }
        } else {
            $emp_facility = new \App\Models\EmpFacility();
            $emp_facility->emp_id = $id;
        }

        $emp_facility->title = $request->input('facility_title');
        $emp_facility->type = $request->input('facility_type');
        $emp_facility->given_date = $request->input('facility_from');
        $emp_facility->upto_date = $request->input('facility_to');
        $emp_facility->detail = $request->input('facility_detail');
        $emp_facility->save();

        $msg = $request->filled('facility_id') ? 'Facility Record Updated successfully.' : 'Facility Record Added successfully.';
        return redirect()->back()->with('success', $msg);
    }

    public function employee_child(Request $request, $id)
    {
        // dd($request->all());
        $validator =
            \Validator::make(
                $request->all(),
                (
                    [
                        'student' => 'required',
                        'class' => 'required',
                        'branches' => 'required',
                        'amount' => 'required',
                    ]
                )
            );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->withInput()->with('error', $messages->first());
        }
        $emp_children = new EmpChildrens();
        $emp_children->emp_id = $id;
        $emp_children->student_id = $request->input('student');
        $emp_children->class_id = $request->input('class');
        $emp_children->branch_id = $request->input('branches');
        $emp_children->amount = $request->input('amount');
        $emp_children->save();
        return redirect()->back()->with('success', 'Children Record Added successfully.');
    }


    public function employee_personal_info(Request $request, $id)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            // 'salute' => 'nullable|string',
            'name' => 'required|string',
            // 'f_name' => 'nullable|string',
            'cnic' => 'nullable|string',
            // 'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            // 'religion' => 'nullable|string',
            // 'blood_group' => 'nullable|string',
            'phone' => 'nullable|string',
            // 'email' => 'nullable|email',
            // 'eobi' => 'nullable|string',
            // 'ssc' => 'nullable|string',
            // 'present_address' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'branch_id' => 'nullable|integer',
            // 'department_id' => 'nullable|integer',
            // 'designation_id' => 'nullable|integer',
        ]);
        $validatedData = $request->all();
        $employee = Employee::findOrFail($id);

        if ($request->hasFile('profile_img')) {

            // 🔹 Delete old image (if exists)
            if (!empty($employee->profile_img)) {
                $oldPath = storage_path('emp_profile_images/' . $employee->profile_img);

                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // 🔹 Store new image
            $file = $request->file('profile_img');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $file->storeAs('emp_profile_images', $fileNameToStore);

            // 🔹 Save in DB
            $employee->profile_img = $fileNameToStore;
        }

        $employee->salute = $validatedData['salute'];
        $employee->name = $validatedData['name'];
        $employee->f_name = $validatedData['f_name'];
        $employee->cnic = $validatedData['cnic'];
        $employee->dob = $validatedData['dob'];
        $employee->gender = $validatedData['gender'];
        $employee->religion = $validatedData['religion'];
        $employee->blood_group = $validatedData['blood_group'];
        $employee->phone = $validatedData['phone'];
        $employee->category = $validatedData['category'];
        $employee->email = $validatedData['email'];
        $employee->eobi_id = $validatedData['eobi_id'];
        $employee->ssc_id = $validatedData['ssc_id'];
        $employee->present_address = $validatedData['present_address'];
        $employee->address = $validatedData['address'];
        // $employee->branch_id = $validatedData['branch_id'];
        if (Auth::user()->type == 'company') {
            $employee->department_id = $validatedData['department_id'];
            $employee->designation_id = $validatedData['designation_id'];
        }
        
        $employee->save();
        return redirect()->back()->with('success', 'Employee Info updated successfully.');
    }

    public function json(Request $request)
    {
        $designations = Designation::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();

        return response()->json($designations);
    }

    function employeeNumber()
    {
        $latest = Employee::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->employee_id + 1;
    }

    public function profile(Request $request)
    {
        if (\Auth::user()->can('manage employee profile')) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId());
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
            }
            if (!empty($request->designation)) {
                $employees->where('designation_id', $request->designation);
            }
            $employees = $employees->get();

            $brances = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $brances->prepend('All', '');

            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', '');

            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', '');

            return view('employee.profile', compact('employees', 'departments', 'designations', 'brances'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

public function employeedesiganddeprtment(Request $request)
    {
        $employees = Employee::where('department_id', $request->department_id)
            ->where('designation_id', $request->designation_id);

        if (!empty($request->branch_id)) {
            $employees->where('branch_id', $request->branch_id);
        }

        $employees = $employees->get()
            ->mapWithKeys(function ($employee) {
                $employeeNumber = !empty($employee->employee_id) ? \Auth::user()->employeeIdFormat($employee->employee_id) : '';
                $label = trim($employeeNumber . ' - ' . $employee->name, ' -');

                return [$employee->id => $label];
            })
            ->toArray();

        return response()->json($employees);
    }
    public function profileShow($id)
    {
        if (\Auth::user()->can('show employee profile')) {
            $empId = Crypt::decrypt($id);
            $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee = Employee::find($empId);
            $employeesId = \Auth::user()->employeeIdFormat($employee->employee_id);

            return view('employee.show', compact('employee', 'employeesId', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function lastLogin()
    {
        $users = User::where('created_by', \Auth::user()->creatorId())->get();

        return view('employee.lastLogin', compact('users'));
    }

    public function employeeJson(Request $request)
    {
        $employees = Employee::where('branch_id', $request->branch)->get()->pluck('name', 'id')->toArray();

        return response()->json($employees);
    }

    public function getdepartment(Request $request)
    {
        if (Auth::user()->type == 'company') {
            if ($request->branch_id == 0) {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
            }
        } else {
            if ($request->branch_id == 0) {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
            }
        }

        return response()->json($departments);
    }
    public function getempDesignation(Request $request)
    {
        // dd($request->all());
        $employee = Employee::where('id', $request->id)->first();
        if (Auth::user()->type == 'company') {
            if ($request->id == 0) {
                $designation = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $designation = Designation::where('created_by', '=', \Auth::user()->creatorId())->where('id', $employee->designation_id)->get()->pluck('name', 'id')->toArray();
            }
        } else {
            if ($request->id == 0) {
                $designation = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $designation = Designation::where('created_by', '=', \Auth::user()->creatorId())->where('id', $employee->designation_id)->get()->pluck('name', 'id')->toArray();
            }
        }

        return response()->json($designation);
    }
    public function getempDepartment(Request $request)
    {
        // dd($request->all());
        $employee = Employee::where('id', $request->id)->first();
        if (Auth::user()->type == 'company') {
            if ($request->id == 0) {
                $department = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $department = Department::where('created_by', '=', \Auth::user()->creatorId())->where('id', $employee->department_id)->get()->pluck('name', 'id')->toArray();
            }
        } else {
            if ($request->id == 0) {
                $department = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
            } else {
                $department = Department::where('created_by', '=', \Auth::user()->creatorId())->where('id', $employee->department_id)->get()->pluck('name', 'id')->toArray();
            }
        }

        return response()->json($department);
    }

    public function joiningletterPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $obj = [
            'date' => \Auth::user()->dateFormat($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
        ];

        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterpdf', compact('joiningletter', 'employees'));

    }
    public function joiningletterDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);



        $obj = [
            'date' => \Auth::user()->dateFormat($date),

            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
            //

        ];
        // dd($obj);
        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterdocx', compact('joiningletter', 'employees'));

    }
    public function ExpCertificatePdf($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->first();
        $experience_certificate = ExperienceCertificate::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        // dd($employees->salaryType->name);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff = date_diff($date1, $date2);
        $duration = $diff->format("%a days");

        if (!empty($termination->termination_date)) {

            $obj = [
                'date' => \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }


        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatepdf', compact('experience_certificate', 'employees'));

    }
    public function ExpCertificateDoc($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->first();
        $experience_certificate = ExperienceCertificate::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff = date_diff($date1, $date2);
        $duration = $diff->format("%a days");
        if (!empty($termination->termination_date)) {
            $obj = [
                'date' => \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }

        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatedocx', compact('experience_certificate', 'employees'));

    }
    public function NocPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' => \Auth::user()->dateFormat($date),
            'employee_name' => !empty($employees) ? $employees->name : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocpdf', compact('noc_certificate', 'employees'));

    }
    public function NocDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();
        $date = date('Y-m-d');
        $employees = Employee::find($id);
        $settings = Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' => \Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocdocx', compact('noc_certificate', 'employees'));

    }

    //Export
    public function export()
    {
        $name = 'employee_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeeExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    //import
    public function importFile()
    {
        return view('employee.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $employees = (new EmployeesImport())->toArray(request()->file('file'))[0];
        $totalCustomer = count($employees) - 1;
        $errorArray = [];

        for ($i = 1; $i <= count($employees) - 1; $i++) {
            $employee = $employees[$i];

            if ($employee[5] == null) {
                return redirect()->back()->with('error', __('Email Filed is Required'));
            }
            $employeeByEmail = Employee::where('email', $employee[5])->first();
            $userByEmail = User::where('email', $employee[5])->first();
            // dd($userByEmail);

            if (!empty($employeeByEmail) && !empty($userByEmail)) {

                $employeeData = $employeeByEmail;
            } else {


                $user = new User();
                $user->name = $employee[0];
                $user->email = $employee[5];
                $user->password = Hash::make($employee[6]);
                $user->type = 'employee';
                $user->lang = 'en';
                $user->created_by = \Auth::user()->creatorId();
                $user->save();
                $user->assignRole('Employee');
                $employeeData = new Employee();
                $employeeData->employee_id = $this->employeeNumber();
                $employeeData->user_id = $user->id;
            }


            $employeeData->name = $employee[0];
            $employeeData->dob = $employee[1];
            $employeeData->gender = $employee[2];
            $employeeData->phone = $employee[3];
            $employeeData->address = $employee[4];
            $employeeData->email = $employee[5];
            $employeeData->password = Hash::make($employee[6]);
            $employeeData->employee_id = $this->employeeNumber();
            $employeeData->branch_id = $employee[8];
            $employeeData->department_id = $employee[9];
            $employeeData->designation_id = $employee[10];
            $employeeData->company_doj = $employee[11];
            $employeeData->account_holder_name = $employee[12];
            $employeeData->account_number = $employee[13];
            $employeeData->bank_name = $employee[14];
            $employeeData->bank_identifier_code = $employee[15];
            $employeeData->branch_location = $employee[16];
            $employeeData->tax_payer_id = $employee[17];
            $employeeData->created_by = \Auth::user()->creatorId();

            if (empty($employeeData)) {

                $errorArray[] = $employeeData;
            } else {

                $employeeData->save();
            }
        }

        $errorRecord = [];

        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg'] = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg'] = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }


    public function AssignLeaveAfterProbation(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $today = now();
        $joiningDate = \Carbon\Carbon::parse($employee->joining_date);
        $probationEndDate = \Carbon\Carbon::parse($employee->probation_end);
        $already_assigned = EmployeeLeaves::where('employee_id', $employee->id)
            ->whereNotNull('annual_total')
            ->first();

        if ($already_assigned) {
            return redirect()->route('employee.index')->with('error', __('Employee leaves already assigned.'));
        }

        $annualTotal = 0;
        $casualTotal = 0;
        if($employee->category == 'Regular'){
            if ($joiningDate->year < $today->year || $probationEndDate->year < $today->year) {
                $annualTotal = 12 * 2.5;
                $casualTotal = 12 * 0.8;
            } elseif ($today->greaterThanOrEqualTo($probationEndDate)) {
                $remainingAnnualMonths = 12 - $probationEndDate->month + 1;
                $annualTotal = $remainingAnnualMonths * 2.5;

                $remainingCasualMonths = 12 - $today->month + 1;
                $casualTotal = $remainingCasualMonths * 0.8;
            } else {
                $remainingCasualMonths = 12 - $today->month + 1;
                $casualTotal = $remainingCasualMonths * 0.8;

                return redirect()->route('employee.index')->with('error', __('Employee probation not ended yet. Only casual leaves considered.'));
            }
        }
        else{
                $casualTotal = 0;
                $annualTotal = 0;
            }
        $emp_leave = EmployeeLeaves::firstOrNew(['employee_id' => $employee->id]);
        $emp_leave->annual_total = $annualTotal;
        $emp_leave->casual_total = $casualTotal;
        $emp_leave->save();

        return redirect()->route('employee.index')->with('success', __('Employee leaves assigned successfully.'));
    }

    public function employeeData(Request $request)
    {
        if (Auth::user()->type == 'company') {
            $employee = Employee::where('id', $request->id)->where('created_by', '=', \Auth::user()->creatorId())->first()->toArray();
        } else {
            $employee = Employee::where('id', $request->id)->where('owned_by', '=', \Auth::user()->ownedId())->first()->toArray();
        }
        return response()->json($employee);
    }
    public function emp_rejoin_show($id)
    {
        $emp = Employee::find($id);
        $employee = Employee::get()->pluck('name', 'id');
        $branches = User::where('id', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments = Department::get()->pluck('name', 'id');
        $departments->prepend('Select Department', '');
        $designation = Designation::get()->pluck('name', 'id');
        $designation->prepend('Select Department', '');
        return view('employee.emp_rejoin', compact('emp', 'employee', 'departments', 'branches', 'designation'));
    }
    public function emp_rejoin(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'employee_id' => 'required',
                'old_department' => 'required',
                'old_designation' => 'required',
                'new_department' => 'required',
                'new_designation' => 'required',
                'rejoin_date' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        \DB::beginTransaction();
        try {

            $emp = Employee::find($request->employee_id);
            // dd(@$emp->resignation,@$emp->termination,$emp,$request->all());
            if ($emp->resignation == null && $emp->termination == null) {
                return redirect()->back()->with('error', __('Employee is not resigned or terminated.'));
            }
            if ($emp->resignation != null) {
                $leaving_date = @$emp->resignation->last_attendance_date;
            } elseif ($emp->termination != null) {
                $leaving_date = @$emp->termination->termination_date;
            }
            $emp_rejoin = new EmployeeRejoin();
            $emp_rejoin->employee_id = $request->employee_id;
            $emp_rejoin->old_department_id = $request->old_department;
            $emp_rejoin->old_designation_id = $request->old_designation;
            $emp_rejoin->new_department_id = $request->new_department;
            $emp_rejoin->new_designation_id = $request->new_designation;
            $emp_rejoin->prev_leaving_date = $leaving_date;
            $emp_rejoin->prev_doj = $emp->company_doj;
            $emp_rejoin->rejoin_date = $request->rejoin_date;
            $emp_rejoin->created_by = \Auth::user()->creatorId();
            $emp_rejoin->owned_by = \Auth::user()->ownedId();
            $emp_rejoin->save();
            if ($emp_rejoin) {
                $emp->department_id = $request->new_department;
                $emp->designation_id = $request->new_designation;
                $emp->company_doj = $request->rejoin_date;
                $emp->is_res_ter = 0;
                $emp->save();
            }
            //calculate and leaves assign according to policy
            $today = now();
            $joiningDate = \Carbon\Carbon::parse($emp_rejoin->rejoin_date);
            $already_assigned = EmployeeLeaves::where('employee_id', $emp->id)
                ->whereNotNull('annual_total')
                ->first();

            $annualTotal = 0;
            $casualTotal = 0;
            if($emp->category == 'Regular'){
                $remainingCasualMonths = 12 - $today->month ;
                $casualTotal = $remainingCasualMonths * 0.8;
                $remainingAnnualMonths = 12 - $today->month;
                $annualTotal = $remainingAnnualMonths * 2.5;
            }else{
                $casualTotal = 0;
                $annualTotal = 0;
            }
            if (!$already_assigned) {
                $emp_leave = EmployeeLeaves::firstOrNew(['employee_id' => $emp->id]);
                $emp_leave->annual_total = $annualTotal;
                $emp_leave->casual_total = $casualTotal;
                $emp_leave->save();
            }else{
                $emp_leave = EmployeeLeaves::where('employee_id', $emp->id)->first();
                $emp_leave->annual_total = $annualTotal;
                $emp_leave->casual_total = $casualTotal;
                $emp_leave->save();
            }
            \DB::commit();
            return redirect()->back()->with('success', __('Employee Rejoin Successfully.'));
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }

    public function fetchStudentAmount(Request $request)
    {
        $studentId = $request->input('id');

        $studentClass = StudentRegistration::find($studentId);
        $classid = $studentClass->id;
        $tution_fee = FeeHead::where('fee_head', 'TUITION FEE')->first();
        $headid = $tution_fee->id;
        // dd($student->class_id);
        $amount = ClassWiseFee::where('class_id', $classid)
            ->where('head_id', $headid)
            ->first();
        // dd($headid,$classid,$amount);
        if ($amount) {
            return response()->json(['amount' => $amount->amount], 200);
        }
        return response()->json(['amount' => 0], 200);
    }

     public function destroyEmployeeExperience($id)
    {
        $exp = \App\Models\EmpExperience::findOrFail($id);
        $exp->delete();
        return redirect()->back()->with('success', 'Experience deleted successfully.');
    }

    public function destroyEmployeeEducation($id)
    {
        $edu = \App\Models\EmpEducation::findOrFail($id);
        $edu->delete();
        return redirect()->back()->with('success', 'Education deleted successfully.');
    }
    
    public function destroyEmployeeFacility($id)
    {
        $fac = \App\Models\EmpFacility::findOrFail($id);
        $fac->delete();
        return redirect()->back()->with('success', 'Facility deleted successfully.');
    }

    public function saveEmergencyContacts(Request $request, $id)
    {
        // check / validation  if empty then error return
        $validator = \Validator::make(
            $request->all(),
            [
                 'contacts' => 'required|array',
                'contacts.*.contact_name' => 'nullable|string',
                'contacts.*.relationship' => 'nullable|string',
                'contacts.*.phone' => 'nullable|string',
            ],
            [
                'contacts.required' => 'At least one contact is required.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json([
                'status' => false,
                'message' => $messages->first()
            ]);
        }   

        foreach ($request->contacts as $contact) {

            if (!empty($contact['contact_name']) || !empty($contact['phone'])) {

                EmployeeEmergencyContact::create([
                    'employee_id'   => $id,
                    'contact_name'  => $contact['contact_name'] ?? null,
                    'relationship'  => $contact['relationship'] ?? null,
                    'phone'         => $contact['phone'] ?? null,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Contacts saved successfully'
        ]);
    }

    public function getEmergencyContacts($id)
    {
        return EmployeeEmergencyContact::where('employee_id', $id)->get();
    }

    public function deleteEmergencyContact($id)
    {
        EmployeeEmergencyContact::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deleted successfully'
        ]);
    }

    public function EmployeeChildrenCnic(Request $request)
    {
        $emp=Employee::find($request->employee_id);
    
        $registrations = StudentRegistration::with('session', 'class', 'branches','fee_structure')->where(function ($q) use ($emp) {
            $q->where('fathercnic', $emp->cnic)
            ->orWhere('mothercnic', $emp->cnic);
        })->get();
        $pattern = '%TUITION%';
        $head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
        return response()->json(['siblings' => $registrations,'head' => $head ]);
    }
}
