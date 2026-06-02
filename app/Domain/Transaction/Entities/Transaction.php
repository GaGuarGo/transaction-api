<?php

namespace App\Domain\Transaction\Entities;

use DateTimeImmutable;

class Transaction
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $senderWalletId,
        public readonly string $receiverWalletId,
        public readonly int $amount, // in cents
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}
}
