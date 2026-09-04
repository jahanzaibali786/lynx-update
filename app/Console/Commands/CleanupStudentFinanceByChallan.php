<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupStudentFinanceByChallan extends Command
{
    protected $signature = 'student-finance:cleanup-challans
        {--from=2018-01-01 : First fee month to include}
        {--to=2025-09-30 : Last fee month to include}
        {--date-column=fee_month : challans date column to filter: fee_month or challan_date}
        {--chunk=300 : Challans deleted per scheduler run}
        {--execute : Schedule the destructive cleanup after two minutes}
        {--process : Process the next scheduled challan cleanup chunk}';

    protected $description = 'Preview, schedule, and process old student finance cleanup using challans as the base';

    public function handle()
    {
        if ($this->option('process')) {
            return $this->processNextChunk();
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $this->option('from'))->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $this->option('to'))->startOfDay();
        } catch (Throwable $e) {
            $this->error('Dates must use YYYY-MM-DD format.');
            return self::FAILURE;
        }

        if ($to->lt($from)) {
            $this->error('The --to date must be on or after --from.');
            return self::FAILURE;
        }
        $dateColumn = $this->dateColumn();
        
        $chunkSize = max(50, min(1000, (int) $this->option('chunk')));
        $this->showPreview($from, $to, $dateColumn);

        if (!$this->option('execute')) {
            $this->warn('Preview only. Nothing was deleted.');
            $this->line('Add --execute to schedule deletion two minutes from now.');
            return self::SUCCESS;
        }

        $activeRun = DB::table('student_finance_cleanup_runs')
            ->whereIn('status', ['scheduled', 'running'])
            ->exists();

        if ($activeRun) {
            $this->error('Another student finance cleanup is already active.');
            return self::FAILURE;
        }

        $executeAfter = now()->addMinutes(2);
        $runId = DB::table('student_finance_cleanup_runs')->insertGetId([
            'cleanup_type' => 'challan',
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'current_year' => (int) $from->format('Y'),
            'last_receipt_id' => 0,
            'last_challan_id' => 0,
            'chunk_size' => $chunkSize,
            'status' => 'scheduled',
            'totals' => json_encode($this->emptyTotals(['date_column' => $dateColumn])),
            'execute_after' => $executeAfter,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::warning('Student finance challan cleanup scheduled', [
            'run_id' => $runId,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'date_column' => $dateColumn,
            'chunk_size' => $chunkSize,
            'execute_after' => $executeAfter->toDateTimeString(),
        ]);

        $this->warn("Challan cleanup run {$runId} is scheduled for {$executeAfter->toDateTimeString()}.");
        $this->line('Run php artisan student-finance:cleanup-challans --process, or let the scheduler/browser monitor process it.');

        return self::SUCCESS;
    }

    private function showPreview(Carbon $from, Carbon $to, string $dateColumn): void
    {
        $this->info("Challan cleanup preview by {$dateColumn}: {$from->toDateString()} through {$to->toDateString()}");
        $this->line('Admission and Registration challans and their receipts are excluded.');

        $rows = DB::table('challans as c')
            ->leftJoin('student_receipts as sr', 'sr.challan_id', '=', 'c.id')
            ->selectRaw("YEAR(c.{$dateColumn}) AS year")
            ->selectRaw('COUNT(DISTINCT c.id) AS challans')
            ->selectRaw('COUNT(DISTINCT sr.id) AS receipts')
            ->selectRaw('COUNT(DISTINCT sr.voucher_id) AS receipt_vouchers')
            ->selectRaw('COUNT(DISTINCT c.voucher_id) AS challan_vouchers')
            ->whereBetween("c.{$dateColumn}", [$from->toDateString(), $to->toDateString()])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->groupByRaw("YEAR(c.{$dateColumn})")
            ->orderBy('year')
            ->get()
            ->map(function ($row) {
                return [
                    (string) $row->year,
                    number_format($row->challans),
                    number_format($row->receipts),
                    number_format($row->receipt_vouchers),
                    number_format($row->challan_vouchers),
                ];
            })
            ->all();

        $this->table(['Year', 'Challans', 'Receipts', 'Receipt Vouchers', 'Challan Vouchers'], $rows);
    }

    private function processNextChunk()
    {
        DB::table('student_finance_cleanup_runs')
            ->where('cleanup_type', 'challan')
            ->where('status', 'running')
            ->where('locked_at', '<', now()->subMinutes(15))
            ->update([
                'status' => 'scheduled',
                'execute_after' => now(),
                'locked_at' => null,
                'last_error' => 'Recovered automatically after a stale worker lock.',
                'updated_at' => now(),
            ]);

        $run = DB::transaction(function () {
            $run = DB::table('student_finance_cleanup_runs')
                ->where('cleanup_type', 'challan')
                ->where('status', 'scheduled')
                ->where('execute_after', '<=', now())
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$run) {
                return null;
            }

            DB::table('student_finance_cleanup_runs')
                ->where('id', $run->id)
                ->update([
                    'status' => 'running',
                    'locked_at' => now(),
                    'updated_at' => now(),
                ]);

            return $run;
        });

        if (!$run) {
            return self::SUCCESS;
        }

        try {
            return $this->processRun($run);
        } catch (Throwable $e) {
            DB::table('student_finance_cleanup_runs')
                ->where('id', $run->id)
                ->update([
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                    'locked_at' => null,
                    'updated_at' => now(),
                ]);

            Log::error('Student finance challan cleanup failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Challan cleanup run {$run->id} failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function processRun($run)
    {
        $from = Carbon::parse($run->from_date);
        $to = Carbon::parse($run->to_date);
        $year = (int) $run->current_year;
        $dateColumn = $this->runDateColumn($run);

        if ($year > (int) $to->format('Y')) {
            $this->completeRun($run->id);
            return self::SUCCESS;
        }

        $yearFrom = Carbon::create($year, 1, 1)->max($from)->toDateString();
        $yearTo = Carbon::create($year, 12, 31)->min($to)->toDateString();

        $challans = DB::table('challans')
            ->select(['id', 'voucher_id'])
            ->whereBetween($dateColumn, [$yearFrom, $yearTo])
            ->where('id', '>', (int) ($run->last_challan_id ?? 0))
            ->whereRaw('LOWER(COALESCE(challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->orderBy('id')
            ->limit((int) $run->chunk_size)
            ->get();

        if ($challans->isEmpty()) {
            $nextYear = $year + 1;

            if ($nextYear > (int) $to->format('Y')) {
                $this->completeRun($run->id);
                return self::SUCCESS;
            }

            DB::table('student_finance_cleanup_runs')
                ->where('id', $run->id)
                ->update([
                    'current_year' => $nextYear,
                    'last_challan_id' => 0,
                    'status' => 'scheduled',
                    'execute_after' => now()->addMinutes(2),
                    'locked_at' => null,
                    'updated_at' => now(),
                ]);

            Log::info('Student finance challan cleanup advanced year', [
                'run_id' => $run->id,
                'year' => $nextYear,
            ]);

            return self::SUCCESS;
        }

        $challanIds = $challans->pluck('id')->map(fn ($id) => (int) $id)->all();
        $lastChallanId = (int) $challans->max('id');

        $deleted = DB::transaction(function () use ($challanIds) {
            $counts = $this->emptyTotals();

            $receiptIds = DB::table('student_receipts')
                ->whereIn('challan_id', $challanIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $receiptVoucherIds = DB::table('student_receipts')
                ->whereIn('challan_id', $challanIds)
                ->pluck('voucher_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $challanHeadIds = DB::table('challan_heads')
                ->whereIn('challan_id', $challanIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $challanVoucherIds = DB::table('challans')
                ->whereIn('id', $challanIds)
                ->pluck('voucher_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $adjustmentVoucherIds = $this->tableExists('challan_sec_adjustments')
                ? DB::table('challan_sec_adjustments')
                    ->whereIn('challan_id', $challanIds)
                    ->pluck('voucher_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all()
                : [];

            $directJournalIds = DB::table('journal_entries')
                ->whereIn('challan_id', $challanIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $referenceJournalIds = DB::table('journal_entries')
                ->whereIn('reference_id', $challanIds)
                ->where(function ($query) {
                    $query->whereIn('voucher_type', ['JV', 'BRV', 'CRV', 'BPV', 'CPV'])
                        ->orWhereRaw('LOWER(COALESCE(category, "")) IN (?, ?, ?, ?, ?, ?, ?, ?)', [
                            'regular',
                            'advance',
                            'registration',
                            'readmission',
                            'transfer',
                            'withdrawal',
                            'challan',
                            'challan adjustment',
                        ]);
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $itemJournalIds = [];
            if ($receiptIds || $challanHeadIds) {
                $itemJournalIds = DB::table('journal_items')
                    ->where(function ($query) use ($receiptIds, $challanHeadIds) {
                        if ($receiptIds) {
                            $query->whereIn('receipt_id', $receiptIds);
                        }
                        if ($challanHeadIds) {
                            $method = $receiptIds ? 'orWhereIn' : 'whereIn';
                            $query->{$method}('entry_id', $challanHeadIds);
                        }
                    })
                    ->pluck('journal')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            $journalIds = array_values(array_unique(array_merge(
                $receiptVoucherIds,
                $challanVoucherIds,
                $adjustmentVoucherIds,
                $directJournalIds,
                $referenceJournalIds,
                $itemJournalIds
            )));

            if ($journalIds) {
                $counts['journal_items'] = DB::table('journal_items')
                    ->whereIn('journal', $journalIds)
                    ->delete();
                $counts['journals'] = DB::table('journal_entries')
                    ->whereIn('id', $journalIds)
                    ->delete();
            }

            if ($receiptIds) {
                $counts['receipts'] = DB::table('student_receipts')
                    ->whereIn('id', $receiptIds)
                    ->delete();
            }

            if ($this->tableExists('challan_sec_adjustments')) {
                $counts['challan_sec_adjustments'] = DB::table('challan_sec_adjustments')
                    ->whereIn('challan_id', $challanIds)
                    ->delete();
            }

            if ($this->tableExists('employee_child_adjustments')) {
                $counts['employee_child_adjustments'] = DB::table('employee_child_adjustments')
                    ->whereIn('challan_id', $challanIds)
                    ->delete();
            }

            $counts['challan_heads'] = DB::table('challan_heads')
                ->whereIn('challan_id', $challanIds)
                ->delete();

            $counts['challans'] = DB::table('challans')
                ->whereIn('id', $challanIds)
                ->delete();

            return $counts;
        }, 3);

        $totals = $this->mergeTotals(
            json_decode($run->totals ?: '{}', true) ?: [],
            $deleted
        );
        $totals['date_column'] = $dateColumn;

        DB::table('student_finance_cleanup_runs')
            ->where('id', $run->id)
            ->update([
                'last_challan_id' => $lastChallanId,
                'status' => 'scheduled',
                'totals' => json_encode($totals),
                'execute_after' => now()->addMinutes(2),
                'locked_at' => null,
                'updated_at' => now(),
            ]);

        Log::warning('Student finance challan cleanup chunk completed', [
            'run_id' => $run->id,
            'year' => $year,
            'last_challan_id' => $lastChallanId,
            'deleted' => $deleted,
            'totals' => $totals,
        ]);

        return self::SUCCESS;
    }

    private function completeRun($runId): void
    {
        DB::table('student_finance_cleanup_runs')
            ->where('id', $runId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'locked_at' => null,
                'updated_at' => now(),
            ]);

        Log::warning('Student finance challan cleanup completed', ['run_id' => $runId]);
        $this->info("Challan cleanup run {$runId} completed.");
    }

    private function dateColumn(): string
    {
        return in_array($this->option('date-column'), ['challan_date', 'fee_month'], true)
            ? $this->option('date-column')
            : 'fee_month';
    }

    private function runDateColumn($run): string
    {
        $totals = json_decode($run->totals ?: '{}', true) ?: [];
        return in_array(($totals['date_column'] ?? null), ['challan_date', 'fee_month'], true)
            ? $totals['date_column']
            : 'fee_month';
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    private function emptyTotals(array $extra = []): array
    {
        return array_merge([
            'challans' => 0,
            'challan_heads' => 0,
            'receipts' => 0,
            'journals' => 0,
            'journal_items' => 0,
            'challan_sec_adjustments' => 0,
            'employee_child_adjustments' => 0,
        ], $extra);
    }

    private function mergeTotals(array $current, array $deleted): array
    {
        $totals = $this->emptyTotals();

        foreach ($totals as $key => $value) {
            $totals[$key] = (int) ($current[$key] ?? 0) + (int) ($deleted[$key] ?? 0);
        }

        return $totals;
    }
}
