<?php

namespace App\Application\UseCases\Wallet;

use App\Application\Exceptions\UnauthorizedWalletAccessException;
use App\Application\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

class GetWalletUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(string $walletId, string $requestingUserId): Wallet
    {
        $wallet = $this->walletRepository->findById($walletId);

        if (! $wallet) {
            throw new WalletNotFoundException;
        }

        if ($wallet->userId !== $requestingUserId) {
            throw new UnauthorizedWalletAccessException;
        }

        return $wallet;
    }
}
