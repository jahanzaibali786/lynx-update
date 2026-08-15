<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeContractController extends Controller
{
    /**
     * Update the specified contract in storage.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->type !== 'company' || !Auth::user()->can('edit employee')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $contract = EmployeeContract::findOrFail($id);

        // Multi-company security: check tenant ownership
        if ($contract->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after:from_date',
            'status' => 'required|in:active,expired,terminated',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        // Prevent overlapping contract periods for the same employee
        $overlapping = EmployeeContract::where('employee_id', $contract->employee_id)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->where('from_date', '<=', $request->to_date)
                      ->where('to_date', '>=', $request->from_date);
            })
            ->exists();

        if ($overlapping) {
            return redirect()->back()->withInput()->with('error', __('The contract dates overlap with an existing contract period.'));
        }

        $contract->from_date = $request->from_date;
        $contract->to_date = $request->to_date;
        $contract->status = $request->status;
        $contract->remarks = $request->remarks;
        $contract->updated_by = Auth::user()->id;
        $contract->save();

        return redirect()->back()->with('success', __('Contract updated successfully.'));
    }

    /**
     * Renew the contract for a visiting employee.
     */
    public function renew(Request $request, $employeeId)
    {
        if (Auth::user()->type !== 'company' || !Auth::user()->can('edit employee')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employee = Employee::findOrFail($employeeId);

        // Multi-company security: check tenant ownership
        if ($employee->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($employee->category !== 'Visiting' && $employee->category !== 'Adhoc') {
            return redirect()->back()->with('error', __('Contracts can only be managed for Visiting and Adhoc employees.'));
        }

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after:from_date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        // Prevent overlapping contract periods for the same employee
        $overlapping = EmployeeContract::where('employee_id', $employeeId)
            ->where(function ($query) use ($request) {
                $query->where('from_date', '<=', $request->to_date)
                      ->where('to_date', '>=', $request->from_date);
            })
            ->exists();

        if ($overlapping) {
            return redirect()->back()->withInput()->with('error', __('The new contract dates overlap with an existing contract period.'));
        }

        DB::beginTransaction();
        try {
            // Mark all previous active contracts as expired
            EmployeeContract::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            // Create new active contract
            EmployeeContract::create([
                'employee_id' => $employeeId,
                'created_by' => Auth::user()->creatorId(),
                'owned_by' => $employee->owned_by, // Branch ID from employee
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'status' => 'active',
                'remarks' => $request->remarks,
                'added_by' => Auth::user()->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', __('Contract renewed successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Error renewing contract: ') . $e->getMessage());
        }
    }
}
