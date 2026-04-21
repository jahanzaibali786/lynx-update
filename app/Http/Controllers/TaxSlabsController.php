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
        $empScale = EmployeeScale::with('employeeScaleHeads')->where('id', $empScaleId)->first();
        $empScaleHeads = $empScale->employeeScaleHeads;
        $totalCharges = 0;
        foreach ($empScaleHeads as $head) {
            //initali basic + house rent
            if ($head->SalaryHeads->head == 'Initial Basic' || $head->SalaryHeads->head == 'House Rent') {
                $totalCharges += $head->head_value;
            }
        }
        $lastPayScale = EmployeeScale::with('employeeScaleHeads')->where('id', '<', $empScaleId)->orderBy('id', 'desc')->first();
        $otherAdditionsInSal = $lastPayScale->drns + $lastPayScale->conv + $lastPayScale->misc + $lastPayScale->other_add + $lastPayScale->chaild_concession;
        $totalCharges += $otherAdditionsInSal;
        //current month till next june
        $currentMonth = date('n');
        $months = (12 - $currentMonth) + 6;
        $prevmonthCount = ($currentMonth >= 7) ? $currentMonth - 7 + 1 : $currentMonth + 12 - 7 + 1;

        //previous calculations
        $startDate = ($currentMonth >= 7) ? date('Y-m-d', strtotime('first day of July this year')) : date('Y-m-d', strtotime('first day of July last year'));
        $endDate = date('Y-m-d', strtotime('last day of last month'));
        // dd($startDate,$endDate);
        $previousPaidSal = EmployeeMonthlySalary::with('salary_heads')
            ->where('employee_id', $request->employee_id)
            // ->where('status', 'paid')
            ->whereBetween('salary_date', [$startDate, $endDate])
            ->get();
        // dd($previousPaidSal);
        //last month salary generated or not
        $lastMonthSalary = EmployeeMonthlySalary::where('employee_id', $request->employee_id)
            ->whereMonth('salary_date', date('m'))
            ->whereYear('salary_date', date('Y'))
            ->first();

        if (!$lastMonthSalary) {
            $prevmonthCount = $prevmonthCount - 1;
            $months = $months + 1;
        }

        // dd($prevmonthCount,$months);
        $totalCharges = $totalCharges * $months;

        $prevSalAmnt = 0;
        $prevSubmittedTax = 0;
        $prevOtherAddition = 0;
        foreach ($previousPaidSal as $sal) {
            //basic and house rent
            $prevSubmittedTax += $sal->it;
            $prevOtherAddition += $sal->conv + $sal->misc + $sal->drns + $sal->other_add + $sal->chaild_con;

            foreach ($sal->salary_heads as $head) {
                if ($head->SalaryHead->head == 'Initial Basic' || $head->SalaryHead->head == 'House Rent') {
                    $prevSalAmnt += $head->head_value;
                }
            }
        }
        //basic and house rent\
        if ($lastMonthSalary) {
            $prevSubmittedTax += $lastMonthSalary->it;
            $prevOtherAddition += $lastMonthSalary->conv + $lastMonthSalary->misc + $lastMonthSalary->drns + $lastMonthSalary->other_add + $lastMonthSalary->chaild_con;

            foreach ($lastMonthSalary->salary_heads as $head) {
                if ($head->SalaryHead->head == 'Initial Basic' || $head->SalaryHead->head == 'House Rent') {
                    $prevSalAmnt += $head->head_value;
                }
            }
        }
        $prevSalAmnt = ($prevSalAmnt + $prevOtherAddition);

        //calcualtion start
        $yearlySal = $totalCharges + $prevSalAmnt;

        $currentYear = date('Y');
        if ($currentMonth <= 6) {
            $currentYear = $currentYear - 1;
        }
        $taxSlabs = TaxSlab::where('year', $currentYear)
            ->where(function ($query) use ($yearlySal) {
                $query->where(function ($q) use ($yearlySal) {
                    $q->where('lower_limit', '<=', $yearlySal)
                        ->where('upper_limit', '>=', $yearlySal);
                })
                    ->orWhere(function ($q) use ($yearlySal) {
                        $q->where('lower_limit', '<=', $yearlySal)
                            ->whereNull('upper_limit'); // for "unlimited" upper limit
                    });
            })
            ->first();
            // dd($yearlySal, $taxSlabs,$currentMonth);
        if (!$taxSlabs) {
            return response()->json(['error' => 'Tax slab not found for the year ' . $currentYear . '. Please contact admin.'], 404);
        }
        $taxAmount = 0;
        $yearlytax = $yearlySal - ($taxSlabs->lower_limit - 1);
        $totalTax = ($yearlytax / 100) * $taxSlabs->prev_limit_percentage;
        if ($totalTax > 0) {
            $taxAmount = (($totalTax + $taxSlabs->fixed_tax_amount) - ($prevSubmittedTax));
        }
        $perMonTax = $taxAmount / $months;
        if($perMonTax < 0){
            $perMonTax = 0;
        }
        // dd($perMonTax,$totalTax,$prevSubmittedTax,$yearlytax);
        return response()->json(['permonthtax' => round($perMonTax), 'totaltax' => round($totalTax + $taxSlabs->fixed_tax_amount), 'prevTax' => round($prevSubmittedTax)]);
    }
}
