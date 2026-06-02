<?php

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): Transaction;

    /** @return Transaction[] */
    public function findByUserId(int $userId): array;
}
