<?php

namespace App\Http\Controllers;

use App\Exports\StudentEnrollmentExport;
use App\Models\Classes;
use App\Models\ClassSection;
use App\Models\Section;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;
use Yajra\DataTables\Facades\DataTables;

class StudentEnrollment extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        if ($user->type === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $user->creatorId())
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('All Branches', '');

            $classes = collect();
            $classes->prepend('All Class', 'all');
            $sections = collect();
            $sections->prepend('All Section', '');
            $query = StudentEnrollments::with('StudentRegistration')->where('student_enrollments.active_status', 1)

                ->where('student_enrollments.created_by', $user->creatorId());
            // dd($query->get());
        } else {
            $branches = User::where('id', $user->ownedId())->pluck('name', 'id');
            $classes = Classes::where('owned_by', $user->ownedId())
                ->where('active_status', 1)->pluck('name', 'id');
            $classes->prepend('All Class', 'all');
            $classIds = $classes->keys()->filter();

            $sections = DB::table('class_sections')
                ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                ->whereIn('class_sections.class_id', $classIds)
                ->select('sections.id', 'sections.name')
                ->distinct()->pluck('sections.name', 'sections.id');
            $sections->prepend('All Section', '');
            $query = StudentEnrollments::with('StudentRegistration')
                ->whereHas('StudentRegistration', function ($q) {
                    $q->where('student_status', '!=', 'withdrawl');
                })->where('student_enrollments.owned_by', $user->ownedId());
        }
        // dd($sections);
        $sessions = Session::where('created_by', $user->creatorId())
            ->pluck('year', 'id');

        if (!empty($request->branches)) {
            $query->where('student_enrollments.owned_by', $request->branches);
            $classes = Classes::where('owned_by', $request->branches)
                ->where('active_status', 1)
                ->pluck('name', 'id');
            $classes->prepend('All Class', 'all');
        }
        if (!empty($request->classes) && $request->classes != 'all') {
            $query->where('student_enrollments.class_id', $request->classes);
            $sections = DB::table('class_sections')
                ->join('sections', 'class_sections.section_id', '=', 'sections.id')
                ->where('class_sections.class_id', $request->classes)
                ->select('sections.id', 'sections.name')
                ->distinct()->pluck('sections.name', 'sections.id');
            // dd($sections);
        }
        if (!empty($request->sections) && $request->classes != 'all') {
            $query->where('student_enrollments.section_id', $request->sections);
        }
        if (!empty($request->sessions)) {
            $query->where('student_enrollments.session_id', $request->sessions);
        }
        if (!empty($request->gender)) {
            $query->whereHas('StudentRegistration', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->join(
                'student_registrations',
                'student_enrollments.regId',
                '=',
                'student_registrations.id'
            )
                ->where(function ($q) use ($searchTerm) {
                    $q->orWhere('student_registrations.stdname', 'like', "%{$searchTerm}%")
                        ->orWhere('student_enrollments.enrollId', 'like', "%{$searchTerm}%");
                })
                ->select('student_enrollments.*');
        }
        if (!empty($request->sort)) {
            switch ($request->sort) {
                case 'asc':
                case 'desc':
                    $query->join(
                        'student_registrations',
                        'student_enrollments.regId',
                        '=',
                        'student_registrations.reg_no'
                    )->select('student_enrollments.*', 'student_registrations.stdname')->orderBy('student_registrations.stdname', $request->sort);
                    break;
                case 'gender':
                    $query->orderBy(
                        StudentRegistration::select('gender')
                            ->whereColumn('student_registrations.id', 'student_enrollments.regId'),
                        'asc'
                    );
                    break;

                case 'date':
                    $query->orderBy('student_enrollments.adm_date', 'asc');
                    break;
            }
        }
        // 8) Always filter only active enrollments
        if ($request->has('export') && $request->export == 'excel') {
            $branch = $request->branches ? $branches[$request->branches] : 'All Branches';
            $enrollments = $query->where('student_enrollments.active_status', 1)->get();
            return Excel::download(new StudentEnrollmentExport($enrollments, $branch, $branches, $request), 'student_enrollment.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            $branch = $request->branches ? $branches[$request->branches] : 'All Branches';
            $enrollments = $query->where('student_enrollments.active_status', 1)->get();
            return Excel::download(new StudentEnrollmentExport($enrollments, $branch, $branches, $request), 'student_enrollment.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }
        if ($request->has('print')) {
            ini_set('max_execution_time', 0);
            $report_name = 'Student Enrollment Report';
            $branch = $request->branches ? $branches[$request->branches] : 'All Branches';
            $enrollments = $query->join('classes', 'student_enrollments.class_id', '=', 'classes.id')
                ->join('users', 'student_enrollments.owned_by', '=', 'users.id') // Join to get branch name
                ->where('student_enrollments.active_status', 1)
                ->select('student_enrollments.*', 'student_registrations.*', 'classes.name as class_name', 'users.name as branch_name');
            if ($request->print == 'pdf') {
                $enrollments = $enrollments->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')->get();
                $groupedEnrollments = $enrollments->groupBy(['branch_name', 'class_name']);
                $bodyHtml = view('students.enrollment.print', compact('branches', 'groupedEnrollments'))->render();
                $report_name = 'Student Profile';
            } else if ($request->print == 'class_print') {
                $enrollments = $enrollments->join('student_registrations', 'student_enrollments.regId', '=', 'student_registrations.id')
                    ->orderBy('users.name', 'asc') // First by branch
                    ->orderBy('classes.name', 'asc') // Then by class
                    ->orderBy('section_id', 'asc') // Then by class
                    ->orderBy('student_registrations.stdname', 'asc') // Then by student name
                    // ->take(50)
                    ->get();
                // dd($enrollments);
                $groupedEnrollments = $enrollments->groupBy(['branch_name', 'class_name']);
                $bodyHtml = view('students.enrollment.class_print', compact('groupedEnrollments', 'branches'))->render();
                $report_name = 'Class List';
            }

            $periods = false;
            $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name', 'periods', 'branch'));
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
            ' . $headerHtml . '
            <div class="footer">' . $footerHtml . '</div>
            ' . $bodyHtml . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();
            return $dompdf->stream('StudentProfile.pdf', ['Attachment' => false]);

        }
        // if ($request->has('print')) {
        //     ini_set('max_execution_time', 0);
        //     $report_name = 'Student Enrollment Report';
        //     $branch = $branches[$request->branches] ?? 'All Branches';
        //     if ($request->print == 'pdf') {
        //         $enrollments = $query->where('student_enrollments.active_status', 1)->get();
        //         $bodyHtml = view('students.enrollment.print', compact('enrollments', 'branches'))->render();
        //         $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name', 'branch'))->render();
        //         $footerHtml = view('students.concession.report.pdf.footer')->render();
        //     } else if ($request->print == 'class_print') {
        //         $enrollments = $query->where('student_enrollments.active_status', 1)->orderBy('class_id', 'asc')->get();
        //         $bodyHtml = view('students.enrollment.class_print', compact('enrollments', 'branches'))->render();
        //         $headerHtml = view('studentReports.pdf_header', compact('request', 'report_name', 'branch'))->render();
        //         $footerHtml = view('students.concession.report.pdf.footer')->render();
        //     }
        //     $finalHtml = '
        //         <html><head>
        //         <style>
        //             @page {
        //                 margin-top: 100px;
        //                 margin-bottom: 100px;
        //             }
        //             body { font-family: sans-serif; font-size:
        //             12px; }
        //             .header {
        //                 position: fixed;
        //                 top: -60px;
        //                 left: 0;
        //                 right: 0;
        //                 height: 100px;
        //                 text-align: center;
        //             }
        //             .footer {
        //                 position: fixed;
        //                 bottom: -60px;
        //                 left: 0;
        //                 right: 0;
        //                 height: 50px;
        //                 text-align: center;
        //                 font-size: 10px;
        //                 color: #888;
        //             }
        //         </style>
        //         </head>
        //         <body>
        //             <div class="header">' . $headerHtml . '</div>
        //             <div class="footer">' . $footerHtml . '</div>
        //             ' . $bodyHtml . '
        //         </body></html>';
        //     $pdfPath = storage_path('app/public/StudentDefaulter.pdf');

        //     $pdf = Browsershot::html($bodyHtml)
        //         ->format('A4')
        //         ->landscape(false)
        //         ->showBackground()
        //         ->setOption('baseUrl', config('app.url'))
        //         ->setOption('displayHeaderFooter', true)
        //         ->setOption('headerTemplate', $headerHtml)
        //         ->setOption('footerTemplate', $footerHtml)
        //         ->margins(20, 10, 30, 10)
        //         ->pdf();


        //     // Return PDF as response
        //     return response($pdf)
        //         ->header('Content-Type', 'application/pdf')
        //         ->header('Content-Disposition', 'inline; filename="StudentDefaulter.pdf"');
        // }
        // Check if AJAX request for DataTables
        if ($request->ajax()) {
            return $this->getEnrollmentDataTable($query);
        }

        return view(
            'students.enrollment.list',
            compact('branches', 'classes', 'sections', 'sessions')
        );
    }

    /**
     * Get enrollment data for DataTables AJAX
     */
    public function getEnrollmentDataTable($query)
    {
        return DataTables::of($query->where('student_enrollments.active_status', 1))
            ->addIndexColumn()
            ->addColumn('branch_name', function ($enrollment) {
                return @$enrollment->branch->name ?? '-';
            })
            ->addColumn('reg_no', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                return @$studentData->reg_no ?? '-';
            })
            ->addColumn('roll_no', function ($enrollment) {
                return @$enrollment->enrollId ?? '-';
            })
            ->addColumn('student_name', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                return @$studentData->stdname ?? '-';
            })
            ->addColumn('father_name', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                return @$studentData->fathername ?? '-';
            })
            ->addColumn('class_name', function ($enrollment) {
                return @$enrollment->class->name ?? '-';
            })
            ->addColumn('section_name', function ($enrollment) {
                $sectionName = $enrollment->section->name ?? 'Set Section';
                $sectionUrl = route('section.show', $enrollment->id);
                return '<a href="#" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-start justify-content-start text-left"
                        style="text-align:left; min-height:38px; white-space:normal; word-break:break-word; line-height:1.2; width:160px !important;"
                        data-size="lg" data-url="' . $sectionUrl . '" data-ajax-popup="true" data-title="Section History">
                        ' . $sectionName . '
                    </a>';
            })
            ->addColumn('session_year', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                return @$studentData->session->year ?? '-';
            })
            ->addColumn('reg_type', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                return @$studentData->registeroption->name ?? '-';
            })
            ->addColumn('admission_date', function ($enrollment) {
                return $enrollment->adm_date ? date('d-M-Y', strtotime($enrollment->adm_date)) : '-';
            })
            ->addColumn('action', function ($enrollment) {
                $studentData = StudentRegistration::where('id', $enrollment->regId)->first();
                $showUrl = route('registration.show', $enrollment->regId);
                $admissionUrl = route('admission.order', $studentData->id);

                return '<div class="action-btn ms-2">
                    <a href="' . $showUrl . '" class="mx-1 btn btn-sm align-items-center btn-outline-primary"
                        data-bs-title="Show">
                        <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                    </a>
                    <a href="' . $admissionUrl . '" class="mx-1 btn btn-sm align-items-center btn-outline-success"
                        data-bs-title="Admission Order">
                        <span class="btn-inner--icon"><i class="ti ti-receipt"></i></span>
                    </a>
                </div>';
            })
            ->rawColumns(['section_name', 'action'])
            ->make(true);
    }

    public function exportToExcel($enrollments, $branch, $branches)
    {
        // return Excel::download(new StudentEnrollmentExport($enrollments,$branch,$branches), 'student_enrollment.pdf',\Maatwebsite\Excel\Excel::MPDF);
        return Excel::download(new StudentEnrollmentExport($enrollments, $branch, $branches), 'student_enrollment.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = null)
    {
        $classes = Classes::pluck('name', 'id');
        $session = Session::pluck('title');
        return view('students.enrollment.create', compact('classes', 'session', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $registration = StudentRegistration::findOrFail($request->registration_id);
            if ($registration->student_status == 'Registered' || $registration->student_status == '') {
                $section = ClassSection::where('class_id', $registration->class_id)->first();
                $prevEnrollId = StudentEnrollments::selectRaw(
                    'MAX(CAST(enrollId AS UNSIGNED)) as max_enroll_id'
                )->value('max_enroll_id');

                $newEnrollId = $prevEnrollId ? $prevEnrollId + 1 : 1;
                $enrollment = new StudentEnrollments();
                $enrollment->enrollId = $newEnrollId;
                $enrollment->regId = $request->registration_id;
                $enrollment->class_id = $registration->class_id;
                $enrollment->section_id = $section->id;
                $enrollment->session_id = $registration->session_id;
                $enrollment->owned_by = \Auth::user()->ownedId();
                $enrollment->created_by = \Auth::user()->creatorId();
                $enrollment->save();
                $registration->student_status = 'Enrolled';
                $registration->save();

                return redirect()->route('enrollment.index')->with('success', __('Student Enroll successfully'));
            } elseif ($registration->student_status == 'Enrolled') {
                return redirect()->back()->with('error', __('Student Already Enrolled'))->withInput();
            } else {
                return redirect()->back()->with('error', __('Failed to create enrollment'))->withInput();
            }
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', __('Failed to create enrollment'))->withInput();
        }
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
        $enrollment = StudentEnrollments::findOrFail($id);
        $classes = Classes::pluck('name', 'id');
        $session = Session::pluck('title');
        return view('students.enrollment.edit', compact('enrollment', 'classes', 'session'));
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
        try {
            $validator = Validator::make($request->all(), [
                'classname' => 'required|exists:classes,id',
                'section' => 'required|exists:sections,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $enrollment = StudentEnrollments::findOrFail($id);
            $enrollment->class_id = $request->input('classname');
            $enrollment->section_id = $request->input('section');
            $enrollment->save();
            return redirect()->route('enrollment.index')->with('success', __('Enrollment updated successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to update enrollment'))->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_sections($classId)
    {
        $sections = DB::table('class_sections')
            ->join('sections', 'class_sections.section_id', '=', 'sections.id')
            ->where('class_sections.class_id', $classId)
            ->select('sections.id', 'sections.name')
            ->get();
        return response()->json($sections);
    }
}
