<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DailyClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'deposit_date' => ['required', 'date'],
            'issued_by_id' => ['required', 'integer', 'different:received_by_id'],
            'received_by_id' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
        ];

        foreach ([5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1] as $denomination) {
            $rules['note_' . $denomination] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'issued_by_id.different' => __('Issued By and Received By cannot be the same employee.'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => $validator->errors()->first(),
        ], 422));
    }
}
