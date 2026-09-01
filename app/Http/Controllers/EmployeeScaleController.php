<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeScale;
use App\Models\EmployeeScaleHeads;
use App\Exports\EmployeeScaleReportExport;
use App\Models\SalaryHeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeScaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage employee scale')) {
            $query = EmployeeScale::with('employeeScaleHeads','employeepayScaledetailHeads')
            ->where('created_by', \Auth::user()->creatorId());
      
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend(__('Select Department'), '');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            if (!empty($request->scale_no)) {
                $query->where('scale_no', $request->scale_no);
            }
            if (!empty($request->effect_from)) {
                $query->where('effect_from', $request->effect_from);
            }
            if (!empty($request->department)) {
                $query->where('department_id', $request->department);
            }
            if ($request->adhoc != null) {
                $query->where('adhoc', $request->adhoc);
            }
            if ($request->status != null && $request->status != 'all') {
                $query->where('status', $request->status);
                $stat = $request->status;
            }elseif($request->status == 'all'){
                $stat = 'all';
            }
            else{
                $query->where('status', 1);
                $stat = 1;
            }
            // dd($request->all(),$query->get());
            if ($request->has('export') && $request->export == 'excel') {
                $employee_scales = $query->get();  
                return Excel::download(new EmployeeScaleReportExport($employee_scales,$heads), 'employee_scales.xlsx');
            }
            if ($request->has('export') && $request->export == 'pdf') {
                $employee_scales = $query->get();  
                return Excel::download(new EmployeeScaleReportExport($employee_scales,$heads), 'employee_scales.pdf', \Maatwebsite\Excel\Excel::MPDF);
            }
            foreach ($heads as $account) {
                $headKey = 'head_' . $account->id;
                if (!is_null($request->input($headKey))) {
                    $query->whereHas('employeeScaleHeads', function ($q) use ($request, $headKey, $account) {
                        $q->where('head', $account->id)
                          ->where('head_value', $request->input($headKey));
                    });
                }
            }
            

            $employee_scales = $query->get();

            return view('scale.index', compact('employee_scales', 'department', 'heads', 'stat'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function generateReport(Request $request)
    {
        $query = EmployeeScale::with('employeeScaleHeads')->where('status', 1)->where('created_by', \Auth::user()->creatorId());
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            if (!empty($request->scale_no)) {
                $query->where('scale_no', $request->scale_no);
            }
            if (!empty($request->effect_from)) {
                $query->where('effect_from', $request->effect_from);
            }
            if (!empty($request->department)) {
                $query->where('department_id', $request->department);
            }
            if (!empty($request->adhoc)) {
                $query->where('adhoc', $request->adhoc);
            }
            if (!empty($request->status)) {
                $query->where('status', $request->status);
            }
            foreach ($heads as $account) {
                $headKey = 'head_' . $account->id;
                if (!is_null($request->input($headKey))) {
                    $query->whereHas('employeeScaleHeads', function ($q) use ($request, $headKey, $account) {
                        $q->where('head', $account->id)
                          ->where('head_value', $request->input($headKey));
                    });
                }
            }

        $employee_scales = $query->get();
        $pdf = new Dompdf();
        $texthtml = view('scale.report', compact('employee_scales','heads'))->render();
        $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
        $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
        $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                body { font-family: sans-serif; font-size: 12px; }
                .header {
                    position: fixed;
                    top: -100px;
                    left: 0;
                    right: 0;
                    height: 100px;
                    text-align: center;
                }
                .footer {
                    position: fixed;
                    bottom: -60px;
                    left: 0;
                    right: 0;
                    height: 50px;
                    text-align: center;
                    font-size: 10px;
                    color: #888;
                }
                .logo{
                    position: absolute;
                    top:40px;
                    }
            </style>
            </head>
            <body>
                <div class="header">' . $headerHtml . '</div>
                <div class="footer">' . $footerHtml . '</div>
                ' . $texthtml . '
            </body></html>';
        $pdfOptions = new Options();
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('defaultFont', 'Helvetica');
        $pdf->setOptions($pdfOptions);

        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->stream('employee_scale_report.pdf');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create employee scale')) {
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            return view('scale.create', compact('department', 'heads'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create employee scale')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'scale_no' => 'required|unique:employee_scales',
                    'department_id' => 'required|numeric',
                    'account_id' => 'required',
                    // 'type' => 'required',
                    'account_value' => 'required',
                    'effect_from' => 'required|date',
                    'adhoc' => 'required|numeric',
                    'status' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first()
                ], 400);
            }
            DB::beginTransaction();
            try {
                $employee_scales = new EmployeeScale();
                $employee_scales->scale_no = $request->scale_no;
                $employee_scales->type = $request->type;
                $employee_scales->effect_from = $request->effect_from;
                $employee_scales->adhoc = $request->adhoc;
                $employee_scales->department_id = $request->department_id;
                $employee_scales->status = $request->status;
                $employee_scales->owned_by = \Auth::user()->ownedId();
                $employee_scales->created_by = \Auth::user()->creatorId();
                $employee_scales->save();
                if ($employee_scales) {
                    for ($i = 0; $i < count($request->account_id); $i++) {
                        EmployeeScaleHeads::Create([
                            'scale_id' => $employee_scales->id,
                            'scale_no' => $employee_scales->scale_no,
                            'head' => $request->account_id[$i],
                            'head_value' => $request->account_value[$i] ?? 0,
                            'owned_by' => \Auth::user()->ownedId(),
                            'created_by' => \Auth::user()->creatorId(),
                        ]);
                    }
                }
                DB::commit();
                $employee_scales->load('department', 'employeeScaleHeads');
                $initialBasicHead = SalaryHeads::where('created_by', \Auth::user()->creatorId())
                    ->whereRaw('LOWER(TRIM(head)) = ?', ['initial basic'])
                    ->first();

                $initialBasic = 0;
                if ($initialBasicHead) {
                    $initialBasic = (float) optional(
                        $employee_scales->employeeScaleHeads->firstWhere('head', $initialBasicHead->id)
                    )->head_value;
                }

                return response()->json([
                    'success' => true,
                    'message' => __('Employee Scale successfully created.'),
                    'scale' => [
                        'id' => $employee_scales->id,
                        'label' => $employee_scales->scale_no,
                        'scale_no' => $employee_scales->scale_no,
                        'department_id' => $employee_scales->department_id,
                        'initial_basic' => round($initialBasic, 2),
                        'heads' => $employee_scales->employeeScaleHeads->map(function ($head) {
                            return [
                                'head' => $head->head,
                                'head_value' => (float) $head->head_value,
                            ];
                        })->values(),
                    ],
                    'row_html' => view('scale.row', [
                        'scale' => $employee_scales,
                        'heads' => SalaryHeads::where('created_by', \Auth::user()->creatorId())->get(),
                    ])->render(),
                ], 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeScale $employeeScale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeScale $employeeScale)
    {
        if (\Auth::user()->can('edit employee scale')) {
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $heads = SalaryHeads::where('created_by', \Auth::user()->creatorId())->get();
            return view('scale.edit', compact('employeeScale', 'department', 'heads'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit employee scale')) {
          
            $validator = \Validator::make($request->all(), [
                'scale_no' => 'required',
                // 'type' => 'required',
                'department_id' => 'required|numeric',
                'account_id' => 'required|array',
                'account_value' => 'required|array',
                'adhoc' => 'required|numeric',
                'status' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
    
            DB::beginTransaction();
            try {
                $employee_scale = EmployeeScale::findOrFail($id);
                $employee_scale->adhoc = $request->adhoc;
                $employee_scale->type = $request->type;
                $employee_scale->status = $request->status;
                $employee_scale->department_id = $request->department_id;
                $employee_scale->owned_by = \Auth::user()->ownedId();
                $employee_scale->created_by = \Auth::user()->creatorId();
                $employee_scale->save();
            
                EmployeeScaleHeads::where('scale_no', $employee_scale->scale_no)->delete();
                for ($i = 0; $i < count($request->account_id); $i++) {
                    EmployeeScaleHeads::create(
                        [
                            'scale_id' => $employee_scale->id,
                            'scale_no' => $employee_scale->scale_no,
                            'head' => $request->account_id[$i],
                            'head_value' => $request->account_value[$i] ?? 0,
                            'owned_by' => \Auth::user()->ownedId(),
                            'created_by' => \Auth::user()->creatorId(),
                        ]
                    );
                }

                DB::commit();
                $employee_scale->load('department', 'employeeScaleHeads');
                return response()->json([
                    'success' => true,
                    'message' => __('Employee Scale successfully updated.'),
                    'row_id' => $employee_scale->id,
                    'row_html' => view('scale.row', [
                        'scale' => $employee_scale,
                        'heads' => SalaryHeads::where('created_by', \Auth::user()->creatorId())->get(),
                    ])->render(),
                ], 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Error occurred while updating Employee Scale: ' . $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeScale  $employeeScale
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeScale $employeeScale)
    {
        if (!\Auth::user()->can('delete employee scale')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        if ((int) $employeeScale->created_by !== (int) \Auth::user()->creatorId()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        DB::beginTransaction();
        try {
            EmployeeScaleHeads::where('scale_id', $employeeScale->id)->delete();
            $employeeScale->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Employee Scale successfully deleted.'),
                'row_id' => $employeeScale->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * AJAX auto-create used by Bulk Salary Proposal.
     * Salary Value is split into Initial Basic 50%, House Rent 40%, Medical 10%.
     */
    public function autoCreateFromBulk(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'department_id' => 'required|integer',
            'salary_value' => 'required|numeric|min:0.01',
            'effect_from' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = \Auth::user();
        $departmentId = (int) $request->department_id;
        $salaryValue = round((float) $request->salary_value, 2);

        $department = Department::where('id', $departmentId)
            ->where('created_by', $user->creatorId())
            ->first();

        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Invalid department.'], 422);
        }

        $heads = SalaryHeads::where('created_by', $user->creatorId())->get();
        $normalise = function ($value) {
            return strtolower(trim(preg_replace('/\\s+/', ' ', (string) $value)));
        };

        $initialHead = $heads->first(function ($head) use ($normalise) {
            return $normalise($head->head ?? '') === 'initial basic';
        });
        $houseHead = $heads->first(function ($head) use ($normalise) {
            return in_array($normalise($head->head ?? ''), ['house rent', 'house rent allowance'], true);
        });
        $medicalHead = $heads->first(function ($head) use ($normalise) {
            return in_array($normalise($head->head ?? ''), ['medical', 'medical allowance'], true);
        });

        if (!$initialHead || !$houseHead || !$medicalHead) {
            return response()->json([
                'success' => false,
                'message' => 'Initial Basic, House Rent and Medical salary heads must exist.',
            ], 422);
        }

        $initialBasic = round($salaryValue * 0.50, 2);
        $houseRent = round($salaryValue * 0.40, 2);
        $medical = round($salaryValue - $initialBasic - $houseRent, 2);

        DB::beginTransaction();
        try {
            $existingScales = EmployeeScale::with('employeeScaleHeads')
                ->where('created_by', $user->creatorId())
                ->where('department_id', $departmentId)
                ->lockForUpdate()
                ->get();

            // Do not duplicate the same generated structure in the same department.
            foreach ($existingScales as $existingScale) {
                $headMap = $existingScale->employeeScaleHeads->keyBy('head');
                $existingInitial = round((float) optional($headMap->get($initialHead->id))->head_value, 2);
                $existingHouse = round((float) optional($headMap->get($houseHead->id))->head_value, 2);
                $existingMedical = round((float) optional($headMap->get($medicalHead->id))->head_value, 2);

                if (abs($existingInitial - $initialBasic) <= 0.01
                    && abs($existingHouse - $houseRent) <= 0.01
                    && abs($existingMedical - $medical) <= 0.01) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'created' => false,
                        'message' => 'Matching scale already exists in this department and has been selected.',
                        'scale' => [
                            'id' => $existingScale->id,
                            'label' => $existingScale->scale_no,
                            'scale_no' => $existingScale->scale_no,
                            'department_id' => $existingScale->department_id,
                            'initial_basic' => $existingInitial,
                            'house_rent' => $existingHouse,
                            'medical' => $existingMedical,
                            'salary_value' => round($existingInitial + $existingHouse + $existingMedical, 2),
                        ],
                    ], 200);
                }
            }

            /*
             * Detect the existing scale-number format inside THIS department.
             * Examples:
             *   125ADMH -> 126ADMH
             *   ADM520  -> ADM521
             *
             * If both formats exist, use the format used most often. On a tie,
             * numeric-first is preferred because current scales use values such
             * as 520ADMH. Only the numeric portion is incremented.
             */
            $numericFirst = [];
            $alphaFirst = [];

            foreach ($existingScales as $existingScale) {
                $existingScaleNo = strtoupper(trim((string) $existingScale->scale_no));

                if (preg_match('/^(\d+)([A-Z]+)$/', $existingScaleNo, $matches)) {
                    $numericFirst[] = [
                        'number' => (int) $matches[1],
                        'alpha' => $matches[2],
                    ];
                    continue;
                }

                if (preg_match('/^([A-Z]+)(\d+)$/', $existingScaleNo, $matches)) {
                    $alphaFirst[] = [
                        'number' => (int) $matches[2],
                        'alpha' => $matches[1],
                    ];
                }
            }

            $format = null;
            $alphaPart = null;
            $maxNumber = 0;

            if (count($numericFirst) > 0 || count($alphaFirst) > 0) {
                if (count($numericFirst) >= count($alphaFirst) && count($numericFirst) > 0) {
                    $format = 'numeric_first';
                    usort($numericFirst, function ($a, $b) {
                        return $b['number'] <=> $a['number'];
                    });
                    $maxNumber = $numericFirst[0]['number'];
                    $alphaPart = $numericFirst[0]['alpha'];
                } else {
                    $format = 'alpha_first';
                    usort($alphaFirst, function ($a, $b) {
                        return $b['number'] <=> $a['number'];
                    });
                    $maxNumber = $alphaFirst[0]['number'];
                    $alphaPart = $alphaFirst[0]['alpha'];
                }
            }

            if (!$format || !$alphaPart) {
                // No parseable scale exists yet. Derive an abbreviation from the
                // department and default to numeric-first formatting.
                $words = preg_split('/[^A-Za-z0-9]+/', (string) $department->name, -1, PREG_SPLIT_NO_EMPTY);
                $alphaPart = '';
                foreach ($words as $word) {
                    $alphaPart .= strtoupper(substr($word, 0, 1));
                    if (strlen($alphaPart) >= 4) {
                        break;
                    }
                }

                if (strlen($alphaPart) < 2) {
                    $alphaPart = strtoupper(substr(
                        preg_replace('/[^A-Za-z]/', '', (string) $department->name),
                        0,
                        4
                    ));
                }

                $alphaPart = $alphaPart ?: 'SCL';
                $format = 'numeric_first';
                $maxNumber = 0;
            }

            $nextNumber = ((int) $maxNumber) + 1;
            do {
                $scaleNo = $format === 'numeric_first'
                    ? ($nextNumber . $alphaPart)
                    : ($alphaPart . $nextNumber);

                // Keep globally unique too because manual scale creation currently
                // validates scale_no globally.
                $exists = EmployeeScale::where('created_by', $user->creatorId())
                    ->where('scale_no', $scaleNo)
                    ->exists();

                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            $scale = new EmployeeScale();
            $scale->scale_no = $scaleNo;
            $scale->type = null;
            $scale->effect_from = $request->effect_from;
            $scale->adhoc = 0;
            $scale->department_id = $departmentId;
            $scale->status = 1;
            $scale->owned_by = $user->ownedId();
            $scale->created_by = $user->creatorId();
            $scale->save();

            $generatedValues = [
                (int) $initialHead->id => $initialBasic,
                (int) $houseHead->id => $houseRent,
                (int) $medicalHead->id => $medical,
            ];

            // Same head-append pattern as the existing scale store method.
            foreach ($heads as $head) {
                EmployeeScaleHeads::create([
                    'scale_id' => $scale->id,
                    'scale_no' => $scale->scale_no,
                    'head' => $head->id,
                    'head_value' => $generatedValues[(int) $head->id] ?? 0,
                    'owned_by' => $user->ownedId(),
                    'created_by' => $user->creatorId(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'created' => true,
                'message' => 'Employee Scale ' . $scale->scale_no . ' created and selected.',
                'scale' => [
                    'id' => $scale->id,
                    'label' => $scale->scale_no,
                    'scale_no' => $scale->scale_no,
                    'department_id' => $scale->department_id,
                    'initial_basic' => $initialBasic,
                    'house_rent' => $houseRent,
                    'medical' => $medical,
                    'salary_value' => $salaryValue,
                ],
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unable to create employee scale: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDeptWiseScales(Request $request) {
        if (\Auth::user()->type == 'company') { 
            $employee_scales = EmployeeScale::where('department_id', $request->dept) 
                ->where('status', 1) 
                ->where('created_by', \Auth::user()->creatorId()) 
                ->get(['id', 'scale_no as title']); 
        } else { 
            $employee_scales = EmployeeScale::where('department_id', $request->dept) 
                ->where('status', 1)
                ->where('owned_by', \Auth::user()->ownedId())
                ->get(['id', 'scale_no as title']);
        }
        return response()->json(['success' => true, 'data' => $employee_scales]);
    }
}
