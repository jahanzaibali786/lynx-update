<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveEmployeeTransferRequest;
use App\Http\Requests\DeleteEmployeeTransferRequest;
use App\Http\Requests\StoreEmployeeTransferRequest;
use App\Http\Requests\UpdateEmployeeTransferRequest;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePayscaleDetail;
use App\Models\EmployeeTransfer;
use App\Models\User;
use App\Services\EmployeeTransferService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class EmployeeTransferController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $fiscalStartYear = $today->month >= 7 ? $today->year : $today->year - 1;
        $datefrom = $request->filled('datefrom')
            ? $request->input('datefrom')
            : Carbon::create($fiscalStartYear, 7, 1)->toDateString();
        $dateto = $request->filled('dateto')
            ? $request->input('dateto')
            : Carbon::create($fiscalStartYear + 1, 6, 30)->toDateString();
        $type = $request->input('type');
        $status = $request->input('status');
        if (\Auth::user()->can('manage transfer')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = EmployeeTransfer::with('department_from', 'department_to', 'branch_from', 'branch_to')->where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                if (Auth::user()->type == 'Employee') {
                    $emp = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    $query = EmployeeTransfer::with('department', 'branch')->where('owned_by', '=', \Auth::user()->ownedId())->where('employee_id', '=', $emp->id);
                } else {
                    $query = EmployeeTransfer::with('department', 'branch')->where('owned_by', '=', \Auth::user()->ownedId());
                }
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            if ($type != '') {
                if ($type == 'transfer_in') {
                    $query->where('branch_to_id', $request->branches);
                } elseif ($type == 'transfer_out') {
                    $query->where('branch_from_id', $request->branches);
                }
            }
            $startDate = Carbon::parse($datefrom)->startOfMonth()->toDateString();
            $endDate = Carbon::parse($dateto)->endOfMonth()->toDateString();
            $query->whereBetween('transfer_date', [$startDate, $endDate]);
            if ($status !== null && $status !== '') {
                $query = $query->where('status', $status);
            }
            $transfers = $query->orderByDesc('id')->get();

            return view('employee.transfer.index', compact('transfers', 'branches', 'datefrom', 'dateto', 'type', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create(EmployeeTransferService $employeeTransferService)
    {
        if (\Auth::user()->can('create transfer')) {
            return view(
                'employee.transfer.create',
                $employeeTransferService->formOptions(\Auth::user())
            );
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(
        StoreEmployeeTransferRequest $request,
        EmployeeTransferService $employeeTransferService
    )
    {
        $employeeTransferService->create($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Employee Transfer successfully created.'),
            ]);
        }

        return redirect()->route('employee-transfer.index')
            ->with('success', __('Employee Transfer successfully created.'));
    }

    public function show(Transfer $transfer)
    {
        return redirect()->route('employee-transfer.index');
    }

public function print($id)
{
    $transfer = EmployeeTransfer::where('id', $id)->first();
    if(isset($transfer)){
        $html = view('employee.transfer.print', compact('transfer'))->render();
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        // $options->set('defaultFont', 'Arial'); // Optional, but do NOT use 'Arial, sans-serif'
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=\"employee_transfer.pdf\"');
    }else{
        return redirect()->back()->with('error', __('Employee Transfer Not Found !'));
    }
}

    public function edit($id, EmployeeTransferService $employeeTransferService)
    {
        if (\Auth::user()->can('edit transfer')) {
            $transfer = $employeeTransferService->findForUser((int) $id, \Auth::user());
            if (!$transfer) {
                return response()->json(['error' => __('Employee transfer not found.')], 404);
            }

            return view('employee.transfer.edit', array_merge(
                $employeeTransferService->formOptions(\Auth::user()),
                compact('transfer')
            ));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(
        UpdateEmployeeTransferRequest $request,
        $id,
        EmployeeTransferService $employeeTransferService
    )
    {
        $transfer = $employeeTransferService->findForUser((int) $id, $request->user());
        if (!$transfer) {
            return redirect()->back()->with('error', __('Employee transfer not found.'));
        }

        $employeeTransferService->update($transfer, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Transfer successfully updated.'),
            ]);
        }

        return redirect()->route('employee-transfer.index')
            ->with('success', __('Transfer successfully updated.'));
    }

    public function destroy(
        DeleteEmployeeTransferRequest $request,
        $id,
        EmployeeTransferService $employeeTransferService
    )
    {
        $transfer = $employeeTransferService->findForUser((int) $id, $request->user());
        if (! $transfer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Employee transfer not found.'),
                ], 404);
            }

            return redirect()->back()->with('error', __('Employee transfer not found.'));
        }

        $employeeTransferService->delete($transfer, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Transfer successfully deleted.'),
            ]);
        }

        return redirect()->route('employee-transfer.index')
            ->with('success', __('Transfer successfully deleted.'));
    }
    public function employeeDetail(Request $request)
    {
        // dd($request->all());
        $employee = Employee::where('owned_by', '=', $request->id)->get();
        $department = Department::where('owned_by', '=', $request->id)->get();
        $designation = Designation::where('owned_by', '=', $request->id)->get();

        if ($request->emp_no) {
            $employee = Employee::with([
                'employee_payscale_details' => function ($query) {
                    $query->latest()->first();
                }
            ])
                ->where('employee_id', $request->emp_no)
                ->first();
            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee Not Found'
                ], 404);
            }
            if ($employee->owned_by != Auth::user()->ownedId()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee is not from this branch'
                ], 404);
            }
            $department = Department::where('id', $employee[0]->department_id)->first();
            $designation = Designation::where('id', $employee[0]->designation_id)->first();
        }

        return response()->json([
            'status' => 'success',
            'employee' => $employee,
            'department' => $department,
            'designation' => $designation,
        ]);
    }


    public function approve(
        ApproveEmployeeTransferRequest $request,
        $id,
        EmployeeTransferService $employeeTransferService
    )
    {
        $result = $employeeTransferService->approve((int) $id, $request->user());
        $message = $result['already_approved']
            ? __('Transfer is already approved.')
            : __('Transfer successfully approved.');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'id' => $result['transfer']->id,
                'status' => $result['transfer']->status,
            ]);
        }

        return redirect()->route('employee-transfer.index')->with('success', $message);
    }

    public function employeedep(Request $request)
    {
        $employee = Employee::with('department', 'designation')->where('id', '=', $request->id)->first();
        $scale = EmployeePayscaleDetail::with('scale')->where('employee_id', '=', $employee->id)->orderBy('id', 'desc')->first();

        $result = [
            'status' => 'success',
            'employee' => $employee,
            'scale' => $scale,
        ];
        return response()->json($result);
    }
}
