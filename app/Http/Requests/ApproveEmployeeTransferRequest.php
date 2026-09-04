<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveEmployeeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit transfer') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
