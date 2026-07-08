<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyStatistics;
use App\Exports\ProfessionWiseListingExport;
use App\Exports\SiblingStudentExport;
use App\Exports\StaffChildExport;
use App\Exports\StudentDataAnalysisExport;
use App\Exports\StudypackStudentExport;
use App\Models\Classes;
use App\Models\Registring_option;
use App\Models\EmpChildrens;
use App\Models\Employee;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\StudentTransfer;
use App\Models\StudentWithdrawal;
use App\Models\StudyPack;
use App\Models\StudyPackChallans;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\StudyPackChallanListExport;
use Carbon\Carbon;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;

class StudentReportController2 extends Controller
{
    public function monthlystatistics(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('Select Branch', '');
        } else {
            $branches = User::where('id', '=', $user->ownedId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }

        $selectedBranchId = $request->branches ?? null;
        $selectedDate = $request->date ?? date('Y-m-d');
        $selectedMonth = date('m', strtotime($selectedDate));
        $selectedYear = date('Y', strtotime($selectedDate));
        $previousMonth = date('m', strtotime('-1 month', strtotime($selectedDate)));
        $previousYear = date('Y', strtotime('-1 month', strtotime($selectedDate)));
        // dd($previousMonth,$previousYear);
        $report = [];

        if ($selectedBranchId) {
            $branchIds = [$selectedBranchId];
        } else {
            $branchIds = $branches->keys();
        }

        foreach ($branchIds as $branchId) {
            $branchName = $branches[$branchId];

            $classes = Classes::with(['classSection'])
                ->where('owned_by', $branchId)
                ->get();

            foreach ($classes as $class) {
                foreach ($class->classSection as $section) {
                    $opening = StudentEnrollments::
                        where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->where(function ($query) use ($previousMonth, $previousYear) {
                            $query->whereYear('adm_date', '<=', $previousYear)
                                ->whereMonth('adm_date', '<=', $previousMonth);
                        })
                        ->count();
                    $newAdmissions = StudentEnrollments::where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->whereMonth('adm_date', $selectedMonth)
                        ->whereYear('adm_date', $selectedYear)
                        ->count();
                    $withdrawals = StudentWithdrawal::where('class_id', $class->id)
                        ->where('section_id', $section->section_id)
                        ->where('owned_by', $branchId)
                        ->whereMonth('withdraw_date', $selectedMonth)
                        ->whereYear('withdraw_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $transferIn = StudentTransfer::where('section_to', $section->section_id)
                        ->where('class_to', $class->id)
                        ->where('branch_to', $branchId)
                        ->whereMonth('transfer_date', $selectedMonth)
                        ->whereYear('transfer_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $transferOut = StudentTransfer::where('section_from', $section->section_id)
                        ->where('class_from', $class->id)
                        ->where('branch_from', $branchId)
                        ->whereMonth('transfer_date', $selectedMonth)
                        ->whereYear('transfer_date', $selectedYear)
                        ->where('status', 'approved')
                        ->count();
                    $closingBalance = $opening + $newAdmissions + $transferIn - $withdrawals - $transferOut;

                    $report[] = [
                        'branch' => $branchName,
                        'class' => $class->name,
                        'section' => $section->sectionName->name,
                        'opening' => $opening,
                        'new_admissions' => $newAdmissions,
                        'transfer_in' => $transferIn,
                        'withdrawals' => $withdrawals,
                        'transfer_out' => $transferOut,
                        'closing_balance' => $closingBalance,
                    ];

                    if ($request->has('export') && $request->export == 'excel') {
                        return Excel::download(new MonthlyStatistics($branches, $report, $selectedBranchId, $selectedDate), 'MonthlyStatisticscha.xlsx');
                    }
                    if ($request->has('print') && $request->print == 'pdf') {
                        $pdf = new Dompdf();
                        $html = view('studentReports.pdf.monthlystatistics', compact('branches', 'report', 'selectedBranchId', 'selectedDate'))->render();
                        $headerHtml = view('studentReports.pdf_header', compact('request'));
                        $footerHtml = view('students.concession.report.pdf.footer')->render();
                        $html = '<html><head>
                        <style>
                            @page {
                                margin-top: 100px;
                                margin-bottom: 100px;
                            }
                            .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                            .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
                        </style>
                        </head><body>
                        <div class="header">' . $headerHtml . '</div>
                        <div class="footer">' . $footerHtml . '</div>
                        ' . $html . '
                        </body></html>';
                        $options = new Options();
                        $options->set('isHtml5ParserEnabled', true);
                        $options->set('isRemoteEnabled', true);
                        $dompdf = new Dompdf($options);
                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'potrait');
                        $dompdf->render();

                        return $dompdf->stream('MonthlyStatistics.pdf');
                    }
                }
            }
        }
        return view('studentReports.report2.monthlystatistics', compact('branches', 'report', 'selectedBranchId', 'selectedDate'));
    }
public function sibling_students(Request $request)
{
    $user = \Auth::user();

    // Step 1: Get relevant branches and base student query
    if ($user->type == 'company') {
        $branches = User::where('type', 'branch')
            ->where('created_by', $user->creatorId())
            ->pluck('name', 'id');

        $branches->prepend($user->name, $user->id);
        $branches->prepend('All Branches', 'All Branches');

        $branchId = $user->creatorId();

        $studentsQuery = StudentRegistration::with('concession')
            ->where('created_by', $branchId);
    } else {
        $branches = User::where('id', $user->ownedId())
            ->pluck('name', 'id');

        $branches->prepend('All Branches', 'All Branches');

        $branchId = $user->ownedId();

        $studentsQuery = StudentRegistration::with('concession')
            ->where('owned_by', $branchId);
    }

    // Step 2: Apply branch filter if selected
    if ($request->filled('branches')) {
        $studentsQuery->where('owned_by', $request->branches);
    }

    // Step 3: Filter students who have shared father/mother CNIC (siblings)
    $students = $studentsQuery->where(function ($query) {
        $query->whereIn('fathercnic', function ($sub) {
            $sub->select('fathercnic')
                ->from('student_registrations')
                ->whereNotNull('fathercnic')
                ->where('fathercnic', '!=', '')
                ->groupBy('fathercnic')
                ->havingRaw('COUNT(*) > 1');
        })->orWhereIn('mothercnic', function ($sub) {
            $sub->select('mothercnic')
                ->from('student_registrations')
                ->whereNotNull('mothercnic')
                ->where('mothercnic', '!=', '')
                ->groupBy('mothercnic')
                ->havingRaw('COUNT(*) > 1');
        });
    })
        // Step 4: Sort by CNIC so siblings are together
        ->orderByRaw("COALESCE(NULLIF(fathercnic, ''), NULLIF(mothercnic, '')) ASC")
        ->orderBy('owned_by') // Optional: maintain branch grouping
        ->get();

    // Step 5: Group by branch for display/export
    $groupedStudents = $students->groupBy('owned_by');

    // Step 6: Export or render view (fixed syntax)
    if ($request->has('export')) {
        if ($request->export === 'excel') {
            return Excel::download(
                new SiblingStudentExport($branches, $groupedStudents), 
                'SiblingStudentsReport.xlsx'
            );
        } else {
            return Excel::download(
                new SiblingStudentExport($branches, $groupedStudents),
                'SiblingStudentsReport.pdf',
                \Maatwebsite\Excel\Excel::MPDF
            );
        }
    }

    return view('studentReports.report2.sibling_students', compact('branches', 'students', 'groupedStudents'));
}
   public function staff_child(Request $request)
    {
        $user = \Auth::user();
        $isCompany = $user->type === 'company';
        $ownerId = $isCompany ? $user->creatorId() : $user->ownedId();

        // Build branch list
        if ($isCompany) {
            $branches = User::where('type', 'branch')
                ->where('created_by', $ownerId)
                ->get()
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
        } else {
            $branches = User::where('id', $ownerId)->get()->pluck('name', 'id');
        }
        $branches->prepend('Select Branch', '');

        // Base query with eager loads
        $baseQuery = StudentRegistration::with([
            'concession.policy',
            'concession.policy_head',
            'branches',
            'class',
            'enrollment',
            'fee_structure',
        ]);

        if ($isCompany) {
            $baseQuery->where('created_by', $ownerId);
        } else {
            $baseQuery->where('owned_by', $ownerId);
        }

        if ($request->filled('branches')) {
            $baseQuery->where('owned_by', $request->branches);
        }

        $baseOn = $request->base_on ?? 'cnic';

        // Employee status filter
        $employeeStatus = $request->employee_status ?? 'active';
        $employeeQuery = Employee::with('designation');
        if ($employeeStatus === 'active') {
            $employeeQuery->where(function ($q) {
                $q->where('is_res_ter', 0)->orWhereNull('is_res_ter');
            });
        } elseif ($employeeStatus === 'resigned') {
            $employeeQuery->where('is_res_ter', 1);
        }

        // Pre-load employees keyed by CNIC
        $employeesByCnic = $employeeQuery->get()->keyBy('cnic');

        if ($baseOn === 'staff_child') {
            $teacherChild = Registring_option::where('created_by', $user->creatorId())
                ->where('name', 'TEACHER CHILD')
                ->first();
            $students = $baseQuery
                ->where('register_option', $teacherChild->id ?? 0)
                ->orderBy('stdname')
                ->get();
        } else {
            // CNIC base: find students whose fathercnic or mothercnic matches any employee CNIC
            $employeeCnicList = $employeesByCnic->keys()->filter()->toArray();

            if (empty($employeeCnicList)) {
                $students = collect();
            } else {
                $students = $baseQuery
                    ->where(function ($q) use ($employeeCnicList) {
                        $q->whereIn('fathercnic', $employeeCnicList)
                          ->orWhereIn('mothercnic', $employeeCnicList);
                    })
                    ->orderByRaw("COALESCE(fathercnic, mothercnic)")
                    ->get();
            }
        }

        // Map employee data onto each student
        $students->each(function ($student) use ($employeesByCnic) {
            $employee = $employeesByCnic->get($student->fathercnic)
                ?? $employeesByCnic->get($student->mothercnic)
                ?? new \App\Models\Employee(['owned_by' => null, 'employee_id' => '']);
            $student->setRelation('employee', $employee);
        });

        // Keep only students whose employee was found in the filtered set
        $students = $students->filter(fn ($s) => $s->employee->exists)->values();

        $groupedStudents = $students->groupBy('owned_by');

        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(new StaffChildExport($branches, $students, $groupedStudents), 'StaffChildReport.xlsx');
        }

        if ($request->has('print') && $request->print == 'pdf') {
            $report_name = 'Staff Child Report';
            return Excel::download(new StaffChildExport($branches, $groupedStudents, $report_name, $request->all()), 'StaffChildReport.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('studentReports.report2.staff_child', compact('branches', 'students', 'groupedStudents'));
    }
    public function student_data_analysis(Request $request)
    {
        $user = auth()->user();
        $isCompany = $user->type === 'company';
        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();
        $branches = User::when(
            $isCompany,
            fn($q) => $q->where('type', 'branch')->where('created_by', $creatorId),
            fn($q) => $q->where('id', $ownedId)
        )
            ->pluck('name', 'id');
        $branches->prepend('All Branches', 'all');
        $filterBranch = $request->input('branches', 'all');
        $branchesToLoop = $filterBranch === 'all'
            ? $branches->filter(fn($_, $id) => $id !== 'all')
            : $branches->only($filterBranch);
        $numBackYears = (int) $request->input('years', 2);       // default = 2
        $currentYear = now()->year;                            // e.g. 2025

        $periods = [];
        for ($i = $numBackYears; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $start = "{$year}-01-01";
            $end = "{$year}-12-31";
            $yy = substr($year, 2, 2);
            $label = "1‑JAN‑{$yy} — 31‑DEC‑{$yy}";
            $periods[] = compact('start', 'end', 'label');
        }
        $classesByBranch = [];
        $stats = [];
        foreach ($branchesToLoop as $branchId => $branchName) {
            $branchClasses = Classes::when(
                $isCompany,
                fn($q) => $q->where('created_by', $creatorId),
                fn($q) => $q->where('owned_by', $ownedId)
            )
                ->when(
                    is_numeric($branchId),
                    fn($q) => $q->where('owned_by', $branchId)
                )->where('active_status', 1)
                ->get(['id', 'name'])
                ->keyBy('id');

            $classesByBranch[$branchId] = $branchClasses;
            foreach ($branchClasses as $classId => $classModel) {
                foreach ($periods as $i => $p) {
                    $reg = StudentRegistration::where('reg_class', $classId)
                        ->when(
                            is_numeric($branchId),
                            fn($q) => $q->where('owned_by', $branchId)
                        )
                        ->whereBetween('regdate', [$p['start'], $p['end']])
                        ->count();

                    $adm = StudentEnrollments::where('class_id', $classId)
                        ->where('active_status', 1)
                        ->when(
                            is_numeric($branchId),
                            fn($q) => $q->where('owned_by', $branchId)
                        )
                        ->whereBetween('adm_date', [$p['start'], $p['end']])
                        ->count();

                    $tf_in = StudentTransfer::where('class_to', $classId)
                        ->when(
                            is_numeric($branchId),
                            fn($q) => $q->where('branch_to', $branchId)
                        )
                        ->whereBetween('transfer_date', [$p['start'], $p['end']])
                        ->count();
                    
                    $tf_out = StudentTransfer::where('class_from', $classId)
                        ->when(
                            is_numeric($branchId),
                            fn($q) => $q->where('branch_from', $branchId)
                        )
                        ->whereBetween('transfer_date', [$p['start'], $p['end']])
                        ->count();

                    $wd = StudentWithdrawal::where('class_id', $classId)
                        ->when(
                            is_numeric($branchId),
                            fn($q) => $q->where('session_id', $branchId)
                        )
                        ->whereBetween('withdraw_date', [$p['start'], $p['end']])
                        ->count();

                    $gain = $adm + $tf_in - $wd - $tf_out;

                    $stats[$branchId][$classId][$i] = compact('reg', 'adm', 'wd', 'gain', 'tf_in', 'tf_out');
                }
            }
        }
        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new StudentDataAnalysisExport($branches, $branchesToLoop, $filterBranch, $periods, $classesByBranch, $stats), 'StudentDataAnalysis(RegAdmWDAnalysis).xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new StudentDataAnalysisExport($branches, $branchesToLoop, $filterBranch, $periods, $classesByBranch, $stats), 'StudentDataAnalysis(RegAdmWDAnalysis).pdf');
        }
        return view('studentReports.report2.student_data_analysis', [
            'branches' => $branches,
            'branchesToLoop' => $branchesToLoop,
            'filterBranch' => $filterBranch,
            'periods' => $periods,
            'classesByBranch' => $classesByBranch,
            'stats' => $stats,
        ]);
    }

    public function studypackStudent(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('Select Branch', '');
            $branchId = $user->creatorId();
            // dd($branchId);
            $studypacks = StudyPackChallans::where('created_by', $user->creatorId());
        } else {
            $branches = User::where('id', '=', $user->ownedId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $branchId = $user->ownedId();
            $studypacks = StudyPackChallans::where('owned_by', $user->ownedId());
        }
        if ($request->filled('branches')) {
            $studypacks->where('branch_id', $request->branches);
        }
        $studypacks = $studypacks->get();
        if ($request->has('export') && $request->export == 'excel') {
            $studypack = $studypacks->find($request->challanno);
            return Excel::download(new StudypackStudentExport($studypack), 'StudypackStudent.xlsx');
        }

        if ($request->has('export') && $request->export == 'exportallreportexcel') {    
            $studypacks = StudyPackChallans::get()->groupBy('owned_by');
            return Excel::download(new StudyPackChallanListExport($studypacks, $branches, "STUDY PACK CHALLAN LIST", $request->all()), 'StudypackStudent.xlsx');
        }

        return view('studentReports.report2.studypack_student', compact('branches', 'studypacks'));
    }
 public function profession_wise_listing(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')
                ->where('created_by', $user->creatorId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', '');
            $branchId = $user->creatorId();
            $students = StudentEnrollments::with('StudentRegistration')->where('created_by', $branchId);
        } else {
            $branches = User::where('id', '=', $user->ownedId())
                ->get()
                ->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $branchId = $user->ownedId();
            $students = StudentEnrollments::with('StudentRegistration')->where('owned_by', $branchId);
        }
        if ($request->filled('branches')) {
            $students->where('owned_by', $request->branches);
        }
$students = $students->with('studentRegistration')->orderBy('owned_by')->get();

$groupedStudents = $students->groupBy(function ($student) {
    return $student->owned_by; // First level: branch
})->map(function ($branchGroup) {
    // Second level: group by profession with priority logic
    return $branchGroup->groupBy(function ($student) {
        $registration = $student->studentRegistration;

        if ($registration && !empty($registration->fatherprofession)) {
            return $registration->fatherprofession;
        } elseif ($registration && !empty($registration->motherprofession)) {
            return $registration->motherprofession;
        } elseif ($registration && !empty($registration->gardianprofession)) {
            return $registration->gardianprofession;
        }

        return 'Unknown'; // fallback group
    });
});

        // $students = $students->groupBy('owned_by')->get();

        // $groupedStudents = $students->groupBy(function ($student) {
        //     // Prioritize professions: father > mother > guardian
        //     $profession = $student->StudentRegistration->fatherprofession
        //         ?? $student->StudentRegistration->motherprofession
        //         ?? $student->StudentRegistration->guardianprofession
        //         ?? 'Unknown';
        //     return $profession;
        // });
        // dd($students,$grouped);
        if ($request->has('export') && $request->export == 'excel') {
            $branchName = $branches[$request->branches] ?? 'All Branches';
            return Excel::download(new ProfessionWiseListingExport($groupedStudents, $branchName, $branches,$branchId), 'ProfessionWiseListingReport.xlsx');
        }
        if ($request->has('print') && $request->print == 'pdf') {
            $branchName = $branches[$request->branches] ?? 'All Branches';
            return Excel::download(new ProfessionWiseListingExport($groupedStudents, $branchName, $branches,$branchId), 'ProfessionWiseListingReport.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        return view('studentReports.report2.profession_wise_listing', compact('branches', 'students', 'groupedStudents'));
    }

}
