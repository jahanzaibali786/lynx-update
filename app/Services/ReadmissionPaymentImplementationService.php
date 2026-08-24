<?php

namespace App\Services;

use App\Models\Challans;
use App\Models\StudentEnrollments;
use App\Models\StudentFeeRevisionBatch;
use App\Models\StudentFeeRevisionItem;
use App\Models\StudentFeeStructure;
use App\Models\StudentHistory;
use App\Models\StudentReadmission;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\StudentWithdrawal;
use Carbon\Carbon;

class ReadmissionPaymentImplementationService
{
    /**
     * Apply Readmission/Re-enrollment implementation on the FIRST positive
     * receipt of the application main challan.
     *
     * IMPORTANT:
     * - Rs. 1 is enough.
     * - Full payment is NOT required.
     * - Readmission trigger challan: Re-Admission.
     * - Re-enrollment trigger challan: Admission.
     * - The operation is idempotent through approval_snapshot.implementation.
     *
     * Returns true when the challan belongs to an approved Readmission /
     * Re-enrollment application (including already-applied applications).
     * Returns false for ordinary challans.
     */
    public function applyOnFirstReceipt(
        Challans $challan,
        StudentReceipt $receipt
    ): bool {
        if ((float) ($receipt->recipt_amount ?? 0) <= 0) {
            return false;
        }

        /*
         * Main challan is stored directly in student_readmissions.challan_id.
         * Gap Regular challans are stored in challan_ids only and therefore
         * never trigger the student implementation.
         */
        $record = StudentReadmission::where(
            'challan_id',
            $challan->id
        )
            ->lockForUpdate()
            ->first();

        if (!$record) {
            return false;
        }

        $snapshot = $this->decodeJson($record->snapshot);

        if (empty($snapshot)) {
            /*
             * This service is for the new snapshot workflow.
             * Do not silently mutate old/legacy applications.
             */
            return false;
        }

        $flowType = strtolower(
            (string) (
                $snapshot['flow']['type']
                ?? $record->flow_type
                ?? ''
            )
        );

        if (!in_array($flowType, ['readmission', 're_enrollment'], true)) {
            return false;
        }

        /*
         * Only an HO-approved application can be implemented.
         */
        if (strtolower(trim((string) $record->status)) !== 'approved') {
            return false;
        }

        /*
         * Make sure payment is on the correct main challan type.
         */
        $challanType = strtolower(
            trim((string) $challan->challan_type)
        );

        $validMainChallan = $flowType === 'readmission'
            ? in_array(
                $challanType,
                ['re-admission', 'readmission'],
                true
            )
            : $challanType === 'admission';

        if (!$validMainChallan) {
            return false;
        }

        $approvalSnapshot = $this->decodeJson(
            $record->approval_snapshot
        );

        /*
         * Hard idempotency guard.
         * A second/third receipt must never duplicate histories,
         * promotion rows or fee revision rows.
         */
        if (
            !empty(
                $approvalSnapshot['implementation']['applied_at']
                ?? null
            )
        ) {
            return true;
        }

        $studentRegId = $snapshot['student']['reg_id'] ?? null;

        $student = StudentRegistration::findOrFail(
            $studentRegId
        );

        $enrollment = StudentEnrollments::where(
            'regId',
            $student->id
        )
            ->orderByDesc('id')
            ->first();

        $source = $snapshot['source_placement'] ?? [];
        $target = $snapshot['target_placement'] ?? [];

        $selectedHeadIds = collect(
            $snapshot['selected_head_ids'] ?? []
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $beforeStructure = $this->studentFeeStructureState(
            $student->id
        );

        /*
        |--------------------------------------------------------------------------
        | 1. APPLY STUDENT FEE STRUCTURE
        |--------------------------------------------------------------------------
        |
        | Readmission:
        | - keep the student's continuing structure;
        | - move branch/class metadata;
        | - apply approved Tuition revision.
        |
        | Re-enrollment:
        | - attach/update approved target structure.
        |
        */
        if ($flowType === 'readmission') {
            $this->applyReadmissionStudentStructureRevision(
                $student,
                $target,
                $snapshot['fee_revision'] ?? []
            );
        } else {
            $this->attachStructureFromSnapshot(
                $student,
                $target,
                $snapshot['target_fee_structure'] ?? [],
                $selectedHeadIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. READMIT / RE-ENROLL STUDENT ON RECEIPT DATE
        |--------------------------------------------------------------------------
        */
        $receiptDate = Carbon::parse(
            $receipt->recipt_date
                ?? $challan->paid_date
                ?? now()
        )->toDateString();

        StudentRegistration::where(
            'id',
            $student->id
        )->update([
            'class_id' => $target['class_id']
                ?? $student->class_id,
            'student_status' => 'Enrolled',
            'active_status' => 1,
            'session_id' => $target['session_id']
                ?? $student->session_id,
            'owned_by' => $target['branch_id']
                ?? $student->owned_by,
            'branch' => $target['branch_id']
                ?? $student->branch,
        ]);

        $enrollment = $this->activateEnrollment(
            $student,
            $enrollment,
            $target,
            $receiptDate,
            $record
        );

        /*
        |--------------------------------------------------------------------------
        | 3. CLOSE WITHDRAWAL
        |--------------------------------------------------------------------------
        */
        $withdrawalId = $snapshot['withdrawal']['id']
            ?? null;

        if ($withdrawalId) {
            StudentWithdrawal::where(
                'id',
                $withdrawalId
            )->update([
                'status' => 'readmitted',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. PROMOTION / TRANSFER / FEE REVISION AUDIT
        |--------------------------------------------------------------------------
        |
        | These records are now created on FIRST RECEIPT, not on HO approval.
        |
        */
        $this->recordReadmissionPromotionAndFeeRevision(
            $student,
            $enrollment,
            $record,
            $source,
            $target,
            $snapshot,
            $flowType,
            $beforeStructure,
            $receiptDate
        );

        /*
        |--------------------------------------------------------------------------
        | 5. READMISSION / RE-ENROLLMENT HISTORY
        |--------------------------------------------------------------------------
        */
        $studentReference = optional($enrollment)->enrollId
            ?: $student->roll_no
            ?: $student->id;

        $history = new StudentHistory();
        $history->reg_id = $student->id;
        $history->student_id = $studentReference;
        $history->event_type = $flowType === 're_enrollment'
            ? 'Re-enrollment'
            : 'Readmission';
        $history->from_session_id =
            $source['session_id'] ?? null;
        $history->from_class_id =
            $source['class_id'] ?? null;
        $history->from_section_id =
            $source['section_id'] ?? null;
        $history->from_branch_id =
            $source['branch_id'] ?? null;
        $history->to_session_id =
            $target['session_id'] ?? null;
        $history->to_class_id =
            $target['class_id'] ?? null;
        $history->to_section_id =
            $target['section_id'] ?? null;
        $history->to_branch_id =
            $target['branch_id'] ?? null;

        /*
         * Student actually becomes readmitted/re-enrolled on payment.
         */
        $history->effective_date = $receiptDate;

        $history->remarks =
            ($snapshot['request']['reason']
                ?? $record->remarks)
            . ' | Implemented on first receipt #'
            . $receipt->id;

        $history->owned_by =
            $target['branch_id']
            ?? $record->owned_by;

        $history->created_by =
            $record->created_by
            ?: $student->created_by;

        $history->save();

        /*
        |--------------------------------------------------------------------------
        | 6. MARK APPLICATION IMPLEMENTED
        |--------------------------------------------------------------------------
        */
        $approvalSnapshot['implementation'] = [
            'status' => 'applied',
            'trigger' => 'first_positive_receipt',
            'applied_at' => now()->toISOString(),
            'receipt_id' => (int) $receipt->id,
            'receipt_date' => $receiptDate,
            'receipt_amount' => (float) $receipt->recipt_amount,
            'challan_id' => (int) $challan->id,
            'challan_no' => $challan->challanNo,
            'challan_type' => $challan->challan_type,
            'applied_by' => [
                'id' => \Auth::id(),
                'name' => optional(\Auth::user())->name,
            ],
        ];

        $record->approval_snapshot = json_encode(
            $approvalSnapshot
        );

        $record->save();

        return true;
    }

    private function activateEnrollment(
        StudentRegistration $student,
        $enrollment,
        array $target,
        string $receiptDate,
        StudentReadmission $record
    ) {
        /*
         * Normal case: withdrawn student already has an enrollment row.
         */
        if ($enrollment) {
            $enrollment->owned_by =
                $target['branch_id']
                ?? $enrollment->owned_by;

            $enrollment->active_status = 1;

            $enrollment->class_id =
                $target['class_id']
                ?? $enrollment->class_id;

            $enrollment->section_id =
                $target['section_id']
                ?? $enrollment->section_id;

            $enrollment->session_id =
                $target['session_id']
                ?? $enrollment->session_id;

            /*
             * Readmission / Re-enrollment becomes effective on payment.
             */
            $enrollment->adm_date = $receiptDate;

            if (isset($enrollment->adm_session)) {
                $enrollment->adm_session =
                    $target['session_id']
                    ?? $enrollment->adm_session;
            }

            if (isset($enrollment->adm_branch)) {
                $enrollment->adm_branch =
                    $target['branch_id']
                    ?? $enrollment->adm_branch;
            }

            $enrollment->save();

            return $enrollment;
        }

        /*
         * Fallback for a historical withdrawn registration that no longer has
         * an enrollment row. Do not block implementation.
         */
        $prevEnrollId = StudentEnrollments::orderByDesc(
            'enrollId'
        )->value('enrollId');

        $newEnrollId = $prevEnrollId
            ? ((int) $prevEnrollId + 1)
            : 1;

        $enrollment = new StudentEnrollments();
        $enrollment->enrollId = $newEnrollId;
        $enrollment->regId = $student->id;
        $enrollment->class_id =
            $target['class_id']
            ?? $student->class_id;
        $enrollment->section_id =
            $target['section_id']
            ?? $student->section_id;
        $enrollment->session_id =
            $target['session_id']
            ?? $student->session_id;
        $enrollment->adm_date = $receiptDate;
        $enrollment->adm_session =
            $target['session_id']
            ?? $student->session_id;
        $enrollment->adm_branch =
            $target['branch_id']
            ?? $student->owned_by;
        $enrollment->owned_by =
            $target['branch_id']
            ?? $record->owned_by;
        $enrollment->active_status = 1;
        $enrollment->created_by =
            $record->created_by
            ?: $student->created_by;
        $enrollment->save();

        /*
         * Only assign a new roll number when the registration has none.
         * Existing Readmission/Re-enrollment identity is preserved.
         */
        if (empty($student->roll_no)) {
            $student->roll_no = $newEnrollId;
            $student->save();
        }

        return $enrollment;
    }

    private function studentFeeStructureState($studentId)
    {
        return StudentFeeStructure::where(
            'reg_id',
            $studentId
        )
            ->get()
            ->keyBy('head_id')
            ->map(function ($row) {
                $amount = (float) ($row->amount ?? 0);
                $discount = (float) ($row->discount ?? 0);

                return [
                    'id' => (int) $row->id,
                    'head_id' => (int) $row->head_id,
                    'amount' => $amount,
                    'discount' => $discount,
                    'checked_status' =>
                        (int) ($row->checked_status ?? 0),
                    'is_custom' =>
                        (int) ($row->is_custom ?? 0),
                    'payable_amount' => round(
                        $amount
                        - (($amount * $discount) / 100),
                        2
                    ),
                ];
            });
    }

    private function applyReadmissionStudentStructureRevision(
        StudentRegistration $student,
        array $targetPlacement,
        array $feeRevision
    ): void {
        $branchId = (int) (
            $targetPlacement['branch_id']
            ?? $student->owned_by
        );

        $classId = (int) (
            $targetPlacement['class_id']
            ?? $student->class_id
        );

        /*
         * One continuing StudentFeeStructure for the student.
         * No session filter.
         */
        StudentFeeStructure::where(
            'reg_id',
            $student->id
        )->update([
            'owned_by' => $branchId,
            'branch_id' => $branchId,
            'class_id' => $classId,
        ]);

        if (
            empty($feeRevision['session_changed'])
            || empty($feeRevision['enabled'])
            || empty($feeRevision['head_id'])
        ) {
            return;
        }

        $structure = StudentFeeStructure::where(
            'reg_id',
            $student->id
        )
            ->where(
                'head_id',
                (int) $feeRevision['head_id']
            )
            ->orderByDesc('id')
            ->first();

        if (!$structure) {
            return;
        }

        $structure->amount = (float) (
            $feeRevision['new_base_amount']
            ?? $structure->amount
        );

        $structure->owned_by = $branchId;
        $structure->branch_id = $branchId;
        $structure->class_id = $classId;

        if (
            (int) ($feeRevision['percentage'] ?? 0) !== 0
        ) {
            $structure->is_custom = 1;
        }

        $structure->save();
    }

    private function attachStructureFromSnapshot(
        StudentRegistration $student,
        array $targetPlacement,
        array $targetRows,
        array $selectedHeadIds
    ): void {
        $branchId = (int) (
            $targetPlacement['branch_id']
            ?? $student->owned_by
        );

        $classId = (int) (
            $targetPlacement['class_id']
            ?? $student->class_id
        );

        $selectedHeadIds = array_map(
            'intval',
            $selectedHeadIds
        );

        StudentFeeStructure::where(
            'reg_id',
            $student->id
        )->update([
            'checked_status' => 0,
        ]);

        foreach ($targetRows as $row) {
            $headId = (int) (
                $row['head_id'] ?? 0
            );

            if (!$headId) {
                continue;
            }

            $structure = StudentFeeStructure::where(
                'reg_id',
                $student->id
            )
                ->where('head_id', $headId)
                ->orderByDesc('id')
                ->first();

            if (!$structure) {
                $structure = new StudentFeeStructure();
                $structure->reg_id = $student->id;
                $structure->student_id =
                    $student->roll_no;
                $structure->head_id = $headId;
                $structure->created_by =
                    $student->created_by;
            }

            $structure->branch_id = $branchId;
            $structure->class_id = $classId;
            $structure->owned_by = $branchId;

            $structure->amount = (float) (
                $row['charge_amount']
                ?? $row['class_amount']
                ?? 0
            );

            $structure->discount = (float) (
                $row['base_discount']
                ?? 0
            );

            $structure->checked_status = in_array(
                $headId,
                $selectedHeadIds,
                true
            ) ? 1 : 0;

            if (
                ($row['charge_source'] ?? '')
                    === 'student_fee_structure_revision'
                && (int) (
                    $row['increment_percentage'] ?? 0
                ) !== 0
            ) {
                $structure->is_custom = 1;
            }

            $structure->save();
        }

        StudentFeeStructure::where(
            'reg_id',
            $student->id
        )->update([
            'owned_by' => $branchId,
            'branch_id' => $branchId,
            'class_id' => $classId,
        ]);
    }

    private function recordReadmissionPromotionAndFeeRevision(
        StudentRegistration $student,
        $enrollment,
        StudentReadmission $record,
        array $source,
        array $target,
        array $snapshot,
        string $flowType,
        $beforeStructure,
        string $implementationDate
    ): void {
        $sessionChanged =
            (int) ($source['session_id'] ?? 0)
            !== (int) ($target['session_id'] ?? 0);

        $classChanged =
            (int) ($source['class_id'] ?? 0)
            !== (int) ($target['class_id'] ?? 0);

        $sectionChanged =
            (int) ($source['section_id'] ?? 0)
            !== (int) ($target['section_id'] ?? 0);

        $branchChanged =
            (int) ($source['branch_id'] ?? 0)
            !== (int) ($target['branch_id'] ?? 0);

        if (
            !$sessionChanged
            && !$classChanged
            && !$sectionChanged
            && !$branchChanged
        ) {
            return;
        }

        /*
         * Promotion/placement becomes effective on first payment.
         */
        $effectiveDate = $implementationDate;

        /*
         * Fee increment keeps its independently selected effective month.
         */
        $feeRevisionEffectiveFrom =
            $snapshot['fee_revision']['effective_from']
            ?? $snapshot['request']['tuition_increment_effective_from']
            ?? $snapshot['request']['billing_month']
            ?? $effectiveDate;

        $feeRevisionEffectiveFrom = Carbon::parse(
            $feeRevisionEffectiveFrom
        )
            ->startOfMonth()
            ->toDateString();

        $studentReference =
            optional($enrollment)->enrollId
            ?: $student->roll_no
            ?: $student->id;

        $promotion = new \App\Models\StudentPromotions();
        $promotion->student_id = $studentReference;
        $promotion->prev_session =
            $source['session_id'] ?? null;
        $promotion->new_session =
            $target['session_id'] ?? null;
        $promotion->class_from =
            $source['class_id'] ?? null;
        $promotion->class_to =
            $target['class_id'] ?? null;
        $promotion->prev_section =
            $source['section_id'] ?? null;
        $promotion->new_section =
            $target['section_id'] ?? null;
        $promotion->branch_from =
            $source['branch_id'] ?? null;
        $promotion->branch_to =
            $target['branch_id'] ?? null;
        $promotion->promotion_date =
            $effectiveDate;
        $promotion->owned_by =
            $target['branch_id']
            ?? $record->owned_by;
        $promotion->created_by =
            $record->created_by
            ?: $student->created_by;
        $promotion->save();

        $promotionHistory = new StudentHistory();
        $promotionHistory->reg_id = $student->id;
        $promotionHistory->student_id =
            $studentReference;
        $promotionHistory->event_type =
            $branchChanged
                ? 'transfer'
                : 'promote';
        $promotionHistory->from_session_id =
            $source['session_id'] ?? null;
        $promotionHistory->from_class_id =
            $source['class_id'] ?? null;
        $promotionHistory->from_section_id =
            $source['section_id'] ?? null;
        $promotionHistory->from_branch_id =
            $source['branch_id'] ?? null;
        $promotionHistory->to_session_id =
            $target['session_id'] ?? null;
        $promotionHistory->to_class_id =
            $target['class_id'] ?? null;
        $promotionHistory->to_section_id =
            $target['section_id'] ?? null;
        $promotionHistory->to_branch_id =
            $target['branch_id'] ?? null;
        $promotionHistory->effective_date =
            $effectiveDate;
        $promotionHistory->remarks =
            (
                $branchChanged
                    ? 'Readmission Branch Promotion'
                    : 'Readmission Promotion'
            )
            . ' - '
            . (
                $flowType === 're_enrollment'
                    ? 'Re-enrollment'
                    : 'Readmission'
            )
            . ' - applied on first payment';
        $promotionHistory->owned_by =
            $target['branch_id']
            ?? $record->owned_by;
        $promotionHistory->created_by =
            $record->created_by
            ?: $student->created_by;
        $promotionHistory->save();

        $batch = StudentFeeRevisionBatch::create([
            'revision_type' => $branchChanged
                ? 'branch_promotion'
                : 'promotion',
            'promotion_id' => $promotion->id,
            'student_id' => $studentReference,
            'reg_id' => $student->id,
            'session_from_id' =>
                $source['session_id'] ?? null,
            'session_to_id' =>
                $target['session_id'] ?? null,
            'branch_from_id' =>
                $source['branch_id'] ?? null,
            'branch_to_id' =>
                $target['branch_id'] ?? null,
            'class_from_id' =>
                $source['class_id'] ?? null,
            'class_to_id' =>
                $target['class_id'] ?? null,
            'section_from_id' =>
                $source['section_id'] ?? null,
            'section_to_id' =>
                $target['section_id'] ?? null,
            'effective_from' =>
                $feeRevisionEffectiveFrom,
            'status' => 'applied',
            'remarks' =>
                'Readmission placement/session revision applied on first payment; Tuition fee revision effective from '
                . Carbon::parse(
                    $feeRevisionEffectiveFrom
                )->format('F Y'),
            'owned_by' =>
                $target['branch_id']
                ?? $record->owned_by,
            'created_by' =>
                $record->created_by
                ?: $student->created_by,
        ]);

        $feeRevision =
            $snapshot['fee_revision'] ?? [];

        if (
            $sessionChanged
            && !empty($feeRevision['enabled'])
            && !empty($feeRevision['head_id'])
        ) {
            $headId = (int) $feeRevision['head_id'];

            $before = $beforeStructure->get(
                $headId,
                []
            );

            $after = StudentFeeStructure::where(
                'reg_id',
                $student->id
            )
                ->where('head_id', $headId)
                ->orderByDesc('id')
                ->first();

            $prevBase = (float) (
                $feeRevision['prev_base_amount']
                ?? $before['amount']
                ?? 0
            );

            $newBase = (float) (
                $feeRevision['new_base_amount']
                ?? optional($after)->amount
                ?? $prevBase
            );

            $prevPayable = (float) (
                $feeRevision['prev_payable_amount']
                ?? $before['payable_amount']
                ?? $prevBase
            );

            $newPayable = (float) (
                $feeRevision['new_payable_amount']
                ?? $newBase
            );

            StudentFeeRevisionItem::create([
                'batch_id' => $batch->id,
                'student_fee_structure_id' =>
                    optional($after)->id
                    ?: ($before['id'] ?? null),
                'student_id' => $studentReference,
                'reg_id' => $student->id,
                'head_id' => $headId,
                'percentage' => max(
                    0,
                    (int) (
                        $feeRevision['percentage'] ?? 0
                    )
                ),
                'prev_base_amount' => $prevBase,
                'new_base_amount' => $newBase,
                'prev_payable_amount' =>
                    $prevPayable,
                'new_payable_amount' =>
                    $newPayable,
            ]);
        }
    }

    private function decodeJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode(
            (string) $value,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }
}