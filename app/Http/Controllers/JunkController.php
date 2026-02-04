<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudentHistory;
use App\Models\StudentRegistration;
use App\Models\Challans;
use App\Models\Utility;
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
//     public function deleteReceipts()
// {
//     // Check if already running
//     $lockKey = 'receipt_deletion_nov_2025_lock';
    
//     if (\Cache::has($lockKey)) {
//         \Log::warning('Deletion already running - blocked duplicate request');
//         return redirect()->back()->with('error', 'Deletion process is already running. Please wait.');
//     }
    
//     // Set lock for 30 minutes
//     \Cache::put($lockKey, now(), 1800);
    
//     try {
//         set_time_limit(0);
//         ini_set('memory_limit', '512M');
        
//         \Log::info('==== Receipt Deletion STARTED (Nov 2025) ====');
        
//         // Get challans
//         $challans = \DB::table('challans')
//             ->where('fee_month', 'like', '2025-11%')
//             ->where('status', '!=', 'Issued')
//             ->pluck('id')
//             ->toArray();
        
//         \Log::info('Challans fetched', ['count' => count($challans)]);
        
//         if (empty($challans)) {
//             \Log::info('No challans to process');
//             \Cache::forget($lockKey);
//             return redirect()->back()->with('success', 'No receipts found to delete.');
//         }
        
//         $processedCount = 0;
//         $errorCount = 0;
        
//         // Process one challan at a time
//         foreach ($challans as $challanId) {
//             try {
//                 // Use a transaction for each complete challan
//                 \DB::transaction(function() use ($challanId) {
                    
//                     // Get receipts for this challan
//                     $receipts = \DB::table('student_receipts')
//                         ->where('challan_id', $challanId)
//                         ->get();
                    
//                     if ($receipts->isEmpty()) {
//                         return;
//                     }
                    
//                     foreach ($receipts as $receipt) {
//                         // Delete journal items
//                         \DB::table('journal_items')
//                             ->where('journal', $receipt->voucher_id)
//                             ->delete();
                        
//                         // Delete journal entry
//                         \DB::table('journal_entries')
//                             ->where('id', $receipt->voucher_id)
//                             ->delete();
                        
//                         // Revert bank balance
//                         if (!empty($receipt->bank_id)) {
//                             Utility::bankAccountBalance(
//                                 $receipt->bank_id,
//                                 $receipt->recipt_amount,
//                                 'debit'
//                             );
//                         }
                        
//                         // Delete receipt
//                         \DB::table('student_receipts')
//                             ->where('id', $receipt->id)
//                             ->delete();
//                     }
                    
//                     // Update challan heads
//                     \DB::table('challan_heads')
//                         ->where('challan_id', $challanId)
//                         ->update(['paid' => 0]);
                    
//                     // Update challan
//                     \DB::table('challans')
//                         ->where('id', $challanId)
//                         ->update([
//                             'paid_amount' => 0,
//                             'paid_date' => null,
//                             'status' => 'Issued'
//                         ]);
                        
//                 }, 5); // 5 deadlock retry attempts
                
//                 $processedCount++;
                
//                 if ($processedCount % 10 == 0) {
//                     \Log::info('Progress update', [
//                         'processed' => $processedCount,
//                         'total' => count($challans)
//                     ]);
//                 }
                
//             } catch (\Exception $e) {
//                 $errorCount++;
//                 \Log::error('Challan processing failed', [
//                     'challan_id' => $challanId,
//                     'error' => $e->getMessage()
//                 ]);
                
//                 // Continue with next challan
//                 continue;
//             }
//         }
        
//         \Log::info('==== Receipt Deletion COMPLETED ====', [
//             'total_challans' => count($challans),
//             'processed' => $processedCount,
//             'errors' => $errorCount
//         ]);
        
//         \Cache::forget($lockKey);
        
//         return redirect()->back()->with('success', 
//             "Deletion completed. Processed: {$processedCount}, Errors: {$errorCount}"
//         );
        
//     } catch (\Exception $e) {
//         \Log::error('Fatal error in deletion process', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);
        
//         \Cache::forget($lockKey);
        
//         return redirect()->back()->with('error', 'Deletion failed: ' . $e->getMessage());
//     }
// }

    public function deleteReceipts()
    {
        set_time_limit(0);
        $challanNos = [
            '132747',
        ];
        $challans = Challans::whereIn('challanNo', $challanNos)->get();
        foreach($challans as $challan){
            $receipts = StudentReceipt::where('challan_id', $challan->id)->get();
            foreach($receipts as $receipt){
                $journal = JournalEntry::where('id', $receipt->voucher_id)->delete();
                $journalItem = JournalItem::where('journal', $receipt->voucher_id)->delete();
                // challan heads paid amount
                $challanheads = ChallanHead::where('challan_id', $challan->id)->get();
                foreach($challanheads as $head){
                    $head->paid = 0;
                    $head->save();
                }
                $challan->status = 'Issued';
                $challan->paid_amount = null;
                $challan->paid_date = null;
                $challan->save();
                $receipt->delete();
            }
        }
        return redirect()->back()->with('success', 'Student Receipts Deleted Successfully');
    }
    // public function deleteReceipts()
    // {
    //     set_time_limit(0);
       
    //     $receipts = StudentReceipt::where('recipt_date','like', '%2025-12%')->orWhere('recipt_date','like', '%2026-01%')->get();
    //     // dd($receipts);

    //     foreach($receipts as $receipt){
    //         $journal = JournalEntry::where('id', $receipt->voucher_id)->delete();
    //         $journalItem = JournalItem::where('journal', $receipt->voucher_id)->delete();
    //         $challan = Challans::where('id', $receipt->challan_id)->first();
    //         // challan heads paid amount
    //         $challanheads = ChallanHead::where('challan_id', $challan->id)->get();
    //         foreach($challanheads as $head){
    //             $head->paid = 0;
    //             $head->save();
    //         }
    //         $challan->paid_amount = $challan->paid_amount - $receipt->recipt_amount;
    //         if($challan->paid_amount == 0){
    //             $challan->status = 'Issued';
    //         }else{
    //             $challan->status = 'Partial Paid';
    //         }
    //         $challan->paid_amount = null;
    //         $challan->paid_date = null;
    //         $challan->save();
    //         $receipt->delete();
    //         // dd('Done');
    //     }
    //     return redirect()->back()->with('success', 'Student Receipts Deleted Successfully');
    // }
}
