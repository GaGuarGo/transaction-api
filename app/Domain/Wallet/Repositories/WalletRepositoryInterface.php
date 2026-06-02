<?php

namespace App\Domain\Wallet\Repositories;

use App\Domain\Wallet\Entities\Wallet;

interface WalletRepositoryInterface
{
    public function findById(int $id): ?Wallet;

    /** @return Wallet[] */
    public function findByUserId(int $userId): array;

    public function save(Wallet $wallet): Wallet;

    public function update(Wallet $wallet): Wallet;
}
