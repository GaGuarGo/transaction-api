<?php

namespace App\Domain\Transaction\Entities;

use DateTimeImmutable;

class Transaction
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $senderId,
        public readonly int $receiverId,
        public readonly int $amount, // in cents
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}
}
