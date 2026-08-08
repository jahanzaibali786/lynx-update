<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class CorrectJunJul2026BimonthlyLateFees extends Command
{
    protected $signature = 'challans:correct-jun-jul-2026-late-fees
        {--dry-run : Preview corrections without changing data}
        {--apply : Apply the corrections}
        {--owned-by= : Limit challans to this owned_by school/branch/tenant id}
        {--created-by= : Limit challans to this created_by tenant/company id}
        {--session-id= : Limit challans to this academic session id}
        {--all-tenants : Allow running without owned_by/created_by/session filters}
        {--chunk=200 : Challans to process per chunk}
        {--export= : Dry-run Excel export path. Defaults to storage/app/reports/jun_jul_2026_late_fee_dry_run_YYYYmmdd_His.xlsx}';

    protected $description = 'Correct June-July 2026 bi-monthly challan late fees using the 25% threshold';

    private const FEE_MONTH = '2026-06-01';
    private const FIRST_MONTH = '2026-06-01';
    private const SECOND_MONTH = '2026-07-01';
    private const THRESHOLD = 0.25;
    private const LATE_FEE_PER_DAY = 120;
    private const LATE_FEE_CAP = 1200;

    public function handle(): int
    {
        if ($this->option('apply') && $this->option('dry-run')) {
            $this->error('Use either --apply or --dry-run, not both.');
            return self::FAILURE;
        }

        $dryRun = ! $this->option('apply');
        $chunkSize = max(25, min(1000, (int) $this->option('chunk')));

        if (! $this->option('all-tenants')
            && ! $this->option('owned-by')
            && ! $this->option('created-by')
            && ! $this->option('session-id')
        ) {
            $this->error('Provide --owned-by, --created-by, or --session-id. Use --all-tenants only after reviewing the preview SQL.');
            return self::FAILURE;
        }

        $lateFeeHeadIds = DB::table('fee_heads')
            ->whereRaw('LOWER(fee_head) LIKE ?', ['%late fee%'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($lateFeeHeadIds)) {
            $this->error('No LATE FEE fee head was found.');
            return self::FAILURE;
        }

        $this->info(($dryRun ? 'Dry run' : 'Applying') . ' June-July 2026 bi-monthly late-fee correction.');
        $this->line('Correct threshold: 25%; late fee: 120/day; max cap: 1,200.');

        $totals = [
            'processed' => 0,
            'remove' => 0,
            'update' => 0,
            'no_change' => 0,
            'skipped' => 0,
        ];
        $exportPath = $dryRun ? $this->exportPath() : null;
        $spreadsheet = $exportPath ? $this->createPreviewSpreadsheet() : null;
        $exportRow = 2;

        $query = $this->affectedChallanIdQuery($lateFeeHeadIds);

        $query->orderBy('c.id')->chunkById($chunkSize, function (Collection $rows) use ($dryRun, $lateFeeHeadIds, $spreadsheet, &$exportRow, &$totals) {
            $work = function () use ($rows, $dryRun, $lateFeeHeadIds, $spreadsheet, &$exportRow, &$totals) {
                foreach ($rows as $row) {
                    $result = $this->processChallan((int) $row->id, $lateFeeHeadIds, $dryRun);
                    $totals['processed']++;
                    $totals[$result['counter']]++;
                    $this->appendPreviewRow($spreadsheet, $exportRow, $result);

                    $this->line(sprintf(
                        '%s | receipt: %s | existing: %s | corrected: %s | diff: %s | %s%s',
                        $result['challan_id'],
                        $result['receipt_date'] ?: '-',
                        number_format($result['existing_late_fee'], 2),
                        number_format($result['correct_late_fee'], 2),
                        number_format($result['difference'], 2),
                        $result['action'],
                        $result['note'] ? ' | ' . $result['note'] : ''
                    ));
                }
            };

            if ($dryRun) {
                $work();
                return;
            }

            DB::transaction($work, 3);
        }, 'c.id', 'id');

        $this->newLine();
        $this->table(
            ['Processed', 'REMOVE', 'UPDATE', 'NO CHANGE', 'Skipped'],
            [[
                $totals['processed'],
                $totals['remove'],
                $totals['update'],
                $totals['no_change'],
                $totals['skipped'],
            ]]
        );

        if ($dryRun) {
            if ($spreadsheet && $exportPath) {
                $this->savePreviewSpreadsheet($spreadsheet, $exportPath);
                $this->info('Dry-run Excel exported to: ' . $exportPath);
            }

            $this->warn('Dry run only. Re-run with --apply to write corrections.');
        }

        return self::SUCCESS;
    }

    private function affectedChallanIdQuery(array $lateFeeHeadIds)
    {
        $query = DB::table('challans as c')
            ->join('challan_heads as ch', 'ch.challan_id', '=', 'c.id')
            ->select('c.id')
            ->where('c.fee_month', self::FEE_MONTH)
            ->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(c.other_months, ''), ' ', ''))", [self::FIRST_MONTH])
            ->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(c.other_months, ''), ' ', ''))", [self::SECOND_MONTH])
            ->whereIn('ch.head_id', $lateFeeHeadIds)
            ->whereRaw('LOWER(COALESCE(c.status, "")) != ?', ['paid'])
            ->groupBy('c.id');

        $this->whereNotSoftDeleted($query, 'challans', 'c');
        $this->whereNotSoftDeleted($query, 'challan_heads', 'ch');

        if ($this->option('owned-by')) {
            $query->where('c.owned_by', (int) $this->option('owned-by'));
        }

        if ($this->option('created-by')) {
            $query->where('c.created_by', (int) $this->option('created-by'));
        }

        if ($this->option('session-id')) {
            $query->where('c.session_id', (int) $this->option('session-id'));
        }

        return $query;
    }

    private function processChallan(int $challanId, array $lateFeeHeadIds, bool $dryRun): array
    {
        $challan = DB::table('challans')->where('id', $challanId);
        $this->whereNotSoftDeleted($challan, 'challans');
        $challan = $dryRun ? $challan->first() : $challan->lockForUpdate()->first();

        if (! $challan) {
            return $this->result($challanId, null, null, 0.0, 0.0, 'NO CHANGE', 'skipped', 'Challan missing or soft deleted.');
        }

        if (strtolower((string) ($challan->status ?? '')) === 'paid') {
            return $this->result($challanId, $challan->challanNo ?? null, null, 0.0, 0.0, 'NO CHANGE', 'skipped', 'Challan is already paid.');
        }

        $lateHeads = DB::table('challan_heads')
            ->where('challan_id', $challanId)
            ->whereIn('head_id', $lateFeeHeadIds)
            ->orderBy('id')
            ->get();

        $existingLateFee = (float) $lateHeads->sum('price');
        $paidLateFee = $this->challanHeadsHavePaidColumn()
            ? (float) $lateHeads->sum('paid')
            : 0.0;
        $baseTotal = max(0.0, (float) $challan->total_amount - $existingLateFee);
        $netPayable = max(0.0, $baseTotal - (float) ($challan->concession_amount ?? 0));
        $thresholdAmount = $netPayable * self::THRESHOLD;
        $receipt = $this->firstChargeableReceipt($challan, $thresholdAmount);

        if (! $receipt) {
            return $this->result($challanId, $challan->challanNo ?? null, null, $existingLateFee, $existingLateFee, 'NO CHANGE', 'skipped', 'No post-due receipt found before the previous payments reached 25%.');
        }

        $correctLateFee = $this->calculateLateFee($challan, $receipt);
        $difference = $correctLateFee - $existingLateFee;

        if (abs($difference) < 0.00001) {
            return $this->result($challanId, $challan->challanNo ?? null, $receipt->recipt_date, $existingLateFee, $correctLateFee, 'NO CHANGE', 'no_change');
        }

        $action = $correctLateFee <= 0 ? 'REMOVE' : 'UPDATE';

        if ($paidLateFee > 0 && $correctLateFee < $paidLateFee) {
            return $this->result(
                $challanId,
                $challan->challanNo ?? null,
                $receipt->recipt_date,
                $existingLateFee,
                $correctLateFee,
                'NO CHANGE',
                'skipped',
                'Late Fee paid amount is higher than corrected fee; not changing paid history.'
            );
        }

        if ($correctLateFee <= 0 && $paidLateFee > 0) {
            return $this->result(
                $challanId,
                $challan->challanNo ?? null,
                $receipt->recipt_date,
                $existingLateFee,
                $correctLateFee,
                'NO CHANGE',
                'skipped',
                'Late Fee head has paid amount; not removing paid history.'
            );
        }

        if ($correctLateFee > 0 && $this->paidDuplicateLateFeeHeadsExist($lateHeads)) {
            return $this->result(
                $challanId,
                $challan->challanNo ?? null,
                $receipt->recipt_date,
                $existingLateFee,
                $correctLateFee,
                'NO CHANGE',
                'skipped',
                'Duplicate Late Fee head has paid amount; review manually to preserve paid history.'
            );
        }

        if (! $dryRun) {
            $this->applyCorrection($challan, $lateHeads, $correctLateFee, $difference);
        }

        return $this->result(
            $challanId,
            $challan->challanNo ?? null,
            $receipt->recipt_date,
            $existingLateFee,
            $correctLateFee,
            $action,
            strtolower($action)
        );
    }

    private function firstChargeableReceipt(object $challan, float $thresholdAmount)
    {
        try {
            $dueDate = Carbon::parse($challan->due_date);
        } catch (Throwable $e) {
            return null;
        }

        $query = DB::table('student_receipts')
            ->where('challan_id', $challan->id)
            ->whereNotNull('recipt_date')
            ->where('recipt_amount', '>', 0)
            ->orderBy('recipt_date')
            ->orderBy('id');

        $this->whereNotSoftDeleted($query, 'student_receipts');

        $runningTotal = 0.0;
        foreach ($query->get() as $receipt) {
            $paymentDate = Carbon::parse($receipt->recipt_date);
            $paidBeforeThisReceipt = $runningTotal;

            if ($paymentDate->gt($dueDate) && ($thresholdAmount <= 0 || $paidBeforeThisReceipt < $thresholdAmount)) {
                $receipt->paid_before_this_receipt = $paidBeforeThisReceipt;
                return $receipt;
            }

            $runningTotal += (float) $receipt->recipt_amount;
        }

        return null;
    }

    private function calculateLateFee(object $challan, object $receipt): float
    {
        try {
            $dueDate = Carbon::parse($challan->due_date);
            $paymentDate = Carbon::parse($receipt->recipt_date);
        } catch (Throwable $e) {
            return 0.0;
        }

        if ($paymentDate->lte($dueDate)) {
            return 0.0;
        }

        $daysOverdue = $dueDate->diffInDays($paymentDate);

        return (float) min($daysOverdue * self::LATE_FEE_PER_DAY, self::LATE_FEE_CAP);
    }

    private function applyCorrection(object $challan, Collection $lateHeads, float $correctLateFee, float $difference): void
    {
        $now = now();
        $lateHeadIds = $lateHeads->pluck('head_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $canonicalHead = $this->lateFeeHead($lateHeads->first()->head_id);

        if ($correctLateFee <= 0) {
            DB::table('challan_heads')->whereIn('id', $lateHeads->pluck('id'))->delete();
            $this->deleteLateFeeJournalItems($challan, $lateHeadIds, $canonicalHead);
        } else {
            if (empty($canonicalHead->account_id) || empty($canonicalHead->receivable_account_id)) {
                throw new \RuntimeException("Late Fee head {$canonicalHead->id} must have both income and receivable accounts before applying challan {$challan->id}.");
            }

            $keepHead = $lateHeads->first();

            DB::table('challan_heads')
                ->where('id', $keepHead->id)
                ->update([
                    'head_id' => $canonicalHead->id,
                    'price' => $correctLateFee,
                    'concession' => 0,
                    'updated_at' => $now,
                ]);

            $duplicateHeadIds = $lateHeads->pluck('id')->filter(fn ($id) => (int) $id !== (int) $keepHead->id)->values();
            if ($duplicateHeadIds->isNotEmpty()) {
                DB::table('challan_heads')->whereIn('id', $duplicateHeadIds)->delete();
            }

            $this->syncLateFeeJournalItems($challan, (int) $keepHead->id, $canonicalHead, $correctLateFee, $lateHeadIds);
        }

        $newTotal = max(0.0, (float) $challan->total_amount + $difference);
        $updates = [
            'total_amount' => $newTotal,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('challans', 'balance')) {
            $updates['balance'] = max(0.0, $newTotal - (float) ($challan->paid_amount ?? 0) - (float) ($challan->concession_amount ?? 0));
        }

        DB::table('challans')->where('id', $challan->id)->update($updates);

        $this->assertJournalBalanced($challan);
    }

    private function syncLateFeeJournalItems(object $challan, int $challanHeadId, object $lateFeeHead, float $amount, array $lateHeadIds): void
    {
        if (empty($challan->voucher_id)) {
            return;
        }

        $items = $this->lateFeeJournalItemQuery($challan, $lateHeadIds, $lateFeeHead)->orderBy('id')->get();
        $income = $items->first(fn ($item) => (float) $item->credit > 0 && (int) $item->account === (int) $lateFeeHead->account_id);
        $receivable = $items->first(fn ($item) => (float) $item->debit > 0 && (int) $item->account === (int) $lateFeeHead->receivable_account_id);
        $keepIds = collect();

        $incomeId = $this->upsertLateFeeJournalItem($income, $challan, $challanHeadId, $lateFeeHead->id, $lateFeeHead->account_id, 0.0, $amount, 'Late Fee Income');
        if ($incomeId) {
            $keepIds->push($incomeId);
        }

        $receivableId = $this->upsertLateFeeJournalItem($receivable, $challan, $challanHeadId, $lateFeeHead->id, $lateFeeHead->receivable_account_id, $amount, 0.0, 'Late Fee Receivable');
        if ($receivableId) {
            $keepIds->push($receivableId);
        }

        $deleteIds = $items->pluck('id')->diff($keepIds)->values();
        if ($deleteIds->isNotEmpty()) {
            DB::table('journal_items')->whereIn('id', $deleteIds)->delete();
        }
    }

    private function upsertLateFeeJournalItem($item, object $challan, int $challanHeadId, int $headId, ?int $accountId, float $debit, float $credit, string $label): ?int
    {
        if (empty($accountId)) {
            return null;
        }

        $payload = [
            'journal' => $challan->voucher_id,
            'account' => $accountId,
            'head' => $headId,
            'entry_id' => $challanHeadId,
            'types' => 'Challan',
            'description' => $label . ' - Challan No ' . $challan->challanNo,
            'debit' => $debit,
            'credit' => $credit,
            'user_type' => 'Student',
            'user_id' => $challan->student_id,
            'updated_at' => now(),
        ];

        if ($item) {
            DB::table('journal_items')->where('id', $item->id)->update($payload);
            return (int) $item->id;
        }

        $payload['created_at'] = now();
        return (int) DB::table('journal_items')->insertGetId($payload);
    }

    private function deleteLateFeeJournalItems(object $challan, array $lateHeadIds, object $lateFeeHead): void
    {
        if (empty($challan->voucher_id)) {
            return;
        }

        $this->lateFeeJournalItemQuery($challan, $lateHeadIds, $lateFeeHead)->delete();
    }

    private function lateFeeJournalItemQuery(object $challan, array $lateHeadIds, object $lateFeeHead)
    {
        $lateAccountIds = collect([$lateFeeHead->account_id, $lateFeeHead->receivable_account_id])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return DB::table('journal_items')
            ->where('journal', $challan->voucher_id)
            ->whereRaw('LOWER(COALESCE(types, "")) = ?', ['challan'])
            ->where(function ($query) use ($lateHeadIds, $lateAccountIds) {
                if (! empty($lateHeadIds) && Schema::hasColumn('journal_items', 'head')) {
                    $query->whereIn('head', $lateHeadIds);
                }

                if (! empty($lateAccountIds)) {
                    $query->orWhereIn('account', $lateAccountIds);
                }
            });
    }

    private function lateFeeHead(int $headId): object
    {
        $head = DB::table('fee_heads')->where('id', $headId)->first();

        if (! $head) {
            throw new \RuntimeException("Late fee head {$headId} no longer exists.");
        }

        return $head;
    }

    private function assertJournalBalanced(object $challan): void
    {
        if (empty($challan->voucher_id)) {
            return;
        }

        $totals = DB::table('journal_items')
            ->selectRaw('COALESCE(SUM(debit), 0) as debit_total, COALESCE(SUM(credit), 0) as credit_total')
            ->where('journal', $challan->voucher_id)
            ->first();

        if (abs((float) $totals->debit_total - (float) $totals->credit_total) > 0.00001) {
            throw new \RuntimeException("Journal {$challan->voucher_id} would be unbalanced after correcting challan {$challan->id}.");
        }
    }

    private function exportPath(): string
    {
        $path = $this->option('export');

        if (! $path) {
            return storage_path('app/reports/jun_jul_2026_late_fee_dry_run_' . now()->format('Ymd_His') . '.xlsx');
        }

        if (! preg_match('/\.(xlsx)$/i', $path)) {
            $path .= '.xlsx';
        }

        if (! preg_match('/^[A-Za-z]:[\\\\\/]|^[\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return $path;
    }

    private function createPreviewSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dry Run');
        $sheet->fromArray([
            'challan_id',
            'challan_no',
            'receipt date',
            'existing late fee',
            'corrected late fee',
            'difference',
            'action',
        ], null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('D:F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function appendPreviewRow(?Spreadsheet $spreadsheet, int &$row, array $result): void
    {
        if (! $spreadsheet) {
            return;
        }

        $spreadsheet->getActiveSheet()->fromArray([
            $result['challan_id'],
            $result['challan_no'] ?: '',
            $result['receipt_date'] ?: '',
            $result['existing_late_fee'],
            $result['correct_late_fee'],
            $result['difference'],
            $result['action'],
        ], null, 'A' . $row);

        $row++;
    }

    private function savePreviewSpreadsheet(Spreadsheet $spreadsheet, string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    private function whereNotSoftDeleted($query, string $table, ?string $alias = null): void
    {
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull(($alias ?: $table) . '.deleted_at');
        }
    }

    private function challanHeadsHavePaidColumn(): bool
    {
        return Schema::hasColumn('challan_heads', 'paid');
    }

    private function paidDuplicateLateFeeHeadsExist(Collection $lateHeads): bool
    {
        if (! $this->challanHeadsHavePaidColumn()) {
            return false;
        }

        return $lateHeads
            ->slice(1)
            ->contains(fn ($head) => (float) ($head->paid ?? 0) > 0);
    }

    private function result(int $challanId, ?string $challanNo, ?string $receiptDate, float $existing, float $correct, string $action, string $counter, string $note = ''): array
    {
        return [
            'challan_id' => $challanId,
            'challan_no' => $challanNo,
            'receipt_date' => $receiptDate,
            'existing_late_fee' => $existing,
            'correct_late_fee' => $correct,
            'difference' => $correct - $existing,
            'action' => $action,
            'counter' => $counter,
            'note' => $note,
        ];
    }
}
