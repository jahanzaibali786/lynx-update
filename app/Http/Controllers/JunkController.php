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
use Illuminate\Support\Facades\Log;

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
    //actual
//   public function deleteReceipts()
// {
//     $lockKey = 'receipt_deletion_dec_2025_lock';
    
//     // Check if already running
//     if (\Cache::has($lockKey)) {
//         \Log::warning('Deletion blocked - process already running', [
//             'lock_key' => $lockKey,
//             'timestamp' => now()
//         ]);
//         return redirect()->back()->with('error', 'Deletion process is already running. Please wait.');
//     }
    
//     // Set lock for 30 minutes
//     \Cache::put($lockKey, now(), 1800);
//     \Log::info('Lock acquired', ['lock_key' => $lockKey, 'duration' => '30 minutes']);
    
//     try {
//         set_time_limit(0);
//         ini_set('memory_limit', '512M');
        
//         \Log::info('==== Receipt Deletion Process STARTED ====', [
//             'date' => now(),
//             'target_month' => '2025-12',
//             'memory_limit' => '512M'
//         ]);
        
//         // STEP 1: Clear any previous running states (ONE TIME)
//         \Log::info('STEP 1: Clearing previous running states...');
        
//         $clearedStates = 0;
        
//         // Clear any stuck transactions or locks
//         try {
//             // Clear any old cache locks
//             $oldLocks = \Cache::get('receipt_deletion_previous_locks', []);
//             foreach ($oldLocks as $oldLock) {
//                 \Cache::forget($oldLock);
//                 $clearedStates++;
//             }
            
//             \Log::info('Previous states cleared', [
//                 'cleared_locks' => $clearedStates
//             ]);
            
//         } catch (\Exception $e) {
//             \Log::warning('Error clearing previous states', [
//                 'error' => $e->getMessage()
//             ]);
//         }
        
//         // STEP 2: Fetch target challans
//         \Log::info('STEP 2: Fetching target challans...');
        
//         $challans = \DB::table('challans')
//             ->where('fee_month', 'like', '2025-12%')
//             ->where('status', '!=', 'Issued')
//             ->pluck('id')
//             ->toArray();
        
//         \Log::info('Challans fetched successfully', [
//             'total_count' => count($challans),
//             'challan_ids_sample' => array_slice($challans, 0, 5)
//         ]);
        
//         if (empty($challans)) {
//             \Log::info('No challans found to process - exiting gracefully');
//             \Cache::forget($lockKey);
//             return redirect()->back()->with('success', 'No receipts found to delete.');
//         }
        
//         // STEP 3: Process deletions
//         \Log::info('STEP 3: Starting deletion process...', [
//             'total_challans_to_process' => count($challans)
//         ]);
        
//         $processedCount = 0;
//         $errorCount = 0;
//         $receiptsDeleted = 0;
//         $journalEntriesDeleted = 0;
//         $startTime = microtime(true);
        
//         // Process one challan at a time
//         foreach ($challans as $index => $challanId) {
//             $challanStartTime = microtime(true);
            
//             \Log::info('Processing challan', [
//                 'challan_id' => $challanId,
//                 'progress' => ($index + 1) . '/' . count($challans)
//             ]);
            
//             try {
//                 $challanReceiptsDeleted = 0;
//                 $challanJournalDeleted = 0;
                
//                 // Use a transaction for each complete challan
//                 \DB::transaction(function() use ($challanId, &$challanReceiptsDeleted, &$challanJournalDeleted) {
                    
//                     // Get receipts for this challan
//                     $receipts = \DB::table('student_receipts')
//                         ->where('challan_id', $challanId)
//                         ->get();
                    
//                     \Log::info('Receipts found for challan', [
//                         'challan_id' => $challanId,
//                         'receipt_count' => $receipts->count()
//                     ]);
                    
//                     if ($receipts->isEmpty()) {
//                         \Log::info('No receipts for challan - skipping', [
//                             'challan_id' => $challanId
//                         ]);
//                         return;
//                     }
                    
//                     foreach ($receipts as $receipt) {
//                         \Log::debug('Processing receipt', [
//                             'receipt_id' => $receipt->id,
//                             'voucher_id' => $receipt->voucher_id,
//                             'amount' => $receipt->recipt_amount
//                         ]);
                        
//                         // Delete journal items
//                         $journalItemsDeleted = \DB::table('journal_items')
//                             ->where('journal', $receipt->voucher_id)
//                             ->delete();
                        
//                         \Log::debug('Journal items deleted', [
//                             'receipt_id' => $receipt->id,
//                             'items_deleted' => $journalItemsDeleted
//                         ]);
                        
