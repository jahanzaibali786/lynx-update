<?php
// app/Services/PreChallanReportService.php

namespace App\Services;

use App\Models\{
    StudentRegistration,
    StudentFeeStructure,
    Challans,
    ChallanHead,
    Concession,
    ConcessionPolicyHead,
    FeeHead,
    Classes
};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB};

class PreChallanReportService
{
    /**
     * Run the full report calculation and return [reportGroups, heads, averageTuitionFee].
     *
     * @param  int         $branchId   owned_by branch id (required — snapshot is always per branch)
     * @param  string      $dateInput  'Y-m'  e.g. '2026-05'
     * @param  int         $creatorId  company user id
     * @param  array|null  $filters    optional: ['class'=>…, 'section'=>…, 'student'=>…]
     */
    public function calculate(int $branchId, string $dateInput, int $creatorId, array $filters = []): array
    {
        $currentMonth = Carbon::createFromFormat('Y-m', $dateInput);
        $currentYearMonth = $currentMonth->format('Y-m');
        $lastMonthDate = $currentMonth->copy()->subMonth()->format('Y-m-01');
        $lastYearMonth = date('Y-m', strtotime($lastMonthDate));

        // ── Students ──────────────────────────────────────────────────────────
        $baseQ = StudentRegistration::query()
            ->where('student_status', 'Enrolled')
            ->where('owned_by', $branchId);

        if (!empty($filters['class']) && $filters['class'] !== 'all') {
            $baseQ->where('class_id', $filters['class']);
        }
        if (!empty($filters['section']) && $filters['section'] !== 'all') {
            $baseQ->where('section_id', $filters['section']);
        }
        if (!empty($filters['student']) && $filters['student'] !== 'all') {
            $baseQ->where('roll_no', $filters['student']);
        }

        $studentsList = $baseQ->with(['registeroption', 'class', 'section'])->get();
        $studentIds = $studentsList->pluck('id')->all();

        if (empty($studentIds)) {
            return [collect(), collect(), 0];
        }

        // ── Fee structures ────────────────────────────────────────────────────
        $feeStructures = StudentFeeStructure::with('feehead')
            ->whereIn('reg_id', $studentIds)
            ->where('checked_status', 1)
            ->get()
            ->groupBy('reg_id');

        // ── Arrears ───────────────────────────────────────────────────────────
        $arrearsData = Challans::select(
            'student_id',
            DB::raw('SUM(total_amount - paid_amount - concession_amount) AS arrears')
        )
            ->whereIn('student_id', $studentIds)
            ->whereNotIn('challan_type', ['Registration', 'Withdrawal'])
            ->where('status', '!=', 'Paid')
            ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') < ?", [$currentYearMonth])
            ->whereRaw("DATE_FORMAT(fee_month, '%Y-%m') >= '2026-01'")
            ->groupBy('student_id')
            ->pluck('arrears', 'student_id');

        // ── Concessions ───────────────────────────────────────────────────────
        $concessions = Concession::with('concession.policy_head')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'Approved')
            ->where('active_status', '1')
            ->where(function ($q) use ($currentMonth) {
                $q->where('end_date', '>=', $currentMonth->toDateString())
                    ->orWhereNull('end_date');
            })
            ->orderBy('student_id')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');

        $policyHeads = ConcessionPolicyHead::whereIn(
            'concession_id',
            $concessions->pluck('concession_id')->unique()->all()
        )
            ->get()
            ->groupBy('concession_id')
            ->map(fn($rows) => $rows->keyBy('head_id'));

        // ── Fee heads ─────────────────────────────────────────────────────────
        $heads = FeeHead::orderBy('id')->get();

        $tuitionHead = $heads->first(fn($h) => stripos($h->fee_head, 'tuition') !== false);
        $lateFeeHead = $heads->first(
            fn($h) => stripos($h->fee_head, 'late fee') !== false
            || stripos($h->fee_head, 'late-fee') !== false
            || stripos($h->fee_head, 'latefee') !== false
        );

