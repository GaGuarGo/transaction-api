<?php

namespace App\Http\Resources;

use App\Domain\Wallet\Entities\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function __construct(private readonly Wallet $wallet) {}

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->wallet->id,
            'name' => $this->wallet->name,
            'balance' => $this->wallet->balance,
        ];
    }
}
