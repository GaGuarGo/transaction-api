<?php

namespace App\Application\DTOs;

class TransferDTO
{
    public function __construct(
        public readonly int $senderId,
        public readonly int $receiverId,
        public readonly int $amount, // in cents
    ) {}
}
