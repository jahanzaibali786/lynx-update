<?php

namespace App\Http\Controllers;

use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeScale;
use App\Models\TaxSlab;
use Illuminate\Http\Request;

class TaxSlabsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tax_slabs = TaxSlab::all();
        return view('tax_slabs.index', compact('tax_slabs'));
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
        $empScaleId = $request->empScaleId;
        // -----------------------------
        // 1. EMPLOYEE SCALE
        // -----------------------------
        $empScale = EmployeeScale::with(['employeeScaleHeads.SalaryHeads'])
            ->where('id', $empScaleId)
            ->first();

        if (!$empScale) {
            return response()->json(['error' => 'Employee scale not found'], 404);
        }

        // -----------------------------
        // 2. CURRENT MONTHLY SALARY
        // -----------------------------
        $monthlySalary = 0;

        foreach ($empScale->employeeScaleHeads as $head) {
            if (
                $head->SalaryHeads &&
                in_array($head->SalaryHeads->head, ['Initial Basic', 'House Rent'])
            ) {
                $monthlySalary += $head->head_value;
            }
        }
        // previous scale additions

            $lastPayScale = EmployeeScale::where('id', '<', $empScaleId)
            ->orderBy('id', 'desc')
            ->first();

            $lastPayScale = EmployeeScale::with('employeeScaleHeads')->where('id', '<', $empScaleId)->orderBy('id', 'desc')->first();
            $lastscale = EmployeeMonthlySalary::where('employee_id', $request->employee_id)
                ->orderBy('id', 'desc')
                ->first();

            $otherAdditionsInSal = 0;

            if ($lastscale) {
                $otherAdditionsInSal =
                    ($lastscale->drns ?? 0) +
                    ($lastscale->conv ?? 0) +
                    ($lastscale->misc ?? 0) +
                    ($lastscale->other_add ?? 0);
            }

            $monthlySalary += $otherAdditionsInSal;
        // -----------------------------
        // 3. LAST SALARY / REJOIN CHECK
        // -----------------------------
        $lastSalary = EmployeeMonthlySalary::where('employee_id', $request->employee_id)
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
            : (12 - $currentMonth + 6);

        // -----------------------------
        // 5. MODE SWITCH
        // -----------------------------

        $prevSubmittedTax = 0;
        $prevSalAmnt = 0;

        if ($isRejoin) {

            // =============================
            // 🔴 MODE 2: REJOIN / FRESH
            // =============================

            $months = $remainingMonths;

            $yearlySal = $monthlySalary * $months;

        } else {

            // =============================
            // 🟢 MODE 1: CONTINUATION
            // =============================

            $fyStart = $currentMonth >= 7
                ? \Carbon\Carbon::create(date('Y'), 7, 1)
                : \Carbon\Carbon::create(date('Y') - 1, 7, 1);

            $monthsPassed = $fyStart->diffInMonths(now()) + 1;

            $months = $monthsPassed;

            // previous salary
            $previousPaidSal = EmployeeMonthlySalary::with('salary_heads.SalaryHead', 'scaleHeads')
                ->where('employee_id', $request->employee_id)
                ->whereBetween('salary_date', [
                    $fyStart->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])
                ->get();
                // dd($previousPaidSal);
            // last salary
                $lastSalary = EmployeeMonthlySalary::with('scaleHeads')->where('employee_id', $request->employee_id)
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

            foreach ($previousPaidSal as $sal) {

                $prevSubmittedTax += $sal->it ?? 0;

                // foreach ($sal->salary_heads as $head) {
                //     if (
                //         $head->SalaryHead &&
                //         in_array($head->SalaryHead->head, ['Initial Basic', 'House Rent'])
                //     ) {
                //         $prevSalAmnt += $head->head_value;
                //     }
                // }

                 foreach ($sal->scaleHeads as $head) {
                    if (
                        $head->SalaryHeads &&
                        in_array($head->SalaryHeads->head, ['Initial Basic', 'House Rent'])
                    ) {
                        $prevSalAmnt += $head->head_value;
                    }
                }

                $prevSalAmnt +=
                    ($sal->conv ?? 0) +
                    ($sal->misc ?? 0) +
                    ($sal->drns ?? 0) +
                    ($sal->other_add ?? 0) +
                    ($sal->chaild_con ?? 0);
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
            return response()->json([
                'error' => 'Tax slab not found for year ' . $currentYear
            ], 404);
        }

        // -----------------------------
        // 8. TAX CALCULATION
        // -----------------------------
        $taxable = $yearlySal - ($taxSlabs->lower_limit - 1);

        $totalTax = ($taxable * $taxSlabs->prev_limit_percentage) / 100;

        $taxAmount = ($totalTax + $taxSlabs->fixed_tax_amount) - $prevSubmittedTax;

        // $taxable = max($yearlySal - $taxSlabs->lower_limit, 0);

        // $totalTax = ($taxable * $taxSlabs->prev_limit_percentage) / 100;
        // $taxAmount = max(
        //     ($totalTax + $taxSlabs->fixed_tax_amount) - $prevSubmittedTax,
        //     0
        // );
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
        return response()->json([
            'mode'          => $isRejoin ? 'REJOIN' : 'CONTINUE',
            'permonthtax'   => round($perMonTax),
            'totaltax'      => round($totalTax + $taxSlabs->fixed_tax_amount),
            'prevTax'       => round($prevSubmittedTax),
            'yearlySalary'  => round($yearlySal),
            'months'        => $months,
            'remainingMonths' => $remainingMonths,
            'gapMonths'     => $gapMonths
        ]);
    }
}
