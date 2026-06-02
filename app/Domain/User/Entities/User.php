<?php

namespace App\Domain\User\Entities;

use DateTimeImmutable;

class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly DateTimeImmutable $birthdate,
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}

    public function withUpdatedFields(string $username, string $email): self
    {
        return new self(
            $this->id,
            $username,
            $email,
            $this->password,
            $this->birthdate,
            $this->createdAt,
        );
    }
}
