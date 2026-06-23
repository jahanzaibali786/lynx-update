<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupStudentFinanceData extends Command
{
    protected $signature = 'student-finance:cleanup
        {--from=2018-01-01 : First receipt date to include}
        {--to=2025-09-30 : Last receipt date to include}
        {--chunk=500 : Receipts deleted per scheduler run}
        {--execute : Schedule the destructive cleanup after two minutes}
        {--process : Process the next scheduled cleanup chunk}';

    protected $description = 'Preview, schedule, and process old student receipt/challan financial cleanup';

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

        $chunkSize = max(100, min(2000, (int) $this->option('chunk')));
        $this->showPreview($from, $to);

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
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'current_year' => (int) $from->format('Y'),
            'last_receipt_id' => 0,
            'chunk_size' => $chunkSize,
            'status' => 'scheduled',
            'totals' => json_encode($this->emptyTotals()),
            'execute_after' => $executeAfter,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::warning('Student finance cleanup scheduled', [
            'run_id' => $runId,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'chunk_size' => $chunkSize,
            'execute_after' => $executeAfter->toDateTimeString(),
        ]);

        $this->warn("Cleanup run {$runId} is scheduled for {$executeAfter->toDateTimeString()}.");
        $this->line('The Laravel scheduler must run every minute on the server.');

        return self::SUCCESS;
    }

    private function showPreview(Carbon $from, Carbon $to)
    {
        $this->info("Cleanup preview: {$from->toDateString()} through {$to->toDateString()}");
        $this->line('Admission challans and their receipts are excluded.');

        $rows = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->selectRaw('YEAR(recipt_date) AS year')
            ->selectRaw('COUNT(*) AS receipts')
            ->selectRaw('COUNT(DISTINCT sr.voucher_id) AS receipt_vouchers')
            ->selectRaw('COUNT(DISTINCT sr.challan_id) AS challans')
            ->whereBetween('sr.recipt_date', [$from->toDateString(), $to->toDateString()])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) != ?', ['admission'])
            ->groupByRaw('YEAR(recipt_date)')
            ->orderBy('year')
            ->get()
            ->map(function ($row) {
                return [
                    (string) $row->year,
                    number_format($row->receipts),
                    number_format($row->receipt_vouchers),
                    number_format($row->challans),
                ];
            })
            ->all();

        $this->table(['Year', 'Receipts', 'Receipt Vouchers', 'Linked Challans'], $rows);

        $mixedChallans = DB::query()
            ->fromSub(function ($query) use ($to) {
                $query->from('student_receipts')
                    ->select('challan_id')
                    ->groupBy('challan_id')
                    ->havingRaw('MIN(recipt_date) <= ?', [$to->toDateString()])
                    ->havingRaw('MAX(recipt_date) > ?', [$to->toDateString()]);
            }, 'mixed')
            ->count();

        $this->line("Mixed-date challans that will be retained: {$mixedChallans}");
    }

    private function processNextChunk()
    {
        DB::table('student_finance_cleanup_runs')
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

            Log::error('Student finance cleanup failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Cleanup run {$run->id} failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function processRun($run)
    {
        $from = Carbon::parse($run->from_date);
        $to = Carbon::parse($run->to_date);
        $year = (int) $run->current_year;

        if ($year > (int) $to->format('Y')) {
            $this->completeRun($run->id);
            return self::SUCCESS;
        }

        $yearFrom = Carbon::create($year, 1, 1)->max($from)->toDateString();
        $yearTo = Carbon::create($year, 12, 31)->min($to)->toDateString();

        $receipts = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->select(['sr.id', 'sr.voucher_id', 'sr.challan_id'])
            ->whereBetween('sr.recipt_date', [$yearFrom, $yearTo])
            ->where('sr.id', '>', $run->last_receipt_id)
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) != ?', ['admission'])
            ->orderBy('sr.id')
            ->limit((int) $run->chunk_size)
            ->get();

        if ($receipts->isEmpty()) {
            $nextYear = $year + 1;

            if ($nextYear > (int) $to->format('Y')) {
                $this->completeRun($run->id);
                return self::SUCCESS;
            }

            DB::table('student_finance_cleanup_runs')
                ->where('id', $run->id)
                ->update([
                    'current_year' => $nextYear,
                    'last_receipt_id' => 0,
                    'status' => 'scheduled',
                    'execute_after' => now()->addMinutes(2),
                    'locked_at' => null,
                    'updated_at' => now(),
                ]);

            Log::info('Student finance cleanup advanced year', [
                'run_id' => $run->id,
                'year' => $nextYear,
            ]);

            return self::SUCCESS;
        }

        $receiptIds = $receipts->pluck('id')->map(fn ($id) => (int) $id)->all();
        $receiptVoucherIds = $receipts->pluck('voucher_id')->filter()->unique()
            ->map(fn ($id) => (int) $id)->values()->all();
        $candidateChallanIds = $receipts->pluck('challan_id')->filter()->unique()
            ->map(fn ($id) => (int) $id)->values()->all();
        $lastReceiptId = (int) $receipts->max('id');

        $deleted = DB::transaction(function () use (
            $receiptIds,
            $receiptVoucherIds,
            $candidateChallanIds
        ) {
            $counts = $this->emptyTotals();

            $counts['receipt_journal_items'] = DB::table('journal_items')
                ->where(function ($query) use ($receiptIds, $receiptVoucherIds) {
                    $query->whereIn('receipt_id', $receiptIds);
                    if ($receiptVoucherIds) {
                        $query->orWhereIn('journal', $receiptVoucherIds);
                    }
                })
                ->delete();

            if ($receiptVoucherIds) {
                $counts['receipt_journals'] = DB::table('journal_entries')
                    ->whereIn('id', $receiptVoucherIds)
                    ->delete();
            }

            $counts['receipts'] = DB::table('student_receipts')
                ->whereIn('id', $receiptIds)
                ->delete();

            $challanIds = DB::table('challans')
                ->whereIn('id', $candidateChallanIds)
                ->whereRaw('LOWER(COALESCE(challan_type, "")) != ?', ['admission'])
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('student_receipts')
                        ->whereColumn('student_receipts.challan_id', 'challans.id');
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (!$challanIds) {
                return $counts;
            }

            $challanVoucherIds = DB::table('challans')
                ->whereIn('id', $challanIds)
                ->pluck('voucher_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $matchedJournalIds = DB::table('journal_entries as je')
                ->join('challans as c', 'c.id', '=', 'je.reference_id')
                ->whereIn('c.id', $challanIds)
                ->where('je.voucher_type', 'JV')
                ->whereRaw('LOWER(COALESCE(je.category, "")) = LOWER(COALESCE(c.challan_type, ""))')
                ->pluck('je.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $directJournalIds = DB::table('journal_entries')
                ->whereIn('challan_id', $challanIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $challanJournalIds = array_values(array_unique(array_merge(
                $challanVoucherIds,
                $matchedJournalIds,
                $directJournalIds
            )));

            if ($challanJournalIds) {
                $counts['challan_journal_items'] = DB::table('journal_items')
                    ->whereIn('journal', $challanJournalIds)
                    ->delete();
                $counts['challan_journals'] = DB::table('journal_entries')
                    ->whereIn('id', $challanJournalIds)
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

        DB::table('student_finance_cleanup_runs')
            ->where('id', $run->id)
            ->update([
                'last_receipt_id' => $lastReceiptId,
                'status' => 'scheduled',
                'totals' => json_encode($totals),
                'execute_after' => now()->addMinutes(2),
                'locked_at' => null,
                'updated_at' => now(),
            ]);

        Log::warning('Student finance cleanup chunk completed', [
            'run_id' => $run->id,
            'year' => $year,
            'last_receipt_id' => $lastReceiptId,
            'deleted' => $deleted,
            'totals' => $totals,
        ]);

        return self::SUCCESS;
    }

    private function completeRun($runId)
    {
        DB::table('student_finance_cleanup_runs')
            ->where('id', $runId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'locked_at' => null,
                'updated_at' => now(),
            ]);

        Log::warning('Student finance cleanup completed', ['run_id' => $runId]);
        $this->info("Cleanup run {$runId} completed.");
    }

    private function emptyTotals()
    {
        return [
            'receipts' => 0,
            'receipt_journals' => 0,
            'receipt_journal_items' => 0,
            'challans' => 0,
            'challan_heads' => 0,
            'challan_journals' => 0,
            'challan_journal_items' => 0,
        ];
    }

    private function mergeTotals(array $current, array $deleted)
    {
        $totals = $this->emptyTotals();

        foreach ($totals as $key => $value) {
            $totals[$key] = (int) ($current[$key] ?? 0) + (int) ($deleted[$key] ?? 0);
        }

        return $totals;
    }
}
