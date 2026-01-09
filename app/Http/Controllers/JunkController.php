<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\Challans;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ChallanHead;
use App\Models\StudentReceipt;
use App\Models\StudentWithdrawal;
use Illuminate\Support\Facades\DB;

class JunkController extends Controller
{
    public function maintainEnrollHis()
    {
        DB::beginTransaction();
        try {
            $enrollments = StudentEnrollments::get();
            foreach ($enrollments as $enrollment) {
                // Skip if history already exists
                if (
                    StudentHistory::where('reg_id', $enrollment->regId)
                        ->where('student_id', $enrollment->enrollId)
                        ->where('event_type', 'enroll')
                        ->exists()
                ) {
                    continue;
                }

                $his = new StudentHistory();
                $his->reg_id = $enrollment->regId;
                $his->student_id = $enrollment->enrollId;
                $his->event_type = 'enroll';
                $his->from_session_id = $enrollment->adm_session;
                if ($enrollment->active_status == 1 && !empty($enrollment->class_id)) {
                    $his->from_class_id = $enrollment->class_id;
                    $his->to_class_id = $enrollment->class_id;
                } else {
                    $his->from_class_id = null;
                    $his->to_class_id = null;
                }
                if ($enrollment->active_status == 1 && !empty($enrollment->section_id)) {
                    $his->from_section_id = $enrollment->section_id;
                    $his->to_section_id = $enrollment->section_id;
                } else {
                    $his->from_section_id = null;
                    $his->to_section_id = null;
                }
                $his->from_branch_id = $enrollment->owned_by;
                $his->to_branch_id = $enrollment->owned_by;
                $his->to_session_id = $enrollment->session_id;
                $his->effective_date = $enrollment->adm_date;
                $his->remarks = 'Bulk Enrollment';
                $his->owned_by = $enrollment->owned_by;
                $his->created_by = $enrollment->created_by;
                $his->save();
            }
            DB::commit();
            return redirect()->back()->with('success', 'Student Enrollment History Created Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e, $enrollment);
            return redirect()->back()->with('error', 'Failed to Create Student Enrollment History');
        }
    }
    public function HisStatus()
    {
        $enrollments = StudentEnrollments::get();
        foreach ($enrollments as $enrollment) {
            $stdhis = StudentHistory::where('reg_id', $enrollment->regId)
                ->where('student_id', $enrollment->enrollId)
                ->where('event_type', 'enroll')
                ->first();
            if($stdhis){
                $stdhis->status = $enrollment->active_status;
                $stdhis->save();
            }
        }
        return redirect()->back()->with('success', 'Student Enrollment History Status Updated Successfully');
    }
    public function Regchange()
    {
        set_time_limit(0);
        $co = 0;
        $registrations = StudentWithdrawal::skip(1000)->take(500)->get();
        foreach($registrations as $reg){
            $secondChallan = StudentRegistration::where('id', $reg->student_id)->first();
            if($secondChallan){
                if($secondChallan->student_status == 'Enrolled'){
                    $co++;
                    $reg->delete();
                    continue;
                    dd('as',$secondChallan,$reg);
                    
                }else{
                     $secondChallan->active_status = 0;
                            $enr = StudentEnrollments::where('regId', $secondChallan->id)->first();
                            if(!$enr){
                                $co++;
                                $reg->delete();
                                continue;
                                dd($secondChallan,$enr);
                            }else{
                                $secondChallan->student_status = 'withdrawal';
                                $secondChallan->save();
                                $enr->active_status = 0;
                                $enr->save();
                            }
                }
           
            }else{
                // $co++;
                continue;

            }
        }
        // $registrations = StudentRegistration::skip(4100)->take(50)->get();
        // foreach($registrations as $reg){
        //     $secondChallan = Challans::where('rollno', $reg->roll_no)->get();
        //     foreach($secondChallan as $cha){
        //         $cha->student_id = $reg->id;
        //         $cha->save();
        //     }
        // }
        dd('Done',$co);
        // $registrations = StudentRegistration::get();
        // foreach ($registrations as $registration) {
        //     $stdhis = StudentEnrollments::where('regId', $registration->reg_no)
        //         ->where('enrollId', $registration->roll_no)
        //         ->first();
        //     if($stdhis){
        //         $stdhis->regId = $registration->id;
        //         $stdhis->save();
        //     }
        // }
        return redirect()->back()->with('success', 'Student Enrollment History Status Updated Successfully');
    }
    public function deleteReceipts()
    {
        set_time_limit(0);
       
        $receipts = StudentReceipt::where('recipt_date','like', '%2025-12%')->orWhere('recipt_date','like', '%2026-01%')->get();
        // dd($receipts);

        foreach($receipts as $receipt){
            $journal = JournalEntry::where('id', $receipt->voucher_id)->delete();
            $journalItem = JournalItem::where('journal', $receipt->voucher_id)->delete();
            $challan = Challans::where('id', $receipt->challan_id)->first();
            // challan heads paid amount
            $challanheads = ChallanHead::where('challan_id', $challan->id)->get();
            foreach($challanheads as $head){
                $head->paid = 0;
                $head->save();
            }
            $challan->paid_amount = $challan->paid_amount - $receipt->recipt_amount;
            if($challan->paid_amount == 0){
                $challan->status = 'Issued';
            }else{
                $challan->status = 'Partial Paid';
            }
            $challan->paid_amount = null;
            $challan->paid_date = null;
            $challan->save();
            $receipt->delete();
            // dd('Done');
        }
        return redirect()->back()->with('success', 'Student Receipts Deleted Successfully');
    }
}
