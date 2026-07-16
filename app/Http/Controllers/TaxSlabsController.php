<?php

namespace App\Http\Controllers;

use App\Models\AdvanceTaxCollection;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeePayscaleDetail;
use App\Models\EmployeeScale;
use App\Models\User;
use App\Models\TaxSlab;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\RecentlyRevisedTaxesExport;
use Maatwebsite\Excel\Facades\Excel;

class TaxSlabsController extends Controller
{
    private function isCashPaymode($paymode)
    {
        return strtolower(trim((string) $paymode)) === 'cash';
    }

    private function isTaxableSalaryHead($headName)
    {
        return !in_array(strtolower(trim((string) $headName)), [
            'medical',
            'medical allowance',
        ], true);
    }

    private function approvedAdvanceTaxCollection($employeeId, Carbon $fromDate, Carbon $toDate)
    {
        return (float) AdvanceTaxCollection::where('employee_id', $employeeId)
            ->where('status', 1)
            ->whereBetween('tax_month', [
                $fromDate->copy()->startOfMonth()->format('Y-m-d'),
                $toDate->copy()->endOfMonth()->format('Y-m-d'),
            ])
            ->sum('amount');
    }

    private function getEmployeeScalePaymode($employeeId, $scaleId)
    {
        $today = Carbon::now()->format('Y-m-d');
        $query = EmployeePayscaleDetail::where('employee_id', $employeeId)
            ->whereNotNull('paymode')
            ->where('paymode', '!=', '')
            ->where(function ($query) use ($today) {
                $query->whereNull('effect_from')
                    ->orWhereDate('effect_from', '<=', $today);
            });

        if (!empty($scaleId)) {
            $scaleDetail = (clone $query)
                ->where('pay_scale_id', $scaleId)
                ->orderByRaw('effect_from IS NULL')
                ->orderBy('effect_from', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($scaleDetail) {
                return $scaleDetail->paymode;
            }
        }

        return $query
            ->orderByRaw('effect_from IS NULL')
            ->orderBy('effect_from', 'desc')
            ->orderBy('id', 'desc')
            ->value('paymode');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $currentCalYear = (int) date('Y');
        $fromYear = $request->input('from_year', $currentCalYear - 1);
        $toYear = $request->input('to_year', $currentCalYear);

        $query = TaxSlab::query();

        if (!empty($fromYear)) {
            $query->where('year', '>=', $fromYear);
        }
        if (!empty($toYear)) {
            $query->where('year', '<=', $toYear);
        }

        $tax_slabs = $query->orderBy('year', 'desc')->orderBy('no', 'asc')->get();

        $yearsList = range($currentCalYear - 10, $currentCalYear + 5);
        $years = array_combine($yearsList, $yearsList);

        return view('tax_slabs.index', compact('tax_slabs', 'years', 'fromYear', 'toYear'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tax_slabs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'year' => 'required|numeric',
                'no' => 'required|numeric',
                'lower_limit' => 'required|numeric',
                'upper_limit' => 'required|numeric',
                'fixed_tax_amount' => 'required|numeric',
                'prev_limit_percentage' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $tax_slab = new TaxSlab();
        $tax_slab->year = $request->year;
        $tax_slab->no = $request->no;
        $tax_slab->lower_limit = $request->lower_limit;
        $tax_slab->upper_limit = $request->upper_limit;
        $tax_slab->fixed_tax_amount = $request->fixed_tax_amount;
        $tax_slab->prev_limit_percentage = $request->prev_limit_percentage;
        $tax_slab->save();

        return redirect()->route('tax-slab.index')->with('success', __('Tax slab successfully created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tax_slab = TaxSlab::findOrFail($id);
        return view('tax_slabs.edit', compact('tax_slab'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'year' => 'required|numeric',
                'no' => 'required|numeric',
                'lower_limit' => 'required|numeric',
                'upper_limit' => 'required|numeric',
                'fixed_tax_amount' => 'required|numeric',
                'prev_limit_percentage' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $tax_slab = TaxSlab::findOrFail($id);
        $tax_slab->year = $request->year;
        $tax_slab->no = $request->no;
        $tax_slab->lower_limit = $request->lower_limit;
        $tax_slab->upper_limit = $request->upper_limit;
        $tax_slab->fixed_tax_amount = $request->fixed_tax_amount;
        $tax_slab->prev_limit_percentage = $request->prev_limit_percentage;
        $tax_slab->save();

        return redirect()->route('tax-slab.index')->with('success', __('Tax slab successfully updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        TaxSlab::findOrFail($id)->delete();
        return redirect()->route('tax-slab.index')->with('success', __('Tax slab successfully deleted.'));
    }

    public function calculateTax(Request $request)
    {
        $responseArray = $this->computeTaxForEmployee($request->employee_id, $request->empScaleId);
        
        if (isset($responseArray['error_code'])) {
            return response()->json(['error' => $responseArray['error']], $responseArray['error_code']);
        }

        return response()->json($responseArray);
    }

    private function computeTaxForEmployee($employee_id, $empScaleId)
    {
        $currentPaymode = $this->getEmployeeScalePaymode($employee_id, $empScaleId);
        $isCurrentCashPaymode = $this->isCashPaymode($currentPaymode);

        // -----------------------------
        // 1. EMPLOYEE SCALE
        // -----------------------------
        $empScale = EmployeeScale::with(['employeeScaleHeads.SalaryHeads'])
            ->where('id', $empScaleId)
            ->first();

        if (!$empScale) {
            return ['error' => 'Employee scale not found', 'error_code' => 404];
        }

        // -----------------------------
        // 2. CURRENT MONTHLY SALARY
        // -----------------------------
        $monthlySalary = 0;

        if (!$isCurrentCashPaymode) {
            foreach ($empScale->employeeScaleHeads as $head) {
                if (
                    $head->SalaryHeads &&
                    $this->isTaxableSalaryHead($head->SalaryHeads->head)
                ) {
                    $monthlySalary += $head->head_value;
                }
            }
        }
        // previous scale additions

            $lastPayScale = EmployeeScale::where('id', '<', $empScaleId)
            ->orderBy('id', 'desc')
            ->first();

            $lastPayScale = EmployeeScale::with('employeeScaleHeads')->where('id', '<', $empScaleId)->orderBy('id', 'desc')->first();
            $lastscale = EmployeeMonthlySalary::where('employee_id', $employee_id)
                ->orderBy('id', 'desc')
                ->first();

            $otherAdditionsInSal = 0;

            if ($lastscale && !$isCurrentCashPaymode) {
                $otherAdditionsInSal =
                    ($lastscale->drns ?? 0) +
                    ($lastscale->misc ?? 0) +
                    ($lastscale->other_add ?? 0);
            }

            $monthlySalary += $otherAdditionsInSal;
        // -----------------------------
        // 3. LAST SALARY / REJOIN CHECK
        // -----------------------------
        $lastSalary = EmployeeMonthlySalary::where('employee_id', $employee_id)
            ->orderBy('salary_date', 'desc')
            ->first();

        $isRejoin = false;
        $gapMonths = 0;

        if ($lastSalary) {
            $gapMonths = \Carbon\Carbon::parse($lastSalary->salary_date)
                ->diffInMonths(now());

            if ($gapMonths > 6) {
                $isRejoin = true;
            }
        } else {
            $isRejoin = true; // brand new employee
        }

        // -----------------------------
        // 4. MONTH CALCULATION
        // -----------------------------
        $currentMonth = date('n');

        // remaining months till June
        $remainingMonths = ($currentMonth <= 6)
            ? (6 - $currentMonth + 1)
            : (12 - $currentMonth + 7);

        // -----------------------------
        // 5. MODE SWITCH
        // -----------------------------

        $prevSubmittedTax = 0;
        $salaryTaxReceived = 0;
        $advanceTaxCollection = 0;
        $prevSalAmnt = 0;
        $monthlySalaryHeads = [];
        $monthlySalaryTot = [];

        if ($isRejoin) {

            // =============================
            // 🔴 MODE 2: REJOIN / FRESH
            // =============================

            $months = $remainingMonths;

            $yearlySal = $monthlySalary * $months;
            $fyStart = $currentMonth >= 7
                ? Carbon::create(date('Y'), 7, 1)
                : Carbon::create(date('Y') - 1, 7, 1);
            $advanceTaxCollection = $this->approvedAdvanceTaxCollection($employee_id, $fyStart, Carbon::now());
            $prevSubmittedTax += $advanceTaxCollection;

        } else {

            // =============================
            // 🟢 MODE 1: CONTINUATION
            // =============================

            $fyStart = $currentMonth >= 7
                ? Carbon::create(date('Y'), 7, 1)
                : Carbon::create(date('Y') - 1, 7, 1);

            $monthsPassed = $fyStart->diffInMonths(now()) + 1;

            $months = $monthsPassed;

            // previous salary
            $previousPaidSal = EmployeeMonthlySalary::with('scaleHeads.salaryHeads')
                ->where('employee_id', $employee_id)
                ->whereBetween('salary_date', [
                    $fyStart->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])
                ->where(function ($query) {
                    $query->whereNull('paymode')
                        ->orWhereRaw('LOWER(TRIM(paymode)) != ?', ['cash']);
                })
                ->get();
            $salaryTaxReceived = (float) $previousPaidSal->sum('it');
            $advanceTaxCollection = $this->approvedAdvanceTaxCollection($employee_id, $fyStart, Carbon::now());
            $prevSubmittedTax = $salaryTaxReceived + $advanceTaxCollection;

            // last salary
                $lastSalary = EmployeeMonthlySalary::with('scaleHeads')->where('employee_id', $employee_id)
                    ->orderBy('salary_date', 'desc')
                    ->first();
                $missingMonths = 0;

                if ($lastSalary) {
                     $lastMonthDate = \Carbon\Carbon::parse($lastSalary->salary_date)->startOfMonth();
                    $currentMonthDate = now()->startOfMonth();

                    $missingMonths = max(
                        $lastMonthDate->diffInMonths($currentMonthDate) - 1,
                        0
                    );
                }
            $monthlySalaryHeads = [];
            $monthlySalaryTot = [];
            foreach ($previousPaidSal as $sal) {

                $monthTotal = 0;
                foreach ($sal->scaleHeads as $head) {
                    if (
                        $head->salaryHeads &&
                        $this->isTaxableSalaryHead($head->salaryHeads->head)
                    ) {
                        $prevSalAmnt += $head->head_value;
                        $monthTotal += $head->head_value;
                    }
                }

                $prevSalAmnt +=
                    ($sal->misc ?? 0) +
                    ($sal->drns ?? 0) +
                    ($sal->other_add ?? 0);
                $month = date('Y-m', strtotime($sal->salary_date));

                $monthlySalaryHeads[$month] = ($monthlySalaryHeads[$month] ?? 0) + $monthTotal;
                $monthlySalaryTot[$month] = ($monthlySalaryTot[$month] ?? 0) + $prevSalAmnt;
            }
            // projected future
            $futureMonths = $remainingMonths + $missingMonths;

            $yearlySal = $prevSalAmnt + ($monthlySalary * $futureMonths);
        }

        // -----------------------------
        // 6. TAX YEAR
        // -----------------------------
        $currentYear = date('Y');
        if ($currentMonth <= 6) {
            $currentYear--;
        }

        // -----------------------------
        // 7. TAX SLAB
        // -----------------------------
        $taxSlabs = TaxSlab::where('year', $currentYear)
            ->where(function ($query) use ($yearlySal) {
                $query->where(function ($q) use ($yearlySal) {
                    $q->where('lower_limit', '<=', $yearlySal)
                        ->where('upper_limit', '>=', $yearlySal);
                })
                ->orWhere(function ($q) use ($yearlySal) {
                    $q->where('lower_limit', '<=', $yearlySal)
                        ->whereNull('upper_limit');
                });
            })
            ->first();
        if (!$taxSlabs) {
            if ($isCurrentCashPaymode) {
                return [
                    'mode' => 'CASH_EXEMPT',
                    'permonthtax' => 0,
                    'totaltax' => 0,
                    'prevTax' => round($prevSubmittedTax),
                    'salaryTaxReceived' => round($salaryTaxReceived),
                    'advanceTaxCollection' => round($advanceTaxCollection),
                    'yearlySalary' => round($yearlySal),
                    'months' => $months,
                    'remainingMonths' => $remainingMonths,
                    'gapMonths' => $gapMonths,
                    'paymode' => $currentPaymode,
                    'message' => 'Cash paymode is exempt from upcoming tax. Previous collected tax is shown.',
                ];
            }

            return ['error' => 'Tax slab not found for year ' . $currentYear, 'error_code' => 404];
        }

        // -----------------------------
        // 8. TAX CALCULATION
        // -----------------------------
        $taxable = $yearlySal - ($taxSlabs->lower_limit - 1);

        $totalTax = ($taxable * $taxSlabs->prev_limit_percentage) / 100;

        $taxAmount = ($totalTax + $taxSlabs->fixed_tax_amount) - $prevSubmittedTax;

        // -----------------------------
        // 9. MONTHLY TAX
        // -----------------------------
       // divisor logic
        $divisor = $isRejoin ? $months : ($remainingMonths + $missingMonths);

        // monthly tax
        $perMonTax = $divisor > 0 ? $taxAmount / $divisor : 0;

        // safety
        $perMonTax = max(0, $perMonTax);

        // -----------------------------
        // 10. RESPONSE
        // -----------------------------
        return [
            'mode'          => $isCurrentCashPaymode ? 'CASH_EXEMPT' : ($isRejoin ? 'REJOIN' : 'CONTINUE'),
            'permonthtax'   => round($perMonTax),
            'totaltax'      => round($totalTax + $taxSlabs->fixed_tax_amount),
            'prevTax'       => round($prevSubmittedTax),
            'salaryTaxReceived' => round($salaryTaxReceived),
            'advanceTaxCollection' => round($advanceTaxCollection),
            'yearlySalary'  => round($yearlySal),
            'months'        => $months,
            'remainingMonths' => $remainingMonths,
            'gapMonths'     => $gapMonths,
            'paymode'       => $currentPaymode,
            'message'       => $isCurrentCashPaymode ? 'Cash paymode is exempt from upcoming tax. Previous collected tax is shown.' : null,
            'monthlySalaryHeads' => $monthlySalaryHeads,
            'monthlySalaryTot' => $monthlySalaryTot,
        ];
    }

    public function showRevisePage(Request $request)
    {
        $currentMonth = date('n');
        
        // Clear recently updated session if reset clicked
        if ($request->input('clear_session') == 1) {
            session()->forget('recently_updated_employees');
        }

        // Fetch branches based on user type (same logic as TransferController)
        $branches = User::where('type', '=', 'branch')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        $branches->prepend(__('Select Branch'), '');

        $branchId = $request->input('branch_id');
        
        $currentYear = date('Y');
        if ($currentMonth <= 6) {
            $currentYear--;
        }
        $taxSlabExists = TaxSlab::where('year', $currentYear)->exists();

        $recentlyUpdated = session('recently_updated_employees', []);

        $taxableHeads = \DB::table('salary_heads')
            ->whereRaw('LOWER(TRIM(head)) NOT IN (?, ?)', ['medical', 'medical allowance'])
            ->orderBy('id')
            ->pluck('head')
            ->toArray();

        return view('tax_slabs.revise', compact('branches', 'branchId', 'taxSlabExists', 'recentlyUpdated', 'taxableHeads'));
    }

    public function reviseEmployeeTaxes(Request $request)
    {
        if (date('m') != 7) {
            return redirect()->back()->with('error', __('Tax revision is only allowed in July.'));
        }

        $currentYear = date('Y');
        $taxSlabExists = TaxSlab::where('year', $currentYear)->exists();

        if (!$taxSlabExists) {
            return redirect()->back()->with('error', __('Tax slab for current year is not created yet.'));
        }

        $branchId = $request->input('branch_id');
        $query = \App\Models\Employee::where('is_res_ter', 0);
        if (!empty($branchId)) {
            $query->where('owned_by', $branchId);
        }
        $employees = $query->get();
        $updatedCount = 0;
        $updatedEmployees = [];

        foreach ($employees as $employee) {
            $latestScaleDetail = \App\Models\EmployeePayscaleDetail::with(['scale.employeeScaleHeads.SalaryHeads'])
                ->where('employee_id', $employee->id)
                ->orderBy('id', 'desc')
                ->first();

            if (!$latestScaleDetail) {
                continue; // Skip if employee doesn't have a scale detail
            }

            $taxResult = $this->computeTaxForEmployee($employee->id, $latestScaleDetail->pay_scale_id);

            if (isset($taxResult['error_code'])) {
                continue; // Skip if tax cannot be computed
            }

            $newTax = isset($taxResult['permonthtax']) ? (int) round($taxResult['permonthtax']) : 0;
            $oldTax = isset($latestScaleDetail->itax) ? (int) round($latestScaleDetail->itax) : 0;

            if ($newTax !== $oldTax) {
                $oldNet = (int) round($latestScaleDetail->net);
                $newNet = (int) round($latestScaleDetail->net + $oldTax - $newTax);

                $newScaleDetail = $latestScaleDetail->replicate();
                $newScaleDetail->itax = $newTax;
                // net = old_net + old_itax - new_itax
                $newScaleDetail->net = $newNet;
                // effect_from will remain the same as the user requested: "dont change this data effect_from"
                $newScaleDetail->created_at = \Carbon\Carbon::now();
                $newScaleDetail->updated_at = \Carbon\Carbon::now();
                $newScaleDetail->save();

                 $headValues = [];
                $gross = 0;
                if ($latestScaleDetail->scale) {
                    foreach ($latestScaleDetail->scale->employeeScaleHeads as $scaleHead) {
                        if ($scaleHead->SalaryHeads && $this->isTaxableSalaryHead($scaleHead->SalaryHeads->head)) {
                            $hName = $scaleHead->SalaryHeads->head;
                            $val = (float) $scaleHead->head_value;
                            $headValues[$hName] = $val;
                            $gross += $val;
                        }
                    }
                }

                $otherIncome = (float) ($latestScaleDetail->other_add ?? 0);
                $gross += $otherIncome;

                $updatedEmployees[] = (object) [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'branch_name' => optional($employee->branch)->name ?? '-',
                    'payScale' => optional($latestScaleDetail->scale)->name ?? '-',
                    'scale_no' => optional($latestScaleDetail->scale)->scale_no ?? '-',
                    'heads' => $headValues,
                    'other_income' => $otherIncome,
                    'gross' => $gross,
                    'oldTax' => $oldTax,
                    'newTax' => $newTax,
                    'taxChange' => $newTax - $oldTax,
                    'oldNet' => $oldNet,
                    'newNet' => $newNet,
                    'netChange' => $newNet - $oldNet,
                ];

                $updatedCount++;
            }
        }

        // Store standard session data so it can be downloaded via GET request
        session(['recently_updated_employees' => $updatedEmployees]);

        return redirect()->route('tax-slab.showRevisePage', ['branch_id' => $branchId])
            ->with('success', __("Tax revised successfully. $updatedCount employee(s) scale histories updated."));
    }

    public function exportRecentlyRevised(Request $request)
    {
        $recentlyUpdated = session('recently_updated_employees', []);

        if (empty($recentlyUpdated)) {
            return redirect()->back()->with('error', __('No revised employees records found to export.'));
        }

        $taxableHeads = \DB::table('salary_heads')
            ->whereRaw('LOWER(TRIM(head)) NOT IN (?, ?)', ['medical', 'medical allowance'])
            ->orderBy('id')
            ->pluck('head')
            ->toArray();

        return Excel::download(new RecentlyRevisedTaxesExport($recentlyUpdated, $taxableHeads), 'recently_revised_employees_tax.xlsx');
    }
}
