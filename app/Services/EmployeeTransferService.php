<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeTransferService
{
    public function formOptions(User $user): array
    {
        $creatorId = (int) $user->creatorId();
        $departments = Department::where('created_by', $creatorId)->pluck('name', 'id');
        $departments->prepend('Select Department', '');
        $designations = Designation::where('created_by', $creatorId)->pluck('name', 'id');
        $designations->prepend('Select Designation', '');

        if ($user->type === 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id');
            $branches->prepend($user->name, $user->id);
            $branches->prepend('Select Branch', '');
            $fromBranches = clone $branches;
            $toBranches = clone $branches;
            $employees = Employee::where('created_by', $creatorId)->pluck('name', 'id');
        } else {
            $fromBranches = User::where('id', $user->ownedId())->pluck('name', 'id');
            $toBranches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->pluck('name', 'id');
            $toBranches->prepend('Select Branch', '');
            $employees = Employee::where('created_by', $creatorId)
                ->where('owned_by', $user->ownedId())
                ->pluck('name', 'id');
        }

        $employees->prepend('Select Employee', '');

        return [
            'departments' => $departments,
            'designations' => $designations,
            'from_branches' => $fromBranches,
            'to_branches' => $toBranches,
            'employees' => $employees,
        ];
    }

    public function findForUser(int $id, User $user): ?EmployeeTransfer
    {
        $query = EmployeeTransfer::where('created_by', $user->creatorId());

        if ($user->type !== 'company') {
            $query->where('owned_by', $user->ownedId());
        }

        return $query->find($id);
    }

    public function create(array $data, User $user): EmployeeTransfer
    {
        return DB::transaction(function () use ($data, $user) {
            $employeeQuery = Employee::where('created_by', $user->creatorId());
            if ($user->type !== 'company') {
                $employeeQuery->where('owned_by', $user->ownedId());
            }

            $employee = $employeeQuery->lockForUpdate()->find($data['employee_id']);
            if (! $employee) {
                throw ValidationException::withMessages([
                    'employee_id' => __('The selected employee is not available for transfer.'),
                ]);
            }

            $currentBranchId = (int) ($employee->branch_id ?: $employee->owned_by);
            if ($currentBranchId !== (int) $data['branch_from_id']) {
                throw ValidationException::withMessages([
                    'branch_from_id' => __('Branch From must match the employee current branch.'),
                ]);
            }

            return EmployeeTransfer::create([
                'employee_id' => $employee->id,
                'branch_from_id' => $currentBranchId,
                'branch_to_id' => $data['branch_to_id'],
                'department_from_id' => $employee->department_id,
                'department_to_id' => $data['department_to_id'],
                'designation_from_id' => $employee->designation_id,
                'designation_to_id' => $data['designation_to_id'],
                'transfer_date' => $data['transfer_date'],
                'transfer_reason' => $data['description'] ?? '',
                'status' => 0,
                'owned_by' => $user->ownedId(),
                'created_by' => $user->creatorId(),
            ]);
        });
    }

    public function update(EmployeeTransfer $transfer, array $data, User $user): EmployeeTransfer
    {
        return DB::transaction(function () use ($transfer, $data, $user) {
            $lockedTransfer = EmployeeTransfer::where('created_by', $user->creatorId())
                ->lockForUpdate()
                ->find($transfer->id);

            if (! $lockedTransfer || ($user->type !== 'company' && (int) $lockedTransfer->owned_by !== (int) $user->ownedId())) {
                throw ValidationException::withMessages([
                    'employee_id' => __('The employee transfer record is not available.'),
                ]);
            }

            if ((int) $lockedTransfer->status === 1) {
                throw ValidationException::withMessages([
                    'employee_id' => __('An approved employee transfer cannot be edited.'),
                ]);
            }

            if ((int) $data['employee_id'] !== (int) $lockedTransfer->employee_id) {
                throw ValidationException::withMessages([
                    'employee_id' => __('The employee cannot be changed on an existing transfer.'),
                ]);
            }

            $lockedTransfer->branch_to_id = $data['branch_to_id'];
            $lockedTransfer->department_to_id = $data['department_to_id'];
            $lockedTransfer->designation_to_id = $data['designation_to_id'];
            $lockedTransfer->transfer_date = $data['transfer_date'];
            $lockedTransfer->transfer_reason = $data['description'] ?? '';
            $lockedTransfer->save();

            return $lockedTransfer;
        });
    }

    public function approve(int $id, User $user): array
    {
        return DB::transaction(function () use ($id, $user) {
            $transferQuery = EmployeeTransfer::where('created_by', $user->creatorId());
            if ($user->type !== 'company') {
                $transferQuery->where('owned_by', $user->ownedId());
            }

            $transfer = $transferQuery->lockForUpdate()->find($id);
            if (! $transfer) {
                throw ValidationException::withMessages([
                    'transfer' => __('The employee transfer record is not available.'),
                ]);
            }

            if ((int) $transfer->status === 1) {
                return ['transfer' => $transfer, 'already_approved' => true];
            }

            $employee = Employee::where('created_by', $user->creatorId())
                ->lockForUpdate()
                ->find($transfer->employee_id);

            if (! $employee) {
                throw ValidationException::withMessages([
                    'employee_id' => __('Employee not found.'),
                ]);
            }

            $employee->owned_by = $transfer->branch_to_id;
            $employee->branch_id = $transfer->branch_to_id;
            $employee->department_id = $transfer->department_to_id;
            $employee->designation_id = $transfer->designation_to_id;
            $employee->save();

            $transfer->status = 1;
            $transfer->save();

            return ['transfer' => $transfer, 'already_approved' => false];
        });
    }

    public function delete(EmployeeTransfer $transfer, User $user): void
    {
        DB::transaction(function () use ($transfer, $user) {
            $transferQuery = EmployeeTransfer::where('created_by', $user->creatorId());
            if ($user->type !== 'company') {
                $transferQuery->where('owned_by', $user->ownedId());
            }

            $lockedTransfer = $transferQuery->lockForUpdate()->find($transfer->id);
            if (! $lockedTransfer) {
                throw ValidationException::withMessages([
                    'transfer' => __('The employee transfer record is not available.'),
                ]);
            }

            if ((int) $lockedTransfer->status === 1 && $user->type !== 'company') {
                throw ValidationException::withMessages([
                    'transfer' => __('Only the company can delete an approved employee transfer.'),
                ]);
            }

            if ((int) $lockedTransfer->status === 1) {
                $hasLaterApprovedTransfer = EmployeeTransfer::where('created_by', $user->creatorId())
                    ->where('employee_id', $lockedTransfer->employee_id)
                    ->where('status', 1)
                    ->where('id', '>', $lockedTransfer->id)
                    ->exists();

                if ($hasLaterApprovedTransfer) {
                    throw ValidationException::withMessages([
                        'transfer' => __('Delete the employee latest approved transfer first.'),
                    ]);
                }

                $employee = Employee::where('created_by', $user->creatorId())
                    ->lockForUpdate()
                    ->find($lockedTransfer->employee_id);

                if (! $employee) {
                    throw ValidationException::withMessages([
                        'employee_id' => __('Employee not found.'),
                    ]);
                }

                $employee->owned_by = $lockedTransfer->branch_from_id;
                $employee->branch_id = $lockedTransfer->branch_from_id;
                $employee->department_id = $lockedTransfer->department_from_id;
                $employee->designation_id = $lockedTransfer->designation_from_id;
                $employee->save();
            }

            $lockedTransfer->delete();
        });
    }
}
