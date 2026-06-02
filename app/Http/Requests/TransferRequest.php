<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'toId' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ((int) $this->input('toId') === $this->user()->id) {
                    $validator->errors()->add('toId', 'You cannot transfer money to yourself.');
                }
            },
        ];
    }
}
