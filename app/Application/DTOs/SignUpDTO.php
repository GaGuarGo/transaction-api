<?php

namespace App\Application\DTOs;

class SignUpDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly string $birthdate,
    ) {}
}
