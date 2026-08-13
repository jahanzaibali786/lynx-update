<?php

namespace App\Services;

use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\FeeHead;
use App\Models\JournalItem;
use App\Models\LateFeeRemovalLog;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RemoveLateFeeService
{
    public function removeLateFee(string $challanNo, float $removeAmount): array
    {
        return DB::transaction(function () use ($challanNo, $removeAmount) {
            // Lock the challan for update to prevent concurrent modifications
            $challan = Challans::where('challanNo', $challanNo)
                ->lockForUpdate()
                ->first();

            if (!$challan) {
                throw new Exception('Challan not found.');
            }

            // Get the Late Fee FeeHead record
            $lateFeeFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();

            if (!$lateFeeFeeHead) {
                throw new Exception('Late Fee FeeHead not found.');
            }

            // Find the Late Fee head by matching head_id
            $lateFeeHead = ChallanHead::where('challan_id', $challan->id)
                ->where('head_id', $lateFeeFeeHead->id)
                ->lockForUpdate()
                ->first();

            if (!$lateFeeHead) {
                throw new Exception('Late Fee head not found in this challan.');
            }

            // Calculate outstanding amount
            $outstandingAmount = $lateFeeHead->price - $lateFeeHead->concession - $lateFeeHead->paid;

            if ($outstandingAmount <= 0) {
                throw new Exception('Late Fee is already fully paid. Cannot remove.');
            }

            if ($removeAmount > $outstandingAmount) {
                throw new Exception('Remove amount cannot exceed outstanding Late Fee amount.');
            }

            // Store old values for audit log
            $oldAmount = $lateFeeHead->price;
            $oldTotalAmount = $challan->total_amount;

            // Handle complete removal
            if ($removeAmount == $outstandingAmount) {
                $this->handleCompleteRemoval($challan, $lateFeeHead, $removeAmount);
            } else {
                // Handle partial removal
                $this->handlePartialRemoval($challan, $lateFeeHead, $removeAmount);
            }

            // Recalculate challan status
            $this->recalculateChallanStatus($challan);

            // Create audit log
            $this->createAuditLog($challan, $lateFeeHead, $oldAmount, $removeAmount, $oldTotalAmount);

            return [
                'success' => true,
                'message' => 'Late Fee removed successfully.',
                'challan_no' => $challan->challanNo,
                'removed_amount' => $removeAmount,
                'new_late_fee_price' => $lateFeeHead->price,
                'new_total_amount' => $challan->total_amount,
                'status' => $challan->status,
            ];
        });
    }

    private function handleCompleteRemoval(Challans $challan, ChallanHead $lateFeeHead, float $removeAmount): void
    {
        $lateFeeHeadId = $lateFeeHead->head_id;

        // If there's a paid amount, set the price to the paid amount (base amount)
        // Otherwise, delete the head completely
        if ($lateFeeHead->paid > 0) {
            $lateFeeHead->price = $lateFeeHead->paid;
            $lateFeeHead->concession = 0;
            $lateFeeHead->save();

            // Adjust journal items to match the new price
            $journalItems = JournalItem::where('journal', $challan->voucher_id)
                ->where('types', 'Challan')
                ->where('head', $lateFeeHeadId)
                ->get();
            if ($journalItems->count() > 0) {
                $journalItems->each(function($journalItem) use ($lateFeeHead) {
                    // Only update the side that is not zero
                    if ($journalItem->debit > 0) {
                        $journalItem->debit = $lateFeeHead->price;
                    }
                    if ($journalItem->credit > 0) {
                        $journalItem->credit = $lateFeeHead->price;
                    }
                    $journalItem->save();
                });
            }
        } else {
            // No paid amount, delete the head completely
            $lateFeeHead->delete();

            // Delete related journal items
            $journalItems = JournalItem::where('journal', $challan->voucher_id)
                ->where('types', 'Challan')
                ->where('head', $lateFeeHeadId)
                ->get();
            if ($journalItems->count() > 0) {
                $journalItems->each->delete();
            }
        }
    }

    private function handlePartialRemoval(Challans $challan, ChallanHead $lateFeeHead, float $removeAmount): void
    {
        // Reduce the Late Fee head price by the entered amount
        $lateFeeHead->price = max($lateFeeHead->paid + $lateFeeHead->concession, $lateFeeHead->price - $removeAmount);
        $lateFeeHead->save();

        // Find and reduce the related Challan journal item amount
        $journalItems = JournalItem::where('journal', $challan->voucher_id)
            ->where('types', 'Challan')
            ->where('head', $lateFeeHead->head_id)
            ->get();
        if ($journalItems->count() > 0) {
            $journalItems->each(function($journalItem) use ($lateFeeHead) {
                // Only update the side that is not zero
                if ($journalItem->debit > 0) {
                    $journalItem->debit = $lateFeeHead->price;
                }
                if ($journalItem->credit > 0) {
                    $journalItem->credit = $lateFeeHead->price;
                }
                $journalItem->save();
            });
        }
    }

    private function recalculateChallanStatus(Challans $challan): void
    {
        // Recalculate challan totals based on heads
        $heads = ChallanHead::where('challan_id', $challan->id)->get();
        
        $challan->total_amount = $heads->sum('price');
        $challan->paid_amount = $heads->sum('paid');
        $challan->concession_amount = $heads->sum('concession');

        // Recalculate status
        if ($challan->paid_amount >= ($challan->total_amount - $challan->concession_amount)) {
            $challan->status = 'Paid';
        } elseif ($challan->paid_amount > 0) {
            $challan->status = 'Partial Paid';
        } else {
            $challan->status = 'Issued';
        }
        $challan->save();
    }

    private function createAuditLog(Challans $challan, ChallanHead $lateFeeHead, float $oldAmount, float $removedAmount, float $oldTotalAmount): void
    {
        LateFeeRemovalLog::create([
            'challan_id' => $challan->id,
            'challan_no' => $challan->challanNo,
            'challan_head_id' => $lateFeeHead->id,
            'old_amount' => $oldAmount,
            'removed_amount' => $removedAmount,
            'updated_amount' => $lateFeeHead->price,
            'old_total_amount' => $oldTotalAmount,
            'new_total_amount' => $challan->total_amount,
            'user_id' => Auth::id(),
            'removal_date' => now(),
        ]);
    }

    public function searchChallan(string $challanNo): ?array
    {
        $challan = Challans::where('challanNo', $challanNo)
            ->with(['heads.feeHead', 'student', 'class', 'receipts.journalItems','receipts.journalItems.heads'])
            ->first();

        if (!$challan) {
            return null;
        }

        // Get the Late Fee FeeHead record
        $lateFeeFeeHead = FeeHead::where('fee_head', 'LATE FEE')->first();

        if (!$lateFeeFeeHead) {
            return [
                'challan' => $challan,
                'heads' => $challan->heads,
                'receipts' => $challan->receipts ?? [],
                'late_fee_head' => null,
                'has_late_fee' => false,
            ];
        }

        // Find the ChallanHead that matches the Late Fee head_id
        $lateFeeHead = $challan->heads->first(function ($head) use ($lateFeeFeeHead) {
            return $head->head_id === $lateFeeFeeHead->id;
        });

        if (!$lateFeeHead) {
            return [
                'challan' => $challan,
                'heads' => $challan->heads,
                'receipts' => $challan->receipts ?? [],
                'late_fee_head' => null,
                'has_late_fee' => false,
            ];
        }

        $outstandingAmount = $lateFeeHead->price - $lateFeeHead->concession - $lateFeeHead->paid;

        return [
            'challan' => $challan,
            'heads' => $challan->heads,
            'receipts' => $challan->receipts ?? [],
            'late_fee_head' => $lateFeeHead,
            'has_late_fee' => true,
            'outstanding_amount' => $outstandingAmount,
        ];
    }
}
