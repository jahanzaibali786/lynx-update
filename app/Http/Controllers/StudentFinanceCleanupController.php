<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentFinanceCleanupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeMonitor();

        $run = $this->getRun($request->integer('run'));

        if (!$run) {
            return redirect()->route('dashboard')
                ->with('error', 'No student finance cleanup run was found.');
        }

        return view('admin.student_finance_cleanup.index', [
            'runId' => $run->id,
            'initialStatus' => $this->buildStatus($run),
        ]);
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

    private function buildStatus($run)
    {
        $totals = json_decode($run->totals ?: '{}', true) ?: [];
        $deletedReceipts = (int) ($totals['receipts'] ?? 0);

        $remainingReceipts = DB::table('student_receipts as sr')
            ->join('challans as c', 'c.id', '=', 'sr.challan_id')
            ->whereBetween('sr.recipt_date', [$run->from_date, $run->to_date])
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) != ?', ['admission'])
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
            ->whereRaw('LOWER(COALESCE(c.challan_type, "")) != ?', ['admission'])
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
}