//                         // Delete journal entry
//                         $journalEntryDeleted = \DB::table('journal_entries')
//                             ->where('id', $receipt->voucher_id)
//                             ->delete();
                        
//                         $challanJournalDeleted += ($journalEntryDeleted + $journalItemsDeleted);
                        
//                         // Revert bank balance
//                         if (!empty($receipt->bank_id)) {
//                             \Log::debug('Reverting bank balance', [
//                                 'receipt_id' => $receipt->id,
//                                 'bank_id' => $receipt->bank_id,
//                                 'amount' => $receipt->recipt_amount
//                             ]);
                            
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
                        
//                         $challanReceiptsDeleted++;
//                     }
                    
//                     // Update challan heads
//                     $headsUpdated = \DB::table('challan_heads')
//                         ->where('challan_id', $challanId)
//                         ->update(['paid' => 0]);
                    
//                     \Log::info('Challan heads updated', [
//                         'challan_id' => $challanId,
//                         'heads_updated' => $headsUpdated
//                     ]);
                    
//                     // Update challan
//                     \DB::table('challans')
//                         ->where('id', $challanId)
//                         ->update([
//                             'paid_amount' => 0,
//                             'paid_date' => null,
//                             'status' => 'Issued'
//                         ]);
                    
//                     \Log::info('Challan status updated to Issued', [
//                         'challan_id' => $challanId
//                     ]);
                        
//                 }, 5); // 5 deadlock retry attempts
                
//                 $processedCount++;
//                 $receiptsDeleted += $challanReceiptsDeleted;
//                 $journalEntriesDeleted += $challanJournalDeleted;
                
//                 $challanElapsed = microtime(true) - $challanStartTime;
                
//                 \Log::info('Challan processed successfully', [
//                     'challan_id' => $challanId,
//                     'receipts_deleted' => $challanReceiptsDeleted,
//                     'journal_entries_deleted' => $challanJournalDeleted,
//                     'time_taken' => round($challanElapsed, 2) . 's'
//                 ]);
                
//                 // Progress update every 10 challans
//                 if ($processedCount % 10 == 0) {
//                     $elapsed = microtime(true) - $startTime;
//                     $avgTime = $elapsed / $processedCount;
//                     $remaining = (count($challans) - $processedCount) * $avgTime;
                    
//                     \Log::info('===== PROGRESS UPDATE =====', [
//                         'processed' => $processedCount,
//                         'total' => count($challans),
//                         'percentage' => round(($processedCount / count($challans)) * 100, 2) . '%',
//                         'errors' => $errorCount,
//                         'receipts_deleted' => $receiptsDeleted,
//                         'journal_entries_deleted' => $journalEntriesDeleted,
//                         'elapsed_time' => round($elapsed, 2) . 's',
//                         'estimated_remaining' => round($remaining, 2) . 's'
//                     ]);
//                 }
                
//             } catch (\Exception $e) {
//                 $errorCount++;
//                 \Log::error('Challan processing FAILED', [
//                     'challan_id' => $challanId,
//                     'error_message' => $e->getMessage(),
//                     'error_code' => $e->getCode(),
//                     'error_line' => $e->getLine(),
//                     'error_file' => $e->getFile()
//                 ]);
                
//                 // Continue with next challan
//                 continue;
//             }
//         }
        
//         $totalElapsed = microtime(true) - $startTime;
        
//         \Log::info('==== Receipt Deletion Process COMPLETED ====', [
//             'total_challans' => count($challans),
//             'successfully_processed' => $processedCount,
//             'errors' => $errorCount,
//             'total_receipts_deleted' => $receiptsDeleted,
//             'total_journal_entries_deleted' => $journalEntriesDeleted,
//             'total_time' => round($totalElapsed, 2) . 's',
//             'average_time_per_challan' => round($totalElapsed / count($challans), 2) . 's',
//             'completion_timestamp' => now()
//         ]);
        
//         \Cache::forget($lockKey);
//         \Log::info('Lock released', ['lock_key' => $lockKey]);
        
//         return redirect()->back()->with('success', 
//             "Deletion completed successfully! Processed: {$processedCount} challans, Receipts deleted: {$receiptsDeleted}, Errors: {$errorCount}"
//         );
        
//     } catch (\Exception $e) {
//         \Log::error('==== FATAL ERROR in Deletion Process ====', [
//             'error_message' => $e->getMessage(),
//             'error_code' => $e->getCode(),
//             'error_line' => $e->getLine(),
//             'error_file' => $e->getFile(),
//             'trace' => $e->getTraceAsString(),
//             'timestamp' => now()
//         ]);
        