        $alwaysSkipKeywords = ['admission', 'readmission', 'security', 'late fee', 'late-fee', 'latefee'];
        $annualKeywords = ['annual', 'yearly'];

        // ── Previous-month challans ───────────────────────────────────────────
        $prevChallansPass1 = Challans::whereIn('student_id', $studentIds)
            ->whereNotIn('challan_type', ['registration'])
            ->whereRaw("DATE_FORMAT(STR_TO_DATE(fee_month, '%Y-%m-%d'), '%Y-%m') = ?", [$lastYearMonth])
            ->get()->groupBy('student_id');

        $prevChallansPass2 = Challans::whereIn('student_id', $studentIds)
            ->whereNotIn('challan_type', ['registration'])
            ->whereRaw('FIND_IN_SET(?, other_months)', [$lastMonthDate])
            ->get()->groupBy('student_id');

        $prevChallans = $prevChallansPass1->union($prevChallansPass2->diffKeys($prevChallansPass1));
        $prevChallanIds = $prevChallans->flatten()->pluck('id')->all();

        $prevChallanItems = ChallanHead::with('feehead')
            ->whereIn('challan_id', $prevChallanIds)
            ->get()->groupBy('challan_id');

        $parseOtherMonths = function (?string $raw): array {
            if (empty($raw))
                return [];
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        };

        // ── Build rows ────────────────────────────────────────────────────────
        $totalTuitionNet = 0;
        $tuitionStudentCount = 0;

