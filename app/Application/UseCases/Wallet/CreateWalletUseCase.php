<?php

namespace App\Application\UseCases\Wallet;

use App\Application\DTOs\CreateWalletDTO;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

class CreateWalletUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(CreateWalletDTO $dto): Wallet
    {
        return $this->walletRepository->save(new Wallet(
            id: null,
            userId: $dto->userId,
            name: $dto->name,
            balance: 0,
        ));
    }
}
