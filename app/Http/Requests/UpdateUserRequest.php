<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'username' => ['required', 'string', 'min:3', 'max:50', "unique:users,username,{$userId}"],
            'email' => ['required', 'email', "unique:users,email,{$userId}"],
        ];
    }
}
