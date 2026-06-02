<?php

namespace App\Domain\Wallet\Repositories;

use App\Domain\Wallet\Entities\Wallet;

interface WalletRepositoryInterface
{
    public function findById(string $id): ?Wallet;

    /** @return Wallet[] */
    public function findByUserId(string $userId): array;

    public function save(Wallet $wallet): Wallet;

    public function update(Wallet $wallet): Wallet;
}
