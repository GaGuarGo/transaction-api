<?php

namespace App\Application\DTOs;

class CreateWalletDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
    ) {}
}
