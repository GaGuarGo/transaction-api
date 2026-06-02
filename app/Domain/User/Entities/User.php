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
        public readonly int $balance, // in cents
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}

    public function withBalance(int $balance): self
    {
        return new self(
            $this->id,
            $this->username,
            $this->email,
            $this->password,
            $this->birthdate,
            $balance,
            $this->createdAt,
        );
    }

    public function withUpdatedFields(string $username, string $email): self
    {
        return new self(
            $this->id,
            $username,
            $email,
            $this->password,
            $this->birthdate,
            $this->balance,
            $this->createdAt,
        );
    }
}
