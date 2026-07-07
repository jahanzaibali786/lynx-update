<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeLeaves;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use DB;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage leave')) {
            $fromDate = $request->input('from_date', Carbon::now()->subMonths(2)->startOfMonth()->format('Y-m-d'));
            $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));
            $selectedBranch = $request->input('branches');
            $selectedEmployee = $request->input('employee_id');

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Leave::with(['employees', 'leaveType', 'addedBy'])->where('created_by', '=', \Auth::user()->creatorId());
                $employeeQuery = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('is_res_ter', 0);
            } else if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $query = Leave::with(['employees', 'leaveType', 'addedBy'])->where('employee_id', '=', $employee->id);
                $employeeQuery = Employee::where('id', '=', optional($employee)->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Leave::with(['employees', 'leaveType', 'addedBy'])->where('owned_by', '=', \Auth::user()->ownedId());
                $employeeQuery = Employee::where('owned_by', '=', \Auth::user()->ownedId())->where('is_res_ter', 0);
            }
            if (!empty($selectedBranch)) {
                $query->where('owned_by', '=', $selectedBranch);
                $employeeQuery->where(function ($query) use ($selectedBranch) {
                    $query->where('owned_by', $selectedBranch)
                        ->orWhere('branch_id', $selectedBranch);
                });
            }
            if (!empty($selectedEmployee)) {
                $query->where('employee_id', '=', $selectedEmployee);
            }
            if (!empty($fromDate)) {
                $query->whereDate('start_date', '>=', $fromDate);
            }
            if (!empty($toDate)) {
                $query->whereDate('start_date', '<=', $toDate);
            }

            $employees = $employeeQuery->orderBy('name')->get()->pluck('name', 'id');
            $employees->prepend('All Employees', '');
            $leaves = $query->orderBy('start_date', 'desc')->orderBy('id', 'desc')->get();


            return view('leave.index', compact('leaves', 'branches', 'employees', 'fromDate', 'toDate', 'selectedBranch', 'selectedEmployee'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create leave')) {
            if (\Auth::user()->type == 'company') {
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
            } else if (\Auth::user()->type == 'Employee') {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->where('is_res_ter', 0)->get()->pluck('name', 'id');
                $leavetypes = LeaveType::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            } else {
                $employees = Employee::where('owned_by', '=', \Auth::user()->ownedId())->where('is_res_ter', 0)->get()->pluck('name', 'id');
                $leavetypes = LeaveType::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
            }

            //    $leavetypes_days = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('leave.create', compact('employees', 'leavetypes', 'branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
        if (\Auth::user()->can('create leave')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'applied_on' => 'required',
                    'leave_reason' => 'required',
                    // 'remark' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                DB::rollback();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $messages->first(),
                        'errors' => $validator->errors(),
                    ], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }

            if (\Auth::user()->type != "Employee") {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    DB::rollback();
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $messages->first(),
                            'errors' => $validator->errors(),
                        ], 422);
                    }
                    return redirect()->back()->with('error', $messages->first());
                }
            }
            $employee = \Auth::user()->type == 'Employee'
                ? Employee::where('user_id', \Auth::id())->first()
                : Employee::find($request->employee_id);

            if (!$employee) {
                DB::rollback();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Employee not found.'),
                        'errors' => ['employee_id' => [__('Employee not found.')]],
                    ], 422);
                }
                return redirect()->back()->with('error', __('Employee not found.'));
            }

            $leave_type = LeaveType::find($request->leave_type_id);
            if (!$leave_type) {
                DB::rollback();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Leave type not found.'),
                        'errors' => ['leave_type_id' => [__('Leave type not found.')]],
                    ], 422);
                }
                return redirect()->back()->with('error', __('Leave type not found.'));
            }
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));
            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

            $employeeLeaves = EmployeeLeaves::where('employee_id', $employee->id)->first();
            $available_days = 0;
            if (str_contains(strtolower($leave_type->title) , 'annual' )&& $employeeLeaves) {
                $available_days = $employeeLeaves->annual_total - $employeeLeaves->annual_consumed;
            } elseif (str_contains(strtolower($leave_type->title) , 'casual' )&& $employeeLeaves) {
                $available_days = $employeeLeaves->casual_total - $employeeLeaves->casual_consumed;
            }
            if ((str_contains(strtolower($leave_type->title), 'annual') || str_contains(strtolower($leave_type->title), 'casual')) && $available_days < $total_leave_days) {
                DB::rollback();
                $message = __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.');
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'errors' => ['leave_type_id' => [$message]],
                    ], 422);
                }
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
            } else {
                $leave = new Leave();
                $leave->employee_id = $employee->id;
                $leave->leave_type_id = $request->leave_type_id;
                $leave->applied_on = $request->applied_on;
                $leave->start_date = $request->start_date;
                $leave->end_date = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason = $request->leave_reason;
                $leave->remark = $request->remark;
                $leave->status = 'Pending';
                $leave->added_by = \Auth::id();
                $leave->owned_by = $employee->owned_by;
                $leave->created_by = \Auth::user()->creatorId();
                $leave->save();

                if ($leave) {
                    if ((str_contains(strtolower($leave->leaveType->title) ,'annual') || str_contains(strtolower($leave->leaveType->title) , 'casual'))){
                        if (str_contains(strtolower($leave->leaveType->title) , 'annual') && $employeeLeaves) {
                            if($employeeLeaves->annual_consumed == $employeeLeaves->annual_total){
                                DB::rollback();
                                $message = __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.');
                                if ($request->ajax()) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => $message,
                                        'errors' => ['leave_type_id' => [$message]],
                                    ], 422);
                                }
                                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
                            }
                            $employeeLeaves->annual_consumed += $total_leave_days;
                        } elseif (str_contains(strtolower($leave->leaveType->title) , 'casual') && $employeeLeaves) {
                            if($employeeLeaves->casual_consumed == $employeeLeaves->casual_total){
                                DB::rollback();
                                $message = __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.');
                                if ($request->ajax()) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => $message,
                                        'errors' => ['leave_type_id' => [$message]],
                                    ], 422);
                                }
                                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
                            }
                            $employeeLeaves->casual_consumed += $total_leave_days;
                            // dd($employeeLeaves,$total_leave_days);
                        }
                        $employeeLeaves->save();
                    }
                }
                DB::commit();
                if ($request->ajax()) {
                    $leave->load(['employees', 'leaveType', 'addedBy']);
                    return response()->json([
                        'success' => true,
                        'message' => __('Leave successfully created.'),
                        'row_html' => view('leave.partials.row', [
                            'leave' => $leave,
                            'loopIteration' => 1,
                        ])->render(),
                    ]);
                }
                return redirect()->route('leave.index')->with('success', __('Leave successfully created.'));
            }
        } else {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(Leave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit(Leave $leave)
    {
        if (\Auth::user()->can('edit leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                if (\Auth::user()->type == 'company') {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId());
                    $branches = User::where('type', '=', 'branch')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                    $branches->prepend('Select Branch', '');
                } else {
                    $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                    $employees = Employee::where('owned_by', '=', \Auth::user()->ownedId());
                }
                
                $leavetypes = LeaveType::get();
                // dd($leavetypes);
                $employees = $employees->where('is_res_ter', 0)->where('id', '=', $leave->employee_id)->get()->pluck('name', 'id');
                return view('leave.edit', compact('leave', 'employees', 'leavetypes', 'branches'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {
        $leave = Leave::find($leave);
        if (!$leave) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Leave not found.'),
                ], 404);
            }
            return redirect()->back()->with('error', __('Leave not found.'));
        }
        if (\Auth::user()->can('edit leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'leave_type_id' => 'required',
                        'applied_on' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        // 'remark' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $messages->first(),
                            'errors' => $validator->errors(),
                        ], 422);
                    }
                    return redirect()->back()->with('error', $messages->first());
                }
                if (\Auth::user()->type != "Employee") {
                    $validator = \Validator::make($request->all(), [
                        'employee_id' => 'required',
                    ]);

                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => $messages->first(),
                                'errors' => $validator->errors(),
                            ], 422);
                        }
                        return redirect()->back()->with('error', $messages->first());
                    }
                }

                $employee = \Auth::user()->type == 'Employee'
                    ? Employee::where('user_id', \Auth::id())->first()
                    : Employee::find($request->employee_id);
                if (!$employee) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Employee not found.'),
                            'errors' => ['employee_id' => [__('Employee not found.')]],
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('Employee not found.'));
                }

                $leave_type = LeaveType::find($request->leave_type_id);
                if (!$leave_type) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Leave type not found.'),
                            'errors' => ['leave_type_id' => [__('Leave type not found.')]],
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('Leave type not found.'));
                }

                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D'));
                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

                $employeeLeaves = EmployeeLeaves::where('employee_id', $employee->id)->first();
                $available_days = 0;
                if (str_contains(strtolower($leave->leaveType->title), 'annual') && $employeeLeaves) {
                    $available_days = $employeeLeaves->annual_total - $employeeLeaves->annual_consumed ;
                }
                elseif (str_contains(strtolower($leave->leaveType->title), 'casual') && $employeeLeaves) {
                    $available_days = $employeeLeaves->casual_total - $employeeLeaves->casual_consumed ;
                }
                if ((str_contains(strtolower($leave->leaveType->title), 'annual')) && $available_days < $total_leave_days) {
                    $message = __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.');
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                            'errors' => ['leave_type_id' => [$message]],
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
                } else {
                    if ($leave->leave_type_id != $request->leave_type_id) {
                        if (str_contains(strtolower($leave->leaveType->title) , 'annual') && $employeeLeaves) {
                            $employeeLeaves->annual_consumed -= $leave->total_leave_days;
                        } elseif (str_contains(strtolower($leave->leaveType->title) , 'casual') && $employeeLeaves) {
                            $employeeLeaves->casual_consumed -= $leave->total_leave_days;
                        }
                    }else{}
                    // dd('out',$leave,$employeeLeaves,$request->employee_id,$available_days,$request->all(),$total_leave_days);
                    
                    if (str_contains(strtolower($leave->leaveType->title), 'annual') && $employeeLeaves) {
                        $employeeLeaves->annual_consumed = ($employeeLeaves->annual_consumed + $total_leave_days) - $leave->total_leave_days;
                    } elseif (str_contains(strtolower($leave->leaveType->title), 'casual') && $employeeLeaves) {
                        $employeeLeaves->casual_consumed = ($employeeLeaves->casual_consumed + $total_leave_days) - $leave->total_leave_days;
                    }

                    $leave->employee_id = $employee->id;
                    $leave->leave_type_id = $request->leave_type_id;
                    $leave->applied_on = $request->applied_on;
                    $leave->start_date = $request->start_date;
                    $leave->end_date = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason = $request->leave_reason;
                    $leave->remark = $request->remark;
                    $leave->added_by = $leave->added_by ?: \Auth::user()->id;
                    $leave->owned_by = $employee->owned_by;
                    $leave->save();

                    if ($employeeLeaves) {
                        $employeeLeaves->save();
                    }

                    if ($request->ajax()) {
                        $leave->load(['employees', 'leaveType', 'addedBy']);
                        return response()->json([
                            'success' => true,
                            'message' => __('Leave successfully updated.'),
                            'row_html' => view('leave.partials.row', [
                                'leave' => $leave,
                                'loopIteration' => '',
                            ])->render(),
                            'leave_id' => $leave->id,
                        ]);
                    }
                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                }
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Permission denied.'),
                    ], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Request $request, Leave $leave)
    {
        if (\Auth::user()->can('delete leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leaveId = $leave->id;
                $leaveTypeTitle = strtolower(optional($leave->leaveType)->title ?? '');
                $leave->delete();
                if ((str_contains($leaveTypeTitle, 'annual') || str_contains($leaveTypeTitle, 'casual'))){
                    if (str_contains($leaveTypeTitle, 'annual') ) {
                        $employeeLeaves = EmployeeLeaves::where('employee_id', $leave->employee_id)->first();
                        if ($employeeLeaves) {
                            $employeeLeaves->annual_consumed = $employeeLeaves->annual_consumed - $leave->total_leave_days;
                            $employeeLeaves->save();
                        }
                    } elseif (str_contains($leaveTypeTitle, 'casual') ) {
                        $employeeLeaves = EmployeeLeaves::where('employee_id', $leave->employee_id)->first();
                        if ($employeeLeaves) {
                            $employeeLeaves->casual_consumed = $employeeLeaves->casual_consumed - $leave->total_leave_days;
                            $employeeLeaves->save();
                        }
                    }
                }
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('Leave successfully deleted.'),
                        'leave_id' => $leaveId,
                    ]);
                }
                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Permission denied.'),
                    ], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.'),
                ], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $leave = Leave::find($id);
        $employee = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {

        $leave = Leave::find($request->leave_id);

        $leave->status = $request->status;
        if ($leave->status == 'Approval') {
            $startDate = new \DateTime($leave->start_date);
            $endDate = new \DateTime($leave->end_date);
            $total_leave_days = $startDate->diff($endDate)->days;
            $leave->total_leave_days = $total_leave_days;
            $leave->status = 'Approved';
        }

        $leave->save();


        //Send Email
        $setings = Utility::settings();
        if (!empty($employee->id)) {
            if ($setings['leave_status'] == 1) {

                $employee = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();
                $leave->name = !empty($employee->name) ? $employee->name : '';
                $leave->email = !empty($employee->email) ? $employee->email : '';
                //            dd($leave);

                $actionArr = [

                    'leave_name' => !empty($employee->name) ? $employee->name : '',
                    'leave_status' => $leave->status,
                    'leave_reason' => $leave->leave_reason,
                    'leave_start_date' => $leave->start_date,
                    'leave_end_date' => $leave->end_date,
                    'total_leave_days' => $leave->total_leave_days,

                ];
                //            dd($actionArr);
                $resp = Utility::sendEmailTemplate('leave_action_sent', [$employee->id => $employee->email], $actionArr);


                return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

            }

        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }


    public function jsoncount(Request $request)
    {
        $leave_counts = [];
        $leave_types = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
        $employee = Employee::find($request->employee_id);
        $employeeLeaves = EmployeeLeaves::where('employee_id', $request->employee_id)->first();
        // dd($employeeLeaves);
        $isOnProbation = $employee ? $employee->probation_end > now() : false;
        foreach ($leave_types as $type) {
            $leave_count = [
                'total_leave' => 0,
                'title' => $type->title,
                'days' => $type->days,
                'id' => $type->id,
                'isOnProbation' => $isOnProbation,
            ];
            if (strtolower($type->title) == 'annual' || strtolower($type->title) == 'annual leave' && $employeeLeaves) {
                // dd('');
                $leave_count['days'] = $employeeLeaves->annual_total;
                $leave_count['total_leave'] = $employeeLeaves->annual_consumed ?? 0;
                $leave_count['is_annual'] = true;
            }
            elseif (strtolower($type->title) == 'casual' && $employeeLeaves) {
                $leave_count['days'] = $employeeLeaves->casual_total;
                $leave_count['total_leave'] = $employeeLeaves->casual_consumed ?? 0;
                $leave_count['is_casual'] = true;
                // dd('casual', $leave_count);
            }
            else {
                $counts = Leave::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave'))
                    ->where('leave_type_id', $type->id)
                    ->where('employee_id', $request->employee_id)
                    ->groupBy('leaves.leave_type_id')
                    ->first();
                $leave_count['total_leave'] = !empty($counts) ? $counts['total_leave'] : 0;
            }
            $leave_counts[] = $leave_count;
        }
        return $leave_counts;
    }

    public function branchemployees(Request $request)
    {
        $employee = Employee::where('owned_by', '=', $request->id)->where('is_res_ter', 0)->get();

        $result = [
            'status' => 'success',
            'employee' => $employee,
        ];
        return response()->json($result);
    }

}
