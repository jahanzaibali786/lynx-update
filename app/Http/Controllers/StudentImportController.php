<?php

namespace App\Http\Controllers;

use App\Exports\StudentImportSampleExport;
use App\Imports\StudentImport;
use App\Models\Classes;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    public function downloadSample(Request $request)
    {
        $branchId = $request->input('branch');
        $classId = $request->input('class_id');
        $sessionId = $request->input('session_id');
        $status = $request->input('status');

        return Excel::download(
            new StudentImportSampleExport($branchId, $classId, $sessionId, $status),
            'student_update_sample.xlsx'
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

        $classes = Classes::where('created_by', $userCreatorId)->pluck('name', 'id');
        $classes->prepend('All Classes', 'all');

        $sessions = Session::where('created_by', $userCreatorId)->pluck('year', 'id')->sortDesc();
        $sessions->prepend('All Sessions', 'all');

        $statuses = [
            '' => 'All Statuses',
            'Enrolled' => 'Enrolled',
            'Registered' => 'Registered',
        ];

        return view('students.registration.import', compact('branches', 'classes', 'sessions', 'statuses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new StudentImport;
            Excel::import($import, $request->file('file'));

            $success = $import->getSuccessCount();
            $errors = $import->getErrorCount();
            $skipped = $import->getSkipCount();
            $errorDetails = $import->getErrors();

            $message = "{$success} student(s) updated successfully.";
            if ($skipped > 0 || $errors > 0) {
                $message .= " {$skipped} row(s) skipped (no match), {$errors} error(s).";
                return redirect()->route('student.import')
                    ->with('warning', $message)
                    ->with('import_errors', $errorDetails);
            }

            return redirect()->route('student.import')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('student.import')->with('error', __('Import failed: ') . $e->getMessage());
        }
    }
}
