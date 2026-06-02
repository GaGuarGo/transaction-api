<?php

namespace App\Domain\Wallet\Repositories;

use App\Domain\Wallet\Entities\Wallet;

interface WalletRepositoryInterface
{
    public function findById(string $id): ?Wallet;

    /** Locks the row for update — must be called inside a DB transaction. */
    public function findByIdForUpdate(string $id): ?Wallet;

    /** @return Wallet[] */
    public function findByUserId(string $userId): array;

    public function save(Wallet $wallet): Wallet;

    public function update(Wallet $wallet): Wallet;
}
