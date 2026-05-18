<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\StudentRegistration;
// use Illuminate\Http\Request;
use App\Models\Section;

class ResultApiController extends Controller
{
    public function getStudents()
    {
        // dd('hi');
        $students = StudentRegistration::with('enrollment')
            ->select(
                'id',
                'reg_no',
                'roll_no',
                'stdname',
                'dob',
                'gender',
                'fathername',
                'fathercell',
                'class_id',
                'branch',
                'session_id',
                'owned_by',
                'created_by'
            )
            ->where('student_Status', 'Enrolled')
            ->where('active_Status', 1)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Students fetched successfully',
            'data' => $students
        ]);
    }
    public function getClasses()
    {

        $classes = Classes::with('classSection', 'classSectionAll')
            ->where('active_Status', 1)
            ->distinct()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Classes fetched successfully',
            'data' => $classes
        ]);
    }
    public function getBranches()
    {

        $branches = User::where('type', 'branch')
            ->where('is_active', 1)
            ->distinct()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Branches fetched successfully',
            'data' => $branches
        ]);
    }
    public function getBranch($id)
    {

        $branch = User::with('SchoolDetails')->where('type', 'branch')
            ->where('is_active', 1)
            ->where('id', $id)
            ->first();

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Branch fetched successfully',
            'data' => $branch
        ]);
    }
    public function getEmployeesByBranch($branch_id)
    {
        $employees = User::where('owned_by', $branch_id)
            ->with('employee', 'employee.designation','employee.branchdetail','employee.userbranch')
            ->where('is_active', 1)
            ->whereHas('employee.department', function ($q) {

                $q->where(function ($query) {
                    $query->where('name', 'LIKE', '%MANAGEMENT%')
                        ->orWhere('name', 'LIKE', '%ACADEMIC%');
                });
            })
            ->whereNotIn('type', ['company', 'branch', 'super admin'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => $employees->isEmpty() ? 'No employees found for this branch' : 'Employees fetched successfully',
            'data'    => $employees
        ]);
    }
    public function getSections()
    {
        $sections = Section::where('active_Status', 1)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Sections fetched successfully',
            'data' => $sections
        ]);
    }
    public function getClassSection(){
        $classsection = ClassSection::where('active_Status', 1)
        ->with('sectionName')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Class-section fetched successfully',
            'data' => $classsection
        ]);

    }
}
