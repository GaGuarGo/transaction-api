<?php

namespace App\Domain\Wallet\Entities;

use DateTimeImmutable;

class Wallet
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly int $balance, // in cents
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}

    public function withBalance(int $balance): self
    {
        return new self($this->id, $this->userId, $this->name, $balance, $this->createdAt);
    }
}
