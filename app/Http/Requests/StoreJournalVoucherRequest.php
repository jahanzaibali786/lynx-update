<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJournalVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = strtoupper($this->input('voucher_type') ?: 'JV');
        $type = in_array($type, ['JV', 'CPV', 'BPV', 'CRV', 'BRV'], true) ? $type : 'JV';

        if ($type === 'JV') {
            return $this->user()?->can('create journal voucher') ?? false;
        }

        return $this->user()?->can('create journal entry') ?? false;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'voucher_type' => ['nullable', 'in:jv,cpv,bpv,crv,brv,JV,CPV,BPV,CRV,BRV'],
            'status' => ['nullable', 'in:Draft,Submitted,Approved,Posted,Reversed'],
            'branches' => ['nullable', 'integer'],
            'bank_id' => ['nullable', 'integer'],
            'payment_mode' => ['nullable', 'string', 'max:50'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'cheque_date' => ['nullable', 'date'],
            'user_type' => ['nullable', 'in:Customer,Vender,Vendor,Employee,Student'],
            'user_id' => ['nullable', 'integer'],
            'transaction_no' => ['nullable', 'string', 'max:150'],
            'reversed_entry_id' => ['nullable', 'integer'],
            'reversed_timestamp' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'accounts' => ['required', 'array', 'min:1'],
            'category_type_id' => ['nullable', 'integer'],
            'payee_account_title' => ['nullable', 'string', 'max:191'],
            'payee_account_no' => ['nullable', 'string', 'max:191'],
            'payee_contact' => ['nullable', 'string', 'max:191'],
            'payee_email' => ['nullable', 'email', 'max:191'],
            'payee_cnic' => ['nullable', 'string', 'max:191'],
            'receiver_name' => ['nullable', 'string', 'max:191'],
            'receiver_cnic' => ['nullable', 'string', 'max:191'],
            'receiver_contact' => ['nullable', 'string', 'max:191'],
            'receiver_email' => ['nullable', 'email', 'max:191'],
            'payment_date' => ['nullable', 'date'],
            'voucher_series' => ['nullable', 'in:SYSTEM,MANUAL,system,manual'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => __('Permission denied.'),
        ], 403));
    }
}