//         \Cache::forget($lockKey);
//         \Log::info('Lock released after fatal error', ['lock_key' => $lockKey]);
        
//         return redirect()->back()->with('error', 'Deletion failed: ' . $e->getMessage());
//     }
// }

public function deleteReceipts()
{
    set_time_limit(0);

    Log::info('===== December Challan Deletion Started =====');

    try {

        // DB::transaction(function () {
            $sr = 1;
            $challans = Challans::where('fee_month', 'like', '2025-12-%')
                ->orwhere('fee_month', 'like', '2025-11-%')
                ->whereNotIn('challan_type', ['Admission', 'Registration'])
                ->get();
            // dd($challans);
            Log::info('Challans fetched', [
                'total_challans' => $challans->count()
            ]);

            foreach ($challans as $challan) {
                // dd($challan,$challan->receipts);
                $challan->sr = $sr++;
                Log::info('Processing Challan', [
                    'challan_id' => $challan->id,
                    'voucher_id' => $challan->voucher_id,
                    'fee_month'  => $challan->fee_month
                ]);

                // ========================
                // Delete Receipts
                // ========================
                foreach ($challan->receipts as $receipt) {

                    Log::info('Processing Receipt', [
                        'receipt_id' => $receipt->id,
                        'voucher_id' => $receipt->voucher_id
                    ]);

                    // Delete Journal Items
                    $deletedItems = $receipt->journalItems()->delete();
                    
                    Log::info('Journal Items Deleted (Receipt)', [
                        'receipt_id' => $receipt->id,
                        'deleted_items_count' => $deletedItems
                    ]);
                    $deleteJournal = $receipt->journalEntry()->delete();
                    Log::info('Journal Entry Deleted (Receipt)', [
                        'receipt_id' => $receipt->id,
                        'voucher_id' => $receipt->voucher_id
                    ]);
                    $receipt->delete();

                    Log::info('Receipt Deleted', [
                        'receipt_id' => $receipt->id
                    ]);
                }

                // ========================
                // Delete Challan Voucher
                // ========================

                $deletedChallanItems = $challan->journalItems()->delete();
                
                Log::info('Journal Items Deleted (Challan)', [
                    'challan_id' => $challan->id,
                    'deleted_items_count' => $deletedChallanItems
                ]);

                $deleteJournal = $challan->journalEntry()->delete();
                
                Log::info('Journal Entry Deleted (Challan)', [
                    'challan_id' => $challan->id,
                    'voucher_id' => $challan->voucher_id
                ]);

                // ========================
                // Delete Heads
                // ========================
                $deletedHeads = $challan->heads()->delete();

                Log::info('Challan Heads Deleted', [
                    'challan_id' => $challan->id,
                    'deleted_heads_count' => $deletedHeads
                ]);

                // ========================
                // Delete Challan
                // ========================
                $challan->delete();

                Log::info('Challan Deleted Successfully', [
                    'challan_id' => $challan->id
                ]);

                Log::info('------------------------------------', [
                    'Row done' => $challan->sr
                ]);
            }

        // });

        Log::info('===== December Challan Deletion Completed Successfully =====');

        return redirect()->back()->with('success', 'December Challans Deleted Safely');

    } catch (\Exception $e) {

        Log::error('===== ERROR During December Challan Deletion =====', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);
        dd($e);
        return redirect()->back()->with('error', 'Something went wrong. Check logs.');
    }
}

   public function deleteLateFeeBulk()
{
    set_time_limit(0);
    
    DB::beginTransaction();
    try {
        // Step 1: Get student IDs for Shifa panel (register_option = 2) excluding specific roll numbers
        $studentIds = DB::table('student_registrations')
            ->where('register_option', 2)
            ->whereNotIn('roll_no', [5064, 4825])
            ->pluck('id');
        
        if($studentIds->isEmpty()) {
            DB::rollBack();
            return response()->json(['message' => 'No students found matching criteria'], 404);
        }
        
        // Step 2: Get all challans for these students that have UNPAID late fee
        $challans = Challans::whereIn('student_id', $studentIds)
            ->where('fee_month','like', '2025-11%')
            ->whereHas('heads', function($query) {
                $query->where('head_id', 6)
                      ->where('paid', 0); 
            })
            ->get();
        // dd($challans);
        if($challans->isEmpty()) {
            DB::rollBack();
            return response()->json(['message' => 'No unpaid late fees found'], 404);
        }
        
        $processedCount = 0;
        
        foreach($challans as $challan) {
            // Step 3: Get the UNPAID late fee challan head
            $lateFeeHead = ChallanHead::where('challan_id', $challan->id)
                ->where('head_id', 6)
                ->where('paid', 0)
                ->first();
            // dd($lateFeeHead);
            if($lateFeeHead) {
                // Step 4: Delete the unpaid late fee challan head
                $lateFeeHead->delete();
                
                // Step 5: Recalculate challan amounts based on remaining heads
                $remainingHeads = ChallanHead::where('challan_id', $challan->id)->get();
                
                // Calculate new totals
                $newTotalAmount = 0;
                $newPaidAmount = 0;
                $newConcessionAmount = 0;
                // dd($remainingHeads);
                foreach($remainingHeads as $head) {
                    $headTotal = intval($head->price) - intval($head->concession); // Calculate net amount per head
                    $newTotalAmount += intval($headTotal);
                    $newPaidAmount += intval($head->paid);
                    $newConcessionAmount += intval($head->concession);
                }
                
                // Update challan
                $challan->total_amount = $newTotalAmount;
                $challan->paid_amount = $newPaidAmount;
                $challan->concession_amount = $newConcessionAmount;
                
                // Step 6: Update challan status based on payment
                if($newPaidAmount == 0) {
                    $challan->status = 'Issued';
                    $challan->paid_date = null;
                } elseif($newPaidAmount >= $newTotalAmount && $newTotalAmount > 0) {
                    $challan->status = 'Paid';
                } else {
                    $challan->status = 'Partial';
                }
                
                $challan->save();
                
                // Step 7: Handle journal items if voucher exists
                if($challan->voucher_id) {
                    $journalItems = JournalItem::where('journal', $challan->voucher_id)
                        ->where('head', 6)
                        ->where('types', 'Challan')
                        ->delete(); // Delete journal items for late fee
                }
                
                $processedCount++;
            }
        }
        
        DB::commit();
        
        // Return JSON response instead of redirect to avoid redirect loops
        return response()->json([
            'success' => true,
            'message' => "Unpaid late fees deleted successfully from {$processedCount} challans",
            'processed' => $processedCount
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        dd($e);
        // Return JSON error response
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
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
	public function fixDuplicateChallans()
    {
        try {
            return DB::transaction(function () {

                // Step 1: Get duplicate challans
                $duplicates = DB::table('challans')
                    ->where('fee_month', 'LIKE', '%2026-05%')
                    ->where('owned_by', 17)
                    ->whereIn('challanNo', function ($q) {
                        $q->select('challanNo')
                            ->from('challans')
                            ->where('fee_month', 'LIKE', '%2026-05%')
                            ->groupBy('challanNo')
                            ->havingRaw('COUNT(*) > 1');
                    })
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
				// dd($duplicates);
                if ($duplicates->isEmpty()) {
                    return response('<h3>No duplicate challans found.</h3>', 200)
                        ->header('Content-Type', 'text/html');
                }

                // Step 2: Get max challanNo once (LOCKED)
                $latest = DB::table('challans')
                    ->lockForUpdate()
                    ->orderByRaw('CAST(challanNo AS UNSIGNED) DESC')
                    ->first();

                $currentMax = $latest ? (int) $latest->challanNo : 0;

                $updatedData = [];

                // Step 3: Update records
                foreach ($duplicates as $challan) {

                    $oldChallanNo = $challan->challanNo;

                    $currentMax++;
                    $newChallanNo = $currentMax;

                    DB::table('challans')
                        ->where('id', $challan->id)
                        ->update(['challanNo' => $newChallanNo]);

                    $updatedData[] = [
                        'id' => $challan->id,
                        'rollno' => $challan->rollno,
                        'old' => $oldChallanNo,
                        'new' => $newChallanNo,
                    ];
                }

                // Step 4: Build HTML table
                $html = '
            <html>
            <head>
                <title>Challan Fix Report</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                    th { background-color: #f4f4f4; }
                </style>
            </head>
            <body>
                <h2>Duplicate Challan Fix Report (2026-05 | Branch 17)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Roll No</th>
                            <th>Old Challan No</th>
                            <th>New Challan No</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($updatedData as $row) {
                    $html .= '
                    <tr>
                        <td>' . $row['id'] . '</td>
                        <td>' . $row['rollno'] . '</td>
                        <td>' . $row['old'] . '</td>
                        <td>' . $row['new'] . '</td>
                    </tr>';
                }

                $html .= '
                    </tbody>
                </table>
            </body>
            </html>';

                return response($html)->header('Content-Type', 'text/html');
            });

        } catch (\Exception $e) {
            return response('<h3>Error: ' . $e->getMessage() . '</h3>', 500)
                ->header('Content-Type', 'text/html');
        }
    }
}
