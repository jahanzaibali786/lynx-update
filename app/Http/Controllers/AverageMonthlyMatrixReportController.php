<?php

namespace App\Http\Controllers;

use App\Exports\AverageMonthlyMatrixReportExport;
use App\Models\FeeHead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AverageMonthlyMatrixReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();

        if ($user->type === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $branches->prepend('All Branches', 'all');

            $selectedBranch = $request->input('branches', 'all');

            $branchIds = $selectedBranch === 'all'
                ? $branches->keys()
                    ->reject(fn ($key) => $key === 'all')
                    ->values()
                    ->all()
                : [$selectedBranch];
        } else {
            $branches = User::where('id', $ownedId)
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $selectedBranch = $ownedId;
            $branchIds = [$ownedId];
        }

        $branchName = $selectedBranch === 'all'
            ? 'LYNX NETWORK'
            : ($branches[$selectedBranch] ?? ($branches[$ownedId] ?? 'Branch'));

        $tuitionHead = FeeHead::whereRaw(
                'LOWER(fee_head) LIKE ?',
                ['%tuition%']
            )
            ->when(
                $user->type === 'company',
                fn ($query) => $query->where('created_by', $creatorId),
                fn ($query) => $query->where('owned_by', $ownedId)
            )
            ->first();

        if (! $tuitionHead) {
            return back()->with(
                'error',
                'Tuition fee head not found.'
            );
        }

        [$fromDate, $toDate] = $this->resolveDateRange($request);

        $months = $this->buildMonths(
            $fromDate,
            $toDate
        );

        $yearGroups = collect($months)
            ->groupBy('year')
            ->map(fn ($items) => $items->values()->all())
            ->all();

        $monthMetrics = $this->fetchMonthMetrics(
            $user,
            $tuitionHead->id,
            $branchIds,
            $fromDate,
            $toDate
        );

        $reportRows = $this->buildReportRows(
            $branches,
            $branchIds,
            $months,
            $monthMetrics
        );

        $grandTotals = $this->buildGrandTotals(
            $months,
            $reportRows
        );

        $reportName = 'Average Monthly Fee & Discount Percentage Report';

        $fromLabel = $fromDate->format('M-y');
        $toLabel = $toDate->format('M-y');

        $yearLabel = count($yearGroups) === 1
            ? array_key_first($yearGroups)
            : null;

        if ($request->get('export') === 'excel') {
            return Excel::download(
                new AverageMonthlyMatrixReportExport(
                    $branches,
                    $reportRows,
                    $months,
                    $yearGroups,
                    $grandTotals,
                    $branchName,
                    $reportName,
                    $fromLabel,
                    $toLabel,
                    $fromDate->toDateString(),
                    $toDate->toDateString(),
                    $yearLabel
                ),
                'average_monthly_report.xlsx'
            );
        }

        if ($request->get('print') === 'pdf') {
            return Excel::download(
                new AverageMonthlyMatrixReportExport(
                    $branches,
                    $reportRows,
                    $months,
                    $yearGroups,
                    $grandTotals,
                    $branchName,
                    $reportName,
                    $fromLabel,
                    $toLabel,
                    $fromDate->toDateString(),
                    $toDate->toDateString(),
                    $yearLabel
                ),
                'average_monthly_report.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }

        return view(
            'studentReports.average_monthly_report',
            compact(
                'branches',
                'branchName',
                'months',
                'yearGroups',
                'reportRows',
                'grandTotals',
                'reportName',
                'fromLabel',
                'toLabel',
                'yearLabel',
                'selectedBranch'
            )
        );
    }

    private function resolveDateRange(Request $request): array
    {
        $fromInput = $request->input('month_from');
        $toInput = $request->input('month_to');

        $fromDate = $fromInput
            ? Carbon::parse($fromInput)->startOfMonth()
            : now()->startOfYear()->startOfMonth();

        $toDate = $toInput
            ? Carbon::parse($toInput)->endOfMonth()
            : now()->endOfYear()->endOfMonth();

        if ($fromDate->gt($toDate)) {
            [
                $fromDate,
                $toDate
            ] = [
                $toDate->copy()->startOfMonth(),
                $fromDate->copy()->endOfMonth()
            ];
        }

        return [$fromDate, $toDate];
    }

    private function buildMonths(
        Carbon $fromDate,
        Carbon $toDate
    ): array {
        $months = [];

        $cursor = $fromDate
            ->copy()
            ->startOfMonth();

        while ($cursor->lte($toDate)) {
            $months[] = [
                'key' => $cursor->format('Y-m-01'),
                'year' => $cursor->format('Y'),
                'month' => $cursor->format('m'),
                'month_label' => $cursor->format('M'),
                'month_year_label' => $cursor->format('M-y'),
            ];

            $cursor->addMonth();
        }

        return $months;
    }

    private function fetchMonthMetrics(
        $user,
        int $tuitionHeadId,
        array $branchIds,
        Carbon $fromDate,
        Carbon $toDate
    ) {
        $reportMonths = [];

        $monthCursor = $fromDate
            ->copy()
            ->startOfMonth();

        while ($monthCursor->lte($toDate)) {
            $reportMonths[] = $monthCursor->format('Y-m-01');
            $monthCursor->addMonth();
        }

        $effectiveDiscountSql = "
            CASE
                WHEN challan_heads.concession IS NULL
                    OR challan_heads.concession = 0
                THEN COALESCE(challans.concession_amount, 0)
                ELSE challan_heads.concession
            END
        ";

        $query = DB::table('challan_heads')
            ->join(
                'challans',
                'challan_heads.challan_id',
                '=',
                'challans.id'
            )
            ->where(
                'challan_heads.head_id',
                $tuitionHeadId
            );

        /*
         * Single month challans:
         *     other_months IS NULL / empty
         *     -> use fee_month
         *
         * Multi month challans:
         *     other_months contains CSV months
         *     -> use those months
         */
        $query->where(function ($query) use (
            $fromDate,
            $toDate,
            $reportMonths
        ) {
            $query->where(function ($singleMonthQuery) use (
                $fromDate,
                $toDate
            ) {
                $singleMonthQuery
                    ->where(function ($nullQuery) {
                        $nullQuery
                            ->whereNull('challans.other_months')
                            ->orWhereRaw(
                                "TRIM(COALESCE(challans.other_months, '')) = ''"
                            );
                    })
                    ->whereBetween(
                        'challans.fee_month',
                        [
                            $fromDate->copy()->startOfMonth()->toDateString(),
                            $toDate->copy()->endOfMonth()->toDateString()
                        ]
                    );
            });

            $query->orWhere(function ($multiMonthQuery) use (
                $reportMonths
            ) {
                $multiMonthQuery
                    ->whereNotNull('challans.other_months')
                    ->whereRaw(
                        "TRIM(challans.other_months) <> ''"
                    )
                    ->where(function ($monthQuery) use (
                        $reportMonths
                    ) {
                        foreach ($reportMonths as $monthKey) {
                            $monthQuery->orWhereRaw(
                                "
                                FIND_IN_SET(
                                    ?,
                                    REPLACE(
                                        challans.other_months,
                                        ' ',
                                        ''
                                    )
                                ) > 0
                                ",
                                [$monthKey]
                            );
                        }
                    });
            });
        });

        if ($user->type === 'company') {
            $query->whereIn(
                'challans.owned_by',
                $branchIds
            );
        } else {
            $query->where(
                'challans.owned_by',
                $user->ownedId()
            );
        }

        $challans = $query
            ->selectRaw("
                challans.id AS challan_id,
                challans.owned_by AS branch_id,
                challans.fee_month,
                challans.other_months,
                challan_heads.price AS gross_amount,
                ({$effectiveDiscountSql}) AS discount_amount
            ")
            ->get();

        $metrics = [];

        foreach ($challans as $challan) {
            $otherMonths = trim(
                (string) ($challan->other_months ?? '')
            );

            if ($otherMonths !== '') {
                $challanMonths = collect(
                    explode(',', $otherMonths)
                )
                    ->map(function ($month) {
                        $month = trim($month);

                        if ($month === '') {
                            return null;
                        }

                        return Carbon::parse($month)
                            ->startOfMonth()
                            ->format('Y-m-01');
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            } else {
                $challanMonths = [
                    Carbon::parse($challan->fee_month)
                        ->startOfMonth()
                        ->format('Y-m-01')
                ];
            }

            if (empty($challanMonths)) {
                $challanMonths = [
                    Carbon::parse($challan->fee_month)
                        ->startOfMonth()
                        ->format('Y-m-01')
                ];
            }

            /*
             * IMPORTANT:
             * Use ALL months of the challan when dividing amount.
             *
             * If challan covers June + July and report is only July,
             * July still receives 1/2.
             */
            $numberOfMonths = count($challanMonths);

            if ($numberOfMonths <= 0) {
                $numberOfMonths = 1;
            }

            $grossAmount = (float) (
                $challan->gross_amount ?? 0
            );

            $discountAmount = (float) (
                $challan->discount_amount ?? 0
            );

            $payableAmount = $grossAmount - $discountAmount;

            $monthlyGross = $grossAmount / $numberOfMonths;
            $monthlyDiscount = $discountAmount / $numberOfMonths;
            $monthlyPayable = $payableAmount / $numberOfMonths;

            foreach ($challanMonths as $monthKey) {
                if (! in_array(
                    $monthKey,
                    $reportMonths,
                    true
                )) {
                    continue;
                }

                $branchId = $challan->branch_id;

                if (! isset(
                    $metrics[$branchId][$monthKey]
                )) {
                    $metrics[$branchId][$monthKey] = [
                        'gross_total' => 0,
                        'discount_total' => 0,
                        'payable_total' => 0,
                        'challan_ids' => [],
                    ];
                }

                $metrics[$branchId][$monthKey]['gross_total']
                    += $monthlyGross;

                $metrics[$branchId][$monthKey]['discount_total']
                    += $monthlyDiscount;

                $metrics[$branchId][$monthKey]['payable_total']
                    += $monthlyPayable;

                /*
                 * Unique challan count.
                 *
                 * This is used as TOTAL STUDENTS as requested.
                 */
                $metrics[$branchId][$monthKey]['challan_ids'][
                    $challan->challan_id
                ] = true;
            }
        }

        return collect($metrics)
            ->map(function ($branchMonths) {
                return collect($branchMonths)
                    ->map(function ($metric) {
                        $challanCount = count(
                            $metric['challan_ids'] ?? []
                        );

                        return (object) [
                            'gross_total' => round(
                                $metric['gross_total'] ?? 0,
                                4
                            ),

                            'discount_total' => round(
                                $metric['discount_total'] ?? 0,
                                4
                            ),

                            'payable_total' => round(
                                $metric['payable_total'] ?? 0,
                                4
                            ),

                            'head_count' => $challanCount,

                            /*
                             * Explicit field for display.
                             */
                            'total_students' => $challanCount,
                        ];
                    });
            });
    }

    private function buildReportRows(
        $branches,
        array $branchIds,
        array $months,
        $monthMetrics
    ): array {
        $rows = [];

        foreach ($branchIds as $branchId) {
            $row = [
                'branch_id' => $branchId,
                'branch_name' => $branches[$branchId] ?? 'Unknown Branch',
                'months' => [],
            ];

            foreach ($months as $month) {
                $metric = data_get(
                    $monthMetrics,
                    $branchId . '.' . $month['key']
                );

                $gross = (float) (
                    $metric->gross_total ?? 0
                );

                $discount = (float) (
                    $metric->discount_total ?? 0
                );

                $payable = (float) (
                    $metric->payable_total ?? 0
                );

                $count = (int) (
                    $metric->total_students
                    ?? $metric->head_count
                    ?? 0
                );

                $row['months'][$month['key']] = [
                    /*
                     * Average is calculated directly using
                     * monthly payable / total challans.
                     */
                    'avg_fee' => $count > 0
                        ? round(
                            $payable / $count,
                            2
                        )
                        : 0,

                    'total_students' => $count,

                    'discount_percent' => $gross > 0
                        ? round(
                            ($discount / $gross) * 100,
                            4
                        )
                        : 0,

                    'gross_total' => $gross,
                    'discount_total' => $discount,
                    'payable_total' => $payable,
                    'head_count' => $count,
                ];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function buildGrandTotals(
        array $months,
        array $reportRows
    ): array {
        $grandTotals = [];

        foreach ($months as $month) {
            $grossTotal = 0;
            $discountTotal = 0;
            $payableTotal = 0;
            $totalStudents = 0;

            foreach ($reportRows as $row) {
                $cell = $row['months'][
                    $month['key']
                ] ?? [];

                $studentCount = (int) (
                    $cell['total_students']
                    ?? $cell['head_count']
                    ?? 0
                );

                $grossTotal += (float) (
                    $cell['gross_total'] ?? 0
                );

                $discountTotal += (float) (
                    $cell['discount_total'] ?? 0
                );

                $payableTotal += (float) (
                    $cell['payable_total'] ?? 0
                );

                $totalStudents += $studentCount;
            }

            $grandTotals[$month['key']] = [
                /*
                 * IMPORTANT:
                 *
                 * Previous version averaged branch averages.
                 *
                 * Now overall average is correctly based on:
                 *
                 * TOTAL PAYABLE / TOTAL STUDENTS
                 */
                'avg_fee' => $totalStudents > 0
                    ? round(
                        $payableTotal / $totalStudents,
                        2
                    )
                    : 0,

                'total_students' => $totalStudents,

                'discount_percent' => $grossTotal > 0
                    ? round(
                        ($discountTotal / $grossTotal) * 100,
                        4
                    )
                    : 0,

                'gross_total' => $grossTotal,
                'discount_total' => $discountTotal,
                'payable_total' => $payableTotal,
            ];
        }

        return $grandTotals;
    }
}