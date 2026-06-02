<?php

namespace App\Application\DTOs;

class TransferDTO
{
    public function __construct(
        public readonly int $senderWalletId,
        public readonly int $receiverWalletId,
        public readonly int $amount, // in cents
    ) {}
}