        $reportGroups = $studentsList
            ->sort(function ($a, $b) {
                if ($a->owned_by != $b->owned_by)
                    return $a->owned_by <=> $b->owned_by;
                return strcmp(strtolower($a->stdname ?? ''), strtolower($b->stdname ?? ''));
            })
            ->groupBy('owned_by')
            ->map(function ($groupOfStudents) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads, $prevChallans, $prevChallanItems, $currentMonth, &$totalTuitionNet, &$tuitionStudentCount, $tuitionHead, $lateFeeHead, $alwaysSkipKeywords, $annualKeywords, $parseOtherMonths, $lastMonthDate) {
                return $groupOfStudents
                    ->sortBy(fn($s) => strtolower($s->stdname ?? ''))
                    ->map(function ($student) use ($feeStructures, $arrearsData, $concessions, $policyHeads, $heads, $prevChallans, $prevChallanItems, $currentMonth, &$totalTuitionNet, &$tuitionStudentCount, $tuitionHead, $lateFeeHead, $alwaysSkipKeywords, $annualKeywords, $parseOtherMonths, $lastMonthDate) {
                        $studentFeeStructures = $feeStructures->get($student->id, collect());
                        $concession = $concessions->get($student->id);
                        $concessionPolicyHeads = $concession && isset($policyHeads[$concession->concession_id])
                            ? $policyHeads[$concession->concession_id]
                            : collect();
                        $arrears = $arrearsData->get($student->id, 0);
                        $studentChallans = $prevChallans->get($student->id, collect());

                        $prevChallan = $studentChallans->where('challan_type', 'Regular')->last()
                            ?? $studentChallans->where('challan_type', 'Admission')->last()
                            ?? $studentChallans->last();

                        $challanTypeShort = 'RV';
                        if ($prevChallan) {
                            $months = $parseOtherMonths($prevChallan->other_months);
                            if (count($months) > 1)
                                $challanTypeShort = 'AV';
                        }

                        // ── Advance challan covering current month? ───────────
                        $advanceChallanForCurrentMonth = null;
                        $isFirstMonthAfterChallan = false;

                        if ($prevChallan) {
                            $months = $parseOtherMonths($prevChallan->other_months);
                            if (count($months) > 1) {
                                $currentMonthStr = $currentMonth->format('Y-m-01');
                                if (in_array($currentMonthStr, $months, true)) {
                                    $advanceChallanForCurrentMonth = $prevChallan;
                                    $sortedMonths = $months;
                                    sort($sortedMonths);
                                    $challanFeeMonth = Carbon::parse($prevChallan->fee_month)->format('Y-m-01');
                                    $firstSubsequent = null;
                                    foreach ($sortedMonths as $m) {
                                        if ($m > $challanFeeMonth) {
                                            $firstSubsequent = $m;
                                            break;
                                        }
                                    }
                                    if ($firstSubsequent && $firstSubsequent === $currentMonthStr) {
                                        $isFirstMonthAfterChallan = true;
                                    }
                                }
                            }
                        }

                        // ── Per-head amounts ──────────────────────────────────
                        $totalAmount = $totalDiscount = $totalNet = 0;
                        $headDetails = [];

                        foreach ($heads as $head) {
                            $headNameLower = strtolower($head->fee_head);

                            $skip = false;
                            foreach ($alwaysSkipKeywords as $kw) {
                                if (str_contains($headNameLower, strtolower($kw))) {
                                    $skip = true;
                                    break;
                                }
                            }
                            if ($skip) {
                                $headDetails[$head->id] = ['head_name' => $head->fee_head, 'amount' => 0, 'discount_pct' => 0, 'discount_amount' => 0, 'net_amount' => 0];
                                continue;
                            }

                            $isAnnual = false;
                            foreach ($annualKeywords as $kw) {
                                if (str_contains($headNameLower, $kw)) {
                                    $isAnnual = true;
                                    break;
                                }
                            }

                            $amount = $discount = 0;

                            if ($advanceChallanForCurrentMonth) {
                                if ($isAnnual && !$isFirstMonthAfterChallan) {
                                    $amount = $discount = 0;
                                } else {
                                    $feeStructure = $studentFeeStructures->where('branch_id', $student->owned_by)->firstWhere('head_id', $head->id);
                                    if ($feeStructure) {
                                        $amount = (float) $feeStructure->amount;
                                        $discountPercentage = 0;
                                        if ($amount > 0) {
                                            $discountPercentage = $concessionPolicyHeads->has($head->id)
                                                ? (float) $concessionPolicyHeads->get($head->id)->percentage
                                                : (float) $feeStructure->discount;
                                        }
                                        $discount = round(($amount * $discountPercentage) / 100);
                                    }
                                }
                            } else {
                                $feeStructure = $studentFeeStructures->where('branch_id', $student->owned_by)->firstWhere('head_id', $head->id);
                                if ($feeStructure) {
                                    $amount = (float) $feeStructure->amount;
                                    $discountPercentage = 0;
                                    if ($amount > 0) {
                                        $discountPercentage = $concessionPolicyHeads->has($head->id)
                                            ? (float) $concessionPolicyHeads->get($head->id)->percentage
                                            : (float) $feeStructure->discount;
                                    }
                                    $discount = round(($amount * $discountPercentage) / 100);
                                }
                            }

                            $netAmount = $amount - $discount;
                            $totalAmount += $amount;
                            $totalDiscount += $discount;
                            $totalNet += $netAmount;

                            if ($tuitionHead && $head->id == $tuitionHead->id) {
                                $totalTuitionNet += $netAmount;
                            }

                            $headDetails[$head->id] = [
                                'head_name' => $head->fee_head,
                                'amount' => $amount,
                                'discount_pct' => $amount > 0 ? round(($discount / $amount) * 100, 2) : 0,
                                'discount_amount' => $discount,
                                'net_amount' => $netAmount,
                            ];
                        }

                        // ── Prev month amount ─────────────────────────────────
                        $prevMonthAmount = 0;
                        if ($prevChallan) {
                            $items = $prevChallanItems->get($prevChallan->id, collect());
                            $months = $parseOtherMonths($prevChallan->other_months);
                            $monthCount = max(1, count($months));

                            $includeAnnualInPrev = true;
                            if ($monthCount > 1) {
                                $sortedAdv = $months;
                                sort($sortedAdv);
                                $challanFeeStr = Carbon::parse($prevChallan->fee_month)->format('Y-m-01');
                                $firstSubAdv = null;
                                foreach ($sortedAdv as $m) {
                                    if ($m > $challanFeeStr) {
                                        $firstSubAdv = $m;
                                        break;
                                    }
                                }
                                $includeAnnualInPrev = ($firstSubAdv !== null && $firstSubAdv === $lastMonthDate);
                            }

                            $monthly = $annual = 0;
                            foreach ($items as $item) {
                                $name = strtolower($item->feehead->fee_head ?? '');
                                $skipItem = false;
                                foreach ($alwaysSkipKeywords as $ex) {
                                    if (str_contains($name, strtolower($ex))) {
                                        $skipItem = true;
                                        break;
                                    }
                                }
                                if ($skipItem)
                                    continue;

                                $net = (float) $item->price - (float) ($item->concession ?? 0);
                                $isItemAnnual = false;
                                foreach ($annualKeywords as $kw) {
                                    if (str_contains($name, $kw)) {
                                        $isItemAnnual = true;
                                        break;
                                    }
                                }
                                if ($isItemAnnual) {
                                    if ($includeAnnualInPrev)
                                        $annual += $net;
                                } else
                                    $monthly += $net;
                            }
                            $prevMonthAmount = round(($monthly / $monthCount) + $annual);
                        }

                        // ── Late fee ──────────────────────────────────────────
                        $lateFeeAmount = 0;
                        if ($prevChallan && $lateFeeHead) {
                            $challanStatus = strtolower($prevChallan->status);
                            $lateFeeItem = $prevChallanItems->get($prevChallan->id, collect())->firstWhere('head_id', $lateFeeHead->id);

                            if ($challanStatus == 'partially paid' && $lateFeeItem) {
                                $lateFeeNet = (float) $lateFeeItem->price - (float) ($lateFeeItem->concession ?? 0);
                                $lateFeePaid = (float) ($lateFeeItem->paid_amount ?? 0);
                                if ($lateFeePaid < $lateFeeNet)
                                    $lateFeeAmount = $lateFeeNet - $lateFeePaid;
                            } elseif (in_array($challanStatus, ['issued', 'pending'])) {
                                $dueDate = Carbon::parse($prevChallan->due_date);
                                $today = Carbon::now();
                                if ($dueDate->isSaturday())
                                    $dueDate->addDays(2);
                                elseif ($dueDate->isSunday())
                                    $dueDate->addDay();
                                if ($today->greaterThan($dueDate)) {
                                    $lateDays = 0;
                                    $tempDate = $dueDate->copy()->addDay();
                                    while ($tempDate->lessThanOrEqualTo($today) && $lateDays < 10) {
                                        if (!$tempDate->isWeekend())
                                            $lateDays++;
                                        $tempDate->addDay();
                                    }
                                    $lateFeeAmount = min($lateDays * 120, 1200);
                                }
                            }
                        }

                        $netReceivable = ($totalNet + $arrears + $lateFeeAmount);
                        $difference = $totalNet - $prevMonthAmount;
                        $tuitionStudentCount++;

                        return [
                            'student_id' => $student->id,
                            'roll_no' => $student->roll_no,
                            'student_name' => $student->stdname,
                            'father_name' => $student->fathername,
                            'registration_type' => $student->registeroption->name ?? 'N/A',
                            'challan_type_short' => $challanTypeShort,
                            'class_name' => $student->class->name ?? 'N/A',
                            'section_name' => $student->section->name ?? 'N/A',
                            'concession_category' => $concession && $concession->concession ? $concession->concession->title : 'No Concession',
                            'head_details' => $headDetails,
                            'total_amount' => $totalAmount,
                            'total_discount' => $totalDiscount,
                            'total_net' => $totalNet,
                            'arrears' => $arrears,
                            'late_fee' => $lateFeeAmount,
                            'net_receivable' => $netReceivable,
                            'gross' => $totalAmount - $totalDiscount,
                            'prev_month_amount' => $prevMonthAmount,
                            'difference' => $difference,
                        ];
                    })->values();
            });

        $averageTuitionFee = $tuitionStudentCount > 0
            ? round($totalTuitionNet / $tuitionStudentCount, 2)
            : 0;

        return [$reportGroups, $heads, $averageTuitionFee];
    }
}