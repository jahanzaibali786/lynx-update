<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySalaryAttendance;
use App\Models\EmployeeMonthlySalaryHeads;
use App\Models\EmployeePayscaleDetail;
use App\Models\EmployeeScale;
use App\Models\EmployeeScaleHeads;
use App\Models\Resignation;
use App\Models\EmployeeLeaves;
use App\Models\User;
use App\Models\Utility;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrDataImportController extends Controller
{
    public function showHrForm()
    {
        return view('data_import.hrform');
    }
    public function importHRData(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt',
            'data_type' => 'required|string',
        ]);

        $file = $request->file('excel_file');
        $dataType = $request->input('data_type');
        set_time_limit(0);
        if ($dataType == 'employees') {
            return $this->EmployeeImport($file, $request);
        } else if ($dataType == 'emp_scales_create') {
            return $this->EmpScalesCreateImport($file, $request);
        } else if ($dataType == 'emp_scale') {
            return $this->EmpScaleImport($file, $request);
        } else if ($dataType == 'emp_security') {
            return $this->EmpSecurityImport($file, $request);
        } else if ($dataType == 'emp_salary') {
            return $this->EmpSalaryImport($file, $request);
        } else if ($dataType == 'employee_scale') {
            return $this->EmpScaleAttachImport($file, $request);
        } else if ($dataType == 'emp_leaves') {
            return $this->EmpLeavesImport($file, $request);
        } else {
            return redirect()->back()->with('error', 'No Data Type Selected');
        }
    }

     public function EmpScalesCreateImport($file, $request){
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);
        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];
        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 2000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    // dd($all_data);
                    $record_status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    // 0 => scale no
                    // 1 => inital basics
                    // 2 => house rent
                    // 3 => medical allowance
                    // 4 => gross

                    $scale = EmployeeScale::where('scale_no', $all_data[0])->first();
                    // dd($scale);
                    if ($scale) {
                        $reason = 'Scale Already Exists';
                        $duplication_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }else{
                        $employee_scale = new EmployeeScale();
                        $employee_scale->scale_no = $all_data[0];
                        $employee_scale->type = 'visiting';
                        $employee_scale->effect_from = date('Y-m-d');
                        $employee_scale->adhoc = 0;
                        //academic dep
                        $dept = Department::where('name', 'like', '%Academic%')->first();
                        $employee_scale->department_id = $dept->id;
                        $employee_scale->status = 1;
                        $employee_scale->owned_by = 2;
                        $employee_scale->created_by = 2;
                        $employee_scale->save();
                        if($employee_scale){
                            for($i=1; $i<=3; $i++){
                                if($i == 1){
                                    $head_id = 2;
                                    $head_value = $all_data[1];
                                }else if($i == 2){
                                    $head_id = 4;
                                    $head_value = $all_data[2];
                                }else if($i == 3){
                                    $head_id = 3;
                                    $head_value = $all_data[3];
                                }else{
                                    dd('error');
                                }
                                $schead = EmployeeScaleHeads::create([
                                    'scale_id' => $employee_scale->id,
                                    'scale_no' => $employee_scale->scale_no,
                                    'head' => $head_id,
                                    'head_value' => $head_value,
                                    'owned_by' => 2,
                                    'created_by' => 2,
                                ]);
                                // dd($schead);
                            }
                        }
                    }
                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }
            fclose($handle);
            DB::commit();
            if (!empty($skip_data)) {
                $export_filename = 'employee_scale_create_errors_' . time() . '.csv';
                $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                $error_file = fopen($error_filepath, 'w+');
                fputcsv($error_file, $header);
                foreach ($skip_data as $row) {
                    fputcsv($error_file, $row);
                }
                fclose($error_file);
                return response()->download($error_filepath)->deleteFileAfterSend(true);
            }
            return redirect()->back()->with('message', "{$success_counter} employee scale(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
        }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function EmpLeavesImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 2000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    $record_status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    // 0 emp name
                    // 1 department
                    // jan 25
                    // 2 annual ob
                    // 3 annual avail
                    // 4 annual balanace
                    // 5 casual ob
                    // 6 casual avail
                    // 7 casual balance
                    //8 empty
                    // feb 25
                    // 9 annual ob
                    // 10 annual avail
                    // 11 annual balance
                    // 12 casual ob
                    // 13 casual avail
                    // 14 casual balance
                    // 15 empty
                    // march 25
                    // 16 annual ob
                    // 17 annual avail
                    // 18 annual balance
                    // 19 casual ob
                    // 20 casual avail
                    // 21 casual balance
                    // 22 empty
                    // april 25
                    // 23 annual ob
                    // 24 annual avail
                    // 25 annual balance
                    // 26 casual ob
                    // 27 casual avail
                    // 28 casual balance
                    // 29 empty
                    // may 25
                    // 30 annual ob
                    // 31 annual avail
                    // 32 annual balance
                    // 33 casual ob
                    // 34 casual avail
                    // 35 casual balance
                    // dd($all_data);
                    $totalcasual = 10;
                    if (strtolower($all_data[1]) == 'academic') {
                        $totalannual = 0;
                    } else {
                        $totalannual = 30;
                    }
                    // dd($totalannual, $totalcasual, $all_data);
                    $department = Department::where('name', 'like', '%' . $all_data[1] . '%')->first();
                    if (!$department) {
                        dd($all_data, 'department');
                        $reason = 'Department Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $empNameclean = trim(
                        preg_replace(
                            '/\s+/', ' ',
                            preg_replace('/\s*\(.*?\)\s*/', ' ', $all_data[0])
                        )
                    );
                    if(strtolower($empNameclean) == 'Grand Total'){
                        continue;
                    }
                    $employee = Employee::where('name', 'like', '%' . $empNameclean. '%')->where('department_id', $department->id)->first();
                    if (!$employee) {
                        dd($all_data, 'employee',$empNameclean);
                        $reason = 'Employee Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }

                    $employee_leaves = EmployeeLeaves::where('employee_id', $employee->id)->first();

                    // dd($employee_leaves,$employee);
                    if (!$employee_leaves) {
                        if ($employee->id == 48) {
                            continue;
                        }else {
                            $employee_leaves = EmployeeLeaves::create([
                                'employee_id' => $employee->id,
                                'casual_total' => $totalcasual,
                                'annual_total' => $totalannual,
                                'owned_by' => $employee->owned_by,
                                'created_by' => $employee->created_by,
                            ]);
                            // dd($all_data, 'employee leaves', $employee);
                            // $reason = 'Employee Leaves Not Found';
                            // $error_counter++;
                            // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            // $count++;
                            // continue;
                        }
                    }
                    $consumed_annual = $totalannual - $all_data[32];
                    $consumed_casual = $totalcasual - $all_data[35];
                    $employee_leaves->casual_consumed = $consumed_casual;
                    $employee_leaves->annual_consumed = $consumed_annual;
                    $employee_leaves->save();
                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'employee_leaves_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, $header);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} Employee Leaves(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    public function EmpScaleAttachImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 2000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    $record_status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    //0 => branch name
                    // 1=> emp name
                    // 2 => emp dept
                    // 3 => emp desig
                    // 4 => scale no
                    // 5 => effect from
                    // 6 => emp account no
                    // 7 => paymod
                    // 8 => bank from pay
                    // 9 => security
                    // 10 => itax
                    // 11 => Eobi
                    // 12 => eobi employer
                    // 13 => Pessi
                    // 14 => pessi employer
                    // 15 => other deduction
                    // 16 => advance
                    // 17 => net
                    $branch = User::where('name', 'like', '%' . $all_data[0] . '%')->first();
                    if (!$branch) {
                        dd($all_data, 'branch');
                        $reason = 'Branch Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $department = Department::where('name', 'like', '%' . $all_data[2] . '%')->first();
                    if (!$department) {
                        dd($all_data, 'department');
                        $reason = 'Department Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $designation = Designation::where('name', 'like', $all_data[3] . '%')->first();
                    if (!$designation) {
                        dd($all_data, 'designation');
                        $reason = 'Designation Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $employee = Employee::where('name', 'like', '%' . $all_data[1] . '%')->where('owned_by', $branch->id)->first();
                    $employee_scale = EmployeeScale::where('scale_no', $all_data[4])->where('department_id', $department->id)->first();
                    if (!$employee_scale) {
                        dd($all_data, 'scale');
                        $reason = 'Scale Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $scale =EmployeePayscaleDetail::where('employee_id', $employee->id)->orderBy('id', 'desc')->first();
                    $scale->pay_scale_id = $employee_scale->id;
                    $scale->save();
                    // if (!$employee) {
                    //     // dd($all_data, 'employee');
                    //     $user = new User();
                    //     $user->name = $all_data[1];
                    //     $user->email = '';
                    //     $user->password = Hash::make('123456');
                    //     $user->type = 'employee';
                    //     $user->is_active = 1;
                    //     $user->owned_by = $branch->id;
                    //     $user->created_by = 2;
                    //     $user->save();
                    //     $emp = new Employee();
                    //     $emp->name = $all_data[1];
                    //     $emp->employee_id = $all_data[0];
                    //     $emp->user_id = $user->id;
                    //     $emp->branch_id = $branch->id;
                    //     $emp->category = 'Regular';
                    //     $emp->company_doj = date('Y-m-d', strtotime($all_data[5]));
                    //     $emp->department_id = $department->id;
                    //     $emp->designation_id = $designation->id;
                    //     $emp->owned_by = $branch->id;
                    //     $emp->is_active = 1;
                    //     $emp->is_res_ter = 0;
                    //     $emp->created_by = 2;
                    //     $emp->save();
                    //     $employee = Employee::find($emp->id);
                    //     // $reason = 'Employee Not Found';
                    //     // $error_counter++;
                    //     // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                    //     // $count++;
                    //     // continue;
                    // }
                    // $employee_scale = EmployeeScale::where('scale_no', $all_data[4])->first();
                    // if (!$employee_scale) {
                    //     if($all_data[4] == '617ADMH'){
                    //         $reason = 'Scale Not Found';
                    //         $error_counter++;
                    //         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                    //         $count++;
                    //         continue;  
                    //     }
                    //     dd($all_data, 'scale');
                    //     $reason = 'Scale Not Found';
                    //     $error_counter++;
                    //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                    //     $count++;
                    //     continue;
                    // }
                    // dd($employee, $branch, $employee_scale,$department,$designation);

                    // account Entry
                    // $emp_account = EmployeePayscaleDetail::where('id', 5)->first();
                    // $duplicate_scale_attach = EmployeePayscaleDetail::where('employee_id', $employee->id)
                    // ->where('pay_scale_id', $employee_scale->id)
                    // ->first();
                    // $bank = BankAccount::where('bank_name', 'like', '%' . $all_data[8] . '%',)->first();
                    // if (!$bank) {
                    //     dd($all_data, 'bank');
                    //     $reason = 'Bank Not Found';
                    //     $error_counter++;
                    //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                    //     $count++;
                    //     continue;
                    // }
                    // if ($duplicate_scale_attach) {
                    //     // dd($all_data, 'duplicate');
                    //     $reason = 'Scale Already Attached';
                    //     $duplication_counter++;
                    //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                    //     $count++;
                    //     continue;
                    // }
                    // // dd($bank,$all_data[8]);
                    // $emps =  EmployeePayscaleDetail::create([
                    //     'employee_id' => $employee->id,
                    //     'appletter' => 5,
                    //     'paymode' => $all_data[6],
                    //     'account_number' => $all_data[7],
                    //     'account_id' => $bank->id,
                    //     'department_id' => $department->id,
                    //     'pay_scale_id' => $employee_scale->id,
                    //     'effect_from' => $all_data[5],
                    //     'drns' => 0,
                    //     'conv' => 0,
                    //     'misc' => 0,
                    //     'chaild_concession' => 0,
                    //     'emp_sec' => $all_data[9],
                    //     'security_receive_account' => $emp_account->security_receive_account,
                    //     'itax' => $all_data[10],
                    //     'tax_payable_account' => $emp_account->tax_payable_account,
                    //     'eobi' => $all_data[11],
                    //     'eobi_employer' => $all_data[12],
                    //     'eobi_payable_account' => $emp_account->eobi_payable_account,
                    //     'pessi' => $all_data[13],
                    //     'pessi_employer' => $all_data[14],
                    //     'pessi_payable_account' => $emp_account->pessi_payable_account,
                    //     'other_deduction' => $all_data[15],
                    //     'other_dedu_payable_account' => $emp_account->other_dedu_payable_account,
                    //     'advance' => $all_data[16],
                    //     'advance_payable_account' => $emp_account->advance_payable_account,
                    //     'net' => round($all_data[17]),
                    //     'net_payable_account' => $emp_account->net_payable_account,
                    // ]);
                    // dd($all_data, 'out',$emps);

                    // if ($employee) {
                    //     // $employee->is_res_ter = 0;
                    //     // $employee->branch_id = $branch->id;
                    //     // $employee->designation_id = $designation->id;
                    //     // $employee->department_id = $department->id;
                    //     $employee->save();
                    //     // /emp leaves
                    //     if (strtolower($department->name) == 'academic') {
                    //         $annual_total = 0;
                    //     } else {
                    //         $annual_total = 30;
                    //     }
                    //     EmployeeLeaves::create([
                    //         'employee_id' => $employee->id,
                    //         'casual_total' => 10,
                    //         'annual_total' => $annual_total,
                    //         'owned_by' => $branch->id,
                    //         'created_by' => 2,
                    //     ]);
                    // }
                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'employee_scale_attach_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, $header);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} Scale Attached(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function EmpSalaryImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 5000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    $record_status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    $department = Department::where('name', 'like', '%' . $all_data[0] . '%')->first();
                    if (!$department) {
                        $reason = 'Department Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $employee = Employee::where('employee_id', $all_data[1])->first();
                    if (!$employee) {
                        $reason = 'Employee Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $salary_date = date('Y-m-d', strtotime('01-01-2021'));
                    $duplicate_salary = EmployeeMonthlySalary::where('employee_id', $employee->id)
                        ->where('salary_date', date('Y-m-d', strtotime($salary_date)))
                        ->first();
                    if ($duplicate_salary) {
                        $reason = 'Salary Already Exists';
                        $duplication_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $employee_scale = EmployeeScale::where('scale_no', $all_data[2])->where('department_id', $department->id)->first();
                    if (!$employee_scale) {
                        $reason = 'Scale Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $created_at = date('Y-m-d H:i:s', strtotime($salary_date));
                    $employee_salary = new EmployeeMonthlySalary();
                    $employee_salary->employee_id = $employee->id;
                    $employee_salary->department_id = $employee->department_id;
                    $employee_salary->scale_id = $employee_scale->id;
                    $employee_salary->scale_no = $employee_scale->scale_no;
                    $employee_salary->sal_days = $all_data[34];
                    $employee_salary->salary_date = $salary_date;
                    $employee_salary->basics = $all_data[7];
                    $employee_salary->conv = $all_data[9];
                    $employee_salary->gross = $all_data[14];
                    $employee_salary->emp_sec = $all_data[15];
                    $employee_salary->it = $all_data[16];
                    $employee_salary->eobi = $all_data[17];
                    $employee_salary->pessi = $all_data[21];
                    $employee_salary->dedu = $all_data[19];
                    $employee_salary->net_pay = $all_data[23];
                    $employee_salary->pessi_employer = $all_data[24];
                    $employee_salary->eobi_employer = $all_data[25];
                    $employee_salary->prc_final = 1;
                    $employee_salary->sal_final = 1;
                    $employee_salary->owned_by = $employee->owned_by;
                    $employee_salary->created_by = 2;
                    $employee_salary->save();
                    $employee_salary->created_at = $created_at;
                    $employee_salary->updated_at = $created_at;
                    $employee_salary->save();
                    $salary_head_names = ['Initial Basic', 'House Rent', 'Medical Allowance', 'Special'];
                    $salary_head_indexes = [6, 8, 10, 11];
                    $salaryheaddb_id = [2, 4, 3, 5];

                    $combined_heads_array = [];

                    foreach ($salary_head_indexes as $i => $index) {
                        $combined_heads_array[] = [
                            'head_name' => $salary_head_names[$i],
                            'head_id' => $salaryheaddb_id[$i],
                            'value' => $all_data[$index] ?? 0,
                        ];
                    }
                    $combined_heads_array = array_filter($combined_heads_array, function ($head) {
                        return $head['value'] != 0 && $head['value'] != '';
                    });
                    foreach ($combined_heads_array as $head) {
                        $sal_head = EmployeeMonthlySalaryHeads::create([
                            'employee_id' => $employee->id,
                            'scale_id' => $employee_scale->id,
                            'scale_no' => $employee_scale->scale_no,
                            'sal_id' => $employee_salary->id,
                            'salary_date' => $salary_date,
                            'head_id' => $head['head_id'],
                            'head_value' => $head['value'],
                            'owned_by' => $employee->owned_by,
                            'created_by' => 2,
                        ]);
                        $sal_head->created_at = $created_at;
                        $sal_head->updated_at = $created_at;
                        $sal_head->save();
                    }
                    if ($employee_salary) {
                        $salary_attendance = EmployeeMonthlySalaryAttendance::create([
                            'employee_id' => $employee->id,
                            'working_days' => $all_data[34],
                            'total_casual' => $all_data[28],
                            'bal_casual' => (int) $all_data[28] - (int) $all_data[29],
                            'total_annual' => (int) $all_data[31],
                            'bal_annual' => (int) $all_data[31] - (int) $all_data[32],
                            'absents' => 30 - (int) $all_data[34],
                            'leave' => (int) $all_data[29] + (int) $all_data[32],
                            'month_days' => 30,
                            'for_month_of' => $salary_date,
                            'gm_final' => 1,
                            'sal_final' => 1,
                            'adm_final' => 1,
                            'accountant_finalize' => 1,
                            'lock_status' => 1,
                            'owned_by' => $employee->owned_by,
                            'created_by' => 2,
                        ]);
                        $salary_attendance->created_at = $created_at;
                        $salary_attendance->updated_at = $created_at;
                        $salary_attendance->save();
                        // dd($all_data,$salary_attendance);
                    }
                    $allAccounts = [
                        [
                            'type' => 'Expenses',
                            'sub_type' => 'Payroll Expenses',
                            'name' => 'Salary Expense (Basic + Med + Rent + Sec)',
                            'debit' =>
                               (float) ($all_data[6] ?? 0) + // Initial Basic
                               (float) ($all_data[8] ?? 0) + // House Rent
                               (float) ($all_data[10] ?? 0) + // Medical Allowance
                               (float) ($all_data[11] ?? 0) + // Special
                               (float) ($employee_salary->dedu ?? 0) , 
                            'credit' => 0,
                        ],
                        // Dr: Employer PASSI
                        [
                            'type' => 'Expenses',
                            'sub_type' => 'Payroll Expenses',
                            'name' => 'Salary Expense - Employer PASSI',
                            'debit' => $employee_salary->pessi_employer,
                            'credit' => 0,
                        ],
                        // Dr: Employer EOBI
                        [
                            'type' => 'Expenses',
                            'sub_type' => 'Payroll Expenses',
                            'name' => 'Salary Expense - Employer EOBI',
                            'debit' => $employee_salary->eobi_employer,
                            'credit' => 0,
                        ],
                        // Cr: Employee Security Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Employee Security Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->emp_sec,
                        ],
                        // Cr: Income Tax Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Tax Payable (Income Tax)',
                            'debit' => 0,
                            'credit' => $employee_salary->it,
                        ],
                        // Cr: EOBI Payable (Employee)
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'EOBI Payable (Employee)',
                            'debit' => 0,
                            'credit' => $employee_salary->eobi,
                        ],
                        // Cr: PASSI Payable (Employee)
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'PASSI Payable (Employee)',
                            'debit' => 0,
                            'credit' => $employee_salary->pessi,
                        ],
                        // Cr: Loan Deduction Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Loan Deduction Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->dedu_loan ?? 0, // or $all_data[?]
                        ],
                        // Cr: Other Deduction Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Other Deduction Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->dedu_other ?? 0, // or $all_data[?]
                        ],
                        // Cr: Net Salary Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Net Salary Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->net_pay,
                        ],
                        // Cr: Employer PASSI Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Employer PASSI Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->pessi_employer,
                        ],
                        // Cr: Employer EOBI Payable
                        [
                            'type' => 'Liabilities',
                            'sub_type' => 'Payables',
                            'name' => 'Employer EOBI Payable',
                            'debit' => 0,
                            'credit' => $employee_salary->eobi_employer,
                        ],
                    ];
                    $filteredAccounts = array_filter($allAccounts, function($item) {
                        return ($item['debit'] ?? 0) > 0 || ($item['credit'] ?? 0) > 0;
                    });
                    // dd($filteredAccounts);
                    $journal_data = [
                        'date' => $salary_date,
                        'reference' => 'SAL-' . $employee_salary->id,
                        'employee_name' => $employee->name,
                        'no' => $employee_salary->id,
                        'salary_month' => date('F Y', strtotime($salary_date)),
                        'id' => $employee_salary->id,
                        'category' => 'salary',
                        'user_id' => $employee->user_id,
                        'user_type' => 'employee',
                        'owned_by' => $employee_salary->owned_by,
                        'created_by' => $employee_salary->created_by,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                        'accounts'      => array_values($filteredAccounts),   
                    ];
                    $journal = Utility::Salaryjrentry($journal_data);
                    $employee_salary->voucher_id = $journal;
                    $employee_salary->save();
                    // dd($employee_scale,$employee_salary,$combined_heads_array,$sal_head,'out');
                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'employee_salary_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, $header);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} Salary(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function EmpSecurityImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 3000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    $record_status = 'Error';
                    $reason = '';
                    $branch = User::where('name', 'like', '%' . $all_data[0] . '%')->first();
                    if (!$branch) {
                        $reason = 'Branch Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $designation = Designation::where('name', 'like', '%' . $all_data[2] . '%')->first();
                    if (!$designation) {
                        $reason = 'Designation Not found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $user = User::where('name', 'like', '%' . $all_data[1] . '%')->where('type', 'employee')->first();
                    if (!$user) {
                        $reason = 'Employee Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    } else {
                        $employee = Employee::where('user_id', $user->id)
                            ->where('designation_id', $designation->id)
                            ->first();
                    }
                    $employee = Employee::whlere('employee_id', $all_data[0])->first();
                    if (!$employee) {
                        $reason = 'Employee Not Found';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }

                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'employee_security_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, $header);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} Security Challan(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    public function EmpScaleImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 2000, ",")) !== false) {
                    if ($count == 0) {
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }
                    $record_status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    if (empty($all_data[9])) {
                        $reason = 'Empty department name';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $Department = Department::where('name', 'like', '%' . $all_data[9] . '%')->first();
                    if (!$Department) {
                        dd($all_data);
                        $reason = 'Department Not Exist';
                        $duplication_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $salary_head_names = ['Initial Basic', 'House Rent', 'Medical Allowance', 'Special'];
                    $salary_head_indexes = [1, 2, 3, 4];
                    $salaryheaddb_id = [2, 4, 3, 5];

                    $combined_heads_array = [];

                    foreach ($salary_head_indexes as $i => $index) {
                        $combined_heads_array[] = [
                            'head_name' => $salary_head_names[$i],
                            'head_id' => $salaryheaddb_id[$i],
                            'value' => $all_data[$index] ?? 0,
                        ];
                    }
                    $combined_heads_array = array_filter($combined_heads_array, function ($head) {
                        return $head['value'] != 0 && $head['value'] != '';
                    });
                    $duplicate_scale = EmployeeScale::where('scale_no', $all_data[0])->where('department_id', $Department->id)
                        ->where('adhoc', $all_data[7] == 'FALSE' ? 0 : 1)
                        ->where('status', $all_data[10] == 'Active' ? 1 : 0)->first();
                    if ($duplicate_scale) {
                        $reason = 'Scale Already Exists';
                        $duplication_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }
                    $employee_scale = new EmployeeScale();
                    $employee_scale->scale_no = $all_data[0];
                    $employee_scale->effect_from = date('Y-m-d', strtotime($all_data[8]));
                    $employee_scale->adhoc = $all_data[7] == 'FALSE' ? 0 : 1;
                    $employee_scale->department_id = $Department->id;
                    $employee_scale->status = $all_data[10] == 'Active' ? 1 : 0;
                    $employee_scale->owned_by = 2;
                    $employee_scale->created_by = 2;
                    $employee_scale->save();

                    if ($employee_scale) {
                        foreach ($combined_heads_array as $head) {
                            EmployeeScaleHeads::create([
                                'scale_id' => $employee_scale->id,
                                'scale_no' => $employee_scale->scale_no,
                                'head' => $head['head_id'],
                                'head_value' => $head['value'],
                                'owned_by' => 2,
                                'created_by' => 2,
                            ]);
                        }
                    }
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'employee_scale_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, $header);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                return redirect()->back()->with('message', "{$success_counter} employee scale(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function EmployeeImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking arrays
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        DB::beginTransaction();

        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                while (($all_data = fgetcsv($handle, 2000, ",")) !== false) {
                    if ($count === 0) {
                        // Save header row
                        $header = array_merge($all_data, ['Status', 'Reason']);
                        $processed_records[] = $header;
                        $count++;
                        continue;
                    }

                    $record_status = 'Error';
                    $reason = '';
                    // $branch_id = 18; // SATELLITE TOWN SENIOR BRANCH RWP
                    // $branch_id = 17; // SATELLITE TOWN NURSERY BRANCH RWP
                    // $branch_id = 5; // I-8/4 DAYCARE BRANCH ISLAMABAD
                    $branch_id = 2; // Head Office
                    // $branch_id = 6; // I-8/4 NURSERY BRANCH ISLAMABAD
                    // $branch_id = 7; // I-8/4 PRIMARY BRANCH ISLAMABAD
                    // $branch_id = 8; // I-8/4 SENIOR BRANCH ISLAMABAD
                    // $branch_id = 53; // PWD BRANCH ISLAMABAD
                    if (empty($all_data[0])) {
                        $reason = 'Empty employee ID';
                        $error_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }

                    $existingEmployee = Employee::where('employee_id', $all_data[0])->first();
                    if ($existingEmployee) {
                        $reason = 'Employee already exists';
                        $duplication_counter++;
                        $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $count++;
                        continue;
                    }

                    $designation = Designation::where('name', 'like', '%' . $all_data[3] . '%')->first();
                    if (!$designation) {
                        $designation_title = preg_replace('/\s*\(.*?\)/', '', $all_data[3]);
                        dd($designation_title);
                        $designation = new Designation();
                        $designation->name = $all_data[3];
                        $designation->department_id = 1;
                        $designation->owned_by = $branch_id;
                        $designation->created_by = 2;
                        $designation->save();

                        // $reason = 'Designation not found';
                        // $error_counter++;
                        // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        // $count++;
                        // continue;
                    }

                    $department = Department::find($designation->department_id);

                    $emp = new Employee();
                    $emp->employee_id = $all_data[0];
                    $emp->name = $all_data[1];
                    $emp->f_name = $all_data[2];
                    $emp->cnic = $all_data[5];
                    $emp->dob = date('Y-m-d', strtotime($all_data[8]));
                    $emp->phone = $all_data[12];
                    $emp->address = $all_data[13];
                    $emp->present_address = $all_data[13];
                    $emp->category = 'Regular';
                    $emp->email = $all_data[11] ?? '';
                    $emp->password = Hash::make('123456');
                    $emp->branch_id = 2;
                    $emp->designation_id = $designation->id;
                    $emp->department_id = $department->id ?? null;
                    $emp->company_doj = date('Y-m-d', strtotime($all_data[9]));
                    $emp->owned_by = $branch_id;
                    $emp->created_by = 2;
                    $emp->save();

                    // Create user
                    $user = new User();
                    $user->name = $all_data[1];
                    $user->email = $all_data[11] ?? '';
                    $user->password = Hash::make('123456');
                    $user->type = 'employee';
                    $user->lang = 'en';
                    $user->owned_by = $branch_id;
                    $user->created_by = 2;
                    $user->save();
                    $emp->user_id = $user->id;
                    $emp->save();
                    $user->assignRole('Employee');

                    // Handle resignation if present and not already added
                    if (!empty($all_data[10])) {
                        $resignationDate = date('Y-m-d', strtotime($all_data[10]));

                        $existingResignation = Resignation::where('employee_id', $emp->id)
                            ->whereDate('resignation_date', $resignationDate)
                            ->first();

                        if (!$existingResignation) {
                            $resignation = new Resignation();
                            $resignation->employee_id = $emp->id;
                            $resignation->branch_id = 2;
                            $resignation->notice_date = $resignationDate;
                            $resignation->resignation_date = $resignationDate;
                            $resignation->last_attendance_date = $resignationDate;
                            $resignation->description = 'Imported resignation';
                            $resignation->owned_by = $branch_id;
                            $resignation->created_by = 2;
                            $resignation->save();

                            $emp->is_res_ter = 1;
                            $emp->save();
                        } else {
                            $reason = 'Resignation already exists';
                            $skip_data[] = array_merge($all_data, ['Status' => 'Skipped', 'Reason' => $reason]);
                        }
                    }

                    $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                    $success_counter++;
                    $count++;
                }

                fclose($handle);
                DB::commit();

                // Handle errors if any
                if (!empty($skip_data)) {
                    $export_filename = 'employee_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');

                    fputcsv($error_file, $header); // Use full header
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }

                    fclose($error_file);
                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Employee(s) added successfully. {$error_counter} errors, {$duplication_counter} duplicates.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }

    }

}
