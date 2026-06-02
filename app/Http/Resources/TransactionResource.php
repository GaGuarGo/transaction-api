<?php

namespace App\Http\Resources;

use App\Domain\Transaction\Entities\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function __construct(
        private readonly Transaction $transaction,
        private readonly array $userWalletIds,
    ) {}

    public function toArray(Request $request): array
    {
        $isSender = in_array($this->transaction->senderWalletId, $this->userWalletIds);

        return [
            'type' => $isSender ? 'sent' : 'received',
            'fromWalletId' => $this->transaction->senderWalletId,
            'toWalletId' => $this->transaction->receiverWalletId,
            'amount' => $this->transaction->amount,
            'date' => $this->transaction->createdAt?->format('c'),
        ];
    }
}
