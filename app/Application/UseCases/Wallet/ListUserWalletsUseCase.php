<?php

namespace App\Application\UseCases\Wallet;

use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

class ListUserWalletsUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(int $userId): array
    {
        return $this->walletRepository->findByUserId($userId);
    }
}
