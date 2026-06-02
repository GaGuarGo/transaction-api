<?php

namespace App\Application\DTOs;

class CreateWalletDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $name,
    ) {}
}
