<?php

namespace App\Http\Controllers;

use App\Exports\PreChallanComparisonExport;
use App\Models\Challans;
use App\Models\PreChallanReport;
use App\Models\PreChallanSnapshot;
use App\Services\PreChallanReportService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class preChallanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $creatorId = $user->type == 'company'
            ? $user->creatorId()
            : $user->ownedId();

        // 1. Branch dropdown
        $branches = DB::table('users')
            ->when(
                $user->type == 'company',
                fn($q) => $q->where('type', 'branch')->where('is_active', 1)->where('created_by', $creatorId),
                fn($q) => $q->where('id', $creatorId)
            )
            ->pluck('name', 'id')
            ->prepend('All Branches', 'all');
        $query = PreChallanReport::with('branch');
        if (Auth::user()->type == 'branch') {
            $query->where('owned_by', Auth::user()->ownedId());
        }
        if ($request->has('branches') && $request->branches != '' && $request->branches != 'all') {
            $query->where('branch_id', $request->branches);
        }
        // date filter 
        if ($request->has('date') && $request->date != '') {
            $query->where('month', $request->date);
        }
        $prechallanreports = $query->orderBy('month', 'desc')->get();
        return view('studentReports.prechallan.list', compact('prechallanreports', 'branches'));
    }
    public function create()
    {

        $user = Auth::user();
        $creatorId = $user->type == 'company'
            ? $user->creatorId()
            : $user->ownedId();

        // 1. Branch dropdown
        $branches = DB::table('users')
            ->when(
                $user->type == 'company',
                fn($q) => $q->where('type', 'branch')->where('is_active', 1)->where('created_by', $creatorId),
                fn($q) => $q->where('id', $creatorId)
            )
            ->pluck('name', 'id')
            ->prepend('All Branches', '');
        return view('studentReports.prechallan.create', compact('branches'));
    }
    public function store(Request $request)
    {
        //db tranasction + try catch
        DB::beginTransaction();
        try {
            $request->validate([
                'branches' => 'required',
                'month' => 'required',
                'file' => 'required|file|mimes:xlsx,xls|max:2048',
            ]);
            //check existing record for same month and branch
            $existing = DB::table('pre_challan_reports')
                ->where('branch_id', $request->branches)
                ->where('month', $request->month)
                ->first();
            if ($existing) {
                return redirect()->route('prechallan.index')->with('error', 'Pre-Challan for this month and branch already Uploaded.');
            }
            // Handle file upload. save file in prechallan forlder and make a per branch seperate folder with month.

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $filePath = 'prechallan/' . $request->branches . '/' . $request->month . '/' . time() . '_' . $file->getClientOriginalName();

                Storage::disk('public')->put($filePath, file_get_contents($file));
            }
            //store in model 
            DB::table('pre_challan_reports')->insert([
                'branch_id' => $request->branches,
                'month' => $request->month,
                'file_path' => $filePath,
                'remarks' => $request->remarks,
                'created_by' => Auth::user()->creatorId(),
                'owned_by' => $request->branches,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('prechallan.index')->with('success', 'Pre-Challan uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload Pre-Challan. Please try again.');
        }
    }
    // show the 
    public function show($id)
    {

        $user = Auth::user();
        $creatorId = $user->type == 'company'
            ? $user->creatorId()
            : $user->ownedId();

        // 1. Branch dropdown
        $branches = DB::table('users')
            ->when(
                $user->type == 'company',
                fn($q) => $q->where('type', 'branch')->where('is_active', 1)->where('created_by', $creatorId),
                fn($q) => $q->where('id', $creatorId)
            )
            ->pluck('name', 'id')
            ->prepend('All Branches', '');
        $report = PreChallanReport::with('branch')->findOrFail($id);
        return view('studentReports.prechallan.show', compact('report', 'branches'));
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $report = PreChallanReport::findOrFail($id);

            $request->validate([
                'file' => 'nullable|file|mimes:xlsx,xls|max:2048',
                'ho_file' => 'nullable|file|mimes:xlsx,xls|max:2048',
            ]);

            $filePath = $report->file_path;
            $hoFilePath = $report->head_office_file;

            // ---------------- FILE ----------------
            if ($request->hasFile('file')) {

                if (!empty($report->file_path) && Storage::disk('public')->exists($report->file_path)) {
                    Storage::disk('public')->delete($report->file_path);
                }

                $file = $request->file('file');
                $filePath = 'prechallan/' . $report->branch_id . '/' . $report->month . '/' . time() . '_' . $file->getClientOriginalName();

                Storage::disk('public')->put($filePath, file_get_contents($file));
            }

            // ---------------- HO FILE ----------------
            if ($request->hasFile('ho_file')) {

                if (!empty($report->head_office_file) && Storage::disk('public')->exists($report->head_office_file)) {
                    Storage::disk('public')->delete($report->head_office_file);
                }

                $hoFile = $request->file('ho_file');
                $hoFilePath = 'prechallan/' . $report->branch_id . '/' . $report->month . '/' . time() . '_' . $hoFile->getClientOriginalName();

                Storage::disk('public')->put($hoFilePath, file_get_contents($hoFile));
            }

            // ---------------- UPDATE ----------------
            $report->update([
                'file_path' => $filePath,
                'head_office_file' => $hoFilePath,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return redirect()->route('prechallan.index')
                ->with('success', 'Pre-Challan updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
    public function destroy($id)
    {
        $report = PreChallanReport::findOrFail($id);
        if ($report->status == 'approved') {
            return redirect()->route('prechallan.index')
                ->with('error', 'Approved Pre-Challan cannot be deleted.');
        } else if ($report->status == 'sent_for_approval') {
            return redirect()->route('prechallan.index')
                ->with('error', 'Pre-Challan sent for approval cannot be deleted.');
        }

        // Delete files from storage
        if (!empty($report->file_path) && Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }
        if (!empty($report->head_office_file) && Storage::disk('public')->exists($report->head_office_file)) {
            Storage::disk('public')->delete($report->head_office_file);
        }

        // Delete the report record
        $report->delete();

        return redirect()->route('prechallan.index')
            ->with('success', 'Pre-Challan deleted successfully.');
    }
    public function sendForApproval($id)
    {
        $report = PreChallanReport::findOrFail($id);
        if ($report->status != 'pending') {
            return redirect()->route('prechallan.index')
                ->with('error', 'Only pending Pre-Challan can be sent for approval.');
        }
        $report->update([
            'status' => 'sent_for_approval',
        ]);
        return redirect()->route('prechallan.index')
            ->with('success', 'Pre-Challan sent for approval successfully.');
    }
    public function updateStatus(Request $request, $id)
    {
        $report = PreChallanReport::findOrFail($id);

        if ($report->status != 'sent_for_approval') {
            return response()->json([
                'success' => false,
                'message' => 'Only Pre-Challan sent for approval can be updated.',
            ], 422);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $report->update([
                'status' => $request->status,
                'ho_remarks' => $request->remarks ?? $report->ho_remarks,
            ]);

            // ── On approval: snapshot the report data ─────────────────────────
            if ($request->status === 'approved') {
                $this->snapshotReport($report);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Re-run the report calculation for the approved branch + month
     * and persist per-student snapshot rows.
     */
    private function snapshotReport(PreChallanReport $report): void
    {
        $service = app(PreChallanReportService::class);
        $branchId = $report->branch_id;
        $month = $report->month;           // stored as 'Y-m' or 'Y-m-d' — normalise below
        $dateInput = \Carbon\Carbon::parse($month)->format('Y-m');
        $creatorId = $report->created_by;

        [$reportGroups] = $service->calculate($branchId, $dateInput, $creatorId);

        // Delete any previous snapshot for the same report (idempotent re-approval)
        PreChallanSnapshot::where('report_reference_id', $report->id)->delete();

        $rows = [];
        $now = now();

        foreach ($reportGroups as $branchGroup) {
            foreach ($branchGroup as $row) {
                $rows[] = [
                    'report_reference_id' => $report->id,
                    'student_id' => $row['student_id'],
                    'month' => $dateInput,
                    'gross' => $row['gross'],
                    'arrears' => $row['arrears'],
                    'net_receivable' => $row['net_receivable'],
                    'prev_month_amount' => $row['prev_month_amount'],
                    'difference' => $row['difference'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'owned_by' => $branchId,
                    'created_by' => $creatorId,
                ];
            }
        }

        // Chunk insert to avoid hitting DB placeholder limits
        foreach (array_chunk($rows, 500) as $chunk) {
            PreChallanSnapshot::insert($chunk);
        }
    }
    public function comparison(Request $request)
    {
        set_time_limit(0);

        $user = \Auth::user();
        $creatorId = $user->type == 'company' ? $user->creatorId() : $user->ownedId();
        $isCompany = $user->type == 'company';
        $rows = collect();
        // ── Branch dropdown ───────────────────────────────────────────────────────
        $branches = \DB::table('users')
            ->when(
                $isCompany,
                fn($q) => $q->where('type', 'branch')->where('is_active', 1)->where('created_by', $creatorId),
                fn($q) => $q->where('id', $creatorId)
            )
            ->pluck('name', 'id')
            ->prepend('All Branches', 'all');

        $selectedBranchId = $request->input('branches');
        $selectedDate = $request->input('date', now()->format('Y-m'));
        $report = collect();

        if ($request->has('date') || $request->has('branches')) {

            $month = $selectedDate; // 'Y-m'

            // ── 1. Pull pre-challan snapshots ─────────────────────────────────────
            $snapshotQuery = PreChallanSnapshot::with(['student.enrollment', 'student.class'])
                ->where('month', $month)
                ->where('created_by', $creatorId);

            if ($selectedBranchId && $selectedBranchId !== 'all') {
                $snapshotQuery->where('owned_by', $selectedBranchId);
            }

            $snapshots = $snapshotQuery->get()->keyBy('student_id');

            if ($snapshots->isEmpty()) {
                return view('studentReports.prechallan.comparison', [
                    'branches' => $branches,
                    'report' => collect(),
                    'selectedBranchId' => $selectedBranchId,
                    'selectedDate' => $selectedDate,
                ]);
            }

            $studentIds = $snapshots->keys()->all();

            // ── 2. Pull regular/advance challans for these students for the month ──
            $feeMonthFormatted = $month; // 'Y-m'  — fee_month is varchar, matched via DATE_FORMAT

            $regularChallans = Challans::select(
                'student_id',
                'owned_by',
                'class_id',
                'total_amount',
                'concession_amount',
                'challan_type',
                'other_months',
                'fee_month'
            )
                ->whereIn('student_id', $studentIds)
                ->whereNotIn('challan_type', ['Registration', 'Withdrawal', 'Admission'])
                ->where(function ($q) use ($feeMonthFormatted) {
                    // fee_month is a varchar that can hold any date in the month (e.g. 2026-05-03,
                    // 2026-05-01, etc.) so match only on Y-m using DATE_FORMAT
                    $q->whereRaw("DATE_FORMAT(STR_TO_DATE(fee_month, '%Y-%m-%d'), '%Y-%m') = ?", [$feeMonthFormatted])
                        // OR subscription/advance challan where other_months contains 'Y-m-01'
                        ->orWhere(function ($q2) use ($feeMonthFormatted) {
                        $q2->whereNotNull('other_months')
                            ->whereRaw("FIND_IN_SET(?, other_months)", [$feeMonthFormatted . '-01']);
                    });
                })
                ->whereRaw("LOWER(status) IN ('issued', 'pending', 'partially paid' , 'partial', 'paid')")
                ->get();
            // For advance/subscription challans that cover multiple months,
            // divide the net evenly across months (same logic as the main report).
            $regularNetByStudent = [];
            foreach ($regularChallans as $challan) {
                $net = (float) $challan->total_amount - (float) $challan->concession_amount;
                $otherMonths = array_filter(array_map('trim', explode(',', $challan->other_months ?? '')));
                $monthCount = max(1, count($otherMonths));

                // Annual-like heads are not split — but at challan level we can only
                // approximate by dividing total (mirrors applyMonthlySplit behaviour).
                $monthlyNet = round($net / $monthCount, 2);

                $sid = $challan->student_id;
                $regularNetByStudent[$sid] = ($regularNetByStudent[$sid] ?? 0) + $monthlyNet;
            }

            // ── 3. Fetch student meta (name, roll, class, adm_date) ───────────────
            $studentMeta = \App\Models\StudentRegistration::with(['class', 'enrollment'])
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');

            // ── 4. Build comparison rows ──────────────────────────────────────────
            $rows = $snapshots->map(function ($snap) use ($regularNetByStudent, $studentMeta) {

                $sid = $snap->student_id;
                $student = $studentMeta->get($sid);
                $regularNet = (float) ($regularNetByStudent[$sid] ?? 0);
                $preNet = (float) $snap->gross;
                $diff = $regularNet - $preNet;

                return [
                    'student_id' => $sid,
                    'roll_no' => $student->roll_no ?? '—',
                    'student_name' => $student->stdname ?? '—',
                    'father_name' => $student->fathername ?? '',
                    'class_name' => $student->class->name ?? '—',
                    'adm_date' => optional($student->enrollment)->adm_date
                        ? \Carbon\Carbon::parse($student->enrollment->adm_date)->format('d-M-Y')
                        : '—',
                    'owned_by' => $snap->owned_by,
                    'regular_net' => $regularNet,
                    'pre_challan_net' => $preNet,
                    'difference' => $diff,
                ];
            });

            // ── 5. Sort & group by branch ─────────────────────────────────────────
            $report = $rows
                ->sortBy(fn($r) => [$r['owned_by'], strtolower($r['student_name'])])
                ->groupBy('owned_by')
                ->sortKeys();
        }

        // ── Export ────────────────────────────────────────────────────────────────
        if ($request->has('export') && !$report->isEmpty()) {

            $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $selectedDate)->format('M-Y');
            $branchLabel = ($selectedBranchId && $selectedBranchId !== 'all')
                ? ($branches[$selectedBranchId] ?? 'Branch')
                : 'All Branches';

            $reportName = "Pre-Challan vs Regular Challan Comparison — {$monthLabel}";
            $filename = "PreChallan_Comparison_{$monthLabel}_{$branchLabel}";

            if ($request->export == 'excel') {
                return Excel::download(
                    new PreChallanComparisonExport($branches, $report, $selectedBranchId, $reportName,'excel'),
                    $filename . '.xlsx'
                );
            }

            if ($request->export == 'pdf') {
                return Excel::download(
                    new PreChallanComparisonExport($branches, $report, $selectedBranchId, $reportName,'pdf'),
                    $filename . '.pdf',
                    \Maatwebsite\Excel\Excel::MPDF
                );
            }
        }

        $sortedRows = $rows->sortBy(fn($r) => [$r['owned_by'], strtolower($r['student_name'])]);

        $totalStudents = $sortedRows->count();
        $regChallanTotal = $sortedRows->sum('regular_net');
        $preChallanTotal = $sortedRows->sum('pre_challan_net');
        $netDifferenceTotal = $sortedRows->sum('difference');

        $report = $sortedRows->groupBy('owned_by')->sortKeys();

        // ─────────────────────────────────────────────────────────────────────────
// Replace your final return view() with this:
// ─────────────────────────────────────────────────────────────────────────
        return view('studentReports.prechallan.comparison', [
            'branches' => $branches,
            'report' => $report,
            'totalStudents' => $totalStudents ?? 0,
            'regChallanTotal' => $regChallanTotal ?? 0,
            'preChallanTotal' => $preChallanTotal ?? 0,
            'netDifferenceTotal' => $netDifferenceTotal ?? 0,
            'selectedBranchId' => $selectedBranchId,
            'selectedDate' => $selectedDate,
        ]);
    }
}
