<?php

namespace App\Http\Resources;

use App\Domain\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function __construct(private readonly User $user) {}

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user->id,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'birthdate' => $this->user->birthdate->format('Y-m-d'),
        ];
    }
}
