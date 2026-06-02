<?php

namespace App\Application\DTOs;

class TransferDTO
{
    public function __construct(
        public readonly string $senderWalletId,
        public readonly string $receiverWalletId,
        public readonly int $amount, // in cents
    ) {}
}
