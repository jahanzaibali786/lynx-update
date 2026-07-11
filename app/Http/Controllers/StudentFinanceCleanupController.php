<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class StudentFinanceCleanupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeMonitor();

        $run = $this->getRun($request->integer('run'));
        $mode = $this->cleanupMode($request->input('mode', optional($run)->cleanup_type ?: 'receipt'));

        return view('admin.student_finance_cleanup.index', [
            'runId' => $run ? $run->id : null,
            'initialStatus' => $run ? $this->buildStatus($run) : null,
            'mode' => $mode,
            'preview' => $this->buildPreview(
                $request->input('from', '2018-01-01'),
                $request->input('to', '2025-09-30'),
                $mode
            ),
        ]);
    }

    public function start(Request $request)
    {
        $this->authorizeMonitor();

        $data = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'mode' => ['nullable', 'in:receipt,challan'],
            'chunk' => ['nullable', 'integer', 'min:100', 'max:2000'],
            'confirm_backup' => ['accepted'],
        ]);

        $activeRun = DB::table('student_finance_cleanup_runs')
            ->whereIn('status', ['scheduled', 'running'])
            ->exists();

        if ($activeRun) {
            return redirect()->route('student-finance-cleanup.index')
                ->with('error', 'Another student finance cleanup is already active.');
        }

        $from = Carbon::createFromFormat('Y-m-d', $data['from']);
        $executeAfter = now()->addMinutes(2);
        $mode = $this->cleanupMode($data['mode'] ?? 'receipt');

        $runId = DB::table('student_finance_cleanup_runs')->insertGetId([
            'cleanup_type' => $mode,
            'from_date' => $data['from'],
            'to_date' => $data['to'],
            'current_year' => (int) $from->format('Y'),
            'last_receipt_id' => 0,
            'last_challan_id' => 0,
            'chunk_size' => max(100, min(2000, (int) ($data['chunk'] ?? 500))),
            'status' => 'scheduled',
            'totals' => json_encode($mode === 'challan' ? $this->emptyChallanTotals() : $this->emptyTotals()),
            'execute_after' => $executeAfter,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('student-finance-cleanup.index', ['run' => $runId])
            ->with('success', ucfirst($mode) . ' cleanup run scheduled. Keep this page open to monitor it.');
    }

    public function process(Request $request, $id)
    {
        $this->authorizeMonitor();

        $run = DB::table('student_finance_cleanup_runs')->find($id);
        abort_if(!$run, 404, 'Cleanup run not found.');

        $command = ($run->cleanup_type ?? 'receipt') === 'challan'
            ? 'student-finance:cleanup-challans'
            : 'student-finance:cleanup';

        Artisan::call($command, ['--process' => true]);

        $run = DB::table('student_finance_cleanup_runs')->find($id);

        return response()->json($this->buildStatus($run));
    }

    public function status(Request $request, $id)
    {
        $this->authorizeMonitor();

        $run = DB::table('student_finance_cleanup_runs')->find($id);
        abort_if(!$run, 404, 'Cleanup run not found.');

        return response()->json($this->buildStatus($run));
    }

    private function getRun($requestedId = null)
    {
        $query = DB::table('student_finance_cleanup_runs');

        if ($requestedId) {
            return $query->where('id', $requestedId)->first();
        }

        return $query->orderByDesc('id')->first();
    }

    private function buildPreview($fromDate, $toDate, $mode = 'receipt')
    {
        try {
            $from = Carbon::createFromFormat('Y-m-d', $fromDate);
            $to = Carbon::createFromFormat('Y-m-d', $toDate);
        } catch (\Throwable $e) {
            $from = Carbon::create(2018, 1, 1);
            $to = Carbon::create(2025, 9, 30);
        }

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        if ($this->cleanupMode($mode) === 'challan') {
            $rows = DB::table('challans as c')
                ->leftJoin('student_receipts as sr', 'sr.challan_id', '=', 'c.id')
                ->selectRaw('YEAR(c.fee_month) AS year')
                ->selectRaw('COUNT(DISTINCT c.id) AS challans')
                ->selectRaw('COUNT(DISTINCT sr.id) AS receipts')
                ->selectRaw('COUNT(DISTINCT sr.voucher_id) AS receipt_vouchers')
                ->selectRaw('COUNT(DISTINCT c.voucher_id) AS challan_vouchers')
                ->whereBetween('c.fee_month', [$from->toDateString(), $to->toDateString()])
                ->whereRaw('LOWER(COALESCE(c.challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
                ->groupByRaw('YEAR(c.fee_month)')
                ->orderBy('year')
                ->get();

            return [
                'mode' => 'challan',
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'total_receipts' => (int) $rows->sum('receipts'),
                'total_vouchers' => (int) $rows->sum('receipt_vouchers') + (int) $rows->sum('challan_vouchers'),
                'total_challans' => (int) $rows->sum('challans'),
                'rows' => $rows,
            ];
        }

        $rows = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->selectRaw('YEAR(sr.recipt_date) AS year')
            ->selectRaw('COUNT(*) AS receipts')
            ->selectRaw('COUNT(DISTINCT sr.voucher_id) AS receipt_vouchers')
            ->selectRaw('COUNT(DISTINCT sr.challan_id) AS challans')
            ->whereBetween('sr.recipt_date', [$from->toDateString(), $to->toDateString()])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->groupByRaw('YEAR(sr.recipt_date)')
            ->orderBy('year')
            ->get();

        return [
            'mode' => 'receipt',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_receipts' => (int) $rows->sum('receipts'),
            'total_vouchers' => (int) $rows->sum('receipt_vouchers'),
            'total_challans' => (int) $rows->sum('challans'),
            'rows' => $rows,
        ];
    }

    private function buildStatus($run)
    {
        if (($run->cleanup_type ?? 'receipt') === 'challan') {
            return $this->buildChallanStatus($run);
        }

        $totals = json_decode($run->totals ?: '{}', true) ?: [];
        $deletedReceipts = (int) ($totals['receipts'] ?? 0);

        $remainingReceipts = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->whereBetween('sr.recipt_date', [$run->from_date, $run->to_date])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->count();

        $targetReceipts = $deletedReceipts + $remainingReceipts;
        $percentage = $targetReceipts > 0
            ? round(($deletedReceipts / $targetReceipts) * 100, 2)
            : ($run->status === 'completed' ? 100 : 0);
        $schedulerDelayed = $run->status === 'scheduled'
            && $run->execute_after
            && now()->gt(\Carbon\Carbon::parse($run->execute_after)->addMinutes(3));

        $yearRows = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->selectRaw('YEAR(sr.recipt_date) AS year, COUNT(*) AS remaining')
            ->whereBetween('sr.recipt_date', [$run->from_date, $run->to_date])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->groupByRaw('YEAR(sr.recipt_date)')
            ->pluck('remaining', 'year');

        $years = [];
        $firstYear = (int) date('Y', strtotime($run->from_date));
        $lastYear = (int) date('Y', strtotime($run->to_date));

        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $years[] = [
                'year' => $year,
                'remaining' => (int) ($yearRows[$year] ?? 0),
                'state' => $year < (int) $run->current_year
                    ? 'completed'
                    : ($year === (int) $run->current_year ? 'processing' : 'pending'),
            ];
        }

        return [
            'id' => (int) $run->id,
            'status' => $run->status,
            'from_date' => $run->from_date,
            'to_date' => $run->to_date,
            'current_year' => (int) $run->current_year,
            'last_receipt_id' => (int) $run->last_receipt_id,
            'last_challan_id' => (int) ($run->last_challan_id ?? 0),
            'cleanup_type' => $run->cleanup_type ?? 'receipt',
            'chunk_size' => (int) $run->chunk_size,
            'deleted_receipts' => $deletedReceipts,
            'remaining_receipts' => $remainingReceipts,
            'target_receipts' => $targetReceipts,
            'percentage' => $percentage,
            'totals' => [
                'receipts' => $deletedReceipts,
                'receipt_journals' => (int) ($totals['receipt_journals'] ?? 0),
                'receipt_journal_items' => (int) ($totals['receipt_journal_items'] ?? 0),
                'challans' => (int) ($totals['challans'] ?? 0),
                'challan_heads' => (int) ($totals['challan_heads'] ?? 0),
                'challan_journals' => (int) ($totals['challan_journals'] ?? 0),
                'challan_journal_items' => (int) ($totals['challan_journal_items'] ?? 0),
            ],
            'years' => $years,
            'execute_after' => $run->execute_after,
            'locked_at' => $run->locked_at,
            'updated_at' => $run->updated_at,
            'completed_at' => $run->completed_at,
            'last_error' => $run->last_error,
            'scheduler_delayed' => $schedulerDelayed,
            'server_time' => now()->toDateTimeString(),
        ];
    }

    private function authorizeMonitor()
    {
        abort_unless(
            auth()->check() && in_array(auth()->user()->type, ['company', 'super admin'], true),
            403,
            'Only administrators can monitor finance cleanup.'
        );
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

    private function buildChallanStatus($run)
    {
        $totals = json_decode($run->totals ?: '{}', true) ?: [];
        $deletedChallans = (int) ($totals['challans'] ?? 0);

        $remainingChallans = DB::table('challans')
            ->whereBetween('fee_month', [$run->from_date, $run->to_date])
            ->whereRaw('LOWER(COALESCE(challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->count();

        $targetChallans = $deletedChallans + $remainingChallans;
        $percentage = $targetChallans > 0
            ? round(($deletedChallans / $targetChallans) * 100, 2)
            : ($run->status === 'completed' ? 100 : 0);
        $schedulerDelayed = $run->status === 'scheduled'
            && $run->execute_after
            && now()->gt(\Carbon\Carbon::parse($run->execute_after)->addMinutes(3));

        $yearRows = DB::table('challans')
            ->selectRaw('YEAR(fee_month) AS year, COUNT(*) AS remaining')
            ->whereBetween('fee_month', [$run->from_date, $run->to_date])
            ->whereRaw('LOWER(COALESCE(challan_type, "")) NOT IN (?, ?)', ['admission', 'registration'])
            ->groupByRaw('YEAR(fee_month)')
            ->pluck('remaining', 'year');

        $years = [];
        $firstYear = (int) date('Y', strtotime($run->from_date));
        $lastYear = (int) date('Y', strtotime($run->to_date));

        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $years[] = [
                'year' => $year,
                'remaining' => (int) ($yearRows[$year] ?? 0),
                'state' => $year < (int) $run->current_year
                    ? 'completed'
                    : ($year === (int) $run->current_year ? 'processing' : 'pending'),
            ];
        }

        return [
            'id' => (int) $run->id,
            'status' => $run->status,
            'cleanup_type' => 'challan',
            'from_date' => $run->from_date,
            'to_date' => $run->to_date,
            'current_year' => (int) $run->current_year,
            'last_receipt_id' => (int) $run->last_receipt_id,
            'last_challan_id' => (int) ($run->last_challan_id ?? 0),
            'chunk_size' => (int) $run->chunk_size,
            'deleted_receipts' => (int) ($totals['receipts'] ?? 0),
            'remaining_receipts' => $remainingChallans,
            'target_receipts' => $targetChallans,
            'deleted_challans' => $deletedChallans,
            'remaining_challans' => $remainingChallans,
            'target_challans' => $targetChallans,
            'percentage' => $percentage,
            'totals' => [
                'receipts' => (int) ($totals['receipts'] ?? 0),
                'receipt_journals' => 0,
                'receipt_journal_items' => 0,
                'challans' => $deletedChallans,
                'challan_heads' => (int) ($totals['challan_heads'] ?? 0),
                'challan_journals' => (int) ($totals['journals'] ?? 0),
                'challan_journal_items' => (int) ($totals['journal_items'] ?? 0),
                'challan_sec_adjustments' => (int) ($totals['challan_sec_adjustments'] ?? 0),
                'employee_child_adjustments' => (int) ($totals['employee_child_adjustments'] ?? 0),
            ],
            'years' => $years,
            'execute_after' => $run->execute_after,
            'locked_at' => $run->locked_at,
            'updated_at' => $run->updated_at,
            'completed_at' => $run->completed_at,
            'last_error' => $run->last_error,
            'scheduler_delayed' => $schedulerDelayed,
            'server_time' => now()->toDateTimeString(),
        ];
    }

    private function cleanupMode($mode)
    {
        return $mode === 'challan' ? 'challan' : 'receipt';
    }

    private function emptyChallanTotals()
    {
        return [
            'challans' => 0,
            'challan_heads' => 0,
            'receipts' => 0,
            'journals' => 0,
            'journal_items' => 0,
            'challan_sec_adjustments' => 0,
            'employee_child_adjustments' => 0,
            'date_column' => 'fee_month',
        ];
    }
}
