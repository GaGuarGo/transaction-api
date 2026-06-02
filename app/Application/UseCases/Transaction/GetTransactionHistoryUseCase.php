<?php

namespace App\Application\UseCases\Transaction;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

class GetTransactionHistoryUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function execute(string $userId, ?string $walletId = null): array
    {
        return $this->transactionRepository->findByUserId($userId, $walletId);
    }
}
