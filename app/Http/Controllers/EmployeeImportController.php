<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeImportSampleExport;
use App\Imports\EmployeeImport;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeImportController extends Controller
{
    public function downloadSample(Request $request)
    {
        $branchId = $request->input('branch');
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');
        $status = $request->input('status');

        return Excel::download(
            new EmployeeImportSampleExport($branchId, $departmentId, $designationId, $status),
            'employee_update_sample.xlsx'
        );
    }

    public function showImportForm()
    {
        $userType = \Auth::user()->type;
        $userCreatorId = \Auth::user()->creatorId();
        $userOwnedId = \Auth::user()->ownedId();

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')->where('created_by', $userCreatorId)->where('is_active', 1)->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('All Branches', 'all');
        } else {
            $branches = User::where('id', $userOwnedId)->where('is_active', 1)->pluck('name', 'id');
            $branches->prepend('All Branches', 'all');
        }

        $departments = Department::where('created_by', $userCreatorId)->pluck('name', 'id');
        $departments->prepend('All Departments', 'all');

        $designations = Designation::where('created_by', $userCreatorId)->pluck('name', 'id');
        $designations->prepend('All Designations', 'all');

        $statuses = [
            '' => 'All Statuses',
            '0' => 'Active',
            '1' => 'Resigned',
            '2' => 'Terminated',
        ];

        return view('employee.employee_import', compact('branches', 'departments', 'designations', 'statuses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new EmployeeImport;
            Excel::import($import, $request->file('file'));

            $success = $import->getSuccessCount();
            $errors = $import->getErrorCount();
            $skipped = $import->getSkipCount();
            $errorDetails = $import->getErrors();

            $message = "{$success} employee(s) updated successfully.";
            if ($skipped > 0 || $errors > 0) {
                $message .= " {$skipped} row(s) skipped (no match), {$errors} error(s).";
                return redirect()->route('employee.bulk.update')
                    ->with('warning', $message)
                    ->with('import_errors', $errorDetails);
            }

            return redirect()->route('employee.bulk.update')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('employee.bulk.update')->with('error', __('Import failed: ') . $e->getMessage());
        }
    }
}
