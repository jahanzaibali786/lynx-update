<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit transfer') ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();
        $creatorId = (int) $user->creatorId();

        $branchRule = Rule::exists('users', 'id')->where(function ($query) use ($creatorId) {
            $query->where('id', $creatorId)
                ->orWhere(function ($branchQuery) use ($creatorId) {
                    $branchQuery->where('type', 'branch')
                        ->where('created_by', $creatorId);
                });
        });

        return [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('created_by', $creatorId),
            ],
            'branch_to_id' => ['required', 'integer', $branchRule],
            'department_to_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where('created_by', $creatorId),
            ],
            'designation_to_id' => [
                'required',
                'integer',
                Rule::exists('designations', 'id')->where(function ($query) use ($creatorId) {
                    $query->where('created_by', $creatorId)
                        ->where('department_id', $this->input('department_to_id'));
                }),
            ],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
