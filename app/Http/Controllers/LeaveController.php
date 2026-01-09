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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage leave')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Leave::where('created_by', '=', \Auth::user()->creatorId());
            } else if (\Auth::user()->type == 'Employee') {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $query = Leave::where('employee_id', '=', $employee->id);
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Leave::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $leaves = $query->paginate(25);


            return view('leave.index', compact('leaves', 'branches'));
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
                    return redirect()->back()->with('error', $messages->first());
                }
            }

            $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
            $leave_type = LeaveType::find($request->leave_type_id);
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));
            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

            $employeeLeaves = EmployeeLeaves::where('employee_id', $request->employee_id ?? $employee->id)->first();
            $available_days = 0;
            if (str_contains(strtolower($leave_type->title) , 'annual' )&& $employeeLeaves) {
                $available_days = $employeeLeaves->annual_total - $employeeLeaves->annual_consumed;
            } elseif (str_contains(strtolower($leave_type->title) , 'casual' )&& $employeeLeaves) {
                $available_days = $employeeLeaves->casual_total - $employeeLeaves->casual_consumed;
            }
            if ((str_contains(strtolower($leave_type->title), 'annual') || str_contains(strtolower($leave_type->title), 'casual')) && $available_days < $total_leave_days) {               dd($available_days,$total_leave_days,$available_days < $total_leave_days,str_contains(strtolower($leave_type->title) , 'annual'));
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
            } else {
                $leave = new Leave();
                $leave->employee_id = \Auth::user()->type == "Employee" ? $employee->id : $request->employee_id;
                $leave->leave_type_id = $request->leave_type_id;
                $leave->applied_on = $request->applied_on;
                $leave->start_date = $request->start_date;
                $leave->end_date = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason = $request->leave_reason;
                $leave->remark = $request->remark;
                $leave->status = 'Pending';
                $leave->owned_by = $leave->employee->branch_id ?? \Auth::user()->ownedId();
                $leave->created_by = \Auth::user()->creatorId();
                $leave->save();

                if ($leave) {
                    if ((str_contains(strtolower($leave->leaveType->title) ,'annual') || str_contains(strtolower($leave->leaveType->title) , 'casual'))){
                        if (str_contains(strtolower($leave->leaveType->title) , 'annual') && $employeeLeaves) {
                            if($employeeLeaves->annual_consumed == $employeeLeaves->annual_total){
                                dd('annual',$employeeLeaves->annual_consumed,$employeeLeaves->annual_total);
                                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
                            }
                            $employeeLeaves->annual_consumed += $total_leave_days;
                        } elseif (str_contains(strtolower($leave->leaveType->title) , 'casual') && $employeeLeaves) {
                            if($employeeLeaves->casual_consumed == $employeeLeaves->casual_total){
                                dd('annual',$employeeLeaves->annual_consumed,$employeeLeaves->annual_total);
                                return redirect()->back()->with('error', __('Leave type ' . $leave_type->title . ' reached a maximum days. Please make sure your selected days are within the available ' . $available_days . ' days.'));
                            }
                            $employeeLeaves->casual_consumed += $total_leave_days;
                            // dd($employeeLeaves,$total_leave_days);
                        }
                        $employeeLeaves->save();
                    }
                }
                DB::commit();
                return redirect()->route('leave.index')->with('success', __('Leave successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
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
                    return redirect()->back()->with('error', $messages->first());
                }

                $leave_type = LeaveType::find($request->leave_type_id);

                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D'));
                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

                $employeeLeaves = EmployeeLeaves::where('employee_id', $request->employee_id)->first();
                $available_days = 0;
                if (str_contains(strtolower($leave->leaveType->title), 'annual') && $employeeLeaves) {
                    $available_days = $employeeLeaves->annual_total - $employeeLeaves->annual_consumed ;
                }
                elseif (str_contains(strtolower($leave->leaveType->title), 'casual') && $employeeLeaves) {
                    $available_days = $employeeLeaves->casual_total - $employeeLeaves->casual_consumed ;
                }
                if ((str_contains(strtolower($leave->leaveType->title), 'annual')) && $available_days < $total_leave_days) {
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

                    $leave->employee_id = $request->employee_id;
                    $leave->leave_type_id = $request->leave_type_id;
                    $leave->applied_on = $request->applied_on;
                    $leave->start_date = $request->start_date;
                    $leave->end_date = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason = $request->leave_reason;
                    $leave->remark = $request->remark;
                    $leave->owned_by = $leave->employees->branch_id ?? \Auth::user()->ownedId();
                    $leave->save();

                    $employeeLeaves->save();

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Leave $leave)
    {
        if (\Auth::user()->can('delete leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leave->delete();
                $leave_type = LeaveType::find($leave->leave_type_id);
                if ((str_contains(strtolower($leave->leaveType->title), 'annual') || str_contains(strtolower($leave->leaveType->title), 'casual'))){
                    if (str_contains(strtolower($leave->leaveType->title), 'annual') ) {
                        $employeeLeaves = EmployeeLeaves::where('employee_id', $leave->employee_id)->first();
                        $employeeLeaves->annual_consumed = $employeeLeaves->annual_consumed - $leave->total_leave_days;
                        $employeeLeaves->save();
                    } elseif (str_contains(strtolower($leave->leaveType->title), 'casual') ) {
                        $employeeLeaves = EmployeeLeaves::where('employee_id', $leave->employee_id)->first();
                        $employeeLeaves->casual_consumed = $employeeLeaves->casual_consumed - $leave->total_leave_days;
                        $employeeLeaves->save();
                    }
                }
                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
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
