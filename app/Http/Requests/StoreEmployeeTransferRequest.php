<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create transfer') ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();
        $creatorId = (int) $user->creatorId();

        $employeeRule = Rule::exists('employees', 'id')
            ->where('created_by', $creatorId);

        if ($user->type !== 'company') {
            $employeeRule->where('owned_by', (int) $user->ownedId());
        }

        $branchRule = Rule::exists('users', 'id')->where(function ($query) use ($creatorId) {
            $query->where('id', $creatorId)
                ->orWhere(function ($branchQuery) use ($creatorId) {
                    $branchQuery->where('type', 'branch')
                        ->where('created_by', $creatorId);
                });
        });

        $branchFromRule = $user->type === 'company'
            ? $branchRule
            : Rule::in([(int) $user->ownedId()]);

        return [
            'employee_id' => ['required', 'integer', $employeeRule],
            'branch_from_id' => ['required', 'integer', $branchFromRule],
            'branch_to_id' => ['required', 'integer', $branchRule],
            'department_to_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where('created_by', $creatorId),
            ],
            'designation_to_id' => [
                'required',
                'integer',
                Rule::exists('designations', 'id')
                    ->where('created_by', $creatorId)
                    ->where('department_id', $this->input('department_to_id')),
            ],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'transfer_type' => ['nullable', 'in:inter-branch,inter-city'],
        ];
    }
}
