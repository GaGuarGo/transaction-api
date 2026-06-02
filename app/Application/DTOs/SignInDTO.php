<?php

namespace App\Application\DTOs;

class SignInDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
    ) {}
}
