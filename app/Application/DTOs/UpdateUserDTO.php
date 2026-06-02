<?php

namespace App\Application\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly string $email,
    ) {}
}
