<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Challan;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\FeeHead;
use App\Models\Registring_option;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeStructure;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\StudentWithdrawal;
use App\Models\Utility;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DataImportController extends Controller
{
    public function showForm()
    {
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
            $this->EnrollmentImport($file, $request);
        } else if ($dataType == 'class') {
            $this->ClassImport($request);
        } else if ($dataType == 'section') {
            $this->SectionImport($request);
        } else if ($dataType == 'registration') {
            $this->RegistrationImport($file, $request);
        } else if ($dataType == 'student_detail') {
            $this->StudentDetail($file, $request);
        } else if ($dataType == 'transfer') {
            $this->TransferImport($file, $request);
        } else if ($dataType == 'withdraw') {
            $this->WithdrawImport($file, $request);
        } else {
            return redirect()->back()->with('error', 'No Data Type Selected');
        }
        return redirect()->back()->with('success', 'Data imported successfully!');
    }
    private function WithdrawImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and error tracking
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $error_records = [];
        $processed_records = [];

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;

                while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';

                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $error_records[] = ['row' => $count, 'reason' => 'Empty enrollment ID'];
                            $error_counter++;
                            $reason = 'Empty enrollment ID';
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[4]);
                        $branchfrom = User::where('name', $clean_title)->first();
                        if (!$branchfrom) {
                            $error_records[] = ['row' => $count, 'reason' => 'From branch not found: ' . $all_data[4]];
                            $error_counter++;
                            $reason = 'From branch not found: ' . $all_data[4];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        // Rest of validation and processing logic...
                        // Validate enrollment
                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            if (!empty($all_data[5]) || !empty($all_data[6])) {
                                $reg = StudentRegistration::where('stdname', 'LIKE', '%' . $all_data[5] . '%')
                                    ->orWhere('fathername', 'LIKE', '%' . $all_data[6] . '%')
                                    ->where('owned_by', $branchfrom->id)
                                    ->first();

                                if ($reg) {
                                    // If registration found by name, try to find the enrollment
                                    $enr = StudentEnrollments::where('regId', $reg->reg_no)->where('owned_by', $branchfrom->id)->first();
                                    if (!$enr) {
                                        $error_records[] = ['row' => $count, 'reason' => 'Enrollment not found for student: ' . $all_data[5]];
                                        $error_counter++;
                                        $reason = 'Enrollment not found for student: ' . $all_data[5];
                                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                        $count++;
                                        continue;
                                    }
                                } else {
                                    $error_records[] = ['row' => $count, 'reason' => 'Student not found: ' . $all_data[5] . ' ' . $all_data[6]];
                                    $error_counter++;
                                    $reason = 'Student not found: ' . $all_data[5] . ' ' . $all_data[6];
                                    $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                    $count++;
                                    continue;
                                }
                            } else {
                                $error_records[] = ['row' => $count, 'reason' => 'Enrollment not found: ' . $all_data[0]];
                                $error_counter++;
                                $reason = 'Enrollment not found: ' . $all_data[0];
                                $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                $count++;
                                continue;
                            }
                        }

                        // Check for existing withdrawal (prevent duplicates)
                        $withdraw = StudentWithdrawal::where('student_id', $enr->enrollId)->first();
                        if ($withdraw) {
                            $error_records[] = ['row' => $count, 'reason' => 'Student already withdrawn'];
                            $duplication_counter++;
                            $reason = 'Student already withdrawn';
                            $processed_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        // Validate withdrawal date
                        $withdrawal_date = date('Y-m-d', strtotime($all_data[1]));
                        if (!$withdrawal_date) {
                            $error_records[] = ['row' => $count, 'reason' => 'Invalid withdrawal date: ' . $all_data[1]];
                            $error_counter++;
                            $reason = 'Invalid withdrawal date: ' . $all_data[1];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            $count++;
                            continue;
                        }

                        if (!$enr->class_id) {
                            $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
                            if (!$reg->class_id) {
                                $class = Classes::where('name', $all_data[7])->where('owned_by', $enr->owned_by)->first();
                                if (!$class) {
                                    $class = new Classes();
                                    $class->name = $all_data[7];
                                    $class->owned_by = $enr->owned_by;
                                    $class->created_by = auth()->user()->id;
                                    $class->save();
                                }
                                $reg->class_id = $class->id;
                                $reg->save();
                            }
                            $enr->class_id = $reg->class_id;
                            $enr->save();
                        }

                        // Create new withdrawal
                        $withdrawal = new StudentWithdrawal();
                        $withdrawal->student_id = $enr->enrollId;
                        $withdrawal->challan_id = null;
                        $withdrawal->branch_id = $branchfrom->id;
                        $withdrawal->class_id = $enr->class_id;
                        $withdrawal->withdraw_date = $withdrawal_date;
                        $withdrawal->apply_date = $withdrawal_date;
                        $withdrawal->reason = isset($all_data[3]) && $all_data[3] != '' ? $all_data[3] : $all_data[2];
                        $withdrawal->remark = isset($all_data[3]) && $all_data[3] != '' ? $all_data[3] : $all_data[2];
                        $withdrawal->owned_by = $enr->owned_by;
                        $withdrawal->created_by = \Auth::user()->creatorId();
                        $withdrawal->save();

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

                // Generate Excel file
                $export_filename = 'withdrawal_results_' . time() . '.xlsx';
                $this->exportToExcel($processed_records, $export_filename);

                return response()->download(public_path('assets/export/' . $export_filename))->with('message', "{$success_counter} Student Withdrawal added successfully. {$error_counter} rows skipped due to errors. {$duplication_counter} duplicates found.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
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

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;

                while (($all_data = fgetcsv($handle, 4000, ",")) !== FALSE) {
                    if ($count > 0) {
                        $record_status = 'Error';
                        $reason = '';

                        if (empty($all_data[0])) {
                            $error_counter++;
                            $reason = 'Empty enrollment ID';
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            continue;
                        }

                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $error_counter++;
                            $reason = 'Enrollment not found: ' . $all_data[0];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            continue;
                        }

                        $reg = StudentRegistration::where('reg_no', $enr->regId)->where('owned_by', $enr->owned_by)->first();
                        if (!$reg) {
                            $error_counter++;
                            $reason = 'Registration not found for enrollment: ' . $all_data[0];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            continue;
                        }

                        $class = Classes::where('name', $all_data[1])->where('owned_by', $enr->owned_by)->first();
                        if (!$class) {
                            $error_counter++;
                            $reason = 'Class not found: ' . $all_data[1];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            continue;
                        }

                        $section = Section::where('name', $all_data[2])->first();
                        if (!$section) {
                            $error_counter++;
                            $reason = 'Section not found: ' . $all_data[2];
                            $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                            continue;
                        }

                        $sectionclass = ClassSection::where('class_id', $class->id)
                            ->where('section_id', $section->id)
                            ->where('owned_by', $enr->owned_by)
                            ->where('active_status', 1)
                            ->first();

                        if (!$sectionclass) {
                            if ($enr->owned_by == 53) {
                                $sectionclass = new ClassSection();
                                $sectionclass->class_id = $class->id;
                                $sectionclass->section_id = $section->id;
                                $sectionclass->owned_by = $enr->owned_by;
                                $sectionclass->created_by = auth()->user()->id;
                                $sectionclass->save();
                            } else {
                                $error_counter++;
                                $reason = 'Class section not found and cannot be created for this branch';
                                $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                                continue;
                            }
                        }

                        if ($reg) {
                            $reg->mothername = $all_data[4];
                            $reg->fatherprofession = $all_data[5];
                            $reg->motherprofession = $all_data[6];
                            $reg->address = $all_data[7];
                            $reg->permanent_address = $all_data[7];
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

                // Generate Excel file
                $export_filename = 'student_details_results_' . time() . '.xlsx';
                $this->exportToExcel($processed_records, $export_filename);

                return response()->download(public_path('assets/export/' . $export_filename))->with('success', "{$success_counter} Section added successfully. {$error_counter} rows skipped due to Duplication,incomplete or invalid data.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
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
            $filenames = $filename . "enrollments.csv";
            $new = fopen($filenames, 'w+');

            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;
                $header = null;

                while (($all_data = fgetcsv($handle, 4000, ",")) !== FALSE) {
                    if ($count == 0) {
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
                    if ($registration === null) {
                        $skipped_records[] = [
                            'reg_no' => $all_data[1],
                            'reason' => 'Registration not found'
                        ];
                        $reason = 'Registration not found';
                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $error_counter++;
                        $count++;
                        continue;
                    }

                    // Skip if student status is neither Registered nor empty
                    if (!in_array($registration->student_status, ['Registered', ''])) {
                        $skipped_records[] = [
                            'reg_no' => $all_data[1],
                            'reason' => 'Student status is not valid for enrollment'
                        ];
                        $reason = 'Student status is not valid for enrollment';
                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $error_counter++;
                        $count++;
                        continue;
                    }

                    // Check for existing enrollment to avoid duplication
                    $existingEnrollment = StudentEnrollments::where('regId', $all_data[1])
                        ->where('session_id', $registration->session_id)
                        ->first();

                    if ($existingEnrollment) {
                        $skipped_records[] = [
                            'reg_no' => $all_data[1],
                            'reason' => 'Student already enrolled in this session'
                        ];
                        $reason = 'Student already enrolled in this session';
                        $processed_records[] = array_merge($all_data, ['Status' => 'Duplicate', 'Reason' => $reason]);
                        $duplication_counter++;
                        $count++;
                        continue;
                    }

                    // Check for existing admission challan to avoid duplication
                    $existingChallan = Challans::where('student_id', $all_data[1])
                        ->where('session_id', $registration->session_id)
                        ->where('challan_type', 'Admission')
                        ->first();

                    if ($existingChallan) {
                        $skipped_records[] = [
                            'reg_no' => $all_data[1],
                            'reason' => 'Admission challan already exists for this student'
                        ];
                        $reason = 'Admission challan already exists for this student';
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

                        $enrollment = new StudentEnrollments();
                        $enrollment->enrollId = $newEnrollId;
                        $enrollment->regId = $all_data[1];
                        $enrollment->adm_date = date('Y-m-d', strtotime($all_data[4]));
                        $enrollment->class_id = $registration->class_id ?? NULL;
                        $enrollment->section_id = $section->id ?? NULL;
                        $enrollment->session_id = $registration->session_id;
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
                        $skipped_records[] = [
                            'reg_no' => $all_data[1],
                            'reason' => 'Processing error: ' . $innerException->getMessage()
                        ];
                        $reason = 'Processing error: ' . $innerException->getMessage();
                        $processed_records[] = array_merge($all_data, ['Status' => $record_status, 'Reason' => $reason]);
                        $error_counter++;
                    }

                    $count++;
                }

                fclose($handle);
                if (is_resource($new)) {
                    fclose($new);
                }

                DB::commit();

                // Log results
                \Log::info("Enrollment Import Completed. Success: {$success_counter}, Duplicates: {$duplication_counter}, Errors: {$error_counter}");

                // Generate Excel file
                $export_filename = 'enrollment_results_' . time() . '.xlsx';
                $this->exportToExcel($processed_records, $export_filename);

                return response()->download(public_path('assets/export/' . $export_filename))->with('success', "Import completed. Processed: {$count}, Success: {$success_counter}, Duplicates: {$duplication_counter}, Errors: {$error_counter}");
            }
        } catch (\Exception $e) {
            DB::rollback();
            if (is_resource($new)) {
                fclose($new);
            }
            \Log::error("Enrollment Import Error: " . $e->getMessage());
            return redirect()->back()->with('error', "Import failed: " . $e->getMessage());
        }
    }

    private function processAdmissionChallan($all_data, $registration, $newEnrollId, $created_at)
    {
        $admission_fee = !empty($all_data[5]) ? floatval($all_data[5]) : 0;
        $security_fee = !empty($all_data[6]) ? floatval($all_data[6]) : 0;
        $annual_fee = !empty($all_data[7]) ? floatval($all_data[7]) : 0;
        $other_fee = 0; // For now it's set to 0

        $total_amount = $admission_fee + $security_fee + $annual_fee + $other_fee;

        // Create challan
        $challan = new Challans();
        $challan->student_id = $all_data[1];
        $challan->class_id = $registration->class_id ?? null;
        $challan->rollno = $newEnrollId;
        $challan->challanNo = $this->challanNo();
        $challan->challan_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->challan_type = 'Admission';
        $challan->total_amount = $total_amount;
        $challan->paid_amount = $total_amount;
        $challan->issue_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->due_date = date('Y-m-d', strtotime($all_data[8]));
        $challan->status = 'Paid';
        $challan->session_id = $registration->session_id;
        $challan->owned_by = $registration->owned_by;
        $challan->created_by = \Auth::user()->creatorId();
        $challan->created_at = $created_at;
        $challan->updated_at = $created_at;
        $challan->save();

        // Find bank account
        $bankAccount = BankAccount::where('owned_by', $challan->owned_by)->first();
        if (!$bankAccount) {
            throw new \Exception('Bank account not found for owner ID: ' . $challan->owned_by);
        }

        $items = [];
        $item_index = 0;

        // Process fee heads
        $fee_heads = [
            ['amount' => $admission_fee, 'pattern' => '%admission%'],
            ['amount' => $security_fee, 'pattern' => '%security%'],
            ['amount' => $annual_fee, 'pattern' => '%annual%']
        ];

        foreach ($fee_heads as $fee) {
            if ($fee['amount'] > 0) {
                // Find fee head
                $fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($fee['pattern'])])->first();
                if (!$fee_head) {
                    throw new \Exception('Fee head not found for pattern: ' . $fee['pattern']);
                }

                // Create challan head
                $challan_head = new ChallanHead();
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
                    'total' => $fee['amount']
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
            'account_id' => $bankAccount->chart_account_id,
            'items' => $items,
            'created_at' => $created_at
        ];

        // Create accounting entries
        $dataret = Utility::jrentry($data);
        $challan->voucher_id = $dataret;
        $challan->save();

        return $challan;
    }
    private function ClassImport($data)
    {
        // Get the uploaded file
        $file = $data->file('excel_file');
        if (!$file) {
            return redirect()->back()->with('error', 'No file uploaded.');
        }

        // Handle file upload
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Error file path
        $error_filename = pathinfo($filename, PATHINFO_FILENAME) . '_errors.csv';
        $error_filepath = public_path('assets/import/csv_file/' . $error_filename);

        // Counters
        $count = 0;
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;

        DB::beginTransaction();
        try {
            // Open CSV file
            if (($handle = fopen($filepath, 'r')) === FALSE) {
                return redirect()->back()->with('error', 'Could not open the file.');
            }

            // Create error file
            $error_file = fopen($error_filepath, 'w+');
            fputcsv($error_file, ['Branch', 'Class Name', 'Section', 'Error']);

            // Process CSV rows
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Skip header row
                if ($count === 0 || (isset($row[0]) && $row[0] === 'Branch')) {
                    $count++;
                    continue;
                }

                // Check for incomplete data
                if (count($row) < 3 || empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    $error_counter++;
                    fputcsv($error_file, [
                        $row[0] ?? '',
                        $row[1] ?? '',
                        $row[2] ?? '',
                        'Incomplete data'
                    ]);
                    continue;
                }

                $branch_name = trim($row[0]);
                $class_name = trim($row[1]);
                $section_name = trim($row[2]);

                // Find branch
                $branch = User::where('name', $branch_name)->first();
                if (!$branch) {
                    $error_counter++;
                    fputcsv($error_file, [$branch_name, $class_name, $section_name, 'Branch not found']);
                    continue;
                }

                // Find section
                $section = Section::where('name', $section_name)->first();
                if (!$section) {
                    $error_counter++;
                    fputcsv($error_file, [$branch_name, $class_name, $section_name, 'Section not found']);
                    continue;
                }

                // Get or create class
                $class = Classes::firstOrCreate(
                    [
                        'name' => $class_name,
                        'owned_by' => $branch->id
                    ],
                    [
                        'active_status' => '1',
                        'created_by' => auth()->user()->id
                    ]
                );

                // Check if class-section combination already exists
                $exists = ClassSection::where('class_id', $class->id)
                    ->where('section_id', $section->id)
                    ->where('owned_by', $branch->id)
                    ->exists();

                if ($exists) {
                    $error_counter++;
                    $duplication_counter++;
                    fputcsv($error_file, [
                        $branch_name,
                        $class_name,
                        $section_name,
                        'Class-Section combination already exists'
                    ]);
                    continue;
                }

                // Create class-section relationship
                ClassSection::create([
                    'active_status' => 1,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'owned_by' => $branch->id,
                    'created_by' => auth()->user()->id,
                ]);

                $success_counter++;
                $count++;
            }

            // Close files
            fclose($handle);
            fclose($error_file);

            // Clean up original file
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Commit transaction
            DB::commit();

            // Generate message
            $message = "{$success_counter} class-section combinations added successfully. ";
            if ($error_counter > 0) {
                $message .= "{$error_counter} rows skipped ({$duplication_counter} duplications, " .
                    ($error_counter - $duplication_counter) . " errors).";
            }

            // Return appropriate response
            if ($error_counter > 0) {
                // Return error file for download with success message
                session()->flash('message', $message);
                return response()->download($error_filepath, $error_filename, [
                    'Content-Type' => 'text/csv',
                ])->deleteFileAfterSend(true);
            } else {
                // Delete empty error file if it exists
                if (file_exists($error_filepath)) {
                    unlink($error_filepath);
                }
                // Return success message
                return redirect()->back()->with('message', $message);
            }
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            dd($e);
            // Log error
            \Log::error("Class Import Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Clean up files
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function SectionImport($data)
    {
        set_time_limit(0);
        $file = $data->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);
        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;
                $success_counter = 0;
                $error_counter = 0;
                $duplication_counter = 0;
                while (($all_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($count > 0) {
                        // dd($all_data);
                        if (empty($all_data[0])) {
                            $error_counter++;
                            continue;
                        }
                        $branch = User::where('id', 2)->first();
                        if (!$branch) {
                            $error_counter++;
                            continue;
                        }
                        $duplicate_user_check = Section::get()->where('name', $all_data[0]);
                        if ($duplicate_user_check->count() > 0) {
                            $error_counter++;
                            $duplication_counter++;
                            continue;
                        }
                        $info = [
                            'name' => $all_data[0],
                            'created_by' => auth()->user()->id,
                            'owned_by' => $branch->id,
                        ];
                        Section::create($info);
                        $success_counter++;
                    }
                    $count++;
                }
                fclose($handle);
            }
            DB::commit();
            return redirect()->back()->with('message', "{$success_counter} Section added successfully. {$error_counter} rows skipped due to Duplication,incomplete or invalid data.");
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
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

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;
                $success_counter = 0;
                $error_counter = 0;
                $duplication_counter = 0;
                while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
                    if ($count > 0) {
                        $error_reason = null;

                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $error_reason = 'Branch name is empty';
                            $error_counter++;
                            $error_records[] = [$count, $all_data[1] ?? 'N/A', $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[0]);
                        $branch = User::where('name', $clean_title)->first();
                        if (!$branch) {
                            $error_reason = 'Branch not found: ' . $clean_title;
                            $error_counter++;
                            $error_records[] = [$count, $all_data[1] ?? 'N/A', $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        // Check if student ID exists
                        if (empty($all_data[1])) {
                            $error_reason = 'Registration number is empty';
                            $error_counter++;
                            $error_records[] = [$count, 'N/A', $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        // Check for duplicate student registration
                        $duplicate_user_check = StudentRegistration::where('id', $all_data[1])->get();
                        if ($duplicate_user_check->count() > 0) {
                            $error_reason = 'Duplicate registration number';
                            $error_counter++;
                            $duplication_counter++;
                            $error_records[] = [$count, $all_data[1], $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        // Set status values
                        $act_status = isset($all_data[25]) && strtoupper($all_data[25]) == 'YES' ? 1 : 0;
                        $std_status = isset($all_data[26]) && strtoupper($all_data[26]) == 'YES' ? 'Enrolled' : 'Registered';

                        // Format date - assuming format is d/m/Y (1/4/2019 = 4 Jan 2019)
                        $regdate = $all_data[8] ?? null;
                        if (!$regdate) {
                            $error_reason = 'Registration date is missing';
                            $error_counter++;
                            $error_records[] = [$count, $all_data[1], $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        try {
                            $timestamp = strtotime($regdate); // Convert string to timestamp
                            if ($timestamp === false) {
                                throw new \Exception("Invalid date format");
                            }
                            $regdate = date('Y-m-d', $timestamp);
                            $created_at = date('Y-m-d H:i:s', $timestamp);
                        } catch (\Exception $e) {
                            $error_reason = 'Invalid date format: ' . $all_data[8];
                            $error_counter++;
                            $error_records[] = [$count, $all_data[1], $all_data[2] ?? 'N/A', $error_reason];
                            $count++;
                            continue;
                        }

                        // Create new student registration
                        $stdreg = new StudentRegistration();
                        $stdreg->reg_no = $all_data[1];
                        $stdreg->regdate = $regdate;
                        $stdreg->stdname = $all_data[2] ?? '';
                        $stdreg->fathername = $all_data[3] ?? '';
                        $stdreg->fatherphone = $all_data[5] ?? '';
                        $stdreg->branch = $branch->id;
                        $stdreg->session_id = 2;
                        $stdreg->active_status = $act_status;
                        $stdreg->student_status = $std_status;
                        $stdreg->registrationfee = $all_data[6] ?? 0;
                        $stdreg->register_option = 1;
                        $stdreg->owned_by = $branch->id;
                        $stdreg->created_by = auth()->user()->id;
                        $stdreg->save();
                        $stdreg->created_at = $created_at;
                        $stdreg->updated_at = $created_at;
                        $stdreg->save();

                        if ($stdreg) {
                            $reg_dis = Registring_option::where('id', 1)
                                ->where('created_by', \Auth::user()->creatorId())
                                ->first();

                            // Create new challan
                            $challan = new Challans();
                            $challan->student_id = $stdreg->id;
                            $challan->class_id = $request->input('class_id') ?? null;
                            $challan->rollno = $stdreg->id;
                            $challan->challanNo = $this->challanNo();
                            $challan->challan_date = $regdate;
                            $challan->challan_type = 'Registration';
                            $challan->total_amount = $reg_dis->discount ?? 0;
                            $challan->paid_amount = $reg_dis->discount ?? 0;
                            $challan->issue_date = $regdate;

                            // Handle due date (one week after registration date)
                            try {
                                $due_date = Carbon::parse($regdate)->addWeek()->toDateString();
                            } catch (\Exception $e) {
                                $due_date = Carbon::now()->addWeek()->toDateString();
                            }

                            $challan->due_date = $due_date;
                            $challan->status = 'Issued';
                            $challan->session_id = $stdreg->session_id;
                            $challan->owned_by = $stdreg->owned_by;
                            $challan->created_by = \Auth::user()->creatorId();
                            $challan->save();
                            $challan->created_at = $created_at;
                            $challan->updated_at = $created_at;
                            $challan->save();

                            // Find bank account
                            $bankAccount = BankAccount::where('owned_by', $challan->owned_by)->first();
                            if (!$bankAccount) {
                                throw new \Exception('Bank account not found for owner ID: ' . $challan->owned_by);
                            }

                            // Create student receipt
                            $recipts = new StudentReceipt();
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
                                throw new \Exception('Registration fee head not found');
                            }

                            // Create challan head
                            $challan_head = new ChallanHead();
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
                                'amount' => $challan->paid_amount,
                                'total' => $challan->paid_amount,
                                'user_type' => 'Student',
                                'category' => 'Registration',
                                'owned_by' => $challan->owned_by,
                                'created_by' => $challan->created_by,
                                'account_id' => $bankAccount->chart_account_id,
                                'items' => $item,
                                'created_at' => $created_at
                            ];

                            // Update registration fee
                            $updateStdreg = StudentRegistration::find($stdreg->id);
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
                        $success_counter++;
                    }
                    $count++;
                }
                fclose($handle);

                // Delete imported file after processing
                if (file_exists($filepath)) {
                    unlink($filepath);
                }

                // Export error records to Excel if any exist
                $error_export_filename = null;
                if (count($error_records) > 1) { // More than just the header row
                    $error_export_filename = 'registration_import_errors_' . date('Y-m-d_H-i-s') . '.xlsx';
                    $this->exportToExcel($error_records, $error_export_filename);
                }

                DB::commit();

                $message = "{$success_counter} Student Registration added successfully. {$error_counter} rows skipped due to duplication, incomplete or invalid data.";

                if ($error_export_filename) {
                    $download_url = asset('assets/export/' . $error_export_filename);
                    $message .= ' <a href="' . $download_url . '" class="btn btn-sm btn-primary">Download Error Report</a>';
                    return redirect()->back()->with('message', $message);
                }

                return redirect()->back()->with('message', $message);
            }

            throw new \Exception("Unable to open file: {$filepath}");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration Import Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function TransferImport($file, $request)
    {
        set_time_limit(0);
        $file = $request->file('excel_file');
        $filename = $file->getClientOriginalName();
        $file->move(public_path('assets/import/csv_file/'), $filename);
        $filepath = public_path('assets/import/csv_file/' . $filename);

        // Initialize counters and error tracking
        $success_counter = 0;
        $error_counter = 0;
        $duplication_counter = 0;
        $error_records = [];
        $error_records[] = ['Row', 'Enrollment ID', 'Registration No', 'Student Name', 'Reason']; // Header row

        DB::beginTransaction();
        try {
            if (($handle = fopen($filepath, 'r')) !== FALSE) {
                $count = 0;

                while (($all_data = fgetcsv($handle, 3500, ",")) !== FALSE) {
                    if ($count > 0) {
                        // Check for empty data in first column
                        if (empty($all_data[0])) {
                            $error_records[] = [$count, 'N/A', $all_data[1] ?? 'N/A', 'N/A', 'Empty enrollment ID'];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        // Validate enrollment
                        $enr = StudentEnrollments::where('enrollId', $all_data[0])->first();
                        if (!$enr) {
                            $error_records[] = [$count, $all_data[0], $all_data[1] ?? 'N/A', 'N/A', 'Enrollment not found'];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        // Get student name from enrollment for better error reporting
                        $student_name = '';
                        if ($enr && $enr->regId) {
                            $reg = StudentRegistration::find($enr->regId);
                            if ($reg) {
                                $student_name = $reg->stdname;
                            }
                        }
                        
                        // Validate registration
                        $reg = StudentRegistration::where('reg_no', $all_data[1])->where('owned_by', $enr->owned_by)->first();
                        if (!$reg) {
                            $error_records[] = [$count, $all_data[0], $all_data[1] ?? 'N/A', $student_name, 'Registration not found'];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        // Clean and validate branch from data (column 5)
                        $clean_title = preg_replace('/\s*\(.*?\)/', '', $all_data[5]);
                        $branchfrom = User::where('name', $clean_title)->first();
                        if (!$branchfrom) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'From branch not found: ' . $all_data[5]];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        // Clean and validate branch to data (column 3)
                        $clean_title_to = preg_replace('/\s*\(.*?\)/', '', $all_data[3]);
                        $branchto = User::where('name', $clean_title_to)->first();
                        if (!$branchto) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'To branch not found: ' . $all_data[3]];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        // Validate class to and from
                        $classto = Classes::where('name', $all_data[4])->where('owned_by', $branchto->id)->first();
                        if (!$classto) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'Class not found: ' . $all_data[1]];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        $classfrom = Classes::where('name', $all_data[6])->where('owned_by', $branchfrom->id)->first();
                        if (!$classfrom) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'Class not found: ' . $all_data[1]];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        
                        // Get first section from source class/branch
                        $sectionClassFrom = ClassSection::where('class_id', $classfrom->id)
                        ->where('owned_by', $branchfrom->id)
                        ->where('active_status', 1)
                        ->first();
                        
                        if (!$sectionClassFrom) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'No active section found for source class'];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        $sectionfrom = Section::find($sectionClassFrom->section_id);
                        
                        // Get first section from destination class/branch
                        $sectionClassTo = ClassSection::where('class_id', $classto->id)
                        ->where('owned_by', $branchto->id)
                        ->where('active_status', 1)
                        ->first();
                        
                        if (!$sectionClassTo) {
                            // if($branchto->)
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'No active section found for destination class'];
                            $error_counter++;
                            $count++;
                            continue;
                        }
                        $sectionto = Section::find($sectionClassTo->section_id);
                        
                        // Check for existing transfer (prevent duplicates)
                        $transf = StudentTransfer::where('student_id', $enr->enrollId)->first();
                        if ($transf) {
                            $error_records[] = [$count, $all_data[0], $all_data[1], $student_name, 'Student already transferred'];
                            $duplication_counter++;
                            $count++;
                            continue;
                        }
                        
                        $transfer_type = isset($all_data[12]) ? trim($all_data[12]) : 'Inter Branch Transfer';
                        $transfer_date = !empty($all_data[6]) ? date('Y-m-d', strtotime($all_data[9])) : date('Y-m-d');

                        // Create challan if inter-branch transfer
                        $challan = null;
                        $item = [];
                        $total = 0;
                        $concession = 0;

                        // if ($transfer_type == 'Inter Branch Transfer') {
                        //     $challan = new Challans();
                        //     $challan->student_id = $enr->regId;
                        //     $challan->rollno = $enr->enrollId;
                        //     $challan->class_id = $classfrom->id;
                        //     $challan->challanNo = $this->challanNo();
                        //     $challan->challan_date = $transfer_date;
                        //     $challan->challan_type = 'Transfer';
                        //     $challan->issue_date = $transfer_date;
                        //     $challan->due_date = $transfer_date; // Using same date for due date
                        //     $challan->status = 'Issued';
                        //     $challan->session_id = $enr->session_id;
                        //     $challan->owned_by = $enr->owned_by;
                        //     $challan->created_by = \Auth::user()->creatorId();
                        //     $challan->save();

                        //     $itemIndex = 0;
                        //     $transfer_fee_amount = !empty($all_data[7]) ? floatval($all_data[7]) : 0;

                        //     // Find transfer fee head
                        //     $pattern = '%transfer fee%';
                        //     $adm_fee_head = FeeHead::whereRaw('LOWER(fee_head) LIKE ?', [strtolower($pattern)])->first();

                        //     if (!$adm_fee_head) {
                        //         throw new \Exception('Transfer fee head not found');
                        //     }
                        //     $fee = StudentFeeStructure::where('head_id', $adm_fee_head->id)
                        //         ->where('reg_id', $enr->regId)
                        //         ->orderBy('id', 'Desc')
                        //         ->first();

                        //     $fee_amount = $fee ? $fee->amount : $transfer_fee_amount;
                        //     $fee_discount = $fee ? $fee->discount : 0;

                        //     // Create challan head
                        //     $challan_head = new ChallanHead();
                        //     $challan_head->challan_id = $challan->id;
                        //     $challan_head->head_id = $adm_fee_head->id;
                        //     $challan_head->price = $fee_amount;
                        //     $challan_head->concession = round(($fee_amount / 100) * $fee_discount);
                        //     $challan_head->save();

                        //     $total += $fee_amount;
                        //     $concession += round(($fee_amount / 100) * $fee_discount);

                        //     // Build item array for journal entry
                        //     $item[$itemIndex]['prod_id'] = $challan_head->id;
                        //     $item[$itemIndex]['head'] = $adm_fee_head->id;
                        //     $item[$itemIndex]['price'] = $fee_amount;
                        //     $item[$itemIndex]['quantity'] = 1;
                        //     $item[$itemIndex]['concession'] = round(($fee_amount / 100) * $fee_discount);
                        //     $item[$itemIndex]['total'] = $fee_amount - round(($fee_amount / 100) * $fee_discount);
                        //     $itemIndex++;

                        //     $challan->total_amount = $total;
                        //     $challan->save();
                        // }

                        $transfer = new StudentTransfer();
                        $transfer->student_id = $enr->enrollId;
                        $transfer->challan_id = $challan ? $challan->id : null;
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
                        dd($all_data,$transfer_date,$transfer_type);
                        // if ($transfer_type == 'Inter Branch Transfer' && $challan) {
                        //     $data = [
                        //         'id' => $challan->id,
                        //         'no' => $challan->challanNo,
                        //         'date' => $challan->challan_date,
                        //         'reference' => $enr->regId,
                        //         'description' => 'Transfer Fee',
                        //         'category' => 'Transfer',
                        //         'user_id' => $enr->regId,
                        //         'user_type' => 'Student',
                        //         'amount' => $total - $concession,
                        //         'total' => $total,
                        //         'owned_by' => $challan->owned_by,
                        //         'created_by' => $challan->created_by,
                        //         'items' => $item
                        //     ];

                        //     $dataret = Utility::jrentry($data);
                        //     $challan->voucher_id = $dataret;
                        //     $challan->save();
                        // }

                        $success_counter++;
                    }
                    $count++;
                }
                fclose($handle);

                // Delete imported file after processing
                if (file_exists($filepath)) {
                    unlink($filepath);
                }

                // Export error records to Excel if any exist
                $error_export_filename = null;
                if (count($error_records) > 1) { // More than just the header row
                    $error_export_filename = 'transfer_import_errors_' . date('Y-m-d_H-i-s') . '.xlsx';
                    $this->exportToExcel($error_records, $error_export_filename);
                }

                DB::commit();

                $message = "{$success_counter} Student Transfer added successfully. " .
                    "{$error_counter} rows skipped due to errors. " .
                    "{$duplication_counter} duplicates found.";

                if ($error_export_filename) {
                    $download_url = asset('assets/export/' . $error_export_filename);
                    $message .= ' <a href="' . $download_url . '" class="btn btn-sm btn-primary">Download Error Report</a>';
                    return redirect()->back()->with('message', $message);
                }

                return redirect()->back()->with('message', $message);
            }

            throw new \Exception("Unable to open file: {$filepath}");

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            \Log::error("Transfer Import Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', "An error occurred: " . $e->getMessage());
        }
    }
    private function exportToExcel($data, $filename)
    {
        // Make sure export directory exists
        $export_dir = public_path('assets/export');
        if (!is_dir($export_dir)) {
            mkdir($export_dir, 0777, true);
        }

        // Create Excel spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add data to worksheet
        $row = 1;
        foreach ($data as $row_data) {
            $col = 1;
            foreach ($row_data as $cell_value) {
                $sheet->setCellValueByColumnAndRow($col, $row, $cell_value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns for better readability
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Style the header row
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFD3D3D3',
                ],
            ],
        ]);

        // Add conditional formatting to highlight error types
        $conditionalStyles = [
            // Red for duplicates
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
            // Yellow for missing data
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
            // Orange for not found references
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];

        $conditionalStyles[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
        $conditionalStyles[0]->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
        $conditionalStyles[0]->setText('Duplicate');
        $conditionalStyles[0]->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $conditionalStyles[0]->getStyle()->getFill()->getStartColor()->setARGB('FFFF9999');

        $conditionalStyles[1]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
        $conditionalStyles[1]->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
        $conditionalStyles[1]->setText('empty');
        $conditionalStyles[1]->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $conditionalStyles[1]->getStyle()->getFill()->getStartColor()->setARGB('FFFFCC99');

        $conditionalStyles[2]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
        $conditionalStyles[2]->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
        $conditionalStyles[2]->setText('not found');
        $conditionalStyles[2]->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $conditionalStyles[2]->getStyle()->getFill()->getStartColor()->setARGB('FFFFCC66');

        // Apply conditional formatting to the error reason column
        $lastRowIndex = count($data);
        $reasonColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($data[0]));
        $sheet->getStyle($reasonColumnLetter . '2:' . $reasonColumnLetter . $lastRowIndex)->setConditionalStyles($conditionalStyles);

        // Create writer and save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($export_dir . '/' . $filename);

        return $filename;
    }
    private function challanNo()
    {
        $latest = Challans::where('created_by', '=', \Auth::user()->creatorId())->orderBY('id', 'desc')->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->challanNo + 1;
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

