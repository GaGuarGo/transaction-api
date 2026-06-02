<?php

namespace App\Http\Resources;

use App\Domain\Transaction\Entities\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function __construct(
        private readonly Transaction $transaction,
        private readonly int $currentUserId,
    ) {}

    public function toArray(Request $request): array
    {
        $isSender = $this->transaction->senderId === $this->currentUserId;

        return [
            'type' => $isSender ? 'sent' : 'received',
            'toId' => $isSender ? $this->transaction->receiverId : null,
            'fromId' => $isSender ? null : $this->transaction->senderId,
            'amount' => $this->transaction->amount,
            'date' => $this->transaction->createdAt?->format('c'),
        ];
    }
}
