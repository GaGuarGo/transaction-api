<?php

namespace App\Application\UseCases\Transaction;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class GetTransactionHistoryUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function execute(int $userId): array
    {
        return $this->transactionRepository->findByUserId($userId);
    }
}
