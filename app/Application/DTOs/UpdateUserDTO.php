<?php

namespace App\Application\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $username,
        public readonly string $email,
    ) {}
}
