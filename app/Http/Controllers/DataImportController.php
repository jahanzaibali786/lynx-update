<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Challan;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\ChartOfAccount;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\Concession;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Registring_option;
use App\Models\Section;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeStructure;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\StudentWithdrawal;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Log;

class DataImportController extends Controller
{
    public function showForm()
    {
        Session::forget('counter');

        return view('data_import.form');
    }

    public function importData(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt',
            'data_type' => 'required|string',
        ]);

        $file = $request->file('excel_file');
        $dataType = $request->input('data_type');
        set_time_limit(0);

        if ($dataType == 'enrollment') {
            return $this->EnrollmentImport($file, $request);
        } elseif ($dataType == 'enrollment_status') {
            return $this->EnrollmentStatus($file, $request);
        } elseif ($dataType == 'enrollment2') {
            return $this->EnrollmentImport2($file, $request);
        } elseif ($dataType == 'registration2') {
            return $this->RegistrationImport2($file, $request);
        } elseif ($dataType == 'class') {
            return $this->ClassImport($request);
        } elseif ($dataType == 'section') {
            return $this->SectionImport($request);
        } elseif ($dataType == 'registration') {
            return $this->RegistrationImport($file, $request);
        } elseif ($dataType == 'student_detail') {
            return $this->StudentDetail($file, $request);
        } elseif ($dataType == 'student_detail2') {
            return $this->StudentDetail2($file, $request);
        } elseif ($dataType == 'transfer') {
            return $this->TransferImport($file, $request);
        } elseif ($dataType == 'withdraw') {
            return $this->WithdrawImport($file, $request);
        } elseif ($dataType == 'regular_challan') {
            return $this->RegularChallanImport($file, $request);
        } elseif ($dataType == 'security') {
            return $this->SecurityChallanImport($file, $request);
        } elseif ($dataType == 'receipts') {
            return $this->ReceiptsImport($file, $request);
        } elseif ($dataType == 'challan_concession') {
            return $this->ChallanConcession($file, $request);
        } elseif ($dataType == 'concession') {
            return $this->ConcessionImport($file, $request);
        } elseif ($dataType == 'concession_list') {
            return $this->ConcessionListImport($file, $request);
        } else {
            return redirect()->back()->with('error', 'No Data Type Selected');
        }
    }

    private function EnrollmentImport2($file, $request)
    {
        // dd($request->all());
        // new code for enrollment
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

                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {
                    if ($count > 0) {
                        dd($all_data);
                        $record_status = 'Error';
                        $reason = '';
                        // widthdraw data
                        // Validate enrollment
                        //     $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
                        //       $enr->active_status = '0';
                        //      $enr->save();
                        //     $withdrawal_date = date('Y-m-d', strtotime($all_data[0]));
                        //     if ($enr) {
                        //         $wdth = StudentWithdrawal::where('student_id', $enr->regId)->first();
                        //         if(!$wdth){
                        //             // Create new withdrawal
                        //             $withdrawal = new StudentWithdrawal();
                        //             $withdrawal->student_id = $enr->regId;
                        //             $withdrawal->challan_id = null;
                        //             $withdrawal->branch_id = 6;
                        //             $withdrawal->class_id = $enr->class_id;
                        //             $withdrawal->withdraw_date = $withdrawal_date;
                        //             $withdrawal->apply_date = $withdrawal_date;
                        //             $withdrawal->reason = "School Change"; // Modify accordingly
                        //             $withdrawal->remark = "School Change"; // Modify accordingly
                        //             $withdrawal->owned_by = $enr->owned_by;
                        //             $withdrawal->created_by = \Auth::user()->creatorId();
                        //             $withdrawal->save();

                        //         }else{
                        //             $reason = 'Student already withdrawn';
                        //             $error_counter++;
                        //             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        //             $count++;
                        //             continue;
                        //         }
                        //     } else {
                        //         $reason = 'Enrollment not found';
                        //         $error_counter++;
                        //         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        //         $count++;
                        //         continue;
                        //     }
                        // $enr->active_status = '0';
                        // $enr->save();
                        // $reg = StudentRegistration::where('roll_no', $all_data[1])->first();
                        // $reg->student_status = 'withdrawl';
                        // $reg->save();
                        //  DB::commit();

                        // Proceed with withdrawal logic if no errors
                        // Add to successful records
                        // $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        // $success_counter++;
                        // widthdraw data

                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $reason = 'Empty Roll No';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $reg = StudentRegistration::where('roll_no', $all_data[0])->first();
                        if (!$reg) {
                            $reg = StudentRegistration::where('reg_no', $all_data[6])->first();
                        }
                        if (!$reg) {
                            $reason = 'Registration not found';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $branch = User::where('name', 'like', '%' . $all_data[3] . '%')->first();
                        if (!$branch) {
                            $reason = 'Branch not found: ' . $all_data[3];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        // Check for existing enrollment to avoid duplication

                        $class = Classes::where('name', 'like', '%' . $all_data[4] . '%')->where('owned_by', $branch->id)->first();
                        if (!$class) {
                            $reason = 'Class not found: ' . $all_data[4];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        if ($class) {
                            $section = Section::whereRaw('LOWER(name) like ?', ['%' . strtolower($all_data[5]) . '%'])->first();
                            if (!$section) {
                                $reason = 'Section not found: ' . $all_data[5];
                                $error_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            }
                        }
                        $sectionclass = ClassSection::where('class_id', $class->id)->where('section_id', $section->id)->where('owned_by', $branch->id)->where('active_status', 1)->first();
                        // dd($all_data,$class, $section);
                        if (!$sectionclass) {
                            $reason = 'Class Section not found: ' . $all_data[5];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $existingEnrollment = StudentEnrollments::where('regId', $reg->id)->first();
                        // if (!$existingEnrollment) {
                        //     $reason = 'existingEnrollment not found: ' . $all_data[0];
                        //     $error_counter++;
                        //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        //     $count++;
                        //     continue;
                        // }
                        // $reg->dob = date('Y-m-d', strtotime($all_data[7]));
                        $reg->reg_no = $all_data[6];
                        $reg->stdname = $all_data[2];
                        $reg->class_id = $class->id;
                        $reg->branch = $branch->id;
                        $reg->owned_by = $branch->id;
                        $reg->student_status = 'Enrolled';
                        $reg->save();

                        $enrollment_date = date('Y-m-d', strtotime($all_data[1]));
                        if ($existingEnrollment) {
                            $existingEnrollment->enrollId = $all_data[0];
                            $existingEnrollment->regId = $reg->id;
                            $existingEnrollment->adm_date = date('Y-m-d', strtotime($all_data[1]));
                            $existingEnrollment->class_id = $class->id;
                            $existingEnrollment->section_id = $section->id;
                            $existingEnrollment->owned_by = $branch->id;
                            $existingEnrollment->active_status = 1;
                            // $existingEnrollment->adm_date = $enrollment_date;
                            $existingEnrollment->save();
                            // if ($existingEnrollment->enrollId != '' && $existingEnrollment->enrollId != null) {
                            //     $existingEnrollment->enrollId = $all_data[2];
                            //     $existingEnrollment->save();
                            // }
                            // $reason = 'Student already enrolled in this session';
                            // $duplication_counter++;
                            // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            // $count++;
                            // continue;
                        } else {
                            // Create new enrollment
                            $newEnrollId = $all_data[0];
                            $created_at = date('Y-m-d H:i:s', strtotime($all_data[1]));

                            $enrollment = new StudentEnrollments;
                            $enrollment->enrollId = $newEnrollId;
                            $enrollment->regId = $reg->id;
                            $enrollment->adm_date = $enrollment_date;
                            $enrollment->class_id = $class->id;
                            $enrollment->section_id = $section->id;
                            $enrollment->adm_session = 2;
                            $enrollment->adm_branch = $branch->id;
                            $enrollment->session_id = 2;
                            $enrollment->owned_by = $branch->id;
                            $enrollment->active_status = 1;
                            $enrollment->created_by = \Auth::user()->creatorId();
                            $enrollment->created_at = $created_at;
                            $enrollment->updated_at = $created_at;
                            $enrollment->save();
                            $reg->roll_no = $newEnrollId;
                            $reg->save();
                        }
                        $existingEnrollment = StudentEnrollments::where('regId', $reg->id)->first();
                        if (!$existingEnrollment) {

                            $reason = 'existingEnrollment not found: ' . $all_data[0];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'enrollment_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e, $all_data);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
        // set_time_limit(0);
        // $file = $request->file('excel_file');
        // $filename = $file->getClientOriginalName();
        // $file->move(public_path('assets/import/csv_file/'), $filename);
        // $filepath = public_path('assets/import/csv_file/' . $filename);

        // // Initialize counters and tracking arrays
        // $success_counter = 0;
        // $error_counter = 0;
        // $duplication_counter = 0;
        // $skip_data = [];
        // $processed_records = [];

        // DB::beginTransaction();
        // try {
        //     if (($handle = fopen($filepath, 'r')) !== FALSE) {
        //         $count = 0;

        //         while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
        //             if ($count > 0) {
        //                 $record_status = 'Error';
        //                 $reason = '';
        //                 // Check for empty data in first column
        //                 if (empty($all_data[2])) {
        //                     $reason = 'Empty enrollment ID';
        //                     $error_counter++;
        //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
        //                     $count++;
        //                     continue;
        //                 }
        //                 $reg = StudentRegistration::where('stdname', 'like', '%' . $all_data[3] . '%')
        //                     ->Where('fathername', 'like', '%' . $all_data[4] . '%')->first();
        //                 if (!$reg) {
        //                     $reason = 'Registration not found';
        //                     $error_counter++;
        //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
        //                     $count++;
        //                     continue;
        //                 }
        //                 // Check for existing enrollment to avoid duplication
        //                 $existingEnrollment = StudentEnrollments::where('regId', $reg->id)
        //                     ->first();

        //                 if ($existingEnrollment) {
        //                     if ($existingEnrollment->enrollId != '' && $existingEnrollment->enrollId != null) {
        //                         $existingEnrollment->enrollId = $all_data[2];
        //                         $existingEnrollment->save();
        //                     }
        //                     $reason = 'Student already enrolled in this session';
        //                     $duplication_counter++;
        //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
        //                     $count++;
        //                     continue;
        //                 }

        //                 // Proceed with enrollment logic if no errors
        //                 $enrollment_date = date('Y-m-d', strtotime($all_data[2]));
        //                 $branch = User::where('name', 'like', '%' . $all_data[1] . '%')->first();
        //                 if (!$branch) {
        //                     $reason = 'Branch not found: ' . $all_data[1];
        //                     $error_counter++;
        //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
        //                     $count++;
        //                     continue;
        //                 }
        //                 $class = Classes::where('name', 'like', '%' . $all_data[8] . '%')->where('owned_by', $branch->id)->first();
        //                 if (!$class) {
        //                     $reason = 'Class not found: ' . $all_data[8] . ' for branch: ' . $branch->name;
        //                     $error_counter++;
        //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
        //                     $count++;
        //                     continue;
        //                 }
        //                 $section = Section::where('name', 'like', '%' . $all_data[9] . '%')
        //                     ->where('owned_by', $branch->id)->first();
        //                 if (!$section) {
        //                     //create section
        //                     $section = new Section();
        //                     $section->name = $all_data[9];
        //                     $section->created_by = auth()->user()->id;
        //                     $section->owned_by = $branch->id;
        //                     $section->save();
        //                 }

        //                 // Create new enrollment
        //                 $newEnrollId = $all_data[0];
        //                 $created_at = date('Y-m-d H:i:s', strtotime($all_data[2]));

        //                 $enrollment = new StudentEnrollments();
        //                 $enrollment->enrollId = $newEnrollId;
        //                 $enrollment->regId = $reg->id;
        //                 $enrollment->adm_date = $enrollment_date;
        //                 $enrollment->class_id = $class->id;
        //                 $enrollment->section_id = $section->id;
        //                 $enrollment->session_id = $branch->id;
        //                 $enrollment->owned_by = $branch->id;
        //                 $enrollment->created_by = \Auth::user()->creatorId();
        //                 $enrollment->created_at = $created_at;
        //                 $enrollment->updated_at = $created_at;
        //                 $enrollment->save();
        //                 $reg->roll_no = $newEnrollId;
        //                 $reg->save();
        //                 // Add to successful records
        //                 $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
        //                 $success_counter++;
        //             } else {
        //                 // Save the header row with additional columns
        //                 $header = $all_data;
        //                 $header[] = 'Status';
        //                 $header[] = 'Reason';
        //                 $processed_records[] = $header;
        //             }
        //             $count++;
        //         }
        //         fclose($handle);
        //         DB::commit();
        //         if (!empty($skip_data)) {
        //             $export_filename = 'enrollment_errors_' . time() . '.csv';
        //             $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
        //             $error_file = fopen($error_filepath, 'w+');
        //             fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
        //             foreach ($skip_data as $row) {
        //                 fputcsv($error_file, $row);
        //             }
        //             fclose($error_file);
        //             return response()->download($error_filepath)->deleteFileAfterSend(true);
        //         }
        //         return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
        //     }
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     dd($e);
        //     return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        // }
    }

    //  private function EnrollmentImport2($file, $request)
    // {
    //     // dd($request->all());
    //     // new code for enrollment
    //     set_time_limit(0);
    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Initialize counters and tracking arrays
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skip_data = [];
    //     $processed_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;

    //             while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
    //                 if ($count > 0) {
    //                     // dd($all_data);
    //                     $record_status = 'Error';
    //                     $reason = '';
    //                         // widthdraw data
    //                         // Validate enrollment
    //                             $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
    //                               $enr->active_status = '0';
    //                              $enr->save();
    //                             $withdrawal_date = date('Y-m-d', strtotime($all_data[0]));
    //                             if ($enr) {
    //                                 $wdth = StudentWithdrawal::where('student_id', $enr->regId)->first();
    //                                 if(!$wdth){
    //                                     // Create new withdrawal
    //                                     $withdrawal = new StudentWithdrawal();
    //                                     $withdrawal->student_id = $enr->regId;
    //                                     $withdrawal->challan_id = null;
    //                                     $withdrawal->branch_id = 6;
    //                                     $withdrawal->class_id = $enr->class_id;
    //                                     $withdrawal->withdraw_date = $withdrawal_date;
    //                                     $withdrawal->apply_date = $withdrawal_date;
    //                                     $withdrawal->reason = "School Change"; // Modify accordingly
    //                                     $withdrawal->remark = "School Change"; // Modify accordingly
    //                                     $withdrawal->owned_by = $enr->owned_by;
    //                                     $withdrawal->created_by = \Auth::user()->creatorId();
    //                                     $withdrawal->save();

    //                                 }else{
    //                                     $reason = 'Student already withdrawn';
    //                                     $error_counter++;
    //                                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                                     $count++;
    //                                     continue;
    //                                 }
    //                             } else {
    //                                 $reason = 'Enrollment not found';
    //                                 $error_counter++;
    //                                 $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                                 $count++;
    //                                 continue;
    //                             }
    //                         $enr->active_status = '0';
    //                         $enr->save();
    //                         $reg = StudentRegistration::where('roll_no', $all_data[1])->first();
    //                         $reg->student_status = 'withdrawl';
    //                         $reg->save();
    //                          DB::commit();

    //                         // Proceed with withdrawal logic if no errors
    //                         // Add to successful records
    //                         $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                         $success_counter++;
    //                         // widthdraw data

    //                     // Check for empty data in first column
    //                     // if (empty($all_data[2])) {
    //                     //     $reason = 'Empty enrollment ID';
    //                     //     $error_counter++;
    //                     //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     $count++;
    //                     //     continue;
    //                     // }
    //                     // $reg = StudentRegistration::where('roll_no', $all_data[0])->first();

    //                     // if (!$reg) {
    //                     //     $reason = 'Registration not found';
    //                     //     $error_counter++;
    //                     //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     $count++;
    //                     //     continue;
    //                     // }
    //                     // // Check for existing enrollment to avoid duplication
    //                     // $existingEnrollment = StudentEnrollments::where('regId', $reg->id)->first();
    //                     // $class = Classes::where('name', 'like', '%' . $all_data[5] . '%')->where('owned_by', '6')->first();
    //                     // if ($class) {
    //                     //     $section = Section::whereRaw('LOWER(name) like ?', ['%' . strtolower($all_data[6]) . '%'])->first();
    //                     // }
    //                     // // dd($all_data,$class, $section);
    //                     // $reg->dob = date('Y-m-d', strtotime($all_data[7]));
    //                     // $reg->class_id = $class->id;
    //                     // $reg->student_status = 'Enrolled';
    //                     // $reg->save();
    //                     //     $enrollment_date = date('Y-m-d', strtotime($all_data[1]));
    //                     // if ($existingEnrollment) {
    //                     //         $existingEnrollment->class_id = $class->id;
    //                     //         $existingEnrollment->section_id = $section->id;
    //                     //         $existingEnrollment->owned_by = 6;
    //                     //         $existingEnrollment->active_status = 1;
    //                     //         // $existingEnrollment->adm_date = $enrollment_date;
    //                     //         $existingEnrollment->save();
    //                     //     // if ($existingEnrollment->enrollId != '' && $existingEnrollment->enrollId != null) {
    //                     //     //     $existingEnrollment->enrollId = $all_data[2];
    //                     //     //     $existingEnrollment->save();
    //                     //     // }
    //                     //     // $reason = 'Student already enrolled in this session';
    //                     //     // $duplication_counter++;
    //                     //     // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     // $count++;
    //                     //     // continue;
    //                     // }else{

    //                     //     // Create new enrollment
    //                     //     $newEnrollId = $all_data[0];
    //                     //     $created_at = date('Y-m-d H:i:s', strtotime($all_data[2]));

    //                     //     $enrollment = new StudentEnrollments();
    //                     //     $enrollment->enrollId = $newEnrollId;
    //                     //     $enrollment->regId = $reg->id;
    //                     //     $enrollment->adm_date = $enrollment_date;
    //                     //     $enrollment->class_id = $class->id;
    //                     //     $enrollment->section_id = $section->id;
    //                     //     $enrollment->session_id =2;
    //                     //     $enrollment->owned_by = 6;
    //                     //     $enrollment->active_status = 1;
    //                     //     $enrollment->created_by = \Auth::user()->creatorId();
    //                     //     $enrollment->created_at = $created_at;
    //                     //     $enrollment->updated_at = $created_at;
    //                     //     $enrollment->save();
    //                     //     $reg->roll_no = $newEnrollId;
    //                     //     $reg->save();
    //                     // }
    //                     // Add to successful records
    //                     $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                     $success_counter++;
    //                 } else {
    //                     // Save the header row with additional columns
    //                     $header = $all_data;
    //                     $header[] = 'Status';
    //                     $header[] = 'Reason';
    //                     $processed_records[] = $header;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //             DB::commit();
    //             if (!empty($skip_data)) {
    //                 $export_filename = 'enrollment_errors_' . time() . '.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');
    //                 fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
    //                 foreach ($skip_data as $row) {
    //                     fputcsv($error_file, $row);
    //                 }
    //                 fclose($error_file);
    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }
    //             return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e,$all_data);
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    //     // set_time_limit(0);
    //     // $file = $request->file('excel_file');
    //     // $filename = $file->getClientOriginalName();
    //     // $file->move(public_path('assets/import/csv_file/'), $filename);
    //     // $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // // Initialize counters and tracking arrays
    //     // $success_counter = 0;
    //     // $error_counter = 0;
    //     // $duplication_counter = 0;
    //     // $skip_data = [];
    //     // $processed_records = [];

    //     // DB::beginTransaction();
    //     // try {
    //     //     if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //     //         $count = 0;

    //     //         while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
    //     //             if ($count > 0) {
    //     //                 $record_status = 'Error';
    //     //                 $reason = '';
    //     //                 // Check for empty data in first column
    //     //                 if (empty($all_data[2])) {
    //     //                     $reason = 'Empty enrollment ID';
    //     //                     $error_counter++;
    //     //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //     //                     $count++;
    //     //                     continue;
    //     //                 }
    //     //                 $reg = StudentRegistration::where('stdname', 'like', '%' . $all_data[3] . '%')
    //     //                     ->Where('fathername', 'like', '%' . $all_data[4] . '%')->first();
    //     //                 if (!$reg) {
    //     //                     $reason = 'Registration not found';
    //     //                     $error_counter++;
    //     //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //     //                     $count++;
    //     //                     continue;
    //     //                 }
    //     //                 // Check for existing enrollment to avoid duplication
    //     //                 $existingEnrollment = StudentEnrollments::where('regId', $reg->id)
    //     //                     ->first();

    //     //                 if ($existingEnrollment) {
    //     //                     if ($existingEnrollment->enrollId != '' && $existingEnrollment->enrollId != null) {
    //     //                         $existingEnrollment->enrollId = $all_data[2];
    //     //                         $existingEnrollment->save();
    //     //                     }
    //     //                     $reason = 'Student already enrolled in this session';
    //     //                     $duplication_counter++;
    //     //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //     //                     $count++;
    //     //                     continue;
    //     //                 }

    //     //                 // Proceed with enrollment logic if no errors
    //     //                 $enrollment_date = date('Y-m-d', strtotime($all_data[2]));
    //     //                 $branch = User::where('name', 'like', '%' . $all_data[1] . '%')->first();
    //     //                 if (!$branch) {
    //     //                     $reason = 'Branch not found: ' . $all_data[1];
    //     //                     $error_counter++;
    //     //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //     //                     $count++;
    //     //                     continue;
    //     //                 }
    //     //                 $class = Classes::where('name', 'like', '%' . $all_data[8] . '%')->where('owned_by', $branch->id)->first();
    //     //                 if (!$class) {
    //     //                     $reason = 'Class not found: ' . $all_data[8] . ' for branch: ' . $branch->name;
    //     //                     $error_counter++;
    //     //                     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //     //                     $count++;
    //     //                     continue;
    //     //                 }
    //     //                 $section = Section::where('name', 'like', '%' . $all_data[9] . '%')
    //     //                     ->where('owned_by', $branch->id)->first();
    //     //                 if (!$section) {
    //     //                     //create section
    //     //                     $section = new Section();
    //     //                     $section->name = $all_data[9];
    //     //                     $section->created_by = auth()->user()->id;
    //     //                     $section->owned_by = $branch->id;
    //     //                     $section->save();
    //     //                 }

    //     //                 // Create new enrollment
    //     //                 $newEnrollId = $all_data[0];
    //     //                 $created_at = date('Y-m-d H:i:s', strtotime($all_data[2]));

    //     //                 $enrollment = new StudentEnrollments();
    //     //                 $enrollment->enrollId = $newEnrollId;
    //     //                 $enrollment->regId = $reg->id;
    //     //                 $enrollment->adm_date = $enrollment_date;
    //     //                 $enrollment->class_id = $class->id;
    //     //                 $enrollment->section_id = $section->id;
    //     //                 $enrollment->session_id = $branch->id;
    //     //                 $enrollment->owned_by = $branch->id;
    //     //                 $enrollment->created_by = \Auth::user()->creatorId();
    //     //                 $enrollment->created_at = $created_at;
    //     //                 $enrollment->updated_at = $created_at;
    //     //                 $enrollment->save();
    //     //                 $reg->roll_no = $newEnrollId;
    //     //                 $reg->save();
    //     //                 // Add to successful records
    //     //                 $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //     //                 $success_counter++;
    //     //             } else {
    //     //                 // Save the header row with additional columns
    //     //                 $header = $all_data;
    //     //                 $header[] = 'Status';
    //     //                 $header[] = 'Reason';
    //     //                 $processed_records[] = $header;
    //     //             }
    //     //             $count++;
    //     //         }
    //     //         fclose($handle);
    //     //         DB::commit();
    //     //         if (!empty($skip_data)) {
    //     //             $export_filename = 'enrollment_errors_' . time() . '.csv';
    //     //             $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //     //             $error_file = fopen($error_filepath, 'w+');
    //     //             fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
    //     //             foreach ($skip_data as $row) {
    //     //                 fputcsv($error_file, $row);
    //     //             }
    //     //             fclose($error_file);
    //     //             return response()->download($error_filepath)->deleteFileAfterSend(true);
    //     //         }
    //     //         return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //     //     }
    //     // } catch (\Exception $e) {
    //     //     DB::rollBack();
    //     //     dd($e);
    //     //     return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     // }
    // }
    private function EnrollmentStatus($file, $request)
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

                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';
                        // dd($all_data);
                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $reason = 'Empty enrollment ID';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $reason = 'Enrollment not found';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $branch_name = iconv('UTF-8', 'UTF-8//IGNORE', string: (string) $all_data[3]);
                        $branch_name = trim(preg_replace('/\s+/', ' ', $branch_name));
                        $branch = User::where('name', 'like', '%' . $branch_name . '%')->first();
                        if (!$branch) {
                            dd($all_data, 'branch');
                            $reason = 'Branch not found: ' . $all_data[3];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $q = Classes::where('name', 'like', '%' . $all_data[1] . '%')->where('owned_by', $branch->id);
                        $class = $q->first();
                        if (!$class) {
                            dd($all_data, 'class', $enr, $q->toSql(), $q->getBindings(), $branch, $branch_name);
                            $reason = 'Class not found: ' . $all_data[1];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $section = Section::where('name', 'like', '%' . $all_data[2] . '%')->first();
                        if (!$section) {
                            dd($all_data, 'section');
                            $reason = 'Section not found: ' . $all_data[2];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $enr->class_id = $class->id;
                        $enr->section_id = $section->id;
                        $enr->adm_branch = $branch->id;
                        $enr->owned_by = $branch->id;
                        $enr->save();
                        $reg = StudentRegistration::where('roll_no', $all_data[0])->where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
                        if (!$reg) {
                            $reg = StudentRegistration::where('roll_no', $all_data[0])->first();
                            if ($reg) {
                                $reg->reg_no = $enr->regId;
                                $reg->save();
                            } else {
                                dd($all_data, 'reg');
                                $reason = 'Registration not found';
                                $error_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            }
                        }
                        $reg->reg_class = $class->id;
                        $reg->class_id = $class->id;
                        $reg->branch = $branch->id;
                        $reg->owned_by = $branch->id;
                        $reg->save();
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'enrollment_status_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Enrollment(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function RegistrationImport2($file, $request)
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

                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';
                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $reason = 'Empty registration number';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Validate registration
                        $reg = $all_data[0];
                        if (!$reg) {
                            $reason = 'Registration Number not found';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $branch = User::where('name', 'like', '%' . $all_data[32] . '%')->first();
                        if (!$branch) {
                            $reason = 'Branch not found: ' . $all_data[32];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        $class = Classes::where('name', 'like', '%' . $all_data[27] . '%')->where('owned_by', $branch->id)->first();
                        if (!$class) {
                            dd($branch, $all_data, $class, $all_data[32]);
                            $reason = 'Class not found: ' . $all_data[31];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        // Check for existing registration to avoid duplication
                        $existingRegistration = StudentRegistration::where('reg_no', $all_data[0])
                            ->first();

                        if ($existingRegistration) {
                            $reason = 'Registration already exists for this session';
                            $duplication_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Proceed with registration logic if no errors
                        $regdate = date('Y-m-d', strtotime($all_data[1]));
                        $created_at = date('Y-m-d H:i:s', strtotime($all_data[1]));
                        $parts = preg_split("/\s*s\/d\/o\s*/i", $all_data[2]);

                        // Update registration
                        $reg = new StudentRegistration;
                        $reg->regdate = $regdate;
                        $reg->stdname = trim($parts[0]);
                        $reg->fathername = trim($parts[1]);
                        $reg->fatherphone = $all_data[9] ?? '';
                        $reg->fathercell = $all_data[9] ?? '';
                        $reg->reg_no = $all_data[0] ?? '';
                        $reg->branch = $branch->id ?? '';
                        $reg->class_id = $class->id ?? '';
                        $reg->reg_class = $class->id ?? '';
                        $reg->session_id = 2;
                        $reg->registrationfee = $all_data[30] ?? 0;
                        $reg->fatherprofession = $all_data[11] ?? '';
                        $reg->mothername = $all_data[12] ?? '';
                        $reg->motherprofession = $all_data[14] ?? '';
                        $reg->dob = date('Y-m-d', strtotime($all_data[3])) ?? '';
                        $reg->gender = $all_data[5] ?? '';
                        $reg->register_option = $all_data[30] == '1500' ? 2 : 1;
                        $reg->city = $all_data[22] ?? '';
                        $reg->district = $all_data[23] ?? '';
                        $reg->address = $all_data[24] ?? '';
                        $reg->permanent_address = $all_data[24] ?? '';
                        $reg->owned_by = $branch->id ?? '';
                        $reg->created_by = \Auth::user()->creatorId();
                        $reg->save();
                        $reg->created_at = $created_at;
                        $reg->updated_at = $created_at;
                        $reg->save();

                        if ($reg) {
                            // challan
                            $challan = new Challans;
                            $challan->student_id = $reg->id;
                            $challan->class_id = $reg->class_id ?? null;
                            $challan->rollno = $reg->id;
                            $challan->challanNo = $this->challanNo();
                            $challan->challan_date = $regdate;
                            $challan->challan_type = 'Registration';
                            $challan->total_amount = $all_data[30] ?? 0;
                            $challan->paid_amount = $all_data[30] ?? 0;
                            $challan->issue_date = $regdate;
                            $challan->due_date = date('Y-m-d', strtotime($regdate . ' +7 days'));
                            $challan->status = 'Paid';
                            $challan->session_id = $reg->session_id;
                            $challan->owned_by = $reg->owned_by;
                            $challan->created_by = \Auth::user()->creatorId();
                            $challan->created_at = $created_at;
                            $challan->updated_at = $created_at;
                            $challan->save();
                            if ($challan) {
                                $reg_dis = Registring_option::where('id', $all_data[30] == '1500' ? 2 : 1)
                                    ->where('created_by', \Auth::user()->creatorId())
                                    ->first();
                                // Handle due date (one week after registration date)
                                try {
                                    $due_date = Carbon::parse($regdate)->addWeek()->toDateString();
                                } catch (\Exception $e) {
                                    $due_date = Carbon::now()->addWeek()->toDateString();
                                }

                                $challan->due_date = $due_date;
                                $challan->status = 'Paid';
                                $challan->session_id = $reg->session_id;
                                $challan->owned_by = $reg->owned_by;
                                $challan->created_by = \Auth::user()->creatorId();
                                $challan->save();
                                $challan->created_at = $created_at;
                                $challan->updated_at = $created_at;
                                $challan->save();
                                // Find bank account
                                $bankAccount = BankAccount::where('owned_by', $challan->owned_by)
                                    ->where('bank_name', 'like', '%CSH')->first();
                                if (!$bankAccount) {
                                    $error_reason = 'Bank account not found for owner ID: ' . $challan->owned_by;
                                    $error_counter++;
                                    $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
                                    $skipped_records[] = $all_data; // Store the
                                    $count++;

                                    continue;
                                }

                                // Create student receipt
                                $recipts = new StudentReceipt;
                                $recipts->recipt_date = $regdate;
                                $recipts->challan_id = $challan->id;
                                $recipts->recipt_amount = $challan->paid_amount;
                                $recipts->student_id = $challan->student_id;
                                $recipts->challan_amount = $challan->paid_amount;
                                $recipts->late_amount = 0;
                                $recipts->arrears = 0;
                                $recipts->bank_id = $bankAccount->id;
                                $recipts->referance = 'Registration Fee';
                                $recipts->receive_type = 'CD';
                                $recipts->received_by = Auth::user()->id;
                                $recipts->owned_by = $challan->owned_by;
                                $recipts->created_by = \Auth::user()->creatorId();
                                $recipts->save();
                                $recipts->created_at = $created_at;
                                $recipts->updated_at = $created_at;
                                $recipts->save();
                                // Find registration fee head
                                $pattern = '%registration%';
                                $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                                if (!$adm_fee_head) {
                                    $error_reason = 'Registration fee head not found';
                                    $error_counter++;
                                    $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
                                    $skipped_records[] = $all_data; // Store the
                                    $count++;

                                    continue;
                                    // throw new \Exception('Registration fee head not found');
                                }

                                // Create challan head
                                $challan_head = new ChallanHead;
                                $challan_head->challan_id = $challan->id;
                                $challan_head->head_id = $adm_fee_head->id;
                                $challan_head->price = $reg_dis->discount ?? 0;
                                $challan_head->concession = 0;
                                $challan_head->save();
                                $challan_head->created_at = $created_at;
                                $challan_head->updated_at = $created_at;
                                $challan_head->save();

                                $item['0']['head'] = $challan_head->head_id;
                                $item['0']['price'] = $reg_dis->discount ?? 0;
                                $item['0']['quantity'] = 1;
                                $item['0']['concession'] = 0;
                                $item['0']['total'] = $reg_dis->discount ?? 0;

                                $data = [
                                    'id' => $challan->id,
                                    'no' => $challan->challanNo,
                                    'date' => $challan->challan_date,
                                    'recipt' => $recipts->id,
                                    'reference' => $challan->student_id,
                                    'description' => 'Registration Fee Amount',
                                    'user_id' => $challan->student_id,
                                    'bank_id' => $bankAccount->id,
                                    'amount' => $challan->paid_amount,
                                    'total' => $challan->paid_amount,
                                    'user_type' => 'Student',
                                    'category' => 'Registration',
                                    'owned_by' => $challan->owned_by,
                                    'created_by' => $challan->created_by,
                                    'account_id' => $bankAccount->chart_account_id,
                                    'items' => $item,
                                    'created_at' => $created_at,
                                ];

                                // Update registration fee
                                $updateStdreg = StudentRegistration::find($reg->id);
                                if ($updateStdreg) {
                                    $updateStdreg->registrationfee = $reg_dis->discount ?? 0;
                                    $updateStdreg->created_at = $created_at;
                                    $updateStdreg->save();
                                }

                                // Update bank account balance
                                if ($bankAccount->id) {
                                    Utility::bankAccountBalance($bankAccount->id, $challan->paid_amount, 'credit');
                                }

                                // Create accounting entries
                                $dataret = Utility::crv_entry($data);
                                $challan->voucher_id = $dataret;
                                $challan->save();
                            }
                        }

                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'registration_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Registration(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // private function WithdrawImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Initialize counters and tracking arrays
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skip_data = [];
    //     $processed_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;

    //             while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
    //                 if ($count > 0) {
    //                     $record_status = 'Error';
    //                     $reason = '';

    //                     // Check for empty data in first column
    //                     if (empty($all_data[0])) {
    //                         $reason = 'Empty enrollment ID';
    //                         $error_counter++;
    //                     } else {
    //                         $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[4]);
    //                         $branchfrom = User::where('name', $clean_title)->first();
    //                         if (!$branchfrom) {
    //                             dd($all_data, 'branchfrom', $clean_title);
    //                             $reason = 'From branch not found: ' . $all_data[4];
    //                             $error_counter++;
    //                         } else {
    //                             // Validate enrollment
    //                             $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
    //                             if (!$enr) {
    //                                 $reason = 'Enrollment not found: ' . $all_data[0];
    //                                 $error_counter++;
    //                             } else {
    //                                 // Check for existing withdrawal (prevent duplicates)
    //                                 $withdraw = StudentWithdrawal::where('student_id', $enr->enrollId)->first();
    //                                 if ($withdraw) {
    //                                     $reason = 'Student already withdrawn';
    //                                     $duplication_counter++;
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     // Log the error or skip
    //                     if ($reason) {
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     } else {
    //                         // Proceed with withdrawal logic if no errors
    //                         $withdrawal_date = date('Y-m-d', strtotime($all_data[1]));

    //                         // Create new withdrawal
    //                         $withdrawal = new StudentWithdrawal();
    //                         $withdrawal->student_id = $enr->enrollId;
    //                         $withdrawal->challan_id = null;
    //                         $withdrawal->branch_id = $branchfrom->id;
    //                         $withdrawal->class_id = $enr->class_id;
    //                         $withdrawal->withdraw_date = $withdrawal_date;
    //                         $withdrawal->apply_date = $withdrawal_date;
    //                         $withdrawal->reason = $all_data[3] ?? $all_data[2]; // Modify accordingly
    //                         $withdrawal->remark = $all_data[3] ?? $all_data[2]; // Modify accordingly
    //                         $withdrawal->owned_by = $enr->owned_by;
    //                         $withdrawal->created_by = \Auth::user()->creatorId();
    //                         $withdrawal->save();

    //                         // Add to successful records
    //                         $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                         $success_counter++;
    //                     }
    //                 } else {
    //                     // Save the header row with additional columns
    //                     $header = $all_data;
    //                     $header[] = 'Status';
    //                     $header[] = 'Reason';
    //                     $processed_records[] = $header;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //             DB::commit();

    //             // Generate error file if there are errors
    //             if (!empty($skip_data)) {
    //                 $export_filename = 'withdraw_errors_' . time() . '.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');

    //                 // Write the header for the error file
    //                 fputcsv($error_file, ['Enrollment ID', 'Status', 'Reason']);

    //                 foreach ($skip_data as $row) {
    //                     fputcsv($error_file, $row);
    //                 }

    //                 fclose($error_file);
    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }

    //             // If no errors, create a success report
    //             $export_filename = 'withdrawal_results_' . time() . '.csv';
    //             return response()->download(public_path('assets/import/csv_file/' . $export_filename))
    //                 ->with('message', "{$success_counter} Student Withdrawal(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e);
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    // }

    private function WithdrawImport($file, $request)
    {
        set_time_limit(0);

        $filename = time() . '_' . $file->getClientOriginalName();
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

                $row = 0;

                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {

                    // HEADER
                    if ($row == 0) {
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                        $row++;

                        continue;
                    }

                    $status = 'Error';
                    $reason = '';

                    // CSV columns
                    $roll_no = $all_data[1] ?? null;
                    $reg_no = $all_data[2] ?? null;

                    if (empty($roll_no)) {
                        $reason = 'Empty Roll No';
                        $error_counter++;
                    } else {

                        // Student Registration
                        $reg = StudentRegistration::where('roll_no', $roll_no)
                            // ->where('reg_no', $reg_no)
                            ->first();

                        if (!$reg) {
                            $reason = 'Student registration not found';
                            $error_counter++;
                        } else {

                            // Enrollment
                            $enr = StudentEnrollments::where('enrollId', $roll_no)->first();

                            if (!$enr) {
                                $reason = 'Enrollment not found';
                                $error_counter++;
                            } else {

                                // Duplicate withdrawal check
                                $alreadyWithdrawn = StudentWithdrawal::where('student_id', $reg->id)->first();

                                if ($alreadyWithdrawn) {

                                    $reason = 'Student already withdrawn';
                                    $reg->update([
                                        'active_status' => 0,
                                        'student_status' => 'withdrawal',
                                    ]);

                                    // Update enrollment
                                    $enr->update([
                                        'active_status' => 0,
                                    ]);

                                    $duplication_counter++;
                                } else {

                                    // Class
                                    $class = Classes::where('id', $enr->class_id)
                                        ->where('owned_by', $reg->branch)
                                        ->first();

                                    if (!$class) {
                                        $reason = 'Class not found';
                                        $error_counter++;
                                    } else {

                                        // CREATE WITHDRAWAL
                                        StudentWithdrawal::create([
                                            'student_id' => $reg->id,
                                            'challan_id' => null,
                                            'branch_id' => $reg->branch,
                                            'class_id' => $class->id,
                                            'withdraw_date' => now(),
                                            'apply_date' => now(),
                                            'reason' => 'withdraw import',
                                            'remark' => 'withdraw import',
                                            'owned_by' => $reg->owned_by,
                                            'created_by' => \Auth::user()->creatorId(),
                                        ]);

                                        // Update student
                                        $reg->update([
                                            'active_status' => 0,
                                            'student_status' => 'withdrawal',
                                        ]);

                                        // Update enrollment
                                        $enr->update([
                                            'active_status' => 0,
                                        ]);

                                        $status = 'Success';
                                        $success_counter++;
                                    }
                                }
                            }
                        }
                    }

                    if ($status == 'Error') {
                        $skip_data[] = array_merge($all_data, [$status, $reason]);
                    }

                    $processed_records[] = array_merge($all_data, [$status, $reason]);
                    $row++;
                }

                fclose($handle);
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | ERROR FILE
            |--------------------------------------------------------------------------
            */
            if (!empty($skip_data)) {
                $error_filename = 'withdraw_errors_' . time() . '.csv';
                $error_path = public_path('assets/import/csv_file/' . $error_filename);

                $error_file = fopen($error_path, 'w+');
                fputcsv($error_file, ['Roll No', 'Reg No', 'Status', 'Reason']);

                foreach ($skip_data as $row) {
                    fputcsv($error_file, $row);
                }

                fclose($error_file);

                return response()->download($error_path)->deleteFileAfterSend(true);
            }

            /*
            |--------------------------------------------------------------------------
            | SUCCESS FILE
            |--------------------------------------------------------------------------
            */
            $success_filename = 'withdrawal_results_' . time() . '.csv';
            $success_path = public_path('assets/import/csv_file/' . $success_filename);

            $success_file = fopen($success_path, 'w+');

            foreach ($processed_records as $row) {
                fputcsv($success_file, $row);
            }

            fclose($success_file);

            return response()
                ->download($success_path)
                ->deleteFileAfterSend(true)
                ->with(
                    'message',
                    "{$success_counter} withdrawals added, {$error_counter} errors, {$duplication_counter} duplicates."
                );

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::error('Withdraw Import Error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Import failed. Check logs.');
        }
    }

    private function StudentDetail($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize tracking arrays
        $processed_records = [];
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = []; // Array to gather records that were skipped

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;

                while (($all_data = fgetcsv($handle, 4000, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';

                        if (empty($all_data[0])) {
                            $error_counter++;
                            // Record the skip data (entire all_data)
                            $skip_data[] = $all_data;

                            continue;
                        }

                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $error_counter++;
                            // Record the skip data (entire all_data)
                            $skip_data[] = $all_data;

                            continue;
                        }

                        $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
                        if (!$reg) {
                            $error_counter++;
                            // Record the skip data (entire all_data)
                            $skip_data[] = $all_data;

                            continue;
                        }

                        $class = Classes::where('name', $all_data[1])->where('owned_by', $enr->owned_by)->first();
                        if (!$class) {
                            $error_counter++;
                            // Record the skip data (entire all_data)
                            $skip_data[] = $all_data;

                            continue;
                        }

                        $section = Section::where('name', $all_data[2])->first();
                        if (!$section) {
                            $error_counter++;
                            // Record the skip data (entire all_data)
                            $skip_data[] = $all_data;

                            continue;
                        }

                        $sectionclass = ClassSection::where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->where('owned_by', $enr->owned_by)
                            ->where('active_status', 1)
                            ->first();

                        if (!$sectionclass) {
                            if ($enr->owned_by == 53) {
                                $sectionclass = new ClassSection;
                                $sectionclass->class_id = $class->id;
                                $sectionclass->section_id = $section->id;
                                $sectionclass->owned_by = $enr->owned_by;
                                $sectionclass->created_by = auth()->user()->id;
                                $sectionclass->save();
                            } else {
                                $error_counter++;
                                // Record the skip data (entire all_data)
                                $skip_data[] = $all_data;

                                continue;
                            }
                        }

                        // Updating registration data
                        if ($reg) {
                            $reg->mothername = $all_data[4];
                            $reg->fatherprofession = $all_data[5];
                            $reg->motherprofession = $all_data[6];
                            $reg->address = $all_data[7];
                            $reg->permanent_address = $all_data[7]; // Assuming it's in the same column
                            $reg->register_option = 1;
                            $reg->reg_class = $sectionclass->class_id;
                            $reg->class_id = $sectionclass->class_id;
                            $reg->save();
                        }

                        $enr->class_id = $sectionclass->class_id;
                        $enr->adm_session = 2;
                        $enr->section_id = $sectionclass->section_id;
                        $enr->save();

                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();

                // Generate error file if there are errors
                if (!empty($skip_data)) {
                    $export_filename = 'student_details_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');

                    // Write the error file header
                    fputcsv($error_file, ['Enrollment ID', 'Class', 'Section', 'Mother Name', 'Father Profession', 'Mother Profession', 'Address', 'Permanent Address']);

                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }

                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                // If no errors, generate a success report
                $export_filename = 'student_details_results_' . time() . '.csv';

                return response()->download(public_path('assets/import/csv_file/' . $export_filename))
                    ->with('success', "{$success_counter} Student Detail(s) added successfully. {$error_counter} rows skipped due to errors.");
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function StudentDetail2($file, $request)
    {
        set_time_limit(0);

        $file = $request->file('excel_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        $processed_records = [];
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $error_records = [];

        DB::beginTransaction();

        try {
            if (($handle = fopen($filepath, 'r')) !== false) {

                $row = 0;

                while (($all_data = fgetcsv($handle, 4000, ',')) !== false) {

                    /* ---------------- HEADER ---------------- */
                    if ($row === 0) {
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                        $row++;

                        continue;
                    }

                    $status = 'Error';
                    $reason = '';
                    // dd($all_data);
                    // CSV indexes
                    $branch_name = $all_data[0] ?? null;
                    $roll_no = $all_data[1] ?? null;
                    $reg_no = $all_data[2] ?? null;
                    $student_name = $all_data[3] ?? null;
                    $class_name = $all_data[4] ?? null;
                    $section_name = $all_data[17] ?? null;
                    $student_status = $all_data[19] ?? null;
                    $active_status = $all_data[20] ?? null;

                    if (empty($branch_name) || empty($roll_no) || empty($reg_no)) {
                        $reason = 'Required fields missing';
                        $error_counter++;
                    } else {

                        /* -------- Branch -------- */
                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $branch_name);
                        $branch = User::where('name', $clean_title)->first();

                        if (!$branch) {
                            $reason = 'Branch not found';
                            dd($all_data, 'branch', $clean_title);
                            $error_counter++;
                        } else {

                            /* -------- Registration -------- */
                            $reg = StudentRegistration::where('reg_no', $reg_no)
                                ->where('roll_no', $roll_no)
                                ->first();

                            if (!$reg) {
                                $reason = 'Student registration not found';
                                dd($all_data, 'reg', $reg_no, $roll_no);
                                $error_counter++;
                            } else {

                                /* -------- Enrollment -------- */
                                $enr = StudentEnrollments::where('enrollId', $roll_no)
                                    ->where('regId', $reg->id)
                                    ->first();

                                if (!$enr) {
                                    $reason = 'Enrollment not found';
                                    dd($all_data, 'enr', $roll_no, $reg->id);
                                    $error_counter++;
                                } else {

                                    /* -------- Class -------- */
                                    $class = Classes::where('name', $class_name)
                                        ->where('owned_by', $branch->id)
                                        ->first();

                                    if (!$class) {
                                        $reason = 'Class not found';
                                        dd($all_data, 'class', $class_name, $branch->id);
                                        $error_counter++;
                                    } else {

                                        /* -------- Section -------- */
                                        $section = Section::where('name', $section_name)->first();

                                        if (!$section) {
                                            $reason = 'Section not found';
                                            dd($all_data, 'section', $section_name);
                                            $error_counter++;
                                        } else {

                                            /* -------- Class Section -------- */
                                            $sectionclass = ClassSection::where('class_id', $class->id)
                                                ->where('section_id', $section->id)
                                                ->where('owned_by', $branch->id)
                                                ->where('active_status', 1)
                                                ->first();

                                            if (!$sectionclass) {
                                                if ($branch->id == 53) {
                                                    $sectionclass = ClassSection::create([
                                                        'class_id' => $class->id,
                                                        'section_id' => $section->id,
                                                        'owned_by' => $branch->id,
                                                        'created_by' => auth()->id(),
                                                        'active_status' => 1,
                                                    ]);
                                                } else {
                                                    $reason = 'Class section not found';
                                                    dd($all_data, 'sectionclass', $class->id, $section->id, $branch->id);
                                                    $error_counter++;
                                                }
                                            }

                                            /* -------- UPDATE DATA -------- */
                                            if (empty($reason)) {

                                                // Registration
                                                $reg->update([
                                                    'stdname' => $student_name,
                                                    'owned_by' => $branch->id,
                                                    'branch' => $branch->id,
                                                    'class_id' => $class->id,
                                                    // 'student_status' => $student_status ?? $reg->student_status,
                                                    // 'active_status' => $active_status ?? $reg->active_status,
                                                ]);

                                                // Enrollment
                                                $enr->update([
                                                    'owned_by' => $branch->id,
                                                    'class_id' => $sectionclass->class_id,
                                                    'section_id' => $sectionclass->section_id,
                                                    'adm_branch' => $branch->id,
                                                    'adm_session' => 2,
                                                    // 'active_status' => $active_status ?? $enr->active_status,
                                                ]);

                                                $status = 'Success';
                                                $success_counter++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($status === 'Error') {
                        $error_records[] = [$row, $branch_name, $roll_no, $reason];
                    }

                    $processed_records[] = array_merge($all_data, [$status, $reason]);
                    $row++;
                }

                fclose($handle);
            }

            DB::commit();

            /* ---------------- ERROR FILE ---------------- */
            if (!empty($error_records)) {
                $error_filename = 'student_details_errors_' . time() . '.csv';
                $error_path = public_path('assets/import/csv_file/' . $error_filename);

                $error_file = fopen($error_path, 'w+');
                fputcsv($error_file, ['Row No', 'Branch', 'Roll No', 'Reason']);

                foreach ($error_records as $row) {
                    fputcsv($error_file, $row);
                }

                fclose($error_file);

                return response()->download($error_path)->deleteFileAfterSend(true);
            }

            /* ---------------- SUCCESS FILE ---------------- */
            $success_filename = 'student_details_results_' . time() . '.csv';
            $success_path = public_path('assets/import/csv_file/' . $success_filename);

            $success_file = fopen($success_path, 'w+');

            foreach ($processed_records as $row) {
                fputcsv($success_file, $row);
            }

            fclose($success_file);

            return response()
                ->download($success_path)
                ->deleteFileAfterSend(true)
                ;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('StudentDetail Import Error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Import failed. Check logs.');
        }
    }

    private function EnrollmentImport($file, $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and tracking
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skipped_records = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                $header = null;

                while (($all_data = fgetcsv($handle, 4000, ',')) !== false) {
                    if ($count === 0) {
                        // Save header row with additional columns for status info
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                        $count++;

                        continue;
                    }

                    // Skip empty rows
                    if (empty(array_filter($all_data))) {
                        $count++;

                        continue;
                    }

                    $all_data = array_map('trim', $all_data);
                    $record_status = 'Error';
                    $reason = '';

                    $registration = StudentRegistration::where('reg_no', $all_data[1])->first();

                    // Skip if registration doesn't exist
                    if ($registration == null) {
                        $reason = 'Registration not found';
                        $skipped_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $error_counter++;
                        $count++;

                        continue;
                    }
                    // dd($all_data);
                    // Check for existing enrollment to avoid duplication
                    $existingEnrollment = StudentEnrollments::where('regId', $all_data[1])
                        ->where('enrollId', $all_data[11])
                        ->where('session_id', $registration->session_id)
                        ->first();

                    if ($existingEnrollment) {
                        // Check if this is a second installment for an existing enrollment
                        if (date('Y-m-d', strtotime($existingEnrollment->adm_date)) == date('Y-m-d', strtotime($all_data[4]))) {
                            $secondInstallmentChallan = Challans::where('student_id', $all_data[1])
                                ->where('rollno', $all_data[11])
                                ->where('session_id', $registration->session_id)
                                ->where('challan_type', 'Admission')
                                ->where('challan_date', date('Y-m-d', strtotime($all_data[8])))
                                ->first();
                            if (!$secondInstallmentChallan) {
                                $this->processAdmissionChallan($all_data, $registration, $all_data[11], date('Y-m-d H:i:s', strtotime($all_data[8])));
                                $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => 'Second installment challan created']);
                                $count++;
                                continue;
                            }
                        }
                        // dd('condition false');
                        $reason = 'Student already enrolled in this session';
                        $skipped_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                        $processed_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                        $duplication_counter++;
                        $count++;

                        continue;
                    }

                    // Check for existing admission challan to avoid duplication
                    $existingChallan = Challans::where('student_id', $all_data[1])
                        ->where('rollno', $all_data[11])
                        ->where('session_id', $registration->session_id)
                        ->where('challan_type', 'Admission')
                        ->first();

                    if ($existingChallan) {
                        $reason = 'Admission challan already exists for this student';
                        $skipped_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                        $processed_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                        $duplication_counter++;
                        $count++;

                        continue;
                    }

                    try {
                        // Get section once
                        $section = ClassSection::where('class_id', $registration->class_id)->first();

                        // Create enrollment
                        $newEnrollId = $all_data[11];
                        $created_at = date('Y-m-d H:i:s', strtotime($all_data[4]));

                        $enrollment = new StudentEnrollments;
                        $enrollment->enrollId = $newEnrollId;
                        $enrollment->regId = $all_data[1];
                        $enrollment->adm_date = date('Y-m-d', strtotime($all_data[4]));
                        $enrollment->class_id = $registration->class_id ?? null;
                        $enrollment->section_id = $section->id ?? null;
                        $enrollment->session_id = 2;
                        $enrollment->owned_by = $registration->owned_by;
                        $enrollment->created_by = \Auth::user()->creatorId();
                        $enrollment->created_at = $created_at;
                        $enrollment->updated_at = $created_at;
                        $enrollment->save();

                        // Update registration status
                        $registration->roll_no = $newEnrollId;
                        $registration->student_status = 'Enrolled';
                        $registration->save();

                        // Process challan and fee heads
                        $this->processAdmissionChallan($all_data, $registration, $newEnrollId, $created_at);

                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } catch (\Exception $innerException) {
                        $reason = 'Processing error: ' . $innerException->getMessage();
                        $skipped_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $error_counter++;
                    }

                    $count++;
                }
                fclose($handle);
                DB::commit();

                // Generate error file if there are errors
                if (!empty($skipped_records)) {
                    $export_filename = 'enrollment_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');

                    // Write the header for the error file
                    fputcsv($error_file, ['Enrollment ID', 'Status', 'Reason']); // Header can be modified as required

                    foreach ($skipped_records as $row) {
                        fputcsv($error_file, $row);
                    }

                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                $export_filename = 'enrollment_results_' . time() . '.csv';

                return response()->download(public_path('assets/import/csv_file/' . $export_filename))
                    ->with('success', "Import completed. Processed: {$count}, Success: {$success_counter}, Duplicates: {$duplication_counter}, Errors: {$error_counter}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function processAdmissionChallan($all_data, $registration, $newEnrollId, $created_at)
    {
        $admission_fee = !empty($all_data[5]) ? floatval($all_data[5]) : 0;
        $security_fee = !empty($all_data[6]) ? floatval($all_data[6]) : 0;
        $annual_fee = !empty($all_data[7]) ? floatval($all_data[7]) : 0;
        $other_fee = !empty($all_data[9]) ? floatval($all_data[9]) : 0; // For now it's set to 0

        $total_amount = $admission_fee + $security_fee + $annual_fee + $other_fee;

        // Create challan
        $challan = new Challans;
        $challan->student_id = $all_data[1];
        $challan->class_id = $registration->class_id ?? null;
        $challan->rollno = $newEnrollId;
        $challan->challanNo = $this->challanNo();
        $challan->challan_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->fee_month = date('Y-m-d', strtotime($all_data[8]));
        $challan->challan_type = 'Admission';
        $challan->total_amount = $total_amount;
        $challan->paid_amount = 0;
        $challan->issue_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->due_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->status = 'unpaid';
        $challan->session_id = $registration->session_id;
        $challan->owned_by = $registration->owned_by;
        $challan->created_by = \Auth::user()->creatorId();
        $challan->created_at = $created_at;
        $challan->updated_at = $created_at;
        $challan->save();

        $items = [];
        $item_index = 0;

        // Process fee heads
        $fee_heads = [
            ['amount' => $admission_fee, 'pattern' => '%admission%'],
            ['amount' => $security_fee, 'pattern' => '%security%'],
            ['amount' => $annual_fee, 'pattern' => '%annual%'],
            ['amount' => $other_fee, 'pattern' => '%tuition%'],
        ];

        foreach ($fee_heads as $fee) {
            if ($fee['amount'] > 0) {
                // Find fee head
                $fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($fee['pattern'])])->first();
                if (!$fee_head) {
                    dd('Fee head not found for pattern: ' . $fee['pattern']);
                    // throw new \Exception('Fee head not found for pattern: ' . $fee['pattern']);
                }

                // Create challan head
                $challan_head = new ChallanHead;
                $challan_head->challan_id = $challan->id;
                $challan_head->head_id = $fee_head->id;
                $challan_head->price = $fee['amount'];
                $challan_head->concession = 0;
                $challan_head->created_at = $created_at;
                $challan_head->updated_at = $created_at;
                $challan_head->save();

                // Add to items array for journal entry
                $items[$item_index] = [
                    'head' => $fee_head->id,
                    'price' => $fee['amount'],
                    'quantity' => 1,
                    'concession' => 0,
                    'total' => $fee['amount'],
                ];
                $item_index++;
            }
        }

        // Prepare data for journal entry
        $data = [
            'id' => $challan->id,
            'no' => $challan->challanNo,
            'date' => $challan->challan_date,
            'reference' => $challan->student_id,
            'description' => 'Admission Fee Amount',
            'user_id' => $challan->student_id,
            'amount' => $challan->paid_amount,
            'total' => $challan->paid_amount,
            'user_type' => 'Student',
            'category' => 'Admission',
            'owned_by' => $challan->owned_by,
            'created_by' => $challan->created_by,
            'items' => $items,
            'created_at' => $created_at,
        ];

        // Create accounting entries
        $dataret = Utility::jrentry($data);
        $challan->voucher_id = $dataret;
        $challan->save();

        return $challan;
    }

    private function ClassImport(Request $data)
    {
        $file = $data->file('excel_file');
        if (!$file) {
            return redirect()->back()->with('error', 'No file uploaded.');
        }

        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Prepare error filename
        $error_filename = pathinfo($filename, PATHINFO_FILENAME) . '_errors_' . time() . '.csv';
        $error_filepath = public_path('assets/import/csv_file/' . $error_filename);

        $count = 0;
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $error_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) === false) {
                return redirect()->back()->with('error', 'Could not open the file.');
            }

            while (($row = fgetcsv($handle, 1000, ',')) != false) {
                if ($count == 0 || (isset($row[0]) && strtolower(trim($row[0])) == 'branch')) {
                    $count++;

                    continue;
                }
                $classes = Classes::where('name', 'like', $row[3] . '%')->where('owned_by', 6)->first();
                $section = Section::where('name', 'like', '%' . $row[5] . '%')->first();
                $classesold = Classes::where('name', 'like', $row[2] . '%')->where('owned_by', 6)->first();
                $sectionold = Section::where('name', 'like', $row[4] . '%')->first();
                if (!$classes) {
                    dd('sa', $row);
                }
                if (!$section) {
                    dd('na', $row);
                }
                $classsection = ClassSection::where('class_id', $classes->id)
                    ->where('section_id', $section->id)
                    ->where('owned_by', 6)
                    ->first();
                if (!$classsection) {

                } else {
                    $registration = StudentRegistration::where('roll_no', $row[0])->first();
                    $enrollment = StudentEnrollments::where('enrollId', $row[0])->first();
                    // dd($enrollment,$classes,$section,$classesold,$sectionold);
                    if ($enrollment) {
                        $enrollment->class_id = $classes->id;
                        $enrollment->section_id = $section->id;
                        $enrollment->save();
                    }
                    if ($registration) {
                        $registration->class_id = $classes->id;
                        $registration->save();
                    }
                }
                // $branch_name = iconv('UTF-8', 'UTF-8//IGNORE', (string) $row[2]);
                // $branch_name = trim(preg_replace('/\s+/', ' ', $branch_name));
                // $class_name = (string) $row[0];
                // $section_name = (string) $row[1];

                // if (empty($branch_name) || empty($class_name) || empty($section_name)) {
                //     $error_counter++;
                //     $error_records[] = [$branch_name, $class_name, $section_name, 'Incomplete data'];
                //     continue;
                // }
                // $branch = User::where('name', 'like', '%' . $branch_name . '%')->first();
                // if (!$branch) {
                //     dd($branch_name);
                //     $error_counter++;
                //     $error_records[] = [$branch_name, $class_name, $section_name, 'Branch not found'];
                //     continue;
                // }

                // $section = Section::where('name', $section_name)->where('active_status', 1)->first();
                // if (!$section) {
                //     $error_counter++;
                //     $error_records[] = [$branch_name, $class_name, $section_name, 'Section not found'];
                //     continue;
                // }

                // $class = Classes::where('name', $class_name)->where('owned_by', $branch->id)->first();
                // if ($class) {
                //     $class->active_status = 1;
                //     $class->save();
                //     $classsec = ClassSection::where('class_id', $class->id)
                //         ->where('section_id', $section->id)
                //         ->where('owned_by', $branch->id)
                //         ->first();
                //     // dd($class, $classsec);
                //     if ($classsec) {
                //         $classsec->active_status = 1;
                //         $classsec->section_id = $section->id;
                //         $classsec->class_id = $class->id;
                //         $classsec->owned_by = $branch->id;
                //         $classsec->save();
                //     } else {
                //         $classsec = ClassSection::create([
                //             'active_status' => 1,
                //             'class_id' => $class->id,
                //             'section_id' => $section->id,
                //             'owned_by' => $branch->id,
                //             'created_by' => 2,
                //         ]);
                //     }
                //     // $error_counter++;
                //     // $error_records[] = [$branch_name, $class_name, $section_name, 'Duplicat'];
                //     // continue;
                // } else {
                //     // dd($branch_name,$class_name,$section_name,$branch->id,$class);
                //     $class = new Classes();
                //     $class->name = $class_name;
                //     $class->owned_by = $branch->id;
                //     $class->created_by = auth()->user()->id;
                //     $class->save();
                // }
                // $exists = ClassSection::where('class_id', $class->id)
                //     ->where('section_id', $section->id)
                //     ->where('owned_by', $branch->id)
                //     ->exists();

                // if ($exists) {

                //     $error_counter++;
                //     $duplication_counter++;
                //     $error_records[] = [$branch_name, $class_name, $section_name, 'Class-Section combination already exists'];
                //     continue;
                // }

                // ClassSection::create([
                //     'active_status' => 1,
                //     'class_id' => $class->id,
                //     'section_id' => $section->id,
                //     'owned_by' => $branch->id,
                //     'created_by' => 2,
                // ]);

                $success_counter++;
                $count++;
            }

            fclose($handle);
            unlink($filepath);
            DB::commit();

            // If errors, create file and download it
            if ($error_counter > 0 && !empty($error_records)) {
                $error_file = fopen($error_filepath, 'w+');
                fputcsv($error_file, ['Branch', 'Class Name', 'Section', 'Error']);
                foreach ($error_records as $error_row) {
                    fputcsv($error_file, $error_row);
                }
                fclose($error_file);

                return response()->download($error_filepath)->deleteFileAfterSend(true);
            }

            return redirect()->back()->with('success', "{$success_counter} class-section combinations added successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            dd($e);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // private function SectionImport($data)
    // {
    //     set_time_limit(0);
    //     $file = $data->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Setup counters
    //     $count = 0;
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $error_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             while (($all_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    //                 // Skip header
    //                 if ($count === 0 || (isset($all_data[0]) && strtolower(trim($all_data[0])) === 'section')) {
    //                     $count++;
    //                     continue;
    //                 }

    //                 $section_name = trim($all_data[0] ?? '');

    //                 if (empty($section_name)) {
    //                     $error_counter++;
    //                     $error_records[] = ['', '', $section_name, 'Section name is empty'];
    //                     continue;
    //                 }

    //                 $duplicate_check = Section::where('name', $section_name)->where('active_status', 1)->exists();
    //                 if ($duplicate_check) {
    //                     $error_counter++;
    //                     $duplication_counter++;
    //                     $error_records[] = [$branch->name ?? '', '', $section_name, 'Section already exists'];
    //                     continue;
    //                 }
    //                 Section::create([
    //                     'name' => $section_name,
    //                     'created_by' => 2,
    //                     'active_status' => 1,
    //                     'owned_by' => 2,
    //                 ]);
    //                 $success_counter++;
    //                 $count++;
    //             }
    //             fclose($handle);
    //             unlink($filepath);
    //         }

    //         DB::commit();

    //         // If errors occurred, generate downloadable error file
    //         if (!empty($error_records)) {
    //             $export_filename = 'section_import_errors_' . time() . '.csv';
    //             $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //             $error_file = fopen($error_filepath, 'w+');

    //             fputcsv($error_file, ['Branch', 'Class Name', 'Section', 'Error']);
    //             foreach ($error_records as $row) {
    //                 fputcsv($error_file, $row);
    //             }
    //             fclose($error_file);

    //             return response()->download($error_filepath)->deleteFileAfterSend(true);
    //         }

    //         return redirect()->back()->with('message', "{$success_counter} Section(s) added successfully. {$error_counter} rows skipped due to duplication or missing data.");
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         if (file_exists($filepath)) {
    //             unlink($filepath);
    //         }
    //         dd($e);
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    // }

    private function SectionImport($data)
    {
        set_time_limit(0);
        $file = $data->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Setup counters
        $count = 0;
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $error_records = [];

        DB::beginTransaction();
        try {

            if (($handle = fopen($filepath, 'r')) !== false) {
                while (($all_data = fgetcsv($handle, 1000, ',')) !== false) {
                    // Skip header

                    // all_data[0] => table id
                    // all_data[1] => reg_date
                    // all_data[2] => reg_no
                    // all_data[3] => roll_no
                    // all_data[4] => stdname
                    // all_data[5] => father_name

                    $reg = StudentRegistration::where('id', $all_data[0])->first();
                    if (!$reg) {
                        $error_counter++;
                        $error_records[] = [$all_data[0], '', '', 'Registration not found'];

                        continue;
                    }
                    $enr = StudentEnrollments::where('regId', $reg->id)->first();
                    if ($enr) {
                        $enr->delete();
                    }

                    $challan = Challans::where('student_id', $reg->id)->get();
                    // dd($challan,$reg);
                    if ($challan) {
                        foreach ($challan as $ch) {
                            $head = ChallanHead::where('challan_id', $ch->id)->delete();
                            $journal = JournalEntry::where('id', $ch->voucher_id)->delete();
                            $item = JournalItem::where('journal', $ch->voucher_id)->delete();
                            $recipts = StudentReceipt::where('challan_id', $ch->id)->delete();
                            $ch->delete();
                        }

                    }
                    $reg->delete();

                    $success_counter++;
                    $count++;
                }
                fclose($handle);
                unlink($filepath);
            }

            DB::commit();

            // If errors occurred, generate downloadable error file
            if (!empty($error_records)) {
                $export_filename = 'section_import_errors_' . time() . '.csv';
                $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                $error_file = fopen($error_filepath, 'w+');

                fputcsv($error_file, ['Branch', 'Class Name', 'Section', 'Error']);
                foreach ($error_records as $row) {
                    fputcsv($error_file, $row);
                }
                fclose($error_file);

                return response()->download($error_filepath)->deleteFileAfterSend(true);
            }

            return redirect()->back()->with('message', "{$success_counter} Section(s) added successfully. {$error_counter} rows skipped due to duplication or missing data.");
        } catch (\Exception $e) {
            DB::rollBack();

            if (file_exists($filepath)) {
                unlink($filepath);
            }
            dd($e);

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // private function RegistrationImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Initialize error records tracking
    //     $error_records = [];
    //     $error_records[] = ['Row', 'Registration No', 'Student Name', 'Reason']; // Header row
    //     $skipped_records = []; // Array to gather records that were skipped

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;
    //             $success_counter = 0;
    //             $error_counter = 0;
    //             $duplication_counter = 0;
    //             while (($all_data = fgetcsv($handle, 4000, ",")) !== FALSE) {
    //                 if ($count > 0) {
    //                     $error_reason = null;

    //                     // Check for empty data in first column
    //                     if (empty($all_data[0])) {
    //                         $error_reason = 'Branch name is empty';
    //                         $error_counter++;
    //                         $error_records[] = [$count, $all_data[1] ?? 'N/A', $all_data[2] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }
    //                     //0 branch
    //                     //2 regno
    //                     //4 name
    //                     //5 father name
    //                     //6 class
    //                     $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[0]);
    //                     $branch = User::where('name', $clean_title)->first();
    //                     if (!$branch) {
    //                         $error_reason = 'Branch not found: ' . $clean_title;
    //                         $error_counter++;
    //                         $error_records[] = [$count, $all_data[0] ?? 'N/A', $all_data[1] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }

    //                     // Check if student ID exists
    //                     if (empty($all_data[2])) {
    //                         $error_reason = 'Registration number is empty';
    //                         $error_counter++;
    //                         $error_records[] = [$count, 'N/A', $all_data[2] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }

    //                     // Check for duplicate student registration
    //                     $duplicate_user_check = StudentRegistration::where('reg_no', $all_data[2])->get();
    //                     if ($duplicate_user_check->count() > 0) {
    //                         $error_reason = 'Duplicate registration number';
    //                         $error_counter++;
    //                         $duplication_counter++;
    //                         $error_records[] = [$count, $all_data[2], $all_data[1] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }

    //                     // Set status values
    //                     $act_status = isset($all_data[9]) && strtoupper($all_data[9]) == 'YES' ? 1 : 0;
    //                     $std_status = isset($all_data[9]) && strtoupper($all_data[9]) == 'YES' ? 'Enrolled' : 'Registered';

    //                     // Format date - assuming format is d/m/Y (1/4/2019 = 4 Jan 2019)
    //                     $regdate = $all_data[3] ?? null;
    //                     if (!$regdate) {
    //                         $error_reason = 'Registration date is missing';
    //                         $error_counter++;
    //                         $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }

    //                     try {
    //                         $timestamp = strtotime($regdate); // Convert string to timestamp
    //                         if ($timestamp === false) {
    //                             throw new \Exception("Invalid date format");
    //                         }
    //                         $regdate = date('Y-m-d', $timestamp);
    //                         $created_at = date('Y-m-d H:i:s', $timestamp);
    //                     } catch (\Exception $e) {
    //                         $error_reason = 'Invalid date format: ' . $all_data[3];
    //                         $error_counter++;
    //                         $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the skipped data
    //                         $count++;
    //                         continue;
    //                     }
    //                     $class = Classes::where('name', 'like', '%' . $all_data[6] . '%')->where('owned_by', $branch->id)->first();
    //                     if (!$class) {
    //                         $error_reason = 'Class not found: ' . $all_data[6] . ' for branch: ' . $branch->name;
    //                         $error_counter++;
    //                         $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
    //                         $skipped_records[] = $all_data; // Store the
    //                         $count++;
    //                         continue;
    //                     }
    //                     // Create new student registration
    //                     $stdreg = new StudentRegistration();
    //                     $stdreg->reg_no = $all_data[2];
    //                     $stdreg->regdate = $regdate;
    //                     $stdreg->stdname = $all_data[4] ?? '';
    //                     $stdreg->fathername = $all_data[5] ?? '';
    //                     $stdreg->fatherphone = $all_data[7] ?? '';
    //                     $stdreg->branch = $branch->id;
    //                     $stdreg->session_id = 2;
    //                     $stdreg->active_status = $act_status;
    //                     $stdreg->student_status = $std_status;
    //                     $stdreg->registrationfee = $all_data[8] ?? 0;
    //                     $stdreg->class_id = $class->id;
    //                     $stdreg->reg_class = $class->id;
    //                     $stdreg->register_option = 1;
    //                     $stdreg->owned_by = $branch->id;
    //                     $stdreg->created_by = auth()->user()->id;
    //                     $stdreg->save();
    //                     $stdreg->created_at = $created_at;
    //                     $stdreg->updated_at = $created_at;
    //                     $stdreg->save();

    //                     if ($stdreg) {
    //                         $reg_dis = Registring_option::where('id', 1)
    //                             ->where('created_by', \Auth::user()->creatorId())
    //                             ->first();

    //                         // Create new challan
    //                         $challan = new Challans();
    //                         $challan->student_id = $stdreg->id;
    //                         $challan->class_id = $class->id ?? null;
    //                         $challan->challanNo = $this->challanNo();
    //                         $challan->challan_date = $regdate;
    //                         $challan->challan_type = 'Registration';
    //                         $challan->total_amount = $reg_dis->discount ?? 0;
    //                         $challan->paid_amount = $reg_dis->discount ?? 0;
    //                         $challan->issue_date = $regdate;

    //                         // Handle due date (one week after registration date)
    //                         try {
    //                             $due_date = Carbon::parse($regdate)->addWeek()->toDateString();
    //                         } catch (\Exception $e) {
    //                             $due_date = Carbon::now()->addWeek()->toDateString();
    //                         }

    //                         $challan->due_date = $due_date;
    //                         $challan->status = 'Paid';
    //                         $challan->session_id = $stdreg->session_id;
    //                         $challan->owned_by = $stdreg->owned_by;
    //                         $challan->created_by = \Auth::user()->creatorId();
    //                         $challan->save();
    //                         $challan->created_at = $created_at;
    //                         $challan->updated_at = $created_at;
    //                         $challan->save();

    //                         // Find bank account
    //                         $bankAccount = BankAccount::where('owned_by', $challan->owned_by)
    //                             ->where('bank_name', 'like', '%CSH')->first();
    //                         // dd($bankAccount,$all_data);
    //                         if (!$bankAccount) {
    //                             $error_reason = 'Bank account not found for owner ID: ' . $challan->owned_by;
    //                             $error_counter++;
    //                             $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
    //                             $skipped_records[] = $all_data; // Store the
    //                             $count++;
    //                             continue;
    //                         }

    //                         // Create student receipt
    //                         $recipts = new StudentReceipt();
    //                         $recipts->recipt_date = $regdate;
    //                         $recipts->challan_id = $challan->id;
    //                         $recipts->recipt_amount = $challan->paid_amount;
    //                         $recipts->student_id = $challan->student_id;
    //                         $recipts->challan_amount = $challan->paid_amount;
    //                         $recipts->late_amount = 0;
    //                         $recipts->arrears = 0;
    //                         $recipts->bank_id = $bankAccount->id;
    //                         $recipts->referance = 'Registration Fee';
    //                         $recipts->receive_type = 'CD';
    //                         $recipts->received_by = Auth::user()->id;
    //                         $recipts->owned_by = $challan->owned_by;
    //                         $recipts->created_by = \Auth::user()->creatorId();
    //                         $recipts->save();
    //                         $recipts->created_at = $created_at;
    //                         $recipts->updated_at = $created_at;
    //                         $recipts->save();

    //                         // Find registration fee head
    //                         $pattern = '%registration%';
    //                         $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
    //                         if (!$adm_fee_head) {
    //                             $error_reason = 'Registration fee head not found';
    //                             $error_counter++;
    //                             $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
    //                             $skipped_records[] = $all_data; // Store the
    //                             $count++;
    //                             continue;
    //                             // throw new \Exception('Registration fee head not found');
    //                         }

    //                         // Create challan head
    //                         $challan_head = new ChallanHead();
    //                         $challan_head->challan_id = $challan->id;
    //                         $challan_head->head_id = $adm_fee_head->id;
    //                         $challan_head->price = $reg_dis->discount ?? 0;
    //                         $challan_head->concession = 0;
    //                         $challan_head->save();
    //                         $challan_head->created_at = $created_at;
    //                         $challan_head->updated_at = $created_at;
    //                         $challan_head->save();

    //                         $item['0']['head'] = $challan_head->head_id;
    //                         $item['0']['price'] = $reg_dis->discount ?? 0;
    //                         $item['0']['quantity'] = 1;
    //                         $item['0']['concession'] = 0;
    //                         $item['0']['total'] = $reg_dis->discount ?? 0;

    //                         $data = [
    //                             'id' => $challan->id,
    //                             'no' => $challan->challanNo,
    //                             'date' => $challan->challan_date,
    //                             'recipt' => $recipts->id,
    //                             'reference' => $challan->student_id,
    //                             'description' => 'Registration Fee Amount',
    //                             'user_id' => $challan->student_id,
    //                             'amount' => $challan->paid_amount,
    //                             'total' => $challan->paid_amount,
    //                             'user_type' => 'Student',
    //                             'category' => 'Registration',
    //                             'owned_by' => $challan->owned_by,
    //                             'created_by' => $challan->created_by,
    //                             'account_id' => $bankAccount->chart_account_id,
    //                             'items' => $item,
    //                             'created_at' => $created_at
    //                         ];

    //                         // Update registration fee
    //                         $updateStdreg = StudentRegistration::find($stdreg->id);
    //                         if ($updateStdreg) {
    //                             $updateStdreg->registrationfee = $reg_dis->discount ?? 0;
    //                             $updateStdreg->created_at = $created_at;
    //                             $updateStdreg->save();
    //                         }

    //                         // Update bank account balance
    //                         if ($bankAccount->id) {
    //                             Utility::bankAccountBalance($bankAccount->id, $challan->paid_amount, 'credit');
    //                         }

    //                         // Create accounting entries
    //                         $dataret = Utility::crv_entry($data);
    //                         $challan->voucher_id = $dataret;
    //                         $challan->save();
    //                     }
    //                     $success_counter++;
    //                 }
    //                 $count++;
    //             }

    //             fclose($handle);
    //             DB::commit();

    //             // Export error records to Excel if any exist
    //             if (!empty($error_records)) {
    //                 $export_filename = 'registration_import_errors_' . time() . '.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');
    //                 fputcsv($error_file, ['Row', 'Registration No', 'Student Name', 'Error']);
    //                 foreach ($error_records as $row) {
    //                     fputcsv($error_file, $row);
    //                 }
    //                 fclose($error_file);
    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }
    //             $message = "{$success_counter} Student Registration added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.";

    //             return redirect()->back()->with('message', $message);
    //         }

    //         throw new \Exception("Unable to open file: {$filepath}");

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e);
    //         \Log::error('Registration Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    // }

    private function RegistrationImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize error records tracking
        $error_records = [];
        $error_records[] = ['Row', 'Registration No', 'Student Name', 'Reason']; // Header row
        $skipped_records = []; // Array to gather records that were skipped

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;
                $success_counter = 0;
                $error_counter = 0;
                $duplication_counter = 0;
                while (($all_data = fgetcsv($handle, 4000, ',')) !== false) {
                    $all_data = array_map(function ($value) {
                        return is_string($value)
                            ? iconv('UTF-8', 'UTF-8//IGNORE', $value)
                            : $value;
                    }, $all_data);                    
                    if ($count > 0) {
                        $error_reason = null;
                        // dd($all_data);
                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $error_reason = 'Branch name is empty';
                            $error_counter++;
                            $error_records[] = [$count, $all_data[0] ?? 'N/A', $all_data[1] ?? 'N/A', $error_reason];
                            $skipped_records[] = $all_data; // Store the skipped data
                            $count++;

                            continue;
                        }
                        // dd($all_data);
                        // 0 branch
                        // 1 rollno
                        // 2 regno
                        // 3 stdname
                        // 4 class
                        // 5 gender
                        // 6 father name
                        // 7 father cnic
                        // 8 father occupation
                        // 9 mother name
                        // 10 mother cnic
                        // 11 mother occupation
                        // 12 reg date
                        // 13 adm date
                        // 14 dob
                        // 15 contact no
                        // 16 address
                        // 17 Section
                        // 18 reg fee
                        // 19 student status
                        // 20 active status 1 or 0
                        // 21 withdrwa date
                        // 22 Reg type (shifa , normal)
                        // dd($all_data);
                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[0]);
                        $branch = User::where('name', $clean_title)->first();
                        if($all_data[22] != ''){
                            $regType = strtolower($all_data[22]) == 'normal' ? 1 : 2;
                        }
                        if (!$branch) {
                            dd($clean_title);
                            $error_reason = 'Branch not found: ' . $clean_title;
                            $error_counter++;
                            $error_records[] = [$count, $all_data[0] ?? 'N/A', $all_data[0] ?? 'N/A', $error_reason];
                            $skipped_records[] = $all_data; // Store the skipped data
                            $count++;

                            continue;
                        }

                        // Check if student ID exists
                        if (empty($all_data[2])) {
                            $error_reason = 'Registration number is empty';
                            $error_counter++;
                            $error_records[] = [$count, 'N/A', $all_data[2] ?? 'N/A', $error_reason];
                            $skipped_records[] = $all_data; // Store the skipped data
                            $count++;

                            continue;
                        }
                        if ($all_data[12] != '') {
                            try {
                                $timestamp = strtotime($all_data[12]); // Convert string to timestamp
                                if ($timestamp == false) {
                                    throw new \Exception('Invalid date format');
                                }
                                $regdate = date('Y-m-d', $timestamp);
                                $created_at = date('Y-m-d H:i:s', $timestamp);
                            } catch (\Exception $e) {
                                $error_reason = 'Invalid date format: ' . $all_data[12];
                                dd($all_data, $error_reason);
                                $error_counter++;
                                $error_records[] = [$count, $all_data[2], $all_data[12] ?? 'N/A', $error_reason];
                                $skipped_records[] = $all_data; // Store the skipped data
                                $count++;

                                continue;
                            }
                        } else {
                            $created_at = date('Y-m-d H:i:s');
                        }

                        $class = Classes::where('name', 'like', '%' . $all_data[4] . '%')->where('owned_by', $branch->id)->first();
                        if (!$class) {
                            $error_reason = 'Class not found: ' . $all_data[4] . ' for branch: ' . $branch->name;
                            $error_counter++;
                            $error_records[] = [$count, $all_data[2], $all_data[4] ?? 'N/A', $error_reason];
                            $skipped_records[] = $all_data; // Store the
                            $count++;

                            continue;
                        }
                        $reg = StudentRegistration::where('reg_no', $all_data[2])->where('roll_no', $all_data[1])->first();
                        if ($reg) {
                            $reg->regdate = date('Y-m-d', strtotime($all_data[12]));
                            $reg->dob = date('Y-m-d', strtotime($all_data[14]));
                            $reg->stdname = $all_data[3] ?? '';
                            $reg->gender = $all_data[5] ?? '';
                            $reg->fathercnic = $all_data[7] ?? '';
                            $reg->fathername = $all_data[6] ?? '';
                            $reg->fatherprofession = $all_data[8] ?? '';
                            $reg->mothername = $all_data[9] ?? '';
                            $reg->mothercnic = $all_data[10] ?? '';
                            $reg->motherprofession = $all_data[11] ?? '';
                            $reg->address = $all_data[17] ?? '';
                            $reg->registrationfee = $all_data[18] ?? null;
                            $reg->reg_class = $class->id;
                            $reg->register_option = $regType;
                            
                            // Update status from sheet data
                            $reg->student_status = $all_data[19];
                            $reg->active_status = $all_data[20];
                            $reg->save();
                            $reg->created_at = $created_at;
                            $reg->updated_at = $created_at;
                            $reg->created_by = 2;
                            $reg->owned_by = $branch->id;
                            $reg->save();
                            // enrollment update
                            $enr = StudentEnrollments::where('enrollId', $all_data[1])->
                                where('regId', $reg->id)->first();
                            if ($enr) {
                                $enr->regId = $reg->id;
                                $enr->adm_branch = $branch->id;
                                $enr->adm_session = 2;
                                $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                // Update enrollment status from sheet data
                                $enr->active_status = $all_data[20];
                                if ($all_data[21] != '') {
                                    // withdrwa date exist then add withdraw
                                    $withdraw = new StudentWithdrawal;
                                    $withdraw->student_id = $enr->enrollId;
                                    $withdraw->class_id = $enr->class_id;
                                    $withdraw->section_id = $enr->section_id;
                                    $withdraw->branch_id = $branch->id;
                                    $withdraw->session_id = 2;
                                    $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                    $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                    $withdraw->reason = 'Imported Withdraw';
                                    $withdraw->created_by = 2;
                                    $withdraw->owned_by = $branch->id;
                                    $withdraw->created_at = $created_at;
                                    $withdraw->save();
                                    $this->applyWithdrawalState($reg, $enr);

                                }
                                $enr->save();
                            } else {
                                // dd('enr not found at first positon';
                                $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
                                if ($enr) {
                                    $enr->regId = $reg->id;
                                    $enr->adm_branch = $branch->id;
                                    $enr->adm_session = 2;
                                    $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                    // Update enrollment status from sheet data
                                    $enr->active_status = $all_data[20];
                                    if ($all_data[21] != '') {
                                        // withdrwa date exist then add withdraw
                                        $withdraw = new StudentWithdrawal;
                                        $withdraw->student_id = $enr->enrollId;
                                        $withdraw->class_id = $enr->class_id;
                                        $withdraw->section_id = $enr->section_id;
                                        $withdraw->branch_id = $branch->id;
                                        $withdraw->session_id = 2;
                                        $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                        $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                        $withdraw->reason = 'Imported Withdraw';
                                        $withdraw->created_by = 2;
                                        $withdraw->owned_by = $branch->id;
                                        $withdraw->created_at = $created_at;
                                        $withdraw->save();
                                        $this->applyWithdrawalState($reg, $enr);
                                    }
                                    $enr->save();
                                } else {
                                    $enr = StudentEnrollments::where('regId', $reg->id)->first();
                                    if ($enr) {
                                        $enr->enrollId = $all_data[1];
                                        $enr->adm_branch = $branch->id;
                                        $enr->adm_session = 2;
                                        $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                        // Update enrollment status from sheet data
                                        $enr->active_status = $all_data[20];
                                        if ($all_data[21] != '') {
                                            // withdrwa date exist then add withdraw
                                            $withdraw = new StudentWithdrawal;
                                            $withdraw->student_id = $enr->enrollId;
                                            $withdraw->class_id = $enr->class_id;
                                            $withdraw->section_id = $enr->section_id;
                                            $withdraw->branch_id = $branch->id;
                                            $withdraw->session_id = 2;
                                            $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                            $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                            $withdraw->reason = 'Imported Withdraw';
                                            $withdraw->created_by = 2;
                                            $withdraw->owned_by = $branch->id;
                                            $withdraw->created_at = $created_at;
                                            $withdraw->save();
                                            $this->applyWithdrawalState($reg, $enr);
                                        }
                                        $enr->save();
                                    } else {
                                        $enr = new StudentEnrollments;
                                        $enr->enrollId = $all_data[1];
                                        $enr->regId = $reg->id;
                                        $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                        $enr->class_id = $class->id;
                                        $enr->adm_session = 2;
                                        $enr->adm_branch = $branch->id;
                                        $enr->session_id = 2;
                                        $enr->owned_by = $branch->id;
                                        $enr->created_at = $created_at;
                                        $enr->active_status = $all_data[20];
                                        $enr->save();
                                        $reg->roll_no = $all_data[1];
                                        $reg->save();
                                        $reg->created_at = $created_at;
                                        $reg->updated_at = $created_at;
                                        $reg->created_by = 2;
                                        $reg->owned_by = $branch->id;
                                        $reg->save();
                                    }
                                }
                            }
                        } else {
                            $reg = StudentRegistration::where('roll_no', $all_data[1])
                                ->first();
                            if ($reg) {
                                // Update registration data
                                $reg->reg_no = $all_data[2];
                                $reg->regdate = date('Y-m-d', strtotime($all_data[12]));
                                $reg->dob = date('Y-m-d', strtotime($all_data[14]));
                                $reg->stdname = $all_data[3] ?? '';
                                $reg->gender = $all_data[5] ?? '';
                                $reg->fathercnic = $all_data[7] ?? '';
                                $reg->fathername = $all_data[6] ?? '';
                                $reg->fatherprofession = $all_data[8] ?? '';
                                $reg->mothername = $all_data[9] ?? '';
                                $reg->mothercnic = $all_data[10] ?? '';
                                $reg->motherprofession = $all_data[11] ?? '';
                                $reg->address = $all_data[17] ?? '';
                                // Update status from sheet data
                                $reg->student_status = $all_data[19];
                                $reg->active_status = $all_data[20];
                                $reg->reg_class = $class->id;
                                $reg->register_option = $regType;
                                $reg->save();
                                $reg->created_at = $created_at;
                                $reg->updated_at = $created_at;
                                $reg->save();
                                // enrollment update
                                $enr = StudentEnrollments::where('enrollId', $all_data[1])
                                    ->where('regId', $reg->id)->first();
                                if ($enr) {
                                    $enr->regId = $reg->id;
                                    $enr->adm_branch = $branch->id;
                                    $enr->adm_session = 2;
                                    $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                    // Update enrollment status from sheet data
                                    $enr->active_status = $all_data[20];
                                    if ($all_data[21] != '') {
                                        // withdrwa date exist then add withdraw
                                        $withdraw = new StudentWithdrawal;
                                        $withdraw->student_id = $enr->enrollId;
                                        $withdraw->class_id = $enr->class_id;
                                        $withdraw->section_id = $enr->section_id;
                                        $withdraw->branch_id = $branch->id;
                                        $withdraw->session_id = 2;
                                        $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                        $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                        $withdraw->reason = 'Imported Withdraw';
                                        $withdraw->created_by = 2;
                                        $withdraw->owned_by = $branch->id;
                                        $withdraw->created_at = $created_at;
                                        $withdraw->save();
                                        $this->applyWithdrawalState($reg, $enr);
                                    }
                                    $enr->save();
                                } else {
                                    // dd('enr not found at second positon');
                                    $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
                                    if ($enr) {
                                        $enr->regId = $reg->id;
                                        $enr->adm_branch = $branch->id;
                                        $enr->adm_session = 2;
                                        $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                        // Update enrollment status from sheet data
                                        $enr->active_status = $all_data[20];
                                        if ($all_data[21] != '') {
                                            // withdrwa date exist then add withdraw
                                            $withdraw = new StudentWithdrawal;
                                            $withdraw->student_id = $enr->enrollId;
                                            $withdraw->class_id = $enr->class_id;
                                            $withdraw->section_id = $enr->section_id;
                                            $withdraw->branch_id = $branch->id;
                                            $withdraw->session_id = 2;
                                            $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                            $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                            $withdraw->reason = 'Imported Withdraw';
                                            $withdraw->created_by = 2;
                                            $withdraw->owned_by = $branch->id;
                                            $withdraw->created_at = $created_at;
                                            $withdraw->save();
                                            $this->applyWithdrawalState($reg, $enr);
                                        }
                                        $enr->save();
                                    } else {
                                        $enr = StudentEnrollments::where('regId', $reg->id)->first();
                                        if ($enr) {
                                            $enr->enrollId = $all_data[1];
                                            $enr->adm_branch = $branch->id;
                                            $enr->adm_session = 2;
                                            $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                            // Update enrollment status from sheet data
                                            $enr->active_status = $all_data[20];
                                            if ($all_data[21] != '') {
                                                // withdrwa date exist then add withdraw
                                                $withdraw = new StudentWithdrawal;
                                                $withdraw->student_id = $enr->enrollId;
                                                $withdraw->class_id = $enr->class_id;
                                                $withdraw->section_id = $enr->section_id;
                                                $withdraw->branch_id = $branch->id;
                                                $withdraw->session_id = 2;
                                                $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                $withdraw->reason = 'Imported Withdraw';
                                                $withdraw->created_by = 2;
                                                $withdraw->owned_by = $branch->id;
                                                $withdraw->created_at = $created_at;
                                                $withdraw->save();
                                                $this->applyWithdrawalState($reg, $enr);
                                            }
                                            $enr->save();
                                        } else {
                                            $enr = new StudentEnrollments;
                                            $enr->enrollId = $all_data[1];
                                            $enr->regId = $reg->id;
                                            $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                            $enr->class_id = $class->id;
                                            $enr->adm_session = 2;
                                            $enr->adm_branch = $branch->id;
                                            $enr->session_id = 2;
                                            $enr->owned_by = $branch->id;
                                            $enr->created_at = $created_at;
                                            $enr->active_status = $all_data[20];
                                            $enr->save();
                                            $reg->roll_no = $all_data[1];
                                            $reg->save();
                                            $reg->created_at = $created_at;
                                            $reg->updated_at = $created_at;
                                            $reg->created_by = 2;
                                            $reg->owned_by = $branch->id;
                                            $reg->save();
                                        }
                                    }

                                }
                            } else {
                                $reg = StudentRegistration::where('reg_no', $all_data[2])
                                    ->first();
                                if ($reg) {
                                    $reg->roll_no = $all_data[1];
                                    $reg->reg_no = $all_data[2];
                                    $reg->regdate = date('Y-m-d', strtotime($all_data[12]));
                                    $reg->dob = date('Y-m-d', strtotime($all_data[14]));
                                    $reg->stdname = $all_data[3] ?? '';
                                    $reg->gender = $all_data[5] ?? '';
                                    $reg->fathercnic = $all_data[7] ?? '';
                                    $reg->fathername = $all_data[6] ?? '';
                                    $reg->fatherprofession = $all_data[8] ?? '';
                                    $reg->mothername = $all_data[9] ?? '';
                                    $reg->mothercnic = $all_data[10] ?? '';
                                    $reg->motherprofession = $all_data[11] ?? '';
                                    $reg->address = $all_data[17] ?? '';
                                    $reg->registrationfee = $all_data[18] ?? null;
                                    // Update status from sheet data
                                    $reg->student_status = $all_data[19];
                                    $reg->active_status = $all_data[20];
                                    $reg->reg_class = $class->id;
                                    $reg->register_option = $regType;
                                    $reg->save();
                                    $reg->created_at = $created_at;
                                    $reg->updated_at = $created_at;
                                    $reg->save();
                                    // enrollment update
                                    $enr = StudentEnrollments::where('enrollId', $all_data[1])
                                        ->where('regId', $reg->id)->first();
                                    if ($enr) {
                                        $enr->regId = $reg->id;
                                        $enr->adm_branch = $branch->id;
                                        $enr->adm_session = 2;
                                        $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                        // Update enrollment status from sheet data
                                        $enr->active_status = $all_data[20];
                                        if ($all_data[21] != '') {
                                            // withdrwa date exist then add withdraw
                                            $withdraw = new StudentWithdrawal;
                                            $withdraw->student_id = $enr->enrollId;
                                            $withdraw->class_id = $enr->class_id;
                                            $withdraw->section_id = $enr->section_id;
                                            $withdraw->branch_id = $branch->id;
                                            $withdraw->session_id = 2;
                                            $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                            $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                            $withdraw->reason = 'Imported Withdraw';
                                            $withdraw->created_by = 2;
                                            $withdraw->owned_by = $branch->id;
                                            $withdraw->created_at = $created_at;
                                            $withdraw->save();
                                            $this->applyWithdrawalState($reg, $enr);
                                        }
                                        $enr->save();
                                    } else {
                                        // dd('enr not found at third positon');
                                        $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
                                        if ($enr) {
                                            $enr->regId = $reg->id;
                                            $enr->adm_branch = $branch->id;
                                            $enr->adm_session = 2;
                                            $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                            // Update enrollment status from sheet data
                                            $enr->active_status = $all_data[20];
                                            if ($all_data[21] != '') {
                                                // withdrwa date exist then add withdraw
                                                $withdraw = new StudentWithdrawal;
                                                $withdraw->student_id = $enr->enrollId;
                                                $withdraw->class_id = $enr->class_id;
                                                $withdraw->section_id = $enr->section_id;
                                                $withdraw->branch_id = $branch->id;
                                                $withdraw->session_id = 2;
                                                $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                $withdraw->reason = 'Imported Withdraw';
                                                $withdraw->created_by = 2;
                                                $withdraw->owned_by = $branch->id;
                                                $withdraw->created_at = $created_at;
                                                $withdraw->save();
                                                $this->applyWithdrawalState($reg, $enr);
                                            }
                                            $enr->save();
                                        } else {
                                            $enr = StudentEnrollments::where('regId', $reg->id)->first();
                                            if ($enr) {
                                                $enr->enrollId = $all_data[1];
                                                $enr->adm_branch = $branch->id;
                                                $enr->adm_session = 2;
                                                $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                                // Update enrollment status from sheet data
                                                $enr->active_status = $all_data[20];
                                                if ($all_data[21] != '') {
                                                    // withdrwa date exist then add withdraw
                                                    $withdraw = new StudentWithdrawal;
                                                    $withdraw->student_id = $enr->enrollId;
                                                    $withdraw->class_id = $enr->class_id;
                                                    $withdraw->section_id = $enr->section_id;
                                                    $withdraw->branch_id = $branch->id;
                                                    $withdraw->session_id = 2;
                                                    $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                    $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                    $withdraw->reason = 'Imported Withdraw';
                                                    $withdraw->created_by = 2;
                                                    $withdraw->owned_by = $branch->id;
                                                    $withdraw->created_at = $created_at;
                                                    $withdraw->save();
                                                    $this->applyWithdrawalState($reg, $enr);
                                                }
                                                $enr->save();
                                            } else {
                                                $enr = new StudentEnrollments;
                                                $enr->enrollId = $all_data[1];
                                                $enr->regId = $reg->id;
                                                $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                                $enr->class_id = $class->id;
                                                $enr->adm_session = 2;
                                                $enr->adm_branch = $branch->id;
                                                $enr->session_id = 2;
                                                $enr->owned_by = $branch->id;
                                                $enr->created_at = $created_at;
                                                $enr->active_status = $all_data[20];
                                                $enr->save();
                                                $reg->roll_no = $all_data[1];
                                                $reg->save();
                                                $reg->created_at = $created_at;
                                                $reg->updated_at = $created_at;
                                                $reg->created_by = 2;
                                                $reg->owned_by = $branch->id;
                                                $reg->save();
                                            }
                                        }

                                    }
                                } else {
                                    // on basis of name
                                    $reg = StudentRegistration::where('stdname', $all_data[3])
                                        ->whereNotIn('student_status', ['other', 'withdrawal', 'Enrolled', 'Registered'])
                                        ->first();
                                    if ($reg) {
                                        $reg->roll_no = $all_data[1];
                                        $reg->reg_no = $all_data[2];
                                        $reg->regdate = date('Y-m-d', strtotime($all_data[12]));
                                        $reg->dob = date('Y-m-d', strtotime($all_data[14]));
                                        $reg->gender = $all_data[5] ?? '';
                                        $reg->fathercnic = $all_data[7] ?? '';
                                        $reg->fathername = $all_data[6] ?? '';
                                        $reg->fatherprofession = $all_data[8] ?? '';
                                        $reg->mothername = $all_data[9] ?? '';
                                        $reg->mothercnic = $all_data[10] ?? '';
                                        $reg->motherprofession = $all_data[11] ?? '';
                                        $reg->address = $all_data[17] ?? '';
                                        $reg->registrationfee = $all_data[18] ?? null;
                                        // Update status from sheet data
                                        $reg->student_status = $all_data[19];
                                        $reg->active_status = $all_data[20];
                                        $reg->reg_class = $class->id;

                                        $reg->register_option = $regType;
                                        $reg->save();
                                        $reg->created_at = $created_at;
                                        $reg->updated_at = $created_at;
                                        $reg->save();
                                        // enrollment update
                                        $enr = StudentEnrollments::where('enrollId', $all_data[1])->where('regId', $reg->id)->first();
                                        if ($enr) {
                                            $enr->regId = $reg->id;
                                            $enr->adm_branch = $branch->id;
                                            $enr->adm_session = 2;
                                            $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                            // Update enrollment status from sheet data
                                            $enr->active_status = $all_data[20];
                                            if ($all_data[21] != '') {
                                                // withdrwa date exist then add withdraw
                                                $withdraw = new StudentWithdrawal;
                                                $withdraw->student_id = $enr->enrollId;
                                                $withdraw->class_id = $enr->class_id;
                                                $withdraw->section_id = $enr->section_id;
                                                $withdraw->branch_id = $branch->id;
                                                $withdraw->session_id = 2;
                                                $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                $withdraw->reason = 'Imported Withdraw';
                                                $withdraw->created_by = 2;
                                                $withdraw->owned_by = $branch->id;
                                                $withdraw->created_at = $created_at;
                                                $withdraw->save();
                                                $this->applyWithdrawalState($reg, $enr);
                                            }
                                            $enr->save();
                                        } else {
                                            // dd('enr not found at fourth positon');
                                            $enr = StudentEnrollments::where('enrollId', $all_data[1])->first();
                                            if ($enr) {
                                                $enr->regId = $reg->id;
                                                $enr->adm_branch = $branch->id;
                                                $enr->adm_session = 2;
                                                $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                                // Update enrollment status from sheet data
                                                $enr->active_status = $all_data[20];
                                                if ($all_data[21] != '') {
                                                    // withdrwa date exist then add withdraw
                                                    $withdraw = new StudentWithdrawal;
                                                    $withdraw->student_id = $enr->enrollId;
                                                    $withdraw->class_id = $enr->class_id;
                                                    $withdraw->section_id = $enr->section_id;
                                                    $withdraw->branch_id = $branch->id;
                                                    $withdraw->session_id = 2;
                                                    $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                    $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                    $withdraw->reason = 'Imported Withdraw';
                                                    $withdraw->created_by = 2;
                                                    $withdraw->owned_by = $branch->id;
                                                    $withdraw->created_at = $created_at;
                                                    $withdraw->save();
                                                    $this->applyWithdrawalState($reg, $enr);
                                                }
                                                $enr->save();
                                            } else {
                                                $enr = StudentEnrollments::where('regId', $reg->id)->first();
                                                if ($enr) {
                                                    $enr->enrollId = $all_data[1];
                                                    $enr->adm_branch = $branch->id;
                                                    $enr->adm_session = 2;
                                                    $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                                    // Update enrollment status from sheet data
                                                    $enr->active_status = $all_data[20];
                                                    if ($all_data[21] != '') {
                                                        // withdrwa date exist then add withdraw
                                                        $withdraw = new StudentWithdrawal;
                                                        $withdraw->student_id = $enr->enrollId;
                                                        $withdraw->class_id = $enr->class_id;
                                                        $withdraw->section_id = $enr->section_id;
                                                        $withdraw->branch_id = $branch->id;
                                                        $withdraw->session_id = 2;
                                                        $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                                        $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                                        $withdraw->reason = 'Imported Withdraw';
                                                        $withdraw->created_by = 2;
                                                        $withdraw->owned_by = $branch->id;
                                                        $withdraw->created_at = $created_at;
                                                        $withdraw->save();
                                                        $this->applyWithdrawalState($reg, $enr);
                                                    }
                                                    $enr->save();
                                                } else {
                                                    $enr = new StudentEnrollments;
                                                    $enr->enrollId = $all_data[1];
                                                    $enr->regId = $reg->id;
                                                    $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                                    $enr->class_id = $class->id;
                                                    $enr->adm_session = 2;
                                                    $enr->adm_branch = $branch->id;
                                                    $enr->session_id = 2;
                                                    $enr->owned_by = $branch->id;
                                                    $enr->created_at = $created_at;
                                                    $enr->active_status = $all_data[20];
                                                    $enr->save();
                                                    $reg->roll_no = $all_data[1];
                                                    $reg->save();
                                                    $reg->created_at = $created_at;
                                                    $reg->updated_at = $created_at;
                                                    $reg->save();
                                                }
                                            }
                                        }
                                    } else {
                                        // new
                                        $reg = new StudentRegistration;
                                        $reg->roll_no = $all_data[1];
                                        $reg->reg_no = $all_data[2];
                                        $reg->regdate = date('Y-m-d', strtotime($all_data[12]));
                                        $reg->dob = date('Y-m-d', strtotime($all_data[14]));
                                        $reg->stdname = $all_data[3] ?? '';
                                        $reg->gender = $all_data[5] ?? '';
                                        $reg->fathercnic = $all_data[7] ?? '';
                                        $reg->fathername = $all_data[6] ?? '';
                                        $reg->fatherprofession = $all_data[8] ?? '';
                                        $reg->mothername = $all_data[9] ?? '';
                                        $reg->mothercnic = $all_data[10] ?? '';
                                        $reg->motherprofession = $all_data[11] ?? '';
                                        $reg->address = $all_data[17] ?? '';
                                        $reg->registrationfee = $all_data[18] ?? null;
                                        // Set status from sheet data
                                        $reg->student_status = $all_data[19];
                                        $reg->active_status = $all_data[20];
                                        $reg->reg_class = $class->id;
                                        $reg->register_option = $regType;
                                        $reg->owned_by = $branch->id;
                                        $reg->created_by = \Auth::user()->creatorId();
                                        $reg->save();
                                        $reg->created_at = $created_at;
                                        $reg->updated_at = $created_at;
                                        $reg->save();
                                        // enrollment add
                                        $enr = new StudentEnrollments;
                                        $enr->enrollId = $all_data[1];
                                        $enr->regId = $reg->id;
                                        $enr->adm_date = date('Y-m-d', strtotime($all_data[13]));
                                        $enr->class_id = $class->id;
                                        $enr->adm_session = 2;
                                        $enr->adm_branch = $branch->id;
                                        $enr->session_id = 2;
                                        $enr->active_status = $all_data[20];
                                        $enr->created_at = $created_at;
                                        $enr->owned_by = $branch->id;
                                        $enr->created_by = \Auth::user()->creatorId();
                                        $enr->save();
                                        if ($all_data[21] != '') {
                                            // withdrwa date exist then add withdraw
                                            $withdraw = new StudentWithdrawal;
                                            $withdraw->student_id = $enr->enrollId;
                                            $withdraw->class_id = $enr->class_id;
                                            $withdraw->section_id = $enr->section_id;
                                            $withdraw->branch_id = $branch->id;
                                            $withdraw->session_id = 2;
                                            $withdraw->apply_date = date('Y-m-d', strtotime($all_data[13]));
                                            $withdraw->withdraw_date = date('Y-m-d', strtotime($all_data[21]));
                                            $withdraw->reason = 'Imported Withdraw';
                                            $withdraw->created_by = 2;
                                            $withdraw->owned_by = $branch->id;
                                            $withdraw->created_at = $created_at;
                                            $withdraw->save();
                                            $this->applyWithdrawalState($reg, $enr);
                                        }
                                    }

                                }
                            }
                            if (!empty($all_data[16] && $all_data[16] != 0)) {

                                // delete the registration challan if more than 1
                                $existingChallan = Challans::where('student_id', $reg->id)
                                    ->where('challan_type', 'Registration')
                                    ->where('total_amount', $all_data[18] ?? 0)
                                    ->get();
                                if ($existingChallan->count() > 1) {
                                    foreach ($existingChallan as $key => $chalanToDelete) {
                                        if ($key > 0) {
                                            // delete challan heads
                                            ChallanHead::where('challan_id', $chalanToDelete->id)->delete();
                                            // delete receipts
                                            StudentReceipt::where('challan_id', $chalanToDelete->id)->delete();
                                            // delete challan
                                            $chalanToDelete->delete();
                                        }
                                    }
                                }

                                // if challan exist for registration fee then skip challan creation
                                $existingChallan = Challans::where('student_id', $reg->id)
                                    ->where('challan_type', 'Registration')
                                    ->where('total_amount', $all_data[18] ?? 0)
                                    ->first();
                                if ($existingChallan) {
                                    $error_reason = 'Challan already exists for this registration fee';
                                    $error_counter++;
                                    $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
                                    $skipped_records[] = $all_data; // Store the
                                    $count++;

                                    continue;
                                }

                                // Create new challan
                                $challan = new Challans;
                                $challan->student_id = $reg->id;
                                $challan->class_id = $class->id ?? null;
                                $challan->challanNo = $this->challanNo();
                                $challan->challan_date = $regdate;
                                $challan->challan_type = 'Registration';
                                $challan->total_amount = $all_data[18] ?? 0;
                                $challan->paid_amount = $all_data[18] ?? 0;
                                $challan->issue_date = $regdate;

                                // Handle due date (one week after registration date)
                                try {
                                    $due_date = Carbon::parse($regdate)->addWeek()->toDateString();
                                } catch (\Exception $e) {
                                    $due_date = Carbon::now()->addWeek()->toDateString();
                                }

                                $challan->due_date = $due_date;
                                $challan->status = 'Paid';
                                $challan->session_id = 2;
                                $challan->owned_by = $reg->owned_by;
                                $challan->created_by = \Auth::user()->creatorId();
                                $challan->save();
                                $challan->created_at = $created_at;
                                $challan->updated_at = $created_at;
                                $challan->save();

                                // Find bank account
                                $bankAccount = BankAccount::where('owned_by', $challan->owned_by)
                                    ->where('bank_name', 'like', '%CSH')->first();
                                // dd($bankAccount,$all_data);
                                if (!$bankAccount) {
                                    if ($challan->owned_by == 1906) {
                                        $bankAccount = BankAccount::where('id', 33)->first();
                                    } else {
                                        dd($all_data, 'bank account not found' . $challan->owned_by);
                                        $error_reason = 'Bank account not found for owner ID: ' . $challan->owned_by;
                                        $error_counter++;
                                        $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
                                        $skipped_records[] = $all_data; // Store the
                                        $count++;

                                        continue;
                                    }
                                }

                                // Create student receipt
                                $recipts = new StudentReceipt;
                                $recipts->recipt_date = $regdate;
                                $recipts->challan_id = $challan->id;
                                $recipts->recipt_amount = $challan->paid_amount;
                                $recipts->student_id = $challan->student_id;
                                $recipts->challan_amount = $challan->paid_amount;
                                $recipts->late_amount = 0;
                                $recipts->arrears = 0;
                                $recipts->bank_id = $bankAccount->id;
                                $recipts->referance = 'Registration Fee';
                                $recipts->receive_type = 'CD';
                                $recipts->received_by = Auth::user()->id;
                                $recipts->owned_by = $challan->owned_by;
                                $recipts->created_by = \Auth::user()->creatorId();
                                $recipts->save();
                                $recipts->created_at = $created_at;
                                $recipts->updated_at = $created_at;
                                $recipts->save();

                                // Find registration fee head
                                $pattern = '%registration%';
                                $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                                if (!$adm_fee_head) {
                                    $error_reason = 'Registration fee head not found';
                                    $error_counter++;
                                    $error_records[] = [$count, $all_data[2], $all_data[3] ?? 'N/A', $error_reason];
                                    $skipped_records[] = $all_data; // Store the
                                    $count++;

                                    continue;
                                    // throw new \Exception('Registration fee head not found');
                                }

                                // Create challan head
                                $challan_head = new ChallanHead;
                                $challan_head->challan_id = $challan->id;
                                $challan_head->head_id = $adm_fee_head->id;
                                $challan_head->price = $all_data[18] ?? 0;
                                $challan_head->concession = 0;
                                $challan_head->save();
                                $challan_head->created_at = $created_at;
                                $challan_head->updated_at = $created_at;
                                $challan_head->save();

                                $item['0']['head'] = $challan_head->head_id;
                                $item['0']['price'] = $all_data[18] ?? 0;
                                $item['0']['quantity'] = 1;
                                $item['0']['concession'] = 0;
                                $item['0']['total'] = $all_data[18] ?? 0;

                                $data = [
                                    'id' => $challan->id,
                                    'challan_id' => $challan->id,
                                    'no' => $challan->challanNo,
                                    'prod_id' => $recipts->id,
                                    'date' => $challan->challan_date,
                                    'recipt' => $recipts->id,
                                    'reference' => $challan->student_id,
                                    'description' => 'Registration Fee Amount',
                                    'user_id' => $challan->student_id,
                                    'branch_name' => optional(optional($challan->student)->branch_name)->name ?? '',
                                    'std_name' => $all_data[4],
                                    'branch_id' => $reg->owned_by,
                                    'bank_id' => $bankAccount->id,
                                    'bank_name' => $bankAccount->bank_name,
                                    'amount' => $challan->paid_amount,
                                    'total' => $challan->paid_amount,
                                    'user_type' => 'Student',
                                    'category' => 'Registration',
                                    'owned_by' => $challan->owned_by,
                                    'created_by' => $challan->created_by,
                                    'account_id' => $bankAccount->chart_account_id,
                                    'items' => $item,
                                    'created_at' => $created_at,
                                ];

                                // Update registration fee
                                $updateStdreg = StudentRegistration::find($reg->id);
                                if ($updateStdreg) {
                                    $updateStdreg->registrationfee = $all_data[18] ?? 0;
                                    $updateStdreg->active_status = 1;
                                    $updateStdreg->created_by = \Auth::user()->creatorId();
                                    $updateStdreg->owned_by = $branch->id;
                                    $updateStdreg->created_at = $created_at;
                                    $updateStdreg->save();
                                }

                                // Update bank account balance
                                if ($bankAccount->id) {
                                    Utility::bankAccountBalance($bankAccount->id, $challan->paid_amount, 'credit');
                                }

                                // Create accounting entries
                                $dataret = Utility::crv_entry($data);
                                $challan->voucher_id = $dataret;
                                $challan->save();
                            
                            }
                        }
                        //log
                        Log::info('Registration Import: ' . $success_counter . ' imported successfully');
                        $success_counter++;
                    }
                    $count++;
                }

                fclose($handle);
                DB::commit();

                // Export error records to Excel if any exist
                if (!empty($error_records)) {
                    $export_filename = 'registration_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Row', 'Registration No', 'Student Name', 'Error']);
                    foreach ($error_records as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }
                $message = "{$success_counter} Student Registration added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.";

                return redirect()->back()->with('message', $message);
            }
            throw new \Exception("Unable to open file: {$filepath}");
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::error('Registration Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function applyWithdrawalState($reg, $enr)
    {
        // Update registration
        $reg->update([
            'student_status' => 'Withdrawal',
            'active_status' => 0,
        ]);

        // Deactivate ALL enrollments of this student
        StudentEnrollments::where('regId', $reg->id)->update([
            'active_status' => 0,
        ]);
    }

    private function TransferImport($file, $request)
    {
        // dd($file);
        set_time_limit(0);
        $filename = $file->getClientOriginalName();
        $file->move(public_path(path: 'assets/import/csv_file/'), $filename);
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
                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';
                        // dd($all_data);
                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $reason = 'Empty enrollment ID';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Validate enrollment
                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $reason = 'Enrollment not found';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Get student name
                        $student_name = 'N/A';
                        $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
                        if ($reg) {
                            $student_name = $reg->stdname;
                        }

                        // Clean and validate branch from data (column 5)
                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[5]);
                        $branchfrom = User::where('name', 'like', '%' . $clean_title . '%')->first();
                        if (!$branchfrom) {
                            dd($all_data, 'branchfrom', $clean_title);
                            $reason = 'From branch not found: ' . $all_data[5];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Clean and validate branch to data (column 3)
                        $clean_title_to = preg_replace('/\s*\(.*?\)/', '', $all_data[3]);
                        $branchto = User::where('name', 'like', '%' . $clean_title_to . '%')->first();
                        if (!$branchto) {
                            dd($all_data, 'branchto', $clean_title_to);
                            $reason = 'To branch not found: ' . $all_data[3];
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Validate class to and from
                        $classto = Classes::where('name', $all_data[4])->where('owned_by', $branchto->id)->first();
                        if (!$classto) {
                            $classto = new Classes;
                            $classto->name = $all_data[4];
                            $classto->owned_by = $branchto->id;
                            $classto->created_by = auth()->user()->id;
                            $classto->save();
                        }

                        $classfrom = Classes::where('name', $all_data[6])->where('owned_by', $branchfrom->id)->first();
                        if (!$classfrom) {
                            $classfrom = new Classes;
                            $classfrom->name = $all_data[6];
                            $classfrom->owned_by = $branchfrom->id;
                            $classfrom->created_by = auth()->user()->id;
                            $classfrom->save();
                            // $reason = 'Source class not found: ' . $all_data[6];
                            // $error_counter++;
                            // $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            // $count++;
                            // continue;
                        }

                        // Get first section from source class/branch
                        $sectionClassFrom = ClassSection::where('class_id', $classfrom->id)
                            ->where('owned_by', $branchfrom->id)
                            // ->where('active_status', 1)
                            ->first();

                        if (!$sectionClassFrom) {
                            $sectionfrom = new Section;
                            $sectionfrom->name = 'A';
                            $sectionfrom->owned_by = $branchfrom->id;
                            $sectionfrom->created_by = auth()->user()->id;
                            $sectionfrom->save();
                            $sectionClassFrom = new ClassSection;
                            $sectionClassFrom->class_id = $classfrom->id;
                            $sectionClassFrom->section_id = $sectionfrom->id;
                            $sectionClassFrom->owned_by = $branchfrom->id;
                            $sectionClassFrom->created_by = auth()->user()->id;
                            $sectionClassFrom->save();
                        }
                        $sectionfrom = Section::find($sectionClassFrom->section_id);

                        // Get first section from destination class/branch
                        $sectionClassTo = ClassSection::where('class_id', $classto->id)
                            ->where('owned_by', $branchto->id)
                            // ->where('active_status', 1)
                            ->first();

                        if (!$sectionClassTo) {
                            $sectionto = new Section;
                            $sectionto->name = 'A';
                            $sectionto->owned_by = $branchto->id;
                            $sectionto->created_by = auth()->user()->id;
                            $sectionto->save();
                            $sectionClassTo = new ClassSection;
                            $sectionClassTo->class_id = $classto->id;
                            $sectionClassTo->section_id = $sectionto->id;
                            $sectionClassTo->owned_by = $branchto->id;
                            $sectionClassTo->created_by = auth()->user()->id;
                            $sectionClassTo->save();
                        }
                        $sectionto = Section::find($sectionClassTo->section_id);

                        // Check for existing transfer (prevent duplicates)
                        $transf = StudentTransfer::where('student_id', $enr->enrollId)->first();
                        if ($transf) {
                            $reason = 'Student already transferred';
                            $duplication_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Parse transfer date
                        $transfer_date = date('Y-m-d');
                        if (!empty($all_data[9])) {
                            try {
                                $transfer_date = date('Y-m-d', strtotime($all_data[9]));
                            } catch (\Exception $e) {
                                // If date parsing fails, use current date
                                $transfer_date = date('Y-m-d');
                            }
                        }

                        // Determine transfer type
                        $transfer_type = '';
                        if (isset($all_data[12]) && !empty($all_data[12])) {
                            $transfer_type = trim($all_data[12]);
                        }

                        // Create transfer entry
                        $transfer = new StudentTransfer;
                        $transfer->student_id = $enr->enrollId;
                        $transfer->transfer_date = $transfer_date;
                        $transfer->transfer_type = $transfer_type;
                        $transfer->branch_from = $branchfrom->id;
                        $transfer->class_from = $classfrom->id;
                        $transfer->section_from = $sectionfrom->id;
                        $transfer->branch_to = $branchto->id;
                        $transfer->class_to = $classto->id;
                        $transfer->section_to = $sectionto->id;
                        $transfer->reason = isset($all_data[8]) ? $all_data[8] : '';
                        $transfer->session_id = $enr->session_id;
                        $transfer->owned_by = $enr->owned_by;
                        $transfer->created_by = \Auth::user()->creatorId();
                        $transfer->save();
                        // challan
                        $challan = new Challans;
                        $challan->student_id = $all_data[1];
                        $challan->rollno = $all_data[0];
                        $challan->class_id = $classfrom->id;
                        $challan->challanNo = $this->challanNo();
                        $challan->challan_date = $transfer_date;
                        $challan->challan_type = $transfer_type;
                        $challan->issue_date = $transfer_date;
                        $challan->due_date = $transfer_date;
                        $challan->status = 'paid';
                        $challan->session_id = $enr->session_id;
                        $challan->owned_by = $enr->owned_by;
                        $challan->created_by = \Auth::user()->creatorId();
                        $challan->save();
                        $challan->created_at = date('Y-m-d H:i:s', strtotime($transfer_date));
                        $challan->updated_at = date('Y-m-d H:i:s', strtotime($transfer_date));
                        $challan->save();
                        $concessionAmount = 0;
                        $itemIndex = 0;
                        $pattern = '%transfer fee%';
                        $trns_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();
                        $challan_head = new ChallanHead;
                        $challan_head->challan_id = $challan->id;
                        $challan_head->head_id = $trns_fee_head->id;
                        $challan_head->price = $all_data[7];
                        $challan_head->concession = 0;
                        $challan_head->save();
                        // jv
                        $raw_price = $all_data[7];
                        $clean_price = (float) str_replace(',', '', $raw_price);
                        $clean_price = (int) str_replace(',', '', $raw_price);
                        $item[$itemIndex]['head'] = $trns_fee_head->id;
                        $item[$itemIndex]['price'] = $clean_price;
                        $item[$itemIndex]['quantity'] = 1;
                        $item[$itemIndex]['concession'] = 0;
                        $item[$itemIndex]['total'] = $clean_price;
                        $data['id'] = $challan->id;
                        $data['no'] = $challan->challanNo;
                        $data['date'] = $challan->challan_date;
                        $data['reference'] = $challan->student_id;
                        $data['category'] = 'Transfer Challan';
                        $data['user_id'] = $enr->enrollId;
                        $data['user_type'] = 'Student';
                        $data['owned_by'] = $challan->owned_by;
                        $data['created_by'] = $challan->created_by;
                        $data['created_at'] = date('Y-m-d H:i:s', strtotime($transfer_date));
                        $data['updated_at'] = date('Y-m-d H:i:s', strtotime($transfer_date));
                        $data['items'] = $item;
                        // dd($data);
                        $dataret = Utility::jrentry($data);
                        $challan->voucher_id = $dataret;
                        $challan->save();

                        //
                        $journalItem = new JournalItem;
                        $journalItem->journal = @$challan->voucher_id;
                        $journalItem->account = @$trns_fee_head->account_id;
                        $journalItem->head = @$trns_fee_head->id;
                        $journalItem->description = $trns_fee_head->fee_head;
                        $journalItem->entry_id = @$challan_head->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = $clean_price;
                        $journalItem->debit = 0;
                        $journalItem->save();
                        $journalItem->created_at = @$challan->created_at;
                        $journalItem->updated_at = @$challan->updated_at;
                        $journalItem->save();

                        //  reciveable entry
                        $journalItem = new JournalItem;
                        $journalItem->journal = @$challan->voucher_id;
                        $journalItem->account = @$trns_fee_head->receivable_account_id;
                        $journalItem->head = $trns_fee_head->id;
                        $journalItem->description = 'Reciveable of Challan no : ' . @$challan->challanNo;
                        $journalItem->entry_id = @$challan_head->id;
                        $journalItem->types = 'Challan';
                        $journalItem->credit = 0;
                        $journalItem->debit = $clean_price;
                        $journalItem->save();
                        $journalItem->created_at = @$challan->created_at;
                        $journalItem->updated_at = @$challan->updated_at;
                        $journalItem->save();
                        $school = User::find($challan->owned_by);
                        $bankAccount = BankAccount::where('owned_by', $school->id)->first();
                        // dd($all_data,$bankAccount,$challan->owned_by);
                        $recipts = new StudentReceipt;
                        $recipts->recipt_date = date('Y-m-d', strtotime($all_data[2]));
                        $recipts->challan_id = $challan->id;
                        $recipts->recipt_amount = $all_data[7];
                        $recipts->student_id = $challan->student_id;
                        $recipts->challan_amount = $all_data[7];
                        $recipts->late_amount = 0;
                        $recipts->arrears = 0;
                        $recipts->bank_id = $bankAccount->id;
                        $recipts->referance = $challan->challan_type;
                        $recipts->receive_type = $all_data[11];
                        $recipts->received_by = Auth::user()->id;
                        $recipts->owned_by = $challan->owned_by;
                        $recipts->created_by = \Auth::user()->creatorId();
                        $recipts->save();
                        $recipts->created_at = date('Y-m-d H:i:s', strtotime($all_data[2]));
                        $recipts->updated_at = date('Y-m-d H:i:s', strtotime($all_data[2]));
                        $recipts->save();
                        if ($recipts) {
                            $challan->status = 'paid';
                            $challan->paid_date = date('Y-m-d', strtotime($all_data[2]));
                            $challan->save();
                            $challan_head = ChallanHead::where('head_id', $trns_fee_head->id)->where('challan_id', $challan->id)->first();
                            $challan_head->paid = @$challan_head->paid + intval($all_data[7]);
                            $challan_head->save();

                            $data['id'] = $challan->id;
                            $data['no'] = $challan->challanNo;
                            $data['date'] = $challan->paid_date;
                            $data['reference'] = $challan->reference;
                            $data['description'] = $challan->description;
                            $data['user_id'] = $challan->student_id;
                            $data['user_type'] = 'Student';
                            $data['amount'] = $challan->amount;
                            $data['category'] = $challan->challan_type;
                            $data['owned_by'] = $challan->owned_by;
                            $data['created_by'] = \Auth::user()->creatorId();
                            $data['account_id'] = $bankAccount->chart_account_id;
                            $data['recipt'] = $recipts->id;
                            $data['items'] = $item;
                            $data['total'] = $all_data[7];
                            // dd($data);
                            // $dataret = Utility::brv_entry($data);
                            if (ucwords($all_data[11]) == 'CD') {
                                $dataret = Utility::crv_entry($data);
                            } else {
                                $dataret = Utility::brv_entry($data);
                            }

                        }
                        $transfer->challan_id = $challan->id;
                        $transfer->save();
                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();

                // Generate error file if there are errors
                if (!empty($skip_data)) {
                    $export_filename = 'transfer_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');

                    // Write the header for the error file
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);

                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }

                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                // If no errors, return success message
                return redirect()->back()->with('message', "{$success_counter} Student Transfer(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::error('Transfer Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function RegularChallanImport($file, $request)
    {
        set_time_limit(0);
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
                // index 1 => BRANCH name
                // index 3 => challan no
                // index 4 => roll no
                // index 5 => student name
                // index 6 => class name
                // index 7 => billing month
                // index 8 => class month fee
                // index 9 => admission head amount
                // index 10 => annual head amount
                // index 11 => tuition total amount
                // index 12 => tuition discounted amount
                // index 13 => tuition paid amount
                // index 14 => Computer haed amount
                // index 15 => SECURITY HEAD amount
                // index 16 => Security Adjustemnt
                // index 17 => readamission head amount
                // index 18 => monthly care head amount
                // index 19 => AC head amount
                // index 20 => ac discount %
                // index 21 => ac final amount
                // index 22 => extra care fee
                // index 23 => extra care discount %
                // index 24 => extra care final amount                
                // index 25 => Monthly care head amount
                // index 26 => stationary head amount
                // index 27 => study pack amount
                // index 28 => MONTHLY CARE-LATE STAY FEE
                // index 29 => transport charges
                // index 30 => Arrears
                // index 31 => Net Receivable
                // index 32 => Discount
                // index 33 => Category
                // index 34 => RegId
                // index 35 => temp status
                while (($all_data = fgetcsv($handle, 7000, ',')) !== false) {
                    // dd($all_data);
                    if ($count > 0) {
                        if ($all_data[1] == '' || $all_data[3] == '' || $all_data[4] == '' || $all_data[5] == '' || $all_data[6] == '') {
                            continue;
                        }
                        $dates = $all_data[7];
                        $billingMonth = array_values(array_filter(explode(', ', $dates)))[0];
                        $otherMonthCount = count(array_values(array_filter(explode(', ', $dates))));
                        $record_status = 'Error';
                        $reason = '';
                        $all_data = array_map('trim', $all_data);
                        $branch = User::where('name', 'like', '%' . $all_data[1] . '%')->first();
                        if (!$branch) {
                            $reason = 'Branch not found: ' . $all_data[1];
                            dd($all_data, $count, $branch, $reason);
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        // class
                        $class = Classes::where('name', 'like', '%' . $all_data[6] . '%')->where('owned_by', $branch->id)->first();
                        if (!$class) {
                            $class = new Classes;
                            $class->name = $all_data[6];
                            $class->owned_by = $branch->id;
                            $class->created_by = auth()->user()->id;
                            $class->save();
                        }
                        if (!empty($all_data[34])) {
                            $regclean = preg_replace('/\s*\(.*?\)/', '', $all_data[34]);
                            $rollclean = preg_replace('/\s*\(.*?\)/', '', $all_data[4]);
                            $reg = StudentRegistration::where('reg_no', $regclean)->where('roll_no', $rollclean)->first();
                            if (!$reg) {
                                // dd('Student not found', $all_data, $count, 'rollno ' . $rollclean, 'regno ' . $regclean);
                                $reason = 'Student not found against roll no :' . $all_data[4] . ' and reg no: ' . $all_data[34];
                                $error_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            }
                        } else {
                            dd('registration number empty', $all_data, $count);
                        }
                        $enr = StudentEnrollments::where('enrollId', $reg->roll_no)->first();
                        if (!$enr) {
                            dd('enrollment not found', $all_data, $count);
                        }
                        // if (!$enr) {
                        //     $reg = StudentRegistration::where('reg_no', $all_data[30])
                        //         // ->where('stdname', 'like', '%' . $all_data[5] . '%')
                        //         ->first();
                        //     if (!$reg) {
                        //         $reason = 'Registration not found: ' . $all_data[30];
                        //         $error_counter++;
                        //         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        //         $count++;
                        //         continue;
                        //     }
                        //     $enr = StudentEnrollments::where('regId', $reg->id)->first();
                        //     if ($enr) {
                        //         $enr->enrollId = $all_data[4];
                        //         $enr->save();
                        //         $reg->roll_no = $all_data[4];
                        //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
                        //             $reg->student_status = 'other';
                        //         }
                        //         $reg->save();
                        //     } else {
                        //         $enr = new StudentEnrollments();
                        //         $enr->adm_date = date('Y-m-d', strtotime($billingMonth));
                        //         $enr->class_id = $reg->class_id;
                        //         $enr->session_id = $reg->session_id;
                        //         $enr->adm_session = $reg->session_id;
                        //         $enr->adm_branch = $branch->id;
                        //         $enr->enrollId = $all_data[4];
                        //         $enr->regId = $reg->id;
                        //         $enr->active_status = 0;
                        //         $enr->owned_by = $branch->id;
                        //         $enr->created_by = \Auth::user()->creatorId();
                        //         $enr->save();
                        //         $reg->roll_no = $all_data[4];
                        //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
                        //             $reg->student_status = 'other';
                        //         }
                        //         $reg->save();
                        //     }
                        // } else {
                        //     $reg = StudentRegistration::where('roll_no', $all_data[4])->first();
                        //     if ($reg) {
                        //         $enr->regId = $reg->id;
                        //         $enr->save();
                        //         $reg->roll_no = $all_data[4];
                        //         $reg->reg_no = $all_data[30];
                        //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
                        //             $reg->student_status = 'other';
                        //         }
                        //         $reg->save();
                        //     }
                        // }

                        $timestamp = strtotime($billingMonth);
                        $year = date('Y', $timestamp);
                        $month = date('m', $timestamp);
                        $challan = Challans::where('student_id', $reg->id)
                            ->whereIn('challan_type', ['Regular', 'Advance'])
                            ->whereYear('challan_date', $year)
                            ->whereMonth('challan_date', $month)
                            ->exists();

                        if ($challan) {
                            if (strtolower($reg->stdname) != strtolower($all_data[5])) {
                                $reason = 'Challan already exists for another student with same roll no and reg: ' . $all_data[34] . ' and name: ' . $all_data[5];
                                $duplication_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            } else {
                                // dd('challan exist', $count, $all_data);
                            }
                            $reason = 'Challan already exists for student: ' . $reg->id;
                            $duplication_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        if ($otherMonthCount > 1) {
                            $advance = Challans::where('student_id', $reg->id)
                                ->where('challan_type', 'Advance')
                                ->whereYear('challan_date', $year)
                                ->whereMonth('challan_date', $month)
                                ->exists();
                            if ($advance) {
                                // dd('advance exist');
                                $reason = 'Advance challan already exists for student: ' . $reg->id;
                                $duplication_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            }
                        }
                        $challan = new Challans;
                        $challan->student_id = $reg->id;
                        $challan->rollno = $reg->roll_no;
                        $challan->class_id = $class->id;
                        // $challan->challanNo = $all_data[3];
                        $challan->challanNo = $this->challanNo();
                        $challan->challan_date = date('Y-m-d', strtotime($billingMonth));
                        $challan->fee_month = date('Y-m-d', strtotime($billingMonth));
                        $challan->challan_type = 'Regular';
                        $challan->temp_status = isset($all_data[35]) ? $all_data[35] : null;
                        $challan->concession_amount = 0;
                        $challan->total_amount = 0;
                        $challan->paid_amount = 0;
                        $challan->issue_date = date('Y-m-d', strtotime($billingMonth));
                        if ($otherMonthCount > 1) {
                            $months = explode(',', $all_data[7]);
                            $formattedMonths = [];
                            foreach ($months as $month) {
                                $timestamp = strtotime('01-' . trim($month));
                                if ($timestamp) {
                                    $formattedMonths[] = date('Y-m-d', $timestamp);
                                }
                            }
                            $challan->other_months = implode(',', $formattedMonths);
                            $challan->challan_type = 'Advance';
                        }
                        $challan->due_date = date('Y-m-d', strtotime($billingMonth . ' +7 days'));
                        $challan->status = 'issued';
                        $challan->session_id = $reg->session_id;
                        $challan->owned_by = $branch->id;
                        $challan->created_by = \Auth::user()->creatorId();
                        $challan->save();
                        $challan->created_at = date('Y-m-d H:i:s', strtotime($billingMonth));
                        $challan->updated_at = date('Y-m-d H:i:s', strtotime($billingMonth));
                        $challan->save();
                        // dd($challan, $all_data);
                        $fee_head_mappings = [
                            ['index' => 9, 'type' => 'admission'],        // Admission fee
                            ['index' => 11, 'type' => 'tuition'],         // Tuition fee
                            ['index' => 18, 'type' => 'monthly care'],   // Monthly care
                            ['index' => 19, 'type' => 'ac'],
                            ['index' => 15, 'type' => 'security'],
                            ['index' => 16, 'type' => 'security adjustment'],
                            ['index' => 14, 'type' => 'computer'],
                            ['index' => 17, 'type' => 'readmission'],
                            ['index' => 22, 'type' => 'extra care'],     // Extra care
                            ['index' => 26, 'type' => 'stationary'],     // Stationary
                            ['index' => 27, 'type' => 'study pack'],
                            ['index' => 28, 'type' => 'MONTHLY CARE-LATE STAY FEE'],
                            ['index' => 29, 'type' => 'transport'],
                        ];

                        // Define which heads should NOT be multiplied for advance challans
                        $non_recurring_heads = ['admission', 'security', 'security adjustment', 'annual', 'readmission'];

                        $total_fees = 0;
                        $item = [];
                        $itemIndex = 0;
                        $fee_head_mappings = array_filter(array_map(function ($mapping) use ($all_data, &$total_fees, $otherMonthCount, $non_recurring_heads) {
                            $index = $mapping['index'];

                            if (isset($all_data[$index]) && is_numeric($all_data[$index]) && $all_data[$index] > 0) {
                                $amount = $all_data[$index];

                                // Multiply by number of months if it's an advance challan AND the head is recurring
                                if ($otherMonthCount > 1 && !in_array($mapping['type'], $non_recurring_heads)) {
                                    $amount = $amount * $otherMonthCount;
                                }

                                $mapping['amount'] = $amount;
                                $total_fees += $amount;

                                return $mapping;
                            }

                            return null;
                        }, $fee_head_mappings));

                        $totalReceivedAmnt = 0;

                        foreach ($fee_head_mappings as $mapping) {
                            $index = $mapping['index'];
                            $type = $mapping['type'];
                            $fee_head = FeeHead::where('fee_head', 'LIKE', '%' . $type . '%')->first();
                            if ($fee_head) {
                                $head_amount = $mapping['amount'];
                                $concession = 0;

                                // Tuition discount (existing logic)
                                if ($type == 'tuition' && isset($all_data[12])) {
                                    $concession = $all_data[12];
                                    // Multiply concession by months for advance challans
                                    if ($otherMonthCount > 1) {
                                        $concession = $concession * $otherMonthCount;
                                    }
                                }

                                // AC head discount (index 19 amount, 20 discount %, 21 final amount)
                                if ($type == 'ac' && isset($all_data[19]) && isset($all_data[21])) {
                                    $ac_original = $all_data[19];
                                    $ac_final = $all_data[21];
                                    $concession = $ac_original - $ac_final;
                                    $head_amount = $ac_original;

                                    // Multiply by months for advance challans
                                    if ($otherMonthCount > 1) {
                                        $concession = $concession * $otherMonthCount;
                                        $head_amount = $head_amount * $otherMonthCount;
                                    }
                                }

                                // Extra care discount (index 22 amount, 23 discount %, 24 final amount)
                                if ($type == 'extra care' && isset($all_data[22]) && isset($all_data[24])) {
                                    $extra_care_original = $all_data[22];
                                    $extra_care_final = $all_data[24];
                                    $concession = $extra_care_original - $extra_care_final;
                                    $head_amount = $extra_care_original;

                                    // Multiply by months for advance challans
                                    if ($otherMonthCount > 1) {
                                        $concession = $concession * $otherMonthCount;
                                        $head_amount = $head_amount * $otherMonthCount;
                                    }
                                }

                                $totalReceivedAmnt += $head_amount;

                                $challan_head = new ChallanHead;
                                $challan_head->challan_id = $challan->id;
                                $challan_head->head_id = $fee_head->id;
                                $challan_head->price = $head_amount;
                                $challan_head->concession = $concession;
                                $challan_head->save();
                                $challan_head->created_at = date('Y-m-d H:i:s', strtotime($billingMonth));
                                $challan_head->updated_at = date('Y-m-d H:i:s', strtotime($billingMonth));
                                $challan_head->save();
                                
                                $challan->total_amount += $head_amount;
                                $challan->concession_amount += $concession;
                                $challan->save();
                                // add discount item as well
                                $item[$itemIndex]['prod_id'] = $challan_head->id;
                                $item[$itemIndex]['head'] = $fee_head->id;
                                $item[$itemIndex]['price'] = $head_amount ? $head_amount : 0;
                                $item[$itemIndex]['quantity'] = 1;
                                $item[$itemIndex]['concession'] = $concession;
                                $item[$itemIndex]['total'] = $head_amount;
                                $itemIndex++;
                            }
                        }
                        // if ($count == 176) {
                        //     dd($totalReceivedAmnt, $all_data,$item);
                        // }
                        // dd($totalReceivedAmnt,$item);
                        $challan->total_amount = $totalReceivedAmnt;
                        $challan->save();
                        // dd($item, $challan,$enr);
                        $data['id'] = $challan->id;
                        $data['no'] = $challan->challanNo;
                        $data['date'] = $challan->challan_date;
                        $data['reference'] = $challan->student_id;
                        $data['category'] = 'Regular';
                        $data['user_id'] = $reg->id;
                        $data['std_name'] = @$reg->stdname ?? '';
                        $data['branch_name'] = $branch->name;
                        $data['fee_month'] = date('M-y', strtotime($challan->fee_month));
                        $data['user_type'] = 'Student';
                        $data['owned_by'] = $challan->owned_by;
                        $data['created_by'] = $challan->created_by;
                        $data['created_at'] = date('Y-m-d H:i:s', strtotime($billingMonth));
                        $data['updated_at'] = date('Y-m-d H:i:s', strtotime($billingMonth));
                        $data['items'] = $item;
                        $dataret = Utility::jrentry($data);
                        $challan->voucher_id = $dataret;
                        $challan->save();
                        // if ($all_data[29] != 'Discount Policy' || $all_data[29] != '') {

                        //     $parsedConcessions = [];

                        //     $policyString = $all_data[29]; // Example: '15%T.FEE+15%ADM+50%SECURITY'

                        //     $headNameMappings = [
                        //         'T.FEE' => 'TUITION',
                        //         'TUT' => 'TUITION',
                        //         'TUITION' => 'TUITION',
                        //         // add more mappings as needed
                        //     ];

                        //     $policyParts = explode('+', $policyString);
                        //     $extractedHeads = [];

                        //     foreach ($policyParts as $part) {
                        //         if (preg_match('/^([\d.]+)%(.+)$/', trim($part), $matches)) {
                        //             $percentage = (float) $matches[1];
                        //             $fullHeadName = trim($matches[2]);
                        //             $headName = preg_replace('/\([^)]+\)/', '', $fullHeadName);
                        //             $headName = trim($headName);
                        //             $cleanHeadName = strtoupper($headName);

                        //             if (array_key_exists($cleanHeadName, $headNameMappings)) {
                        //                 $headName = $headNameMappings[$cleanHeadName];
                        //             }

                        //             $feeHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($headName).'%'])->first();

                        //             if ($feeHead) {
                        //                 $extractedHeads[] = [
                        //                     'head_id' => $feeHead->id,
                        //                     'percentage' => $percentage,
                        //                 ];
                        //             }
                        //         }
                        //     }

                        //     $matchingPolicies = ConcessionPolicy::with('policy_head') // Assuming relation to pivot table
                        //         ->get()
                        //         ->filter(function ($policy) use ($extractedHeads) {

                        //             // Step 2.1: Compare total number of heads
                        //             if (count($policy->policy_head) != count($extractedHeads)) {
                        //                 return false;
                        //             }

                        //             foreach ($extractedHeads as $extractedHead) {
                        //                 $match = $policy->policy_head->firstWhere('head_id', $extractedHead['head_id']);

                        //                 // Head not found or percentage does not match
                        //                 if (!$match || $match->percentage != $extractedHead['percentage']) {
                        //                     return false;
                        //                 }
                        //             }

                        //             return true; // All heads and percentages matched
                        //         });
                        //     $a = $matchingPolicies->pluck('id')->toArray();
                        //     // dd($a,$extractedHeads,$policyParts,$policyString);
                        //     // if no policies found, error throw error
                        //     if (count($a) == 0) {
                        //         // create new policy
                        //         $policy = new ConcessionPolicy;
                        //         $policy->order_no = 1;
                        //         $policy->title = $all_data[39];
                        //         $policy->description = $all_data[39];
                        //         $policy->owned_by = \Auth::user()->ownedId();
                        //         $policy->created_by = \Auth::user()->creatorId();
                        //         $policy->save();
                        //         foreach ($extractedHeads as $extractedHead) {
                        //             $policy_head = new ConcessionPolicyHead;
                        //             $policy_head->concession_id = $policy->id;
                        //             $policy_head->head_id = $extractedHead['head_id'];
                        //             $policy_head->percentage = $extractedHead['percentage'];
                        //             $policy_head->save();
                        //         }
                        //         $a[] = $policy->id;
                        //         // $reason = 'Concession Policy not found: ' . $all_data[2];
                        //         // $error_counter++;
                        //         // $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
                        //         // $count++;
                        //         // continue;
                        //     }

                        //     // Concession::where('student_id', $reg->id)->update([
                        //     //     'end_date' => '2024-01-01', // Update the end date to 2024-01-01
                        //     //     'active_status' => '0',
                        //     // ]);
                        //     $con = Concession::where('student_id', $reg->id)->wherein('concession_id', $a)->first();
                        //     // if no concession found, create new concession
                        //     if (!$con) {
                        //         $con = new Concession;
                        //         $con->student_id = $reg->id;
                        //         $con->class_id = $reg->class_id;
                        //         $con->concession_id = $a[0];
                        //         $con->concession_by = 'MOHSIN FIAZ';
                        //         $con->apply_date = '2016-01-01';
                        //         $con->start_date = '2016-01-01';
                        //         $con->end_date = '2024-01-01';
                        //         $con->remarks = 'Imported';
                        //         $con->status = 'Approved';
                        //         $con->owned_by = $reg->owned_by;
                        //         $con->created_by = \Auth::user()->creatorId();
                        //         $con->save();
                        //     }
                        //     if (!$con) {
                        //         $reason = 'Concession not found: '.$all_data[2];
                        //         $error_counter++;
                        //         $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
                        //         $count++;
                        //         // continue;
                        //     }
                        //     $challan->concession_id = $con->id;
                        //     $challan->save();
                        //     // dd($con);
                        // }
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                        \Log::info('Regular Challan Added Challan No :' . $challan->challanNo);
                    } else {
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'regular_challan_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Regular Challan(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e, $count, $all_data);
            \Log::error('Regular Challan Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    // private function RegularChallanImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/'.$filename);
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skip_data = [];
    //     $processed_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== false) {
    //             $count = 0;
    //             // index 1 => BRANCH name
    //             // index 3 => challan no
    //             // index 4 => roll no
    //             // index 5 => student name
    //             // index 6 => class name
    //             // index 7 => billing month
    //             // index 8 => class month fee
    //             // index 9 => admission head amount
    //             // index 10 => annual head amount
    //             // index 11 => tuition total amount
    //             // index 12 => tuition discounted amount
    //             // index 13 => tuition paid amount
    //             // index 14 => Computer haed amount
    //             // index 15 => SECURITY HEAD amount
    //             // index 16 => Security Adjustemnt
    //             // index 17 => readamission head amount
    //             // index 18 => monthly care head amount
    //             // index 19 => AC head amount
    //             // index 20 => extra care fee
    //             // index 21 => Monthly care head amount
    //             // index 22 => stationary head amount
    //             // index 23 => study pack amount
    //             // index 24 => late fee charges
    //             // index 25 => transport charges
    //             // index 26 => Arrears
    //             // index 27 => Net Receivable
    //             // index 28 => Discount
    //             // index 29 => Category
    //             // index 30 => RegId
    //             while (($all_data = fgetcsv($handle, 7000, ',')) !== false) {
    //                 if ($count > 0) {
    //                     if ($all_data[1] == '' || $all_data[3] == '' || $all_data[4] == '' || $all_data[5] == '' || $all_data[6] == '') {
    //                         continue;
    //                     }
    //                     $dates = $all_data[7];
    //                     $billingMonth = array_values(array_filter(explode(', ', $dates)))[0];
    //                     $otherMonthCount = count(array_values(array_filter(explode(', ', $dates))));
    //                     $record_status = 'Error';
    //                     $reason = '';
    //                     $all_data = array_map('trim', $all_data);
    //                     $branch = User::where('name', 'like', '%'.$all_data[1].'%')->first();
    //                     if (!$branch) {
    //                         $reason = 'Branch not found: '.$all_data[1];
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     // class
    //                     $class = Classes::where('name', 'like', '%'.$all_data[6].'%')->where('owned_by', $branch->id)->first();
    //                     if (!$class) {
    //                         $class = new Classes;
    //                         $class->name = $all_data[6];
    //                         $class->owned_by = $branch->id;
    //                         $class->created_by = auth()->user()->id;
    //                         $class->save();
    //                     }
    //                     if (!empty($all_data[30])) {
    //                         $regclean = preg_replace('/\s*\(.*?\)/', '', $all_data[30]);
    //                         $rollclean = preg_replace('/\s*\(.*?\)/', '', $all_data[4]);
    //                         $reg = StudentRegistration::where('reg_no', $regclean)->where('roll_no', $rollclean)->first();
    //                         if (!$reg) {
    //                             // dd('Student not found', $all_data, $count, 'rollno ' . $rollclean, 'regno ' . $regclean);
    //                             $reason = 'Student not found against roll no :'.$all_data[4].' and reg no: '.$all_data[30];
    //                             $error_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;

    //                             continue;
    //                         }
    //                     } else {
    //                         dd('registration number empty', $all_data, $count);
    //                     }
    //                     $enr = StudentEnrollments::where('enrollId', $reg->roll_no)->first();
    //                     if (!$enr) {
    //                         dd('enrollment not found', $all_data, $count);
    //                     }
    //                     // if (!$enr) {
    //                     //     $reg = StudentRegistration::where('reg_no', $all_data[30])
    //                     //         // ->where('stdname', 'like', '%' . $all_data[5] . '%')
    //                     //         ->first();
    //                     //     if (!$reg) {
    //                     //         $reason = 'Registration not found: ' . $all_data[30];
    //                     //         $error_counter++;
    //                     //         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //         $count++;
    //                     //         continue;
    //                     //     }
    //                     //     $enr = StudentEnrollments::where('regId', $reg->id)->first();
    //                     //     if ($enr) {
    //                     //         $enr->enrollId = $all_data[4];
    //                     //         $enr->save();
    //                     //         $reg->roll_no = $all_data[4];
    //                     //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
    //                     //             $reg->student_status = 'other';
    //                     //         }
    //                     //         $reg->save();
    //                     //     } else {
    //                     //         $enr = new StudentEnrollments();
    //                     //         $enr->adm_date = date('Y-m-d', strtotime($billingMonth));
    //                     //         $enr->class_id = $reg->class_id;
    //                     //         $enr->session_id = $reg->session_id;
    //                     //         $enr->adm_session = $reg->session_id;
    //                     //         $enr->adm_branch = $branch->id;
    //                     //         $enr->enrollId = $all_data[4];
    //                     //         $enr->regId = $reg->id;
    //                     //         $enr->active_status = 0;
    //                     //         $enr->owned_by = $branch->id;
    //                     //         $enr->created_by = \Auth::user()->creatorId();
    //                     //         $enr->save();
    //                     //         $reg->roll_no = $all_data[4];
    //                     //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
    //                     //             $reg->student_status = 'other';
    //                     //         }
    //                     //         $reg->save();
    //                     //     }
    //                     // } else {
    //                     //     $reg = StudentRegistration::where('roll_no', $all_data[4])->first();
    //                     //     if ($reg) {
    //                     //         $enr->regId = $reg->id;
    //                     //         $enr->save();
    //                     //         $reg->roll_no = $all_data[4];
    //                     //         $reg->reg_no = $all_data[30];
    //                     //         if ($reg->student_status != 'withdrawl' && $reg->student_status != 'Enrolled') {
    //                     //             $reg->student_status = 'other';
    //                     //         }
    //                     //         $reg->save();
    //                     //     }
    //                     // }

    //                     $timestamp = strtotime($billingMonth);
    //                     $year = date('Y', $timestamp);
    //                     $month = date('m', $timestamp);
    //                     $challan = Challans::where('student_id', $reg->id)
    //                         ->where('challan_type', 'Regular')
    //                         ->whereYear('challan_date', $year)
    //                         ->whereMonth('challan_date', $month)
    //                         ->exists();

    //                     if ($challan) {
    //                         if (strtolower($reg->stdname) != strtolower($all_data[5])) {
    //                             $reason = 'Challan already exists for another student with same roll no and reg: '.$all_data[30].' and name: '.$all_data[5];
    //                             $duplication_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;

    //                             continue;
    //                         } else {
    //                             dd('challan exist', $count, $all_data);
    //                         }
    //                         $reason = 'Challan already exists for student: '.$reg->id;
    //                         $duplication_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     if ($otherMonthCount > 1) {
    //                         $advance = Challans::where('student_id', $reg->id)
    //                             ->where('challan_type', 'Advance')
    //                             ->whereYear('challan_date', $year)
    //                             ->whereMonth('challan_date', $month)
    //                             ->exists();
    //                         if ($advance) {
    //                             // dd('advance exist');
    //                             $reason = 'Advance challan already exists for student: '.$reg->id;
    //                             $duplication_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;

    //                             continue;
    //                         }
    //                     }
    //                     $challan = new Challans;
    //                     $challan->student_id = $reg->id;
    //                     $challan->rollno = $reg->roll_no;
    //                     $challan->class_id = $class->id;
    //                     $challan->challanNo = $all_data[3];
    //                     $challan->challan_date = date('Y-m-d', strtotime($billingMonth));
    //                     $challan->fee_month = date('Y-m-d', strtotime($billingMonth));
    //                     $challan->challan_type = 'Regular';
    //                     $challan->concession_amount = $all_data[12];
    //                     $challan->total_amount = $all_data[27] + $all_data[28];
    //                     $challan->paid_amount = 0;
    //                     $challan->issue_date = date('Y-m-d', strtotime($billingMonth));
    //                     if ($otherMonthCount > 1) {
    //                         $months = explode(',', $all_data[7]);
    //                         $formattedMonths = [];
    //                         foreach ($months as $month) {
    //                             $timestamp = strtotime('01-'.trim($month));
    //                             if ($timestamp) {
    //                                 $formattedMonths[] = date('Y-m-d', $timestamp);
    //                             }
    //                         }
    //                         $challan->other_months = implode(',', $formattedMonths);
    //                         $challan->challan_type = 'Advance';
    //                     }
    //                     $challan->due_date = date('Y-m-d', strtotime($billingMonth.' +7 days'));
    //                     $challan->status = 'issued';
    //                     $challan->session_id = $reg->session_id;
    //                     $challan->owned_by = $branch->id;
    //                     $challan->created_by = \Auth::user()->creatorId();
    //                     $challan->save();
    //                     $challan->created_at = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                     $challan->updated_at = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                     $challan->save();
    //                     // dd($challan, $all_data);
    //                     $fee_head_mappings = [
    //                         ['index' => 9, 'type' => 'admission'],        // Admission fee
    //                         ['index' => 11, 'type' => 'tuition'],         // Tuition fee
    //                         ['index' => 18, 'type' => 'monthly care'],   // Monthly care
    //                         ['index' => 19, 'type' => 'ac'],
    //                         ['index' => 15, 'type' => 'security'],
    //                         ['index' => 16, 'type' => 'security adjustment'],
    //                         ['index' => 14, 'type' => 'computer'],            // AC head
    //                         ['index' => 17, 'type' => 'readmission'],            // AC head
    //                         ['index' => 20, 'type' => 'extra care'],     // Extra care
    //                         ['index' => 22, 'type' => 'stationary'],     // Stationary
    //                         ['index' => 23, 'type' => 'study pack'],
    //                         ['index' => 24, 'type' => 'late fee'],
    //                         ['index' => 25, 'type' => 'transport'],
    //                     ];
    //                     $total_fees = 0;
    //                     $item = [];
    //                     $itemIndex = 0;
    //                     $fee_head_mappings = array_filter(array_map(function ($mapping) use ($all_data, &$total_fees) {
    //                         $index = $mapping['index'];

    //                         if (isset($all_data[$index]) && is_numeric($all_data[$index]) && $all_data[$index] > 0) {
    //                             $amount = $all_data[$index];
    //                             $mapping['amount'] = $amount;
    //                             $total_fees += $amount;

    //                             return $mapping;
    //                         }

    //                         return null;
    //                     }, $fee_head_mappings));
    //                     $totalReceivedAmnt = 0;
    //                     foreach ($fee_head_mappings as $mapping) {
    //                         $index = $mapping['index'];
    //                         $type = $mapping['type'];
    //                         $fee_head = FeeHead::where('fee_head', 'LIKE', '%'.$type.'%')->first();
    //                         if ($fee_head) {
    //                             $head_amount = $mapping['amount'];
    //                             $concession = 0;
    //                             if ($type == 'tuition' && isset($all_data[12])) {
    //                                 // dd($all_data);
    //                                 $concession = $all_data[12];
    //                                 // $head_amount += $concession;
    //                             }
    //                             $totalReceivedAmnt += $head_amount;
    //                             // dd('out');
    //                             $challan_head = new ChallanHead;
    //                             $challan_head->challan_id = $challan->id;
    //                             $challan_head->head_id = $fee_head->id;
    //                             $challan_head->price = $head_amount;
    //                             $challan_head->concession = $concession;
    //                             $challan_head->save();
    //                             $challan_head->created_at = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                             $challan_head->updated_at = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                             $challan_head->save();
    //                             // add discount item as well
    //                             $item[$itemIndex]['prod_id'] = $challan_head->id;
    //                             $item[$itemIndex]['head'] = $fee_head->id;
    //                             $item[$itemIndex]['price'] = $head_amount ? $head_amount : 0;
    //                             $item[$itemIndex]['quantity'] = 1;
    //                             $item[$itemIndex]['concession'] = $concession;
    //                             $item[$itemIndex]['total'] = $head_amount;
    //                             $itemIndex++;
    //                         }
    //                     }
    //                     // if ($count == 176) {
    //                     //     dd($totalReceivedAmnt, $all_data,$item);
    //                     // }
    //                     // dd($totalReceivedAmnt);
    //                     $challan->total_amount = $totalReceivedAmnt;
    //                     $challan->save();
    //                     // dd($item, $challan,$enr);
    //                     $data['id'] = $challan->id;
    //                     $data['no'] = $challan->challanNo;
    //                     $data['date'] = $challan->challan_date;
    //                     $data['reference'] = $challan->student_id;
    //                     $data['category'] = 'Regular';
    //                     $data['user_id'] = $reg->roll_no;
    //                     $data['std_name'] = @$reg->stdname ?? '';
    //                     $data['branch_name'] = $branch->name;
    //                     $data['fee_month'] = date('M-y', strtotime($challan->fee_month));
    //                     $data['user_type'] = 'Student';
    //                     $data['owned_by'] = $challan->owned_by;
    //                     $data['created_by'] = $challan->created_by;
    //                     $data['created_at'] = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                     $data['updated_at'] = date('Y-m-d H:i:s', strtotime($billingMonth));
    //                     $data['items'] = $item;
    //                     $dataret = Utility::jrentry($data);
    //                     $challan->voucher_id = $dataret;
    //                     $challan->save();
    //                     if ($all_data[29] != 'Discount Policy' || $all_data[29] != '') {

    //                         $parsedConcessions = [];

    //                         $policyString = $all_data[29]; // Example: '15%T.FEE+15%ADM+50%SECURITY'

    //                         $headNameMappings = [
    //                             'T.FEE' => 'TUITION',
    //                             'TUT' => 'TUITION',
    //                             'TUITION' => 'TUITION',
    //                             // add more mappings as needed
    //                         ];

    //                         $policyParts = explode('+', $policyString);
    //                         $extractedHeads = [];

    //                         foreach ($policyParts as $part) {
    //                             if (preg_match('/^([\d.]+)%(.+)$/', trim($part), $matches)) {
    //                                 $percentage = (float) $matches[1];
    //                                 $fullHeadName = trim($matches[2]);
    //                                 $headName = preg_replace('/\([^)]+\)/', '', $fullHeadName);
    //                                 $headName = trim($headName);
    //                                 $cleanHeadName = strtoupper($headName);

    //                                 if (array_key_exists($cleanHeadName, $headNameMappings)) {
    //                                     $headName = $headNameMappings[$cleanHeadName];
    //                                 }

    //                                 $feeHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($headName).'%'])->first();

    //                                 if ($feeHead) {
    //                                     $extractedHeads[] = [
    //                                         'head_id' => $feeHead->id,
    //                                         'percentage' => $percentage,
    //                                     ];
    //                                 }
    //                             }
    //                         }

    //                         $matchingPolicies = ConcessionPolicy::with('policy_head') // Assuming relation to pivot table
    //                             ->get()
    //                             ->filter(function ($policy) use ($extractedHeads) {

    //                                 // Step 2.1: Compare total number of heads
    //                                 if (count($policy->policy_head) != count($extractedHeads)) {
    //                                     return false;
    //                                 }

    //                                 foreach ($extractedHeads as $extractedHead) {
    //                                     $match = $policy->policy_head->firstWhere('head_id', $extractedHead['head_id']);

    //                                     // Head not found or percentage does not match
    //                                     if (!$match || $match->percentage != $extractedHead['percentage']) {
    //                                         return false;
    //                                     }
    //                                 }

    //                                 return true; // All heads and percentages matched
    //                             });
    //                         $a = $matchingPolicies->pluck('id')->toArray();
    //                         // dd($a,$extractedHeads,$policyParts,$policyString);
    //                         // if no policies found, error throw error
    //                         if (count($a) == 0) {
    //                             // create new policy
    //                             $policy = new ConcessionPolicy;
    //                             $policy->order_no = 1;
    //                             $policy->title = $all_data[39];
    //                             $policy->description = $all_data[39];
    //                             $policy->owned_by = \Auth::user()->ownedId();
    //                             $policy->created_by = \Auth::user()->creatorId();
    //                             $policy->save();
    //                             foreach ($extractedHeads as $extractedHead) {
    //                                 $policy_head = new ConcessionPolicyHead;
    //                                 $policy_head->concession_id = $policy->id;
    //                                 $policy_head->head_id = $extractedHead['head_id'];
    //                                 $policy_head->percentage = $extractedHead['percentage'];
    //                                 $policy_head->save();
    //                             }
    //                             $a[] = $policy->id;
    //                             // $reason = 'Concession Policy not found: ' . $all_data[2];
    //                             // $error_counter++;
    //                             // $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
    //                             // $count++;
    //                             // continue;
    //                         }

    //                         // Concession::where('student_id', $reg->id)->update([
    //                         //     'end_date' => '2024-01-01', // Update the end date to 2024-01-01
    //                         //     'active_status' => '0',
    //                         // ]);
    //                         $con = Concession::where('student_id', $reg->id)->wherein('concession_id', $a)->first();
    //                         // if no concession found, create new concession
    //                         if (!$con) {
    //                             $con = new Concession;
    //                             $con->student_id = $reg->id;
    //                             $con->class_id = $reg->class_id;
    //                             $con->concession_id = $a[0];
    //                             $con->concession_by = 'MOHSIN FIAZ';
    //                             $con->apply_date = '2016-01-01';
    //                             $con->start_date = '2016-01-01';
    //                             $con->end_date = '2024-01-01';
    //                             $con->remarks = 'Imported';
    //                             $con->status = 'Approved';
    //                             $con->owned_by = $reg->owned_by;
    //                             $con->created_by = \Auth::user()->creatorId();
    //                             $con->save();
    //                         }
    //                         if (!$con) {
    //                             $reason = 'Concession not found: '.$all_data[2];
    //                             $error_counter++;
    //                             $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             // continue;
    //                         }
    //                         $challan->concession_id = $con->id;
    //                         $challan->save();
    //                         // dd($con);
    //                     }
    //                     $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                     $success_counter++;
    //                 } else {
    //                     $header = $all_data;
    //                     $header[] = 'Status';
    //                     $header[] = 'Reason';
    //                     $processed_records[] = $header;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //             DB::commit();
    //             if (!empty($skip_data)) {
    //                 $export_filename = 'regular_challan_import_errors_'.time().'.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/'.$export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');
    //                 fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
    //                 foreach ($skip_data as $row) {
    //                     fputcsv($error_file, $row);
    //                 }
    //                 fclose($error_file);

    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }

    //             return redirect()->back()->with('message', "{$success_counter} Regular Challan(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e, $count, $all_data);
    //         \Log::error('Regular Challan Import Error: '.$e->getMessage()."\n".$e->getTraceAsString());

    //         return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
    //     }
    // }

    private function SecurityChallanImport($file, $request)
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

                while (($all_data = fgetcsv($handle, 3500, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';

                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $reason = 'Empty enrollment ID';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Validate enrollment
                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $reason = 'Enrollment not found';
                            $error_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }

                        // Check for existing security challan (prevent duplicates)
                        $securityChallan = Challans::where('student_id', $enr->enrollId)
                            ->where('challan_type', 'Security')
                            ->first();
                        if ($securityChallan) {
                            $reason = 'Security challan already exists for student: ' . $enr->enrollId;
                            $duplication_counter++;
                            $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;

                            continue;
                        }
                        // Add to successful records
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Save the header row with additional columns
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'security_challan_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
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
            \Log::error('Security Challan Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // private function ReceiptsImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/'.$filename);

    //     // Initialize counters and tracking arrays
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skip_data = [];
    //     $processed_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== false) {
    //             $count = 0;

    //             while (($all_data = fgetcsv($handle, 30000, ',')) !== false) {
    //                 if ($count > 0) {
    //                     // if($count == 2){
    //                     //     dd($all_data);
    //                     // }
    //                     $record_status = 'Error';
    //                     $reason = '';
    //                     // dd($all_data);
    //                     // Check for empty data in first column
    //                     // 0 index => branch name
    //                     // 1 index => rpt. date
    //                     // 2 index => ch type
    //                     // 3 index => rollno
    //                     // 4 index => student name
    //                     // 5 index => class
    //                     // 6 index => challan no
    //                     // 7 index => biling period
    //                     // 8 index => fee subs
    //                     // 9 index => bank
    //                     // 10 index => D status
    //                     // 11 index => T.head
    //                     // 12 index => Reference
    //                     // 13 index => Amount
    //                     // 14 index => Over receipt
    //                     if (empty($all_data[6])) {
    //                         $reason = 'Empty Challan No';
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }

    //                     $chaln = Challans::with('heads')->where('challanNo', $all_data[6])->first();
    //                     if (!$chaln) {
    //                         $reason = 'Challan Not Exist';
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     // $branch = User::where('name', 'like', '%'.$all_data[0].'%')->first();
    //                     // if (!$branch) {
    //                     //     dd($all_data, 'branch');
    //                     //     $reason = 'Branch Not Found';
    //                     //     $error_counter++;
    //                     //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     $count++;
    //                     //     continue;
    //                     // }
    //                     $remainingAmount = $all_data[13];
    //                     $challanBeforePaid = $chaln->total_amount - ($chaln->paid_amount + $chaln->concession_amount);
    //                     if ($remainingAmount <= 0) {
    //                         $reason = 'Amount is 0';
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     // challan already paid.
    //                     if (($chaln->paid_amount + $chaln->concession_amount) == $chaln->total_amount) {
    //                         $reason = 'Challan Already Paid';
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     $itemIndex = 0;
    //                     $item = [];
    //                     // dd($chaln->heads);
    //                     foreach ($chaln->heads as $head) {
    //                         if (($head->price - ($head->concession ?? 0)) == $head->paid) {
    //                             continue;
    //                             }
    //                         $dueAmount = $head->price - ($head->concession ?? 0);
    //                         $alreadyPaid = $head->paid ?? 0;
    //                         $remainingDue = $dueAmount - $alreadyPaid;

    //                         if ($remainingDue <= 0) {
    //                             continue;
    //                         }

    //                         if ($remainingAmount >= $remainingDue) {
    //                             $head->paid += $remainingDue;
    //                             $chaln->paid_amount += $remainingDue;
    //                             $remainingAmount -= $remainingDue;
    //                         } else {
    //                             $head->paid += $remainingAmount;
    //                             $chaln->paid_amount += $remainingAmount;
    //                             $remainingAmount = 0;
    //                             break;
    //                         }
    //                         $head->save();
    //                         // dd($head,'m');
    //                         $item[$itemIndex]['head'] = $head->head_id;
    //                         $item[$itemIndex]['price'] = $head->paid;
    //                         $item[$itemIndex]['quantity'] = 1;
    //                         $item[$itemIndex]['concession'] = 0;
    //                         $item[$itemIndex]['total'] = $head->paid;
    //                         $itemIndex++;
    //                     }
    //                     // After distributing to all heads
    //                     if ($remainingAmount > 0) {
    //                         $latefeeHead = FeeHead::where('fee_head', 'like', '%Late Fee%')->first();
    //                         // dd($latefeeHead);
    //                         $latehead = new ChallanHead;
    //                         $latehead->challan_id = $chaln->id;
    //                         $latehead->head_id = $latefeeHead->id;
    //                         $latehead->price = $remainingAmount;
    //                         $latehead->concession = 0;
    //                         $latehead->paid = $remainingAmount;
    //                         $latehead->save();

    //                         $chaln->total_amount += $remainingAmount;
    //                         $chaln->paid_amount += $remainingAmount;
    //                         $chaln->save();
    //                         if ($latehead) {
    //                             // dd($count,$all_data,$chaln,$remainingAmount);
    //                             $account_name = ChartOfAccount::where('id', $latefeeHead->account_id)->first();
    //                             $feeHeads = FeeHead::where('id', $latefeeHead->id)->first();
    //                             $journalItem = new JournalItem;
    //                             $journalItem->journal = @$chaln->voucher_id;
    //                             $journalItem->account = @$feeHeads->account_id;
    //                             $journalItem->head = @$latefeeHead->id;
    //                             $journalItem->entry_id = @$latehead->id;
    //                             $journalItem->description = 'Income Account: Roll no '.$all_data[3].' Challan no '.$all_data[6].' - '.@$data['std_name'].' - '.date('M-y', strtotime($all_data[7])).' - '.@$all_data[0];
    //                             $journalItem->types = 'Challan';
    //                             $journalItem->credit = $remainingAmount;
    //                             $journalItem->debit = 0;
    //                             $journalItem->save();
    //                             $journalItem->created_at = @$chaln->created_at;
    //                             $journalItem->updated_at = @$chaln->updated_at;
    //                             $journalItem->save();
    //                             //  reciveable entry
    //                             $journalItem = new JournalItem;
    //                             $journalItem->journal = @$chaln->voucher_id;
    //                             $journalItem->account = @$feeHeads->receivable_account_id;
    //                             $journalItem->head = @$latefeeHead->id;
    //                             $journalItem->description = 'Account Receivable: Roll no '.$data['user_id'].' Challan no '.$data['no'].' - '.@$data['std_name'].' - '.@$data['fee_month'].' - '.@$data['branch_name'];
    //                             $journalItem->entry_id = @$latehead->id;
    //                             $journalItem->types = 'Challan';
    //                             $journalItem->credit = 0;
    //                             $journalItem->debit = $remainingAmount;
    //                             $journalItem->save();
    //                             $journalItem->created_at = @$chaln->created_at;
    //                             $journalItem->updated_at = @$chaln->updated_at;
    //                             $journalItem->save();
    //                         }
    //                         $item[$itemIndex]['head'] = $latehead->head_id;
    //                         $item[$itemIndex]['price'] = $remainingAmount;
    //                         $item[$itemIndex]['quantity'] = 1;
    //                         $item[$itemIndex]['concession'] = 0;
    //                         $item[$itemIndex]['total'] = $remainingAmount;
    //                         $itemIndex++;
    //                     }

    //                     // bank account on name basis
    //                     // clean bank name
    //                     $clean_title = trim(preg_replace('/\s*\(.*?\)/', '', $all_data[9]), " \t\n\r\0\x0B-");
    //                     $normalizedInput = ltrim($clean_title, '0');
    //                     $bankAccount = BankAccount::whereRaw("TRIM(LEADING '0' FROM account_number) = ?", [$normalizedInput])->first();

    //                     // dd($bankAccount);
    //                     if (!$bankAccount) {
    //                         dd($all_data, 'bank not found', $count);
    //                         $reason = 'Bank Account Not Found';
    //                         $error_counter++;
    //                         $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     $recipts = new StudentReceipt;
    //                     $recipts->recipt_date = date('Y-m-d', strtotime($all_data[1]));
    //                     $recipts->challan_id = $chaln->id;
    //                     $recipts->recipt_amount = $all_data[13];
    //                     $recipts->student_id = $chaln->student_id;
    //                     $recipts->challan_amount = $challanBeforePaid;
    //                     $recipts->late_amount = 0;
    //                     $recipts->arrears = 0;
    //                     $recipts->bank_id = $bankAccount->id;
    //                     $recipts->account_id = $bankAccount->chart_account_id;
    //                     $recipts->referance = $all_data[12];
    //                     $recipts->receive_type = $all_data[2];
    //                     // $recipts->received_by = $branch->id;
    //                     // $recipts->owned_by = $branch->id;
    //                     $recipts->received_by = $chaln->owned_by;
    //                     $recipts->owned_by = $chaln->owned_by;
    //                     $recipts->created_by = \Auth::user()->creatorId();
    //                     $recipts->save();
    //                     $recipts->created_at = date('Y-m-d H:i:s', strtotime($all_data[1]));
    //                     $recipts->updated_at = date('Y-m-d H:i:s', strtotime($all_data[1]));
    //                     $recipts->save();
    //                     // dd($recipts);
    //                     if ($recipts) {
    //                         if (($chaln->paid_amount + $chaln->concession_amount) >= $chaln->total_amount) {
    //                             $chaln->status = 'paid';
    //                         } else {
    //                             $chaln->status = 'partial';
    //                         }
    //                         $chaln->paid_date = date('Y-m-d', strtotime($all_data[1]));
    //                         $chaln->save();
    //                         $data['id'] = $chaln->id;
    //                         $data['no'] = $chaln->challanNo;
    //                         $data['prod_id'] = $recipts->id;
    //                         $data['bank_id'] = $bankAccount->id;
    //                         $data['branch_id'] = $chaln->owned_by;
    //                         $data['date'] = $chaln->paid_date;
    //                         $data['reference'] = $all_data[12];
    //                         $data['description'] = $chaln->description;
    //                         $data['user_id'] = $chaln->student_id;
    //                         $data['user_type'] = 'Student';
    //                         $data['amount'] = $all_data[13];
    //                         $data['category'] = $chaln->challan_type;
    //                         $data['owned_by'] = $chaln->owned_by;
    //                         $data['branch_name'] = @$chaln->student->branch->name ?? 'N/A';
    //                         $data['std_name'] = $all_data[4];
    //                         $data['recipt'] = $recipts->id;
    //                         $data['bank_name'] = $bankAccount->bank_name;
    //                         $data['created_by'] = \Auth::user()->creatorId();
    //                         $data['account_id'] = $bankAccount->chart_account_id;
    //                         $data['items'] = $item;
    //                         $data['total'] = $all_data[13];
    //                         // $dataret = Utility::brv_entry($data);
    //                         // dd($data);
    //                         if (ucwords($all_data[2]) == 'CD') {
    //                             $dataret = Utility::crv_entry($data);
    //                         } else {
    //                             $dataret = Utility::brv_entry($data);
    //                         }

    //                     }
    //                     $chaln->save();
    //                     // if ($receipt) {
    //                     //     $reason = 'Receipt already exists for this challan';
    //                     //     $duplication_counter++;
    //                     //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     $count++;
    //                     //     continue;
    //                     // }
    //                     // if (!$feeHead) {
    //                     //     $reason = 'Fee Head Not Found' . $all_data[11];
    //                     //     $error_counter++;
    //                     //     $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                     //     $count++;
    //                     //     continue;
    //                     // }
    //                     // if ($feeHead->id != $chaln->heads[0]->head_id) {

    //                     // }

    //                     // Add to successful records
    //                     $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                     $success_counter++;
    //                 } else {
    //                     // Save the header row with additional columns
    //                     $header = $all_data;
    //                     $header[] = 'Status';
    //                     $header[] = 'Reason';
    //                     $processed_records[] = $header;
    //                 }
    //                 $count++;
    //                 Session::put('counter', $success_counter);
    //             }
    //             fclose($handle);
    //             DB::commit();
    //             if (! empty($skip_data)) {
    //                 $export_filename = 'receipts_import_errors_'.time().'.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/'.$export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');
    //                 fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
    //                 foreach ($skip_data as $row) {
    //                     fputcsv($error_file, $row);
    //                 }
    //                 fclose($error_file);

    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }
    //             Session::forget('counter');

    //             return redirect()->back()->with('message', "{$success_counter} Receipt(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e, $count, $all_data);
    //         \Log::error('Receipts Import Error: '.$e->getMessage()."\n".$e->getTraceAsString());

    //         return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
    //     }
    // }
    private function ReceiptsImport($file, $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $filenameBase = pathinfo($filename, PATHINFO_FILENAME);
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $deadlock_data = [];
        $processed_records = [];
        $raw_header = [];
        $challanLateFeeCalculated = [];

        if (($handle = fopen($filepath, 'r')) === false) {
            return redirect()->back()->with('error', 'Unable to open file.');
        }

        $count = 0;

        while (($all_data = fgetcsv($handle, 30000, ',')) !== false) {

            /* ── HEADER ROW ──────────────────────────────────────────────── */
            if ($count === 0) {
                $raw_header = $all_data;
                $header = $all_data;
                $header[] = 'Status';
                $header[] = 'Reason';
                $processed_records[] = $header;
                $count++;
                continue;
            }

            if ($count % 100 === 0) {
                gc_collect_cycles();
            }

            $rowNumber = $count;
            $maxRetries = 3;
            $attempt = 0;
            $rowError = null;
            $isDeadlockRow = false;

            /* ── DEADLOCK-RETRYING TRANSACTION ───────────────────────────── */
            while ($attempt <= $maxRetries) {
                try {
                    DB::transaction(function () use ($all_data, $rowNumber, &$challanLateFeeCalculated, &$success_counter) {
                        \Log::info('Processing receipt import row', [
                            'row' => $rowNumber,
                            'challan_no' => $all_data[6] ?? 'N/A',
                            'amount' => $all_data[13] ?? 'N/A',
                        ]);

                        /* ═══════════════════════════════════════════════════
                         * BASIC VALIDATION
                         * ═══════════════════════════════════════════════════ */
                        if (empty(trim($all_data[6] ?? ''))) {
                            throw new \Exception('Empty Challan No');
                        }

                        $amount = (float) ($all_data[13] ?? 0);
                        if ($amount <= 0) {
                            throw new \Exception('Amount is 0 or negative');
                        }

                        /* ═══════════════════════════════════════════════════
                         * CHALLAN LOOKUP
                         * ═══════════════════════════════════════════════════ */
                        $chaln = Challans::where('challanNo', $all_data[6])
                            ->whereIn('challan_type', ['Regular', 'Advance'])
                            ->first();
                        // dd($chaln);
                        if (!$chaln) {
                            throw new \Exception('Challan Not Exist: ' . $all_data[6]);
                        }

                        $student = $chaln->student;
                        $isShifaStudent = ($student && $student->register_option == 2);

                        // ── CRITICAL FIX: always derive payment date from the CSV
                        //    row — never from $chaln->paid_date, which changes each
                        //    iteration and would collapse all payments onto one voucher.
                        $paymentDate = date('Y-m-d', strtotime($all_data[1]));

                        \Log::info('Challan loaded', [
                            'challan_no' => $chaln->challanNo,
                            'total_amount' => $chaln->total_amount,
                            'paid_amount' => $chaln->paid_amount,
                            'status' => $chaln->status,
                            'is_shifa' => $isShifaStudent,
                            'payment_date' => $paymentDate,
                        ]);

                        /* ═══════════════════════════════════════════════════
                         * FEE HEAD IDENTIFICATION
                         * ═══════════════════════════════════════════════════ */
                        $feeHeadName = trim($all_data[11] ?? '');
                        $isLateFee = (trim(strtolower($feeHeadName)) === 'late fee');
                        $isOverReceipt = (stripos($feeHeadName, 'over receipt') !== false
                            || stripos($feeHeadName, 'overreceipt') !== false);

                        $feeHead = FeeHead::where('fee_head', 'like', '%' . $feeHeadName . '%')->first();

                        if (!$feeHead && !$isLateFee && !$isOverReceipt) {
                            throw new \Exception('Fee Head Not Found: ' . $feeHeadName);
                        }

                        /* ═══════════════════════════════════════════════════
                         * REGULAR HEAD MUST ALREADY EXIST ON CHALLAN
                         * ═══════════════════════════════════════════════════ */
                        if (!$isLateFee && !$isOverReceipt && $feeHead) {
                            $existingHead = ChallanHead::where('challan_id', $chaln->id)
                                ->where('head_id', $feeHead->id)
                                ->first();
                            if (!$existingHead) {
                                throw new \Exception(
                                    'Fee head "' . $feeHeadName . '" does not exist on this challan. '
                                    . 'Cannot add new regular fee heads during receipt import.'
                                );
                            }
                        }

                        /* ═══════════════════════════════════════════════════
                         * LATE FEE CALCULATION
                         * ═══════════════════════════════════════════════════ */
                        $challanKey = $chaln->id;

                        if (!$isLateFee && !$isShifaStudent) {
                            if (!isset($challanLateFeeCalculated[$challanKey])) {
                                $this->calculateAndUpdateLateFee($chaln, $paymentDate);
                                $challanLateFeeCalculated[$challanKey] = true;
                                $chaln->refresh();
                                \Log::info('Late fee calculated (first encounter)', [
                                    'challan_id' => $challanKey,
                                    'date' => $paymentDate,
                                ]);
                            }
                        }

                        if ($isLateFee && !$isShifaStudent) {
                            $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                            if ($calculatedLateFee <= 0) {
                                throw new \Exception(
                                    'Late fee rejected: no calculated late fee for non-Shifa challan '
                                    . $chaln->challanNo
                                );
                            }
                        }

                        $chaln->refresh();

                        /* ═══════════════════════════════════════════════════
                         * FIND OR CREATE CHALLAN HEAD
                         * ═══════════════════════════════════════════════════ */
                        $challanHead = null;
                        $isNewHead = false;

                        if ($isLateFee) {

                            $latefeeHeadDef = FeeHead::where('fee_head', 'like', '%Late Fee%')->first();
                            if (!$latefeeHeadDef) {
                                throw new \Exception('Late Fee FeeHead definition not found in system');
                            }

                            $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                ->where('head_id', $latefeeHeadDef->id)
                                ->first();

                            if (!$challanHead) {
                                $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                                if ($amount > $calculatedLateFee + 0.01) {
                                    throw new \Exception(
                                        'Late fee payment (' . number_format($amount, 2) . ') exceeds '
                                        . 'calculated (' . number_format($calculatedLateFee, 2) . ') '
                                        . 'for challan ' . $chaln->challanNo
                                    );
                                }

                                $isNewHead = true;
                                $latehead = new ChallanHead;
                                $latehead->challan_id = $chaln->id;
                                $latehead->head_id = $latefeeHeadDef->id;
                                $latehead->price = $calculatedLateFee;
                                $latehead->concession = 0;
                                $latehead->paid = 0;
                                $latehead->save();

                                $chaln->total_amount = ($chaln->total_amount ?? 0) + $calculatedLateFee;
                                $chaln->save();

                                $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                    . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                    . ' - ' . ($all_data[0] ?? '');

                                // Income journal
                                $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $latefeeHeadDef->account_id)
                                    ->where('head', $latefeeHeadDef->id)
                                    ->where('entry_id', $latehead->id)
                                    ->where('types', 'Challan')
                                    ->where('debit', 0)->first();

                                if ($incomeJ) {
                                    $incomeJ->credit = ($incomeJ->credit ?? 0) + $calculatedLateFee;
                                    $incomeJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $latefeeHeadDef->account_id;
                                    $ji->head = $latefeeHeadDef->id;
                                    $ji->entry_id = $latehead->id;
                                    $ji->description = 'Income Account: ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = $calculatedLateFee;
                                    $ji->debit = 0;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                // Receivable journal
                                $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $latefeeHeadDef->receivable_account_id)
                                    ->where('head', $latefeeHeadDef->id)
                                    ->where('entry_id', $latehead->id)
                                    ->where('types', 'Challan')
                                    ->where('credit', 0)->first();

                                if ($receivableJ) {
                                    $receivableJ->debit = ($receivableJ->debit ?? 0) + $calculatedLateFee;
                                    $receivableJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $latefeeHeadDef->receivable_account_id;
                                    $ji->head = $latefeeHeadDef->id;
                                    $ji->entry_id = $latehead->id;
                                    $ji->description = 'Account Receivable: ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = 0;
                                    $ji->debit = $calculatedLateFee;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                $challanHead = $latehead;
                                $chaln->refresh();
                                $challanHead->refresh();
                            }

                        } elseif ($isOverReceipt) {

                            if (!$feeHead) {
                                throw new \Exception('Over-receipt Fee Head Not Found: ' . $feeHeadName);
                            }

                            $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                ->where('head_id', $feeHead->id)
                                ->first();

                            if (!$challanHead) {
                                $isNewHead = true;
                                $newHead = new ChallanHead;
                                $newHead->challan_id = $chaln->id;
                                $newHead->head_id = $feeHead->id;
                                $newHead->price = $amount;
                                $newHead->concession = 0;
                                $newHead->paid = 0;
                                $newHead->save();

                                $chaln->total_amount = ($chaln->total_amount ?? 0) + $amount;
                                $chaln->save();

                                $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                    . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                    . ' - ' . ($all_data[0] ?? '');

                                // Income journal
                                $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $feeHead->account_id)
                                    ->where('head', $feeHead->id)
                                    ->where('entry_id', $newHead->id)
                                    ->where('types', 'Challan')
                                    ->where('debit', 0)->first();

                                if ($incomeJ) {
                                    $incomeJ->credit = ($incomeJ->credit ?? 0) + $amount;
                                    $incomeJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $feeHead->account_id;
                                    $ji->head = $feeHead->id;
                                    $ji->entry_id = $newHead->id;
                                    $ji->description = 'Income Account: ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = $amount;
                                    $ji->debit = 0;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                // Receivable journal
                                $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $feeHead->receivable_account_id)
                                    ->where('head', $feeHead->id)
                                    ->where('entry_id', $newHead->id)
                                    ->where('types', 'Challan')
                                    ->where('credit', 0)->first();

                                if ($receivableJ) {
                                    $receivableJ->debit = ($receivableJ->debit ?? 0) + $amount;
                                    $receivableJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $feeHead->receivable_account_id;
                                    $ji->head = $feeHead->id;
                                    $ji->entry_id = $newHead->id;
                                    $ji->description = 'Account Receivable: ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = 0;
                                    $ji->debit = $amount;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                $challanHead = $newHead;
                                $chaln->refresh();
                                $challanHead->refresh();
                            }

                        } else {
                            $challanHead = ChallanHead::where('challan_id', $chaln->id)
                                ->where('head_id', $feeHead->id)
                                ->first();
                        }

                        if (!$challanHead) {
                            throw new \Exception('Could not locate or create challan head for: ' . $feeHeadName);
                        }

                        /* ═══════════════════════════════════════════════════
                         * VALIDATE LATE FEE PAYMENT (existing head)
                         * ═══════════════════════════════════════════════════ */
                        if ($isLateFee && !$isNewHead) {
                            $calculatedLateFee = $this->calculateLateFeeAmount($chaln, $paymentDate);
                            $totalLateFeePayment = ($challanHead->paid ?? 0) + $amount;

                            if ($totalLateFeePayment > $calculatedLateFee + 0.01) {
                                throw new \Exception(
                                    'Late fee payment exceeds calculated amount. '
                                    . 'Calculated: ' . number_format($calculatedLateFee, 2)
                                    . ', Already paid: ' . number_format($challanHead->paid ?? 0, 2)
                                    . ', Paying now: ' . number_format($amount, 2)
                                    . ', Total would be: ' . number_format($totalLateFeePayment, 2)
                                );
                            }
                        }

                        /* ═══════════════════════════════════════════════════
                         * OVER-RECEIPT EXPANSION
                         * ═══════════════════════════════════════════════════ */
                        if ($isOverReceipt && !$isNewHead) {
                            $remainingDue = ($challanHead->price ?? 0)
                                - (($challanHead->paid ?? 0) + ($challanHead->concession ?? 0));

                            if ($remainingDue < 0.01) {
                                \Log::info('Over-receipt: expanding existing head', [
                                    'challan_no' => $chaln->challanNo,
                                    'expand_by' => $amount,
                                ]);

                                $challanHead->price = ($challanHead->price ?? 0) + $amount;
                                $challanHead->save();

                                $chaln->total_amount = ($chaln->total_amount ?? 0) + $amount;
                                $chaln->save();

                                $description = 'Roll no ' . ($all_data[3] ?? '') . ' Challan no ' . ($all_data[6] ?? '')
                                    . ' - ' . ($all_data[4] ?? '') . ' - ' . ($all_data[7] ?? '')
                                    . ' - ' . ($all_data[0] ?? '');

                                // Income journal
                                $incomeJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $feeHead->account_id)
                                    ->where('head', $feeHead->id)
                                    ->where('entry_id', $challanHead->id)
                                    ->where('types', 'Challan')
                                    ->where('debit', 0)->first();

                                if ($incomeJ) {
                                    $incomeJ->credit = ($incomeJ->credit ?? 0) + $amount;
                                    $incomeJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $feeHead->account_id;
                                    $ji->head = $feeHead->id;
                                    $ji->entry_id = $challanHead->id;
                                    $ji->description = 'Income Account (Over-receipt Expansion): ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = $amount;
                                    $ji->debit = 0;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                // Receivable journal
                                $receivableJ = JournalItem::where('journal', $chaln->voucher_id)
                                    ->where('account', $feeHead->receivable_account_id)
                                    ->where('head', $feeHead->id)
                                    ->where('entry_id', $challanHead->id)
                                    ->where('types', 'Challan')
                                    ->where('credit', 0)->first();

                                if ($receivableJ) {
                                    $receivableJ->debit = ($receivableJ->debit ?? 0) + $amount;
                                    $receivableJ->save();
                                } else {
                                    $ji = new JournalItem;
                                    $ji->journal = $chaln->voucher_id;
                                    $ji->account = $feeHead->receivable_account_id;
                                    $ji->head = $feeHead->id;
                                    $ji->entry_id = $challanHead->id;
                                    $ji->description = 'Account Receivable (Over-receipt Expansion): ' . $description;
                                    $ji->types = 'Challan';
                                    $ji->user_id = $chaln->student_id;
                                    $ji->user_type = 'Student';
                                    $ji->credit = 0;
                                    $ji->debit = $amount;
                                    $ji->save();
                                    $ji->created_at = $chaln->created_at;
                                    $ji->updated_at = $chaln->updated_at;
                                    $ji->save();
                                }

                                $chaln->refresh();
                                $challanHead->refresh();
                            }
                        }

                        /* ═══════════════════════════════════════════════════
                         * VALIDATE PAYMENT VS REMAINING DUE (non-over-receipt)
                         * ═══════════════════════════════════════════════════ */
                        if (!$isOverReceipt && !$isNewHead) {
                            $remainingDue = ($challanHead->price ?? 0)
                                - (($challanHead->paid ?? 0) + ($challanHead->concession ?? 0));
                            // dd($remainingDue,$amount,$chaln,$challanHead);
                            if ($amount > $remainingDue + 0.01) {
                                throw new \Exception(
                                    'Payment (' . number_format($amount, 2) . ') exceeds remaining due '
                                    . '(' . number_format($remainingDue, 2) . ') for "' . $feeHeadName . '"'
                                );
                            }
                        }

                        /* ═══════════════════════════════════════════════════
                         * APPLY PAYMENT TO HEAD + CHALLAN
                         * ═══════════════════════════════════════════════════ */
                        $challanHead->paid = ($challanHead->paid ?? 0) + $amount;
                        $challanHead->save();

                        $chaln->paid_amount = ($chaln->paid_amount ?? 0) + $amount;
                        $chaln->paid_date = $paymentDate;

                        $totalPaidWithConcession = ($chaln->paid_amount ?? 0) + ($chaln->concession_amount ?? 0);
                        if ($totalPaidWithConcession >= ($chaln->total_amount ?? 0) - 0.01) {
                            $chaln->status = 'paid';
                        } elseif (($chaln->paid_amount ?? 0) > 0) {
                            $chaln->status = 'partial';
                        } else {
                            $chaln->status = 'unpaid';
                        }
                        $chaln->save();

                        \Log::info('Payment applied', [
                            'challan_no' => $chaln->challanNo,
                            'fee_head' => $feeHeadName,
                            'amount' => $amount,
                            'challan_status' => $chaln->status,
                            'payment_date' => $paymentDate,
                        ]);

                        /* ═══════════════════════════════════════════════════
                         * BANK ACCOUNT
                         * ═══════════════════════════════════════════════════ */
                        $clean_title = trim(preg_replace('/\s*\(.*?\)/', '', $all_data[9]));
                        $normalizedInput = ltrim($clean_title, '0');

                        $bankAccount = BankAccount::whereRaw(
                            "TRIM(LEADING '0' FROM account_number) = ?",
                            [$normalizedInput]
                        )->first();

                        if (!$bankAccount) {
                            throw new \Exception('Bank Account Not Found: ' . $all_data[9]);
                        }

                        /* ═══════════════════════════════════════════════════
                         * STUDENT RECEIPT
                         * ═══════════════════════════════════════════════════ */
                        $receipt = StudentReceipt::create([
                            'recipt_date' => $paymentDate,
                            'challan_id' => $chaln->id,
                            'student_id' => $chaln->student_id,
                            'recipt_amount' => $amount,
                            'challan_amount' => $chaln->total_amount,
                            'bank_id' => $bankAccount->id,
                            'account_id' => $bankAccount->chart_account_id,
                            'referance' => $all_data[12],
                            'receive_type' => $all_data[10],
                            'received_by' => $chaln->owned_by,
                            'owned_by' => $chaln->owned_by,
                            'created_by' => auth()->user()->creatorId(),
                        ]);

                        /* ═══════════════════════════════════════════════════
                         * VOUCHER ENTRY (BRV / CRV)
                         *
                         * FIX: pass $paymentDate (from CSV row) as 'date'.
                         *      The voucher functions key on this date, so:
                         *        - Nov-11 payment → Nov-11 voucher
                         *        - Nov-12 payment → separate Nov-12 voucher
                         *        - Two Nov-11 rows on same challan → same voucher
                         *          (items accumulated, bank entry summed)
                         * ═══════════════════════════════════════════════════ */
                        $voucherHeadId = $isLateFee
                            ? (FeeHead::where('fee_head', 'like', '%Late Fee%')->value('id'))
                            : ($feeHead->id ?? null);

                        if (!$voucherHeadId) {
                            throw new \Exception('Cannot resolve fee head ID for voucher entry');
                        }

                        $data = [
                            'id' => $chaln->id,
                            'challan_id' => $chaln->id,
                            'no' => $chaln->challanNo,
                            'prod_id' => $receipt->id,
                            'bank_id' => $bankAccount->id,
                            'branch_id' => $chaln->owned_by,
                            'date' => $paymentDate,           // ← FIXED: row-level date
                            'reference' => $all_data[12],
                            'description' => $chaln->description,
                            'user_id' => $chaln->student_id,
                            'branch_name' => optional(optional($chaln->student)->branch_name)->name ?? '',
                            'std_name' => $all_data[4],
                            'recipt' => $receipt->id,
                            'bank_name' => $bankAccount->bank_name,
                            'user_type' => 'Student',
                            'amount' => $amount,
                            'category' => $chaln->challan_type,
                            'owned_by' => $chaln->owned_by,
                            'created_by' => auth()->user()->creatorId(),
                            'account_id' => $bankAccount->chart_account_id,
                            'items' => [
                                [
                                    'head' => $voucherHeadId,
                                    'price' => $amount,
                                    'quantity' => 1,
                                    'concession' => 0,
                                    'total' => $amount,
                                ]
                            ],
                            'total' => $amount,
                        ];

                        ucwords($all_data[2]) === 'CD'
                            ? Utility::crv_entry($data)
                            : Utility::brv_entry($data);

                        $success_counter++;

                        \Log::info('Receipt import row successful', [
                            'row' => $rowNumber,
                            'challan_no' => $chaln->challanNo,
                            'receipt_id' => $receipt->id,
                            'payment_date' => $paymentDate,
                        ]);

                    }, 1);

                    $rowError = null;
                    break;

                } catch (\Illuminate\Database\QueryException $e) {

                    $isDeadlock = ($e->getCode() === '40001'
                        || ($e->errorInfo[1] ?? null) === 1213);

                    if ($isDeadlock && $attempt < $maxRetries) {
                        $attempt++;
                        \Log::warning('Deadlock on row, retrying', [
                            'row' => $rowNumber,
                            'attempt' => $attempt,
                        ]);
                        usleep(100000 * $attempt);
                        continue;
                    }

                    $rowError = $e;
                    $isDeadlockRow = $isDeadlock;
                    break;

                } catch (\Throwable $e) {
                    $rowError = $e;
                    break;
                }
            }

            /* ── HANDLE ROW OUTCOME ──────────────────────────────────────── */
            if ($rowError !== null) {
                $error_counter++;

                \Log::error('Receipt import row failed', [
                    'row' => $rowNumber,
                    'challan_no' => $all_data[6] ?? 'N/A',
                    'type' => $isDeadlockRow ? 'DEADLOCK' : 'ERROR',
                    'error' => $rowError->getMessage(),
                    'line' => $rowError->getLine(),
                ]);

                if ($isDeadlockRow) {
                    $deadlock_data[] = $all_data;
                } else {
                    $skip_data[] = array_merge($all_data, [
                        'Status' => 'Error',
                        'Reason' => $rowError->getMessage(),
                    ]);
                }
            }

            $count++;
        }

        fclose($handle);

        /* ════════════════════════════════════════════════════════════════════
         * BUILD OUTPUT FILES & DOWNLOAD
         * ════════════════════════════════════════════════════════════════════ */
        $hasErrors = !empty($skip_data);
        $hasDeadlocks = !empty($deadlock_data);

        $deadlockSavedPath = null;
        if ($hasDeadlocks) {
            $deadlockDir = public_path('assets/import/deadlock');
            if (!is_dir($deadlockDir)) {
                mkdir($deadlockDir, 0755, true);
            }
            $deadlockFilename = $filenameBase . '_deadlock.csv';
            $deadlockSavedPath = $deadlockDir . '/' . $deadlockFilename;

            $fp = fopen($deadlockSavedPath, 'w+');
            if (!empty($raw_header)) {
                fputcsv($fp, $raw_header);
            }
            foreach ($deadlock_data as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            \Log::info('Deadlock retry file saved', [
                'path' => $deadlockSavedPath,
                'row_count' => count($deadlock_data),
            ]);
        }

        $errorPath = null;
        if ($hasErrors) {
            $errorFile = 'receipts_import_errors_' . time() . '.csv';
            $errorPath = public_path('assets/import/csv_file/' . $errorFile);
            $fp = fopen($errorPath, 'w+');
            if (!empty($processed_records)) {
                fputcsv($fp, $processed_records[0]);
            }
            foreach ($skip_data as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        }

        \Log::info('Import completed', [
            'success' => $success_counter,
            'errors' => $error_counter,
            'deadlocks' => count($deadlock_data),
            'duplicates' => $duplication_counter,
            'deadlock_file' => $deadlockSavedPath ?? 'none',
        ]);

        if ($hasErrors) {
            $msg = $hasDeadlocks
                ? "{$success_counter} imported. {$error_counter} failed (see download). "
                . count($deadlock_data) . " deadlock row(s) saved to: assets/import/deadlock/{$filenameBase}_deadlock.csv — re-import that file to retry."
                : null;

            $download = response()->download($errorPath, basename($errorPath))->deleteFileAfterSend(true);
            if ($msg) {
                session()->flash('warning', $msg);
            }
            return $download;
        }

        if ($hasDeadlocks) {
            return redirect()->back()->with(
                'warning',
                "{$success_counter} imported. " . count($deadlock_data) . " deadlock row(s) saved to: "
                . "assets/import/deadlock/{$filenameBase}_deadlock.csv — re-import that file to retry."
            );
        }

        return redirect()->back()->with(
            'message',
            "{$success_counter} receipts imported successfully. {$error_counter} failed. {$duplication_counter} duplicates skipped."
        );
    }


    /**
     * Calculate the late fee amount for a challan based on payment date
     * (Helper method to calculate late fee without updating the challan)
     */
    private function calculateLateFeeAmount($challan, $paymentDate)
    {
        // Get the due date from challan
        $dueDate = \Carbon\Carbon::parse($challan->due_date);
        $today = \Carbon\Carbon::parse($paymentDate);

        // Check if due date has passed
        if ($today->lte($dueDate)) {
            return 0; // Not overdue yet
        }

        // Calculate days overdue (maximum 10 days)
        $daysOverdue = $today->diffInDays($dueDate);
        $daysOverdue = min($daysOverdue, 10); // Cap at 10 days

        // Calculate late fee amount
        $lateFeePerDay = 120;
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;

        return $lateFeeAmount;
    }

    private function calculateAndUpdateLateFee($challan, $paymentDate)
    {
        \Log::info('=== START calculateAndUpdateLateFee ===', [
            'challan_id' => $challan->id,
            'status' => $challan->status,
            'due_date' => $challan->due_date,
            'paid_date' => $challan->paid_date ?? null,
        ]);

        $status = strtolower($challan->status ?? '');

        // ❌ Skip if fully paid
        if ($status === 'paid') {
            \Log::info('Challan already fully paid. Skipping.');
            return;
        }

        // ✅ Get Late Fee Head
        $lateFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();
        if (!$lateFeeHead) {
            \Log::error('Late Fee Head not found.');
            return;
        }

        // ✅ Check existing late fee
        $existingLateFee = ChallanHead::where('challan_id', $challan->id)
            ->where('head_id', $lateFeeHead->id)
            ->first();

        /*
        =====================================================
        SPECIAL CASE:
        Partial + paid_date exists + NO late fee head
        =====================================================
        */
        if (
            in_array($status, ['partial', 'partial paid']) &&
            !empty($challan->paid_date) &&
            !$existingLateFee
        ) {
            \Log::info('Partial paid with paid_date found. Using paid_date for late fee calculation.');

            $calculationDate = \Carbon\Carbon::parse($challan->paid_date);
        } else {
            // Normal case → use passed payment date
            $calculationDate = \Carbon\Carbon::parse($paymentDate);
        }

        $dueDate = \Carbon\Carbon::parse($challan->due_date);

        // Not overdue
        if ($calculationDate->lte($dueDate)) {
            \Log::info('Not overdue.');
            return;
        }

        // Calculate overdue days (max 10)
        $daysOverdue = min($calculationDate->diffInDays($dueDate), 10);
        $lateFeePerDay = 120;
        $lateFeeAmount = $daysOverdue * $lateFeePerDay;

        if ($lateFeeAmount <= 0) {
            return;
        }

        \Log::info('Late fee calculated', [
            'days_overdue' => $daysOverdue,
            'late_fee' => $lateFeeAmount
        ]);

        /*
        =====================================================
        UPDATE EXISTING LATE FEE
        =====================================================
        */
        if ($existingLateFee) {

            $oldPrice = $existingLateFee->price ?? 0;

            $challan->total_amount -= $oldPrice;

            $existingLateFee->update([
                'price' => $lateFeeAmount,
                'updated_at' => now(),
            ]);

            JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('debit', 0)
                ->update([
                    'credit' => $lateFeeAmount,
                    'updated_at' => now(),
                ]);

            JournalItem::where('journal', $challan->voucher_id)
                ->where('head', $lateFeeHead->id)
                ->where('credit', 0)
                ->update([
                    'debit' => $lateFeeAmount,
                    'updated_at' => now(),
                ]);

            $challan->total_amount += $lateFeeAmount;
            $challan->save();

            \Log::info('Existing late fee updated.');
            return;
        }

        /*
        =====================================================
        CREATE NEW LATE FEE
        (Covers unpaid OR partial without late fee)
        =====================================================
        */

        \Log::info('Creating new late fee.');

        $latehead = ChallanHead::create([
            'challan_id' => $challan->id,
            'head_id' => $lateFeeHead->id,
            'price' => $lateFeeAmount,
            'concession' => 0,
            'paid' => 0,
        ]);

        // Income Entry
        JournalItem::create([
            'journal' => $challan->voucher_id,
            'account' => $lateFeeHead->account_id,
            'head' => $lateFeeHead->id,
            'entry_id' => $latehead->id,
            'user_id' => $challan->student_id,
            'user_type' => 'Student',
            'types' => 'Challan',
            'credit' => $lateFeeAmount,
            'debit' => 0,
            'description' => 'Late Fee Income - Challan No ' . $challan->challanNo,
        ]);

        // Receivable Entry
        JournalItem::create([
            'journal' => $challan->voucher_id,
            'account' => $lateFeeHead->receivable_account_id,
            'head' => $lateFeeHead->id,
            'entry_id' => $latehead->id,
            'user_id' => $challan->student_id,
            'user_type' => 'Student',
            'types' => 'Challan',
            'credit' => 0,
            'debit' => $lateFeeAmount,
            'description' => 'Late Fee Receivable - Challan No ' . $challan->challanNo,
        ]);

        $challan->total_amount += $lateFeeAmount;
        $challan->save();

        \Log::info('Late fee created successfully.');

        \Log::info('=== END calculateAndUpdateLateFee ===');
    }
    public function challanNo()
    {
        $latest = Challans::orderByRaw('CAST(challanNo AS UNSIGNED) DESC')
            ->first();

        if (! $latest || ! $latest->challanNo) {
            return 1;
        }

        return (int) $latest->challanNo + 1;
    }

    private function ConcessionImport($file, $request)
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
                while (($all_data = fgetcsv($handle, 500, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';
                        $concessionsheads = explode('+', $all_data[1]);
                        $parsedConcessions = [];
                        foreach ($concessionsheads as $concession) {
                            if (preg_match('/^(\d+)%(.+)$/', $concession, $matches)) {
                                $percentage = (int) $matches[1];
                                $fullHeadName = trim($matches[2]);
                                $headName = preg_replace('/\([^)]+\)/', '', $fullHeadName);
                                $headName = trim($headName);
                                $feeHead = FeeHead::where(DB::raw('LOWER(fee_head)'), 'LIKE', strtolower($headName) . '%')->first();
                                $parsedConcessions[] = [
                                    'percentage' => $percentage,
                                    'head_name' => $headName,
                                    'head' => @$feeHead,
                                ];
                            }
                        }
                        // dd($parsedConcessions);
                        $concession_policy = new ConcessionPolicy;
                        $concession_policy->order_no = $all_data[0];
                        $concession_policy->title = $all_data[1];
                        $concession_policy->description = $all_data[1];
                        $concession_policy->owned_by = \Auth::user()->ownedId();
                        $concession_policy->created_by = \Auth::user()->creatorId();
                        $concession_policy->save();
                        foreach ($parsedConcessions as $concession) {
                            if (!$concession['head'] || $concession['head'] == null) {
                                $reason = 'Fee Head Not Found: ' . $concession['head_name'];
                                $error_counter++;
                                $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;

                                continue;
                            }
                            $concession_policy_head = new ConcessionPolicyHead;
                            $concession_policy_head->concession_id = $concession_policy->id;
                            $concession_policy_head->head_id = $concession['head']->id;
                            $concession_policy_head->percentage = $concession['percentage'];
                            $concession_policy_head->save();
                        }
                    }
                    $count++;
                }
                fclose($handle);
                DB::commit();
                if (!empty($skip_data)) {
                    $export_filename = 'concession_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Concession(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::error('Concession Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    //    private function ConcessionListImport($file, $request)
    //     {
    //         set_time_limit(0);
    //         $file = $request->file('excel_file');
    //         $filename = $file->getClientOriginalName();
    //         $file->move(public_path('assets/import/csv_file/'), $filename);
    //         $filepath = public_path('assets/import/csv_file/' . $filename);

    //         $success_counter = 0;
    //         $error_counter = 0;
    //         $duplication_counter = 0;
    //         $skip_data = [];
    //         $processed_records = [];
    //         DB::beginTransaction();
    //         try {
    //             if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //                 $count = 0;
    //                 while (($all_data = fgetcsv($handle, 500, ",")) !== FALSE) {
    //                     if ($count > 0) {
    //                         $record_status = 'Error';
    //                         $reason = '';
    //                         //12
    //                         $active_status = strtolower($all_data[13]) == 'active' ? 1 : 0;
    //                         // Check for empty data in first column
    //                         if (empty($all_data[8])) {
    //                             $reason = 'Concession Not Found';
    //                             $error_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }
    //                         $order_no = explode(' - ', $all_data[8]);
    //                         if (!$order_no) {
    //                             $reason = 'Order No Not Found: ' . $all_data[8];
    //                             $duplication_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }
    //                         // dd($order_no);
    //                         $concession_policy = ConcessionPolicy::with('policy_head')->where('order_no', $order_no[0])->first();
    //                         if (!$concession_policy) {
    //                             $reason = 'Concession Policy Not Found: ' . $all_data[8];
    //                             $error_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }
    //                         $stdconcession = Concession::where('student_id', $all_data[3])->where('concession_id', $concession_policy->id)->where('active_status', $active_status)->first();
    //                         if ($stdconcession) {
    //                             $reason = 'Concession already exists for student: ' . $all_data[1];
    //                             $duplication_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }

    //                         if (empty($all_data[3])) {  // Changed from $all_data[3] == '' to empty() for better null/empty checking
    //                             // Registration student lookup
    //                             $student = StudentRegistration::where('reg_no', $all_data[2])->first();  // Changed from [3] to [2] as it seems you're checking reg_no
    //                             if (!$student) {
    //                                 $reason = 'Student Not Found reg no: ' . $all_data[2];
    //                                 $error_counter++;
    //                                 $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                                 $count++;
    //                                 continue;
    //                             }
    //                             $student_type = 'Registration Student';
    //                         } else {
    //                             // Enrolled student lookup
    //                             $a = StudentEnrollments::where('enrollId', $all_data[3])->first();
    //                             if (!$a) {
    //                                 $reason = 'Student Not Found with roll no: ' . $all_data[3];
    //                                 $error_counter++;
    //                                 $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                                 $count++;
    //                                 continue;
    //                             }else{
    //                                 $student = StudentRegistration::where('id', $a->regId)->first();
    //                             }
    //                             $student_type = 'Regular Student';
    //                         }
    //                          $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[0]);
    //                         $branchfrom = User::where('name', 'like', '%' . $clean_title . '%')->first();
    //                         if (!$branchfrom) {
    //                             dd($all_data, 'branchfrom', $clean_title);
    //                             $reason = 'From branch not found: ' . $all_data[0];
    //                             $error_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }
    //                         $cls = Classes::where('name', $all_data[4])->where('owned_by', $branchfrom->id)->first();
    //                         if (!$cls) {
    //                             $reason = 'Class Not Found: ' . $all_data[10];
    //                             $error_counter++;
    //                             $skip_data[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
    //                             $count++;
    //                             continue;
    //                         }
    //                         $concession = new Concession();
    //                         $concession->student_id = $student->id;
    //                         $concession->class_id = $cls->id;
    //                         $concession->concession_id = $concession_policy->id;
    //                         $concession->concession_by = $all_data[11];
    //                         $concession->apply_date = date('Y-m-d', strtotime($all_data[6]));
    //                         $concession->start_date = date('Y-m-d', strtotime($all_data[6]));
    //                         $concession->end_date = date('Y-m-d', strtotime($all_data[9]));
    //                         $concession->status = $all_data[12];
    //                         $concession->remarks = $student_type;
    //                         $concession->active_status = $active_status;
    //                         $concession->created_by = \Auth::user()->creatorId();
    //                         $concession->owned_by = $student->owned_by;
    //                         $concession->save();
    //                         $concession->created_at = date('Y-m-d', strtotime($all_data[6]));
    //                         $concession->updated_at = date('Y-m-d', strtotime($all_data[6]));
    //                         $concession->save();
    //                         $success_counter++;
    //                     }
    //                     $count++;
    //                 }
    //                 fclose($handle);
    //                 DB::commit();
    //                 if (!empty($skip_data)) {
    //                     $export_filename = 'concession_import_errors_' . time() . '.csv';
    //                     $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
    //                     $error_file = fopen($error_filepath, 'w+');
    //                     fputcsv($error_file, ['Enrollment ID', 'Registration No', 'Student Name', 'Status', 'Reason']);
    //                     foreach ($skip_data as $row) {
    //                         fputcsv($error_file, $row);
    //                     }
    //                     fclose($error_file);
    //                     return response()->download($error_filepath)->deleteFileAfterSend(true);
    //                 }
    //                 return redirect()->back()->with('message', "{$success_counter} Concession(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //             }
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             dd($e);
    //             \Log::error("Concession Import Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    //             return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //         }
    //     }

    private function ChallanConcession($file, $request)
    {
        set_time_limit(0);

        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];

        // simple in-memory caches to cut DB hits
        $policyCache = [];   // signature => policy_id
        $headCache = [];   // UPPER(headname) => FeeHead model

        if (($handle = fopen($filepath, 'r')) !== false) {
            $count = 0;

            while (($all_data = fgetcsv($handle, 7000, ',')) !== false) {
                if ($count == 0) {
                    $count++;

                    continue;
                } // header
                $currentRow = $count;
                $count++;

                // required columns check
                if ($all_data[1] === '' || $all_data[3] === '' || $all_data[4] === '' || $all_data[5] === '' || $all_data[6] === '') {
                    $error_counter++;
                    $skip_data[] = ['row' => $currentRow, 'reason' => 'Missing required columns', 'data' => $all_data];

                    continue;
                }

                // skip “no policy” markers
                if (in_array(strtolower(trim($all_data[29])), ['', 'discount policy', 'wrong discount'], true)) {
                    $skip_data[] = ['row' => $currentRow, 'reason' => 'No discount policy', 'data' => $all_data];

                    continue;
                }

                // clean reg/roll
                $regclean = preg_replace('/\D+/', '', $all_data[30]);
                $rollclean = preg_replace('/\D+/', '', $all_data[4]);

                $reg = StudentRegistration::where('reg_no', $regclean)
                    ->where('roll_no', $rollclean)->first();
                if (!$reg) {
                    $error_counter++;
                    $skip_data[] = ['row' => $currentRow, 'reason' => "Student not found (Roll:$rollclean Reg:$regclean)", 'data' => $all_data];

                    continue;
                }

                $challan = Challans::where('challanNo', $all_data[3])
                    ->where('rollno', $reg->roll_no)->first();
                if (!$challan) {
                    $error_counter++;
                    $skip_data[] = ['row' => $currentRow, 'reason' => 'Challan not found', 'data' => $all_data];

                    continue;
                }

                // --- Build policy composition from CSV ---
                $policyString = $all_data[29]; // e.g. '15%T.FEE+15%ADM+50%SECURITY'

                $headNameMappings = [
                    'T.FEE' => 'TUITION',
                    'TUT' => 'TUITION',
                    'TUITION' => 'TUITION',
                ];

                $policyParts = explode('+', $policyString);
                $extracted = []; // [['head_id'=>..,'percentage'=>..], ...]

                foreach ($policyParts as $part) {
                    if (!preg_match('/^([\d.]+)%(.+)$/', trim($part), $m)) {
                        continue;
                    }
                    $percentage = (float) $m[1];
                    $fullHeadName = strtoupper(trim(preg_replace('/\([^)]+\)/', '', $m[2])));

                    if (isset($headNameMappings[$fullHeadName])) {
                        $fullHeadName = $headNameMappings[$fullHeadName];
                    }

                    // fee head lookup with cache, prefer exact match, then LIKE fallback
                    if (!isset($headCache[$fullHeadName])) {
                        $feeHead = FeeHead::whereRaw('UPPER(fee_head) = ?', [$fullHeadName])->first();
                        if (!$feeHead) {
                            $feeHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($fullHeadName) . '%'])->first();
                        }
                        if (!$feeHead) {
                            continue;
                        }
                        $headCache[$fullHeadName] = $feeHead;
                    } else {
                        $feeHead = $headCache[$fullHeadName];
                    }

                    $extracted[] = ['head_id' => $feeHead->id, 'percentage' => $percentage];
                }

                if (empty($extracted)) {
                    $error_counter++;
                    $skip_data[] = ['row' => $currentRow, 'reason' => 'Unable to parse discount heads', 'data' => $all_data];

                    continue;
                }

                // --- Canonical signature: same heads+% => same signature ---
                // sort by head_id to make order irrelevant
                usort($extracted, fn($a, $b) => $a['head_id'] <=> $b['head_id']);
                $signature = implode('|', array_map(fn($e) => $e['head_id'] . ':' . $e['percentage'], $extracted));

                // --- Find or create ConcessionPolicy by signature (NOT title) ---
                if (!isset($policyCache[$signature])) {
                    // Try find existing by signature (needs an indexed/unique column 'signature')
                    $existingPolicy = ConcessionPolicy::where('signature', $signature)->first();

                    if (!$existingPolicy) {
                        // Fallback: compute by composition (for 1st run if 'signature' column not yet added)
                        $existingPolicy = ConcessionPolicy::with('policy_head')->get()->first(function ($p) use ($extracted) {
                            if (count($p->policy_head) !== count($extracted)) {
                                return false;
                            }
                            foreach ($extracted as $e) {
                                $ph = $p->policy_head->firstWhere('head_id', $e['head_id']);
                                if (!$ph || (float) $ph->percentage !== (float) $e['percentage']) {
                                    return false;
                                }
                            }

                            return true;
                        });
                    }

                    if (!$existingPolicy) {
                        $policy = new ConcessionPolicy;
                        $policy->order_no = 1;
                        $policy->title = $policyString;     // keep original text (display only)
                        $policy->description = $policyString;
                        $policy->signature = $signature;        // <<< NEW: add this column in DB
                        $policy->owned_by = \Auth::user()->ownedId();
                        $policy->created_by = \Auth::user()->creatorId();
                        $policy->save();

                        foreach ($extracted as $e) {
                            $policy_head = new ConcessionPolicyHead;
                            $policy_head->concession_id = $policy->id;
                            $policy_head->head_id = $e['head_id'];
                            $policy_head->percentage = $e['percentage'];
                            $policy_head->save();
                        }

                        $policyCache[$signature] = $policy->id;
                    } else {
                        // ensure signature is saved on older rows if column exists
                        if (empty($existingPolicy->signature)) {
                            $existingPolicy->signature = $signature;
                            $existingPolicy->save();
                        }
                        $policyCache[$signature] = $existingPolicy->id;
                    }
                }

                $policyId = $policyCache[$signature];

                // --- Assign concession to student if not already assigned THIS policy ---
                $con = Concession::where('student_id', $reg->id)
                    ->where('concession_id', $policyId)
                    ->first();

                if (!$con) {
                    $con = new Concession;
                    $con->student_id = $reg->id;
                    $con->class_id = $reg->class_id;
                    $con->concession_id = $policyId;
                    $con->concession_by = 'MOHSIN FIAZ';
                    $con->apply_date = '2016-01-01';
                    $con->start_date = '2016-01-01';
                    $con->end_date = '2024-01-01';
                    $con->remarks = 'Imported';
                    $con->status = 'Approved';
                    $con->owned_by = $reg->owned_by;
                    $con->created_by = \Auth::user()->creatorId();
                    $con->save();

                    $success_counter++;
                } else {
                    $duplication_counter++;
                }

                // link to challan (idempotent)
                if ($challan->concession_id !== $policyId) {
                    $challan->concession_id = $policyId;
                    $challan->save();
                }
            }

            fclose($handle);
        }

        return dd([
            'success' => $success_counter,
            'errors' => $error_counter,
            'duplicates' => $duplication_counter,
            'skipped' => count($skip_data),
            'skippedData' => $skip_data,
        ]);
    }

    //old logic corect
    // private function ConcessionListImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/'.$filename);
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skip_data = [];
    //     $processed_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== false) {
    //             $count = 0;
    //             // index 0 => Branch
    //             // index 1 => student name
    //             // index 2 => roll no
    //             // index 3 => class name
    //             // index 4 => section name
    //             // index 6 => d.o.j

    //             //Admission  Fee
    //             // index 7 => Amount
    //             // index 8 => disc %
    //             // index 9 => Final Amount
    //             // index 10 check status (1,0)

    //             //Annual  Fee
    //             // index 11 => Amount
    //             // index 12 => disc %
    //             // index 13 => Final Amount
    //             // index 14 check status (1,0)

    //             //Security  Fee
    //             // index 15 => Amount
    //             // index 16 => disc %
    //             // index 17 => Final Amount
    //             // index 18 check status (1,0)

    //             //Tuition  Fee
    //             // index 19 => Amount
    //             // index 20 => disc %
    //             // index 21 => Final Amount
    //             // index 22 check status (1,0)

    //             //Ac  Fee
    //             // index 23 => Amount
    //             // index 24 => disc %
    //             // index 25 => Final Amount
    //             // index 26 check status (1,0)

    //             //Extra care  Fee
    //             // index 27 => Amount
    //             // index 28 => disc %
    //             // index 29 => Final Amount
    //             // index 30 check status (1,0)

    //             //Monthly care  Fee
    //             // index 31 => Amount
    //             // index 32 => disc %
    //             // index 33 => Final Amount
    //             // index 34 check status (1,0)

    //             //Transport Charges
    //             // index 35 => Amount
    //             // index 36 => disc %
    //             // index 37 => Final Amount
    //             // index 38 check status (1,0)

    //             //Discount Policy
    //             // index 39 => Discount Policy

    //             while (($all_data = fgetcsv($handle, 7000, ',')) !== false) {
    //                 if ($count > 0) {

    //                     // $dates = $all_data[4];
    //                     // $month_count = count(array_values(array_filter(explode(', ', $dates))));
    //                     // $billingMonths = array_values(array_filter(explode(', ', $dates)));
    //                     $record_status = 'Error';
    //                     $reason = '';
    //                     // dd($all_data);
    //                     $all_data = array_map('trim', $all_data);
    //                     $enr = StudentEnrollments::where('enrollId', $all_data[2])->first();
    //                     if (!$enr) {
    //                         $reason = 'Enrollment not found: '.$all_data[2];
    //                         dd($all_data, $reason);
    //                         $error_counter++;
    //                         $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2]], ['Status' => $record_status, 'Reason' => $reason]);
    //                         $count++;

    //                         continue;
    //                     }
    //                     $student = StudentRegistration::where('id', $enr->regId)->first();
    //                     if ($student) {
    //                         if ($all_data[39] != 'Discount Policy') {

    //                             $parsedConcessions = [];

    //                             $policyString = $all_data[39]; // Example: '15%T.FEE+15%ADM+50%SECURITY'

    //                             $headNameMappings = [
    //                                 'T.FEE' => 'TUITION',
    //                                 'TUT' => 'TUITION',
    //                                 'TUITION' => 'TUITION',
    //                                 // add more mappings as needed
    //                             ];

    //                             $policyParts = explode('+', $policyString);
    //                             $extractedHeads = [];

    //                             foreach ($policyParts as $part) {
    //                                 if (preg_match('/^([\d.]+)%(.+)$/', trim($part), $matches)) {
    //                                     $percentage = (float) $matches[1];
    //                                     $fullHeadName = trim($matches[2]);
    //                                     $headName = preg_replace('/\([^)]+\)/', '', $fullHeadName);
    //                                     $headName = trim($headName);
    //                                     $cleanHeadName = strtoupper($headName);

    //                                     if (array_key_exists($cleanHeadName, $headNameMappings)) {
    //                                         $headName = $headNameMappings[$cleanHeadName];
    //                                     }

    //                                     $feeHead = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($headName).'%'])->first();

    //                                     if ($feeHead) {
    //                                         $extractedHeads[] = [
    //                                             'head_id' => $feeHead->id,
    //                                             'percentage' => $percentage,
    //                                         ];
    //                                     }
    //                                 }
    //                             }

    //                             $matchingPolicies = ConcessionPolicy::with('policy_head') // Assuming relation to pivot table
    //                                 ->get()
    //                                 ->filter(function ($policy) use ($extractedHeads) {

    //                                     // Step 2.1: Compare total number of heads
    //                                     if (count($policy->policy_head) !== count($extractedHeads)) {
    //                                         return false;
    //                                     }

    //                                     foreach ($extractedHeads as $extractedHead) {
    //                                         $match = $policy->policy_head->firstWhere('head_id', $extractedHead['head_id']);

    //                                         // Head not found or percentage does not match
    //                                         if (!$match || $match->percentage != $extractedHead['percentage']) {
    //                                             return false;
    //                                         }
    //                                     }

    //                                     return true; // All heads and percentages matched
    //                                 });
    //                             $a = $matchingPolicies->pluck('id')->toArray();
    //                             // dd($a,$extractedHeads,$policyParts,$policyString);
    //                             // if no policies found, error throw error
    //                             if (count($a) == 0) {
    //                                 // create new policy
    //                                 $policy = new ConcessionPolicy;
    //                                 $policy->order_no = 1;
    //                                 $policy->title = $all_data[39];
    //                                 $policy->description = $all_data[39];
    //                                 $policy->owned_by = \Auth::user()->ownedId();
    //                                 $policy->created_by = \Auth::user()->creatorId();
    //                                 $policy->save();
    //                                 foreach ($extractedHeads as $extractedHead) {
    //                                     $policy_head = new ConcessionPolicyHead;
    //                                     $policy_head->concession_id = $policy->id;
    //                                     $policy_head->head_id = $extractedHead['head_id'];
    //                                     $policy_head->percentage = $extractedHead['percentage'];
    //                                     $policy_head->save();
    //                                 }
    //                                 $a[] = $policy->id;
    //                                 // $reason = 'Concession Policy not found: ' . $all_data[2];
    //                                 // $error_counter++;
    //                                 // $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
    //                                 // $count++;
    //                                 // continue;
    //                             }

    //                             Concession::where('student_id', $student->id)->update([
    //                                 'end_date' => '2024-01-01', // Update the end date to 2024-01-01
    //                                 'active_status' => '0',
    //                             ]);
    //                             $con = Concession::where('student_id', $student->id)->wherein('concession_id', $a)->first();
    //                             // if no concession found, create new concession
    //                             if (!$con) {
    //                                 $con = new Concession;
    //                                 $con->student_id = $student->id;
    //                                 $con->class_id = $student->class_id;
    //                                 $con->concession_id = $a[0];
    //                                 $con->concession_by = 'MOHSIN FIAZ';
    //                                 $con->apply_date = '2024-01-01';
    //                                 $con->start_date = '2024-01-01';
    //                                 $con->end_date = '2038-01-01';
    //                                 $con->remarks = 'Imported';
    //                                 $con->status = 'Approved';
    //                                 $con->owned_by = $student->owned_by;
    //                                 $con->created_by = \Auth::user()->creatorId();
    //                                 $con->save();
    //                             }
    //                             if (!$con) {
    //                                 $reason = 'Concession not found: '.$all_data[2];
    //                                 $error_counter++;
    //                                 $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2], $all_data[39]], ['Status' => $record_status, 'Reason' => $reason]);
    //                                 $count++;
    //                                 // continue;
    //                             }
    //                             if ($con) {
    //                                 $con->end_date = '2038-01-01';
    //                                 $con->active_status = '1';
    //                                 $con->owned_by = $student->owned_by;
    //                                 $con->save();
    //                             }
    //                             // dd($con);
    //                         }

    //                         $fee_heads = FeeHead::get();
    //                         // dd($fee_heads);
    //                         if ($fee_heads) {
    //                             for ($i = 0; $i < count($fee_heads); $i++) {
    //                                 $classfee = StudentFeeStructure::updateOrCreate(
    //                                     [
    //                                         'reg_id' => $student->id,
    //                                         'branch_id' => $student->owned_by,
    //                                         'head_id' => $fee_heads[$i]['id'],
    //                                     ],
    //                                     [
    //                                         'amount' => 0,
    //                                         'class_id' => $student->class_id,
    //                                         'discount' => '0', // set discount to 0 for all fee heads
    //                                         'owned_by' => $student->owned_by,
    //                                         'created_by' => $student->created_by,
    //                                     ]
    //                                 );
    //                             }
    //                         }else{
    //                             dd('Fee heads not found');
    //                         }
    //                         // dd($classfee);
    //                         $headadmission = FeeHead::where('fee_head', 'like', '%ADMISSION FEE%')->first();
    //                         if (!$headadmission) {
    //                             dd('Admission fee head not found');
    //                         }
    //                         // dd($headadmission);
    //                         $headadmissionFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headadmission->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[7],
    //                                 'discount' => !$con ? $all_data[8] : '0',
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[10],
    //                             ]
    //                         );
    //                         // dd($headadmissionFee);
    //                         $headannual = FeeHead::where('fee_head', 'like', '%ANNUAL FEE%')->first();
    //                         if (!$headannual) {
    //                             dd('Annual fee head not found');
    //                         }
    //                         $headannualFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headannual->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[11],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => !$con ? $all_data[12] : '0',
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[14],
    //                             ]
    //                         );
    //                         $headsecurity = FeeHead::where('fee_head', 'like', '%SECURITY FEE%')->first();
    //                         $headsecurityFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headsecurity->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[15],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => !$con ? $all_data[16] : '0',
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[18],
    //                             ]
    //                         );
    //                         $headtution = FeeHead::where('fee_head', 'like', '%Tuition Fee%')->first();
    //                         $headtutionFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headtution->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[19],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => !$con ? $all_data[20] : '0',
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[22],
    //                             ]
    //                         );

    //                         $headac = FeeHead::where('fee_head', 'like', '%AC-INVERTER FEE%')->first();
    //                         $headacFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headac->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[23],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => $all_data[24],
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[26],
    //                             ]
    //                         );
    //                         $headcarefee = FeeHead::where('fee_head', 'like', '%EXTRA CARE FEE%')->first();
    //                         $headextraFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headcarefee->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[27],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => $all_data[28],
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[30],
    //                             ]
    //                         );
    //                         $headlatefee = FeeHead::where('fee_head', 'like', '%MONTHLY CARE-LATE STAY FEE%')->first();
    //                         $headextraFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headlatefee->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[31],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => $all_data[32],
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[34],
    //                             ]
    //                         );
    //                         $headtransportfee = FeeHead::where('fee_head', 'like', '%TRANSPORT FEE%')->first();
    //                         $headtransportFee = StudentFeeStructure::updateOrCreate(
    //                             [
    //                                 'reg_id' => $student->id,
    //                                 'branch_id' => $student->owned_by,
    //                                 'head_id' => $headtransportfee->id,
    //                             ],
    //                             [
    //                                 'student_id' => $student->roll_no,
    //                                 'amount' => $all_data[35],
    //                                 'class_id' => $student->class_id,
    //                                 'discount' => $all_data[36],
    //                                 'owned_by' => $student->owned_by,
    //                                 'created_by' => $student->created_by,
    //                                 'checked_status' => $all_data[38],
    //                             ]
    //                         );

    //                     }
    //                     $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
    //                     $success_counter++;
    //                 } else {
    //                     $header = $all_data;
    //                     $header[] = 'Status';
    //                     $header[] = 'Reason';
    //                     $processed_records[] = $header;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //             DB::commit();
    //             if (! empty($skip_data)) {
    //                 $export_filename = 'regular_challan_import_errors_'.time().'.csv';
    //                 $error_filepath = public_path('assets/import/csv_file/'.$export_filename);
    //                 $error_file = fopen($error_filepath, 'w+');
    //                 fputcsv($error_file, ['Enrollment ID', 'Roll No', 'Student Name', 'Status', 'Reason']);
    //                 foreach ($skip_data as $row) {
    //                     fputcsv($error_file, $row);
    //                 }
    //                 fclose($error_file);

    //                 return response()->download($error_filepath)->deleteFileAfterSend(true);
    //             }

    //             return redirect()->back()->with('message', "{$success_counter} Regular Challan(s) added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e);
    //         \Log::error('Regular Challan Import Error: '.$e->getMessage()."\n".$e->getTraceAsString());

    //         return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
    //     }
    // }
    private function ConcessionListImport($file, $request)
    {
        set_time_limit(0);
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $skip_data = [];
        $processed_records = [];

        // Helper function to clean decimal formatting
        $cleanDecimal = function ($value) {
            $value = trim($value);
            if ($value === '' || $value === null) {
                return '0';
            }

            // Convert to float then to string to normalize
            $floatVal = (float) $value;

            // If it's a whole number (like 38.0000), return without decimals
            if ($floatVal == floor($floatVal)) {
                return (string) intval($floatVal);
            }

            // Otherwise, format with up to 8 decimals and remove trailing zeros
            $formatted = number_format($floatVal, 8, '.', '');
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.'); // Remove decimal point if no decimals left

            return $formatted;
        };

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== false) {
                $count = 0;

                // CSV Column Indices
                // index 0 => Branch
                // index 1 => Student Name
                // index 2 => Roll No
                // index 3 => Class Name
                // index 4 => Section Name
                // index 6 => D.O.J

                // Admission Fee: index 7 => Amount, 8 => Disc %, 9 => Final Amount, 10 => Check Status
                // Annual Fee: index 11 => Amount, 12 => Disc %, 13 => Final Amount, 14 => Check Status
                // Security Fee: index 15 => Amount, 16 => Disc %, 17 => Final Amount, 18 => Check Status
                // Tuition Fee: index 19 => Amount, 20 => Disc %, 21 => Final Amount, 22 => Check Status
                // AC Fee: index 23 => Amount, 24 => Disc %, 25 => Final Amount, 26 => Check Status
                // Extra Care Fee: index 27 => Amount, 28 => Disc %, 29 => Final Amount, 30 => Check Status
                // Monthly Care Fee: index 31 => Amount, 32 => Disc %, 33 => Final Amount, 34 => Check Status
                // Transport Fee: index 35 => Amount, 36 => Disc %, 37 => Final Amount, 38 => Check Status
                // Discount Policy: index 39

                while (($all_data = fgetcsv($handle, 7000, ',')) !== false) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';

                        // Trim all data
                        $all_data = array_map('trim', $all_data);

                        // Find enrollment
                        $enr = StudentEnrollments::where('enrollId', $all_data[2])->first();
                        if (!$enr) {
                            $reason = 'Enrollment not found: ' . $all_data[2];
                            $error_counter++;
                            $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2]], ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        // Find student
                        $student = StudentRegistration::where('id', $enr->regId)->first();
                        if (!$student) {
                            $reason = 'Student not found for enrollment: ' . $all_data[2];
                            $error_counter++;
                            $skip_data[] = array_merge([$all_data[0], $all_data[1], $all_data[2]], ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        // Define fee head mappings with their discount column indices
                        $feeHeadMappings = [
                            'ADMISSION FEE' => ['amount_idx' => 7, 'discount_idx' => 8, 'check_idx' => 10, 'short_name' => 'Adm'],
                            'ANNUAL FEE' => ['amount_idx' => 11, 'discount_idx' => 12, 'check_idx' => 14, 'short_name' => 'Annual'],
                            'SECURITY FEE' => ['amount_idx' => 15, 'discount_idx' => 16, 'check_idx' => 18, 'short_name' => 'Security'],
                            'Tuition Fee' => ['amount_idx' => 19, 'discount_idx' => 20, 'check_idx' => 22, 'short_name' => 'Tuition'],
                            'AC-INVERTER FEE' => ['amount_idx' => 23, 'discount_idx' => 24, 'check_idx' => 26, 'short_name' => 'AC'],
                            'EXTRA CARE FEE' => ['amount_idx' => 27, 'discount_idx' => 28, 'check_idx' => 30, 'short_name' => 'Ecare'],
                            'MONTHLY CARE-LATE STAY FEE' => ['amount_idx' => 31, 'discount_idx' => 32, 'check_idx' => 34, 'short_name' => 'Mcare'],
                            'TRANSPORT FEE' => ['amount_idx' => 35, 'discount_idx' => 36, 'check_idx' => 38, 'short_name' => 'Tran'],
                        ];

                        // Extract discount percentages from CSV - Clean decimal formatting
                        $discountData = [];
                        foreach ($feeHeadMappings as $headName => $mapping) {
                            $feeHead = FeeHead::where('fee_head', 'like', '%' . $headName . '%')->first();
                            if ($feeHead) {
                                $discountPercentage = isset($all_data[$mapping['discount_idx']]) ? trim($all_data[$mapping['discount_idx']]) : '0';

                                // Clean the decimal formatting (remove trailing zeros)
                                $discountPercentage = $cleanDecimal($discountPercentage);

                                $discountData[] = [
                                    'head_id' => $feeHead->id,
                                    'percentage' => $discountPercentage,
                                    'short_name' => $mapping['short_name'],
                                    'amount' => isset($all_data[$mapping['amount_idx']]) ? $all_data[$mapping['amount_idx']] : 0,
                                    'check_status' => isset($all_data[$mapping['check_idx']]) ? $all_data[$mapping['check_idx']] : 0,
                                ];
                            }
                        }

                        // Sort discount data by head_id for consistent comparison
                        usort($discountData, function ($a, $b) {
                            return $a['head_id'] <=> $b['head_id'];
                        });

                        // Build policy title from discount data with exact percentages
                        $policyTitleParts = [];
                        foreach ($discountData as $disc) {
                            // Use exact percentage value from CSV
                            $policyTitleParts[] = $disc['percentage'] . '%' . $disc['short_name'];
                        }
                        $generatedPolicyTitle = implode('+', $policyTitleParts);

                        // Find matching concession policy
                        $matchingPolicy = null;
                        $allPolicies = ConcessionPolicy::with('policy_head')->get();

                        foreach ($allPolicies as $policy) {
                            // Check if count matches
                            if (count($policy->policy_head) !== count($discountData)) {
                                continue;
                            }

                            // Sort policy heads for comparison
                            $policyHeads = $policy->policy_head->sortBy('head_id')->values();

                            // Check if all heads and percentages match (with tolerance for floating point)
                            $allMatch = true;
                            foreach ($discountData as $index => $disc) {
                                if (
                                    !isset($policyHeads[$index]) ||
                                    $policyHeads[$index]->head_id != $disc['head_id'] ||
                                    abs((float) $policyHeads[$index]->percentage - (float) $disc['percentage']) > 0.00000001
                                ) {
                                    $allMatch = false;
                                    break;
                                }
                            }

                            if ($allMatch) {
                                $matchingPolicy = $policy;
                                break;
                            }
                        }

                        // If no matching policy found, create new one
                        if (!$matchingPolicy) {
                            $newPolicy = new ConcessionPolicy;
                            $newPolicy->order_no = 1;
                            $newPolicy->title = $generatedPolicyTitle;
                            $newPolicy->description = $generatedPolicyTitle;
                            $newPolicy->owned_by = \Auth::user()->ownedId();
                            $newPolicy->created_by = \Auth::user()->creatorId();
                            $newPolicy->save();

                            foreach ($discountData as $disc) {
                                // Use direct DB insert to preserve exact decimal precision
                                DB::table('concession_policy_heads')->insert([
                                    'concession_id' => $newPolicy->id,
                                    'head_id' => $disc['head_id'],
                                    'percentage' => $disc['percentage'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            $matchingPolicy = $newPolicy;
                        }

                        // Check if any discount exists
                        $hasDiscount = false;
                        foreach ($discountData as $disc) {
                            if ((float) $disc['percentage'] > 0) {
                                $hasDiscount = true;
                                break;
                            }
                        }

                        // Update or create concession
                        if ($hasDiscount) {
                            // Deactivate existing concessions
                            Concession::where('student_id', $student->id)->update([
                                'end_date' => '2024-01-01',
                                'active_status' => '0',
                            ]);

                            // Find or create concession
                            $concession = Concession::where('student_id', $student->id)
                                ->where('concession_id', $matchingPolicy->id)
                                ->first();

                            if (!$concession) {
                                $concession = new Concession;
                                $concession->student_id = $student->id;
                                $concession->class_id = $student->class_id;
                                $concession->concession_id = $matchingPolicy->id;
                                $concession->concession_by = 'MOHSIN FIAZ';
                                $concession->apply_date = '2024-01-01';
                                $concession->start_date = '2024-01-01';
                                $concession->remarks = 'Imported';
                                $concession->status = 'Approved';
                                $concession->owned_by = $student->owned_by;
                                $concession->created_by = \Auth::user()->creatorId();
                            }

                            $concession->end_date = '2038-01-01';
                            $concession->active_status = '1';
                            $concession->save();
                        } else {
                            $concession = null;
                        }

                        // Get all fee heads
                        $allFeeHeads = FeeHead::get();

                        // Initialize all fee heads with 0 amount and unchecked status
                        foreach ($allFeeHeads as $feeHead) {
                            StudentFeeStructure::updateOrCreate(
                                [
                                    'reg_id' => $student->id,
                                    'branch_id' => $student->owned_by,
                                    'head_id' => $feeHead->id,
                                ],
                                [
                                    'amount' => 0,
                                    'class_id' => $student->class_id,
                                    'discount' => '0',
                                    'checked_status' => '0',
                                    'owned_by' => $student->owned_by,
                                    'created_by' => $student->created_by,
                                ]
                            );
                        }

                        // Update fee structures for mapped heads
                        foreach ($feeHeadMappings as $headName => $mapping) {
                            $feeHead = FeeHead::where('fee_head', 'like', '%' . $headName . '%')->first();
                            if ($feeHead) {
                                $amount = isset($all_data[$mapping['amount_idx']]) ? $all_data[$mapping['amount_idx']] : 0;

                                // Get exact discount value from CSV and clean it
                                $discount = isset($all_data[$mapping['discount_idx']]) ? trim($all_data[$mapping['discount_idx']]) : '0';
                                $discount = $cleanDecimal($discount);

                                $checkStatus = isset($all_data[$mapping['check_idx']]) ? $all_data[$mapping['check_idx']] : 0;

                                // If concession exists, set discount to 0 (policy handles it)
                                if ($concession) {
                                    $discount = '0';
                                }

                                StudentFeeStructure::updateOrCreate(
                                    [
                                        'reg_id' => $student->id,
                                        'branch_id' => $student->owned_by,
                                        'head_id' => $feeHead->id,
                                    ],
                                    [
                                        'student_id' => $student->roll_no,
                                        'amount' => $amount,
                                        'class_id' => $student->class_id,
                                        'discount' => $discount,
                                        'checked_status' => $checkStatus,
                                        'owned_by' => $student->owned_by,
                                        'created_by' => $student->created_by,
                                    ]
                                );
                            }
                        }

                        // Ensure COMPUTER FEE is unchecked
                        $computerFeeHead = FeeHead::where('fee_head', 'like', '%COMPUTER FEE%')->first();
                        if ($computerFeeHead) {
                            StudentFeeStructure::updateOrCreate(
                                [
                                    'reg_id' => $student->id,
                                    'branch_id' => $student->owned_by,
                                    'head_id' => $computerFeeHead->id,
                                ],
                                [
                                    'amount' => 0,
                                    'class_id' => $student->class_id,
                                    'discount' => 0,
                                    'checked_status' => '0',
                                    'owned_by' => $student->owned_by,
                                    'created_by' => $student->created_by,
                                ]
                            );
                        }
                        //only for shifa branch set register option to 2
                        $student->update([
                            'register_option' => 2,
                        ]);
                        $processed_records[] = array_merge($all_data, ['Status' => 'Success', 'Reason' => '']);
                        $success_counter++;
                    } else {
                        // Header row
                        $header = $all_data;
                        $header[] = 'Status';
                        $header[] = 'Reason';
                        $processed_records[] = $header;
                    }
                    $count++;
                }

                fclose($handle);
                DB::commit();

                // Handle error file export
                if (!empty($skip_data)) {
                    $export_filename = 'concession_import_errors_' . time() . '.csv';
                    $error_filepath = public_path('assets/import/csv_file/' . $export_filename);
                    $error_file = fopen($error_filepath, 'w+');
                    fputcsv($error_file, ['Branch', 'Student Name', 'Roll No', 'Status', 'Reason']);
                    foreach ($skip_data as $row) {
                        fputcsv($error_file, $row);
                    }
                    fclose($error_file);

                    return response()->download($error_filepath)->deleteFileAfterSend(true);
                }

                return redirect()->back()->with('message', "{$success_counter} Concession(s) processed successfully. {$error_counter} rows skipped due to errors.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Concession Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    //  private function WithdrawImport($file, $request)
    // {
    //     set_time_limit(0);
    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Initialize counters and error tracking
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $error_records = [];

    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;

    //             while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
    //                 if ($count > 0) {
    //                     // Check for empty data in first column
    //                     if (empty($all_data[0])) {
    //                         $error_records[] = ['row' => $count, 'reason' => 'Empty enrollment ID'];
    //                         $error_counter++;
    //                         $count++;
    //                         continue;
    //                     }
    //                     $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[4]);
    //                     $branchfrom = User::where('name', $clean_title)->first();
    //                     if (!$branchfrom) {
    //                         $error_records[] = ['row' => $count, 'reason' => 'From branch not found: ' . $all_data[4]];
    //                         $error_counter++;
    //                         $count++;
    //                         continue;
    //                     }
    //                     // Validate enrollment
    //                     $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
    //                     if (!$enr) {
    //                         if (!empty($all_data[5]) || !empty($all_data[6])) {
    //                             $reg = StudentRegistration::where('stdname', 'LIKE', '%' . $all_data[5] . '%')
    //                                 ->orWhere('fathername', 'LIKE', '%' . $all_data[6] . '%')
    //                                 ->where('owned_by', $branchfrom->id)
    //                                 ->first();
    //                             // dd($all_data, $enr,$reg);
    //                             if ($reg) {
    //                                 // If registration found by name, try to find the enrollment
    //                                 $enr = StudentEnrollments::where('regId', $reg->reg_no)->where('owned_by', $branchfrom->id)->first();
    //                                 if (!$enr) {
    //                                     $error_records[] = ['row' => $count, 'reason' => 'Enrollment not found for student: ' . $all_data[5]];
    //                                     $error_counter++;
    //                                     $count++;
    //                                     continue;
    //                                 }
    //                             } else {
    //                                 $error_records[] = ['row' => $count, 'reason' => 'Student not found: ' . $all_data[5] . ' ' . $all_data[6]];
    //                                 $error_counter++;
    //                                 $count++;
    //                                 continue;
    //                             }
    //                         } else {
    //                             $error_records[] = ['row' => $count, 'reason' => 'Enrollment not found: ' . $all_data[0]];
    //                             $error_counter++;
    //                             $count++;
    //                             continue;
    //                         }
    //                     }
    //                     // Check for existing withdrawal (prevent duplicates)
    //                     $withdraw = StudentWithdrawal::where('student_id', $enr->enrollId)->first();
    //                     if ($withdraw) {
    //                         $error_records[] = ['row' => $count, 'reason' => 'Student already withdrawn'];
    //                         $duplication_counter++;
    //                         $count++;
    //                         continue;
    //                     }

    //                     // Validate withdrawal date
    //                     $withdrawal_date = date('Y-m-d', strtotime($all_data[1]));
    //                     if (!$withdrawal_date) {
    //                         $error_records[] = ['row' => $count, 'reason' => 'Invalid withdrawal date: ' . $all_data[1]];
    //                         $error_counter++;
    //                         $count++;
    //                         continue;
    //                     }
    //                     if (!$enr->class_id) {
    //                         $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
    //                         if (!$reg->class_id) {
    //                             $class = Classes::where('name', $all_data[7])->where('owned_by', $enr->owned_by)->first();
    //                             if (!$class) {
    //                                 $class = new Classes();
    //                                 $class->name = $all_data[7];
    //                                 $class->owned_by = $enr->owned_by;
    //                                 $class->created_by = auth()->user()->id;
    //                                 $class->save();
    //                             }
    //                             $reg->class_id = $class->id;
    //                             $reg->save();
    //                         }
    //                         $enr->class_id = $reg->class_id;
    //                         $enr->save();
    //                     }
    //                     // Create new withdrawal
    //                     $withdrawal = new StudentWithdrawal();
    //                     $withdrawal->student_id = $enr->enrollId;
    //                     $withdrawal->challan_id = null;
    //                     $withdrawal->branch_id = $branchfrom->id;
    //                     $withdrawal->class_id = $enr->class_id;
    //                     $withdrawal->withdraw_date = $withdrawal_date;
    //                     $withdrawal->apply_date = $withdrawal_date;
    //                     $withdrawal->reason = isset($all_data[3]) && $all_data[3] != '' ? $all_data[3] : $all_data[2];
    //                     $withdrawal->remark = isset($all_data[3]) && $all_data[3] != '' ? $all_data[3] : $all_data[2];
    //                     $withdrawal->owned_by = $enr->owned_by;
    //                     $withdrawal->created_by = \Auth::user()->creatorId();
    //                     $withdrawal->save();
    //                     $success_counter++;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //             DB::commit();
    //             return redirect()->back()->with('message', "{$success_counter} Student Withdrawal added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e);
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    // }
    // private function StudentDetail($file, $request)
    // {
    //     set_time_limit(0);
    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);
    //     DB::beginTransaction();
    //     try {
    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;
    //             $success_counter = 0;
    //             $error_counter = 0;
    //             $duplication_counter = 0;
    //             while (($all_data = fgetcsv($handle, 4000, ",")) !== FALSE) {
    //                 if ($count > 0) {
    //                     if (empty($all_data[0])) {
    //                         $error_counter++;
    //                         continue;
    //                     }
    //                     $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
    //                     if (!$enr) {
    //                         $error_counter++;
    //                         continue;
    //                     }
    //                     $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
    //                     if (!$reg) {
    //                         $error_counter++;
    //                         continue;
    //                     }
    //                     $class = Classes::where('name', $all_data[1])->where('owned_by', $enr->owned_by)->first();
    //                     if (!$class) {
    //                         $error_counter++;
    //                         continue;
    //                     }
    //                     $section = Section::where('name', $all_data[2])->first();
    //                     if (!$section) {
    //                         dd($section, $all_data, $enr, $reg, $class, 'section not found');
    //                         $error_counter++;
    //                         continue;
    //                     }
    //                     $sectionclass = ClassSection::where('class_id', $class->id)->where('section_id', $section->id)->where('owned_by', $enr->owned_by)->where('active_status', 1)->first();
    //                     if (!$sectionclass) {
    //                         if ($enr->owned_by == 53) {
    //                             $sectionclass = new ClassSection();
    //                             $sectionclass->class_id = $class->id;
    //                             $sectionclass->section_id = $section->id;
    //                             $sectionclass->owned_by = $enr->owned_by;
    //                             $sectionclass->created_by = auth()->user()->id;
    //                             $sectionclass->save();
    //                         } else {
    //                             dd($class->id, $section->id, $enr->owned_by);
    //                             $error_counter++;
    //                             continue;
    //                         }

    //                     }
    //                     if ($reg) {
    //                         $reg->mothername = $all_data[4];
    //                         $reg->fatherprofession = $all_data[5];
    //                         $reg->motherprofession = $all_data[6];
    //                         $reg->address = $all_data[7];
    //                         $reg->permanent_address = $all_data[7];
    //                         $reg->register_option = 1;
    //                         $reg->reg_class = $sectionclass->class_id;
    //                         $reg->class_id = $sectionclass->class_id;
    //                         $reg->save();

    //                     }
    //                     $enr->class_id = $sectionclass->class_id;
    //                     $enr->adm_session = 2;
    //                     $enr->section_id = $sectionclass->section_id;
    //                     $enr->save();
    //                     DB::commit();
    //                     $success_counter++;
    //                 }
    //                 $count++;
    //             }
    //             fclose($handle);
    //         }
    //         return redirect()->back()->with('success', "{$success_counter} Section added successfully. {$error_counter} rows skipped due to Duplication,incomplete or invalid data.");
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         dd($e);
    //         return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
    //     }
    // }
    // private function EnrollmentImport($file, $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:csv,txt',
    //     ]);

    //     $file = $request->file('excel_file');
    //     $filename = $file->getClientOriginalName();
    //     $file->move(public_path('assets/import/csv_file/'), $filename);
    //     $filepath = public_path('assets/import/csv_file/' . $filename);

    //     // Initialize counters
    //     $success_counter = 0;
    //     $error_counter = 0;
    //     $duplication_counter = 0;
    //     $skipped_records = [];

    //     DB::beginTransaction();
    //     try {
    //         $filenames = $filename . "enrollments.csv";
    //         $new = fopen($filenames, 'w+');

    //         if (($handle = fopen($filepath, 'r')) !== FALSE) {
    //             $count = 0;

    //             while (($all_data = fgetcsv($handle, 4000, ",")) !== FALSE) {
    //                 // Skip header row or empty rows
    //                 if ($count == 0 || empty(array_filter($all_data))) {
    //                     $count++;
    //                     continue;
    //                 }

    //                 $all_data = array_map('trim', $all_data);
    //                 $registration = StudentRegistration::where('reg_no', $all_data[1])->first();

    //                 // Skip if registration doesn't exist
    //                 if ($registration === null) {
    //                     $skipped_records[] = [
    //                         'reg_no' => $all_data[1],
    //                         'reason' => 'Registration not found'
    //                     ];
    //                     $error_counter++;
    //                     $count++;
    //                     continue;
    //                 }

    //                 // Skip if student status is neither Registered nor empty
    //                 if (!in_array($registration->student_status, ['Registered', ''])) {
    //                     $skipped_records[] = [
    //                         'reg_no' => $all_data[1],
    //                         'reason' => 'Student status is not valid for enrollment'
    //                     ];
    //                     $error_counter++;
    //                     $count++;
    //                     continue;
    //                 }

    //                 // Check for existing enrollment to avoid duplication
    //                 $existingEnrollment = StudentEnrollments::where('regId', $all_data[1])
    //                     ->where('session_id', $registration->session_id)
    //                     ->first();

    //                 if ($existingEnrollment) {
    //                     $skipped_records[] = [
    //                         'reg_no' => $all_data[1],
    //                         'reason' => 'Student already enrolled in this session'
    //                     ];
    //                     $duplication_counter++;
    //                     $count++;
    //                     continue;
    //                 }

    //                 // Check for existing admission challan to avoid duplication
    //                 $existingChallan = Challans::where('student_id', $all_data[1])
    //                     ->where('session_id', $registration->session_id)
    //                     ->where('challan_type', 'Admission')
    //                     ->first();

    //                 if ($existingChallan) {
    //                     $skipped_records[] = [
    //                         'reg_no' => $all_data[1],
    //                         'reason' => 'Admission challan already exists for this student'
    //                     ];
    //                     $duplication_counter++;
    //                     $count++;
    //                     continue;
    //                 }

    //                 try {
    //                     // Get section once
    //                     $section = ClassSection::where('class_id', $registration->class_id)->first();

    //                     // Create enrollment
    //                     $newEnrollId = $all_data[11];
    //                     $created_at = date('Y-m-d H:i:s', strtotime($all_data[4]));

    //                     $enrollment = new StudentEnrollments();
    //                     $enrollment->enrollId = $newEnrollId;
    //                     $enrollment->regId = $all_data[1];
    //                     $enrollment->adm_date = date('Y-m-d', strtotime($all_data[4]));
    //                     $enrollment->class_id = $registration->class_id ?? NULL;
    //                     $enrollment->section_id = $section->id ?? NULL;
    //                     $enrollment->session_id = $registration->session_id;
    //                     $enrollment->owned_by = $registration->owned_by;
    //                     $enrollment->created_by = \Auth::user()->creatorId();
    //                     $enrollment->created_at = $created_at;
    //                     $enrollment->updated_at = $created_at;
    //                     $enrollment->save();

    //                     // Update registration status
    //                     $registration->roll_no = $newEnrollId;
    //                     $registration->student_status = 'Enrolled';
    //                     $registration->save();

    //                     // Process challan and fee heads
    //                     $this->processAdmissionChallan($all_data, $registration, $newEnrollId, $created_at);

    //                     $success_counter++;
    //                 } catch (\Exception $innerException) {
    //                     $skipped_records[] = [
    //                         'reg_no' => $all_data[1],
    //                         'reason' => 'Processing error: ' . $innerException->getMessage()
    //                     ];
    //                     $error_counter++;
    //                 }

    //                 $count++;
    //             }

    //             fclose($handle);
    //             if (is_resource($new)) {
    //                 fclose($new);
    //             }

    //             DB::commit();

    //             // Log results
    //             \Log::info("Enrollment Import Completed. Success: {$success_counter}, Duplicates: {$duplication_counter}, Errors: {$error_counter}");

    //             return [
    //                 'success' => true,
    //                 'message' => "Import completed. Processed: {$count}, Success: {$success_counter}, Duplicates: {$duplication_counter}, Errors: {$error_counter}",
    //                 'skipped_records' => $skipped_records
    //             ];
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         if (is_resource($new)) {
    //             fclose($new);
    //         }
    //         dd($e);
    //         \Log::error("Enrollment Import Error: " . $e->getMessage());
    //         return [
    //             'success' => false,
    //             'message' => "Import failed: " . $e->getMessage()
    //         ];
    //     }
    // }

}
