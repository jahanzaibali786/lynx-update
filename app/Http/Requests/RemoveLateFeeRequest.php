<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RemoveLateFeeRequest extends FormRequest
{
    // public function authorize(): bool
    // {
    //     return $this->user()?->can('remove late fee') ?? false;
    // }

    public function rules(): array
    {
        return [
            'challan_no' => ['required', 'string', 'exists:challans,challanNo'],
            'remove_amount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'challan_no.required' => __('Challan number is required.'),
            'challan_no.exists' => __('Challan not found.'),
            'remove_amount.required' => __('Remove amount is required.'),
            'remove_amount.numeric' => __('Remove amount must be a number.'),
            'remove_amount.gt' => __('Remove amount must be greater than zero.'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422));
    }

    // protected function failedAuthorization(): void
    // {
    //     throw new HttpResponseException(response()->json([
    //         'status' => 'error',
    //         'message' => __('Permission denied.'),
    //     ], 403));
    // }
}
