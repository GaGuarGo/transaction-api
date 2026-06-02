<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fromWalletId' => ['required', 'integer', 'exists:wallets,id'],
            'toWalletId' => ['required', 'integer', 'exists:wallets,id'],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('fromWalletId') === $this->input('toWalletId')) {
                    $validator->errors()->add('toWalletId', 'Cannot transfer to the same wallet.');
                }
            },
        ];
    }
}
